<?= $this->include('templates/header') ?>

<!-- Toast Notification Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 flex flex-col gap-2"></div>

<div class="flex flex-col gap-6 max-w-[1600px] w-full mx-auto">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="p-4 bg-green-100 dark:bg-green-900/20 border border-green-500 text-green-700 dark:text-green-400 rounded-lg">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')): ?>
        <div class="p-4 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mt-6 md:mt-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Attendance Management</h1>
            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Track worker attendance, check-in/check-out times, and generate attendance reports.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openCheckInModal()" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[20px]">login</span>
                <span>Manual Check-In/Out</span>
            </button>
            <button onclick="exportToCSV()" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[20px]">download</span>
                <span>Export</span>
            </button>
        </div>
    </div>

    <!-- Manual Check-In/Out Modal -->
    <div id="checkInModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-background-dark rounded-xl shadow-xl max-w-md w-full">
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Manual Check-In/Out</h3>
                <button onclick="closeCheckInModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form id="checkInForm" class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Worker</label>
                    <select id="workerSelect" onchange="updateZoneOptions()" class="w-full px-3 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary">
                        <option value="">Select Worker</option>
                        <?php foreach ($attendance as $worker): ?>
                            <?php if (!isset($worker['status']) || $worker['status'] !== 'inactive'): ?>
                                <option value="<?= esc($worker['worker_id']) ?>" 
                                        data-zones='<?= json_encode($worker['assigned_zones']) ?>'
                                        data-name="<?= esc($worker['name']) ?>"
                                        data-shift="<?= esc($worker['shift']) ?>"
                                        data-shift-start="<?= esc($worker['shift_start']) ?>">
                                    <?= esc($worker['name']) ?> (<?= esc($worker['worker_id']) ?>) - <?= esc($worker['shift']) ?> Shift
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Zone</label>
                    <select id="zoneSelect" class="w-full px-3 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary">
                        <option value="">Select worker first</option>
                    </select>
                    <p id="noZonesMessage" class="hidden mt-1 text-xs text-amber-600 dark:text-amber-400">
                        <span class="material-symbols-outlined text-sm align-middle">warning</span>
                        This worker has no assigned zones
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Action</label>
                    <div class="flex gap-3">
                        <button type="button" id="checkInBtn" onclick="setAction('in')" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 rounded-lg text-sm font-medium text-green-700 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                            <span class="material-symbols-outlined text-lg">login</span>
                            <span>Check In</span>
                        </button>
                        <button type="button" id="checkOutBtn" onclick="setAction('out')" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <span class="material-symbols-outlined text-lg">logout</span>
                            <span>Check Out</span>
                        </button>
                    </div>
                    <input type="hidden" id="actionType" value="in">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time</label>
                    <input type="time" id="timeInput" class="w-full px-3 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeCheckInModal()" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors">
                        Record
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
                    <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-2xl">check_circle</span>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs font-medium">Present Today</p>
                    <p class="text-gray-900 dark:text-white text-2xl font-bold stats-present">
                        <?php 
                        $presentCount = 0;
                        foreach ($attendance as $record) {
                            if ($record['time_in']) $presentCount++;
                        }
                        echo $presentCount;
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-2xl">schedule</span>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs font-medium">Avg Time In</p>
                    <p class="text-gray-900 dark:text-white text-2xl font-bold stats-avg-time">
                        <?php 
                        $totalMinutes = 0;
                        $count = 0;
                        foreach ($attendance as $record) {
                            if ($record['time_in']) {
                                // Convert time to minutes since midnight
                                $time = strtotime($record['time_in']);
                                $hours = (int)date('H', $time);
                                $minutes = (int)date('i', $time);
                                $totalMinutes += ($hours * 60) + $minutes;
                                $count++;
                            }
                        }
                        if ($count > 0) {
                            $avgMinutes = round($totalMinutes / $count);
                            $avgHours = floor($avgMinutes / 60);
                            $avgMins = $avgMinutes % 60;
                            echo sprintf('%d:%02d', $avgHours, $avgMins);
                        } else {
                            echo '-';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-amber-100 dark:bg-amber-900/20 rounded-lg">
                    <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-2xl">timer</span>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs font-medium">Avg Work Hours</p>
                    <p class="text-gray-900 dark:text-white text-2xl font-bold stats-avg-hours">
                        <?php 
                        $totalHours = 0;
                        $count = 0;
                        foreach ($attendance as $record) {
                            if ($record['work_hours']) {
                                // Extract numeric value from work_hours (e.g., "8.5h" -> 8.5)
                                $hours = (float)str_replace('h', '', $record['work_hours']);
                                $totalHours += $hours;
                                $count++;
                            }
                        }
                        if ($count > 0) {
                            echo number_format($totalHours / $count, 1) . 'h';
                        } else {
                            echo '-';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-red-100 dark:bg-red-900/20 rounded-lg">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">person_off</span>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-xs font-medium">Absent Today</p>
                    <p class="text-gray-900 dark:text-white text-2xl font-bold stats-absent">
                        <?php 
                        $absentCount = 0;
                        $currentTime = date('H:i:s');
                        foreach ($attendance as $record) {
                            // Check if worker hasn't checked in and current time is past their shift start
                            if (!$record['time_in']) {
                                $shiftKey = strtolower($record['shift']);
                                $shiftStartTime = $shift_times[$shiftKey] ?? '06:00:00';
                                if ($currentTime > $shiftStartTime) {
                                    $absentCount++;
                                }
                            }
                        }
                        echo $absentCount;
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-visible">
        <!-- Filter Section -->
        <div class="flex flex-wrap items-center gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-700 overflow-visible">
            <div class="relative flex-1 min-w-[200px]">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input id="searchInput" type="text" placeholder="Search by name, ID, or department..." 
                    class="w-full pl-10 pr-4 py-2 bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary"
                    onkeyup="filterTable()">
            </div>
            <div class="relative">
                <button id="filterBtn" onclick="toggleFilter()" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <span class="material-symbols-outlined text-lg">filter_list</span>
                    <span>Filters</span>
                    <span id="filterBadge" class="hidden ml-1 px-1.5 py-0.5 text-xs font-bold bg-primary text-white rounded-full"></span>
                </button>
                <!-- Filter Dropdown -->
                <div id="filterDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-visible">
                    <div class="p-4 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Filter Attendance</h3>
                            <button onclick="clearFilters()" class="text-xs text-primary hover:underline">Clear All</button>
                        </div>
                        
                        <!-- Department Filter -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                            <select id="departmentFilter" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm p-2">
                                <option value="">All Departments</option>
                                <?php if (!empty($departments)): ?>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= strtolower(esc($dept['name'])) ?>"><?= esc($dept['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select id="statusFilter" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm p-2">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="late">Late</option>
                                <option value="absent">Absent</option>
                                <option value="completed">Completed</option>
                                <option value="not started">Not Started</option>
                            </select>
                        </div>
                        
                        <!-- Shift Filter -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Shift</label>
                            <select id="shiftFilter" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm p-2">
                                <option value="">All Shifts</option>
                                <?php if (!empty($shifts)): ?>
                                    <?php foreach ($shifts as $shift): ?>
                                        <option value="<?= esc($shift['name']) ?>"><?= esc(ucfirst($shift['name'])) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <button onclick="applyFilters()" class="w-full px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
            <div class="relative">
                <button id="dateFilterBtn" onclick="toggleDateFilter()" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <span class="material-symbols-outlined text-lg">calendar_today</span>
                    <span id="dateFilterLabel"><?= esc($filter_label) ?></span>
                    <span class="material-symbols-outlined text-lg">expand_more</span>
                </button>
                <div id="dateFilterDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 overflow-visible">
                    <div class="py-1">
                        <button onclick="filterByDate('today')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Today</button>
                        <button onclick="filterByDate('yesterday')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Yesterday</button>
                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                        <div class="px-4 py-3 pb-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Custom Date</label>
                            <input type="date" id="customDate" onchange="filterByCustomDate()" class="w-full px-3 py-2 text-sm bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table id="attendanceTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-4 py-3 w-8"></th>
                        <th scope="col" class="px-4 py-3">Worker Name</th>
                        <th scope="col" class="px-4 py-3">ID Tag</th>
                        <th scope="col" class="px-4 py-3">Department</th>
                        <th scope="col" class="px-4 py-3">Shift</th>
                        <th scope="col" class="px-4 py-3">Time In</th>
                        <th scope="col" class="px-4 py-3">Time Out</th>
                        <th scope="col" class="px-4 py-3">Work Hours</th>
                        <th scope="col" class="px-4 py-3">Zones Visited</th>
                        <th scope="col" class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $index => $record): ?>
                        <tr data-worker-row="true" class="bg-white dark:bg-background-dark border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-4 py-3">
                                <?php if (!empty($record['zones'])): ?>
                                    <button onclick="toggleZones(<?= $index ?>)" class="text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors">
                                        <span id="arrow-<?= $index ?>" class="material-symbols-outlined text-lg transition-transform">chevron_right</span>
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <?php 
                                    $colors = ['blue', 'purple', 'yellow', 'pink', 'cyan', 'green', 'red', 'indigo', 'orange', 'teal'];
                                    $colorIndex = $index % count($colors);
                                    $color = $colors[$colorIndex];
                                    ?>
                                    <?php if (!empty($record['profile_photo'])): ?>
                                        <img src="<?= base_url('uploads/profiles/' . $record['profile_photo']) ?>" alt="<?= esc($record['name']) ?>" class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                                    <?php else: ?>
                                        <div class="w-8 h-8 rounded-full bg-<?= $color ?>-100 dark:bg-<?= $color ?>-900/30 flex items-center justify-center text-<?= $color ?>-700 dark:text-<?= $color ?>-300 font-semibold text-xs border border-<?= $color ?>-200 dark:border-<?= $color ?>-800">
                                            <?= esc($record['initials']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <span class="font-medium text-gray-900 dark:text-white"><?= esc($record['name']) ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-600 dark:text-gray-400"><?= esc($record['id_tag']) ?></td>
                            <td class="px-4 py-3"><?= esc(ucwords($record['department'])) ?></td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300"><?= esc($record['shift']) ?></span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400"><?= esc($record['shift_start']) ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($record['time_in']): ?>
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-base">login</span>
                                        <span class="font-medium text-gray-900 dark:text-white"><?= esc($record['time_in']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($record['time_out']): ?>
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-base">logout</span>
                                        <span class="font-medium text-gray-900 dark:text-white"><?= esc($record['time_out']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($record['work_hours']): ?>
                                    <span class="font-medium text-gray-900 dark:text-white"><?= esc($record['work_hours']) ?></span>
                                <?php elseif ($record['time_in'] && !$record['time_out']): ?>
                                    <span class="text-amber-600 dark:text-amber-400 font-medium">In Progress</span>
                                <?php else: ?>
                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (!empty($record['zones'])): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400">
                                        <span class="material-symbols-outlined text-sm">location_on</span>
                                        <?= count($record['zones']) ?> zones
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php 
                                // Determine status based on shift time and check-in
                                $currentTime = date('H:i:s');
                                
                                // Try to get shift start time - normalize the shift name
                                $shiftName = strtolower($record['shift']);
                                $shiftStartTime = '06:00:00'; // Default
                                
                                // Check all possible variations
                                if (isset($shift_times[$record['shift']])) {
                                    $shiftStartTime = $shift_times[$record['shift']];
                                } elseif (isset($shift_times[$shiftName])) {
                                    $shiftStartTime = $shift_times[$shiftName];
                                } elseif (isset($shift_times[ucfirst($shiftName)])) {
                                    $shiftStartTime = $shift_times[ucfirst($shiftName)];
                                }
                                
                                // Only consider past shift start if viewing today
                                $isPastShiftStart = false;
                                if ($is_today) {
                                    $isPastShiftStart = $currentTime > $shiftStartTime;
                                } else {
                                    // For past dates, always consider it past shift time
                                    $isPastShiftStart = true;
                                }
                                ?>
                                
                                <?php if ($record['time_in'] && $record['time_out']): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                        Completed
                                    </span>
                                <?php elseif ($record['time_in']): ?>
                                    <?php 
                                    // Check if they clocked in late
                                    $timeIn24 = date('H:i:s', strtotime($record['time_in']));
                                    $isLate = $timeIn24 > $shiftStartTime;
                                    ?>
                                    <?php if ($isLate): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Late
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                            Active
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($isPastShiftStart): ?>
                                        <?php if (isset($record['status']) && $record['status'] === 'inactive'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                                Inactive/On Leave
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                Absent
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                            Not Started
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- Zone Details Row (Hidden by default) -->
                        <?php if (!empty($record['zones'])): ?>
                            <tr id="zones-<?= $index ?>" class="hidden bg-gray-50 dark:bg-gray-800/50">
                                <td colspan="10" class="px-4 py-3">
                                    <div class="pl-12">
                                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">Zone Activity</h4>
                                        <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                            <table class="w-full text-xs">
                                                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left font-medium">Zone Name</th>
                                                        <th class="px-3 py-2 text-left font-medium">Entry Time</th>
                                                        <th class="px-3 py-2 text-left font-medium">Exit Time</th>
                                                        <th class="px-3 py-2 text-left font-medium">Duration</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-gray-700 dark:text-gray-300">
                                                    <?php foreach ($record['zones'] as $zone): ?>
                                                        <tr class="border-t border-gray-200 dark:border-gray-700">
                                                            <td class="px-3 py-2 font-medium"><?= esc($zone['name']) ?></td>
                                                            <td class="px-3 py-2"><?= esc($zone['entry']) ?></td>
                                                            <td class="px-3 py-2">
                                                                <?php if ($zone['exit']): ?>
                                                                    <?= esc($zone['exit']) ?>
                                                                <?php else: ?>
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400">
                                                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                                                        Currently Here
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="px-3 py-2 font-medium text-primary">
                                                                <?php if ($zone['duration']): ?>
                                                                    <?= esc($zone['duration']) ?>
                                                                <?php else: ?>
                                                                    <span class="text-amber-600 dark:text-amber-400">In Progress</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (empty($attendance)): ?>
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-4xl">event_busy</span>
                                    <p class="text-sm">No attendance records found for today</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (!empty($attendance)): ?>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 gap-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 w-full sm:w-auto">
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <span>Show</span>
                    <select id="rowsPerPageSelect" onchange="changeRowsPerPage()" class="pl-3 pr-8 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <span>Showing</span>
                    <span id="showingStart" class="font-medium text-gray-900 dark:text-white">1</span>
                    <span>to</span>
                    <span id="showingEnd" class="font-medium text-gray-900 dark:text-white">0</span>
                    <span>of</span>
                    <span id="totalRecords" class="font-medium text-gray-900 dark:text-white">0</span>
                    <span>results</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button id="prevBtn" onclick="changePage(-1)" class="flex items-center justify-center h-8 px-3 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                    <span class="ml-1 text-sm font-medium">Previous</span>
                </button>
                <button id="nextBtn" onclick="changePage(1)" class="flex items-center justify-center h-8 px-3 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="mr-1 text-sm font-medium">Next</span>
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Pagination variables
let currentPage = 1;
let rowsPerPage = 10;
let updateInterval;
let isUpdating = false;
let currentFilterType = '<?= esc(request()->getGet("filter") ?? "today") ?>';
let currentCustomDate = '<?= esc(request()->getGet("date") ?? "") ?>';
let expandedZones = new Set(); // Track which zones are currently expanded

// Real-time data update
async function updateAttendanceData() {
    if (isUpdating) return;
    isUpdating = true;
    
    try {
        let url = '<?= base_url('workers/attendance-data') ?>?filter=' + currentFilterType;
        if (currentCustomDate) {
            url += '&date=' + currentCustomDate;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            updateStats(data.stats);
            updateTable(data.attendance, data.is_today, data.current_time, data.shift_times);
        }
    } catch (error) {
        console.error('Error updating attendance data:', error);
    } finally {
        isUpdating = false;
    }
}

// Update stats cards
function updateStats(stats) {
    // Update present count
    const presentElements = document.querySelectorAll('.stats-present');
    presentElements.forEach(el => el.textContent = stats.present);
    
    // Update absent count  
    const absentElements = document.querySelectorAll('.stats-absent');
    absentElements.forEach(el => el.textContent = stats.absent);
    
    // Update avg time in
    const avgTimeElements = document.querySelectorAll('.stats-avg-time');
    avgTimeElements.forEach(el => el.textContent = stats.avg_time_in);
    
    // Update avg work hours
    const avgHoursElements = document.querySelectorAll('.stats-avg-hours');
    avgHoursElements.forEach(el => el.textContent = stats.avg_work_hours);
}

// Update table with new data
function updateTable(attendance, isToday, currentTime, shiftTimes) {
    const tbody = document.querySelector('#attendanceTable tbody');
    if (!tbody) return;
    
    const colors = ['blue', 'purple', 'yellow', 'pink', 'cyan', 'green', 'red', 'indigo', 'orange', 'teal'];
    
    let html = '';
    attendance.forEach((record, index) => {
        const color = colors[index % colors.length];
        const hasZones = record.zones && record.zones.length > 0;
        
        // Determine status - normalize shift name for lookup
        let statusHtml = '';
        const shiftLower = record.shift.toLowerCase();
        const shiftCapital = record.shift.charAt(0).toUpperCase() + record.shift.slice(1).toLowerCase();
        
        // Try multiple variations to find shift time
        let shiftStartTime = '06:00:00';
        if (shiftTimes[record.shift]) {
            shiftStartTime = shiftTimes[record.shift];
        } else if (shiftTimes[shiftLower]) {
            shiftStartTime = shiftTimes[shiftLower];
        } else if (shiftTimes[shiftCapital]) {
            shiftStartTime = shiftTimes[shiftCapital];
        }
        
        // Only check if past shift start when viewing today
        const isPastShiftStart = isToday && currentTime > shiftStartTime;
        
        if (record.time_in && record.time_out) {
            statusHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300"><span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>Completed</span>';
        } else if (record.time_in) {
            const timeIn24 = record.time_in; // Already in correct format from backend
            const isLate = timeIn24 > shiftStartTime;
            if (isLate) {
                statusHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Late</span>';
            } else {
                statusHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400"><span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>Active</span>';
            }
        } else {
            if (isPastShiftStart) {
                if (record.status === 'inactive') {
                    statusHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400"><span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>Inactive/On Leave</span>';
                } else {
                    statusHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Absent</span>';
                }
            } else {
                statusHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Not Started</span>';
            }
        }
        
        html += `
            <tr data-worker-row="true" class="bg-white dark:bg-background-dark border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <td class="px-4 py-3">
                    ${hasZones ? `<button onclick="toggleZones(${index})" class="text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors"><span id="arrow-${index}" class="material-symbols-outlined text-lg transition-transform">chevron_right</span></button>` : ''}
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        ${record.profile_photo ? 
                            `<img src="<?= base_url('uploads/profiles/') ?>${record.profile_photo}" alt="${record.name}" class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-700">` :
                            `<div class="w-8 h-8 rounded-full bg-${color}-100 dark:bg-${color}-900/30 flex items-center justify-center text-${color}-700 dark:text-${color}-300 font-semibold text-xs border border-${color}-200 dark:border-${color}-800">${record.initials}</div>`
                        }
                        <span class="font-medium text-gray-900 dark:text-white">${record.name}</span>
                    </div>
                </td>
                <td class="px-4 py-3 font-mono text-gray-600 dark:text-gray-400">${record.id_tag}</td>
                <td class="px-4 py-3">${record.department.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ')}</td>
                <td class="px-4 py-3">
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">${record.shift}</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">${record.shift_start}</span>
                </td>
                <td class="px-4 py-3">
                    ${record.time_in ? 
                        `<div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-green-600 dark:text-green-400 text-base">login</span><span class="font-medium text-gray-900 dark:text-white">${record.time_in}</span></div>` :
                        '<span class="text-gray-400 dark:text-gray-500">-</span>'
                    }
                </td>
                <td class="px-4 py-3">
                    ${record.time_out ? 
                        `<div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-red-600 dark:text-red-400 text-base">logout</span><span class="font-medium text-gray-900 dark:text-white">${record.time_out}</span></div>` :
                        '<span class="text-gray-400 dark:text-gray-500">-</span>'
                    }
                </td>
                <td class="px-4 py-3">
                    ${record.work_hours ? 
                        `<span class="font-medium text-gray-900 dark:text-white">${record.work_hours}</span>` :
                        (record.time_in && !record.time_out ? '<span class="text-amber-600 dark:text-amber-400 font-medium">In Progress</span>' : '<span class="text-gray-400 dark:text-gray-500">-</span>')
                    }
                </td>
                <td class="px-4 py-3">
                    ${hasZones ? 
                        `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400"><span class="material-symbols-outlined text-sm">location_on</span>${record.zones.length} zones</span>` :
                        '<span class="text-gray-400 dark:text-gray-500">-</span>'
                    }
                </td>
                <td class="px-4 py-3">${statusHtml}</td>
            </tr>
        `;
        
        // Zone details row
        if (hasZones) {
            html += `
                <tr id="zones-${index}" class="hidden bg-gray-50 dark:bg-gray-800/50">
                    <td colspan="10" class="px-4 py-3">
                        <div class="pl-12">
                            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">Zone Activity</h4>
                            <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <table class="w-full text-xs">
                                    <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-medium">Zone Name</th>
                                            <th class="px-3 py-2 text-left font-medium">Entry Time</th>
                                            <th class="px-3 py-2 text-left font-medium">Exit Time</th>
                                            <th class="px-3 py-2 text-left font-medium">Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-700 dark:text-gray-300">
                                        ${record.zones.map(zone => `
                                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                                <td class="px-3 py-2 font-medium">${zone.name}</td>
                                                <td class="px-3 py-2">${zone.entry}</td>
                                                <td class="px-3 py-2">
                                                    ${zone.exit ? zone.exit : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400"><span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>Currently Here</span>'}
                                                </td>
                                                <td class="px-3 py-2 font-medium text-primary">
                                                    ${zone.duration ? zone.duration : '<span class="text-amber-600 dark:text-amber-400">In Progress</span>'}
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }
    });
    
    if (attendance.length === 0) {
        html = `
            <tr>
                <td colspan="10" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                    <div class="flex flex-col items-center gap-2">
                        <span class="material-symbols-outlined text-4xl">event_busy</span>
                        <p class="text-sm">No attendance records found</p>
                    </div>
                </td>
            </tr>
        `;
    }
    
    tbody.innerHTML = html;
    
    // Restore expanded zones after table update
    expandedZones.forEach(index => {
        const zoneRow = document.getElementById('zones-' + index);
        const arrow = document.getElementById('arrow-' + index);
        if (zoneRow && arrow) {
            zoneRow.classList.remove('hidden');
            zoneRow.style.display = '';
            arrow.classList.add('rotate-90');
        }
    });
    
    updatePagination();
}

// Start real-time updates
document.addEventListener('DOMContentLoaded', function() {
    // Update every 1 second for real-time data
    updateInterval = setInterval(updateAttendanceData, 1000);
    
    // Initial update after 1 second
    setTimeout(updateAttendanceData, 1000);
    
    updatePagination();
});

// Clear interval when leaving page
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});

function changeRowsPerPage() {
    rowsPerPage = parseInt(document.getElementById('rowsPerPageSelect').value);
    currentPage = 1;
    updatePagination();
}

// Track active check-ins for each worker
const activeCheckIns = {};

// Initialize activeCheckIns from current attendance data
<?php foreach ($attendance as $record): ?>
    <?php if (!empty($record['zones'])): ?>
        <?php foreach ($record['zones'] as $zone): ?>
            <?php if ($zone['exit'] === null): // No exit time means still checked in ?>
                if (!activeCheckIns['<?= $record['worker_id'] ?>']) {
                    activeCheckIns['<?= $record['worker_id'] ?>'] = [];
                }
                // Find the zone_id from zone name
                <?php foreach ($all_zones as $z): ?>
                    <?php if ($z['zone_name'] === $zone['name']): ?>
                        if (!activeCheckIns['<?= $record['worker_id'] ?>'].includes('<?= $z['zone_id'] ?>')) {
                            activeCheckIns['<?= $record['worker_id'] ?>'].push('<?= $z['zone_id'] ?>');
                        }
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endforeach; ?>

// Toast notification function
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    const icon = type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info';
    
    toast.className = `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 min-w-[300px] animate-slide-in`;
    toast.innerHTML = `
        <span class="material-symbols-outlined text-xl">${icon}</span>
        <span class="flex-1">${message}</span>
        <button onclick="this.parentElement.remove()" class="text-white/80 hover:text-white">
            <span class="material-symbols-outlined text-lg">close</span>
        </button>
    `;
    
    document.getElementById('toastContainer').appendChild(toast);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.style.animation = 'slide-out 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Modal functions
function openCheckInModal() {
    document.getElementById('checkInModal').classList.remove('hidden');
    // Set current time as default
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('timeInput').value = `${hours}:${minutes}`;
}

function closeCheckInModal() {
    document.getElementById('checkInModal').classList.add('hidden');
    document.getElementById('checkInForm').reset();
    document.getElementById('zoneSelect').innerHTML = '<option value="">Select worker first</option>';
    document.getElementById('noZonesMessage').classList.add('hidden');
}

function updateZoneOptions() {
    const workerSelect = document.getElementById('workerSelect');
    const zoneSelect = document.getElementById('zoneSelect');
    const noZonesMessage = document.getElementById('noZonesMessage');
    const action = document.getElementById('actionType').value;
    
    const selectedOption = workerSelect.options[workerSelect.selectedIndex];
    
    if (!selectedOption.value) {
        zoneSelect.innerHTML = '<option value="">Select worker first</option>';
        noZonesMessage.classList.add('hidden');
        return;
    }
    
    const workerId = selectedOption.value;
    const zonesData = selectedOption.getAttribute('data-zones');
    let zones = [];
    
    try {
        zones = JSON.parse(zonesData) || [];
    } catch (e) {
        zones = [];
    }
    
    if (zones.length === 0) {
        zoneSelect.innerHTML = '<option value="">No zones assigned</option>';
        zoneSelect.disabled = true;
        noZonesMessage.classList.remove('hidden');
        return;
    }
    
    // Get active check-ins for this worker
    const workerActiveZones = activeCheckIns[workerId] || [];
    
    // Filter zones based on action
    let availableZones = [];
    if (action === 'in') {
        // For check-in: show only zones NOT currently checked in
        availableZones = zones.filter(zone => !workerActiveZones.includes(zone.zone_id));
    } else {
        // For check-out: show only zones currently checked in
        availableZones = zones.filter(zone => workerActiveZones.includes(zone.zone_id));
    }
    
    if (availableZones.length === 0) {
        const message = action === 'in' 
            ? 'Already checked in to all assigned zones'
            : 'No active check-ins to check out from';
        zoneSelect.innerHTML = `<option value="">${message}</option>`;
        zoneSelect.disabled = true;
        noZonesMessage.classList.remove('hidden');
        noZonesMessage.innerHTML = `<span class="material-symbols-outlined text-sm align-middle">info</span> ${message}`;
        return;
    }
    
    zoneSelect.disabled = false;
    noZonesMessage.classList.add('hidden');
    
    // Populate zone options based on action
    let optionsHTML = '<option value="">Select Zone</option>';
    availableZones.forEach(zone => {
        optionsHTML += `<option value="${zone.zone_id}">${zone.zone_name}</option>`;
    });
    zoneSelect.innerHTML = optionsHTML;
}

function setAction(action) {
    const checkInBtn = document.getElementById('checkInBtn');
    const checkOutBtn = document.getElementById('checkOutBtn');
    
    if (action === 'in') {
        checkInBtn.classList.remove('bg-gray-50', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700', 'text-gray-700', 'dark:text-gray-300');
        checkInBtn.classList.add('bg-green-50', 'dark:bg-green-900/20', 'border-green-200', 'dark:border-green-800', 'text-green-700', 'dark:text-green-400');
        
        checkOutBtn.classList.remove('bg-red-50', 'dark:bg-red-900/20', 'border-red-200', 'dark:border-red-800', 'text-red-700', 'dark:text-red-400');
        checkOutBtn.classList.add('bg-gray-50', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700', 'text-gray-700', 'dark:text-gray-300');
    } else {
        checkOutBtn.classList.remove('bg-gray-50', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700', 'text-gray-700', 'dark:text-gray-300');
        checkOutBtn.classList.add('bg-red-50', 'dark:bg-red-900/20', 'border-red-200', 'dark:border-red-800', 'text-red-700', 'dark:text-red-400');
        
        checkInBtn.classList.remove('bg-green-50', 'dark:bg-green-900/20', 'border-green-200', 'dark:border-green-800', 'text-green-700', 'dark:text-green-400');
        checkInBtn.classList.add('bg-gray-50', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700', 'text-gray-700', 'dark:text-gray-300');
    }
    
    document.getElementById('actionType').value = action;
    
    // Update zone options based on new action
    updateZoneOptions();
}

// Form submission
document.getElementById('checkInForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const workerSelect = document.getElementById('workerSelect');
    const worker = workerSelect.value;
    const zone = document.getElementById('zoneSelect').value;
    const time = document.getElementById('timeInput').value;
    const action = document.getElementById('actionType').value;
    
    if (!worker || !zone || !time) {
        showToast('Please fill in all fields', 'error');
        return;
    }
    
    const selectedOption = workerSelect.options[workerSelect.selectedIndex];
    const workerName = selectedOption.getAttribute('data-name');
    const zoneName = document.getElementById('zoneSelect').selectedOptions[0].text;
    const actionText = action === 'in' ? 'checked in to' : 'checked out from';
    
    // Submit to backend
    fetch('<?= base_url('workers/record-attendance') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            worker_id: worker,
            zone_id: zone,
            time: time,
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update active check-ins tracking
            if (!activeCheckIns[worker]) {
                activeCheckIns[worker] = [];
            }
            
            if (action === 'in') {
                // Add zone to active check-ins
                if (!activeCheckIns[worker].includes(zone)) {
                    activeCheckIns[worker].push(zone);
                }
            } else {
                // Remove zone from active check-ins
                const index = activeCheckIns[worker].indexOf(zone);
                if (index > -1) {
                    activeCheckIns[worker].splice(index, 1);
                }
            }
            
            // Show success message
            const message = `${workerName} ${actionText} ${zoneName} at ${time}`;
            showToast(message, 'success');
            
            closeCheckInModal();
            
            // Reload page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to record attendance', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while recording attendance', 'error');
    });
});

function toggleZones(index) {
    const zoneRow = document.getElementById('zones-' + index);
    const arrow = document.getElementById('arrow-' + index);
    
    if (zoneRow.classList.contains('hidden')) {
        zoneRow.classList.remove('hidden');
        zoneRow.style.display = '';
        arrow.classList.add('rotate-90');
        expandedZones.add(index); // Track expanded state
    } else {
        zoneRow.classList.add('hidden');
        zoneRow.style.display = 'none';
        arrow.classList.remove('rotate-90');
        expandedZones.delete(index); // Remove from tracking
    }
}

function toggleDateFilter() {
    const dropdown = document.getElementById('dateFilterDropdown');
    dropdown.classList.toggle('hidden');
}

function toggleFilter() {
    const dropdown = document.getElementById('filterDropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const dateDropdown = document.getElementById('dateFilterDropdown');
    const dateButton = document.getElementById('dateFilterBtn');
    if (dateDropdown && dateButton && !dateDropdown.contains(event.target) && !dateButton.contains(event.target)) {
        dateDropdown.classList.add('hidden');
    }
    
    const filterDropdown = document.getElementById('filterDropdown');
    const filterButton = document.getElementById('filterBtn');
    if (filterDropdown && filterButton && !filterDropdown.contains(event.target) && !filterButton.contains(event.target)) {
        filterDropdown.classList.add('hidden');
    }
});

function filterByDate(period) {
    currentFilterType = period;
    currentCustomDate = '';
    updateAttendanceData();
    document.getElementById('dateFilterLabel').textContent = period.charAt(0).toUpperCase() + period.slice(1);
    document.getElementById('dateFilterDropdown').classList.add('hidden');
}

function filterByCustomDate() {
    const dateInput = document.getElementById('customDate');
    if (dateInput.value) {
        currentCustomDate = dateInput.value;
        currentFilterType = 'custom';
        updateAttendanceData();
        const formattedDate = new Date(dateInput.value).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        document.getElementById('dateFilterLabel').textContent = formattedDate;
        document.getElementById('dateFilterDropdown').classList.add('hidden');
    }
}

function applyFilters() {
    updateFilterBadge();
    filterTable();
}

function clearFilters() {
    document.getElementById('departmentFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('shiftFilter').value = '';
    updateFilterBadge();
    filterTable();
}

function updateFilterBadge() {
    const departmentFilter = document.getElementById('departmentFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const shiftFilter = document.getElementById('shiftFilter').value;
    
    const activeFilters = [departmentFilter, statusFilter, shiftFilter].filter(f => f !== '').length;
    const badge = document.getElementById('filterBadge');
    
    if (activeFilters > 0) {
        badge.textContent = activeFilters;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

function filterTable() {
    currentPage = 1;
    updatePagination();
}

function updatePagination() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const departmentFilter = document.getElementById('departmentFilter').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    const shiftFilter = document.getElementById('shiftFilter').value.toLowerCase();
    const table = document.getElementById('attendanceTable');
    const tbody = table.getElementsByTagName('tbody')[0];
    const allRows = tbody.getElementsByTagName('tr');
    
    let visibleRows = [];
    
    // Collect visible worker rows only (excluding zone detail rows)
    for (let i = 0; i < allRows.length; i++) {
        const row = allRows[i];
        
        // Skip zone detail rows
        if (!row.hasAttribute('data-worker-row')) {
            continue;
        }
        
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        // Search in name, ID, and department columns
        for (let j = 1; j <= 3; j++) {
            if (cells[j]) {
                const text = cells[j].textContent || cells[j].innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        if (!found) continue;
        
        // Apply department filter
        if (departmentFilter && cells[3]) {
            const department = cells[3].textContent.toLowerCase().trim();
            if (department !== departmentFilter) {
                continue;
            }
        }
        
        // Apply shift filter
        if (shiftFilter && cells[4]) {
            const shift = cells[4].textContent.toLowerCase().trim();
            if (!shift.includes(shiftFilter)) {
                continue;
            }
        }
        
        // Apply status filter
        if (statusFilter && cells[9]) {
            const status = cells[9].textContent.toLowerCase().trim();
            if (!status.includes(statusFilter)) {
                continue;
            }
        }
        
        visibleRows.push(row);
    }
    
    // Hide all worker rows first (but not zone detail rows)
    for (let i = 0; i < allRows.length; i++) {
        if (allRows[i].hasAttribute('data-worker-row')) {
            allRows[i].style.display = 'none';
        }
    }
    
    // Calculate pagination
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = Math.min(startIndex + rowsPerPage, visibleRows.length);
    
    // Show only rows for current page
    for (let i = startIndex; i < endIndex; i++) {
        visibleRows[i].style.display = '';
        
        // Find the corresponding zone detail row and show it if it's expanded
        const button = visibleRows[i].querySelector('button[onclick*="toggleZones"]');
        if (button) {
            const match = button.getAttribute('onclick').match(/toggleZones\((\d+)\)/);
            if (match) {
                const zoneIndex = match[1];
                const zoneRow = document.getElementById('zones-' + zoneIndex);
                if (zoneRow && !zoneRow.classList.contains('hidden')) {
                    zoneRow.style.display = '';
                }
            }
        }
    }
    
    // Hide zone detail rows for workers not on current page
    for (let i = 0; i < allRows.length; i++) {
        if (!allRows[i].hasAttribute('data-worker-row')) {
            const zoneRow = allRows[i];
            const zoneId = zoneRow.id;
            if (zoneId && zoneId.startsWith('zones-')) {
                const zoneIndex = zoneId.replace('zones-', '');
                let workerVisible = false;
                
                // Check if the corresponding worker row is visible
                for (let j = startIndex; j < endIndex; j++) {
                    const workerButton = visibleRows[j].querySelector('button[onclick*="toggleZones(' + zoneIndex + ')"]');
                    if (workerButton) {
                        workerVisible = true;
                        break;
                    }
                }
                
                // Hide zone row if its worker is not visible
                if (!workerVisible) {
                    zoneRow.style.display = 'none';
                }
            }
        }
    }
    
    // Update pagination info
    const total = visibleRows.length;
    document.getElementById('showingStart').textContent = total === 0 ? '0' : startIndex + 1;
    document.getElementById('showingEnd').textContent = total === 0 ? '0' : endIndex;
    document.getElementById('totalRecords').textContent = total;
    
    // Update button states
    document.getElementById('prevBtn').disabled = currentPage === 1;
    const totalPages = Math.ceil(total / rowsPerPage);
    document.getElementById('nextBtn').disabled = currentPage >= totalPages || total === 0;
}

function changePage(direction) {
    currentPage += direction;
    updatePagination();
}

// Initialize pagination on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePagination();
});

function exportToCSV() {
    const table = document.getElementById('attendanceTable');
    const rows = table.querySelectorAll('tbody tr[data-worker-row="true"]');
    let csv = [];
    
    // Add header
    const headerRow = table.querySelector('thead tr');
    const headers = [];
    headerRow.querySelectorAll('th').forEach((th, index) => {
        if (index > 0) { // Skip expand button column
            headers.push('"' + th.innerText.trim().replace(/\n/g, ' ') + '"');
        }
    });
    csv.push(headers.join(','));
    
    // Add data rows (only visible ones)
    for (let i = 0; i < rows.length; i++) {
        if (rows[i].style.display === 'none') continue; // Skip hidden rows
        
        const row = [];
        const cols = rows[i].querySelectorAll('td');
        
        for (let j = 1; j < cols.length; j++) { // Skip expand button column
            // Get text content and clean it up
            let text = cols[j].innerText || cols[j].textContent || '';
            text = text.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim();
            // Escape quotes in the text
            text = text.replace(/"/g, '""');
            row.push('"' + text + '"');
        }
        
        csv.push(row.join(','));
    }
    
    // Create and download CSV file
    const csvContent = '\uFEFF' + csv.join('\n'); // Add BOM for Excel compatibility
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    
    // Generate filename with current filter/date
    const filterLabel = document.getElementById('dateFilterLabel').textContent.trim();
    const filename = 'attendance_' + filterLabel.replace(/\s+/g, '_').toLowerCase() + '_' + new Date().toISOString().split('T')[0] + '.csv';
    a.download = filename;
    
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    showToast('Attendance data exported successfully', 'success');
}
</script>

<style>
@keyframes slide-in {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slide-out {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}
</style>

<?= $this->include('templates/footer') ?>
