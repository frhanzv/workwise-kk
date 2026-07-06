<?php

namespace App\Controllers;

use App\Controllers\Traits\HandlesStockMovement;
use App\Controllers\Traits\HandlesInventoryTags;
use App\Models\ProductModel;
use App\Models\RawMaterialModel;
use App\Models\ZoneModel;
use App\Services\InventoryStockService;

class RawMaterials extends BaseController
{
    use HandlesStockMovement;
    use HandlesInventoryTags;

    protected function inventoryItemType(): string
    {
        return 'raw_material';
    }

    protected function getInventoryItemModel()
    {
        return new RawMaterialModel();
    }

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

        foreach ($materials as &$m) {
            if ($m['status'] === 'active') {
                $totalActive++;
            } else {
                $totalInactive++;
            }
            $tagCount = (new \App\Models\InventoryItemTagModel())->countTagsForItem('raw_material', (int) $m['id']);
            if ($tagCount > 0 || !empty($m['epc_no'])) {
                $taggedCount++;
            }
            $m['tag_count'] = $tagCount;
            if ($tagCount > 0) {
                (new InventoryStockService())->syncBalanceFromTags('raw_material', (int) $m['id']);
                $refreshed = $model->find($m['id']);
                if ($refreshed) {
                    $m['quantity_on_hand'] = $refreshed['quantity_on_hand'];
                }
            }
        }
        unset($m);

        $zoneNames = [];
        foreach ($zones as $zone) {
            $zoneNames[$zone['zone_id']] = $zone['zone_name'];
        }
        foreach ($materials as &$m) {
            $raw = $m['storage_location'] ?? null;
            if (($raw === null || $raw === '') && !empty($m['warehouse_location'])) {
                $raw = json_encode([(string) $m['warehouse_location']]);
            }
            $m['storage_zone_name'] = ProductModel::storageLocationsLabel($raw, $zoneNames);
        }
        unset($m);

        return view('raw_materials/list', [
            'title'     => 'Raw Material Master List',
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
            return redirect()->back()->withInput()->with('error', 'Raw Material Code already exists.');
        }

        $epcNo = $this->request->getPost('epc_no') ?: null;
        if ($epcError = $this->validateEpc($epcNo)) {
            return redirect()->back()->withInput()->with('error', $epcError);
        }
        if ($tagError = $this->validatePendingTags()) {
            return redirect()->back()->withInput()->with('error', $tagError);
        }

        $payload = $this->materialPayloadFromRequest($materialCode, $epcNo);
        $payload['status']      = 'active';
        $payload['qty_per_tag'] = 0;

        if (empty($payload['storage_location'])) {
            return redirect()->back()->withInput()->with('error', 'Select at least one storage zone, or choose All zones.');
        }

        $newId = $model->insert($payload);
        if ($newId) {
            $this->afterInventoryItemSaved('raw_material', (int) $newId, $epcNo);
        }

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

        $service = new InventoryStockService();
        if ($service->itemHasActiveTags('raw_material', (int) $id)) {
            $service->syncBalanceFromTags('raw_material', (int) $id);
            $material = $model->find($id);
        }

        $lastZone = null;
        if (!empty($material['last_seen_zone'])) {
            $lastZone = $zoneModel->where('zone_id', $material['last_seen_zone'])->first();
        }

        $zoneNames = [];
        foreach ($zoneModel->where('status', 'active')->findAll() as $z) {
            $zoneNames[$z['zone_id']] = $z['zone_name'];
        }
        $storageRaw = $material['storage_location'] ?? null;
        if (($storageRaw === null || $storageRaw === '') && !empty($material['warehouse_location'])) {
            $storageRaw = json_encode([(string) $material['warehouse_location']]);
        }
        $storageZonesLabel = ProductModel::storageLocationsLabel($storageRaw, $zoneNames);

        return view('raw_materials/view', array_merge([
            'title'         => 'Raw Material Details',
            'user'          => $this->getLoggedInUser(),
            'material'      => $material,
            'lastZone'      => $lastZone,
            'storageZonesLabel' => $storageZonesLabel,
            'itemType'      => 'raw_material',
            'itemId'        => (int) $id,
            'stockInUrl'    => base_url('raw-materials/stock-in/' . $id),
            'stockOutUrl'   => base_url('raw-materials/stock-out/' . $id),
            'tags'          => (new InventoryStockService())->getTagsForItem('raw_material', (int) $id),
            'qr_code'       => (new InventoryStockService())->ensureQrCode('raw_material', $material),
        ], $this->loadStockViewData('raw_material', (int) $id)));
    }

    public function stockIn($id)
    {
        return $this->processStockMovement('raw_material', (int) $id, 'in', base_url('raw-materials/view/' . $id));
    }

    public function stockOut($id)
    {
        return $this->processStockMovement('raw_material', (int) $id, 'out', base_url('raw-materials/view/' . $id));
    }

    public function edit($id)
    {
        $model    = new RawMaterialModel();
        $material = $model->find($id);

        if (!$material) {
            return redirect()->to(base_url('raw-materials/list'))->with('error', 'Raw material not found.');
        }

        $service = new InventoryStockService();
        if ($service->itemHasActiveTags('raw_material', (int) $id)) {
            $service->syncBalanceFromTags('raw_material', (int) $id);
            $material = $model->find($id);
        }

        return view('raw_materials/edit', [
            'title'    => 'Edit Raw Material',
            'user'     => $this->getLoggedInUser(),
            'material' => $material,
            'zones'    => (new ZoneModel())->where('status', 'active')->orderBy('zone_name')->findAll(),
            'tags'     => (new InventoryStockService())->getTagsForItem('raw_material', (int) $id),
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
        if ($model->isCodeTaken($materialCode, (int) $id)) {
            return redirect()->back()->withInput()->with('error', 'Raw Material Code already exists.');
        }

        $epcNo = $this->request->getPost('epc_no') ?: null;
        if ($epcError = $this->validateEpc($epcNo, (int) $id)) {
            return redirect()->back()->withInput()->with('error', $epcError);
        }

        $payload = $this->materialPayloadFromRequest($materialCode, $epcNo);
        if (empty($payload['storage_location'])) {
            return redirect()->back()->withInput()->with('error', 'Select at least one storage zone, or choose All zones.');
        }

        $model->update($id, $payload);

        if ($epcNo) {
            $this->afterInventoryItemSaved('raw_material', (int) $id, $epcNo);
        } else {
            (new InventoryStockService())->ensureQrCode('raw_material', $model->find($id));
        }

        $this->applyTagRegisteredQuantitiesFromRequest('raw_material', (int) $id);

        $service = new InventoryStockService();
        if ($service->itemHasActiveTags('raw_material', (int) $id)) {
            $service->syncBalanceFromTags('raw_material', (int) $id);
        }

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
            'material_name'     => 'required|min_length[2]|max_length[150]',
            'material_code'     => 'required|max_length[50]',
            'sap_code'          => 'required|max_length[50]',
            'shelf_life_months' => 'required|integer|greater_than_equal_to[0]',
            'expiry_date'       => 'required|valid_date[Y-m-d]',
            'cost_price'        => 'required|decimal|greater_than_equal_to[0]',
            'selling_price'     => 'required|decimal|greater_than_equal_to[0]',
        ];
    }

    private function materialPayloadFromRequest(string $materialCode, ?string $epcNo): array
    {
        $post      = $this->request;
        $suppliers = $this->suppliersFromRequest();
        $storage   = $this->storageLocationsFromRequest();
        $zoneIds   = $storage ? (json_decode($storage, true) ?: []) : [];
        $firstZone = ($zoneIds !== [] && ($zoneIds[0] ?? '') !== ProductModel::STORAGE_ALL_ZONES)
            ? $zoneIds[0]
            : null;

        return array_merge([
            'material_code'     => $materialCode,
            'material_name'     => $post->getPost('material_name'),
            'sap_code'          => $post->getPost('sap_code'),
            'description'       => $post->getPost('description') ?: null,
            'shelf_life_months' => (int) $post->getPost('shelf_life_months'),
            'expiry_date'       => $post->getPost('expiry_date'),
            'suppliers'         => $suppliers,
            'storage_location'  => $storage,
            // Keep legacy single warehouse field in sync with first specific zone.
            'warehouse_location'=> $firstZone,
            'supplier_name'     => $this->firstSupplierName($suppliers),
            'cost_price'        => $post->getPost('cost_price'),
            'selling_price'     => $post->getPost('selling_price'),
            'epc_no'            => $epcNo,
            'unit'              => $post->getPost('unit') ?: null,
        ], $this->tagFieldsFromRequest());
    }

    private function suppliersFromRequest(): ?string
    {
        $posted = $this->request->getPost('suppliers');
        if (!is_array($posted)) {
            return null;
        }

        $names = [];
        foreach ($posted as $name) {
            $name = trim((string) $name);
            if ($name !== '') {
                $names[] = $name;
            }
        }

        $names = array_values(array_unique($names));

        return $names === [] ? null : json_encode($names);
    }

    private function storageLocationsFromRequest(): ?string
    {
        if ($this->request->getPost('storage_all_zones')) {
            return json_encode([ProductModel::STORAGE_ALL_ZONES]);
        }

        $posted = $this->request->getPost('storage_locations');
        if (!is_array($posted)) {
            return null;
        }

        $zoneIds = [];
        foreach ($posted as $zoneId) {
            $zoneId = trim((string) $zoneId);
            if ($zoneId !== '' && $zoneId !== ProductModel::STORAGE_ALL_ZONES) {
                $zoneIds[] = $zoneId;
            }
        }

        $zoneIds = array_values(array_unique($zoneIds));

        return $zoneIds === [] ? null : json_encode($zoneIds);
    }

    private function firstSupplierName(?string $suppliersJson): ?string
    {
        if (!$suppliersJson) {
            return null;
        }
        $names = json_decode($suppliersJson, true);

        return is_array($names) && $names !== [] ? (string) $names[0] : null;
    }

    private function validateEpc(?string $epcNo, ?int $excludeId = null): ?string
    {
        if (!$epcNo) {
            return null;
        }

        $service = new InventoryStockService();
        if ($service->isEpcUsedElsewhere($epcNo, 'raw_material', $excludeId ?? 0)) {
            return 'EPC tag is already registered to another item.';
        }

        return null;
    }
}
