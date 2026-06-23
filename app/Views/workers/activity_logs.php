<?= $this->include('templates/header') ?>

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
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Worker Activity Logs</h1>
            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Track worker movements and zone visits for <?= esc($date_label) ?></p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="exportToCSV()" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[20px]">download</span>
                <span>Export</span>
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col flex-1">
        <div class="flex flex-wrap justify-between items-center gap-4 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 flex-1 min-w-[280px]">
                <div class="relative w-full max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xl">search</span>
                    <input id="searchInput" class="pl-10 w-full h-11 bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary placeholder-gray-500 dark:placeholder-gray-400 transition-colors" placeholder="Search by name, ID, or zone..." type="text"/>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <button id="dateFilterBtn" onclick="toggleDateFilter()" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined text-lg">calendar_today</span>
                        <span id="dateFilterLabel"><?= esc($date_label) ?></span>
                        <span class="material-symbols-outlined text-lg">expand_more</span>
                    </button>
                    <div id="dateFilterDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 overflow-visible">
                        <div class="py-1">
                            <button onclick="filterByDate('today')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Today</button>
                            <button onclick="filterByDate('yesterday')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Yesterday</button>
                            <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                            <div class="px-4 py-3 pb-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Custom Date</label>
                                <input type="date" id="customDate" value="<?= esc($date) ?>" onchange="filterByCustomDate()" class="w-full px-3 py-2 text-sm bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-primary focus:border-primary">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left" id="activityTable">
                <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Worker Name</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID Tag</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Attendance Time In</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Attendance Time Out</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Zone Name</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time In</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time Out</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-background-dark divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($worker_activity)): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-5xl text-gray-300 dark:text-gray-600">assignment</span>
                                    <p class="text-gray-500 dark:text-gray-400 text-base font-medium">No worker activity recorded on this date</p>
                                    <p class="text-gray-400 dark:text-gray-500 text-sm">Activity logs will appear here once workers check in to zones</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($worker_activity as $worker): ?>
                            <?php foreach ($worker['zones'] as $index => $zone): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <?php if ($index === 0): ?>
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap" rowspan="<?= count($worker['zones']) ?>">
                                            <?= esc($worker['name']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300" rowspan="<?= count($worker['zones']) ?>">
                                            <?= esc($worker['department']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300" rowspan="<?= count($worker['zones']) ?>">
                                            <?= esc($worker['id_tag']) ?>
                                        </td>
                                        <td class="px-6 py-4 font-semibold <?= $worker['is_late'] ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' ?>" rowspan="<?= count($worker['zones']) ?>">
                                            <?= esc($worker['time_in']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300" rowspan="<?= count($worker['zones']) ?>">
                                            <?= esc($worker['time_out']) ?>
                                        </td>
                                    <?php endif; ?>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                        <?= esc($zone['name']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                        <?= esc($zone['entry']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                        <?= esc($zone['exit']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                        <?= esc($zone['duration']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (!empty($worker_activity)): ?>
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

function changeRowsPerPage() {
    rowsPerPage = parseInt(document.getElementById('rowsPerPageSelect').value);
    currentPage = 1;
    updatePagination();
}

// Toggle date filter dropdown
function toggleDateFilter() {
    const dropdown = document.getElementById('dateFilterDropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const dateDropdown = document.getElementById('dateFilterDropdown');
    const dateButton = document.getElementById('dateFilterBtn');
    if (dateDropdown && dateButton && !dateDropdown.contains(event.target) && !dateButton.contains(event.target)) {
        dateDropdown.classList.add('hidden');
    }
});

// Filter by predefined date
function filterByDate(period) {
    let targetDate = '';
    if (period === 'today') {
        targetDate = new Date().toISOString().split('T')[0];
    } else if (period === 'yesterday') {
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate() - 1);
        targetDate = yesterday.toISOString().split('T')[0];
    }
    window.location.href = '<?= base_url('workers/activity-logs') ?>?date=' + targetDate;
}

// Filter by custom date
function filterByCustomDate() {
    const dateInput = document.getElementById('customDate');
    if (dateInput.value) {
        window.location.href = '<?= base_url('workers/activity-logs') ?>?date=' + dateInput.value;
    }
}

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    currentPage = 1;
    updatePagination();
});

// Update pagination
function updatePagination() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('activityTable');
    const rows = table.querySelectorAll('tbody tr');
    
    let visibleRows = [];
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            visibleRows.push(row);
        }
    });
    
    // Hide all rows first
    rows.forEach(row => row.style.display = 'none');
    
    // Calculate pagination
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = Math.min(startIndex + rowsPerPage, visibleRows.length);
    
    // Show only rows for current page
    for (let i = startIndex; i < endIndex; i++) {
        visibleRows[i].style.display = '';
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

// Export to CSV functionality
function exportToCSV() {
    const table = document.getElementById('activityTable');
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    // Get headers
    const headers = [];
    rows[0].querySelectorAll('th').forEach(th => {
        headers.push(th.textContent.trim());
    });
    csv.push(headers.join(','));
    
    // Get data rows
    const dataRows = Array.from(rows).slice(1);
    const processedWorkers = new Set();
    
    dataRows.forEach(row => {
        if (row.style.display === 'none') return;
        
        const cells = row.querySelectorAll('td');
        if (cells.length === 0) return;
        
        const rowData = [];
        cells.forEach(cell => {
            let text = cell.textContent.trim();
            // Escape quotes and wrap in quotes if contains comma
            if (text.includes(',') || text.includes('"')) {
                text = '"' + text.replace(/"/g, '""') + '"';
            }
            rowData.push(text);
        });
        
        csv.push(rowData.join(','));
    });
    
    // Create download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'worker_activity_logs_<?= $date ?>.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?= $this->include('templates/footer') ?>
