<?php

namespace App\Controllers\Traits;

use App\Services\InventoryStockService;

trait HandlesStockMovement
{
    protected function loadStockViewData(string $itemType, int $itemId): array
    {
        $service = new InventoryStockService();

        return [
            'stock_summary'      => $service->getItemStockSummary($itemType, $itemId) ?? [],
            'stock_transactions' => $service->getItemTransactions($itemType, $itemId, 50),
        ];
    }

    protected function applyBalanceFromEdit(string $itemType, int $itemId, float $previousBalance): ?string
    {
        $service = new InventoryStockService();
        if ($service->itemHasActiveTags($itemType, $itemId)) {
            return null;
        }

        $submitted = $this->request->getPost('set_balance');
        if ($submitted === null || $submitted === '') {
            return null;
        }

        $newBalance = (float) $submitted;
        if ($newBalance < 0) {
            return 'Balance cannot be negative.';
        }

        $diff = round($newBalance - $previousBalance, 3);
        if (abs($diff) < 0.0000001) {
            return null;
        }

        $userId = (int) session()->get('id');
        $service = new InventoryStockService();

        try {
            if ($diff > 0) {
                $service->stockIn($itemType, $itemId, $diff, 'web', null, null, $userId ?: null, 'Balance adjusted on edit');
            } else {
                $service->stockOut($itemType, $itemId, abs($diff), 'web', null, null, $userId ?: null, 'Balance adjusted on edit');
            }
        } catch (\RuntimeException $e) {
            return $e->getMessage();
        }

        return null;
    }

    protected function applyInitialStock(string $itemType, int $itemId): void
    {
        $qty = (float) ($this->request->getPost('initial_quantity') ?? 0);
        if ($qty <= 0) {
            return;
        }

        $userId = (int) session()->get('id');
        (new InventoryStockService())->stockIn(
            $itemType,
            $itemId,
            $qty,
            'web',
            null,
            null,
            $userId ?: null,
            'Opening stock on create'
        );
    }

    protected function processStockMovement(string $itemType, int $id, string $direction, string $redirectUrl)
    {
        $quantity = (float) $this->request->getPost('quantity');
        $notes    = $this->request->getPost('notes') ?: null;

        if ($quantity <= 0) {
            return redirect()->to($redirectUrl)->with('error', 'Quantity must be greater than zero.');
        }

        try {
            $service = new InventoryStockService();
            $userId  = (int) session()->get('id');

            $result = $direction === 'in'
                ? $service->stockIn($itemType, $id, $quantity, 'web', null, null, $userId, $notes)
                : $service->stockOut($itemType, $id, $quantity, 'web', null, null, $userId, $notes);

            $label = $direction === 'in' ? 'Stock in' : 'Stock out';
            return redirect()->to($redirectUrl)->with(
                'success',
                $label . ' of ' . format_inventory_qty($quantity) . ' recorded. New balance: ' . format_inventory_qty($result['balance_after'])
            );
        } catch (\RuntimeException $e) {
            return redirect()->to($redirectUrl)->with('error', $e->getMessage());
        }
    }
}
