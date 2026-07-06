<?php if (empty($activity_logs)): ?>
    <tr>
        <td colspan="8" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined text-4xl block mb-2 text-gray-300">rss_feed</span>
            No zone activity for this period. RFID scans appear here when a tagged item enters or leaves a zone.
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($activity_logs as $scan): ?>
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors scan-row cursor-pointer" data-item-type="<?= esc($scan['type']) ?>" data-item-id="<?= esc($scan['item_id']) ?>" data-search="<?= strtolower(esc($scan['code'] . ' ' . $scan['name'] . ' ' . $scan['zone_name'])) ?>">
            <td class="px-4 lg:px-6 py-3">
                <?php if ($scan['type'] === 'product'): ?>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300">Product</span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300">Raw Material</span>
                <?php endif; ?>
            </td>
            <td class="px-4 lg:px-6 py-3">
                <span class="text-sm font-mono font-bold text-primary dark:text-blue-400"><?= esc($scan['code']) ?></span>
            </td>
            <td class="px-4 lg:px-6 py-3 text-sm font-semibold text-gray-900 dark:text-white">
                <?= esc($scan['name']) ?>
            </td>
            <td class="px-4 lg:px-6 py-3 text-xs font-bold tabular-nums text-indigo-600 dark:text-indigo-400"><?= format_inventory_qty($scan['balance'] ?? 0) ?></td>
            <td class="px-4 lg:px-6 py-3 text-sm text-gray-600 dark:text-gray-300"><?= esc($scan['zone_name']) ?></td>
            <td class="px-4 lg:px-6 py-3">
                <?php if (($scan['status'] ?? '') === 'IN'): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300"><?= esc($scan['presence_label'] ?? 'In Zone') ?></span>
                <?php elseif (($scan['status'] ?? '') === 'OUT'): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400"><?= esc($scan['presence_label'] ?? 'Left Zone') ?></span>
                <?php else: ?>
                    <span class="text-xs text-gray-400">—</span>
                <?php endif; ?>
            </td>
            <td class="px-4 lg:px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 tabular-nums"><?= esc($scan['time_in']) ?></td>
            <td class="px-4 lg:px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 tabular-nums"><?= esc($scan['time_out']) ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
