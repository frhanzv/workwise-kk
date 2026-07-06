<?= $this->include('templates/header') ?>

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config') ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="text-gray-900 dark:text-white text-xl font-bold leading-tight">Units of Measure</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Used in product and raw material forms.</p>
        </div>
    </div>
    <button type="button" onclick="openAddModal()" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined text-lg">add</span>
        Add Unit
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="mb-4 p-3 bg-green-100 dark:bg-green-900/20 border border-green-500 text-green-700 dark:text-green-400 rounded-lg text-sm">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-4 p-3 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg text-sm">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto max-h-[calc(100vh-200px)] overflow-y-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Label</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Description</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Sort</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (!empty($units)): ?>
                    <?php foreach ($units as $unit): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                            <td class="px-4 py-3 text-sm font-mono font-medium text-gray-900 dark:text-white"><?= esc($unit['code']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white"><?= esc($unit['label']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate"><?= esc($unit['description'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-sm tabular-nums text-gray-600 dark:text-gray-400"><?= (int) $unit['sort_order'] ?></td>
                            <td class="px-4 py-3">
                                <?php if ($unit['is_active']): ?>
                                    <span class="px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/20 rounded-full">Active</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-400 bg-gray-100 dark:bg-gray-900/20 rounded-full">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick='editUnit(<?= json_encode($unit, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <form action="<?= base_url('config/units-of-measure/toggle/' . $unit['id']) ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-gray-600 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                            <span class="material-symbols-outlined text-lg"><?= $unit['is_active'] ? 'toggle_on' : 'toggle_off' ?></span>
                                        </button>
                                    </form>
                                    <button type="button" onclick="confirmDelete('<?= base_url('config/units-of-measure/delete/' . $unit['id']) ?>', '<?= esc($unit['code']) ?>')" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No units configured yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="unitModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
        <h2 id="modalTitle" class="text-xl font-bold text-gray-900 dark:text-white mb-4">Add Unit</h2>
        <form id="unitForm" method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" id="unitId" name="id">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Code <span class="text-red-500">*</span></label>
                <input type="text" id="unitCode" name="code" required maxlength="20" placeholder="e.g. pcs, kg"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent font-mono">
                <p class="text-xs text-gray-500 mt-1">Short code stored on products and materials.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Label <span class="text-red-500">*</span></label>
                <input type="text" id="unitLabel" name="label" required maxlength="50" placeholder="e.g. Pieces"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea id="unitDescription" name="description" rows="2" placeholder="Optional notes..."
                          class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort Order</label>
                <input type="number" id="unitSort" name="sort_order" value="0" min="0"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Save</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">delete</span>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Unit</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Remove <span id="deleteName" class="font-medium text-gray-900 dark:text-white"></span>?</p>
            </div>
        </div>
        <form id="deleteForm" method="POST" class="flex gap-3">
            <?= csrf_field() ?>
            <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg">Cancel</button>
            <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Unit';
    document.getElementById('unitForm').action = '<?= base_url('config/units-of-measure/store') ?>';
    document.getElementById('unitId').value = '';
    document.getElementById('unitCode').value = '';
    document.getElementById('unitLabel').value = '';
    document.getElementById('unitDescription').value = '';
    document.getElementById('unitSort').value = '0';
    document.getElementById('unitCode').readOnly = false;
    document.getElementById('unitModal').classList.remove('hidden');
}

function editUnit(unit) {
    document.getElementById('modalTitle').textContent = 'Edit Unit';
    document.getElementById('unitForm').action = '<?= base_url('config/units-of-measure/update') ?>';
    document.getElementById('unitId').value = unit.id;
    document.getElementById('unitCode').value = unit.code;
    document.getElementById('unitLabel').value = unit.label;
    document.getElementById('unitDescription').value = unit.description || '';
    document.getElementById('unitSort').value = unit.sort_order || 0;
    document.getElementById('unitCode').readOnly = false;
    document.getElementById('unitModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('unitModal').classList.add('hidden');
}

function confirmDelete(url, name) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?= $this->include('templates/footer') ?>
