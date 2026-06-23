<?php

namespace App\Models;

use CodeIgniter\Model;

class CountryModel extends Model
{
    protected $table = 'countries';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'code', 'is_active', 'created_at', 'updated_at'];
    protected $useTimestamps = false;
    protected $returnType = 'array';
    
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]|is_unique[countries.name,id,{id}]',
        'code' => 'required|min_length[2]|max_length[3]|is_unique[countries.code,id,{id}]'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Country name is required',
            'min_length' => 'Country name must be at least 2 characters',
            'max_length' => 'Country name cannot exceed 100 characters',
            'is_unique' => 'Country name already exists'
        ],
        'code' => [
            'required' => 'Country code is required',
            'is_unique' => 'Country code already exists'
        ]
    ];
    
    public function getActiveCountries()
    {
        return $this->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }
}
