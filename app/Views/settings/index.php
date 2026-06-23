<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between gap-2 mb-2 mt-6 md:mt-4">
        <div class="flex flex-col">
            <p class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Settings</p>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-normal leading-normal">Manage your account settings and preferences</p>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800" role="alert">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-green-600 dark:text-green-400">check_circle</span>
                <span class="text-sm font-medium text-green-800 dark:text-green-300"><?= session()->getFlashdata('success') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800" role="alert">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400">error</span>
                <span class="text-sm font-medium text-red-800 dark:text-red-300"><?= session()->getFlashdata('error') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Profile Photo Card -->
        <div class="bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="material-symbols-outlined text-primary text-2xl">account_circle</span>
                <h2 class="text-gray-900 dark:text-white text-xl font-bold">Profile Photo</h2>
            </div>

            <div class="flex flex-col items-center gap-4">
                <!-- Current Photo Display -->
                <div class="relative">
                    <?php if (!empty($userData['profile_photo'])): ?>
                        <img src="<?= base_url('uploads/profiles/' . $userData['profile_photo']) ?>" 
                             alt="Profile Photo" 
                             class="w-32 h-32 rounded-full object-cover border-4 border-primary shadow-lg">
                    <?php else: ?>
                        <div class="w-32 h-32 rounded-full bg-primary flex items-center justify-center border-4 border-primary/20 shadow-lg">
                            <span class="text-white font-bold text-4xl">
                                <?= esc($user['initials']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Upload Photo Form -->
                <form action="<?= base_url('settings/upload-photo') ?>" method="POST" enctype="multipart/form-data" class="w-full" id="photoForm">
                    <?= csrf_field() ?>
                    <div class="flex flex-col gap-3">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-background-dark hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <span class="material-symbols-outlined text-gray-400 text-4xl mb-2">cloud_upload</span>
                                <p class="mb-1 text-sm text-gray-500 dark:text-gray-400 font-medium">
                                    <span class="font-semibold">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG or GIF (MAX. 2MB)</p>
                                <p id="fileName" class="mt-2 text-xs text-primary font-semibold hidden"></p>
                            </div>
                            <input id="profile_photo" name="profile_photo" type="file" class="hidden" accept="image/*" onchange="showFileName(this)" />
                        </label>
                        
                        <button 
                            type="submit" 
                            id="uploadBtn"
                            class="hidden items-center justify-center gap-2 w-full px-4 py-2.5 text-sm font-bold text-white bg-primary rounded-lg hover:bg-primary/90 transition-colors shadow-sm"
                        >
                            <span class="material-symbols-outlined text-lg">upload</span>
                            Upload Photo
                        </button>
                        
                        <?php if (!empty($userData['profile_photo'])): ?>
                            <a href="<?= base_url('settings/reset-photo') ?>" 
                               class="flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors border border-red-200 dark:border-red-800"
                               onclick="return confirm('Are you sure you want to reset your profile photo?')">
                                <span class="material-symbols-outlined text-lg">refresh</span>
                                Reset to Default
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Profile Information Card -->
        <div class="bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="material-symbols-outlined text-primary text-2xl">badge</span>
                <h2 class="text-gray-900 dark:text-white text-xl font-bold">Profile Information</h2>
            </div>

            <form action="<?= base_url('settings/update-profile') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                
                <div>
                    <label for="username" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                        Username
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        value="<?= esc($userData['username']) ?>" 
                        disabled 
                        class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400"
                    >
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Username cannot be changed</p>
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        value="<?= esc($userData['email']) ?>" 
                        required
                        class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-background-dark px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                </div>

                <div>
                    <label for="full_name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                        Full Name
                    </label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        value="<?= esc($userData['full_name']) ?>" 
                        required
                        class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-background-dark px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                    >
                </div>

                <button 
                    type="submit" 
                    class="flex items-center justify-center gap-2 w-full px-4 py-2.5 text-sm font-bold text-white bg-primary rounded-lg hover:bg-primary/90 transition-colors shadow-sm"
                >
                    <span class="material-symbols-outlined text-lg">save</span>
                    Update Profile
                </button>
            </form>
        </div>

        <!-- Change Password Card -->
        <div class="bg-white dark:bg-background-dark border border-gray-200 dark:border-gray-700 rounded-lg p-6 lg:col-span-2">
            <div class="flex items-center gap-3 mb-4">
                <span class="material-symbols-outlined text-primary text-2xl">lock_reset</span>
                <h2 class="text-gray-900 dark:text-white text-xl font-bold">Change Password</h2>
            </div>

            <form action="<?= base_url('settings/change-password') ?>" method="POST" class="max-w-2xl">
                <?= csrf_field() ?>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="current_password" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                            Current Password
                        </label>
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            required
                            class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-background-dark px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                            placeholder="Enter current password"
                        >
                    </div>

                    <div>
                        <label for="new_password" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                            New Password
                        </label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            required
                            minlength="6"
                            class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-background-dark px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                            placeholder="Enter new password"
                        >
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                            Confirm Password
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                            minlength="6"
                            class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-background-dark px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                            placeholder="Confirm new password"
                        >
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="flex items-center justify-center gap-2 mt-4 px-6 py-2.5 text-sm font-bold text-white bg-primary rounded-lg hover:bg-primary/90 transition-colors shadow-sm"
                >
                    <span class="material-symbols-outlined text-lg">key</span>
                    Change Password
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function showFileName(input) {
    const fileName = document.getElementById('fileName');
    const uploadBtn = document.getElementById('uploadBtn');
    
    if (input.files && input.files[0]) {
        fileName.textContent = '📷 ' + input.files[0].name;
        fileName.classList.remove('hidden');
        uploadBtn.classList.remove('hidden');
        uploadBtn.classList.add('flex');
    } else {
        fileName.classList.add('hidden');
        uploadBtn.classList.add('hidden');
        uploadBtn.classList.remove('flex');
    }
}
</script>

<?= $this->include('templates/footer') ?>
