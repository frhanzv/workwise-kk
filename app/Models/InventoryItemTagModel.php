<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryItemTagModel extends Model
{
    protected $table            = 'inventory_item_tags';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'item_type',
        'item_id',
        'epc_no',
        'quantity',
        'default_quantity',
        'label',
        'status',
        'last_seen_zone',
        'last_seen_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByEpc(string $epcNo): ?array
    {
        return $this->where('epc_no', $epcNo)
            ->where('status', 'active')
            ->first();
    }

    public function isEpcRegistered(string $epcNo, ?int $excludeTagId = null): bool
    {
        $builder = $this->where('epc_no', $epcNo)
            ->where('status', 'active');
        if ($excludeTagId) {
            $builder->where('id !=', $excludeTagId);
        }

        return $builder->countAllResults() > 0;
    }

    public function findByEpc(string $epcNo): ?array
    {
        return $this->where('epc_no', $epcNo)->first();
    }

    public function getTagsForItem(string $itemType, int $itemId, bool $activeOnly = true): array
    {
        $builder = $this->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->orderBy('id', 'ASC');

        if ($activeOnly) {
            $builder->where('status', 'active');
        }

        return $builder->findAll();
    }

    public function countTagsForItem(string $itemType, int $itemId): int
    {
        return $this->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('status', 'active')
            ->countAllResults();
    }

    public function totalTagQuantity(string $itemType, int $itemId): float
    {
        $row = $this->selectSum('quantity', 'total')
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('status', 'active')
            ->first();

        return (float) ($row['total'] ?? 0);
    }

    public function updateLastSeen(int $tagId, string $zoneId): bool
    {
        return $this->update($tagId, [
            'last_seen_zone' => $zoneId,
            'last_seen_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}
