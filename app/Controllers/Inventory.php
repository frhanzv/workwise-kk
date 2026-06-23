<?php

namespace App\Controllers;

use App\Models\InventoryZoneRecordModel;
use App\Models\ProductModel;
use App\Models\RawMaterialModel;
use App\Models\ZoneModel;

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

        $warehouseZoneName = null;
        if ($type === 'raw_material' && !empty($item['warehouse_location'])) {
            $whZone = $zoneModel->where('zone_id', $item['warehouse_location'])->first();
            $warehouseZoneName = $whZone['zone_name'] ?? $item['warehouse_location'];
        }

        $fmtDate = static function ($d) {
            return !empty($d) ? date('d M Y', strtotime($d)) : '—';
        };
        $fmtMoney = static function ($v) {
            return $v !== null && $v !== '' ? 'RM ' . number_format((float) $v, 2) : '—';
        };
        $yesNo = static function ($v) {
            return !empty($v) ? 'Yes' : 'No';
        };

        $detailFields = [
            ['label' => 'Reference Number', 'value' => $code],
            ['label' => 'Name', 'value' => $name],
            ['label' => 'SAP Code', 'value' => $item['sap_code'] ?? '—'],
            ['label' => 'Category', 'value' => $item['category'] ?? '—'],
            ['label' => 'Unit', 'value' => $item['unit'] ?? '—'],
            ['label' => 'Status', 'value' => ucfirst($item['status'] ?? 'active')],
        ];

        if ($type === 'product') {
            $detailFields = array_merge($detailFields, [
                ['label' => 'Entry Date', 'value' => $fmtDate($item['entry_date'] ?? null)],
                ['label' => 'Lot Number', 'value' => $item['lot_number'] ?? '—'],
                ['label' => 'Shelf Life (Months)', 'value' => $item['shelf_life_months'] ?? '—'],
                ['label' => 'Manufacturing Date', 'value' => $fmtDate($item['manufacturing_date'] ?? null)],
                ['label' => 'Expiry Date', 'value' => $fmtDate($item['expiry_date'] ?? null)],
                ['label' => 'Cost Price', 'value' => $fmtMoney($item['cost_price'] ?? null)],
                ['label' => 'Selling Price', 'value' => $fmtMoney($item['selling_price'] ?? null)],
                ['label' => 'QC Status', 'value' => $item['qc_status'] ?? '—'],
                ['label' => 'NSF Certified', 'value' => $yesNo($item['nsf_certified'] ?? 0)],
                ['label' => 'Halal Certified', 'value' => $yesNo($item['halal_certified'] ?? 0)],
            ]);
        } else {
            $detailFields = array_merge($detailFields, [
                ['label' => 'Warehouse Location', 'value' => $warehouseZoneName ?? '—'],
                ['label' => 'Min Stock', 'value' => $item['min_stock'] ?? '—'],
                ['label' => 'Expiry Alert (Days)', 'value' => $item['expiry_alert_days'] ?? '—'],
                ['label' => 'Supplier', 'value' => $item['supplier_name'] ?? '—'],
                ['label' => 'Manufacturer', 'value' => $item['manufacturer_name'] ?? '—'],
                ['label' => 'Sample Test', 'value' => $yesNo($item['sample_test'] ?? 0)],
                ['label' => 'Pre-Sample Test', 'value' => $yesNo($item['pre_sample_test'] ?? 0)],
                ['label' => 'K Test', 'value' => $yesNo($item['k_test'] ?? 0)],
            ]);
        }

        $detailFields[] = ['label' => 'EPC Tag', 'value' => $item['epc_no'] ?? '—'];
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

        $scanRecords = [];
        foreach ($records as $record) {
            $isIn        = empty($record['check_out_time']);
            $checkInTs   = strtotime($record['check_in_time']);
            $canLive     = $isIn && $record['date'] === $today;
            $endTs       = $canLive ? $now : ($isIn ? $checkInTs : strtotime($record['check_out_time']));

            $scanRecords[] = [
                'zone_name' => $zones[$record['zone_id']] ?? $record['zone_id'],
                'status'    => $isIn ? 'IN' : 'OUT',
                'time_in'   => date('H:i:s', $checkInTs),
                'time_out'  => $isIn ? '—' : date('H:i:s', strtotime($record['check_out_time'])),
                'duration'  => $this->fmtDuration(max(0, $endTs - $checkInTs)),
                'date'      => date('d M Y', strtotime($record['date'])),
                'is_live'   => $canLive,
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
                'created_at'     => !empty($item['created_at']) ? date('d M Y', strtotime($item['created_at'])) : '—',
                'last_seen_at'   => !empty($item['last_seen_at']) ? date('d M Y H:i', strtotime($item['last_seen_at'])) : '—',
                'last_zone_name' => $lastZoneName ?? '—',
            ],
            'detail_fields' => $detailFields,
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
        $records = $this->getZoneRecords(
            $zoneId,
            $dateRange['start_date'],
            $dateRange['end_date']
        );

        $formattedLogs = $this->formatActivityLogs($records);
        $recentScans   = array_slice($formattedLogs, 0, 50);
        $sideItems     = $this->buildSideItemsFromRecords($records);
        $stats         = $this->computeStats($records);

        $totalZones = (new ZoneModel())->where('status', 'active')->countAllResults();

        return [
            'products'       => $sideItems['products'],
            'materials'      => $sideItems['materials'],
            'recent_scans'   => $recentScans,
            'all_scans'      => $formattedLogs,
            'stats'          => $stats,
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
        ];
    }

    private function buildSideItemsFromRecords(array $records): array
    {
        $latestByKey = [];

        foreach ($records as $record) {
            $key = $record['item_type'] . ':' . $record['item_id'];
            if (!isset($latestByKey[$key])) {
                $latestByKey[$key] = $record;
            }
        }

        if (empty($latestByKey)) {
            return ['products' => [], 'materials' => []];
        }

        $formatted = $this->formatActivityLogs(array_values($latestByKey));
        $products  = [];
        $materials = [];

        foreach ($formatted as $row) {
            $entry = [
                'id'            => $row['item_id'],
                'product_code'  => $row['type'] === 'product' ? $row['code'] : null,
                'product_name'  => $row['type'] === 'product' ? $row['name'] : null,
                'material_code' => $row['type'] === 'raw_material' ? $row['code'] : null,
                'material_name' => $row['type'] === 'raw_material' ? $row['name'] : null,
                'status'        => $row['status'],
                'duration'      => $row['duration'],
                'check_in_ts'   => $row['check_in_ts'],
                'is_live'       => $row['is_live'],
            ];

            if ($row['type'] === 'product') {
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
        $isToday       = true; // live duration only when viewing includes today

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

        $today     = date('Y-m-d');
        $formatted = [];

        foreach ($records as $record) {
            $isProduct = $record['item_type'] === 'product';
            $item      = $isProduct
                ? ($products[$record['item_id']] ?? null)
                : ($materials[$record['item_id']] ?? null);

            if (!$item) {
                continue;
            }

            $isIn        = empty($record['check_out_time']);
            $checkInTs   = strtotime($record['check_in_time']);
            $canLive     = $isIn && $record['date'] === $today;
            $endTs       = $canLive ? $now : ($isIn ? $checkInTs : strtotime($record['check_out_time']));
            $durationSec = max(0, $endTs - $checkInTs);

            $formatted[] = [
                'item_id'      => $record['item_id'],
                'type'         => $record['item_type'],
                'type_label'   => $isProduct ? 'Product' : 'Raw Material',
                'code'         => $isProduct ? $item['product_code'] : $item['material_code'],
                'name'         => $isProduct ? $item['product_name'] : $item['material_name'],
                'zone_name'    => $zones[$record['zone_id']] ?? $record['zone_id'],
                'status'       => $isIn ? 'IN' : 'OUT',
                'time_in'      => date('H:i:s', $checkInTs),
                'time_out'     => $isIn ? '—' : date('H:i:s', strtotime($record['check_out_time'])),
                'duration'     => $this->fmtDuration($durationSec),
                'check_in_ts'  => $canLive ? $checkInTs : null,
                'is_live'      => $canLive,
                'record_date'  => $record['date'],
                'view_url'     => $isProduct
                    ? base_url('products/view/' . $item['id'])
                    : base_url('raw-materials/view/' . $item['id']),
            ];
        }

        return $formatted;
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
