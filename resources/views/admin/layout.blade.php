<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - E-Commerce Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Sidebar Styling */
        .sidebar {
            backdrop-filter: blur(10px);
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 256px;
            will-change: width;
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar-text {
            transition: opacity 0.3s ease, width 0.3s ease;
            white-space: nowrap;
        }

        .sidebar.collapsed .sidebar-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        .sidebar.collapsed .nav-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar.collapsed .nav-item-icon {
            margin-right: 0 !important;
        }

        .sidebar.collapsed .sidebar-header {
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-header > div {
            justify-content: center;
        }

        .sidebar.collapsed .logout-btn {
            padding-left: 0;
            padding-right: 0;
            justify-content: center;
        }

        .sidebar.collapsed .logout-btn i {
            margin-right: 0 !important;
        }

        /* Mobile sidebar slide animation */
        @media (max-width: 767px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }

            main {
                margin-left: 0 !important;
            }
        }

        /* Menu item hover effects */
        .nav-item {
            position: relative;
            overflow: visible;
        }

        .sidebar:not(.collapsed) .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 0;
            background: linear-gradient(180deg, #3b82f6, #8b5cf6);
            transition: height 0.3s ease;
            border-radius: 0 4px 4px 0;
        }

        .sidebar:not(.collapsed) .nav-item:hover::before,
        .sidebar:not(.collapsed) .nav-item.active::before {
            height: 70%;
        }

        .nav-item-icon {
            transition: all 0.3s ease;
        }

        .nav-item:hover .nav-item-icon {
            transform: scale(1.1) rotate(5deg);
            color: #60a5fa;
        }

        .nav-item.active .nav-item-icon {
            color: #3b82f6;
        }

        .nav-item-text {
            transition: transform 0.3s ease;
        }

        .nav-item:hover .nav-item-text {
            transform: translateX(4px);
        }

        /* Logout button ripple effect */
        .logout-btn {
            position: relative;
            overflow: hidden;
        }

        .logout-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .logout-btn:active::after {
            width: 300px;
            height: 300px;
        }

        /* Profile badge animation */
        .profile-badge {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .profile-badge:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }

        /* Sidebar header gradient */
        .sidebar-header {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        }

        /* Scrollbar styling */
        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: #1f2937;
            border-radius: 10px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 10px;
            transition: background 0.3s;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        /* Active nav item pulse animation */
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
            }
            50% {
                box-shadow: 0 0 20px 5px rgba(59, 130, 246, 0.2);
            }
        }

        .nav-item.active {
            animation: pulse-glow 2s infinite;
        }

        /* Smooth height transition for sidebar */
        @media (min-width: 768px) {
            .sidebar {
                height: 100vh;
                position: fixed;
                left: 0;
                top: 0;
            }

            main {
                margin-left: 256px;
            }

            main.sidebar-collapsed {
                margin-left: 80px;
            }
        }

        /* Main content smooth transition */
        main {
            transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: margin-left;
        }

        /* Smooth resize for body */
        body {
            overflow-x: hidden;
        }

        /* Lock content width to prevent expansion/shrinkage */
        @media (min-width: 768px) {
            main > * {
                max-width: 1280px;
                margin-left: auto;
                margin-right: auto;
            }
        }

        /* Disable width/size transitions on all content */
        main *:not(.sidebar):not(.toggle-sidebar-btn) {
            transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform !important;
        }

        /* Keep hover effects smooth */
        main a:hover,
        main button:hover,
        main .group:hover * {
            transition-duration: 0.2s;
        }

        /* Toggle button styles */
        .toggle-sidebar-btn {
            position: fixed;
            top: 20px;
            left: 268px;
            z-index: 35;
            transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: left;
        }

        .toggle-sidebar-btn.collapsed {
            left: 92px;
        }

        /* Icon rotation animation */
        .toggle-sidebar-btn i {
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Tooltip for collapsed sidebar */
        .nav-item {
            position: relative;
        }

        .sidebar.collapsed .nav-item:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: #1f2937;
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            white-space: nowrap;
            margin-left: 10px;
            font-size: 14px;
            z-index: 100;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .sidebar.collapsed .nav-item:hover::before {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color: #1f2937;
            margin-left: 4px;
            z-index: 100;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <div class="min-h-screen flex">
        <!-- Mobile Menu Button -->
        <button id="mobile-menu-btn" class="md:hidden fixed top-4 left-4 z-40 bg-gradient-to-br from-gray-800 to-gray-900 text-white p-3 rounded-lg shadow-lg hover:shadow-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-300 transform hover:scale-110">
            <i class="fas fa-bars text-lg"></i>
        </button>

        <!-- Desktop Toggle Button -->
        <button id="toggle-sidebar-btn" class="toggle-sidebar-btn hidden md:flex items-center justify-center w-10 h-10 bg-gradient-to-br from-gray-800 to-gray-900 text-white rounded-lg shadow-lg hover:shadow-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-300 transform hover:scale-110">
            <i class="fas fa-chevron-left text-sm"></i>
        </button>

        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar fixed h-screen inset-y-0 left-0 w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white z-30 md:z-0 shadow-2xl">
            <div class="flex flex-col h-full">
                <!-- Header -->
                <div class="sidebar-header p-6 border-b border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center shadow-lg">
                                <i class="fas fa-shield-alt text-white text-lg"></i>
                            </div>
                            <div class="sidebar-text">
                            <h1 class="text-xl font-bold text-white">Admin Panel</h1>
                                <p class="text-gray-400 text-xs mt-0.5">E-Commerce Management</p>
                            </div>
                        </div>
                        <button id="close-sidebar" class="md:hidden text-gray-400 hover:text-white transition-all hover:rotate-90 duration-300">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="sidebar-nav flex-1 p-4 space-y-2 overflow-y-auto">
                    <a href="/admin/dashboard" data-tooltip="Dashboard" class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg transition-all duration-300 {{ request()->is('admin/dashboard') ? 'bg-gradient-to-r from-gray-700 to-gray-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white hover:shadow-md' }}">
                        <i class="nav-item-icon fas fa-chart-line w-5 text-center mr-3 text-lg"></i>
                        <span class="sidebar-text nav-item-text font-medium">Dashboard</span>
                    </a>
                    <a href="/admin/users" data-tooltip="User Management" class="nav-item {{ request()->is('admin/users') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg transition-all duration-300 {{ request()->is('admin/users') ? 'bg-gradient-to-r from-gray-700 to-gray-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white hover:shadow-md' }}">
                        <i class="nav-item-icon fas fa-users w-5 text-center mr-3 text-lg"></i>
                        <span class="sidebar-text nav-item-text font-medium">User Management</span>
                    </a>
                    <a href="/admin/banners" data-tooltip="Banners" class="nav-item {{ request()->is('admin/banners') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg transition-all duration-300 {{ request()->is('admin/banners') ? 'bg-gradient-to-r from-gray-700 to-gray-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white hover:shadow-md' }}">
                        <i class="nav-item-icon fas fa-images w-5 text-center mr-3 text-lg"></i>
                        <span class="sidebar-text nav-item-text font-medium">Banners</span>
                    </a>
                    <a href="/admin/vouchers" data-tooltip="Vouchers" class="nav-item {{ request()->is('admin/vouchers') ? 'active' : '' }} flex items-center px-4 py-3 rounded-lg transition-all duration-300 {{ request()->is('admin/vouchers') ? 'bg-gradient-to-r from-gray-700 to-gray-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-700 hover:text-white hover:shadow-md' }}">
                        <i class="nav-item-icon fas fa-ticket-alt w-5 text-center mr-3 text-lg"></i>
                        <span class="sidebar-text nav-item-text font-medium">Vouchers</span>
                    </a>
                </nav>

                <!-- Footer -->
                <div class="p-4 border-t border-gray-700 bg-gradient-to-b from-gray-900 to-black">
                    <div class="flex items-center justify-center mb-4 p-3 bg-gray-800 rounded-lg hover:bg-gray-750 transition-all duration-300">
                        <div class="sidebar-text flex-1 min-w-0 mr-3">
                            <p class="text-xs text-gray-400 mb-1">Logged in as</p>
                            <p class="text-sm font-semibold text-white truncate" id="admin-name">Admin</p>
                        </div>
                        <div class="profile-badge w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    </div>
                    <button onclick="logout()" class="logout-btn w-full px-4 py-2.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 rounded-lg transition-all duration-300 flex items-center justify-center text-sm font-medium shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        <span class="sidebar-text">Logout</span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div id="sidebar-overlay" class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-20 hidden transition-opacity duration-300"></div>

        <!-- Main Content -->
        <main class="flex-1 min-h-screen pt-16 md:pt-8 p-4 md:p-8 bg-gray-50">
            @yield('content')
        </main>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const closeSidebar = document.getElementById('close-sidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('hidden');
        }

        if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleSidebar);
        if (closeSidebar) closeSidebar.addEventListener('click', toggleSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);

        // Desktop sidebar collapse/expand
        const toggleSidebarBtn = document.getElementById('toggle-sidebar-btn');
        const mainContent = document.querySelector('main');

        function toggleDesktopSidebar() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
            toggleSidebarBtn.classList.toggle('collapsed');

            // Toggle icon
            const icon = toggleSidebarBtn.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
            }

            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        if (toggleSidebarBtn) {
            toggleSidebarBtn.addEventListener('click', toggleDesktopSidebar);

            // Restore sidebar state from localStorage
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed && window.innerWidth >= 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
                toggleSidebarBtn.classList.add('collapsed');
                const icon = toggleSidebarBtn.querySelector('i');
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            }
        }

        // Get token from localStorage
        const token = localStorage.getItem('access_token');

        if (!token) {
            window.location.href = '/admin/login';
        }

        // Set token in headers for API calls
        const apiBaseUrl = window.location.origin + '/api';

        async function fetchWithAuth(url, options = {}) {
            const token = localStorage.getItem('access_token');
            const headers = {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                ...options.headers
            };

            const isFormData = options.body instanceof FormData;
            if (!isFormData && !headers['Content-Type']) {
                headers['Content-Type'] = 'application/json';
            }

            return fetch(`${apiBaseUrl}${url}`, {
                ...options,
                headers
            });
        }

        // Toast notification function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';

            toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 animate-slide-in`;
            toast.innerHTML = `
                <i class="fas ${icon}"></i>
                <span>${message}</span>
            `;

            document.getElementById('toast-container').appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slide-out 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        async function logout() {
            const token = localStorage.getItem('access_token');
            if (token) {
                try {
                    await fetchWithAuth('/auth/logout', { method: 'POST' });
                } catch (e) {
                    console.error('Logout error:', e);
                }
            }
            localStorage.removeItem('access_token');
            localStorage.removeItem('refresh_token');
            window.location.href = '/admin/login';
        }

        // Load admin info on page load
        fetchWithAuth('/auth/me')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    if (data.data.role !== 'admin') {
                        showToast('Access denied. Admin role required.', 'error');
                        setTimeout(() => logout(), 2000);
                        return;
                    }
                    const adminNameEl = document.getElementById('admin-name');
                    if (adminNameEl) {
                        adminNameEl.textContent = data.data.name;
                    }
                } else {
                    logout();
                }
            })
            .catch(() => {
                logout();
            });
    </script>
    <style>
        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slide-out {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease;
        }
    </style>
    @yield('scripts')
</body>
</html>
