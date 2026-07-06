<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6 px-2 sm:px-4 py-2 sm:py-4">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mt-4 md:mt-2">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined text-3xl">menu_book</span>
            </div>
            <div>
                <h1 class="text-2xl lg:text-3xl font-extrabold leading-tight tracking-tight text-gray-900 dark:text-white uppercase">Stock Ledger</h1>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-1">Batch balances · Start / In / Out / Running</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
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
            <div class="flex items-center h-9 px-4 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <span class="text-sm font-black text-gray-900 dark:text-white tabular-nums tracking-tight"><?= esc($as_of_date) ?></span>
            </div>
            <button onclick="window.print()" class="flex items-center justify-center gap-2 h-9 px-4 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors whitespace-nowrap print:hidden">
                <span class="material-symbols-outlined text-base leading-none">print</span>
                Print
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-background-dark p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ledger Rows</p>
                <span class="material-symbols-outlined text-primary">table_rows</span>
            </div>
            <p id="stat-rows" class="text-3xl font-black text-gray-900 dark:text-white tabular-nums"><?= count($rows) ?></p>
        </div>
        <div class="bg-white dark:bg-background-dark p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</p>
                <span class="material-symbols-outlined text-blue-500">inventory_2</span>
            </div>
            <p class="text-3xl font-black text-gray-900 dark:text-white tabular-nums"><?= count(array_filter($rows, static fn ($r) => !empty($r['show_total']))) ?></p>
        </div>
        <div class="bg-white dark:bg-background-dark p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm col-span-2">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Inventory</p>
                <span class="material-symbols-outlined text-indigo-500">inventory</span>
            </div>
            <p class="text-3xl font-black text-gray-900 dark:text-white tabular-nums"><?= format_inventory_qty($grand_total) ?></p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Sum of all running balances</p>
        </div>
    </div>

    <div class="bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
        <div class="px-4 lg:px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-primary/10 p-2 rounded-lg">
                    <span class="material-symbols-outlined text-primary">menu_book</span>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">2. Stock Ledger</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">As of <?= esc($as_of_date) ?></p>
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
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide">Batch Number</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">Start Balance</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">Qty IN</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">Qty OUT</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">Running Balance</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">Total Inventory</th>
                        <th class="px-3 py-3 text-xs font-black text-gray-500 dark:text-gray-300 uppercase tracking-wide text-right">
                            Last Transaction
                            <span class="block font-semibold normal-case tracking-normal text-[10px] text-gray-400 dark:text-gray-500">Date &amp; Time</span>
                        </th>
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
                                <td class="px-3 py-2.5 text-sm font-semibold text-gray-900 dark:text-white">
                                    <?= esc($row['name']) ?>
                                    <?php if (!empty($row['unit'])): ?>
                                        <span class="block text-[10px] font-medium text-gray-400 dark:text-gray-500"><?= esc($row['unit']) ?></span>
                                    <?php endif; ?>
                                </td>
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
                                <td class="px-3 py-2.5 text-sm tabular-nums text-right font-black text-indigo-700 dark:text-indigo-300">
                                    <?= !empty($row['show_total']) ? format_inventory_qty($row['total_inventory']) : '' ?>
                                </td>
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
    aside, header, nav, .print\\:hidden, button, #type-filter, #search-input {
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
</script>

<?= $this->include('templates/footer') ?>
