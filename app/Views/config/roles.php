<?= $this->include('templates/header') ?>

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6 bg-[#1e293b] dark:bg-gray-900 -mx-6 -mt-6 px-6 py-4">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config') ?>" class="text-white hover:text-gray-300">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-white text-xl font-bold leading-tight">List of Role</h1>
    </div>
    <button onclick="openAddModal()" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined text-lg">add</span>
        Add Role
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Role Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($roles)): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                                No roles found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roles as $role): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white"><?= esc($role['role_name']) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= esc($role['description']) ?></td>
                                <td class="px-4 py-3">
                                    <?php if ($role['is_active']): ?>
                                        <span class="px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/20 rounded-full">Active</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-400 bg-gray-100 dark:bg-gray-900/20 rounded-full">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick='openEditModal(<?= json_encode($role) ?>)' class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </button>
                                        <a href="<?= base_url('config/roles/toggle/' . $role['id']) ?>" class="text-gray-600 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300" onclick="return confirm('Are you sure you want to toggle the status?')">
                                            <span class="material-symbols-outlined text-lg"><?= $role['is_active'] ? 'toggle_on' : 'toggle_off' ?></span>
                                        </a>
                                        <button onclick="confirmDelete(<?= $role['id'] ?>, '<?= esc($role['role_name']) ?>')" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                            <span class="material-symbols-outlined text-lg">delete</span>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6" id="deleteMessage">Are you sure you want to delete this role?</p>
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

<form id="deleteForm" method="GET" style="display: none;"></form>

<!-- Add Role Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-[#1e293b] rounded-lg max-w-4xl w-full my-8">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Add New Role</h3>
        </div>
        <form action="<?= base_url('config/roles/store') ?>" method="POST" class="p-6">
            <?= csrf_field() ?>
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Role Name *</label>
                    <input type="text" name="role_name" required class="w-full px-3 py-2.5 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-[#2d3748] text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2.5 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-[#2d3748] text-white"></textarea>
                </div>
            </div>

            <!-- Permissions Section -->
            <div class="mb-6">
                <h4 class="text-white font-semibold mb-2">Permissions</h4>
                <p class="text-gray-400 text-sm mb-4">Choose what users with this role can do. Technical keys are shown on the right for reference.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 max-h-[400px] overflow-y-auto pr-2">
                    <!-- Antenna Mode List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.antenna_mode" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-purple-400 text-xl">sensors</span>
                            <span class="text-gray-200 text-sm">Antenna Mode List</span>
                        </div>
                    </label>

                    <!-- Department List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.departments" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-400 text-xl">corporate_fare</span>
                            <span class="text-gray-200 text-sm">Department List</span>
                        </div>
                    </label>

                    <!-- Job Position List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.job_positions" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-orange-400 text-xl">badge</span>
                            <span class="text-gray-200 text-sm">Job Position List</span>
                        </div>
                    </label>

                    <!-- Country List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.countries" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-400 text-xl">public</span>
                            <span class="text-gray-200 text-sm">Country List</span>
                        </div>
                    </label>

                    <!-- State List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.states" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-400 text-xl">map</span>
                            <span class="text-gray-200 text-sm">State List</span>
                        </div>
                    </label>

                    <!-- City List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.cities" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-teal-400 text-xl">location_city</span>
                            <span class="text-gray-200 text-sm">City List</span>
                        </div>
                    </label>

                    <!-- Operating Hours -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.operating_hours" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-400 text-xl">schedule</span>
                            <span class="text-gray-200 text-sm">Operating Hours</span>
                        </div>
                    </label>

                    <!-- Shift List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.shifts" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-pink-400 text-xl">work_history</span>
                            <span class="text-gray-200 text-sm">Shift List</span>
                        </div>
                    </label>

                    <!-- Staff Groups -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.staff_groups" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-cyan-400 text-xl">groups</span>
                            <span class="text-gray-200 text-sm">Staff Groups</span>
                        </div>
                    </label>

                    <!-- Groups Shift -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.groups_shift" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-400 text-xl">schedule</span>
                            <span class="text-gray-200 text-sm">Groups Shift</span>
                        </div>
                    </label>

                    <!-- Staff Availability -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.staff_availability" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-teal-400 text-xl">how_to_reg</span>
                            <span class="text-gray-200 text-sm">Staff Availability</span>
                        </div>
                    </label>

                    <!-- Staff Shift Allocation -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.staff_shift_allocation" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-400 text-xl">calendar_month</span>
                            <span class="text-gray-200 text-sm">Staff Shift Allocation</span>
                        </div>
                    </label>

                    <!-- Public Holidays -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.public_holidays" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-rose-400 text-xl">celebration</span>
                            <span class="text-gray-200 text-sm">Public Holidays</span>
                        </div>
                    </label>

                    <!-- Leave Reason List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.leave_reasons" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-yellow-400 text-xl">event_busy</span>
                            <span class="text-gray-200 text-sm">Leave Reason List</span>
                        </div>
                    </label>

                    <!-- Role List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.roles" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-violet-400 text-xl">admin_panel_settings</span>
                            <span class="text-gray-200 text-sm">Role List</span>
                        </div>
                    </label>

                    <!-- RFID Settings -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.rfid_settings" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-400 text-xl">settings</span>
                            <span class="text-gray-200 text-sm">RFID Settings</span>
                        </div>
                    </label>

                    <!-- Dashboard -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="dashboard.view" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-400 text-xl">dashboard</span>
                            <span class="text-gray-200 text-sm">Dashboard</span>
                        </div>
                    </label>

            <!-- Zones -->
            <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                <input type="checkbox" name="permissions[]" value="zones.view" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-400 text-xl">location_on</span>
                    <span class="text-gray-200 text-sm">Zones</span>
                </div>
            </label>

            <!-- Workers -->
            <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                <input type="checkbox" name="permissions[]" value="workers.view" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-purple-400 text-xl">badge</span>
                    <span class="text-gray-200 text-sm">Workers</span>
                </div>
            </label>

            <!-- Reports -->
            <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                <input type="checkbox" name="permissions[]" value="reports.view" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-400 text-xl">assessment</span>
                    <span class="text-gray-200 text-sm">Reports</span>
                </div>
            </label>
                </div> <!-- Close grid -->
            </div> <!-- Close permissions section -->

            <div class="flex gap-3 justify-end border-t border-gray-700 pt-4">
                <button type="button" onclick="closeAddModal()" class="px-6 py-2.5 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Add Role
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Role Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-[#1e293b] rounded-lg max-w-4xl w-full my-8">
        <div class="p-6 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Edit Role</h3>
        </div>
        <form action="<?= base_url('config/roles/update') ?>" method="POST" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit_id">
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Role Name *</label>
                    <input type="text" name="role_name" id="edit_role_name" required class="w-full px-3 py-2.5 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-[#2d3748] text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <textarea name="description" id="edit_description" rows="3" class="w-full px-3 py-2.5 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-[#2d3748] text-white"></textarea>
                </div>
            </div>

            <!-- Permissions Section -->
            <div class="mb-6">
                <h4 class="text-white font-semibold mb-2">Permissions</h4>
                <p class="text-gray-400 text-sm mb-4">Choose what users with this role can do. Technical keys are shown on the right for reference.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 max-h-[400px] overflow-y-auto pr-2">
                    <!-- Antenna Mode List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.antenna_mode" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-purple-400 text-xl">sensors</span>
                            <span class="text-gray-200 text-sm">Antenna Mode List</span>
                        </div>
                    </label>

                    <!-- Department List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.departments" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-400 text-xl">corporate_fare</span>
                            <span class="text-gray-200 text-sm">Department List</span>
                        </div>
                    </label>

                    <!-- Job Position List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.job_positions" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-orange-400 text-xl">badge</span>
                            <span class="text-gray-200 text-sm">Job Position List</span>
                        </div>
                    </label>

                    <!-- Country List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.countries" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-400 text-xl">public</span>
                            <span class="text-gray-200 text-sm">Country List</span>
                        </div>
                    </label>

                    <!-- State List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.states" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-400 text-xl">map</span>
                            <span class="text-gray-200 text-sm">State List</span>
                        </div>
                    </label>

                    <!-- City List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.cities" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-teal-400 text-xl">location_city</span>
                            <span class="text-gray-200 text-sm">City List</span>
                        </div>
                    </label>

                    <!-- Operating Hours -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.operating_hours" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-400 text-xl">schedule</span>
                            <span class="text-gray-200 text-sm">Operating Hours</span>
                        </div>
                    </label>

                    <!-- Shift List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.shifts" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-pink-400 text-xl">work_history</span>
                            <span class="text-gray-200 text-sm">Shift List</span>
                        </div>
                    </label>

                    <!-- Staff Groups -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.staff_groups" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-cyan-400 text-xl">groups</span>
                            <span class="text-gray-200 text-sm">Staff Groups</span>
                        </div>
                    </label>

                    <!-- Groups Shift -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.groups_shift" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-400 text-xl">schedule</span>
                            <span class="text-gray-200 text-sm">Groups Shift</span>
                        </div>
                    </label>

                    <!-- Staff Availability -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.staff_availability" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-teal-400 text-xl">how_to_reg</span>
                            <span class="text-gray-200 text-sm">Staff Availability</span>
                        </div>
                    </label>

                    <!-- Staff Shift Allocation -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.staff_shift_allocation" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-400 text-xl">calendar_month</span>
                            <span class="text-gray-200 text-sm">Staff Shift Allocation</span>
                        </div>
                    </label>

                    <!-- Public Holidays -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.public_holidays" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-rose-400 text-xl">celebration</span>
                            <span class="text-gray-200 text-sm">Public Holidays</span>
                        </div>
                    </label>

                    <!-- Leave Reason List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.leave_reasons" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-yellow-400 text-xl">event_busy</span>
                            <span class="text-gray-200 text-sm">Leave Reason List</span>
                        </div>
                    </label>

                    <!-- Role List -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.roles" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-violet-400 text-xl">admin_panel_settings</span>
                            <span class="text-gray-200 text-sm">Role List</span>
                        </div>
                    </label>

                    <!-- RFID Settings -->
                    <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
                        <input type="checkbox" name="permissions[]" value="config.rfid_settings" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-400 text-xl">settings</span>
                            <span class="text-gray-200 text-sm">RFID Settings</span>
                        </div>
                    </label>

                    <!-- Dashboard -->
        <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
            <input type="checkbox" name="permissions[]" value="dashboard.view" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-400 text-xl">dashboard</span>
                <span class="text-gray-200 text-sm">Dashboard</span>
            </div>
        </label>

        <!-- Zones -->
        <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
            <input type="checkbox" name="permissions[]" value="zones.view" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-green-400 text-xl">location_on</span>
                <span class="text-gray-200 text-sm">Zones</span>
            </div>
        </label>

        <!-- Workers -->
        <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
            <input type="checkbox" name="permissions[]" value="workers.view" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-400 text-xl">badge</span>
                <span class="text-gray-200 text-sm">Workers</span>
            </div>
        </label>

        <!-- Reports -->
        <label class="flex items-center gap-3 p-3 bg-gray-700/30 hover:bg-gray-700/50 rounded-lg cursor-pointer border border-gray-600">
            <input type="checkbox" name="permissions[]" value="reports.view" class="w-4 h-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-orange-400 text-xl">assessment</span>
                <span class="text-gray-200 text-sm">Reports</span>
            </div>
        </label>
    </div>
</div>

            <div class="flex gap-3 justify-end border-t border-gray-700 pt-4">
                <button type="button" onclick="closeEditModal()" class="px-6 py-2.5 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Update Role
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openEditModal(role) {
    document.getElementById('edit_id').value = role.id;
    document.getElementById('edit_role_name').value = role.role_name;
    document.getElementById('edit_description').value = role.description || '';
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

let deleteUrl = '';

function confirmDelete(id, itemName) {
    deleteUrl = '<?= base_url('config/roles/delete/') ?>' + id;
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

// Close modals when clicking outside
document.getElementById('addModal').addEventListener('click', function(e) {
    if (e.target === this) closeAddModal();
});

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
        closeEditModal();
        closeDeleteModal();
    }
});
</script>

<?= $this->include('templates/footer') ?>