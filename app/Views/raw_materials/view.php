<?= $this->include('templates/header') ?>

<?php
$fmtDate = static function ($d) {
    return !empty($d) ? date('d M Y', strtotime($d)) : '—';
};
$fmtMoney = static function ($v) {
    return $v !== null && $v !== '' ? 'RM ' . number_format((float) $v, 2) : '—';
};
?>

<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-4 mt-6 md:mt-4">
        <div class="flex items-center gap-4">
            <a href="<?= base_url('raw-materials/list') ?>" class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><?= esc($material['material_name']) ?></h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-mono"><?= esc($material['material_code']) ?></p>
            </div>
        </div>
        <a href="<?= base_url('raw-materials/edit/' . $material['id']) ?>" class="flex items-center gap-2 h-10 px-4 rounded-lg bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-base">edit</span>
            Edit
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="p-4 bg-green-100 dark:bg-green-900/20 border border-green-500 text-green-700 dark:text-green-400 rounded-lg">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="p-4 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?= view('inventory/_stock_panel', [
        'itemType'           => $itemType,
        'itemId'             => $itemId,
        'stock_summary'      => $stock_summary,
        'stock_transactions' => $stock_transactions,
        'stockInUrl'         => $stockInUrl,
        'stockOutUrl'        => $stockOutUrl,
        'tagDrivenStock'     => !empty($tags),
    ]) ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <?php
            $supplierList = [];
            $rawSuppliers = $material['suppliers'] ?? null;
            if (is_string($rawSuppliers) && $rawSuppliers !== '') {
                $decoded = json_decode($rawSuppliers, true);
                $supplierList = is_array($decoded) ? $decoded : [];
            } elseif (is_array($rawSuppliers)) {
                $supplierList = $rawSuppliers;
            } elseif (!empty($material['supplier_name'])) {
                $supplierList = [(string) $material['supplier_name']];
            }
            ?>
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Raw Material Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Raw Material Code</dt><dd class="mt-1 text-sm font-mono text-primary dark:text-blue-400"><?= esc($material['material_code']) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Raw Material Name</dt><dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white"><?= esc($material['material_name']) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400">SAP Code</dt><dd class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100"><?= esc($material['sap_code'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Shelf Life (Months)</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100"><?= esc($material['shelf_life_months'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Expiry Date</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100"><?= $fmtDate($material['expiry_date'] ?? null) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Unit of Measure</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100"><?= esc($material['unit'] ?? '—') ?></dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Allowed Zones</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100"><?= esc($storageZonesLabel ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400">QR Code</dt><dd class="mt-1 text-sm font-mono text-xs break-all text-gray-900 dark:text-gray-100"><?= esc($qr_code ?? $material['qr_code'] ?? '—') ?></dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Suppliers</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100"><?= $supplierList !== [] ? esc(implode(', ', $supplierList)) : '—' ?></dd></div>
                    <?php if (!empty($material['description'])): ?>
                        <div class="sm:col-span-2"><dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Description</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100"><?= nl2br(esc($material['description'])) ?></dd></div>
                    <?php endif; ?>
                </dl>
            </div>

            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Pricing &amp; Financials</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Cost Price</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100"><?= $fmtMoney($material['cost_price'] ?? null) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Selling Price</dt><dd class="mt-1 text-sm text-gray-900 dark:text-gray-100"><?= $fmtMoney($material['selling_price'] ?? null) ?></dd></div>
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Status</h2>
                <?php if ($material['status'] === 'active'): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Active</span>
                <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">Inactive</span>
                <?php endif; ?>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">Registered <?= $fmtDate($material['created_at'] ?? null) ?></p>
            </div>

            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-purple-500">rss_feed</span>
                    UHF RFID Tags
                    <span class="text-xs font-normal normal-case text-gray-400">(<?= ($material['tag_mode'] ?? 'single') === 'multi' ? 'Multi' : 'Single' ?> · <?= format_inventory_qty((float)($material['qty_per_tag'] ?? 1)) ?>/tag)</span>
                </h2>
                <?php if (!empty($tags)): ?>
                    <ul class="space-y-2">
                        <?php foreach ($tags as $tag): ?>
                            <li class="p-2 rounded-lg bg-purple-50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-900/30">
                                <p class="text-sm font-mono text-purple-800 dark:text-purple-300 break-all"><?= esc($tag['epc_no']) ?></p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Registered: <?= format_inventory_qty((float) ($tag['tag_registered_quantity'] ?? $tag['tag_quantity'])) ?>
                                    · Current: <?= format_inventory_qty((float) $tag['tag_quantity']) ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php elseif (!empty($material['epc_no'])): ?>
                    <p class="text-sm font-mono text-purple-800 dark:text-purple-300 break-all"><?= esc($material['epc_no']) ?></p>
                <?php else: ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No UHF tags assigned.</p>
                <?php endif; ?>
            </div>

            <?= view('inventory/_qr_code_panel', ['qr_code' => $qr_code ?? $material['qr_code'] ?? '', 'itemLabel' => 'Raw Material']) ?>

            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-blue-500">location_on</span>
                    Last Known Location
                </h2>
                <?php if (!empty($lastZone)): ?>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($lastZone['zone_name']) ?></p>
                    <?php if (!empty($material['last_seen_at'])): ?>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= date('d M Y H:i', strtotime($material['last_seen_at'])) ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Not yet scanned by any zone.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>
