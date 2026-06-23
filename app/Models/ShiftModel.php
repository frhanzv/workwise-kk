<?php

namespace App\Models;

use CodeIgniter\Model;

class ShiftModel extends Model
{
    protected $table = 'shifts';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'start_time', 'end_time', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'start_time' => 'required',
        'end_time' => 'required',
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Shift name is required',
            'min_length' => 'Shift name must be at least 2 characters',
        ],
        'start_time' => [
            'required' => 'Start time is required',
        ],
        'end_time' => [
            'required' => 'End time is required',
        ],
    ];
}
