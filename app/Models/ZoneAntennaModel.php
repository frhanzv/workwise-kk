<?php

namespace App\Models;

use CodeIgniter\Model;

class ZoneAntennaModel extends Model
{
    protected $table            = 'zone_antennas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'zone_id',
        'antenna_name',
        'ip_address',
        'port',
        'antenna_mode',
        'function',
        'power_level',
        'status',
        'sort_order'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'zone_id'      => 'required',
        'ip_address'   => 'required|valid_ip',
        'port'         => 'required|integer|greater_than[0]|less_than[65536]',
        'antenna_mode' => 'required|max_length[50]',
        'power_level'  => 'required|integer|greater_than[0]|less_than_equal_to[100]',
    ];
    
    protected $validationMessages = [
        'ip_address' => [
            'valid_ip' => 'Please enter a valid IP address',
        ],
    ];
    
    protected $skipValidation = false;

    /**
     * Get all antennas for a specific zone
     */
    public function getZoneAntennas($zoneId)
    {
        return $this->where('zone_id', $zoneId)
                    ->where('status', 'active')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Delete all antennas for a zone
     */
    public function deleteZoneAntennas($zoneId)
    {
        return $this->where('zone_id', $zoneId)->delete();
    }
}
