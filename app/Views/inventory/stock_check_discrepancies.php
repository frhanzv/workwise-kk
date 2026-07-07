<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6 px-2 sm:px-4 py-2 sm:py-4">
    <div class="flex flex-wrap items-center justify-between gap-4 mt-4 md:mt-2">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Stock Check Discrepancy Records</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Items/tags not scanned during completed stock checks.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= base_url('inventory/stock-check/discrepancies-export') ?>" class="px-4 py-2 rounded-lg text-sm font-bold border border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
                Download Excel
            </a>
            <a href="<?= base_url('inventory/stock-check') ?>" class="text-sm text-primary hover:underline">← Back to Stock Check</a>
        </div>
    </div>

    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
        <?php if (empty($rows)): ?>
            <p class="text-sm text-gray-500 dark:text-gray-400">No discrepancy records yet.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <th class="py-2.5 pr-4 font-bold whitespace-nowrap">Date &amp; Time</th>
                            <th class="py-2.5 pr-4 font-bold">Item</th>
                            <th class="py-2.5 pr-4 font-bold">Not Scanned</th>
                            <th class="py-2.5 font-bold text-right whitespace-nowrap">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-900 dark:text-gray-100">
                        <?php foreach ($rows as $row): ?>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-2.5 pr-4 whitespace-nowrap tabular-nums"><?= esc($row['datetime'] ?? '—') ?></td>
                                <td class="py-2.5 pr-4">
                                    <span class="text-xs text-gray-500 dark:text-gray-400"><?= esc($row['type_label'] ?? '') ?></span><br>
                                    <span class="font-medium"><?= esc($row['item_label'] ?? '') ?></span>
                                </td>
                                <td class="py-2.5 pr-4 font-mono text-xs break-all"><?= esc($row['not_scanned'] ?? '') ?></td>
                                <td class="py-2.5 text-right tabular-nums font-medium whitespace-nowrap">
                                    <?= esc($row['quantity_fmt'] ?? '0') ?>
                                    <?php if (!empty($row['unit'])): ?>
                                        <span class="text-gray-400 text-xs"><?= esc($row['unit']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->include('templates/footer') ?>
