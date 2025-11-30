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
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.open {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <div class="min-h-screen flex">
        <!-- Mobile Menu Button -->
        <button id="mobile-menu-btn" class="md:hidden fixed top-4 left-4 z-40 bg-gray-800 text-white p-3 rounded-lg shadow-lg hover:bg-gray-700 transition-colors">
            <i class="fas fa-bars text-lg"></i>
        </button>

        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar fixed md:fixed inset-y-0 left-0 w-64 bg-gray-800 text-white z-30 shadow-xl">
            <div class="flex flex-col h-full">
                <!-- Header -->
                <div class="p-6 border-b border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-white">Admin Panel</h1>
                            <p class="text-gray-400 text-xs mt-1">E-Commerce Management</p>
                        </div>
                        <button id="close-sidebar" class="md:hidden text-gray-400 hover:text-white transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                    <a href="/admin/dashboard" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->is('admin/dashboard') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                        <i class="fas fa-chart-line w-5 text-center mr-3"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    <a href="/admin/users" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->is('admin/users') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                        <i class="fas fa-users w-5 text-center mr-3"></i>
                        <span class="font-medium">User Management</span>
                    </a>
                </nav>

                <!-- Footer -->
                <div class="p-4 border-t border-gray-700 bg-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-400 mb-1">Logged in as</p>
                            <p class="text-sm font-semibold text-white truncate" id="admin-name">Admin</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center ml-3 flex-shrink-0">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    </div>
                    <button onclick="logout()" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg transition-colors flex items-center justify-center text-sm font-medium">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div id="sidebar-overlay" class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-20 hidden"></div>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 min-h-screen p-4 md:p-8">
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

        // Get token from localStorage
        const token = localStorage.getItem('access_token');

        if (!token) {
            window.location.href = '/admin/login';
        }

        // Set token in headers for API calls
        const apiBaseUrl = window.location.origin + '/api';

        async function fetchWithAuth(url, options = {}) {
            const token = localStorage.getItem('access_token');
            return fetch(`${apiBaseUrl}${url}`, {
                ...options,
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...options.headers
                }
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
