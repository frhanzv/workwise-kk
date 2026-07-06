<?php if (empty($products)): ?>
    <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">No products scanned for this period</td></tr>
<?php else: ?>
    <?php foreach ($products as $p): ?>
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 item-row cursor-pointer" data-item-type="product" data-item-id="<?= esc($p['id']) ?>" data-search="<?= strtolower(esc($p['product_code'] . ' ' . $p['product_name'])) ?>">
            <td class="px-4 py-3">
                <span class="text-xs font-mono font-bold text-primary dark:text-blue-400"><?= esc($p['product_code']) ?></span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white"><?= esc($p['product_name']) ?></td>
            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300"><?= esc($p['current_zone'] ?? '—') ?></td>
            <td class="px-4 py-3 text-xs font-bold tabular-nums text-indigo-600 dark:text-indigo-400"><?= format_inventory_qty((float)($p['balance'] ?? 0)) ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
