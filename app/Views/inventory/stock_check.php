<?= $this->include('templates/header') ?>

<?php
$inputClass = 'block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-primary focus:ring-primary sm:text-sm p-2.5';
$labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
?>

<div class="flex flex-col gap-6 px-2 sm:px-4 py-2 sm:py-4">
    <div class="flex flex-wrap items-center justify-between gap-4 mt-4 md:mt-2">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Stock Check</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Count physical stock via UHF tag or product batch QR code.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= base_url('inventory/stock-check/discrepancies-page') ?>" class="px-4 py-2 rounded-lg text-sm font-bold border border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-200 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors">
                Discrepancy Records
            </a>
            <a href="<?= base_url('inventory/monitoring') ?>" class="text-sm text-primary hover:underline">← Back to Inventory Monitoring</a>
        </div>
    </div>

    <div class="p-4 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 text-sm text-blue-900 dark:text-blue-100 space-y-2">
        <p><strong>Lookup desk scanning (UHF mode):</strong></p>
        <ul class="list-disc pl-5 space-y-1 text-blue-800 dark:text-blue-200">
            <li>Set the desk antenna to <strong>LOOKUP</strong> or <strong>STOCK CHECK</strong> in Zones — not IN/OUT.</li>
            <li>After you start a check, scans appear here automatically (same as Search Stock / Tag + Stock In).</li>
            <li>On complete, any registered tags <strong>not scanned</strong> are recorded as discrepancies (inventory balance is <strong>not</strong> changed).</li>
        </ul>
    </div>

    <div id="listen-banner" class="hidden flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl border border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20">
        <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
            <div>
                <p class="text-sm font-bold text-indigo-900 dark:text-indigo-100">Listening for lookup-desk RFID scans</p>
                <p class="text-xs text-indigo-700 dark:text-indigo-300">Each tag scan is added to the count automatically</p>
            </div>
        </div>
        <span id="listen-time" class="text-xs text-indigo-600 dark:text-indigo-400 tabular-nums"></span>
    </div>

    <div id="scan-alert" class="hidden p-3 rounded-lg text-sm border"></div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Setup -->
        <div class="xl:col-span-1 bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">1. Select Item</h2>
            <div class="space-y-1.5">
                <label class="<?= $labelClass ?>">Item Type</label>
                <select id="item_type" class="<?= $inputClass ?>">
                    <option value="product">Product</option>
                    <option value="raw_material">Raw Material</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="<?= $labelClass ?>">Item</label>
                <select id="item_id" class="<?= $inputClass ?>"></select>
            </div>
            <div class="space-y-1.5">
                <label class="<?= $labelClass ?>">Scan Method</label>
                <div class="flex flex-wrap gap-4 pt-1">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="radio" name="scan_method" value="qr" checked class="text-primary focus:ring-primary"> QR Code (batch)
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="radio" name="scan_method" value="uhf" class="text-primary focus:ring-primary"> UHF Tag
                    </label>
                </div>
            </div>
            <button type="button" onclick="startCheck()" class="w-full py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors">Start Stock Check</button>
        </div>

        <!-- Scanning -->
        <div class="xl:col-span-2 bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4 relative">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">2. Scan &amp; Count</h2>

            <div id="result-panel" class="hidden p-4 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 text-sm space-y-2 text-gray-900 dark:text-amber-100"></div>

            <div class="relative space-y-4">
            <div id="scan-panel-lock" class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-gray-900/40 dark:bg-black/50 backdrop-blur-[1px]">
                <p id="scan-panel-lock-msg" class="text-sm font-medium text-white px-4 py-2 rounded-lg bg-gray-900/80 dark:bg-gray-800/90 border border-gray-600">Click <strong>Start Stock Check</strong> first</p>
            </div>

            <div id="scan-panel" class="space-y-4 opacity-40 pointer-events-none">
                <div id="session-info" class="hidden p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-sm space-y-1 text-gray-900 dark:text-white">
                    <p><span class="text-gray-500 dark:text-gray-400">Item:</span> <strong id="si-name">—</strong></p>
                    <p><span class="text-gray-500 dark:text-gray-400">Expected balance:</span> <strong id="si-expected">0</strong></p>
                    <p><span class="text-gray-500 dark:text-gray-400">Counted so far:</span> <strong id="si-counted" class="text-primary">0</strong></p>
                    <p id="si-tag-progress" class="hidden"><span class="text-gray-500 dark:text-gray-400">Tags scanned:</span> <strong id="si-tag-count">0 / 0</strong></p>
                </div>

                <div id="tag-status-panel" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-3 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/10">
                        <p class="text-xs font-bold uppercase tracking-wider text-green-700 dark:text-green-400 mb-2">Scanned tags</p>
                        <ul id="scanned-tags-list" class="text-xs font-mono space-y-1 text-green-900 dark:text-green-100 max-h-32 overflow-y-auto"></ul>
                        <p id="scanned-tags-empty" class="text-xs text-green-700/70 dark:text-green-400/70">None yet</p>
                    </div>
                    <div class="p-3 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/10">
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-2">Not scanned yet</p>
                        <ul id="missing-tags-list" class="text-xs font-mono space-y-1 text-amber-900 dark:text-amber-100 max-h-32 overflow-y-auto"></ul>
                        <p id="missing-tags-empty" class="text-xs text-amber-700/70 dark:text-amber-400/70 hidden">All tags scanned</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="<?= $labelClass ?>">Scan QR / Enter Code</label>
                        <input id="scan_qr" type="text" placeholder="WK|P|PRD-0001|LOT123" class="<?= $inputClass ?> font-mono"/>
                    </div>
                    <div class="space-y-1.5">
                        <label class="<?= $labelClass ?>">Scan UHF EPC</label>
                        <input id="scan_epc" type="text" placeholder="E200..." class="<?= $inputClass ?> font-mono"/>
                    </div>
                </div>

                <button type="button" onclick="submitScan()" id="btn-scan" disabled class="px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white rounded-lg text-sm font-bold hover:bg-gray-700 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Add Scan</button>

                <div id="scans-log" class="hidden">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Scan log</p>
                    <ul id="scans-log-list" class="text-xs font-mono space-y-1 max-h-24 overflow-y-auto text-gray-700 dark:text-gray-300"></ul>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-3">
                    <label class="<?= $labelClass ?>">Manual Count Override (optional)</label>
                    <input id="counted_quantity" type="number" step="0.001" min="0" placeholder="Leave blank to use scan count" class="<?= $inputClass ?> md:max-w-xs"/>
                    <textarea id="notes" rows="2" placeholder="Notes..." class="<?= $inputClass ?>"></textarea>
                    <button type="button" onclick="completeCheck()" id="btn-complete" disabled class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-bold disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Complete Stock Check</button>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<div id="complete-confirm-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/50 dark:bg-black/60">
    <div class="w-full max-w-md rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-background-dark shadow-xl p-6 space-y-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Complete stock check?</h3>
            <p id="complete-confirm-summary" class="mt-2 text-sm text-gray-600 dark:text-gray-300"></p>
        </div>
        <ul id="complete-confirm-tags" class="text-xs font-mono space-y-1 max-h-40 overflow-y-auto p-3 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 text-amber-900 dark:text-amber-200"></ul>
        <p class="text-xs text-gray-500 dark:text-gray-400">These will be saved to discrepancy records. Stock balance will not change.</p>
        <div class="flex flex-wrap justify-end gap-3 pt-1">
            <button type="button" id="complete-confirm-cancel" class="px-4 py-2 rounded-lg text-sm font-bold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Cancel</button>
            <button type="button" id="complete-confirm-ok" class="px-4 py-2 rounded-lg text-sm font-bold bg-green-600 hover:bg-green-700 text-white transition-colors">Complete check</button>
        </div>
    </div>
</div>

<script>
const products = <?= json_encode(array_map(fn($p) => ['id' => $p['id'], 'label' => $p['product_code'] . ' — ' . $p['product_name'], 'balance' => $p['quantity_on_hand'] ?? 0], $products)) ?>;
const materials = <?= json_encode(array_map(fn($m) => ['id' => $m['id'], 'label' => $m['material_code'] . ' — ' . $m['material_name'], 'balance' => $m['quantity_on_hand'] ?? 0], $materials)) ?>;
const scansUrl = <?= json_encode(base_url('inventory/search-stock/scans')) ?>;

let sessionId = null;
let scanMethod = 'qr';
let scanBusy = false;
let lastPollTs = Date.now() / 1000;
let pollTimer = null;
let currentMissingTags = [];
const seenScanIds = new Set();

function formatInventoryQty(n) {
    const v = Number(n) || 0;
    if (Math.abs(v - Math.round(v)) < 1e-9) return String(Math.round(v));
    return v.toFixed(3).replace(/\.?0+$/, '');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function showScanAlert(message, type) {
    const el = document.getElementById('scan-alert');
    el.classList.remove('hidden', 'bg-green-100', 'dark:bg-green-900/20', 'border-green-500', 'text-green-700', 'dark:text-green-400', 'bg-red-100', 'dark:bg-red-900/20', 'border-red-500', 'text-red-700', 'dark:text-red-400');
    if (type === 'success') {
        el.classList.add('bg-green-100', 'dark:bg-green-900/20', 'border-green-500', 'text-green-700', 'dark:text-green-400');
    } else {
        el.classList.add('bg-red-100', 'dark:bg-red-900/20', 'border-red-500', 'text-red-700', 'dark:text-red-400');
    }
    el.textContent = message;
}

function hideScanAlert() {
    document.getElementById('scan-alert').classList.add('hidden');
}

function unlockScanPanel() {
    document.getElementById('scan-panel-lock').classList.add('hidden');
    const panel = document.getElementById('scan-panel');
    panel.classList.remove('opacity-40', 'pointer-events-none');
    document.getElementById('scan-panel-lock-msg').innerHTML = 'Click <strong>Start Stock Check</strong> first';
    document.getElementById('result-panel').classList.add('hidden');
}

function lockScanPanel(completed = false) {
    const lock = document.getElementById('scan-panel-lock');
    const panel = document.getElementById('scan-panel');
    panel.classList.add('opacity-40', 'pointer-events-none');
    if (completed) {
        lock.classList.add('hidden');
    } else {
        lock.classList.remove('hidden');
        document.getElementById('scan-panel-lock-msg').innerHTML = 'Click <strong>Start Stock Check</strong> first';
    }
}

function populateItems() {
    const type = document.getElementById('item_type').value;
    const sel = document.getElementById('item_id');
    const list = type === 'product' ? products : materials;
    sel.innerHTML = list.map(i => `<option value="${i.id}">${i.label} (Bal: ${formatInventoryQty(i.balance)})</option>`).join('');
}

document.getElementById('item_type').addEventListener('change', populateItems);
populateItems();

function startListenPoll() {
    stopListenPoll();
    const tick = () => {
        document.getElementById('listen-time').textContent = 'Updated ' + new Date().toLocaleTimeString();
        pollLookupScans();
    };
    pollTimer = setInterval(tick, 1200);
    tick();
}

function stopListenPoll() {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
    document.getElementById('listen-banner').classList.add('hidden');
}

async function pollLookupScans() {
    if (!sessionId || scanMethod !== 'uhf' || scanBusy) return;
    try {
        const res = await fetch(scansUrl + '?since=' + encodeURIComponent(lastPollTs), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store',
        });
        const data = await res.json();
        if (!data.success || !data.scans?.length) return;

        for (const scan of data.scans) {
            if (seenScanIds.has(scan.id)) continue;
            seenScanIds.add(scan.id);
            lastPollTs = Math.max(lastPollTs, scan.ts || lastPollTs);
            const epc = (scan.epc || '').trim();
            if (!epc) continue;
            document.getElementById('scan_epc').value = epc;
            await submitScan(true);
        }
    } catch (e) {
        // retry on next poll
    }
}

function paintTagStatus(data) {
    const isUhf = scanMethod === 'uhf' && (data.expected_tag_count || 0) > 0;
    document.getElementById('tag-status-panel').classList.toggle('hidden', !isUhf);
    document.getElementById('si-tag-progress').classList.toggle('hidden', !isUhf);

    if (!isUhf) return;

    const scanned = data.scanned_tag_count ?? 0;
    const expected = data.expected_tag_count ?? 0;
    document.getElementById('si-tag-count').textContent = scanned + ' / ' + expected;

    const scannedList = document.getElementById('scanned-tags-list');
    const missingList = document.getElementById('missing-tags-list');
    const scannedTags = data.scanned_tags || [];
    const missingTags = data.missing_tags || [];
    currentMissingTags = missingTags;

    scannedList.innerHTML = scannedTags.map(t => {
        const label = t.label ? escapeHtml(t.label) + ' — ' : '';
        return `<li class="truncate">${label}${escapeHtml(t.epc_no)} <span class="text-green-600 dark:text-green-400">(${formatInventoryQty(t.quantity)})</span></li>`;
    }).join('');
    document.getElementById('scanned-tags-empty').classList.toggle('hidden', scannedTags.length > 0);

    missingList.innerHTML = missingTags.map(t => {
        const label = t.label ? escapeHtml(t.label) + ' — ' : '';
        return `<li class="truncate">${label}${escapeHtml(t.epc_no)} <span class="text-amber-600 dark:text-amber-400">(${formatInventoryQty(t.quantity)})</span></li>`;
    }).join('');
    document.getElementById('missing-tags-empty').classList.toggle('hidden', missingTags.length > 0);
}

function paintScansLog(scans) {
    const log = document.getElementById('scans-log');
    const list = document.getElementById('scans-log-list');
    if (!scans?.length) {
        log.classList.add('hidden');
        return;
    }
    log.classList.remove('hidden');
    list.innerHTML = scans.map(s => {
        const ref = escapeHtml(s.scan_reference);
        const qty = formatInventoryQty(s.quantity);
        return `<li>+${qty} — ${ref} <span class="text-gray-400">(${escapeHtml(s.scan_method)})</span></li>`;
    }).join('');
}

async function post(url, data) {
    const payload = { ...data, '<?= csrf_token() ?>': '<?= csrf_hash() ?>' };
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(payload),
    });
    return res.json();
}

async function startCheck() {
    hideScanAlert();
    scanMethod = document.querySelector('input[name="scan_method"]:checked').value;
    const data = await post('<?= base_url('inventory/stock-check/start') ?>', {
        item_type: document.getElementById('item_type').value,
        item_id: document.getElementById('item_id').value,
        scan_method: scanMethod,
    });
    if (!data.success) { alert(data.message || 'Failed'); return; }

    sessionId = data.session_id;
    seenScanIds.clear();
    lastPollTs = Date.now() / 1000;

    unlockScanPanel();
    document.getElementById('session-info').classList.remove('hidden');
    document.getElementById('si-name').textContent = data.item.name + ' (' + data.item.code + ')';
    document.getElementById('si-expected').textContent = formatInventoryQty(data.expected_balance);
    document.getElementById('si-counted').textContent = '0';
    document.getElementById('btn-scan').disabled = false;
    document.getElementById('btn-complete').disabled = false;
    document.getElementById('result-panel').classList.add('hidden');
    document.getElementById('counted_quantity').value = '';
    document.getElementById('notes').value = '';
    document.getElementById('scan_qr').value = '';
    document.getElementById('scan_epc').value = '';

    paintTagStatus(data);
    paintScansLog([]);

    if (scanMethod === 'uhf') {
        document.getElementById('listen-banner').classList.remove('hidden');
        startListenPoll();
        document.getElementById('scan_epc').focus();
    } else {
        stopListenPoll();
        document.getElementById('scan_qr').focus();
    }
}

async function submitScan(fromPoll = false) {
    if (!sessionId || scanBusy) return;
    const qr = document.getElementById('scan_qr').value.trim();
    const epc = document.getElementById('scan_epc').value.trim();
    if (!qr && !epc) {
        if (!fromPoll) alert('Enter a QR code or UHF EPC.');
        return;
    }

    scanBusy = true;
    const data = await post('<?= base_url('inventory/stock-check/scan') ?>', {
        session_id: sessionId,
        qr_code: qr,
        epc: epc,
    });
    scanBusy = false;

    if (!data.success) {
        if (fromPoll) {
            showScanAlert(data.message || 'Scan rejected', 'error');
        } else {
            alert(data.message || 'Scan failed');
        }
        return;
    }

    hideScanAlert();
    document.getElementById('si-counted').textContent = formatInventoryQty(data.counted_balance);
    document.getElementById('scan_qr').value = '';
    document.getElementById('scan_epc').value = '';
    paintTagStatus(data);
    paintScansLog(data.scans);

    if (!fromPoll) {
        if (scanMethod === 'uhf') {
            document.getElementById('scan_epc').focus();
        } else {
            document.getElementById('scan_qr').focus();
        }
    }
}

function tagsNotScanned() {
    return currentMissingTags;
}

function showCompleteConfirmModal(tags) {
    return new Promise(resolve => {
        const modal = document.getElementById('complete-confirm-modal');
        const count = tags.length;
        const noun = count === 1 ? 'tag was' : 'tags were';
        document.getElementById('complete-confirm-summary').textContent =
            `${count} ${noun} not scanned. Record as discrepancy and complete this check?`;
        document.getElementById('complete-confirm-tags').innerHTML = tags.map(t => {
            const label = t.label ? escapeHtml(t.label) + ' — ' : '';
            const qty = formatInventoryQty(t.quantity ?? t.current_quantity);
            return `<li>${label}${escapeHtml(t.epc_no)} (${qty})</li>`;
        }).join('');

        const onCancel = () => {
            cleanup();
            resolve(false);
        };
        const onOk = () => {
            cleanup();
            resolve(true);
        };
        const cleanup = () => {
            modal.classList.add('hidden');
            document.getElementById('complete-confirm-cancel').removeEventListener('click', onCancel);
            document.getElementById('complete-confirm-ok').removeEventListener('click', onOk);
        };

        document.getElementById('complete-confirm-cancel').addEventListener('click', onCancel);
        document.getElementById('complete-confirm-ok').addEventListener('click', onOk);
        modal.classList.remove('hidden');
    });
}

async function completeCheck() {
    if (!sessionId) return;

    const pending = scanMethod === 'uhf' ? tagsNotScanned() : [];
    if (pending.length > 0) {
        const confirmed = await showCompleteConfirmModal(pending);
        if (!confirmed) return;
    }

    stopListenPoll();

    const data = await post('<?= base_url('inventory/stock-check/complete') ?>', {
        session_id: sessionId,
        counted_quantity: document.getElementById('counted_quantity').value,
        notes: document.getElementById('notes').value,
    });
    if (!data.success) { alert(data.message || 'Failed'); return; }

    const panel = document.getElementById('result-panel');
    panel.classList.remove('hidden');
    let html = `<p><strong>Expected:</strong> ${formatInventoryQty(data.expected_balance)}</p>`;
    html += `<p><strong>Counted:</strong> ${formatInventoryQty(data.counted_balance)}</p>`;
    html += `<p><strong>Variance:</strong> <span class="${data.variance < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'} font-bold">${formatInventoryQty(data.variance)}</span></p>`;
    html += `<p><strong>Balance after:</strong> ${formatInventoryQty(data.balance_after)}</p>`;

    if (data.expected_tag_count > 0) {
        html += `<p class="mt-2"><strong>Tags scanned:</strong> ${data.scanned_tag_count} / ${data.expected_tag_count}</p>`;
        const discrepancies = data.discrepancies?.length ? data.discrepancies : data.missing_tags;
        if (discrepancies?.length) {
            html += '<div class="mt-3 p-3 rounded-lg border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20">';
            html += '<p class="font-bold text-amber-800 dark:text-amber-300">Recorded as discrepancy (not scanned):</p>';
            html += '<ul class="list-disc pl-5 mt-2 space-y-1 font-mono text-xs text-amber-900 dark:text-amber-200">';
            discrepancies.forEach(t => {
                const label = t.label ? escapeHtml(t.label) + ' — ' : '';
                html += `<li>${label}${escapeHtml(t.epc_no)} (${formatInventoryQty(t.quantity)})</li>`;
            });
            html += '</ul></div>';
        }
        if (data.scanned_tags?.length) {
            html += '<p class="font-bold mt-3">Scanned tags:</p><ul class="list-disc pl-5 space-y-1 font-mono text-xs">';
            data.scanned_tags.forEach(t => {
                const label = t.label ? escapeHtml(t.label) + ' — ' : '';
                html += `<li>${label}${escapeHtml(t.epc_no)} (${formatInventoryQty(t.quantity)})</li>`;
            });
            html += '</ul>';
        }
    } else if (data.discrepancies?.length) {
        html += '<div class="mt-3 p-3 rounded-lg border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20">';
        html += '<p class="font-bold text-amber-800 dark:text-amber-300">Recorded as discrepancy:</p>';
        html += '<ul class="list-disc pl-5 mt-2 space-y-1 text-xs">';
        data.discrepancies.forEach(t => {
            html += `<li>${escapeHtml(t.label || t.epc_no)} (${formatInventoryQty(t.quantity)})</li>`;
        });
        html += '</ul></div>';
    }
    panel.innerHTML = html;
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    sessionId = null;
    document.getElementById('btn-scan').disabled = true;
    document.getElementById('btn-complete').disabled = true;
    lockScanPanel(true);

}

document.getElementById('scan_epc').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); submitScan(); }
});
document.getElementById('scan_qr').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); submitScan(); }
});

window.addEventListener('beforeunload', stopListenPoll);

</script>

<?= $this->include('templates/footer') ?>
