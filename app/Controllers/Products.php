<?php

namespace App\Controllers;

use App\Controllers\Traits\HandlesStockMovement;
use App\Controllers\Traits\HandlesInventoryTags;
use App\Models\ProductModel;
use App\Models\ZoneModel;
use App\Services\InventoryStockService;

class Products extends BaseController
{
    use HandlesStockMovement;
    use HandlesInventoryTags;

    protected function inventoryItemType(): string
    {
        return 'product';
    }

    protected function getInventoryItemModel()
    {
        return new ProductModel();
    }
    public function index()
    {
        return redirect()->to(base_url('products/list'));
    }

    public function list()
    {
        $model     = new ProductModel();
        $zoneModel = new ZoneModel();

        $products = $model->getWithZone();
        $zones    = $zoneModel->where('status', 'active')->findAll();

        $totalActive   = 0;
        $totalInactive = 0;
        $taggedCount   = 0;

        foreach ($products as &$p) {
            if ($p['status'] === 'active') {
                $totalActive++;
            } else {
                $totalInactive++;
            }
            $tagCount = (new \App\Models\InventoryItemTagModel())->countTagsForItem('product', (int) $p['id']);
            if ($tagCount > 0 || !empty($p['epc_no'])) {
                $taggedCount++;
            }
            $p['tag_count'] = $tagCount;
            if ($tagCount > 0) {
                (new InventoryStockService())->syncBalanceFromTags('product', (int) $p['id']);
                $refreshed = $model->find($p['id']);
                if ($refreshed) {
                    $p['quantity_on_hand'] = $refreshed['quantity_on_hand'];
                }
            }
        }
        unset($p);

        $zoneNames = [];
        foreach ($zones as $zone) {
            $zoneNames[$zone['zone_id']] = $zone['zone_name'];
        }
        foreach ($products as &$p) {
            $p['storage_zone_name'] = ProductModel::storageLocationsLabel($p['storage_location'] ?? null, $zoneNames);
        }
        unset($p);

        return view('products/list', [
            'title'          => 'Product Master List',
            'user'           => $this->getLoggedInUser(),
            'products'       => $products,
            'zones'          => $zones,
            'stats' => [
                'total'    => count($products),
                'active'   => $totalActive,
                'inactive' => $totalInactive,
                'tagged'   => $taggedCount,
            ],
        ]);
    }

    public function add()
    {
        $model = new ProductModel();

        return view('products/add', [
            'title'        => 'Add Product',
            'user'         => $this->getLoggedInUser(),
            'product_code' => $model->generateProductCode(),
            'zones'        => (new ZoneModel())->where('status', 'active')->orderBy('zone_name')->findAll(),
        ]);
    }

    public function store()
    {
        $model = new ProductModel();

        if (!$this->validate($this->productRules())) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $productCode = $this->request->getPost('product_code');
        if ($model->isCodeTaken($productCode)) {
            return redirect()->back()->withInput()->with('error', 'Product Code already exists.');
        }

        $epcNo = $this->request->getPost('epc_no') ?: null;
        if ($epcError = $this->validateEpc($epcNo)) {
            return redirect()->back()->withInput()->with('error', $epcError);
        }
        if ($tagError = $this->validatePendingTags()) {
            return redirect()->back()->withInput()->with('error', $tagError);
        }

        $payload = $this->productPayloadFromRequest($productCode, $epcNo);
        $payload['status']      = 'active';
        $payload['qty_per_tag'] = 0;

        if (empty($payload['storage_location'])) {
            return redirect()->back()->withInput()->with('error', 'Select at least one storage zone, or choose All zones.');
        }

        $newId = $model->insert($payload);
        if ($newId) {
            $this->afterInventoryItemSaved('product', (int) $newId, $epcNo);
        }

        return redirect()->to(base_url('products/list'))->with('success', 'Product added successfully.');
    }

    public function view($id)
    {
        $model     = new ProductModel();
        $zoneModel = new ZoneModel();

        $product = $model->find($id);
        if (!$product) {
            return redirect()->to(base_url('products/list'))->with('error', 'Product not found.');
        }

        $service = new InventoryStockService();
        if ($service->itemHasActiveTags('product', (int) $id)) {
            $service->syncBalanceFromTags('product', (int) $id);
            $product = $model->find($id);
        }

        $lastZone = null;
        if (!empty($product['last_seen_zone'])) {
            $lastZone = $zoneModel->where('zone_id', $product['last_seen_zone'])->first();
        }

        $zoneNames = [];
        foreach ($zoneModel->where('status', 'active')->findAll() as $z) {
            $zoneNames[$z['zone_id']] = $z['zone_name'];
        }
        $storageZonesLabel = ProductModel::storageLocationsLabel($product['storage_location'] ?? null, $zoneNames);

        return view('products/view', array_merge([
            'title'    => 'Product Details',
            'user'     => $this->getLoggedInUser(),
            'product'  => $product,
            'lastZone' => $lastZone,
            'storageZonesLabel' => $storageZonesLabel,
            'itemType' => 'product',
            'itemId'   => (int) $id,
            'stockInUrl'  => base_url('products/stock-in/' . $id),
            'stockOutUrl' => base_url('products/stock-out/' . $id),
            'tags'     => (new InventoryStockService())->getTagsForItem('product', (int) $id),
            'qr_code'  => (new InventoryStockService())->ensureQrCode('product', $product),
        ], $this->loadStockViewData('product', (int) $id)));
    }

    public function stockIn($id)
    {
        return $this->processStockMovement('product', (int) $id, 'in', base_url('products/view/' . $id));
    }

    public function stockOut($id)
    {
        return $this->processStockMovement('product', (int) $id, 'out', base_url('products/view/' . $id));
    }

    public function edit($id)
    {
        $model   = new ProductModel();
        $product = $model->find($id);

        if (!$product) {
            return redirect()->to(base_url('products/list'))->with('error', 'Product not found.');
        }

        $service = new InventoryStockService();
        if ($service->itemHasActiveTags('product', (int) $id)) {
            $service->syncBalanceFromTags('product', (int) $id);
            $product = $model->find($id);
        }

        return view('products/edit', [
            'title'   => 'Edit Product',
            'user'    => $this->getLoggedInUser(),
            'product' => $product,
            'zones'   => (new ZoneModel())->where('status', 'active')->orderBy('zone_name')->findAll(),
            'tags'    => (new InventoryStockService())->getTagsForItem('product', (int) $id),
        ]);
    }

    public function update($id)
    {
        $model   = new ProductModel();
        $product = $model->find($id);

        if (!$product) {
            return redirect()->to(base_url('products/list'))->with('error', 'Product not found.');
        }

        if (!$this->validate($this->productRules())) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $productCode = $this->request->getPost('product_code');
        if ($model->isCodeTaken($productCode, (int)$id)) {
            return redirect()->back()->withInput()->with('error', 'Product Code already exists.');
        }

        $epcNo = $this->request->getPost('epc_no') ?: null;
        if ($epcError = $this->validateEpc($epcNo, (int)$id)) {
            return redirect()->back()->withInput()->with('error', $epcError);
        }

        $payload = $this->productPayloadFromRequest($productCode, $epcNo);
        if (empty($payload['storage_location'])) {
            return redirect()->back()->withInput()->with('error', 'Select at least one storage zone, or choose All zones.');
        }

        $model->update($id, $payload);

        if ($epcNo) {
            $this->afterInventoryItemSaved('product', (int) $id, $epcNo);
        } else {
            (new InventoryStockService())->ensureQrCode('product', $model->find($id));
        }

        $this->applyTagRegisteredQuantitiesFromRequest('product', (int) $id);

        $service = new InventoryStockService();
        if ($service->itemHasActiveTags('product', (int) $id)) {
            $service->syncBalanceFromTags('product', (int) $id);
        }

        return redirect()->to(base_url('products/view/' . $id))->with('success', 'Product updated successfully.');
    }

    public function delete($id)
    {
        $model   = new ProductModel();
        $product = $model->find($id);

        if (!$product) {
            return redirect()->to(base_url('products/list'))->with('error', 'Product not found.');
        }

        $model->delete($id);

        return redirect()->to(base_url('products/list'))->with('success', 'Product deleted.');
    }

    public function updateEpc()
    {
        return $this->assignTag();
    }

    private function productRules(): array
    {
        return [
            'product_name'      => 'required|min_length[2]|max_length[150]',
            'product_code'      => 'required|max_length[50]',
            'sap_code'          => 'required|max_length[50]',
            'shelf_life_months' => 'required|integer|greater_than_equal_to[0]',
            'expiry_date'       => 'required|valid_date[Y-m-d]',
            'cost_price'        => 'required|decimal|greater_than_equal_to[0]',
            'selling_price'     => 'required|decimal|greater_than_equal_to[0]',
        ];
    }

    private function productPayloadFromRequest(string $productCode, ?string $epcNo): array
    {
        $post = $this->request;

        return array_merge([
            'product_code'      => $productCode,
            'product_name'      => $post->getPost('product_name'),
            'sap_code'          => $post->getPost('sap_code'),
            'description'       => $post->getPost('description') ?: null,
            'shelf_life_months' => (int) $post->getPost('shelf_life_months'),
            'expiry_date'       => $post->getPost('expiry_date'),
            'suppliers'         => $this->suppliersFromRequest(),
            'storage_location'  => $this->storageLocationsFromRequest(),
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

    private function validateEpc(?string $epcNo, ?int $excludeId = null): ?string
    {
        if (!$epcNo) {
            return null;
        }

        $service = new InventoryStockService();
        if ($excludeId) {
            $tag = (new \App\Models\InventoryItemTagModel())->getByEpc($epcNo);
            if ($tag && $tag['item_type'] === 'product' && (int) $tag['item_id'] === $excludeId) {
                return null;
            }
        }
        if ($service->isEpcUsedElsewhere($epcNo, 'product', $excludeId ?? 0)) {
            return 'EPC tag is already registered to another item.';
        }

        return null;
    }

}
