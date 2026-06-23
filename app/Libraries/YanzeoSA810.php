<?php

namespace App\Libraries;

use Exception;

/**
 * Yanzeo SA810 UHF RFID Reader Library
 * 
 * This library provides an interface to communicate with the Yanzeo SA810 RFID reader.
 * The SA810 typically communicates via TCP/IP or Serial connection.
 * 
 * Communication Protocol:
 * - The SA810 can work in different modes: Wiegand, RS232, RS485, TCP/IP
 * - For network mode, it typically sends data via HTTP POST or TCP socket
 */
class YanzeoSA810
{
    protected $config;
    protected $socket;
    protected $isConnected = false;
    protected $lastError = '';
    
    public function __construct()
    {
        $this->config = config('RFIDReader');
    }
    
    /**
     * Connect to the RFID reader via TCP socket
     * 
     * @return bool
     */
    public function connect(): bool
    {
        if ($this->isConnected) {
            return true;
        }
        
        try {
            $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            
            if ($this->socket === false) {
                throw new Exception('Failed to create socket: ' . socket_strerror(socket_last_error()));
            }
            
            // Set connection timeout
            socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, [
                'sec' => $this->config->connectionTimeout,
                'usec' => 0
            ]);
            
            $result = @socket_connect($this->socket, $this->config->readerIP, $this->config->readerPort);
            
            if ($result === false) {
                $errorCode = socket_last_error($this->socket);
                $errorMsg = socket_strerror($errorCode);
                socket_close($this->socket);
                $this->socket = null;
                throw new Exception("Failed to connect to {$this->config->readerIP}:{$this->config->readerPort} - {$errorMsg} (Error: {$errorCode})");
            }
            
            $this->isConnected = true;
            log_message('info', 'Connected to Yanzeo SA810 reader at ' . $this->config->readerIP . ':' . $this->config->readerPort);
            
            return true;
        } catch (Exception $e) {
            log_message('error', 'Yanzeo SA810 connection error: ' . $e->getMessage());
            $this->lastError = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Disconnect from the RFID reader
     */
    public function disconnect(): void
    {
        if ($this->socket && $this->isConnected) {
            socket_close($this->socket);
            $this->isConnected = false;
            log_message('info', 'Disconnected from Yanzeo SA810 reader');
        }
    }
    
    /**
     * Send command to the reader
     * 
     * @param string $command
     * @return mixed
     */
    public function sendCommand(string $command)
    {
        if (!$this->isConnected) {
            if (!$this->connect()) {
                return false;
            }
        }
        
        try {
            socket_write($this->socket, $command, strlen($command));
            $response = socket_read($this->socket, 2048);
            
            return $response;
        } catch (Exception $e) {
            log_message('error', 'Yanzeo SA810 command error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Parse RFID tag data received from the reader
     * Different protocols may format data differently
     * 
     * @param mixed $rawData
     * @return array|null
     */
    public function parseTagData($rawData): ?array
    {
        if (empty($rawData)) {
            return null;
        }
        
        // Convert to string if needed
        $data = is_array($rawData) ? json_encode($rawData) : (string)$rawData;
        
        // Log the raw data for debugging
        log_message('debug', 'RFID raw data: ' . $data);
        
        // Parse based on configured protocol
        switch ($this->config->protocol) {
            case 'wiegand':
                return $this->parseWiegandData($data);
            
            case 'json':
                return $this->parseJsonData($data);
            
            case 'hex':
                return $this->parseHexData($data);
            
            default:
                // Default parsing - extract EPC (Electronic Product Code)
                return $this->parseDefaultData($data);
        }
    }
    
    /**
     * Parse Wiegand protocol data
     */
    protected function parseWiegandData(string $data): ?array
    {
        // Wiegand typically sends decimal or hex card numbers
        // Format varies (26-bit, 34-bit, etc.)
        
        // Remove any non-hex characters
        $cleaned = preg_replace('/[^0-9A-Fa-f]/', '', $data);
        
        if (empty($cleaned)) {
            return null;
        }
        
        return [
            'tag_id' => strtoupper($cleaned),
            'timestamp' => date('Y-m-d H:i:s'),
            'reader_id' => $this->config->readerID,
            'raw_data' => $data
        ];
    }
    
    /**
     * Parse JSON protocol data
     */
    protected function parseJsonData(string $data): ?array
    {
        $json = json_decode($data, true);
        
        if (!$json || !isset($json['tag_id'])) {
            return null;
        }
        
        return [
            'tag_id' => $json['tag_id'],
            'timestamp' => $json['timestamp'] ?? date('Y-m-d H:i:s'),
            'reader_id' => $json['reader_id'] ?? $this->config->readerID,
            'raw_data' => $data
        ];
    }
    
    /**
     * Parse hexadecimal EPC data
     */
    protected function parseHexData(string $data): ?array
    {
        // UHF tags typically use EPC (Electronic Product Code)
        // Format: 96-bit or 128-bit hex string
        
        // Extract hex string (remove spaces, dashes, etc.)
        $hex = preg_replace('/[^0-9A-Fa-f]/', '', $data);
        
        if (strlen($hex) < 8) {
            return null;
        }
        
        return [
            'tag_id' => strtoupper($hex),
            'timestamp' => date('Y-m-d H:i:s'),
            'reader_id' => $this->config->readerID,
            'raw_data' => $data
        ];
    }
    
    /**
     * Default parsing method
     */
    protected function parseDefaultData(string $data): ?array
    {
        // Try to extract any hex or alphanumeric identifier
        if (preg_match('/([0-9A-Fa-f]{8,})/i', $data, $matches)) {
            return [
                'tag_id' => strtoupper($matches[1]),
                'timestamp' => date('Y-m-d H:i:s'),
                'reader_id' => $this->config->readerID,
                'raw_data' => $data
            ];
        }
        
        // If no hex found, use the cleaned string
        $cleaned = preg_replace('/[^0-9A-Za-z]/', '', $data);
        
        if (strlen($cleaned) >= 4) {
            return [
                'tag_id' => strtoupper($cleaned),
                'timestamp' => date('Y-m-d H:i:s'),
                'reader_id' => $this->config->readerID,
                'raw_data' => $data
            ];
        }
        
        return null;
    }
    
    /**
     * Get reader status
     * 
     * @return array
     */
    public function getStatus(): array
    {
        return [
            'connected' => $this->isConnected,
            'reader_ip' => $this->config->readerIP,
            'reader_port' => $this->config->readerPort,
            'protocol' => $this->config->protocol,
            'last_error' => $this->lastError
        ];
    }
    
    /**
     * Get last error message
     * 
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }
    
    /**
     * Set reader to continuous reading mode
     */
    public function startContinuousRead(): bool
    {
        // SA810 specific command - adjust based on actual protocol
        // This is a placeholder - consult SA810 documentation for actual command
        $command = chr(0xBB) . chr(0x00) . chr(0x27) . chr(0x00) . chr(0x03) . chr(0x22) . chr(0x27) . chr(0x10) . chr(0x83) . chr(0x7E);
        
        return $this->sendCommand($command) !== false;
    }
    
    /**
     * Stop continuous reading mode
     */
    public function stopContinuousRead(): bool
    {
        // SA810 specific command - adjust based on actual protocol
        $command = chr(0xBB) . chr(0x00) . chr(0x28) . chr(0x00) . chr(0x00) . chr(0x28) . chr(0x7E);
        
        return $this->sendCommand($command) !== false;
    }
}
