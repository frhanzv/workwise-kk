<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ZoneModel;

class Products extends BaseController
{
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

        foreach ($products as $p) {
            if ($p['status'] === 'active') {
                $totalActive++;
            } else {
                $totalInactive++;
            }
            if (!empty($p['epc_no'])) {
                $taggedCount++;
            }
        }

        return view('products/list', [
            'title'          => 'Products',
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
            'zones'        => [],
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
            return redirect()->back()->withInput()->with('error', 'Product code already exists.');
        }

        $epcNo = $this->request->getPost('epc_no') ?: null;
        if ($epcError = $this->validateEpc($epcNo)) {
            return redirect()->back()->withInput()->with('error', $epcError);
        }

        $model->insert($this->productPayloadFromRequest($productCode, $epcNo));

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

        $lastZone = null;
        if (!empty($product['last_seen_zone'])) {
            $lastZone = $zoneModel->where('zone_id', $product['last_seen_zone'])->first();
        }

        return view('products/view', [
            'title'    => 'Product Details',
            'user'     => $this->getLoggedInUser(),
            'product'  => $product,
            'lastZone' => $lastZone,
        ]);
    }

    public function edit($id)
    {
        $model   = new ProductModel();
        $product = $model->find($id);

        if (!$product) {
            return redirect()->to(base_url('products/list'))->with('error', 'Product not found.');
        }

        return view('products/edit', [
            'title'   => 'Edit Product',
            'user'    => $this->getLoggedInUser(),
            'product' => $product,
            'zones'   => [],
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
            return redirect()->back()->withInput()->with('error', 'Product code already exists.');
        }

        $epcNo = $this->request->getPost('epc_no') ?: null;
        if ($epcError = $this->validateEpc($epcNo, (int)$id)) {
            return redirect()->back()->withInput()->with('error', $epcError);
        }

        $model->update($id, $this->productPayloadFromRequest($productCode, $epcNo));

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

    private function productRules(): array
    {
        return [
            'product_name'         => 'required|min_length[2]|max_length[150]',
            'product_code'         => 'required|max_length[50]',
            'sap_code'             => 'required|max_length[50]',
            'entry_date'           => 'required|valid_date[Y-m-d]',
            'lot_number'           => 'required|max_length[50]',
            'shelf_life_months'    => 'required|integer|greater_than_equal_to[0]',
            'manufacturing_date'   => 'required|valid_date[Y-m-d]',
            'expiry_date'          => 'required|valid_date[Y-m-d]',
            'pricing_start_date'   => 'required|valid_date[Y-m-d]',
            'cost_price'           => 'required|decimal|greater_than_equal_to[0]',
            'selling_price'        => 'required|decimal|greater_than_equal_to[0]',
        ];
    }

    private function productPayloadFromRequest(string $productCode, ?string $epcNo): array
    {
        $post = $this->request;

        return [
            'product_code'       => $productCode,
            'product_name'       => $post->getPost('product_name'),
            'sap_code'           => $post->getPost('sap_code'),
            'entry_date'         => $post->getPost('entry_date'),
            'lot_number'         => $post->getPost('lot_number'),
            'shelf_life_months'  => (int) $post->getPost('shelf_life_months'),
            'analysis_date'      => $this->nullableDate($post->getPost('analysis_date')),
            'manufacturing_date' => $post->getPost('manufacturing_date'),
            'expiry_date'        => $post->getPost('expiry_date'),
            'customer_name'      => $post->getPost('customer_name') ?: null,
            'category'           => $post->getPost('category') ?: null,
            'description'        => $post->getPost('description') ?: null,
            'ph_level_target'    => $post->getPost('ph_level_target') ?: null,
            'purity_grade'       => $post->getPost('purity_grade') ?: null,
            'density_20c'        => $post->getPost('density_20c') ?: null,
            'viscosity'          => $post->getPost('viscosity') ?: null,
            'pricing_start_date' => $post->getPost('pricing_start_date'),
            'cost_price'         => $post->getPost('cost_price'),
            'selling_price'      => $post->getPost('selling_price'),
            'color_description'  => $post->getPost('color_description') ?: null,
            'qc_status'          => $post->getPost('qc_status') ?: null,
            'qc_quantity'        => $post->getPost('qc_quantity') !== '' ? $post->getPost('qc_quantity') : null,
            'nsf_certified'      => $post->getPost('nsf_certified') ? 1 : 0,
            'halal_certified'    => $post->getPost('halal_certified') ? 1 : 0,
            'epc_no'             => $epcNo,
            'unit'               => $post->getPost('unit') ?: null,
            'status'             => $post->getPost('status') ?? 'active',
        ];
    }

    private function validateEpc(?string $epcNo, ?int $excludeId = null): ?string
    {
        if (!$epcNo) {
            return null;
        }

        $model = new ProductModel();
        if ($model->isEpcRegistered($epcNo, $excludeId)) {
            return 'EPC tag is already registered to another product.';
        }

        $rmModel = new \App\Models\RawMaterialModel();
        if ($rmModel->isEpcRegistered($epcNo)) {
            return 'EPC tag is already registered to a raw material.';
        }

        return null;
    }

    private function nullableDate(?string $value): ?string
    {
        return ($value && $value !== '') ? $value : null;
    }
}
