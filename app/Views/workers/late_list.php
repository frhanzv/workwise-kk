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
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Staff Late List</h1>
            <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Workers who checked in late <?= esc($dateLabel) ?></p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="exportToCSV()" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[20px]">download</span>
                <span>Export</span>
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col flex-1">
        <div class="flex flex-wrap justify-between items-center gap-4 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 flex-1 min-w-[280px]">
                <div class="relative w-full max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xl">search</span>
                    <input id="searchInput" class="pl-10 w-full h-11 bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary placeholder-gray-500 dark:placeholder-gray-400 transition-colors" placeholder="Search by name, ID, or IC number..." type="text"/>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <button id="filterBtn" onclick="toggleFilter()" class="flex items-center justify-center h-10 px-4 gap-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined text-base">filter_list</span>
                        <span>Filters</span>
                        <span id="filterBadge" class="hidden ml-1 px-1.5 py-0.5 text-xs font-bold bg-primary text-white rounded-full"></span>
                    </button>
                    <div id="filterDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 p-4 space-y-3">
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
                        <button onclick="clearFilters()" class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            Clear Filters
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <button id="dateFilterBtn" onclick="toggleDateFilter()" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined text-lg">calendar_today</span>
                        <span id="dateFilterLabel"><?= esc($dateLabel) ?></span>
                        <span class="material-symbols-outlined text-lg">expand_more</span>
                    </button>
                    <div id="dateFilterDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 overflow-visible">
                        <div class="py-1">
                            <button onclick="filterByDate('today')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Today</button>
                            <button onclick="filterByDate('yesterday')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Yesterday</button>
                            <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                            <div class="px-4 py-3 pb-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Custom Date</label>
                                <input type="date" id="customDate" onchange="filterByCustomDate()" class="w-full px-3 py-2 text-sm bg-background-light dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg focus:ring-primary focus:border-primary">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" id="lateStaffTable">
                <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Full Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">IC No / Passport No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Staff No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check In</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-background-dark divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($lateStaff)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-5xl text-gray-300 dark:text-gray-600">event_available</span>
                                    <p class="text-gray-500 dark:text-gray-400 text-base font-medium">No late staff <?= $dateLabel === 'Today' ? 'today' : 'on this day' ?></p>
                                    <p class="text-gray-400 dark:text-gray-500 text-sm">All workers checked in on time</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lateStaff as $index => $staff): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors staff-row">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= $index + 1 ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick='openDetailModal(<?= json_encode($staff) ?>)' 
                                       class="text-primary hover:text-primary/80 transition-colors"
                                       title="View Details">
                                        <span class="material-symbols-outlined text-xl">search</span>
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <?php 
                                        $colors = ['blue', 'purple', 'yellow', 'pink', 'cyan', 'green', 'red', 'indigo', 'orange', 'teal'];
                                        $colorIndex = $index % count($colors);
                                        $color = $colors[$colorIndex];
                                        ?>
                                        <?php if (!empty($staff['profile_photo']) && file_exists(FCPATH . 'uploads/profiles/' . $staff['profile_photo'])): ?>
                                            <img src="<?= base_url('uploads/profiles/' . $staff['profile_photo']) ?>" 
                                                 alt="<?= esc($staff['full_name']) ?>" 
                                                 class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-<?= $color ?>-100 dark:bg-<?= $color ?>-900/30 flex items-center justify-center text-<?= $color ?>-700 dark:text-<?= $color ?>-300 font-semibold text-sm border border-<?= $color ?>-200 dark:border-<?= $color ?>-800">
                                                <?= esc($staff['initials']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white staff-name"><?= esc($staff['full_name']) ?></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400"><?= esc($staff['shift']) ?> Shift (<?= esc($staff['shift_start']) ?>)</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white staff-ic"><?= esc($staff['ic_number']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white staff-id"><?= esc($staff['worker_id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-red-600 dark:text-red-400"><?= esc($staff['check_in_time']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (!empty($lateStaff)): ?>
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
                    <span id="totalRecords" class="font-medium text-gray-900 dark:text-white">0</span>
                    <span>results</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button id="prevBtn" onclick="changePage(-1)" class="flex items-center justify-center h-8 px-3 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                    <span class="ml-1 text-sm font-medium">Previous</span>
                </button>
                <button id="nextBtn" onclick="changePage(1)" class="flex items-center justify-center h-8 px-3 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="mr-1 text-sm font-medium">Next</span>
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-background-dark rounded-xl shadow-xl max-w-2xl w-full">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white uppercase">Person</h3>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Staff No:</label>
                    <input id="modalStaffNo" type="text" readonly class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Full Name:</label>
                    <input id="modalFullName" type="text" readonly class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-lg text-sm">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Name On Vendor Pass:</label>
                <input id="modalVendorName" type="text" readonly class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-lg text-sm">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Time In:</label>
                    <input id="modalTimeIn" type="text" readonly class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Time Out:</label>
                    <input id="modalTimeOut" type="text" readonly class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-lg text-sm">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Late Reason:</label>
                <textarea id="modalLateReason" rows="3" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-lg text-sm resize-none"></textarea>
            </div>
        </div>
        
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700">
            <button onclick="closeDetailModal()" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 transition-colors">
                Back
            </button>
            <button onclick="approveStaff()" class="px-5 py-2.5 text-sm font-bold text-white bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 rounded-lg focus:outline-none focus:ring-4 focus:ring-green-300 dark:focus:ring-green-800 transition-colors">
                Approve
            </button>
            <button onclick="rejectStaff()" class="px-5 py-2.5 text-sm font-bold text-white bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 rounded-lg focus:outline-none focus:ring-4 focus:ring-red-300 dark:focus:ring-red-800 transition-colors">
                Reject
            </button>
        </div>
    </div>
</div>

<script>
    let currentStaff = null;
    
    // Pagination variables
    let currentPage = 1;
    let rowsPerPage = 10;
    
    function changeRowsPerPage() {
        rowsPerPage = parseInt(document.getElementById('rowsPerPageSelect').value);
        currentPage = 1;
        updatePagination();
    }
    
    // Toggle filter dropdown
    function toggleFilter() {
        const dropdown = document.getElementById('filterDropdown');
        dropdown.classList.toggle('hidden');
    }
    
    // Toggle date filter dropdown
    function toggleDateFilter() {
        const dropdown = document.getElementById('dateFilterDropdown');
        dropdown.classList.toggle('hidden');
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const dateDropdown = document.getElementById('dateFilterDropdown');
        const dateButton = document.getElementById('dateFilterBtn');
        if (dateDropdown && dateButton && !dateDropdown.contains(event.target) && !dateButton.contains(event.target)) {
            dateDropdown.classList.add('hidden');
        }
        
        const filterDropdown = document.getElementById('filterDropdown');
        const filterButton = document.getElementById('filterBtn');
        if (filterDropdown && filterButton && !filterDropdown.contains(event.target) && !filterButton.contains(event.target)) {
            filterDropdown.classList.add('hidden');
        }
    });
    
    // Filter by predefined date
    function filterByDate(period) {
        window.location.href = '<?= base_url('workers/late-list') ?>?filter=' + period;
    }
    
    // Filter by custom date
    function filterByCustomDate() {
        const dateInput = document.getElementById('customDate');
        if (dateInput.value) {
            window.location.href = '<?= base_url('workers/late-list') ?>?date=' + dateInput.value;
        }
    }
    
    // Apply filters
    function applyFilters() {
        updateFilterBadge();
        currentPage = 1;
        updatePagination();
    }
    
    // Clear filters
    function clearFilters() {
        document.getElementById('shiftFilter').value = '';
        updateFilterBadge();
        currentPage = 1;
        updatePagination();
    }
    
    // Update filter badge
    function updateFilterBadge() {
        const shiftFilter = document.getElementById('shiftFilter').value;
        const activeFilters = [shiftFilter].filter(f => f !== '').length;
        const badge = document.getElementById('filterBadge');
        
        if (activeFilters > 0) {
            badge.textContent = activeFilters;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
    
    // Filter table - deprecated, now using updatePagination
    function filterTable() {
        updatePagination();
    }
    
    // Open detail modal
    function openDetailModal(staff) {
        currentStaff = staff;
        document.getElementById('modalStaffNo').value = staff.worker_id;
        document.getElementById('modalFullName').value = staff.full_name;
        document.getElementById('modalVendorName').value = staff.full_name; // Or use vendor_name if available
        document.getElementById('modalTimeIn').value = staff.check_in_time;
        document.getElementById('modalTimeOut').value = ''; // Empty for now
        document.getElementById('modalLateReason').value = ''; // Can be filled by user
        document.getElementById('detailModal').classList.remove('hidden');
    }
    
    // Close detail modal
    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
        currentStaff = null;
    }
    
    // Approve staff
    function approveStaff() {
        if (!currentStaff) return;
        
        const lateReason = document.getElementById('modalLateReason').value;
        
        // Here you would send an AJAX request to approve the staff member
        fetch('<?= base_url('workers/approve-late') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                worker_id: currentStaff.worker_id,
                date: '<?= $date ?>',
                late_reason: lateReason,
                action: 'approve'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to approve');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
    
    // Reject staff
    function rejectStaff() {
        if (!currentStaff) return;
        
        const lateReason = document.getElementById('modalLateReason').value;
        
        if (!lateReason.trim()) {
            alert('Please provide a reason for rejection');
            return;
        }
        
        // Here you would send an AJAX request to reject the staff member
        fetch('<?= base_url('workers/approve-late') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                worker_id: currentStaff.worker_id,
                date: '<?= $date ?>',
                late_reason: lateReason,
                action: 'reject'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to reject');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        currentPage = 1;
        updatePagination();
    });
    
    // Update pagination
    function updatePagination() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const shiftFilter = document.getElementById('shiftFilter').value.toLowerCase();
        const rows = document.querySelectorAll('.staff-row');
        
        let visibleRows = [];
        
        rows.forEach(row => {
            const name = row.querySelector('.staff-name').textContent.toLowerCase();
            const staffId = row.querySelector('.staff-id').textContent.toLowerCase();
            const icNumber = row.querySelector('.staff-ic').textContent.toLowerCase();
            const shiftText = row.querySelector('.text-xs').textContent.toLowerCase();
            
            const searchMatch = name.includes(searchTerm) || staffId.includes(searchTerm) || icNumber.includes(searchTerm);
            const shiftMatch = !shiftFilter || shiftText.includes(shiftFilter);
            
            if (searchMatch && shiftMatch) {
                visibleRows.push(row);
            }
        });
        
        // Hide all rows first
        rows.forEach(row => row.style.display = 'none');
        
        // Calculate pagination
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = Math.min(startIndex + rowsPerPage, visibleRows.length);
        
        // Show only rows for current page
        for (let i = startIndex; i < endIndex; i++) {
            visibleRows[i].style.display = '';
            // Update row numbers
            const rowNumber = visibleRows[i].querySelector('td:first-child');
            if (rowNumber) {
                rowNumber.textContent = i + 1;
            }
        }
        
        // Update pagination info
        const total = visibleRows.length;
        document.getElementById('showingStart').textContent = total === 0 ? '0' : startIndex + 1;
        document.getElementById('showingEnd').textContent = total === 0 ? '0' : endIndex;
        document.getElementById('totalRecords').textContent = total;
        
        // Update button states
        document.getElementById('prevBtn').disabled = currentPage === 1;
        const totalPages = Math.ceil(total / rowsPerPage);
        document.getElementById('nextBtn').disabled = currentPage >= totalPages || total === 0;
    }
    
    function changePage(direction) {
        currentPage += direction;
        updatePagination();
    }
    
    // Initialize pagination on page load
    document.addEventListener('DOMContentLoaded', function() {
        updatePagination();
    });
    
    // Export to Excel functionality
    function exportToCSV() {
        const urlParams = new URLSearchParams(window.location.search);
        const date = urlParams.get('date') || '<?= $date ?>';
        const filter = urlParams.get('filter') || '';
        const shift = document.getElementById('shiftFilter').value;
        
        // Build export URL with current filters
        let exportUrl = '<?= base_url('workers/export-late-list') ?>?';
        if (date) exportUrl += 'date=' + date + '&';
        if (filter) exportUrl += 'filter=' + filter + '&';
        if (shift) exportUrl += 'shift=' + shift;
        
        // Redirect to export URL
        window.location.href = exportUrl;
    }
</script>

<?= $this->include('templates/footer') ?>
