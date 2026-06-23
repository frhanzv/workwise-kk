<?= $this->include('templates/header') ?>

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config') ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-gray-900 dark:text-white text-xl font-bold leading-tight">Antenna Mode List</h1>
    </div>
    <button onclick="openAddModal()" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined text-lg">add</span>
        Add Mode
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
    <div class="overflow-x-auto max-h-[calc(100vh-0px)] overflow-y-auto">
    <table class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Mode Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Description</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Color</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (!empty($modes)): ?>
                <?php foreach ($modes as $mode): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white"><?= esc($mode['mode_name']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= esc($mode['description'] ?? '-') ?></td>
                        <td class="px-4 py-3">
                            <?php if (!empty($mode['color'])): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= esc($mode['color']) ?>-100 text-<?= esc($mode['color']) ?>-800 dark:bg-<?= esc($mode['color']) ?>-900/30 dark:text-<?= esc($mode['color']) ?>-300"><?= ucfirst(esc($mode['color'])) ?></span>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($mode['is_active']): ?>
                                <span class="px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/20 rounded-full">Active</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-400 bg-gray-100 dark:bg-gray-900/20 rounded-full">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editMode(<?= $mode['id'] ?>, '<?= esc($mode['mode_name']) ?>', '<?= esc($mode['description'] ?? '') ?>', '<?= esc($mode['color'] ?? '') ?>')" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                                <form action="<?= base_url('config/antenna-mode/toggle/' . $mode['id']) ?>" method="POST" class="inline">
                                    <button type="submit" class="text-gray-600 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                        <span class="material-symbols-outlined text-lg"><?= $mode['is_active'] ? 'toggle_on' : 'toggle_off' ?></span>
                                    </button>
                                </form>
                                <button onclick="confirmDelete('<?= base_url('config/antenna-mode/delete/' . $mode['id']) ?>', '<?= esc($mode['mode_name']) ?>')" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No antenna modes found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="modeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
        <h2 id="modalTitle" class="text-xl font-bold text-gray-900 dark:text-white mb-4">Add Antenna Mode</h2>
        <form id="modeForm" method="POST">
            <input type="hidden" id="modeId" name="id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mode Name</label>
                <input type="text" id="modeName" name="mode_name" required placeholder="Insert antenna mode name..."
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea id="modeDescription" name="description" rows="3" placeholder="Insert description of the antenna mode..."
                          class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Color</label>
                <select id="modeColor" name="color" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Select Color</option>
                    <option value="purple">Purple</option>
                    <option value="blue">Blue</option>
                    <option value="amber">Amber</option>
                    <option value="teal">Teal</option>
                    <option value="green">Green</option>
                    <option value="red">Red</option>
                    <option value="orange">Orange</option>
                    <option value="pink">Pink</option>
                    <option value="indigo">Indigo</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Antenna Mode';
    document.getElementById('modeForm').action = '<?= base_url('config/antenna-mode/store') ?>';
    document.getElementById('modeId').value = '';
    document.getElementById('modeName').value = '';
    document.getElementById('modeDescription').value = '';
    document.getElementById('modeColor').value = '';
    document.getElementById('modeModal').classList.remove('hidden');
}

function editMode(id, name, description, color) {
    document.getElementById('modalTitle').textContent = 'Edit Antenna Mode';
    document.getElementById('modeForm').action = '<?= base_url('config/antenna-mode/update') ?>';
    document.getElementById('modeId').value = id;
    document.getElementById('modeName').value = name;
    document.getElementById('modeDescription').value = description;
    document.getElementById('modeColor').value = color || '';
    document.getElementById('modeModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modeModal').classList.add('hidden');
}
</script>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">delete</span>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Confirmation</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone</p>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6" id="deleteMessage">Are you sure you want to delete this antenna mode?</p>
        <div class="flex gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors">
                Cancel
            </button>
            <button onclick="executeDelete()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                Delete
            </button>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    <?= csrf_field() ?>
</form>

<script>
let deleteUrl = '';

function confirmDelete(url, itemName) {
    deleteUrl = url;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${itemName}"?`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteUrl = '';
}

function executeDelete() {
    if (deleteUrl) {
        const form = document.getElementById('deleteForm');
        form.action = deleteUrl;
        form.submit();
    }
}
</script>

<?= $this->include('templates/footer') ?>
