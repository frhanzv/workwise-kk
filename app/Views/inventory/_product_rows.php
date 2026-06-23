<?php if (empty($products)): ?>
    <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">No products scanned for this period</td></tr>
<?php else: ?>
    <?php foreach ($products as $p): ?>
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 item-row cursor-pointer" data-item-type="product" data-item-id="<?= esc($p['id']) ?>" data-search="<?= strtolower(esc($p['product_code'] . ' ' . $p['product_name'])) ?>">
            <td class="px-4 py-3">
                <span class="text-xs font-mono font-bold text-primary"><?= esc($p['product_code']) ?></span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white"><?= esc($p['product_name']) ?></td>
            <td class="px-4 py-3">
                <?php if (($p['status'] ?? '') === 'IN'): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300 uppercase">IN</span>
                <?php elseif (($p['status'] ?? '') === 'OUT'): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300 uppercase">OUT</span>
                <?php else: ?>
                    <span class="text-xs text-gray-400">—</span>
                <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-xs font-bold text-gray-500 tabular-nums <?= !empty($p['is_live']) ? 'text-green-600 dark:text-green-400 font-medium' : '' ?>" <?= !empty($p['is_live']) && !empty($p['check_in_ts']) ? 'data-check-in-ts="' . esc($p['check_in_ts']) . '"' : '' ?>><?= esc($p['duration'] ?? '—') ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
