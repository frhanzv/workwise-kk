<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table            = 'suppliers';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['name', 'description', 'sort_order', 'is_active'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $validationRules = [
        'id'   => 'permit_empty|is_natural_no_zero',
        'name' => 'required|min_length[1]|max_length[150]|is_unique[suppliers.name,id,{id}]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'  => 'Supplier name is required',
            'is_unique' => 'This supplier already exists',
        ],
    ];

    public function getActiveForSelect(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}
