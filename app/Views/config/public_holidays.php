<?= $this->include('templates/header') ?>

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config') ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-gray-900 dark:text-white text-xl font-bold leading-tight">Public Holidays</h1>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="mb-4 p-3 bg-green-100 dark:bg-green-900/20 border border-green-500 text-green-700 dark:text-green-400 rounded-lg text-sm">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-4 p-3 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg text-sm">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="mb-4 flex items-center gap-3">
    <span class="flex items-center gap-2 text-sm">
        <span class="text-gray-700 dark:text-gray-300">Federal Holiday :</span>
        <span class="w-3 h-3 bg-red-500 rounded"></span>
    </span>
    <span class="flex items-center gap-2 text-sm">
        <span class="text-gray-700 dark:text-gray-300">State Holiday :</span>
        <span class="w-3 h-3 bg-orange-500 rounded"></span>
    </span>
</div>

<?php
// Prepare holidays data
$request = service('request');
$currentYear = $year ?? date('Y');
$currentMonth = $month ?? date('m');
$currentDay = $request->getGet('day') ?? date('d');
$view = $request->getGet('view') ?? 'month';

$holidaysByDate = [];
foreach ($holidays as $holiday) {
    $date = date('Y-m-d', strtotime($holiday['holiday_date']));
    if (!isset($holidaysByDate[$date])) {
        $holidaysByDate[$date] = [];
    }
    $holidaysByDate[$date][] = $holiday;
}

// Debug: Check what holidays we have
// echo '<pre>'; var_dump($holidaysByDate); echo '</pre>';

// Calendar calculations
$firstDay = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
$daysInMonth = date('t', $firstDay);
$dayOfWeek = date('w', $firstDay);
$monthName = date('F Y', $firstDay);

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
    $prevMonth = date('m', strtotime("-1 month", $firstDay));
    $prevMonthYear = date('Y', strtotime("-1 month", $firstDay));
    $nextMonth = date('m', strtotime("+1 month", $firstDay));
    $nextMonthYear = date('Y', strtotime("+1 month", $firstDay));
    $prevYear = $currentYear - 1;
    $nextYear = $currentYear + 1;
}
?>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
            <button onclick="changeView('month')" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors <?= $view === 'month' ? 'bg-primary text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
                month
            </button>
            <button onclick="changeView('week')" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors <?= $view === 'week' ? 'bg-primary text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
                week
            </button>
            <button onclick="changeView('day')" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors <?= $view === 'day' ? 'bg-primary text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
                day
            </button>
        </div>
        
        <h2 class="text-xl font-bold text-gray-900 dark:text-white uppercase">
            <?php if ($view === 'day'): ?>
                <?= $fullDateName ?>
            <?php elseif ($view === 'week'): ?>
                <?= $weekRange ?>
            <?php else: ?>
                <?= $monthName ?>
            <?php endif; ?>
        </h2>
        
        <div class="flex items-center gap-2">
            <?php if ($view === 'day'): ?>
                <a href="<?= base_url('config/public-holidays?view=day&year=' . date('Y', $prevYearDate) . '&month=' . date('m', $prevYearDate) . '&day=' . date('d', $prevYearDate)) ?>" class="px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">
                    <span class="material-symbols-outlined text-sm">keyboard_double_arrow_left</span>
                </a>
                <a href="<?= base_url('config/public-holidays?view=day&year=' . date('Y', $prevDate) . '&month=' . date('m', $prevDate) . '&day=' . date('d', $prevDate)) ?>" class="px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </a>
                <a href="<?= base_url('config/public-holidays?view=day&year=' . date('Y', $nextDate) . '&month=' . date('m', $nextDate) . '&day=' . date('d', $nextDate)) ?>" class="px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </a>
                <a href="<?= base_url('config/public-holidays?view=day&year=' . date('Y', $nextYearDate) . '&month=' . date('m', $nextYearDate) . '&day=' . date('d', $nextYearDate)) ?>" class="px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">
                    <span class="material-symbols-outlined text-sm">keyboard_double_arrow_right</span>
                </a>
            <?php elseif ($view === 'week'): ?>
                <a href="<?= base_url('config/public-holidays?view=week&year=' . date('Y', $prevYearDate) . '&month=' . date('m', $prevYearDate) . '&day=' . date('d', $prevYearDate)) ?>" class="px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">
                    <span class="material-symbols-outlined text-sm">keyboard_double_arrow_left</span>
                </a>
                <a href="<?= base_url('config/public-holidays?view=week&year=' . date('Y', $prevDate) . '&month=' . date('m', $prevDate) . '&day=' . date('d', $prevDate)) ?>" class="px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </a>
                <a href="<?= base_url('config/public-holidays?view=week&year=' . date('Y', $nextDate) . '&month=' . date('m', $nextDate) . '&day=' . date('d', $nextDate)) ?>" class="px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </a>
                <a href="<?= base_url('config/public-holidays?view=week&year=' . date('Y', $nextYearDate) . '&month=' . date('m', $nextYearDate) . '&day=' . date('d', $nextYearDate)) ?>" class="px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">
                    <span class="material-symbols-outlined text-sm">keyboard_double_arrow_right</span>
                </a>
            <?php else: ?>
                <a href="<?= base_url('config/public-holidays?view=month&year=' . $prevYear . '&month=' . $currentMonth) ?>" class="px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">
                    <span class="material-symbols-outlined text-sm">keyboard_double_arrow_left</span>
                </a>
                <a href="<?= base_url('config/public-holidays?view=month&year=' . $prevMonthYear . '&month=' . $prevMonth) ?>" class="px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </a>
                <a href="<?= base_url('config/public-holidays?view=month&year=' . $nextMonthYear . '&month=' . $nextMonth) ?>" class="px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </a>
                <a href="<?= base_url('config/public-holidays?view=month&year=' . $nextYear . '&month=' . $currentMonth) ?>" class="px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm rounded-lg">
                    <span class="material-symbols-outlined text-sm">keyboard_double_arrow_right</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($view === 'day'): ?>
        <!-- Day View -->
        <div class="text-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?= $currentDayName ?></h3>
        </div>
        <div class="min-h-[400px] p-4 border border-gray-200 dark:border-gray-700 rounded-lg <?= isset($holidaysByDate[date('Y-m-d', $currentDate)]) ? (in_array('Federal', array_column($holidaysByDate[date('Y-m-d', $currentDate)], 'type')) ? 'bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-700' : 'bg-orange-100 dark:bg-orange-900/30 border-orange-300 dark:border-orange-700') : 'bg-gray-50 dark:bg-gray-900/50' ?> cursor-pointer" onclick="openAddModalWithDate('<?= date('Y-m-d', $currentDate) ?>')">
            <?php if (isset($holidaysByDate[date('Y-m-d', $currentDate)])): ?>
                <?php foreach ($holidaysByDate[date('Y-m-d', $currentDate)] as $holiday): ?>
                    <div class="p-3 mb-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600" onclick="event.stopPropagation(); showHolidayInfo('<?= addslashes(json_encode([$holiday])) ?>')">
                        <div class="font-medium text-gray-900 dark:text-white"><?= esc($holiday['name']) ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            <?php if ($holiday['type'] === 'Federal'): ?>
                                <span class="inline-flex items-center gap-1 text-xs">
                                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                    Federal Holiday
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-xs">
                                    <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                                    State Holiday
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    
    <?php elseif ($view === 'week'): ?>
        <!-- Week View -->
        <div class="grid grid-cols-7 gap-2">
            <?php for ($i = 0; $i < 7; $i++): ?>
                <?php
                $weekDate = strtotime("+$i days", $weekStart);
                $weekDateStr = date('Y-m-d', $weekDate);
                $hasHoliday = isset($holidaysByDate[$weekDateStr]);
                $holidayType = '';
                if ($hasHoliday) {
                    $holidayType = $holidaysByDate[$weekDateStr][0]['type'];
                }
                ?>
                <div>
                    <div class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 py-2">
                        <?= date('D n/j', $weekDate) ?>
                    </div>
                    <div class="min-h-[300px] p-2 border border-gray-200 dark:border-gray-700 rounded-lg <?= $hasHoliday ? ($holidayType === 'Federal' ? 'bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-700' : 'bg-orange-100 dark:bg-orange-900/30 border-orange-300 dark:border-orange-700') : 'bg-white dark:bg-gray-800' ?> cursor-pointer" onclick="openAddModalWithDate('<?= $weekDateStr ?>')">
                        <?php if ($hasHoliday): ?>
                            <?php foreach ($holidaysByDate[$weekDateStr] as $holiday): ?>
                                <div class="p-2 mb-2 bg-white dark:bg-gray-700 rounded text-xs" onclick="event.stopPropagation(); showHolidayInfo('<?= addslashes(json_encode([$holiday])) ?>')">
                                    <div class="font-medium text-gray-900 dark:text-white truncate"><?= esc($holiday['name']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    
    <?php else: ?>
        <!-- Month View -->
        <div class="grid grid-cols-7 gap-2">
        <!-- Day headers -->
        <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day): ?>
            <div class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 py-2"><?= $day ?></div>
        <?php endforeach; ?>

        <!-- Empty cells for days before month starts -->
        <?php for ($i = 0; $i < $dayOfWeek; $i++): ?>
            <div class="aspect-square p-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50"></div>
        <?php endfor; ?>

        <!-- Calendar days -->
        <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
            <?php
            $currentDate = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
            $hasHoliday = isset($holidaysByDate[$currentDate]);
            $holidayType = '';
            if ($hasHoliday) {
                $holidayType = $holidaysByDate[$currentDate][0]['type'];
            }
            ?>
            <div class="aspect-square p-2 border border-gray-200 dark:border-gray-700 rounded-lg relative <?= $hasHoliday ? ($holidayType === 'Federal' ? 'bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-700' : 'bg-orange-100 dark:bg-orange-900/30 border-orange-300 dark:border-orange-700') : 'bg-white dark:bg-gray-800' ?> hover:shadow-md transition-shadow cursor-pointer" onclick="<?= $hasHoliday ? "editHoliday(" . $holidaysByDate[$currentDate][0]['id'] . ", '" . addslashes($holidaysByDate[$currentDate][0]['name']) . "', '" . $holidaysByDate[$currentDate][0]['holiday_date'] . "', '" . $holidaysByDate[$currentDate][0]['type'] . "', " . $holidaysByDate[$currentDate][0]['is_active'] . ")" : "openAddModalWithDate('" . $currentDate . "')" ?>">
                <div class="text-sm font-medium text-gray-900 dark:text-white"><?= $day ?></div>
                <?php if ($hasHoliday): ?>
                    <?php foreach ($holidaysByDate[$currentDate] as $holiday): ?>
                        <div class="mt-1 text-xs text-gray-700 dark:text-gray-300 truncate"><?= esc($holiday['name']) ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="holidayModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
        <h2 id="modalTitle" class="text-xl font-bold text-gray-900 dark:text-white mb-4">Add Public Holiday</h2>
        <form id="holidayForm" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" id="holidayId" name="id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Holiday Name</label>
                <input type="text" id="holidayName" name="name" required placeholder="Enter holiday name..."
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                <input type="date" id="holidayDate" name="holiday_date" required
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary focus:border-primary [&::-webkit-calendar-picker-indicator]:dark:invert [&::-webkit-calendar-picker-indicator]:cursor-pointer">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                <select id="holidayType" name="type" required class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Select Type</option>
                    <option value="Federal">Federal Holiday</option>
                    <option value="State">State Holiday</option>
                </select>
            </div>
            <div id="statusField" class="mb-4 hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select id="holidayStatus" name="status" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button type="button" id="deleteBtn" onclick="deleteCurrentHoliday()" class="hidden flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Delete
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">delete</span>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Confirmation</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone</p>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6" id="deleteMessage">Are you sure you want to delete this public holiday?</p>
        <div class="flex gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors">
                Cancel
            </button>
            <button onclick="executeDelete()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                Delete
            </button>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    <?= csrf_field() ?>
</form>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Public Holiday';
    document.getElementById('holidayForm').action = '<?= base_url('config/public-holidays/store') ?>';
    document.getElementById('holidayId').value = '';
    document.getElementById('holidayName').value = '';
    document.getElementById('holidayDate').value = '';
    document.getElementById('holidayType').value = '';
    document.getElementById('holidayStatus').value = '1';
    document.getElementById('statusField').classList.add('hidden');
    document.getElementById('holidayModal').classList.remove('hidden');
}

function openAddModalWithDate(date) {
    document.getElementById('modalTitle').textContent = 'Add Public Holiday';
    document.getElementById('holidayForm').action = '<?= base_url('config/public-holidays/store') ?>';
    document.getElementById('holidayId').value = '';
    document.getElementById('holidayName').value = '';
    document.getElementById('holidayDate').value = date;
    document.getElementById('holidayType').value = '';
    document.getElementById('holidayStatus').value = '1';
    document.getElementById('statusField').classList.add('hidden');
    document.getElementById('deleteBtn').classList.add('hidden');
    document.getElementById('holidayModal').classList.remove('hidden');
}

function editHoliday(id, name, date, type, status) {
    document.getElementById('modalTitle').textContent = 'Edit Public Holiday';
    document.getElementById('holidayForm').action = '<?= base_url('config/public-holidays/update') ?>';
    document.getElementById('holidayId').value = id;
    document.getElementById('holidayName').value = name;
    document.getElementById('holidayDate').value = date;
    document.getElementById('holidayType').value = type;
    document.getElementById('holidayStatus').value = status;
    document.getElementById('statusField').classList.remove('hidden');
    document.getElementById('deleteBtn').classList.remove('hidden');
    document.getElementById('holidayModal').classList.remove('hidden');
}

function deleteCurrentHoliday() {
    const id = document.getElementById('holidayId').value;
    const name = document.getElementById('holidayName').value;
    if (id) {
        closeModal();
        confirmDelete(id, name);
    }
}

function closeModal() {
    document.getElementById('holidayModal').classList.add('hidden');
}

let deleteUrl = '';

function confirmDelete(id, itemName) {
    deleteUrl = '<?= base_url('config/public-holidays/delete/') ?>' + id;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${itemName}"?`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteUrl = '';
}

function executeDelete() {
    if (deleteUrl) {
        const form = document.getElementById('deleteForm');
        form.action = deleteUrl;
        form.submit();
    }
}

function changeView(view) {
    const urlParams = new URLSearchParams(window.location.search);
    const year = urlParams.get('year') || new Date().getFullYear();
    const month = urlParams.get('month') || String(new Date().getMonth() + 1).padStart(2, '0');
    const day = urlParams.get('day') || String(new Date().getDate()).padStart(2, '0');
    window.location.href = `<?= base_url('config/public-holidays') ?>?view=${view}&year=${year}&month=${month}&day=${day}`;
}
</script>

<?= $this->include('templates/footer') ?>
