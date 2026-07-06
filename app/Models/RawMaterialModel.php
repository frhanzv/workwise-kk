<?php

namespace App\Models;

use CodeIgniter\Model;

class RawMaterialModel extends Model
{
    protected $table            = 'raw_materials';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'material_code',
        'material_name',
        'sap_code',
        'appearance',
        'chemical_formula',
        'ph_range',
        'assay_content',
        'specific_gravity',
        'shelf_life_months',
        'warehouse_location',
        'min_stock',
        'expiry_alert_days',
        'sample_test',
        'pre_sample_test',
        'k_test',
        'supplier_name',
        'manufacturer_name',
        'supplier_shelf_life_months',
        'category',
        'description',
        'suppliers',
        'storage_location',
        'expiry_date',
        'cost_price',
        'selling_price',
        'epc_no',
        'tag_mode',
        'qty_per_tag',
        'unit',
        'quantity_on_hand',
        'qr_code',
        'last_seen_zone',
        'last_seen_at',
        'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateMaterialCode(): string
    {
        $last = $this->orderBy('id', 'DESC')->first();
        if (!$last) {
            return 'RM-0001';
        }
        preg_match('/(\d+)$/', $last['material_code'], $matches);
        $next = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        return 'RM-' . str_pad($next, 4, '0', STR_PAD_LEFT);
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
        $builder = $this->where('material_code', $code);
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
        return $this->select('raw_materials.*, zones.zone_name')
                    ->join('zones', 'zones.zone_id = raw_materials.last_seen_zone', 'left')
                    ->findAll();
    }

    /**
     * @return list<string>
     */
    public static function decodeStorageLocations(?string $raw): array
    {
        return ProductModel::decodeStorageLocations($raw);
    }

    /**
     * When storage locations are configured, only those zones may be entered.
     * Empty list means no restriction (any zone allowed).
     */
    public static function isZoneAllowed(array $material, string $zoneId): bool
    {
        return ProductModel::isZoneAllowedForProduct($material, $zoneId);
    }
}
