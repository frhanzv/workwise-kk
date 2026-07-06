<?= $this->include('templates/header') ?>

<div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('config') ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-gray-900 dark:text-white text-xl font-bold leading-tight">Widget Settings</h1>
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

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 max-w-2xl">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Floating Widgets</h2>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
        Configure the Find Stock and Analytics Assistant floating buttons and their panels.
    </p>

    <form action="<?= base_url('config/widget-settings/update') ?>" method="POST" class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="floatingButtonSize" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                    Floating button size
                </label>
                <select id="floatingButtonSize" name="floatingButtonSize"
                        class="w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">
                    <option value="sm" <?= $config->floatingButtonSize === 'sm' ? 'selected' : '' ?>>Small</option>
                    <option value="md" <?= $config->floatingButtonSize === 'md' ? 'selected' : '' ?>>Medium</option>
                    <option value="lg" <?= $config->floatingButtonSize === 'lg' ? 'selected' : '' ?>>Large</option>
                </select>
            </div>

            <div>
                <label for="panelSize" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                    Panel size
                </label>
                <select id="panelSize" name="panelSize"
                        class="w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">
                    <option value="sm" <?= $config->panelSize === 'sm' ? 'selected' : '' ?>>Small</option>
                    <option value="md" <?= $config->panelSize === 'md' ? 'selected' : '' ?>>Medium</option>
                    <option value="lg" <?= $config->panelSize === 'lg' ? 'selected' : '' ?>>Large</option>
                </select>
            </div>
        </div>

        <div class="space-y-3">
            <label class="flex items-start gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 cursor-pointer">
                <input type="checkbox" name="floatingButtonsMoveable" value="1"
                       <?= $config->floatingButtonsMoveable ? 'checked' : '' ?>
                       class="mt-1 rounded border-gray-300 text-primary focus:ring-primary">
                <span>
                    <span class="block text-sm font-semibold text-gray-900 dark:text-white">Moveable floating buttons</span>
                    <span class="block text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Allow users to drag Find Stock and Analytics Assistant buttons anywhere on screen.
                    </span>
                </span>
            </label>

            <label class="flex items-start gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 cursor-pointer">
                <input type="checkbox" name="panelsMoveable" value="1"
                       <?= $config->panelsMoveable ? 'checked' : '' ?>
                       class="mt-1 rounded border-gray-300 text-primary focus:ring-primary">
                <span>
                    <span class="block text-sm font-semibold text-gray-900 dark:text-white">Moveable panels</span>
                    <span class="block text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Allow dragging open panels by their header bar.
                    </span>
                </span>
            </label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                    class="px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors">
                Save settings
            </button>
            <a href="<?= base_url('config') ?>"
               class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

<?= $this->include('templates/footer') ?>
