<?= $this->include('templates/header') ?>

<?php
$fmtDate = static function ($d) {
    return !empty($d) ? date('d M Y', strtotime($d)) : '—';
};
$yesNo = static function ($v) {
    return !empty($v) ? 'Yes' : 'No';
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Basic Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-xs font-medium text-gray-500">Reference Number</dt><dd class="mt-1 text-sm font-mono text-primary"><?= esc($material['material_code']) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Raw Material Name</dt><dd class="mt-1 text-sm font-semibold"><?= esc($material['material_name']) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">SAP Code</dt><dd class="mt-1 text-sm font-mono"><?= esc($material['sap_code'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Warehouse Location</dt><dd class="mt-1 text-sm"><?= esc($warehouseZone['zone_name'] ?? $material['warehouse_location'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Min Stock</dt><dd class="mt-1 text-sm"><?= esc($material['min_stock'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Expiry Alert (Days)</dt><dd class="mt-1 text-sm"><?= esc($material['expiry_alert_days'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Category</dt><dd class="mt-1 text-sm"><?= esc($material['category'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Unit</dt><dd class="mt-1 text-sm"><?= esc($material['unit'] ?? '—') ?></dd></div>
                    <?php if (!empty($material['description'])): ?>
                        <div class="sm:col-span-2"><dt class="text-xs font-medium text-gray-500">Description</dt><dd class="mt-1 text-sm"><?= nl2br(esc($material['description'])) ?></dd></div>
                    <?php endif; ?>
                </dl>
            </div>

            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Quality Tests</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div><dt class="text-xs font-medium text-gray-500">Sample Test</dt><dd class="mt-1 text-sm"><?= $yesNo($material['sample_test'] ?? 0) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Pre-Sample Test</dt><dd class="mt-1 text-sm"><?= $yesNo($material['pre_sample_test'] ?? 0) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">K Test</dt><dd class="mt-1 text-sm"><?= $yesNo($material['k_test'] ?? 0) ?></dd></div>
                </dl>
            </div>

            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Supplier Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-xs font-medium text-gray-500">Supplier Name</dt><dd class="mt-1 text-sm"><?= esc($material['supplier_name'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Manufacturer Name</dt><dd class="mt-1 text-sm"><?= esc($material['manufacturer_name'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Supplier Shelf Life (Months)</dt><dd class="mt-1 text-sm"><?= esc($material['supplier_shelf_life_months'] ?? '—') ?></dd></div>
                </dl>
            </div>

            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Technical Specification</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-xs font-medium text-gray-500">Appearance / Active Ingredient</dt><dd class="mt-1 text-sm"><?= esc($material['appearance'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Chemical Formula</dt><dd class="mt-1 text-sm"><?= esc($material['chemical_formula'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">pH Range</dt><dd class="mt-1 text-sm"><?= esc($material['ph_range'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Assay / Content</dt><dd class="mt-1 text-sm"><?= esc($material['assay_content'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Specific Gravity</dt><dd class="mt-1 text-sm"><?= esc($material['specific_gravity'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Shelf Life (Months)</dt><dd class="mt-1 text-sm"><?= esc($material['shelf_life_months'] ?? '—') ?></dd></div>
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Status</h2>
                <?php if ($material['status'] === 'active'): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                <?php endif; ?>
                <p class="text-xs text-gray-500 mt-3">Registered <?= $fmtDate($material['created_at'] ?? null) ?></p>
            </div>

            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-purple-500">rss_feed</span>
                    UHF RFID Tag
                </h2>
                <?php if (!empty($material['epc_no'])): ?>
                    <p class="text-sm font-mono text-purple-800 dark:text-purple-200 break-all"><?= esc($material['epc_no']) ?></p>
                <?php else: ?>
                    <p class="text-sm text-gray-500">No UHF tag assigned.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-blue-500">location_on</span>
                    Last Known Location
                </h2>
                <?php if (!empty($lastZone)): ?>
                    <p class="text-sm font-semibold"><?= esc($lastZone['zone_name']) ?></p>
                    <?php if (!empty($material['last_seen_at'])): ?>
                        <p class="text-xs text-gray-500"><?= date('d M Y H:i', strtotime($material['last_seen_at'])) ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-sm text-gray-500">Not yet scanned by any zone.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>
