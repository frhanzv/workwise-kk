<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Auth routes (no auth filter)
$routes->get('login', 'Auth::login');
$routes->post('auth/authenticate', 'Auth::authenticate');
$routes->get('logout', 'Auth::logout');

// RFID API routes (no auth filter - for RFID reader webhook)
$routes->group('api/rfid', function($routes) {
    $routes->post('tag-read', 'RFID::tagRead');        // Main webhook endpoint
    $routes->get('scan', 'RFID::scan');                // Testing endpoint
    $routes->get('scan-zone', 'RFID::scanZone');       // Multi-reader endpoint (also tracks assets)
    $routes->get('status', 'RFID::status');            // Reader status
    $routes->get('test-connection', 'RFID::testConnection'); // Test connection
    $routes->post('manual', 'RFID::manual');           // Manual attendance entry
});

// Protected routes (require authentication)
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('location-selector', 'LocationSelector::index');
    $routes->post('location-selector/select', 'LocationSelector::select');
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('dashboard/live-data', 'Dashboard::liveData');
    $routes->get('zones', 'Zones::index');
    $routes->get('zones/add', 'Zones::add');
    $routes->post('zones/store', 'Zones::store');
    $routes->get('zones/view/(:segment)', 'Zones::view/$1');
    $routes->get('zones/edit/(:segment)', 'Zones::edit/$1');
    $routes->post('zones/update/(:num)', 'Zones::update/$1');
    $routes->post('zones/update/(:segment)', 'Zones::update/$1');
    $routes->post('zones/delete/(:segment)', 'Zones::delete/$1');
    $routes->get('zones/update-icons', 'Zones::updateAllIcons');
    $routes->post('zones/test-connection/(:segment)', 'Zones::testConnection/$1');
    $routes->post('zones/test-antenna-connection', 'Zones::testAntennaConnection');
    $routes->get('debug/zones', 'DebugZones::index');
    $routes->get('workers', 'Workers::index');
    $routes->get('workers/list', 'Workers::workerList');
    $routes->get('workers/tracking/(:segment)', 'Workers::tracking/$1');
    $routes->get('workers/add', 'Workers::add');
    $routes->post('workers/store', 'Workers::store');
    $routes->get('workers/view/(:segment)', 'Workers::view/$1');
    $routes->get('workers/edit/(:segment)', 'Workers::edit/$1');
    $routes->post('workers/update/(:segment)', 'Workers::update/$1');
    $routes->post('workers/delete/(:segment)', 'Workers::delete/$1');
    $routes->get('workers/attendance', 'Workers::attendance');
    $routes->get('workers/attendance-data', 'Workers::attendanceData');
    $routes->get('workers/monitoring', 'Workers::monitoring');
    $routes->get('workers/monitoring-data', 'Workers::monitoringData');
    $routes->get('workers/late-list', 'Workers::lateList');
    $routes->get('workers/export-late-list', 'Workers::exportLateList');
    $routes->get('workers/early-list', 'Workers::earlyList');
    $routes->get('workers/export-early-list', 'Workers::exportEarlyList');
    $routes->get('workers/activity-logs', 'Workers::activityLogs');
    $routes->post('workers/record-attendance', 'Workers::recordAttendance');
    $routes->post('workers/mark-leave', 'Workers::markLeave');
    $routes->post('workers/remove-leave', 'Workers::removeLeave');
    $routes->post('workers/batchUpload', 'Workers::batchUpload');
    $routes->post('workers/update-rfid-tag', 'Workers::updateRfidTag');
    $routes->get('workers/check-rfid-card', 'Workers::checkRfidCard');
    $routes->get('workers/shift-preview', 'Workers::shiftPreview');
    $routes->get('workers/shift-preview-debug', 'Workers::shiftPreviewDebug');
    
    // Asset tracking routes
    $routes->get('workers/assets', 'Workers::assetList');
    $routes->post('workers/assign-asset', 'Workers::assignAsset');
    $routes->post('workers/unassign-asset', 'Workers::unassignAsset');
    $routes->post('workers/store-asset', 'Workers::storeAsset');
    $routes->post('workers/update-asset-epc', 'Workers::updateAssetEpc');
    $routes->get('workers/get-assets/(:segment)', 'Workers::getWorkerAssets/$1');
    $routes->get('assets/in-zone/(:segment)', 'Workers::getAssetsInZone/$1');
    
    // Inventory monitoring
    $routes->get('inventory/monitoring', 'Inventory::monitoring');
    $routes->get('inventory/monitoring-data', 'Inventory::monitoringData');
    $routes->get('inventory/item-detail', 'Inventory::itemDetail');

    // Products routes
    $routes->get('products', 'Products::index');
    $routes->get('products/list', 'Products::list');
    $routes->get('products/add', 'Products::add');
    $routes->post('products/store', 'Products::store');
    $routes->get('products/view/(:num)', 'Products::view/$1');
    $routes->get('products/edit/(:num)', 'Products::edit/$1');
    $routes->post('products/update/(:num)', 'Products::update/$1');
    $routes->post('products/delete/(:num)', 'Products::delete/$1');

    // Raw Materials routes
    $routes->get('raw-materials', 'RawMaterials::index');
    $routes->get('raw-materials/list', 'RawMaterials::list');
    $routes->get('raw-materials/add', 'RawMaterials::add');
    $routes->post('raw-materials/store', 'RawMaterials::store');
    $routes->get('raw-materials/view/(:num)', 'RawMaterials::view/$1');
    $routes->get('raw-materials/edit/(:num)', 'RawMaterials::edit/$1');
    $routes->post('raw-materials/update/(:num)', 'RawMaterials::update/$1');
    $routes->post('raw-materials/delete/(:num)', 'RawMaterials::delete/$1');

    // Production Batches routes
    $routes->get('production', 'ProductionBatches::index');
    $routes->get('production/list', 'ProductionBatches::list');
    $routes->get('production/add', 'ProductionBatches::add');
    $routes->post('production/store', 'ProductionBatches::store');
    $routes->get('production/view/(:num)', 'ProductionBatches::view/$1');
    $routes->post('production/add-material/(:num)', 'ProductionBatches::addMaterial/$1');
    $routes->post('production/remove-material/(:num)', 'ProductionBatches::removeMaterial/$1');
    $routes->post('production/add-product/(:num)', 'ProductionBatches::addProduct/$1');
    $routes->post('production/remove-product/(:num)', 'ProductionBatches::removeProduct/$1');
    $routes->post('production/complete/(:num)', 'ProductionBatches::complete/$1');
    $routes->post('production/cancel/(:num)', 'ProductionBatches::cancel/$1');

    // Reports routes
    $routes->get('reports', 'Reports::index');
    $routes->get('reports/export-pdf', 'Reports::exportPdf');
    
    // Config routes
    $routes->get('config', 'Config::index');
    $routes->get('config/rfid-settings', 'Config::rfidSettings');
    $routes->post('config/rfid-settings/update', 'Config::updateRfidSettings');
    $routes->get('config/antenna-mode', 'Config::antennaMode');
    $routes->post('config/antenna-mode/store', 'Config::storeAntennaMode');
    $routes->post('config/antenna-mode/update', 'Config::updateAntennaMode');
    $routes->post('config/antenna-mode/toggle/(:num)', 'Config::toggleAntennaMode/$1');
    $routes->post('config/antenna-mode/delete/(:num)', 'Config::deleteAntennaMode/$1');
    $routes->get('config/operating-hours', 'Config::operatingHours');
    $routes->post('config/operating-hours/store', 'Config::storeOperatingHour');
    $routes->post('config/operating-hours/update', 'Config::updateOperatingHour');
    $routes->post('config/operating-hours/toggle/(:num)', 'Config::toggleOperatingHour/$1');
    $routes->post('config/operating-hours/delete/(:num)', 'Config::deleteOperatingHour/$1');
    $routes->get('config/shifts', 'Config::shifts');
    $routes->post('config/shifts/store', 'Config::storeShift');
    $routes->post('config/shifts/update', 'Config::updateShift');
    $routes->post('config/shifts/toggle/(:num)', 'Config::toggleShift/$1');
    $routes->post('config/shifts/delete/(:num)', 'Config::deleteShift/$1');
    $routes->get('config/staff-groups', 'Config::staffGroups');
    $routes->post('config/staff-groups/store', 'Config::storeStaffGroup');
    $routes->post('config/staff-groups/update', 'Config::updateStaffGroup');
    $routes->post('config/staff-groups/toggle/(:num)', 'Config::toggleStaffGroup/$1');
    $routes->post('config/staff-groups/delete/(:num)', 'Config::deleteStaffGroup/$1');
    $routes->get('config/public-holidays', 'Config::publicHolidays');
    $routes->post('config/public-holidays/store', 'Config::storePublicHoliday');
    $routes->post('config/public-holidays/update', 'Config::updatePublicHoliday');
    $routes->post('config/public-holidays/toggle/(:num)', 'Config::togglePublicHoliday/$1');
    $routes->post('config/public-holidays/delete/(:num)', 'Config::deletePublicHoliday/$1');
    $routes->get('config/leave-reasons', 'Config::leaveReasons');
    $routes->post('config/leave-reasons/store', 'Config::storeLeaveReason');
    $routes->post('config/leave-reasons/update', 'Config::updateLeaveReason');
    $routes->post('config/leave-reasons/toggle/(:num)', 'Config::toggleLeaveReason/$1');
    $routes->post('config/leave-reasons/delete/(:num)', 'Config::deleteLeaveReason/$1');
    $routes->get('config/system-logs', 'Config::systemLogs');
    $routes->get('config/load-more-logs', 'Config::loadMoreLogs');
    $routes->get('config/groups-shift', 'Config::groupsShift');
    $routes->post('config/groups-shift/store', 'Config::storeGroupsShift');
    $routes->post('config/groups-shift/update', 'Config::updateGroupsShift');
    $routes->post('config/groups-shift/toggle/(:num)', 'Config::toggleGroupsShift/$1');
    $routes->post('config/groups-shift/delete/(:num)', 'Config::deleteGroupsShift/$1');
    $routes->get('config/staff-availability', 'Config::staffAvailability');
    $routes->post('config/staff-availability/store', 'Config::storeStaffAvailability');
    $routes->post('config/staff-availability/update', 'Config::updateStaffAvailability');
    $routes->post('config/staff-availability/toggle/(:num)', 'Config::toggleStaffAvailability/$1');
    $routes->post('config/staff-availability/delete/(:num)', 'Config::deleteStaffAvailability/$1');
    $routes->get('config/staff-shift-allocation', 'Config::staffShiftAllocation');
    $routes->get('config/staff-shift-allocation/add', 'Config::addStaffShiftAllocation');
    $routes->get('config/staff-shift-allocation/edit', 'Config::editStaffShiftAllocation');
    $routes->post('config/staff-shift-allocation/save', 'Config::saveStaffShiftAllocation');
    $routes->post('config/staff-shift-allocation/copy', 'Config::copyStaffShiftSequence');
    $routes->post('config/staff-shift-allocation/delete', 'Config::deleteStaffShiftAllocation');
    $routes->get('config/departments', 'Config::departments');
    $routes->post('config/departments/store', 'Config::storeDepartment');
    $routes->post('config/departments/update', 'Config::updateDepartment');
    $routes->post('config/departments/toggle/(:num)', 'Config::toggleDepartment/$1');
    $routes->post('config/departments/delete/(:num)', 'Config::deleteDepartment/$1');
    $routes->get('config/job-positions', 'Config::jobPositions');
    $routes->post('config/job-positions/store', 'Config::storeJobPosition');
    $routes->post('config/job-positions/update', 'Config::updateJobPosition');
    $routes->post('config/job-positions/toggle/(:num)', 'Config::toggleJobPosition/$1');
    $routes->post('config/job-positions/delete/(:num)', 'Config::deleteJobPosition/$1');
    $routes->get('config/states', 'Config::states');
    $routes->post('config/states/store', 'Config::storeState');
    $routes->post('config/states/update', 'Config::updateState');
    $routes->post('config/states/toggle/(:num)', 'Config::toggleState/$1');
    $routes->post('config/states/delete/(:num)', 'Config::deleteState/$1');
    $routes->get('config/cities', 'Config::cities');
    $routes->post('config/cities/store', 'Config::storeCity');
    $routes->post('config/cities/update', 'Config::updateCity');
    $routes->post('config/cities/toggle/(:num)', 'Config::toggleCity/$1');
    $routes->post('config/cities/delete/(:num)', 'Config::deleteCity/$1');
    $routes->get('config/countries', 'Config::countries');
    $routes->post('config/countries/store', 'Config::storeCountry');
    $routes->post('config/countries/update', 'Config::updateCountry');
    $routes->post('config/countries/toggle/(:num)', 'Config::toggleCountry/$1');
    $routes->post('config/countries/delete/(:num)', 'Config::deleteCountry/$1');
    $routes->get('config/get-states-by-country', 'Config::getStatesByCountry');
    $routes->get('config/rfid-reader', 'Config::rfidReader');
    $routes->post('config/rfid-reader/update', 'Config::updateRfidReader');
    $routes->get('config/rfid-reader/test-connection', 'Config::testRfidConnection');
    $routes->get('config/roles', 'Config::roles');
    $routes->post('config/roles/store', 'Config::storeRole');
    $routes->post('config/roles/update', 'Config::updateRole');
    $routes->get('config/roles/toggle/(:num)', 'Config::toggleRole/$1');
    $routes->get('config/roles/delete/(:num)', 'Config::deleteRole/$1');
    
    // Settings routes
    $routes->get('settings', 'Settings::index');
    $routes->post('settings/update-profile', 'Settings::updateProfile');
    $routes->post('settings/upload-photo', 'Settings::uploadPhoto');
    $routes->get('settings/reset-photo', 'Settings::resetPhoto');
    $routes->post('settings/change-password', 'Settings::changePassword');
    $routes->get('test', 'Test::index');

    // Analytics chat interface
    $routes->get('analytics', 'Analytics::chat');
    $routes->get('analytics/chat', 'Analytics::chat');

    // Analytics API endpoints
    $routes->post('analytics/query', 'Analytics::query');
    $routes->get('analytics/getHistory', 'Analytics::getHistory');
    $routes->post('analytics/clearHistory', 'Analytics::clearHistory');
    $routes->post('analytics/deleteHistoryItems', 'Analytics::deleteHistoryItems');
    $routes->get('analytics/debug', 'Analytics::debugData');
    $routes->get('analytics/chat', 'Analytics::chat');
    $routes->get('analytics/getConversations', 'Analytics::getConversations');
    $routes->get('analytics/getConversationMessages/(:num)', 'Analytics::getConversationMessages/$1');
    $routes->delete('analytics/deleteConversation/(:num)', 'Analytics::deleteConversation/$1');
    $routes->post('analytics/renameConversation', 'Analytics::renameConversation');

    
});
