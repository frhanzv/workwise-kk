<?php

namespace App\Controllers;

use App\Models\RawMaterialModel;
use App\Models\ZoneModel;

class RawMaterials extends BaseController
{
    public function index()
    {
        return redirect()->to(base_url('raw-materials/list'));
    }

    public function list()
    {
        $model     = new RawMaterialModel();
        $zoneModel = new ZoneModel();

        $materials = $model->getWithZone();
        $zones     = $zoneModel->where('status', 'active')->findAll();

        $totalActive   = 0;
        $totalInactive = 0;
        $taggedCount   = 0;

        foreach ($materials as $m) {
            if ($m['status'] === 'active') {
                $totalActive++;
            } else {
                $totalInactive++;
            }
            if (!empty($m['epc_no'])) {
                $taggedCount++;
            }
        }

        return view('raw_materials/list', [
            'title'     => 'Raw Materials',
            'user'      => $this->getLoggedInUser(),
            'materials' => $materials,
            'zones'     => $zones,
            'stats' => [
                'total'    => count($materials),
                'active'   => $totalActive,
                'inactive' => $totalInactive,
                'tagged'   => $taggedCount,
            ],
        ]);
    }

    public function add()
    {
        $model = new RawMaterialModel();

        return view('raw_materials/add', [
            'title'         => 'Add Raw Material',
            'user'          => $this->getLoggedInUser(),
            'material_code' => $model->generateMaterialCode(),
            'zones'         => (new ZoneModel())->where('status', 'active')->orderBy('zone_name')->findAll(),
        ]);
    }

    public function store()
    {
        $model = new RawMaterialModel();

        if (!$this->validate($this->materialRules())) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $materialCode = $this->request->getPost('material_code');
        if ($model->isCodeTaken($materialCode)) {
            return redirect()->back()->withInput()->with('error', 'Material code already exists.');
        }

        $epcNo = $this->request->getPost('epc_no') ?: null;
        if ($epcError = $this->validateEpc($epcNo)) {
            return redirect()->back()->withInput()->with('error', $epcError);
        }

        $model->insert($this->materialPayloadFromRequest($materialCode, $epcNo));

        return redirect()->to(base_url('raw-materials/list'))->with('success', 'Raw material added successfully.');
    }

    public function view($id)
    {
        $model     = new RawMaterialModel();
        $zoneModel = new ZoneModel();

        $material = $model->find($id);
        if (!$material) {
            return redirect()->to(base_url('raw-materials/list'))->with('error', 'Raw material not found.');
        }

        $lastZone = null;
        if (!empty($material['last_seen_zone'])) {
            $lastZone = $zoneModel->where('zone_id', $material['last_seen_zone'])->first();
        }

        $warehouseZone = null;
        if (!empty($material['warehouse_location'])) {
            $warehouseZone = $zoneModel->where('zone_id', $material['warehouse_location'])->first();
        }

        return view('raw_materials/view', [
            'title'           => 'Raw Material Details',
            'user'            => $this->getLoggedInUser(),
            'material'        => $material,
            'lastZone'        => $lastZone,
            'warehouseZone'   => $warehouseZone,
        ]);
    }

    public function edit($id)
    {
        $model    = new RawMaterialModel();
        $material = $model->find($id);

        if (!$material) {
            return redirect()->to(base_url('raw-materials/list'))->with('error', 'Raw material not found.');
        }

        return view('raw_materials/edit', [
            'title'    => 'Edit Raw Material',
            'user'     => $this->getLoggedInUser(),
            'material' => $material,
            'zones'    => (new ZoneModel())->where('status', 'active')->orderBy('zone_name')->findAll(),
        ]);
    }

    public function update($id)
    {
        $model    = new RawMaterialModel();
        $material = $model->find($id);

        if (!$material) {
            return redirect()->to(base_url('raw-materials/list'))->with('error', 'Raw material not found.');
        }

        if (!$this->validate($this->materialRules())) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $materialCode = $this->request->getPost('material_code');
        if ($model->isCodeTaken($materialCode, (int)$id)) {
            return redirect()->back()->withInput()->with('error', 'Material code already exists.');
        }

        $epcNo = $this->request->getPost('epc_no') ?: null;
        if ($epcError = $this->validateEpc($epcNo, (int)$id)) {
            return redirect()->back()->withInput()->with('error', $epcError);
        }

        $model->update($id, $this->materialPayloadFromRequest($materialCode, $epcNo));

        return redirect()->to(base_url('raw-materials/view/' . $id))->with('success', 'Raw material updated successfully.');
    }

    public function delete($id)
    {
        $model    = new RawMaterialModel();
        $material = $model->find($id);

        if (!$material) {
            return redirect()->to(base_url('raw-materials/list'))->with('error', 'Raw material not found.');
        }

        $model->delete($id);

        return redirect()->to(base_url('raw-materials/list'))->with('success', 'Raw material deleted.');
    }

    private function materialRules(): array
    {
        return [
            'material_name'              => 'required|min_length[2]|max_length[150]',
            'material_code'              => 'required|max_length[50]',
            'sap_code'                   => 'required|max_length[50]',
            'warehouse_location'         => 'required|max_length[100]',
            'min_stock'                  => 'required|decimal|greater_than_equal_to[0]',
            'expiry_alert_days'          => 'required|integer|greater_than_equal_to[0]',
            'supplier_name'              => 'required|max_length[150]',
            'manufacturer_name'          => 'required|max_length[150]',
            'supplier_shelf_life_months' => 'required|integer|greater_than_equal_to[0]',
        ];
    }

    private function materialPayloadFromRequest(string $materialCode, ?string $epcNo): array
    {
        $post = $this->request;

        return [
            'material_code'              => $materialCode,
            'material_name'              => $post->getPost('material_name'),
            'sap_code'                   => $post->getPost('sap_code'),
            'warehouse_location'         => $post->getPost('warehouse_location'),
            'min_stock'                  => $post->getPost('min_stock'),
            'expiry_alert_days'          => (int) $post->getPost('expiry_alert_days'),
            'sample_test'                => $post->getPost('sample_test') ? 1 : 0,
            'pre_sample_test'            => $post->getPost('pre_sample_test') ? 1 : 0,
            'k_test'                     => $post->getPost('k_test') ? 1 : 0,
            'supplier_name'              => $post->getPost('supplier_name'),
            'manufacturer_name'          => $post->getPost('manufacturer_name'),
            'supplier_shelf_life_months' => (int) $post->getPost('supplier_shelf_life_months'),
            'appearance'                 => $post->getPost('appearance') ?: null,
            'chemical_formula'           => $post->getPost('chemical_formula') ?: null,
            'ph_range'                   => $post->getPost('ph_range') ?: null,
            'assay_content'              => $post->getPost('assay_content') ?: null,
            'specific_gravity'           => $post->getPost('specific_gravity') ?: null,
            'shelf_life_months'          => $post->getPost('shelf_life_months') !== '' ? (int) $post->getPost('shelf_life_months') : null,
            'category'                   => $post->getPost('category') ?: null,
            'description'                => $post->getPost('description') ?: null,
            'epc_no'                     => $epcNo,
            'unit'                       => $post->getPost('unit') ?: null,
            'status'                     => $post->getPost('status') ?? 'active',
        ];
    }

    private function validateEpc(?string $epcNo, ?int $excludeId = null): ?string
    {
        if (!$epcNo) {
            return null;
        }

        $model = new RawMaterialModel();
        if ($model->isEpcRegistered($epcNo, $excludeId)) {
            return 'EPC tag is already registered to another raw material.';
        }

        $productModel = new \App\Models\ProductModel();
        if ($productModel->isEpcRegistered($epcNo)) {
            return 'EPC tag is already registered to a product.';
        }

        return null;
    }
}
