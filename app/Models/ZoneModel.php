<?php

namespace App\Models;

use CodeIgniter\Model;

class ZoneModel extends Model
{
    protected $table            = 'zones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'zone_id',
        'zone_name',
        'location',
        'icon',
        'icon_color',
        'antenna_mode',
        'antenna_color',
        'ip_address',
        'port',
        'power_level',
        'function',
        'status',
        'location_image',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'zone_id'      => 'required|min_length[3]|max_length[50]|is_unique[zones.zone_id,id,{id}]',
        'zone_name'    => 'required|min_length[3]|max_length[255]',
        'location'     => 'permit_empty|max_length[255]',
        'antenna_mode' => 'required|max_length[50]',
        'ip_address'   => 'required|valid_ip',
        'port'         => 'required|integer|greater_than[0]|less_than[65536]',
        'power_level'  => 'required|integer|greater_than[0]|less_than_equal_to[100]',
    ];
    
    protected $validationMessages = [
        'zone_id' => [
            'required'  => 'Zone ID is required',
            'is_unique' => 'This Zone ID already exists',
        ],
        'zone_name' => [
            'required' => 'Zone Name is required',
        ],
        'ip_address' => [
            'valid_ip' => 'Please enter a valid IP address',
        ],
    ];
    
    protected $skipValidation = false;
    
    /**
     * Get the zone function based on all antennas
     * If zone has multiple antennas, check all of them
     */
    public function getZoneFunction($zoneId)
    {
        $zoneAntennaModel = new \App\Models\ZoneAntennaModel();
        $antennas = $zoneAntennaModel->getZoneAntennas($zoneId);
        
        // If no antennas in zone_antennas table, fall back to zone table
        if (empty($antennas)) {
            $zone = $this->where('zone_id', $zoneId)->first();
            return $zone['function'] ?? 'IN / OUT';
        }
        
        // Check all antenna functions
        $hasIn = false;
        $hasOut = false;
        
        foreach ($antennas as $antenna) {
            $function = strtoupper(trim($antenna['function'] ?? 'IN / OUT'));
            
            // Check if function contains IN
            if (strpos($function, 'IN') !== false) {
                $hasIn = true;
            }
            // Check if function contains OUT
            if (strpos($function, 'OUT') !== false) {
                $hasOut = true;
            }
        }
        
        // Determine overall function
        if ($hasIn && $hasOut) {
            return 'IN / OUT';
        } elseif ($hasIn) {
            return 'IN ONLY';
        } elseif ($hasOut) {
            return 'OUT ONLY';
        }
        
        return 'IN / OUT'; // Default
    }
}
