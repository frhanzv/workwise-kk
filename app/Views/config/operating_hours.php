<?= $this->include('templates/header') ?>

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config') ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-gray-900 dark:text-white text-xl font-bold leading-tight">List of Operating Hours</h1>
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Day</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Start Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">End Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (!empty($hours)): ?>
                    <?php foreach ($hours as $hour): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white"><?= esc($hour['day']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= date('h:i A', strtotime($hour['start_time'])) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= date('h:i A', strtotime($hour['end_time'])) ?></td>
                            <td class="px-4 py-3">
                                <?php if ($hour['is_active']): ?>
                                    <span class="px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/20 rounded-full">Active</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-400 bg-gray-100 dark:bg-gray-900/20 rounded-full">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='editHour(<?= json_encode($hour) ?>)' class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <form action="<?= base_url('config/operating-hours/toggle/' . $hour['id']) ?>" method="POST" class="inline">
                                        <button type="submit" class="text-gray-600 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                            <span class="material-symbols-outlined text-lg"><?= $hour['is_active'] ? 'toggle_on' : 'toggle_off' ?></span>
                                        </button>
                                    </form>
                                    <button onclick="confirmDelete('<?= base_url('config/operating-hours/delete/' . $hour['id']) ?>', '<?= esc($hour['day']) ?>')" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No operating hours found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="hourModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
        <h2 id="modalTitle" class="text-xl font-bold text-gray-900 dark:text-white mb-4">New Operating Hours</h2>
        <form id="hourForm" method="POST">
            <input type="hidden" id="hourId" name="id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Day</label>
                <select id="hourDay" name="day" required class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Select</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Time</label>
                <div class="flex gap-2 items-center">
                    <input type="number" id="startHour" name="start_hour" min="1" max="12" placeholder="HH" required
                           class="w-20 px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-center focus:ring-2 focus:ring-primary focus:border-transparent">
                    <span class="text-gray-500">:</span>
                    <input type="number" id="startMinute" name="start_minute" min="0" max="59" placeholder="MM" required
                           class="w-20 px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-center focus:ring-2 focus:ring-primary focus:border-transparent">
                    <select id="startPeriod" name="start_period" required class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="AM">AM</option>
                        <option value="PM">PM</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Time</label>
                <div class="flex gap-2 items-center">
                    <input type="number" id="endHour" name="end_hour" min="1" max="12" placeholder="HH" required
                           class="w-20 px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-center focus:ring-2 focus:ring-primary focus:border-transparent">
                    <span class="text-gray-500">:</span>
                    <input type="number" id="endMinute" name="end_minute" min="0" max="59" placeholder="MM" required
                           class="w-20 px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-center focus:ring-2 focus:ring-primary focus:border-transparent">
                    <select id="endPeriod" name="end_period" required class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="AM">AM</option>
                        <option value="PM">PM</option>
                    </select>
                </div>
            </div>

            <div id="statusField" class="mb-6 hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select id="hourStatus" name="status" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6" id="deleteMessage">Are you sure you want to delete this operating hour?</p>
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
    document.getElementById('modalTitle').textContent = 'New Operating Hours';
    document.getElementById('hourForm').action = '<?= base_url('config/operating-hours/store') ?>';
    document.getElementById('hourId').value = '';
    document.getElementById('hourDay').value = '';
    document.getElementById('startHour').value = '';
    document.getElementById('startMinute').value = '';
    document.getElementById('startPeriod').value = 'AM';
    document.getElementById('endHour').value = '';
    document.getElementById('endMinute').value = '';
    document.getElementById('endPeriod').value = 'AM';
    document.getElementById('hourStatus').value = '1';
    document.getElementById('statusField').classList.add('hidden');
    document.getElementById('hourModal').classList.remove('hidden');
}

function editHour(hour) {
    document.getElementById('modalTitle').textContent = 'Edit Operating Hours';
    document.getElementById('hourForm').action = '<?= base_url('config/operating-hours/update') ?>';
    document.getElementById('hourId').value = hour.id;
    document.getElementById('hourDay').value = hour.day;
    
    // Parse start time
    let startTime = new Date('1970-01-01T' + hour.start_time);
    let startHours = startTime.getHours();
    let startPeriod = startHours >= 12 ? 'PM' : 'AM';
    startHours = startHours % 12 || 12;
    document.getElementById('startHour').value = startHours;
    document.getElementById('startMinute').value = String(startTime.getMinutes()).padStart(2, '0');
    document.getElementById('startPeriod').value = startPeriod;
    
    // Parse end time
    let endTime = new Date('1970-01-01T' + hour.end_time);
    let endHours = endTime.getHours();
    let endPeriod = endHours >= 12 ? 'PM' : 'AM';
    endHours = endHours % 12 || 12;
    document.getElementById('endHour').value = endHours;
    document.getElementById('endMinute').value = String(endTime.getMinutes()).padStart(2, '0');
    document.getElementById('endPeriod').value = endPeriod;
    
    document.getElementById('hourStatus').value = hour.is_active ? '1' : '0';
    document.getElementById('statusField').classList.remove('hidden');
    document.getElementById('hourModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('hourModal').classList.add('hidden');
}

let deleteUrl = '';

function confirmDelete(url, itemName) {
    deleteUrl = url;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete the operating hours for "${itemName}"?`;
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
