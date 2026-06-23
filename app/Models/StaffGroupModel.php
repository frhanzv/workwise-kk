<?php

namespace App\Models;

use CodeIgniter\Model;

class StaffGroupModel extends Model
{
    protected $table = 'staff_groups';
    protected $primaryKey = 'id';
    protected $allowedFields = ['code', 'name', 'note', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'code' => 'required|min_length[2]|max_length[50]|is_unique[staff_groups.code,id,{id}]',
        'name' => 'required|min_length[2]|max_length[100]',
    ];
    
    protected $validationMessages = [
        'code' => [
            'required' => 'Code is required',
            'is_unique' => 'This code already exists',
        ],
        'name' => [
            'required' => 'Name is required',
            'min_length' => 'Name must be at least 2 characters',
        ],
    ];
}
