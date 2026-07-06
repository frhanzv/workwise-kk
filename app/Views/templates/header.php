<?php
/**
 * Ensure $user variable is always available
 */
if (!isset($user)) {
    $baseController = new class extends \App\Controllers\BaseController {
        public function getUserData() {
            return $this->getLoggedInUser();
        }
    };
    $user = $baseController->getUserData();
}

// Ensure $title is set
$title = $title ?? 'Dashboard';
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= esc($title) ?> - Workwise</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <style>
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 20;
        }
    </style>
</head>
<body>
<!-- Rest of your header template continues here -->
    </style>
    <script id="tailwind-config">
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
                    $isDashboardOverview = ($currentUrl == '' || $currentUrl == 'dashboard');
                    $isZones = (strpos($currentUrl, 'zones') !== false);
                    $isWorkers = (strpos($currentUrl, 'workers') !== false);
                    $isWorkerList = ($currentUrl == 'workers/list');
                    $isAttendance = (strpos($currentUrl, 'workers/attendance') !== false);
                    $isLocationSelector = (strpos($currentUrl, 'location-selector') !== false);
                    $isLateList = (strpos($currentUrl, 'workers/late-list') !== false);
                    $isEarlyList = (strpos($currentUrl, 'workers/early-list') !== false);
                    $isActivityLogs = (strpos($currentUrl, 'workers/activity-logs') !== false);
                    $isShiftPreview = (strpos($currentUrl, 'workers/shift-preview') !== false);
                    $isReports = (strpos($currentUrl, 'reports') !== false);
                    $isConfig = (strpos($currentUrl, 'config') !== false);
                    $isSettings = (strpos($currentUrl, 'settings') !== false);
                    $isInventoryMonitoring = (strpos($currentUrl, 'inventory/monitoring') !== false);
                    $isStockCheck = (strpos($currentUrl, 'inventory/stock-check') !== false);
                    $isStockLedger = (strpos($currentUrl, 'inventory/stock-ledger') !== false);
                    $isLocationMismatch = (strpos($currentUrl, 'inventory/location-mismatch') !== false);
                    $isTagStockIn = (strpos($currentUrl, 'inventory/tag-stock-in') !== false);
                    $isSearchStock = (strpos($currentUrl, 'inventory/search-stock') !== false);
                    $isProducts = (strpos($currentUrl, 'products') !== false);
                    $isRawMaterials = (strpos($currentUrl, 'raw-materials') !== false);
                    $isInventory = $isProducts || $isRawMaterials || $isStockCheck || $isStockLedger || $isLocationMismatch || $isTagStockIn || $isSearchStock;
                    $isDashboard = $isDashboardOverview || $isInventoryMonitoring;
                    $showProduction = false; // set true to re-enable Production nav
                    $isProduction = $showProduction && (strpos($currentUrl, 'production') !== false);
                    ?>
                    <div class="flex flex-col gap-1 mt-2">
                        <div class="flex flex-col">
                            <button type="button" onclick="toggleDashboardMenu()" class="flex items-center gap-2 px-2 py-1.5 rounded-lg <?= $isDashboard ? 'bg-primary/10 dark:bg-primary/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' ?> w-full">
                                <span class="material-symbols-outlined text-lg <?= $isDashboard ? 'text-primary dark:text-white' : '' ?>">dashboard</span>
                                <p class="<?= $isDashboard ? 'text-primary dark:text-white' : '' ?> text-xs font-medium leading-normal flex-1 text-left">Dashboard</p>
                                <span id="dashboard-arrow" class="material-symbols-outlined text-sm transition-transform <?= $isDashboard ? 'text-primary dark:text-white rotate-180' : '' ?>">keyboard_arrow_down</span>
                            </button>
                            <div id="dashboard-submenu" class="flex flex-col pl-5 mt-1 gap-1 <?= $isDashboard ? '' : 'hidden' ?>">
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isDashboardOverview ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('dashboard') ?>">
                                    <p class="text-xs font-medium leading-normal">Productivity Dashboard</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isInventoryMonitoring ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('inventory/monitoring') ?>">
                                    <p class="text-xs font-medium leading-normal">Inventory Dashboard</p>
                                </a>
                            </div>
                        </div>
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
                        <div class="flex flex-col">
                            <button type="button" onclick="toggleInventoryMenu()" class="flex items-center gap-2 px-2 py-1.5 rounded-lg <?= $isInventory ? 'bg-primary/10 dark:bg-primary/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' ?> w-full">
                                <span class="material-symbols-outlined text-lg <?= $isInventory ? 'text-primary dark:text-white' : '' ?>">inventory_2</span>
                                <p class="<?= $isInventory ? 'text-primary dark:text-white' : '' ?> text-xs font-medium leading-normal flex-1 text-left">Inventory</p>
                                <span id="inventory-arrow" class="material-symbols-outlined text-sm transition-transform <?= $isInventory ? 'text-primary dark:text-white rotate-180' : '' ?>">keyboard_arrow_down</span>
                            </button>
                            <div id="inventory-submenu" class="flex flex-col pl-5 mt-1 gap-1 <?= $isInventory ? '' : 'hidden' ?>">
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isProducts ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('products/list') ?>">
                                    <p class="text-xs font-medium leading-normal">Product Master List</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isRawMaterials ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('raw-materials/list') ?>">
                                    <p class="text-xs font-medium leading-normal">Raw Material Master List</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isSearchStock ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('inventory/search-stock') ?>">
                                    <p class="text-xs font-medium leading-normal">Search Stock</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isTagStockIn ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('inventory/tag-stock-in') ?>">
                                    <p class="text-xs font-medium leading-normal">Tag + Stock In</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isStockCheck ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('inventory/stock-check') ?>">
                                    <p class="text-xs font-medium leading-normal">Stock Check</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isStockLedger ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('inventory/stock-ledger') ?>">
                                    <p class="text-xs font-medium leading-normal">Stock Ledger</p>
                                </a>
                                <a class="flex items-center gap-2 px-2 py-1 rounded-lg <?= $isLocationMismatch ? 'bg-primary/10 dark:bg-primary/20 text-primary dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('inventory/location-mismatch') ?>">
                                    <p class="text-xs font-medium leading-normal">Location Mismatch</p>
                                </a>
                            </div>
                        </div>
                        <?php if ($showProduction): ?>
                        <a class="flex items-center gap-2 px-2 py-1.5 rounded-lg <?= $isProduction ? 'bg-primary/10 dark:bg-primary/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' ?>" href="<?= base_url('production/list') ?>">
                            <span class="material-symbols-outlined text-lg <?= $isProduction ? 'text-primary dark:text-white' : '' ?>">precision_manufacturing</span>
                            <p class="<?= $isProduction ? 'text-primary dark:text-white' : '' ?> text-xs font-medium leading-normal">Production</p>
                        </a>
                        <?php endif; ?>
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
