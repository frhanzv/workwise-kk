<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'product_code',
        'product_name',
        'sap_code',
        'entry_date',
        'lot_number',
        'shelf_life_months',
        'analysis_date',
        'manufacturing_date',
        'expiry_date',
        'customer_name',
        'category',
        'description',
        'ph_level_target',
        'purity_grade',
        'density_20c',
        'viscosity',
        'pricing_start_date',
        'cost_price',
        'selling_price',
        'color_description',
        'qc_status',
        'qc_quantity',
        'nsf_certified',
        'halal_certified',
        'epc_no',
        'unit',
        'last_seen_zone',
        'last_seen_at',
        'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateProductCode(): string
    {
        $last = $this->orderBy('id', 'DESC')->first();
        if (!$last) {
            return 'PRD-0001';
        }
        preg_match('/(\d+)$/', $last['product_code'], $matches);
        $next = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        return 'PRD-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function getByEpc(string $epcNo): ?array
    {
        return $this->where('epc_no', $epcNo)->first();
    }

    public function isEpcRegistered(string $epcNo, ?int $excludeId = null): bool
    {
        $builder = $this->where('epc_no', $epcNo);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    public function isCodeTaken(string $code, ?int $excludeId = null): bool
    {
        $builder = $this->where('product_code', $code);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    public function updateLastSeen(int $id, string $zoneId): bool
    {
        return $this->update($id, [
            'last_seen_zone' => $zoneId,
            'last_seen_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function getWithZone(): array
    {
        return $this->select('products.*, zones.zone_name')
                    ->join('zones', 'zones.zone_id = products.last_seen_zone', 'left')
                    ->findAll();
    }
}
