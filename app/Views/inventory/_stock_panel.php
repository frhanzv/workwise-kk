<?php
/** @var string $itemType product|raw_material */
/** @var int $itemId */
/** @var array $stock_summary */
/** @var array $stock_transactions */
/** @var string $stockInUrl */
/** @var string $stockOutUrl */
/** @var bool $tagDrivenStock */
$summary = $stock_summary ?? [];
$txns    = $stock_transactions ?? [];
$tagDrivenStock = !empty($tagDrivenStock);
?>
<div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
    <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock Quantity</h2>

    <?php if ($tagDrivenStock): ?>
        <p class="text-sm text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-3">
            Balance is the <strong>sum of current tag stock</strong>. Use <strong>Stock In / Out</strong> below for web adjustments (no EPC needed). Zone <strong>OUT</strong> deducts current stock; zone <strong>IN</strong> restores up to each tag's registered qty.
        </p>
    <?php endif; ?>

    <?php if ($tagDrivenStock): ?>
        <div class="p-5 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 text-center">
            <p class="text-xs font-bold text-indigo-700 dark:text-indigo-400 uppercase">Balance</p>
            <p class="text-3xl font-black text-indigo-800 dark:text-indigo-300 tabular-nums mt-1"><?= format_inventory_qty((float)($summary['balance'] ?? 0)) ?></p>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Balance = sum of all active UHF tag quantities.</p>
    <?php else: ?>
        <div class="grid grid-cols-3 gap-3">
            <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-center">
                <p class="text-xs font-bold text-green-700 dark:text-green-400 uppercase">Total Stock In</p>
                <p class="text-2xl font-black text-green-800 dark:text-green-300 tabular-nums mt-1"><?= format_inventory_qty((float)($summary['total_stock_in'] ?? 0)) ?></p>
            </div>
            <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-center">
                <p class="text-xs font-bold text-red-700 dark:text-red-400 uppercase">Total Stock Out</p>
                <p class="text-2xl font-black text-red-800 dark:text-red-300 tabular-nums mt-1"><?= format_inventory_qty((float)($summary['total_stock_out'] ?? 0)) ?></p>
            </div>
            <div class="p-4 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 text-center">
                <p class="text-xs font-bold text-indigo-700 dark:text-indigo-400 uppercase">Balance</p>
                <p class="text-2xl font-black text-indigo-800 dark:text-indigo-300 tabular-nums mt-1"><?= format_inventory_qty((float)($summary['balance'] ?? 0)) ?></p>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Example: Stock in 10, then stock out 5 → balance shows 5.</p>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <form action="<?= esc($stockInUrl) ?>" method="post" class="p-4 rounded-lg border border-green-200 dark:border-green-800 bg-green-50/50 dark:bg-green-900/10 space-y-3">
            <?= csrf_field() ?>
            <p class="text-sm font-bold text-green-800 dark:text-green-300 flex items-center gap-1">
                <span class="material-symbols-outlined text-base">add_circle</span> Stock In
            </p>
            <input type="number" name="quantity" step="0.001" min="0.001" required placeholder="Qty to add"
                   class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2.5 text-sm"/>
            <input type="text" name="notes" placeholder="Notes (optional)"
                   class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2.5 text-sm"/>
            <button type="submit" class="w-full py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-bold">Record Stock In</button>
        </form>
        <form action="<?= esc($stockOutUrl) ?>" method="post" class="p-4 rounded-lg border border-red-200 dark:border-red-800 bg-red-50/50 dark:bg-red-900/10 space-y-3">
            <?= csrf_field() ?>
            <p class="text-sm font-bold text-red-800 dark:text-red-300 flex items-center gap-1">
                <span class="material-symbols-outlined text-base">remove_circle</span> Stock Out
            </p>
            <input type="number" name="quantity" step="0.001" min="0.001" required placeholder="Qty to remove" max="<?= (float)($summary['balance'] ?? 0) ?>"
                   class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2.5 text-sm"/>
            <input type="text" name="notes" placeholder="Notes (optional)"
                   class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2.5 text-sm"/>
            <button type="submit" class="w-full py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-bold">Record Stock Out</button>
        </form>
    </div>

    <?php if (!empty($txns)): ?>
        <div>
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Movement History</p>
            <div class="overflow-x-auto max-h-48 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full text-xs">
                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800">
                        <tr class="text-gray-500 dark:text-gray-400">
                            <th class="px-3 py-2 text-left">Date</th>
                            <th class="px-3 py-2 text-left">Type</th>
                            <th class="px-3 py-2 text-right">Qty</th>
                            <th class="px-3 py-2 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <?php foreach ($txns as $txn): ?>
                            <tr>
                                <td class="px-3 py-2 tabular-nums text-gray-600 dark:text-gray-300"><?= esc($txn['datetime']) ?></td>
                                <td class="px-3 py-2">
                                    <?php if ($txn['transaction_type'] === 'stock_in'): ?>
                                        <span class="text-green-600 dark:text-green-400 font-bold">Stock In</span>
                                    <?php elseif ($txn['transaction_type'] === 'stock_out'): ?>
                                        <span class="text-red-600 dark:text-red-400 font-bold">Stock Out</span>
                                    <?php else: ?>
                                        <span class="text-amber-600 dark:text-amber-400 font-bold">Adjust</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-right font-bold tabular-nums text-gray-900 dark:text-white"><?= format_inventory_qty($txn['quantity']) ?></td>
                                <td class="px-3 py-2 text-right tabular-nums text-gray-900 dark:text-white"><?= format_inventory_qty($txn['balance_after']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
