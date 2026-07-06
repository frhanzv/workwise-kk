<?= $this->include('templates/header') ?>

<?php
$totalMismatches = (int) ($total ?? count($rows));
$highCount   = (int) ($alert_counts['High'] ?? 0);
$mediumCount = (int) ($alert_counts['Medium'] ?? 0);
$lowCount    = (int) ($alert_counts['Low'] ?? 0);

$filterLabels = [];
if ($type_filter === 'product') {
    $filterLabels[] = 'Products';
} elseif ($type_filter === 'raw_material') {
    $filterLabels[] = 'Raw Materials';
}
if ($alert_filter) {
    $filterLabels[] = $alert_filter . ' alert';
}
$filterSummary = $filterLabels !== [] ? implode(' · ', $filterLabels) : 'All types · All alerts';
?>

<div class="mismatch-page flex flex-col gap-6 px-2 sm:px-4 py-2 sm:py-4">
    <!-- Print-only header -->
    <div class="mismatch-print-header hidden">
        <div class="flex items-start justify-between gap-4 border-b border-gray-300 pb-3 mb-3">
            <div>
                <p class="text-lg font-bold tracking-tight">Workwise — Inventory Dashboard</p>
                <p class="text-sm font-semibold mt-0.5">Location Mismatch Monitoring</p>
                <p class="text-xs text-gray-600 mt-1">Items detected outside their configured store locations</p>
                <p id="print-filter-summary" class="text-xs text-gray-500 mt-0.5"><?= esc($filterSummary) ?></p>
            </div>
            <div class="text-right text-xs text-gray-600">
                <p>As of <?= esc($as_of_date) ?></p>
                <p>Printed <?= date('d-M-y H:i') ?></p>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-start justify-between gap-4 mt-4 md:mt-2 mismatch-no-print">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Inventory Location Mismatch Monitoring</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Items detected outside their configured store locations</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400 dark:text-gray-500 tabular-nums">Updated <span id="last-updated-time"><?= esc($as_of_date) ?></span></span>
            <button type="button" onclick="manualRefresh()" class="flex items-center justify-center gap-1.5 h-9 px-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <span class="material-symbols-outlined text-base leading-none">refresh</span>
                Refresh
            </button>
            <button onclick="window.print()" class="flex items-center justify-center gap-1.5 h-9 px-3 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <span class="material-symbols-outlined text-base leading-none">print</span>
                Print
            </button>
        </div>
    </div>

    <div class="mismatch-stats grid grid-cols-2 lg:grid-cols-4 gap-4">
        <button type="button" onclick="changeAlertFilter('')" id="card-all" class="mismatch-stat-card text-left bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary/40 transition-colors <?= $alert_filter === null ? 'ring-2 ring-primary/30' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 mismatch-no-print">
                    <span class="material-symbols-outlined text-xl">wrong_location</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Mismatches</p>
                    <p id="stat-rows" class="text-2xl lg:text-3xl font-black text-gray-900 dark:text-white tabular-nums"><?= $totalMismatches ?></p>
                </div>
            </div>
        </button>
        <button type="button" onclick="changeAlertFilter('High')" id="card-high" class="mismatch-stat-card text-left bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-red-400/50 transition-colors <?= $alert_filter === 'High' ? 'ring-2 ring-red-400/40' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 mismatch-no-print">
                    <span class="material-symbols-outlined text-xl">error</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">High · 24h+</p>
                    <p id="stat-high" class="text-2xl lg:text-3xl font-black text-red-600 dark:text-red-400 tabular-nums"><?= $highCount ?></p>
                </div>
            </div>
        </button>
        <button type="button" onclick="changeAlertFilter('Medium')" id="card-medium" class="mismatch-stat-card text-left bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-amber-400/50 transition-colors <?= $alert_filter === 'Medium' ? 'ring-2 ring-amber-400/40' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 mismatch-no-print">
                    <span class="material-symbols-outlined text-xl">warning</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Medium · 4–24h</p>
                    <p id="stat-medium" class="text-2xl lg:text-3xl font-black text-amber-600 dark:text-amber-400 tabular-nums"><?= $mediumCount ?></p>
                </div>
            </div>
        </button>
        <button type="button" onclick="changeAlertFilter('Low')" id="card-low" class="mismatch-stat-card text-left bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-blue-400/50 transition-colors <?= $alert_filter === 'Low' ? 'ring-2 ring-blue-400/40' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 mismatch-no-print">
                    <span class="material-symbols-outlined text-xl">info</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Low · under 4h</p>
                    <p id="stat-low" class="text-2xl lg:text-3xl font-black text-blue-600 dark:text-blue-400 tabular-nums"><?= $lowCount ?></p>
                </div>
            </div>
        </button>
    </div>

    <div class="mismatch-table-wrap bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="mismatch-screen-table-head flex flex-wrap items-center justify-between gap-3 px-4 lg:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-3 flex-1 min-w-0">
                <div class="relative w-full sm:w-44">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none">filter_list</span>
                    <select id="type-filter" onchange="changeTypeFilter(this.value)" class="w-full h-10 appearance-none rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 pl-10 pr-8 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/50 cursor-pointer">
                        <option value="" <?= $type_filter === null ? 'selected' : '' ?>>All Types</option>
                        <option value="product" <?= $type_filter === 'product' ? 'selected' : '' ?>>Products</option>
                        <option value="raw_material" <?= $type_filter === 'raw_material' ? 'selected' : '' ?>>Raw Materials</option>
                    </select>
                </div>
                <div class="relative w-full sm:w-40">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none">priority_high</span>
                    <select id="alert-filter" onchange="changeAlertFilter(this.value)" class="w-full h-10 appearance-none rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 pl-10 pr-8 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/50 cursor-pointer">
                        <option value="" <?= $alert_filter === null ? 'selected' : '' ?>>All Alerts</option>
                        <option value="High" <?= $alert_filter === 'High' ? 'selected' : '' ?>>High</option>
                        <option value="Medium" <?= $alert_filter === 'Medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="Low" <?= $alert_filter === 'Low' ? 'selected' : '' ?>>Low</option>
                    </select>
                </div>
                <div class="relative flex-1 min-w-[200px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none">search</span>
                    <input id="search-input" onkeyup="filterMismatch()" class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 pl-10 pr-4 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/50 placeholder:text-gray-400" placeholder="Search code, name, batch, location..." type="text"/>
                </div>
            </div>
            <span id="row-count" class="text-xs font-bold px-2.5 py-1 bg-primary/10 text-primary rounded-full whitespace-nowrap"><?= $totalMismatches ?> records</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1100px]" id="mismatch-table">
                <thead class="bg-gray-50 dark:bg-gray-800/90">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Type</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Code</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Item Name</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Batch Number</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">Qty</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Store Location</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Mismatch Location</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Date &amp; Time</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Alert Status</th>
                    </tr>
                </thead>
                <tbody id="mismatch-body" class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                    <?php if (empty($rows)): ?>
                        <tr id="mismatch-empty">
                            <td colspan="9" class="px-6 py-16 text-center">
                                <span class="material-symbols-outlined text-5xl text-green-500/60 dark:text-green-400/50 block mb-3 mismatch-no-print">check_circle</span>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">No location mismatches found</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">All tracked items are in their configured store locations</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $search = strtolower(trim(
                                ($row['type_label'] ?? '') . ' ' .
                                ($row['code'] ?? '') . ' ' .
                                ($row['name'] ?? '') . ' ' .
                                ($row['batch_number'] ?? '') . ' ' .
                                ($row['store_location'] ?? '') . ' ' .
                                ($row['mismatch_location'] ?? '') . ' ' .
                                ($row['alert_status'] ?? '')
                            ));
                            $alert = $row['alert_status'] ?? 'Low';
                            if ($alert === 'High') {
                                $alertClass = 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300';
                            } elseif ($alert === 'Medium') {
                                $alertClass = 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300';
                            } else {
                                $alertClass = 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300';
                            }
                            ?>
                            <tr class="mismatch-row hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors" data-search="<?= esc($search) ?>">
                                <td class="px-3 py-2.5">
                                    <?php if (($row['item_type'] ?? '') === 'product'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300">Product</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300">Raw Material</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="text-xs font-mono font-bold text-primary dark:text-blue-400"><?= esc($row['code']) ?></span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($row['name']) ?></span>
                                    <?php if (!empty($row['unit'])): ?>
                                        <span class="block text-[10px] font-medium text-gray-400 dark:text-gray-500"><?= esc($row['unit']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2.5 text-sm font-mono text-gray-700 dark:text-gray-300">
                                    <?= ($row['batch_number'] ?? '') !== '' ? esc($row['batch_number']) : '—' ?>
                                </td>
                                <td class="px-3 py-2.5 text-sm tabular-nums text-right font-bold text-gray-900 dark:text-white">
                                    <?= esc($row['qty_fmt'] ?? format_inventory_qty($row['qty'])) ?>
                                </td>
                                <td class="px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300">
                                    <?= esc($row['store_location']) ?>
                                </td>
                                <td class="px-3 py-2.5 text-sm font-medium text-gray-900 dark:text-white">
                                    <?= esc($row['mismatch_location']) ?>
                                </td>
                                <td class="px-3 py-2.5 text-xs tabular-nums text-gray-600 dark:text-gray-300">
                                    <?= esc($row['detected_at'] ?: '—') ?>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold <?= $alertClass ?>">
                                        <?= esc($alert) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
    .mismatch-page,
    .mismatch-table-wrap,
    .overflow-x-auto,
    #mismatch-table,
    #mismatch-table thead,
    #mismatch-table tbody {
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

    #app-mobile-header,
    #sidebar,
    #mobile-overlay,
    #stock-finder-btn,
    #stock-finder-panel,
    #analytics-floating-btn,
    #analytics-chat-widget,
    footer,
    .mismatch-no-print,
    .mismatch-screen-table-head,
    button {
        display: none !important;
    }

    .layout-container,
    main,
    .mismatch-page {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: visible !important;
        gap: 0 !important;
    }

    .mismatch-print-header {
        display: block !important;
    }

    .mismatch-stats {
        display: flex !important;
        flex-direction: row !important;
        gap: 8px !important;
        margin-bottom: 10px !important;
    }

    .mismatch-stats .mismatch-stat-card {
        display: block !important;
    }

    .mismatch-stat-card {
        flex: 1 !important;
        padding: 8px 10px !important;
        border: 1px solid #ccc !important;
        border-radius: 4px !important;
        box-shadow: none !important;
        background: #fff !important;
        ring: none !important;
    }

    .mismatch-stat-card p {
        color: #333 !important;
    }

    .mismatch-stat-card .text-2xl,
    .mismatch-stat-card .text-3xl,
    .mismatch-stat-card .font-black {
        font-size: 16px !important;
        line-height: 1.2 !important;
        color: #000 !important;
    }

    .mismatch-stat-card .text-red-600,
    .mismatch-stat-card .text-amber-600,
    .mismatch-stat-card .text-blue-600 {
        color: #000 !important;
    }

    .mismatch-table-wrap {
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        overflow: visible !important;
    }

    .overflow-x-auto {
        overflow: visible !important;
    }

    #mismatch-table {
        min-width: 0 !important;
        width: 100% !important;
        font-size: 8px !important;
        border-collapse: collapse !important;
    }

    #mismatch-table thead {
        display: table-header-group;
    }

    #mismatch-table th {
        background: #e8e8e8 !important;
        color: #000 !important;
        padding: 4px 3px !important;
        border: 1px solid #999 !important;
        font-size: 7px !important;
        white-space: nowrap;
    }

    #mismatch-table td {
        padding: 3px 3px !important;
        border: 1px solid #ccc !important;
        color: #000 !important;
        background: #fff !important;
        vertical-align: top;
    }

    #mismatch-body tr.mismatch-row {
        page-break-inside: avoid;
        border-bottom: 1px solid #ddd !important;
    }

    #mismatch-body tr.mismatch-row.print-show {
        display: table-row !important;
    }

    #mismatch-table .text-primary,
    #mismatch-table .text-red-600,
    #mismatch-table .text-green-500,
    #mismatch-table .text-blue-700,
    #mismatch-table .text-amber-700 {
        color: #000 !important;
    }

    #mismatch-table span.inline-flex {
        background: #eee !important;
        color: #000 !important;
        border: 1px solid #bbb !important;
        padding: 1px 4px !important;
    }

    #mismatch-empty .material-symbols-outlined {
        display: none !important;
    }
}
</style>

<script>
let mismatchRows = <?= json_encode($rows ?? []) ?>;
let mismatchAlertCounts = <?= json_encode($alert_counts ?? ['High' => 0, 'Medium' => 0, 'Low' => 0]) ?>;
let currentTypeFilter = <?= json_encode($type_filter) ?>;
let currentAlertFilter = <?= json_encode($alert_filter) ?>;
let isUpdating = false;
let updateInterval;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text == null ? '' : String(text);
    return div.innerHTML;
}

function formatInventoryQty(n) {
    const v = Number(n) || 0;
    if (Math.abs(v - Math.round(v)) < 1e-9) return String(Math.round(v));
    return v.toFixed(3).replace(/\.?0+$/, '');
}

function alertClass(status) {
    if (status === 'High') return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300';
    if (status === 'Medium') return 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300';
    return 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300';
}

function rowSearchKey(row) {
    return [
        row.type_label, row.code, row.name, row.batch_number,
        row.store_location, row.mismatch_location, row.alert_status,
    ].join(' ').toLowerCase();
}

function buildPrintFilterSummary() {
    const parts = [];
    if (currentTypeFilter === 'product') parts.push('Products');
    else if (currentTypeFilter === 'raw_material') parts.push('Raw Materials');
    if (currentAlertFilter) parts.push(currentAlertFilter + ' alert');
    const base = parts.length ? parts.join(' · ') : 'All types · All alerts';
    const q = (document.getElementById('search-input')?.value || '').trim();
    return q ? base + ' · Search: "' + q + '"' : base;
}

function paintMismatchTable() {
    const tbody = document.getElementById('mismatch-body');
    if (!tbody) return;

    if (!mismatchRows.length) {
        tbody.innerHTML = `
            <tr id="mismatch-empty">
                <td colspan="9" class="px-6 py-16 text-center">
                    <span class="material-symbols-outlined text-5xl text-green-500/60 dark:text-green-400/50 block mb-3 mismatch-no-print">check_circle</span>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">No location mismatches found</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">All tracked items are in their configured store locations</p>
                </td>
            </tr>`;
        filterMismatch();
        return;
    }

    tbody.innerHTML = mismatchRows.map((row) => {
        const isProduct = row.item_type === 'product';
        const typeBadge = isProduct
            ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300">Product</span>'
            : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300">Raw Material</span>';
        const batch = row.batch_number ? escapeHtml(row.batch_number) : '—';
        const qty = escapeHtml(row.qty_fmt || formatInventoryQty(row.qty));
        const unit = row.unit ? `<span class="block text-[10px] font-medium text-gray-400 dark:text-gray-500">${escapeHtml(row.unit)}</span>` : '';
        const alert = row.alert_status || 'Low';

        return `
            <tr class="mismatch-row hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors" data-search="${escapeHtml(rowSearchKey(row))}">
                <td class="px-3 py-2.5">${typeBadge}</td>
                <td class="px-3 py-2.5"><span class="text-xs font-mono font-bold text-primary dark:text-blue-400">${escapeHtml(row.code)}</span></td>
                <td class="px-3 py-2.5">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">${escapeHtml(row.name)}</span>
                    ${unit}
                </td>
                <td class="px-3 py-2.5 text-sm font-mono text-gray-700 dark:text-gray-300">${batch}</td>
                <td class="px-3 py-2.5 text-sm tabular-nums text-right font-bold text-gray-900 dark:text-white">${qty}</td>
                <td class="px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300">${escapeHtml(row.store_location)}</td>
                <td class="px-3 py-2.5 text-sm font-medium text-gray-900 dark:text-white">${escapeHtml(row.mismatch_location)}</td>
                <td class="px-3 py-2.5 text-xs tabular-nums text-gray-600 dark:text-gray-300">${escapeHtml(row.detected_at || '—')}</td>
                <td class="px-3 py-2.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold ${alertClass(alert)}">${escapeHtml(alert)}</span>
                </td>
            </tr>`;
    }).join('');

    filterMismatch();
}

function updateMismatchStats() {
    const counts = mismatchAlertCounts || {};
    const totalAll = (counts.High || 0) + (counts.Medium || 0) + (counts.Low || 0);
    const visible = document.querySelectorAll('#mismatch-body tr.mismatch-row:not(.hidden)').length;

    const statHigh = document.getElementById('stat-high');
    const statMedium = document.getElementById('stat-medium');
    const statLow = document.getElementById('stat-low');
    if (statHigh) statHigh.textContent = String(counts.High || 0);
    if (statMedium) statMedium.textContent = String(counts.Medium || 0);
    if (statLow) statLow.textContent = String(counts.Low || 0);

    const statRows = document.getElementById('stat-rows');
    if (statRows) {
        statRows.textContent = currentAlertFilter ? String(mismatchRows.length) : String(totalAll);
    }
}

function updateAlertCardRings() {
    const cards = {
        '': 'card-all',
        High: 'card-high',
        Medium: 'card-medium',
        Low: 'card-low',
    };
    Object.entries(cards).forEach(([filter, id]) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('ring-2', 'ring-primary/30', 'ring-red-400/40', 'ring-amber-400/40', 'ring-blue-400/40');
        const active = (currentAlertFilter || '') === filter;
        if (!active) return;
        if (filter === '') el.classList.add('ring-2', 'ring-primary/30');
        else if (filter === 'High') el.classList.add('ring-2', 'ring-red-400/40');
        else if (filter === 'Medium') el.classList.add('ring-2', 'ring-amber-400/40');
        else if (filter === 'Low') el.classList.add('ring-2', 'ring-blue-400/40');
    });
}

function syncFilterControls() {
    const typeEl = document.getElementById('type-filter');
    const alertEl = document.getElementById('alert-filter');
    if (typeEl) typeEl.value = currentTypeFilter || '';
    if (alertEl) alertEl.value = currentAlertFilter || '';
}

function updatePageUrl() {
    const url = new URL(window.location.href);
    if (currentTypeFilter) url.searchParams.set('type', currentTypeFilter);
    else url.searchParams.delete('type');
    if (currentAlertFilter) url.searchParams.set('alert', currentAlertFilter);
    else url.searchParams.delete('alert');
    window.history.replaceState({}, '', url.toString());
}

function getMismatchDataUrl() {
    const url = new URL('<?= base_url('inventory/location-mismatch-data') ?>', window.location.origin);
    if (currentTypeFilter) url.searchParams.set('type', currentTypeFilter);
    if (currentAlertFilter) url.searchParams.set('alert', currentAlertFilter);
    url.searchParams.set('_', String(Date.now()));
    return url.toString();
}

function changeTypeFilter(value) {
    currentTypeFilter = value || null;
    updatePageUrl();
    syncFilterControls();
    refreshMismatchData();
}

function changeAlertFilter(value) {
    currentAlertFilter = value || null;
    updatePageUrl();
    syncFilterControls();
    updateAlertCardRings();
    refreshMismatchData();
}

function filterMismatch() {
    const q = (document.getElementById('search-input').value || '').trim().toLowerCase();
    const rows = document.querySelectorAll('#mismatch-body tr.mismatch-row');
    let visible = 0;

    rows.forEach((row) => {
        const hay = row.getAttribute('data-search') || '';
        const show = !q || hay.includes(q);
        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });

    const empty = document.getElementById('mismatch-empty');
    if (empty) {
        empty.classList.toggle('hidden', visible > 0);
    }

    const countEl = document.getElementById('row-count');
    if (countEl) {
        countEl.textContent = visible + ' records';
    }
}

async function refreshMismatchData() {
    if (isUpdating) return;
    isUpdating = true;
    try {
        const res = await fetch(getMismatchDataUrl(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store',
        });
        const data = await res.json();
        if (!data.success) return;

        mismatchRows = data.rows || [];
        mismatchAlertCounts = data.alert_counts || { High: 0, Medium: 0, Low: 0 };

        paintMismatchTable();
        updateMismatchStats();

        const updated = document.getElementById('last-updated-time');
        if (updated && data.last_updated) {
            updated.textContent = data.last_updated;
        }
    } catch (e) {
        console.error('Failed to refresh location mismatches', e);
    } finally {
        isUpdating = false;
    }
}

function manualRefresh() { refreshMismatchData(); }

window.addEventListener('beforeprint', () => {
    const summary = document.getElementById('print-filter-summary');
    if (summary) summary.textContent = buildPrintFilterSummary();
    document.querySelectorAll('#mismatch-body tr.mismatch-row').forEach((row) => {
        row.classList.add('print-show');
        row.classList.remove('hidden');
    });
});

window.addEventListener('afterprint', () => {
    document.querySelectorAll('#mismatch-body tr.mismatch-row').forEach((row) => {
        row.classList.remove('print-show');
    });
    filterMismatch();
});

document.addEventListener('DOMContentLoaded', () => {
    updateAlertCardRings();
    updateInterval = setInterval(refreshMismatchData, 5000);
});
</script>

<?= $this->include('templates/footer') ?>
