<?= $this->include('templates/header') ?>

<?php
$totalMismatches = count($rows);
$highCount   = (int) ($alert_counts['High'] ?? 0);
$mediumCount = (int) ($alert_counts['Medium'] ?? 0);
$lowCount    = (int) ($alert_counts['Low'] ?? 0);
?>

<div class="flex flex-col gap-6 px-2 sm:px-4 py-2 sm:py-4">
    <div class="flex flex-wrap items-start justify-between gap-4 mt-4 md:mt-2">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Inventory Location Mismatch Monitoring</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Items detected outside their configured store locations</p>
        </div>
        <div class="flex items-center gap-2 print:hidden">
            <span class="text-xs text-gray-400 dark:text-gray-500 tabular-nums">Updated <?= esc($as_of_date) ?></span>
            <button onclick="window.location.reload()" class="flex items-center justify-center gap-1.5 h-9 px-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <span class="material-symbols-outlined text-base leading-none">refresh</span>
                Refresh
            </button>
            <button onclick="window.print()" class="flex items-center justify-center gap-1.5 h-9 px-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <span class="material-symbols-outlined text-base leading-none">print</span>
                Print
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <button type="button" onclick="changeAlertFilter('')" class="text-left bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary/40 transition-colors <?= $alert_filter === null ? 'ring-2 ring-primary/30' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400">
                    <span class="material-symbols-outlined text-xl">wrong_location</span>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Mismatches</p>
                    <p id="stat-rows" class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums"><?= $totalMismatches ?></p>
                </div>
            </div>
        </button>
        <button type="button" onclick="changeAlertFilter('High')" class="text-left bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-red-400/50 transition-colors <?= $alert_filter === 'High' ? 'ring-2 ring-red-400/40' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400">
                    <span class="material-symbols-outlined text-xl">error</span>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">High · 24h+</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 tabular-nums"><?= $highCount ?></p>
                </div>
            </div>
        </button>
        <button type="button" onclick="changeAlertFilter('Medium')" class="text-left bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-amber-400/50 transition-colors <?= $alert_filter === 'Medium' ? 'ring-2 ring-amber-400/40' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400">
                    <span class="material-symbols-outlined text-xl">warning</span>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Medium · 4–24h</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 tabular-nums"><?= $mediumCount ?></p>
                </div>
            </div>
        </button>
        <button type="button" onclick="changeAlertFilter('Low')" class="text-left bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-blue-400/50 transition-colors <?= $alert_filter === 'Low' ? 'ring-2 ring-blue-400/40' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                    <span class="material-symbols-outlined text-xl">info</span>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Low · under 4h</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 tabular-nums"><?= $lowCount ?></p>
                </div>
            </div>
        </button>
    </div>

    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 lg:px-6 py-4 border-b border-gray-200 dark:border-gray-700 print:hidden">
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
            <span id="row-count" class="text-xs font-semibold px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-full whitespace-nowrap"><?= $totalMismatches ?> records</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1100px]" id="mismatch-table">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item Name</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Batch Number</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Qty</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Store Location</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mismatch Location</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date &amp; Time</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alert Status</th>
                    </tr>
                </thead>
                <tbody id="mismatch-body" class="divide-y divide-gray-100 dark:divide-gray-800">
                    <?php if (empty($rows)): ?>
                        <tr id="mismatch-empty">
                            <td colspan="9" class="px-6 py-16 text-center">
                                <span class="material-symbols-outlined text-5xl text-green-500/60 dark:text-green-400/50 block mb-3">check_circle</span>
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
                                <td class="px-4 py-3">
                                    <?php if (($row['item_type'] ?? '') === 'product'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300">Product</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300">Raw Material</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-mono font-medium text-primary"><?= esc($row['code']) ?></span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?= esc($row['name']) ?></p>
                                    <?php if (!empty($row['unit'])): ?>
                                        <p class="text-xs text-gray-400 dark:text-gray-500"><?= esc($row['unit']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-600 dark:text-gray-300">
                                    <?= ($row['batch_number'] ?? '') !== '' ? esc($row['batch_number']) : '—' ?>
                                </td>
                                <td class="px-4 py-3 text-sm tabular-nums text-right font-semibold text-gray-900 dark:text-white">
                                    <?= format_inventory_qty($row['qty']) ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-gray-300">
                                        <span class="material-symbols-outlined text-base text-green-500">location_on</span>
                                        <?= esc($row['store_location']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 text-sm font-medium text-red-600 dark:text-red-400">
                                        <span class="material-symbols-outlined text-base">wrong_location</span>
                                        <?= esc($row['mismatch_location']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs tabular-nums text-gray-500 dark:text-gray-400">
                                    <?= esc($row['detected_at'] ?: '—') ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold <?= $alertClass ?>">
                                        <?= esc($alert) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="mismatch-empty" class="hidden">
                            <td colspan="9" class="px-6 py-12 text-center text-gray-400 text-sm">No matching records.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    aside, header, nav, .print\\:hidden, button, #type-filter, #alert-filter, #search-input {
        display: none !important;
    }
    main, .flex.flex-col.gap-6 {
        padding: 0 !important;
        margin: 0 !important;
    }
    body {
        background: white !important;
    }
}
</style>

<script>
function changeTypeFilter(value) {
    const url = new URL(window.location.href);
    if (value) {
        url.searchParams.set('type', value);
    } else {
        url.searchParams.delete('type');
    }
    window.location.href = url.toString();
}

function changeAlertFilter(value) {
    const url = new URL(window.location.href);
    if (value) {
        url.searchParams.set('alert', value);
    } else {
        url.searchParams.delete('alert');
    }
    window.location.href = url.toString();
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

    const statRows = document.getElementById('stat-rows');
    if (statRows) {
        statRows.textContent = String(visible);
    }
}
</script>

<?= $this->include('templates/footer') ?>
