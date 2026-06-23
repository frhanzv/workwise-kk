<?= $this->include('templates/header') ?>

<?php
$fmtDate = static function ($d) {
    return !empty($d) ? date('d M Y', strtotime($d)) : '—';
};
$fmtMoney = static function ($v) {
    return $v !== null && $v !== '' ? 'RM ' . number_format((float) $v, 2) : '—';
};
$yesNo = static function ($v) {
    return !empty($v) ? 'Yes' : 'No';
};
?>

<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-4 mt-6 md:mt-4">
        <div class="flex items-center gap-4">
            <a href="<?= base_url('products/list') ?>" class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"><?= esc($product['product_name']) ?></h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-mono"><?= esc($product['product_code']) ?></p>
            </div>
        </div>
        <a href="<?= base_url('products/edit/' . $product['id']) ?>" class="flex items-center gap-2 h-10 px-4 rounded-lg bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">
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
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Basic Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-xs font-medium text-gray-500">Reference Number</dt><dd class="mt-1 text-sm font-mono text-primary"><?= esc($product['product_code']) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Entry Date</dt><dd class="mt-1 text-sm"><?= $fmtDate($product['entry_date'] ?? null) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Product Name</dt><dd class="mt-1 text-sm font-semibold"><?= esc($product['product_name']) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">SAP Code</dt><dd class="mt-1 text-sm font-mono"><?= esc($product['sap_code'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Lot Number</dt><dd class="mt-1 text-sm"><?= esc($product['lot_number'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Shelf Life (Months)</dt><dd class="mt-1 text-sm"><?= esc($product['shelf_life_months'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Analysis Date</dt><dd class="mt-1 text-sm"><?= $fmtDate($product['analysis_date'] ?? null) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Manufacturing Date</dt><dd class="mt-1 text-sm"><?= $fmtDate($product['manufacturing_date'] ?? null) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Expiry Date</dt><dd class="mt-1 text-sm"><?= $fmtDate($product['expiry_date'] ?? null) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Customer Name</dt><dd class="mt-1 text-sm"><?= esc($product['customer_name'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Category</dt><dd class="mt-1 text-sm"><?= esc($product['category'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Unit</dt><dd class="mt-1 text-sm"><?= esc($product['unit'] ?? '—') ?></dd></div>
                    <?php if (!empty($product['description'])): ?>
                        <div class="sm:col-span-2"><dt class="text-xs font-medium text-gray-500">Description</dt><dd class="mt-1 text-sm"><?= nl2br(esc($product['description'])) ?></dd></div>
                    <?php endif; ?>
                </dl>
            </div>

            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Technical Specification</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-xs font-medium text-gray-500">pH Level Target</dt><dd class="mt-1 text-sm"><?= esc($product['ph_level_target'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Purity Grade</dt><dd class="mt-1 text-sm"><?= esc($product['purity_grade'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Density @20°C</dt><dd class="mt-1 text-sm"><?= esc($product['density_20c'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Viscosity</dt><dd class="mt-1 text-sm"><?= esc($product['viscosity'] ?? '—') ?></dd></div>
                </dl>
            </div>

            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Pricing &amp; QC</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><dt class="text-xs font-medium text-gray-500">Pricing Start Date</dt><dd class="mt-1 text-sm"><?= $fmtDate($product['pricing_start_date'] ?? null) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Cost Price</dt><dd class="mt-1 text-sm"><?= $fmtMoney($product['cost_price'] ?? null) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Selling Price</dt><dd class="mt-1 text-sm"><?= $fmtMoney($product['selling_price'] ?? null) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Color Description</dt><dd class="mt-1 text-sm"><?= esc($product['color_description'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">QC Status</dt><dd class="mt-1 text-sm"><?= esc($product['qc_status'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">QC Quantity</dt><dd class="mt-1 text-sm"><?= esc($product['qc_quantity'] ?? '—') ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">NSF Certification</dt><dd class="mt-1 text-sm"><?= $yesNo($product['nsf_certified'] ?? 0) ?></dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Halal Certification</dt><dd class="mt-1 text-sm"><?= $yesNo($product['halal_certified'] ?? 0) ?></dd></div>
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Status</h2>
                <?php if ($product['status'] === 'active'): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                <?php endif; ?>
                <p class="text-xs text-gray-500 mt-3">Registered <?= $fmtDate($product['created_at'] ?? null) ?></p>
            </div>

            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-purple-500">rss_feed</span>
                    UHF RFID Tag
                </h2>
                <?php if (!empty($product['epc_no'])): ?>
                    <p class="text-sm font-mono text-purple-800 dark:text-purple-200 break-all"><?= esc($product['epc_no']) ?></p>
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
                    <?php if (!empty($product['last_seen_at'])): ?>
                        <p class="text-xs text-gray-500"><?= date('d M Y H:i', strtotime($product['last_seen_at'])) ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-sm text-gray-500">Not yet scanned by any zone.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>
