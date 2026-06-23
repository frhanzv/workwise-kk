<?php
/** @var array|null $record */
/** @var string $material_code */
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
    <?php $section('Basic Details (Raw Material)', 'category'); ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference Number <span class="text-red-500">*</span></label>
            <input type="text" name="material_code" value="<?= esc($val('material_code', $material_code ?? '')) ?>" required class="<?= $inputClass ?> font-mono"/>
            <p class="text-xs text-gray-500">Internal raw material ID.</p>
        </div>
        <div class="space-y-1.5 md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Raw Material Name <span class="text-red-500">*</span></label>
            <input type="text" name="material_name" value="<?= esc($val('material_name')) ?>" required class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SAP Code <span class="text-red-500">*</span></label>
            <input type="text" name="sap_code" value="<?= esc($val('sap_code')) ?>" required class="<?= $inputClass ?> font-mono"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Warehouse Location <span class="text-red-500">*</span></label>
            <select name="warehouse_location" required class="<?= $inputClass ?>">
                <option value="">— Select zone —</option>
                <?php foreach ($zones as $zone): ?>
                    <option value="<?= esc($zone['zone_id']) ?>" <?= $val('warehouse_location') === $zone['zone_id'] ? 'selected' : '' ?>><?= esc($zone['zone_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min Stock <span class="text-red-500">*</span></label>
            <input type="number" step="0.001" name="min_stock" value="<?= esc($val('min_stock')) ?>" required min="0" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Alert (Days) <span class="text-red-500">*</span></label>
            <input type="number" name="expiry_alert_days" value="<?= esc($val('expiry_alert_days')) ?>" required min="0" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
            <input type="text" name="category" value="<?= esc($val('category')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
            <input type="text" name="unit" value="<?= esc($val('unit')) ?>" placeholder="e.g. kg, liters" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5 md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
            <textarea name="description" rows="2" class="<?= $inputClass ?>"><?= esc($val('description')) ?></textarea>
        </div>
    </div>
</div>

<div class="space-y-5">
    <?php $section('Quality Tests', 'science'); ?>
    <div class="flex flex-wrap gap-6">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="sample_test" value="1" <?= $checked('sample_test') ? 'checked' : '' ?> class="rounded border-gray-300 text-primary focus:ring-primary"/>
            Sample Test
        </label>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="pre_sample_test" value="1" <?= $checked('pre_sample_test') ? 'checked' : '' ?> class="rounded border-gray-300 text-primary focus:ring-primary"/>
            Pre-Sample Test
        </label>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="k_test" value="1" <?= $checked('k_test') ? 'checked' : '' ?> class="rounded border-gray-300 text-primary focus:ring-primary"/>
            K Test
        </label>
    </div>
</div>

<div class="space-y-5">
    <?php $section('Supplier Information', 'local_shipping'); ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier Name <span class="text-red-500">*</span></label>
            <input type="text" name="supplier_name" value="<?= esc($val('supplier_name')) ?>" required class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Manufacturer Name <span class="text-red-500">*</span></label>
            <input type="text" name="manufacturer_name" value="<?= esc($val('manufacturer_name')) ?>" required class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier Shelf Life (Months) <span class="text-red-500">*</span></label>
            <input type="number" name="supplier_shelf_life_months" value="<?= esc($val('supplier_shelf_life_months')) ?>" required min="0" class="<?= $inputClass ?>"/>
        </div>
    </div>
</div>

<div class="space-y-5">
    <?php $section('Technical Specification (Optional)', 'biotech'); ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Appearance / Active Ingredient</label>
            <input type="text" name="appearance" value="<?= esc($val('appearance')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Chemical Formula</label>
            <input type="text" name="chemical_formula" value="<?= esc($val('chemical_formula')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">pH Range</label>
            <input type="text" name="ph_range" value="<?= esc($val('ph_range')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assay / Content</label>
            <input type="text" name="assay_content" value="<?= esc($val('assay_content')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Specific Gravity</label>
            <input type="text" name="specific_gravity" value="<?= esc($val('specific_gravity')) ?>" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Shelf Life (Months)</label>
            <input type="number" name="shelf_life_months" value="<?= esc($val('shelf_life_months')) ?>" min="0" class="<?= $inputClass ?>"/>
        </div>
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
