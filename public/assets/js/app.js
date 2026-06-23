// Custom JavaScript for Workwise Dashboard

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Workwise Dashboard Loaded');
    
    // You can add interactive features here
    // Example: Real-time updates, search functionality, etc.
});

// Dark mode toggle (if you want to add this feature)
function toggleDarkMode() {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
}

// Toggle Dashboard submenu
function toggleDashboardMenu() {
    const submenu = document.getElementById('dashboard-submenu');
    const arrow = document.getElementById('dashboard-arrow');

    submenu.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}

// Toggle Workers submenu
function toggleWorkersMenu() {
    const submenu = document.getElementById('workers-submenu');
    const arrow = document.getElementById('workers-arrow');
    
    submenu.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}

// Toggle Inventory submenu
function toggleInventoryMenu() {
    const submenu = document.getElementById('inventory-submenu');
    const arrow = document.getElementById('inventory-arrow');
    
    submenu.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}

// Load dark mode preference
if (localStorage.getItem('darkMode') === 'true' || 
    (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
}
