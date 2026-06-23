<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6">
    <!-- Back Button and Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="<?= base_url('workers/list') ?>" class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white"><?= esc($worker['first_name'] . ' ' . $worker['last_name']) ?></h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Worker Profile & Access Details</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= base_url('workers/edit/' . urlencode($worker['worker_id'])) ?>" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-[20px]">edit</span>
                <span>Edit Worker</span>
            </a>
            <button onclick="confirmDeleteWorker('<?= base_url('workers/delete/' . urlencode($worker['worker_id'])) ?>', '<?= esc($worker['first_name'] . ' ' . $worker['last_name']) ?>')" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                <span class="material-symbols-outlined text-[20px]">delete</span>
                <span>Delete</span>
            </button>
        </div>
    </div>

    <!-- Worker Profile Card -->
    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="bg-gradient-to-r from-primary to-blue-600 h-32"></div>
        <div class="px-6 pb-6">
            <div class="flex flex-col md:flex-row gap-6 -mt-16">
                <!-- Profile Photo -->
                <div class="flex-shrink-0">
                    <?php if (!empty($worker['profile_photo'])): ?>
                        <img src="<?= base_url('uploads/profiles/' . $worker['profile_photo']) ?>" alt="Profile Photo" class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 object-cover shadow-lg">
                    <?php else: ?>
                        <?php 
                        $nameParts = explode(' ', trim($worker['first_name'] . ' ' . $worker['last_name']));
                        $initials = strtoupper(
                            (isset($nameParts[0]) ? substr($nameParts[0], 0, 1) : '') .
                            (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '')
                        );
                        ?>
                        <div class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <span class="text-white text-4xl font-bold"><?= $initials ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Worker Info -->
                <div class="flex-1 mt-4 md:mt-20">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= esc($worker['first_name'] . ' ' . $worker['last_name']) ?></h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1"><?= esc($worker['position']) ?> • <?= esc(ucwords($worker['department'])) ?></p>
                    
                    <div class="flex flex-wrap items-center gap-3 mt-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-<?= $worker['status'] === 'active' ? 'green' : ($worker['status'] === 'inactive' ? 'red' : 'yellow') ?>-100 text-<?= $worker['status'] === 'active' ? 'green' : ($worker['status'] === 'inactive' ? 'red' : 'yellow') ?>-800 dark:bg-<?= $worker['status'] === 'active' ? 'green' : ($worker['status'] === 'inactive' ? 'red' : 'yellow') ?>-900/30 dark:text-<?= $worker['status'] === 'active' ? 'green' : ($worker['status'] === 'inactive' ? 'red' : 'yellow') ?>-300 border border-<?= $worker['status'] === 'active' ? 'green' : ($worker['status'] === 'inactive' ? 'red' : 'yellow') ?>-200 dark:border-<?= $worker['status'] === 'active' ? 'green' : ($worker['status'] === 'inactive' ? 'red' : 'yellow') ?>-800">
                            <span class="size-1.5 rounded-full bg-<?= $worker['status'] === 'active' ? 'green' : ($worker['status'] === 'inactive' ? 'red' : 'yellow') ?>-500"></span>
                            <?= ucfirst(esc($worker['status'])) ?>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            <?= ucfirst(esc($worker['shift'])) ?> Shift
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <div class="flex gap-0">
                <button onclick="switchTab('attendance')" id="tabAttendance" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-primary text-primary bg-blue-50 dark:bg-blue-900/20">
                    Attendance
                </button>
                <button onclick="switchTab('details')" id="tabDetails" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Details
                </button>
                <button onclick="switchTab('groups')" id="tabGroups" class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Groups
                </button>
            </div>
        </div>

        <!-- Attendance Tab Content -->
        <div id="contentAttendance" class="tab-content p-6">
            <?= $this->include('workers/partials/attendance_calendar') ?>
        </div>

        <!-- Details Tab Content -->
        <div id="contentDetails" class="tab-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
                <!-- Left Column - Personal Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Contact Information -->
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary">contact_mail</span>
                    Contact Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Email Address</label>
                        <p class="text-sm text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-400 text-lg">mail</span>
                            <?= esc($worker['email']) ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Phone Number</label>
                        <p class="text-sm text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-400 text-lg">phone</span>
                            <?= esc($worker['phone'] ?: 'Not provided') ?>
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Address</label>
                        <p class="text-sm text-gray-900 dark:text-white flex items-start gap-2">
                            <span class="material-symbols-outlined text-gray-400 text-lg">location_on</span>
                            <?= esc($worker['address'] ?: 'Not provided') ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">City</label>
                        <p class="text-sm text-gray-900 dark:text-white"><?= esc($worker['city_name'] ?? 'Not provided') ?></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">State</label>
                        <p class="text-sm text-gray-900 dark:text-white"><?= esc($worker['state_name'] ?? 'Not provided') ?></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Country</label>
                        <p class="text-sm text-gray-900 dark:text-white"><?= esc($worker['country_name'] ?? 'Not provided') ?></p>
                    </div>
                </div>
            </div>

            <!-- Assigned Zones -->
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary">grid_view</span>
                    Assigned Zones
                    <span class="ml-auto text-xs font-medium px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full"><?= count($assignedZones) ?> Zones</span>
                </h2>
                
                <?php if (!empty($assignedZones)): ?>
                    <div class="space-y-2">
                        <?php foreach ($assignedZones as $zone): ?>
                            <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                                <div class="w-10 h-10 rounded-full bg-<?= $zone['icon_color'] ?>-100 dark:bg-<?= $zone['icon_color'] ?>-900/30 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-<?= $zone['icon_color'] ?>-600 dark:text-<?= $zone['icon_color'] ?>-400 text-lg"><?= $zone['icon'] ?></span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?= esc($zone['zone_id']) ?> - <?= esc($zone['zone_name']) ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($zone['location'] ?: 'No location specified') ?></p>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $zone['antenna_color'] ?>-100 text-<?= $zone['antenna_color'] ?>-800 dark:bg-<?= $zone['antenna_color'] ?>-900/30 dark:text-<?= $zone['antenna_color'] ?>-300 border border-<?= $zone['antenna_color'] ?>-200 dark:border-<?= $zone['antenna_color'] ?>-800">
                                    <?= esc($zone['antenna_mode']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <span class="material-symbols-outlined text-gray-300 dark:text-gray-600 text-5xl">location_off</span>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">No zones assigned</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column - Work Details -->
        <div class="space-y-6">
            <!-- Work Information -->
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary">badge</span>
                    Work Details
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Worker ID</label>
                        <p class="text-sm font-mono text-gray-900 dark:text-white"><?= esc($worker['worker_id']) ?></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">ID/IC Number</label>
                        <p class="text-sm font-mono text-gray-900 dark:text-white"><?= esc($worker['ic_number'] ?? 'Not provided') ?></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Department</label>
                        <p class="text-sm text-gray-900 dark:text-white"><?= esc(ucwords($worker['department'])) ?></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Position/Role</label>
                        <p class="text-sm text-gray-900 dark:text-white"><?= esc($worker['position']) ?></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Start Date</label>
                        <p class="text-sm text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-400 text-lg">calendar_today</span>
                            <?= date('M d, Y', strtotime($worker['start_date'])) ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Work Shift(s)</label>
                        <?php 
                        $workerShifts = !empty($worker['shift']) ? explode(',', $worker['shift']) : [];
                        $workerShifts = array_map('trim', $workerShifts);
                        ?>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($workerShifts as $shiftName): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <?= ucfirst(esc($shiftName)) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary">history</span>
                    Activity
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Last Active</label>
                        <p class="text-sm text-gray-900 dark:text-white">
                            <?php 
                            if (!empty($worker['last_active'])) {
                                $lastActiveTime = strtotime($worker['last_active']);
                                $diff = time() - $lastActiveTime;
                                if ($diff < 60) {
                                    echo 'Just now';
                                } elseif ($diff < 3600) {
                                    echo floor($diff / 60) . ' minutes ago';
                                } elseif ($diff < 86400) {
                                    echo floor($diff / 3600) . ' hours ago';
                                } else {
                                    echo date('M d, Y \a\t H:i', $lastActiveTime);
                                }
                            } else {
                                echo 'Never';
                            }
                            ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Registered On</label>
                        <p class="text-sm text-gray-900 dark:text-white">
                            <?= date('M d, Y', strtotime($worker['created_at'])) ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Last Updated</label>
                        <p class="text-sm text-gray-900 dark:text-white">
                            <?= date('M d, Y', strtotime($worker['updated_at'])) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>

        <!-- Groups Tab Content -->
        <div id="contentGroups" class="tab-content hidden p-6">
            <div class="text-center py-12">
                <span class="material-symbols-outlined text-gray-300 dark:text-gray-600 text-5xl">groups</span>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Groups information coming soon</p>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">delete</span>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Worker</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone</p>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6" id="deleteMessage">Are you sure you want to delete this worker?</p>
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

<script>
let deleteUrl = '';

function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Reset all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-primary', 'text-primary', 'bg-blue-50', 'dark:bg-blue-900/20');
        button.classList.add('border-transparent', 'text-gray-600', 'dark:text-gray-400');
    });
    
    // Show selected tab content
    document.getElementById('content' + tabName.charAt(0).toUpperCase() + tabName.slice(1)).classList.remove('hidden');
    
    // Highlight selected tab button
    const activeTab = document.getElementById('tab' + tabName.charAt(0).toUpperCase() + tabName.slice(1));
    activeTab.classList.remove('border-transparent', 'text-gray-600', 'dark:text-gray-400');
    activeTab.classList.add('border-primary', 'text-primary', 'bg-blue-50', 'dark:bg-blue-900/20');
}

function confirmDeleteWorker(url, workerName) {
    deleteUrl = url;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete ${workerName}? This action cannot be undone.`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteUrl = '';
}

function executeDelete() {
    if (deleteUrl) {
        window.location.href = deleteUrl;
    }
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>

<?= view('templates/footer') ?>
