<?php if (empty($materials)): ?>
    <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">No raw materials scanned for this period</td></tr>
<?php else: ?>
    <?php foreach ($materials as $m): ?>
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 item-row cursor-pointer" data-item-type="raw_material" data-item-id="<?= esc($m['id']) ?>" data-search="<?= strtolower(esc($m['material_code'] . ' ' . $m['material_name'])) ?>">
            <td class="px-4 py-3">
                <span class="text-xs font-mono font-bold text-primary dark:text-blue-400"><?= esc($m['material_code']) ?></span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white"><?= esc($m['material_name']) ?></td>
            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300"><?= esc($m['current_zone'] ?? '—') ?></td>
            <td class="px-4 py-3 text-xs font-bold tabular-nums text-indigo-600 dark:text-indigo-400"><?= format_inventory_qty((float)($m['balance'] ?? 0)) ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
