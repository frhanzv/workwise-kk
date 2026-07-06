<?= $this->include('templates/header') ?>

<?php
$inputClass = 'block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-primary focus:ring-primary sm:text-sm p-2.5';
$labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1';
?>

<div class="flex flex-col gap-6 px-2 sm:px-4 py-2 sm:py-4 w-full max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-4 mt-4 md:mt-2">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Search Stock</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Scan a UHF tag or batch QR code to view item details and stock.</p>
        </div>
        <a href="<?= base_url('inventory/monitoring') ?>" class="text-sm text-primary hover:underline">← Inventory Monitoring</a>
    </div>

    <div class="p-4 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 text-sm text-blue-900 dark:text-blue-100 space-y-2">
        <p><strong>How scanning works (3 different readers / modes):</strong></p>
        <ul class="list-disc pl-5 space-y-1 text-blue-800 dark:text-blue-200">
            <li><strong>Lookup desk reader</strong> (zone antenna set to <strong>LOOKUP</strong>) — scan here while this page is open. Shows details only. <em>Never stocks in or out.</em></li>
            <li><strong>Tag + Stock In page</strong> — scan at lookup desk or type EPC to assign tag and do first stock in.</li>
            <li><strong>Warehouse IN / OUT readers</strong> — normal zone gates. Restore stock on IN, deduct on OUT (after first stock in is done).</li>
        </ul>
        <p class="text-xs text-blue-700 dark:text-blue-300">Configure a lookup desk in Zones → set antenna function to <strong>LOOKUP</strong>. Keep warehouse gates as IN / OUT.</p>
    </div>

    <div id="listen-banner" class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl border border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20">
        <div class="flex items-center gap-3">
            <span id="listen-dot" class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
            <div>
                <p class="text-sm font-bold text-indigo-900 dark:text-indigo-100">Listening for lookup-desk RFID scans</p>
                <p class="text-xs text-indigo-700 dark:text-indigo-300">Scan at a reader configured as LOOKUP — results appear below automatically.</p>
            </div>
        </div>
        <span id="listen-time" class="text-xs font-mono text-indigo-600 dark:text-indigo-400"></span>
    </div>

    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Manual entry (optional)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="<?= $labelClass ?>" for="scan_epc">UHF EPC Tag</label>
                <input type="text" id="scan_epc" class="<?= $inputClass ?> font-mono uppercase text-lg tracking-wide" placeholder="Scan UHF tag…" autocomplete="off" autofocus/>
            </div>
            <div class="space-y-1.5">
                <label class="<?= $labelClass ?>" for="scan_qr">Batch QR Code</label>
                <input type="text" id="scan_qr" class="<?= $inputClass ?> font-mono" placeholder="WW|P|PRD-0001|…" autocomplete="off"/>
            </div>
        </div>
        <div id="lookup-status" class="hidden text-sm"></div>
    </div>

    <div id="result-panel" class="hidden space-y-6">
        <!-- Header card -->
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <span id="res-type-badge" class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary mb-2">Product</span>
                    <span id="res-stock-status" class="hidden inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ml-1 mb-2"></span>
                    <h2 id="res-name" class="text-xl font-bold text-gray-900 dark:text-white">—</h2>
                    <p id="res-code" class="text-sm font-mono text-gray-500 dark:text-gray-400 mt-1">—</p>
                    <p id="res-desc" class="text-sm text-gray-600 dark:text-gray-300 mt-2 hidden"></p>
                </div>
                <div class="text-right space-y-1">
                    <p id="res-balance-label" class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Balance</p>
                    <p id="res-balance" class="text-3xl font-bold text-primary">0</p>
                    <p id="res-unit" class="text-xs text-gray-500 dark:text-gray-400">—</p>
                    <p id="res-stock-detail" class="text-xs mt-1 hidden"></p>
                    <p id="res-product-total" class="text-xs text-gray-500 dark:text-gray-400 mt-1 hidden"></p>
                </div>
            </div>

            <div id="res-tag-presence" class="hidden mt-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-sm text-green-800 dark:text-green-200"></div>
            <div id="res-scanned-tag" class="hidden mt-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-sm text-amber-900 dark:text-amber-100"></div>

            <div class="flex flex-wrap gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a id="res-view-link" href="#" class="text-sm text-primary hover:underline">View master record →</a>
                <a id="res-edit-link" href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Edit</a>
            </div>
        </div>

        <!-- Stock summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <p id="res-summary-1-label" class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Stock In</p>
                <p id="res-total-in" class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">0</p>
            </div>
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <p id="res-summary-2-label" class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Stock Out</p>
                <p id="res-total-out" class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">0</p>
            </div>
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <p id="res-summary-3-label" class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Current Balance</p>
                <p id="res-balance-2" class="text-2xl font-bold text-primary mt-1">0</p>
            </div>
        </div>

        <!-- Details grid -->
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Item Details</h3>
            <dl id="res-details" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-3 text-sm"></dl>
        </div>

        <!-- Tags -->
        <div id="tags-section" class="hidden bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 id="tags-section-title" class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">UHF Tags</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-900 dark:text-gray-100">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-2 pr-4">EPC</th>
                            <th class="pb-2 pr-4">Current Qty</th>
                            <th class="pb-2 pr-4">Registered Qty</th>
                            <th class="pb-2">Label</th>
                        </tr>
                    </thead>
                    <tbody id="res-tags" class="divide-y divide-gray-100 dark:divide-gray-800"></tbody>
                </table>
            </div>
        </div>

        <!-- Zone activity today -->
        <div id="zone-section" class="hidden bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Zone Activity — Today</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-900 dark:text-gray-100">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-2 pr-4">Zone</th>
                            <th class="pb-2 pr-4">EPC</th>
                            <th class="pb-2 pr-4">Status</th>
                            <th class="pb-2 pr-4">In</th>
                            <th class="pb-2 pr-4">Out</th>
                            <th class="pb-2">Duration</th>
                        </tr>
                    </thead>
                    <tbody id="res-zones" class="divide-y divide-gray-100 dark:divide-gray-800"></tbody>
                </table>
            </div>
        </div>

        <!-- Recent transactions -->
        <div id="txn-section" class="hidden bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Recent Stock Movements</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-900 dark:text-gray-100">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-2 pr-4">Date</th>
                            <th class="pb-2 pr-4">Type</th>
                            <th class="pb-2 pr-4">Qty</th>
                            <th class="pb-2 pr-4">Method</th>
                            <th class="pb-2">Notes</th>
                        </tr>
                    </thead>
                    <tbody id="res-txns" class="divide-y divide-gray-100 dark:divide-gray-800"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const lookupUrl = <?= json_encode(base_url('inventory/search-stock/lookup')) ?>;
const scansUrl = <?= json_encode(base_url('inventory/search-stock/scans')) ?>;
const tagStockInUrl = <?= json_encode(base_url('inventory/tag-stock-in')) ?>;
let lookupTimer = null;
let lastLookupKey = '';
let lastPollTs = Date.now() / 1000;
let pollTimer = null;
const seenScanIds = new Set();

function formatQty(n) {
    const v = Number(n) || 0;
    if (Math.abs(v - Math.round(v)) < 1e-9) return String(Math.round(v));
    return v.toFixed(3).replace(/\.?0+$/, '');
}

function setStatus(msg, isError) {
    const el = document.getElementById('lookup-status');
    el.classList.remove('hidden', 'text-red-600', 'dark:text-red-400', 'text-gray-500', 'dark:text-gray-400');
    el.classList.add(isError ? 'text-red-600' : 'text-gray-500', isError ? 'dark:text-red-400' : 'dark:text-gray-400');
    el.textContent = msg;
}

function scheduleLookup() {
    clearTimeout(lookupTimer);
    lookupTimer = setTimeout(runLookup, 300);
}

async function runLookup() {
    const epc = document.getElementById('scan_epc').value.trim().toUpperCase();
    const qr = document.getElementById('scan_qr').value.trim();
    const key = epc + '|' + qr;

    if (!epc && !qr) {
        document.getElementById('result-panel').classList.add('hidden');
        document.getElementById('lookup-status').classList.add('hidden');
        lastLookupKey = '';
        return;
    }

    if (key === lastLookupKey) return;
    lastLookupKey = key;

    setStatus('Looking up…', false);

    const params = new URLSearchParams();
    if (epc) params.set('epc', epc);
    if (qr) params.set('qr_code', qr);

    try {
        const res = await fetch(lookupUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();

        if (!data.success) {
            document.getElementById('result-panel').classList.add('hidden');
            setStatus(data.message || 'Not found.', true);
            return;
        }

        renderResult(data);
        setStatus('Found — ' + data.item.name, false);
    } catch (e) {
        setStatus('Lookup failed. Try again.', true);
    }
}

function renderStockStatus(status) {
    const badge = document.getElementById('res-stock-status');
    const detail = document.getElementById('res-stock-detail');
    if (!status || !status.label) {
        badge.classList.add('hidden');
        detail.classList.add('hidden');
        return;
    }
    const tones = {
        green: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        amber: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
        gray:  'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    };
    badge.textContent = status.label;
    badge.className = 'inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ml-1 mb-2 ' + (tones[status.tone] || tones.gray);
    badge.classList.remove('hidden');
    if (status.detail) {
        detail.textContent = status.detail;
        detail.className = 'text-xs mt-1 text-gray-600 dark:text-gray-300';
        detail.classList.remove('hidden');
    } else {
        detail.classList.add('hidden');
    }
}

function renderResult(data) {
    const panel = document.getElementById('result-panel');
    panel.classList.remove('hidden');

    const item = data.item || {};
    const summary = data.stock_summary || {};
    const tagScoped = !!data.scoped_to_tag;
    const productSummary = data.product_stock_summary || null;

    document.getElementById('res-balance-label').textContent = tagScoped ? 'Tag Balance' : 'Balance';
    document.getElementById('res-summary-1-label').textContent = tagScoped ? 'Registered Qty' : 'Total Stock In';
    document.getElementById('res-summary-2-label').textContent = tagScoped ? 'Qty OUT' : 'Total Stock Out';
    document.getElementById('res-summary-3-label').textContent = tagScoped ? 'Tag Balance' : 'Current Balance';
    document.getElementById('tags-section-title').textContent = tagScoped ? 'Scanned Tag' : 'UHF Tags';

    document.getElementById('res-type-badge').textContent = data.type_label || 'Item';
    renderStockStatus(data.stock_status);
    document.getElementById('res-name').textContent = item.name || '—';
    document.getElementById('res-code').textContent = item.code || '—';

    if (tagScoped) {
        document.getElementById('res-total-in').textContent = summary.registered_qty_fmt || formatQty(summary.registered_qty ?? 0);
        document.getElementById('res-total-out').textContent = summary.total_stock_out_fmt || '0';
        document.getElementById('res-total-in').className = 'text-2xl font-bold text-gray-900 dark:text-white mt-1';
    } else {
        document.getElementById('res-total-in').textContent = summary.total_stock_in_fmt || '0';
        document.getElementById('res-total-out').textContent = summary.total_stock_out_fmt || '0';
        document.getElementById('res-total-in').className = 'text-2xl font-bold text-green-600 dark:text-green-400 mt-1';
    }

    document.getElementById('res-balance').textContent = formatQty(summary.balance ?? item.balance ?? 0);
    document.getElementById('res-balance-2').textContent = formatQty(summary.balance ?? item.balance ?? 0);
    document.getElementById('res-unit').textContent = item.unit ? 'Unit: ' + item.unit : '';

    const productTotalEl = document.getElementById('res-product-total');
    if (tagScoped && productSummary) {
        productTotalEl.textContent = 'Product total (all tags): ' + (productSummary.balance_fmt || formatQty(productSummary.balance ?? 0));
        productTotalEl.classList.remove('hidden');
    } else {
        productTotalEl.classList.add('hidden');
    }

    const descEl = document.getElementById('res-desc');
    if (item.description) {
        descEl.textContent = item.description;
        descEl.classList.remove('hidden');
    } else {
        descEl.classList.add('hidden');
    }

    document.getElementById('res-view-link').href = data.view_url || '#';
    document.getElementById('res-edit-link').href = data.edit_url || '#';

    const presenceEl = document.getElementById('res-tag-presence');
    if (data.tag_presence) {
        presenceEl.textContent = 'Scanned tag is IN zone: ' + data.tag_presence.zone_name + ' (since ' + data.tag_presence.since + ')';
        presenceEl.classList.remove('hidden');
    } else {
        presenceEl.classList.add('hidden');
    }

    const scannedEl = document.getElementById('res-scanned-tag');
    if (data.scanned_tag) {
        const t = data.scanned_tag;
        scannedEl.innerHTML = '<strong>Scanned tag:</strong> ' + escapeHtml(t.epc_no || '') +
            ' — qty <strong>' + formatQty(t.tag_display_quantity ?? t.tag_quantity) + '</strong>, registered qty <strong>' + formatQty(t.tag_registered_quantity) + '</strong>';
        scannedEl.classList.remove('hidden');
    } else if (data.scanned_epc) {
        scannedEl.innerHTML = '<strong>Scanned EPC:</strong> ' + escapeHtml(data.scanned_epc) + ' (legacy item EPC, not in tag table)';
        scannedEl.classList.remove('hidden');
    } else {
        scannedEl.classList.add('hidden');
    }

    const detailsEl = document.getElementById('res-details');
    detailsEl.innerHTML = (data.detail_fields || []).map(f =>
        '<div><dt class="text-gray-500 dark:text-gray-400">' + escapeHtml(f.label) + '</dt>' +
        '<dd class="font-medium text-gray-900 dark:text-white mt-0.5 break-all">' + escapeHtml(String(f.value ?? '—')) + '</dd></div>'
    ).join('');

    const tags = data.tags || [];
    const tagsSection = document.getElementById('tags-section');
    const tagsBody = document.getElementById('res-tags');
    if (tags.length) {
        tagsSection.classList.remove('hidden');
        tagsBody.innerHTML = tags.map(t => {
            const highlight = data.scanned_epc && t.epc_no && t.epc_no.toUpperCase() === data.scanned_epc.toUpperCase();
            const displayQty = t.tag_display_quantity ?? t.tag_quantity ?? 0;
            return '<tr class="' + (highlight ? 'bg-amber-50 dark:bg-amber-900/20' : '') + '">' +
                '<td class="' + tableCellMono + '">' + escapeHtml(t.epc_no || '') + '</td>' +
                '<td class="' + tableCell + '">' + formatQty(displayQty) + '</td>' +
                '<td class="' + tableCell + '">' + formatQty(t.tag_registered_quantity) + '</td>' +
                '<td class="' + tableCellLast + '">' + escapeHtml(t.tag_label || '—') + '</td></tr>';
        }).join('');
    } else {
        tagsSection.classList.add('hidden');
    }

    const zones = data.scan_records || [];
    const zoneSection = document.getElementById('zone-section');
    const zonesBody = document.getElementById('res-zones');
    if (zones.length) {
        zoneSection.classList.remove('hidden');
        zonesBody.innerHTML = zones.map(z => {
            const badge = z.status === 'IN'
                ? '<span class="text-green-600 dark:text-green-400 font-bold">IN</span>'
                : '<span class="text-gray-500 dark:text-gray-400">OUT</span>';
            return '<tr><td class="' + tableCell + '">' + escapeHtml(z.zone_name) + '</td>' +
                '<td class="' + tableCellMono + '">' + escapeHtml(z.tag_epc || '—') + '</td>' +
                '<td class="' + tableCell + '">' + badge + '</td>' +
                '<td class="' + tableCell + '">' + escapeHtml(z.time_in) + '</td>' +
                '<td class="' + tableCell + '">' + escapeHtml(z.time_out) + '</td>' +
                '<td class="' + tableCellLast + '">' + escapeHtml(z.duration) + '</td></tr>';
        }).join('');
    } else {
        zoneSection.classList.add('hidden');
    }

    const txns = data.stock_transactions || [];
    const txnSection = document.getElementById('txn-section');
    const txnsBody = document.getElementById('res-txns');
    if (txns.length) {
        txnSection.classList.remove('hidden');
        txnsBody.innerHTML = txns.map(t =>
            '<tr><td class="' + tableCell + ' whitespace-nowrap">' + escapeHtml(t.datetime || t.created_at || '') + '</td>' +
            '<td class="' + tableCell + '">' + escapeHtml(t.transaction_label || t.transaction_type || '') + '</td>' +
            '<td class="' + tableCell + ' font-medium">' + formatQty(t.quantity) + '</td>' +
            '<td class="' + tableCell + '">' + escapeHtml(t.scan_method || '—') + '</td>' +
            '<td class="' + tableCellLast + '">' + escapeHtml(t.notes || '—') + '</td></tr>'
        ).join('');
    } else {
        txnSection.classList.add('hidden');
    }
}

function escapeHtml(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

const tableCell = 'py-2 pr-4 text-gray-900 dark:text-gray-100';
const tableCellMono = 'py-2 pr-4 font-mono text-xs text-gray-800 dark:text-gray-200';
const tableCellLast = 'py-2 text-gray-900 dark:text-gray-100';

document.getElementById('scan_epc').addEventListener('input', function () {
    if (this.value.trim()) document.getElementById('scan_qr').value = '';
    scheduleLookup();
});
document.getElementById('scan_qr').addEventListener('input', function () {
    if (this.value.trim()) document.getElementById('scan_epc').value = '';
    scheduleLookup();
});
document.getElementById('scan_epc').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); clearTimeout(lookupTimer); runLookup(); }
});
document.getElementById('scan_qr').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); clearTimeout(lookupTimer); runLookup(); }
});

async function pollLookupScans() {
    try {
        const res = await fetch(scansUrl + '?since=' + encodeURIComponent(lastPollTs), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (!data.success || !data.scans) return;

        for (const scan of data.scans) {
            if (seenScanIds.has(scan.id)) continue;
            seenScanIds.add(scan.id);
            lastPollTs = Math.max(lastPollTs, scan.ts || lastPollTs);

            if (!scan.found) {
                document.getElementById('scan_epc').value = scan.epc || '';
                document.getElementById('scan_qr').value = '';
                lastLookupKey = '';
                document.getElementById('result-panel').classList.add('hidden');
                setStatus('Tag not registered: ' + (scan.epc || '') + ' — use Tag + Stock In.', true);
                continue;
            }

            document.getElementById('scan_epc').value = scan.epc || '';
            document.getElementById('scan_qr').value = '';
            lastLookupKey = '';
            await runLookup();
            setStatus('RFID lookup scan: ' + (scan.item_name || scan.epc), false);
        }
    } catch (e) {
        // silent retry on next poll
    }
}

function startListenPoll() {
    document.getElementById('listen-time').textContent = 'Updated ' + new Date().toLocaleTimeString();
    pollTimer = setInterval(() => {
        pollLookupScans();
        document.getElementById('listen-time').textContent = 'Updated ' + new Date().toLocaleTimeString();
    }, 1200);
    pollLookupScans();
}

document.addEventListener('DOMContentLoaded', () => {
    startListenPoll();
    <?php if (!empty($prefill)): ?>
    document.getElementById('scan_epc').value = <?= json_encode($prefill) ?>;
    lastLookupKey = '';
    runLookup();
    <?php endif; ?>
});
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        clearInterval(pollTimer);
    } else {
        clearInterval(pollTimer);
        startListenPoll();
    }
});
</script>

<?= $this->include('templates/footer') ?>
