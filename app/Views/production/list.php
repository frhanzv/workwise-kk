<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="p-4 bg-green-100 dark:bg-green-900/20 border border-green-500 text-green-700 dark:text-green-400 rounded-lg">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="p-4 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="flex flex-wrap items-center justify-between gap-4 mt-6 md:mt-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Production Batches</h1>
            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Track raw material consumption and product output per batch.</p>
        </div>
        <a href="<?= base_url('production/add') ?>" class="flex items-center justify-center h-10 px-4 gap-2 bg-primary text-white rounded-lg text-sm font-bold tracking-wide hover:bg-primary/90 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-base">add</span>
            <span>New Batch</span>
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                <span class="material-symbols-outlined text-2xl">precision_manufacturing</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Batches</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= $stats['total'] ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400">
                <span class="material-symbols-outlined text-2xl">pending</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Open</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= $stats['open'] ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                <span class="material-symbols-outlined text-2xl">task_alt</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Completed</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= $stats['completed'] ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400">
                <span class="material-symbols-outlined text-2xl">cancel</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Cancelled</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= $stats['cancelled'] ?></p>
            </div>
        </div>
    </div>

    <!-- Batch Table -->
    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap justify-between items-center gap-4 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="relative w-full max-w-md">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xl">search</span>
                <input id="searchInput" class="pl-10 w-full h-11 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary placeholder-gray-500 dark:placeholder-gray-400" placeholder="Search by batch number or notes..." type="text"/>
            </div>
            <select id="statusFilter" class="h-10 px-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary">
                <option value="">All Status</option>
                <option value="open">Open</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left" id="batchTable">
                <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3">Batch No</th>
                        <th class="px-6 py-3">Materials Used</th>
                        <th class="px-6 py-3">Products Out</th>
                        <th class="px-6 py-3">Notes</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Created</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($batches)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <span class="material-symbols-outlined text-4xl mb-2 block">precision_manufacturing</span>
                                No production batches yet. <a href="<?= base_url('production/add') ?>" class="text-primary underline">Create the first batch.</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($batches as $batch): ?>
                            <?php
                                $statusColor = match($batch['status']) {
                                    'open'      => 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400',
                                    'completed' => 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400',
                                    'cancelled' => 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors batch-row"
                                data-batch="<?= esc(strtolower($batch['batch_no'] . ' ' . $batch['notes'])) ?>"
                                data-status="<?= $batch['status'] ?>">
                                <td class="px-6 py-4">
                                    <a href="<?= base_url('production/view/' . $batch['id']) ?>" class="font-semibold text-primary hover:underline">
                                        <?= esc($batch['batch_no']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="flex items-center gap-1 text-amber-600 dark:text-amber-400 font-medium">
                                        <span class="material-symbols-outlined text-base">category</span>
                                        <?= $batch['materials_count'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="flex items-center gap-1 text-blue-600 dark:text-blue-400 font-medium">
                                        <span class="material-symbols-outlined text-base">inventory_2</span>
                                        <?= $batch['products_count'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    <?= $batch['notes'] ? esc($batch['notes']) : '-' ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColor ?>">
                                        <?= ucfirst($batch['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    <?= date('M d, Y', strtotime($batch['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= base_url('production/view/' . $batch['id']) ?>"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors">
                                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const searchInput  = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');

function filterRows() {
    const q  = searchInput.value.toLowerCase();
    const st = statusFilter.value;
    document.querySelectorAll('.batch-row').forEach(row => {
        const matchText   = !q  || row.dataset.batch.includes(q);
        const matchStatus = !st || row.dataset.status === st;
        row.style.display = (matchText && matchStatus) ? '' : 'none';
    });
}

searchInput.addEventListener('input', filterRows);
statusFilter.addEventListener('change', filterRows);
</script>

<?= $this->include('templates/footer') ?>
