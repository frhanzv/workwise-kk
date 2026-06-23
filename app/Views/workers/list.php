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
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Worker List</h1>
            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Manage all registered workers, view details, and update records.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openUploadModal()" class="flex items-center justify-center h-10 px-4 gap-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-base">upload_file</span>
                <span>Upload List</span>
            </button>
            <a href="<?= base_url('workers/add') ?>" class="flex items-center justify-center h-10 px-4 gap-2 bg-primary text-white rounded-lg text-sm font-bold tracking-wide hover:bg-primary/90 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Add Worker</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                <span class="material-symbols-outlined text-2xl">group</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Workers</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= number_format($stats['total_workers']) ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                <span class="material-symbols-outlined text-2xl">check_circle</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Active Today</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= number_format($stats['active_today']) ?></p>
            </div>
        </div>
        <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <div class="p-3 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400">
                <span class="material-symbols-outlined text-2xl">warning</span>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Inactive/On Leave</p>
                <p class="text-gray-900 dark:text-white text-2xl font-bold"><?= number_format($stats['inactive']) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col flex-1">
        <div class="flex flex-wrap justify-between items-center gap-4 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 flex-1 min-w-[280px]">
                <div class="relative w-full max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xl">search</span>
                    <input id="searchInput" class="pl-10 w-full h-11 bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary placeholder-gray-500 dark:placeholder-gray-400 transition-colors" placeholder="Search by name, ID, or department..." type="text"/>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <button id="filterBtn" class="flex items-center justify-center h-10 px-4 gap-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined text-base">filter_list</span>
                        <span>Filters</span>
                        <span id="filterBadge" class="hidden ml-1 px-1.5 py-0.5 text-xs font-bold bg-primary text-white rounded-full"></span>
                    </button>
                    <!-- Filter Dropdown -->
                    <div id="filterDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
                        <div class="p-4 space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Filter Workers</h3>
                                <button onclick="clearFilters()" class="text-xs text-primary hover:underline">Clear All</button>
                            </div>
                            
                            <!-- Department Filter -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                                <select id="departmentFilter" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm p-2">
                                    <option value="">All Departments</option>
                                    <?php if (!empty($departments)): ?>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= strtolower(esc($dept['name'])) ?>"><?= esc($dept['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <!-- Status Filter -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <select id="statusFilter" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm p-2">
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on break">On Break</option>
                                    <option value="offline">Offline</option>
                                </select>
                            </div>
                            
                            <!-- Shift Filter -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Shift</label>
                                <select id="shiftFilter" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm p-2">
                                    <option value="">All Shifts</option>
                                    <?php if (!empty($shifts)): ?>
                                        <?php foreach ($shifts as $shift): ?>
                                            <option value="<?= esc($shift['name']) ?>"><?= esc(ucfirst($shift['name'])) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <button onclick="applyFilters()" class="w-full px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
                <button onclick="exportToCSV()" class="flex items-center justify-center h-10 px-4 gap-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800">
                    <span class="material-symbols-outlined text-base">download</span>
                    <span>Export</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 font-semibold" scope="col">
                            <div class="flex items-center gap-2 cursor-pointer group">
                                Name
                                <span class="material-symbols-outlined text-base text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300">unfold_more</span>
                            </div>
                        </th>
                        <th class="px-6 py-4 font-semibold" scope="col">ID Number</th>
                        <th class="px-6 py-4 font-semibold" scope="col">Department</th>
                        <th class="px-6 py-4 font-semibold" scope="col">Role</th>
                        <th class="px-6 py-4 font-semibold" scope="col">Shift(s)</th>
                        <th class="px-6 py-4 font-semibold" scope="col">Total Zones</th>
                        <th class="px-6 py-4 font-semibold" scope="col">Status</th>
                        <th class="px-6 py-4 font-semibold" scope="col">Last Active</th>
                        <th class="px-6 py-4 font-semibold text-right" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($workers as $worker): ?>
                        <tr class="bg-white dark:bg-background-dark hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors cursor-pointer" data-shift="<?= esc($worker['shift']) ?>" onclick="window.location='<?= base_url('workers/view/' . urlencode($worker['id_number'])) ?>'">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($worker['profile_photo'])): ?>
                                        <img src="<?= base_url('uploads/profiles/' . $worker['profile_photo']) ?>" alt="<?= esc($worker['name']) ?>" class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                                    <?php else: ?>
                                        <div class="w-8 h-8 rounded-full bg-<?= $worker['color'] ?>-100 dark:bg-<?= $worker['color'] ?>-900/30 flex items-center justify-center text-<?= $worker['color'] ?>-700 dark:text-<?= $worker['color'] ?>-300 font-semibold text-xs border border-<?= $worker['color'] ?>-200 dark:border-<?= $worker['color'] ?>-800">
                                            <?= esc($worker['initials']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex flex-col">
                                        <p class="text-gray-900 dark:text-white font-medium"><?= esc($worker['name']) ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($worker['email']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-gray-600 dark:text-gray-300"><?= esc($worker['id_number']) ?></td>
                            <td class="px-6 py-4"><?= esc($worker['department']) ?></td>
                            <td class="px-6 py-4"><?= esc($worker['role']) ?></td>
                            <td class="px-6 py-4">
                                <?php 
                                $workerShifts = !empty($worker['shift']) ? explode(',', $worker['shift']) : [];
                                $workerShifts = array_map('trim', $workerShifts);
                                ?>
                                <div class="flex flex-wrap gap-1">
                                    <?php foreach ($workerShifts as $shiftName): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            <?= ucfirst(esc($shiftName)) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4"><?= esc($worker['total_zones']) ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= $worker['status_color'] ?>-100 text-<?= $worker['status_color'] ?>-800 dark:bg-<?= $worker['status_color'] ?>-900/30 dark:text-<?= $worker['status_color'] ?>-400 border border-<?= $worker['status_color'] ?>-200 dark:border-<?= $worker['status_color'] ?>-800">
                                    <span class="size-1.5 rounded-full bg-<?= $worker['status_color'] ?>-500"></span>
                                    <?= esc($worker['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4"><?= esc($worker['last_active']) ?></td>
                            <td class="px-6 py-4 text-right" onclick="event.stopPropagation()">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openAssignAssetModal('<?= esc($worker['id_number']) ?>', '<?= esc($worker['name']) ?>')" class="p-2 text-gray-500 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Assign Asset">
                                        <span class="material-symbols-outlined text-lg">inventory_2</span>
                                    </button>
                                    <button onclick="openScanCardModal('<?= esc($worker['id_number']) ?>', '<?= esc($worker['name']) ?>', '<?= esc($worker['rfid_tag_id'] ?? '') ?>')" class="p-2 text-gray-500 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Scan Card">
                                        <span class="material-symbols-outlined text-lg">credit_card</span>
                                    </button>
                                    <a href="<?= base_url('workers/edit/' . urlencode($worker['id_number'])) ?>" class="p-2 text-gray-500 hover:text-primary dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <button onclick="confirmDeleteWorker('<?= base_url('workers/delete/' . urlencode($worker['id_number'])) ?>', '<?= esc($worker['name']) ?>')" class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Delete">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($workers)): ?>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 gap-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 w-full sm:w-auto">
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <span>Show</span>
                    <select id="rowsPerPageSelect" onchange="changeRowsPerPage()" class="pl-3 pr-8 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <span>Showing</span>
                    <span id="showingStart" class="font-medium text-gray-900 dark:text-white">1</span>
                    <span>to</span>
                    <span id="showingEnd" class="font-medium text-gray-900 dark:text-white">0</span>
                    <span>of</span>
                    <span id="totalWorkers" class="font-medium text-gray-900 dark:text-white">0</span>
                    <span>results</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button id="prevBtn" class="flex items-center justify-center h-8 px-3 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                    <span class="ml-1 text-sm font-medium">Previous</span>
                </button>
                <button id="nextBtn" class="flex items-center justify-center h-8 px-3 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="mr-1 text-sm font-medium">Next</span>
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

<!-- Scan Card Modal -->
<div id="scanCardModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-2xl">credit_card</span>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Scan RFID Card</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400" id="scanCardWorkerName">Assign card to worker</p>
            </div>
        </div>
        
        <form id="scanCardForm">
            <?= csrf_field() ?>
            <input type="hidden" id="scanWorkerId" name="worker_id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Card ID (RFID Tag ID)
                    <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="rfidTagInput" 
                       name="rfid_tag_id" 
                       placeholder="E2003412EF1234567890ABCD"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent font-mono"
                       required>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Tap the RFID card on reader or enter tag ID manually
                </p>
            </div>
            
            <div id="scanCardError" class="hidden mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-400"></div>
            
            <div id="scanCardSuccess" class="hidden mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-400"></div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeScanCardModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors">
                    Cancel
                </button>
                <button type="submit" id="scanCardSubmitBtn" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 font-medium transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">save</span>
                    Save Card ID
                </button>
            </div>
        </form>
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

<!-- Upload CSV Modal -->
<div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-2xl mx-4 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-2xl">upload_file</span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Batch Upload Workers</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Upload CSV file to add multiple workers</p>
                </div>
            </div>
            <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <p class="text-sm text-blue-800 dark:text-blue-300 mb-2"><strong>CSV Format Required:</strong></p>
            <p class="text-xs text-blue-700 dark:text-blue-400 font-mono">full_name,email,worker_id,department,position,phone,shift,status</p>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">Example: John Doe,john@example.com,W001,operations,technician,1234567890,day,active</p>
            <button onclick="downloadTemplate()" class="mt-2 text-xs text-primary hover:underline flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">download</span>
                Download CSV Template
            </button>
        </div>
        
        <form id="uploadForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload CSV File</label>
                <div class="flex items-center justify-center w-full">
                    <label for="csvFile" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <span class="material-symbols-outlined text-gray-400 text-4xl mb-2">cloud_upload</span>
                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">CSV file only</p>
                            <p id="fileName" class="text-xs text-primary font-medium mt-2 hidden"></p>
                        </div>
                        <input id="csvFile" name="csv_file" type="file" accept=".csv" class="hidden" onchange="handleFileSelect(this)" />
                    </label>
                </div>
            </div>
            
            <div id="uploadProgress" class="hidden mb-4">
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                    <div id="progressBar" class="bg-primary h-2.5 rounded-full" style="width: 0%"></div>
                </div>
                <p id="progressText" class="text-sm text-gray-600 dark:text-gray-400 mt-2 text-center">Uploading...</p>
            </div>
            
            <div id="uploadResults" class="hidden mb-4 p-4 rounded-lg"></div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeUploadModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="uploadCSV()" id="uploadBtn" disabled class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Upload Workers
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Asset Modal -->
<div id="assignAssetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-lg mx-4 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-orange-600 dark:text-orange-400 text-2xl">inventory_2</span>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Assign Asset</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400" id="assignAssetWorkerName">Assign asset to worker</p>
            </div>
        </div>
        
        <form id="assignAssetForm">
            <?= csrf_field() ?>
            <input type="hidden" id="assetWorkerId" name="worker_id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Asset Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="assetNameInput" 
                       name="asset_name" 
                       placeholder="e.g. Smoke Detector, Fire Extinguisher"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                       required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    EPC Number <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="assetEpcInput" 
                       name="epc_no" 
                       placeholder="E2003412EF1234567890ABCD"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent font-mono"
                       required>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Enter the RFID EPC tag number attached to the asset
                </p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description (optional)
                </label>
                <input type="text" 
                       id="assetDescInput" 
                       name="description" 
                       placeholder="Brief description of the asset"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            <!-- Current assets assigned to this worker -->
            <div id="workerAssetsSection" class="hidden mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Currently Assigned Assets</label>
                <div id="workerAssetsList" class="space-y-2 max-h-32 overflow-y-auto"></div>
            </div>
            
            <div id="assignAssetError" class="hidden mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-400"></div>
            <div id="assignAssetSuccess" class="hidden mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-400"></div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeAssignAssetModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors">
                    Cancel
                </button>
                <button type="submit" id="assignAssetSubmitBtn" class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">assignment</span>
                    Assign Asset
                </button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    <?= csrf_field() ?>
</form>

<script>
let deleteUrl = '';

function confirmDeleteWorker(url, workerName) {
    deleteUrl = url;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete ${workerName}? This will remove all their access and records.`;
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

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
        closeUploadModal();
    }
});

// Upload Modal Functions
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('csvFile').value = '';
    document.getElementById('fileName').classList.add('hidden');
    document.getElementById('uploadBtn').disabled = true;
    document.getElementById('uploadProgress').classList.add('hidden');
    document.getElementById('uploadResults').classList.add('hidden');
    document.getElementById('progressBar').style.width = '0%';
}

function handleFileSelect(input) {
    const file = input.files[0];
    if (file) {
        if (!file.name.endsWith('.csv')) {
            alert('Please select a CSV file');
            input.value = '';
            return;
        }
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileName').classList.remove('hidden');
        document.getElementById('uploadBtn').disabled = false;
    }
}

function downloadTemplate() {
    const headers = ['full_name', 'email', 'worker_id', 'department', 'position', 'phone', 'shift', 'status'];
    const example = ['John Doe', 'john@example.com', 'W001', 'operations', 'technician', '1234567890', 'day', 'active'];
    
    let csvContent = headers.join(',') + '\\n';
    csvContent += example.join(',') + '\\n';
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', 'worker_upload_template.csv');
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function uploadCSV() {
    const fileInput = document.getElementById('csvFile');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Please select a file');
        return;
    }
    
    const formData = new FormData();
    formData.append('csv_file', file);
    
    // Get CSRF token
    const csrfToken = document.querySelector('input[name="csrf_test_name"]').value;
    formData.append('csrf_test_name', csrfToken);
    
    // Show progress
    document.getElementById('uploadProgress').classList.remove('hidden');
    document.getElementById('uploadBtn').disabled = true;
    document.getElementById('progressText').textContent = 'Uploading and processing...';
    
    // Simulate progress animation
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += 5;
        if (progress <= 90) {
            document.getElementById('progressBar').style.width = progress + '%';
        }
    }, 100);
    
    // Upload file
    fetch('<?= base_url('workers/batchUpload') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        document.getElementById('progressBar').style.width = '100%';
        
        const resultsDiv = document.getElementById('uploadResults');
        resultsDiv.classList.remove('hidden');
        
        if (data.success) {
            resultsDiv.className = 'mb-4 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800';
            resultsDiv.innerHTML = `
                <p class="text-green-800 dark:text-green-300 font-semibold mb-2">Upload Successful!</p>
                <p class="text-sm text-green-700 dark:text-green-400">✓ ${data.inserted} workers added successfully</p>
                ${data.skipped > 0 ? `<p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">⚠ ${data.skipped} rows skipped (duplicates or errors)</p>` : ''}
                ${data.errors && data.errors.length > 0 ? `
                    <div class="mt-2 text-xs text-red-700 dark:text-red-400">
                        <p class="font-semibold">Errors:</p>
                        <ul class="list-disc list-inside">
                            ${data.errors.slice(0, 5).map(err => `<li>${err}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
            `;
            
            setTimeout(() => {
                closeUploadModal();
                location.reload();
            }, 3000);
        } else {
            resultsDiv.className = 'mb-4 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800';
            resultsDiv.innerHTML = `
                <p class="text-red-800 dark:text-red-300 font-semibold mb-2">Upload Failed</p>
                <p class="text-sm text-red-700 dark:text-red-400">${data.message || 'An error occurred'}</p>
            `;
            document.getElementById('uploadBtn').disabled = false;
        }
    })
    .catch(error => {
        clearInterval(progressInterval);
        const resultsDiv = document.getElementById('uploadResults');
        resultsDiv.classList.remove('hidden');
        resultsDiv.className = 'mb-4 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800';
        resultsDiv.innerHTML = `
            <p class="text-red-800 dark:text-red-300 font-semibold">Error</p>
            <p class="text-sm text-red-700 dark:text-red-400">${error.message}</p>
        `;
        document.getElementById('uploadBtn').disabled = false;
    });
}


// Filter dropdown toggle
const filterBtn = document.getElementById('filterBtn');
const filterDropdown = document.getElementById('filterDropdown');

filterBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    filterDropdown.classList.toggle('hidden');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!filterBtn.contains(e.target) && !filterDropdown.contains(e.target)) {
        filterDropdown.classList.add('hidden');
    }
});

function applyFilters() {
    filterDropdown.classList.add('hidden');
    if (window.filterTable) {
        window.filterTable();
    }
    updateFilterBadge();
}

function clearFilters() {
    document.getElementById('departmentFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('shiftFilter').value = '';
    if (window.filterTable) {
        window.filterTable();
    }
    updateFilterBadge();
}

function updateFilterBadge() {
    const department = document.getElementById('departmentFilter').value;
    const status = document.getElementById('statusFilter').value;
    const shift = document.getElementById('shiftFilter').value;
    const activeFilters = [department, status, shift].filter(f => f !== '').length;
    
    const badge = document.getElementById('filterBadge');
    if (activeFilters > 0) {
        badge.textContent = activeFilters;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

// Export to CSV function
function exportToCSV() {
    const table = document.querySelector('table');
    const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');
    
    if (rows.length === 0) {
        alert('No data to export');
        return;
    }
    
    // Escape commas and quotes in data
    const escapeCSV = (text) => {
        if (text.includes(',') || text.includes('"') || text.includes('\n')) {
            return '"' + text.replace(/"/g, '""') + '"';
        }
        return text;
    };
    
    // CSV headers
    const headers = ['Name', 'Email', 'ID Number', 'Department', 'Role', 'Total Zones', 'Status', 'Last Active'];
    let csvContent = headers.join(',') + '\n';
    
    // Extract data from visible rows
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        
        // Extract name and email from the first cell's nested structure
        const nameCell = cells[0];
        const paragraphs = nameCell.querySelectorAll('p');
        const name = paragraphs[0] ? paragraphs[0].textContent.trim() : '';
        const email = paragraphs[1] ? paragraphs[1].textContent.trim() : '';
        
        const idNumber = cells[1].textContent.trim();
        const department = cells[2].textContent.trim();
        const role = cells[3].textContent.trim();
        const totalZones = cells[4].textContent.trim();
        
        // Extract status text (skip the dot span)
        const statusSpan = cells[5].querySelector('span');
        const status = statusSpan ? statusSpan.textContent.trim() : cells[5].textContent.trim();
        
        const lastActive = cells[6].textContent.trim();
        
        const rowData = [
            escapeCSV(name),
            escapeCSV(email),
            escapeCSV(idNumber),
            escapeCSV(department),
            escapeCSV(role),
            escapeCSV(totalZones),
            escapeCSV(status),
            escapeCSV(lastActive)
        ];
        
        csvContent += rowData.join(',') + '\n';
    });
    
    // Create download link
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    // Generate filename based on active filters
    const timestamp = new Date().toISOString().slice(0, 10);
    const departmentFilter = document.getElementById('departmentFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const shiftFilter = document.getElementById('shiftFilter').value;
    
    let filename = 'workers_export';
    const filters = [];
    
    if (departmentFilter) filters.push(departmentFilter);
    if (statusFilter) filters.push(statusFilter);
    if (shiftFilter) filters.push(shiftFilter);
    
    if (filters.length > 0) {
        filename += '_' + filters.join('_');
    }
    
    filename += '_' + timestamp + '.csv';
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Show success message if toast function exists
    if (typeof showToast === 'function') {
        showToast('Workers exported successfully', 'success');
    }
}

// Toast notification function
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `flex items-center gap-3 p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
        type === 'error' ? 'bg-red-600 text-white' : 
        type === 'success' ? 'bg-green-600 text-white' : 
        type === 'warning' ? 'bg-yellow-600 text-white' : 
        'bg-blue-600 text-white'
    }`;
    
    toast.innerHTML = `
        <span class="material-symbols-outlined text-2xl">${
            type === 'error' ? 'error' : 
            type === 'success' ? 'check_circle' : 
            type === 'warning' ? 'warning' : 
            'info'
        }</span>
        <span class="flex-1">${message}</span>
        <button onclick="this.parentElement.remove()" class="hover:opacity-80 transition-opacity">
            <span class="material-symbols-outlined">close</span>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Slide in
    setTimeout(() => toast.classList.remove('translate-x-full'), 10);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'fixed top-20 right-4 z-50 flex flex-col gap-2 max-w-sm';
    document.body.appendChild(container);
    return container;
}

// Pagination and search functionality
let currentPage = 1;
let currentRowsPerPage = 10;
let visibleRows = [];
let searchInput, tableRows, prevBtn, nextBtn, showingStart, showingEnd, totalWorkers;

function updateVisibleRows() {
    visibleRows = [];
    const searchTerm = searchInput.value.toLowerCase();
    const departmentFilter = document.getElementById('departmentFilter').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    const shiftFilter = document.getElementById('shiftFilter').value.toLowerCase();
    
    tableRows.forEach(row => {
        const name = row.cells[0].textContent.toLowerCase();
        const idNumber = row.cells[1].textContent.toLowerCase();
        const department = row.cells[2].textContent.toLowerCase();
        const role = row.cells[3].textContent.toLowerCase();
        const status = row.cells[5].textContent.toLowerCase();
        const shift = (row.dataset.shift || '').toLowerCase();
        
        // Search match
        const matchesSearch = name.includes(searchTerm) || 
                             idNumber.includes(searchTerm) || 
                             department.includes(searchTerm) ||
                             role.includes(searchTerm);
        
        // Filter matches (exact match for filters)
        const matchesDepartment = !departmentFilter || department.trim() === departmentFilter;
        const matchesStatus = !statusFilter || status.trim() === statusFilter;
        // Handle comma-separated shifts - check if filter matches any of the worker's shifts
        const matchesShift = !shiftFilter || shift.split(',').map(s => s.trim().toLowerCase()).includes(shiftFilter);
        
        if (matchesSearch && matchesDepartment && matchesStatus && matchesShift) {
            visibleRows.push(row);
        }
    });
}

function displayPage() {
    // Hide all rows first
    tableRows.forEach(row => row.style.display = 'none');
    
    // Calculate start and end indices
    const startIndex = (currentPage - 1) * currentRowsPerPage;
    const endIndex = Math.min(startIndex + currentRowsPerPage, visibleRows.length);
    
    // Show only rows for current page
    for (let i = startIndex; i < endIndex; i++) {
        visibleRows[i].style.display = '';
    }
    
    // Update pagination info
    const total = visibleRows.length;
    if (total === 0) {
        showingStart.textContent = '0';
        showingEnd.textContent = '0';
    } else {
        showingStart.textContent = startIndex + 1;
        showingEnd.textContent = endIndex;
    }
    totalWorkers.textContent = total;
    
    // Update button states
    prevBtn.disabled = currentPage === 1;
    const totalPages = Math.ceil(total / currentRowsPerPage);
    nextBtn.disabled = currentPage >= totalPages || total === 0;
}

function filterTable() {
    currentPage = 1;
    updateVisibleRows();
    displayPage();
}

function changeRowsPerPage() {
    currentRowsPerPage = parseInt(document.getElementById('rowsPerPageSelect').value);
    currentPage = 1;
    filterTable();
}

document.addEventListener('DOMContentLoaded', function() {
    searchInput = document.getElementById('searchInput');
    tableRows = document.querySelectorAll('tbody tr');
    prevBtn = document.getElementById('prevBtn');
    nextBtn = document.getElementById('nextBtn');
    showingStart = document.getElementById('showingStart');
    showingEnd = document.getElementById('showingEnd');
    totalWorkers = document.getElementById('totalWorkers');
    
    // Initialize
    updateVisibleRows();
    displayPage();
    
    // Event listeners
    searchInput.addEventListener('input', filterTable);
    
    // Get filter references (filters only apply when user clicks "Apply Filters" button)
    const departmentFilter = document.getElementById('departmentFilter');
    const statusFilter = document.getElementById('statusFilter');
    const shiftFilter = document.getElementById('shiftFilter');
    
    prevBtn.addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            displayPage();
        }
    });
    
    nextBtn.addEventListener('click', function() {
        const totalPages = Math.ceil(visibleRows.length / currentRowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            displayPage();
        }
    });
});

// Scan Card Modal Functions
function openScanCardModal(workerId, workerName, currentRfidTag) {
    document.getElementById('scanWorkerId').value = workerId;
    document.getElementById('scanCardWorkerName').textContent = workerName;
    document.getElementById('rfidTagInput').value = currentRfidTag || '';
    document.getElementById('scanCardError').classList.add('hidden');
    document.getElementById('scanCardSuccess').classList.add('hidden');
    document.getElementById('scanCardModal').classList.remove('hidden');
    
    // Focus on input
    setTimeout(() => {
        document.getElementById('rfidTagInput').focus();
    }, 100);
}

function closeScanCardModal() {
    document.getElementById('scanCardModal').classList.add('hidden');
    document.getElementById('scanCardForm').reset();
}

// Handle scan card form submission
document.getElementById('scanCardForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const workerId = document.getElementById('scanWorkerId').value;
    const rfidTagId = document.getElementById('rfidTagInput').value.trim().toUpperCase();
    const submitBtn = document.getElementById('scanCardSubmitBtn');
    const errorDiv = document.getElementById('scanCardError');
    const successDiv = document.getElementById('scanCardSuccess');
    
    // Validate input
    if (!rfidTagId || rfidTagId.length < 4) {
        errorDiv.textContent = 'Please enter a valid RFID tag ID (minimum 4 characters)';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Hide messages
    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">refresh</span> Saving...';
    
    // Send AJAX request
    fetch('<?= base_url('workers/update-rfid-tag') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            worker_id: workerId,
            rfid_tag_id: rfidTagId,
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            successDiv.innerHTML = '<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">check_circle</span>' + data.message + '</span>';
            successDiv.classList.remove('hidden');
            
            // Close modal after 2 seconds and reload page
            setTimeout(() => {
                closeScanCardModal();
                location.reload();
            }, 2000);
        } else {
            errorDiv.innerHTML = '<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">error</span>' + data.message + '</span>';
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-lg">save</span> Save Card ID';
        }
    })
    .catch(error => {
        errorDiv.innerHTML = '<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">error</span>Network error. Please try again.</span>';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="material-symbols-outlined text-lg">save</span> Save Card ID';
    });
});

// Auto-uppercase RFID input
document.getElementById('rfidTagInput')?.addEventListener('input', function(e) {
    this.value = this.value.toUpperCase().replace(/\s+/g, '');
});

// Close modal on outside click
document.getElementById('scanCardModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeScanCardModal();
    }
});

// Assign Asset Modal Functions
function openAssignAssetModal(workerId, workerName) {
    document.getElementById('assetWorkerId').value = workerId;
    document.getElementById('assignAssetWorkerName').textContent = 'Assign asset to ' + workerName;
    document.getElementById('assetNameInput').value = '';
    document.getElementById('assetEpcInput').value = '';
    document.getElementById('assetDescInput').value = '';
    document.getElementById('assignAssetError').classList.add('hidden');
    document.getElementById('assignAssetSuccess').classList.add('hidden');
    document.getElementById('assignAssetModal').classList.remove('hidden');

    // Load existing assets for this worker
    loadWorkerAssets(workerId);

    setTimeout(() => document.getElementById('assetNameInput').focus(), 100);
}

function closeAssignAssetModal() {
    document.getElementById('assignAssetModal').classList.add('hidden');
    document.getElementById('assignAssetForm').reset();
    document.getElementById('workerAssetsSection').classList.add('hidden');
}

function loadWorkerAssets(workerId) {
    fetch('<?= base_url('workers/get-assets/') ?>' + workerId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.assets.length > 0) {
            const section = document.getElementById('workerAssetsSection');
            const list = document.getElementById('workerAssetsList');
            list.innerHTML = '';
            
            data.assets.forEach(asset => {
                list.innerHTML += `
                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-orange-500 text-sm">inventory_2</span>
                            <span class="text-sm text-gray-900 dark:text-white font-medium">${asset.asset_name}</span>
                            <span class="text-xs text-gray-500 font-mono">${asset.epc_no || 'No EPC'}</span>
                        </div>
                        <button type="button" onclick="unassignAsset(${asset.id})" class="text-xs text-red-500 hover:text-red-700 hover:underline">Remove</button>
                    </div>
                `;
            });

            section.classList.remove('hidden');
        } else {
            document.getElementById('workerAssetsSection').classList.add('hidden');
        }
    })
    .catch(() => {});
}

function unassignAsset(assetId) {
    if (!confirm('Remove this asset assignment?')) return;
    
    fetch('<?= base_url('workers/unassign-asset') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            asset_id: assetId,
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const workerId = document.getElementById('assetWorkerId').value;
            loadWorkerAssets(workerId);
        }
    });
}

// Handle assign asset form submission
document.getElementById('assignAssetForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const workerId = document.getElementById('assetWorkerId').value;
    const assetName = document.getElementById('assetNameInput').value.trim();
    const epcNo = document.getElementById('assetEpcInput').value.trim().toUpperCase();
    const description = document.getElementById('assetDescInput').value.trim();
    const submitBtn = document.getElementById('assignAssetSubmitBtn');
    const errorDiv = document.getElementById('assignAssetError');
    const successDiv = document.getElementById('assignAssetSuccess');
    
    if (!assetName) {
        errorDiv.textContent = 'Please enter an asset name';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (!epcNo || epcNo.length < 4) {
        errorDiv.textContent = 'Please enter a valid EPC number (minimum 4 characters)';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="material-symbols-outlined text-lg animate-spin">refresh</span> Assigning...';
    
    fetch('<?= base_url('workers/assign-asset') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            worker_id: workerId,
            asset_name: assetName,
            epc_no: epcNo,
            description: description,
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            successDiv.innerHTML = '<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">check_circle</span>' + data.message + '</span>';
            successDiv.classList.remove('hidden');
            
            // Reload worker assets list
            loadWorkerAssets(workerId);
            
            // Clear form inputs
            document.getElementById('assetNameInput').value = '';
            document.getElementById('assetEpcInput').value = '';
            document.getElementById('assetDescInput').value = '';
            
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-lg">assignment</span> Assign Asset';
        } else {
            errorDiv.innerHTML = '<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">error</span>' + data.message + '</span>';
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-lg">assignment</span> Assign Asset';
        }
    })
    .catch(error => {
        errorDiv.innerHTML = '<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">error</span>Network error. Please try again.</span>';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="material-symbols-outlined text-lg">assignment</span> Assign Asset';
    });
});

// Auto-uppercase EPC input
document.getElementById('assetEpcInput')?.addEventListener('input', function() {
    this.value = this.value.toUpperCase().replace(/\s+/g, '');
});

// Close assign asset modal on outside click
document.getElementById('assignAssetModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAssignAssetModal();
    }
});
</script>

<?= $this->include('templates/footer') ?>
