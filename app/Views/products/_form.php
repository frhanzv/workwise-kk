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
$section = static function (string $title, string $icon) {
    echo '<div class="border-b border-gray-200 dark:border-gray-700 pb-4">';
    echo '<h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">';
    echo '<span class="material-symbols-outlined text-primary">' . esc($icon) . '</span>' . esc($title);
    echo '</h2></div>';
};

$selectedSuppliers = old('suppliers');
if ($selectedSuppliers === null) {
    $raw = $record['suppliers'] ?? null;
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        $selectedSuppliers = is_array($decoded) ? $decoded : [];
    } elseif (is_array($raw)) {
        $selectedSuppliers = $raw;
    } else {
        $selectedSuppliers = [];
    }
}
if (!is_array($selectedSuppliers)) {
    $selectedSuppliers = [];
}
$selectedSuppliers = array_values(array_filter(array_map('strval', $selectedSuppliers), static fn ($s) => trim($s) !== ''));
if ($selectedSuppliers === []) {
    $selectedSuppliers = [''];
}

$supplierOptions = (new \App\Models\SupplierModel())->getActiveForSelect();
$supplierNames   = array_column($supplierOptions, 'name');

$allowsAllZones = old('storage_all_zones');
if ($allowsAllZones === null) {
    $allowsAllZones = $record === null
        ? '1'
        : (\App\Models\ProductModel::allowsAllZones($record['storage_location'] ?? null) ? '1' : '0');
}
$allowsAllZones = (string) $allowsAllZones === '1';

$selectedStorageZones = old('storage_locations');
if ($selectedStorageZones === null) {
    $selectedStorageZones = \App\Models\ProductModel::decodeStorageLocations($record['storage_location'] ?? null);
}
if (!is_array($selectedStorageZones)) {
    $selectedStorageZones = [];
}
$selectedStorageZones = array_values(array_filter(
    array_map('strval', $selectedStorageZones),
    static fn ($s) => trim($s) !== '' && $s !== \App\Models\ProductModel::STORAGE_ALL_ZONES
));
if ($selectedStorageZones === []) {
    $selectedStorageZones = [''];
}
$zoneIds = array_column($zones, 'zone_id');
?>
<div class="space-y-5">
    <?php $section('Product Details', 'inventory_2'); ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-1.5 md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Name <span class="text-red-500">*</span></label>
            <input type="text" name="product_name" value="<?= esc($val('product_name')) ?>" required placeholder="e.g. Sodium Hypochlorite 12%" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Code <span class="text-red-500">*</span></label>
            <input type="text" name="product_code" value="<?= esc($val('product_code', $product_code ?? '')) ?>" required class="<?= $inputClass ?> font-mono"/>
            <p class="text-xs text-gray-500 dark:text-gray-400">Batch ID or Lot Number. Auto-generated — you may customize it.</p>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SAP Code <span class="text-red-500">*</span></label>
            <input type="text" name="sap_code" value="<?= esc($val('sap_code')) ?>" required class="<?= $inputClass ?> font-mono"/>
        </div>
        <div class="space-y-1.5 md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Description</label>
            <textarea name="description" rows="3" class="<?= $inputClass ?>" placeholder="Optional product description"><?= esc($val('description')) ?></textarea>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Shelf Life (Months) <span class="text-red-500">*</span></label>
            <input type="number" name="shelf_life_months" value="<?= esc($val('shelf_life_months')) ?>" required min="0" class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Date <span class="text-red-500">*</span></label>
            <input type="date" name="expiry_date" value="<?= esc($val('expiry_date')) ?>" required class="<?= $inputClass ?>"/>
        </div>
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit of Measure</label>
            <?= view('inventory/_unit_select', ['val' => $val, 'inputClass' => $inputClass]) ?>
        </div>
        <div class="space-y-1.5 md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Allowed Zones</label>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 mb-2">
                <input type="checkbox" name="storage_all_zones" id="storage-all-zones" value="1" <?= $allowsAllZones ? 'checked' : '' ?> class="rounded border-gray-300 text-primary focus:ring-primary"/>
                All zones
            </label>
            <div id="storage-specific" class="<?= $allowsAllZones ? 'hidden' : '' ?>">
                <div id="storage-list" class="space-y-2">
                    <?php foreach ($selectedStorageZones as $selectedZoneId): ?>
                        <div class="flex gap-2 storage-row">
                            <select name="storage_locations[]" class="<?= $inputClass ?> flex-1">
                                <option value="">— Select zone —</option>
                                <?php foreach ($zones as $zone): ?>
                                    <option value="<?= esc($zone['zone_id']) ?>" <?= (string) $selectedZoneId === (string) $zone['zone_id'] ? 'selected' : '' ?>><?= esc($zone['zone_name']) ?></option>
                                <?php endforeach; ?>
                                <?php if ($selectedZoneId !== '' && !in_array($selectedZoneId, $zoneIds, true)): ?>
                                    <option value="<?= esc($selectedZoneId) ?>" selected><?= esc($selectedZoneId) ?> (inactive/removed)</option>
                                <?php endif; ?>
                            </select>
                            <button type="button" class="storage-remove h-[42px] px-3 rounded-lg border border-gray-300 dark:border-gray-600 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" title="Remove">
                                <span class="material-symbols-outlined text-base">close</span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="storage-add" class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
                    <span class="material-symbols-outlined text-base">add</span> Add storage zone
                </button>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Zones this product may enter. RFID IN is denied for zones not listed.</p>
        </div>
        <div class="space-y-1.5 md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier List</label>
            <div id="supplier-list" class="space-y-2">
                <?php foreach ($selectedSuppliers as $selectedSupplier): ?>
                    <div class="flex gap-2 supplier-row">
                        <select name="suppliers[]" class="<?= $inputClass ?> flex-1">
                            <option value="">— Select supplier —</option>
                            <?php foreach ($supplierOptions as $option): ?>
                                <option value="<?= esc($option['name']) ?>" <?= $selectedSupplier === $option['name'] ? 'selected' : '' ?>><?= esc($option['name']) ?></option>
                            <?php endforeach; ?>
                            <?php if ($selectedSupplier !== '' && !in_array($selectedSupplier, $supplierNames, true)): ?>
                                <option value="<?= esc($selectedSupplier) ?>" selected><?= esc($selectedSupplier) ?> (inactive/removed)</option>
                            <?php endif; ?>
                        </select>
                        <button type="button" class="supplier-remove h-[42px] px-3 rounded-lg border border-gray-300 dark:border-gray-600 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" title="Remove">
                            <span class="material-symbols-outlined text-base">close</span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="supplier-add" class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
                <span class="material-symbols-outlined text-base">add</span> Add supplier
            </button>
            <p class="text-xs text-gray-500 dark:text-gray-400">Select one or more suppliers. Manage options under <a href="<?= base_url('config/suppliers') ?>" class="text-primary hover:underline" target="_blank">Configuration → Suppliers</a>.</p>
        </div>
    </div>
</div>

<div class="space-y-5">
    <?php $section('Pricing & Financials', 'payments'); ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
    <?php $section('Tag Mode', 'rss_feed'); ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tag Mode</label>
            <select name="tag_mode" id="tag-mode" class="<?= $inputClass ?>">
                <option value="single" <?= $val('tag_mode', 'single') === 'single' ? 'selected' : '' ?>>Single — 1 product, 1 UHF tag</option>
                <option value="multi" <?= $val('tag_mode') === 'multi' ? 'selected' : '' ?>>Multi — 1 product, many UHF tags</option>
            </select>
        </div>
    </div>
</div>

<script>
(function () {
    const inputClass = <?= json_encode($inputClass) ?>;

    function setupMultiSelect(listId, addId, rowClass, removeClass, fieldName, options, blankLabel, unique) {
        const list = document.getElementById(listId);
        const addBtn = document.getElementById(addId);
        if (!list || !addBtn) return;

        function selectedValues(exceptSelect) {
            const values = [];
            list.querySelectorAll('select').forEach(select => {
                if (select === exceptSelect) return;
                if (select.value) values.push(select.value);
            });
            return values;
        }

        function fillOptions(select, keepValue) {
            const taken = unique ? selectedValues(select) : [];
            const current = keepValue ?? select.value;
            select.innerHTML = '';

            const blank = document.createElement('option');
            blank.value = '';
            blank.textContent = blankLabel;
            select.appendChild(blank);

            options.forEach(opt => {
                if (taken.includes(opt.value) && opt.value !== current) return;
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.label;
                select.appendChild(option);
            });

            if (current && [...select.options].some(o => o.value === current)) {
                select.value = current;
            } else {
                select.value = '';
            }
        }

        function refreshAllOptions() {
            list.querySelectorAll('select').forEach(select => fillOptions(select, select.value));
            if (unique) {
                const taken = selectedValues(null);
                addBtn.disabled = taken.length >= options.length;
                addBtn.classList.toggle('opacity-40', addBtn.disabled);
                addBtn.classList.toggle('pointer-events-none', addBtn.disabled);
            }
        }

        function bindSelect(select) {
            select.addEventListener('change', refreshAllOptions);
        }

        function bindRemove(btn) {
            btn.addEventListener('click', () => {
                const rows = list.querySelectorAll('.' + rowClass);
                if (rows.length <= 1) {
                    const select = rows[0]?.querySelector('select');
                    if (select) select.value = '';
                    refreshAllOptions();
                    return;
                }
                btn.closest('.' + rowClass)?.remove();
                refreshAllOptions();
            });
        }

        list.querySelectorAll('select').forEach(select => {
            bindSelect(select);
            fillOptions(select, select.value);
        });
        list.querySelectorAll('.' + removeClass).forEach(bindRemove);
        refreshAllOptions();

        addBtn.addEventListener('click', () => {
            if (unique && selectedValues(null).length >= options.length) return;

            const row = document.createElement('div');
            row.className = 'flex gap-2 ' + rowClass;

            const select = document.createElement('select');
            select.name = fieldName;
            select.className = inputClass + ' flex-1';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = removeClass + ' h-[42px] px-3 rounded-lg border border-gray-300 dark:border-gray-600 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20';
            removeBtn.title = 'Remove';
            removeBtn.innerHTML = '<span class="material-symbols-outlined text-base">close</span>';

            row.appendChild(select);
            row.appendChild(removeBtn);
            list.appendChild(row);
            bindSelect(select);
            bindRemove(removeBtn);
            refreshAllOptions();
            select.focus();
        });
    }

    setupMultiSelect(
        'supplier-list',
        'supplier-add',
        'supplier-row',
        'supplier-remove',
        'suppliers[]',
        <?= json_encode(array_map(static fn ($s) => ['value' => $s['name'], 'label' => $s['name']], $supplierOptions), JSON_UNESCAPED_UNICODE) ?>,
        '— Select supplier —',
        true
    );

    setupMultiSelect(
        'storage-list',
        'storage-add',
        'storage-row',
        'storage-remove',
        'storage_locations[]',
        <?= json_encode(array_map(static fn ($z) => ['value' => $z['zone_id'], 'label' => $z['zone_name']], $zones), JSON_UNESCAPED_UNICODE) ?>,
        '— Select zone —',
        true
    );

    const allZonesCb = document.getElementById('storage-all-zones');
    const storageSpecific = document.getElementById('storage-specific');
    allZonesCb?.addEventListener('change', () => {
        storageSpecific?.classList.toggle('hidden', allZonesCb.checked);
    });
})();
</script>
