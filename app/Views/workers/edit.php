<?= view('templates/header', ['title' => 'Edit Worker']) ?>

<main class="flex-1 p-6 overflow-y-auto" style="height: calc(100vh - 64px);">
    <div class="w-full">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-6">
            <a href="<?= base_url('workers/list') ?>" class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div class="flex flex-col">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Worker</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Update worker details and access permissions.</p>
            </div>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="p-4 mb-6 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="p-6">
                <form action="<?= base_url('workers/update/' . urlencode($worker['worker_id'])) ?>" method="post" enctype="multipart/form-data" class="space-y-8">
                    <?= csrf_field() ?>
                    <input type="hidden" name="assigned_zones" id="assigned_zones" value="<?= esc(implode(',', $assignedZones)) ?>">
                    
                    <!-- Personal Information -->
                    <div class="space-y-6">
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">person</span>
                                Personal Information
                            </h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="first-name">First Name</label>
                                <input class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5" id="first-name" name="first_name" placeholder="e.g. Olivia" type="text" value="<?= esc($worker['first_name']) ?>" required/>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="last-name">Last Name</label>
                                <input class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5" id="last-name" name="last_name" placeholder="e.g. Rhye" type="text" value="<?= esc($worker['last_name']) ?>" required/>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="email">Email Address</label>
                                <input class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5" id="email" name="email" placeholder="olivia@company.com" type="email" value="<?= esc($worker['email']) ?>" required/>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="phone">Phone Number</label>
                                <input class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5" id="phone" name="phone" placeholder="+1 (555) 000-0000" type="tel" value="<?= esc($worker['phone']) ?>"/>
                            </div>
                            
                            <div class="space-y-2 md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="address">Residential Address</label>
                                <textarea class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5" id="address" name="address" placeholder="Enter full address" rows="3"><?= esc($worker['address']) ?></textarea>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="city">City</label>
                                <select class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5" id="city" name="city_id">
                                    <option value="">Select City</option>
                                    <?php if (!empty($cities)): ?>
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?= $city['id'] ?>" data-state="<?= $city['state_id'] ?>" <?= ($worker['city_id'] == $city['id']) ? 'selected' : '' ?>><?= esc($city['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="state">State</label>
                                <select class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5" id="state" name="state_id" onchange="filterCities()">
                                    <option value="">Select State</option>
                                    <?php if (!empty($states)): ?>
                                        <?php foreach ($states as $state): ?>
                                            <option value="<?= $state['id'] ?>" data-country="<?= $state['country_id'] ?>" <?= ($worker['state_id'] == $state['id']) ? 'selected' : '' ?>><?= esc($state['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="country">Country</label>
                                <select class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5" id="country" name="country_id" onchange="loadStates(this.value)">
                                    <option value="">Select Country</option>
                                    <?php if (!empty($countries)): ?>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?= $country['id'] ?>" <?= ($worker['country_id'] == $country['id']) ? 'selected' : '' ?>><?= esc($country['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Details -->
                    <div class="space-y-6">
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">badge</span>
                                Employment Details
                            </h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="worker-id">Worker ID / Tag Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-500 text-lg">tag</span>
                                    </div>
                                    <input class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 sm:text-sm pl-10 p-2.5 cursor-not-allowed" id="worker-id" name="worker_id" type="text" value="<?= esc($worker['worker_id']) ?>" readonly/>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Worker ID cannot be changed</p>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="ic-number">ID/IC Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-500 text-lg">badge</span>
                                    </div>
                                    <input class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm pl-10 p-2.5" id="ic-number" name="ic_number" placeholder="Enter IC/ID Number" type="text" value="<?= esc($worker['ic_number'] ?? '') ?>"/>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="rfid-tag-id">
                                    RFID Card ID
                                    <?php if (!empty($worker['rfid_tag_id'])): ?>
                                        <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-full">Registered</span>
                                    <?php else: ?>
                                        <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full">Not Assigned</span>
                                    <?php endif; ?>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-500 text-lg">credit_card</span>
                                    </div>
                                    <input class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm pl-10 pr-10 p-2.5 font-mono" 
                                           id="rfid-tag-id" 
                                           name="rfid_tag_id" 
                                           type="text" 
                                           placeholder="E2003412EF1234567890ABCD"
                                           value="<?= esc($worker['rfid_tag_id'] ?? '') ?>"/>
                                    <div id="rfid-status-icon" class="hidden absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="material-symbols-outlined text-lg"></span>
                                    </div>
                                </div>
                                <div id="rfid-validation-message" class="hidden text-xs mt-1"></div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">UHF RFID tag ID for automatic attendance tracking</p>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="department">Department</label>
                                <select class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5" id="department" name="department_id" required>
                                    <option disabled value="">Select Department</option>
                                    <?php if (!empty($departments)): ?>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= (int) $dept['id'] ?>" <?= (int) ($worker['department_id'] ?? 0) === (int) $dept['id'] || (empty($worker['department_id']) && strtolower($worker['department']) === strtolower($dept['name'])) ? 'selected' : '' ?>><?= esc($dept['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="operations">Operations</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="position">Job Title / Position</label>
                                <select class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5" id="position" name="position" required>
                                    <option disabled value="">Select Position</option>
                                    <?php if (!empty($positions)): ?>
                                        <?php foreach ($positions as $pos): ?>
                                            <option value="<?= esc($pos['title']) ?>" <?= ($worker['position'] === $pos['title']) ? 'selected' : '' ?>><?= esc($pos['title']) ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="Line Operator">Line Operator</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="start-date">Start Date</label>
                                <input class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5" id="start-date" name="start_date" type="date" value="<?= esc($worker['start_date']) ?>" required/>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assigned Shift(s) <span class="text-xs text-gray-500">(Check one or more for double shift)</span></label>
                                <input type="hidden" name="shift" id="shift-hidden" value="<?= esc($worker['shift']) ?>" required>
                                <?php 
                                $workerShifts = !empty($worker['shift']) ? explode(',', $worker['shift']) : [];
                                $workerShifts = array_map('trim', $workerShifts);
                                ?>
                                <div class="space-y-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600">
                                    <?php if (!empty($shifts)): ?>
                                        <?php foreach ($shifts as $shift): ?>
                                            <div class="flex items-center">
                                                <input type="checkbox" 
                                                       class="shift-checkbox w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary dark:bg-gray-700 dark:border-gray-600" 
                                                       id="shift-<?= esc($shift['name']) ?>" 
                                                       value="<?= esc($shift['name']) ?>"
                                                       <?= in_array($shift['name'], $workerShifts) ? 'checked' : '' ?>
                                                       onchange="updateShiftValue()">
                                                <label class="ml-2 text-sm text-gray-900 dark:text-gray-300" for="shift-<?= esc($shift['name']) ?>">
                                                    <?= esc(ucfirst($shift['name'])) ?> (<?= date('H:i', strtotime($shift['start_time'])) ?> - <?= date('H:i', strtotime($shift['end_time'])) ?>)
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500">No shifts available - Please add shifts in Config</p>
                                    <?php endif; ?>
                                </div>
                                <script>
                                function updateShiftValue() {
                                    const checkboxes = document.querySelectorAll('.shift-checkbox:checked');
                                    const values = Array.from(checkboxes).map(cb => cb.value);
                                    document.getElementById('shift-hidden').value = values.join(',');
                                }
                                </script>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="status">Status</label>
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="flex items-center">
                                        <input <?= $worker['status'] === 'active' ? 'checked' : '' ?> class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary dark:bg-gray-700 dark:border-gray-600" id="status-active" name="status" type="radio" value="active"/>
                                        <label class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300" for="status-active">Active</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input <?= $worker['status'] === 'inactive' ? 'checked' : '' ?> class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary dark:bg-gray-700 dark:border-gray-600" id="status-inactive" name="status" type="radio" value="inactive"/>
                                        <label class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300" for="status-inactive">Inactive</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Zones -->
                    <div class="space-y-6">
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">grid_view</span>
                                Assigned Zones
                            </h2>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="zones-select">Assign Zones</label>
                                <div class="flex gap-2">
                                    <select class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5" id="zones-select">
                                        <option disabled selected value="">Select a zone</option>
                                        <?php foreach ($zones as $zone): ?>
                                            <option value="<?= esc($zone['zone_id']) ?>" data-name="<?= esc($zone['zone_name']) ?>" data-location="<?= esc($zone['location']) ?>" data-icon="<?= esc($zone['icon']) ?>" data-color="<?= esc($zone['icon_color']) ?>">
                                                <?= esc($zone['zone_id']) ?> - <?= esc($zone['zone_name']) ?><?= $zone['location'] ? ' - ' . esc($zone['location']) : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition-colors shrink-0" type="button" onclick="addZone()">
                                        <span class="material-symbols-outlined text-lg">add</span>
                                        Add
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Select a zone from the list to assign it to this worker.</p>
                            </div>
                            
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-100/50 dark:bg-gray-800">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Current Zone Access</h3>
                                    <span id="zone-count" class="text-xs font-medium px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full">0 Zones</span>
                                </div>
                                <ul id="zones-list" class="divide-y divide-gray-200 dark:divide-gray-700 min-h-[100px]">
                                    <!-- Zones will be added dynamically via JavaScript -->
                                    <li class="p-8 text-center text-sm text-gray-500 dark:text-gray-400" id="no-zones-message">
                                        No zones assigned yet. Select zones from the dropdown above.
                                    </li>
                                </ul>
                                <div class="p-3 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-200 dark:border-gray-700 text-center">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Only authorized zones will be accessible via smart badge.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Assets -->
                    <div class="space-y-6">
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-orange-500">inventory_2</span>
                                Assigned Assets
                            </h2>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Add new asset form -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Add Asset</label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <input type="text" id="new-asset-name" placeholder="Asset Name (e.g. Smoke Detector)" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5">
                                    <input type="text" id="new-asset-epc" placeholder="EPC No (e.g. E2003412EF12345678)" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm p-2.5 font-mono uppercase">
                                    <button type="button" onclick="addAssetToWorker()" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg flex items-center justify-center gap-2 transition-colors">
                                        <span class="material-symbols-outlined text-lg">add</span>
                                        Assign Asset
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Enter the asset name and its RFID EPC tag number to assign it to this worker.</p>
                            </div>

                            <!-- Asset list -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-100/50 dark:bg-gray-800">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Current Assets</h3>
                                    <span id="asset-count" class="text-xs font-medium px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded-full"><?= count($workerAssets ?? []) ?> Assets</span>
                                </div>
                                <ul id="assets-list" class="divide-y divide-gray-200 dark:divide-gray-700 min-h-[80px]">
                                    <?php if (!empty($workerAssets)): ?>
                                        <?php foreach ($workerAssets as $asset): ?>
                                            <li class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors" data-asset-id="<?= $asset['id'] ?>">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center shrink-0">
                                                        <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">package_2</span>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?= esc($asset['asset_name']) ?></p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">EPC: <?= esc($asset['epc_no'] ?? 'Not set') ?></p>
                                                    </div>
                                                </div>
                                                <button class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition-colors p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50" type="button" onclick="removeAssetFromWorker(<?= $asset['id'] ?>, this)">
                                                    <span class="material-symbols-outlined">delete</span>
                                                </button>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="p-8 text-center text-sm text-gray-500 dark:text-gray-400" id="no-assets-message">
                                            No assets assigned yet. Add an asset above.
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Documents & Photo -->
                    <div class="space-y-6">
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">upload_file</span>
                                Documents &amp; Photo
                            </h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Profile Photo</label>
                                <div class="flex flex-col items-center justify-center w-full gap-2">
                                    <label id="profile-photo-upload-box" class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 overflow-hidden" for="dropzone-file">
                                        <img id="profile-photo-preview" src="<?php if (!empty($worker['profile_photo'])): ?><?= base_url('uploads/profiles/' . $worker['profile_photo']) ?><?php endif; ?>" alt="Profile Preview" class="<?php if (empty($worker['profile_photo'])): ?>hidden <?php endif; ?>absolute inset-0 w-full h-full object-contain max-h-40 rounded-lg z-10 bg-white dark:bg-gray-800" style="max-width: 100%; max-height: 160px;" />
                                        <button id="remove-profile-photo-btn" type="button" class="<?php if (empty($worker['profile_photo'])): ?>hidden <?php endif; ?>absolute top-2 right-2 z-20 bg-red-600 hover:bg-red-700 text-white rounded-full p-1 shadow" title="Remove photo">
                                            <span class="material-symbols-outlined text-base">close</span>
                                        </button>
                                        <div id="profile-photo-upload-ui" class="flex flex-col items-center justify-center pt-5 pb-6 z-20<?php if (!empty($worker['profile_photo'])): ?> hidden<?php endif; ?>">
                                            <span class="material-symbols-outlined text-gray-500 dark:text-gray-400 text-3xl mb-2">cloud_upload</span>
                                            <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX. 800x400px)</p>
                                        </div>
                                        <input class="hidden" id="dropzone-file" name="profile_photo" type="file" accept="image/*"/>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Proof / Contracts</label>
                                <div class="flex flex-col items-center justify-center w-full">
                                    <div id="doc-upload-wrapper-edit" class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 overflow-auto">
                                        <div id="document-upload-ui-edit" class="flex flex-col items-center justify-center pt-5 pb-6 z-0">
                                            <span class="material-symbols-outlined text-gray-500 dark:text-gray-400 text-3xl mb-2">description</span>
                                            <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload documents</span></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">PDF, DOCX (MAX. 5MB)</p>
                                        </div>
                                        <div id="documents-preview-list-edit" class="absolute inset-0 w-full h-full overflow-y-auto p-3 z-10 hidden"></div>
                                        <input class="hidden" id="dropzone-doc-edit" name="documents[]" multiple type="file" accept=".pdf,.doc,.docx"/>
                                        <input type="hidden" id="existing-documents" name="existing_documents" value="<?= !empty($worker['documents']) ? esc($worker['documents']) : '' ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="<?= base_url('workers/list') ?>" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700">
                            Cancel
                        </a>
                        <button class="flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-primary rounded-lg hover:bg-primary/90 focus:ring-4 focus:ring-primary/30 focus:outline-none" type="submit">
                            <span class="material-symbols-outlined text-lg">save</span>
                            Update Worker
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
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

// Image preview for profile photo
document.addEventListener('DOMContentLoaded', function() {
    // Profile photo logic ...existing code...
    const fileInput = document.getElementById('dropzone-file');
    const previewImg = document.getElementById('profile-photo-preview');
    const uploadUI = document.getElementById('profile-photo-upload-ui');
    const removeBtn = document.getElementById('remove-profile-photo-btn');
    if (fileInput && previewImg && uploadUI && removeBtn) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    previewImg.src = evt.target.result;
                    previewImg.classList.remove('hidden');
                    uploadUI.classList.add('hidden');
                    removeBtn.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
                showToast('Profile photo selected!', 'success');
            } else {
                previewImg.src = '';
                previewImg.classList.add('hidden');
                uploadUI.classList.remove('hidden');
                removeBtn.classList.add('hidden');
            }
        });
        removeBtn.addEventListener('click', function() {
            fileInput.value = '';
            previewImg.src = '';
            previewImg.classList.add('hidden');
            uploadUI.classList.remove('hidden');
            removeBtn.classList.add('hidden');
            showToast('Profile photo removed', 'info');
        });
    }

    // Documents upload logic (edit page only)
    const docInputEdit = document.getElementById('dropzone-doc-edit');
    const docPreviewListEdit = document.getElementById('documents-preview-list-edit');
    const docUploadWrapperEdit = document.getElementById('doc-upload-wrapper-edit');
    let docFilesEdit = [];
    let existingDocuments = <?= !empty($worker['documents']) ? json_encode(json_decode($worker['documents'], true)) : '[]' ?>;
    
    if (docInputEdit && docPreviewListEdit && docUploadWrapperEdit) {
        // Load and display existing documents
        if (existingDocuments.length > 0) {
            const uploadUI = document.getElementById('document-upload-ui-edit');
            uploadUI.classList.add('hidden');
            docPreviewListEdit.classList.remove('hidden');
            
            existingDocuments.forEach((fileName, idx) => {
                const ext = fileName.split('.').pop().toLowerCase();
                let icon = 'description';
                if (ext === 'pdf') icon = 'picture_as_pdf';
                else if (ext === 'doc' || ext === 'docx') icon = 'article';
                
                const wrapper = document.createElement('div');
                wrapper.className = 'flex items-center gap-2 bg-white dark:bg-gray-700 rounded px-3 py-2 border border-gray-200 dark:border-gray-600 relative';
                wrapper.innerHTML = `
                    <span class="material-symbols-outlined text-lg text-primary">${icon}</span>
                    <a href="<?= base_url('uploads/documents/') ?>${fileName}" target="_blank" class="truncate flex-1 text-sm text-gray-900 dark:text-white hover:text-primary hover:underline" title="${fileName}">${fileName}</a>
                    <button type="button" class="remove-existing-doc text-red-600 hover:text-red-800 bg-transparent rounded-full p-1 ml-2" data-filename="${fileName}" title="Remove">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                `;
                docPreviewListEdit.appendChild(wrapper);
            });
            
            // Add remove handlers for existing documents
            docPreviewListEdit.querySelectorAll('.remove-existing-doc').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const filename = this.getAttribute('data-filename');
                    existingDocuments = existingDocuments.filter(f => f !== filename);
                    this.closest('.flex.items-center.gap-2').remove();
                    
                    // Update hidden field
                    const newValue = JSON.stringify(existingDocuments);
                    document.getElementById('existing-documents').value = newValue;
                    console.log('Removed document:', filename);
                    console.log('Remaining documents:', newValue);
                    
                    if (existingDocuments.length === 0 && docFilesEdit.length === 0) {
                        docPreviewListEdit.classList.add('hidden');
                        document.getElementById('document-upload-ui-edit').classList.remove('hidden');
                    }
                    showToast('Document removed', 'info');
                });
            });
        }
        
        // Handle clicks on wrapper to open file dialog
        docUploadWrapperEdit.addEventListener('click', function(e) {
            // Don't open dialog if clicking on a file item or its children (remove button)
            if (e.target.closest('.flex.items-center.gap-2.bg-white')) {
                return;
            }
            docInputEdit.click();
        });
        
        docInputEdit.addEventListener('change', function(e) {
            const newFiles = Array.from(docInputEdit.files);
            // Append new files to existing array
            docFilesEdit.push(...newFiles);
            updateDocInputFilesEdit();
            renderDocPreviewsEdit();
            if (newFiles.length > 0) {
                showToast(`${newFiles.length} document(s) added!`, 'success');
            }
        });
    }
    function renderDocPreviewsEdit() {
        const uploadUI = document.getElementById('document-upload-ui-edit');
        docPreviewListEdit.innerHTML = '';
        
        if (docFilesEdit.length === 0) {
            docPreviewListEdit.classList.add('hidden');
            uploadUI.classList.remove('hidden');
            return;
        }
        
        // Hide upload UI and show preview list
        uploadUI.classList.add('hidden');
        docPreviewListEdit.classList.remove('hidden');
        
        docFilesEdit.forEach((file, idx) => {
            const ext = file.name.split('.').pop().toLowerCase();
            let icon = 'description';
            if (ext === 'pdf') icon = 'picture_as_pdf';
            else if (ext === 'doc' || ext === 'docx') icon = 'article';
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-center gap-2 bg-white dark:bg-gray-700 rounded px-3 py-2 border border-gray-200 dark:border-gray-600 relative';
            wrapper.innerHTML = `
                <span class="material-symbols-outlined text-lg text-primary">${icon}</span>
                <span class="truncate flex-1 text-sm text-gray-900 dark:text-white" title="${file.name}">${file.name}</span>
                <button type="button" class="remove-doc-btn-edit text-red-600 hover:text-red-800 bg-transparent rounded-full p-1 ml-2" data-idx="${idx}" title="Remove">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            `;
            docPreviewListEdit.appendChild(wrapper);
        });
        // Add remove event listeners
        docPreviewListEdit.querySelectorAll('.remove-doc-btn-edit').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent triggering file input
                const idx = parseInt(this.getAttribute('data-idx'));
                docFilesEdit.splice(idx, 1);
                updateDocInputFilesEdit();
                renderDocPreviewsEdit();
                showToast('Document removed', 'info');
            });
        });
    }
    function updateDocInputFilesEdit() {
        const dt = new DataTransfer();
        docFilesEdit.forEach(f => dt.items.add(f));
        docInputEdit.files = dt.files;
    }
});

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'fixed top-20 right-4 z-50 flex flex-col gap-2 max-w-sm';
    document.body.appendChild(container);
    return container;
}

function addZone() {
    const select = document.getElementById('zones-select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (!selectedOption.value) {
        showToast('Please select a zone first', 'warning');
        return;
    }
    
    // Check if zone already exists
    const existingZones = document.querySelectorAll('#zones-list li[data-zone]');
    for (let zone of existingZones) {
        if (zone.dataset.zone === selectedOption.value) {
            showToast('This zone is already assigned', 'error');
            return;
        }
    }
    
    // Remove "no zones" message if it exists
    const noZonesMsg = document.getElementById('no-zones-message');
    if (noZonesMsg) {
        noZonesMsg.remove();
    }
    
    // Get icon and color from data attributes
    const icon = selectedOption.dataset.icon || 'location_on';
    const color = selectedOption.dataset.color || 'blue';
    
    // Add zone to list
    const zonesList = document.getElementById('zones-list');
    const li = document.createElement('li');
    li.className = 'p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors';
    li.dataset.zone = selectedOption.value;
    li.innerHTML = `
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-${color}-100 dark:bg-${color}-900/30 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-${color}-600 dark:text-${color}-400">${icon}</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">${selectedOption.text}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">New Assignment</p>
            </div>
        </div>
        <button class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition-colors p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50" type="button" onclick="removeZone(this)">
            <span class="material-symbols-outlined">delete</span>
        </button>
    `;
    zonesList.appendChild(li);
    
    updateZoneCount();
    updateHiddenZonesField();
    select.selectedIndex = 0;
    showToast('Zone added successfully', 'success');
}

function removeZone(button) {
    const li = button.closest('li');
    li.remove();
    
    // Show "no zones" message if list is empty
    const zonesList = document.getElementById('zones-list');
    const remainingZones = document.querySelectorAll('#zones-list li[data-zone]');
    if (remainingZones.length === 0) {
        const noZonesLi = document.createElement('li');
        noZonesLi.id = 'no-zones-message';
        noZonesLi.className = 'p-8 text-center text-sm text-gray-500 dark:text-gray-400';
        noZonesLi.textContent = 'No zones assigned yet. Select zones from the dropdown above.';
        zonesList.appendChild(noZonesLi);
    }
    
    updateZoneCount();
    updateHiddenZonesField();
}

function updateZoneCount() {
    const count = document.querySelectorAll('#zones-list li[data-zone]').length;
    const label = count === 1 ? 'Zone' : 'Zones';
    document.getElementById('zone-count').textContent = `${count} ${label}`;
}

function updateHiddenZonesField() {
    const zones = [];
    document.querySelectorAll('#zones-list li').forEach(li => {
        zones.push(li.dataset.zone);
    });
    document.getElementById('assigned_zones').value = zones.join(',');
}

// Initialize zone count on page load and populate existing zones
document.addEventListener('DOMContentLoaded', function() {
    updateZoneCount();
    
    // Populate existing assigned zones
    <?php if (!empty($assignedZones)): ?>
        const zonesList = document.getElementById('zones-list');
        const noZonesMsg = document.getElementById('no-zones-message');
        if (noZonesMsg) noZonesMsg.remove();
        
        <?php 
        foreach ($assignedZones as $zoneId):
            // Find the zone details
            $zoneInfo = null;
            foreach ($zones as $z) {
                if ($z['zone_id'] === $zoneId) {
                    $zoneInfo = $z;
                    break;
                }
            }
            if ($zoneInfo):
        ?>
            const li_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $zoneId) ?> = document.createElement('li');
            li_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $zoneId) ?>.className = 'p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors';
            li_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $zoneId) ?>.dataset.zone = '<?= esc($zoneId) ?>';
            li_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $zoneId) ?>.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-<?= $zoneInfo['icon_color'] ?>-100 dark:bg-<?= $zoneInfo['icon_color'] ?>-900/30 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-<?= $zoneInfo['icon_color'] ?>-600 dark:text-<?= $zoneInfo['icon_color'] ?>-400"><?= $zoneInfo['icon'] ?></span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?= esc($zoneInfo['zone_id']) ?> - <?= esc($zoneInfo['zone_name']) ?><?= $zoneInfo['location'] ? ' - ' . esc($zoneInfo['location']) : '' ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Assigned Zone</p>
                    </div>
                </div>
                <button class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition-colors p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50" type="button" onclick="removeZone(this)">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            `;
            zonesList.appendChild(li_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $zoneId) ?>);
        <?php 
            endif;
        endforeach; 
        ?>
        
        updateZoneCount();
    <?php endif; ?>
});

// RFID Card Scan Help Modal
function openScanCardHelp() {
    const modal = document.getElementById('scanCardHelpModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

// Auto-uppercase and remove spaces from RFID input
document.getElementById('rfid-tag-id')?.addEventListener('input', function(e) {
    this.value = this.value.toUpperCase().replace(/\s+/g, '');
    checkRfidAvailability(this.value);
});

// Check RFID card availability
let rfidCheckTimeout;
function checkRfidAvailability(rfidTagId) {
    const statusIcon = document.getElementById('rfid-status-icon');
    const validationMessage = document.getElementById('rfid-validation-message');
    const rfidInput = document.getElementById('rfid-tag-id');
    const currentWorkerId = '<?= esc($worker['worker_id']) ?>';
    
    // Clear previous timeout
    clearTimeout(rfidCheckTimeout);
    
    // Hide status while typing
    if (statusIcon) statusIcon.classList.add('hidden');
    if (validationMessage) {
        validationMessage.classList.add('hidden');
        validationMessage.textContent = '';
    }
    
    // If empty, don't check
    if (!rfidTagId || rfidTagId.length < 4) {
        if (rfidInput) {
            rfidInput.classList.remove('border-green-500', 'border-red-500');
            rfidInput.classList.add('border-gray-300', 'dark:border-gray-600');
        }
        return;
    }
    
    // Check after 500ms delay
    rfidCheckTimeout = setTimeout(() => {
        fetch(`<?= base_url('workers/check-rfid-card') ?>?rfid_tag_id=${encodeURIComponent(rfidTagId)}&current_worker_id=${encodeURIComponent(currentWorkerId)}`)
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    // Card is available
                    if (rfidInput) {
                        rfidInput.classList.remove('border-red-500', 'border-gray-300', 'dark:border-gray-600');
                        rfidInput.classList.add('border-green-500');
                    }
                    if (statusIcon) {
                        statusIcon.querySelector('span').textContent = 'check_circle';
                        statusIcon.querySelector('span').classList.remove('text-red-500');
                        statusIcon.querySelector('span').classList.add('text-green-500');
                        statusIcon.classList.remove('hidden');
                    }
                    if (validationMessage && data.message) {
                        validationMessage.textContent = data.message;
                        validationMessage.classList.remove('text-red-600', 'dark:text-red-400', 'hidden');
                        validationMessage.classList.add('text-green-600', 'dark:text-green-400');
                    }
                } else {
                    // Card is already in use
                    if (rfidInput) {
                        rfidInput.classList.remove('border-green-500', 'border-gray-300', 'dark:border-gray-600');
                        rfidInput.classList.add('border-red-500');
                    }
                    if (statusIcon) {
                        statusIcon.querySelector('span').textContent = 'error';
                        statusIcon.querySelector('span').classList.remove('text-green-500');
                        statusIcon.querySelector('span').classList.add('text-red-500');
                        statusIcon.classList.remove('hidden');
                    }
                    if (validationMessage) {
                        validationMessage.textContent = data.message || 'This card is already in use';
                        validationMessage.classList.remove('text-green-600', 'dark:text-green-400', 'hidden');
                        validationMessage.classList.add('text-red-600', 'dark:text-red-400');
                    }
                    showToast(data.message || 'This RFID card is already in use!', 'error');
                }
            })
            .catch(error => {
                console.error('Error checking RFID:', error);
            });
    }, 500);
}

// Location dropdowns handling
function loadStates(countryId) {
    const stateSelect = document.getElementById('state');
    const citySelect = document.getElementById('city');
    
    // Show/hide states based on country
    const allStateOptions = stateSelect.querySelectorAll('option[data-country]');
    allStateOptions.forEach(option => {
        if (!countryId || option.dataset.country === countryId) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Reset state selection if current state doesn't match country
    if (stateSelect.value) {
        const selectedOption = stateSelect.querySelector(`option[value="${stateSelect.value}"]`);
        if (selectedOption && selectedOption.dataset.country !== countryId) {
            stateSelect.value = '';
        }
    }
    
    // Reset and filter cities
    filterCities();
}

function filterCities() {
    const stateId = document.getElementById('state').value;
    const citySelect = document.getElementById('city');
    const allOptions = citySelect.querySelectorAll('option[data-state]');
    
    allOptions.forEach(option => {
        if (!stateId || option.dataset.state === stateId) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Reset selection if current city doesn't match state
    if (citySelect.value) {
        const selectedOption = citySelect.querySelector(`option[value="${citySelect.value}"]`);
        if (selectedOption && selectedOption.dataset.state !== stateId) {
            citySelect.value = '';
        }
    }
}

// Initialize on page load
window.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($worker['country_id'])): ?>
    // Filter states for selected country
    loadStates('<?= $worker['country_id'] ?>');
    <?php else: ?>
    // Hide all states initially if no country selected
    const stateSelect = document.getElementById('state');
    const allStateOptions = stateSelect.querySelectorAll('option[data-country]');
    allStateOptions.forEach(option => {
        option.style.display = 'none';
    });
    <?php endif; ?>
    
    // Filter cities
    filterCities();
});

// Asset Management Functions
document.getElementById('new-asset-epc')?.addEventListener('input', function() {
    this.value = this.value.toUpperCase().replace(/\s+/g, '');
});

function addAssetToWorker() {
    const nameInput = document.getElementById('new-asset-name');
    const epcInput = document.getElementById('new-asset-epc');
    const assetName = nameInput.value.trim();
    const epcNo = epcInput.value.trim().toUpperCase();
    const workerId = '<?= esc($worker['worker_id']) ?>';

    if (!assetName) {
        showToast('Please enter an asset name', 'warning');
        nameInput.focus();
        return;
    }
    if (!epcNo || epcNo.length < 4) {
        showToast('Please enter a valid EPC number (min 4 characters)', 'warning');
        epcInput.focus();
        return;
    }

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
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            nameInput.value = '';
            epcInput.value = '';

            // Remove "no assets" message
            const noMsg = document.getElementById('no-assets-message');
            if (noMsg) noMsg.remove();

            // Add to list
            const list = document.getElementById('assets-list');
            const li = document.createElement('li');
            li.className = 'p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors';
            li.dataset.assetId = data.asset_id;
            li.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">package_2</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${assetName}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">EPC: ${epcNo}</p>
                    </div>
                </div>
                <button class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition-colors p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50" type="button" onclick="removeAssetFromWorker(${data.asset_id}, this)">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            `;
            list.appendChild(li);
            updateAssetCount();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(() => showToast('Network error. Please try again.', 'error'));
}

function removeAssetFromWorker(assetId, btn) {
    if (!confirm('Remove this asset from the worker?')) return;

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
            showToast('Asset removed', 'success');
            btn.closest('li').remove();
            updateAssetCount();

            // Show "no assets" message if empty
            const list = document.getElementById('assets-list');
            if (list.querySelectorAll('li[data-asset-id]').length === 0) {
                const noMsg = document.createElement('li');
                noMsg.id = 'no-assets-message';
                noMsg.className = 'p-8 text-center text-sm text-gray-500 dark:text-gray-400';
                noMsg.textContent = 'No assets assigned yet. Add an asset above.';
                list.appendChild(noMsg);
            }
        } else {
            showToast(data.message || 'Failed to remove asset', 'error');
        }
    })
    .catch(() => showToast('Network error. Please try again.', 'error'));
}

function updateAssetCount() {
    const count = document.querySelectorAll('#assets-list li[data-asset-id]').length;
    document.getElementById('asset-count').textContent = count + ' Asset' + (count !== 1 ? 's' : '');
}
</script>

<?= view('templates/footer') ?>
