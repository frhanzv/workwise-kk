<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6 max-w-[1600px] w-full mx-auto">
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

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mt-6 md:mt-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Zone List</h1>
            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Manage and configure all facility zones and associated antenna hardware.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="exportToCSV()" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[20px]">download</span>
                <span>Export</span>
            </button>
            <a href="<?= base_url('zones/add') ?>" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[20px]">add_location_alt</span>
                <span>Add New Zone</span>
            </a>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4 p-1">
        <div class="relative w-full md:w-96 group">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 transition-colors group-focus-within:text-primary">search</span>
            <input id="searchInput" class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-gray-400 dark:placeholder-gray-500 shadow-sm" placeholder="Search by Zone ID, Location, or IP..." type="text"/>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Filter by:</span>
                <select id="modeFilter" class="bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-lg focus:ring-primary focus:border-primary block p-2.5 outline-none cursor-pointer">
                    <option value="">All Modes</option>
                    <?php if (!empty($antenna_modes)): ?>
                        <?php foreach ($antenna_modes as $mode): ?>
                            <option value="<?= strtolower(esc($mode['mode_name'])) ?>"><?= esc($mode['mode_name']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 font-semibold" scope="col">Zone ID</th>
                        <th class="px-6 py-4 font-semibold" scope="col">Zone Location</th>
                        <th class="px-6 py-4 font-semibold" scope="col">Antenna Mode</th>
                        <th class="px-6 py-4 font-semibold" scope="col">IP Address</th>
                        <th class="px-6 py-4 font-semibold" scope="col">IN/OUT Function</th>
                        <th class="px-6 py-4 font-semibold text-right" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($zones as $zone): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group cursor-pointer" onclick="window.location='<?= base_url('zones/view/' . urlencode($zone['id'])) ?>'">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white"><?= esc($zone['id']) ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-<?= $zone['icon_color'] ?>-100 text-<?= $zone['icon_color'] ?>-600 dark:bg-<?= $zone['icon_color'] ?>-900/30 dark:text-<?= $zone['icon_color'] ?>-400">
                                        <span class="material-symbols-outlined text-[18px]"><?= $zone['icon'] ?></span>
                                    </div>
                                    <span><?= esc($zone['location']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= $zone['antenna_color'] ?>-100 text-<?= $zone['antenna_color'] ?>-800 dark:bg-<?= $zone['antenna_color'] ?>-900/30 dark:text-<?= $zone['antenna_color'] ?>-300 border border-<?= $zone['antenna_color'] ?>-200 dark:border-<?= $zone['antenna_color'] ?>-800">
                                    <?= esc($zone['antenna_mode']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-gray-600 dark:text-gray-400"><?= esc($zone['ip_address']) ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= $zone['function_color'] ?>-100 text-<?= $zone['function_color'] ?>-800 dark:bg-<?= $zone['function_color'] ?>-900/30 dark:text-<?= $zone['function_color'] ?>-300 border border-<?= $zone['function_color'] ?>-200 dark:border-<?= $zone['function_color'] ?>-800">
                                    <?php if ($zone['animated']): ?>
                                        <span class="w-1.5 h-1.5 rounded-full bg-<?= $zone['function_color'] ?>-600 animate-pulse"></span>
                                    <?php else: ?>
                                        <span class="w-1.5 h-1.5 rounded-full bg-<?= $zone['function_color'] ?>-600"></span>
                                    <?php endif; ?>
                                    <?= esc($zone['function']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right" onclick="event.stopPropagation()">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="<?= base_url('zones/edit/' . $zone['id']) ?>" class="p-2 text-gray-500 hover:text-primary dark:text-gray-400 dark:hover:text-white transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800" title="Edit Zone">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                    <button onclick="confirmDelete('<?= base_url('zones/delete/' . $zone['id']) ?>', 'Zone <?= esc($zone['id']) ?>')" class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800" title="Delete Zone">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($zones)): ?>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between px-4 sm:px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/30 gap-4">
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
                <span id="paginationInfo" class="text-sm text-gray-700 dark:text-gray-300">
                    Showing <span class="font-semibold text-gray-900 dark:text-white" id="showingStart">1</span> to 
                    <span class="font-semibold text-gray-900 dark:text-white" id="showingEnd">0</span> of 
                    <span class="font-semibold text-gray-900 dark:text-white" id="totalZones">0</span> zones
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button id="prevBtn" class="flex items-center justify-center gap-1 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                    Previous
                </button>
                <button id="nextBtn" class="flex items-center justify-center gap-1 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </button>
            </div>
        </div>
        <?php endif; ?>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6" id="deleteMessage">Are you sure you want to delete this item?</p>
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
let deleteUrl = '';

function confirmDelete(url, itemName) {
    deleteUrl = url;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete ${itemName}?`;
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

function exportToCSV() {
    const table = document.querySelector('table');
    const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');
    
    if (rows.length === 0) {
        alert('No data to export');
        return;
    }
    
    // CSV headers
    const headers = ['Zone ID', 'Zone Location', 'Antenna Mode', 'IP Address', 'Function'];
    let csvContent = headers.join(',') + '\n';
    
    // Extract data from visible rows
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const zoneId = cells[0].textContent.trim();
        // Get only the location text, not the icon
        const locationSpan = cells[1].querySelector('span:not(.material-symbols-outlined)');
        const location = locationSpan ? locationSpan.textContent.trim() : cells[1].textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
        const antennaMode = cells[2].textContent.trim();
        const ipAddress = cells[3].textContent.trim();
        const functionType = cells[4].textContent.trim();
        
        // Escape commas and quotes in data
        const escapeCSV = (text) => {
            if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                return '"' + text.replace(/"/g, '""') + '"';
            }
            return text;
        };
        
        const rowData = [
            escapeCSV(zoneId),
            escapeCSV(location),
            escapeCSV(antennaMode),
            escapeCSV(ipAddress),
            escapeCSV(functionType)
        ];
        
        csvContent += rowData.join(',') + '\n';
    });
    
    // Create download link
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    const timestamp = new Date().toISOString().slice(0, 10);
    link.setAttribute('href', url);
    link.setAttribute('download', `zones_export_${timestamp}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Pagination and Filter functionality
let currentPage = 1;
let currentRowsPerPage = 10;
let visibleRows = [];
let searchInput, modeFilter, tableRows, prevBtn, nextBtn, showingStart, showingEnd, totalZones;

function updateVisibleRows() {
    visibleRows = [];
    const searchTerm = searchInput.value.toLowerCase();
    const modeValue = modeFilter.value.toLowerCase();
    
    tableRows.forEach(row => {
        const zoneId = row.cells[0].textContent.toLowerCase();
        const zoneLocation = row.cells[1].textContent.toLowerCase();
        const antennaMode = row.cells[2].textContent.toLowerCase();
        const ipAddress = row.cells[3].textContent.toLowerCase();
        
        const matchesSearch = zoneId.includes(searchTerm) || 
                             zoneLocation.includes(searchTerm) || 
                             ipAddress.includes(searchTerm);
        
        const matchesMode = modeValue === '' || antennaMode.includes(modeValue);
        
        if (matchesSearch && matchesMode) {
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
    totalZones.textContent = total;
    
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
    modeFilter = document.getElementById('modeFilter');
    tableRows = document.querySelectorAll('tbody tr');
    prevBtn = document.getElementById('prevBtn');
    nextBtn = document.getElementById('nextBtn');
    showingStart = document.getElementById('showingStart');
    showingEnd = document.getElementById('showingEnd');
    totalZones = document.getElementById('totalZones');
    
    // Initialize
    updateVisibleRows();
    displayPage();
    
    // Event listeners
    searchInput.addEventListener('input', filterTable);
    modeFilter.addEventListener('change', filterTable);
    
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
</script>

<?= $this->include('templates/footer') ?>
