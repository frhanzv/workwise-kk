<?= view('templates/header', ['title' => 'Zone Definition']) ?>

<main class="flex-1 p-6 overflow-y-auto" style="height: calc(100vh - 64px);">
    <div class="flex flex-col gap-6 w-full">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <a href="<?= base_url('zones') ?>" class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div class="flex flex-col gap-2">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Zone Definition</h1>
                <p class="text-gray-500 dark:text-gray-400">Create new zones or update existing configurations for antenna tracking.</p>
            </div>
        </div>

        <!-- Success Message -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/20 border border-green-500 text-green-700 dark:text-green-400 rounded-lg flex items-center gap-3">
                <span class="material-symbols-outlined">check_circle</span>
                <span><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg flex items-center gap-3">
                <span class="material-symbols-outlined">error</span>
                <span><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <form action="<?= base_url('zones/store') ?>"
                  method="post"
                  enctype="multipart/form-data"
                  class="flex flex-col">
                <?= csrf_field() ?>
                <div class="p-6 flex flex-col gap-8">
                    <!-- Zone Information -->
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">location_on</span>
                                Zone Information
                            </h2>
                            <span class="text-xs font-medium px-2 py-1 rounded bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Active Status</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="zone_id">Zone ID</label>
                                <input class="rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary w-full p-2.5" id="zone_id" name="zone_id" placeholder="e.g. ZN-001" type="text" required/>
                            </div>
                            
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="zone_name">Zone Location</label>
                                <input class="rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary w-full p-2.5" id="zone_name" name="zone_name" placeholder="e.g. Warehouse Entrance A" type="text" required/>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300" for="location">Location Details (Optional)</label>
                            <textarea class="rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary w-full p-2.5" id="location" name="location" placeholder="Additional location details..." rows="2"></textarea>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Location Picture (Optional)
                            </label>
                            <input type="file"
                                   name="location_image"
                                   accept=".png,.jpg,.jpeg,.webp"
                                   class="block w-full text-sm text-gray-400
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-lg file:border-0
                                          file:bg-primary file:text-white
                                          hover:file:bg-primary/90">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Accepted formats: PNG, JPG, JPEG, WEBP
                            </p>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <input class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-primary bg-gray-50 dark:bg-gray-800 focus:ring-primary focus:ring-offset-0 dark:focus:ring-offset-gray-900 cursor-pointer" id="restricted_zone" name="restricted" type="checkbox" value="1"/>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer select-none" for="restricted_zone">Restricted Zone</label>
                        </div>
                    </div>

                    <hr class="border-gray-200 dark:border-gray-700"/>

                    <!-- Antenna Configuration -->
                    <div class="flex flex-col gap-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">router</span>
                                Antenna Configuration
                            </h2>
                            <button type="button" onclick="addAntenna()" class="text-sm font-medium text-primary hover:text-primary/80 flex items-center gap-1">
                                <span class="material-symbols-outlined text-base">add_circle</span>
                                Add Antenna
                            </button>
                        </div>

                        <div id="antennas-container" class="flex flex-col gap-4">
                            <!-- Antenna 1 -->
                            <div class="antenna-block flex flex-col gap-4 p-5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Antenna 01</h3>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">IP Address</label>
                                        <div class="relative">
                                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">lan</span>
                                            <input class="pl-10 p-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary w-full" name="antenna_ip[]" placeholder="192.168.1.101" type="text" required/>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Port</label>
                                        <input class="p-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary w-full" name="antenna_port[]" placeholder="49152" type="number" min="1" max="65535" value="49152" required/>
                                    </div>
                                    
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Antenna Mode</label>
                                        <select class="p-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary w-full" name="antenna_mode[]" required>
                                            <option value="">Select Antenna Mode</option>
                                            <?php foreach($antennaModes as $mode): ?>
                                                <option value="<?= esc($mode['mode_name']) ?>"><?= esc($mode['mode_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="flex flex-col gap-2">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Function</label>
                                        <select class="p-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary w-full" name="antenna_function[]" required>
                                            <option value="IN">IN</option>
                                            <option value="OUT">OUT</option>
                                            <option value="IN / OUT" selected>IN / OUT</option>
                    <option value="LOOKUP">LOOKUP (Search Stock desk — no stock change)</option>
                                            <option value="LOOKUP">LOOKUP (Search Stock desk — no stock change)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-800/50 rounded-b-xl border-t border-gray-200 dark:border-gray-700">
                    <a href="<?= base_url('zones') ?>" class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-white font-semibold text-sm hover:bg-primary/90 shadow-sm transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">save</span>
                        Save Zone
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
let antennaCount = 1;

function addAntenna() {
    antennaCount++;
    const container = document.getElementById('antennas-container');
    const newAntenna = document.createElement('div');
    newAntenna.className = 'antenna-block flex flex-col gap-4 p-5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50';
    
    newAntenna.innerHTML = `
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Antenna ${String(antennaCount).padStart(2, '0')}</h3>
            <button type="button" onclick="removeAntenna(this)" class="text-gray-400 hover:text-red-500 transition-colors">
                <span class="material-symbols-outlined text-lg">delete</span>
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">IP Address</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">lan</span>
                    <input class="pl-10 p-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary w-full" name="antenna_ip[]" placeholder="192.168.1.10${antennaCount}" type="text" required/>
                </div>
            </div>
            
            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Port</label>
                <input class="p-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary w-full" name="antenna_port[]" placeholder="49152" type="number" min="1" max="65535" value="49152" required/>
            </div>
            
            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Antenna Mode</label>
                <select class="p-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary w-full" name="antenna_mode[]" required>
                    <option value="">Select Antenna Mode</option>
                    <?php foreach($antennaModes as $mode): ?>
                        <option value="<?= esc($mode['mode_name']) ?>"><?= esc($mode['mode_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Function</label>
                <select class="p-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary w-full" name="antenna_function[]" required>
                    <option value="IN">IN</option>
                    <option value="OUT">OUT</option>
                    <option value="IN / OUT" selected>IN / OUT</option>
                    <option value="LOOKUP">LOOKUP (Search Stock desk — no stock change)</option>
                </select>
            </div>
        </div>
    `;
    
    container.appendChild(newAntenna);
}

function removeAntenna(button) {
    const antennaBlock = button.closest('.antenna-block');
    antennaBlock.remove();
    updateAntennaNumbers();
}

function updateAntennaNumbers() {
    const antennas = document.querySelectorAll('.antenna-block');
    antennas.forEach((antenna, index) => {
        const header = antenna.querySelector('h3');
        header.textContent = `Antenna ${String(index + 1).padStart(2, '0')}`;
    });
    antennaCount = antennas.length;
}
</script>

<?= view('templates/footer') ?>