<?php
/** @var array|null $record */
/** @var string $product_code */
/** @var array $zones */
$record = $record ?? null;
$inputClass = 'block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5';
$val = static function (string $key, $default = '') use ($record) {
    if (old($key) !== null) {
        return old($key);
    }
    return $record[$key] ?? $default;
};
$checked = static function (string $key) use ($record, $val) {
    $v = $val($key, 0);
    return $v === '1' || $v === 1 || $v === true;
};
$section = static function (string $title, string $icon) {
    echo '<div class="border-b border-gray-200 dark:border-gray-700 pb-4">';
    echo '<h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">';
    echo '<span class="material-symbols-outlined text-primary">' . esc($icon) . '</span>' . esc($title);
    echo '</h2></div>';
};
?>
<div class="space-y-5">
    <?php $section('Basic Details (Product)', 'inventory_2'); ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference Number <span class="text-red-500">*</span></label>
            <input type="text" name="product_code" value="<?= esc($val('product_code', $product_code ?? '')) ?>" required class="<?= $inputClass ?> font-mono"/>
            <p class="text-xs text-gray-500">Auto-generated reference number. You may customize it.</p>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Entry Date <span class="text-red-500">*</span></label>
            <input type="date" name="entry_date" value="<?= esc($val('entry_date', date('Y-m-d'))) ?>" required class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5 md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Name <span class="text-red-500">*</span></label>
            <input type="text" name="product_name" value="<?= esc($val('product_name')) ?>" required placeholder="e.g. Sodium Hypochlorite 12%" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SAP Code <span class="text-red-500">*</span></label>
            <input type="text" name="sap_code" value="<?= esc($val('sap_code')) ?>" required class="<?= $inputClass ?> font-mono"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lot Number <span class="text-red-500">*</span></label>
            <input type="text" name="lot_number" value="<?= esc($val('lot_number')) ?>" required class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Shelf Life (Months) <span class="text-red-500">*</span></label>
            <input type="number" name="shelf_life_months" value="<?= esc($val('shelf_life_months')) ?>" required min="0" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Analysis Date</label>
            <input type="date" name="analysis_date" value="<?= esc($val('analysis_date')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Manufacturing Date <span class="text-red-500">*</span></label>
            <input type="date" name="manufacturing_date" value="<?= esc($val('manufacturing_date')) ?>" required class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Date <span class="text-red-500">*</span></label>
            <input type="date" name="expiry_date" value="<?= esc($val('expiry_date')) ?>" required class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Name</label>
            <input type="text" name="customer_name" value="<?= esc($val('customer_name')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
            <input type="text" name="category" value="<?= esc($val('category')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
            <input type="text" name="unit" value="<?= esc($val('unit')) ?>" placeholder="e.g. pcs, drum" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5 md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
            <textarea name="description" rows="2" class="<?= $inputClass ?>"><?= esc($val('description')) ?></textarea>
        </div>
    </div>
</div>

<div class="space-y-5">
    <?php $section('Technical Specification', 'science'); ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">pH Level Target</label>
            <input type="text" name="ph_level_target" value="<?= esc($val('ph_level_target')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Purity Grade</label>
            <input type="text" name="purity_grade" value="<?= esc($val('purity_grade')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Density @20°C</label>
            <input type="text" name="density_20c" value="<?= esc($val('density_20c')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Viscosity</label>
            <input type="text" name="viscosity" value="<?= esc($val('viscosity')) ?>" class="<?= $inputClass ?>"/>
        </div>
    </div>
</div>

<div class="space-y-5">
    <?php $section('Pricing & Financials', 'payments'); ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date <span class="text-red-500">*</span></label>
            <input type="date" name="pricing_start_date" value="<?= esc($val('pricing_start_date', date('Y-m-d'))) ?>" required class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost Price (RM) <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" name="cost_price" value="<?= esc($val('cost_price')) ?>" required min="0" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selling Price (RM) <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" name="selling_price" value="<?= esc($val('selling_price')) ?>" required min="0" class="<?= $inputClass ?>"/>
        </div>
    </div>
</div>

<div class="space-y-5">
    <?php $section('Product QC Record', 'verified'); ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color Description</label>
            <input type="text" name="color_description" value="<?= esc($val('color_description')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">QC Status</label>
            <select name="qc_status" class="<?= $inputClass ?>">
                <option value="">— Select —</option>
                <?php foreach (['Pass', 'Fail', 'Pending'] as $opt): ?>
                    <option value="<?= esc($opt) ?>" <?= $val('qc_status') === $opt ? 'selected' : '' ?>><?= esc($opt) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
            <input type="number" step="0.001" name="qc_quantity" value="<?= esc($val('qc_quantity')) ?>" min="0" class="<?= $inputClass ?>"/>
        </div>
    </div>
</div>

<div class="space-y-5">
    <?php $section('Certification', 'workspace_premium'); ?>
    <div class="flex flex-wrap gap-6">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="nsf_certified" value="1" <?= $checked('nsf_certified') ? 'checked' : '' ?> class="rounded border-gray-300 text-primary focus:ring-primary"/>
            NSF Certification
        </label>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="halal_certified" value="1" <?= $checked('halal_certified') ? 'checked' : '' ?> class="rounded border-gray-300 text-primary focus:ring-primary"/>
            Halal Certification
        </label>
    </div>
</div>

<div class="space-y-5">
    <?php $section('UHF RFID Tag', 'rss_feed'); ?>
    <p class="text-xs text-gray-500 dark:text-gray-400">Assign a UHF tag for zone tracking (Workwise RFID).</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">EPC Tag Number</label>
            <input type="text" name="epc_no" value="<?= esc($val('epc_no')) ?>" placeholder="e.g. E200471472C06426C2510112" class="<?= $inputClass ?> font-mono"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <select name="status" class="<?= $inputClass ?>">
                <option value="active" <?= $val('status', 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $val('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
    </div>
</div>
