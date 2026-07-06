<?php

namespace App\Controllers\Traits;

use App\Services\InventoryStockService;

trait HandlesInventoryTags
{
    protected function inventoryItemType(): string
    {
        return 'product';
    }

    public function listTags($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $itemType = $this->inventoryItemType();
        $service  = new InventoryStockService();
        $item     = $this->getInventoryItemModel()->find((int) $id);

        if (!$item) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
        }

        if ($service->itemHasActiveTags($itemType, (int) $id)) {
            $service->syncBalanceFromTags($itemType, (int) $id);
            $item = $this->getInventoryItemModel()->find((int) $id);
        }

        return $this->response->setJSON([
            'success'     => true,
            'tags'        => $service->getTagsForItem($itemType, (int) $id),
            'tag_mode'    => $item['tag_mode'] ?? 'single',
            'qty_per_tag' => (float) ($item['qty_per_tag'] ?? 0),
            'balance'     => (float) ($item['quantity_on_hand'] ?? 0),
        ]);
    }

    public function assignTag()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data     = $this->request->getJSON(true) ?? [];
        $id       = (int) ($data['id'] ?? 0);
        $epcNo    = strtoupper(trim((string) ($data['epc_no'] ?? '')));
        $quantity = isset($data['quantity']) ? (float) $data['quantity'] : null;
        $label    = trim((string) ($data['label'] ?? '')) ?: null;
        $itemType = $this->inventoryItemType();

        if ($id <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item ID is required']);
        }

        $model = $this->getInventoryItemModel();
        if (!$model->find($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
        }

        try {
            $tag = (new InventoryStockService())->assignTag($itemType, $id, $epcNo, $quantity, $label);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'UHF tag assigned successfully.',
                'tag'     => $tag,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function removeTag()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data   = $this->request->getJSON(true) ?? [];
        $tagId  = (int) ($data['tag_id'] ?? 0);

        if ($tagId <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tag ID is required']);
        }

        $removed = (new InventoryStockService())->removeTag($tagId);

        return $this->response->setJSON([
            'success' => $removed,
            'message' => $removed ? 'Tag removed.' : 'Tag not found.',
        ]);
    }

    public function updateTag()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data     = $this->request->getJSON(true) ?? [];
        $tagId    = (int) ($data['tag_id'] ?? 0);
        $quantity = isset($data['quantity']) ? (float) $data['quantity'] : 0;

        if ($tagId <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tag ID is required']);
        }

        try {
            $updated = (new InventoryStockService())->updateTagDefaultQuantity($tagId, $quantity);

            return $this->response->setJSON([
                'success' => $updated,
                'message' => $updated ? 'Registered tag quantity updated.' : 'Tag not found.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    protected function applyTagRegisteredQuantitiesFromRequest(string $itemType, int $itemId): void
    {
        $posted = $this->request->getPost('tag_registered_qty');
        if (!is_array($posted)) {
            return;
        }

        $service = new InventoryStockService();
        foreach ($posted as $tagId => $qty) {
            try {
                $service->updateTagDefaultQuantity((int) $tagId, (float) $qty);
            } catch (\Throwable $e) {
                log_message('warning', 'Could not update tag registered qty: ' . $e->getMessage());
            }
        }
    }

    public function updateEpc()
    {
        return $this->assignTag();
    }

    protected function getInventoryItemModel()
    {
        return new \App\Models\ProductModel();
    }

    protected function tagFieldsFromRequest(): array
    {
        return [
            'tag_mode' => $this->request->getPost('tag_mode') === 'multi' ? 'multi' : 'single',
        ];
    }

    protected function afterInventoryItemSaved(string $itemType, int $itemId, ?string $epcNo): void
    {
        $service = new InventoryStockService();
        $item    = $this->getInventoryItemModel()->find($itemId);
        if ($item) {
            $service->ensureQrCode($itemType, $item);
        }

        $pendingTags = $this->pendingTagsFromRequest();
        if (!empty($pendingTags)) {
            $this->assignPendingTags($itemType, $itemId, $pendingTags);

            return;
        }

        if ($epcNo) {
            try {
                $qty = (float) ($this->request->getPost('epc_quantity') ?? 0);
                $service->assignTag($itemType, $itemId, $epcNo, $qty > 0 ? $qty : null);
            } catch (\Throwable $e) {
                log_message('warning', 'Could not auto-assign EPC on save: ' . $e->getMessage());
            }
        }
    }

    /** @return list<array{epc_no: string, quantity: float}> */
    protected function pendingTagsFromRequest(): array
    {
        $raw = $this->request->getPost('pending_tags');
        if (!$raw) {
            return [];
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $tags = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }
            $epc = strtoupper(trim((string) ($row['epc_no'] ?? '')));
            $qty = (float) ($row['quantity'] ?? 0);
            if ($epc === '' || strlen($epc) < 4) {
                continue;
            }
            if ($qty < 0) {
                $qty = 0;
            }
            $tags[] = ['epc_no' => $epc, 'quantity' => $qty];
        }

        return $tags;
    }

    protected function assignPendingTags(string $itemType, int $itemId, array $tags): void
    {
        $service = new InventoryStockService();

        foreach ($tags as $tag) {
            try {
                $service->assignTag($itemType, $itemId, $tag['epc_no'], $tag['quantity']);
            } catch (\Throwable $e) {
                log_message('warning', 'Could not assign pending tag on save: ' . $e->getMessage());
            }
        }
    }

    protected function validatePendingTags(?int $excludeItemId = null): ?string
    {
        $tags = $this->pendingTagsFromRequest();
        if (empty($tags)) {
            return null;
        }

        $itemType = $this->inventoryItemType();
        $service  = new InventoryStockService();
        $seen     = [];

        foreach ($tags as $tag) {
            if (isset($seen[$tag['epc_no']])) {
                return 'Duplicate EPC in tag list: ' . $tag['epc_no'];
            }
            $seen[$tag['epc_no']] = true;

            if ($service->isEpcUsedElsewhere($tag['epc_no'], $itemType, $excludeItemId ?? 0)) {
                return 'EPC tag is already registered: ' . $tag['epc_no'];
            }
        }

        $tagMode = $this->request->getPost('tag_mode') === 'multi' ? 'multi' : 'single';
        if ($tagMode === 'single' && count($tags) > 1) {
            return 'Single-tag mode allows only one UHF tag.';
        }

        return null;
    }
}
