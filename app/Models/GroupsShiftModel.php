<?php

namespace App\Models;

use CodeIgniter\Model;

class GroupsShiftModel extends Model
{
    protected $table = 'groups_shift';
    protected $primaryKey = 'id';
    protected $allowedFields = ['group', 'code', 'name', 'start_time', 'end_time', 'color', 'status', 'is_default', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'group' => 'required|min_length[2]|max_length[100]',
        'code' => 'required|min_length[2]|max_length[50]',
        'name' => 'required|min_length[2]|max_length[100]',
        'start_time' => 'permit_empty',
        'end_time' => 'permit_empty',
        'color' => 'permit_empty|max_length[50]',
        'status' => 'required|in_list[ACTIVE,INACTIVE]',
        'is_default' => 'permit_empty|in_list[YES,NO]',
    ];
    
    protected $validationMessages = [
        'group' => [
            'required' => 'Group is required',
            'min_length' => 'Group must be at least 2 characters',
        ],
        'code' => [
            'required' => 'Code is required',
            'min_length' => 'Code must be at least 2 characters',
        ],
        'name' => [
            'required' => 'Name is required',
            'min_length' => 'Name must be at least 2 characters',
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be either ACTIVE or INACTIVE',
        ],
        'is_default' => [
            'required' => 'Default field is required',
            'in_list' => 'Default must be either YES or NO',
        ],
    ];
}
