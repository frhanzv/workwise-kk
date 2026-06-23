<?php if (empty($activity_logs)): ?>
    <tr>
        <td colspan="8" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined text-4xl block mb-2 text-gray-300">rss_feed</span>
            No scan records for this period. Items appear here after RFID zone IN/OUT scans.
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
                <span class="text-sm font-mono font-bold text-primary"><?= esc($scan['code']) ?></span>
            </td>
            <td class="px-4 lg:px-6 py-3 text-sm font-semibold text-gray-900 dark:text-white"><?= esc($scan['name']) ?></td>
            <td class="px-4 lg:px-6 py-3 text-sm text-gray-600 dark:text-gray-300"><?= esc($scan['zone_name']) ?></td>
            <td class="px-4 lg:px-6 py-3">
                <?php if ($scan['status'] === 'IN'): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300 uppercase">IN</span>
                <?php elseif ($scan['status'] === 'OUT'): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300 uppercase">OUT</span>
                <?php else: ?>
                    <span class="text-xs text-gray-400">—</span>
                <?php endif; ?>
            </td>
            <td class="px-4 lg:px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 tabular-nums"><?= esc($scan['time_in']) ?></td>
            <td class="px-4 lg:px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 tabular-nums"><?= esc($scan['time_out']) ?></td>
            <td class="px-4 lg:px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 tabular-nums <?= !empty($scan['is_live']) ? 'text-green-600 dark:text-green-400 font-medium' : '' ?>" <?= !empty($scan['is_live']) && !empty($scan['check_in_ts']) ? 'data-check-in-ts="' . esc($scan['check_in_ts']) . '"' : '' ?>><?= esc($scan['duration']) ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
