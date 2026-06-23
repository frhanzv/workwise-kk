<?php
// Get current view, month/year from URL or use current
$currentMonth = $_GET['month'] ?? date('n');
$currentYear = $_GET['year'] ?? date('Y');
$currentDay = $_GET['day'] ?? date('d');
$view = $_GET['view'] ?? 'month';

// Create a map of leave records by date for easy lookup
$leaveByDate = [];
if (isset($leaveRecords) && is_array($leaveRecords)) {
    foreach ($leaveRecords as $leave) {
        $leaveByDate[$leave['leave_date']] = $leave;
    }
}

// Create date object
$firstDay = new DateTime("$currentYear-$currentMonth-01");
$lastDay = new DateTime($firstDay->format('Y-m-t'));

// Get first and last day of month
$daysInMonth = $firstDay->format('t');
$startDayOfWeek = $firstDay->format('w'); // 0 (Sunday) to 6 (Saturday)

// Current date for day/week view
$currentDate = mktime(0, 0, 0, $currentMonth, $currentDay, $currentYear);
$currentDayName = date('l', $currentDate);
$fullDateName = date('F j, Y', $currentDate);

// Week calculations
$weekStart = strtotime('last sunday', strtotime('+1 day', $currentDate));
if (date('w', $currentDate) == 0) {
    $weekStart = $currentDate;
}
$weekEnd = strtotime('+6 days', $weekStart);
$weekRange = date('M j, Y', $weekStart) . ' - ' . date('M j, Y', $weekEnd);

// Navigation for different views
if ($view === 'day') {
    $prevDate = strtotime('-1 day', $currentDate);
    $nextDate = strtotime('+1 day', $currentDate);
    $prevYearDate = strtotime('-1 year', $currentDate);
    $nextYearDate = strtotime('+1 year', $currentDate);
} elseif ($view === 'week') {
    $prevDate = strtotime('-7 days', $currentDate);
    $nextDate = strtotime('+7 days', $currentDate);
    $prevYearDate = strtotime('-1 year', $currentDate);
    $nextYearDate = strtotime('+1 year', $currentDate);
} else {
    $prevMonth = date('m', strtotime("-1 month", $firstDay->getTimestamp()));
    $prevMonthYear = date('Y', strtotime("-1 month", $firstDay->getTimestamp()));
    $nextMonth = date('m', strtotime("+1 month", $firstDay->getTimestamp()));
    $nextMonthYear = date('Y', strtotime("+1 month", $firstDay->getTimestamp()));
    $prevYear = $currentYear - 1;
    $nextYear = $currentYear + 1;
}

// Month navigation
$monthName = $firstDay->format('F Y');
?>

<div class="space-y-6">
    <!-- Staff Info Card -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">IC No - <?= !empty($worker['ic_number']) ? esc($worker['ic_number']) : 'Not provided' ?></h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Staff No - <?= esc($worker['worker_id']) ?></p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Sub Type - PERMANENT</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Department - <?= esc(ucwords($worker['department'])) ?></p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-white dark:bg-gray-800 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 w-40">
                    <span class="material-symbols-outlined text-green-600 dark:text-green-400">person</span>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= $attendanceStats['present'] ?? 0 ?></p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Attendance</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 bg-white dark:bg-gray-800 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 w-40">
                    <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400">event_busy</span>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= $attendanceStats['paid_leave'] ?? 0 ?></p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Paid Leave</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 bg-white dark:bg-gray-800 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 w-40">
                    <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">local_hospital</span>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= $attendanceStats['medical_leave'] ?? 0 ?></p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Medical Leave</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 bg-white dark:bg-gray-800 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 w-40">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400">cancel</span>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= $attendanceStats['absent'] ?? 0 ?></p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Absent</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Header -->
    <div class="flex items-center justify-between">
        <div class="flex gap-1 bg-gray-100 dark:bg-gray-900 p-1 rounded-lg">
            <button onclick="changeView('month')" class="px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $view === 'month' ? 'bg-primary text-white shadow-md' : 'bg-transparent text-gray-700 dark:text-gray-300 hover:bg-white/50 dark:hover:bg-gray-800' ?>">
                Month
            </button>
            <button onclick="changeView('week')" class="px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $view === 'week' ? 'bg-primary text-white shadow-md' : 'bg-transparent text-gray-700 dark:text-gray-300 hover:bg-white/50 dark:hover:bg-gray-800' ?>">
                Week
            </button>
            <button onclick="changeView('day')" class="px-4 py-2 text-sm font-medium rounded-lg transition-all <?= $view === 'day' ? 'bg-primary text-white shadow-md' : 'bg-transparent text-gray-700 dark:text-gray-300 hover:bg-white/50 dark:hover:bg-gray-800' ?>">
                Day
            </button>
        </div>
        
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white uppercase">
                <?php if ($view === 'day'): ?>
                    <?= $fullDateName ?>
                <?php elseif ($view === 'week'): ?>
                    <?= $weekRange ?>
                <?php else: ?>
                    <?= $monthName ?>
                <?php endif; ?>
            </h2>
        </div>
        
        <div class="flex gap-2">
            <?php if ($view === 'day'): ?>
                <a href="<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=day&year=<?= date('Y', $prevYearDate) ?>&month=<?= date('m', $prevYearDate) ?>&day=<?= date('d', $prevYearDate) ?>" class="p-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors">
                    <span class="material-symbols-outlined">keyboard_double_arrow_left</span>
                </a>
                <a href="<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=day&year=<?= date('Y', $prevDate) ?>&month=<?= date('m', $prevDate) ?>&day=<?= date('d', $prevDate) ?>" class="p-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors">
                    <span class="material-symbols-outlined">chevron_left</span>
                </a>
                <a href="<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=day&year=<?= date('Y', $nextDate) ?>&month=<?= date('m', $nextDate) ?>&day=<?= date('d', $nextDate) ?>" class="p-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors">
                    <span class="material-symbols-outlined">chevron_right</span>
                </a>
                <a href="<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=day&year=<?= date('Y', $nextYearDate) ?>&month=<?= date('m', $nextYearDate) ?>&day=<?= date('d', $nextYearDate) ?>" class="p-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors">
                    <span class="material-symbols-outlined">keyboard_double_arrow_right</span>
                </a>
            <?php elseif ($view === 'week'): ?>
                <a href="<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=week&year=<?= date('Y', $prevYearDate) ?>&month=<?= date('m', $prevYearDate) ?>&day=<?= date('d', $prevYearDate) ?>" class="p-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors">
                    <span class="material-symbols-outlined">keyboard_double_arrow_left</span>
                </a>
                <a href="<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=week&year=<?= date('Y', $prevDate) ?>&month=<?= date('m', $prevDate) ?>&day=<?= date('d', $prevDate) ?>" class="p-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors">
                    <span class="material-symbols-outlined">chevron_left</span>
                </a>
                <a href="<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=week&year=<?= date('Y', $nextDate) ?>&month=<?= date('m', $nextDate) ?>&day=<?= date('d', $nextDate) ?>" class="p-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors">
                    <span class="material-symbols-outlined">chevron_right</span>
                </a>
                <a href="<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=week&year=<?= date('Y', $nextYearDate) ?>&month=<?= date('m', $nextYearDate) ?>&day=<?= date('d', $nextYearDate) ?>" class="p-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors">
                    <span class="material-symbols-outlined">keyboard_double_arrow_right</span>
                </a>
            <?php else: ?>
                <a href="<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=month&year=<?= $prevYear ?>&month=<?= $currentMonth ?>" class="p-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors">
                    <span class="material-symbols-outlined">keyboard_double_arrow_left</span>
                </a>
                <a href="<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=month&year=<?= $prevMonthYear ?>&month=<?= $prevMonth ?>" class="p-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors">
                    <span class="material-symbols-outlined">chevron_left</span>
                </a>
                <a href="<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=month&year=<?= $nextMonthYear ?>&month=<?= $nextMonth ?>" class="p-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors">
                    <span class="material-symbols-outlined">chevron_right</span>
                </a>
                <a href="<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=month&year=<?= $nextYear ?>&month=<?= $currentMonth ?>" class="p-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors">
                    <span class="material-symbols-outlined">keyboard_double_arrow_right</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($view === 'day'): ?>
        <!-- Day View -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="text-center py-3 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?= $currentDayName ?></h3>
            </div>
            <div class="min-h-[400px] p-4">
                <?php 
                $currentDateStr = date('Y-m-d', $currentDate);
                $isHoliday = false;
                $holidayName = '';
                $holidayType = '';
                foreach ($publicHolidays as $holiday) {
                    if ($holiday['holiday_date'] === $currentDateStr) {
                        $isHoliday = true;
                        $holidayName = $holiday['name'];
                        $holidayType = $holiday['type'] ?? 'State';
                        break;
                    }
                }
                
                $attendanceStatus = '';
                $attendanceColor = '';
                if (isset($attendanceRecords[$currentDateStr])) {
                    $attendanceStatus = $attendanceRecords[$currentDateStr];
                    switch ($attendanceStatus) {
                        case 'PRESENT':
                            $attendanceColor = 'bg-green-500/20 dark:bg-green-500/20 border-green-500 dark:border-green-500 text-green-700 dark:text-green-300';
                            break;
                        case 'PAID LEAVE':
                            $attendanceColor = 'bg-yellow-500/20 dark:bg-yellow-500/20 border-yellow-500 dark:border-yellow-500 text-yellow-700 dark:text-yellow-300';
                            break;
                        case 'MEDICAL LEAVE':
                            $attendanceColor = 'bg-orange-500/20 dark:bg-orange-500/20 border-orange-500 dark:border-orange-500 text-orange-700 dark:text-orange-300';
                            break;
                        case 'ABSENT':
                            $attendanceColor = 'bg-red-500/20 dark:bg-red-500/20 border-red-500 dark:border-red-500 text-red-700 dark:text-red-300';
                            break;
                    }
                }
                ?>
                <?php if ($isHoliday): ?>
                    <?php 
                    $holidayBgColor = $holidayType === 'Federal' ? 'bg-red-50 dark:bg-red-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20';
                    $holidayBorderColor = $holidayType === 'Federal' ? 'border-red-300 dark:border-red-700' : 'border-yellow-300 dark:border-yellow-700';
                    $holidayIconColor = $holidayType === 'Federal' ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400';
                    $holidayTextColor = $holidayType === 'Federal' ? 'text-red-800 dark:text-red-300' : 'text-yellow-800 dark:text-yellow-300';
                    $holidaySubtextColor = $holidayType === 'Federal' ? 'text-red-700 dark:text-red-400' : 'text-yellow-700 dark:text-yellow-400';
                    ?>
                    <div class="p-4 mb-4 <?= $holidayBgColor ?> rounded-lg border <?= $holidayBorderColor ?>">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined <?= $holidayIconColor ?>">event</span>
                            <div>
                                <div class="font-medium <?= $holidayTextColor ?>"><?= esc($holidayName) ?></div>
                                <div class="text-sm <?= $holidaySubtextColor ?>"><?= esc($holidayType) ?> Holiday</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($attendanceStatus): ?>
                    <div class="p-4 rounded-lg border-2 <?= $attendanceColor ?>">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined">check_circle</span>
                            <div>
                                <div class="font-medium"><?= $attendanceStatus ?></div>
                                <div class="text-sm opacity-75">Status for this day</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-400 dark:text-gray-600">
                        <span class="material-symbols-outlined text-5xl">event_busy</span>
                        <p class="mt-2">No attendance record</p>
                    </div>
                <?php endif; ?>
                
                <?php if ($isHR ?? false): ?>
                    <div class="mt-4">
                        <button onclick="openLeaveModal('<?= $currentDateStr ?>')" class="w-full px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">event_busy</span>
                            <span>Mark Leave</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    
    <?php elseif ($view === 'week'): ?>
        <!-- Week View -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="grid grid-cols-7">
                <?php for ($i = 0; $i < 7; $i++): ?>
                    <?php
                    $weekDate = strtotime("+$i days", $weekStart);
                    $weekDateStr = date('Y-m-d', $weekDate);
                    
                    $isHoliday = false;
                    $holidayName = '';
                    $holidayType = '';
                    foreach ($publicHolidays as $holiday) {
                        if ($holiday['holiday_date'] === $weekDateStr) {
                            $isHoliday = true;
                            $holidayName = $holiday['name'];
                            $holidayType = $holiday['type'] ?? 'State';
                            break;
                        }
                    }
                    
                    $attendanceStatus = '';
                    $attendanceColor = '';
                    if (isset($attendanceRecords[$weekDateStr])) {
                        $attendanceStatus = $attendanceRecords[$weekDateStr];
                        switch ($attendanceStatus) {
                            case 'PRESENT':
                                $attendanceColor = 'bg-green-500/20 dark:bg-green-500/20 border-green-500 dark:border-green-500 text-green-700 dark:text-green-300';
                                break;
                            case 'PAID LEAVE':
                                $attendanceColor = 'bg-yellow-500/20 dark:bg-yellow-500/20 border-yellow-500 dark:border-yellow-500 text-yellow-700 dark:text-yellow-300';
                                break;
                            case 'MEDICAL LEAVE':
                                $attendanceColor = 'bg-orange-500/20 dark:bg-orange-500/20 border-orange-500 dark:border-orange-500 text-orange-700 dark:text-orange-300';
                                break;
                            case 'ABSENT':
                                $attendanceColor = 'bg-red-500/20 dark:bg-red-500/20 border-red-500 dark:border-red-500 text-red-700 dark:text-red-300';
                                break;
                        }
                    }
                    
                    $bgClass = $isHoliday ? ($holidayType === 'Federal' ? 'bg-red-50 dark:bg-red-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20') : 'bg-white dark:bg-gray-800';
                    ?>
                    <div class="border-r border-b border-gray-200 dark:border-gray-700 last:border-r-0">
                        <div class="text-center py-3 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300"><?= date('D', $weekDate) ?></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400"><?= date('n/j', $weekDate) ?></div>
                        </div>
                        <div class="min-h-[300px] p-2 <?= $bgClass ?>">
                            <?php if ($isHoliday): ?>
                                <?php 
                                $weekHolidayBgColor = $holidayType === 'Federal' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-yellow-100 dark:bg-yellow-900/30';
                                $weekHolidayBorderColor = $holidayType === 'Federal' ? 'border-red-300 dark:border-red-700' : 'border-yellow-300 dark:border-yellow-700';
                                $weekHolidayTextColor = $holidayType === 'Federal' ? 'text-red-800 dark:text-red-300' : 'text-yellow-800 dark:text-yellow-300';
                                ?>
                                <div class="p-2 mb-2 <?= $weekHolidayBgColor ?> rounded text-xs border <?= $weekHolidayBorderColor ?>">
                                    <div class="font-medium <?= $weekHolidayTextColor ?> truncate"><?= esc($holidayName) ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($attendanceStatus): ?>
                                <div class="p-2 rounded text-xs font-semibold border-2 <?= $attendanceColor ?>">
                                    <div class="text-center"><?= $attendanceStatus ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($isHR ?? false): ?>
                                <button onclick="openLeaveModal('<?= $weekDateStr ?>')" class="mt-2 w-full px-2 py-1.5 bg-primary text-white rounded text-xs font-medium hover:bg-primary/90 transition-colors">
                                    <span class="material-symbols-outlined text-sm">event_busy</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    
    <?php else: ?>
        <!-- Month View -->
    <!-- Calendar Grid -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Day Headers -->
        <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
            <?php 
            $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            foreach ($dayNames as $day): 
            ?>
                <div class="p-3 text-center">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400"><?= $day ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Calendar Days -->
        <div class="grid grid-cols-7">
            <?php 
            // Empty cells before first day of month
            for ($i = 0; $i < $startDayOfWeek; $i++): 
            ?>
                <div class="aspect-square border-r border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 p-2">
                    <span class="text-sm text-gray-400 dark:text-gray-600"><?= date('j', strtotime("-" . ($startDayOfWeek - $i) . " days", $firstDay->getTimestamp())) ?></span>
                </div>
            <?php endfor; ?>

            <?php 
            // Days of the month
            for ($day = 1; $day <= $daysInMonth; $day++): 
                $currentDate = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
                $isToday = $currentDate === date('Y-m-d');
                $isWeekend = date('w', strtotime($currentDate)) == 0 || date('w', strtotime($currentDate)) == 6;
                
                // Check if it's a public holiday
                $isHoliday = false;
                $holidayName = '';
                $holidayType = '';
                foreach ($publicHolidays as $holiday) {
                    if ($holiday['holiday_date'] === $currentDate) {
                        $isHoliday = true;
                        $holidayName = $holiday['name'];
                        $holidayType = $holiday['type'] ?? 'State';
                        break;
                    }
                }
                
                // Check attendance status for this day
                $attendanceStatus = '';
                $attendanceColor = '';
                if (isset($attendanceRecords[$currentDate])) {
                    $attendanceStatus = $attendanceRecords[$currentDate];
                    switch ($attendanceStatus) {
                        case 'PRESENT':
                            $attendanceColor = 'bg-green-500/20 dark:bg-green-500/20 border-green-500/50 dark:border-green-500/50 text-green-700 dark:text-green-300';
                            break;
                        case 'PAID LEAVE':
                            $attendanceColor = 'bg-yellow-500/20 dark:bg-yellow-500/20 border-yellow-500/50 dark:border-yellow-500/50 text-yellow-700 dark:text-yellow-300';
                            break;
                        case 'MEDICAL LEAVE':
                            $attendanceColor = 'bg-orange-500/20 dark:bg-orange-500/20 border-orange-500/50 dark:border-orange-500/50 text-orange-700 dark:text-orange-300';
                            break;
                        case 'ABSENT':
                            $attendanceColor = 'bg-red-500/20 dark:bg-red-500/20 border-red-500/50 dark:border-red-500/50 text-red-700 dark:text-red-300';
                            break;
                    }
                }
                
                $bgClass = $isHoliday ? ($holidayType === 'Federal' ? 'bg-red-50 dark:bg-red-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20') : ($isWeekend ? 'bg-gray-50 dark:bg-gray-900/50' : 'bg-white dark:bg-gray-800');
                $holidayDayColor = $isHoliday ? ($holidayType === 'Federal' ? 'text-red-700 dark:text-red-400' : 'text-yellow-700 dark:text-yellow-400') : 'text-gray-900 dark:text-white';
                
                // Check if leave exists for this date and determine click action
                $hasLeave = isset($leaveByDate[$currentDate]);
                $isClickable = ($isHR ?? false);
            ?>
                <div class="aspect-square border-r border-b border-gray-200 dark:border-gray-700 <?= $bgClass ?> p-2 relative <?= $isClickable ? 'hover:bg-blue-50 dark:hover:bg-blue-900/20 cursor-pointer' : '' ?> transition-colors" <?php if ($isClickable): ?><?php if ($hasLeave): ?>onclick="viewLeaveDetails('<?= $currentDate ?>')"<?php else: ?>onclick="openLeaveModal('<?= $currentDate ?>')"<?php endif; ?><?php endif; ?>>
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-medium <?= $isToday ? 'w-6 h-6 flex items-center justify-center rounded-full bg-blue-600 text-white' : $holidayDayColor ?>">
                            <?= $day ?>
                        </span>
                    </div>
                    <?php if ($isHoliday): ?>
                        <p class="text-xs <?= $holidayType === 'Federal' ? 'text-red-700 dark:text-red-400' : 'text-yellow-700 dark:text-yellow-400' ?> mt-1 line-clamp-2"><?= esc($holidayName) ?></p>
                    <?php endif; ?>
                    <?php if ($attendanceStatus): ?>
                        <div class="mt-1">
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded border <?= $attendanceColor ?> block text-center">
                                <?= $attendanceStatus ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>

            <?php 
            // Empty cells after last day of month
            $totalCells = $startDayOfWeek + $daysInMonth;
            $remainingCells = (7 - ($totalCells % 7)) % 7;
            for ($i = 1; $i <= $remainingCells; $i++): 
            ?>
                <div class="aspect-square border-r border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 p-2">
                    <span class="text-sm text-gray-400 dark:text-gray-600"><?= $i ?></span>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($isHR ?? false): ?>
<!-- Mark Leave Modal -->
<div id="leaveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Mark Leave</h3>
            <button onclick="closeLeaveModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <form id="leaveForm" class="p-4 space-y-4">
            <input type="hidden" id="leaveWorkerId" value="<?= esc($worker['worker_id']) ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Leave Date <span class="text-red-500">*</span></label>
                <input type="date" id="leaveDate" required 
                       class="w-full px-3 py-2 bg-background-light dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Leave Reason <span class="text-red-500">*</span></label>
                <select id="leaveReasonId" required 
                        class="w-full px-3 py-2 bg-background-light dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary">
                    <option value="">Select Reason</option>
                    <?php foreach ($leaveReasons as $reason): ?>
                        <option value="<?= $reason['id'] ?>">[<?= esc($reason['type']) ?>] <?= esc($reason['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes (Optional)</label>
                <textarea id="leaveNotes" rows="3" placeholder="Additional notes..."
                          class="w-full px-3 py-2 bg-background-light dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary"></textarea>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeLeaveModal()" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors">
                    Mark Leave
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Leave Modal -->
<div id="viewLeaveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-background-dark rounded-xl shadow-xl max-w-md w-full">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Leave Details</h3>
            <button onclick="closeViewLeaveModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div id="viewLeaveContent" class="p-4 space-y-4">
            <!-- Content will be populated by JavaScript -->
        </div>
        
        <div class="flex gap-3 p-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="closeViewLeaveModal()" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Close
            </button>
            <button type="button" id="cancelLeaveBtn" onclick="confirmCancelLeave()" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition-colors">
                Cancel Leave
            </button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[55] flex items-center justify-center p-4">
    <div class="bg-white dark:bg-background-dark rounded-xl shadow-xl max-w-sm w-full">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 mb-4">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">warning</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Cancel Leave?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Are you sure you want to cancel this leave? This action cannot be undone.</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeConfirmModal()" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    No, Keep It
                </button>
                <button type="button" onclick="cancelLeave()" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition-colors">
                    Yes, Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed top-4 right-4 z-[60] max-w-sm w-full">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 flex items-start gap-3">
        <div id="toastIcon" class="flex-shrink-0"></div>
        <div class="flex-1">
            <p id="toastTitle" class="font-semibold text-sm"></p>
            <p id="toastMessage" class="text-sm text-gray-600 dark:text-gray-400 mt-1"></p>
        </div>
        <button onclick="hideToast()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0">
            <span class="material-symbols-outlined text-lg">close</span>
        </button>
    </div>
</div>
<?php endif; ?>

<script>
// Toast notification functions
function showToast(title, message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toastIcon');
    const titleEl = document.getElementById('toastTitle');
    const messageEl = document.getElementById('toastMessage');
    
    // Set icon and colors based on type
    if (type === 'success') {
        icon.innerHTML = '<span class="material-symbols-outlined text-green-600 dark:text-green-400 text-2xl">check_circle</span>';
        titleEl.className = 'font-semibold text-sm text-green-700 dark:text-green-400';
    } else if (type === 'error') {
        icon.innerHTML = '<span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">error</span>';
        titleEl.className = 'font-semibold text-sm text-red-700 dark:text-red-400';
    } else if (type === 'warning') {
        icon.innerHTML = '<span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400 text-2xl">warning</span>';
        titleEl.className = 'font-semibold text-sm text-yellow-700 dark:text-yellow-400';
    } else {
        icon.innerHTML = '<span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-2xl">info</span>';
        titleEl.className = 'font-semibold text-sm text-blue-700 dark:text-blue-400';
    }
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    toast.classList.remove('hidden');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        hideToast();
    }, 5000);
}

function hideToast() {
    const toast = document.getElementById('toast');
    toast.classList.add('hidden');
}

function changeView(view) {
    const urlParams = new URLSearchParams(window.location.search);
    const year = urlParams.get('year') || new Date().getFullYear();
    const month = urlParams.get('month') || String(new Date().getMonth() + 1).padStart(2, '0');
    const day = urlParams.get('day') || String(new Date().getDate()).padStart(2, '0');
    window.location.href = `<?= base_url('workers/view/' . urlencode($worker['worker_id'])) ?>?view=${view}&year=${year}&month=${month}&day=${day}`;
}

// Leave modal functions - always available
function openLeaveModal(selectedDate = '') {
    const modal = document.getElementById('leaveModal');
    if (!modal) {
        console.error('Leave modal not found');
        return;
    }
    modal.classList.remove('hidden');
    // Pre-fill the date if provided, otherwise use today
    const dateToSet = selectedDate || new Date().toISOString().split('T')[0];
    const dateInput = document.getElementById('leaveDate');
    if (dateInput) {
        dateInput.value = dateToSet;
    }
}

function closeLeaveModal() {
    const modal = document.getElementById('leaveModal');
    if (modal) {
        modal.classList.add('hidden');
    }
    const form = document.getElementById('leaveForm');
    if (form) {
        form.reset();
    }
}

function closeViewLeaveModal() {
    const modal = document.getElementById('viewLeaveModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function confirmCancelLeave() {
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
}

let currentViewingLeaveDate = '';

function viewLeaveDetails(date) {
    currentViewingLeaveDate = date;
    const leaveData = <?= json_encode($leaveByDate ?? []) ?>;
    
    if (!leaveData[date]) {
        showToast('Error', 'Leave record not found', 'error');
        return;
    }
    
    const leave = leaveData[date];
    const content = document.getElementById('viewLeaveContent');
    
    content.innerHTML = `
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Leave Date</label>
                <p class="text-gray-900 dark:text-white">${leave.leave_date}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Leave Type</label>
                <p class="text-gray-900 dark:text-white">${leave.reason_type}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                <p class="text-gray-900 dark:text-white">${leave.reason_name}</p>
            </div>
            ${leave.notes ? `
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <p class="text-gray-900 dark:text-white">${leave.notes}</p>
            </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('viewLeaveModal').classList.remove('hidden');
}

function cancelLeave() {
    if (!currentViewingLeaveDate) return;
    
    const workerId = document.getElementById('leaveWorkerId').value;
    
    fetch('<?= base_url('workers/remove-leave') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            worker_id: workerId,
            leave_date: currentViewingLeaveDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            closeConfirmModal();
            closeViewLeaveModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast('Error', data.message || 'Failed to cancel leave', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'An error occurred while canceling leave', 'error');
    });
}

<?php if ($isHR ?? false): ?>
// Handle leave form submission
document.getElementById('leaveForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const workerId = document.getElementById('leaveWorkerId').value;
    const leaveReasonId = document.getElementById('leaveReasonId').value;
    const leaveDate = document.getElementById('leaveDate').value;
    const notes = document.getElementById('leaveNotes').value;
    
    if (!leaveReasonId || !leaveDate) {
        showToast('Validation Error', 'Please fill in all required fields', 'warning');
        return;
    }
    
    // Submit to backend
    fetch('<?= base_url('workers/mark-leave') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            worker_id: workerId,
            leave_reason_id: leaveReasonId,
            leave_date: leaveDate,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            closeLeaveModal();
            // Reload page to show updated calendar
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast('Error', data.message || 'Failed to mark leave', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'An error occurred while marking leave', 'error');
    });
});

// Close modal on outside click
document.getElementById('leaveModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeLeaveModal();
    }
});
<?php endif; ?>
</script>
