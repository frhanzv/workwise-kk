<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\InventoryStockService;

class MobileInventory extends BaseController
{
    private InventoryStockService $stock;

    public function __construct()
    {
        $this->stock = new InventoryStockService();
    }

    public function lookup()
    {
        $epc    = $this->request->getGet('epc') ?: $this->getJson('epc');
        $qrCode = $this->request->getGet('qr_code') ?: $this->getJson('qr_code');

        if (!$epc && !$qrCode) {
            return $this->jsonError('Provide epc or qr_code.', 422);
        }

        $item = $this->stock->lookupByScan($epc, $qrCode);
        if (!$item) {
            return $this->jsonError('Item not found.', 404);
        }

        return $this->response->setJSON(['success' => true, 'item' => $item]);
    }

    public function balance()
    {
        $type = $this->request->getGet('type') ?: $this->getJson('type');
        $id   = (int) ($this->request->getGet('id') ?: $this->getJson('id') ?? 0);

        if (!in_array($type, ['product', 'raw_material'], true) || $id <= 0) {
            return $this->jsonError('Valid type and id required.', 422);
        }

        $item = $this->stock->getBalance($type, $id);
        if (!$item) {
            return $this->jsonError('Item not found.', 404);
        }

        return $this->response->setJSON(['success' => true, 'item' => $item]);
    }

    public function totalInventory()
    {
        return $this->response->setJSON([
            'success' => true,
            'totals'  => $this->stock->getTotalInventory(),
        ]);
    }

    public function stockIn()
    {
        return $this->handleMovement('stock_in');
    }

    public function stockOut()
    {
        return $this->handleMovement('stock_out');
    }

    public function transactions()
    {
        $start = $this->request->getGet('start_date');
        $end   = $this->request->getGet('end_date');
        $limit = (int) ($this->request->getGet('limit') ?: 100);

        return $this->response->setJSON([
            'success'      => true,
            'transactions' => $this->stock->getTransactions($start, $end, $limit),
        ]);
    }

    public function stockCheckStart()
    {
        $type   = $this->getJson('item_type');
        $id     = (int) ($this->getJson('item_id') ?? 0);
        $method = $this->getJson('scan_method') ?: 'qr';

        if (!in_array($type, ['product', 'raw_material'], true) || $id <= 0) {
            return $this->jsonError('Valid item_type and item_id required.', 422);
        }

        try {
            $result = $this->stock->startStockCheck($type, $id, $method, $this->apiUserId());
            return $this->response->setJSON(['success' => true] + $result);
        } catch (\RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
    }

    public function stockCheckScan()
    {
        $sessionId = (int) ($this->getJson('session_id') ?? 0);
        $epc       = $this->getJson('epc');
        $qrCode    = $this->getJson('qr_code');

        if ($sessionId <= 0 || (!$epc && !$qrCode)) {
            return $this->jsonError('session_id and epc or qr_code required.', 422);
        }

        try {
            $result = $this->stock->scanStockCheck($sessionId, $epc, $qrCode);
            return $this->response->setJSON(['success' => true] + $result);
        } catch (\RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
    }

    public function stockCheckComplete()
    {
        $sessionId = (int) ($this->getJson('session_id') ?? 0);
        $counted   = $this->getJson('counted_quantity');
        $notes     = $this->getJson('notes');

        if ($sessionId <= 0) {
            return $this->jsonError('session_id required.', 422);
        }

        try {
            $result = $this->stock->completeStockCheck(
                $sessionId,
                $counted !== null && $counted !== '' ? (float) $counted : null,
                $notes,
                $this->apiUserId()
            );
            return $this->response->setJSON(['success' => true] + $result);
        } catch (\RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
    }

    private function handleMovement(string $direction)
    {
        $json = $this->request->getJSON(true) ?? [];

        $epc      = $json['epc'] ?? null;
        $qrCode   = $json['qr_code'] ?? null;
        $type     = $json['item_type'] ?? null;
        $id       = isset($json['item_id']) ? (int) $json['item_id'] : 0;
        $quantity = isset($json['quantity']) ? (float) $json['quantity'] : 0;
        $zoneId   = $json['zone_id'] ?? null;
        $notes    = $json['notes'] ?? null;

        if ($epc || $qrCode) {
            $lookup = $this->stock->lookupByScan($epc, $qrCode);
            if (!$lookup) {
                return $this->jsonError('Scanned item not found.', 404);
            }
            $type = $lookup['type'];
            $id   = $lookup['id'];
        }

        if (!in_array($type, ['product', 'raw_material'], true) || $id <= 0) {
            return $this->jsonError('Valid item_type and item_id required (or scan epc/qr_code).', 422);
        }

        if ($quantity <= 0) {
            return $this->jsonError('Quantity must be greater than zero.', 422);
        }

        $method    = $epc ? 'uhf' : ($qrCode ? 'qr' : 'manual');
        $reference = $epc ?: $qrCode;

        try {
            $result = $direction === 'stock_in'
                ? $this->stock->stockIn($type, $id, $quantity, $method, $reference, $zoneId, $this->apiUserId(), $notes)
                : $this->stock->stockOut($type, $id, $quantity, $method, $reference, $zoneId, $this->apiUserId(), $notes);

            return $this->response->setJSON(['success' => true] + $result);
        } catch (\RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
    }

    private function getJson(string $key)
    {
        $json = $this->request->getJSON(true) ?? [];
        return $json[$key] ?? $this->request->getPost($key) ?? $this->request->getGet($key);
    }

    private function apiUserId(): ?int
    {
        return \App\Libraries\ApiContext::$userId;
    }

    private function jsonError(string $message, int $code = 400)
    {
        return $this->response->setJSON(['success' => false, 'message' => $message])->setStatusCode($code);
    }
}
