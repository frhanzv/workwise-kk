<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="<?= base_url('zones') ?>" class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= esc($zone['zone_id']) ?> - <?= esc($zone['zone_name']) ?></h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Zone Configuration & Hardware Details</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= base_url('zones/edit/' . urlencode($zone['zone_id'])) ?>" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-[20px]">edit</span>
                <span>Edit Zone</span>
            </a>
            <button onclick="confirmDelete('<?= base_url('zones/delete/' . urlencode($zone['zone_id'])) ?>', 'Zone <?= esc($zone['zone_id']) ?>')" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                <span class="material-symbols-outlined text-[20px]">delete</span>
                <span>Delete</span>
            </button>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-<?= $zone['icon_color'] ?>-100 dark:bg-<?= $zone['icon_color'] ?>-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-<?= $zone['icon_color'] ?>-600 dark:text-<?= $zone['icon_color'] ?>-400 text-2xl"><?= $zone['icon'] ?></span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Location Type</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($zone['zone_name']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-<?= $zone['antenna_color'] ?>-100 dark:bg-<?= $zone['antenna_color'] ?>-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-<?= $zone['antenna_color'] ?>-600 dark:text-<?= $zone['antenna_color'] ?>-400 text-2xl">sensors</span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Antenna Mode</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($zone['antenna_mode']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-<?= $zone['function_color'] ?>-100 dark:bg-<?= $zone['function_color'] ?>-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-<?= $zone['function_color'] ?>-600 dark:text-<?= $zone['function_color'] ?>-400 text-2xl">import_export</span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Access Control</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white"><?= esc($zone['function']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-2xl">check_circle</span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                    <p class="text-sm font-semibold text-green-600 dark:text-green-400">Active</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Antenna Configurations -->
    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">router</span>
                Antenna Configurations
                <span class="ml-2 px-2 py-0.5 text-xs font-bold bg-primary/10 text-primary rounded-full"><?= count($antennas) ?> <?= count($antennas) == 1 ? 'Antenna' : 'Antennas' ?></span>
            </h2>
        </div>
        <div class="p-6 grid grid-cols-1 <?= count($antennas) > 1 ? 'lg:grid-cols-2' : '' ?> gap-6">
            <?php foreach ($antennas as $index => $antenna): 
                $antennaColor = $modeColorMap[$antenna['antenna_mode']] ?? 'purple';
                $functionColor = 'blue';
                if ($antenna['function'] == 'IN' || $antenna['function'] == 'IN ONLY') {
                    $functionColor = 'green';
                } elseif ($antenna['function'] == 'OUT' || $antenna['function'] == 'OUT ONLY') {
                    $functionColor = 'red';
                } elseif ($antenna['function'] == 'LOOKUP') {
                    $functionColor = 'purple';
                }
            ?>
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-5 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-lg">sensors</span>
                        <?= esc($antenna['antenna_name'] ?? 'Antenna ' . ($index + 1)) ?>
                    </h3>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-<?= $functionColor ?>-100 text-<?= $functionColor ?>-800 dark:bg-<?= $functionColor ?>-900/30 dark:text-<?= $functionColor ?>-300 border border-<?= $functionColor ?>-200 dark:border-<?= $functionColor ?>-800">
                        <?php if ($functionColor == 'green'): ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-green-600 animate-pulse"></span>
                        <?php elseif ($functionColor == 'red'): ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                        <?php else: ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                        <?php endif; ?>
                        <?= esc($antenna['function']) ?>
                    </span>
                </div>
                
                <div class="space-y-3">
                    <!-- Network Info -->
                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Network Configuration</label>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-gray-400 text-base">computer</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 w-12">IP:</span>
                                <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white"><?= esc($antenna['ip_address']) ?></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-gray-400 text-base">settings_ethernet</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 w-12">Port:</span>
                                <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white"><?= esc($antenna['port'] ?: '49152') ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Antenna Mode -->
                    <div class="p-3 bg-<?= $antennaColor ?>-50 dark:bg-<?= $antennaColor ?>-900/10 rounded-lg border border-<?= $antennaColor ?>-200 dark:border-<?= $antennaColor ?>-800">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Antenna Mode</label>
                        <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded text-xs font-bold bg-<?= $antennaColor ?>-100 text-<?= $antennaColor ?>-800 dark:bg-<?= $antennaColor ?>-900/30 dark:text-<?= $antennaColor ?>-300 border border-<?= $antennaColor ?>-300 dark:border-<?= $antennaColor ?>-700">
                            <span class="material-symbols-outlined text-sm">sensors</span>
                            <?= esc($antenna['antenna_mode']) ?>
                        </span>
                    </div>
                    
                    <!-- Test Connection Button -->
                    <button onclick="testAntennaConnection('<?= esc($antenna['ip_address']) ?>', '<?= esc($antenna['port'] ?: 49152) ?>', <?= $index ?>)" 
                            id="testBtn_<?= $index ?>" 
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors border border-green-200 dark:border-green-800 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined text-base" id="testIcon_<?= $index ?>">network_check</span>
                        <span id="testText_<?= $index ?>">Test Connection</span>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">location_on</span>
                    Location Information
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Zone Identifier</label>
                    <p class="text-lg font-mono font-bold text-gray-900 dark:text-white"><?= esc($zone['zone_id']) ?></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Zone Name</label>
                    <p class="text-base font-medium text-gray-900 dark:text-white"><?= esc($zone['zone_name']) ?></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Physical Location</label>
                    <p class="text-base text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-400">place</span>
                        <?= esc($zone['location'] ?: 'Not specified') ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Access Control -->
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">import_export</span>
                    Access Control Function
                </h2>
            </div>
            <div class="p-6">
                <div class="p-4 bg-<?= $zone['function_color'] ?>-50 dark:bg-<?= $zone['function_color'] ?>-900/10 rounded-lg border border-<?= $zone['function_color'] ?>-200 dark:border-<?= $zone['function_color'] ?>-800 mb-4">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-3">Direction Control</label>
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-base font-bold bg-<?= $zone['function_color'] ?>-100 text-<?= $zone['function_color'] ?>-800 dark:bg-<?= $zone['function_color'] ?>-900/30 dark:text-<?= $zone['function_color'] ?>-300 border border-<?= $zone['function_color'] ?>-300 dark:border-<?= $zone['function_color'] ?>-700">
                        <?php if ($zone['animated']): ?>
                            <span class="w-2.5 h-2.5 rounded-full bg-<?= $zone['function_color'] ?>-600 animate-pulse"></span>
                        <?php else: ?>
                            <span class="w-2.5 h-2.5 rounded-full bg-<?= $zone['function_color'] ?>-600"></span>
                        <?php endif; ?>
                        <?= esc($zone['function']) ?>
                    </span>
                </div>
                <div class="space-y-3">
                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <span class="material-symbols-outlined text-gray-400 text-lg mt-0.5">info</span>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <?php if ($zone['function'] == 'IN / OUT'): ?>
                                Bidirectional access control. This zone monitors both entry and exit events for comprehensive tracking.
                            <?php elseif ($zone['function'] == 'IN ONLY'): ?>
                                Entry-only tracking. This zone records when workers enter the area but does not track exits.
                            <?php else: ?>
                                Exit-only tracking. This zone records when workers leave the area but does not track entries.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">history</span>
                System Information
            </h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Status</label>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border border-green-200 dark:border-green-800">
                        <span class="size-2 rounded-full bg-green-500 animate-pulse"></span>
                        Active & Online
                    </span>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Created Date</label>
                    <p class="text-sm text-gray-900 dark:text-white"><?= date('M d, Y \a\t H:i', strtotime($zone['created_at'])) ?></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Last Updated</label>
                    <p class="text-sm text-gray-900 dark:text-white"><?= date('M d, Y \a\t H:i', strtotime($zone['updated_at'])) ?></p>
                </div>
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Zone</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone</p>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6" id="deleteMessage">Are you sure you want to delete this zone?</p>
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

<!-- Connection Test Result Modal -->
<div id="connectionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-lg mx-4 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center" id="connectionResultIcon">
                <span class="material-symbols-outlined text-2xl"></span>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="connectionResultTitle">Connection Test</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400" id="connectionResultSubtitle"></p>
            </div>
        </div>
        <div id="connectionResultContent" class="text-gray-600 dark:text-gray-300 mb-6"></div>
        <button onclick="closeConnectionModal()" class="w-full px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark font-medium transition-colors">
            Close
        </button>
    </div>
</div>

<script>
let deleteUrl = '';

function confirmDelete(url, zoneName) {
    deleteUrl = url;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete ${zoneName}? This action cannot be undone.`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteUrl = '';
}

function executeDelete() {
    if (deleteUrl) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = deleteUrl;
        document.body.appendChild(form);
        form.submit();
    }
}

function testConnection() {
    const btn = document.getElementById('testConnectionBtn');
    const icon = document.getElementById('testConnectionIcon');
    const text = document.getElementById('testConnectionText');
    
    // Disable button and show loading state
    btn.disabled = true;
    icon.textContent = 'progress_activity';
    icon.classList.add('animate-spin');
    text.textContent = 'Testing...';
    
    fetch('<?= base_url("zones/test-connection/" . urlencode($zone["zone_id"])) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        // Reset button
        btn.disabled = false;
        icon.textContent = 'network_check';
        icon.classList.remove('animate-spin');
        text.textContent = 'Test Connection';
        
        // Show result modal
        showConnectionResult(data);
    })
    .catch(error => {
        // Reset button
        btn.disabled = false;
        icon.textContent = 'network_check';
        icon.classList.remove('animate-spin');
        text.textContent = 'Test Connection';
        
        showConnectionResult({
            success: false,
            message: 'Failed to perform connection test',
            details: error.message
        });
    });
}

function testAntennaConnection(ip, port, index) {
    const btn = document.getElementById('testBtn_' + index);
    const icon = document.getElementById('testIcon_' + index);
    const text = document.getElementById('testText_' + index);
    
    // Disable button and show loading state
    btn.disabled = true;
    icon.textContent = 'progress_activity';
    icon.classList.add('animate-spin');
    text.textContent = 'Testing...';
    
    fetch('<?= base_url("zones/test-antenna-connection") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            ip: ip,
            port: port
        })
    })
    .then(response => response.json())
    .then(data => {
        // Reset button
        btn.disabled = false;
        icon.textContent = 'network_check';
        icon.classList.remove('animate-spin');
        text.textContent = 'Test Connection';
        
        // Show result modal
        showConnectionResult(data);
    })
    .catch(error => {
        // Reset button
        btn.disabled = false;
        icon.textContent = 'network_check';
        icon.classList.remove('animate-spin');
        text.textContent = 'Test Connection';
        
        showConnectionResult({
            success: false,
            message: 'Failed to perform connection test',
            details: error.message
        });
    });
}

function showConnectionResult(data) {
    const modal = document.getElementById('connectionModal');
    const iconDiv = document.getElementById('connectionResultIcon');
    const title = document.getElementById('connectionResultTitle');
    const subtitle = document.getElementById('connectionResultSubtitle');
    const content = document.getElementById('connectionResultContent');
    
    if (data.success) {
        iconDiv.className = 'flex-shrink-0 w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center';
        iconDiv.querySelector('span').className = 'material-symbols-outlined text-green-600 dark:text-green-400 text-2xl';
        iconDiv.querySelector('span').textContent = 'check_circle';
        title.textContent = 'Connection Successful';
        subtitle.textContent = 'RFID reader is online and responding';
        content.innerHTML = `
            <div class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/10 rounded-lg border border-green-200 dark:border-green-800">
                <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-xl mt-0.5">info</span>
                <div>
                    <p class="text-sm font-medium text-green-900 dark:text-green-300 mb-1">${data.message}</p>
                    ${data.details ? `<p class="text-xs text-green-700 dark:text-green-400">${data.details}</p>` : ''}
                </div>
            </div>
        `;
    } else {
        iconDiv.className = 'flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center';
        iconDiv.querySelector('span').className = 'material-symbols-outlined text-red-600 dark:text-red-400 text-2xl';
        iconDiv.querySelector('span').textContent = 'error';
        title.textContent = 'Connection Failed';
        subtitle.textContent = 'Unable to reach RFID reader';
        
        let troubleshootingHtml = '';
        if (data.troubleshooting && data.troubleshooting.length > 0) {
            troubleshootingHtml = `
                <div class="mt-4 pt-4 border-t border-red-200 dark:border-red-800">
                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">Troubleshooting Tips:</p>
                    <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                        ${data.troubleshooting.map(tip => `<li class="flex items-start gap-2"><span class="material-symbols-outlined text-primary text-lg mt-0.5">arrow_right</span><span>${tip}</span></li>`).join('')}
                    </ul>
                </div>
            `;
        }
        
        content.innerHTML = `
            <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/10 rounded-lg border border-red-200 dark:border-red-800">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-xl mt-0.5">warning</span>
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-900 dark:text-red-300 mb-1">${data.message}</p>
                    ${data.details ? `<p class="text-xs text-red-700 dark:text-red-400 font-mono mb-2">${data.details}</p>` : ''}
                    ${data.error_code ? `<p class="text-xs text-red-700 dark:text-red-400">Error Code: ${data.error_code}</p>` : ''}
                </div>
            </div>
            ${troubleshootingHtml}
        `;
    }
    
    modal.classList.remove('hidden');
}

function closeConnectionModal() {
    document.getElementById('connectionModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

document.getElementById('connectionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeConnectionModal();
    }
});
</script>

<?= view('templates/footer') ?>
