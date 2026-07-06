<?php
/** @var string $itemType product|raw_material */
/** @var int|null $itemId */
/** @var array $existingTags */
/** @var string|null $assignTagUrl */
/** @var string|null $removeTagUrl */
/** @var callable $val */
/** @var string $inputClass */

$itemId        = $itemId ?? null;
$existingTags  = $existingTags ?? [];
$isEdit        = $itemId !== null;
$panelId       = 'uhf-tags-panel';
$csrfName      = csrf_token();
$csrfHash      = csrf_hash();
$defaultQty    = (float) ($val('qty_per_tag', '0') ?: 0);
if ($defaultQty < 0) {
    $defaultQty = 0;
}
$tagRowClass   = 'uhf-tag-row flex flex-wrap items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600';
$tagLabelClass = 'text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide';
$tagEpcClass   = 'text-sm font-mono text-gray-900 dark:text-white truncate';
$tagQtyClass   = 'block w-full rounded-lg border border-gray-300 dark:border-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white sm:text-sm p-2 focus:border-primary focus:ring-primary';
?>
<div id="<?= $panelId ?>" class="space-y-4" data-edit="<?= $isEdit ? '1' : '0' ?>" data-item-id="<?= $itemId ? (int) $itemId : '' ?>">
    <input type="hidden" name="pending_tags" id="pending-tags-json" value=""/>

    <?php if ($isEdit): ?>
        <p class="text-xs text-gray-500 dark:text-gray-400">
            <strong>Registered qty</strong> = how much stock this EPC can hold (zone IN restores up to this). Does <strong>not</strong> stock in — use <strong>Stock In / Out on the View page</strong> or zone RFID.
        </p>
    <?php endif; ?>

    <div id="uhf-tags-list" class="space-y-2">
        <?php if ($isEdit && !empty($existingTags)): ?>
            <?php foreach ($existingTags as $tag): ?>
                <?php
                $registered = (float) ($tag['tag_registered_quantity'] ?? $tag['tag_quantity'] ?? 0);
                $current    = (float) ($tag['tag_quantity'] ?? 0);
                ?>
                <div class="<?= $tagRowClass ?>"
                     data-tag-id="<?= (int) $tag['tag_id'] ?>" data-epc="<?= esc($tag['epc_no']) ?>">
                    <div class="flex-1 min-w-[140px]">
                        <p class="<?= $tagLabelClass ?>">EPC</p>
                        <p class="<?= $tagEpcClass ?>"><?= esc($tag['epc_no']) ?></p>
                    </div>
                    <div class="w-28">
                        <label class="<?= $tagLabelClass ?>">Registered qty</label>
                        <input type="number" step="0.001" min="0" name="tag_registered_qty[<?= (int) $tag['tag_id'] ?>]"
                               class="<?= $tagQtyClass ?>"
                               value="<?= esc($registered) ?>"/>
                    </div>
                    <div class="w-28">
                        <label class="<?= $tagLabelClass ?>">Current stock</label>
                        <div class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-900/50 text-gray-700 dark:text-gray-300 sm:text-sm p-2 font-semibold tabular-nums">
                            <?= format_inventory_qty($current) ?>
                        </div>
                    </div>
                    <button type="button" class="uhf-tag-remove mt-5 p-1.5 text-red-500 dark:text-red-400 hover:text-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30" title="Remove tag">
                        <span class="material-symbols-outlined text-base">delete</span>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <p id="uhf-tags-empty" class="text-sm text-gray-500 dark:text-gray-400 <?= ($isEdit && !empty($existingTags)) ? 'hidden' : '' ?>">
        No UHF tags assigned yet. Add one below.
    </p>

    <div class="p-4 rounded-lg border border-dashed border-gray-300 dark:border-gray-500 bg-gray-50/50 dark:bg-gray-800/50 space-y-3">
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Add UHF tag</p>
        <div class="grid grid-cols-1 sm:grid-cols-[1fr_120px_auto] gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">EPC Tag Number</label>
                <input type="text" id="uhf-new-epc" placeholder="e.g. E200471472C06426C2510112"
                       class="<?= $inputClass ?> font-mono uppercase" autocomplete="off"/>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Registered qty</label>
                <input type="number" step="0.001" min="0" id="uhf-new-qty" value="<?= esc($defaultQty) ?>"
                       class="<?= $inputClass ?>"/>
            </div>
            <button type="button" id="uhf-add-tag-btn"
                    class="h-[42px] px-4 rounded-lg bg-primary text-white text-sm font-medium hover:bg-primary/90 flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-base">add</span> Add
            </button>
        </div>
        <p id="uhf-tags-error" class="hidden text-sm text-red-600 dark:text-red-400"></p>
    </div>
</div>

<script>
(function() {
    const panel = document.getElementById('<?= $panelId ?>');
    if (!panel || panel.dataset.initialized === '1') return;
    panel.dataset.initialized = '1';

    const isEdit = panel.dataset.edit === '1';
    const itemId = panel.dataset.itemId;
    const listEl = document.getElementById('uhf-tags-list');
    const emptyEl = document.getElementById('uhf-tags-empty');
    const pendingInput = document.getElementById('pending-tags-json');
    const epcInput = document.getElementById('uhf-new-epc');
    const qtyInput = document.getElementById('uhf-new-qty');
    const errorEl = document.getElementById('uhf-tags-error');
    const tagModeSelect = document.getElementById('tag-mode');
    const csrfName = <?= json_encode($csrfName) ?>;
    const csrfHash = <?= json_encode($csrfHash) ?>;
    const assignUrl = <?= json_encode($assignTagUrl ?? '') ?>;
    const removeUrl = <?= json_encode($removeTagUrl ?? '') ?>;

    const tagRowClass = <?= json_encode($tagRowClass) ?>;
    const tagLabelClass = <?= json_encode($tagLabelClass) ?>;
    const tagEpcClass = <?= json_encode($tagEpcClass) ?>;
    const tagQtyClass = <?= json_encode($tagQtyClass) ?>;

    let pendingTags = [];

    function tagMode() {
        return tagModeSelect?.value || 'single';
    }

    function currentTagCount() {
        return listEl.querySelectorAll('.uhf-tag-row').length;
    }

    function syncEmptyState() {
        const count = currentTagCount();
        emptyEl?.classList.toggle('hidden', count > 0);
    }

    function syncPendingInput() {
        if (!isEdit) {
            pendingInput.value = JSON.stringify(pendingTags);
        }
    }

    function showError(msg) {
        if (!errorEl) return;
        errorEl.textContent = msg;
        errorEl.classList.remove('hidden');
    }

    function clearError() {
        errorEl?.classList.add('hidden');
    }

    function normalizeEpc(value) {
        return (value || '').trim().toUpperCase().replace(/\s+/g, '');
    }

    function isDuplicateEpc(epc) {
        const rows = listEl.querySelectorAll('.uhf-tag-row');
        for (const row of rows) {
            if (row.dataset.epc === epc) return true;
        }
        return pendingTags.some(t => t.epc_no === epc);
    }

    function buildPendingRow(epc, qty) {
        const row = document.createElement('div');
        row.className = tagRowClass;
        row.dataset.epc = epc;
        row.innerHTML = `
            <div class="flex-1 min-w-[140px]">
                <p class="${tagLabelClass}">EPC</p>
                <p class="${tagEpcClass}"></p>
            </div>
            <div class="w-28">
                <label class="${tagLabelClass}">Registered qty</label>
                <input type="number" step="0.001" min="0" class="${tagQtyClass} uhf-pending-qty-input" value="${qty}"/>
            </div>
            <button type="button" class="uhf-tag-remove mt-5 p-1.5 text-red-500 dark:text-red-400 hover:text-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30" title="Remove">
                <span class="material-symbols-outlined text-base">delete</span>
            </button>
        `;
        row.querySelector('p.font-mono').textContent = epc;
        const qtyField = row.querySelector('.uhf-pending-qty-input');
        qtyField?.addEventListener('change', () => {
            const newQty = parseFloat(qtyField.value);
            if (isNaN(newQty) || newQty < 0) return;
            const idx = pendingTags.findIndex(t => t.epc_no === epc);
            if (idx >= 0) pendingTags[idx].quantity = newQty;
            syncPendingInput();
        });
        row.querySelector('.uhf-tag-remove').addEventListener('click', () => {
            pendingTags = pendingTags.filter(t => t.epc_no !== epc);
            row.remove();
            syncPendingInput();
            syncEmptyState();
        });
        return row;
    }

    function postJson(url, payload) {
        payload[csrfName] = csrfHash;
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload),
        }).then(r => r.json());
    }

    function addTagRowFromServer(tag) {
        const registered = tag.tag_registered_quantity ?? tag.tag_quantity ?? 0;
        const current = tag.tag_quantity ?? 0;
        const row = document.createElement('div');
        row.className = tagRowClass;
        row.dataset.tagId = tag.tag_id;
        row.dataset.epc = tag.epc_no;
        row.innerHTML = `
            <div class="flex-1 min-w-[140px]">
                <p class="${tagLabelClass}">EPC</p>
                <p class="${tagEpcClass}"></p>
            </div>
            <div class="w-28">
                <label class="${tagLabelClass}">Registered qty</label>
                <input type="number" step="0.001" min="0" name="tag_registered_qty[${tag.tag_id}]" class="${tagQtyClass}" value="${registered}"/>
            </div>
            <div class="w-28">
                <label class="${tagLabelClass}">Current stock</label>
                <div class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-900/50 text-gray-700 dark:text-gray-300 sm:text-sm p-2 font-semibold tabular-nums">${current}</div>
            </div>
            <button type="button" class="uhf-tag-remove mt-5 p-1.5 text-red-500 dark:text-red-400 hover:text-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30" title="Remove tag">
                <span class="material-symbols-outlined text-base">delete</span>
            </button>
        `;
        row.querySelector('p.font-mono').textContent = tag.epc_no;
        row.querySelector('.uhf-tag-remove')?.addEventListener('click', () => {
            if (!confirm('Remove this UHF tag?')) return;
            postJson(removeUrl, { tag_id: parseInt(tag.tag_id, 10) }).then(data => {
                if (data.success) {
                    row.remove();
                    syncEmptyState();
                    clearError();
                } else {
                    showError(data.message || 'Could not remove tag.');
                }
            });
        });
        listEl.appendChild(row);
        syncEmptyState();
    }

    listEl.querySelectorAll('.uhf-tag-row[data-tag-id] .uhf-tag-remove').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.uhf-tag-row');
            const tagId = row?.dataset.tagId;
            if (!tagId || !confirm('Remove this UHF tag?')) return;
            postJson(removeUrl, { tag_id: parseInt(tagId, 10) }).then(data => {
                if (data.success) {
                    row.remove();
                    syncEmptyState();
                    clearError();
                } else {
                    showError(data.message || 'Could not remove tag.');
                }
            });
        });
    });

    document.getElementById('uhf-add-tag-btn')?.addEventListener('click', () => {
        clearError();
        const epc = normalizeEpc(epcInput.value);
        const qty = parseFloat(qtyInput.value);
        const defaultFromForm = parseFloat(document.querySelector('[name="qty_per_tag"]')?.value);
        const resolvedQty = !isNaN(qty) ? qty : (!isNaN(defaultFromForm) ? defaultFromForm : 0);

        if (!epc || epc.length < 4) {
            showError('Enter a valid EPC tag (minimum 4 characters).');
            return;
        }
        if (isNaN(resolvedQty) || resolvedQty < 0) {
            showError('Quantity cannot be negative.');
            return;
        }
        if (isDuplicateEpc(epc)) {
            showError('This EPC is already in the list.');
            return;
        }
        if (tagMode() === 'single' && currentTagCount() >= 1) {
            showError('Single-tag mode allows only one UHF tag. Switch to multi-tag mode to add more.');
            return;
        }

        if (isEdit) {
            postJson(assignUrl, { id: itemId, epc_no: epc, quantity: resolvedQty }).then(data => {
                if (data.success && data.tag) {
                    addTagRowFromServer(data.tag);
                    epcInput.value = '';
                    clearError();
                } else {
                    showError(data.message || 'Could not assign tag.');
                }
            }).catch(() => showError('Network error while assigning tag.'));
        } else {
            pendingTags.push({ epc_no: epc, quantity: resolvedQty });
            listEl.appendChild(buildPendingRow(epc, resolvedQty));
            syncPendingInput();
            syncEmptyState();
            epcInput.value = '';
        }
    });

    epcInput?.addEventListener('input', function() {
        this.value = normalizeEpc(this.value);
    });

    document.querySelector('[name="qty_per_tag"]')?.addEventListener('input', function() {
        const v = parseFloat(this.value);
        if (!isNaN(v) && v >= 0 && qtyInput && document.activeElement !== qtyInput) {
            qtyInput.value = v;
        }
    });

    syncEmptyState();
    syncPendingInput();
})();
</script>
