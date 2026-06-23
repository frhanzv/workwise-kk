<?= $this->include('templates/header') ?>

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config') ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-gray-900 dark:text-white text-xl font-bold leading-tight">Groups Shift</h1>
    </div>
    <button onclick="openAddModal()" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined text-lg">add</span>
        Create
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Work Schedule Rule Code</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (!empty($groupsShifts)): ?>
                    <?php foreach ($groupsShifts as $groupsShift): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white"><?= esc($groupsShift['group']) ?></td>
                            <td class="px-4 py-3 text-sm font-medium" style="color: <?= !empty($groupsShift['color']) ? esc($groupsShift['color']) : 'white' ?>"><?= esc($groupsShift['code']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= esc($groupsShift['name']) ?></td>
                            <td class="px-4 py-3">
                                <?php if ($groupsShift['is_active']): ?>
                                    <span class="px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/20 rounded-full">Active</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-400 bg-gray-100 dark:bg-gray-900/20 rounded-full">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='editGroupsShift(<?= json_encode($groupsShift) ?>)' class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <form action="<?= base_url('config/groups-shift/toggle/' . $groupsShift['id']) ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-gray-600 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                            <span class="material-symbols-outlined text-lg"><?= $groupsShift['is_active'] ? 'toggle_on' : 'toggle_off' ?></span>
                                        </button>
                                    </form>
                                    <button onclick="confirmDelete(<?= $groupsShift['id'] ?>, '<?= esc($groupsShift['group']) ?>')" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">No groups shift found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Create Groups Shift</h2>
        <form id="createForm" action="<?= base_url('config/groups-shift/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><span class="text-red-500">*</span>Group:</label>
                <select name="group" id="createGroup" required
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Select group...</option>
                    <?php foreach ($staffGroups as $staffGroup): ?>
                        <option value="<?= esc($staffGroup['code']) ?>"><?= esc($staffGroup['code']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><span class="text-red-500">*</span>Code:</label>
                <input type="text" name="code" id="createCode" required placeholder="Enter code..."
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><span class="text-red-500">*</span>Name:</label>
                <input type="text" name="name" id="createName" required placeholder="Enter name..."
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><span class="text-red-500">*</span>Status:</label>
                <select name="status" id="createStatus" required
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="ACTIVE">ACTIVE</option>
                    <option value="INACTIVE">INACTIVE</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeCreateModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Edit Config</h2>
        </div>
        <form id="editForm" action="<?= base_url('config/groups-shift/update') ?>" method="POST" class="flex flex-col flex-1 min-h-0">
            <div class="p-6 overflow-y-auto flex-1">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editId">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><span class="text-red-500">*</span>Group:</label>
                    <select name="group" id="editGroup" required
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select group...</option>
                        <?php foreach ($staffGroups as $staffGroup): ?>
                            <option value="<?= esc($staffGroup['code']) ?>"><?= esc($staffGroup['code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><span class="text-red-500">*</span>Code:</label>
                    <input type="text" name="code" id="editCode" required
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><span class="text-red-500">*</span>Name:</label>
                    <input type="text" name="name" id="editName" required
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><span class="text-red-500">*</span>Start Time:</label>
                    <input type="time" name="start_time" id="editStartTime"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><span class="text-red-500">*</span>End Time:</label>
                    <input type="time" name="end_time" id="editEndTime"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><span class="text-red-500">*</span>Color:</label>
                    <select name="color" id="editColor"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select color...</option>
                        <option value="red" style="color: red;">Red</option>
                        <option value="blue" style="color: blue;">Blue</option>
                        <option value="green" style="color: green;">Green</option>
                        <option value="orange" style="color: orange;">Orange</option>
                        <option value="purple" style="color: purple;">Purple</option>
                        <option value="pink" style="color: pink;">Pink</option>
                        <option value="yellow" style="color: #d4af37;">Yellow</option>
                        <option value="cyan" style="color: cyan;">Cyan</option>
                        <option value="lime" style="color: lime;">Lime</option>
                        <option value="brown" style="color: brown;">Brown</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><span class="text-red-500">*</span>Status:</label>
                    <select name="status" id="editStatus" required
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="INACTIVE">INACTIVE</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><span class="text-red-500">*</span>Default:</label>
                    <select name="is_default" id="editDefault" required
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="NO">NO</option>
                        <option value="YES">YES</option>
                    </select>
                </div>
            </div>
            </div>
            <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

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
        <p class="text-gray-600 dark:text-gray-300 mb-6" id="deleteMessage">Are you sure you want to delete this groups shift?</p>
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
function openAddModal() {
    document.getElementById('createGroup').value = '';
    document.getElementById('createCode').value = '';
    document.getElementById('createName').value = '';
    document.getElementById('createStatus').value = 'ACTIVE';
    document.getElementById('createModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}

function editGroupsShift(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('editGroup').value = data.group || '';
    document.getElementById('editCode').value = data.code || '';
    document.getElementById('editName').value = data.name || '';
    document.getElementById('editStartTime').value = data.start_time || '';
    document.getElementById('editEndTime').value = data.end_time || '';
    document.getElementById('editColor').value = data.color || '';
    document.getElementById('editStatus').value = data.status || 'ACTIVE';
    document.getElementById('editDefault').value = data.is_default || 'NO';
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

let deleteUrl = '';

function confirmDelete(id, itemName) {
    deleteUrl = '<?= base_url('config/groups-shift/delete/') ?>' + id;
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
