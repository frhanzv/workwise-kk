<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetModel extends Model
{
    protected $table            = 'assets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'asset_name',
        'epc_no',
        'description',
        'assigned_worker_id',
        'assigned_at',
        'last_seen_zone',
        'last_seen_at',
        'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAssetByEpc(string $epcNo): ?array
    {
        return $this->where('epc_no', $epcNo)->first();
    }

    public function getAssetsByWorker(string $workerId): array
    {
        return $this->where('assigned_worker_id', $workerId)
                    ->where('status', 'assigned')
                    ->findAll();
    }

    public function getAssetsInZone(string $zoneId): array
    {
        return $this->where('last_seen_zone', $zoneId)->findAll();
    }

    public function assignToWorker(int $assetId, string $workerId): bool
    {
        return $this->update($assetId, [
            'assigned_worker_id' => $workerId,
            'assigned_at'        => date('Y-m-d H:i:s'),
            'status'             => 'assigned',
        ]);
    }

    public function unassignFromWorker(int $assetId): bool
    {
        return $this->update($assetId, [
            'assigned_worker_id' => null,
            'assigned_at'        => null,
            'status'             => 'available',
        ]);
    }

    public function updateLastSeen(int $assetId, string $zoneId): bool
    {
        return $this->update($assetId, [
            'last_seen_zone' => $zoneId,
            'last_seen_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function isEpcRegistered(string $epcNo, ?int $excludeId = null): bool
    {
        $builder = $this->where('epc_no', $epcNo);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    public function getAssetsWithWorkerInfo(): array
    {
        return $this->select('assets.*, workers.first_name, workers.last_name, workers.worker_id as w_id')
                    ->join('workers', 'workers.worker_id = assets.assigned_worker_id', 'left')
                    ->findAll();
    }
}
