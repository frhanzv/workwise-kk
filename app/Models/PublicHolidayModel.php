<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicHolidayModel extends Model
{
    protected $table = 'public_holidays';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'holiday_date', 'type', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'holiday_date' => 'required|valid_date',
        'type' => 'required|in_list[Federal,State]',
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Holiday name is required',
            'min_length' => 'Holiday name must be at least 2 characters',
        ],
        'holiday_date' => [
            'required' => 'Holiday date is required',
            'valid_date' => 'Please enter a valid date',
        ],
        'type' => [
            'required' => 'Holiday type is required',
            'in_list' => 'Please select a valid holiday type',
        ],
    ];
}
