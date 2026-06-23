<?= $this->include('templates/header') ?>

<?php
$statusColor = match($batch['status']) {
    'open'      => 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 border-yellow-300 dark:border-yellow-700',
    'completed' => 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-300 dark:border-green-700',
    'cancelled' => 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-300 dark:border-red-700',
    default     => 'bg-gray-100 text-gray-600',
};
$isOpen = $batch['status'] === 'open';
?>

<div class="flex flex-col gap-6">
    <?php if ($flash_success ?? null): ?>
        <div class="p-4 bg-green-100 dark:bg-green-900/20 border border-green-500 text-green-700 dark:text-green-400 rounded-lg">
            <?= esc($flash_success) ?>
        </div>
    <?php endif; ?>
    <?php if ($flash_error ?? null): ?>
        <div class="p-4 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg">
            <?= esc($flash_error) ?>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex flex-wrap items-start justify-between gap-4 mt-6 md:mt-4">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
                <a href="<?= base_url('production/list') ?>" class="hover:text-primary">Production Batches</a>
                <span class="material-symbols-outlined text-sm">chevron_right</span>
                <span><?= esc($batch['batch_no']) ?></span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight"><?= esc($batch['batch_no']) ?></h1>
                <span class="px-3 py-1 rounded-full text-sm font-medium border <?= $statusColor ?>">
                    <?= ucfirst($batch['status']) ?>
                </span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                Created <?= date('F d, Y \a\t h:i A', strtotime($batch['created_at'])) ?>
            </p>
            <?php if ($batch['notes']): ?>
                <p class="text-gray-600 dark:text-gray-300 text-sm mt-2 max-w-2xl"><?= esc($batch['notes']) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($isOpen): ?>
        <div class="flex items-center gap-2">
            <form action="<?= base_url('production/complete/' . $batch['id']) ?>" method="post" onsubmit="return confirm('Mark this batch as completed?')">
                <?= csrf_field() ?>
                <button type="submit" class="flex items-center gap-2 h-10 px-4 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors">
                    <span class="material-symbols-outlined text-base">task_alt</span>
                    Complete Batch
                </button>
            </form>
            <form action="<?= base_url('production/cancel/' . $batch['id']) ?>" method="post" onsubmit="return confirm('Cancel this batch? All raw materials will be restored to active.')">
                <?= csrf_field() ?>
                <button type="submit" class="flex items-center gap-2 h-10 px-4 border border-red-400 text-red-600 dark:text-red-400 rounded-lg text-sm font-semibold hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <span class="material-symbols-outlined text-base">cancel</span>
                    Cancel Batch
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400">
                <span class="material-symbols-outlined text-2xl">category</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Materials Used</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= count($batch['materials']) ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                <span class="material-symbols-outlined text-2xl">inventory_2</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Products Out</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= count($batch['products']) ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-<?= $isOpen ? 'yellow' : ($batch['status'] === 'completed' ? 'green' : 'red') ?>-50 dark:bg-<?= $isOpen ? 'yellow' : ($batch['status'] === 'completed' ? 'green' : 'red') ?>-900/20 text-<?= $isOpen ? 'yellow' : ($batch['status'] === 'completed' ? 'green' : 'red') ?>-600 dark:text-<?= $isOpen ? 'yellow' : ($batch['status'] === 'completed' ? 'green' : 'red') ?>-400">
                <span class="material-symbols-outlined text-2xl"><?= $isOpen ? 'pending' : ($batch['status'] === 'completed' ? 'task_alt' : 'cancel') ?></span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Status</p>
                <p class="text-gray-900 dark:text-white text-lg font-bold"><?= ucfirst($batch['status']) ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">
                <span class="material-symbols-outlined text-2xl">schedule</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Last Updated</p>
                <p class="text-gray-900 dark:text-white text-sm font-semibold"><?= date('M d, H:i', strtotime($batch['updated_at'])) ?></p>
            </div>
        </div>
    </div>

    <!-- Two columns: Materials | Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- RAW MATERIALS panel -->
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl text-amber-500">category</span>
                    <h2 class="text-gray-900 dark:text-white font-semibold text-base">Raw Materials Consumed</h2>
                    <span class="ml-1 px-2 py-0.5 bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 text-xs rounded-full font-medium"><?= count($batch['materials']) ?></span>
                </div>
            </div>

            <!-- Add material form (open batches only) -->
            <?php if ($isOpen): ?>
            <form action="<?= base_url('production/add-material/' . $batch['id']) ?>" method="post"
                  class="flex items-center gap-2 px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/30">
                <?= csrf_field() ?>
                <?php if (!empty($available_materials)): ?>
                    <select name="raw_material_id" required
                            class="flex-1 h-9 px-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary">
                        <option value="">— Select raw material —</option>
                        <?php foreach ($available_materials as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= esc($m['material_code']) ?> — <?= esc($m['material_name']) ?><?= $m['category'] ? ' (' . esc($m['category']) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit"
                            class="flex items-center gap-1 h-9 px-3 bg-amber-500 text-white rounded-lg text-xs font-semibold hover:bg-amber-600 transition-colors whitespace-nowrap">
                        <span class="material-symbols-outlined text-sm">add</span>
                        Add
                    </button>
                <?php else: ?>
                    <p class="text-xs text-gray-400 italic">No active raw materials available to add.</p>
                <?php endif; ?>
            </form>
            <?php endif; ?>

            <!-- Material list -->
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/50">
                <?php if (empty($batch['materials'])): ?>
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">
                        <span class="material-symbols-outlined text-3xl mb-2 block text-amber-300">category</span>
                        No raw materials added yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($batch['materials'] as $m): ?>
                        <div class="flex items-center gap-3 px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                            <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-500 flex-shrink-0">
                                <span class="material-symbols-outlined text-lg">category</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-gray-900 dark:text-white text-sm font-semibold truncate"><?= esc($m['material_name']) ?></p>
                                <p class="text-gray-400 text-xs"><?= esc($m['material_code']) ?><?= $m['category'] ? ' · ' . esc($m['category']) : '' ?></p>
                                <?php if ($m['epc_no']): ?>
                                    <p class="text-gray-400 text-xs font-mono truncate">EPC: <?= esc($m['epc_no']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-gray-400 text-xs"><?= date('M d, H:i', strtotime($m['added_at'])) ?></p>
                                <?php if ($m['last_seen_zone']): ?>
                                    <p class="text-xs text-blue-500">Zone: <?= esc($m['last_seen_zone']) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if ($isOpen): ?>
                                <form action="<?= base_url('production/remove-material/' . $batch['id']) ?>" method="post"
                                      onsubmit="return confirm('Remove this material? It will be restored to active.')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="pivot_id" value="<?= $m['pivot_id'] ?>">
                                    <input type="hidden" name="raw_material_id" value="<?= $m['id'] ?>">
                                    <button type="submit" class="p-1 text-gray-400 hover:text-red-500 transition-colors rounded" title="Remove">
                                        <span class="material-symbols-outlined text-lg">remove_circle</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- PRODUCTS panel -->
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl text-blue-500">inventory_2</span>
                    <h2 class="text-gray-900 dark:text-white font-semibold text-base">Products Produced</h2>
                    <span class="ml-1 px-2 py-0.5 bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 text-xs rounded-full font-medium"><?= count($batch['products']) ?></span>
                </div>
            </div>

            <!-- Add product form (open batches only) -->
            <?php if ($isOpen): ?>
            <form action="<?= base_url('production/add-product/' . $batch['id']) ?>" method="post"
                  class="flex items-center gap-2 px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/30">
                <?= csrf_field() ?>
                <?php if (!empty($available_products)): ?>
                    <select name="product_id" required
                            class="flex-1 h-9 px-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary">
                        <option value="">— Select product —</option>
                        <?php foreach ($available_products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= esc($p['product_code']) ?> — <?= esc($p['product_name']) ?><?= $p['category'] ? ' (' . esc($p['category']) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit"
                            class="flex items-center gap-1 h-9 px-3 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors whitespace-nowrap">
                        <span class="material-symbols-outlined text-sm">add</span>
                        Add
                    </button>
                <?php else: ?>
                    <p class="text-xs text-gray-400 italic">No active products available to add.</p>
                <?php endif; ?>
            </form>
            <?php endif; ?>

            <!-- Product list -->
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/50">
                <?php if (empty($batch['products'])): ?>
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">
                        <span class="material-symbols-outlined text-3xl mb-2 block text-blue-300">inventory_2</span>
                        No products linked yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($batch['products'] as $p): ?>
                        <div class="flex items-center gap-3 px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                            <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-500 flex-shrink-0">
                                <span class="material-symbols-outlined text-lg">inventory_2</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-gray-900 dark:text-white text-sm font-semibold truncate"><?= esc($p['product_name']) ?></p>
                                <p class="text-gray-400 text-xs"><?= esc($p['product_code']) ?><?= $p['category'] ? ' · ' . esc($p['category']) : '' ?></p>
                                <?php if ($p['epc_no']): ?>
                                    <p class="text-gray-400 text-xs font-mono truncate">EPC: <?= esc($p['epc_no']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-gray-400 text-xs"><?= date('M d, H:i', strtotime($p['added_at'])) ?></p>
                                <?php if ($p['last_seen_zone']): ?>
                                    <p class="text-xs text-blue-500">Zone: <?= esc($p['last_seen_zone']) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if ($isOpen): ?>
                                <form action="<?= base_url('production/remove-product/' . $batch['id']) ?>" method="post"
                                      onsubmit="return confirm('Remove this product from the batch?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="pivot_id" value="<?= $p['pivot_id'] ?>">
                                    <button type="submit" class="p-1 text-gray-400 hover:text-red-500 transition-colors rounded" title="Remove">
                                        <span class="material-symbols-outlined text-lg">remove_circle</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /grid -->

    <!-- Traceability summary (completed batches) -->
    <?php if ($batch['status'] === 'completed' && !empty($batch['materials']) && !empty($batch['products'])): ?>
    <div class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-xl p-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-xl text-green-600">route</span>
            <h3 class="text-green-800 dark:text-green-300 font-semibold">Traceability Summary</h3>
        </div>
        <p class="text-green-700 dark:text-green-400 text-sm">
            <strong><?= count($batch['materials']) ?></strong> raw material<?= count($batch['materials']) !== 1 ? 's' : '' ?>
            (<?= implode(', ', array_map(fn($m) => esc($m['material_code']), $batch['materials'])) ?>)
            were consumed to produce
            <strong><?= count($batch['products']) ?></strong> product<?= count($batch['products']) !== 1 ? 's' : '' ?>
            (<?= implode(', ', array_map(fn($p) => esc($p['product_code']), $batch['products'])) ?>)
            in batch <strong><?= esc($batch['batch_no']) ?></strong>.
        </p>
    </div>
    <?php endif; ?>

</div>

<?= $this->include('templates/footer') ?>
