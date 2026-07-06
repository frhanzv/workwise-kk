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
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Product Master List</h1>
            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Master data for finished products and UHF RFID tags.</p>
        </div>
        <a href="<?= base_url('products/add') ?>" class="flex items-center justify-center h-10 px-4 gap-2 bg-primary text-white rounded-lg text-sm font-bold tracking-wide hover:bg-primary/90 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-base">add</span>
            <span>Add Product</span>
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                <span class="material-symbols-outlined text-2xl">inventory_2</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= $stats['total'] ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                <span class="material-symbols-outlined text-2xl">check_circle</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Active</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= $stats['active'] ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400">
                <span class="material-symbols-outlined text-2xl">cancel</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Inactive</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= $stats['inactive'] ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">
                <span class="material-symbols-outlined text-2xl">rss_feed</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">UHF Tagged</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= $stats['tagged'] ?></p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap justify-between items-center gap-4 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="relative w-full max-w-md">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xl">search</span>
                <input id="searchInput" class="pl-10 w-full h-11 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary placeholder-gray-500 dark:placeholder-gray-400" placeholder="Search by name, product code, or SAP code..." type="text"/>
            </div>
            <select id="statusFilter" class="h-10 px-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" id="productsTable">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product Code</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">SAP Code</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Tag Mode</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden xl:table-cell">Allowed Zones</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700" id="productsBody">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <span class="material-symbols-outlined text-4xl block mb-2">inventory_2</span>
                                No products found. <a href="<?= base_url('products/add') ?>" class="text-primary hover:underline">Add one now.</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors product-row"
                                data-name="<?= strtolower(esc($p['product_name'])) ?>"
                                data-code="<?= strtolower(esc($p['product_code'])) ?>"
                                data-sap="<?= strtolower(esc($p['sap_code'] ?? '')) ?>"
                                data-status="<?= esc($p['status']) ?>">
                                <td class="px-6 py-4 text-sm font-mono font-medium text-primary"><?= esc($p['product_code']) ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                            <span class="material-symbols-outlined text-base text-blue-600 dark:text-blue-400">inventory_2</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($p['product_name']) ?></p>
                                            <?php if (!empty($p['unit'])): ?>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Unit: <?= esc($p['unit']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-mono text-gray-600 dark:text-gray-300 hidden md:table-cell">
                                    <?= !empty($p['sap_code']) ? esc($p['sap_code']) : '—' ?>
                                </td>
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    <?php if (($p['tag_mode'] ?? 'single') === 'multi'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300">Multi</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">Single</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 hidden xl:table-cell text-sm text-gray-700 dark:text-gray-300">
                                    <?= esc($p['storage_zone_name'] ?? '—') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($p['status'] === 'active'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400">Active</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-400">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                onclick="openEpcAssignModal(<?= (int) $p['id'] ?>, <?= json_encode($p['product_name']) ?>)"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                                title="<?= !empty($p['epc_no']) ? 'Change UHF tag' : 'Assign UHF tag' ?>">
                                            <span class="material-symbols-outlined text-base">rss_feed</span>
                                        </button>
                                        <a href="<?= base_url('products/view/' . $p['id']) ?>" class="p-1.5 rounded-lg text-gray-500 hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="View">
                                            <span class="material-symbols-outlined text-base">visibility</span>
                                        </a>
                                        <a href="<?= base_url('products/edit/' . $p['id']) ?>" class="p-1.5 rounded-lg text-gray-500 hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Edit">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </a>
                                        <button onclick="confirmDelete(<?= $p['id'] ?>, '<?= esc($p['product_name']) ?>')" class="p-1.5 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Delete">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-sm w-full p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 rounded-lg bg-red-100 dark:bg-red-900/20">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400">delete</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Delete Product</h3>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">Are you sure you want to delete <strong id="deleteProductName"></strong>? This action cannot be undone.</p>
        <div class="flex gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 h-10 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
            <form id="deleteForm" method="post" class="flex-1">
                <?= csrf_field() ?>
                <button type="submit" class="w-full h-10 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
const searchInput   = document.getElementById('searchInput');
const statusFilter  = document.getElementById('statusFilter');
const rows          = document.querySelectorAll('.product-row');

function filterRows() {
    const q      = searchInput.value.toLowerCase();
    const status = statusFilter.value;
    rows.forEach(row => {
        const matchSearch = !q || row.dataset.name.includes(q) || row.dataset.code.includes(q) || (row.dataset.sap || '').includes(q);
        const matchStatus = !status || row.dataset.status === status;
        row.style.display = matchSearch && matchStatus ? '' : 'none';
    });
}

searchInput.addEventListener('input', filterRows);
statusFilter.addEventListener('change', filterRows);

function confirmDelete(id, name) {
    document.getElementById('deleteProductName').textContent = name;
    document.getElementById('deleteForm').action = '<?= base_url('products/delete/') ?>' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?= view('inventory/_epc_assign_modal', [
    'postUrl'      => base_url('products/assign-tag'),
    'tagsUrl'      => base_url('products/tags'),
    'removeTagUrl' => base_url('products/remove-tag'),
    'updateTagUrl' => base_url('products/update-tag'),
    'itemLabel'    => 'product',
]) ?>

<?= $this->include('templates/footer') ?>
