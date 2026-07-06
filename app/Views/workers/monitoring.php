<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6 px-2 sm:px-4 py-2 sm:py-4">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="<?= base_url('location-selector') ?>" class="flex items-center justify-center size-10 sm:size-12 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-700" title="Change Location">
                <span class="material-symbols-outlined text-xl sm:text-2xl">arrow_back</span>
            </a>
            <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined text-3xl">sensors</span>
            </div>
            <div>
                <h1 class="text-2xl lg:text-3xl font-extrabold leading-tight tracking-tight text-gray-900 dark:text-white uppercase">RFID Worker Monitoring</h1>
                <div class="flex items-center gap-2 mt-1">
                    <?php if (!empty($selected_zone_name)): ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                            <span class="material-symbols-outlined text-sm">location_on</span>
                            <?= esc($selected_zone_name) ?>
                        </span>
                    <?php else: ?>
                        <a href="<?= base_url('location-selector') ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800 hover:bg-yellow-200 dark:hover:bg-yellow-900/30 transition-colors">
                            <span class="material-symbols-outlined text-sm">warning</span>
                            No Location Selected - Click to Select
                        </a>
                    <?php endif; ?>
                    <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dashboard v1.0.5</p>
                </div>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500">search</span>
                <input id="search-input" onkeyup="searchTable()" class="w-full rounded-lg border-none bg-gray-100 dark:bg-gray-800 py-2 pl-10 pr-4 text-sm font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/50 transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500" placeholder="Search ID or Name..." type="text"/>
            </div>
            <div class="flex items-center gap-3 px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <span id="current-date" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"></span>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <span id="current-time" class="text-sm font-black text-gray-900 dark:text-white tabular-nums tracking-tight"></span>
            </div>
            <div class="flex items-center justify-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <?php 
                $isSystemActive = $active_readers > 0;
                $statusColor = $isSystemActive ? 'text-green-500' : 'text-red-500';
                $statusText = $isSystemActive ? 'Active' : 'Inactive';
                ?>
                <span id="system-status-indicator" class="material-symbols-outlined <?= $statusColor ?> text-sm">circle</span>
                <span id="system-status-text" class="text-xs font-bold text-gray-700 dark:text-white uppercase tracking-wider">Live System: <?= $statusText ?></span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6">
        <div class="relative overflow-hidden flex flex-col gap-2 rounded-lg p-6 lg:p-8 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider">Checked In</p>
                <div class="bg-primary/10 p-2 rounded-lg">
                    <span class="material-symbols-outlined text-primary">login</span>
                </div>
            </div>
            <div class="flex items-baseline gap-3">
                <p id="checked-in-count" class="text-gray-900 dark:text-white text-5xl lg:text-6xl font-black tabular-nums tracking-tighter"><?= esc($checked_in) ?></p>
                <p class="text-primary text-xs font-extrabold flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">trending_up</span>
                    Live
                </p>
            </div>
            <div class="mt-4 h-2 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                <div id="checked-in-bar" class="h-full bg-primary" style="width: <?= min(($checked_in / 200) * 100, 100) ?>%"></div>
            </div>
            <p id="checked-in-percentage" class="text-xs font-bold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wide">
                <?= round(min(($checked_in / 200) * 100, 100)) ?>% of maximum capacity
            </p>
        </div>
        
        <div class="relative overflow-hidden flex flex-col gap-2 rounded-lg p-6 lg:p-8 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider">Checked Out</p>
                <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded-lg">
                    <span class="material-symbols-outlined text-gray-500 dark:text-gray-400">logout</span>
                </div>
            </div>
            <div class="flex items-baseline gap-3">
                <p id="checked-out-count" class="text-gray-900 dark:text-white text-5xl lg:text-6xl font-black tabular-nums tracking-tighter"><?= esc($checked_out) ?></p>
                <p class="text-gray-400 dark:text-gray-500 text-xs font-extrabold flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">trending_up</span>
                    Today
                </p>
            </div>
            <div class="mt-4 h-2 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                <div id="checked-out-bar" class="h-full bg-gray-400 dark:bg-gray-600" style="width: <?= min(($checked_out / 100) * 100, 100) ?>%"></div>
            </div>
            <p id="last-checkout-text" class="text-xs font-bold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wide">
                <?php if (!empty($activity_logs)): ?>
                    Last checkout <?= date('g:i A', strtotime($activity_logs[0]['time_out'])) ?>
                <?php else: ?>
                    No checkouts yet today
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Real-Time Access Logs Section -->
    <div class="flex flex-col gap-4 lg:gap-6">
        <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-4">
            <div class="flex flex-col gap-2">
                <div>
                    <h2 class="text-xl lg:text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white uppercase">Real-Time Access Logs</h2>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-widest">
                            <span class="material-symbols-outlined text-sm text-primary">schedule</span>
                            Last Updated: <span id="last-updated-time"><?= esc($last_updated) ?></span>
                        </div>
                        <span class="text-gray-300 dark:text-gray-700">|</span>
                        <span class="text-primary text-xs font-extrabold uppercase tracking-widest">Live Data Stream On</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button onclick="toggleFilters()" class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-white text-xs font-extrabold uppercase tracking-wider hover:bg-gray-200 dark:hover:bg-gray-700 transition-all border border-gray-200 dark:border-gray-700">
                    <span class="material-symbols-outlined text-lg">filter_list</span>
                    More Filters
                </button>
                <button onclick="exportToCSV()" class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-xs font-extrabold uppercase tracking-wider hover:opacity-90 transition-all">
                    <span class="material-symbols-outlined text-lg">download</span>
                    Export CSV
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div id="filter-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="toggleFilters()"></div>
        
        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white dark:bg-background-dark rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white dark:bg-background-dark border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary">
                            <span class="material-symbols-outlined">filter_list</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-gray-900 dark:text-white uppercase tracking-tight">Advanced Filters</h3>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Filter monitoring data by multiple criteria</p>
                        </div>
                    </div>
                    <button onclick="toggleFilters()" class="flex items-center justify-center size-8 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6 space-y-6">
                    <!-- Zone Filters -->
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-lg">location_on</span>
                            <p class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">Filter by Zone</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button onclick="filterByZone('all')" class="zone-filter-btn active px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg bg-primary text-white transition-all">
                                All Zones
                            </button>
                            <?php 
                            // Get all zones for gate filters
                            $zoneModel = new \App\Models\ZoneModel();
                            $allZones = $zoneModel->where('status', 'active')->findAll();
                            foreach ($allZones as $zone): 
                            ?>
                                <button onclick="filterByZone('<?= esc($zone['zone_id']) ?>')" class="zone-filter-btn px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all border border-gray-200 dark:border-gray-700">
                                    <?= esc($zone['zone_name']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 dark:border-gray-700"></div>
                    
                    <!-- Department Filters -->
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-lg">business_center</span>
                            <p class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">Filter by Department</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button onclick="filterByDepartment('all')" class="department-filter-btn active px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg bg-primary text-white transition-all">
                                All Departments
                            </button>
                            <?php if (!empty($departments)): ?>
                                <?php foreach ($departments as $dept): ?>
                                    <button onclick="filterByDepartment('<?= esc($dept['name']) ?>')" class="department-filter-btn px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all border border-gray-200 dark:border-gray-700">
                                        <?= esc(ucwords($dept['name'])) ?>
                                    </button>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 dark:border-gray-700"></div>
                    
                    <!-- Status Filters -->
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-lg">toggle_on</span>
                            <p class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">Filter by Status</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button onclick="filterByStatus('all')" class="status-filter-btn active px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg bg-primary text-white transition-all">
                                All Status
                            </button>
                            <button onclick="filterByStatus('IN')" class="status-filter-btn px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all border border-gray-200 dark:border-gray-700">
                                Checked In
                            </button>
                            <button onclick="filterByStatus('OUT')" class="status-filter-btn px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all border border-gray-200 dark:border-gray-700">
                                Checked Out
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between gap-3">
                    <button onclick="clearAllFilters()" class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-700 transition-all border border-gray-200 dark:border-gray-700">
                        Clear All Filters
                    </button>
                    <button onclick="applyFilters()" class="px-6 py-2 text-xs font-bold uppercase tracking-wider rounded-lg bg-primary text-white hover:opacity-90 transition-all">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Table -->
    <div class="bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden shadow-sm">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table id="activity-table" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 lg:px-6 py-4 lg:py-5 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24 lg:w-32">ID Number</th>
                        <th class="px-4 lg:px-6 py-4 lg:py-5 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Worker Name</th>
                        <th class="px-4 lg:px-6 py-4 lg:py-5 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Department</th>
                        <th class="px-4 lg:px-6 py-4 lg:py-5 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 lg:px-6 py-4 lg:py-5 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Gate In Time</th>
                        <th class="px-4 lg:px-6 py-4 lg:py-5 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Gate Out Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <?php if (!empty($activity_logs)): ?>
                        <?php foreach ($activity_logs as $log): ?>
                            <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors <?= $log['is_latest'] ? 'bg-primary/5 dark:bg-primary/10' : '' ?>">
                                <td class="px-4 lg:px-6 py-4 lg:py-5">
                                    <span class="text-xs font-extrabold <?= $log['is_latest'] ? 'text-primary' : 'text-gray-600 dark:text-gray-400' ?>">#<?= esc($log['id_number']) ?></span>
                                </td>
                                <td class="px-4 lg:px-6 py-4 lg:py-5">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($log['profile_photo'])): ?>
                                            <img src="<?= base_url('uploads/profiles/' . $log['profile_photo']) ?>" 
                                                 alt="<?= esc($log['full_name']) ?>" 
                                                 class="size-8 rounded-full object-cover border-2 <?= $log['is_latest'] ? 'border-primary' : 'border-gray-200 dark:border-gray-700' ?>">
                                        <?php else: ?>
                                            <div class="size-8 rounded-full <?= $log['is_latest'] ? 'bg-primary/20 text-primary' : 'bg-'.$log['color'].'-100 dark:bg-'.$log['color'].'-900/30 text-'.$log['color'].'-600 dark:text-'.$log['color'].'-400' ?> flex items-center justify-center font-black text-xs">
                                                <?= esc($log['initials']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <span class="font-bold text-sm text-gray-900 dark:text-white"><?= esc($log['full_name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-4 lg:px-6 py-4 lg:py-5 text-xs font-semibold text-gray-500 dark:text-gray-400"><?= esc(ucwords($log['department'])) ?></td>
                                <td class="px-4 lg:px-6 py-4 lg:py-5">
                                    <?php if ($log['status'] === 'IN'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full <?= $log['is_latest'] ? 'bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-300 dark:border-green-900/30' : 'bg-green-50 dark:bg-green-900/10 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-900/20' ?> text-xs font-black uppercase tracking-wider">
                                            IN
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-xs font-black uppercase tracking-wider border border-red-200 dark:border-red-900/30">
                                            OUT
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 lg:px-6 py-4 lg:py-5 text-right text-xs font-black tabular-nums <?php if ($log['check_in_status'] === 'green'): ?>text-green-600 dark:text-green-400<?php else: ?>text-red-600 dark:text-red-400<?php endif; ?>"><?= esc($log['time_in']) ?></td>
                                <td class="px-4 lg:px-6 py-4 lg:py-5 text-right text-xs font-black tabular-nums <?php if ($log['time_out'] !== '-' && $log['check_out_status'] === 'green'): ?>text-green-600 dark:text-green-400<?php elseif ($log['time_out'] !== '-'): ?>text-red-600 dark:text-red-400<?php else: ?>text-gray-500 dark:text-gray-400<?php endif; ?>">
                                    <?php if ($log['time_out'] !== '-'): ?>
                                        <?= esc($log['time_out']) ?>
                                    <?php else: ?>
                                        <span class="text-gray-400 dark:text-gray-500">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-4xl">inbox</span>
                                    <p class="font-semibold">No activity logs yet today</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Card View -->
        <div id="mobile-activity-cards" class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
            <?php if (!empty($activity_logs)): ?>
                <?php foreach ($activity_logs as $log): ?>
                    <div class="p-4 <?= $log['is_latest'] ? 'bg-primary/5 dark:bg-primary/10' : '' ?> hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <div class="flex items-start gap-3">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <?php if (!empty($log['profile_photo'])): ?>
                                    <img src="<?= base_url('uploads/profiles/' . $log['profile_photo']) ?>" 
                                         alt="<?= esc($log['full_name']) ?>" 
                                         class="size-10 rounded-full object-cover border-2 <?= $log['is_latest'] ? 'border-primary' : 'border-gray-200 dark:border-gray-700' ?>">
                                <?php else: ?>
                                    <div class="size-10 rounded-full <?= $log['is_latest'] ? 'bg-primary/20 text-primary' : 'bg-'.$log['color'].'-100 dark:bg-'.$log['color'].'-900/30 text-'.$log['color'].'-600 dark:text-'.$log['color'].'-400' ?> flex items-center justify-center font-black text-sm">
                                        <?= esc($log['initials']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-sm text-gray-900 dark:text-white truncate"><?= esc($log['full_name']) ?></p>
                                        <p class="text-xs font-medium <?= $log['is_latest'] ? 'text-primary' : 'text-gray-500 dark:text-gray-400' ?>">#<?= esc($log['id_number']) ?></p>
                                    </div>
                                    <?php if ($log['status'] === 'IN'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full <?= $log['is_latest'] ? 'bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-300 dark:border-green-900/30' : 'bg-green-50 dark:bg-green-900/10 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-900/20' ?> text-xs font-black uppercase">
                                            IN
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-xs font-black uppercase border border-red-200 dark:border-red-900/30">
                                            OUT
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="font-semibold text-gray-600 dark:text-gray-400"><?= esc(ucwords($log['department'])) ?></span>
                                    <span class="text-gray-300 dark:text-gray-700">•</span>
                                    <span class="font-black tabular-nums <?php if ($log['check_in_status'] === 'green'): ?>text-green-600 dark:text-green-400<?php else: ?>text-red-600 dark:text-red-400<?php endif; ?>"><?= esc($log['time_in']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                    <div class="flex flex-col items-center gap-2">
                        <span class="material-symbols-outlined text-4xl">inbox</span>
                        <p class="font-semibold">No activity logs yet today</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="px-4 lg:px-6 py-3 lg:py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-background-dark flex flex-col sm:flex-row items-center justify-between gap-3">
            <p id="record-count" class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Showing <?= ($current_page - 1) * $per_page + 1 ?>-<?= min($current_page * $per_page, $total_records) ?> of <?= $total_records ?> Workers
            </p>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="flex items-center gap-2">
                    <!-- Previous Button -->
                    <?php if ($current_page > 1): ?>
                        <a href="<?= base_url('workers/monitoring?page=' . ($current_page - 1)) ?>" class="flex items-center justify-center size-9 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-700">
                            <span class="material-symbols-outlined text-lg">chevron_left</span>
                        </a>
                    <?php else: ?>
                        <div class="flex items-center justify-center size-9 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 border border-gray-200 dark:border-gray-700 cursor-not-allowed">
                            <span class="material-symbols-outlined text-lg">chevron_left</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start_page = max(1, $current_page - 1);
                    $end_page = min($total_pages, $current_page + 1);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <?php if ($i == $current_page): ?>
                            <div class="flex items-center justify-center size-9 rounded-lg bg-primary text-white font-bold text-sm">
                                <?= $i ?>
                            </div>
                        <?php else: ?>
                            <a href="<?= base_url('workers/monitoring?page=' . $i) ?>" class="flex items-center justify-center size-9 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-700 font-bold text-sm">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <!-- Next Button -->
                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?= base_url('workers/monitoring?page=' . ($current_page + 1)) ?>" class="flex items-center justify-center size-9 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-700">
                            <span class="material-symbols-outlined text-lg">chevron_right</span>
                        </a>
                    <?php else: ?>
                        <div class="flex items-center justify-center size-9 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 border border-gray-200 dark:border-gray-700 cursor-not-allowed">
                            <span class="material-symbols-outlined text-lg">chevron_right</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Asset Tracking Section -->
    <div id="asset-tracking-section" class="bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden shadow-sm">
        <div class="px-4 lg:px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-orange-100 dark:bg-orange-900/20 p-2 rounded-lg">
                    <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">inventory_2</span>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">Asset Tracking</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Assets detected in this zone</p>
                </div>
            </div>
            <span data-asset-count class="text-xs font-bold px-2.5 py-1 bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 rounded-full"><?= count($zone_assets ?? []) ?> Assets</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 lg:px-6 py-3 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Asset Name</th>
                        <th class="px-4 lg:px-6 py-3 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">EPC No</th>
                        <th class="px-4 lg:px-6 py-3 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assigned To</th>
                        <th class="px-4 lg:px-6 py-3 text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Seen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <?php if (!empty($zone_assets)): ?>
                        <?php foreach ($zone_assets as $asset): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 lg:px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-orange-500 text-lg">package_2</span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white"><?= esc($asset['asset_name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-4 lg:px-6 py-3">
                                    <span class="text-xs font-mono font-bold text-gray-600 dark:text-gray-400"><?= esc($asset['epc_no'] ?? '-') ?></span>
                                </td>
                                <td class="px-4 lg:px-6 py-3">
                                    <?php if (!empty($asset['first_name'])): ?>
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-blue-500 text-sm">person</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($asset['first_name'] . ' ' . $asset['last_name']) ?></span>
                                            <span class="text-xs text-gray-500">(<?= esc($asset['w_id']) ?>)</span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 lg:px-6 py-3">
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                                        <?= !empty($asset['last_seen_at']) ? date('H:i:s', strtotime($asset['last_seen_at'])) : '-' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-3xl text-gray-400">inventory_2</span>
                                    <p class="text-sm font-semibold">No assets detected in this zone yet</p>
                                    <p class="text-xs text-gray-400">Assets will appear here when their EPC tags are scanned by the reader</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- System Status Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="flex items-center gap-3 p-4 rounded-lg bg-gray-100 dark:bg-gray-800/30 border border-gray-200 dark:border-gray-700/50">
            <span class="material-symbols-outlined text-primary text-2xl">router</span>
            <div>
                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wide">Reader Status</p>
                <p id="reader-stats" class="text-xs font-black text-gray-900 dark:text-white uppercase"><?= esc($active_readers) ?> ACTIVE / <?= $total_readers - $active_readers ?> OFFLINE</p>
            </div>
        </div>
        <div class="flex items-center gap-3 p-4 rounded-lg bg-gray-100 dark:bg-gray-800/30 border border-gray-200 dark:border-gray-700/50">
            <span class="material-symbols-outlined text-primary text-2xl">history</span>
            <div>
                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wide">Uptime</p>
                <p id="system-uptime" class="text-xs font-black text-gray-900 dark:text-white uppercase"><?= esc($uptime) ?></p>
            </div>
        </div>
        <div class="flex items-center gap-3 p-4 rounded-lg bg-gray-100 dark:bg-gray-800/30 border border-gray-200 dark:border-gray-700/50">
            <span class="material-symbols-outlined text-primary text-2xl">security</span>
            <div>
                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wide">Zone Integrity</p>
                <p class="text-xs font-black text-gray-900 dark:text-white uppercase">SECURE (100%)</p>
            </div>
        </div>
    </div>
</div>

<script>
let updateInterval;
let isUpdating = false;
let currentGateFilter = 'all';
let currentDepartmentFilter = 'all';
let currentStatusFilter = 'all';

// Temporary filter values (pending application)
let tempGateFilter = 'all';
let tempDepartmentFilter = 'all';
let tempStatusFilter = 'all';

// Toggle filter visibility
function toggleFilters() {
    const modal = document.getElementById('filter-modal');
    if (!modal.classList.contains('hidden')) {
        // Reset temp values to current values when closing without applying
        tempGateFilter = currentGateFilter;
        tempDepartmentFilter = currentDepartmentFilter;
        tempStatusFilter = currentStatusFilter;
    }
    modal.classList.toggle('hidden');
}

// Clear all filters
function clearAllFilters() {
    // Reset zone filter
    currentGateFilter = 'all';
    tempGateFilter = 'all';
    document.querySelectorAll('.zone-filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-primary', 'text-white');
        btn.classList.add('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
    });
    document.querySelector('.zone-filter-btn').classList.add('active', 'bg-primary', 'text-white');
    document.querySelector('.zone-filter-btn').classList.remove('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
    
    // Reset department filter
    currentDepartmentFilter = 'all';
    tempDepartmentFilter = 'all';
    document.querySelectorAll('.department-filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-primary', 'text-white');
        btn.classList.add('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
    });
    document.querySelector('.department-filter-btn').classList.add('active', 'bg-primary', 'text-white');
    document.querySelector('.department-filter-btn').classList.remove('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
    
    // Reset status filter
    currentStatusFilter = 'all';
    tempStatusFilter = 'all';
    document.querySelectorAll('.status-filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-primary', 'text-white');
        btn.classList.add('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
    });
    document.querySelector('.status-filter-btn').classList.add('active', 'bg-primary', 'text-white');
    document.querySelector('.status-filter-btn').classList.remove('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
    
    // Update data and close modal
    updateMonitoringData();
    toggleFilters();
}

// Filter by zone
function filterByZone(zoneId) {
    tempGateFilter = zoneId;
    
    // Update button states
    document.querySelectorAll('.zone-filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-primary', 'text-white');
        btn.classList.add('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
    });
    
    event.target.classList.add('active', 'bg-primary', 'text-white');
    event.target.classList.remove('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
}

// Filter by department
function filterByDepartment(department) {
    tempDepartmentFilter = department;
    
    // Update button states
    document.querySelectorAll('.department-filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-primary', 'text-white');
        btn.classList.add('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
    });
    
    event.target.classList.add('active', 'bg-primary', 'text-white');
    event.target.classList.remove('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
}

// Filter by status
function filterByStatus(status) {
    tempStatusFilter = status;
    
    // Update button states
    document.querySelectorAll('.status-filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-primary', 'text-white');
        btn.classList.add('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
    });
    
    event.target.classList.add('active', 'bg-primary', 'text-white');
    event.target.classList.remove('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300', 'border', 'border-gray-200', 'dark:border-gray-700');
}

// Apply filters and close modal
function applyFilters() {
    // Apply temporary values to actual filter variables
    currentGateFilter = tempGateFilter;
    currentDepartmentFilter = tempDepartmentFilter;
    currentStatusFilter = tempStatusFilter;
    
    updateMonitoringData();
    toggleFilters();
}

// Fetch and update monitoring data
async function updateMonitoringData() {
    if (isUpdating) return;
    isUpdating = true;
    
    try {
        const params = new URLSearchParams();
        params.set('_', String(Date.now()));
        
        if (currentGateFilter !== 'all') {
            params.set('zone_id', currentGateFilter);
        }
        
        if (currentDepartmentFilter !== 'all') {
            params.set('department', currentDepartmentFilter);
        }
        
        if (currentStatusFilter !== 'all') {
            params.set('status', currentStatusFilter);
        }
        
        const url = '<?= base_url('workers/monitoring-data') ?>?' + params.toString();
        
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store',
        });
        
        if (!response.ok) {
            throw new Error('Monitoring update failed (' + response.status + ')');
        }
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('last-updated-time').textContent = data.last_updated;
            
            updateStatsCards(data);
            updateActivityTable(data.activity_logs, data);
            
            if (data.zone_assets !== undefined) {
                updateAssetTable(data.zone_assets);
            }
            
            updateSystemStatus(data.active_readers);
            updateReaderStats(data.active_readers, data.total_readers);
            
            if (data.uptime) {
                document.getElementById('system-uptime').textContent = data.uptime;
            }
        }
    } catch (error) {
        console.error('Error updating monitoring data:', error);
    } finally {
        isUpdating = false;
    }
}

// Update stats cards
function updateStatsCards(data) {
    // Checked In
    const checkedInEl = document.getElementById('checked-in-count');
    if (checkedInEl) {
        checkedInEl.textContent = data.checked_in;
        const percentage = Math.min((data.checked_in / 200) * 100, 100);
        document.getElementById('checked-in-bar').style.width = percentage + '%';
        document.getElementById('checked-in-percentage').textContent = Math.round(percentage) + '% of maximum capacity';
    }
    
    // Checked Out
    const checkedOutEl = document.getElementById('checked-out-count');
    if (checkedOutEl) {
        checkedOutEl.textContent = data.checked_out;
        const percentage = Math.min((data.checked_out / 100) * 100, 100);
        document.getElementById('checked-out-bar').style.width = percentage + '%';
        
        if (data.activity_logs.length > 0) {
            const lastCheckout = data.activity_logs.find(log => log.status === 'OUT');
            if (lastCheckout) {
                document.getElementById('last-checkout-text').textContent = 'Last checkout ' + lastCheckout.time_in;
            }
        }
    }
}

// Update activity table
function updateActivityTable(logs, responseData) {
    const tbody = document.querySelector('#activity-table tbody');
    const mobileCards = document.getElementById('mobile-activity-cards');
    
    if (logs.length === 0) {
        // Desktop empty state
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center gap-2">
                            <span class="material-symbols-outlined text-4xl">inbox</span>
                            <p class="font-semibold">No activity logs yet today</p>
                        </div>
                    </td>
                </tr>
            `;
        }
        
        // Mobile empty state
        if (mobileCards) {
            mobileCards.innerHTML = `
                <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                    <div class="flex flex-col items-center gap-2">
                        <span class="material-symbols-outlined text-4xl">inbox</span>
                        <p class="font-semibold">No activity logs yet today</p>
                    </div>
                </div>
            `;
        }
        return;
    }
    
    // Update desktop table
    if (tbody) {
        tbody.innerHTML = logs.map((log, index) => {
            const colorClass = log.is_latest ? 'bg-primary/5 dark:bg-primary/10' : '';
            const idColor = log.is_latest ? 'text-primary' : 'text-gray-600 dark:text-gray-400';
            const timeColor = log.is_latest ? 'text-primary' : 'text-gray-500 dark:text-gray-400';
            
            let avatarHtml = '';
            if (log.profile_photo) {
                const borderColor = log.is_latest ? 'border-primary' : 'border-gray-200 dark:border-gray-700';
                avatarHtml = `<img src="<?= base_url('uploads/profiles/') ?>${log.profile_photo}" 
                                 alt="${log.full_name}" 
                                 class="size-8 rounded-full object-cover border-2 ${borderColor}">`;
            } else {
                const bgColor = log.is_latest ? 'bg-primary/20 text-primary' : `bg-${log.color}-100 dark:bg-${log.color}-900/30 text-${log.color}-600 dark:text-${log.color}-400`;
                avatarHtml = `<div class="size-8 rounded-full ${bgColor} flex items-center justify-center font-black text-xs">
                                ${log.initials}
                              </div>`;
            }
            
            const statusBadge = log.status === 'IN' 
                ? `<span class="inline-flex items-center px-3 py-1 rounded-full ${log.is_latest ? 'bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-300 dark:border-green-900/30' : 'bg-green-50 dark:bg-green-900/10 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-900/20'} text-xs font-black uppercase tracking-wider">IN</span>`
                : `<span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-xs font-black uppercase tracking-wider border border-red-200 dark:border-red-900/30">OUT</span>`;
            
            return `
                <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors ${colorClass}">
                    <td class="px-4 lg:px-6 py-4 lg:py-5">
                        <span class="text-xs font-extrabold ${idColor}">#${log.id_number}</span>
                    </td>
                    <td class="px-4 lg:px-6 py-4 lg:py-5">
                        <div class="flex items-center gap-3">
                            ${avatarHtml}
                            <span class="font-bold text-sm text-gray-900 dark:text-white">${log.full_name}</span>
                        </div>
                    </td>
                    <td class="px-4 lg:px-6 py-4 lg:py-5 text-xs font-semibold text-gray-500 dark:text-gray-400">${(log.department || 'N/A').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ')}</td>
                    <td class="px-4 lg:px-6 py-4 lg:py-5">${statusBadge}</td>
                    <td class="px-4 lg:px-6 py-4 lg:py-5 text-right text-xs font-black tabular-nums ${log.check_in_status === 'green' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">${log.time_in}</td>
                    <td class="px-4 lg:px-6 py-4 lg:py-5 text-right text-xs font-black tabular-nums ${log.time_out !== '-' ? (log.check_out_status === 'green' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400') : 'text-gray-400 dark:text-gray-500'}">
                        ${log.time_out !== '-' ? log.time_out : '<span class="text-gray-400 dark:text-gray-500">-</span>'}
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    // Update mobile cards
    if (mobileCards) {
        mobileCards.innerHTML = logs.map((log, index) => {
            const colorClass = log.is_latest ? 'bg-primary/5 dark:bg-primary/10' : '';
            const idColor = log.is_latest ? 'text-primary' : 'text-gray-500 dark:text-gray-400';
            const timeColor = log.is_latest ? 'text-primary' : 'text-gray-500 dark:text-gray-400';
            
            let avatarHtml = '';
            if (log.profile_photo) {
                const borderColor = log.is_latest ? 'border-primary' : 'border-gray-200 dark:border-gray-700';
                avatarHtml = `<img src="<?= base_url('uploads/profiles/') ?>${log.profile_photo}" 
                                 alt="${log.full_name}" 
                                 class="size-10 rounded-full object-cover border-2 ${borderColor}">`;
            } else {
                const bgColor = log.is_latest ? 'bg-primary/20 text-primary' : `bg-${log.color}-100 dark:bg-${log.color}-900/30 text-${log.color}-600 dark:text-${log.color}-400`;
                avatarHtml = `<div class="size-10 rounded-full ${bgColor} flex items-center justify-center font-black text-sm">
                                ${log.initials}
                              </div>`;
            }
            
            const statusBadge = log.status === 'IN'
                ? `<span class="inline-flex items-center px-2.5 py-1 rounded-full ${log.is_latest ? 'bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400 border border-green-300 dark:border-green-900/30' : 'bg-green-50 dark:bg-green-900/10 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-900/20'} text-xs font-black uppercase">IN</span>`
                : `<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-xs font-black uppercase border border-red-200 dark:border-red-900/30">OUT</span>`;
            
            return `
                <div class="p-4 ${colorClass} hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            ${avatarHtml}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm text-gray-900 dark:text-white truncate">${log.full_name}</p>
                                    <p class="text-xs font-medium ${idColor}">#${log.id_number}</p>
                                </div>
                                ${statusBadge}
                            </div>
                            <div class="flex flex-col gap-1.5 text-xs">
                                <span class="font-semibold text-gray-600 dark:text-gray-400">${(log.department || 'N/A').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ')}</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-black tabular-nums ${log.check_in_status === 'green' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">In: ${log.time_in}</span>
                                    ${log.time_out !== '-' ? `<span class="text-gray-300 dark:text-gray-700">•</span><span class="font-black tabular-nums ${log.check_out_status === 'green' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">Out: ${log.time_out}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    // Reapply search filter after table update
    const searchInput = document.getElementById('search-input');
    if (searchInput && searchInput.value) {
        searchTable();
    }
    
    const recordCountEl = document.getElementById('record-count');
    if (responseData && responseData.total_records !== undefined && recordCountEl) {
        const currentPage = <?= $current_page ?>;
        const perPage = <?= $per_page ?>;
        const start = (currentPage - 1) * perPage + 1;
        const end = Math.min(currentPage * perPage, responseData.total_records);
        recordCountEl.textContent = `Showing ${start}-${end} of ${responseData.total_records} Workers`;
    }
}

function updateSystemStatus(activeReaders) {
    const isSystemActive = activeReaders > 0;
    const statusIndicator = document.getElementById('system-status-indicator');
    const statusText = document.getElementById('system-status-text');
    
    if (statusIndicator && statusText) {
        if (isSystemActive) {
            statusIndicator.className = 'material-symbols-outlined text-green-500 text-sm';
            statusText.textContent = 'Live System: Active';
        } else {
            statusIndicator.className = 'material-symbols-outlined text-red-500 text-sm';
            statusText.textContent = 'Live System: Inactive';
        }
    }
}

// Update reader stats
function updateReaderStats(activeReaders, totalReaders) {
    const readerStatsEl = document.getElementById('reader-stats');
    if (readerStatsEl) {
        readerStatsEl.textContent = `${activeReaders} ACTIVE / ${totalReaders - activeReaders} OFFLINE`;
    }
}

// Search table function
function searchTable() {
    const input = document.getElementById('search-input');
    const filter = input.value.toUpperCase();
    
    // Search in desktop table
    const table = document.getElementById('activity-table');
    if (table) {
        const tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            const tdId = tr[i].getElementsByTagName('td')[0];
            const tdName = tr[i].getElementsByTagName('td')[1];
            
            if (tdId || tdName) {
                const idText = tdId ? tdId.textContent || tdId.innerText : '';
                const nameText = tdName ? tdName.textContent || tdName.innerText : '';
                
                if (idText.toUpperCase().indexOf(filter) > -1 || nameText.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = '';
                } else {
                    tr[i].style.display = 'none';
                }
            }
        }
    }
    
    // Search in mobile cards
    const mobileCards = document.getElementById('mobile-activity-cards');
    if (mobileCards) {
        const cards = mobileCards.children;
        for (let i = 0; i < cards.length; i++) {
            const cardText = cards[i].textContent || cards[i].innerText;
            if (cardText.toUpperCase().indexOf(filter) > -1) {
                cards[i].style.display = '';
            } else {
                cards[i].style.display = 'none';
            }
        }
    }
}

// Manual refresh button
function manualRefresh() {
    updateMonitoringData();
}

// Export to CSV Function
function exportToCSV() {
    const table = document.querySelector('table');
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = 'rfid_monitoring_' + new Date().toISOString().slice(0, 10) + '.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Update asset tracking table
function updateAssetTable(assets) {
    const container = document.getElementById('asset-tracking-section');
    if (!container) return;
    
    const badge = container.querySelector('[data-asset-count]');
    if (badge) badge.textContent = (assets ? assets.length : 0) + ' Assets';
    
    const tbody = container.querySelector('tbody');
    if (!tbody) return;
    
    if (!assets || assets.length === 0) {
        tbody.innerHTML = `<tr>
            <td colspan="4" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                <div class="flex flex-col items-center gap-2">
                    <span class="material-symbols-outlined text-3xl text-gray-400">inventory_2</span>
                    <p class="text-sm font-semibold">No assets detected in this zone yet</p>
                    <p class="text-xs text-gray-400">Assets will appear here when their EPC tags are scanned by the reader</p>
                </div>
            </td>
        </tr>`;
        return;
    }
    
    tbody.innerHTML = assets.map(asset => {
                const workerName = asset.first_name 
                    ? `<div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-500 text-sm">person</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">${asset.first_name} ${asset.last_name}</span>
                        <span class="text-xs text-gray-500">(${asset.w_id})</span>
                       </div>`
                    : '<span class="text-xs text-gray-400">Unassigned</span>';
                
                const lastSeen = asset.last_seen_at 
                    ? new Date(asset.last_seen_at).toLocaleTimeString('en-GB', {hour:'2-digit', minute:'2-digit', second:'2-digit'})
                    : '-';
                
                return `<tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                    <td class="px-4 lg:px-6 py-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-orange-500 text-lg">package_2</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">${asset.asset_name}</span>
                        </div>
                    </td>
                    <td class="px-4 lg:px-6 py-3">
                        <span class="text-xs font-mono font-bold text-gray-600 dark:text-gray-400">${asset.epc_no || '-'}</span>
                    </td>
                    <td class="px-4 lg:px-6 py-3">${workerName}</td>
                    <td class="px-4 lg:px-6 py-3">
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">${lastSeen}</span>
                    </td>
                </tr>`;
            }).join('');
}

// Update current date and time display
function updateDateTime() {
    const now = new Date();
    const dateEl = document.getElementById('current-date');
    const timeEl = document.getElementById('current-time');
    if (dateEl) dateEl.textContent = now.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
    if (timeEl) timeEl.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Start date/time clock
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
    // Start auto-update every 1 second for real-time monitoring
    updateInterval = setInterval(updateMonitoringData, 1000);
    
    // Initial update immediately
    setTimeout(updateMonitoringData, 500);
});

// Clear interval when leaving page
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
</script>

<?= $this->include('templates/footer') ?>
