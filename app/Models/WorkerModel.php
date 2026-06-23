<?php

namespace App\Models;

use CodeIgniter\Model;

class WorkerModel extends Model
{
    protected $table            = 'workers';
    protected $primaryKey       = 'worker_id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'worker_id',
        'ic_number',
        'rfid_tag_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'country_id',
        'state_id',
        'city_id',
        'department',
        'position',
        'start_date',
        'shift',
        'status',
        'profile_photo',
        'documents',
        'assigned_zones',
        'last_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'worker_id'   => 'required|is_unique[workers.worker_id]|max_length[50]',
        'first_name'  => 'required|max_length[100]',
        'last_name'   => 'required|max_length[100]',
        'email'       => 'required|valid_email|is_unique[workers.email]',
        'department'  => 'required|max_length[100]',
        'position'    => 'required|max_length[100]',
        'start_date'  => 'required|valid_date',
        'shift'       => 'required|max_length[100]',
        'status'      => 'in_list[active,inactive,on_break,offline]',
    ];

    protected $validationMessages = [
        'worker_id' => [
            'is_unique' => 'This Worker ID is already registered.',
        ],
        'email' => [
            'is_unique' => 'This email is already registered.',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Get worker with full name
    public function getWorkerWithFullName($workerId)
    {
        $worker = $this->find($workerId);
        if ($worker) {
            $worker['full_name'] = $worker['first_name'] . ' ' . $worker['last_name'];
            $worker['initials'] = strtoupper(substr($worker['first_name'], 0, 1) . substr($worker['last_name'], 0, 1));
        }
        return $worker;
    }

    // Get all workers with computed fields
    public function getAllWorkersFormatted()
    {
        $workers = $this->orderBy('created_at', 'DESC')->findAll();
        foreach ($workers as &$worker) {
            $worker['full_name'] = $worker['first_name'] . ' ' . $worker['last_name'];
            $worker['initials'] = strtoupper(substr($worker['first_name'], 0, 1) . substr($worker['last_name'], 0, 1));
            
            // Decode assigned zones
            if (!empty($worker['assigned_zones'])) {
                $worker['zones_array'] = json_decode($worker['assigned_zones'], true) ?: [];
                $worker['total_zones'] = count($worker['zones_array']);
            } else {
                $worker['zones_array'] = [];
                $worker['total_zones'] = 0;
            }
        }
        return $workers;
    }
    
    /**
     * Get worker by RFID tag ID
     * 
     * @param string $rfidTagId
     * @return array|null
     */
    public function getWorkerByRfidTag(string $rfidTagId): ?array
    {
        $worker = $this->where('rfid_tag_id', $rfidTagId)->first();
        
        if ($worker) {
            $worker['full_name'] = $worker['first_name'] . ' ' . $worker['last_name'];
            $worker['initials'] = strtoupper(substr($worker['first_name'], 0, 1) . substr($worker['last_name'], 0, 1));
        }
        
        return $worker;
    }
    
    /**
     * Check if RFID tag is already registered
     * 
     * @param string $rfidTagId
     * @param string|null $excludeWorkerId
     * @return bool
     */
    public function isRfidTagRegistered(string $rfidTagId, ?string $excludeWorkerId = null): bool
    {
        $builder = $this->where('rfid_tag_id', $rfidTagId);
        
        if ($excludeWorkerId) {
            $builder->where('worker_id !=', $excludeWorkerId);
        }
        
        return $builder->countAllResults() > 0;
    }
    
    /**
     * Get all workers with RFID tags
     * 
     * @return array
     */
    public function getWorkersWithRfidTags(): array
    {
        return $this->where('rfid_tag_id IS NOT NULL')
                    ->where('rfid_tag_id !=', '')
                    ->findAll();
    }
}

