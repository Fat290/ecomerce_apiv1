@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Dashboard</h1>
        <p class="text-gray-600">Welcome back! Here's an overview of your platform.</p>
    </div>

    <!-- Statistics Cards -->
    <div id="statistics" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Users</p>
                    <p class="text-3xl font-bold text-gray-800" id="total-users">
                        <span class="inline-block w-8 h-8 border-2 border-gray-300 border-t-blue-500 rounded-full animate-spin"></span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Total Sellers</p>
                    <p class="text-3xl font-bold text-gray-800" id="total-sellers">
                        <span class="inline-block w-8 h-8 border-2 border-gray-300 border-t-green-500 rounded-full animate-spin"></span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-store text-green-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Active Shops</p>
                    <p class="text-3xl font-bold text-gray-800" id="active-shops">
                        <span class="inline-block w-8 h-8 border-2 border-gray-300 border-t-purple-500 rounded-full animate-spin"></span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Pending Sellers</p>
                    <p class="text-3xl font-bold text-gray-800" id="pending-sellers">
                        <span class="inline-block w-8 h-8 border-2 border-gray-300 border-t-yellow-500 rounded-full animate-spin"></span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i>
            Quick Actions
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="/admin/users" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group">
                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-users text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Manage Users</p>
                    <p class="text-sm text-gray-600">View and manage all users</p>
                </div>
            </a>
            <a href="/admin/users?role=seller&status=pending" class="flex items-center p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-colors group">
                <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-user-check text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Review Sellers</p>
                    <p class="text-sm text-gray-600">Approve pending sellers</p>
                </div>
            </a>
            <a href="/admin/users?status=banned" class="flex items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition-colors group">
                <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-ban text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Banned Users</p>
                    <p class="text-sm text-gray-600">View banned accounts</p>
                </div>
            </a>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Load statistics
    fetchWithAuth('/admin/users/statistics')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data) {
                document.getElementById('total-users').textContent = data.data.total_users || 0;
                document.getElementById('total-sellers').textContent = data.data.total_sellers || 0;
                document.getElementById('active-shops').textContent = data.data.active_shops || 0;
                document.getElementById('pending-sellers').textContent = data.data.pending_sellers || 0;
            }
        })
        .catch(err => {
            console.error('Failed to load statistics:', err);
            document.getElementById('total-users').textContent = 'Error';
            document.getElementById('total-sellers').textContent = 'Error';
            document.getElementById('active-shops').textContent = 'Error';
            document.getElementById('pending-sellers').textContent = 'Error';
        });
</script>
@endsection
