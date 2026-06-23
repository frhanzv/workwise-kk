<?php

namespace App\Models;

use CodeIgniter\Model;

class JobPositionModel extends Model
{
    protected $table = 'job_positions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'description', 'is_active', 'created_at', 'updated_at'];
    protected $useTimestamps = false;
    protected $returnType = 'array';
    
    protected $validationRules = [
        'title' => 'required|min_length[2]|max_length[100]|is_unique[job_positions.title,id,{id}]',
        'description' => 'permit_empty|max_length[255]'
    ];
    
    protected $validationMessages = [
        'title' => [
            'required' => 'Job position title is required',
            'min_length' => 'Job position title must be at least 2 characters',
            'max_length' => 'Job position title cannot exceed 100 characters',
            'is_unique' => 'Job position title already exists'
        ]
    ];
    
    public function getActivePositions()
    {
        return $this->where('is_active', 1)
                    ->orderBy('title', 'ASC')
                    ->findAll();
    }
}
