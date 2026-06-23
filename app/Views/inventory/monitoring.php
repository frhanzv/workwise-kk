<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6 px-2 sm:px-4 py-2 sm:py-4">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mt-4 md:mt-2">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined text-3xl">sensors</span>
            </div>
            <div>
                <h1 class="text-2xl lg:text-3xl font-extrabold leading-tight tracking-tight text-gray-900 dark:text-white uppercase">Inventory Monitoring</h1>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-1">Live RFID Tracking</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-52">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-lg pointer-events-none">location_on</span>
                <select id="zone-filter" onchange="changeZone(this.value)" class="w-full appearance-none rounded-lg border-none bg-gray-100 dark:bg-gray-800 py-2 pl-10 pr-8 text-sm font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/50 transition-all cursor-pointer">
                    <option value="">All Zones</option>
                    <?php foreach ($zones as $zone): ?>
                        <option value="<?= esc($zone['zone_id']) ?>" <?= ($selected_zone_id === $zone['zone_id']) ? 'selected' : '' ?>><?= esc($zone['zone_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="relative">
                <button id="dateFilterBtn" onclick="toggleDateFilter()" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-700 whitespace-nowrap">
                    <span class="material-symbols-outlined text-lg">calendar_today</span>
                    <span id="dateFilterLabel"><?= esc($filter_label) ?></span>
                    <span class="material-symbols-outlined text-lg">expand_more</span>
                </button>
                <div id="dateFilterDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50">
                    <div class="py-1">
                        <button onclick="filterByDate('today')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Today</button>
                        <button onclick="filterByDate('yesterday')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Yesterday</button>
                        <button onclick="filterByDate('week')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">This Week</button>
                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                        <div class="px-4 py-3 pb-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Custom Date</label>
                            <input type="date" id="customDate" value="<?= esc($custom_date ?? '') ?>" onchange="filterByCustomDate()" class="w-full px-3 py-2 text-sm bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">search</span>
                <input id="search-input" onkeyup="filterTables()" class="w-full rounded-lg border-none bg-gray-100 dark:bg-gray-800 py-2 pl-10 pr-4 text-sm font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/50 transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500" placeholder="Search code or name..." type="text"/>
            </div>
            <div class="flex items-center gap-3 px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <span id="current-time" class="text-sm font-black text-gray-900 dark:text-white tabular-nums tracking-tight"></span>
            </div>
            <button onclick="manualRefresh()" class="flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-primary/90 transition-colors">
                <span class="material-symbols-outlined text-base">refresh</span>
                Refresh
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-background-dark p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer hover:border-blue-400/50 hover:shadow-md transition-all" onclick="openProductsStatModal()">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Products</p>
                <span class="material-symbols-outlined text-blue-500">inventory_2</span>
            </div>
            <p id="stat-products" class="text-3xl font-black text-gray-900 dark:text-white tabular-nums"><?= esc($stats['products']) ?></p>
            <p class="text-xs text-gray-400 mt-1" data-stat-period>Scanned <?= esc($filter_label) ?></p>
        </div>
        <div class="bg-white dark:bg-background-dark p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer hover:border-amber-400/50 hover:shadow-md transition-all" onclick="openMaterialsStatModal()">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Raw Materials</p>
                <span class="material-symbols-outlined text-amber-500">category</span>
            </div>
            <p id="stat-materials" class="text-3xl font-black text-gray-900 dark:text-white tabular-nums"><?= esc($stats['materials']) ?></p>
            <p class="text-xs text-gray-400 mt-1" data-stat-period>Scanned <?= esc($filter_label) ?></p>
        </div>
        <div class="bg-white dark:bg-background-dark p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer hover:border-primary/50 hover:shadow-md transition-all" onclick="openTotalStatModal()">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Tracked</p>
                <span class="material-symbols-outlined text-primary">radar</span>
            </div>
            <p id="stat-total" class="text-3xl font-black text-gray-900 dark:text-white tabular-nums"><?= esc($stats['total']) ?></p>
            <p class="text-xs text-gray-400 mt-1">Products + materials</p>
        </div>
        <div class="bg-white dark:bg-background-dark p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer hover:border-green-400/50 hover:shadow-md transition-all" onclick="openScansStatModal()">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider" id="stat-today-label">Scans</p>
                <span class="material-symbols-outlined text-green-500">today</span>
            </div>
            <p id="stat-today" class="text-3xl font-black text-gray-900 dark:text-white tabular-nums"><?= esc($stats['scanned_today']) ?></p>
            <p id="stat-today-sub" class="text-xs text-gray-400 mt-1"><?= esc($filter_label) ?></p>
        </div>
    </div>

    <!-- Recent Scans -->
    <div class="bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
        <div class="px-4 lg:px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-primary/10 p-2 rounded-lg">
                    <span class="material-symbols-outlined text-primary">history</span>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">Recent Scans</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Last Updated: <span id="last-updated-time"><?= esc($last_updated) ?></span></p>
                </div>
            </div>
            <span id="recent-count" class="text-xs font-bold px-2.5 py-1 bg-primary/10 text-primary rounded-full"><?= count($recent_scans) ?> Items</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="recent-table">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                        <?php
                        $invSortTh = 'px-4 lg:px-6 py-3 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer select-none hover:text-primary transition-colors';
                        $invSortIcon = '<span class="material-symbols-outlined sort-icon text-sm opacity-70">unfold_more</span>';
                        $invSortWrap = fn($label, $col) => '<th data-sort="' . $col . '" class="' . $invSortTh . '"><span class="inline-flex items-center gap-0.5">' . $label . $invSortIcon . '</span></th>';
                        ?>
                        <?= $invSortWrap('Type', 'type') ?>
                        <?= $invSortWrap('Raw Material/Product ID', 'code') ?>
                        <?= $invSortWrap('Name', 'name') ?>
                        <?= $invSortWrap('Zone', 'zone') ?>
                        <?= $invSortWrap('Status', 'status') ?>
                        <?= $invSortWrap('Time In', 'time_in') ?>
                        <?= $invSortWrap('Time Out', 'time_out') ?>
                        <?= $invSortWrap('Duration', 'duration') ?>
                    </tr>
                </thead>
                <tbody id="recent-body" class="divide-y divide-gray-100 dark:divide-gray-800">
                    <?= $this->include('inventory/_activity_rows', ['activity_logs' => $recent_scans]) ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Products & Raw Materials -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div id="products-section" class="bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
            <div class="px-4 lg:px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-100 dark:bg-blue-900/20 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">inventory_2</span>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">Products</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Scanned <?= esc($filter_label) ?></p>
                    </div>
                </div>
                <span data-product-count class="text-xs font-bold px-2.5 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-full"><?= count($products) ?></span>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-left border-collapse" id="products-table">
                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800/90">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <?php
                            $sideSortTh = 'px-4 py-3 text-xs font-black text-gray-500 uppercase cursor-pointer select-none hover:text-primary transition-colors';
                            $sideSortIcon = '<span class="material-symbols-outlined sort-icon text-sm opacity-70">unfold_more</span>';
                            $sideSortWrap = fn($label, $col) => '<th data-sort="' . $col . '" class="' . $sideSortTh . '"><span class="inline-flex items-center gap-0.5">' . $label . $sideSortIcon . '</span></th>';
                            ?>
                            <?= $sideSortWrap('Product ID', 'code') ?>
                            <?= $sideSortWrap('Name', 'name') ?>
                            <?= $sideSortWrap('Status', 'status') ?>
                            <?= $sideSortWrap('Duration', 'duration') ?>
                        </tr>
                    </thead>
                    <tbody id="products-body" class="divide-y divide-gray-100 dark:divide-gray-800">
                        <?= $this->include('inventory/_product_rows', ['products' => $products]) ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="materials-section" class="bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
            <div class="px-4 lg:px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-amber-100 dark:bg-amber-900/20 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-amber-600 dark:text-amber-400">category</span>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">Raw Materials</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Scanned <?= esc($filter_label) ?></p>
                    </div>
                </div>
                <span data-material-count class="text-xs font-bold px-2.5 py-1 bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 rounded-full"><?= count($materials) ?></span>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-left border-collapse" id="materials-table">
                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800/90">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <?= $sideSortWrap('Raw Material ID', 'code') ?>
                            <?= $sideSortWrap('Name', 'name') ?>
                            <?= $sideSortWrap('Status', 'status') ?>
                            <?= $sideSortWrap('Duration', 'duration') ?>
                        </tr>
                    </thead>
                    <tbody id="materials-body" class="divide-y divide-gray-100 dark:divide-gray-800">
                        <?= $this->include('inventory/_material_rows', ['materials' => $materials]) ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex items-center gap-3 p-4 rounded-lg bg-gray-100 dark:bg-gray-800/30 border border-gray-200 dark:border-gray-700/50">
            <span class="material-symbols-outlined text-primary text-2xl">router</span>
            <div>
                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wide">Reader Status</p>
                <p class="text-xs font-black text-gray-900 dark:text-white uppercase"><?= esc($active_readers) ?> ACTIVE / <?= max($total_readers - $active_readers, 0) ?> OFFLINE</p>
            </div>
        </div>
        <div class="flex items-center gap-3 p-4 rounded-lg bg-gray-100 dark:bg-gray-800/30 border border-gray-200 dark:border-gray-700/50">
            <span class="material-symbols-outlined text-green-500 text-2xl">circle</span>
            <div>
                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wide">Live Updates</p>
                <p class="text-xs font-black text-gray-900 dark:text-white uppercase">Auto-refresh every 5 seconds</p>
            </div>
        </div>
    </div>
</div>

<!-- Stat List Modal -->
<div id="listModal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/60 backdrop-blur-sm" onclick="closeListModal(event)">
    <div class="bg-white dark:bg-background-dark rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh] flex flex-col border border-gray-200 dark:border-gray-700 animate-modal" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <div>
                <h3 id="listModalTitle" class="text-gray-900 dark:text-white font-bold text-base leading-tight"></h3>
                <p id="listModalSubtitle" class="text-gray-500 dark:text-gray-400 text-xs mt-0.5"></p>
            </div>
            <button onclick="closeListModal()" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-5 py-3">
            <div id="listModalBody"></div>
        </div>
    </div>
</div>

<!-- Item Detail Modal -->
<div id="itemModal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/60 backdrop-blur-sm" onclick="closeItemModal(event)">
    <div class="bg-white dark:bg-background-dark rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col border border-gray-200 dark:border-gray-700 animate-modal" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div id="itemModalIcon" class="p-2 bg-primary/10 dark:bg-primary/20 rounded-xl">
                    <span class="material-symbols-outlined text-primary text-xl">inventory_2</span>
                </div>
                <div>
                    <h3 id="itemModalName" class="text-gray-900 dark:text-white font-bold text-base leading-tight"></h3>
                    <p id="itemModalMeta" class="text-gray-500 dark:text-gray-400 text-xs mt-0.5"></p>
                </div>
            </div>
            <button onclick="closeItemModal()" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <div id="itemModalLoading" class="hidden px-5 py-10 text-center text-gray-400">
            <span class="material-symbols-outlined text-3xl animate-spin">progress_activity</span>
            <p class="text-sm mt-2">Loading details...</p>
        </div>

        <div id="itemModalContent" class="flex-1 overflow-y-auto">
            <div id="itemModalDetails" class="grid grid-cols-2 gap-3 px-5 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40"></div>

            <div class="px-5 py-4">
                <p id="itemModalScanTitle" class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Zone Activity</p>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-400 border-b border-gray-100 dark:border-gray-700">
                            <th class="pb-2 text-left font-medium">Zone</th>
                            <th class="pb-2 text-left font-medium">Status</th>
                            <th class="pb-2 text-left font-medium">Time In</th>
                            <th class="pb-2 text-left font-medium">Time Out</th>
                            <th class="pb-2 text-left font-medium">Duration</th>
                        </tr>
                    </thead>
                    <tbody id="itemModalScansBody" class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs"></tbody>
                </table>
                <p id="itemModalNoScans" class="hidden text-sm text-gray-400 text-center py-4">No zone scans for this period.</p>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800/40 rounded-b-2xl">
            <span id="itemModalPeriod" class="text-xs text-gray-400"></span>
            <a id="itemModalEditLink" href="#"
               class="flex items-center gap-1.5 px-4 py-2 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary/90 transition-colors">
                <span class="material-symbols-outlined text-sm">edit</span>
                Edit Item
            </a>
        </div>
    </div>
</div>

<style>
@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(16px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-modal { animation: modalSlideUp 0.18s ease-out forwards; }
</style>

<script src="<?= base_url('assets/js/sortable-table.js') ?>"></script>
<script>
let updateInterval;
let isUpdating = false;
let serverTimeOffset = <?= time() ?> - Math.floor(Date.now() / 1000);
let currentFilterType = <?= json_encode($filter_type ?? 'today') ?>;
let currentCustomDate = <?= json_encode($custom_date ?? '') ?>;
let monitoringModalData = {
    filterLabel: <?= json_encode($filter_label) ?>,
    products: <?= json_encode($products ?? []) ?>,
    materials: <?= json_encode($materials ?? []) ?>,
    allScans: <?= json_encode($all_scans ?? []) ?>,
};
let recentScansData = <?= json_encode($recent_scans ?? []) ?>;
const recentSortState = { column: 'time_in', dir: 'desc' };
const productsSortState = { column: 'duration', dir: 'desc' };
const materialsSortState = { column: 'duration', dir: 'desc' };

function sortRecentScans(scans) {
    if (!scans || !scans.length) return scans || [];
    const { column, dir } = recentSortState;
    return sortBy(scans, (s) => {
        switch (column) {
            case 'type': return s.type_label || s.type;
            case 'code': return s.code;
            case 'name': return s.name;
            case 'zone': return s.zone_name;
            case 'status': return s.status;
            case 'time_in': return s.check_in_ts ?? parseClockMinutes(s.time_in);
            case 'time_out': return parseClockMinutes(s.time_out);
            case 'duration': return s.check_in_ts ?? parseDurationSeconds(s.duration);
            default: return s.check_in_ts ?? parseClockMinutes(s.time_in);
        }
    }, dir);
}

function sortSideItems(items, codeKey, nameKey, state) {
    if (!items || !items.length) return items || [];
    const { column, dir } = state;
    return sortBy(items, (item) => {
        switch (column) {
            case 'code': return item[codeKey];
            case 'name': return item[nameKey];
            case 'status': return item.status;
            case 'duration': return item.check_in_ts ?? parseDurationSeconds(item.duration);
            default: return item[codeKey];
        }
    }, dir);
}

function handleRecentSort(column) {
    toggleSortState(recentSortState, column, ['time_in', 'time_out', 'duration'].includes(column) ? 'desc' : 'asc');
    updateSortableHeaders(document.getElementById('recent-table'), recentSortState.column, recentSortState.dir);
    paintRecentTable();
}

function handleProductsSort(column) {
    toggleSortState(productsSortState, column, ['duration'].includes(column) ? 'desc' : 'asc');
    updateSortableHeaders(document.getElementById('products-table'), productsSortState.column, productsSortState.dir);
    paintProductTable();
}

function handleMaterialsSort(column) {
    toggleSortState(materialsSortState, column, ['duration'].includes(column) ? 'desc' : 'asc');
    updateSortableHeaders(document.getElementById('materials-table'), materialsSortState.column, materialsSortState.dir);
    paintMaterialTable();
}

function updateMonitoringModalData(data) {
    monitoringModalData.products = data.products || [];
    monitoringModalData.materials = data.materials || [];
    monitoringModalData.allScans = data.all_scans || [];
    recentScansData = data.recent_scans || [];
    if (data.filter_label) {
        monitoringModalData.filterLabel = data.filter_label;
    }
}

function openListModal(title, subtitle, rows) {
    document.getElementById('listModalTitle').textContent = title;
    document.getElementById('listModalSubtitle').textContent = subtitle || '';
    let html = '';
    if (!rows || rows.length === 0) {
        html = '<p class="text-xs text-gray-400 italic text-center py-8">No records found.</p>';
    } else {
        rows.forEach(row => {
            const clickAttr = row.itemType && row.itemId
                ? `class="flex items-center justify-between py-2.5 border-b border-gray-100 dark:border-gray-700/60 last:border-0 gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 -mx-2 px-2 rounded transition-colors" onclick="openItemFromListModal('${row.itemType}', ${row.itemId})"`
                : `class="flex items-center justify-between py-2.5 border-b border-gray-100 dark:border-gray-700/60 last:border-0 gap-3"`;
            html += `<div ${clickAttr}>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">${escapeHtml(row.label)}</p>
                    ${row.sub ? `<p class="text-xs text-gray-500 dark:text-gray-400">${escapeHtml(row.sub)}</p>` : ''}
                </div>
                <span class="text-xs font-semibold whitespace-nowrap flex-shrink-0 ${row.cls || 'text-gray-600 dark:text-gray-300'}">${escapeHtml(row.val)}</span>
            </div>`;
        });
    }
    document.getElementById('listModalBody').innerHTML = html;
    document.getElementById('listModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeListModal(event) {
    if (event && event.target !== document.getElementById('listModal')) return;
    document.getElementById('listModal').classList.add('hidden');
    if (!document.getElementById('itemModal').classList.contains('hidden')) return;
    document.body.style.overflow = '';
}

function openItemFromListModal(type, id) {
    closeListModal();
    openItemModal(type, id);
}

function productStatRows() {
    return (monitoringModalData.products || []).map(p => ({
        label: p.product_name,
        sub: p.product_code,
        val: (p.status || '—') + (p.duration ? ' · ' + p.duration : ''),
        cls: p.status === 'IN' ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-300',
        itemType: 'product',
        itemId: p.id,
    }));
}

function materialStatRows() {
    return (monitoringModalData.materials || []).map(m => ({
        label: m.material_name,
        sub: m.material_code,
        val: (m.status || '—') + (m.duration ? ' · ' + m.duration : ''),
        cls: m.status === 'IN' ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-300',
        itemType: 'raw_material',
        itemId: m.id,
    }));
}

function openProductsStatModal() {
    const label = monitoringModalData.filterLabel || 'this period';
    openListModal('Products', 'Unique products scanned · ' + label, productStatRows());
}

function openMaterialsStatModal() {
    const label = monitoringModalData.filterLabel || 'this period';
    openListModal('Raw Materials', 'Unique raw materials scanned · ' + label, materialStatRows());
}

function openTotalStatModal() {
    const label = monitoringModalData.filterLabel || 'this period';
    const rows = [
        ...productStatRows().map(r => ({ ...r, sub: 'Product · ' + r.sub })),
        ...materialStatRows().map(r => ({ ...r, sub: 'Raw Material · ' + r.sub })),
    ];
    openListModal('Total Tracked', 'All unique items scanned · ' + label, rows);
}

function openScansStatModal() {
    const label = monitoringModalData.filterLabel || 'this period';
    const rows = (monitoringModalData.allScans || []).map(s => ({
        label: s.name,
        sub: (s.type_label || s.type) + ' · ' + s.code + ' · ' + s.zone_name,
        val: s.time_in + (s.status ? ' · ' + s.status : ''),
        cls: s.status === 'IN' ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-300',
        itemType: s.type,
        itemId: s.item_id,
    }));
    openListModal('Scans', 'All zone scan records · ' + label, rows);
}

function toggleDateFilter() {
    document.getElementById('dateFilterDropdown').classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    const dd = document.getElementById('dateFilterDropdown');
    const btn = document.getElementById('dateFilterBtn');
    if (dd && btn && !dd.contains(e.target) && !btn.contains(e.target)) {
        dd.classList.add('hidden');
    }
});

function filterByDate(period) {
    currentFilterType = period;
    currentCustomDate = '';
    document.getElementById('customDate').value = '';
    const labels = { today: 'Today', yesterday: 'Yesterday', week: 'This Week' };
    document.getElementById('dateFilterLabel').textContent = labels[period] || period;
    document.getElementById('dateFilterDropdown').classList.add('hidden');
    updateMonitoringData();
}

function filterByCustomDate() {
    const dateInput = document.getElementById('customDate');
    if (!dateInput.value) return;
    currentCustomDate = dateInput.value;
    currentFilterType = 'custom';
    const formatted = new Date(dateInput.value + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('dateFilterLabel').textContent = formatted;
    document.getElementById('dateFilterDropdown').classList.add('hidden');
    updateMonitoringData();
}

function changeZone(zoneId) {
    const url = new URL(window.location.href);
    if (zoneId) url.searchParams.set('zone', zoneId);
    else url.searchParams.delete('zone');
    window.location.href = url.toString();
}

function getMonitoringUrl() {
    const url = new URL('<?= base_url('inventory/monitoring-data') ?>', window.location.origin);
    const zone = document.getElementById('zone-filter')?.value;
    if (zone) url.searchParams.set('zone', zone);
    if (currentCustomDate) {
        url.searchParams.set('date', currentCustomDate);
    } else {
        url.searchParams.set('filter', currentFilterType);
    }
    return url.toString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function formatDuration(seconds) {
    seconds = Math.max(0, parseInt(seconds, 10) || 0);
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    if (h > 0) return `${h}h ${m}m`;
    if (m > 0) return `${m}m ${s}s`;
    return `${s}s`;
}

function statusBadge(status) {
    if (status === 'IN') {
        return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300 uppercase">IN</span>';
    }
    if (status === 'OUT') {
        return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300 uppercase">OUT</span>';
    }
    return '<span class="text-xs text-gray-400">—</span>';
}

function paintRecentTable() {
    updateRecentTable(sortRecentScans(recentScansData));
}

function paintProductTable() {
    updateProductTable(sortSideItems(monitoringModalData.products, 'product_code', 'product_name', productsSortState));
}

function paintMaterialTable() {
    updateMaterialTable(sortSideItems(monitoringModalData.materials, 'material_code', 'material_name', materialsSortState));
}

function updateRecentTable(scans) {
    const tbody = document.getElementById('recent-body');
    const badge = document.getElementById('recent-count');
    if (badge) badge.textContent = (scans ? scans.length : 0) + ' Items';

    if (!scans || scans.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined text-4xl block mb-2 text-gray-300">rss_feed</span>
            No scan records for this period. Items appear here after RFID zone IN/OUT scans.
        </td></tr>`;
        return;
    }

    tbody.innerHTML = scans.map(scan => {
        const typeBadge = scan.type === 'product'
            ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300">Product</span>'
            : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300">Raw Material</span>';
        const search = (scan.code + ' ' + scan.name + ' ' + scan.zone_name).toLowerCase();
        const durationClass = scan.is_live ? 'text-green-600 dark:text-green-400 font-medium' : '';
        const durationAttr = scan.is_live && scan.check_in_ts ? `data-check-in-ts="${scan.check_in_ts}"` : '';
        return `<tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors scan-row cursor-pointer" data-item-type="${escapeHtml(scan.type)}" data-item-id="${scan.item_id}" data-search="${escapeHtml(search)}">
            <td class="px-4 lg:px-6 py-3">${typeBadge}</td>
            <td class="px-4 lg:px-6 py-3"><span class="text-sm font-mono font-bold text-primary">${escapeHtml(scan.code)}</span></td>
            <td class="px-4 lg:px-6 py-3 text-sm font-semibold text-gray-900 dark:text-white">${escapeHtml(scan.name)}</td>
            <td class="px-4 lg:px-6 py-3 text-sm text-gray-600 dark:text-gray-300">${escapeHtml(scan.zone_name)}</td>
            <td class="px-4 lg:px-6 py-3">${statusBadge(scan.status)}</td>
            <td class="px-4 lg:px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 tabular-nums">${escapeHtml(scan.time_in)}</td>
            <td class="px-4 lg:px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 tabular-nums">${escapeHtml(scan.time_out)}</td>
            <td class="px-4 lg:px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 tabular-nums ${durationClass}" ${durationAttr}>${escapeHtml(scan.duration)}</td>
        </tr>`;
    }).join('');
    updateLiveDurations();
}

function updateItemTable(tbodyId, items, itemType, codeKey, nameKey, emptyMsg) {
    const tbody = document.getElementById(tbodyId);
    if (!items || items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">${emptyMsg}</td></tr>`;
        return;
    }
    tbody.innerHTML = items.map(item => {
        const search = (item[codeKey] + ' ' + item[nameKey]).toLowerCase();
        const durationClass = item.is_live ? 'text-green-600 dark:text-green-400 font-medium' : '';
        const durationAttr = item.is_live && item.check_in_ts ? `data-check-in-ts="${item.check_in_ts}"` : '';
        return `<tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 item-row cursor-pointer" data-item-type="${itemType}" data-item-id="${item.id}" data-search="${escapeHtml(search)}">
            <td class="px-4 py-3"><span class="text-xs font-mono font-bold text-primary">${escapeHtml(item[codeKey])}</span></td>
            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${escapeHtml(item[nameKey])}</td>
            <td class="px-4 py-3">${statusBadge(item.status)}</td>
            <td class="px-4 py-3 text-xs font-bold text-gray-500 tabular-nums ${durationClass}" ${durationAttr}>${escapeHtml(item.duration)}</td>
        </tr>`;
    }).join('');
    updateLiveDurations();
}

function updateProductTable(products) {
    const badge = document.querySelector('[data-product-count]');
    if (badge) badge.textContent = products ? products.length : 0;
    updateItemTable('products-body', products, 'product', 'product_code', 'product_name', 'No products scanned for this period');
}

function updateMaterialTable(materials) {
    const badge = document.querySelector('[data-material-count]');
    if (badge) badge.textContent = materials ? materials.length : 0;
    updateItemTable('materials-body', materials, 'raw_material', 'material_code', 'material_name', 'No raw materials scanned for this period');
}

function updateStats(stats, filterLabel) {
    if (!stats) return;
    document.getElementById('stat-products').textContent = stats.products;
    document.getElementById('stat-materials').textContent = stats.materials;
    document.getElementById('stat-total').textContent = stats.total;
    document.getElementById('stat-today').textContent = stats.scanned_today;
    if (filterLabel) {
        document.getElementById('stat-today-sub').textContent = filterLabel;
        document.querySelectorAll('[data-stat-period]').forEach(el => {
            el.textContent = 'Scanned ' + filterLabel;
        });
    }
}

function updateLiveDurations() {
    const now = Math.floor(Date.now() / 1000) + serverTimeOffset;
    document.querySelectorAll('[data-check-in-ts]').forEach(cell => {
        const checkIn = parseInt(cell.dataset.checkInTs, 10);
        if (!checkIn) return;
        cell.textContent = formatDuration(now - checkIn);
    });
}

async function updateMonitoringData() {
    if (isUpdating) return;
    isUpdating = true;
    try {
        const res = await fetch(getMonitoringUrl());
        const data = await res.json();
        if (data.success) {
            updateStats(data.stats, data.filter_label);
            updateMonitoringModalData(data);
            if (data.filter_label) {
                document.getElementById('dateFilterLabel').textContent = data.filter_label;
            }
            paintRecentTable();
            paintProductTable();
            paintMaterialTable();
            document.getElementById('last-updated-time').textContent = data.last_updated;
            if (data.server_time) serverTimeOffset = data.server_time - Math.floor(Date.now() / 1000);
            filterTables();
        }
    } catch (e) {
        console.error('Failed to refresh inventory monitoring', e);
    } finally {
        isUpdating = false;
    }
}

function manualRefresh() { updateMonitoringData(); }

function filterTables() {
    const filter = (document.getElementById('search-input').value || '').toLowerCase();
    document.querySelectorAll('.scan-row, .item-row').forEach(row => {
        const search = row.dataset.search || '';
        row.style.display = !filter || search.includes(filter) ? '' : 'none';
    });
}

function getItemDetailUrl(type, id) {
    const url = new URL('<?= base_url('inventory/item-detail') ?>', window.location.origin);
    url.searchParams.set('type', type);
    url.searchParams.set('id', id);
    if (currentCustomDate) {
        url.searchParams.set('date', currentCustomDate);
    } else {
        url.searchParams.set('filter', currentFilterType);
    }
    return url.toString();
}

function closeItemModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('itemModal').classList.add('hidden');
    document.body.style.overflow = '';
}

async function openItemModal(type, id) {
    const modal = document.getElementById('itemModal');
    const loading = document.getElementById('itemModalLoading');
    const content = document.getElementById('itemModalContent');
    const iconWrap = document.getElementById('itemModalIcon');

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    loading.classList.remove('hidden');
    content.classList.add('hidden');

    const isProduct = type === 'product';
    iconWrap.innerHTML = `<span class="material-symbols-outlined text-primary text-xl">${isProduct ? 'inventory_2' : 'category'}</span>`;
    document.getElementById('itemModalName').textContent = 'Loading...';
    document.getElementById('itemModalMeta').textContent = '';

    try {
        const res = await fetch(getItemDetailUrl(type, id));
        const data = await res.json();
        if (!data.success) {
            document.getElementById('itemModalName').textContent = data.message || 'Item not found';
            loading.classList.add('hidden');
            content.classList.remove('hidden');
            return;
        }

        const item = data.item;
        document.getElementById('itemModalName').textContent = item.name;
        document.getElementById('itemModalMeta').textContent = `${data.type_label} · ${item.code}`;

        const detailsEl = document.getElementById('itemModalDetails');
        const fields = data.detail_fields || [];
        detailsEl.innerHTML = fields.map(f => {
            const mono = (f.label === 'EPC Tag' || f.label === 'Reference Number' || f.label === 'SAP Code') ? ' font-mono' : '';
            return `<div>
                <p class="text-xs text-gray-400 mb-0.5">${escapeHtml(f.label)}</p>
                <p class="text-gray-900 dark:text-white font-bold text-sm${mono}">${escapeHtml(String(f.value ?? '—'))}</p>
            </div>`;
        }).join('');

        document.getElementById('itemModalPeriod').textContent = 'Scans for ' + (data.filter_label || 'this period');
        document.getElementById('itemModalScanTitle').textContent = 'Zone Activity — ' + (data.filter_label || '');

        const scansBody = document.getElementById('itemModalScansBody');
        const noScans = document.getElementById('itemModalNoScans');
        if (!data.scan_records || data.scan_records.length === 0) {
            scansBody.innerHTML = '';
            noScans.classList.remove('hidden');
        } else {
            noScans.classList.add('hidden');
            scansBody.innerHTML = data.scan_records.map(scan => {
                const statusHtml = statusBadge(scan.status);
                const durationClass = scan.is_live ? 'text-green-600 dark:text-green-400 font-medium' : '';
                return `<tr>
                    <td class="py-2 pr-2 text-gray-900 dark:text-white">${escapeHtml(scan.zone_name)}</td>
                    <td class="py-2 pr-2">${statusHtml}</td>
                    <td class="py-2 pr-2 tabular-nums text-gray-500">${escapeHtml(scan.time_in)}</td>
                    <td class="py-2 pr-2 tabular-nums text-gray-500">${escapeHtml(scan.time_out)}</td>
                    <td class="py-2 tabular-nums ${durationClass}">${escapeHtml(scan.duration)}</td>
                </tr>`;
            }).join('');
        }

        document.getElementById('itemModalEditLink').href = data.edit_url || '#';
        loading.classList.add('hidden');
        content.classList.remove('hidden');
    } catch (e) {
        console.error('Failed to load item detail', e);
        document.getElementById('itemModalName').textContent = 'Failed to load details';
        loading.classList.add('hidden');
        content.classList.remove('hidden');
    }
}

function updateClock() {
    const el = document.getElementById('current-time');
    if (el) el.textContent = new Date().toLocaleTimeString('en-GB', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
}

document.addEventListener('DOMContentLoaded', () => {
    bindSortableHeaders(document.getElementById('recent-table'), handleRecentSort);
    bindSortableHeaders(document.getElementById('products-table'), handleProductsSort);
    bindSortableHeaders(document.getElementById('materials-table'), handleMaterialsSort);
    updateSortableHeaders(document.getElementById('recent-table'), recentSortState.column, recentSortState.dir);
    updateSortableHeaders(document.getElementById('products-table'), productsSortState.column, productsSortState.dir);
    updateSortableHeaders(document.getElementById('materials-table'), materialsSortState.column, materialsSortState.dir);

    updateClock();
    setInterval(updateClock, 1000);
    setInterval(updateLiveDurations, 1000);
    updateInterval = setInterval(updateMonitoringData, 5000);

    document.addEventListener('click', (e) => {
        const row = e.target.closest('tr[data-item-type][data-item-id]');
        if (row) {
            openItemModal(row.dataset.itemType, row.dataset.itemId);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeListModal();
            closeItemModal();
        }
    });
});
</script>

<?= $this->include('templates/footer') ?>
