@extends('admin.layout')

@section('title', 'Pending Sellers')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Pending Sellers</h1>
            <p class="text-gray-600">Review new shop registrations and approve or decline sellers.</p>
        </div>
        <div class="flex items-center space-x-3 mt-4 sm:mt-0">
            <select id="status-filter" class="border-2 border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                <option value="pending" selected>Pending</option>
                <option value="active">Active</option>
                <option value="banned">Rejected</option>
            </select>
            <input
                type="text"
                id="shop-search"
                placeholder="Search by shop or owner"
                class="border-2 border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
            >
            <button id="refresh-button" class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition">
                <i class="fas fa-sync-alt mr-1"></i>
                Refresh
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4 flex items-center space-x-4">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xl font-semibold">A</div>
            <div>
                <p class="text-sm text-gray-500">Awaiting Approval</p>
                <p id="stat-pending" class="text-2xl font-bold text-gray-800">0</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex items-center space-x-4">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xl font-semibold">✓</div>
            <div>
                <p class="text-sm text-gray-500">Approved Sellers</p>
                <p id="stat-active" class="text-2xl font-bold text-gray-800">0</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex items-center space-x-4">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-xl font-semibold">!</div>
            <div>
                <p class="text-sm text-gray-500">Rejected</p>
                <p id="stat-banned" class="text-2xl font-bold text-gray-800">0</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="pending-shops-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Loading pending sellers...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
            <div class="text-sm text-gray-500" id="pagination-summary">Showing 0 of 0</div>
            <div class="space-x-2">
                <button id="prev-page" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition">Previous</button>
                <button id="next-page" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition">Next</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const state = {
        status: 'pending',
        search: '',
        page: 1,
        totals: {
            pending: 0,
            active: 0,
            banned: 0,
        },
    };

    const tableBody = document.getElementById('pending-shops-body');
    const paginationSummary = document.getElementById('pagination-summary');
    const prevBtn = document.getElementById('prev-page');
    const nextBtn = document.getElementById('next-page');
    const statusFilter = document.getElementById('status-filter');
    const searchInput = document.getElementById('shop-search');
    const refreshButton = document.getElementById('refresh-button');

    statusFilter.addEventListener('change', () => {
        state.status = statusFilter.value;
        state.page = 1;
        loadPendingShops();
    });

    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            state.search = searchInput.value.trim();
            state.page = 1;
            loadPendingShops();
        }, 300);
    });

    refreshButton.addEventListener('click', () => {
        loadPendingShops(true);
    });

    prevBtn.addEventListener('click', () => {
        if (state.page > 1) {
            state.page--;
            loadPendingShops();
        }
    });

    nextBtn.addEventListener('click', () => {
        state.page++;
        loadPendingShops();
    });

    function updateStats() {
        document.getElementById('stat-pending').textContent = state.totals.pending;
        document.getElementById('stat-active').textContent = state.totals.active;
        document.getElementById('stat-banned').textContent = state.totals.banned;
    }

    function renderRows(shops) {
        if (!shops.length) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center space-y-3">
                            <i class="fas fa-store text-4xl text-gray-300"></i>
                            <p class="text-gray-500 font-medium">No shops found for the selected filters.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = shops.map(shop => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-semibold">
                            ${shop.name?.charAt(0)?.toUpperCase() ?? '?'}
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-semibold text-gray-900">${shop.name ?? '—'}</div>
                            <div class="text-sm text-gray-500">${shop.address ?? 'No address provided'}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">${shop.owner?.name ?? '—'}</div>
                    <div class="text-sm text-gray-500">${shop.owner?.email ?? ''}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">${shop.business_type?.name ?? '—'}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${formatDate(shop.created_at)}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${badgeClass(shop.status)}">
                        ${shop.status}
                    </span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                    ${shop.status === 'pending' ? `
                        <button onclick="approveShop(${shop.id})" class="inline-flex items-center px-3 py-2 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition">
                            <i class="fas fa-check mr-1"></i> Approve
                        </button>
                        <button onclick="rejectShop(${shop.id})" class="inline-flex items-center px-3 py-2 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 transition">
                            <i class="fas fa-times mr-1"></i> Decline
                        </button>
                    ` : '<span class="text-sm text-gray-400">No actions</span>'}
                </td>
            </tr>
        `).join('');
    }

    function badgeClass(status) {
        switch (status) {
            case 'active':
                return 'bg-green-100 text-green-700';
            case 'pending':
                return 'bg-yellow-100 text-yellow-700';
            case 'banned':
                return 'bg-red-100 text-red-700';
            default:
                return 'bg-gray-100 text-gray-600';
        }
    }

    function formatDate(dateString) {
        if (!dateString) return '—';
        const date = new Date(dateString);
        return date.toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }

    async function loadPendingShops(force = false) {
        if (!force) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        Loading sellers...
                    </td>
                </tr>
            `;
        }

        try {
            const params = new URLSearchParams({
                page: state.page,
                status: state.status,
            });

            if (state.search) {
                params.append('search', state.search);
            }

            const response = await fetchWithAuth(`/shops?${params.toString()}`);
            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Failed to load shops');
            }

            renderRows(result.data);
            paginationSummary.textContent = `Showing ${result.pagination.from || 0}-${result.pagination.to || 0} of ${result.pagination.total}`;
            prevBtn.disabled = state.page <= 1;
            nextBtn.disabled = state.page >= result.pagination.last_page;

            // Update totals if provided
            if (result.meta?.totals) {
                state.totals = {
                    pending: result.meta.totals.pending ?? state.totals.pending,
                    active: result.meta.totals.active ?? state.totals.active,
                    banned: result.meta.totals.banned ?? state.totals.banned,
                };
                updateStats();
            } else {
                fetchTotals(); // fallback to dedicated call
            }
        } catch (error) {
            console.error(error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-red-500">
                        ${error.message}
                    </td>
                </tr>
            `;
        }
    }

    async function fetchTotals() {
        try {
            const statuses = ['pending', 'active', 'banned'];
            const totals = {};

            await Promise.all(statuses.map(async status => {
                const response = await fetchWithAuth(`/shops?status=${status}&page=1`);
                const result = await response.json();
                if (result.success) {
                    totals[status] = result.pagination?.total ?? 0;
                } else {
                    totals[status] = 0;
                }
            }));

            state.totals = {
                pending: totals.pending ?? 0,
                active: totals.active ?? 0,
                banned: totals.banned ?? 0,
            };
            updateStats();
        } catch (error) {
            console.error('Failed to load totals', error);
        }
    }

    async function approveShop(shopId) {
        await updateShopStatus(shopId, 'active', 'Shop approved successfully.');
    }

    async function rejectShop(shopId) {
        if (!confirm('Are you sure you want to reject this shop?')) return;
        await updateShopStatus(shopId, 'banned', 'Shop rejected successfully.');
    }

    async function updateShopStatus(shopId, status, successMessage) {
        try {
            const response = await fetchWithAuth(`/shops/${shopId}`, {
                method: 'PATCH',
                body: JSON.stringify({ status }),
            });

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Update failed');
            }

            showToast(successMessage, 'success');
            loadPendingShops();
        } catch (error) {
            console.error(error);
            showToast(error.message, 'error');
        }
    }

    loadPendingShops();
    fetchTotals();
</script>
@endsection

