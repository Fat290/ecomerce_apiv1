@extends('admin.layout')

@section('title', 'Voucher Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Voucher Management</h1>
            <p class="text-gray-600">Create and manage platform-wide vouchers.</p>
        </div>
    </div>

    <!-- Create Voucher Card -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-ticket text-blue-500 mr-2"></i>
            Add New Voucher
        </h2>

        <form id="create-voucher-form" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Voucher Code <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="create-code"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none uppercase"
                        placeholder="E.g., FREESHIP10"
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Voucher Type</label>
                    <select id="create-voucher-type" class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="shipping">Shipping Discount</option>
                        <option value="product">Product Discount</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount Type</label>
                    <select id="create-discount-type" class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="percent">Percent (%)</option>
                        <option value="amount">Fixed Amount</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount Value <span class="text-red-500">*</span></label>
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        id="create-discount-value"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="E.g., 10 or 50000"
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Order Value</label>
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        id="create-min-order"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="Optional"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input
                        type="datetime-local"
                        id="create-start-date"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input
                        type="datetime-local"
                        id="create-end-date"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        required
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="create-status" class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="active">Active</option>
                        <option value="disabled">Disabled</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button
                    type="submit"
                    id="create-submit-btn"
                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-colors"
                >
                    <i class="fas fa-save mr-2"></i>
                    <span id="create-submit-text">Save Voucher</span>
                </button>
                <button
                    type="button"
                    id="create-reset-btn"
                    class="inline-flex items-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors"
                >
                    <i class="fas fa-undo mr-2"></i>
                    Reset
                </button>
            </div>
        </form>
    </div>

    <!-- Existing Vouchers -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-list text-purple-500 mr-2"></i>
                Existing Vouchers
            </h2>
            <button
                type="button"
                id="refresh-vouchers-btn"
                class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition-colors"
            >
                <i class="fas fa-sync-alt mr-1"></i>
                Refresh
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Discount</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Validity</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="vouchers-table-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                                <p class="text-gray-500 text-sm">Loading vouchers...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Voucher Modal -->
<div id="edit-voucher-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 relative">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-edit text-blue-500 mr-2"></i>
                Edit Voucher
            </h3>
            <button id="edit-voucher-close-btn" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <form id="edit-voucher-form" class="px-6 py-5 space-y-4">
            <input type="hidden" id="edit-voucher-id">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Voucher Code</label>
                    <input
                        type="text"
                        id="edit-code"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none uppercase"
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Voucher Type</label>
                    <select id="edit-voucher-type" class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="shipping">Shipping Discount</option>
                        <option value="product">Product Discount</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount Type</label>
                    <select id="edit-discount-type" class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="percent">Percent (%)</option>
                        <option value="amount">Fixed Amount</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount Value</label>
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        id="edit-discount-value"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Order Value</label>
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        id="edit-min-order"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input
                        type="datetime-local"
                        id="edit-start-date"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input
                        type="datetime-local"
                        id="edit-end-date"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        required
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="edit-status" class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="active">Active</option>
                        <option value="disabled">Disabled</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 pb-4">
                <button
                    type="button"
                    id="edit-voucher-cancel-btn"
                    class="inline-flex items-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors"
                >
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </button>
                <button
                    type="submit"
                    id="edit-voucher-save-btn"
                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-colors"
                >
                    <i class="fas fa-save mr-2"></i>
                    <span id="edit-voucher-save-text">Update Voucher</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const vouchersApiBase = '/admin/vouchers';

    function resetCreateForm() {
        document.getElementById('create-code').value = '';
        document.getElementById('create-voucher-type').value = 'shipping';
        document.getElementById('create-discount-type').value = 'percent';
        document.getElementById('create-discount-value').value = '';
        document.getElementById('create-min-order').value = '';
        document.getElementById('create-start-date').value = '';
        document.getElementById('create-end-date').value = '';
        document.getElementById('create-status').value = 'active';
    }
    resetCreateForm();

    function formatDateTime(value) {
        if (!value) return '';
        const date = new Date(value);
        return date.toISOString().slice(0, 16);
    }

    function formatDisplayDate(value) {
        if (!value) return '—';
        const date = new Date(value);
        return date.toLocaleString();
    }

    function loadVouchers() {
        const tbody = document.getElementById('vouchers-table-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-10 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p class="text-gray-500 text-sm">Loading vouchers...</p>
                    </div>
                </td>
            </tr>
        `;

        fetchWithAuth(vouchersApiBase)
            .then(res => res.json())
            .then(data => {
                if (!data.success || !Array.isArray(data.data)) {
                    showToast('Failed to load vouchers', 'error');
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-6 text-center text-red-500 text-sm">Failed to load vouchers.</td></tr>';
                    return;
                }

                if (data.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-flax items-center">
                                    <i class="fas fa-ticket text-gray-300 text-4xl mb-2"></i>
                                    <p class="text-gray-500 text-sm">No vouchers created yet.</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = data.data.map(voucher => `
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">${voucher.code}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 capitalize">${voucher.voucher_type}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <span class="font-semibold">${voucher.discount_type === 'percent' ? voucher.discount_value + '%' : '₫' + Number(voucher.discount_value).toLocaleString()}</span>
                            <span class="text-xs text-gray-500 block">Min: ${voucher.min_order_value ? '₫' + Number(voucher.min_order_value).toLocaleString() : 'Not set'}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <span class="block">${formatDisplayDate(voucher.start_date)}</span>
                            <span class="block text-xs text-gray-500">to</span>
                            <span class="block">${formatDisplayDate(voucher.end_date)}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                                voucher.status === 'active'
                                    ? 'bg-green-100 text-green-800'
                                    : (voucher.status === 'disabled' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-200 text-gray-600')
                            }">
                                ${voucher.status}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center space-x-2">
                                <button
                                    type="button"
                                    class="px-3 py-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 text-xs font-semibold transition-colors"
                                    onclick='openEditModal(${JSON.stringify(voucher)})'
                                >
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-1 bg-red-50 text-red-600 rounded hover:bg-red-100 text-xs font-semibold transition-colors"
                                    onclick="deleteVoucher(${voucher.id})"
                                >
                                    <i class="fas fa-trash-alt mr-1"></i>Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            })
            .catch(err => {
                console.error('Error loading vouchers:', err);
                showToast('Error loading vouchers', 'error');
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-6 text-center text-red-500 text-sm">Error loading vouchers.</td></tr>';
            });
    }

    document.getElementById('refresh-vouchers-btn').addEventListener('click', loadVouchers);
    document.getElementById('create-reset-btn').addEventListener('click', resetCreateForm);

    function openEditModal(voucher) {
        const modal = document.getElementById('edit-voucher-modal');
        document.getElementById('edit-voucher-id').value = voucher.id;
        document.getElementById('edit-code').value = voucher.code ?? '';
        document.getElementById('edit-voucher-type').value = voucher.voucher_type ?? 'product';
        document.getElementById('edit-discount-type').value = voucher.discount_type ?? 'percent';
        document.getElementById('edit-discount-value').value = voucher.discount_value ?? 0;
        document.getElementById('edit-min-order').value = voucher.min_order_value ?? 0;
        document.getElementById('edit-start-date').value = formatDateTime(voucher.start_date);
        document.getElementById('edit-end-date').value = formatDateTime(voucher.end_date);
        document.getElementById('edit-status').value = voucher.status ?? 'active';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('edit-voucher-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('edit-voucher-close-btn').addEventListener('click', closeEditModal);
    document.getElementById('edit-voucher-cancel-btn').addEventListener('click', closeEditModal);

    document.getElementById('create-voucher-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const payload = {
            code: document.getElementById('create-code').value.trim(),
            voucher_type: document.getElementById('create-voucher-type').value,
            discount_type: document.getElementById('create-discount-type').value,
            discount_value: parseFloat(document.getElementById('create-discount-value').value || 0),
            min_order_value: parseFloat(document.getElementById('create-min-order').value || 0),
            start_date: document.getElementById('create-start-date').value,
            end_date: document.getElementById('create-end-date').value,
            status: document.getElementById('create-status').value,
        };

        if (!payload.code || isNaN(payload.discount_value)) {
            showToast('Please provide a valid code and discount value.', 'error');
            return;
        }

        const submitBtn = document.getElementById('create-submit-btn');
        const submitText = document.getElementById('create-submit-text');
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        submitText.textContent = 'Saving...';

        fetchWithAuth(vouchersApiBase, {
            method: 'POST',
            body: JSON.stringify(payload)
        })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    showToast('Voucher created successfully.', 'success');
                    resetCreateForm();
                    loadVouchers();
                } else {
                    showToast(data.message || 'Failed to create voucher.', 'error');
                }
            })
            .catch(err => {
                console.error('Error creating voucher:', err);
                showToast('Error creating voucher', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                submitText.textContent = 'Save Voucher';
            });
    });

    document.getElementById('edit-voucher-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const id = document.getElementById('edit-voucher-id').value;
        if (!id) {
            showToast('No voucher selected.', 'error');
            return;
        }

        const payload = {
            code: document.getElementById('edit-code').value.trim(),
            voucher_type: document.getElementById('edit-voucher-type').value,
            discount_type: document.getElementById('edit-discount-type').value,
            discount_value: parseFloat(document.getElementById('edit-discount-value').value || 0),
            min_order_value: parseFloat(document.getElementById('edit-min-order').value || 0),
            start_date: document.getElementById('edit-start-date').value,
            end_date: document.getElementById('edit-end-date').value,
            status: document.getElementById('edit-status').value,
        };

        const saveBtn = document.getElementById('edit-voucher-save-btn');
        const saveText = document.getElementById('edit-voucher-save-text');
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-70', 'cursor-not-allowed');
        saveText.textContent = 'Updating...';

        fetchWithAuth(`${vouchersApiBase}/${id}`, {
            method: 'PUT',
            body: JSON.stringify(payload)
        })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    showToast('Voucher updated successfully.', 'success');
                    closeEditModal();
                    loadVouchers();
                } else {
                    showToast(data.message || 'Failed to update voucher.', 'error');
                }
            })
            .catch(err => {
                console.error('Error updating voucher:', err);
                showToast('Error updating voucher', 'error');
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                saveText.textContent = 'Update Voucher';
            });
    });

    function deleteVoucher(id) {
        if (!confirm('Are you sure you want to delete this voucher?')) {
            return;
        }

        fetchWithAuth(`${vouchersApiBase}/${id}`, {
            method: 'DELETE'
        })
            .then(res => {
                if (res.status === 204) {
                    showToast('Voucher deleted successfully.', 'success');
                    loadVouchers();
                    return;
                }
                return res.json();
            })
            .then(data => {
                if (data && !data.success) {
                    showToast(data.message || 'Failed to delete voucher.', 'error');
                }
            })
            .catch(err => {
                console.error('Error deleting voucher:', err);
                showToast('Error deleting voucher', 'error');
            });
    }

    loadVouchers();
</script>
@endsection


