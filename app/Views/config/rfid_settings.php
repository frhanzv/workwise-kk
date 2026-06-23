<?= $this->include('templates/header') ?>

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config') ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-gray-900 dark:text-white text-xl font-bold leading-tight">RFID Settings</h1>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="mb-4 p-3 bg-green-100 dark:bg-green-900/20 border border-green-500 text-green-700 dark:text-green-400 rounded-lg text-sm">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-4 p-3 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg text-sm">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Time Interval Settings</h2>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Configure minimum time intervals between RFID tap events to prevent duplicate entries.</p>

    <form action="<?= base_url('config/rfid-settings/update') ?>" method="POST">
        <div class="space-y-6">
            <!-- Check-In to Check-Out Interval -->
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-start gap-3 mb-3">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-2xl">login</span>
                    <div class="flex-1">
                        <label for="checkInToCheckOutInterval" class="block text-sm font-semibold text-gray-900 dark:text-white mb-1">
                            First Tap Interval (Check-In → Check-Out)
                        </label>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                            Minimum seconds required between check-in and check-out. Prevents workers from checking out immediately after checking in.
                        </p>
                        <div class="flex items-center gap-3">
                            <input type="number" 
                                   id="checkInToCheckOutInterval" 
                                   name="checkInToCheckOutInterval" 
                                   value="<?= $config->checkInToCheckOutInterval ?>" 
                                   min="1" 
                                   max="3600"
                                   required
                                   class="w-32 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                            <span class="text-sm text-gray-600 dark:text-gray-400">seconds</span>
                            <span class="text-xs text-gray-500 dark:text-gray-500">(≈ <?= floor($config->checkInToCheckOutInterval / 60) ?> minute<?= $config->checkInToCheckOutInterval >= 120 ? 's' : '' ?>)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-Out to Check-In Interval -->
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-start gap-3 mb-3">
                    <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-2xl">logout</span>
                    <div class="flex-1">
                        <label for="checkOutToCheckInInterval" class="block text-sm font-semibold text-gray-900 dark:text-white mb-1">
                            Second Tap Interval (Check-Out → Check-In Same Zone)
                        </label>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                            Minimum seconds required after check-out before checking in again at the same zone. Prevents rapid re-entry.
                        </p>
                        <div class="flex items-center gap-3">
                            <input type="number" 
                                   id="checkOutToCheckInInterval" 
                                   name="checkOutToCheckInInterval" 
                                   value="<?= $config->checkOutToCheckInInterval ?>" 
                                   min="1" 
                                   max="3600"
                                   required
                                   class="w-32 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                            <span class="text-sm text-gray-600 dark:text-gray-400">seconds</span>
                            <span class="text-xs text-gray-500 dark:text-gray-500">(≈ <?= floor($config->checkOutToCheckInInterval / 60) ?> minute<?= $config->checkOutToCheckInInterval >= 120 ? 's' : '' ?>)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-xl">info</span>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-1">How It Works</h3>
                        <ul class="text-xs text-blue-800 dark:text-blue-400 space-y-1 list-disc list-inside">
                            <li><strong>First Tap:</strong> Worker checks in at a zone</li>
                            <li><strong>Second Tap:</strong> Worker must wait the configured interval before checking out</li>
                            <li><strong>Third Tap:</strong> After checking out, worker must wait the configured interval before checking in again at the same zone</li>
                            <li><strong>Note:</strong> These intervals help prevent accidental duplicate taps and ensure accurate time tracking</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= base_url('config') ?>" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                Cancel
            </a>
            <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
                <span class="material-symbols-outlined text-lg">save</span>
                Save Settings
            </button>
        </div>
    </form>
</div>

<script>
// Real-time minutes calculation
document.getElementById('checkInToCheckOutInterval').addEventListener('input', function(e) {
    const seconds = parseInt(e.target.value) || 0;
    const minutes = Math.floor(seconds / 60);
    const minuteText = minutes >= 2 ? 'minutes' : 'minute';
    e.target.parentElement.querySelector('span:last-child').textContent = `(≈ ${minutes} ${minuteText})`;
});

document.getElementById('checkOutToCheckInInterval').addEventListener('input', function(e) {
    const seconds = parseInt(e.target.value) || 0;
    const minutes = Math.floor(seconds / 60);
    const minuteText = minutes >= 2 ? 'minutes' : 'minute';
    e.target.parentElement.querySelector('span:last-child').textContent = `(≈ ${minutes} ${minuteText})`;
});
</script>

<?= $this->include('templates/footer') ?>
