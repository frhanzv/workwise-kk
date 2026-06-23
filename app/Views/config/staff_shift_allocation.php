<?= $this->include('templates/header') ?>

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config') ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-gray-900 dark:text-white text-xl font-bold leading-tight">Staff Shift Allocation Config</h1>
    </div>
    <a href="<?= base_url('config/staff-shift-allocation/add') ?>" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined text-lg">add</span>
        Create
    </a>
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Group</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Start Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">End Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Total Days</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (!empty($allocations)): ?>
                    <?php foreach ($allocations as $allocation): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white"><?= esc($allocation['group_id']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= date('d M Y', strtotime($allocation['start_date'])) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= date('d M Y', strtotime($allocation['end_date'])) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= esc($allocation['total_days']) ?></td>
                            <td class="px-4 py-3">
                                <?php if ($allocation['is_active']): ?>
                                    <span class="px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/20 rounded-full">Active</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-400 bg-gray-100 dark:bg-gray-900/20 rounded-full">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?= base_url('config/staff-shift-allocation/edit?groupId=' . urlencode($allocation['group_id']) . '&startDate=' . $allocation['start_date'] . '&endDate=' . $allocation['end_date']) ?>" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <button onclick="confirmDelete('<?= esc($allocation['group_id']) ?>', '<?= $allocation['start_date'] ?>', '<?= $allocation['end_date'] ?>')" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">No shift allocations found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6" id="deleteMessage">Are you sure you want to delete this allocation?</p>
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

function confirmDelete(groupId, startDate, endDate) {
    deleteUrl = '<?= base_url('config/staff-shift-allocation/delete') ?>?groupId=' + groupId + '&startDate=' + startDate + '&endDate=' + endDate;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete allocation for "${groupId}" from ${startDate} to ${endDate}?`;
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

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config') ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-gray-900 dark:text-white text-xl font-bold leading-tight">Staff Shift Allocation Config</h1>
    </div>
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

<form id="allocationForm" action="<?= base_url('config/staff-shift-allocation/save') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="group_id" id="groupId" value="<?= esc($selectedGroupId ?? '') ?>">
    <input type="hidden" name="start_date" id="startDateInput" value="<?= esc($startDate ?? '') ?>">
    <input type="hidden" name="end_date" id="endDateInput" value="<?= esc($endDate ?? '') ?>">
    
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <button type="button" onclick="window.location.href='<?= base_url('config') ?>'" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                Back
            </button>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                Save
            </button>
            <button type="button" onclick="openNewModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                New
            </button>
            <button type="button" onclick="openCopyModal()" class="ml-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">content_copy</span>
                Copy Sequence
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <select id="groupSelect" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Select group...</option>
                    <?php foreach ($groupsShifts as $gs): ?>
                        <option value="<?= esc($gs['group']) ?>"><?= esc($gs['group']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <input type="date" id="startDate" value="" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <input type="date" id="endDate" value="" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <button type="button" onclick="loadAllocations()" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">refresh</span>
                    Load
                </button>
            </div>
        </div>
        
        <?php if (!empty($dateRange)): ?>
            <div class="overflow-x-auto">
                <div class="grid gap-4" style="grid-template-columns: repeat(<?= count($dateRange) ?>, minmax(120px, 1fr));">
                    <?php foreach ($dateRange as $date): 
                        $dateObj = new DateTime($date);
                        $dayMonth = $dateObj->format('d M');
                    ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-center"><?= $dayMonth ?></label>
                            <select name="allocations[<?= $date ?>]" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                <option value="">-</option>
                                <?php foreach ($shiftCodes as $sc): ?>
                                    <option value="<?= esc($sc['code']) ?>" <?= (isset($allocations[$date]) && $allocations[$date] == $sc['code']) ? 'selected' : '' ?>><?= esc($sc['code']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <span class="material-symbols-outlined text-5xl mb-3 opacity-50">event</span>
                <p class="text-sm">Select a group and date range, then click Load to view allocations</p>
            </div>
        <?php endif; ?>
    </div>
</form>

<!-- New Allocation Modal -->
<div id="newModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-md">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">New Allocation</h2>
        </div>
        <div class="p-6">
            <p class="text-gray-600 dark:text-gray-400 mb-4">This will clear the current form. Continue?</p>
            <div class="flex gap-3">
                <button onclick="closeNewModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button onclick="resetForm()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Copy Sequence Modal -->
<div id="copyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-md">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Copy Sequence</h2>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Target Start Date:</label>
                <input type="date" id="targetStartDate" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div class="flex gap-3">
                <button onclick="closeCopyModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button onclick="copySequence()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Copy
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function loadAllocations() {
    const groupId = document.getElementById('groupSelect').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!groupId || !startDate || !endDate) {
        alert('Please select group and date range');
        return;
    }
    
    window.location.href = `<?= base_url('config/staff-shift-allocation') ?>?groupId=${groupId}&startDate=${startDate}&endDate=${endDate}`;
}

function openNewModal() {
    document.getElementById('newModal').classList.remove('hidden');
}

function closeNewModal() {
    document.getElementById('newModal').classList.add('hidden');
}

function resetForm() {
    window.location.href = '<?= base_url('config/staff-shift-allocation') ?>';
}

function openCopyModal() {
    const groupId = document.getElementById('groupSelect').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!groupId || !startDate || !endDate) {
        alert('Please load allocations first');
        return;
    }
    
    document.getElementById('copyModal').classList.remove('hidden');
}

function closeCopyModal() {
    document.getElementById('copyModal').classList.add('hidden');
}

function copySequence() {
    const groupId = document.getElementById('groupSelect').value;
    const sourceStartDate = document.getElementById('startDate').value;
    const sourceEndDate = document.getElementById('endDate').value;
    const targetStartDate = document.getElementById('targetStartDate').value;
    
    if (!targetStartDate) {
        alert('Please select target start date');
        return;
    }
    
    fetch('<?= base_url('config/staff-shift-allocation/copy') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            group_id: groupId,
            source_start_date: sourceStartDate,
            source_end_date: sourceEndDate,
            target_start_date: targetStartDate,
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeCopyModal();
            loadAllocations();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert('An error occurred');
        console.error(error);
    });
}
</script>

<?= $this->include('templates/footer') ?>
