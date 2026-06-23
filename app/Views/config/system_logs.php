<?= $this->include('templates/header') ?>

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config') ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-gray-900 dark:text-white text-xl font-bold leading-tight">System Logs</h1>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <!-- Filters -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <!-- Log Level Filter -->
        <div class="flex-1">
            <label for="logLevel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Log Level
            </label>
            <select id="logLevel" 
                    class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="all" <?= $selectedLevel === 'all' ? 'selected' : '' ?>>All Logs</option>
                <option value="critical" <?= $selectedLevel === 'critical' ? 'selected' : '' ?>>Critical</option>
                <option value="error" <?= $selectedLevel === 'error' ? 'selected' : '' ?>>Error</option>
                <option value="warning" <?= $selectedLevel === 'warning' ? 'selected' : '' ?>>Warning</option>
                <option value="info" <?= $selectedLevel === 'info' ? 'selected' : '' ?>>Info</option>
                <option value="debug" <?= $selectedLevel === 'debug' ? 'selected' : '' ?>>Debug</option>
            </select>
        </div>

        <!-- Date Filter -->
        <div class="flex-1">
            <label for="logDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Date
            </label>
            <select id="logDate" 
                    class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                <?php foreach ($logFiles as $date): ?>
                    <option value="<?= $date ?>" <?= $selectedDate === $date ? 'selected' : '' ?>>
                        <?= date('F j, Y', strtotime($date)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Button -->
        <div class="flex items-end">
            <button id="filterLogs" 
                    class="px-6 py-2 bg-primary hover:bg-primary/90 text-white font-medium rounded-lg transition-colors">
                <span class="material-symbols-outlined text-sm align-middle mr-1">filter_alt</span>
                Filter
            </button>
        </div>
    </div>

    <!-- Log Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-gray-600 dark:text-gray-400 text-sm">list</span>
                <span class="text-xs text-gray-600 dark:text-gray-400">Total</span>
            </div>
            <div class="text-lg font-bold text-gray-900 dark:text-white" id="stat-all">
                <?= $stats['total'] ?>
            </div>
        </div>

        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 border border-red-200 dark:border-red-800">
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-sm">dangerous</span>
                <span class="text-xs text-red-600 dark:text-red-400">Critical</span>
            </div>
            <div class="text-lg font-bold text-red-900 dark:text-red-300" id="stat-critical">
                <?= $stats['critical'] ?>
            </div>
        </div>

        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3 border border-orange-200 dark:border-orange-800">
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-orange-600 dark:text-orange-400 text-sm">error</span>
                <span class="text-xs text-orange-600 dark:text-orange-400">Error</span>
            </div>
            <div class="text-lg font-bold text-orange-900 dark:text-orange-300" id="stat-error">
                <?= $stats['error'] ?>
            </div>
        </div>

        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 border border-yellow-200 dark:border-yellow-800">
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400 text-sm">warning</span>
                <span class="text-xs text-yellow-600 dark:text-yellow-400">Warning</span>
            </div>
            <div class="text-lg font-bold text-yellow-900 dark:text-yellow-300" id="stat-warning">
                <?= $stats['warning'] ?>
            </div>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-800">
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-sm">info</span>
                <span class="text-xs text-blue-600 dark:text-blue-400">Info</span>
            </div>
            <div class="text-lg font-bold text-blue-900 dark:text-blue-300" id="stat-info">
                <?= $stats['info'] ?>
            </div>
        </div>

        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 border border-purple-200 dark:border-purple-800">
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-purple-600 dark:text-purple-400 text-sm">bug_report</span>
                <span class="text-xs text-purple-600 dark:text-purple-400">Debug</span>
            </div>
            <div class="text-lg font-bold text-purple-900 dark:text-purple-300" id="stat-debug">
                <?= $stats['debug'] ?>
            </div>
        </div>
    </div>

    <!-- Log Entries -->
    <div id="logContainer" class="space-y-2" style="max-height: 600px; overflow-y: auto;">
        <?php if (empty($logEntries)): ?>
            <div class="text-center py-12">
                <span class="material-symbols-outlined text-gray-400 dark:text-gray-600 text-6xl mb-3">description</span>
                <p class="text-gray-600 dark:text-gray-400 text-base">No log entries found for the selected filters.</p>
            </div>
        <?php else: ?>
            <div id="logEntries">
                <?php foreach ($logEntries as $entry): ?>
                    <?php
                    $levelColors = [
                        'CRITICAL' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300',
                        'ERROR' => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800 text-orange-700 dark:text-orange-300',
                        'WARNING' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-300',
                        'INFO' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300',
                        'DEBUG' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300',
                        'NOTICE' => 'bg-cyan-50 dark:bg-cyan-900/20 border-cyan-200 dark:border-cyan-800 text-cyan-700 dark:text-cyan-300',
                        'ALERT' => 'bg-pink-50 dark:bg-pink-900/20 border-pink-200 dark:border-pink-800 text-pink-700 dark:text-pink-300',
                        'EMERGENCY' => 'bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-700 text-red-800 dark:text-red-200'
                    ];
                    $levelClass = $levelColors[$entry['level']] ?? 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300';
                    
                    $levelIcons = [
                        'CRITICAL' => 'dangerous',
                        'ERROR' => 'error',
                        'WARNING' => 'warning',
                        'INFO' => 'info',
                        'DEBUG' => 'bug_report',
                        'NOTICE' => 'notifications',
                        'ALERT' => 'notification_important',
                        'EMERGENCY' => 'emergency'
                    ];
                    $levelIcon = $levelIcons[$entry['level']] ?? 'circle';
                    ?>
                    <div class="log-entry <?= $levelClass ?> border rounded-lg p-3 hover:shadow-sm transition-shadow">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-xl flex-shrink-0 mt-0.5"><?= $levelIcon ?></span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded"><?= $entry['level'] ?></span>
                                    <span class="text-xs opacity-75"><?= $entry['timestamp'] ?></span>
                                </div>
                                <p class="text-sm break-words font-mono"><?= esc($entry['message']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Loading indicator -->
            <div id="loadingIndicator" class="hidden text-center py-4">
                <div class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm">Loading more logs...</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
let currentOffset = 10;
let isLoading = false;
let hasMore = <?= $totalEntries > 10 ? 'true' : 'false' ?>;
const currentLevel = '<?= $selectedLevel ?>';
const currentDate = '<?= $selectedDate ?>';

const levelColors = {
    'CRITICAL': 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300',
    'ERROR': 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800 text-orange-700 dark:text-orange-300',
    'WARNING': 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-300',
    'INFO': 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300',
    'DEBUG': 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300',
    'NOTICE': 'bg-cyan-50 dark:bg-cyan-900/20 border-cyan-200 dark:border-cyan-800 text-cyan-700 dark:text-cyan-300',
    'ALERT': 'bg-pink-50 dark:bg-pink-900/20 border-pink-200 dark:border-pink-800 text-pink-700 dark:text-pink-300',
    'EMERGENCY': 'bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-700 text-red-800 dark:text-red-200'
};

const levelIcons = {
    'CRITICAL': 'dangerous',
    'ERROR': 'error',
    'WARNING': 'warning',
    'INFO': 'info',
    'DEBUG': 'bug_report',
    'NOTICE': 'notifications',
    'ALERT': 'notification_important',
    'EMERGENCY': 'emergency'
};

function createLogEntry(entry) {
    const levelClass = levelColors[entry.level] || 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300';
    const levelIcon = levelIcons[entry.level] || 'circle';
    const escapedMessage = entry.message.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    
    return `
        <div class="log-entry ${levelClass} border rounded-lg p-3 hover:shadow-sm transition-shadow">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-xl flex-shrink-0 mt-0.5">${levelIcon}</span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded">${entry.level}</span>
                        <span class="text-xs opacity-75">${entry.timestamp}</span>
                    </div>
                    <p class="text-sm break-words font-mono">${escapedMessage}</p>
                </div>
            </div>
        </div>
    `;
}

function loadMoreLogs() {
    if (isLoading || !hasMore) return;
    
    isLoading = true;
    document.getElementById('loadingIndicator').classList.remove('hidden');
    
    fetch(`<?= base_url('config/load-more-logs') ?>?level=${currentLevel}&date=${currentDate}&offset=${currentOffset}`)
        .then(response => response.json())
        .then(data => {
            const logEntries = document.getElementById('logEntries');
            
            data.logs.forEach(log => {
                logEntries.insertAdjacentHTML('beforeend', createLogEntry(log));
            });
            
            currentOffset += 10;
            hasMore = data.hasMore;
            isLoading = false;
            document.getElementById('loadingIndicator').classList.add('hidden');
        })
        .catch(error => {
            console.error('Error loading logs:', error);
            isLoading = false;
            document.getElementById('loadingIndicator').classList.add('hidden');
        });
}

// Infinite scroll
const logContainer = document.getElementById('logContainer');
if (logContainer) {
    logContainer.addEventListener('scroll', function() {
        const scrollPosition = this.scrollTop + this.clientHeight;
        const scrollHeight = this.scrollHeight;
        
        // Load more when scrolled to 80% of the container
        if (scrollPosition >= scrollHeight * 0.8) {
            loadMoreLogs();
        }
    });
}

document.getElementById('filterLogs').addEventListener('click', function() {
    const level = document.getElementById('logLevel').value;
    const date = document.getElementById('logDate').value;
    window.location.href = `<?= base_url('config/system-logs') ?>?level=${level}&date=${date}`;
});

// Allow Enter key to trigger filter
document.getElementById('logLevel').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('filterLogs').click();
    }
});

document.getElementById('logDate').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('filterLogs').click();
    }
});
</script>

<?= $this->include('templates/footer') ?>
