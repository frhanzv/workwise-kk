<?php

namespace App\Commands;

use App\Commands\Traits\RfidResultCliTrait;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ZoneModel;
use Exception;

/**
 * Multi-Reader RFID Listener Service
 * 
 * Listens to multiple RFID readers simultaneously (one per zone)
 * Automatically detects new zones and connects to their readers
 * 
 * Usage: php spark rfid:listen-all
 */
class RfidListenerMulti extends BaseCommand
{
    use RfidResultCliTrait;

    protected $group       = 'RFID';
    protected $name        = 'rfid:listen-all';
    protected $description = 'Start RFID listener for all zone readers';
    
    protected $readers = []; // [zone_id => ['socket' => resource, 'zone' => array, 'buffer' => string]]
    protected $isRunning = false;
    protected $zoneModel;
    protected $lastZoneCheck = 0;
    protected $zoneCheckInterval = 5; // Check for new zones/config changes every 5 seconds
    protected $lastTagId = [];
    protected $tagCooldown = 2; // seconds to prevent duplicate reads
    
    public function run(array $params)
    {
        $this->zoneModel = new ZoneModel();
        
        CLI::write('======================================', 'green');
        CLI::write('   Multi-Reader RFID Listener', 'green');
        CLI::write('======================================', 'green');
        CLI::newLine();
        
        CLI::write('Config check interval: ' . $this->zoneCheckInterval . ' seconds', 'cyan');
        CLI::write('Scanning for zone readers...', 'yellow');
        CLI::write('Press Ctrl+C to stop the service', 'cyan');
        CLI::newLine();
        
        // Register signal handlers for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'shutdown']);
            pcntl_signal(SIGINT, [$this, 'shutdown']);
        }
        
        $this->isRunning = true;
        
        // Main loop
        while ($this->isRunning) {
            try {
                // Check for new zones periodically
                $timeSinceCheck = time() - $this->lastZoneCheck;
                if ($timeSinceCheck > $this->zoneCheckInterval) {
                    $this->updateReaderConnections();
                    $this->lastZoneCheck = time();
                }
                
                // Listen to all connected readers
                $this->listenToAllReaders();
                
                // Allow signal handlers to run
                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }
                
                usleep(10000); // 10ms sleep to prevent CPU overload while maintaining fast response
                
            } catch (Exception $e) {
                CLI::error("Error: " . $e->getMessage());
                sleep(5);
            }
        }
        
        $this->cleanup();
    }
    
    /**
     * Update reader connections based on zones
     */
    protected function updateReaderConnections()
    {
        $startTime = microtime(true);
        $zoneAntennaModel = new \App\Models\ZoneAntennaModel();
        
        // Get all active zones
        $zones = $this->zoneModel
            ->where('status', 'active')
            ->findAll();
        
        // Track all antenna IDs that should be connected
        $activeAntennaIds = [];
        
        // Process each zone and its antennas
        foreach ($zones as $zone) {
            $zoneId = $zone['zone_id'];
            
            // Get all antennas for this zone
            $antennas = $zoneAntennaModel->getZoneAntennas($zoneId);
            
            // If no antennas found in zone_antennas table, use zone's main config (backward compatibility)
            if (empty($antennas) && !empty($zone['ip_address'])) {
                $antennas = [[
                    'id' => 'legacy_' . $zoneId,
                    'zone_id' => $zoneId,
                    'ip_address' => $zone['ip_address'],
                    'port' => $zone['port'],
                    'antenna_mode' => $zone['antenna_mode'],
                    'function' => $zone['function'],
                    'power_level' => $zone['power_level']
                ]];
            }
            
            // Connect to each antenna
            foreach ($antennas as $antenna) {
                $antennaId = $antenna['id'] ?? 'legacy_' . $zoneId;
                $activeAntennaIds[] = $antennaId;
                
                // Check if antenna already connected
                if (isset($this->readers[$antennaId])) {
                    $existing = $this->readers[$antennaId]['antenna'];
                    // Check if config changed (IP, port, or function)
                    $ipChanged = ($existing['ip_address'] ?? '') !== ($antenna['ip_address'] ?? '');
                    $portChanged = ($existing['port'] ?? 49152) !== ($antenna['port'] ?? 49152);
                    $functionChanged = ($existing['function'] ?? 'IN / OUT') !== ($antenna['function'] ?? 'IN / OUT');
                    
                    if ($ipChanged || $portChanged || $functionChanged) {
                        // Config changed, update antenna data in memory
                        $this->readers[$antennaId]['antenna'] = $antenna;
                        $this->readers[$antennaId]['zone'] = $zone;
                        
                        if ($functionChanged) {
                            CLI::write('[' . date('H:i:s') . '] Function changed for ' . ($antenna['antenna_name'] ?? 'Antenna ' . $antennaId) . ': ' . ($existing['function'] ?? 'N/A') . ' → ' . $antenna['function'], 'cyan');
                        }
                        
                        // Only reconnect socket if IP or port changed
                        if ($ipChanged || $portChanged) {
                            $this->disconnectReader($antennaId);
                            $this->connectReader($zone, $antenna);
                        }
                    }
                } else {
                    // New antenna, connect
                    $this->connectReader($zone, $antenna);
                }
            }
        }
        
        // Remove disconnected antennas
        $currentAntennaIds = array_keys($this->readers);
        $removedAntennas = array_diff($currentAntennaIds, $activeAntennaIds);
        foreach ($removedAntennas as $antennaId) {
            $this->disconnectReader($antennaId);
        }
        
        $elapsed = round((microtime(true) - $startTime) * 1000, 2);
        $intervalUsed = time() - $this->lastZoneCheck;
        CLI::write('[' . date('H:i:s') . '] Active antennas: ' . count($this->readers) . ' (checked ' . $intervalUsed . 's ago, took ' . $elapsed . 'ms)', 'dark_gray');
    }
    
    /**
     * Connect to a reader
     */
    protected function connectReader($zone, $antenna)
    {
        $antennaId = $antenna['id'] ?? 'legacy_' . $zone['zone_id'];
        $zoneId = $zone['zone_id'];
        $ip = $antenna['ip_address'];
        $port = $antenna['port'] ?: 49152;
        
        try {
            // Quick check if IP is reachable before attempting socket connection
            $pingTest = @fsockopen($ip, $port, $errno, $errstr, 0.3);
            if (!$pingTest) {
                // Skip connection attempt if not reachable (will retry in 5 seconds)
                return;
            }
            fclose($pingTest);
            
            $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            
            if ($socket === false) {
                throw new Exception('Failed to create socket: ' . socket_strerror(socket_last_error()));
            }
            
            // Set socket options
            socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
            socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1);
            
            $result = @socket_connect($socket, $ip, $port);
            
            if ($result === false) {
                $error = socket_last_error($socket);
                // EINPROGRESS is expected for non-blocking connects
                if ($error !== SOCKET_EINPROGRESS && $error !== SOCKET_EWOULDBLOCK && $error !== 10035) {
                    socket_close($socket);
                    throw new Exception("Failed to connect: " . socket_strerror($error));
                }
                
                // Wait for connection to complete
                $write = [$socket];
                $read = $except = null;
                if (@socket_select($read, $write, $except, 2) === false) {
                    socket_close($socket);
                    throw new Exception("Connection timeout");
                }
            }
            
            // Set to non-blocking after connection
            socket_set_nonblock($socket);
            
            $antennaName = $antenna['antenna_name'] ?? 'Antenna 1';
            $antennaFunction = $antenna['function'] ?? 'IN / OUT';
            
            $this->readers[$antennaId] = [
                'socket' => $socket,
                'zone' => $zone,
                'antenna' => $antenna,
                'buffer' => '',
                'last_activity' => time()
            ];
            
            CLI::write("✓ Connected to {$zone['zone_name']} - {$antennaName} ({$ip}:{$port}) [Function: {$antennaFunction}]", 'green');
            
        } catch (Exception $e) {
            $errAntennaName = $antenna['antenna_name'] ?? 'Antenna';
            CLI::error("✗ Failed to connect to {$zone['zone_name']} - {$errAntennaName} ({$ip}:{$port}): " . $e->getMessage());
        }
    }
    
    /**
     * Disconnect from a reader
     */
    protected function disconnectReader($antennaId)
    {
        if (isset($this->readers[$antennaId])) {
            $reader = $this->readers[$antennaId];
            if ($reader['socket']) {
                @socket_close($reader['socket']);
            }
            $antennaName = $reader['antenna']['antenna_name'] ?? 'Antenna';
            CLI::write("✗ Disconnected from {$reader['zone']['zone_name']} - {$antennaName}", 'yellow');
            unset($this->readers[$antennaId]);
        }
    }
    
    /**
     * Listen to all connected readers
     */
    protected function listenToAllReaders()
    {
        if (empty($this->readers)) {
            return;
        }
        
        // Prepare socket arrays for select
        $read = [];
        $socketToAntenna = [];
        
        foreach ($this->readers as $antennaId => $reader) {
            $read[] = $reader['socket'];
            $key = array_search($reader['socket'], $read, true);
            $socketToAntenna[$key] = $antennaId;
        }
        
        $write = null;
        $except = null;
        $timeout = 0;
        
        $changed = @socket_select($read, $write, $except, $timeout, 100000); // 100ms timeout
        
        if ($changed === false) {
            // Error in select
            return;
        }
        
        if ($changed === 0) {
            // No data available
            return;
        }
        
        // Read from sockets that have data
        foreach ($read as $key => $socket) {
            $antennaId = $socketToAntenna[$key];
            
            $chunk = @socket_read($socket, 1024, PHP_BINARY_READ);
            
            if ($chunk === false) {
                $error = socket_last_error($socket);
                if ($error !== SOCKET_EAGAIN && $error !== SOCKET_EWOULDBLOCK && $error !== 0) {
                    // Connection error, disconnect and immediately try to reconnect
                    $zone = $this->readers[$antennaId]['zone'];
                    $antenna = $this->readers[$antennaId]['antenna'];
                    $this->disconnectReader($antennaId);
                    // Immediate reconnection attempt
                    $this->connectReader($zone, $antenna);
                }
                continue;
            }
            
            if ($chunk === '') {
                // Connection closed, disconnect and immediately try to reconnect
                $zone = $this->readers[$antennaId]['zone'];
                $antenna = $this->readers[$antennaId]['antenna'];
                $this->disconnectReader($antennaId);
                // Immediate reconnection attempt
                $this->connectReader($zone, $antenna);
                continue;
            }
            
            // Append to buffer
            $this->readers[$antennaId]['buffer'] .= $chunk;
            $this->readers[$antennaId]['last_activity'] = time();
            
            // Process buffer
            if ($this->processBuffer($antennaId)) {
                $this->readers[$antennaId]['buffer'] = ''; // Clear buffer after processing
            }
        }
    }
    
    /**
     * Process buffer for a specific reader
     */
    protected function processBuffer($antennaId): bool
    {
        $buffer = $this->readers[$antennaId]['buffer'];
        $zone = $this->readers[$antennaId]['zone'];
        $antenna = $this->readers[$antennaId]['antenna'];
        
        // Parse tag ID
        $tagId = $this->parseTagId($buffer);
        
        if (!$tagId) {
            // Clear buffer if too large
            if (strlen($buffer) > 512) {
                return true;
            }
            return false;
        }
        
        return $this->processTagId($tagId, $zone, $antenna);
    }
    
    /**
     * Process a detected tag ID
     */
    protected function processTagId($tagId, $zone, $antenna): bool
    {
        // Prevent duplicate reads within cooldown period
        $currentTime = time();
        $key = $zone['zone_id'] . '_' . $tagId;
        
        if (isset($this->lastTagId[$key]) && 
            ($currentTime - $this->lastTagId[$key]) < $this->tagCooldown) {
            return true; // Ignore duplicate but clear buffer
        }
        
        $this->lastTagId[$key] = $currentTime;
        
        $antennaName = $antenna['antenna_name'] ?? 'Antenna';
        
        // Process attendance using antenna's function instead of zone's function
        $result = $this->processAttendance($tagId, $zone, $antenna);
        
        // Check if this is an asset tag
        if (isset($result['action']) && $result['action'] === 'asset_tracked') {
            $assetName = $result['asset']['name'] ?? 'Unknown Asset';
            $workerName = isset($result['worker']['name']) ? $result['worker']['name'] : 'Unassigned';
            CLI::write('[' . date('Y-m-d H:i:s') . '] Asset EPC: ' . $tagId . ' @ ' . $zone['zone_name'] . ' - ' . $antennaName, 'yellow');
            CLI::write("  ◆ ASSET: {$assetName} | Held by: {$workerName}", 'light_yellow');
            CLI::newLine();
            return true;
        }
        
        // Display tag with entity name if available
        $entity = $this->resolveRfidEntity($result);
        CLI::write('[' . date('Y-m-d H:i:s') . '] Tag: ' . $tagId . $entity['suffix'] . ' @ ' . $zone['zone_name'] . ' - ' . $antennaName, 'green');

        $this->writeRfidResultDetails($result, $entity);
        
        CLI::newLine();
        return true;
    }
    
    /**
     * Parse tag ID from raw data
     */
    protected function parseTagId($data): ?string
    {
        $hex = bin2hex($data);
        $hex = strtoupper($hex);
        
        // Method 1: Yanzeo SA810 specific protocol
        if (preg_match('/CCFFFF[0-9A-F]{8}[0-9A-F]{4}((?:DD|E2|30)[0-9A-F]{22})/', $hex, $matches)) {
            return $matches[1];
        }
        
        // Method 2: Look for DD/E2/30 prefixed tags
        if (preg_match('/(DD[0-9A-F]{22})/', $hex, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/(E2[0-9A-F]{22})/', $hex, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/(30[0-9A-F]{22})/', $hex, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Process attendance via local API
     */
    protected function processAttendance($tagId, $zone, $antenna): array
    {
        $ch = curl_init();
        
        // Determine function based on antenna configuration (not zone)
        $function = $antenna['function'] ?? 'IN / OUT';
        
        // Pass tag_id, zone_id, and function to the API
        $url = base_url("api/rfid/scan-zone?tag_id=" . urlencode($tagId) . 
                       "&zone_id=" . urlencode($zone['zone_id']) . 
                       "&function=" . urlencode($function));
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            return json_decode($response, true) ?: ['success' => false, 'message' => 'Invalid response'];
        }
        
        return [
            'success' => false,
            'message' => "HTTP Error: {$httpCode}"
        ];
    }
    
    /**
     * Shutdown handler
     */
    public function shutdown()
    {
        CLI::newLine();
        CLI::write('Shutting down multi-reader listener...', 'yellow');
        $this->isRunning = false;
    }
    
    /**
     * Cleanup resources
     */
    protected function cleanup()
    {
        foreach ($this->readers as $antennaId => $reader) {
            $this->disconnectReader($antennaId);
        }
        CLI::write('Service stopped.', 'green');
    }
}
