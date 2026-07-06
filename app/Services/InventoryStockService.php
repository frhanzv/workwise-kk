<?php

namespace App\Services;

use App\Models\InventoryTransactionModel;
use App\Models\InventoryItemTagModel;
use App\Models\InventoryZoneRecordModel;
use App\Models\ProductModel;
use App\Models\RawMaterialModel;
use App\Models\StockCheckScanModel;
use App\Models\StockCheckSessionModel;
use App\Models\ZoneModel;

class InventoryStockService
{
    public function lookupByScan(?string $epc = null, ?string $qrCode = null): ?array
    {
        $tagModel = new InventoryItemTagModel();

        if ($epc) {
            $tag = $tagModel->getByEpc($epc);
            if ($tag) {
                return $this->formatTagPayload($tag);
            }

            $product = (new ProductModel())->getByEpc($epc);
            if ($product) {
                return $this->formatItemPayload('product', $product);
            }
            $material = (new RawMaterialModel())->getByEpc($epc);
            if ($material) {
                return $this->formatItemPayload('raw_material', $material);
            }
        }

        if ($qrCode) {
            $product = (new ProductModel())->where('qr_code', $qrCode)->first();
            if ($product) {
                return $this->formatItemPayload('product', $product);
            }
            $material = (new RawMaterialModel())->where('qr_code', $qrCode)->first();
            if ($material) {
                return $this->formatItemPayload('raw_material', $material);
            }
        }

        return null;
    }

    /**
     * Global stock finder — search by UHF tag, batch code, or item name.
     *
     * @return array{query: string, results: list<array<string, mixed>>}
     */
    public function findStockByQuery(string $rawQuery, int $limit = 12): array
    {
        $query = trim($rawQuery);
        if ($query === '') {
            return ['query' => '', 'results' => []];
        }

        $results = [];
        $seen    = [];

        $add = function (?array $match) use (&$results, &$seen, $limit) {
            if (!$match || count($results) >= $limit) {
                return;
            }
            $key = ($match['type'] ?? '') . ':' . ($match['id'] ?? '') . ':' . ($match['epc_no'] ?? '');
            if (isset($seen[$key])) {
                return;
            }
            $seen[$key] = true;
            $results[]  = $this->formatFinderResult($match);
        };

        $epc = strtoupper($query);
        if (preg_match('/^(E2|DD|30)[0-9A-F]{4,}$/i', $epc)) {
            $add($this->lookupByScan($epc, null));
            if ($results !== []) {
                return ['query' => $query, 'results' => $results];
            }
        }

        if (str_starts_with($query, 'WW|')) {
            $add($this->lookupByScan(null, $query));
            if ($results !== []) {
                return ['query' => $query, 'results' => $results];
            }
        }

        $productModel  = new ProductModel();
        $materialModel = new RawMaterialModel();

        foreach ($productModel->where('product_code', $query)->where('status', 'active')->findAll(5) as $row) {
            $add($this->formatItemPayload('product', $row));
        }
        foreach ($materialModel->where('material_code', $query)->where('status', 'active')->findAll(5) as $row) {
            $add($this->formatItemPayload('raw_material', $row));
        }
        if ($results !== []) {
            return ['query' => $query, 'results' => $results];
        }

        foreach ($productModel
            ->groupStart()
                ->like('product_code', $query)
                ->orLike('product_name', $query)
                ->orLike('sap_code', $query)
            ->groupEnd()
            ->where('status', 'active')
            ->orderBy('product_name', 'ASC')
            ->findAll($limit) as $row) {
            $add($this->formatItemPayload('product', $row));
        }
        foreach ($materialModel
            ->groupStart()
                ->like('material_code', $query)
                ->orLike('material_name', $query)
                ->orLike('sap_code', $query)
            ->groupEnd()
            ->where('status', 'active')
            ->orderBy('material_name', 'ASC')
            ->findAll($limit) as $row) {
            $add($this->formatItemPayload('raw_material', $row));
        }

        return ['query' => $query, 'results' => array_slice($results, 0, $limit)];
    }

    public function formatFinderResult(array $match): array
    {
        $type  = $match['type'];
        $id    = (int) $match['id'];
        $tagId = !empty($match['tag_id']) ? (int) $match['tag_id'] : null;

        if ($this->itemHasActiveTags($type, $id)) {
            $this->syncBalanceFromTags($type, $id);
            $item = $this->getItem($type, $id);
            if ($item) {
                $match['quantity_on_hand'] = (float) ($item['quantity_on_hand'] ?? 0);
                $match['balance']          = (float) ($item['quantity_on_hand'] ?? 0);
            }
        }

        $location = $this->resolveItemLocation($type, $id, $tagId);
        $item     = $this->getItem($type, $id);

        $zoneNamesMap = [];
        foreach ((new ZoneModel())->where('status', 'active')->findAll() as $z) {
            $zoneNamesMap[$z['zone_id']] = $z['zone_name'];
        }

        $storageRaw = $item['storage_location'] ?? null;
        if (($storageRaw === null || $storageRaw === '') && $type === 'raw_material' && !empty($item['warehouse_location'])) {
            $storageRaw = json_encode([(string) $item['warehouse_location']]);
        }

        return [
            'type'             => $type,
            'type_label'       => $match['type_label'] ?? ($type === 'product' ? 'Product' : 'Raw Material'),
            'id'               => $id,
            'code'             => $match['code'] ?? '',
            'name'             => $match['name'] ?? '',
            'unit'             => $match['unit'] ?? '',
            'epc_no'           => $match['epc_no'] ?? '',
            'tag_id'           => $tagId,
            'tag_quantity'     => isset($match['tag_quantity']) ? (float) $match['tag_quantity'] : null,
            'balance'          => (float) ($match['balance'] ?? $match['quantity_on_hand'] ?? 0),
            'location'         => $location,
            'allowed_zones'    => ProductModel::storageLocationsLabel($storageRaw, $zoneNamesMap),
            'detail_url'       => base_url('inventory/search-stock') . '?q=' . rawurlencode($match['epc_no'] ?: $match['code']),
        ];
    }

    private function resolveItemLocation(string $itemType, int $itemId, ?int $tagId = null): array
    {
        $today       = date('Y-m-d');
        $recordModel = new InventoryZoneRecordModel();
        $zoneModel   = new ZoneModel();
        $active      = null;

        if ($tagId) {
            $active = $recordModel->getActiveCheckInAnyZoneForTag($tagId, $today);
        }
        if (!$active) {
            $active = $recordModel->getActiveCheckInAnyZone($itemType, $itemId, $today);
        }

        if ($active) {
            $zone = $zoneModel->where('zone_id', $active['zone_id'])->first();

            return [
                'status'    => 'in_zone',
                'label'     => 'Currently in zone',
                'zone_id'   => $active['zone_id'],
                'zone_name' => $zone['zone_name'] ?? $active['zone_id'],
                'since'     => date('d M Y H:i', strtotime($active['check_in_time'])),
            ];
        }

        $item     = $this->getItem($itemType, $itemId);
        $lastZone = $item['last_seen_zone'] ?? null;

        if ($tagId) {
            $tag = (new InventoryItemTagModel())->find($tagId);
            if (!empty($tag['last_seen_zone'])) {
                $lastZone = $tag['last_seen_zone'];
            }
        }

        if ($lastZone) {
            $zone = $zoneModel->where('zone_id', $lastZone)->first();

            return [
                'status'    => 'last_seen',
                'label'     => 'Last seen at',
                'zone_id'   => $lastZone,
                'zone_name' => $zone['zone_name'] ?? $lastZone,
                'since'     => !empty($item['last_seen_at'])
                    ? date('d M Y H:i', strtotime($item['last_seen_at']))
                    : null,
            ];
        }

        return [
            'status'    => 'unknown',
            'label'     => 'Location unknown',
            'zone_name' => '—',
            'since'     => null,
        ];
    }

    public function stockOutFromZone(
        string $itemType,
        int $itemId,
        float $quantity,
        ?string $scanReference = null,
        ?string $zoneId = null,
        ?int $tagId = null
    ): ?array {
        $item = $this->getItem($itemType, $itemId);
        if (!$item || $quantity <= 0) {
            return null;
        }

        $tagModel = new InventoryItemTagModel();

        if ($tagId) {
            $tag = $tagModel->find($tagId);
            if (!$tag || ($tag['status'] ?? '') !== 'active') {
                return null;
            }

            $tagQty = (float) ($tag['quantity'] ?? 0);
            if ($tagQty <= 0) {
                return null;
            }

            $deduct    = min($quantity, $tagQty);
            $newTagQty = max(0, $tagQty - $deduct);
            $tagModel->update($tagId, ['quantity' => $newTagQty]);

            $this->logItemMovement(
                $itemType,
                $itemId,
                'stock_out',
                $deduct,
                'uhf',
                $scanReference,
                $zoneId,
                null,
                'Zone OUT'
            );
            $balanceAfter = $this->syncBalanceFromTags($itemType, $itemId);

            return [
                'quantity'      => $deduct,
                'balance_after' => $balanceAfter,
                'tag_id'      => $tagId,
            ];
        }

        if ($tagModel->countTagsForItem($itemType, $itemId) > 0) {
            return null;
        }

        $current = (float) ($item['quantity_on_hand'] ?? 0);
        if ($current <= 0) {
            return null;
        }

        $deduct = min($quantity, $current);

        $result = $this->stockOut(
            $itemType,
            $itemId,
            $deduct,
            'uhf',
            $scanReference,
            $zoneId,
            null,
            'Zone OUT'
        );

        return [
            'quantity'      => (float) $result['quantity'],
            'balance_after' => (float) $result['balance_after'],
            'tag_id'        => null,
        ];
    }

    /**
     * Zone IN: restore tag stock up to its registered (default) quantity.
     */
    public function stockInFromZone(
        string $itemType,
        int $itemId,
        int $tagId,
        ?string $epcNo = null,
        ?string $zoneId = null
    ): ?array {
        $tagModel = new InventoryItemTagModel();
        $tag      = $tagModel->find($tagId);
        if (!$tag || ($tag['status'] ?? '') !== 'active') {
            return null;
        }

        $current = normalize_inventory_qty((float) ($tag['quantity'] ?? 0));
        $default = normalize_inventory_qty((float) ($tag['default_quantity'] ?? $tag['quantity'] ?? 0));

        if ($default <= 0 || $current >= $default - 0.0000001) {
            return null;
        }

        $restore = normalize_inventory_qty($default - $current);
        $tagModel->update($tagId, ['quantity' => $default]);

        $this->logItemMovement(
            $itemType,
            $itemId,
            'stock_in',
            $restore,
            'uhf',
            $epcNo,
            $zoneId,
            null,
            'Zone IN'
        );
        $balanceAfter = $this->syncBalanceFromTags($itemType, $itemId);

        return [
            'quantity'      => $restore,
            'balance_after' => $balanceAfter,
            'tag_id'        => $tagId,
        ];
    }

    public function assignTag(
        string $itemType,
        int $itemId,
        string $epcNo,
        ?float $quantity = null,
        ?string $label = null
    ): array {
        $item = $this->getItem($itemType, $itemId);
        if (!$item) {
            throw new \RuntimeException('Item not found.');
        }

        $epcNo = strtoupper(trim($epcNo));
        if ($epcNo === '' || strlen($epcNo) < 4) {
            throw new \RuntimeException('EPC tag must be at least 4 characters.');
        }

        $tagModel    = new InventoryItemTagModel();
        $existingTag = $tagModel->findByEpc($epcNo);

        if ($existingTag && ($existingTag['status'] ?? '') === 'active') {
            if ($existingTag['item_type'] === $itemType && (int) $existingTag['item_id'] === $itemId) {
                throw new \RuntimeException('This EPC tag is already assigned to this item.');
            }
            throw new \RuntimeException('EPC tag is already registered.');
        }

        $alsoExcludeProductId  = null;
        $alsoExcludeMaterialId = null;
        if ($existingTag && ($existingTag['status'] ?? '') !== 'active') {
            if ($existingTag['item_type'] === 'product') {
                $alsoExcludeProductId = (int) $existingTag['item_id'];
            } else {
                $alsoExcludeMaterialId = (int) $existingTag['item_id'];
            }
        }

        if ($this->isEpcUsedElsewhere($epcNo, $itemType, $itemId, $alsoExcludeProductId, $alsoExcludeMaterialId)) {
            throw new \RuntimeException('EPC tag is already registered to another item.');
        }

        $tagMode = $item['tag_mode'] ?? 'single';
        if ($tagMode === 'single' && $tagModel->countTagsForItem($itemType, $itemId) > 0) {
            throw new \RuntimeException('Single-tag mode allows only one UHF tag. Switch to multi-tag mode to add more.');
        }

        $qtyPerTag = (float) ($item['qty_per_tag'] ?? 0);

        if ($quantity !== null) {
            $registeredQty = normalize_inventory_qty(max(0, $quantity));
        } else {
            $registeredQty = normalize_inventory_qty(max(0, $qtyPerTag));
        }

        if ($existingTag) {
            $oldItemType = $existingTag['item_type'];
            $oldItemId   = (int) $existingTag['item_id'];

            $tagModel->update((int) $existingTag['id'], [
                'item_type'        => $itemType,
                'item_id'          => $itemId,
                'quantity'         => 0,
                'default_quantity' => $registeredQty,
                'label'            => $label,
                'status'           => 'active',
            ]);
            $tagId = (int) $existingTag['id'];

            $this->clearItemEpcIfMatches($oldItemType, $oldItemId, $epcNo);
            if ($oldItemType !== $itemType || $oldItemId !== $itemId) {
                $this->syncBalanceFromTags($oldItemType, $oldItemId);
            }
        } else {
            $tagModel->insert([
                'item_type'        => $itemType,
                'item_id'          => $itemId,
                'epc_no'           => $epcNo,
                'quantity'         => 0,
                'default_quantity' => $registeredQty,
                'label'            => $label,
                'status'           => 'active',
            ]);
            $tagId = (int) $tagModel->getInsertID();
        }

        $model = $itemType === 'product' ? new ProductModel() : new RawMaterialModel();
        $model->update($itemId, ['epc_no' => $epcNo]);

        $this->syncBalanceFromTags($itemType, $itemId);

        return $this->formatTagPayload($tagModel->find($tagId));
    }

    /**
     * Preview Tag + Stock In: resolve EPC and auto-determined quantity (no write).
     */
    public function previewTagStockIn(string $itemType, int $itemId, string $epcNo): array
    {
        $item = $this->getItem($itemType, $itemId);
        if (!$item) {
            throw new \RuntimeException('Item not found.');
        }

        $epcNo = strtoupper(trim($epcNo));
        if ($epcNo === '' || strlen($epcNo) < 4) {
            throw new \RuntimeException('Scan a valid UHF EPC tag.');
        }

        return $this->resolveTagStockInQuantity($itemType, $item, $epcNo);
    }

    /**
     * Assign a UHF tag and stock in quantity (Tag + Stock In wizard).
     * Quantity is determined from qty_per_tag or existing tag registered qty — not typed in.
     * Optionally updates batch/item code.
     */
    public function tagAndStockIn(
        string $itemType,
        int $itemId,
        string $epcNo,
        ?string $batchCode = null,
        ?int $userId = null
    ): array {
        if (!in_array($itemType, ['product', 'raw_material'], true)) {
            throw new \RuntimeException('Invalid item type.');
        }

        $model = $itemType === 'product' ? new ProductModel() : new RawMaterialModel();
        $item  = $model->find($itemId);
        if (!$item) {
            throw new \RuntimeException('Item not found.');
        }

        $codeField = $itemType === 'product' ? 'product_code' : 'material_code';
        $batchCode = $batchCode !== null ? trim($batchCode) : '';
        if ($batchCode !== '' && $batchCode !== ($item[$codeField] ?? '')) {
            if ($model->isCodeTaken($batchCode, $itemId)) {
                throw new \RuntimeException('Batch / item code already exists.');
            }
            $model->update($itemId, [$codeField => $batchCode]);
            $item = $model->find($itemId);
        }

        $epcNo    = strtoupper(trim($epcNo));
        $plan     = $this->resolveTagStockInQuantity($itemType, $item, $epcNo);
        $quantity = $plan['quantity'];

        $tagModel = new InventoryItemTagModel();

        if ($plan['mode'] === 'restore') {
            $tagId = (int) $plan['tag_id'];
            $tagModel->update($tagId, ['quantity' => $plan['registered_qty']]);
            $balanceAfter = $this->syncBalanceFromTags($itemType, $itemId);
            $this->logItemMovement(
                $itemType,
                $itemId,
                'stock_in',
                $quantity,
                'uhf',
                $epcNo,
                null,
                $userId,
                'Tag + Stock In (restore): ' . $epcNo
            );
            $tag = $this->formatTagPayload($tagModel->find($tagId));
        } else {
            $tag = $this->assignTag($itemType, $itemId, $epcNo, $quantity);
            $balanceAfter = (float) ($model->find($itemId)['quantity_on_hand'] ?? 0);

            if ($quantity > 0) {
                $tagModel->update((int) $tag['tag_id'], ['quantity' => $quantity]);
                $balanceAfter = $this->syncBalanceFromTags($itemType, $itemId);
                $this->logItemMovement(
                    $itemType,
                    $itemId,
                    'stock_in',
                    $quantity,
                    'uhf',
                    $epcNo,
                    null,
                    $userId,
                    'Tag + Stock In: ' . $epcNo
                );
                $tag = $this->formatTagPayload($tagModel->find((int) $tag['tag_id']));
            }
        }

        return [
            'item'          => $model->find($itemId),
            'item_type'     => $itemType,
            'tag'           => $tag,
            'quantity'      => $quantity,
            'balance_after' => $balanceAfter,
            'mode'          => $plan['mode'],
        ];
    }

    /**
     * @return array{mode: string, quantity: float, registered_qty: float, current_qty: float, tag_id: ?int, epc_no: string}
     */
    private function resolveTagStockInQuantity(string $itemType, array $item, string $epcNo): array
    {
        $itemId   = (int) $item['id'];
        $epcNo    = strtoupper(trim($epcNo));
        $tagModel = new InventoryItemTagModel();
        $existing = $tagModel->findByEpc($epcNo);
        $label    = $itemType === 'product' ? 'product' : 'raw material';

        if ($existing && ($existing['status'] ?? '') === 'active') {
            if ($existing['item_type'] !== $itemType || (int) $existing['item_id'] !== $itemId) {
                throw new \RuntimeException('EPC tag is already registered to another item.');
            }

            $current    = normalize_inventory_qty((float) ($existing['quantity'] ?? 0));
            $registered = normalize_inventory_qty((float) ($existing['default_quantity'] ?? $existing['quantity'] ?? 0));

            if ($registered <= 0) {
                throw new \RuntimeException('This tag has no registered quantity. Set Qty per Tag on the ' . $label . ' first.');
            }
            if ($current >= $registered - 0.0000001) {
                throw new \RuntimeException('This tag is already fully stocked (' . format_inventory_qty($registered) . ').');
            }

            $quantity = normalize_inventory_qty($registered - $current);

            return [
                'mode'           => 'restore',
                'quantity'       => $quantity,
                'registered_qty' => $registered,
                'current_qty'    => $current,
                'tag_id'         => (int) $existing['id'],
                'epc_no'         => $epcNo,
            ];
        }

        $tagMode = $item['tag_mode'] ?? 'single';
        if ($tagMode === 'single' && $tagModel->countTagsForItem($itemType, $itemId) > 0) {
            throw new \RuntimeException('Single-tag mode: scan the existing tag to stock in, or switch to multi-tag mode.');
        }

        if ($this->isEpcUsedElsewhere($epcNo, $itemType, $itemId)) {
            throw new \RuntimeException('EPC tag is already registered to another item.');
        }

        $quantity = normalize_inventory_qty(max(0, (float) ($item['qty_per_tag'] ?? 0)));
        if ($quantity <= 0) {
            throw new \RuntimeException('Set Qty per Tag on the ' . $label . ' master first — that becomes the stock-in quantity.');
        }

        return [
            'mode'           => 'new',
            'quantity'       => $quantity,
            'registered_qty' => $quantity,
            'current_qty'    => 0.0,
            'tag_id'         => null,
            'epc_no'         => $epcNo,
        ];
    }

    /**
     * Align quantity_on_hand with the sum of active tag quantities. Does not write transactions.
     */
    public function syncBalanceFromTags(
        string $itemType,
        int $itemId,
        ?int $userId = null,
        ?string $notes = null
    ): float {
        $tagModel = new InventoryItemTagModel();
        $tagCount = $tagModel->countTagsForItem($itemType, $itemId);

        if ($tagCount === 0) {
            $item = $this->getItem($itemType, $itemId);

            return (float) ($item['quantity_on_hand'] ?? 0);
        }

        $total    = normalize_inventory_qty($tagModel->totalTagQuantity($itemType, $itemId));
        $item     = $this->getItem($itemType, $itemId);
        $previous = normalize_inventory_qty((float) ($item['quantity_on_hand'] ?? 0));

        if (abs($previous - $total) < 0.0000001) {
            return $total;
        }

        $model = $itemType === 'product' ? new ProductModel() : new RawMaterialModel();
        $model->update($itemId, ['quantity_on_hand' => $total]);

        return $total;
    }

    public function syncAllTaggedItemBalances(): void
    {
        $rows = (new InventoryItemTagModel())
            ->select('item_type, item_id')
            ->where('status', 'active')
            ->groupBy('item_type, item_id')
            ->findAll();

        foreach ($rows as $row) {
            $this->syncBalanceFromTags($row['item_type'], (int) $row['item_id']);
        }
    }

    public function itemHasActiveTags(string $itemType, int $itemId): bool
    {
        return (new InventoryItemTagModel())->countTagsForItem($itemType, $itemId) > 0;
    }

    public function updateTagDefaultQuantity(int $tagId, float $defaultQuantity): bool
    {
        if ($defaultQuantity < 0) {
            throw new \RuntimeException('Quantity cannot be negative.');
        }

        $defaultQuantity = normalize_inventory_qty($defaultQuantity);
        $tagModel        = new InventoryItemTagModel();
        $tag             = $tagModel->find($tagId);
        if (!$tag || ($tag['status'] ?? '') !== 'active') {
            return false;
        }

        return $tagModel->update($tagId, ['default_quantity' => $defaultQuantity]);
    }

    /**
     * @deprecated Stock quantity changes belong on the View page. Use updateTagDefaultQuantity for registered qty.
     */
    public function updateTagQuantity(int $tagId, float $quantity): bool
    {
        if ($quantity < 0) {
            throw new \RuntimeException('Quantity cannot be negative.');
        }

        $quantity = normalize_inventory_qty($quantity);

        $tagModel = new InventoryItemTagModel();
        $tag = $tagModel->find($tagId);
        if (!$tag || ($tag['status'] ?? '') !== 'active') {
            return false;
        }

        $oldQty = normalize_inventory_qty((float) ($tag['quantity'] ?? 0));
        if (!$tagModel->update($tagId, ['quantity' => $quantity])) {
            return false;
        }

        $delta = normalize_inventory_qty($quantity - $oldQty);
        if (abs($delta) >= 0.0000001) {
            $this->logItemMovement(
                $tag['item_type'],
                (int) $tag['item_id'],
                $delta > 0 ? 'stock_in' : 'stock_out',
                abs($delta),
                'web',
                null,
                null,
                null,
                'UHF tag qty updated: ' . ($tag['epc_no'] ?? '')
            );
        }
        $this->syncBalanceFromTags($tag['item_type'], (int) $tag['item_id']);

        return true;
    }

    public function removeTag(int $tagId): bool
    {
        $tagModel = new InventoryItemTagModel();
        $tag = $tagModel->find($tagId);
        if (!$tag) {
            return false;
        }

        $removedQty = (float) ($tag['quantity'] ?? 0);
        $tagModel->update($tagId, ['status' => 'inactive']);

        $removedEpc = $tag['epc_no'];
        $remaining  = $tagModel->getTagsForItem($tag['item_type'], (int) $tag['item_id']);

        $model = $tag['item_type'] === 'product' ? new ProductModel() : new RawMaterialModel();
        $item  = $model->find($tag['item_id']);
        if ($item && strtoupper((string) ($item['epc_no'] ?? '')) === strtoupper((string) $removedEpc)) {
            $model->update($tag['item_id'], [
                'epc_no' => !empty($remaining) ? $remaining[0]['epc_no'] : null,
            ]);
        }

        if ($removedQty > 0) {
            $this->logItemMovement(
                $tag['item_type'],
                (int) $tag['item_id'],
                'stock_out',
                $removedQty,
                'web',
                null,
                null,
                null,
                'UHF tag removed: ' . $removedEpc
            );
        }
        $this->syncBalanceFromTags($tag['item_type'], (int) $tag['item_id']);

        return true;
    }

    public function getTagsForItem(string $itemType, int $itemId): array
    {
        $tags = (new InventoryItemTagModel())->getTagsForItem($itemType, $itemId);

        return array_map(fn ($tag) => $this->formatTagPayload($tag), $tags);
    }

    public function isEpcUsedElsewhere(
        string $epcNo,
        string $itemType,
        int $itemId,
        ?int $alsoExcludeProductId = null,
        ?int $alsoExcludeMaterialId = null
    ): bool {
        $excludeProductId  = ($itemType === 'product' && $itemId > 0) ? $itemId : $alsoExcludeProductId;
        $excludeMaterialId = ($itemType === 'raw_material' && $itemId > 0) ? $itemId : $alsoExcludeMaterialId;

        if ((new ProductModel())->isEpcRegistered($epcNo, $excludeProductId)) {
            return true;
        }

        if ((new RawMaterialModel())->isEpcRegistered($epcNo, $excludeMaterialId)) {
            return true;
        }

        if ((new InventoryItemTagModel())->isEpcRegistered($epcNo)) {
            return true;
        }

        if ((new \App\Models\AssetModel())->isEpcRegistered($epcNo)) {
            return true;
        }

        return false;
    }

    private function clearItemEpcIfMatches(string $itemType, int $itemId, string $epcNo): void
    {
        $model = $itemType === 'product' ? new ProductModel() : new RawMaterialModel();
        $item  = $model->find($itemId);
        if ($item && strtoupper((string) ($item['epc_no'] ?? '')) === strtoupper($epcNo)) {
            $remaining = (new InventoryItemTagModel())->getTagsForItem($itemType, $itemId);
            $model->update($itemId, [
                'epc_no' => !empty($remaining) ? $remaining[0]['epc_no'] : null,
            ]);
        }
    }

    public function formatTagPayload(array $tag): array
    {
        $itemType = $tag['item_type'];
        $item = $this->getItem($itemType, (int) $tag['item_id']);
        if (!$item) {
            throw new \RuntimeException('Tagged item not found.');
        }

        $base = $this->formatItemPayload($itemType, $item);

        return array_merge($base, [
            'tag_id'                  => (int) $tag['id'],
            'tag_quantity'            => normalize_inventory_qty((float) ($tag['quantity'] ?? 0)),
            'tag_registered_quantity' => normalize_inventory_qty((float) ($tag['default_quantity'] ?? $tag['quantity'] ?? 0)),
            'tag_label'               => $tag['label'] ?? '',
            'epc_no'                  => $tag['epc_no'],
        ]);
    }

    public function stockIn(
        string $itemType,
        int $itemId,
        float $quantity,
        string $scanMethod = 'manual',
        ?string $scanReference = null,
        ?string $zoneId = null,
        ?int $userId = null,
        ?string $notes = null
    ): array {
        $quantity = normalize_inventory_qty($quantity);

        if ($this->itemHasActiveTags($itemType, $itemId)) {
            return $this->applyTagStockMovement($itemType, $itemId, 'stock_in', $quantity, $scanMethod, $scanReference, $zoneId, $userId, $notes);
        }

        return $this->applyMovement($itemType, $itemId, 'stock_in', $quantity, $scanMethod, $scanReference, $zoneId, $userId, $notes);
    }

    public function stockOut(
        string $itemType,
        int $itemId,
        float $quantity,
        string $scanMethod = 'manual',
        ?string $scanReference = null,
        ?string $zoneId = null,
        ?int $userId = null,
        ?string $notes = null
    ): array {
        $quantity = normalize_inventory_qty($quantity);

        if ($this->itemHasActiveTags($itemType, $itemId)) {
            return $this->applyTagStockMovement($itemType, $itemId, 'stock_out', $quantity, $scanMethod, $scanReference, $zoneId, $userId, $notes);
        }

        return $this->applyMovement($itemType, $itemId, 'stock_out', $quantity, $scanMethod, $scanReference, $zoneId, $userId, $notes);
    }

    public function getBalance(string $itemType, int $itemId): ?array
    {
        $item = $this->getItem($itemType, $itemId);
        if (!$item) {
            return null;
        }
        return $this->formatItemPayload($itemType, $item);
    }

    public function getTotalInventory(): array
    {
        $productQty = (new ProductModel())->selectSum('quantity_on_hand', 'total')->first()['total'] ?? 0;
        $materialQty = (new RawMaterialModel())->selectSum('quantity_on_hand', 'total')->first()['total'] ?? 0;

        return [
            'products_qty'     => (float) $productQty,
            'materials_qty'    => (float) $materialQty,
            'total_qty'        => (float) $productQty + (float) $materialQty,
            'products_count'   => (new ProductModel())->where('status', 'active')->countAllResults(),
            'materials_count'  => (new RawMaterialModel())->where('status', 'active')->countAllResults(),
        ];
    }

    public function getInventoryBreakdown(): array
    {
        $zones = [];
        foreach ((new ZoneModel())->findAll() as $zone) {
            $zones[$zone['zone_id']] = $zone['zone_name'];
        }

        $items = [];

        foreach ((new ProductModel())->where('status', 'active')->orderBy('product_code', 'ASC')->findAll() as $product) {
            $zoneId = $product['last_seen_zone'] ?? null;
            $items[] = [
                'type'          => 'product',
                'id'            => (int) $product['id'],
                'code'          => $product['product_code'],
                'name'          => $product['product_name'],
                'balance'       => (float) ($product['quantity_on_hand'] ?? 0),
                'current_zone'  => $zoneId ? ($zones[$zoneId] ?? $zoneId) : '—',
            ];
        }

        foreach ((new RawMaterialModel())->where('status', 'active')->orderBy('material_code', 'ASC')->findAll() as $material) {
            $zoneId = $material['last_seen_zone'] ?? null;
            $items[] = [
                'type'          => 'raw_material',
                'id'            => (int) $material['id'],
                'code'          => $material['material_code'],
                'name'          => $material['material_name'],
                'balance'       => (float) ($material['quantity_on_hand'] ?? 0),
                'current_zone'  => $zoneId ? ($zones[$zoneId] ?? $zoneId) : '—',
            ];
        }

        usort($items, static fn ($a, $b) => strcmp($a['code'], $b['code']));

        return $items;
    }

    public function startStockCheck(string $itemType, int $itemId, string $scanMethod, ?int $userId = null): array
    {
        $item = $this->getItem($itemType, $itemId);
        if (!$item) {
            throw new \RuntimeException('Item not found.');
        }

        $sessionModel = new StockCheckSessionModel();
        $sessionModel->insert([
            'item_type'        => $itemType,
            'item_id'          => $itemId,
            'scan_method'      => in_array($scanMethod, ['uhf', 'qr'], true) ? $scanMethod : 'qr',
            'status'           => 'in_progress',
            'expected_balance' => (float) ($item['quantity_on_hand'] ?? 0),
            'user_id'          => $userId,
        ]);

        $sessionId = (int) $sessionModel->getInsertID();

        return [
            'session_id'       => $sessionId,
            'item'             => $this->formatItemPayload($itemType, $item),
            'expected_balance' => (float) ($item['quantity_on_hand'] ?? 0),
            'scan_method'      => $scanMethod,
        ];
    }

    public function scanStockCheck(int $sessionId, ?string $epc = null, ?string $qrCode = null): array
    {
        $session = (new StockCheckSessionModel())->find($sessionId);
        if (!$session || $session['status'] !== 'in_progress') {
            throw new \RuntimeException('Stock check session not found or already completed.');
        }

        $lookup = $this->lookupByScan($epc, $qrCode);
        if (!$lookup) {
            throw new \RuntimeException('Scan did not match any inventory item.');
        }

        if ($lookup['type'] !== $session['item_type'] || (int) $lookup['id'] !== (int) $session['item_id']) {
            throw new \RuntimeException('Scanned item does not match this stock check.');
        }

        $reference = $epc ?: $qrCode;
        $method    = $epc ? 'uhf' : 'qr';

        (new StockCheckScanModel())->insert([
            'session_id'     => $sessionId,
            'scan_reference' => $reference,
            'scan_method'    => $method,
            'quantity'       => 1,
        ]);

        $counted = $this->sumSessionScans($sessionId);

        return [
            'session_id'       => $sessionId,
            'counted_balance'  => $counted,
            'expected_balance' => (float) $session['expected_balance'],
            'scan_reference'   => $reference,
        ];
    }

    public function completeStockCheck(int $sessionId, ?float $countedQuantity = null, ?string $notes = null, ?int $userId = null): array
    {
        $session = (new StockCheckSessionModel())->find($sessionId);
        if (!$session || $session['status'] !== 'in_progress') {
            throw new \RuntimeException('Stock check session not found or already completed.');
        }

        $counted  = $countedQuantity ?? $this->sumSessionScans($sessionId);
        $expected = (float) $session['expected_balance'];
        $variance = $counted - $expected;

        (new StockCheckSessionModel())->update($sessionId, [
            'status'          => 'completed',
            'counted_balance' => $counted,
            'variance'        => $variance,
            'notes'           => $notes,
            'completed_at'    => date('Y-m-d H:i:s'),
        ]);

        $stockOutList = [];
        if ($variance < 0) {
            $stockOutList = $this->getRecentStockOuts($session['item_type'], (int) $session['item_id'], 20);
        }

        if ($variance !== 0.0) {
            $this->applyMovement(
                $session['item_type'],
                (int) $session['item_id'],
                'stock_check_adjust',
                abs($variance),
                'manual',
                'stock_check:' . $sessionId,
                null,
                $userId,
                'Stock check variance adjustment',
                $sessionId,
                $variance > 0
            );
        }

        $item = $this->getItem($session['item_type'], (int) $session['item_id']);

        return [
            'session_id'       => $sessionId,
            'expected_balance' => $expected,
            'counted_balance'  => $counted,
            'variance'         => $variance,
            'balance_after'    => (float) ($item['quantity_on_hand'] ?? 0),
            'stock_out_list'   => $stockOutList,
            'scans'            => (new StockCheckScanModel())->where('session_id', $sessionId)->orderBy('id', 'ASC')->findAll(),
        ];
    }

    public function getTransactions(?string $startDate = null, ?string $endDate = null, int $limit = 100): array
    {
        $query = (new InventoryTransactionModel())->orderBy('created_at', 'DESC');
        if ($startDate) {
            $query->where('DATE(created_at) >=', $startDate);
        }
        if ($endDate) {
            $query->where('DATE(created_at) <=', $endDate);
        }
        $rows = $query->findAll($limit);

        return $this->formatTransactionRows($rows);
    }

    public function getItemTransactions(string $itemType, int $itemId, int $limit = 50): array
    {
        $fetchLimit = $this->itemHasActiveTags($itemType, $itemId) ? $limit * 4 : $limit;
        $rows = (new InventoryTransactionModel())
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->orderBy('created_at', 'DESC')
            ->findAll($fetchLimit);

        if ($this->itemHasActiveTags($itemType, $itemId)) {
            $rows = array_values(array_filter($rows, fn ($row) => !$this->isLegacySyncTransaction($row)));
            $rows = array_slice($rows, 0, $limit);
        }

        return $this->formatTransactionRows($rows);
    }

    public function getItemStockSummary(string $itemType, int $itemId): ?array
    {
        $item = $this->getItem($itemType, $itemId);
        if (!$item) {
            return null;
        }

        $tagDriven = $this->itemHasActiveTags($itemType, $itemId);
        $balance   = $tagDriven
            ? $this->syncBalanceFromTags($itemType, $itemId)
            : (float) ($item['quantity_on_hand'] ?? 0);

        $rows = (new InventoryTransactionModel())
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->findAll();

        $totalIn  = 0.0;
        $totalOut = 0.0;

        foreach ($rows as $row) {
            if ($tagDriven && $this->isLegacySyncTransaction($row)) {
                continue;
            }

            $qty = (float) $row['quantity'];
            if ($row['transaction_type'] === 'stock_in') {
                $totalIn += $qty;
            } elseif ($row['transaction_type'] === 'stock_out') {
                $totalOut += $qty;
            }
        }

        $unit = $item['unit'] ?? '';

        return [
            'total_stock_in'  => $totalIn,
            'total_stock_out' => $totalOut,
            'balance'         => $balance,
            'tag_driven'      => $tagDriven,
            'unit'            => $unit,
            'total_stock_in_fmt'  => format_inventory_qty($totalIn),
            'total_stock_out_fmt' => format_inventory_qty($totalOut),
            'balance_fmt'         => format_inventory_qty($balance),
        ];
    }

    /**
     * Items/tags currently seen outside their configured storage locations.
     *
     * Tagged items expand to one row per mismatched tag. Untagged items use the item last-seen zone.
     * Items with no storage locations configured are unrestricted and never appear here.
     *
     * @return list<array<string, mixed>>
     */
    public function getLocationMismatches(?string $typeFilter = null): array
    {
        $includeProducts  = $typeFilter === null || $typeFilter === 'product';
        $includeMaterials = $typeFilter === null || $typeFilter === 'raw_material';

        $zones = [];
        foreach ((new ZoneModel())->findAll() as $zone) {
            $zones[$zone['zone_id']] = $zone['zone_name'];
        }

        $tagsByKey = [];
        $allTags   = (new InventoryItemTagModel())
            ->where('status', 'active')
            ->orderBy('id', 'ASC')
            ->findAll();
        foreach ($allTags as $tag) {
            $key = $tag['item_type'] . ':' . $tag['item_id'];
            $tagsByKey[$key][] = $tag;
        }

        $rows = [];

        if ($includeProducts) {
            foreach ((new ProductModel())->where('status', 'active')->orderBy('product_name', 'ASC')->findAll() as $product) {
                $rows = array_merge($rows, $this->buildMismatchRowsForItem('product', $product, $tagsByKey, $zones));
            }
        }

        if ($includeMaterials) {
            foreach ((new RawMaterialModel())->where('status', 'active')->orderBy('material_name', 'ASC')->findAll() as $material) {
                $rows = array_merge($rows, $this->buildMismatchRowsForItem('raw_material', $material, $tagsByKey, $zones));
            }
        }

        usort($rows, static function ($a, $b) {
            $severity = ['High' => 0, 'Medium' => 1, 'Low' => 2];
            $pa = $severity[$a['alert_status']] ?? 9;
            $pb = $severity[$b['alert_status']] ?? 9;
            if ($pa !== $pb) {
                return $pa <=> $pb;
            }

            return ($b['detected_at_ts'] ?? 0) <=> ($a['detected_at_ts'] ?? 0);
        });

        return $rows;
    }

    /**
     * @param array<string, list<array>> $tagsByKey
     * @param array<string, string> $zones
     * @return list<array<string, mixed>>
     */
    private function buildMismatchRowsForItem(string $itemType, array $item, array $tagsByKey, array $zones): array
    {
        $allowedIds = ProductModel::decodeStorageLocations($item['storage_location'] ?? null);
        if ($allowedIds === [] && $itemType === 'raw_material' && !empty($item['warehouse_location'])) {
            $allowedIds = [(string) $item['warehouse_location']];
        }
        // No specific allowlist (or all zones) — no location mismatch to report.
        if ($allowedIds === [] || ProductModel::allowsAllZones($item['storage_location'] ?? null)) {
            return [];
        }

        $storeNames = [];
        foreach ($allowedIds as $zoneId) {
            $storeNames[] = $zones[$zoneId] ?? $zoneId;
        }
        $storeLocation = implode(', ', $storeNames);

        $itemId    = (int) $item['id'];
        $key       = $itemType . ':' . $itemId;
        $typeLabel = $itemType === 'product' ? 'Product' : 'Raw Material';
        $code      = $itemType === 'product' ? ($item['product_code'] ?? '') : ($item['material_code'] ?? '');
        $name      = $itemType === 'product' ? ($item['product_name'] ?? '') : ($item['material_name'] ?? '');
        $unit      = $item['unit'] ?? '';
        $lotNumber = $itemType === 'product' ? trim((string) ($item['lot_number'] ?? '')) : '';
        $tags      = $tagsByKey[$key] ?? [];
        $rows      = [];

        if ($tags !== []) {
            foreach ($tags as $tag) {
                $seenZoneId = trim((string) ($tag['last_seen_zone'] ?? ''));
                if ($seenZoneId === '' || in_array($seenZoneId, $allowedIds, true)) {
                    continue;
                }

                $batchLabel = trim((string) ($tag['label'] ?? ''));
                if ($batchLabel === '') {
                    $batchLabel = $lotNumber;
                }
                if ($batchLabel === '' && !empty($tag['epc_no'])) {
                    $epc = (string) $tag['epc_no'];
                    $batchLabel = strlen($epc) > 8 ? substr($epc, -8) : $epc;
                }

                $detectedAt = $tag['last_seen_at'] ?? $tag['updated_at'] ?? null;
                $rows[]     = $this->formatMismatchRow(
                    $itemType,
                    $itemId,
                    $typeLabel,
                    $code,
                    $name,
                    $unit,
                    $batchLabel,
                    (float) ($tag['quantity'] ?? 0),
                    $storeLocation,
                    $zones[$seenZoneId] ?? $seenZoneId,
                    $detectedAt,
                    (int) ($tag['id'] ?? 0)
                );
            }

            return $rows;
        }

        $seenZoneId = trim((string) ($item['last_seen_zone'] ?? ''));
        if ($seenZoneId === '' || in_array($seenZoneId, $allowedIds, true)) {
            return [];
        }

        $rows[] = $this->formatMismatchRow(
            $itemType,
            $itemId,
            $typeLabel,
            $code,
            $name,
            $unit,
            $lotNumber,
            (float) ($item['quantity_on_hand'] ?? 0),
            $storeLocation,
            $zones[$seenZoneId] ?? $seenZoneId,
            $item['last_seen_at'] ?? null,
            null
        );

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMismatchRow(
        string $itemType,
        int $itemId,
        string $typeLabel,
        string $code,
        string $name,
        string $unit,
        string $batchNumber,
        float $qty,
        string $storeLocation,
        string $mismatchLocation,
        ?string $detectedAt,
        ?int $tagId
    ): array {
        $ts = $detectedAt ? strtotime($detectedAt) : false;
        $ts = $ts !== false ? $ts : 0;
        $ageHours = $ts > 0 ? max(0, (time() - $ts) / 3600) : 0;

        if ($ageHours >= 24) {
            $alert = 'High';
        } elseif ($ageHours >= 4) {
            $alert = 'Medium';
        } else {
            $alert = 'Low';
        }

        return [
            'item_type'          => $itemType,
            'item_id'            => $itemId,
            'tag_id'             => $tagId,
            'type_label'         => $typeLabel,
            'code'               => $code,
            'name'               => $name,
            'unit'               => $unit,
            'batch_number'       => $batchNumber,
            'qty'                => normalize_inventory_qty($qty),
            'store_location'     => $storeLocation,
            'mismatch_location'  => $mismatchLocation,
            'detected_at'        => $ts > 0 ? date('d-M-y H:i', $ts) : '',
            'detected_at_ts'     => $ts,
            'alert_status'       => $alert,
            'age_hours'          => $ageHours,
        ];
    }

    /**
     * Batch-level stock ledger rows (Type, Code, Item, Batch, Start, In, Out, Running, Total, Last Txn).
     *
     * UHF-tagged items expand to one row per tag (batch). Untagged items use lot number as batch.
     *
     * @return list<array<string, mixed>>
     */
    public function getStockLedger(?string $typeFilter = null): array
    {
        $includeProducts  = $typeFilter === null || $typeFilter === 'product';
        $includeMaterials = $typeFilter === null || $typeFilter === 'raw_material';

        $products  = $includeProducts
            ? (new ProductModel())->where('status', 'active')->orderBy('product_name', 'ASC')->orderBy('product_code', 'ASC')->findAll()
            : [];
        $materials = $includeMaterials
            ? (new RawMaterialModel())->where('status', 'active')->orderBy('material_name', 'ASC')->orderBy('material_code', 'ASC')->findAll()
            : [];

        $tagsByKey = [];
        $allTags   = (new InventoryItemTagModel())
            ->where('status', 'active')
            ->orderBy('id', 'ASC')
            ->findAll();
        foreach ($allTags as $tag) {
            $key = $tag['item_type'] . ':' . $tag['item_id'];
            $tagsByKey[$key][] = $tag;
        }

        $txnByKey = $this->buildLedgerTransactionIndex();

        $rows = [];
        foreach ($products as $product) {
            $rows = array_merge($rows, $this->buildLedgerRowsForItem('product', $product, $tagsByKey, $txnByKey));
        }
        foreach ($materials as $material) {
            $rows = array_merge($rows, $this->buildLedgerRowsForItem('raw_material', $material, $tagsByKey, $txnByKey));
        }

        return $rows;
    }

    /**
     * @param array<string, list<array>> $tagsByKey
     * @param array<string, array{qty_in: float, qty_out: float, last_at: ?string}> $txnByKey
     * @return list<array<string, mixed>>
     */
    private function buildLedgerRowsForItem(string $itemType, array $item, array $tagsByKey, array $txnByKey): array
    {
        $itemId    = (int) $item['id'];
        $key       = $itemType . ':' . $itemId;
        $typeLabel = $itemType === 'product' ? 'Product' : 'Raw Material';
        $code      = $itemType === 'product' ? ($item['product_code'] ?? '') : ($item['material_code'] ?? '');
        $name      = $itemType === 'product' ? ($item['product_name'] ?? '') : ($item['material_name'] ?? '');
        $unit      = $item['unit'] ?? '';
        $lotNumber = $itemType === 'product' ? trim((string) ($item['lot_number'] ?? '')) : '';
        $tags      = $tagsByKey[$key] ?? [];
        $txn       = $txnByKey[$key] ?? ['qty_in' => 0.0, 'qty_out' => 0.0, 'last_at' => null];

        $batchRows = [];

        if ($tags !== []) {
            foreach ($tags as $tag) {
                $start   = normalize_inventory_qty((float) ($tag['default_quantity'] ?? $tag['quantity'] ?? 0));
                $running = normalize_inventory_qty((float) ($tag['quantity'] ?? 0));
                $delta   = normalize_inventory_qty($running - $start);
                $qtyIn   = $delta > 0 ? $delta : 0.0;
                $qtyOut  = $delta < 0 ? normalize_inventory_qty(abs($delta)) : 0.0;

                $batchLabel = trim((string) ($tag['label'] ?? ''));
                if ($batchLabel === '') {
                    $batchLabel = $lotNumber;
                }
                if ($batchLabel === '' && !empty($tag['epc_no'])) {
                    $epc = (string) $tag['epc_no'];
                    $batchLabel = strlen($epc) > 8 ? substr($epc, -8) : $epc;
                }

                $lastAt = $tag['updated_at'] ?? $tag['created_at'] ?? $txn['last_at'];

                $batchRows[] = [
                    'start_balance'   => $start,
                    'qty_in'          => $qtyIn,
                    'qty_out'         => $qtyOut,
                    'running_balance' => $running,
                    'batch_number'    => $batchLabel,
                    'last_at'         => $lastAt,
                    'tag_id'          => (int) $tag['id'],
                ];
            }
        } else {
            $qtyIn   = normalize_inventory_qty((float) $txn['qty_in']);
            $qtyOut  = normalize_inventory_qty((float) $txn['qty_out']);
            $running = normalize_inventory_qty((float) ($item['quantity_on_hand'] ?? 0));
            $start   = normalize_inventory_qty($running - $qtyIn + $qtyOut);
            if ($start < 0) {
                $start = 0.0;
            }

            $batchRows[] = [
                'start_balance'   => $start,
                'qty_in'          => $qtyIn,
                'qty_out'         => $qtyOut,
                'running_balance' => $running,
                'batch_number'    => $lotNumber,
                'last_at'         => $txn['last_at'],
                'tag_id'          => null,
            ];
        }

        $totalInventory = 0.0;
        foreach ($batchRows as $batch) {
            $totalInventory += $batch['running_balance'];
        }
        $totalInventory = normalize_inventory_qty($totalInventory);

        $rows      = [];
        $batchCount = count($batchRows);
        foreach ($batchRows as $index => $batch) {
            $lastAt = $batch['last_at'];
            $rows[] = [
                'item_type'           => $itemType,
                'item_id'             => $itemId,
                'type_label'          => $typeLabel,
                'code'                => $code,
                'name'                => $name,
                'unit'                => $unit,
                'batch_number'        => $batch['batch_number'],
                'start_balance'       => $batch['start_balance'],
                'qty_in'              => $batch['qty_in'],
                'qty_out'             => $batch['qty_out'],
                'running_balance'     => $batch['running_balance'],
                'total_inventory'     => $totalInventory,
                'show_total'          => $index === 0,
                'is_first_in_group'   => $index === 0,
                'is_last_in_group'    => $index === $batchCount - 1,
                'tag_id'              => $batch['tag_id'],
                'last_transaction_at' => $lastAt,
                'last_transaction'    => $lastAt ? date('d-M-y H:i', strtotime($lastAt)) : '',
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, array{qty_in: float, qty_out: float, last_at: ?string}>
     */
    private function buildLedgerTransactionIndex(): array
    {
        $rows = (new InventoryTransactionModel())
            ->orderBy('created_at', 'ASC')
            ->findAll();

        $index = [];
        foreach ($rows as $row) {
            if ($this->isLegacySyncTransaction($row)) {
                continue;
            }

            $key = $row['item_type'] . ':' . $row['item_id'];
            if (!isset($index[$key])) {
                $index[$key] = ['qty_in' => 0.0, 'qty_out' => 0.0, 'last_at' => null];
            }

            $qty = (float) $row['quantity'];
            if ($row['transaction_type'] === 'stock_in') {
                $index[$key]['qty_in'] += $qty;
            } elseif ($row['transaction_type'] === 'stock_out') {
                $index[$key]['qty_out'] += $qty;
            }

            $createdAt = $row['created_at'] ?? null;
            if ($createdAt && ($index[$key]['last_at'] === null || $createdAt > $index[$key]['last_at'])) {
                $index[$key]['last_at'] = $createdAt;
            }
        }

        return $index;
    }

    public function ensureQrCode(string $itemType, array $item): string
    {
        if (!empty($item['qr_code'])) {
            return $item['qr_code'];
        }

        $qr = $itemType === 'product'
            ? 'WW|P|' . $item['product_code'] . '|' . ($item['lot_number'] ?? '')
            : 'WW|RM|' . $item['material_code'];

        $model = $itemType === 'product' ? new ProductModel() : new RawMaterialModel();
        $model->update($item['id'], ['qr_code' => $qr]);

        return $qr;
    }

    private function applyMovement(
        string $itemType,
        int $itemId,
        string $transactionType,
        float $quantity,
        string $scanMethod,
        ?string $scanReference,
        ?string $zoneId,
        ?int $userId,
        ?string $notes,
        ?int $stockCheckSessionId = null,
        ?bool $stockCheckIncrease = null
    ): array {
        if ($quantity <= 0) {
            throw new \RuntimeException('Quantity must be greater than zero.');
        }

        $quantity = normalize_inventory_qty($quantity);

        $item = $this->getItem($itemType, $itemId);
        if (!$item) {
            throw new \RuntimeException('Item not found.');
        }

        if (in_array($transactionType, ['stock_check_adjust'], true)
            && $this->itemHasActiveTags($itemType, $itemId)) {
            throw new \RuntimeException(
                'Stock check adjustments are not supported for UHF-tagged items.'
            );
        }

        $current = normalize_inventory_qty((float) ($item['quantity_on_hand'] ?? 0));

        if ($transactionType === 'stock_check_adjust') {
            $newBalance = normalize_inventory_qty($stockCheckIncrease ? $current + $quantity : $current - $quantity);
            $txnType    = $stockCheckIncrease ? 'stock_in' : 'stock_out';
        } elseif ($transactionType === 'stock_in') {
            $newBalance = normalize_inventory_qty($current + $quantity);
            $txnType    = 'stock_in';
        } else {
            if ($quantity > $current + 0.0000001) {
                throw new \RuntimeException('Insufficient stock. Current balance: ' . format_inventory_qty($current));
            }
            $newBalance = normalize_inventory_qty($current - $quantity);
            $txnType    = 'stock_out';
        }

        $model = $itemType === 'product' ? new ProductModel() : new RawMaterialModel();
        $model->update($itemId, ['quantity_on_hand' => $newBalance]);

        $txnModel = new InventoryTransactionModel();
        $txnModel->insert([
            'item_type'              => $itemType,
            'item_id'                => $itemId,
            'transaction_type'       => $transactionType === 'stock_check_adjust' ? 'stock_check_adjust' : $txnType,
            'quantity'               => $quantity,
            'balance_after'          => $newBalance,
            'scan_method'            => $scanMethod,
            'scan_reference'         => $scanReference,
            'zone_id'                => $zoneId,
            'user_id'                => $userId,
            'notes'                  => $notes,
            'stock_check_session_id' => $stockCheckSessionId,
        ]);

        $updated = $this->getItem($itemType, $itemId);

        return [
            'transaction_id' => (int) $txnModel->getInsertID(),
            'item'           => $this->formatItemPayload($itemType, $updated),
            'quantity'       => $quantity,
            'balance_after'  => $newBalance,
            'transaction_type' => $txnType,
        ];
    }

    private function sumSessionScans(int $sessionId): float
    {
        $scans = (new StockCheckScanModel())->where('session_id', $sessionId)->findAll();
        $total = 0.0;
        foreach ($scans as $scan) {
            $total += (float) $scan['quantity'];
        }
        return $total;
    }

    private function getRecentStockOuts(string $itemType, int $itemId, int $limit = 20): array
    {
        $rows = (new InventoryTransactionModel())
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->whereIn('transaction_type', ['stock_out', 'stock_check_adjust'])
            ->orderBy('created_at', 'DESC')
            ->findAll($limit);

        return $this->formatTransactionRows($rows);
    }

    private function formatTransactionRows(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $productIds  = [];
        $materialIds = [];
        foreach ($rows as $row) {
            if ($row['item_type'] === 'product') {
                $productIds[] = $row['item_id'];
            } else {
                $materialIds[] = $row['item_id'];
            }
        }

        $products = [];
        if ($productIds) {
            foreach ((new ProductModel())->whereIn('id', array_unique($productIds))->findAll() as $p) {
                $products[$p['id']] = $p;
            }
        }
        $materials = [];
        if ($materialIds) {
            foreach ((new RawMaterialModel())->whereIn('id', array_unique($materialIds))->findAll() as $m) {
                $materials[$m['id']] = $m;
            }
        }

        $formatted = [];
        foreach ($rows as $row) {
            $isProduct = $row['item_type'] === 'product';
            $item      = $isProduct ? ($products[$row['item_id']] ?? null) : ($materials[$row['item_id']] ?? null);
            if (!$item) {
                continue;
            }

            $typeLabel = match ($row['transaction_type']) {
                'stock_in' => 'Stock In',
                'stock_out' => 'Stock Out',
                'stock_check_adjust' => 'Stock Check',
                default => ucfirst($row['transaction_type']),
            };

            $formatted[] = [
                'id'               => $row['id'],
                'item_type'        => $row['item_type'],
                'type_label'       => $isProduct ? 'Product' : 'Raw Material',
                'code'             => $isProduct ? $item['product_code'] : $item['material_code'],
                'name'             => $isProduct ? $item['product_name'] : $item['material_name'],
                'transaction_type' => $row['transaction_type'],
                'transaction_label'=> $typeLabel,
                'quantity'         => (float) $row['quantity'],
                'balance_after'    => (float) $row['balance_after'],
                'scan_method'      => $row['scan_method'],
                'scan_reference'   => $row['scan_reference'] ?? '',
                'notes'            => $row['notes'] ?? '',
                'created_at'       => $row['created_at'],
                'datetime'         => date('d M Y H:i', strtotime($row['created_at'])),
            ];
        }

        return $formatted;
    }

    private function applyTagStockMovement(
        string $itemType,
        int $itemId,
        string $transactionType,
        float $quantity,
        string $scanMethod,
        ?string $scanReference,
        ?string $zoneId,
        ?int $userId,
        ?string $notes
    ): array {
        if ($quantity <= 0) {
            throw new \RuntimeException('Quantity must be greater than zero.');
        }

        $quantity = normalize_inventory_qty($quantity);

        $tagModel = new InventoryItemTagModel();
        $tags     = $tagModel->getTagsForItem($itemType, $itemId);
        if (empty($tags)) {
            throw new \RuntimeException('No active UHF tags found on this item.');
        }

        if ($transactionType === 'stock_in') {
            $tag    = $this->pickTagForStockIn($tags);
            $newQty = normalize_inventory_qty((float) ($tag['quantity'] ?? 0) + $quantity);
            $tagModel->update((int) $tag['id'], ['quantity' => $newQty]);
        } else {
            $available = 0.0;
            foreach ($tags as $tag) {
                $available += normalize_inventory_qty((float) ($tag['quantity'] ?? 0));
            }
            $available = normalize_inventory_qty($available);
            if ($quantity > $available + 0.0000001) {
                throw new \RuntimeException('Insufficient stock. Current balance: ' . format_inventory_qty($available));
            }

            $remaining = $quantity;
            foreach ($tags as $tag) {
                if ($remaining <= 0.0000001) {
                    break;
                }
                $tagQty = normalize_inventory_qty((float) ($tag['quantity'] ?? 0));
                if ($tagQty <= 0) {
                    continue;
                }
                $deduct  = normalize_inventory_qty(min($remaining, $tagQty));
                $newQty  = normalize_inventory_qty($tagQty - $deduct);
                $tagModel->update((int) $tag['id'], ['quantity' => $newQty]);
                $remaining = normalize_inventory_qty($remaining - $deduct);
            }
        }

        $balance = $this->syncBalanceFromTags($itemType, $itemId);
        $this->logItemMovement(
            $itemType,
            $itemId,
            $transactionType,
            $quantity,
            $scanMethod,
            $scanReference,
            $zoneId,
            $userId,
            $notes
        );

        return [
            'quantity'      => $quantity,
            'balance_after' => $balance,
        ];
    }

    private function logItemMovement(
        string $itemType,
        int $itemId,
        string $transactionType,
        float $quantity,
        string $scanMethod = 'web',
        ?string $scanReference = null,
        ?string $zoneId = null,
        ?int $userId = null,
        ?string $notes = null
    ): void {
        if ($quantity <= 0) {
            return;
        }

        $quantity = normalize_inventory_qty($quantity);
        $balance  = $this->syncBalanceFromTags($itemType, $itemId);

        (new InventoryTransactionModel())->insert([
            'item_type'        => $itemType,
            'item_id'          => $itemId,
            'transaction_type' => $transactionType,
            'quantity'         => $quantity,
            'balance_after'    => $balance,
            'scan_method'      => $scanMethod,
            'scan_reference'   => $scanReference,
            'zone_id'          => $zoneId,
            'user_id'          => $userId,
            'notes'            => $notes,
        ]);
    }

    private function pickTagForStockIn(array $tags): array
    {
        $bestTag  = $tags[0];
        $bestRoom = -1.0;

        foreach ($tags as $tag) {
            $default = normalize_inventory_qty((float) ($tag['default_quantity'] ?? $tag['quantity'] ?? 0));
            $current = normalize_inventory_qty((float) ($tag['quantity'] ?? 0));
            $room    = $default > 0 ? ($default - $current) : PHP_FLOAT_MAX;

            if ($room > $bestRoom) {
                $bestRoom = $room;
                $bestTag  = $tag;
            }
        }

        return $bestTag;
    }

    private function isLegacySyncTransaction(array $row): bool
    {
        $notes = (string) ($row['notes'] ?? '');

        if ($notes === 'Balance synced from UHF tags') {
            return true;
        }

        if (in_array($notes, [
            'UHF tag quantity updated',
            'UHF tag removed',
            'UHF tag assigned',
            'UHF tag moved to another item',
        ], true)) {
            return true;
        }

        return (bool) preg_match('/^Zone OUT \(tag #\d+\)$/', $notes);
    }

    private function getItem(string $itemType, int $itemId): ?array
    {
        if ($itemType === 'product') {
            return (new ProductModel())->find($itemId);
        }
        if ($itemType === 'raw_material') {
            return (new RawMaterialModel())->find($itemId);
        }
        return null;
    }

    public function formatItemPayload(string $itemType, array $item): array
    {
        $qr = $this->ensureQrCode($itemType, $item);

        return [
            'type'             => $itemType,
            'type_label'       => $itemType === 'product' ? 'Product' : 'Raw Material',
            'id'               => (int) $item['id'],
            'code'             => $itemType === 'product' ? $item['product_code'] : $item['material_code'],
            'name'             => $itemType === 'product' ? $item['product_name'] : $item['material_name'],
            'unit'             => $item['unit'] ?? '',
            'lot_number'       => $itemType === 'product' ? ($item['lot_number'] ?? '') : '',
            'epc_no'           => $item['epc_no'] ?? '',
            'qr_code'          => $qr,
            'tag_mode'         => $item['tag_mode'] ?? 'single',
            'qty_per_tag'      => (float) ($item['qty_per_tag'] ?? 1),
            'tag_count'        => (new InventoryItemTagModel())->countTagsForItem($itemType, (int) $item['id']),
            'quantity_on_hand' => (float) ($item['quantity_on_hand'] ?? 0),
            'balance'          => (float) ($item['quantity_on_hand'] ?? 0),
        ];
    }
}
