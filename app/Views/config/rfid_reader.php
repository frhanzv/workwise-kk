<?= $this->include('templates/header') ?>

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config') ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-gray-900 dark:text-white text-xl font-bold leading-tight">RFID Reader Settings</h1>
    </div>
    <button onclick="testConnection()" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined text-lg">wifi_find</span>
        Test Connection
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="mb-4 p-3 bg-green-100 dark:bg-green-900/20 border border-green-500 text-green-700 dark:text-green-400 rounded-lg text-sm flex items-center gap-2">
        <span class="material-symbols-outlined text-lg">check_circle</span>
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-4 p-3 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg text-sm flex items-center gap-2">
        <span class="material-symbols-outlined text-lg">error</span>
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="grid gap-6 md:grid-cols-2">
    <!-- Configuration Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">settings</span>
            Reader Configuration
        </h2>
        
        <form action="<?= base_url('config/rfid-reader/update') ?>" method="POST">
            <?= csrf_field() ?>
            
            <!-- Reader IP -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Reader IP Address
                    <span class="text-red-500">*</span>
                </label>
                <input type="text" name="reader_ip" value="<?= esc($config->readerIP) ?>" required
                       pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$"
                       placeholder="192.168.1.100"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent font-mono">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">IP address of the Yanzeo SA810 reader</p>
            </div>
            
            <!-- Reader Port -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Reader Port
                    <span class="text-red-500">*</span>
                </label>
                <input type="number" name="reader_port" value="<?= esc($config->readerPort) ?>" required
                       min="1" max="65535"
                       placeholder="6000"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">TCP port (default: 6000 or 8080)</p>
            </div>
            
            <!-- Reader ID -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Reader ID
                </label>
                <input type="text" name="reader_id" value="<?= esc($config->readerID) ?>"
                       placeholder="SA810_001"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Unique identifier for this reader</p>
            </div>
            
            <!-- Protocol -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Protocol
                </label>
                <select name="protocol"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="hex" <?= $config->protocol === 'hex' ? 'selected' : '' ?>>Hexadecimal (EPC)</option>
                    <option value="json" <?= $config->protocol === 'json' ? 'selected' : '' ?>>JSON</option>
                    <option value="wiegand" <?= $config->protocol === 'wiegand' ? 'selected' : '' ?>>Wiegand</option>
                    <option value="default" <?= $config->protocol === 'default' ? 'selected' : '' ?>>Auto-detect</option>
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Data format from reader</p>
            </div>
            
            <!-- Connection Timeout -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Connection Timeout (seconds)
                </label>
                <input type="number" name="connection_timeout" value="<?= esc($config->connectionTimeout) ?>"
                       min="1" max="30"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Time to wait for reader connection</p>
            </div>
            
            <!-- Default Zone ID -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Default Zone ID
                </label>
                <input type="number" name="default_zone_id" value="<?= esc($config->defaultZoneID) ?>"
                       min="1"
                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default zone for attendance records</p>
            </div>
            
            <button type="submit" class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-lg">save</span>
                Save Settings
            </button>
        </form>
    </div>
    
    <!-- Current Status & Info -->
    <div class="space-y-6">
        <!-- Current Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-500">info</span>
                Current Settings
            </h2>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">IP Address:</span>
                    <span class="text-sm font-mono text-gray-900 dark:text-white"><?= esc($config->readerIP) ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Port:</span>
                    <span class="text-sm font-mono text-gray-900 dark:text-white"><?= esc($config->readerPort) ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Reader ID:</span>
                    <span class="text-sm font-mono text-gray-900 dark:text-white"><?= esc($config->readerID) ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Protocol:</span>
                    <span class="text-sm text-gray-900 dark:text-white capitalize"><?= esc($config->protocol) ?></span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Default Zone:</span>
                    <span class="text-sm text-gray-900 dark:text-white"><?= esc($config->defaultZoneID) ?></span>
                </div>
            </div>
        </div>
        
        <!-- API Endpoints -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-500">webhook</span>
                API Endpoints
            </h2>
            
            <div class="space-y-3">
                <div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Webhook URL (for SA810):</div>
                    <div class="bg-gray-50 dark:bg-gray-900 p-2 rounded text-xs font-mono text-gray-900 dark:text-white break-all border border-gray-200 dark:border-gray-700">
                        <?= base_url('api/rfid/tag-read') ?>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Test Endpoint:</div>
                    <div class="bg-gray-50 dark:bg-gray-900 p-2 rounded text-xs font-mono text-gray-900 dark:text-white break-all border border-gray-200 dark:border-gray-700">
                        <?= base_url('api/rfid/scan?tag_id=TEST') ?>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Web Test Interface:</div>
                    <a href="<?= base_url('rfid-test.html') ?>" target="_blank" 
                       class="bg-gray-50 dark:bg-gray-900 p-2 rounded text-xs font-mono text-primary hover:text-primary/80 break-all border border-gray-200 dark:border-gray-700 flex items-center justify-between group">
                        <?= base_url('rfid-test.html') ?>
                        <span class="material-symbols-outlined text-sm opacity-0 group-hover:opacity-100 transition-opacity">open_in_new</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Quick Guide -->
        <div class="bg-gradient-to-br from-primary/10 to-purple-500/10 dark:from-primary/20 dark:to-purple-500/20 rounded-lg border border-primary/20 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">lightbulb</span>
                Quick Setup Guide
            </h2>
            
            <ol class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex gap-2">
                    <span class="font-bold text-primary">1.</span>
                    <span>Connect SA810 to your network via Ethernet</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-bold text-primary">2.</span>
                    <span>Find the reader's IP address (default: 192.168.1.100)</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-bold text-primary">3.</span>
                    <span>Enter IP and Port above and save</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-bold text-primary">4.</span>
                    <span>Click "Test Connection" to verify</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-bold text-primary">5.</span>
                    <span>Configure SA810 to send data to the webhook URL</span>
                </li>
            </ol>
            
            <div class="mt-4 pt-4 border-t border-primary/20">
                <a href="<?= base_url('RFID_IMPLEMENTATION_GUIDE.md') ?>" target="_blank" 
                   class="text-sm text-primary hover:text-primary/80 font-medium flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">description</span>
                    View Full Implementation Guide
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Connection Test Modal -->
<div id="testModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4 shadow-xl">
        <div id="testModalContent" class="text-center">
            <div class="flex items-center justify-center mb-4">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>
            <p class="text-gray-700 dark:text-gray-300">Testing connection to RFID reader...</p>
        </div>
    </div>
</div>

<script>
function testConnection() {
    const modal = document.getElementById('testModal');
    const modalContent = document.getElementById('testModalContent');
    
    // Show loading modal
    modal.classList.remove('hidden');
    modalContent.innerHTML = `
        <div class="flex items-center justify-center mb-4">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
        </div>
        <p class="text-gray-700 dark:text-gray-300">Testing connection to RFID reader...</p>
    `;
    
    // Make API call
    fetch('<?= base_url('config/rfid-reader/test-connection') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalContent.innerHTML = `
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-4xl">check_circle</span>
                        </div>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Connection Successful!</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">${data.message}</p>
                    <button onclick="closeTestModal()" class="w-full px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 font-medium transition-colors">
                        Close
                    </button>
                `;
            } else {
                modalContent.innerHTML = `
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-4xl">error</span>
                        </div>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Connection Failed</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">${data.message}</p>
                    <button onclick="closeTestModal()" class="w-full px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors">
                        Close
                    </button>
                `;
            }
        })
        .catch(error => {
            modalContent.innerHTML = `
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-4xl">error</span>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Network Error</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Could not connect to the server. Please try again.</p>
                <button onclick="closeTestModal()" class="w-full px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors">
                    Close
                </button>
            `;
        });
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('testModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTestModal();
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const ip = document.querySelector('input[name="reader_ip"]').value;
    const port = document.querySelector('input[name="reader_port"]').value;
    
    // Validate IP format
    const ipPattern = /^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/;
    if (!ipPattern.test(ip)) {
        e.preventDefault();
        alert('Please enter a valid IP address (e.g., 192.168.1.100)');
        return false;
    }
    
    // Validate port
    if (port < 1 || port > 65535) {
        e.preventDefault();
        alert('Port must be between 1 and 65535');
        return false;
    }
});
</script>

<?= $this->include('templates/footer') ?>
