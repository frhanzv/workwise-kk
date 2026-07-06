<?= $this->include('templates/header') ?>
<?php $ledgerItemCount = (int) ($item_count ?? 0); ?>

<div class="ledger-page flex flex-col gap-6 px-2 sm:px-4 py-2 sm:py-4">
    <!-- Print-only header -->
    <div class="ledger-print-header hidden">
        <div class="flex items-start justify-between gap-4 border-b border-gray-300 pb-3 mb-3">
            <div>
                <p class="text-lg font-bold tracking-tight">Workwise — Inventory Dashboard</p>
                <p class="text-sm font-semibold mt-0.5">Stock Ledger</p>
            </div>
            <div class="text-right text-xs text-gray-600">
                <p>As of <?= esc($as_of_date) ?></p>
                <p>Printed <?= date('d-M-y H:i') ?></p>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mt-4 md:mt-2 ledger-no-print">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10 text-primary ledger-no-print">
                <span class="material-symbols-outlined text-3xl">menu_book</span>
            </div>
            <div>
                <h1 class="text-2xl lg:text-3xl font-extrabold leading-tight tracking-tight text-gray-900 dark:text-white uppercase">Inventory Dashboard</h1>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto ledger-no-print">
            <div class="relative w-full sm:w-44">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base pointer-events-none">filter_list</span>
                <select id="type-filter" onchange="changeTypeFilter(this.value)" class="w-full h-9 appearance-none rounded-lg border-none bg-gray-100 dark:bg-gray-800 pl-10 pr-8 text-sm font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/50 transition-all cursor-pointer">
                    <option value="" <?= $type_filter === null ? 'selected' : '' ?>>All Types</option>
                    <option value="product" <?= $type_filter === 'product' ? 'selected' : '' ?>>Products</option>
                    <option value="raw_material" <?= $type_filter === 'raw_material' ? 'selected' : '' ?>>Raw Materials</option>
                </select>
            </div>
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base">search</span>
                <input id="search-input" onkeyup="filterLedger()" class="w-full h-9 rounded-lg border-none bg-gray-100 dark:bg-gray-800 pl-10 pr-4 text-sm font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/50 transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500" placeholder="Search code, name, or batch..." type="text"/>
            </div>
            <div class="flex items-center gap-2 h-9 px-3 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 ledger-no-print">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                <span class="text-xs text-gray-500 dark:text-gray-400 tabular-nums">Updated <span id="last-updated-time"><?= esc($as_of_date) ?></span></span>
            </div>
            <button onclick="window.print()" class="flex items-center justify-center gap-2 h-9 px-4 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors whitespace-nowrap print:hidden">
                <span class="material-symbols-outlined text-base leading-none">print</span>
                Print
            </button>
        </div>
    </div>

    <div class="ledger-stats grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-background-dark p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm ledger-stat-card">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ledger Rows</p>
                <span class="material-symbols-outlined text-primary ledger-no-print">table_rows</span>
            </div>
            <p id="stat-rows" class="text-3xl font-black text-gray-900 dark:text-white tabular-nums"><?= count($rows) ?></p>
        </div>
        <div class="bg-white dark:bg-background-dark p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm ledger-stat-card">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</p>
                <span class="material-symbols-outlined text-blue-500 ledger-no-print">inventory_2</span>
            </div>
            <p id="stat-items" class="text-3xl font-black text-gray-900 dark:text-white tabular-nums"><?= $ledgerItemCount ?></p>
        </div>
        <div class="bg-white dark:bg-background-dark p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm col-span-2 ledger-stat-card">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Inventory</p>
                <span class="material-symbols-outlined text-indigo-500 ledger-no-print">inventory</span>
            </div>
            <p id="stat-grand-total" class="text-3xl font-black text-gray-900 dark:text-white tabular-nums"><?= format_inventory_qty($grand_total) ?></p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Sum of all running balances</p>
        </div>
    </div>

    <div class="ledger-table-wrap bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
        <div class="px-4 lg:px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between ledger-screen-table-head">
            <div class="flex items-center gap-3">
                <div class="bg-primary/10 p-2 rounded-lg ledger-no-print">
                    <span class="material-symbols-outlined text-primary">menu_book</span>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">Stock Ledger</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Auto-refreshes every 5 seconds</p>
                </div>
            </div>
            <span id="row-count" class="text-xs font-bold px-2.5 py-1 bg-primary/10 text-primary rounded-full"><?= count($rows) ?> Records</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[960px]" id="ledger-table">
                <thead class="bg-gray-50 dark:bg-gray-800/90">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Type</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Code</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Item Name</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Batch / Tag</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">Registered</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">Qty IN</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">Qty OUT</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">Running Balance</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">Total Inventory</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">Last Transaction</th>
                    </tr>
                </thead>
                <tbody id="ledger-body" class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    <?php if (empty($rows)): ?>
                        <tr id="ledger-empty">
                            <td colspan="10" class="px-4 py-10 text-center text-gray-400 text-sm">No stock ledger rows found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $search = strtolower(trim(
                                ($row['type_label'] ?? '') . ' ' .
                                ($row['code'] ?? '') . ' ' .
                                ($row['name'] ?? '') . ' ' .
                                ($row['batch_number'] ?? '')
                            ));
                            $groupBorder = !empty($row['is_last_in_group'])
                                ? 'border-b-2 border-b-gray-200 dark:border-b-gray-700'
                                : '';
                            ?>
                            <tr class="ledger-row hover:bg-gray-50 dark:hover:bg-gray-800/30 <?= $groupBorder ?>"
                                data-search="<?= esc($search) ?>"
                                data-item-key="<?= esc($row['item_type'] . ':' . $row['item_id']) ?>">
                                <?php if (!empty($row['show_product_info'])): ?>
                                <td class="px-3 py-2.5 align-top border-r border-gray-100 dark:border-gray-800/80" rowspan="<?= (int) ($row['group_rowspan'] ?? 1) ?>">
                                    <?php if (($row['item_type'] ?? '') === 'product'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300">Product</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300">Raw Material</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2.5 align-top border-r border-gray-100 dark:border-gray-800/80" rowspan="<?= (int) ($row['group_rowspan'] ?? 1) ?>">
                                    <span class="text-xs font-mono font-bold text-primary dark:text-blue-400"><?= esc($row['code']) ?></span>
                                </td>
                                <td class="px-3 py-2.5 align-top border-r border-gray-100 dark:border-gray-800/80" rowspan="<?= (int) ($row['group_rowspan'] ?? 1) ?>">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($row['name']) ?></span>
                                    <?php if (!empty($row['unit'])): ?>
                                        <span class="block text-[10px] font-medium text-gray-400 dark:text-gray-500"><?= esc($row['unit']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td class="px-3 py-2.5 text-sm font-mono text-gray-700 dark:text-gray-300">
                                    <?= $row['batch_number'] !== '' ? esc($row['batch_number']) : '—' ?>
                                </td>
                                <td class="px-3 py-2.5 text-sm tabular-nums text-right text-gray-900 dark:text-white">
                                    <?= format_inventory_qty($row['start_balance']) ?>
                                </td>
                                <td class="px-3 py-2.5 text-sm tabular-nums text-right font-semibold text-green-600 dark:text-green-400">
                                    <?= (float) $row['qty_in'] > 0 ? format_inventory_qty($row['qty_in']) : '' ?>
                                </td>
                                <td class="px-3 py-2.5 text-sm tabular-nums text-right font-semibold text-red-600 dark:text-red-400">
                                    <?= (float) $row['qty_out'] > 0 ? format_inventory_qty($row['qty_out']) : '' ?>
                                </td>
                                <td class="px-3 py-2.5 text-sm tabular-nums text-right font-bold text-gray-900 dark:text-white">
                                    <?= format_inventory_qty($row['running_balance']) ?>
                                </td>
                                <?php if (!empty($row['show_total'])): ?>
                                <?php $totalAlign = ((int) ($row['group_rowspan'] ?? 1)) > 1 ? 'align-top' : 'align-middle'; ?>
                                <td class="px-3 py-2.5 text-sm tabular-nums text-right font-black text-indigo-700 dark:text-indigo-300 <?= $totalAlign ?>" rowspan="<?= (int) ($row['group_rowspan'] ?? 1) ?>">
                                    <?= format_inventory_qty($row['total_inventory']) ?>
                                </td>
                                <?php endif; ?>
                                <td class="px-3 py-2.5 text-xs tabular-nums text-right text-gray-600 dark:text-gray-300">
                                    <?= esc($row['last_transaction'] ?: '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="ledger-empty" class="hidden">
                            <td colspan="10" class="px-4 py-10 text-center text-gray-400 text-sm">No matching ledger rows.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 10mm 8mm;
    }

    html, body {
        background: #fff !important;
        color: #111 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        overflow: visible !important;
        height: auto !important;
    }

    html.dark,
    html.dark body,
    body > div,
    .layout-container,
    main,
    .ledger-page,
    .ledger-table-wrap,
    .overflow-x-auto,
    #ledger-table,
    #ledger-table thead,
    #ledger-table tbody {
        background: #fff !important;
        background-color: #fff !important;
    }

    body > div,
    .layout-container {
        overflow: visible !important;
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
    }

    .relative.flex.h-screen {
        height: auto !important;
        min-height: 0 !important;
    }

    /* Hide app chrome */
    #app-mobile-header,
    #sidebar,
    #mobile-overlay,
    #stock-finder-btn,
    #stock-finder-panel,
    #analytics-floating-btn,
    #analytics-chat-widget,
    footer,
    .ledger-no-print,
    .ledger-screen-table-head,
    button {
        display: none !important;
    }

    .layout-container,
    main,
    .ledger-page {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: visible !important;
        gap: 0 !important;
    }

    .ledger-print-header {
        display: block !important;
    }

    .ledger-stats {
        display: flex !important;
        flex-direction: row !important;
        gap: 8px !important;
        margin-bottom: 10px !important;
    }

    .ledger-stat-card {
        flex: 1 !important;
        padding: 8px 10px !important;
        border: 1px solid #ccc !important;
        border-radius: 4px !important;
        box-shadow: none !important;
        background: #fff !important;
    }

    .ledger-stat-card p {
        color: #333 !important;
    }

    .ledger-stat-card .text-3xl {
        font-size: 16px !important;
        line-height: 1.2 !important;
        color: #000 !important;
    }

    .ledger-table-wrap {
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        overflow: visible !important;
    }

    .overflow-x-auto {
        overflow: visible !important;
    }

    #ledger-table {
        min-width: 0 !important;
        width: 100% !important;
        font-size: 8px !important;
        border-collapse: collapse !important;
    }

    #ledger-table thead {
        display: table-header-group;
    }

    #ledger-table th {
        background: #e8e8e8 !important;
        color: #000 !important;
        padding: 4px 3px !important;
        border: 1px solid #999 !important;
        font-size: 7px !important;
        white-space: nowrap;
    }

    #ledger-table td {
        padding: 3px 3px !important;
        border: 1px solid #ccc !important;
        border-color: #ccc !important;
        color: #000 !important;
        background: #fff !important;
        vertical-align: top;
    }

    #ledger-body tr.ledger-row,
    #ledger-body tr.ledger-row.border-b-2 {
        border-bottom: 1px solid #ddd !important;
        border-top: none !important;
        box-shadow: none !important;
    }

    #ledger-body > tr > td {
        border-right-color: #ddd !important;
    }

    #ledger-table .text-primary,
    #ledger-table .text-green-600,
    #ledger-table .text-red-600,
    #ledger-table .text-indigo-700,
    #ledger-table .text-blue-700,
    #ledger-table .text-amber-700 {
        color: #000 !important;
    }

    #ledger-table span.inline-flex {
        background: #eee !important;
        color: #000 !important;
        border: 1px solid #bbb !important;
        padding: 1px 4px !important;
    }

    #ledger-body tr.ledger-row {
        page-break-inside: avoid;
    }

    #ledger-body tr.ledger-row.print-show {
        display: table-row !important;
    }

    #row-count {
        display: inline-block !important;
        background: #eee !important;
        color: #000 !important;
        border: 1px solid #bbb !important;
    }
}
</style>

<script>
let ledgerRows = <?= json_encode($rows) ?>;
let ledgerGrandTotal = <?= json_encode((float) $grand_total) ?>;
let ledgerItemCount = <?= json_encode($ledgerItemCount) ?>;
let currentTypeFilter = <?= json_encode($type_filter) ?>;
let updateInterval = null;
let isUpdating = false;

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

function buildRowSearch(row) {
    return [
        row.type_label || '',
        row.code || '',
        row.name || '',
        row.batch_number || '',
    ].join(' ').toLowerCase();
}

function paintLedgerTable() {
    const body = document.getElementById('ledger-body');
    if (!body) return;

    if (!ledgerRows.length) {
        body.innerHTML = '<tr id="ledger-empty"><td colspan="10" class="px-4 py-10 text-center text-gray-400 text-sm">No stock ledger rows found.</td></tr>';
        filterLedger();
        return;
    }

    let html = ledgerRows.map((row) => {
        const search = buildRowSearch(row);
        const groupBorder = row.is_last_in_group ? 'border-b-2 border-b-gray-200 dark:border-b-gray-700' : '';
        const itemKey = `${row.item_type}:${row.item_id}`;
        let cells = '';

        if (row.show_product_info) {
            const typeBadge = row.item_type === 'product'
                ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300">Product</span>'
                : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300">Raw Material</span>';
            const rowspan = row.group_rowspan || 1;
            const unit = row.unit
                ? `<span class="block text-[10px] font-medium text-gray-400 dark:text-gray-500">${escapeHtml(row.unit)}</span>`
                : '';
            cells += `<td class="px-3 py-2.5 align-top border-r border-gray-100 dark:border-gray-800/80" rowspan="${rowspan}">${typeBadge}</td>`;
            cells += `<td class="px-3 py-2.5 align-top border-r border-gray-100 dark:border-gray-800/80" rowspan="${rowspan}"><span class="text-xs font-mono font-bold text-primary dark:text-blue-400">${escapeHtml(row.code)}</span></td>`;
            cells += `<td class="px-3 py-2.5 align-top border-r border-gray-100 dark:border-gray-800/80" rowspan="${rowspan}"><span class="text-sm font-semibold text-gray-900 dark:text-white">${escapeHtml(row.name)}</span>${unit}</td>`;
        }

        const batch = row.batch_number ? escapeHtml(row.batch_number) : '—';
        const qtyIn = Number(row.qty_in) > 0 ? formatInventoryQty(row.qty_in) : '';
        const qtyOut = Number(row.qty_out) > 0 ? formatInventoryQty(row.qty_out) : '';

        cells += `<td class="px-3 py-2.5 text-sm font-mono text-gray-700 dark:text-gray-300">${batch}</td>`;
        cells += `<td class="px-3 py-2.5 text-sm tabular-nums text-right text-gray-900 dark:text-white">${formatInventoryQty(row.start_balance)}</td>`;
        cells += `<td class="px-3 py-2.5 text-sm tabular-nums text-right font-semibold text-green-600 dark:text-green-400">${qtyIn}</td>`;
        cells += `<td class="px-3 py-2.5 text-sm tabular-nums text-right font-semibold text-red-600 dark:text-red-400">${qtyOut}</td>`;
        cells += `<td class="px-3 py-2.5 text-sm tabular-nums text-right font-bold text-gray-900 dark:text-white">${formatInventoryQty(row.running_balance)}</td>`;

        if (row.show_total) {
            const rowspan = row.group_rowspan || 1;
            const totalAlign = rowspan > 1 ? 'align-top' : 'align-middle';
            cells += `<td class="px-3 py-2.5 text-sm tabular-nums text-right font-black text-indigo-700 dark:text-indigo-300 ${totalAlign}" rowspan="${rowspan}">${formatInventoryQty(row.total_inventory)}</td>`;
        }

        const lastTxn = row.last_transaction ? escapeHtml(row.last_transaction) : '—';
        cells += `<td class="px-3 py-2.5 text-xs tabular-nums text-right text-gray-600 dark:text-gray-300">${lastTxn}</td>`;

        return `<tr class="ledger-row hover:bg-gray-50 dark:hover:bg-gray-800/30 ${groupBorder}" data-search="${escapeHtml(search)}" data-item-key="${escapeHtml(itemKey)}">${cells}</tr>`;
    }).join('');

    html += '<tr id="ledger-empty" class="hidden"><td colspan="10" class="px-4 py-10 text-center text-gray-400 text-sm">No matching ledger rows.</td></tr>';
    body.innerHTML = html;
    filterLedger();
}

function updateLedgerStats() {
    const statItems = document.getElementById('stat-items');
    const statGrand = document.getElementById('stat-grand-total');
    if (statItems) statItems.textContent = String(ledgerItemCount);
    if (statGrand) statGrand.textContent = formatInventoryQty(ledgerGrandTotal);
}

function getLedgerDataUrl() {
    const url = new URL('<?= base_url('inventory/stock-ledger-data') ?>', window.location.origin);
    if (currentTypeFilter) url.searchParams.set('type', currentTypeFilter);
    url.searchParams.set('_', String(Date.now()));
    return url.toString();
}

async function refreshLedgerData() {
    if (isUpdating) return;
    isUpdating = true;
    try {
        const res = await fetch(getLedgerDataUrl(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store',
        });
        const data = await res.json();
        if (!data.success) return;

        ledgerRows = data.rows || [];
        ledgerGrandTotal = Number(data.grand_total) || 0;
        ledgerItemCount = Number(data.item_count) || 0;
        paintLedgerTable();
        updateLedgerStats();

        const updated = document.getElementById('last-updated-time');
        if (updated && data.last_updated) updated.textContent = data.last_updated;
    } catch (e) {
        console.error('Failed to refresh stock ledger', e);
    } finally {
        isUpdating = false;
    }
}

function changeTypeFilter(value) {
    currentTypeFilter = value || null;
    const url = new URL(window.location.href);
    if (currentTypeFilter) url.searchParams.set('type', currentTypeFilter);
    else url.searchParams.delete('type');
    window.history.replaceState({}, '', url.toString());
    refreshLedgerData();
}

function filterLedger() {
    const q = (document.getElementById('search-input').value || '').trim().toLowerCase();
    const rows = document.querySelectorAll('#ledger-body tr.ledger-row');
    let visible = 0;

    rows.forEach((row) => {
        const hay = row.getAttribute('data-search') || '';
        const show = !q || hay.includes(q);
        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });

    const empty = document.getElementById('ledger-empty');
    if (empty) {
        empty.classList.toggle('hidden', visible > 0);
    }

    const countEl = document.getElementById('row-count');
    if (countEl) {
        countEl.textContent = visible + ' Records';
    }

    const statRows = document.getElementById('stat-rows');
    if (statRows) {
        statRows.textContent = String(visible);
    }
}

window.addEventListener('beforeprint', () => {
    document.querySelectorAll('#ledger-body tr.ledger-row').forEach((row) => {
        row.classList.add('print-show');
        row.classList.remove('hidden');
    });
});

window.addEventListener('afterprint', () => {
    document.querySelectorAll('#ledger-body tr.ledger-row').forEach((row) => {
        row.classList.remove('print-show');
    });
    filterLedger();
});

document.addEventListener('DOMContentLoaded', () => {
    updateInterval = setInterval(refreshLedgerData, 5000);
});

window.addEventListener('beforeunload', () => {
    if (updateInterval) clearInterval(updateInterval);
});
</script>

<?= $this->include('templates/footer') ?>
