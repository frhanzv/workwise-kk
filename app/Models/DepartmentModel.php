<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description', 'is_active', 'created_at', 'updated_at'];
    protected $useTimestamps = false;
    protected $returnType = 'array';
    
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]|is_unique[departments.name,id,{id}]',
        'description' => 'permit_empty|max_length[255]'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Department name is required',
            'min_length' => 'Department name must be at least 2 characters',
            'max_length' => 'Department name cannot exceed 100 characters',
            'is_unique' => 'Department name already exists'
        ]
    ];
    
    public function getActiveDepartments()
    {
        return $this->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }
}
