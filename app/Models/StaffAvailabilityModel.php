<?php

namespace App\Models;

use CodeIgniter\Model;

class StaffAvailabilityModel extends Model
{
    protected $table = 'staff_availability';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description', 'status', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'description' => 'permit_empty|max_length[255]',
        'status' => 'required|in_list[ACTIVE,INACTIVE]',
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Name is required',
            'min_length' => 'Name must be at least 2 characters',
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be either ACTIVE or INACTIVE',
        ],
    ];
}
