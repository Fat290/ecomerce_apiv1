@extends('admin.layout')

@section('title', 'User Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">User Management</h1>
            <p class="text-gray-600">Manage users, sellers, and their accounts</p>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter text-blue-500 mr-2"></i>
            Filters & Search
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select id="filter-role" class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="">All Roles</option>
                    <option value="seller">Sellers</option>
                    <option value="buyer">Buyers</option>
                    <option value="admin">Admins</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="filter-status" class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="banned">Banned</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <div class="relative">
                    <input
                        type="text"
                        id="search-input"
                        placeholder="Search by name, email..."
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 pl-10 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    >
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div class="flex items-end">
                <button
                    onclick="loadUsers()"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg transition-colors flex items-center justify-center space-x-2"
                >
                    <i class="fas fa-search"></i>
                    <span>Filter</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                                <p class="text-gray-500">Loading users...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="mt-6 flex justify-center"></div>
</div>

@section('scripts')
<script>
    let currentPage = 1;

    function loadUsers(page = 1) {
        currentPage = page;
        const role = document.getElementById('filter-role').value;
        const status = document.getElementById('filter-status').value;
        const search = document.getElementById('search-input').value;

        // Show loading state
        const tbody = document.getElementById('users-table-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p class="text-gray-500">Loading users...</p>
                    </div>
                </td>
            </tr>
        `;

        let url = `/admin/users?page=${page}`;
        if (role) url += `&role=${role}`;
        if (status) url += `&status=${status}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;

        fetchWithAuth(url)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    renderUsers(data.data);
                    renderPagination(data.pagination);
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Failed to load users</td></tr>';
                    showToast('Failed to load users', 'error');
                }
            })
            .catch(err => {
                console.error('Error loading users:', err);
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error loading users</td></tr>';
                showToast('Error loading users', 'error');
            });
    }

    function renderUsers(users) {
        const tbody = document.getElementById('users-table-body');
        if (users.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-users text-gray-300 text-4xl mb-2"></i>
                            <p class="text-gray-500">No users found</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = users.map(user => `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#${user.id}</td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                            <i class="fas fa-user text-blue-500 text-xs"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900">${user.name}</span>
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">${user.email}</td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full ${
                        user.role === 'admin' ? 'bg-purple-100 text-purple-800' :
                        user.role === 'seller' ? 'bg-blue-100 text-blue-800' :
                        'bg-gray-100 text-gray-800'
                    }">
                        <i class="fas ${user.role === 'admin' ? 'fa-crown' : user.role === 'seller' ? 'fa-store' : 'fa-shopping-cart'} mr-1"></i>
                        ${user.role}
                    </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full ${
                        user.status === 'active' ? 'bg-green-100 text-green-800' :
                        user.status === 'banned' ? 'bg-red-100 text-red-800' :
                        'bg-yellow-100 text-yellow-800'
                    }">
                        <i class="fas ${user.status === 'active' ? 'fa-check-circle' : user.status === 'banned' ? 'fa-ban' : 'fa-clock'} mr-1"></i>
                        ${user.status}
                    </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center space-x-2 flex-wrap gap-2">
                        <button onclick="viewUser(${user.id})" class="px-3 py-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition-colors text-xs">
                            <i class="fas fa-eye mr-1"></i>View
                        </button>
                        ${user.status === 'banned'
                            ? `<button onclick="unbanUser(${user.id})" class="px-3 py-1 bg-green-50 text-green-600 rounded hover:bg-green-100 transition-colors text-xs">
                                <i class="fas fa-unlock mr-1"></i>Unban
                            </button>`
                            : `<button onclick="banUser(${user.id})" class="px-3 py-1 bg-red-50 text-red-600 rounded hover:bg-red-100 transition-colors text-xs">
                                <i class="fas fa-ban mr-1"></i>Ban
                            </button>`
                        }
                        ${user.role === 'seller' && user.status === 'pending'
                            ? `<button onclick="activateSeller(${user.id})" class="px-3 py-1 bg-green-50 text-green-600 rounded hover:bg-green-100 transition-colors text-xs">
                                <i class="fas fa-check mr-1"></i>Activate
                            </button>`
                            : ''
                        }
                        <select onchange="updateStatus(${user.id}, this.value)" class="border border-gray-200 rounded px-2 py-1 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                            <option value="active" ${user.status === 'active' ? 'selected' : ''}>Active</option>
                            <option value="banned" ${user.status === 'banned' ? 'selected' : ''}>Banned</option>
                            <option value="pending" ${user.status === 'pending' ? 'selected' : ''}>Pending</option>
                        </select>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderPagination(pagination) {
        if (!pagination) return;

        const paginationDiv = document.getElementById('pagination');
        let html = '<div class="flex items-center space-x-2">';

        if (pagination.current_page > 1) {
            html += `
                <button onclick="loadUsers(${pagination.current_page - 1})" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-chevron-left"></i>
                </button>
            `;
        }

        html += `
            <span class="px-4 py-2 bg-blue-500 text-white rounded-lg font-semibold">
                Page ${pagination.current_page} of ${pagination.last_page}
            </span>
        `;

        if (pagination.current_page < pagination.last_page) {
            html += `
                <button onclick="loadUsers(${pagination.current_page + 1})" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-chevron-right"></i>
                </button>
            `;
        }

        html += '</div>';
        paginationDiv.innerHTML = html;
    }

    function banUser(id) {
        if (!confirm('Are you sure you want to ban this user?')) return;

        fetchWithAuth(`/admin/users/${id}/ban`, { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('User banned successfully', 'success');
                    loadUsers(currentPage);
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                showToast('Failed to ban user', 'error');
            });
    }

    function unbanUser(id) {
        if (!confirm('Are you sure you want to unban this user?')) return;

        fetchWithAuth(`/admin/users/${id}/unban`, { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('User unbanned successfully', 'success');
                    loadUsers(currentPage);
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                showToast('Failed to unban user', 'error');
            });
    }

    function activateSeller(id) {
        if (!confirm('Are you sure you want to activate this seller?')) return;

        fetchWithAuth(`/admin/users/${id}/activate-seller`, { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Seller activated successfully', 'success');
                    loadUsers(currentPage);
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                showToast('Failed to activate seller', 'error');
            });
    }

    function updateStatus(id, status) {
        if (!confirm(`Are you sure you want to change status to ${status}?`)) {
            loadUsers(currentPage);
            return;
        }

        fetchWithAuth(`/admin/users/${id}/status`, {
            method: 'PUT',
            body: JSON.stringify({ status })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Status updated successfully', 'success');
                    loadUsers(currentPage);
                } else {
                    showToast('Error: ' + data.message, 'error');
                    loadUsers(currentPage);
                }
            })
            .catch(err => {
                showToast('Failed to update status', 'error');
                loadUsers(currentPage);
            });
    }

    function viewUser(id) {
        // TODO: Implement user detail view
        showToast('User detail view coming soon', 'info');
    }

    // Load users on page load
    loadUsers();

    // Allow Enter key to trigger search
    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            loadUsers(1);
        }
    });
</script>
@endsection
