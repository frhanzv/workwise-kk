<?php
/** @var array $scan */
$tags = $scan['tags'] ?? [];
if (empty($tags) && !empty($scan['tag_epc'])) {
    $tags = [['epc' => $scan['tag_epc'], 'status' => $scan['status'] ?? 'IN']];
}
$multi = count($tags) > 1;
foreach ($tags as $tag):
    if (empty($tag['epc'])) {
        continue;
    }
?>
    <span class="block text-xs font-mono font-normal text-purple-600 dark:text-purple-400 mt-0.5">
        <?= esc($tag['epc']) ?>
        <?php if ($multi): ?>
            <span class="text-gray-500 dark:text-gray-400">· <?= ($tag['status'] ?? '') === 'IN' ? 'in zone' : 'left' ?></span>
        <?php endif; ?>
    </span>
<?php endforeach; ?>
