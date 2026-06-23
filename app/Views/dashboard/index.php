<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between gap-2 mt-6 md:mt-4">
        <div class="flex flex-col gap-1">
            <p class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Productivity Dashboard</p>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-normal">Monitor zone activity and worker presence in real-time.</p>
            <!-- Active date range badge -->
            <div class="flex items-center gap-2 mt-1">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 dark:bg-primary/20 border border-primary/20 dark:border-primary/30 text-primary dark:text-blue-300 text-xs font-semibold">
                    <span class="material-symbols-outlined text-sm">calendar_today</span>
                    <span id="filterLabelBadge"><?= esc($filter_label) ?></span>
                </span>
                <span class="text-gray-500 dark:text-gray-400 text-xs" id="filterDateRange">
                    <?php if ($start_date === $end_date): ?>
                        <?= date('l, F j, Y', strtotime($start_date)) ?>
                    <?php else: ?>
                        <?= date('M j', strtotime($start_date)) ?> – <?= date('M j, Y', strtotime($end_date)) ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <span id="liveIndicator" class="inline-flex items-center gap-1.5">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                </span>
                Live · Updates every 5s
            </span>
        </div>
    </div>

    <!-- ── Top Stats Row ───────────────────────────────────────────────── -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <!-- Zones -->
        <div class="flex flex-col gap-0.5 rounded-lg p-3 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-primary/50 hover:shadow-sm transition-all"
             onclick="openZonesModal()">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium">Zones</p>
            <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= esc($stats['total_zones']) ?></p>
        </div>
        <!-- Check-ins -->
        <div class="flex flex-col gap-0.5 rounded-lg p-3 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-primary/50 hover:shadow-sm transition-all"
             onclick="openCheckinsModal()">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium">Check-ins</p>
            <div class="flex items-end gap-1.5">
                <p class="text-gray-900 dark:text-white text-2xl font-bold" id="statCheckins"><?= esc($stats['total_checkins']) ?></p>
                <p class="text-<?= $stats['checkins_change_color'] ?>-500 text-xs font-medium pb-0.5 <?= empty($stats['checkins_change']) ? 'hidden' : '' ?>" id="statCheckinsChange"><?= esc($stats['checkins_change']) ?></p>
            </div>
        </div>
        <!-- Avg Time -->
        <div class="flex flex-col gap-0.5 rounded-lg p-3 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-primary/50 hover:shadow-sm transition-all"
             onclick="openAvgTimeModal()">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium">Avg Time/Zone</p>
            <p class="text-gray-900 dark:text-white text-2xl font-bold" id="statAvgTime"><?= esc($stats['avg_time_in_zone']) ?></p>
        </div>
        <!-- Absent -->
        <div class="flex flex-col gap-0.5 rounded-lg p-3 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-red-300 hover:shadow-sm transition-all"
             onclick="openAbsentModal()">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium">Absent</p>
            <p class="text-red-600 dark:text-red-400 text-2xl font-bold" id="statAbsent"><?= esc($stats['absent_count']) ?></p>
        </div>
        <!-- Late -->
        <div class="flex flex-col gap-0.5 rounded-lg p-3 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-amber-300 hover:shadow-sm transition-all"
             onclick="openLateModal()">
            <p class="text-gray-500 dark:text-gray-400 text-xs font-medium">Late</p>
            <p class="text-amber-500 dark:text-amber-400 text-2xl font-bold" id="statLate"><?= esc($stats['late_count']) ?></p>
        </div>
    </div>

    <!-- ── Main Content: Activity + Sidebars ─────────────────────────── -->
    <div class="flex flex-col lg:flex-row gap-4">

        <!-- Activity Log -->
        <div class="flex-1 min-w-0 bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col">
            <div class="flex flex-wrap justify-between items-center gap-2 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Worker Activity Log</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Search -->
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-base">search</span>
                        <input id="searchInput" onkeyup="filterTable()" class="pl-8 pr-2 h-9 w-44 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-xs rounded-lg focus:ring-primary focus:border-primary" placeholder="Search..." type="text"/>
                    </div>
                    <!-- Date Filter -->
                    <div class="relative">
                        <button id="dateFilterBtn" onclick="toggleDateFilter()" class="flex items-center gap-1.5 h-9 px-3 text-gray-700 dark:text-gray-300 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium hover:bg-gray-50 dark:hover:bg-gray-800">
                            <span class="material-symbols-outlined text-base">calendar_today</span>
                            <span id="dateFilterLabel"><?= esc($filter_label) ?></span>
                            <span class="material-symbols-outlined text-base">expand_more</span>
                        </button>
                        <div id="dateFilterDropdown" class="hidden absolute right-0 mt-1 w-52 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50">
                            <div class="py-1">
                                <button onclick="filterByDate('today')" class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Today</button>
                                <button onclick="filterByDate('yesterday')" class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Yesterday</button>
                                <button onclick="filterByDate('week')" class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">This Week</button>
                                <button onclick="filterByDate('month')" class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">This Month</button>
                                <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                <div class="px-4 py-2">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Date</label>
                                    <input type="date" id="customDate" value="<?= esc($custom_date ?? '') ?>" onchange="filterByCustomDate()" class="w-full px-2 py-1.5 text-xs bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-primary focus:border-primary">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Export -->
                    <button onclick="exportToCSV()" class="flex items-center gap-1.5 h-9 px-3 bg-primary text-white rounded-lg text-xs font-bold hover:bg-primary/90">
                        <span class="material-symbols-outlined text-base">download</span>
                        Export
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto" style="height: calc(100vh - 380px); min-height: 240px;">
                <table id="activityTable" class="w-full text-xs text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
                        <tr>
                            <?php
                            $sortTh = 'px-3 py-2 whitespace-nowrap cursor-pointer select-none hover:text-primary transition-colors';
                            $sortIcon = '<span class="material-symbols-outlined sort-icon text-sm opacity-70">unfold_more</span>';
                            $sortWrap = fn($label, $col) => '<th data-sort="' . $col . '" class="' . $sortTh . '"><span class="inline-flex items-center gap-0.5">' . $label . $sortIcon . '</span></th>';
                            ?>
                            <?= $sortWrap('Worker', 'name') ?>
                            <?= $sortWrap('Dept.', 'department') ?>
                            <?= $sortWrap('ID', 'id') ?>
                            <?= $sortWrap('Date', 'date') ?>
                            <?= $sortWrap('First In', 'time_in') ?>
                            <?= $sortWrap('Last Out', 'time_out') ?>
                            <?= $sortWrap('Zone', 'zone') ?>
                            <?= $sortWrap('Entry', 'entry') ?>
                            <?= $sortWrap('Exit', 'exit') ?>
                            <?= $sortWrap('Duration', 'duration') ?>
                            <?= $sortWrap('Not in Zone', 'not_in_zone') ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($worker_activity)): ?>
                            <tr>
                                <td colspan="11" class="px-3 py-10 text-center text-gray-400 dark:text-gray-500">
                                    <span class="material-symbols-outlined text-4xl block mb-2">assignment</span>
                                    No worker activity recorded for this period.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($worker_activity as $worker): ?>
                                <?php foreach ($worker['zones'] as $index => $zone): ?>
                                    <tr class="bg-white dark:bg-background-dark border-b dark:border-gray-700 hover:bg-primary/5 dark:hover:bg-primary/10 cursor-pointer transition-colors"
                                        onclick="openWorkerModal('<?= esc($worker['worker_id']) ?>','<?= esc($worker['date']) ?>')"
                                        data-worker-id="<?= esc($worker['worker_id']) ?>">
                                        <?php if ($index === 0): ?>
                                            <td class="px-3 py-2 font-medium text-primary whitespace-nowrap align-top" rowspan="<?= count($worker['zones']) ?>">
                                                <?= esc($worker['name']) ?>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap align-top" rowspan="<?= count($worker['zones']) ?>"><?= esc($worker['department']) ?></td>
                                            <td class="px-3 py-2 whitespace-nowrap font-mono align-top" rowspan="<?= count($worker['zones']) ?>"><?= esc($worker['id_tag']) ?></td>
                                            <td class="px-3 py-2 whitespace-nowrap align-top font-medium text-gray-700 dark:text-gray-300" rowspan="<?= count($worker['zones']) ?>"><?= esc($worker['date_label']) ?></td>
                                            <td class="px-3 py-2 whitespace-nowrap align-top font-semibold <?= $worker['is_late'] ? 'text-red-500' : 'text-green-600 dark:text-green-400' ?>" rowspan="<?= count($worker['zones']) ?>"><?= esc($worker['time_in']) ?></td>
                                            <td class="px-3 py-2 whitespace-nowrap align-top" rowspan="<?= count($worker['zones']) ?>"><?= esc($worker['time_out']) ?></td>
                                        <?php endif; ?>
                                        <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-700 dark:text-gray-300"><?= esc($zone['name']) ?></td>
                                        <td class="px-3 py-2 whitespace-nowrap"><?= esc($zone['entry']) ?></td>
                                        <td class="px-3 py-2 whitespace-nowrap <?= $zone['exit'] === 'Active' ? 'text-green-600 dark:text-green-400 font-medium' : '' ?>"><?= esc($zone['exit']) ?></td>
                                        <?php if ($zone['exit'] === 'Active' && !empty($zone['entry_ts'])): ?>
                                            <td class="px-3 py-2 whitespace-nowrap tabular-nums text-green-600 dark:text-green-400 font-medium"
                                                data-zone-entry-ts="<?= esc($zone['entry_ts']) ?>"><?= esc($zone['duration']) ?></td>
                                        <?php else: ?>
                                            <td class="px-3 py-2 whitespace-nowrap"><?= esc($zone['duration']) ?></td>
                                        <?php endif; ?>
                                        <?php if ($index === 0): ?>
                                            <td class="px-3 py-2 whitespace-nowrap align-top font-medium text-orange-500 dark:text-orange-400 tabular-nums"
                                                rowspan="<?= count($worker['zones']) ?>"
                                                data-notinzone-base="<?= (int) ($worker['not_in_zone_sec'] ?? 0) ?>"
                                                data-notinzone-live="<?= !empty($worker['not_in_zone_live']) ? '1' : '0' ?>"
                                                data-notinzone-until="<?= (int) ($worker['not_in_zone_until'] ?? 0) ?>"
                                                data-notinzone-server="<?= (int) ($worker['server_time'] ?? time()) ?>"
                                                data-has-active-zone="<?= !empty($worker['has_active_zone']) ? '1' : '0' ?>"><?= esc($worker['not_in_zone']) ?></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Right Sidebar ──────────────────────────────────────────── -->
        <aside class="w-full lg:w-64 flex-shrink-0 flex flex-col gap-4 lg:self-start">

            <!-- Live Summary -->
            <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Live Summary</h3>
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                </div>
                <div class="flex flex-col gap-2.5">
                    <!-- People in any area -->
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 -mx-2 px-2 rounded transition-colors"
                         onclick="openInAreaModal()">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-primary">groups</span>
                            <span class="text-xs text-gray-600 dark:text-gray-300">In area now</span>
                        </div>
                        <span class="text-sm font-bold text-primary" id="liveInArea"><?= esc($summary['total_in_area']) ?></span>
                    </div>
                    <!-- Came in -->
                    <div class="flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 -mx-2 px-2 rounded transition-colors"
                         onclick="openCameInModal()">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-green-500">login</span>
                            <span class="text-xs text-gray-600 dark:text-gray-300">Came in</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white" id="liveCameIn"><?= esc($summary['total_came_in']) ?></span>
                    </div>
                    <!-- Left -->
                    <div class="flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 -mx-2 px-2 rounded transition-colors"
                         onclick="openLeftModal()">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-red-400">logout</span>
                            <span class="text-xs text-gray-600 dark:text-gray-300">Left place</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white" id="liveLeft"><?= esc($summary['total_left']) ?></span>
                    </div>
                    <!-- Total transactions -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-purple-500">swap_horiz</span>
                            <span class="text-xs text-gray-600 dark:text-gray-300">Transactions</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white" id="liveTransactions"><?= esc($summary['total_transactions']) ?></span>
                    </div>
                    <!-- Avg time per person -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-blue-500">timer</span>
                            <span class="text-xs text-gray-600 dark:text-gray-300">Avg time/person</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white" id="liveAvgTime"><?= esc($summary['avg_person_time']) ?></span>
                    </div>
                    <!-- Avg time NOT in any zone -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-orange-400">location_off</span>
                            <span class="text-xs text-gray-600 dark:text-gray-300">Avg not in zone</span>
                        </div>
                        <span class="text-sm font-bold text-orange-500 dark:text-orange-400" id="liveAvgNotInZone"><?= esc($summary['avg_not_in_zone']) ?></span>
                    </div>
                </div>

                <!-- Over 8h workers — always visible -->
                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 flex items-center gap-1 mb-2">
                        <span class="material-symbols-outlined text-sm">warning</span>
                        In place &gt;8h
                        <span id="liveOver8hCount" class="ml-1 px-1.5 py-0.5 rounded-full text-xs font-bold <?= !empty($summary['over_8h']) ? 'bg-amber-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' ?>">
                            <?= count($summary['over_8h']) ?>
                        </span>
                    </p>
                    <div id="liveOver8hList" class="flex flex-col gap-1.5 overflow-y-auto <?= empty($summary['over_8h']) ? 'hidden' : '' ?>" style="max-height: 120px;">
                        <?php foreach ($summary['over_8h'] as $oh): ?>
                            <div class="flex items-center justify-between gap-2">
                                <a href="<?= base_url('workers/tracking/' . esc($oh['id']) . '?date=' . esc($selected_date)) ?>"
                                   class="text-xs text-gray-700 dark:text-gray-300 hover:text-primary truncate"><?= esc($oh['name']) ?></a>
                                <span class="text-xs font-bold <?= $oh['still_in'] ? 'text-red-500' : 'text-amber-500' ?> whitespace-nowrap"><?= esc($oh['duration']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p id="liveOver8hEmpty" class="text-xs text-gray-400 italic <?= !empty($summary['over_8h']) ? 'hidden' : '' ?>">No workers currently over 8 hours.</p>
                </div>
            </div>

            <!-- Live Zone Occupancy -->
            <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Zone Occupancy (Live)</h3>
                <div id="zoneOccupancyList" class="flex flex-col gap-1 overflow-y-auto" style="max-height: 180px;">
                    <?php foreach ($live_occupancy as $i => $zone): ?>
                        <div class="flex justify-between items-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 -mx-2 px-2 py-1 rounded transition-colors"
                             onclick="openZoneOccupancyDetail(<?= $i ?>)">
                            <p class="text-xs text-gray-700 dark:text-gray-300 truncate pr-2"><?= esc($zone['name']) ?></p>
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full whitespace-nowrap flex-shrink-0 <?= $zone['count'] > 0 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400' ?>">
                                <?= esc($zone['count']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Avg Time per Zone -->
            <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Avg Time per Zone</h3>
                <div id="avgZoneTimeList" class="flex flex-col gap-1 overflow-y-auto" style="max-height: 150px;">
                    <?php foreach ($avg_zone_time as $i => $zone): ?>
                        <div class="flex justify-between items-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 -mx-2 px-2 py-1 rounded transition-colors"
                             onclick="openZoneAvgDetail(<?= $i ?>)">
                            <p class="text-xs text-gray-700 dark:text-gray-300 truncate pr-2"><?= esc($zone['name']) ?></p>
                            <span class="text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap"><?= esc($zone['time']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- ── Generic List Modal ──────────────────────────────────────────────── -->
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

<!-- ── Worker Detail Modal ──────────────────────────────────────────────── -->
<div id="workerModal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/60 backdrop-blur-sm" onclick="closeWorkerModal(event)">
    <div class="bg-white dark:bg-background-dark rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col border border-gray-200 dark:border-gray-700 animate-modal" onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary/10 dark:bg-primary/20 rounded-xl">
                    <span class="material-symbols-outlined text-primary text-xl">badge</span>
                </div>
                <div>
                    <h3 id="modalWorkerName" class="text-gray-900 dark:text-white font-bold text-base leading-tight"></h3>
                    <p id="modalWorkerMeta" class="text-gray-500 dark:text-gray-400 text-xs mt-0.5"></p>
                </div>
            </div>
            <button onclick="closeWorkerModal()" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-2 gap-3 px-5 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">First In</p>
                <p id="modalFirstIn" class="text-gray-900 dark:text-white font-bold text-sm"></p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Last Out</p>
                <p id="modalLastOut" class="text-gray-900 dark:text-white font-bold text-sm"></p>
            </div>
        </div>

        <!-- Zone visits table -->
        <div class="flex-1 overflow-y-auto px-5 py-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Zone Activity</p>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <th class="pb-2 text-left font-medium">Gate / Zone</th>
                        <th class="pb-2 text-left font-medium">Entry</th>
                        <th class="pb-2 text-left font-medium">Exit</th>
                        <th class="pb-2 text-left font-medium">Duration</th>
                    </tr>
                </thead>
                <tbody id="modalZonesBody" class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs">
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800/40 rounded-b-2xl">
            <span class="text-xs text-gray-400">Full timeline &amp; time breakdown</span>
            <a id="modalViewLink" href="#"
               class="flex items-center gap-1.5 px-4 py-2 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary/90 transition-colors">
                <span class="material-symbols-outlined text-sm">open_in_new</span>
                View Full Details
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
// ── Helpers ────────────────────────────────────────────────────────────────
function escH(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatSec(sec, showSeconds = false) {
    sec = Math.max(0, Math.floor(sec));
    const h = Math.floor(sec / 3600);
    const m = Math.floor((sec % 3600) / 60);
    const s = sec % 60;
    if (h > 0) {
        if (showSeconds) return h + 'h ' + m + 'm ' + s + 's';
        return m === 0 ? h + 'h' : h + 'h ' + m + 'm';
    }
    if (m > 0) return m + 'm ' + s + 's';
    return s + 's';
}

function updateTimers() {
    const now = Math.floor(Date.now() / 1000);
    // Active zone duration counters
    document.querySelectorAll('[data-zone-entry-ts]').forEach(el => {
        const ts = parseInt(el.getAttribute('data-zone-entry-ts'));
        if (ts) el.textContent = formatSec(now - ts, true);
    });
    // Not-in-zone counters: server base + elapsed since server sync, capped at shift end
    document.querySelectorAll('[data-notinzone-base]').forEach(el => {
        const base      = parseInt(el.getAttribute('data-notinzone-base') || '0');
        const until     = parseInt(el.getAttribute('data-notinzone-until') || '0');
        const serverAt  = parseInt(el.getAttribute('data-notinzone-server') || '0');
        const hasActive = el.getAttribute('data-has-active-zone') === '1';
        const isLive    = !hasActive && until > 0 && now < until;
        const cap       = until > 0 ? Math.min(now, until) : now;
        const total     = isLive && serverAt ? base + Math.max(0, cap - serverAt) : base;
        el.textContent  = formatSec(total, isLive);
        el.setAttribute('data-notinzone-live', isLive ? '1' : '0');
    });
}
setInterval(updateTimers, 1000);
updateTimers();

// ── Modal data stores (refreshed on every AJAX tick) ──────────────────────
let liveOccupancyData = <?= json_encode(array_values($live_occupancy)) ?>;
let avgZoneTimeData   = <?= json_encode(array_values($avg_zone_time)) ?>;
let statsModalData    = {
    absent_workers: <?= json_encode($stats['absent_workers']) ?>,
    late_workers:   <?= json_encode($stats['late_workers']) ?>,
};
let summaryModalData = {
    in_area_workers: <?= json_encode($summary['in_area_workers']) ?>,
    came_in_workers: <?= json_encode($summary['came_in_workers']) ?>,
    left_workers:    <?= json_encode($summary['left_workers']) ?>,
};

// ── Generic list modal ─────────────────────────────────────────────────────
function openListModal(title, subtitle, rows) {
    document.getElementById('listModalTitle').textContent    = title;
    document.getElementById('listModalSubtitle').textContent = subtitle || '';
    let html = '';
    if (!rows || rows.length === 0) {
        html = '<p class="text-xs text-gray-400 italic text-center py-8">No records found.</p>';
    } else {
        rows.forEach(row => {
            html += `<div class="flex items-center justify-between py-2.5 border-b border-gray-100 dark:border-gray-700/60 last:border-0 gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">${escH(row.label)}</p>
                    ${row.sub ? `<p class="text-xs text-gray-500 dark:text-gray-400">${escH(row.sub)}</p>` : ''}
                </div>
                <span class="text-xs font-semibold whitespace-nowrap flex-shrink-0 ${row.cls || 'text-gray-600 dark:text-gray-300'}">${escH(row.val)}</span>
            </div>`;
        });
    }
    document.getElementById('listModalBody').innerHTML = html;
    const modal = document.getElementById('listModal');
    modal.classList.remove('hidden');
    const panel = modal.querySelector('.animate-modal');
    panel.style.animation = 'none';
    panel.offsetHeight;
    panel.style.animation = '';
    document.body.style.overflow = 'hidden';
}

function closeListModal(e) {
    if (e && e.target !== document.getElementById('listModal')) return;
    document.getElementById('listModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Stat card openers
function openZonesModal() {
    const rows = liveOccupancyData.map(z => ({
        label: z.name,
        val:   z.count + (z.count === 1 ? ' worker' : ' workers'),
        cls:   z.count > 0 ? 'text-primary font-bold' : 'text-gray-400 dark:text-gray-500',
    }));
    openListModal('Zone Occupancy', 'Workers currently in each zone', rows);
}

function openCheckinsModal() {
    const rows = summaryModalData.came_in_workers.map(w => ({
        label: w.name, sub: w.dept, val: w.time_in,
        cls: 'text-gray-700 dark:text-gray-300',
    }));
    openListModal('Check-ins Today', 'Workers who entered during this period', rows);
}

function openAvgTimeModal() {
    const rows = avgZoneTimeData.map(z => ({
        label: z.name,
        val:   z.time,
        cls:   z.time !== '-' ? 'text-gray-900 dark:text-white font-semibold' : 'text-gray-400',
    }));
    openListModal('Avg Time per Zone', 'Average time workers spend per zone visit', rows);
}

function openAbsentModal() {
    const rows = statsModalData.absent_workers.map(w => ({
        label: w.name, sub: w.dept,
        val:   w.shift + ' shift',
        cls:   'text-red-500',
    }));
    openListModal('Absent Workers', 'Active workers with no check-in this period', rows);
}

function openLateModal() {
    const rows = statsModalData.late_workers.map(w => ({
        label: w.name, sub: w.dept,
        val:   w.time_in + ' (due ' + w.shift_start + ')',
        cls:   'text-amber-500',
    }));
    openListModal('Late Workers', 'Workers who arrived after their shift start time', rows);
}

// Summary row openers
function openInAreaModal() {
    const rows = summaryModalData.in_area_workers.map(w => ({
        label: w.name, sub: w.dept, val: 'Since ' + w.time_in,
        cls: 'text-primary',
    }));
    openListModal('In Area Now', 'Workers currently inside any zone', rows);
}

function openCameInModal() {
    const rows = summaryModalData.came_in_workers.map(w => ({
        label: w.name, sub: w.dept, val: w.time_in,
        cls: 'text-green-600 dark:text-green-400',
    }));
    openListModal('Came In', 'All workers who entered today', rows);
}

function openLeftModal() {
    const rows = summaryModalData.left_workers.map(w => ({
        label: w.name, sub: w.dept, val: 'Left ' + w.time_out,
        cls: 'text-red-400',
    }));
    openListModal('Left Place', 'Workers who have left all zones', rows);
}

// Zone detail openers
function openZoneOccupancyDetail(idx) {
    const zone = liveOccupancyData[idx];
    if (!zone) return;
    const rows = zone.workers.map(w => ({
        label: w.name, val: 'Since ' + w.entry,
        cls: 'text-green-600 dark:text-green-400',
    }));
    openListModal(zone.name, 'Workers currently in this zone · ' + zone.count + ' total', rows);
}

function openZoneAvgDetail(idx) {
    const zone = avgZoneTimeData[idx];
    if (!zone) return;
    const rows = zone.workers.map(w => ({
        label: w.name,
        sub:   w.visits + (w.visits === 1 ? ' visit' : ' visits'),
        val:   w.avg_time,
        cls:   'text-gray-900 dark:text-white font-semibold',
    }));
    openListModal(zone.name + ' — Avg Time', 'Per-worker average time in this zone', rows);
}

// ── AJAX live update every 5 s ─────────────────────────────────────────────
let workerActivityData = <?= json_encode(array_values($worker_activity)) ?>;
let currentSelectedDate = '<?= esc($selected_date) ?>';
let currentFilterType = <?= json_encode($filter_type ?? 'today') ?>;
let currentCustomDate = <?= json_encode($custom_date ?? '') ?>;
let isLiveUpdating = false;
const LIVE_URL = '<?= base_url('dashboard/live-data') ?>';
const REFRESH_MS = 5000;
const activitySortState = { column: 'entry', dir: 'desc' };

function sortWorkerActivityList(workers) {
    if (!workers || !workers.length) return workers || [];
    const { column, dir } = activitySortState;
    const primaryZone = (w) => (w.zones && w.zones.length ? w.zones[0] : null);

    return sortBy(workers, (w) => {
        const zone = primaryZone(w);
        switch (column) {
            case 'name': return w.name;
            case 'department': return w.department;
            case 'id': return w.id_tag;
            case 'date': return w.date;
            case 'time_in': return parseClockMinutes(w.time_in);
            case 'time_out': return parseClockMinutes(w.time_out);
            case 'zone': return zone ? zone.name : '';
            case 'entry': return zone?.entry_ts ?? w.latest_activity_ts ?? 0;
            case 'exit': return zone ? zone.exit : '';
            case 'duration': return zone?.entry_ts ?? parseDurationSeconds(zone?.duration);
            case 'not_in_zone': return w.not_in_zone_sec ?? 0;
            case 'latest':
            default: return w.latest_activity_ts ?? 0;
        }
    }, dir);
}

function handleActivitySort(column) {
    toggleSortState(activitySortState, column, ['latest', 'entry', 'time_in', 'time_out', 'duration', 'not_in_zone'].includes(column) ? 'desc' : 'asc');
    updateSortableHeaders(document.getElementById('activityTable'), activitySortState.column, activitySortState.dir);
    renderActivityTable();
}

function renderActivityTable() {
    paintActivityTable(sortWorkerActivityList(workerActivityData));
    updateTimers();
}

function getLiveDataParams() {
    const params = new URLSearchParams();
    if (currentCustomDate) {
        params.set('date', currentCustomDate);
    } else {
        params.set('filter', currentFilterType || 'today');
    }
    return params;
}

function syncUrlParams() {
    const params = getLiveDataParams();
    const qs = params.toString();
    const url = qs ? '<?= base_url('dashboard') ?>?' + qs : '<?= base_url('dashboard') ?>';
    window.history.replaceState({}, '', url);
}

function formatFilterDateRange(start, end) {
    const optsLong = { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' };
    const optsShort = { month: 'short', day: 'numeric' };
    const optsEnd = { month: 'short', day: 'numeric', year: 'numeric' };
    const s = new Date(start + 'T00:00:00');
    const e = new Date(end + 'T00:00:00');
    if (start === end) {
        return s.toLocaleDateString('en-US', optsLong);
    }
    return s.toLocaleDateString('en-US', optsShort) + ' – ' + e.toLocaleDateString('en-US', optsEnd);
}

function updateFilterUI(data) {
    if (!data) return;
    if (data.filter_label) {
        const badge = document.getElementById('filterLabelBadge');
        const btn = document.getElementById('dateFilterLabel');
        if (badge) badge.textContent = data.filter_label;
        if (btn) btn.textContent = data.filter_label;
    }
    if (data.start_date && data.end_date) {
        const range = document.getElementById('filterDateRange');
        if (range) range.textContent = formatFilterDateRange(data.start_date, data.end_date);
    }
    if (data.filter_type) currentFilterType = data.filter_type;
    if (data.custom_date !== undefined) currentCustomDate = data.custom_date || '';
}

async function fetchLiveData() {
    if (isLiveUpdating) return;
    isLiveUpdating = true;
    try {
        const resp = await fetch(LIVE_URL + '?' + getLiveDataParams().toString());
        if (!resp.ok) return;
        const data = await resp.json();
        currentSelectedDate = data.selected_date;
        updateFilterUI(data);
        updateStats(data.stats);
        updateSummary(data.summary);
        updateZoneOccupancy(data.live_occupancy);
        updateAvgZoneTime(data.avg_zone_time);
        workerActivityData = data.worker_activity || [];
        renderActivityTable();
        const now = new Date();
        const t = now.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit', second: '2-digit'});
        document.getElementById('liveIndicator').innerHTML =
            `<span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
            </span>Live &middot; Updated ${t}`;
    } catch (e) {
        console.warn('Live update failed:', e);
    } finally {
        isLiveUpdating = false;
    }
}

function updateStats(s) {
    document.getElementById('statCheckins').textContent = s.total_checkins;
    const chg = document.getElementById('statCheckinsChange');
    if (chg) {
        if (s.checkins_change) {
            chg.textContent = s.checkins_change;
            chg.className = 'text-' + (s.checkins_change_color || 'gray') + '-500 text-xs font-medium pb-0.5';
            chg.classList.remove('hidden');
        } else {
            chg.classList.add('hidden');
        }
    }
    document.getElementById('statAvgTime').textContent = s.avg_time_in_zone;
    document.getElementById('statAbsent').textContent  = s.absent_count;
    document.getElementById('statLate').textContent    = s.late_count;
    statsModalData.absent_workers = s.absent_workers || [];
    statsModalData.late_workers   = s.late_workers   || [];
}

function updateSummary(s) {
    summaryModalData.in_area_workers = s.in_area_workers || [];
    summaryModalData.came_in_workers = s.came_in_workers || [];
    summaryModalData.left_workers    = s.left_workers    || [];
    document.getElementById('liveInArea').textContent       = s.total_in_area;
    document.getElementById('liveCameIn').textContent       = s.total_came_in;
    document.getElementById('liveLeft').textContent         = s.total_left;
    document.getElementById('liveTransactions').textContent = s.total_transactions;
    document.getElementById('liveAvgTime').textContent      = s.avg_person_time;
    document.getElementById('liveAvgNotInZone').textContent = s.avg_not_in_zone;
    const count = s.over_8h.length;
    const badge = document.getElementById('liveOver8hCount');
    const list  = document.getElementById('liveOver8hList');
    const empty = document.getElementById('liveOver8hEmpty');
    badge.textContent = count;
    badge.className = 'ml-1 px-1.5 py-0.5 rounded-full text-xs font-bold ' +
        (count > 0 ? 'bg-amber-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400');
    if (count > 0) {
        let html = '';
        s.over_8h.forEach(w => {
            const cls = w.still_in ? 'text-red-500' : 'text-amber-500';
            html += `<div class="flex items-center justify-between gap-2">
                <a href="<?= base_url('workers/tracking/') ?>${escH(w.id)}?date=${escH(currentSelectedDate)}"
                   class="text-xs text-gray-700 dark:text-gray-300 hover:text-primary truncate">${escH(w.name)}</a>
                <span class="text-xs font-bold ${cls} whitespace-nowrap">${escH(w.duration)}</span>
            </div>`;
        });
        list.innerHTML = html;
        list.classList.remove('hidden');
        empty.classList.add('hidden');
    } else {
        list.innerHTML = '';
        list.classList.add('hidden');
        empty.classList.remove('hidden');
    }
}

function updateZoneOccupancy(zones) {
    liveOccupancyData = zones;
    let html = '';
    zones.forEach((z, i) => {
        const cls = z.count > 0 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400';
        html += `<div class="flex justify-between items-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 -mx-2 px-2 py-1 rounded transition-colors"
            onclick="openZoneOccupancyDetail(${i})">
            <p class="text-xs text-gray-700 dark:text-gray-300 truncate pr-2">${escH(z.name)}</p>
            <span class="px-2 py-0.5 text-xs font-bold rounded-full whitespace-nowrap flex-shrink-0 ${cls}">${z.count}</span>
        </div>`;
    });
    document.getElementById('zoneOccupancyList').innerHTML = html;
}

function updateAvgZoneTime(zones) {
    avgZoneTimeData = zones;
    let html = '';
    zones.forEach((z, i) => {
        html += `<div class="flex justify-between items-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 -mx-2 px-2 py-1 rounded transition-colors"
            onclick="openZoneAvgDetail(${i})">
            <p class="text-xs text-gray-700 dark:text-gray-300 truncate pr-2">${escH(z.name)}</p>
            <span class="text-xs font-semibold text-gray-900 dark:text-white whitespace-nowrap">${escH(z.time)}</span>
        </div>`;
    });
    document.getElementById('avgZoneTimeList').innerHTML = html;
}

function paintActivityTable(workers) {
    const tbody = document.querySelector('#activityTable tbody');
    if (!workers || workers.length === 0) {
        tbody.innerHTML = `<tr><td colspan="11" class="px-3 py-10 text-center text-gray-400 dark:text-gray-500">
            <span class="material-symbols-outlined text-4xl block mb-2">assignment</span>
            No worker activity recorded for this period.</td></tr>`;
        return;
    }
    let html = '';
    workers.forEach(worker => {
        const rs = worker.zones.length;
        worker.zones.forEach((zone, i) => {
            const active    = zone.exit === 'Active';
            const lateClass = worker.is_late ? 'text-red-500' : 'text-green-600 dark:text-green-400';
            html += `<tr class="bg-white dark:bg-background-dark border-b dark:border-gray-700 hover:bg-primary/5 dark:hover:bg-primary/10 cursor-pointer transition-colors"
                onclick="openWorkerModal('${escH(worker.worker_id)}','${escH(worker.date)}')"
                data-worker-id="${escH(worker.worker_id)}">`;
            if (i === 0) {
                html += `<td class="px-3 py-2 font-medium text-primary whitespace-nowrap align-top" rowspan="${rs}">${escH(worker.name)}</td>`;
                html += `<td class="px-3 py-2 whitespace-nowrap align-top" rowspan="${rs}">${escH(worker.department)}</td>`;
                html += `<td class="px-3 py-2 whitespace-nowrap font-mono align-top" rowspan="${rs}">${escH(worker.id_tag)}</td>`;
                html += `<td class="px-3 py-2 whitespace-nowrap align-top font-medium text-gray-700 dark:text-gray-300" rowspan="${rs}">${escH(worker.date_label)}</td>`;
                html += `<td class="px-3 py-2 whitespace-nowrap align-top font-semibold ${lateClass}" rowspan="${rs}">${escH(worker.time_in)}</td>`;
                html += `<td class="px-3 py-2 whitespace-nowrap align-top" rowspan="${rs}">${escH(worker.time_out)}</td>`;
            }
            html += `<td class="px-3 py-2 whitespace-nowrap font-medium text-gray-700 dark:text-gray-300">${escH(zone.name)}</td>`;
            html += `<td class="px-3 py-2 whitespace-nowrap">${escH(zone.entry)}</td>`;
            html += `<td class="px-3 py-2 whitespace-nowrap ${active ? 'text-green-600 dark:text-green-400 font-medium' : ''}">${escH(zone.exit)}</td>`;
            if (active && zone.entry_ts) {
                html += `<td class="px-3 py-2 whitespace-nowrap tabular-nums text-green-600 dark:text-green-400 font-medium" data-zone-entry-ts="${zone.entry_ts}">${escH(zone.duration)}</td>`;
            } else {
                html += `<td class="px-3 py-2 whitespace-nowrap">${escH(zone.duration)}</td>`;
            }
            if (i === 0) {
                const live = worker.not_in_zone_live ? '1' : '0';
                const activeZone = worker.has_active_zone ? '1' : '0';
                const base = worker.not_in_zone_sec || 0;
                const until = worker.not_in_zone_until || 0;
                const serverAt = worker.server_time || Math.floor(Date.now() / 1000);
                html += `<td class="px-3 py-2 whitespace-nowrap align-top font-medium text-orange-500 dark:text-orange-400 tabular-nums" rowspan="${rs}" data-notinzone-base="${base}" data-notinzone-live="${live}" data-notinzone-until="${until}" data-notinzone-server="${serverAt}" data-has-active-zone="${activeZone}">${escH(worker.not_in_zone)}</td>`;
            }
            html += '</tr>';
        });
    });
    tbody.innerHTML = html;
    filterTable();
}

document.addEventListener('DOMContentLoaded', () => {
    bindSortableHeaders(document.getElementById('activityTable'), handleActivitySort);
    updateSortableHeaders(document.getElementById('activityTable'), activitySortState.column, activitySortState.dir);
    fetchLiveData();
    setInterval(fetchLiveData, REFRESH_MS);
});

// ── Date filter (AJAX) ─────────────────────────────────────────────────────
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
    const customInput = document.getElementById('customDate');
    if (customInput) customInput.value = '';
    document.getElementById('dateFilterDropdown').classList.add('hidden');
    syncUrlParams();
    fetchLiveData();
}
function filterByCustomDate() {
    const v = document.getElementById('customDate').value;
    if (!v) return;
    currentCustomDate = v;
    currentFilterType = 'custom';
    document.getElementById('dateFilterDropdown').classList.add('hidden');
    syncUrlParams();
    fetchLiveData();
}

// ── Table search ───────────────────────────────────────────────────────────
function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#activityTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

// ── CSV export ─────────────────────────────────────────────────────────────
function exportToCSV() {
    const table = document.getElementById('activityTable');
    const rows  = table.querySelectorAll('tr');
    const csv   = [];
    const headers = [];
    rows[0].querySelectorAll('th').forEach(th => headers.push('"' + th.innerText.trim() + '"'));
    csv.push(headers.join(','));
    for (let i = 1; i < rows.length; i++) {
        if (rows[i].style.display === 'none') continue;
        const row = [];
        rows[i].querySelectorAll('td').forEach(td => {
            let text = (td.innerText || td.textContent || '').replace(/\n/g, ' ').replace(/\s+/g, ' ').trim().replace(/"/g, '""');
            row.push('"' + text + '"');
        });
        csv.push(row.join(','));
    }
    const blob = new Blob(['﻿' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = window.URL.createObjectURL(blob);
    a.download = 'worker_activity_<?= esc($filter_label) ?>.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

// ── Worker detail modal ────────────────────────────────────────────────────
function openWorkerModal(workerId, workerDate) {
    // Match by both worker_id and date so multi-day views open the right row
    const worker = workerActivityData.find(w =>
        String(w.worker_id) === String(workerId) &&
        (!workerDate || w.date === workerDate)
    ) || workerActivityData.find(w => String(w.worker_id) === String(workerId));
    if (!worker) return;

    document.getElementById('modalWorkerName').textContent = worker.name;
    document.getElementById('modalWorkerMeta').textContent =
        worker.department + (worker.department ? ' · ' : '') + 'ID: ' + worker.id_tag +
        (worker.date_label ? ' · ' + worker.date_label : '');
    document.getElementById('modalFirstIn').innerHTML =
        '<span class="' + (worker.is_late ? 'text-red-500' : 'text-green-600 dark:text-green-400') + '">' + worker.time_in + '</span>' +
        (worker.is_late ? ' <span class="text-xs text-red-400">(late)</span>' : '');
    document.getElementById('modalLastOut').textContent =
        worker.time_out === '-' ? 'Still active' : worker.time_out;

    document.getElementById('modalViewLink').href =
        '<?= base_url('workers/tracking/') ?>' + worker.worker_id + '?date=' + (worker.date || currentSelectedDate);

    let html = '';
    worker.zones.forEach(zone => {
        const isActive = zone.exit === 'Active';
        html += `<tr>
            <td class="py-2 pr-3 font-medium text-gray-800 dark:text-gray-200">
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm text-gray-400">location_on</span>
                    ${zone.name}
                </span>
            </td>
            <td class="py-2 pr-3 text-gray-600 dark:text-gray-400">${zone.entry}</td>
            <td class="py-2 pr-3 ${isActive ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-600 dark:text-gray-400'}">${zone.exit}</td>
            <td class="py-2 text-gray-600 dark:text-gray-400">${zone.duration}</td>
        </tr>`;
    });
    document.getElementById('modalZonesBody').innerHTML = html;

    const modal = document.getElementById('workerModal');
    modal.classList.remove('hidden');
    const panel = modal.querySelector('.animate-modal');
    panel.style.animation = 'none';
    panel.offsetHeight;
    panel.style.animation = '';
    document.body.style.overflow = 'hidden';
}

function closeWorkerModal(e) {
    if (e && e.target !== document.getElementById('workerModal')) return;
    document.getElementById('workerModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('workerModal').classList.add('hidden');
        document.getElementById('listModal').classList.add('hidden');
        document.body.style.overflow = '';
    }
});
</script>

<?= $this->include('templates/footer') ?>



