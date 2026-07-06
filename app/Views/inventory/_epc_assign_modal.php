<?php
/** @var string $postUrl */
/** @var string $tagsUrl e.g. products/tags */
/** @var string $removeTagUrl */
/** @var string $updateTagUrl */
/** @var string $itemLabel */
$modalKey = md5($postUrl);
?>
<div id="epcAssignModal" class="fixed inset-0 z-[100] hidden bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="epcAssignModalTitle">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-lg mx-auto shadow-xl max-h-[90vh] overflow-y-auto mt-[5vh]">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-purple-600 dark:text-purple-400 text-2xl">rss_feed</span>
            </div>
            <div>
                <h3 id="epcAssignModalTitle" class="text-lg font-semibold text-gray-900 dark:text-white">Manage UHF Tags</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400" id="epcAssignItemName"><?= esc($itemLabel) ?></p>
                <p class="text-xs text-gray-400" id="epcAssignModeInfo"></p>
            </div>
        </div>

        <div id="epcTagList" class="mb-4 space-y-2 hidden"></div>

        <form id="epcAssignForm">
            <?= csrf_field() ?>
            <input type="hidden" id="epcAssignItemId" name="id">

            <div class="mb-4 grid grid-cols-1 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">EPC Tag Number <span class="text-red-500">*</span></label>
                    <input type="text" id="epcAssignInput" name="epc_no" placeholder="e.g. E200471472C06426C2510112"
                           class="block w-full rounded-lg border border-gray-300 dark:border-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 sm:text-sm p-2.5 font-mono uppercase" autocomplete="off"/>
                </div>
                <div id="epcQtyWrap">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity for this tag</label>
                    <input type="number" step="0.001" min="0" id="epcAssignQty" name="quantity" class="block w-full rounded-lg border border-gray-300 dark:border-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white sm:text-sm p-2.5"/>
                    <p class="text-xs text-gray-500 mt-1">Each tag can represent a different quantity.</p>
                </div>
            </div>

            <div id="epcAssignError" class="hidden mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 rounded-lg text-sm text-red-700 dark:text-red-400"></div>
            <div id="epcAssignSuccess" class="hidden mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 rounded-lg text-sm text-green-700 dark:text-green-400"></div>

            <div class="flex gap-3">
                <button type="button" onclick="window.closeEpcAssignModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium">Close</button>
                <button type="submit" id="epcAssignSubmitBtn" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg font-medium flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">save</span> Add Tag
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    if (window.__epcAssignModalInit_<?= $modalKey ?>) return;
    window.__epcAssignModalInit_<?= $modalKey ?> = true;

    const epcAssignPostUrl = <?= json_encode($postUrl) ?>;
    const epcTagsUrlBase = <?= json_encode(rtrim($tagsUrl, '/')) ?>;
    const epcRemoveTagUrl = <?= json_encode($removeTagUrl) ?>;
    const epcUpdateTagUrl = <?= json_encode($updateTagUrl ?? '') ?>;
    const csrfName = <?= json_encode(csrf_token()) ?>;
    const csrfHash = <?= json_encode(csrf_hash()) ?>;

    let epcModalTagMode = 'single';
    let epcModalDefaultQty = 0;

    function getModal() {
        return document.getElementById('epcAssignModal');
    }

    window.openEpcAssignModal = function(itemId, itemName) {
        const modal = getModal();
        if (!modal) return;

        document.getElementById('epcAssignItemId').value = itemId;
        document.getElementById('epcAssignItemName').textContent = itemName;
        document.getElementById('epcAssignInput').value = '';
        document.getElementById('epcAssignError').classList.add('hidden');
        document.getElementById('epcAssignSuccess').classList.add('hidden');
        modal.classList.remove('hidden');
        modal.style.display = 'block';
        loadEpcTags(itemId);
        setTimeout(() => document.getElementById('epcAssignInput')?.focus(), 100);
    };

    window.closeEpcAssignModal = function() {
        const modal = getModal();
        if (!modal) return;
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.getElementById('epcAssignForm')?.reset();
    };

    function postJson(url, payload) {
        payload[csrfName] = csrfHash;
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload),
        }).then(r => r.json());
    }

    function loadEpcTags(itemId) {
        fetch(epcTagsUrlBase + '/' + itemId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                epcModalTagMode = data.tag_mode || 'single';
                epcModalDefaultQty = data.qty_per_tag ?? 0;
                document.getElementById('epcAssignModeInfo').textContent =
                    (epcModalTagMode === 'multi' ? 'Multi-tag' : 'Single-tag') + ' · Balance: ' + data.balance;
                document.getElementById('epcAssignQty').value = epcModalDefaultQty;
                renderEpcTagList(data.tags || []);
            });
    }

    function renderEpcTagList(tags) {
        const list = document.getElementById('epcTagList');
        if (!tags.length) {
            list.classList.add('hidden');
            list.innerHTML = '';
            return;
        }
        list.classList.remove('hidden');
        list.innerHTML = '<p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Assigned tags</p>' +
            tags.map(t => `
                <div class="flex flex-wrap items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-mono text-gray-900 dark:text-white truncate">${t.epc_no}</p>
                    </div>
                    <div class="w-24">
                        <input type="number" step="0.001" min="0" value="${t.tag_quantity}"
                               class="epc-tag-qty block w-full rounded-lg border border-gray-300 dark:border-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs p-1.5"
                               data-tag-id="${t.tag_id}"/>
                    </div>
                    <button type="button" onclick="window.removeEpcTag(${t.tag_id})" class="text-red-500 dark:text-red-400 hover:text-red-700 p-1" title="Remove">
                        <span class="material-symbols-outlined text-base">delete</span>
                    </button>
                </div>
            `).join('');

        list.querySelectorAll('.epc-tag-qty').forEach(input => {
            let timer = null;
            input.addEventListener('change', () => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    const qty = parseFloat(input.value);
                    if (!qty || qty <= 0 || !epcUpdateTagUrl) return;
                    postJson(epcUpdateTagUrl, { tag_id: parseInt(input.dataset.tagId, 10), quantity: qty });
                }, 400);
            });
        });
    }

    window.removeEpcTag = function(tagId) {
        if (!confirm('Remove this UHF tag?')) return;
        postJson(epcRemoveTagUrl, { tag_id: tagId }).then(data => {
            if (data.success) {
                loadEpcTags(document.getElementById('epcAssignItemId').value);
                setTimeout(() => location.reload(), 800);
            }
        });
    };

    document.getElementById('epcAssignInput')?.addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/\s+/g, '');
    });

    document.getElementById('epcAssignForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const itemId = document.getElementById('epcAssignItemId').value;
        const epcNo = document.getElementById('epcAssignInput').value.trim().toUpperCase();
        const submitBtn = document.getElementById('epcAssignSubmitBtn');
        const errorDiv = document.getElementById('epcAssignError');
        const successDiv = document.getElementById('epcAssignSuccess');
        const payload = {
            id: itemId,
            epc_no: epcNo,
            quantity: parseFloat(document.getElementById('epcAssignQty').value) ?? epcModalDefaultQty ?? 0,
        };

        if (!epcNo || epcNo.length < 4) {
            errorDiv.textContent = 'Please enter a valid EPC tag (minimum 4 characters).';
            errorDiv.classList.remove('hidden');
            return;
        }

        errorDiv.classList.add('hidden');
        submitBtn.disabled = true;

        postJson(epcAssignPostUrl, payload)
            .then(data => {
                submitBtn.disabled = false;
                if (data.success) {
                    successDiv.textContent = data.message || 'Tag saved.';
                    successDiv.classList.remove('hidden');
                    document.getElementById('epcAssignInput').value = '';
                    loadEpcTags(itemId);
                    setTimeout(() => location.reload(), 1200);
                } else {
                    errorDiv.textContent = data.message || 'Failed to assign tag.';
                    errorDiv.classList.remove('hidden');
                }
            })
            .catch(() => {
                submitBtn.disabled = false;
                errorDiv.textContent = 'Network error.';
                errorDiv.classList.remove('hidden');
            });
    });

    getModal()?.addEventListener('click', function(e) {
        if (e.target === this) window.closeEpcAssignModal();
    });
})();
</script>
