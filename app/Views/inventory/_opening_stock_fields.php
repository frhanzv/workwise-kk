<?php
/** @var callable $val */
/** @var string $inputClass */
/** @var array|null $record */
/** @var int $tagCount */
/** @var float $tagTotal */
$isNew = $record === null;
$tagCount = (int) ($tagCount ?? 0);
$tagTotal = (float) ($tagTotal ?? 0);
$tagDriven = !$isNew && $tagCount > 0;
$balanceValue = old('set_balance');
if ($balanceValue === null && ! $isNew) {
    $balanceValue = $tagDriven
        ? format_inventory_qty($tagTotal)
        : format_inventory_qty((float) ($record['quantity_on_hand'] ?? 0));
}
?>
<div class="space-y-5">
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">inventory</span>
            <?= $isNew ? 'Opening Stock' : 'Stock Balance' ?>
        </h2>
    </div>
    <div class="p-4 rounded-xl border-2 border-indigo-200 dark:border-indigo-800 bg-indigo-50/50 dark:bg-indigo-900/10 space-y-3">
        <?php if ($isNew): ?>
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Optional if you add UHF tags above — <strong>total stock is calculated from tag quantities</strong> when tags are present.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Opening Quantity</label>
                    <input type="number" name="initial_quantity" step="0.001" min="0" value="<?= esc($val('initial_quantity', '')) ?>" placeholder="e.g. 10" class="<?= $inputClass ?>"/>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Only used when no UHF tags are added. Leave blank or 0 if tags define stock.</p>
                </div>
            </div>
        <?php elseif ($tagDriven): ?>
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Total stock is the <strong>sum of current tag stock</strong> (<?= (int) $tagCount ?> tag<?= $tagCount === 1 ? '' : 's' ?>).
                Use <strong>Stock In / Out on the View page</strong> to change stock. Registered qty per EPC is set in the UHF tags section above.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Balance</label>
                    <div class="block w-full rounded-lg border border-indigo-300 dark:border-indigo-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white sm:text-sm p-2.5 font-semibold tabular-nums">
                        <?= esc($balanceValue) ?>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Zone OUT deducts current stock. Zone IN restores up to each tag's registered qty.</p>
                </div>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Change the balance here if needed. Saving records a <strong>stock adjustment</strong> (stock in or stock out) to reach the new total.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Balance</label>
                    <input type="number" name="set_balance" step="0.001" min="0" value="<?= esc($balanceValue) ?>" class="<?= $inputClass ?>"/>
                    <p class="text-xs text-gray-500 dark:text-gray-400">For regular movements, use Stock In / Out on the detail page.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
