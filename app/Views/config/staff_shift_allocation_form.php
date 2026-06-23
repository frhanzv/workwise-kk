<?= $this->include('templates/header') ?>

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config/staff-shift-allocation') ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-gray-900 dark:text-white text-xl font-bold leading-tight"><?= $mode == 'edit' ? 'Edit' : 'Add' ?> Staff Shift Allocation</h1>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-4 p-3 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg text-sm">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<form id="allocationForm" action="<?= base_url('config/staff-shift-allocation/save') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="mode" value="<?= $mode ?>">
    
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <a href="<?= base_url('config/staff-shift-allocation') ?>" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                Back
            </a>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                Save
            </button>
            <?php if ($mode == 'add'): ?>
            <button type="button" onclick="window.location.href='<?= base_url('config/staff-shift-allocation/add') ?>'" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                New
            </button>
            <?php endif; ?>
            <?php if ($mode == 'edit'): ?>
            <button type="button" onclick="openCopyModal()" class="ml-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">content_copy</span>
                Copy Sequence
            </button>
            <?php endif; ?>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div>
                <select name="group_id" id="groupSelect" required class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Select group...</option>
                    <?php 
                    $uniqueGroups = [];
                    foreach ($groupsShifts as $gs): 
                        if (!in_array($gs['group'], $uniqueGroups)):
                            $uniqueGroups[] = $gs['group'];
                    ?>
                        <option value="<?= esc($gs['group']) ?>" <?= (isset($selectedGroupId) && $selectedGroupId == $gs['group']) ? 'selected' : '' ?>><?= esc($gs['group']) ?></option>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </select>
            </div>
            <div>
                <input type="date" name="start_date" id="startDate" required value="<?= esc($startDate ?? '') ?>" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <input type="date" name="end_date" id="endDate" required value="<?= esc($endDate ?? '') ?>" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            <div>
                <button type="button" onclick="loadDateRange()" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">refresh</span>
                </button>
            </div>
            <div>
                <button type="button" onclick="addMoreDates()" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">add</span>
                    Add
                </button>
            </div>
        </div>
        
        <div id="dateRangeContainer">
            <?php if (!empty($dateRange)): ?>
                <div class="overflow-x-auto">
                    <div class="grid gap-4" style="grid-template-columns: repeat(<?= min(count($dateRange), 7) ?>, minmax(120px, 1fr));">
                        <?php foreach ($dateRange as $index => $date): 
                            $dateObj = new DateTime($date);
                            $dayMonth = $dateObj->format('d M');
                            if ($index % 7 == 0 && $index > 0): ?>
                                </div><div class="grid gap-4 mt-4" style="grid-template-columns: repeat(<?= min(count($dateRange) - $index, 7) ?>, minmax(120px, 1fr));">
                            <?php endif; ?>
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
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Copy Sequence Modal -->
<?php if ($mode == 'edit'): ?>
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
<?php endif; ?>

<script>
function loadDateRange() {
    const groupId = document.getElementById('groupSelect').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!groupId || !startDate || !endDate) {
        alert('Please select group and both dates');
        return;
    }
    
    window.location.href = `<?= base_url('config/staff-shift-allocation/add') ?>?groupId=${groupId}&startDate=${startDate}&endDate=${endDate}`;
}

function addMoreDates() {
    alert('Add more dates functionality - extends the date range');
}

<?php if ($mode == 'edit'): ?>
function openCopyModal() {
    document.getElementById('copyModal').classList.remove('hidden');
}

function closeCopyModal() {
    document.getElementById('copyModal').classList.add('hidden');
}

function copySequence() {
    const groupId = '<?= $selectedGroupId ?>';
    const sourceStartDate = '<?= $startDate ?>';
    const sourceEndDate = '<?= $endDate ?>';
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
            window.location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert('An error occurred');
        console.error(error);
    });
}
<?php endif; ?>
</script>

<?= $this->include('templates/footer') ?>
