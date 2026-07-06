<?php
/** @var callable $val */
/** @var string $inputClass */
use App\Models\UnitOfMeasureModel;

$units    = (new UnitOfMeasureModel())->getActiveForSelect();
$selected = (string) $val('unit');
$codes    = array_column($units, 'code');
?>
<select name="unit" class="<?= $inputClass ?>">
    <option value="">— Select unit —</option>
    <?php foreach ($units as $unit): ?>
        <option value="<?= esc($unit['code']) ?>" <?= $selected === $unit['code'] ? 'selected' : '' ?>>
            <?= esc($unit['label']) ?> (<?= esc($unit['code']) ?>)
        </option>
    <?php endforeach; ?>
    <?php if ($selected !== '' && ! in_array($selected, $codes, true)): ?>
        <option value="<?= esc($selected) ?>" selected><?= esc($selected) ?> (custom)</option>
    <?php endif; ?>
</select>
<p class="text-xs text-gray-500">Unit of measure only (not the quantity). Manage options under Configuration → Units of Measure.</p>
