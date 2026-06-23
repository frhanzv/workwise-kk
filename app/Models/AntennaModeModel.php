<?php

namespace App\Models;

use CodeIgniter\Model;

class AntennaModeModel extends Model
{
    protected $table = 'antenna_modes';
    protected $primaryKey = 'id';
    protected $allowedFields = ['mode_name', 'description', 'color', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'mode_name' => 'required|min_length[2]|max_length[100]|is_unique[antenna_modes.mode_name,id,{id}]',
    ];
    
    protected $validationMessages = [
        'mode_name' => [
            'required' => 'Antenna mode name is required',
            'is_unique' => 'This antenna mode already exists',
        ],
    ];
}
