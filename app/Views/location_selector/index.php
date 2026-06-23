<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6 px-2 sm:px-4 lg:px-6 py-4 sm:py-6">
    <div class="max-w-7xl w-full mx-auto">
        <!-- Header -->
        <div class="text-center mb-6 lg:mb-8">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-2">Select Your Location</h1>
            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Choose a zone to start tracking attendance</p>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-4 lg:mb-6 p-3 sm:p-4 bg-green-100 dark:bg-green-900/20 border border-green-500 text-green-700 dark:text-green-400 rounded-lg text-sm sm:text-base">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 lg:mb-6 p-3 sm:p-4 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg text-sm sm:text-base">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <?php if (!empty($zones)): ?>
            <div class="mb-4 lg:mb-6">
                <div class="relative max-w-2xl mx-auto">
                    <span class="material-symbols-outlined absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg sm:text-xl">search</span>
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Search locations by name or zone ID..."
                           class="w-full pl-10 sm:pl-12 pr-10 sm:pr-12 py-3 sm:py-4 text-sm sm:text-base rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                           autocomplete="off">
                    <button id="clearSearch" class="hidden absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <span class="material-symbols-outlined text-lg sm:text-xl">close</span>
                    </button>
                </div>
                <div id="searchResults" class="text-center mt-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400"></div>
            </div>
        <?php endif; ?>

        <form id="locationForm" action="<?= base_url('location-selector/select') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="zone_id" id="selectedZoneId" value="">

            <!-- Zones Grid -->
            <div id="zonesGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 lg:gap-6 mb-32 sm:mb-36 lg:mb-40">
                <?php foreach ($zones as $zone): ?>
                    <div class="zone-card cursor-pointer bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-4 border-transparent hover:scale-[1.02]"
                         data-zone-id="<?= $zone['id'] ?>"
                         data-zone-name="<?= strtolower(esc($zone['zone_name'])) ?>"
                         data-zone-code="<?= strtolower(esc($zone['zone_id'])) ?>"
                         onclick="selectZone(<?= $zone['id'] ?>, '<?= esc($zone['zone_name']) ?>')">
                        
                        <!-- Selected Checkmark -->
                        <div class="selected-indicator hidden absolute top-3 sm:top-4 left-3 sm:left-4 z-10 bg-primary text-white rounded-full w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center shadow-lg">
                            <span class="material-symbols-outlined text-lg sm:text-xl">check</span>
                        </div>

                        <!-- Image Section -->
                        <div class="relative h-40 sm:h-48 overflow-hidden bg-gray-100 dark:bg-gray-700">
                            <?php if (!empty($zone['image_url'])): ?>
                                <!-- Display uploaded image -->
                                <img src="<?= $zone['image_url'] ?>" 
                                     alt="<?= esc($zone['zone_name']) ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <!-- Display placeholder for missing photo -->
                                <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800">
                                    <span class="material-symbols-outlined text-4xl sm:text-5xl text-gray-400 dark:text-gray-500 mb-1 sm:mb-2">add_photo_alternate</span>
                                    <span class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-medium">No Photo Available</span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Signal Status Badge -->
                            <div class="absolute top-2 sm:top-3 right-2 sm:right-3">
                                <div class="flex items-center gap-1 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold text-white shadow-lg" 
                                     style="background-color: <?= $zone['signal_color'] ?>">
                                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-white animate-pulse"></span>
                                    <span class="uppercase"><?= $zone['signal_status'] ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Content Section -->
                        <div class="p-4 sm:p-5 lg:p-6">
                            <!-- Zone Name -->
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1 sm:mb-2 truncate">
                                <?= esc($zone['zone_name']) ?>
                            </h3>
                            
                            <!-- Zone ID -->
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-3 sm:mb-4">
                                Zone ID: <?= esc($zone['zone_id']) ?>
                            </p>

                            <!-- Stats -->
                            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                                <!-- Today's Count -->
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-2 sm:p-3 text-center">
                                    <div class="text-xl sm:text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        <?= $zone['today_count'] ?>
                                    </div>
                                    <div class="text-[10px] sm:text-xs text-gray-600 dark:text-gray-400 mt-0.5">Today's Scans</div>
                                </div>

                                <!-- Last Scan -->
                                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-2 sm:p-3 text-center">
                                    <div class="text-xs sm:text-sm font-semibold text-green-600 dark:text-green-400">
                                        <?php if ($zone['last_scan']): ?>
                                            <?= date('H:i', strtotime($zone['last_scan'])) ?>
                                        <?php else: ?>
                                            --:--
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[10px] sm:text-xs text-gray-600 dark:text-gray-400 mt-0.5">Last Scan</div>
                                </div>

                                <!-- Assets in Zone -->
                                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-2 sm:p-3 text-center">
                                    <div class="text-xl sm:text-2xl font-bold text-orange-600 dark:text-orange-400">
                                        <?= $zone['asset_count'] ?>
                                    </div>
                                    <div class="text-[10px] sm:text-xs text-gray-600 dark:text-gray-400 mt-0.5">Assets</div>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="hidden text-center py-8 sm:py-12 bg-white dark:bg-gray-800 rounded-xl shadow-lg mb-6 lg:mb-8">
                <span class="material-symbols-outlined text-5xl sm:text-6xl text-gray-400 mb-3 sm:mb-4">search_off</span>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2 px-4">No Locations Found</h3>
                <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400 px-4">Try adjusting your search terms</p>
            </div>

            <?php if (empty($zones)): ?>
                <div class="text-center py-8 sm:py-12 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                    <span class="material-symbols-outlined text-5xl sm:text-6xl text-gray-400 mb-3 sm:mb-4">location_off</span>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2 px-4">No Locations Available</h3>
                    <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400 px-4">Please contact your administrator to set up zones.</p>
                </div>
            <?php endif; ?>

            <!-- Select Location Button (Fixed at Bottom) -->
            <?php if (!empty($zones)): ?>
                <div class="fixed bottom-0 left-0 lg:left-56 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-3 sm:p-4 lg:p-6 shadow-2xl z-40">
                    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
                        <div class="max-w-2xl mx-auto">
                        <div id="selectionInfo" class="hidden mb-3 sm:mb-4 p-3 sm:p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center">
                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                Selected: <span id="selectedZoneName" class="font-bold text-gray-900 dark:text-white"></span>
                            </p>
                        </div>
                        
                        <button type="submit" 
                                id="submitBtn"
                                disabled
                                class="w-full bg-primary hover:bg-primary/90 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold text-base sm:text-lg py-3 sm:py-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 sm:gap-3 group">
                            <span class="material-symbols-outlined text-xl sm:text-2xl">location_on</span>
                            <span class="text-sm sm:text-base">Select Location & Continue</span>
                            <span class="material-symbols-outlined text-xl sm:text-2xl group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        </button>
                        
                        <p class="text-center text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 sm:mt-3">
                            Please select a location above to continue
                        </p>
                    </div>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
let selectedZoneId = null;
let selectedZoneName = '';

function selectZone(zoneId, zoneName) {
    // Remove selection from all cards
    document.querySelectorAll('.zone-card').forEach(card => {
        card.classList.remove('border-primary', 'ring-4', 'ring-primary/20');
        card.classList.add('border-transparent');
        card.querySelector('.selected-indicator').classList.add('hidden');
    });
    
    // Add selection to clicked card
    const selectedCard = document.querySelector(`[data-zone-id="${zoneId}"]`);
    selectedCard.classList.remove('border-transparent');
    selectedCard.classList.add('border-primary', 'ring-4', 'ring-primary/20');
    selectedCard.querySelector('.selected-indicator').classList.remove('hidden');
    
    // Update hidden input and selection info
    selectedZoneId = zoneId;
    selectedZoneName = zoneName;
    document.getElementById('selectedZoneId').value = zoneId;
    document.getElementById('selectedZoneName').textContent = zoneName;
    document.getElementById('selectionInfo').classList.remove('hidden');
    
    // Enable submit button
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = false;
    submitBtn.classList.remove('disabled:bg-gray-300');
}

// Prevent form submission if no zone selected
document.getElementById('locationForm').addEventListener('submit', function(e) {
    if (!selectedZoneId) {
        e.preventDefault();
        alert('Please select a location first!');
    }
});

// Search functionality
const searchInput = document.getElementById('searchInput');
const clearSearchBtn = document.getElementById('clearSearch');
const searchResults = document.getElementById('searchResults');
const zonesGrid = document.getElementById('zonesGrid');
const noResults = document.getElementById('noResults');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const zoneCards = document.querySelectorAll('.zone-card');
        let visibleCount = 0;
        
        // Show/hide clear button
        if (searchTerm) {
            clearSearchBtn.classList.remove('hidden');
        } else {
            clearSearchBtn.classList.add('hidden');
        }
        
        // Filter zones
        zoneCards.forEach(card => {
            const zoneName = card.dataset.zoneName;
            const zoneCode = card.dataset.zoneCode;
            
            if (zoneName.includes(searchTerm) || zoneCode.includes(searchTerm)) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });
        
        // Update search results text
        if (searchTerm) {
            if (visibleCount === 0) {
                searchResults.textContent = 'No locations found';
                zonesGrid.classList.add('hidden');
                noResults.classList.remove('hidden');
            } else {
                searchResults.textContent = `Showing ${visibleCount} location${visibleCount !== 1 ? 's' : ''}`;
                zonesGrid.classList.remove('hidden');
                noResults.classList.add('hidden');
            }
        } else {
            searchResults.textContent = '';
            zonesGrid.classList.remove('hidden');
            noResults.classList.add('hidden');
        }
    });
    
    // Clear search
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    });
    
    // Focus search on '/' key
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && document.activeElement !== searchInput) {
            e.preventDefault();
            searchInput.focus();
        }
        
        // Clear search on Escape
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.blur();
        }
    });
}
</script>

<style>
.zone-card {
    position: relative;
}

.zone-card.selected {
    transform: scale(1.02);
}

.zone-card.hidden {
    display: none;
}
</style>

<?= $this->include('templates/footer') ?>