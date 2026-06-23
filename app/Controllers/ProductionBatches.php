<?php

namespace App\Controllers;

class ProductionBatches extends BaseController
{
    public function index()
    {
        return redirect()->to(base_url('production/list'));
    }

    public function list()
    {
        $model   = new \App\Models\ProductionBatchModel();
        $batches = $model->getWithStats();

        $open      = count(array_filter($batches, fn($b) => $b['status'] === 'open'));
        $completed = count(array_filter($batches, fn($b) => $b['status'] === 'completed'));
        $cancelled = count(array_filter($batches, fn($b) => $b['status'] === 'cancelled'));

        return view('production/list', [
            'title'        => 'Production Batches',
            'user'         => $this->getLoggedInUser(),
            'batches'      => $batches,
            'stats'        => [
                'total'     => count($batches),
                'open'      => $open,
                'completed' => $completed,
                'cancelled' => $cancelled,
            ],
            'isProduction' => true,
        ]);
    }

    public function add()
    {
        $model = new \App\Models\ProductionBatchModel();
        return view('production/add', [
            'title'        => 'New Production Batch',
            'user'         => $this->getLoggedInUser(),
            'batch_no'     => $model->generateBatchNo(),
            'isProduction' => true,
        ]);
    }

    public function store()
    {
        $model    = new \App\Models\ProductionBatchModel();
        $batchNo  = $model->generateBatchNo();

        $model->insert([
            'batch_no' => $batchNo,
            'notes'    => $this->request->getPost('notes') ?: null,
            'status'   => 'open',
        ]);

        $id = $model->getInsertID();
        return redirect()->to(base_url("production/view/{$id}"))
            ->with('success', "Batch {$batchNo} created. Add raw materials and products below.");
    }

    public function view($id)
    {
        $model = new \App\Models\ProductionBatchModel();
        $batch = $model->getWithDetails((int)$id);
        if (!$batch) return redirect()->to(base_url('production/list'));

        $rmModel      = new \App\Models\RawMaterialModel();
        $productModel = new \App\Models\ProductModel();

        $usedMaterialIds = array_column($batch['materials'], 'id');
        $usedProductIds  = array_column($batch['products'], 'id');

        // Available = active items not already in this batch
        $availableMaterials = array_values(array_filter(
            $rmModel->where('status', 'active')->orderBy('material_name')->findAll(),
            fn($m) => !in_array($m['id'], $usedMaterialIds)
        ));

        $availableProducts = array_values(array_filter(
            $productModel->where('status', 'active')->orderBy('product_name')->findAll(),
            fn($p) => !in_array($p['id'], $usedProductIds)
        ));

        return view('production/view', [
            'title'               => "Batch {$batch['batch_no']}",
            'user'                => $this->getLoggedInUser(),
            'batch'               => $batch,
            'available_materials' => $availableMaterials,
            'available_products'  => $availableProducts,
            'isProduction'        => true,
            'flash_success'       => session()->getFlashdata('success'),
            'flash_error'         => session()->getFlashdata('error'),
        ]);
    }

    public function addMaterial($batchId)
    {
        $model = new \App\Models\ProductionBatchModel();
        $batch = $model->find((int)$batchId);

        if (!$batch || $batch['status'] !== 'open') {
            return redirect()->back()->with('error', 'Batch is not open.');
        }

        $materialId = (int)$this->request->getPost('raw_material_id');
        if (!$materialId) return redirect()->back()->with('error', 'No material selected.');

        $db = \Config\Database::connect();

        if ($db->table('batch_raw_materials')
            ->where('batch_id', $batchId)->where('raw_material_id', $materialId)
            ->countAllResults()) {
            return redirect()->back()->with('error', 'That material is already in this batch.');
        }

        $db->table('batch_raw_materials')->insert([
            'batch_id'        => $batchId,
            'raw_material_id' => $materialId,
            'added_at'        => date('Y-m-d H:i:s'),
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        (new \App\Models\RawMaterialModel())->update($materialId, ['status' => 'consumed']);

        return redirect()->back()->with('success', 'Raw material added to batch.');
    }

    public function removeMaterial($batchId)
    {
        $model = new \App\Models\ProductionBatchModel();
        $batch = $model->find((int)$batchId);

        if (!$batch || $batch['status'] !== 'open') {
            return redirect()->back()->with('error', 'Batch is not open.');
        }

        $pivotId    = (int)$this->request->getPost('pivot_id');
        $materialId = (int)$this->request->getPost('raw_material_id');

        \Config\Database::connect()->table('batch_raw_materials')
            ->where('id', $pivotId)->where('batch_id', $batchId)->delete();

        (new \App\Models\RawMaterialModel())->update($materialId, ['status' => 'active']);

        return redirect()->back()->with('success', 'Raw material removed and restored to active.');
    }

    public function addProduct($batchId)
    {
        $model = new \App\Models\ProductionBatchModel();
        $batch = $model->find((int)$batchId);

        if (!$batch || $batch['status'] !== 'open') {
            return redirect()->back()->with('error', 'Batch is not open.');
        }

        $productId = (int)$this->request->getPost('product_id');
        if (!$productId) return redirect()->back()->with('error', 'No product selected.');

        $db = \Config\Database::connect();

        if ($db->table('batch_products')
            ->where('batch_id', $batchId)->where('product_id', $productId)
            ->countAllResults()) {
            return redirect()->back()->with('error', 'That product is already in this batch.');
        }

        $db->table('batch_products')->insert([
            'batch_id'   => $batchId,
            'product_id' => $productId,
            'added_at'   => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Product added to batch.');
    }

    public function removeProduct($batchId)
    {
        $model = new \App\Models\ProductionBatchModel();
        $batch = $model->find((int)$batchId);

        if (!$batch || $batch['status'] !== 'open') {
            return redirect()->back()->with('error', 'Batch is not open.');
        }

        $pivotId = (int)$this->request->getPost('pivot_id');

        \Config\Database::connect()->table('batch_products')
            ->where('id', $pivotId)->where('batch_id', $batchId)->delete();

        return redirect()->back()->with('success', 'Product removed from batch.');
    }

    public function complete($batchId)
    {
        (new \App\Models\ProductionBatchModel())->update((int)$batchId, ['status' => 'completed']);
        return redirect()->back()->with('success', 'Batch marked as completed.');
    }

    public function cancel($batchId)
    {
        $db = \Config\Database::connect();

        // Restore all consumed materials back to active
        $materials = $db->table('batch_raw_materials')
            ->where('batch_id', $batchId)->get()->getResultArray();

        $rmModel = new \App\Models\RawMaterialModel();
        foreach ($materials as $m) {
            $rmModel->update($m['raw_material_id'], ['status' => 'active']);
        }

        (new \App\Models\ProductionBatchModel())->update((int)$batchId, ['status' => 'cancelled']);

        return redirect()->to(base_url('production/list'))
            ->with('success', 'Batch cancelled. All raw materials restored to active.');
    }
}
