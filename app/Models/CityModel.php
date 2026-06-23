<?php

namespace App\Models;

use CodeIgniter\Model;

class CityModel extends Model
{
    protected $table = 'cities';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'state_id', 'country_id', 'is_active', 'created_at', 'updated_at'];
    protected $useTimestamps = false;
    protected $returnType = 'array';
    
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'state_id' => 'required|numeric',
        'country_id' => 'required|numeric'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'City name is required',
            'min_length' => 'City name must be at least 2 characters',
            'max_length' => 'City name cannot exceed 100 characters'
        ],
        'state_id' => [
            'required' => 'State is required',
            'numeric' => 'Invalid state'
        ],
        'country_id' => [
            'required' => 'Country is required',
            'numeric' => 'Invalid country'
        ]
    ];
    
    public function getActiveCities()
    {
        return $this->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }
    
    public function getCitiesByState($stateId)
    {
        return $this->where('state_id', $stateId)
                    ->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }
    
    public function getCitiesWithDetails()
    {
        return $this->select('cities.*, states.name as state_name, countries.name as country_name')
                    ->join('states', 'states.id = cities.state_id')
                    ->join('countries', 'countries.id = cities.country_id')
                    ->orderBy('cities.name', 'ASC')
                    ->findAll();
    }
}
