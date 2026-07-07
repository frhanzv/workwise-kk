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
        'tag_id',
        'zone_id',
        'check_in_time',
        'check_out_time',
        'date',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActiveCheckIn(string $itemType, int $itemId, string $zoneId): ?array
    {
        return $this->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('zone_id', $zoneId)
            ->where('check_out_time IS NULL')
            ->orderBy('check_in_time', 'DESC')
            ->first();
    }

    public function getActiveCheckInAnyZone(string $itemType, int $itemId): ?array
    {
        return $this->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('check_out_time IS NULL')
            ->where('tag_id IS NULL')
            ->orderBy('check_in_time', 'DESC')
            ->first();
    }

    public function getActiveCheckInForTag(int $tagId, string $zoneId): ?array
    {
        return $this->where('tag_id', $tagId)
            ->where('zone_id', $zoneId)
            ->where('check_out_time IS NULL')
            ->orderBy('check_in_time', 'DESC')
            ->first();
    }

    public function getActiveCheckInAnyZoneForTag(int $tagId): ?array
    {
        return $this->where('tag_id', $tagId)
            ->where('check_out_time IS NULL')
            ->orderBy('check_in_time', 'DESC')
            ->first();
    }

    /**
     * Close duplicate open sessions for the same tag (keep newest only).
     */
    public function consolidateActiveSessions(): void
    {
        $actives = $this->where('check_out_time IS NULL')
            ->orderBy('check_in_time', 'ASC')
            ->findAll();

        $latestIdByTagKey = [];
        foreach ($actives as $record) {
            $tagKey = $record['item_type'] . ':' . $record['item_id'] . ':' . ($record['tag_id'] ?? '0');
            if (isset($latestIdByTagKey[$tagKey])) {
                $this->update($latestIdByTagKey[$tagKey], ['check_out_time' => $record['check_in_time']]);
            }
            $latestIdByTagKey[$tagKey] = $record['id'];
        }

        // Drop legacy untagged open rows when the same item has a tagged session.
        $actives = $this->where('check_out_time IS NULL')
            ->findAll();

        $taggedItemKeys = [];
        foreach ($actives as $record) {
            if (!empty($record['tag_id'])) {
                $taggedItemKeys[$record['item_type'] . ':' . $record['item_id']] = true;
            }
        }

        foreach ($actives as $record) {
            if (!empty($record['tag_id'])) {
                continue;
            }
            $itemKey = $record['item_type'] . ':' . $record['item_id'];
            if (isset($taggedItemKeys[$itemKey])) {
                $this->update($record['id'], ['check_out_time' => $record['check_in_time']]);
            }
        }
    }

    /**
     * Close open sessions without tag_id (legacy scans) for an item.
     */
    public function consolidateActiveSessionsForDate(string $date): void
    {
        $this->consolidateActiveSessions();
    }

    public function closeUntaggedSessionsForItem(
        string $itemType,
        int $itemId,
        string $checkOutTime
    ): void {
        $open = $this->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('tag_id IS NULL')
            ->where('check_out_time IS NULL')
            ->findAll();

        foreach ($open as $record) {
            $this->update($record['id'], ['check_out_time' => $checkOutTime]);
        }
    }

    public function closeActiveSessionsForItem(
        string $itemType,
        int $itemId,
        string $checkOutTime
    ): void {
        $open = $this->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('check_out_time IS NULL')
            ->findAll();

        foreach ($open as $record) {
            $this->update($record['id'], ['check_out_time' => $checkOutTime]);
        }
    }
}
