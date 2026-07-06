<?php

namespace App\Controllers;

use App\Models\InventoryItemTagModel;
use App\Models\InventoryZoneRecordModel;
use App\Models\ProductModel;
use App\Models\RawMaterialModel;
use App\Models\ZoneModel;
use App\Services\InventoryStockService;

class Inventory extends BaseController
{
    public function monitoring()
    {
        $zoneModel        = new ZoneModel();
        $zones            = $zoneModel->where('status', 'active')->orderBy('zone_name', 'ASC')->findAll();
        $selectedZoneId   = $this->request->getGet('zone') ?: null;
        $selectedZoneName = null;

        if ($selectedZoneId) {
            $zone = $zoneModel->where('zone_id', $selectedZoneId)->first();
            $selectedZoneName = $zone['zone_name'] ?? null;
        }

        $dateRange = $this->resolveDateRange();
        $payload   = $this->buildMonitoringPayload($selectedZoneId, $dateRange);

        return view('inventory/monitoring', array_merge([
            'title'              => 'Inventory Monitoring',
            'user'               => $this->getLoggedInUser(),
            'zones'              => $zones,
            'selected_zone_id'   => $selectedZoneId,
            'selected_zone_name' => $selectedZoneName,
            'filter_label'       => $dateRange['filter_label'],
            'filter_type'        => $dateRange['filter_type'],
            'custom_date'        => $dateRange['custom_date'],
            'is_today'           => $dateRange['is_today'],
        ], $payload));
    }

    public function monitoringData()
    {
        $selectedZoneId = $this->request->getGet('zone') ?: null;
        $dateRange      = $this->resolveDateRange();
        $payload        = $this->buildMonitoringPayload($selectedZoneId, $dateRange);

        return $this->response->setJSON(array_merge([
            'success'      => true,
            'filter_label' => $dateRange['filter_label'],
            'is_today'     => $dateRange['is_today'],
        ], $payload));
    }

    public function itemDetail()
    {
        $type = $this->request->getGet('type');
        $id   = (int) ($this->request->getGet('id') ?? 0);

        if (!in_array($type, ['product', 'raw_material'], true) || $id <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid item.']);
        }

        $dateRange = $this->resolveDateRange();
        $zoneModel = new ZoneModel();
        $now       = time();
        $today     = date('Y-m-d');

        if ($type === 'product') {
            $item = (new ProductModel())->find($id);
            if (!$item) {
                return $this->response->setJSON(['success' => false, 'message' => 'Product not found.']);
            }
            $code    = $item['product_code'];
            $name    = $item['product_name'];
            $editUrl = base_url('products/edit/' . $id);
        } else {
            $item = (new RawMaterialModel())->find($id);
            if (!$item) {
                return $this->response->setJSON(['success' => false, 'message' => 'Raw material not found.']);
            }
            $code    = $item['material_code'];
            $name    = $item['material_name'];
            $editUrl = base_url('raw-materials/edit/' . $id);
        }

        $lastZoneName = null;
        if (!empty($item['last_seen_zone'])) {
            $lastZone = $zoneModel->where('zone_id', $item['last_seen_zone'])->first();
            $lastZoneName = $lastZone['zone_name'] ?? $item['last_seen_zone'];
        }

        $zoneNamesMap = [];
        foreach ($zoneModel->where('status', 'active')->findAll() as $z) {
            $zoneNamesMap[$z['zone_id']] = $z['zone_name'];
        }
        $storageRaw = $item['storage_location'] ?? null;
        if (($storageRaw === null || $storageRaw === '') && $type === 'raw_material' && !empty($item['warehouse_location'])) {
            $storageRaw = json_encode([(string) $item['warehouse_location']]);
        }
        $storageZonesLabel = ProductModel::storageLocationsLabel($storageRaw, $zoneNamesMap);

        $supplierList = [];
        $rawSuppliers = $item['suppliers'] ?? null;
        if (is_string($rawSuppliers) && $rawSuppliers !== '') {
            $decoded = json_decode($rawSuppliers, true);
            $supplierList = is_array($decoded) ? $decoded : [];
        } elseif (!empty($item['supplier_name'])) {
            $supplierList = [(string) $item['supplier_name']];
        }
        $suppliersLabel = $supplierList !== [] ? implode(', ', $supplierList) : '—';

        $fmtDate = static function ($d) {
            return !empty($d) ? date('d M Y', strtotime($d)) : '—';
        };
        $fmtMoney = static function ($v) {
            return $v !== null && $v !== '' ? 'RM ' . number_format((float) $v, 2) : '—';
        };
        $stockService  = new InventoryStockService();
        $stockSummary  = $stockService->getItemStockSummary($type, $id) ?? [];
        $stockTxns     = $stockService->getItemTransactions($type, $id, 20);

        $detailFields = [
            ['label' => 'Total Stock In', 'value' => $stockSummary['total_stock_in_fmt'] ?? '0'],
            ['label' => 'Total Stock Out', 'value' => $stockSummary['total_stock_out_fmt'] ?? '0'],
            ['label' => 'Balance', 'value' => $stockSummary['balance_fmt'] ?? '0'],
            ['label' => $type === 'product' ? 'Product Code' : 'Raw Material Code', 'value' => $code],
            ['label' => 'Name', 'value' => $name],
            ['label' => 'SAP Code', 'value' => $item['sap_code'] ?? '—'],
            ['label' => 'Unit', 'value' => $item['unit'] ?? '—'],
            ['label' => 'Shelf Life (Months)', 'value' => $item['shelf_life_months'] ?? '—'],
            ['label' => 'Expiry Date', 'value' => $fmtDate($item['expiry_date'] ?? null)],
            ['label' => 'Suppliers', 'value' => $suppliersLabel],
            ['label' => 'Allowed Zones', 'value' => $storageZonesLabel],
            ['label' => 'Cost Price', 'value' => $fmtMoney($item['cost_price'] ?? null)],
            ['label' => 'Selling Price', 'value' => $fmtMoney($item['selling_price'] ?? null)],
            ['label' => 'Status', 'value' => ucfirst($item['status'] ?? 'active')],
        ];

        $detailFields[] = ['label' => 'QR Code', 'value' => $item['qr_code'] ?? (new InventoryStockService())->ensureQrCode($type, $item)];

        $itemTags = $stockService->getTagsForItem($type, $id);
        $epcDisplay = !empty($itemTags)
            ? implode(', ', array_column($itemTags, 'epc_no'))
            : ($item['epc_no'] ?? '—');
        if ($epcDisplay === '') {
            $epcDisplay = '—';
        }
        $detailFields[] = ['label' => 'EPC Tag', 'value' => $epcDisplay];
        $detailFields[] = ['label' => 'Last Zone', 'value' => $lastZoneName ?? '—'];
        $detailFields[] = ['label' => 'Last Seen', 'value' => !empty($item['last_seen_at']) ? date('d M Y H:i', strtotime($item['last_seen_at'])) : '—'];
        $detailFields[] = ['label' => 'Registered', 'value' => $fmtDate($item['created_at'] ?? null)];

        $records = (new InventoryZoneRecordModel())
            ->where('item_type', $type)
            ->where('item_id', $id)
            ->where('date >=', $dateRange['start_date'])
            ->where('date <=', $dateRange['end_date'])
            ->orderBy('check_in_time', 'DESC')
            ->findAll();

        $zoneIds = array_unique(array_column($records, 'zone_id'));
        $zones   = [];
        if (!empty($zoneIds)) {
            foreach ($zoneModel->whereIn('zone_id', $zoneIds)->findAll() as $zone) {
                $zones[$zone['zone_id']] = $zone['zone_name'];
            }
        }

        $tagEpcs = [];
        $tagIds  = array_filter(array_unique(array_column($records, 'tag_id')));
        if ($tagIds) {
            foreach ((new \App\Models\InventoryItemTagModel())->whereIn('id', $tagIds)->findAll() as $tag) {
                $tagEpcs[(int) $tag['id']] = $tag['epc_no'];
            }
        }

        $scanRecords = [];
        foreach ($records as $record) {
            $isIn        = empty($record['check_out_time']);
            $checkInTs   = strtotime($record['check_in_time']);
            $canLive     = $isIn && $record['date'] === $today;
            $endTs       = $canLive ? $now : ($isIn ? $checkInTs : strtotime($record['check_out_time']));

            $scanRecords[] = [
                'zone_name'      => $zones[$record['zone_id']] ?? $record['zone_id'],
                'tag_epc'        => !empty($record['tag_id']) ? ($tagEpcs[(int) $record['tag_id']] ?? '') : '',
                'status'         => $isIn ? 'IN' : 'OUT',
                'presence_label' => $isIn ? 'In Zone' : 'Left Zone',
                'time_in'        => date('H:i:s', $checkInTs),
                'time_out'       => $isIn ? '—' : date('H:i:s', strtotime($record['check_out_time'])),
                'duration'       => $this->fmtDuration(max(0, $endTs - $checkInTs)),
                'date'           => date('d M Y', strtotime($record['date'])),
                'is_live'        => $canLive,
            ];
        }

        return $this->response->setJSON([
            'success'      => true,
            'type'         => $type,
            'type_label'   => $type === 'product' ? 'Product' : 'Raw Material',
            'item'         => [
                'id'             => $id,
                'code'           => $code,
                'name'           => $name,
                'category'       => $item['category'] ?? '—',
                'unit'           => $item['unit'] ?? '—',
                'description'    => $item['description'] ?? '',
                'status'         => $item['status'] ?? 'active',
                'epc_no'         => $item['epc_no'] ?? '',
                'balance'        => (float) ($item['quantity_on_hand'] ?? 0),
                'qr_code'        => $item['qr_code'] ?? '',
                'created_at'     => !empty($item['created_at']) ? date('d M Y', strtotime($item['created_at'])) : '—',
                'last_seen_at'   => !empty($item['last_seen_at']) ? date('d M Y H:i', strtotime($item['last_seen_at'])) : '—',
                'last_zone_name' => $lastZoneName ?? '—',
            ],
            'detail_fields' => $detailFields,
            'stock_summary' => $stockSummary,
            'stock_transactions' => $stockTxns,
            'scan_records' => $scanRecords,
            'filter_label' => $dateRange['filter_label'],
            'edit_url'     => $editUrl,
        ]);
    }

    private function resolveDateRange(): array
    {
        $filterType = $this->request->getGet('filter') ?? 'today';
        $customDate = $this->request->getGet('date');
        $today      = date('Y-m-d');

        $startDate   = $today;
        $endDate     = $today;
        $filterLabel = 'Today';

        if ($customDate) {
            $startDate   = $endDate = $customDate;
            $filterLabel = date('M d, Y', strtotime($customDate));
            $filterType  = 'custom';
        } else {
            switch ($filterType) {
                case 'yesterday':
                    $startDate   = $endDate = date('Y-m-d', strtotime('-1 day'));
                    $filterLabel = 'Yesterday';
                    break;
                case 'week':
                    $startDate   = date('Y-m-d', strtotime('monday this week'));
                    $endDate     = date('Y-m-d', strtotime('sunday this week'));
                    $filterLabel = 'This Week';
                    break;
                default:
                    $startDate   = $endDate = $today;
                    $filterLabel = 'Today';
                    $filterType  = 'today';
            }
        }

        return [
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'filter_label' => $filterLabel,
            'filter_type'  => $filterType,
            'custom_date'  => $customDate,
            'is_today'     => ($endDate === $today && $startDate === $today),
        ];
    }

    private function buildMonitoringPayload(?string $zoneId, array $dateRange): array
    {
        $recordModel = new InventoryZoneRecordModel();
        if ($dateRange['is_today']) {
            $recordModel->consolidateActiveSessionsForDate($dateRange['start_date']);
        }

        (new InventoryStockService())->syncAllTaggedItemBalances();

        $records = $this->getZoneRecords(
            $zoneId,
            $dateRange['start_date'],
            $dateRange['end_date']
        );

        $formattedLogs = $this->groupActivityLogsByItem($this->formatActivityLogs($records));
        $recentScans   = array_slice($formattedLogs, 0, 50);
        $sideItems     = $this->buildSideItemsFromRecords($records);
        $stats         = $this->computeStats($records);

        $totalZones = (new ZoneModel())->where('status', 'active')->countAllResults();

        return [
            'products'           => $sideItems['products'],
            'materials'          => $sideItems['materials'],
            'recent_scans'       => $recentScans,
            'all_scans'          => $formattedLogs,
            'stock_transactions' => (new InventoryStockService())->getTransactions(
                $dateRange['start_date'],
                $dateRange['end_date'],
                200
            ),
            'inventory_totals'     => (new InventoryStockService())->getTotalInventory(),
            'inventory_breakdown'  => (new InventoryStockService())->getInventoryBreakdown(),
            'stats'              => $stats,
            'active_readers' => $totalZones,
            'total_readers'  => $totalZones,
            'last_updated'   => date('H:i:s'),
            'server_time'    => time(),
        ];
    }

    private function getZoneRecords(?string $zoneId, string $startDate, string $endDate): array
    {
        $query = (new InventoryZoneRecordModel())
            ->where('date >=', $startDate)
            ->where('date <=', $endDate);

        if ($zoneId) {
            $query->where('zone_id', $zoneId);
        }

        return $query
            ->orderBy('COALESCE(check_out_time, check_in_time)', 'DESC', false)
            ->findAll();
    }

    private function computeStats(array $records): array
    {
        $productIds  = [];
        $materialIds = [];

        foreach ($records as $record) {
            if ($record['item_type'] === 'product') {
                $productIds[$record['item_id']] = true;
            } else {
                $materialIds[$record['item_id']] = true;
            }
        }

        return [
            'products'      => count($productIds),
            'materials'     => count($materialIds),
            'total'         => count($productIds) + count($materialIds),
            'scanned_today' => count($records),
            'inventory_qty' => (new InventoryStockService())->getTotalInventory()['total_qty'],
        ];
    }

    private function buildSideItemsFromRecords(array $records): array
    {
        $latestByKey = [];

        foreach ($records as $record) {
            $key = $record['item_type'] . ':' . $record['item_id'];
            $ts  = strtotime($record['check_in_time']);
            if (!isset($latestByKey[$key]) || $ts > strtotime($latestByKey[$key]['check_in_time'])) {
                $latestByKey[$key] = $record;
            }
        }

        if (empty($latestByKey)) {
            return ['products' => [], 'materials' => []];
        }

        $productIds  = [];
        $materialIds = [];

        foreach (array_keys($latestByKey) as $key) {
            [$type, $id] = explode(':', $key, 2);
            if ($type === 'product') {
                $productIds[] = (int) $id;
            } else {
                $materialIds[] = (int) $id;
            }
        }

        $zoneModel   = new ZoneModel();
        $productMap  = [];
        $materialMap = [];

        if ($productIds !== []) {
            foreach ((new ProductModel())->whereIn('id', $productIds)->findAll() as $p) {
                $productMap[$p['id']] = $p;
            }
        }

        if ($materialIds !== []) {
            foreach ((new RawMaterialModel())->whereIn('id', $materialIds)->findAll() as $m) {
                $materialMap[$m['id']] = $m;
            }
        }

        $products  = [];
        $materials = [];

        foreach ($latestByKey as $record) {
            $isProduct = $record['item_type'] === 'product';
            $item      = $isProduct
                ? ($productMap[$record['item_id']] ?? null)
                : ($materialMap[$record['item_id']] ?? null);

            if (!$item) {
                continue;
            }

            $currentZone = '—';
            if (!empty($item['last_seen_zone'])) {
                $zone        = $zoneModel->where('zone_id', $item['last_seen_zone'])->first();
                $currentZone = $zone['zone_name'] ?? $item['last_seen_zone'];
            }

            $entry = [
                'id'            => $record['item_id'],
                'product_code'  => $isProduct ? $item['product_code'] : null,
                'product_name'  => $isProduct ? $item['product_name'] : null,
                'material_code' => ! $isProduct ? $item['material_code'] : null,
                'material_name' => ! $isProduct ? $item['material_name'] : null,
                'current_zone'  => $currentZone,
                'balance'       => (float) ($item['quantity_on_hand'] ?? 0),
            ];

            if ($isProduct) {
                $products[] = $entry;
            } else {
                $materials[] = $entry;
            }
        }

        return ['products' => $products, 'materials' => $materials];
    }

    private function formatActivityLogs(array $records): array
    {
        if (empty($records)) {
            return [];
        }

        $productModel  = new ProductModel();
        $materialModel = new RawMaterialModel();
        $zoneModel     = new ZoneModel();
        $now           = time();
        $isToday       = true;

        $productIds  = [];
        $materialIds = [];
        $zoneIds     = [];

        foreach ($records as $record) {
            $zoneIds[] = $record['zone_id'];
            if ($record['item_type'] === 'product') {
                $productIds[] = $record['item_id'];
            } else {
                $materialIds[] = $record['item_id'];
            }
        }

        $products = [];
        if (!empty($productIds)) {
            foreach ($productModel->whereIn('id', array_unique($productIds))->findAll() as $p) {
                $products[$p['id']] = $p;
            }
        }

        $materials = [];
        if (!empty($materialIds)) {
            foreach ($materialModel->whereIn('id', array_unique($materialIds))->findAll() as $m) {
                $materials[$m['id']] = $m;
            }
        }

        $zones = [];
        if (!empty($zoneIds)) {
            foreach ($zoneModel->whereIn('zone_id', array_unique($zoneIds))->findAll() as $z) {
                $zones[$z['zone_id']] = $z['zone_name'];
            }
        }

        $tagEpcs = [];
        $tagIds  = array_filter(array_unique(array_column($records, 'tag_id')));
        if ($tagIds) {
            foreach ((new \App\Models\InventoryItemTagModel())->whereIn('id', $tagIds)->findAll() as $tag) {
                $tagEpcs[(int) $tag['id']] = $tag['epc_no'];
            }
        }

        $today     = date('Y-m-d');
        $formatted = [];

        $service = new InventoryStockService();

        foreach ($records as $record) {
            $isProduct = $record['item_type'] === 'product';
            $item      = $isProduct
                ? ($products[$record['item_id']] ?? null)
                : ($materials[$record['item_id']] ?? null);

            if (!$item) {
                continue;
            }

            if ($service->itemHasActiveTags($record['item_type'], (int) $record['item_id'])) {
                $service->syncBalanceFromTags($record['item_type'], (int) $record['item_id']);
                $item = $isProduct
                    ? ($productModel->find($record['item_id']) ?? $item)
                    : ($materialModel->find($record['item_id']) ?? $item);
            }

            $isIn        = empty($record['check_out_time']);
            $checkInTs   = strtotime($record['check_in_time']);

            $formatted[] = [
                'item_id'        => $record['item_id'],
                'type'           => $record['item_type'],
                'type_label'     => $isProduct ? 'Product' : 'Raw Material',
                'code'           => $isProduct ? $item['product_code'] : $item['material_code'],
                'name'           => $isProduct ? $item['product_name'] : $item['material_name'],
                'balance'        => (float) ($item['quantity_on_hand'] ?? 0),
                'zone_name'      => $zones[$record['zone_id']] ?? $record['zone_id'],
                'tag_epc'        => !empty($record['tag_id']) ? ($tagEpcs[(int) $record['tag_id']] ?? '') : '',
                'status'         => $isIn ? 'IN' : 'OUT',
                'presence_label' => $isIn ? 'In Zone' : 'Left Zone',
                'time_in'        => date('H:i:s', $checkInTs),
                'time_out'       => $isIn ? '—' : date('H:i:s', strtotime($record['check_out_time'])),
                'check_in_ts'    => $checkInTs,
                'check_out_ts'   => $isIn ? null : strtotime($record['check_out_time']),
                'record_date'    => $record['date'],
                'view_url'       => $isProduct
                    ? base_url('products/view/' . $item['id'])
                    : base_url('raw-materials/view/' . $item['id']),
            ];
        }

        return $formatted;
    }

    /**
     * Merge zone activity rows that share the same product/raw material and zone.
     */
    private function groupActivityLogsByItem(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $groups = [];

        foreach ($rows as $row) {
            $key = ($row['type'] ?? '') . ':' . ($row['item_id'] ?? 0) . ':' . ($row['zone_name'] ?? '');

            if (!isset($groups[$key])) {
                $groups[$key] = $row;
                $groups[$key]['tags'] = [];
                unset($groups[$key]['tag_epc']);
            }

            $tag = [
                'epc'           => $row['tag_epc'] ?? '',
                'status'        => $row['status'],
                'time_in'       => $row['time_in'],
                'time_out'      => $row['time_out'],
                'check_in_ts'   => $row['check_in_ts'],
                'check_out_ts'  => $row['check_out_ts'] ?? null,
            ];

            if ($tag['epc'] !== '') {
                $groups[$key]['tags'][$tag['epc']] = $tag;
            } else {
                $groups[$key]['tags'][] = $tag;
            }
        }

        $result = [];

        foreach ($groups as $group) {
            $tags = array_values($group['tags']);
            $inTags = array_values(array_filter($tags, static fn ($t) => ($t['status'] ?? '') === 'IN'));

            if ($inTags !== []) {
                $group['status'] = 'IN';
                $group['check_in_ts'] = min(array_column($inTags, 'check_in_ts'));
                $group['time_in'] = date('H:i:s', $group['check_in_ts']);
                $group['time_out'] = '—';
                $group['presence_label'] = count($tags) > 1
                    ? 'In Zone (' . count($inTags) . '/' . count($tags) . ' tags)'
                    : 'In Zone';
            } else {
                $group['status'] = 'OUT';
                $group['presence_label'] = 'Left Zone';
                $group['check_in_ts'] = min(array_column($tags, 'check_in_ts'));
                $group['time_in'] = date('H:i:s', $group['check_in_ts']);
                $outTs = array_filter(array_column($tags, 'check_out_ts'));
                $group['check_out_ts'] = $outTs ? max($outTs) : null;
                $group['time_out'] = $group['check_out_ts'] ? date('H:i:s', $group['check_out_ts']) : '—';
            }

            $group['tags'] = $tags;
            unset($group['tag_epc']);
            $result[] = $group;
        }

        usort($result, static fn ($a, $b) => ($b['check_in_ts'] ?? 0) <=> ($a['check_in_ts'] ?? 0));

        return $result;
    }

    public function stockCheck()
    {
        $products  = (new ProductModel())->where('status', 'active')->orderBy('product_name')->findAll();
        $materials = (new RawMaterialModel())->where('status', 'active')->orderBy('material_name')->findAll();

        return view('inventory/stock_check', [
            'title'     => 'Stock Check',
            'user'      => $this->getLoggedInUser(),
            'products'  => $products,
            'materials' => $materials,
        ]);
    }

    public function searchStock()
    {
        $prefill = trim((string) ($this->request->getGet('q') ?? ''));

        return view('inventory/search_stock', [
            'title'   => 'Search Stock',
            'user'    => $this->getLoggedInUser(),
            'prefill' => $prefill,
        ]);
    }

    public function searchStockLookup()
    {
        $epc    = strtoupper(trim((string) ($this->request->getGet('epc') ?? '')));
        $qrCode = trim((string) ($this->request->getGet('qr_code') ?? ''));

        if ($epc === '' && $qrCode === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Scan a UHF tag or QR code.']);
        }

        $service = new InventoryStockService();
        $match   = $service->lookupByScan($epc !== '' ? $epc : null, $qrCode !== '' ? $qrCode : null);

        if (!$match) {
            return $this->response->setJSON(['success' => false, 'message' => 'No item found for this scan.']);
        }

        $type = $match['type'];
        $id   = (int) $match['id'];

        if ($service->itemHasActiveTags($type, $id)) {
            $service->syncBalanceFromTags($type, $id);
        }

        $payload = $this->buildSearchStockPayload($type, $id, $epc !== '' ? $epc : null, $qrCode !== '' ? $qrCode : null);

        return $this->response->setJSON(array_merge(['success' => true], $payload));
    }

    public function searchStockScans()
    {
        $since = (float) ($this->request->getGet('since') ?? 0);

        return $this->response->setJSON([
            'success' => true,
            'scans'   => \App\Libraries\RfidLookupQueue::since($since),
        ]);
    }

    public function finderSearch()
    {
        $query = trim((string) ($this->request->getGet('q') ?? ''));

        if ($query === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Enter tag, batch code, or item name.']);
        }

        $payload = (new InventoryStockService())->findStockByQuery($query);

        return $this->response->setJSON([
            'success' => true,
            'query'   => $payload['query'],
            'results' => $payload['results'],
        ]);
    }

    public function stockLedger()
    {
        $typeFilter = $this->resolveStockLedgerTypeFilter();
        $payload    = $this->buildStockLedgerPayload($typeFilter);

        return view('inventory/stock_ledger', [
            'title'       => 'Inventory Dashboard',
            'user'        => $this->getLoggedInUser(),
            'rows'        => $payload['rows'],
            'type_filter' => $typeFilter,
            'grand_total' => $payload['grand_total'],
            'item_count'  => $payload['item_count'],
            'as_of_date'  => date('j-M-y H:i'),
        ]);
    }

    public function stockLedgerData()
    {
        $typeFilter = $this->resolveStockLedgerTypeFilter();
        $payload    = $this->buildStockLedgerPayload($typeFilter);

        return $this->response->setJSON(array_merge([
            'success'      => true,
            'last_updated' => date('j-M-y H:i'),
            'server_time'  => time(),
            'as_of_date'   => date('j-M-y H:i'),
        ], $payload));
    }

    private function resolveStockLedgerTypeFilter(): ?string
    {
        $typeFilter = $this->request->getGet('type');

        return in_array($typeFilter, ['product', 'raw_material'], true) ? $typeFilter : null;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, grand_total: float, item_count: int, row_count: int}
     */
    private function buildStockLedgerPayload(?string $typeFilter): array
    {
        $rows       = (new InventoryStockService())->getStockLedger($typeFilter);
        $grandTotal = 0.0;
        $itemCount  = 0;

        foreach ($rows as $row) {
            if (!empty($row['show_product_info'])) {
                $itemCount++;
            }
            if (!empty($row['show_total'])) {
                $grandTotal += (float) $row['total_inventory'];
            }
        }

        return [
            'rows'        => $rows,
            'grand_total' => $grandTotal,
            'item_count'  => $itemCount,
            'row_count'   => count($rows),
        ];
    }

    public function locationMismatch()
    {
        $typeFilter  = $this->resolveLocationMismatchTypeFilter();
        $alertFilter = $this->resolveLocationMismatchAlertFilter();
        $payload     = $this->buildLocationMismatchPayload($typeFilter, $alertFilter);

        return view('inventory/location_mismatch', array_merge([
            'title'        => 'Inventory Location Mismatch Monitoring',
            'user'         => $this->getLoggedInUser(),
            'type_filter'  => $typeFilter,
            'alert_filter' => $alertFilter,
            'as_of_date'   => date('j-M-y H:i'),
        ], $payload));
    }

    public function locationMismatchData()
    {
        $typeFilter  = $this->resolveLocationMismatchTypeFilter();
        $alertFilter = $this->resolveLocationMismatchAlertFilter();
        $payload     = $this->buildLocationMismatchPayload($typeFilter, $alertFilter);

        return $this->response->setJSON(array_merge([
            'success'      => true,
            'last_updated' => date('j-M-y H:i'),
            'server_time'  => time(),
            'as_of_date'   => date('j-M-y H:i'),
        ], $payload));
    }

    private function resolveLocationMismatchTypeFilter(): ?string
    {
        $typeFilter = $this->request->getGet('type');

        return in_array($typeFilter, ['product', 'raw_material'], true) ? $typeFilter : null;
    }

    private function resolveLocationMismatchAlertFilter(): ?string
    {
        $alertFilter = $this->request->getGet('alert');

        return in_array($alertFilter, ['Low', 'Medium', 'High'], true) ? $alertFilter : null;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, alert_counts: array<string, int>, total: int}
     */
    private function buildLocationMismatchPayload(?string $typeFilter, ?string $alertFilter): array
    {
        $rows = (new InventoryStockService())->getLocationMismatches($typeFilter);

        $alertCounts = ['High' => 0, 'Medium' => 0, 'Low' => 0];
        foreach ($rows as $row) {
            $status = $row['alert_status'] ?? '';
            if (isset($alertCounts[$status])) {
                $alertCounts[$status]++;
            }
        }

        if ($alertFilter !== null) {
            $rows = array_values(array_filter(
                $rows,
                static fn ($row) => ($row['alert_status'] ?? '') === $alertFilter
            ));
        }

        return [
            'rows'         => $rows,
            'alert_counts' => $alertCounts,
            'total'        => count($rows),
        ];
    }

    public function tagStockIn()
    {
        $service  = new InventoryStockService();
        $products = (new ProductModel())
            ->where('status', 'active')
            ->orderBy('product_name', 'ASC')
            ->findAll();
        $materials = (new RawMaterialModel())
            ->where('status', 'active')
            ->orderBy('material_name', 'ASC')
            ->findAll();

        $items = [];
        foreach ($products as $product) {
            $items[] = $this->formatTagStockInItem('product', $product, $service->getTagsForItem('product', (int) $product['id']));
        }
        foreach ($materials as $material) {
            $items[] = $this->formatTagStockInItem('raw_material', $material, $service->getTagsForItem('raw_material', (int) $material['id']));
        }

        usort($items, static fn ($a, $b) => strcmp($a['name'], $b['name']));

        return view('inventory/tag_stock_in', [
            'title' => 'Tag + Stock In',
            'user'  => $this->getLoggedInUser(),
            'items' => $items,
        ]);
    }

    public function tagStockInItem()
    {
        $type = (string) ($this->request->getGet('type') ?? '');
        $id   = (int) ($this->request->getGet('id') ?? 0);

        if (!in_array($type, ['product', 'raw_material'], true) || $id <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid item.']);
        }

        $model = $type === 'product' ? new ProductModel() : new RawMaterialModel();
        $row   = $model->find($id);
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item not found.']);
        }

        $service = new InventoryStockService();
        if ($service->itemHasActiveTags($type, $id)) {
            $service->syncBalanceFromTags($type, $id);
            $row = $model->find($id);
        }

        return $this->response->setJSON([
            'success' => true,
            'item'    => $this->formatTagStockInItem($type, $row, $service->getTagsForItem($type, $id)),
        ]);
    }

    public function tagStockInPreview()
    {
        $type  = (string) ($this->request->getGet('type') ?? '');
        $id    = (int) ($this->request->getGet('id') ?? 0);
        $epcNo = trim((string) ($this->request->getGet('epc_no') ?? ''));

        if (!in_array($type, ['product', 'raw_material'], true) || $id <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Select a product or raw material.']);
        }

        try {
            $plan = (new InventoryStockService())->previewTagStockIn($type, $id, $epcNo);

            return $this->response->setJSON([
                'success'        => true,
                'mode'           => $plan['mode'],
                'epc_no'         => $plan['epc_no'],
                'registered_qty' => $plan['registered_qty'],
                'current_qty'    => $plan['current_qty'],
                'max_stock_in'   => $plan['max_stock_in'],
            ]);
        } catch (\RuntimeException $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function tagStockInSubmit()
    {
        $type      = (string) ($this->request->getPost('type') ?? '');
        $id        = (int) ($this->request->getPost('id') ?? 0);
        $batchCode        = trim((string) ($this->request->getPost('batch_code') ?? ''));
        $epcNo            = trim((string) ($this->request->getPost('epc_no') ?? ''));
        $registeredQty    = (float) ($this->request->getPost('registered_quantity') ?? 0);
        $stockInQty       = (float) ($this->request->getPost('stock_in_quantity') ?? 0);
        $storageZoneId    = trim((string) ($this->request->getPost('storage_zone_id') ?? ''));

        if (!in_array($type, ['product', 'raw_material'], true) || $id <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Select a product or raw material.']);
        }
        if ($epcNo === '' || strlen($epcNo) < 4) {
            return $this->response->setJSON(['success' => false, 'message' => 'Scan or enter a valid UHF EPC tag.']);
        }
        if ($storageZoneId === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Scan at the lookup desk (LOOKUP antenna) to set storage location.']);
        }

        try {
            $result = (new InventoryStockService())->tagAndStockIn(
                $type,
                $id,
                $epcNo,
                $registeredQty,
                $stockInQty,
                $batchCode !== '' ? $batchCode : null,
                (int) session()->get('id') ?: null,
                $storageZoneId
            );

            $item = $result['item'];
            $tag  = $result['tag'];
            $qty  = (float) $result['quantity'];
            $loc  = $result['storage_zone_name'] ?? '';
            $message = ($result['mode'] ?? '') === 'existing'
                ? 'Confirmed — stocked in ' . format_inventory_qty($qty) . ' on existing tag.'
                : 'Confirmed — tag assigned (registered ' . format_inventory_qty($result['registered_qty'] ?? 0) . ') and stocked in ' . format_inventory_qty($qty) . '.';
            if ($loc !== '') {
                $message .= ' Stored at ' . $loc . '.';
            }

            $service = new InventoryStockService();

            return $this->response->setJSON([
                'success'       => true,
                'message'       => $message,
                'quantity'      => $result['quantity'],
                'balance_after' => $result['balance_after'],
                'mode'          => $result['mode'] ?? 'new',
                'item'          => $this->formatTagStockInItem($type, $item, $service->getTagsForItem($type, $id)),
                'tag'               => $tag,
                'storage_zone_name' => $result['storage_zone_name'] ?? null,
            ]);
        } catch (\RuntimeException $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function formatTagStockInItem(string $type, array $row, array $tags = []): array
    {
        $isProduct = $type === 'product';
        $storageRaw = $row['storage_location'] ?? null;
        if (($storageRaw === null || $storageRaw === '') && $type === 'raw_material' && !empty($row['warehouse_location'])) {
            $storageRaw = json_encode([(string) $row['warehouse_location']]);
        }
        $allowedIds = ProductModel::decodeStorageLocations($storageRaw);
        if (in_array(ProductModel::STORAGE_ALL_ZONES, $allowedIds, true)) {
            $allowedIds = [];
        }

        return [
            'type'               => $type,
            'id'                 => (int) $row['id'],
            'code'               => $isProduct ? ($row['product_code'] ?? '') : ($row['material_code'] ?? ''),
            'name'               => $isProduct ? ($row['product_name'] ?? '') : ($row['material_name'] ?? ''),
            'sap_code'           => $row['sap_code'] ?? '',
            'unit'               => $row['unit'] ?? '',
            'tag_mode'           => $row['tag_mode'] ?? 'single',
            'qty_per_tag'        => (float) ($row['qty_per_tag'] ?? 0),
            'quantity_on_hand'   => (float) ($row['quantity_on_hand'] ?? 0),
            'tags'               => $tags,
            'allows_all_zones'   => ProductModel::allowsAllZones($storageRaw),
            'allowed_zone_ids'   => $allowedIds,
        ];
    }

    public function stockCheckStart()
    {
        $type   = $this->request->getPost('item_type');
        $id     = (int) $this->request->getPost('item_id');
        $method = $this->request->getPost('scan_method') ?: 'qr';

        try {
            $result = (new InventoryStockService())->startStockCheck(
                $type,
                $id,
                $method,
                (int) session()->get('id')
            );
            return $this->response->setJSON(['success' => true] + $result);
        } catch (\RuntimeException $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function stockCheckScan()
    {
        $sessionId = (int) $this->request->getPost('session_id');
        $epc       = $this->request->getPost('epc');
        $qrCode    = $this->request->getPost('qr_code');

        try {
            $result = (new InventoryStockService())->scanStockCheck($sessionId, $epc ?: null, $qrCode ?: null);
            return $this->response->setJSON(['success' => true] + $result);
        } catch (\RuntimeException $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function stockCheckComplete()
    {
        $sessionId = (int) $this->request->getPost('session_id');
        $counted   = $this->request->getPost('counted_quantity');
        $notes     = $this->request->getPost('notes');

        try {
            $result = (new InventoryStockService())->completeStockCheck(
                $sessionId,
                $counted !== null && $counted !== '' ? (float) $counted : null,
                $notes,
                (int) session()->get('id')
            );
            return $this->response->setJSON(['success' => true] + $result);
        } catch (\RuntimeException $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function stockMovement()
    {
        $direction = $this->request->getPost('direction');
        $type      = $this->request->getPost('item_type');
        $id        = (int) $this->request->getPost('item_id');
        $quantity  = (float) $this->request->getPost('quantity');
        $epc       = $this->request->getPost('epc');
        $qrCode    = $this->request->getPost('qr_code');
        $notes     = $this->request->getPost('notes');

        $service = new InventoryStockService();
        if ($epc || $qrCode) {
            $lookup = $service->lookupByScan($epc ?: null, $qrCode ?: null);
            if (!$lookup) {
                return $this->response->setJSON(['success' => false, 'message' => 'Scanned item not found.']);
            }
            $type = $lookup['type'];
            $id   = $lookup['id'];
        }

        $method    = $epc ? 'uhf' : ($qrCode ? 'qr' : 'web');
        $reference = $epc ?: $qrCode;

        try {
            $result = $direction === 'out'
                ? $service->stockOut($type, $id, $quantity, $method, $reference, null, (int) session()->get('id'), $notes)
                : $service->stockIn($type, $id, $quantity, $method, $reference, null, (int) session()->get('id'), $notes);
            return $this->response->setJSON(['success' => true] + $result);
        } catch (\RuntimeException $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function buildSearchStockPayload(string $type, int $id, ?string $scannedEpc = null, ?string $scannedQr = null): array
    {
        $zoneModel = new ZoneModel();
        $today     = date('Y-m-d');
        $now       = time();

        if ($type === 'product') {
            $item = (new ProductModel())->find($id);
            if (!$item) {
                return ['message' => 'Product not found.'];
            }
            $code    = $item['product_code'];
            $name    = $item['product_name'];
            $editUrl = base_url('products/edit/' . $id);
            $viewUrl = base_url('products/view/' . $id);
        } else {
            $item = (new RawMaterialModel())->find($id);
            if (!$item) {
                return ['message' => 'Raw material not found.'];
            }
            $code    = $item['material_code'];
            $name    = $item['material_name'];
            $editUrl = base_url('raw-materials/edit/' . $id);
            $viewUrl = base_url('raw-materials/view/' . $id);
        }

        $lastZoneName = null;
        if (!empty($item['last_seen_zone'])) {
            $lastZone = $zoneModel->where('zone_id', $item['last_seen_zone'])->first();
            $lastZoneName = $lastZone['zone_name'] ?? $item['last_seen_zone'];
        }

        $zoneNamesMap = [];
        foreach ($zoneModel->where('status', 'active')->findAll() as $z) {
            $zoneNamesMap[$z['zone_id']] = $z['zone_name'];
        }

        $storageRaw = $item['storage_location'] ?? null;
        if (($storageRaw === null || $storageRaw === '') && $type === 'raw_material' && !empty($item['warehouse_location'])) {
            $storageRaw = json_encode([(string) $item['warehouse_location']]);
        }
        $storageZonesLabel = ProductModel::storageLocationsLabel($storageRaw, $zoneNamesMap);

        $supplierList = [];
        $rawSuppliers = $item['suppliers'] ?? null;
        if (is_string($rawSuppliers) && $rawSuppliers !== '') {
            $decoded = json_decode($rawSuppliers, true);
            $supplierList = is_array($decoded) ? $decoded : [];
        } elseif (!empty($item['supplier_name'])) {
            $supplierList = [(string) $item['supplier_name']];
        }
        $suppliersLabel = $supplierList !== [] ? implode(', ', $supplierList) : '—';

        $fmtDate  = static fn ($d) => !empty($d) ? date('d M Y', strtotime($d)) : '—';
        $fmtMoney = static fn ($v) => $v !== null && $v !== '' ? 'RM ' . number_format((float) $v, 2) : '—';

        $stockService = new InventoryStockService();
        $stockSummary = $stockService->getItemStockSummary($type, $id) ?? [];
        $productStockSummary = $stockSummary;
        $stockTxns    = $stockService->getItemTransactions($type, $id, 15);
        $tags         = $stockService->getTagsForItem($type, $id);

        $scannedTag = null;
        if ($scannedEpc) {
            foreach ($tags as $tag) {
                if (strcasecmp($tag['epc_no'] ?? '', $scannedEpc) === 0) {
                    $scannedTag = $tag;
                    break;
                }
            }
        }

        $tagScoped = $scannedTag !== null && $scannedEpc !== '';

        if ($tagScoped) {
            $tagQty = normalize_inventory_qty((float) ($scannedTag['tag_quantity'] ?? 0));
            $regQty = normalize_inventory_qty((float) ($scannedTag['tag_registered_quantity'] ?? 0));
            $delta  = normalize_inventory_qty($tagQty - $regQty);
            $qtyIn  = $delta > 0 ? $delta : 0.0;
            $qtyOut = $delta < 0 ? normalize_inventory_qty(abs($delta)) : 0.0;

            $stockSummary = [
                'balance'             => $tagQty,
                'balance_fmt'         => format_inventory_qty($tagQty),
                'total_stock_in'      => $qtyIn,
                'total_stock_out'     => $qtyOut,
                'total_stock_in_fmt'  => format_inventory_qty($qtyIn),
                'total_stock_out_fmt' => format_inventory_qty($qtyOut),
                'registered_qty'      => $regQty,
                'registered_qty_fmt'  => format_inventory_qty($regQty),
                'tag_driven'          => true,
                'scoped_to_tag'       => true,
                'unit'                => $item['unit'] ?? '',
            ];

            $tags = [$scannedTag];

            $stockTxns = array_values(array_filter($stockTxns, static function ($txn) use ($scannedEpc) {
                $notes = (string) ($txn['notes'] ?? '');
                $ref   = (string) ($txn['scan_reference'] ?? '');

                return stripos($notes, $scannedEpc) !== false
                    || strcasecmp($ref, $scannedEpc) === 0;
            }));
        }

        $stockStatus = $this->resolveStockStatus($scannedTag, $stockSummary, $tags);

        $tagPresence = null;
        if ($scannedTag && !empty($scannedTag['tag_id'])) {
            $tagPresence = $this->getTagZonePresence((int) $scannedTag['tag_id'], $zoneModel);
        }

        $detailFields = [
            ['label' => 'Type', 'value' => $type === 'product' ? 'Product' : 'Raw Material'],
            ['label' => 'Code', 'value' => $code],
            ['label' => 'Name', 'value' => $name],
            ['label' => 'Description', 'value' => trim((string) ($item['description'] ?? '')) ?: '—'],
            ['label' => 'SAP Code', 'value' => $item['sap_code'] ?? '—'],
            ['label' => 'Unit', 'value' => $item['unit'] ?? '—'],
            ['label' => 'Tag Mode', 'value' => ucfirst($item['tag_mode'] ?? 'single')],
            ['label' => 'Shelf Life (Months)', 'value' => $item['shelf_life_months'] ?? '—'],
            ['label' => 'Expiry Date', 'value' => $fmtDate($item['expiry_date'] ?? null)],
            ['label' => 'Suppliers', 'value' => $suppliersLabel],
            ['label' => 'Allowed Zones', 'value' => $storageZonesLabel],
            ['label' => 'Cost Price', 'value' => $fmtMoney($item['cost_price'] ?? null)],
            ['label' => 'Selling Price', 'value' => $fmtMoney($item['selling_price'] ?? null)],
            ['label' => 'QR Code', 'value' => $item['qr_code'] ?? $stockService->ensureQrCode($type, $item)],
            ['label' => 'Last Zone', 'value' => $lastZoneName ?? '—'],
            ['label' => 'Last Seen', 'value' => !empty($item['last_seen_at']) ? date('d M Y H:i', strtotime($item['last_seen_at'])) : '—'],
            ['label' => 'Status', 'value' => ucfirst($item['status'] ?? 'active')],
        ];

        $recordsQuery = (new InventoryZoneRecordModel())
            ->where('item_type', $type)
            ->where('item_id', $id)
            ->where('date >=', $today);

        if ($tagScoped && !empty($scannedTag['tag_id'])) {
            $recordsQuery->where('tag_id', (int) $scannedTag['tag_id']);
        }

        $records = $recordsQuery
            ->orderBy('check_in_time', 'DESC')
            ->findAll(20);

        $zoneIds = array_unique(array_column($records, 'zone_id'));
        $zones   = [];
        if ($zoneIds !== []) {
            foreach ($zoneModel->whereIn('zone_id', $zoneIds)->findAll() as $zone) {
                $zones[$zone['zone_id']] = $zone['zone_name'];
            }
        }

        $tagEpcs = [];
        $tagIds  = array_filter(array_unique(array_column($records, 'tag_id')));
        if ($tagIds !== []) {
            foreach ((new InventoryItemTagModel())->whereIn('id', $tagIds)->findAll() as $tag) {
                $tagEpcs[(int) $tag['id']] = $tag['epc_no'];
            }
        }

        $scanRecords = [];
        foreach ($records as $record) {
            $isIn      = empty($record['check_out_time']);
            $checkInTs = strtotime($record['check_in_time']);
            $canLive   = $isIn && $record['date'] === $today;
            $endTs     = $canLive ? $now : ($isIn ? $checkInTs : strtotime($record['check_out_time']));

            $scanRecords[] = [
                'zone_name'      => $zones[$record['zone_id']] ?? $record['zone_id'],
                'tag_epc'        => !empty($record['tag_id']) ? ($tagEpcs[(int) $record['tag_id']] ?? '') : '',
                'status'         => $isIn ? 'IN' : 'OUT',
                'presence_label' => $isIn ? 'In Zone' : 'Left Zone',
                'time_in'        => date('H:i:s', $checkInTs),
                'time_out'       => $isIn ? '—' : date('H:i:s', strtotime($record['check_out_time'])),
                'duration'       => $this->fmtDuration(max(0, $endTs - $checkInTs)),
            ];
        }

        return [
            'type'                 => $type,
            'type_label'           => $type === 'product' ? 'Product' : 'Raw Material',
            'scoped_to_tag'        => $tagScoped,
            'scanned_epc'          => $scannedEpc ?? '',
            'scanned_qr'           => $scannedQr ?? '',
            'scanned_tag'          => $scannedTag,
            'tag_presence'         => $tagPresence,
            'stock_status'         => $stockStatus,
            'product_stock_summary'=> $tagScoped ? $productStockSummary : null,
            'item'                 => [
                'id'             => $id,
                'code'           => $code,
                'name'           => $name,
                'description'    => $item['description'] ?? '',
                'unit'           => $item['unit'] ?? '',
                'balance'        => (float) ($stockSummary['balance'] ?? $item['quantity_on_hand'] ?? 0),
                'tag_mode'       => $item['tag_mode'] ?? 'single',
            ],
            'stock_summary'        => $stockSummary,
            'detail_fields'        => $detailFields,
            'tags'                 => $tags,
            'stock_transactions'   => $stockTxns,
            'scan_records'         => $scanRecords,
            'edit_url'             => $editUrl,
            'view_url'             => $viewUrl,
        ];
    }

    private function resolveStockStatus(?array $scannedTag, array $stockSummary, array $tags): array
    {
        $balance = (float) ($stockSummary['balance'] ?? 0);

        if ($scannedTag) {
            $tagQty = (float) ($scannedTag['tag_quantity'] ?? 0);
            $regQty = (float) ($scannedTag['tag_registered_quantity'] ?? 0);

            if ($tagQty > 0) {
                return [
                    'label'  => 'Stocked In',
                    'tone'   => 'green',
                    'detail' => 'This tag has ' . format_inventory_qty($tagQty) . ' on hand'
                        . ($regQty > 0 ? ' (registered qty ' . format_inventory_qty($regQty) . ')' : ''),
                ];
            }

            return [
                'label'  => 'Tagged — Not Stocked In',
                'tone'   => 'amber',
                'detail' => 'Tag is registered but qty is 0'
                    . ($regQty > 0 ? ' (registered qty ' . format_inventory_qty($regQty) . ')' : '')
                    . '. Use Tag + Stock In for first stock in.',
            ];
        }

        if ($tags !== []) {
            if ($balance > 0) {
                return [
                    'label'  => 'Stocked In',
                    'tone'   => 'green',
                    'detail' => 'Item balance ' . format_inventory_qty($balance),
                ];
            }

            return [
                'label'  => 'Tagged — Not Stocked In',
                'tone'   => 'amber',
                'detail' => 'Item has tags but zero stock.',
            ];
        }

        if ($balance > 0) {
            return [
                'label'  => 'In Stock (no UHF tag)',
                'tone'   => 'green',
                'detail' => 'Balance ' . format_inventory_qty($balance) . ' without active UHF tag.',
            ];
        }

        return [
            'label'  => 'No Stock',
            'tone'   => 'gray',
            'detail' => 'No quantity on hand.',
        ];
    }

    private function getTagZonePresence(int $tagId, ZoneModel $zoneModel): ?array
    {
        $record = (new InventoryZoneRecordModel())
            ->where('tag_id', $tagId)
            ->where('check_out_time', null)
            ->orderBy('check_in_time', 'DESC')
            ->first();

        if (!$record) {
            return null;
        }

        $zone = $zoneModel->where('zone_id', $record['zone_id'])->first();

        return [
            'zone_name' => $zone['zone_name'] ?? $record['zone_id'],
            'status'    => 'IN',
            'since'     => date('d M Y H:i', strtotime($record['check_in_time'])),
        ];
    }

    private function fmtDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0m';
        }

        $hours   = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs    = $seconds % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        if ($minutes > 0) {
            return $minutes . 'm ' . $secs . 's';
        }

        return $secs . 's';
    }
}
