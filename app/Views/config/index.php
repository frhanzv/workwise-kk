<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-1 mt-6 md:mt-4">
        <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Configuration</h1>
        <p class="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal">Manage system configurations and settings.</p>
    </div>

    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
    <!-- Antenna Mode List -->
    <a href="<?= base_url('config/antenna-mode') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-purple-500/10 dark:bg-purple-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-purple-500 text-2xl">sensors</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Antenna Mode List</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage antenna modes for zone configuration</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Department List -->
    <a href="<?= base_url('config/departments') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-blue-500/10 dark:bg-blue-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-blue-500 text-2xl">corporate_fare</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Department List</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage departments for worker organization</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Job Position List -->
    <a href="<?= base_url('config/job-positions') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-orange-500/10 dark:bg-orange-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-orange-500 text-2xl">badge</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Job Position List</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage job titles and positions for workers</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Country Management -->
    <a href="<?= base_url('config/countries') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-emerald-500/10 dark:bg-emerald-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-emerald-500 text-2xl">public</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Country List</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage countries and regions worldwide</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- State Management -->
    <a href="<?= base_url('config/states') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-indigo-500/10 dark:bg-indigo-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-indigo-500 text-2xl">map</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">State List</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage states and provinces by country</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- City Management -->
    <a href="<?= base_url('config/cities') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-teal-500/10 dark:bg-teal-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-teal-500 text-2xl">location_city</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">City List</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage cities and towns by state</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Operating Hours -->
    <a href="<?= base_url('config/operating-hours') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-amber-500/10 dark:bg-amber-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-amber-500 text-2xl">schedule</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Operating Hours</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage daily operating hours and schedules</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Shift Management -->
    <a href="<?= base_url('config/shifts') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-pink-500/10 dark:bg-pink-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-pink-500 text-2xl">work_history</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Shift List</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage work shifts and time schedules</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Staff Groups -->
    <a href="<?= base_url('config/staff-groups') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-cyan-500/10 dark:bg-cyan-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-cyan-500 text-2xl">groups</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Staff Groups</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage staff groups and team organization</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Groups Shift -->
    <a href="<?= base_url('config/groups-shift') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-indigo-500/10 dark:bg-indigo-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-indigo-500 text-2xl">schedule</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Groups Shift</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Configure work schedule rules and shift groups</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Staff Availability -->
    <a href="<?= base_url('config/staff-availability') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-teal-500/10 dark:bg-teal-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-teal-500 text-2xl">how_to_reg</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Staff Availability</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage staff availability status and types</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Staff Shift Allocation -->
    <a href="<?= base_url('config/staff-shift-allocation') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-amber-500/10 dark:bg-amber-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-amber-500 text-2xl">calendar_month</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Staff Shift Allocation</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Configure rotational shift schedules by group</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Public Holidays -->
    <a href="<?= base_url('config/public-holidays') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-rose-500/10 dark:bg-rose-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-rose-500 text-2xl">celebration</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Public Holidays</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage federal and state public holidays</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Leave Reason List -->
    <a href="<?= base_url('config/leave-reasons') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-yellow-500/10 dark:bg-yellow-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-yellow-500 text-2xl">event_busy</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Leave Reason List</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage leave reasons and types</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Role List -->
    <a href="<?= base_url('config/roles') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-violet-500/10 dark:bg-violet-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-violet-500 text-2xl">admin_panel_settings</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Role List</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage user roles and permissions</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- Units of Measure -->
    <a href="<?= base_url('config/units-of-measure') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-cyan-500/10 dark:bg-cyan-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-cyan-500 text-2xl">straighten</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Units of Measure</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage units for products and raw materials (pcs, kg, liter, etc.)</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>

    <!-- Suppliers -->
    <a href="<?= base_url('config/suppliers') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-amber-500/10 dark:bg-amber-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-amber-500 text-2xl">local_shipping</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">Suppliers</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Manage supplier list for product master forms</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>

    <!-- RFID Settings -->
    <a href="<?= base_url('config/rfid-settings') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-red-500/10 dark:bg-red-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-red-500 text-2xl">settings</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">RFID Settings</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Configure time intervals for antenna scanning</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
    
    <!-- System Logs -->
    <a href="<?= base_url('config/system-logs') ?>" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary transition-colors">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-slate-500/10 dark:bg-slate-500/20 p-2 rounded-lg">
                <span class="material-symbols-outlined text-slate-500 text-2xl">description</span>
            </div>
            <h2 class="text-gray-900 dark:text-white text-base font-semibold">System Logs</h2>
        </div>
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">View and monitor system logs by severity level</p>
        <div class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors text-center">
            Configure
        </div>
    </a>
</div>

<?= $this->include('templates/footer') ?>
