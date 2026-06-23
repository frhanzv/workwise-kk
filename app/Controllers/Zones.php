<?php

namespace App\Controllers;

class Zones extends BaseController
{
    /**
     * Determine icon and color based on zone name keywords
     */
    private function getIconFromZoneName($zoneName)
    {
        $zoneName = strtolower($zoneName);
        
        // Check for keywords and assign appropriate icon
        if (stripos($zoneName, 'main') !== false || stripos($zoneName, 'entrance') !== false || stripos($zoneName, 'gate') !== false) {
            return ['icon' => 'home_pin', 'color' => 'blue'];
        }
        
        if (stripos($zoneName, 'warehouse') !== false || stripos($zoneName, 'storage') !== false || stripos($zoneName, 'loading') !== false) {
            return ['icon' => 'warehouse', 'color' => 'amber'];
        }
        
        if (stripos($zoneName, 'server') !== false || stripos($zoneName, 'data') !== false || stripos($zoneName, 'network') !== false) {
            return ['icon' => 'dns', 'color' => 'red'];
        }
        
        if (stripos($zoneName, 'cafeteria') !== false || stripos($zoneName, 'restaurant') !== false || stripos($zoneName, 'dining') !== false || stripos($zoneName, 'kitchen') !== false) {
            return ['icon' => 'restaurant', 'color' => 'teal'];
        }
        
        if (stripos($zoneName, 'office') !== false || stripos($zoneName, 'desk') !== false || stripos($zoneName, 'work') !== false) {
            return ['icon' => 'business_center', 'color' => 'blue'];
        }
        
        if (stripos($zoneName, 'parking') !== false || stripos($zoneName, 'garage') !== false) {
            return ['icon' => 'local_parking', 'color' => 'indigo'];
        }
        
        if (stripos($zoneName, 'lobby') !== false || stripos($zoneName, 'reception') !== false) {
            return ['icon' => 'meeting_room', 'color' => 'purple'];
        }
        
        if (stripos($zoneName, 'lab') !== false || stripos($zoneName, 'research') !== false) {
            return ['icon' => 'science', 'color' => 'green'];
        }
        
        if (stripos($zoneName, 'security') !== false || stripos($zoneName, 'guard') !== false) {
            return ['icon' => 'shield', 'color' => 'red'];
        }
        
        if (stripos($zoneName, 'production') !== false || stripos($zoneName, 'manufacturing') !== false || stripos($zoneName, 'factory') !== false) {
            return ['icon' => 'precision_manufacturing', 'color' => 'orange'];
        }
        
        // Default icon
        return ['icon' => 'location_on', 'color' => 'blue'];
    }

    /**
     * Ensure upload directory exists
     */
    private function ensureUploadDirectory()
    {
        $uploadPath = WRITEPATH . '../public/uploads/zones';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Check if writable
        if (!is_writable($uploadPath)) {
            log_message('error', 'Upload directory is not writable: ' . $uploadPath);
            return false;
        }
        
        return true;
    }
    
    public function index()
    {
        $zoneModel = new \App\Models\ZoneModel();
        $antennaModeModel = new \App\Models\AntennaModeModel();
        
        // Fetch zones from database
        $zones = $zoneModel->where('status', 'active')->findAll();
        
        // Get all antenna modes for color mapping
        $antennaModes = $antennaModeModel->findAll();
        $modeColorMap = [];
        foreach ($antennaModes as $mode) {
            $modeColorMap[$mode['mode_name']] = $mode['color'] ?? 'purple';
        }
        
        // Transform database records to match view format
        $formattedZones = [];
        foreach ($zones as $zone) {
            // Get function based on all antennas
            $function = $zoneModel->getZoneFunction($zone['zone_id']);
            
            // Determine function color based on function type
            $functionColor = 'blue';
            $animated = false;
            if ($function == 'IN ONLY') {
                $functionColor = 'green';
                $animated = true;
            } elseif ($function == 'OUT ONLY') {
                $functionColor = 'red';
            }
            
            // Get antenna color from database or default to purple
            $antennaColor = $modeColorMap[$zone['antenna_mode']] ?? 'purple';
            
            // Get icon data based on zone name (auto-assign)
            $iconData = $this->getIconFromZoneName($zone['zone_name']);
            
            $formattedZones[] = [
                'id' => $zone['zone_id'],
                'location' => $zone['zone_name'] . ($zone['location'] ? ' - ' . $zone['location'] : ''),
                'icon' => $iconData['icon'],
                'icon_color' => $iconData['color'],
                'antenna_mode' => $zone['antenna_mode'],
                'antenna_color' => $antennaColor,
                'ip_address' => $zone['ip_address'],
                'function' => $function,
                'function_color' => $functionColor,
                'animated' => $animated
            ];
        }
        
        $data = [
            'title' => 'Zone List',
            'user' => $this->getLoggedInUser(),
            'zones' => $formattedZones,
            'antenna_modes' => $antennaModes
        ];

        return view('zones/index', $data);
    }

    public function add()
    {
        $antennaModeModel = new \App\Models\AntennaModeModel();
        
        $data = [
            'title' => 'Zone Definition',
            'user' => $this->getLoggedInUser(),
            'antennaModes' => $antennaModeModel->where('is_active', 1)->orderBy('mode_name', 'ASC')->findAll()
        ];

        return view('zones/add', $data);
    }

    public function view($id)
    {
        $zoneModel = new \App\Models\ZoneModel();
        $zoneAntennaModel = new \App\Models\ZoneAntennaModel();
        $antennaModeModel = new \App\Models\AntennaModeModel();
        
        $zone = $zoneModel->where('zone_id', $id)->first();
        
        if (!$zone) {
            return redirect()->to(base_url('zones'))->with('error', 'Zone not found');
        }
        
        // Get all antennas for this zone
        $antennas = $zoneAntennaModel->getZoneAntennas($id);
        
        // If no antennas exist, create default from zone data (migration compatibility)
        if (empty($antennas)) {
            $antennas = [[
                'ip_address' => $zone['ip_address'],
                'port' => $zone['port'],
                'antenna_mode' => $zone['antenna_mode'],
                'function' => $zone['function'],
                'power_level' => $zone['power_level'],
                'antenna_name' => 'Antenna 1'
            ]];
        }
        
        // Get all antenna modes for color mapping
        $antennaModes = $antennaModeModel->findAll();
        $modeColorMap = [];
        foreach ($antennaModes as $mode) {
            $modeColorMap[$mode['mode_name']] = $mode['color'] ?? 'purple';
        }
        
        // Get function based on all antennas
        $function = $zoneModel->getZoneFunction($id);
        
        // Determine function color
        $functionColor = 'blue';
        $animated = false;
        if ($function == 'IN ONLY') {
            $functionColor = 'green';
            $animated = true;
        } elseif ($function == 'OUT ONLY') {
            $functionColor = 'red';
        }
        
        // Get icon data
        $iconData = $this->getIconFromZoneName($zone['zone_name']);
        $antennaColor = $modeColorMap[$zone['antenna_mode']] ?? 'purple';
        
        $formattedZone = array_merge($zone, [
            'icon' => $iconData['icon'],
            'icon_color' => $iconData['color'],
            'antenna_color' => $antennaColor,
            'function' => $function,
            'function_color' => $functionColor,
            'animated' => $animated
        ]);
        
        $data = [
            'title' => 'View Zone',
            'user' => $this->getLoggedInUser(),
            'zone' => $formattedZone,
            'antennas' => $antennas,
            'antennaModes' => $antennaModes,
            'modeColorMap' => $modeColorMap
        ];

        return view('zones/view', $data);
    }
    
    /**
     * Test RFID reader connection for a zone
     */
    public function testConnection($zoneId)
    {
        $zoneModel = new \App\Models\ZoneModel();
        $zone = $zoneModel->where('zone_id', $zoneId)->first();
        
        if (!$zone) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Zone not found'
            ]);
        }
        
        $ip = $zone['ip_address'];
        $port = $zone['port'] ?: 49152;
        
        if (empty($ip)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No IP address configured for this zone'
            ]);
        }
        
        try {
            $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            
            if ($socket === false) {
                throw new \Exception('Failed to create socket: ' . socket_strerror(socket_last_error()));
            }
            
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
            
            $result = @socket_connect($socket, $ip, $port);
            
            if ($result === false) {
                $errorCode = socket_last_error($socket);
                $errorMsg = socket_strerror($errorCode);
                socket_close($socket);
                
                // Provide helpful error messages
                $troubleshooting = [];
                if ($errorCode == 10061 || strpos($errorMsg, 'refused') !== false) {
                    $troubleshooting = [
                        'Device is reachable but not accepting connections',
                        'Check if TCP communication is enabled on reader',
                        'Verify the port number is correct (default: 49152)'
                    ];
                } elseif ($errorCode == 10060 || strpos($errorMsg, 'timed out') !== false) {
                    $troubleshooting = [
                        'Device is not responding',
                        'Check if reader is powered on',
                        'Verify IP address is correct',
                        'Ensure reader and server are on same network'
                    ];
                }
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Connection failed: {$errorMsg}",
                    'error_code' => $errorCode,
                    'troubleshooting' => $troubleshooting
                ]);
            }
            
            socket_close($socket);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => "Successfully connected to RFID reader at {$ip}:{$port}",
                'reader_ip' => $ip,
                'reader_port' => $port
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Test RFID reader connection for a specific antenna
     * Accepts IP and port via POST JSON
     */
    public function testAntennaConnection()
    {
        $request = $this->request->getJSON();
        
        if (!$request) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request format'
            ]);
        }
        
        $ip = $request->ip ?? '';
        $port = $request->port ?? 49152;
        
        if (empty($ip)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'IP address is required'
            ]);
        }
        
        // Validate IP format
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid IP address format'
            ]);
        }
        
        try {
            $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            
            if ($socket === false) {
                throw new \Exception('Failed to create socket: ' . socket_strerror(socket_last_error()));
            }
            
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
            
            $result = @socket_connect($socket, $ip, $port);
            
            if ($result === false) {
                $errorCode = socket_last_error($socket);
                $errorMsg = socket_strerror($errorCode);
                socket_close($socket);
                
                // Provide helpful error messages
                $troubleshooting = [];
                if ($errorCode == 10061 || strpos($errorMsg, 'refused') !== false) {
                    $troubleshooting = [
                        'Device is reachable but not accepting connections',
                        'Check if TCP communication is enabled on reader',
                        'Verify the port number is correct (default: 49152)'
                    ];
                } elseif ($errorCode == 10060 || strpos($errorMsg, 'timed out') !== false) {
                    $troubleshooting = [
                        'Device is not responding',
                        'Check if reader is powered on',
                        'Verify IP address is correct',
                        'Ensure reader and server are on same network'
                    ];
                }
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Connection failed: {$errorMsg}",
                    'error_code' => $errorCode,
                    'troubleshooting' => $troubleshooting
                ]);
            }
            
            socket_close($socket);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => "Successfully connected to RFID reader at {$ip}:{$port}",
                'reader_ip' => $ip,
                'reader_port' => $port
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function store()
{
    $zoneModel = new \App\Models\ZoneModel();
    $zoneAntennaModel = new \App\Models\ZoneAntennaModel();

    // Get antenna arrays
    $antennaIPs = $this->request->getPost('antenna_ip');
    $antennaPorts = $this->request->getPost('antenna_port');
    $antennaModes = $this->request->getPost('antenna_mode');
    $antennaFunctions = $this->request->getPost('antenna_function');
    
    // Validate we have at least one antenna
    if (!is_array($antennaIPs) || empty($antennaIPs)) {
        return redirect()->back()->withInput()->with('error', 'At least one antenna configuration is required.');
    }

    // Get zone name and automatically assign icon
    $zoneName = $this->request->getPost('zone_name');
    $iconData = $this->getIconFromZoneName($zoneName);
    
    // Determine overall zone function based on first antenna (for backward compatibility)
    $function = $antennaFunctions[0] ?? 'IN / OUT';
    
    // Get form data for main zone
    $zoneData = [
        'zone_id'      => $this->request->getPost('zone_id'),
        'zone_name'    => $zoneName,
        'location'     => $this->request->getPost('location'),
        'icon'         => $iconData['icon'],
        'icon_color'   => $iconData['color'],
        'antenna_mode' => $antennaModes[0] ?? '',
        'antenna_color'=> 'purple',
        'ip_address'   => $antennaIPs[0] ?? '',
        'port'         => $antennaPorts[0] ?? 49152,
        'power_level'  => 30,
        'function'     => $function,
        'status'       => 'active',
    ];

    // Ensure upload directory exists
    if (!$this->ensureUploadDirectory()) {
        return redirect()->back()->withInput()
            ->with('error', 'Upload directory is not accessible. Please contact administrator.');
    }

    // Handle uploaded photo BEFORE database insert
    $locationImage = $this->request->getFile('location_image');
    
    // Debug: Log file upload info
    log_message('debug', 'File upload check - Is Valid: ' . ($locationImage && $locationImage->isValid() ? 'YES' : 'NO'));
    log_message('debug', 'File upload check - Has Moved: ' . ($locationImage && $locationImage->hasMoved() ? 'YES' : 'NO'));
    
    if ($locationImage && $locationImage->isValid() && !$locationImage->hasMoved()) {
        $newName = $locationImage->getRandomName();
        log_message('debug', 'Generated filename: ' . $newName);
        
        try {
            $locationImage->move(WRITEPATH . '../public/uploads/zones', $newName);
            $zoneData['location_image'] = $newName;
            log_message('debug', 'Image uploaded successfully: ' . $newName);
        } catch (\Exception $e) {
            log_message('error', 'Failed to upload image: ' . $e->getMessage());
        }
    } else {
        log_message('debug', 'No valid image file uploaded');
    }

    // Debug: Log what will be inserted
    log_message('debug', 'Zone data to insert: ' . print_r($zoneData, true));

    // Start database transaction
    $db = \Config\Database::connect();
    $db->transStart();

    // Save main zone
    if (!$zoneModel->insert($zoneData)) {
        $db->transRollback();
        $errors = $zoneModel->errors();
        log_message('error', 'Zone insert failed: ' . print_r($errors, true));
        $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to create zone.';
        return redirect()->back()->withInput()->with('error', $errorMsg);
    }

    log_message('debug', 'Zone inserted successfully');

    // Get the inserted zone ID
    $zoneId = $this->request->getPost('zone_id');

    // Save all antennas
    $antennaCount = count($antennaIPs);
    for ($i = 0; $i < $antennaCount; $i++) {
        $antennaData = [
            'zone_id'      => $zoneId,
            'antenna_name' => "Antenna " . ($i + 1),
            'ip_address'   => $antennaIPs[$i],
            'port'         => $antennaPorts[$i] ?? 49152,
            'antenna_mode' => $antennaModes[$i] ?? '',
            'function'     => $antennaFunctions[$i] ?? 'IN / OUT',
            'power_level'  => 30,
            'status'       => 'active',
            'sort_order'   => $i + 1
        ];

        if (!$zoneAntennaModel->insert($antennaData)) {
            $db->transRollback();
            log_message('error', 'Antenna insert failed for antenna ' . ($i + 1));
            return redirect()->back()->withInput()->with('error', 'Failed to save antenna configuration.');
        }
    }

    // Complete transaction
    $db->transComplete();

    if ($db->transStatus() === false) {
        log_message('error', 'Transaction failed');
        return redirect()->back()->withInput()->with('error', 'Failed to create zone with antennas.');
    }

    log_message('debug', 'Zone created successfully with ' . $antennaCount . ' antennas');
    return redirect()->to(base_url('zones'))->with('success', 'Zone created successfully with ' . $antennaCount . ' antenna(s)!');
}
    
    public function edit($id)
    {
        $zoneModel = new \App\Models\ZoneModel();
        $zoneAntennaModel = new \App\Models\ZoneAntennaModel();
        $antennaModeModel = new \App\Models\AntennaModeModel();
        
        $zone = $zoneModel->where('zone_id', $id)->first();
        
        if (!$zone) {
            return redirect()->to(base_url('zones'))->with('error', 'Zone not found.');
        }
        
        // Get all antennas for this zone
        $antennas = $zoneAntennaModel->getZoneAntennas($id);
        
        // If no antennas exist, create default from zone data (migration compatibility)
        if (empty($antennas)) {
            $antennas = [[
                'ip_address' => $zone['ip_address'],
                'port' => $zone['port'],
                'antenna_mode' => $zone['antenna_mode'],
                'function' => $zone['function'],
                'power_level' => $zone['power_level']
            ]];
        }
        
        $data = [
            'title' => 'Edit Zone',
            'user' => $this->getLoggedInUser(),
            'zone' => $zone,
            'antennas' => $antennas,
            'antennaModes' => $antennaModeModel->where('is_active', 1)->orderBy('mode_name', 'ASC')->findAll()
        ];

        return view('zones/edit', $data);
    }
    
    public function update($id)
    {
        $zoneModel = new \App\Models\ZoneModel();
        $zoneAntennaModel = new \App\Models\ZoneAntennaModel();
        
        $zone = $zoneModel->where('zone_id', $id)->first();
        
        if (!$zone) {
            return redirect()->to(base_url('zones'))->with('error', 'Zone not found.');
        }
        
        // Get antenna arrays
        $antennaIPs = $this->request->getPost('antenna_ip');
        $antennaPorts = $this->request->getPost('antenna_port');
        $antennaModes = $this->request->getPost('antenna_mode');
        $antennaFunctions = $this->request->getPost('antenna_function');
        
        // Validate we have at least one antenna
        if (!is_array($antennaIPs) || empty($antennaIPs)) {
            return redirect()->back()->withInput()->with('error', 'At least one antenna configuration is required.');
        }

        // Get zone name and automatically assign icon
        $zoneName = $this->request->getPost('zone_name');
        $iconData = $this->getIconFromZoneName($zoneName);
        
        // Determine overall zone function based on first antenna (for backward compatibility)
        $function = $antennaFunctions[0] ?? 'IN / OUT';
        
        // Get form data for main zone
        $zoneData = [
            'zone_name'    => $zoneName,
            'location'     => $this->request->getPost('location'),
            'icon'         => $iconData['icon'],
            'icon_color'   => $iconData['color'],
            'antenna_mode' => $antennaModes[0] ?? '',
            'antenna_color'=> 'purple',
            'ip_address'   => $antennaIPs[0] ?? '',
            'port'         => $antennaPorts[0] ?? 49152,
            'power_level'  => 30,
            'function'     => $function,
        ];

        // Ensure upload directory exists
        if (!$this->ensureUploadDirectory()) {
            return redirect()->back()->withInput()
                ->with('error', 'Upload directory is not accessible. Please contact administrator.');
        }

        // Handle uploaded photo
        $locationImage = $this->request->getFile('location_image');
        if ($locationImage && $locationImage->isValid() && !$locationImage->hasMoved()) {
            // Delete old image if exists
            if (!empty($zone['location_image'])) {
                $oldImagePath = WRITEPATH . '../public/uploads/zones/' . $zone['location_image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            // Upload new image
            $newName = $locationImage->getRandomName(); 
            $locationImage->move(WRITEPATH . '../public/uploads/zones', $newName);
            $zoneData['location_image'] = $newName;
        } else {
            // Keep old image if no new upload
            if (isset($zone['location_image'])) {
                $zoneData['location_image'] = $zone['location_image'];
            }
        }

        // Start database transaction
        $db = \Config\Database::connect();
        $db->transStart();

        // Update main zone using the database 'id' field, not 'zone_id'
        if (!$zoneModel->update($zone['id'], $zoneData)) {
            $db->transRollback();
            $errors = $zoneModel->errors();
            $errorMsg = !empty($errors) ? implode('<br>', $errors) : 'Failed to update zone.';
            return redirect()->back()->withInput()->with('error', $errorMsg);
        }

        // Delete existing antennas
        $zoneAntennaModel->deleteZoneAntennas($id);

        // Save all antennas
        $antennaCount = count($antennaIPs);
        for ($i = 0; $i < $antennaCount; $i++) {
            $antennaData = [
                'zone_id'      => $id,
                'antenna_name' => "Antenna " . ($i + 1),
                'ip_address'   => $antennaIPs[$i],
                'port'         => $antennaPorts[$i] ?? 49152,
                'antenna_mode' => $antennaModes[$i] ?? '',
                'function'     => $antennaFunctions[$i] ?? 'IN / OUT',
                'power_level'  => 30,
                'status'       => 'active',
                'sort_order'   => $i + 1
            ];

            if (!$zoneAntennaModel->insert($antennaData)) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to save antenna configuration.');
            }
        }

        // Complete transaction
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Failed to update zone with antennas.');
        }

        return redirect()->to(base_url('zones'))->with('success', 'Zone updated successfully with ' . $antennaCount . ' antenna(s)!');
    }
    
    public function delete($id)
    {
        $zoneModel = new \App\Models\ZoneModel();
        
        $zone = $zoneModel->where('zone_id', $id)->first();
        
        if (!$zone) {
            return redirect()->to(base_url('zones'))->with('error', 'Zone not found.');
        }
        
        // Soft delete by setting status to inactive
        if ($zoneModel->update($zone['id'], ['status' => 'inactive'])) {
            return redirect()->to(base_url('zones'))->with('success', 'Zone deleted successfully!');
        } else {
            return redirect()->to(base_url('zones'))->with('error', 'Failed to delete zone.');
        }
    }
    
    public function updateAllIcons()
    {
        $zoneModel = new \App\Models\ZoneModel();
        
        // Get all active zones
        $zones = $zoneModel->where('status', 'active')->findAll();
        
        $updated = 0;
        foreach ($zones as $zone) {
            // Get icon data based on zone name
            $iconData = $this->getIconFromZoneName($zone['zone_name']);
            
            // Update the zone with new icon
            $zoneModel->update($zone['id'], [
                'icon' => $iconData['icon'],
                'icon_color' => $iconData['color']
            ]);
            $updated++;
        }
        
        return redirect()->to(base_url('zones'))->with('success', "Successfully updated icons for {$updated} zones!");
    }
}