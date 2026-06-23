<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= esc($title ?? 'Shift Preview') ?> - Workwise</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Montserrat", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
</head>
<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
<div class="relative flex h-screen w-full flex-col group/design-root">
    <!-- Mobile/Tablet Header with Workwise Branding -->
    <div class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-white dark:bg-background-dark border-b border-gray-200 dark:border-gray-700 px-3 py-2.5 flex items-center justify-between">
        <button id="mobile-menu-btn" class="p-2 rounded-lg bg-primary shadow-md hover:bg-primary/90" onclick="toggleMobileMenu()">
            <span class="material-symbols-outlined text-lg text-white">menu</span>
        </button>
        <h1 class="text-lg font-bold text-gray-900 dark:text-white absolute left-1/2 -translate-x-1/2">Workwise</h1>
        <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center shadow-md">
            <span class="material-symbols-outlined text-xl text-white">lock</span>
        </div>
    </div>
    
    <!-- Mobile/Tablet Overlay -->
    <div id="mobile-overlay" class="lg:hidden hidden fixed inset-0 bg-black bg-opacity-50 z-40" onclick="toggleMobileMenu()"></div>
    
    <div class="layout-container flex flex-1 flex-row overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar" class="fixed lg:relative inset-y-0 left-0 z-50 flex flex-col border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-background-dark w-56 p-3 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
            <div class="flex flex-col justify-between h-full">
                <div class="flex flex-col gap-3">
                    <!-- User Info -->
                    <div class="flex items-center gap-2">
                        <?php if (!empty($user['profile_photo'])): ?>
                            <img src="<?= base_url('uploads/profiles/' . $user['profile_photo']) ?>" 
                                 alt="Profile" 
                                 class="size-9 rounded-full object-cover border-2 border-primary">
                        <?php else: ?>
                            <div class="bg-primary flex items-center justify-center aspect-square rounded-full size-9">
                                <span class="text-white font-bold text-base"><?= esc($user['initials']) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex flex-col">
                            <h1 class="text-gray-900 dark:text-white text-sm font-bold leading-normal"><?= esc($user['name']) ?></h1>
                            <p class="text-gray-500 dark:text-gray-400 text-xs font-normal leading-normal"><?= esc($user['email']) ?></p>
                        </div>
                    </div>
                    
                    <!-- Navigation -->
                    <?php 
                    $currentUrl = uri_string();
                    $isDashboard = ($currentUrl == '' || $currentUrl == 'dashboard');
                    $isZones = (strpos($currentUrl, 'zones') !== false);
                    $isWorkers = (strpos($currentUrl, 'workers') !== false);
                    $isWorkerList = ($currentUrl == 'workers/list');
                    $isAttendance = (strpos($currentUrl, 'workers/attendance') !== false);
                    $isMonitoring = (strpos($currentUrl, 'workers/monitoring') !== false);
                    $isLocationSelector = (strpos($currentUrl, 'location-selector') !== false);
                    $isLateList = (strpos($currentUrl, 'workers/late-list') !== false);
                    $isEarlyList = (strpos($currentUrl, 'workers/early-list') !== false);
                    $isActivityLogs = (strpos($currentUrl, 'workers/activity-logs') !== false);
                    $isShiftPreview = (strpos($currentUrl, 'workers/shift-preview') !== false);
                    $isReports = (strpos($currentUrl, 'reports') !== false);
                    $isConfig = (strpos($currentUrl, 'config') !== false);
                    $isSettings = (strpos($currentUrl, 'settings') !== false);
                    ?>
                    <div class="flex flex-col gap-1 mt-2">
                        <a class="flex items-center gap-2 px-2 py-1.5 rounded-lg <?= $isDashboard ? 'bg-primary/10 dark:bg-primary/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('dashboard') ?>">
                            <span class="material-symbols-outlined text-lg <?= $isDashboard ? 'text-primary dark:text-white' : '' ?>">dashboard</span>
                            <p class="<?= $isDashboard ? 'text-primary dark:text-white' : '' ?> text-xs font-medium leading-normal">Dashboard</p>
                        </a>
                        <a class="flex items-center gap-2 px-2 py-1.5 rounded-lg <?= $isZones ? 'bg-primary/10 dark:bg-primary/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('zones') ?>">
                            <span class="material-symbols-outlined text-lg <?= $isZones ? 'text-primary dark:text-white' : '' ?>">location_city</span>
                            <p class="<?= $isZones ? 'text-primary dark:text-white' : '' ?> text-xs font-medium leading-normal">Zones</p>
                        </a>
                        <div class="flex flex-col">
                            <button type="button" onclick="toggleWorkersMenu()" class="flex items-center gap-2 px-2 py-1.5 rounded-lg <?= $isWorkers ? 'bg-primary/10 dark:bg-primary/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' ?> w-full">
                                <span class="material-symbols-outlined text-lg <?= $isWorkers ? 'text-primary dark:text-white' : '' ?>">group</span>
                                <p class="<?= $isWorkers ? 'text-primary dark:text-white' : '' ?> text-xs font-medium leading-normal flex-1 text-left">Workers</p>
                                <span id="workers-arrow" class="material-symbols-outlined text-sm transition-transform <?= $isWorkers ? 'text-primary dark:text-white rotate-180' : '' ?>">keyboard_arrow_down</span>
                            </button>
                            <div id="workers-submenu" class="flex flex-col pl-5 mt-1 gap-1 <?= $isWorkers ? '' : 'hidden' ?>">
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isWorkerList ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('workers/list') ?>">
                                    <p class="text-xs font-medium leading-normal">Worker List</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isAttendance ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('workers/attendance') ?>">
                                    <p class="text-xs font-medium leading-normal">Attendance</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isLocationSelector ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('location-selector') ?>">
                                    <p class="text-xs font-medium leading-normal">Location Selector</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isLateList ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('workers/late-list') ?>">
                                    <p class="text-xs font-medium leading-normal">Staff Late List</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isEarlyList ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('workers/early-list') ?>">
                                    <p class="text-xs font-medium leading-normal">Staff Early List</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isShiftPreview ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" 
                                   href="<?= base_url('workers/shift-preview') ?>">
                                    <p class="text-xs font-medium leading-normal">Shift Allocation Preview</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isActivityLogs ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('workers/activity-logs') ?>">
                                    <p class="text-xs font-medium leading-normal">Worker Activity Logs</p>
                                </a>
                            </div>
                        </div>
                        <a class="flex items-center gap-2 px-2 py-1.5 rounded-lg <?= $isReports ? 'bg-primary/10 dark:bg-primary/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('reports') ?>">
                            <span class="material-symbols-outlined text-lg <?= $isReports ? 'text-primary dark:text-white' : '' ?>">assessment</span>
                            <p class="<?= $isReports ? 'text-primary dark:text-white' : '' ?> text-xs font-medium leading-normal">Reports</p>
                        </a>
                    </div>
                </div>
                
                <!-- Bottom Navigation -->
                <div class="flex flex-col gap-1">
                    <a class="flex items-center gap-2 px-2 py-1.5 rounded-lg <?= $isConfig ? 'bg-primary/10 dark:bg-primary/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('config') ?>">
                        <span class="material-symbols-outlined text-lg <?= $isConfig ? 'text-primary dark:text-white' : '' ?>">tune</span>
                        <p class="<?= $isConfig ? 'text-primary dark:text-white' : '' ?> text-xs font-medium leading-normal">Config</p>
                    </a>
                    <a class="flex items-center gap-2 px-2 py-1.5 rounded-lg <?= $isSettings ? 'bg-primary/10 dark:bg-primary/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('settings') ?>">
                        <span class="material-symbols-outlined text-lg <?= $isSettings ? 'text-primary dark:text-white' : '' ?>">settings</span>
                        <p class="<?= $isSettings ? 'text-primary dark:text-white' : '' ?> text-xs font-medium leading-normal">Settings</p>
                    </a>
                    <a class="flex items-center gap-2 px-2 py-1.5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg" href="<?= base_url('logout') ?>">
                        <span class="material-symbols-outlined text-lg">logout</span>
                        <p class="text-xs font-medium leading-normal">Log Out</p>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <main class="flex-1 py-2 px-2 sm:py-3 sm:px-4 lg:py-4 lg:px-6 pt-16 md:pt-20 lg:pt-4 overflow-auto">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Staff Shift Allocation Preview</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?= esc($monthName ?? date('F Y')) ?></p>
                </div>
                
                <!-- Month Navigation -->
                <div class="flex items-center gap-4">
                    <a href="<?= base_url('workers/shift-preview?year=' . ($currentMonth == 1 ? $currentYear - 1 : $currentYear) . '&month=' . ($currentMonth == 1 ? 12 : $currentMonth - 1)) ?>" 
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        <span class="material-symbols-outlined text-xl">chevron_left</span>
                    </a>
                    
                    <span class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white whitespace-nowrap"><?= esc($monthName ?? date('F Y')) ?></span>
                    
                    <a href="<?= base_url('workers/shift-preview?year=' . ($currentMonth == 12 ? $currentYear + 1 : $currentYear) . '&month=' . ($currentMonth == 12 ? 1 : $currentMonth + 1)) ?>" 
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        <span class="material-symbols-outlined text-xl">chevron_right</span>
                    </a>
                </div>
                
                <!-- Advanced Filter Button -->
                <button onclick="toggleAdvancedFilter()" 
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl">filter_alt</span>
                    <span class="hidden sm:inline">Advanced Filter</span>
                </button>
            </div>
            
            <!-- Legend -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-green-600 dark:text-green-400 font-semibold">N220</span>
                        <span class="text-gray-700 dark:text-gray-300">Night (22:00)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-blue-600 dark:text-blue-400 font-semibold">M208</span>
                        <span class="text-gray-700 dark:text-gray-300">Morning (08:00)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-orange-600 dark:text-orange-400 font-semibold">RD</span>
                        <span class="text-gray-700 dark:text-gray-300">Rest Day</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-red-600 dark:text-red-400 font-semibold">OD</span>
                        <span class="text-gray-700 dark:text-gray-300">Off Day</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-purple-600 dark:text-purple-400 font-semibold">RG08</span>
                        <span class="text-gray-700 dark:text-gray-300">Regular (08:00)</span>
                    </div>
                </div>
            </div>
            
            <!-- Table Container -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-600 sticky left-0 bg-gray-100 dark:bg-gray-700 z-10">
                                    Name
                                </th>
                                <?php if (!empty($dates)): ?>
                                    <?php foreach ($dates as $dateInfo): ?>
                                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600 min-w-[80px]">
                                            <div><?= esc($dateInfo['day']) ?>-<?= esc(strtoupper($dateInfo['month'])) ?></div>
                                        </th>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($shiftPreview)): ?>
                                <tr>
                                    <td colspan="<?= (count($dates ?? []) + 1) ?>" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center gap-3">
                                            <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600">calendar_today</span>
                                            <p class="text-gray-500 dark:text-gray-400">No shift allocations found for this period</p>
                                            <a href="<?= base_url('config/staff-shift-allocation') ?>" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                                                Configure Shifts
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($shiftPreview as $index => $row): ?>
                                    <tr class="<?= $index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-750' ?> hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white border-r border-gray-300 dark:border-gray-600 sticky left-0 <?= $index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-750' ?>">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-xs font-bold">
                                                    <?= strtoupper(substr($row['name'] ?? 'U', 0, 2)) ?>
                                                </div>
                                                <div>
                                                    <div><?= esc($row['name']) ?></div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400"><?= esc($row['worker_id'] ?? '') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <?php if (!empty($row['days'])): ?>
                                            <?php foreach ($row['days'] as $shift): ?>
                                                <td class="px-3 py-3 text-center border-r border-gray-200 dark:border-gray-600">
                                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded <?= getShiftColorClass($shift) ?>">
                                                        <?= esc($shift) ?>
                                                    </span>
                                                </td>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Summary Stats -->
            <?php if (!empty($shiftPreview)): ?>
            <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Staff</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white"><?= count($shiftPreview) ?></div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Working Days</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white"><?= count($dates ?? []) ?></div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Shifts</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white"><?= count($shiftPreview) * count($dates ?? []) ?></div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Period</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white"><?= esc($monthName ?? date('F Y')) ?></div>
                </div>
            </div>
            <?php endif; ?>
            
        </main>
    </div>
</div>

<?php
function getShiftColorClass($shift) {
    $colors = [
        'N220' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        'M208' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
        'RD' => 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200',
        'OD' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
        'RG08' => 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200',
        'RG06' => 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200',
        'A315' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
        'M107' => 'bg-cyan-100 dark:bg-cyan-900 text-cyan-800 dark:text-cyan-200',
        'RG1' => 'bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200',
        'N119' => 'bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200',
    ];
    
    return $colors[$shift] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
}
?>

<script>
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobile-overlay');
    
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

function toggleWorkersMenu() {
    const submenu = document.getElementById('workers-submenu');
    const arrow = document.getElementById('workers-arrow');
    
    submenu.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}

function toggleAdvancedFilter() {
    alert('Advanced Filter feature coming soon!');
}
</script>

</body>
</html>