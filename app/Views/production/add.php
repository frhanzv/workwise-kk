<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-4 mt-6 md:mt-4">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
                <a href="<?= base_url('production/list') ?>" class="hover:text-primary">Production Batches</a>
                <span class="material-symbols-outlined text-sm">chevron_right</span>
                <span>New Batch</span>
            </div>
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">New Production Batch</h1>
            <p class="text-gray-500 dark:text-gray-400 text-base">Create a batch to link raw materials to finished products.</p>
        </div>
    </div>

    <form action="<?= base_url('production/store') ?>" method="post" class="max-w-2xl">
        <?= csrf_field() ?>

        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6 flex flex-col gap-5">
            <h2 class="text-gray-900 dark:text-white font-semibold text-base border-b border-gray-200 dark:border-gray-700 pb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">precision_manufacturing</span>
                Batch Information
            </h2>

            <!-- Batch No (readonly) -->
            <div class="flex flex-col gap-1.5">
                <label class="text-gray-700 dark:text-gray-300 text-sm font-medium">Batch Number</label>
                <input type="text" value="<?= esc($batch_no) ?>" readonly
                       class="h-11 px-4 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 text-sm rounded-lg cursor-not-allowed">
                <p class="text-xs text-gray-400">Auto-generated — cannot be changed.</p>
            </div>

            <!-- Notes -->
            <div class="flex flex-col gap-1.5">
                <label for="notes" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Notes <span class="text-gray-400">(optional)</span></label>
                <textarea id="notes" name="notes" rows="4"
                          placeholder="Describe what is being produced in this batch, e.g. 'Batch of 50 units of Widget A from Lot #3'..."
                          class="px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary placeholder-gray-400 resize-none"></textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="flex items-center gap-2 h-10 px-5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors">
                    <span class="material-symbols-outlined text-base">add_circle</span>
                    Create Batch
                </button>
                <a href="<?= base_url('production/list') ?>"
                   class="flex items-center gap-2 h-10 px-5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<?= $this->include('templates/footer') ?>
