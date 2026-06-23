<?php

namespace App\Models;

use CodeIgniter\Model;

class StateModel extends Model
{
    protected $table = 'states';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'country_id', 'is_active', 'created_at', 'updated_at'];
    protected $useTimestamps = false;
    protected $returnType = 'array';
    
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'country_id' => 'required|numeric'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'State name is required',
            'min_length' => 'State name must be at least 2 characters',
            'max_length' => 'State name cannot exceed 100 characters'
        ],
        'country_id' => [
            'required' => 'Country is required',
            'numeric' => 'Invalid country'
        ]
    ];
    
    public function getActiveStates()
    {
        return $this->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }
    
    public function getStatesByCountry($countryId)
    {
        return $this->where('country_id', $countryId)
                    ->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }
    
    public function getStatesWithCountry()
    {
        return $this->select('states.*, countries.name as country_name')
                    ->join('countries', 'countries.id = states.country_id')
                    ->orderBy('states.name', 'ASC')
                    ->findAll();
    }
}
