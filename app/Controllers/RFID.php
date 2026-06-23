<?php

namespace App\Controllers;

use App\Models\WorkerModel;
use App\Models\AttendanceRecordModel;
use App\Models\ZoneModel;
use App\Models\ShiftModel;
use App\Models\AssetModel;
use App\Models\ProductModel;
use App\Models\RawMaterialModel;
use App\Models\InventoryZoneRecordModel;
use App\Libraries\YanzeoSA810;
use CodeIgniter\RESTful\ResourceController;

/**
 * RFID Controller
 * 
 * Handles RFID tag reads from Yanzeo SA810 reader
 * and processes attendance records automatically
 */
class RFID extends ResourceController
{
    protected $modelName = 'App\Models\WorkerModel';
    protected $format = 'json';
    
    protected $workerModel;
    protected $attendanceModel;
    protected $zoneModel;
    protected $shiftModel;
    protected $rfidReader;
    protected $config;
    
    public function __construct()
    {
        $this->workerModel = new WorkerModel();
        $this->attendanceModel = new AttendanceRecordModel();
        $this->zoneModel = new ZoneModel();
        $this->shiftModel = new ShiftModel();
        $this->rfidReader = new YanzeoSA810();
        $this->config = config('RFIDReader');
    }
    
    /**
     * Webhook endpoint for RFID reader
     * 
     * The SA810 reader should be configured to send HTTP POST requests
     * to this endpoint when a tag is read
     * 
     * Expected data format (adjust based on your reader configuration):
     * {
     *   "tag_id": "E2003412EF1234567890ABCD",
     *   "reader_id": "SA810_001",
     *   "timestamp": "2025-12-23 10:30:45",
     *   "antenna": 1
     * }
     */
    public function tagRead()
    {
        // Allow requests from the RFID reader
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        
        // Get the raw POST data
        $rawData = $this->request->getBody();
        
        // Try to parse as JSON first
        $data = json_decode($rawData, true);
        
        // If not JSON, try to parse with the library
        if (!$data) {
            $data = $this->rfidReader->parseTagData($rawData);
        }
        
        // Log the received data
        log_message('info', 'RFID tag read: ' . json_encode($data));
        
        if (!$data || !isset($data['tag_id'])) {
            log_message('error', 'Invalid RFID data received: ' . $rawData);
            return $this->fail('Invalid RFID data', 400);
        }
        
        // Process the attendance
        $result = $this->processAttendance($data);
        
        return $this->respond($result);
    }
    
    /**
     * Alternative endpoint for GET requests (for testing)
     * 
     * Usage: /api/rfid/scan?tag_id=ABC123
     */
    public function scan()
    {
        $tagId = $this->request->getGet('tag_id');
        
        if (!$tagId) {
            return $this->fail('tag_id parameter is required', 400);
        }
        
        $data = [
            'tag_id' => $tagId,
            'timestamp' => date('Y-m-d H:i:s'),
            'reader_id' => 'manual'
        ];
        
        $result = $this->processAttendance($data);
        
        return $this->respond($result);
    }
    
    /**
     * Scan endpoint with zone specification (for multi-reader setup)
     * 
     * Usage: /api/rfid/scan-zone?tag_id=ABC123&zone_id=Z-1001&function=IN
     */
    public function scanZone()
    {
        $tagId = $this->request->getGet('tag_id');
        $zoneId = $this->request->getGet('zone_id');
        $antennaFunction = $this->request->getGet('function'); // Get antenna function (IN, OUT, or IN / OUT)
        
        if (!$tagId || !$zoneId) {
            return $this->fail('tag_id and zone_id parameters are required', 400);
        }
        
        // Find the zone
        $zone = $this->zoneModel->where('zone_id', $zoneId)->first();
        
        if (!$zone) {
            return $this->fail('Zone not found', 404);
        }
        
        $data = [
            'tag_id' => $tagId,
            'timestamp' => date('Y-m-d H:i:s'),
            'reader_id' => $zoneId, // Use zone_id as reader identifier
            'zone_id' => $zoneId,
            'antenna_function' => $antennaFunction // Pass antenna function
        ];
        
        $result = $this->processAttendance($data, $zone);
        
        return $this->respond($result);
    }
    
    /**
     * Validate if current time is within worker's assigned shift(s)
     * 
     * @param array $worker Worker data
     * @param string $timestamp Current timestamp
     * @return array ['allowed' => bool, 'message' => string, 'assigned_shifts' => array]
     */
    protected function validateWorkerShift(array $worker, string $timestamp): array
    {
        $currentTime = date('H:i:s', strtotime($timestamp));
        
        // Get worker's shift(s) - can be comma-separated for double shifts
        $workerShifts = $worker['shift'] ?? '';
        
        if (empty($workerShifts)) {
            return [
                'allowed' => false,
                'message' => 'Worker has no assigned shift',
                'assigned_shifts' => []
            ];
        }
        
        // Split shifts (support for double shift: "morning,afternoon" or "shift1,shift2")
        $assignedShiftNames = array_map('trim', explode(',', $workerShifts));
        
        // Get shift details from database
        $validShifts = [];
        $allowedShiftNames = [];
        
        foreach ($assignedShiftNames as $shiftName) {
            $shiftData = $this->shiftModel
                ->where('name', $shiftName)
                ->where('is_active', 1)
                ->first();
            
            if ($shiftData) {
                $validShifts[] = $shiftData;
                $allowedShiftNames[] = $shiftData['name'];
            }
        }
        
        if (empty($validShifts)) {
            return [
                'allowed' => false,
                'message' => 'Worker\'s assigned shift(s) are not found or inactive in the system',
                'assigned_shifts' => []
            ];
        }
        
        // Check if current time falls within any of the assigned shifts
        $isWithinShift = false;
        $matchedShift = null;
        
        foreach ($validShifts as $shift) {
            $shiftStart = $shift['start_time'];
            $shiftEnd = $shift['end_time'];
            
            // Handle shifts that cross midnight (e.g., night shift 22:00 - 06:00)
            if ($shiftEnd < $shiftStart) {
                // Shift crosses midnight
                if ($currentTime >= $shiftStart || $currentTime <= $shiftEnd) {
                    $isWithinShift = true;
                    $matchedShift = $shift;
                    break;
                }
            } else {
                // Normal shift within same day
                if ($currentTime >= $shiftStart && $currentTime <= $shiftEnd) {
                    $isWithinShift = true;
                    $matchedShift = $shift;
                    break;
                }
            }
        }
        
        if ($isWithinShift) {
            return [
                'allowed' => true,
                'message' => 'Access granted for ' . $matchedShift['name'] . ' shift',
                'assigned_shifts' => $validShifts,
                'current_shift' => $matchedShift
            ];
        }
        
        // Not within any assigned shift
        $shiftNames = implode(', ', $allowedShiftNames);
        return [
            'allowed' => false,
            'message' => 'Access denied: Current time is outside your assigned shift(s). You are assigned to: ' . $shiftNames,
            'assigned_shifts' => $validShifts,
            'current_shift' => null
        ];
    }
    
    /**
     * Process attendance based on RFID tag read
     * 
     * @param array $rfidData
     * @param array|null $zone Optional zone override
     * @return array
     */
    protected function processAttendance(array $rfidData, ?array $zone = null): array
    {
        $tagId = $rfidData['tag_id'];
        $timestamp = $rfidData['timestamp'] ?? date('Y-m-d H:i:s');
        $readerId = $rfidData['reader_id'] ?? 'unknown';
        $antennaFunction = $rfidData['antenna_function'] ?? 'IN / OUT'; // Get antenna function, default to both
        
        // Determine zone early for asset tracking
        $resolvedZone = $zone;
        if ($resolvedZone === null) {
            if ($readerId === 'manual' || $readerId === 'unknown') {
                $config = config('RFIDReader');
                $zoneId = $config->defaultZoneID ?? 1;
                $resolvedZone = $this->zoneModel->find($zoneId);
            } else {
                $resolvedZone = $this->zoneModel->where('zone_id', $readerId)->first();
                if (!$resolvedZone) {
                    $config = config('RFIDReader');
                    $zoneId = $config->defaultZoneID ?? 1;
                    $resolvedZone = $this->zoneModel->find($zoneId);
                }
            }
        }
        
        // Check if this EPC belongs to an asset
        $assetModel = new AssetModel();
        $asset = $assetModel->getAssetByEpc($tagId);
        
        if ($asset) {
            // This is an asset tag - update its last seen location
            $zoneIdForAsset = $resolvedZone ? $resolvedZone['zone_id'] : null;
            if ($zoneIdForAsset) {
                $assetModel->updateLastSeen((int)$asset['id'], $zoneIdForAsset);
            }
            
            log_message('info', "Asset detected: {$asset['asset_name']} (EPC: {$tagId}) at zone {$zoneIdForAsset}");
            
            $workerInfo = null;
            if (!empty($asset['assigned_worker_id'])) {
                $assignedWorker = $this->workerModel->where('worker_id', $asset['assigned_worker_id'])->first();
                if ($assignedWorker) {
                    $workerInfo = [
                        'id'   => $assignedWorker['worker_id'],
                        'name' => $assignedWorker['first_name'] . ' ' . $assignedWorker['last_name'],
                    ];
                }
            }
            
            return [
                'success'    => true,
                'message'    => 'Asset tracked: ' . $asset['asset_name'],
                'action'     => 'asset_tracked',
                'asset'      => [
                    'id'     => $asset['id'],
                    'name'   => $asset['asset_name'],
                    'epc'    => $asset['epc_no'],
                    'status' => $asset['status'],
                ],
                'worker'     => $workerInfo,
                'zone'       => $resolvedZone ? [
                    'id'   => $resolvedZone['zone_id'],
                    'name' => $resolvedZone['zone_name'],
                ] : null,
                'time'       => date('H:i:s', strtotime($timestamp)),
            ];
        }
        
        // Check if this EPC belongs to a product
        $productModel = new ProductModel();
        $product = $productModel->getByEpc($tagId);

        if ($product) {
            return $this->processInventoryZoneAttendance(
                'product',
                (int) $product['id'],
                $product['product_code'],
                $product['product_name'],
                $productModel,
                $resolvedZone,
                $timestamp,
                $antennaFunction,
                [
                    'id'   => $product['id'],
                    'code' => $product['product_code'],
                    'name' => $product['product_name'],
                ],
                'product'
            );
        }

        // Check if this EPC belongs to a raw material
        $rawMaterialModel = new RawMaterialModel();
        $rawMaterial = $rawMaterialModel->getByEpc($tagId);

        if ($rawMaterial) {
            return $this->processInventoryZoneAttendance(
                'raw_material',
                (int) $rawMaterial['id'],
                $rawMaterial['material_code'],
                $rawMaterial['material_name'],
                $rawMaterialModel,
                $resolvedZone,
                $timestamp,
                $antennaFunction,
                [
                    'id'   => $rawMaterial['id'],
                    'code' => $rawMaterial['material_code'],
                    'name' => $rawMaterial['material_name'],
                ],
                'raw_material'
            );
        }

        // Find worker by RFID tag
        $worker = $this->workerModel->where('rfid_tag_id', $tagId)->first();

        if (!$worker) {
            log_message('warning', "RFID tag not registered: {$tagId}");
            return [
                'success' => false,
                'message' => 'RFID tag not registered in system',
                'tag_id' => $tagId,
                'action' => 'none'
            ];
        }
        
        // Check if worker is inactive
        if ($worker['status'] === 'inactive') {
            log_message('warning', "Inactive worker attempted entry: {$worker['worker_id']} - {$worker['first_name']} {$worker['last_name']}");
            return [
                'success' => false,
                'message' => 'Access denied: Worker status is inactive',
                'worker' => [
                    'id' => $worker['worker_id'],
                    'name' => $worker['first_name'] . ' ' . $worker['last_name'],
                    'photo' => $worker['profile_photo']
                ],
                'action' => 'denied'
            ];
        }
        
        // Validate worker's shift - check if current time is within assigned shift(s)
        $shiftValidation = $this->validateWorkerShift($worker, $timestamp);
        
        if (!$shiftValidation['allowed']) {
            log_message('warning', "Worker {$worker['worker_id']} attempted access outside assigned shift(s). {$shiftValidation['message']}");
            
            $assignedShiftInfo = [];
            foreach ($shiftValidation['assigned_shifts'] as $shift) {
                $assignedShiftInfo[] = [
                    'name' => $shift['name'],
                    'start' => date('g:i A', strtotime($shift['start_time'])),
                    'end' => date('g:i A', strtotime($shift['end_time']))
                ];
            }
            
            return [
                'success' => false,
                'message' => $shiftValidation['message'],
                'worker' => [
                    'id' => $worker['worker_id'],
                    'name' => $worker['first_name'] . ' ' . $worker['last_name'],
                    'photo' => $worker['profile_photo']
                ],
                'assigned_shifts' => $assignedShiftInfo,
                'current_time' => date('g:i A', strtotime($timestamp)),
                'action' => 'shift_denied'
            ];
        }
        
        log_message('info', "Worker {$worker['worker_id']} shift validation passed: {$shiftValidation['message']}");
        
        // Use the zone already resolved at the top of processAttendance
        $zone = $resolvedZone;
        
        if (!$zone) {
            log_message('error', "No zone found for reader: {$readerId}");
            return [
                'success' => false,
                'message' => 'Zone configuration error',
                'worker' => $worker['first_name'] . ' ' . $worker['last_name']
            ];
        }
        
        $workerId = $worker['worker_id'];
        $zoneId = $zone['zone_id'];
        
        // Check if worker has access to this zone
        $assignedZones = [];
        if (!empty($worker['assigned_zones'])) {
            $assignedZones = json_decode($worker['assigned_zones'], true) ?: [];
        }
        
        if (!empty($assignedZones) && !in_array($zoneId, $assignedZones)) {
            log_message('warning', "Worker {$workerId} attempted to access unauthorized zone {$zoneId}");
            return [
                'success' => false,
                'message' => 'Access denied: No permission for this zone',
                'worker' => [
                    'id' => $workerId,
                    'name' => $worker['first_name'] . ' ' . $worker['last_name'],
                    'photo' => $worker['profile_photo']
                ],
                'zone' => [
                    'id' => $zoneId,
                    'name' => $zone['zone_name']
                ],
                'action' => 'denied'
            ];
        }
        
        $date = date('Y-m-d', strtotime($timestamp));
        $time = date('H:i:s', strtotime($timestamp));
        
        // Check if worker has an active check-in for this zone
        $activeCheckIn = $this->attendanceModel->getActiveCheckIn($workerId, $zoneId, $date);
        
        if ($activeCheckIn) {
            // Worker is already checked in - this would be a check-out attempt
            
            // Check if antenna function allows check-out
            if ($antennaFunction === 'IN') {
                log_message('info', "Check-out denied for worker {$workerId} at zone {$zoneId} - Antenna is IN-only");
                return [
                    'success' => false,
                    'message' => 'This antenna is for CHECK-IN only. Please use the OUT antenna to check out.',
                    'action' => 'denied',
                    'worker' => [
                        'id' => $workerId,
                        'name' => $worker['first_name'] . ' ' . $worker['last_name'],
                        'photo' => $worker['profile_photo']
                    ],
                    'zone' => [
                        'id' => $zoneId,
                        'name' => $zone['zone_name']
                    ]
                ];
            }
            
            // Worker is already checked in - check if enough time has passed for check-out
            $checkInTime = strtotime($activeCheckIn['check_in_time']);
            $currentTime = strtotime($timestamp);
            $timeDifference = $currentTime - $checkInTime; // in seconds
            
            // Get interval from config
            $requiredInterval = $this->config->checkInToCheckOutInterval;
            
            // If less than configured interval, ignore this tap
            if ($timeDifference < $requiredInterval) {
                log_message('info', "Duplicate tap ignored for worker {$workerId} at zone {$zoneId} (only {$timeDifference} seconds since check-in)");
                
                return [
                    'success' => false,
                    'message' => "Please wait at least {$requiredInterval} seconds before tapping out",
                    'action' => 'duplicate',
                    'worker' => [
                        'id' => $workerId,
                        'name' => $worker['first_name'] . ' ' . $worker['last_name'],
                        'photo' => $worker['profile_photo']
                    ],
                    'zone' => [
                        'id' => $zoneId,
                        'name' => $zone['zone_name']
                    ],
                    'time_since_checkin' => $timeDifference
                ];
            }
            
            // More than 1 minute has passed - this is a valid check-out
            $this->attendanceModel->update($activeCheckIn['id'], [
                'check_out_time' => $timestamp
            ]);
            
            // Update worker's last active time
            $this->workerModel->update($workerId, [
                'last_active' => $timestamp
            ]);
            
            log_message('info', "Check-out recorded for worker {$workerId} at zone {$zoneId}");
            
            return [
                'success' => true,
                'message' => 'Check-out recorded successfully',
                'action' => 'checkout',
                'worker' => [
                    'id' => $workerId,
                    'name' => $worker['first_name'] . ' ' . $worker['last_name'],
                    'photo' => $worker['profile_photo']
                ],
                'zone' => [
                    'id' => $zoneId,
                    'name' => $zone['zone_name']
                ],
                'time' => $time,
                'check_in_time' => date('H:i:s', strtotime($activeCheckIn['check_in_time'])),
                'duration' => $this->calculateDuration($activeCheckIn['check_in_time'], $timestamp)
            ];
        } else {
            // No active check-in - this would be a check-in attempt
            
            // Check if antenna function allows check-in
            if ($antennaFunction === 'OUT') {
                log_message('info', "Check-in denied for worker {$workerId} at zone {$zoneId} - Antenna is OUT-only");
                return [
                    'success' => false,
                    'message' => 'This antenna is for CHECK-OUT only. Please use the IN antenna to check in.',
                    'action' => 'denied',
                    'worker' => [
                        'id' => $workerId,
                        'name' => $worker['first_name'] . ' ' . $worker['last_name'],
                        'photo' => $worker['profile_photo']
                    ],
                    'zone' => [
                        'id' => $zoneId,
                        'name' => $zone['zone_name']
                    ]
                ];
            }
            
            // No active check-in - check if there was a recent check-out from this zone
            // Get the most recent completed record for this worker and zone
            $recentCheckOut = $this->attendanceModel
                ->where('worker_id', $workerId)
                ->where('zone_id', $zoneId)
                ->where('date', $date)
                ->where('check_out_time IS NOT NULL')
                ->orderBy('check_out_time', 'DESC')
                ->first();
            
            if ($recentCheckOut) {
                $lastCheckOutTime = strtotime($recentCheckOut['check_out_time']);
                $currentTime = strtotime($timestamp);
                $timeSinceCheckOut = $currentTime - $lastCheckOutTime; // in seconds
                
                // Get interval from config
                $requiredInterval = $this->config->checkOutToCheckInInterval;
                
                // If less than configured interval since last check-out, ignore this tap
                if ($timeSinceCheckOut < $requiredInterval) {
                    log_message('info', "Check-in too soon after check-out for worker {$workerId} at zone {$zoneId} (only {$timeSinceCheckOut} seconds since check-out)");
                    
                    return [
                        'success' => false,
                        'message' => "Please wait at least {$requiredInterval} seconds after check-out before checking in again",
                        'action' => 'duplicate',
                        'worker' => [
                            'id' => $workerId,
                            'name' => $worker['first_name'] . ' ' . $worker['last_name'],
                            'photo' => $worker['profile_photo']
                        ],
                        'zone' => [
                            'id' => $zoneId,
                            'name' => $zone['zone_name']
                        ],
                        'time_since_checkout' => $timeSinceCheckOut
                    ];
                }
            }
            
            // Valid check-in - insert new record
            $this->attendanceModel->insert([
                'worker_id' => $workerId,
                'zone_id' => $zoneId,
                'check_in_time' => $timestamp,
                'date' => $date
            ]);
            
            // Update worker's last active time and status
            $this->workerModel->update($workerId, [
                'last_active' => $timestamp,
                'status' => 'active'
            ]);
            
            log_message('info', "Check-in recorded for worker {$workerId} at zone {$zoneId}");
            
            return [
                'success' => true,
                'message' => 'Check-in recorded successfully',
                'action' => 'checkin',
                'worker' => [
                    'id' => $workerId,
                    'name' => $worker['first_name'] . ' ' . $worker['last_name'],
                    'photo' => $worker['profile_photo']
                ],
                'zone' => [
                    'id' => $zoneId,
                    'name' => $zone['zone_name']
                ],
                'time' => $time
            ];
        }
    }
    
    /**
     * Process zone presence for products and raw materials.
     *
     * Inventory items do not use explicit OUT taps. A scan means the item is
     * in that zone. If the item is seen in a different zone, the previous
     * zone is checked out automatically and duration stops counting.
     */
    protected function processInventoryZoneAttendance(
        string $itemType,
        int $itemId,
        string $itemCode,
        string $itemName,
        ProductModel|RawMaterialModel $itemModel,
        ?array $zone,
        string $timestamp,
        string $antennaFunction,
        array $itemPayload,
        string $responseKey
    ): array {
        if (!$zone) {
            return [
                'success' => false,
                'message' => 'Zone configuration error',
                'action'  => 'none',
            ];
        }

        $zoneId      = $zone['zone_id'];
        $date        = date('Y-m-d', strtotime($timestamp));
        $time        = date('H:i:s', strtotime($timestamp));
        $currentTs   = strtotime($timestamp);
        $recordModel = new InventoryZoneRecordModel();

        $activeRecord = $recordModel->getActiveCheckInAnyZone($itemType, $itemId, $date);
        $zonePayload  = ['id' => $zoneId, 'name' => $zone['zone_name']];
        $duplicateInterval = $this->config->checkInToCheckOutInterval;

        if ($activeRecord) {
            if ($activeRecord['zone_id'] === $zoneId) {
                $itemModel->updateLastSeen($itemId, $zoneId);

                $timeSinceCheckIn = $currentTs - strtotime($activeRecord['check_in_time']);
                if ($timeSinceCheckIn < $duplicateInterval) {
                    return [
                        'success'      => false,
                        'message'      => 'Item already in this zone',
                        'action'       => 'duplicate',
                        $responseKey   => $itemPayload,
                        'zone'         => $zonePayload,
                        'time'         => $time,
                        'check_in_time'=> date('H:i:s', strtotime($activeRecord['check_in_time'])),
                    ];
                }

                log_message('info', "Inventory still in zone: {$itemType} {$itemCode} at zone {$zoneId}");

                return [
                    'success'      => true,
                    'message'      => 'Still in zone: ' . $itemName,
                    'action'       => 'present',
                    $responseKey   => $itemPayload,
                    'zone'         => $zonePayload,
                    'time'         => $time,
                    'check_in_time'=> date('H:i:s', strtotime($activeRecord['check_in_time'])),
                ];
            }

            $previousZone = $this->zoneModel->where('zone_id', $activeRecord['zone_id'])->first();
            $previousZoneName = $previousZone['zone_name'] ?? $activeRecord['zone_id'];

            $recordModel->update($activeRecord['id'], [
                'check_out_time' => $timestamp,
            ]);

            $recordModel->insert([
                'item_type'     => $itemType,
                'item_id'       => $itemId,
                'zone_id'       => $zoneId,
                'check_in_time' => $timestamp,
                'date'          => $date,
            ]);

            $itemModel->updateLastSeen($itemId, $zoneId);

            log_message('info', "Inventory zone transfer: {$itemType} {$itemCode} from {$activeRecord['zone_id']} to {$zoneId}");

            return [
                'success'         => true,
                'message'         => 'Moved to ' . $zone['zone_name'] . ' (left ' . $previousZoneName . ')',
                'action'          => 'zone_transfer',
                $responseKey      => $itemPayload,
                'zone'            => $zonePayload,
                'previous_zone'   => [
                    'id'   => $activeRecord['zone_id'],
                    'name' => $previousZoneName,
                ],
                'time'            => $time,
                'check_in_time'   => date('H:i:s', strtotime($activeRecord['check_in_time'])),
                'check_out_time'  => $time,
                'duration'        => $this->calculateDuration($activeRecord['check_in_time'], $timestamp),
            ];
        }

        $recentCheckOut = $recordModel
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('zone_id', $zoneId)
            ->where('date', $date)
            ->where('check_out_time IS NOT NULL')
            ->orderBy('check_out_time', 'DESC')
            ->first();

        if ($recentCheckOut) {
            $timeSinceCheckOut = $currentTs - strtotime($recentCheckOut['check_out_time']);
            $requiredInterval  = $this->config->checkOutToCheckInInterval;

            if ($timeSinceCheckOut < $requiredInterval) {
                return [
                    'success'    => false,
                    'message'    => "Please wait at least {$requiredInterval} seconds before re-entering this zone",
                    'action'     => 'duplicate',
                    $responseKey => $itemPayload,
                    'zone'       => $zonePayload,
                ];
            }
        }

        $recordModel->insert([
            'item_type'     => $itemType,
            'item_id'       => $itemId,
            'zone_id'       => $zoneId,
            'check_in_time' => $timestamp,
            'date'          => $date,
        ]);

        $itemModel->updateLastSeen($itemId, $zoneId);

        log_message('info', "Inventory check-in: {$itemType} {$itemCode} at zone {$zoneId}");

        return [
            'success'    => true,
            'message'    => 'Check-in recorded: ' . $itemName,
            'action'     => 'checkin',
            $responseKey => $itemPayload,
            'zone'       => $zonePayload,
            'time'       => $time,
        ];
    }

    /**
     * Calculate duration between two timestamps
     * 
     * @param string $startTime
     * @param string $endTime
     * @return string
     */
    protected function calculateDuration(string $startTime, string $endTime): string
    {
        $start = strtotime($startTime);
        $end = strtotime($endTime);
        $diff = $end - $start;
        
        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        
        return sprintf('%dh %dm', $hours, $minutes);
    }
    
    /**
     * Get reader status
     */
    public function status()
    {
        $status = $this->rfidReader->getStatus();
        
        return $this->respond($status);
    }
    
    /**
     * Test connection to reader
     */
    public function testConnection()
    {
        $config = config('RFIDReader');
        $connected = $this->rfidReader->connect();
        
        if ($connected) {
            $this->rfidReader->disconnect();
            
            return $this->respond([
                'success' => true,
                'message' => 'Successfully connected to RFID reader',
                'reader_ip' => $config->readerIP,
                'reader_port' => $config->readerPort
            ]);
        }
        
        $error = $this->rfidReader->getLastError();
        $troubleshooting = [];
        
        // Provide helpful troubleshooting tips
        if (strpos($error, 'Connection refused') !== false || strpos($error, '10061') !== false) {
            $troubleshooting = [
                'issue' => 'Connection refused - device is reachable but not accepting connections',
                'suggestions' => [
                    'The device might be using a different port (try 6000 or 8080 instead of ' . $config->readerPort . ')',
                    'Check if TCP communication is enabled in device settings',
                    'Verify the device is not already connected to another application'
                ]
            ];
        } elseif (strpos($error, 'No connection') !== false || strpos($error, '10060') !== false || strpos($error, 'timed out') !== false) {
            $troubleshooting = [
                'issue' => 'Connection timeout - cannot reach device',
                'suggestions' => [
                    'Check if device is powered on and connected to network',
                    'Verify the IP address ' . $config->readerIP . ' is correct',
                    'Ensure your computer and device are on the same network',
                    'Check if firewall is blocking the connection',
                    'Try pinging the device: ping ' . $config->readerIP
                ]
            ];
        } else {
            $troubleshooting = [
                'issue' => 'Unknown connection error',
                'suggestions' => [
                    'Check device IP and port configuration',
                    'Ensure sockets extension is enabled in PHP',
                    'Review the error details below'
                ]
            ];
        }
        
        return $this->fail('Failed to connect to RFID reader', 500, [
            'error' => $error,
            'config' => [
                'reader_ip' => $config->readerIP,
                'reader_port' => $config->readerPort,
                'protocol' => $config->protocol
            ],
            'troubleshooting' => $troubleshooting
        ]);
    }
    
    /**
     * Manual attendance recording (for testing or backup)
     * 
     * POST /api/rfid/manual
     * Body: {
     *   "worker_id": "W001",
     *   "zone_id": 1,
     *   "action": "in" or "out"
     * }
     */
    public function manual()
    {
        $data = $this->request->getJSON(true);
        
        if (!isset($data['worker_id']) || !isset($data['zone_id']) || !isset($data['action'])) {
            return $this->fail('Missing required fields: worker_id, zone_id, action', 400);
        }
        
        $workerId = $data['worker_id'];
        $zoneId = $data['zone_id'];
        $action = $data['action'];
        $timestamp = date('Y-m-d H:i:s');
        $date = date('Y-m-d');
        
        // Verify worker exists
        $worker = $this->workerModel->find($workerId);
        if (!$worker) {
            return $this->fail('Worker not found', 404);
        }
        
        // Verify zone exists - check both by id (numeric) and zone_id (string like Z-1001)
        if (is_numeric($zoneId)) {
            $zone = $this->zoneModel->find($zoneId);
        } else {
            $zone = $this->zoneModel->where('zone_id', $zoneId)->first();
        }
        
        if (!$zone) {
            return $this->fail('Zone not found', 404);
        }
        
        // Use the actual zone_id for attendance record
        $actualZoneId = $zone['zone_id'];
        
        if ($action === 'in') {
            // Check if already checked in
            $existing = $this->attendanceModel->getActiveCheckIn($workerId, $actualZoneId, $date);
            if ($existing) {
                return $this->fail('Worker is already checked in to this zone', 400);
            }
            
            $this->attendanceModel->insert([
                'worker_id' => $workerId,
                'zone_id' => $actualZoneId,
                'check_in_time' => $timestamp,
                'date' => $date
            ]);
            
            // Update worker status
            $this->workerModel->update($workerId, [
                'last_active' => $timestamp,
                'status' => 'active'
            ]);
            
            return $this->respond([
                'success' => true,
                'message' => 'Check-in recorded successfully',
                'action' => 'checkin',
                'worker' => [
                    'id' => $workerId,
                    'name' => $worker['first_name'] . ' ' . $worker['last_name'],
                    'photo' => $worker['profile_photo']
                ],
                'zone' => [
                    'id' => $actualZoneId,
                    'name' => $zone['zone_name']
                ],
                'time' => date('H:i:s', strtotime($timestamp))
            ]);
        } elseif ($action === 'out') {
            $existing = $this->attendanceModel->getActiveCheckIn($workerId, $actualZoneId, $date);
            if (!$existing) {
                return $this->fail('No active check-in found', 400);
            }
            
            $this->attendanceModel->update($existing['id'], [
                'check_out_time' => $timestamp
            ]);
            
            // Update worker's last active time
            $this->workerModel->update($workerId, [
                'last_active' => $timestamp
            ]);
            
            return $this->respond([
                'success' => true,
                'message' => 'Check-out recorded successfully',
                'action' => 'checkout',
                'worker' => [
                    'id' => $workerId,
                    'name' => $worker['first_name'] . ' ' . $worker['last_name'],
                    'photo' => $worker['profile_photo']
                ],
                'zone' => [
                    'id' => $actualZoneId,
                    'name' => $zone['zone_name']
                ],
                'time' => date('H:i:s', strtotime($timestamp)),
                'check_in_time' => date('H:i:s', strtotime($existing['check_in_time'])),
                'duration' => $this->calculateDuration($existing['check_in_time'], $timestamp)
            ]);
        }
        
        return $this->fail('Invalid action. Use "in" or "out"', 400);
    }
}
