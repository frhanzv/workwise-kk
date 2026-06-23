<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * RFID Reader Configuration
 * 
 * Configuration for Yanzeo SA810 UHF RFID Reader
 */
class RFIDReader extends BaseConfig
{
    /**
     * Reader Connection Settings
     */
    
    // Reader IP address (for network-connected readers)
    public string $readerIP = '192.168.100.115';
    
    // Reader TCP port (default for SA810 is usually 6000 or 8080)
    public int $readerPort = 49152;
    
    // Connection timeout in seconds
    public int $connectionTimeout = 5;
    
    // Reader identifier (useful if you have multiple readers)
    public string $readerID = 'SA810_001';
    
    /**
     * Protocol Settings
     * 
     * Supported protocols:
     * - 'wiegand': Wiegand protocol (26-bit, 34-bit, etc.)
     * - 'json': JSON formatted data
     * - 'hex': Hexadecimal EPC format
     * - 'default': Auto-detect format
     */
    public string $protocol = 'hex';
    
    /**
     * Zone Mapping
     * 
     * If you have multiple readers at different zones,
     * you can map reader IDs to zone IDs
     * 
     * Example:
     * [
     *     'SA810_001' => 1,  // Main entrance
     *     'SA810_002' => 2,  // Warehouse
     *     'SA810_003' => 3,  // Office
     * ]
     */
    public array $readerZoneMapping = [
        'SA810_001' => 1,
    ];
    
    /**
     * Default zone ID if reader is not mapped
     */
    public int $defaultZoneID = 1;
    
    /**
     * Auto Check-out Settings
     * 
     * Enable automatic check-out after certain hours
     */
    public bool $autoCheckout = true;
    
    // Hours after which to auto check-out (e.g., 12 hours)
    public int $autoCheckoutHours = 12;
    
    /**
     * Read Debounce
     * 
     * Minimum seconds between reads for the same tag
     * to prevent duplicate entries
     */
    public int $readDebounceSeconds = 5;
    
    /**
     * Tap Interval Settings
     * 
     * Control minimum time intervals between taps
     */
    
    // Minimum seconds between check-in and check-out (first tap to second tap)
    public int $checkInToCheckOutInterval = 60;
    
    // Minimum seconds between check-out and check-in for same zone (second tap to third tap)
    public int $checkOutToCheckInInterval = 60;
    
    /**
     * Logging Settings
     */
    public bool $logAllReads = true;
    
    public bool $logUnregisteredTags = true;
    
    /**
     * Notification Settings
     * 
     * Enable notifications for attendance events
     */
    public bool $enableNotifications = false;
    
    public array $notificationEmails = [
        // 'hr@company.com',
        // 'security@company.com',
    ];
    
    /**
     * Serial Port Settings (if using serial connection)
     * 
     * Only used if connecting via COM port instead of network
     */
    public ?string $serialPort = null; // e.g., 'COM3' or '/dev/ttyUSB0'
    
    public int $serialBaudRate = 115200;
    
    public int $serialDataBits = 8;
    
    public int $serialStopBits = 1;
    
    public string $serialParity = 'none'; // 'none', 'odd', 'even'
    
    /**
     * Reader Mode
     * 
     * - 'passive': Reader only responds to API calls
     * - 'active': Reader continuously scans and sends data
     * - 'triggered': Reader scans on external trigger
     */
    public string $readerMode = 'active';
    
    /**
     * Antenna Settings
     * 
     * SA810 typically has 4 antennas
     */
    public array $enabledAntennas = [1, 2, 3, 4];
    
    // RF power level (0-30 dBm, typically 20-26 for indoor use)
    public int $rfPower = 26;
    
    /**
     * Tag Filtering
     * 
     * Filter tags based on EPC prefix (useful if you have different tag types)
     */
    public bool $enableTagFiltering = false;
    
    public array $allowedTagPrefixes = [
        // 'E200',
        // 'E280',
    ];
    
    /**
     * Web Configuration URL
     * 
     * URL for the reader's web configuration interface (if available)
     */
    public ?string $webConfigURL = 'http://192.168.1.100';
}
