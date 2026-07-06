<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6">
    <div class="flex items-center gap-4 mt-6 md:mt-4">
        <a href="<?= base_url('raw-materials/list') ?>" class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <span class="material-symbols-outlined text-2xl">arrow_back</span>
        </a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Add Raw Material</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Add a raw material to the master list and assign UHF RFID tags if needed.</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="p-4 bg-red-100 dark:bg-red-900/20 border border-red-500 text-red-700 dark:text-red-400 rounded-lg">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <form action="<?= base_url('raw-materials/store') ?>" method="post" class="p-6 space-y-8">
            <?= csrf_field() ?>
            <?= view('raw_materials/_form', ['record' => null, 'material_code' => $material_code, 'zones' => $zones]) ?>
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="<?= base_url('raw-materials/list') ?>" class="h-10 px-5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center">Cancel</a>
                <button type="submit" class="h-10 px-5 rounded-lg bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">Save Material</button>
            </div>
        </form>
    </div>
</div>

<?= $this->include('templates/footer') ?>
