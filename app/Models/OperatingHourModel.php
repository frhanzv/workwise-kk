<?php

namespace App\Models;

use CodeIgniter\Model;

class OperatingHourModel extends Model
{
    protected $table = 'operating_hours';
    protected $primaryKey = 'id';
    protected $allowedFields = ['day', 'start_time', 'end_time', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'day' => 'required|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
        'start_time' => 'required',
        'end_time' => 'required',
    ];
    
    protected $validationMessages = [
        'day' => [
            'required' => 'Day is required',
            'in_list' => 'Please select a valid day',
        ],
        'start_time' => [
            'required' => 'Start time is required',
        ],
        'end_time' => [
            'required' => 'End time is required',
        ],
    ];
}
