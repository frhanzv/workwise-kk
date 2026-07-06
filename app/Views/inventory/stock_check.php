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
        <a href="<?= base_url('inventory/monitoring') ?>" class="text-sm text-primary hover:underline">← Back to Inventory Dashboard</a>
    </div>

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

            <div id="scan-panel-lock" class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-gray-900/40 dark:bg-black/50 backdrop-blur-[1px]">
                <p class="text-sm font-medium text-white px-4 py-2 rounded-lg bg-gray-900/80 dark:bg-gray-800/90 border border-gray-600">Click <strong>Start Stock Check</strong> first</p>
            </div>

            <div id="scan-panel" class="space-y-4 opacity-40 pointer-events-none">
                <div id="session-info" class="hidden p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-sm space-y-1 text-gray-900 dark:text-white">
                    <p><span class="text-gray-500 dark:text-gray-400">Item:</span> <strong id="si-name">—</strong></p>
                    <p><span class="text-gray-500 dark:text-gray-400">Expected balance:</span> <strong id="si-expected">0</strong></p>
                    <p><span class="text-gray-500 dark:text-gray-400">Counted so far:</span> <strong id="si-counted" class="text-primary">0</strong></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="<?= $labelClass ?>">Scan QR / Enter Code</label>
                        <input id="scan_qr" type="text" placeholder="WW|P|PRD-0001|LOT123" class="<?= $inputClass ?> font-mono"/>
                    </div>
                    <div class="space-y-1.5">
                        <label class="<?= $labelClass ?>">Scan UHF EPC</label>
                        <input id="scan_epc" type="text" placeholder="E200..." class="<?= $inputClass ?> font-mono"/>
                    </div>
                </div>

                <button type="button" onclick="submitScan()" id="btn-scan" disabled class="px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white rounded-lg text-sm font-bold hover:bg-gray-700 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Add Scan</button>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-3">
                    <label class="<?= $labelClass ?>">Manual Count Override (optional)</label>
                    <input id="counted_quantity" type="number" step="0.001" min="0" placeholder="Leave blank to use scan count" class="<?= $inputClass ?> md:max-w-xs"/>
                    <textarea id="notes" rows="2" placeholder="Notes..." class="<?= $inputClass ?>"></textarea>
                    <button type="button" onclick="completeCheck()" id="btn-complete" disabled class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-bold disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Complete Stock Check</button>
                </div>

                <div id="result-panel" class="hidden p-4 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 text-sm space-y-2 text-gray-900 dark:text-amber-100"></div>
            </div>
        </div>
    </div>
</div>

<script>
const products = <?= json_encode(array_map(fn($p) => ['id' => $p['id'], 'label' => $p['product_code'] . ' — ' . $p['product_name'], 'balance' => $p['quantity_on_hand'] ?? 0], $products)) ?>;
const materials = <?= json_encode(array_map(fn($m) => ['id' => $m['id'], 'label' => $m['material_code'] . ' — ' . $m['material_name'], 'balance' => $m['quantity_on_hand'] ?? 0], $materials)) ?>;
let sessionId = null;

function formatInventoryQty(n) {
    const v = Number(n) || 0;
    if (Math.abs(v - Math.round(v)) < 1e-9) return String(Math.round(v));
    return v.toFixed(3).replace(/\.?0+$/, '');
}

function unlockScanPanel() {
    document.getElementById('scan-panel-lock').classList.add('hidden');
    const panel = document.getElementById('scan-panel');
    panel.classList.remove('opacity-40', 'pointer-events-none');
}

function lockScanPanel() {
    document.getElementById('scan-panel-lock').classList.remove('hidden');
    const panel = document.getElementById('scan-panel');
    panel.classList.add('opacity-40', 'pointer-events-none');
}

function populateItems() {
    const type = document.getElementById('item_type').value;
    const sel = document.getElementById('item_id');
    const list = type === 'product' ? products : materials;
    sel.innerHTML = list.map(i => `<option value="${i.id}">${i.label} (Bal: ${formatInventoryQty(i.balance)})</option>`).join('');
}

document.getElementById('item_type').addEventListener('change', populateItems);
populateItems();

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
    const method = document.querySelector('input[name="scan_method"]:checked').value;
    const data = await post('<?= base_url('inventory/stock-check/start') ?>', {
        item_type: document.getElementById('item_type').value,
        item_id: document.getElementById('item_id').value,
        scan_method: method,
    });
    if (!data.success) { alert(data.message || 'Failed'); return; }

    sessionId = data.session_id;
    unlockScanPanel();
    document.getElementById('session-info').classList.remove('hidden');
    document.getElementById('si-name').textContent = data.item.name + ' (' + data.item.code + ')';
    document.getElementById('si-expected').textContent = formatInventoryQty(data.expected_balance);
    document.getElementById('si-counted').textContent = '0';
    document.getElementById('btn-scan').disabled = false;
    document.getElementById('btn-complete').disabled = false;
    document.getElementById('result-panel').classList.add('hidden');
    document.getElementById('scan_qr').focus();
}

async function submitScan() {
    if (!sessionId) return;
    const qr = document.getElementById('scan_qr').value.trim();
    const epc = document.getElementById('scan_epc').value.trim();
    if (!qr && !epc) { alert('Enter a QR code or UHF EPC.'); return; }

    const data = await post('<?= base_url('inventory/stock-check/scan') ?>', {
        session_id: sessionId,
        qr_code: qr,
        epc: epc,
    });
    if (!data.success) { alert(data.message || 'Scan failed'); return; }
    document.getElementById('si-counted').textContent = formatInventoryQty(data.counted_balance);
    document.getElementById('scan_qr').value = '';
    document.getElementById('scan_epc').value = '';
    document.getElementById('scan_qr').focus();
}

async function completeCheck() {
    if (!sessionId) return;
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
    if (data.stock_out_list && data.stock_out_list.length) {
        html += '<p class="font-bold mt-2">Recent Stock Out (possible variance cause):</p><ul class="list-disc pl-5 space-y-1">';
        data.stock_out_list.forEach(t => {
            html += `<li>${t.datetime} — ${t.transaction_label} ${formatInventoryQty(t.quantity)} (bal ${formatInventoryQty(t.balance_after)})</li>`;
        });
        html += '</ul>';
    }
    panel.innerHTML = html;

    sessionId = null;
    document.getElementById('btn-scan').disabled = true;
    document.getElementById('btn-complete').disabled = true;
    lockScanPanel();
}
</script>

<?= $this->include('templates/footer') ?>
