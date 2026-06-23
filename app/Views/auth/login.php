<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login - Workwise</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Montserrat", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="flex h-screen w-full items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md">
            <!-- Logo/Header -->
            <div class="text-center mb-4 sm:mb-6">
                <div class="mx-auto h-12 w-12 sm:h-16 sm:w-16 rounded-full bg-primary flex items-center justify-center mb-3 sm:mb-4 shadow-lg">
                    <span class="material-symbols-outlined text-3xl sm:text-4xl text-white">lock</span>
                </div>
                <h1 class="text-gray-900 dark:text-white text-2xl sm:text-3xl font-black tracking-tight mb-1">
                    Workwise
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-[10px] sm:text-xs font-medium">
                    Worker Safety & Zone Management System
                </p>
            </div>

            <!-- Login Form -->
            <div class="bg-white dark:bg-[#1a2332] rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mb-4 sm:mb-6 rounded-lg bg-red-50 dark:bg-red-900/20 p-3 sm:p-4 border border-red-200 dark:border-red-800" role="alert">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <span class="material-symbols-outlined text-base sm:text-lg text-red-600 dark:text-red-400">error</span>
                            <span class="text-xs sm:text-sm font-medium text-red-800 dark:text-red-300"><?= session()->getFlashdata('error') ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form class="space-y-3 sm:space-y-4" action="<?= base_url('auth/authenticate') ?>" method="POST" autocomplete="on">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label for="username" class="block text-[10px] sm:text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 sm:mb-1.5 uppercase tracking-wide">
                            Username or Email
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base sm:text-lg">person</span>
                            <input 
                                id="username" 
                                name="username" 
                                type="text" 
                                autocomplete="username" 
                                required 
                                class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-background-dark pl-9 sm:pl-10 pr-3 sm:pr-4 py-2 sm:py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"
                                placeholder="Enter your username or email"
                                value=""
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-[10px] sm:text-xs font-bold text-gray-700 dark:text-gray-300 mb-1 sm:mb-1.5 uppercase tracking-wide">
                            Password
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-base sm:text-lg">lock</span>
                            <input 
                                id="password" 
                                name="password" 
                                type="password" 
                                autocomplete="current-password" 
                                required 
                                class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-background-dark pl-9 sm:pl-10 pr-9 sm:pr-10 py-2 sm:py-2.5 text-xs sm:text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"
                                placeholder="Enter your password"
                                value=""
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword()" 
                                class="absolute right-2.5 sm:right-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                tabindex="-1"
                            >
                                <span id="eyeIcon" class="material-symbols-outlined text-base sm:text-lg">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input 
                            id="remember_me" 
                            name="remember_me" 
                            type="checkbox" 
                            value="1"
                            class="h-3.5 w-3.5 rounded border-gray-300 dark:border-gray-600 text-primary focus:ring-primary focus:ring-offset-0 dark:bg-background-dark"
                        >
                        <label for="remember_me" class="ml-2 block text-xs font-medium text-gray-600 dark:text-gray-400">
                            Remember me
                        </label>
                    </div>

                    <button 
                        type="submit" 
                        class="flex w-full justify-center items-center gap-2 rounded-lg bg-primary px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-white shadow-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-background-dark transition-all tracking-wide"
                    >
                        <span class="material-symbols-outlined text-base sm:text-lg">login</span>
                        SIGN IN
                    </button>
                </form>

                <!-- Default Credentials Info -->
                <div class="mt-3 sm:mt-4 rounded-lg bg-primary/5 dark:bg-primary/10 p-2.5 sm:p-3 border border-primary/20">
                    <div class="flex items-start gap-1.5 sm:gap-2">
                        <span class="material-symbols-outlined text-primary text-sm sm:text-base mt-0.5">info</span>
                        <div class="flex-1">
                            <p class="text-[10px] sm:text-xs font-bold text-gray-700 dark:text-gray-300 mb-0.5 sm:mb-1">Default Credentials</p>
                            <p class="text-[10px] sm:text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                Username: <code class="bg-white dark:bg-background-dark px-1 sm:px-1.5 py-0.5 rounded font-mono text-primary text-[9px] sm:text-[11px]">Admin</code><br>
                                Password: <code class="bg-white dark:bg-background-dark px-1 sm:px-1.5 py-0.5 rounded font-mono text-primary text-[9px] sm:text-[11px]">Admin101</code>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-[9px] sm:text-[10px] text-gray-500 dark:text-gray-500 mt-3 sm:mt-4 font-medium">
                &copy; 2025 Bytespace. All rights reserved.
            </p>
        </div>
    </div>

    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.textContent = 'visibility_off';
        } else {
            passwordInput.type = 'password';
            eyeIcon.textContent = 'visibility';
        }
    }
    </script>
</body>
</html>
