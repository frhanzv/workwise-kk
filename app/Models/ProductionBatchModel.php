<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionBatchModel extends Model
{
    protected $table            = 'production_batches';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['batch_no', 'notes', 'status'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateBatchNo(): string
    {
        $last = $this->orderBy('id', 'DESC')->first();
        if (!$last) return 'PB-0001';
        preg_match('/(\d+)$/', $last['batch_no'], $m);
        $next = (int)($m[1] ?? 0) + 1;
        return 'PB-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function getWithStats(): array
    {
        $batches = $this->orderBy('id', 'DESC')->findAll();
        $db = $this->db;
        foreach ($batches as &$batch) {
            $batch['materials_count'] = $db->table('batch_raw_materials')
                ->where('batch_id', $batch['id'])->countAllResults();
            $batch['products_count'] = $db->table('batch_products')
                ->where('batch_id', $batch['id'])->countAllResults();
        }
        return $batches;
    }

    public function getWithDetails(int $id): ?array
    {
        $batch = $this->find($id);
        if (!$batch) return null;

        $db = $this->db;

        $batch['materials'] = $db->table('batch_raw_materials brm')
            ->select('brm.id AS pivot_id, brm.added_at, rm.id, rm.material_code, rm.material_name, rm.category, rm.epc_no, rm.unit, rm.last_seen_zone')
            ->join('raw_materials rm', 'rm.id = brm.raw_material_id')
            ->where('brm.batch_id', $id)
            ->orderBy('brm.added_at', 'ASC')
            ->get()->getResultArray();

        $batch['products'] = $db->table('batch_products bp')
            ->select('bp.id AS pivot_id, bp.added_at, p.id, p.product_code, p.product_name, p.category, p.epc_no, p.unit, p.last_seen_zone')
            ->join('products p', 'p.id = bp.product_id')
            ->where('bp.batch_id', $id)
            ->orderBy('bp.added_at', 'ASC')
            ->get()->getResultArray();

        return $batch;
    }
}
