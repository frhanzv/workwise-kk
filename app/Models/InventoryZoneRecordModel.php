<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryZoneRecordModel extends Model
{
    protected $table            = 'inventory_zone_records';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'item_type',
        'item_id',
        'zone_id',
        'check_in_time',
        'check_out_time',
        'date',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActiveCheckIn(string $itemType, int $itemId, string $zoneId, string $date): ?array
    {
        return $this->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('zone_id', $zoneId)
            ->where('date', $date)
            ->where('check_out_time IS NULL')
            ->first();
    }

    public function getActiveCheckInAnyZone(string $itemType, int $itemId, string $date): ?array
    {
        return $this->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('date', $date)
            ->where('check_out_time IS NULL')
            ->first();
    }
}
