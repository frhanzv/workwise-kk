<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6 max-w-[1600px] w-full mx-auto">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mt-6 md:mt-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Reports & Analytics</h1>
            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Comprehensive reports on worker activity, zone usage, and productivity metrics.</p>
        </div>
        
        <!-- Date Filter -->
        <div class="flex items-center gap-2">
            <div class="relative">
                <button id="dateFilterBtn" onclick="toggleDateFilter()" class="flex items-center justify-center h-10 px-4 gap-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800">
                    <span class="material-symbols-outlined">calendar_today</span>
                    <span class="whitespace-nowrap"><?= esc($filter_label) ?></span>
                    <span class="material-symbols-outlined">expand_more</span>
                </button>
                <div id="dateFilterDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50">
                    <div class="py-1">
                        <button onclick="filterByDate('today')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Today</button>
                        <button onclick="filterByDate('yesterday')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Yesterday</button>
                        <button onclick="filterByDate('week')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">This Week</button>
                        <button onclick="filterByDate('last_week')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Last Week</button>
                        <button onclick="filterByDate('month')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">This Month</button>
                        <button onclick="filterByDate('last_month')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Last Month</button>
                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                        <div class="px-4 py-3">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Custom Date Range</label>
                            <div class="flex flex-col gap-2">
                                <input type="date" id="customStartDate" class="w-full px-3 py-2 text-sm bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-primary focus:border-primary">
                                <input type="date" id="customEndDate" class="w-full px-3 py-2 text-sm bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-primary focus:border-primary">
                                <button onclick="filterByCustomDateRange()" class="w-full px-3 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary/90">Apply</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button onclick="exportToPDF()" class="flex items-center justify-center h-10 px-4 gap-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90">
                <span class="material-symbols-outlined">download</span>
                <span>Export PDF</span>
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <p class="text-gray-600 dark:text-gray-300 text-xs font-medium">Active Workers</p>
                    <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= esc($summary['active_workers']) ?></p>
                    <p class="text-gray-500 dark:text-gray-400 text-xs">of <?= esc($summary['total_workers']) ?> total</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">group</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <p class="text-gray-600 dark:text-gray-300 text-xs font-medium">Total Check-Ins</p>
                    <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= esc($summary['total_check_ins']) ?></p>
                    <p class="text-gray-500 dark:text-gray-400 text-xs"><?= esc($summary['completed_visits']) ?> completed</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-600 dark:text-green-400">login</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <p class="text-gray-600 dark:text-gray-300 text-xs font-medium">Avg. Visit Duration</p>
                    <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= esc($summary['avg_duration']) ?></p>
                    <p class="text-gray-500 dark:text-gray-400 text-xs">per zone visit</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">schedule</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <p class="text-gray-600 dark:text-gray-300 text-xs font-medium">Active Zones</p>
                    <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= esc($summary['total_zones']) ?></p>
                    <p class="text-gray-500 dark:text-gray-400 text-xs">monitored areas</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">location_on</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Activity Chart -->
        <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <h2 class="text-gray-900 dark:text-white text-lg font-bold mb-4">Daily Activity Trend</h2>
            <div class="h-64 flex items-center justify-center" id="dailyActivityContainer">
                <canvas id="dailyActivityChart"></canvas>
            </div>
        </div>

        <!-- Top 5 Zones by Visits -->
        <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <h2 class="text-gray-900 dark:text-white text-lg font-bold mb-4">Top Zones by Visits</h2>
            <div class="h-64 flex items-center justify-center" id="topZonesContainer">
                <canvas id="topZonesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Zone Analytics Table -->
    <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-gray-900 dark:text-white text-lg font-bold">Zone Analytics</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Detailed breakdown of zone usage and performance</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3">Zone Name</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Total Visits</th>
                        <th class="px-4 py-3">Completed</th>
                        <th class="px-4 py-3">Avg. Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($zone_stats)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-gray-600">location_off</span>
                                    <p>No zone activity recorded for this period</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($zone_stats as $zone): ?>
                            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white"><?= esc($zone['zone_name']) ?></td>
                                <td class="px-4 py-3"><?= esc($zone['location']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                        <?= esc($zone['total_visits']) ?> visits
                                    </span>
                                </td>
                                <td class="px-4 py-3"><?= esc($zone['completed_visits']) ?></td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white"><?= esc($zone['avg_duration']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Worker Productivity Table -->
    <div class="bg-white dark:bg-background-dark rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-gray-900 dark:text-white text-lg font-bold">Worker Productivity Report</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Performance metrics for all active workers</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3">Worker Name</th>
                        <th class="px-4 py-3">Department</th>
                        <th class="px-4 py-3">Position</th>
                        <th class="px-4 py-3">Total Visits</th>
                        <th class="px-4 py-3">Zones Visited</th>
                        <th class="px-4 py-3">Total Time</th>
                        <th class="px-4 py-3">Avg. Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($worker_stats)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-gray-600">person_off</span>
                                    <p>No worker activity recorded for this period</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($worker_stats as $worker): ?>
                            <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white"><?= esc($worker['worker_name']) ?></td>
                                <td class="px-4 py-3"><?= esc($worker['department']) ?></td>
                                <td class="px-4 py-3 text-xs"><?= esc($worker['position']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                        <?= esc($worker['total_visits']) ?> visits
                                    </span>
                                </td>
                                <td class="px-4 py-3"><?= esc($worker['zones_visited']) ?> zones</td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white"><?= esc($worker['total_duration']) ?></td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white"><?= esc($worker['avg_duration']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Toggle date filter dropdown
function toggleDateFilter() {
    const dropdown = document.getElementById('dateFilterDropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('dateFilterDropdown');
    const button = document.getElementById('dateFilterBtn');
    
    if (!dropdown.contains(event.target) && !button.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});

// Filter by predefined date ranges
function filterByDate(filter) {
    window.location.href = '<?= base_url('reports') ?>?filter=' + filter;
}

// Filter by custom date range
function filterByCustomDateRange() {
    const startDate = document.getElementById('customStartDate').value;
    const endDate = document.getElementById('customEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }
    
    if (startDate > endDate) {
        alert('Start date must be before end date');
        return;
    }
    
    window.location.href = '<?= base_url('reports') ?>?filter=custom&start_date=' + startDate + '&end_date=' + endDate;
}

// Export to PDF
function exportToPDF() {
    const urlParams = new URLSearchParams(window.location.search);
    let pdfUrl = '<?= base_url('reports/export-pdf') ?>';
    
    // Preserve current filters
    const params = [];
    if (urlParams.has('filter')) {
        params.push('filter=' + urlParams.get('filter'));
    }
    if (urlParams.has('start_date')) {
        params.push('start_date=' + urlParams.get('start_date'));
    }
    if (urlParams.has('end_date')) {
        params.push('end_date=' + urlParams.get('end_date'));
    }
    
    if (params.length > 0) {
        pdfUrl += '?' + params.join('&');
    }
    
    window.location.href = pdfUrl;
}

// Daily Activity Chart
const dailyActivityData = <?= json_encode($daily_activity) ?>;
const ctx1 = document.getElementById('dailyActivityChart');
const dailyActivityContainer = document.getElementById('dailyActivityContainer');

// Check if there's any data
const hasDailyData = dailyActivityData.some(d => d.check_ins > 0);

if (!hasDailyData) {
    // Hide canvas and show no data message
    ctx1.style.display = 'none';
    dailyActivityContainer.innerHTML = '<div class="flex flex-col items-center justify-center h-full text-gray-400 dark:text-gray-500"><span class="material-symbols-outlined text-5xl mb-2">show_chart</span><p class="text-sm">No activity data available</p></div>';
} else {
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: dailyActivityData.map(d => d.label),
            datasets: [{
                label: 'Check-Ins',
                data: dailyActivityData.map(d => d.check_ins),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

// Top Zones Chart
const zoneStatsData = <?= json_encode(array_slice($zone_stats, 0, 5)) ?>;
const ctx2 = document.getElementById('topZonesChart');
const topZonesContainer = document.getElementById('topZonesContainer');

// Check if there's any data
const hasZoneData = zoneStatsData.length > 0 && zoneStatsData.some(z => z.total_visits > 0);

if (!hasZoneData) {
    // Hide canvas and show no data message
    ctx2.style.display = 'none';
    topZonesContainer.innerHTML = '<div class="flex flex-col items-center justify-center h-full text-gray-400 dark:text-gray-500"><span class="material-symbols-outlined text-5xl mb-2">location_off</span><p class="text-sm">No zone visit data available</p></div>';
} else {
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: zoneStatsData.map(z => z.zone_name),
            datasets: [{
                label: 'Total Visits',
                data: zoneStatsData.map(z => z.total_visits),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)'
                ],
                borderColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)',
                    'rgb(139, 92, 246)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}
</script>

<?= $this->include('templates/footer') ?>
