<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitOfMeasureModel extends Model
{
    protected $table            = 'units_of_measure';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['code', 'label', 'description', 'sort_order', 'is_active'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $validationRules = [
        'id'    => 'permit_empty|is_natural_no_zero',
        'code'  => 'required|min_length[1]|max_length[20]|alpha_numeric_punct|is_unique[units_of_measure.code,id,{id}]',
        'label' => 'required|min_length[1]|max_length[50]',
    ];

    protected $validationMessages = [
        'code' => [
            'required'           => 'Unit code is required',
            'is_unique'          => 'This unit code already exists',
            'alpha_numeric_punct' => 'Unit code may only contain letters, numbers, and basic punctuation',
        ],
        'label' => [
            'required' => 'Unit label is required',
        ],
    ];

    public function getActiveForSelect(): array
    {
        $rows = $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('label', 'ASC')
            ->findAll();

        if ($rows !== []) {
            return $rows;
        }

        $defaults = config('Inventory')->defaultUnits;

        return array_map(static fn (array $unit): array => [
            'code'  => $unit['code'],
            'label' => $unit['label'],
        ], $defaults);
    }
}
