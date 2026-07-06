<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Inventory defaults — used to seed units of measure on first migration.
 * Manage the live list under Configuration → Units of Measure.
 */
class Inventory extends BaseConfig
{
    /**
     * Default units seeded when the units_of_measure table is created.
     *
     * @var list<array{code: string, label: string}>
     */
    public array $defaultUnits = [
        ['code' => 'pcs',   'label' => 'Pieces'],
        ['code' => 'kg',    'label' => 'Kilogram'],
        ['code' => 'g',     'label' => 'Gram'],
        ['code' => 'liter', 'label' => 'Liter'],
        ['code' => 'ml',    'label' => 'Milliliter'],
        ['code' => 'drum',  'label' => 'Drum'],
        ['code' => 'bag',   'label' => 'Bag'],
        ['code' => 'box',   'label' => 'Box'],
        ['code' => 'pail',  'label' => 'Pail'],
        ['code' => 'ton',   'label' => 'Metric Ton'],
    ];
}
