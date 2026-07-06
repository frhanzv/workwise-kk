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
        'suppliers',
        'storage_location',
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

    public const STORAGE_ALL_ZONES = '*';

    /**
     * Allowed storage zone IDs. Includes "*" when all zones are allowed.
     *
     * @return list<string>
     */
    public static function decodeStorageLocations(?string $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $ids = [];
            foreach ($decoded as $zoneId) {
                $zoneId = trim((string) $zoneId);
                if ($zoneId !== '') {
                    $ids[] = $zoneId;
                }
            }

            return array_values(array_unique($ids));
        }

        // Legacy single zone_id.
        return [trim($raw)];
    }

    public static function allowsAllZones(?string $raw): bool
    {
        return in_array(self::STORAGE_ALL_ZONES, self::decodeStorageLocations($raw), true);
    }

    /**
     * Label for master list / detail views.
     *
     * @param array<string, string> $zoneNames zone_id => zone_name
     */
    public static function storageLocationsLabel(?string $raw, array $zoneNames = []): string
    {
        $allowed = self::decodeStorageLocations($raw);
        if ($allowed === []) {
            return '—';
        }
        if (in_array(self::STORAGE_ALL_ZONES, $allowed, true)) {
            return 'All zones';
        }

        $names = [];
        foreach ($allowed as $zoneId) {
            $names[] = $zoneNames[$zoneId] ?? $zoneId;
        }

        return $names !== [] ? implode(', ', $names) : '—';
    }

    /**
     * Only listed zones may be entered. "*" = all zones. Empty = deny all.
     */
    public static function isZoneAllowedForProduct(array $product, string $zoneId): bool
    {
        $allowed = self::decodeStorageLocations($product['storage_location'] ?? null);
        if ($allowed === []) {
            return false;
        }
        if (in_array(self::STORAGE_ALL_ZONES, $allowed, true)) {
            return true;
        }

        return in_array((string) $zoneId, $allowed, true);
    }
}
