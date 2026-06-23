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
let isDragging = false;
let dragOffsetX = 0;
let dragOffsetY = 0;

function openAnalyticsChat() {
    const chatWidget = document.getElementById('analytics-chat-widget');
    const floatingBtn = document.getElementById('analytics-floating-btn');
    
    chatWidget.classList.remove('hidden');
    floatingBtn.classList.add('hidden');
    
    // Position at bottom-right initially
    chatWidget.style.bottom = '24px';
    chatWidget.style.right = '24px';
    chatWidget.style.left = 'auto';
    chatWidget.style.top = 'auto';
    
    // Load the chat interface via iframe
    const iframe = document.getElementById('analytics-chat-iframe');
    if (!iframe.src) {
        iframe.src = '<?= base_url('analytics/chat') ?>';
    }
    
    // Initialize drag functionality
    initDragElement();
}

// ============================================================
// DRAG FUNCTIONALITY
// ============================================================

function initDragElement() {
    const dragHandle = document.getElementById('analytics-drag-handle');
    const chatWidget = document.getElementById('analytics-chat-widget');
    
    if (!dragHandle || !chatWidget) return;
    
    dragHandle.addEventListener('mousedown', dragStart);
    dragHandle.addEventListener('touchstart', dragStart);
    
    document.addEventListener('mousemove', drag);
    document.addEventListener('touchmove', drag);
    
    document.addEventListener('mouseup', dragEnd);
    document.addEventListener('touchend', dragEnd);
}

function dragStart(e) {
    if (isChatFullscreen) return; // Don't drag in fullscreen mode
    
    const chatWidget = document.getElementById('analytics-chat-widget');
    const rect = chatWidget.getBoundingClientRect();
    
    if (e.target.closest('#analytics-drag-handle')) {
        isDragging = true;
        
        // Calculate offset between mouse/touch and widget top-left corner
        if (e.type === "touchstart") {
            dragOffsetX = e.touches[0].clientX - rect.left;
            dragOffsetY = e.touches[0].clientY - rect.top;
        } else {
            dragOffsetX = e.clientX - rect.left;
            dragOffsetY = e.clientY - rect.top;
        }
        
        chatWidget.style.transition = 'none';
        
        // Remove bottom/right positioning, use top/left instead
        chatWidget.style.bottom = 'auto';
        chatWidget.style.right = 'auto';
        chatWidget.style.left = rect.left + 'px';
        chatWidget.style.top = rect.top + 'px';
    }
}

function drag(e) {
    if (!isDragging) return;
    
    e.preventDefault();
    
    const chatWidget = document.getElementById('analytics-chat-widget');
    const rect = chatWidget.getBoundingClientRect();
    
    let clientX, clientY;
    
    if (e.type === "touchmove") {
        clientX = e.touches[0].clientX;
        clientY = e.touches[0].clientY;
    } else {
        clientX = e.clientX;
        clientY = e.clientY;
    }
    
    // Calculate new position (mouse position - offset)
    let newLeft = clientX - dragOffsetX;
    let newTop = clientY - dragOffsetY;
    
    // Constrain to viewport boundaries
    const maxLeft = window.innerWidth - rect.width;
    const maxTop = window.innerHeight - rect.height;
    
    newLeft = Math.max(0, Math.min(newLeft, maxLeft));
    newTop = Math.max(0, Math.min(newTop, maxTop));
    
    // Apply new position
    chatWidget.style.left = newLeft + 'px';
    chatWidget.style.top = newTop + 'px';
}

function dragEnd(e) {
    if (isDragging) {
        const chatWidget = document.getElementById('analytics-chat-widget');
        chatWidget.style.transition = '';
        isDragging = false;
    }
}

function minimizeAnalyticsChat() {
    const chatWidget = document.getElementById('analytics-chat-widget');
    const chatBody = document.getElementById('analytics-chat-body');
    const minimizeBtn = document.getElementById('analytics-minimize-btn');
    const maximizeBtn = document.getElementById('analytics-maximize-btn');
    
    if (!isChatMinimized) {
        // Minimize
        chatBody.classList.add('hidden');
        chatWidget.classList.remove('h-[500px]', 'md:h-[600px]');
        chatWidget.classList.add('h-auto');
        minimizeBtn.classList.add('hidden');
        maximizeBtn.classList.remove('hidden');
        isChatMinimized = true;
    } else {
        // Restore
        chatBody.classList.remove('hidden');
        chatWidget.classList.remove('h-auto');
        chatWidget.classList.add('h-[500px]', 'md:h-[600px]');
        minimizeBtn.classList.remove('hidden');
        maximizeBtn.classList.add('hidden');
        isChatMinimized = false;
    }
}

function toggleAnalyticsFullscreen() {
    const chatWidget = document.getElementById('analytics-chat-widget');
    const fullscreenBtn = document.getElementById('analytics-fullscreen-btn');
    const normalBtn = document.getElementById('analytics-normal-btn');
    
    if (!isChatFullscreen) {
        // Go fullscreen
        chatWidget.classList.remove('w-[350px]', 'md:w-[400px]', 'h-[500px]', 'md:h-[600px]', 'rounded-2xl');
        chatWidget.classList.add('w-full', 'h-full', 'rounded-none');
        chatWidget.style.bottom = '0';
        chatWidget.style.right = '0';
        chatWidget.style.left = '0';
        chatWidget.style.top = '0';
        fullscreenBtn.classList.add('hidden');
        normalBtn.classList.remove('hidden');
        isChatFullscreen = true;
    } else {
        // Go normal size
        chatWidget.classList.remove('w-full', 'h-full', 'rounded-none');
        chatWidget.classList.add('w-[350px]', 'md:w-[400px]', 'h-[500px]', 'md:h-[600px]', 'rounded-2xl');
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
    
    chatWidget.classList.add('hidden');
    floatingBtn.classList.remove('hidden');
    
    // Reset states
    isChatMinimized = false;
    isChatFullscreen = false;
    
    // Reset position to bottom-right
    chatWidget.style.bottom = '24px';
    chatWidget.style.right = '24px';
    chatWidget.style.left = 'auto';
    chatWidget.style.top = 'auto';
}
</script>

<!-- Analytics Chat Floating Button -->
<?php if (session()->get('isLoggedIn')): ?>
<!-- Floating Button (Like video call button) -->
<div id="analytics-floating-btn" class="fixed bottom-6 right-6 z-50">
    <button 
        onclick="openAnalyticsChat()"
        class="flex items-center gap-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-4 rounded-full shadow-2xl hover:shadow-blue-500/50 transition-all duration-300 transform hover:scale-105">
        <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <span class="font-semibold hidden sm:inline">Analytics Assistant</span>
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
        </span>
    </button>
</div>

<!-- Floating Chat Widget (Like video chat window) -->
<div 
    id="analytics-chat-widget" 
    class="hidden fixed w-[350px] md:w-[400px] h-[500px] md:h-[600px] bg-gray-900 shadow-2xl z-[60] rounded-2xl overflow-hidden flex flex-col"
    style="bottom: 24px; right: 24px;">
    
    <!-- Chat Header (Draggable) -->
    <div 
        id="analytics-drag-handle"
        class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2.5 flex items-center justify-between border-b border-blue-500 cursor-move select-none">
        <div class="flex items-center gap-2 pointer-events-none">
            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-white font-bold text-xs">Analytics Assistant</h3>
                <p class="text-blue-100 text-[10px]">🔒 Drag to move</p>
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