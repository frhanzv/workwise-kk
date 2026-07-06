<!-- Footer -->
<footer class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-background-dark px-4 py-2 lg:py-3 mt-6">
    <p class="text-center text-[10px] sm:text-xs text-gray-500 dark:text-gray-500 font-medium">
        &copy; <?= date('Y') ?> Bytespace. All rights reserved.
    </p>
</footer>
</main>
</div>
</div>

<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script>
// Mobile menu toggle
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobile-overlay');
    const mobileHeader = document.querySelector('.lg\\:hidden.fixed.top-0');
    const isOpen = !sidebar.classList.contains('-translate-x-full');
    
    if (isOpen) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        if (mobileHeader) mobileHeader.classList.remove('hidden');
        document.body.classList.remove('overflow-hidden');
    } else {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        if (mobileHeader) mobileHeader.classList.add('hidden');
        document.body.classList.add('overflow-hidden');
    }
}

// Close mobile menu when screen resizes to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth >= 1024) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        const mobileHeader = document.querySelector('.lg\\:hidden.fixed.top-0');
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.add('hidden');
        if (mobileHeader) mobileHeader.classList.remove('hidden');
        document.body.classList.remove('overflow-hidden');
    }
});

// ============================================================
// FLOATING ANALYTICS CHAT CONTROLS (VIDEO CHAT STYLE)
// ============================================================

let isChatMinimized = false;
let isChatFullscreen = false;

function openAnalyticsChat() {
    const chatWidget = document.getElementById('analytics-chat-widget');
    const floatingBtn = document.getElementById('analytics-floating-btn');
    
    chatWidget.classList.remove('hidden');
    floatingBtn.classList.add('hidden');
    
    if (window.WWFloatingWidgets) {
        WWFloatingWidgets.applySavedPosition(chatWidget);
    } else {
        chatWidget.style.bottom = '24px';
        chatWidget.style.right = '24px';
        chatWidget.style.left = 'auto';
        chatWidget.style.top = 'auto';
    }
    
    const iframe = document.getElementById('analytics-chat-iframe');
    if (!iframe.src) {
        iframe.src = '<?= base_url('analytics/chat') ?>';
    }
}

function minimizeAnalyticsChat() {
    const chatWidget = document.getElementById('analytics-chat-widget');
    const chatBody = document.getElementById('analytics-chat-body');
    const minimizeBtn = document.getElementById('analytics-minimize-btn');
    const maximizeBtn = document.getElementById('analytics-maximize-btn');
    const panelHeight = chatWidget.dataset.panelHeight || '600px';
    
    if (!isChatMinimized) {
        chatBody.classList.add('hidden');
        chatWidget.style.height = 'auto';
        minimizeBtn.classList.add('hidden');
        maximizeBtn.classList.remove('hidden');
        isChatMinimized = true;
    } else {
        chatBody.classList.remove('hidden');
        chatWidget.style.height = panelHeight;
        minimizeBtn.classList.remove('hidden');
        maximizeBtn.classList.add('hidden');
        isChatMinimized = false;
    }
}

function toggleAnalyticsFullscreen() {
    const chatWidget = document.getElementById('analytics-chat-widget');
    const fullscreenBtn = document.getElementById('analytics-fullscreen-btn');
    const normalBtn = document.getElementById('analytics-normal-btn');
    const panelWidth = chatWidget.dataset.panelWidth || '400px';
    const panelHeight = chatWidget.dataset.panelHeight || '600px';
    
    if (!isChatFullscreen) {
        chatWidget.style.width = '100%';
        chatWidget.style.height = '100%';
        chatWidget.classList.remove('rounded-2xl');
        chatWidget.classList.add('rounded-none');
        chatWidget.style.bottom = '0';
        chatWidget.style.right = '0';
        chatWidget.style.left = '0';
        chatWidget.style.top = '0';
        fullscreenBtn.classList.add('hidden');
        normalBtn.classList.remove('hidden');
        isChatFullscreen = true;
    } else {
        chatWidget.style.width = panelWidth;
        chatWidget.style.height = panelHeight;
        chatWidget.classList.remove('rounded-none');
        chatWidget.classList.add('rounded-2xl');
        chatWidget.style.bottom = '24px';
        chatWidget.style.right = '24px';
        chatWidget.style.left = 'auto';
        chatWidget.style.top = 'auto';
        fullscreenBtn.classList.remove('hidden');
        normalBtn.classList.add('hidden');
        isChatFullscreen = false;
    }
}

function closeAnalyticsChat() {
    const chatWidget = document.getElementById('analytics-chat-widget');
    const floatingBtn = document.getElementById('analytics-floating-btn');
    const panelWidth = chatWidget.dataset.panelWidth || '400px';
    const panelHeight = chatWidget.dataset.panelHeight || '600px';
    
    chatWidget.classList.add('hidden');
    floatingBtn.classList.remove('hidden');
    
    isChatMinimized = false;
    isChatFullscreen = false;
    
    chatWidget.style.width = panelWidth;
    chatWidget.style.height = panelHeight;
    chatWidget.classList.remove('rounded-none');
    chatWidget.classList.add('rounded-2xl');
    chatWidget.style.bottom = '24px';
    chatWidget.style.right = '24px';
    chatWidget.style.left = 'auto';
    chatWidget.style.top = 'auto';
}

document.addEventListener('DOMContentLoaded', function () {
    if (window.WWFloatingWidgets) {
        WWFloatingWidgets.initPanelDrag('analytics-chat-widget', '#analytics-drag-handle', {
            isBlocked: function () { return isChatFullscreen; }
        });
    }
});
</script>

<!-- Analytics Chat Floating Button -->
<?php if (session()->get('isLoggedIn')): ?>
<?php
$widgetConfig = config('Widgets');
$btn = $widgetConfig->floatingButtonClasses();
$panel = $widgetConfig->panelDimensions();
?>
<script>
window.WW_WIDGET_CONFIG = <?= json_encode([
    'floatingButtonsMoveable' => $widgetConfig->floatingButtonsMoveable,
    'panelsMoveable' => $widgetConfig->panelsMoveable,
    'floatingButtonSize' => $widgetConfig->floatingButtonSize,
    'panelSize' => $widgetConfig->panelSize,
    'panelDimensions' => $panel,
], JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= base_url('assets/js/floating-widgets.js') ?>"></script>
<?= view('inventory/_finder_widget') ?>
<!-- Floating Button (Like video call button) -->
<div id="analytics-floating-btn" class="fixed bottom-6 right-6 z-50" data-default-bottom="24" data-default-right="24">
    <button 
        onclick="openAnalyticsChat()"
        class="flex items-center <?= esc($btn['btn']) ?> bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-full shadow-2xl hover:shadow-blue-500/50 transition-all duration-300 transform hover:scale-105">
        <svg class="<?= esc($btn['svg']) ?> animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <span class="font-semibold hidden sm:inline <?= esc($btn['text']) ?>">Analytics Assistant</span>
        <span class="relative flex <?= esc($btn['dot']) ?>">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
            <span class="relative inline-flex rounded-full h-full w-full bg-white"></span>
        </span>
    </button>
</div>

<!-- Floating Chat Widget (Like video chat window) -->
<div 
    id="analytics-chat-widget" 
    class="hidden fixed bg-gray-900 shadow-2xl z-[60] rounded-2xl overflow-hidden flex flex-col"
    style="bottom: 24px; right: 24px; width: <?= esc($panel['analyticsWidth']) ?>; height: <?= esc($panel['analyticsHeight']) ?>;"
    data-panel-width="<?= esc($panel['analyticsWidth']) ?>"
    data-panel-height="<?= esc($panel['analyticsHeight']) ?>">
    
    <!-- Chat Header (Draggable) -->
    <div 
        id="analytics-drag-handle"
        class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2.5 flex items-center justify-between border-b border-blue-500 select-none">
        <div class="flex items-center gap-2 pointer-events-none">
            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-white font-bold text-xs">Analytics Assistant</h3>
                <p class="text-blue-100 text-[10px]"><?= $widgetConfig->panelsMoveable ? 'Drag to move' : 'AI chat' ?></p>
            </div>
        </div>
        <div class="flex items-center gap-1 pointer-events-auto">
            <!-- Minimize Button -->
            <button 
                id="analytics-minimize-btn"
                onclick="minimizeAnalyticsChat()"
                class="p-1.5 hover:bg-white/10 rounded-lg transition-colors"
                title="Minimize">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </button>
            <!-- Maximize Button (Hidden by default) -->
            <button 
                id="analytics-maximize-btn"
                onclick="minimizeAnalyticsChat()"
                class="hidden p-1.5 hover:bg-white/10 rounded-lg transition-colors"
                title="Restore">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
            </button>
            <!-- Fullscreen Toggle -->
            <button 
                id="analytics-fullscreen-btn"
                onclick="toggleAnalyticsFullscreen()"
                class="p-1.5 hover:bg-white/10 rounded-lg transition-colors"
                title="Fullscreen">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                </svg>
            </button>
            <button 
                id="analytics-normal-btn"
                onclick="toggleAnalyticsFullscreen()"
                class="hidden p-1.5 hover:bg-white/10 rounded-lg transition-colors"
                title="Exit fullscreen">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"></path>
                </svg>
            </button>
            <!-- Close Button -->
            <button 
                onclick="closeAnalyticsChat()"
                class="p-1.5 hover:bg-white/10 rounded-lg transition-colors"
                title="Close">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Chat Body -->
    <div id="analytics-chat-body" class="flex-1 overflow-hidden">
        <iframe 
            id="analytics-chat-iframe"
            class="w-full h-full border-0"
            title="Analytics Chat Interface">
        </iframe>
    </div>
</div>
<?php endif; ?>

</body>
</html>