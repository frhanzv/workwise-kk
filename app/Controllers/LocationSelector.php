<?php

namespace App\Controllers;

use App\Models\ZoneModel;
use App\Models\AttendanceRecordModel;
use App\Models\ZoneAntennaModel;
use App\Models\AssetModel;

class LocationSelector extends BaseController
{
    public function index()
    {
        // If not logged in, redirect to login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $zoneModel = new ZoneModel();
        $attendanceModel = new AttendanceRecordModel();
        $antennaModel = new ZoneAntennaModel();
        $assetModel = new AssetModel();

        // Get all active zones with their images - SELECT ALL FIELDS
        $zones = $zoneModel->select('*')->where('status', 'active')->findAll();
        
        // Debug: Log what we got
        log_message('debug', 'Zones fetched: ' . print_r($zones, true));
        
        // Get additional data for each zone
        foreach ($zones as &$zone) {
            // Today's attendance count - use 'id' field for database primary key
            $zone['today_count'] = $attendanceModel->where('zone_id', $zone['zone_id'])
                                                  ->where('date', date('Y-m-d'))
                                                  ->countAllResults();
            
            // Last scan time
            $lastRecord = $attendanceModel->where('zone_id', $zone['zone_id'])
                                         ->orderBy('check_in_time', 'DESC')
                                         ->first();
            $zone['last_scan'] = $lastRecord ? $lastRecord['check_in_time'] : null;
            
            // RFID signal status (from antennas)
            $antennas = $antennaModel->where('zone_id', $zone['zone_id'])->findAll();
            $zone['signal_status'] = $this->calculateSignalStatus($antennas);
            $zone['signal_color'] = $this->getSignalColor($zone['signal_status']);
            
            // Asset tracking - get assets last seen in this zone
            $zone['assets'] = $assetModel
                ->select('assets.*, workers.first_name, workers.last_name, workers.worker_id as w_id')
                ->join('workers', 'workers.worker_id = assets.assigned_worker_id', 'left')
                ->where('assets.last_seen_zone', $zone['zone_id'])
                ->findAll();
            $zone['asset_count'] = count($zone['assets']);
            
            // Ensure location_image is set (even if null)
            if (!isset($zone['location_image'])) {
                $zone['location_image'] = null;
            }
        
            // Ensure icon is set
            if (!isset($zone['icon'])) {
                $zone['icon'] = 'location_on';
            }
            
            // Build image URL if image exists
            if (!empty($zone['location_image'])) {
                $zone['image_url'] = base_url('uploads/zones/' . $zone['location_image']);
                log_message('debug', 'Zone ' . $zone['zone_id'] . ' image URL: ' . $zone['image_url']);
            } else {
                $zone['image_url'] = null;
            }
        }

        // Filter out test zones
        $zones = array_filter($zones, function($zone) {
            return stripos($zone['zone_name'], 'test') === false;
        });

        $data = [
            'title' => 'Location Selector',
            'user' => $this->getLoggedInUser(),
            'zones' => array_values($zones), // Re-index array after filtering
        ];

        return view('location_selector/index', $data);
    }

    public function select()
    {
        // If not logged in, redirect to login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $zoneId = $this->request->getPost('zone_id');

        if (!$zoneId) {
            return redirect()->back()->with('error', 'Please select a location.');
        }

        // Verify zone exists - use 'id' for database lookup
        $zoneModel = new ZoneModel();
        $zone = $zoneModel->where('id', $zoneId)->first();

        if (!$zone) {
            return redirect()->back()->with('error', 'Invalid location selected.');
        }

        // Store in session with zone details
        session()->set([
            'selected_zone' => $zone['zone_id'], // Store zone_id (Z-1001, etc)
            'selected_zone_name' => $zone['zone_name'],
            'selected_zone_db_id' => $zoneId, // Store database ID
        ]);

        return redirect()->to('workers/monitoring')->with('success', 'Location "' . $zone['zone_name'] . '" selected successfully!');
    }

    private function calculateSignalStatus($antennas)
    {
        if (empty($antennas)) {
            return 'no_signal';
        }

        $activeCount = 0;
        foreach ($antennas as $antenna) {
            if (isset($antenna['status']) && $antenna['status'] === 'active') {
                $activeCount++;
            }
        }

        if ($activeCount === 0) {
            return 'no_signal';
        } elseif ($activeCount === count($antennas)) {
            return 'excellent';
        } else {
            return 'good';
        }
    }

    private function getSignalColor($status)
    {
        switch ($status) {
            case 'excellent':
                return '#10b981'; // green (excellent signal)
            case 'good':
                return '#f59e0b'; // orange (good signal)
            case 'no_signal':
            default:
                return '#ef4444'; // red (no signal)
        }
    }
}