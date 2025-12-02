@extends('admin.layout')

@section('title', 'Banner Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Banner Management</h1>
            <p class="text-gray-600">Create and manage the banners shown in the app</p>
        </div>
    </div>

    <!-- Create Banner Card -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-image text-blue-500 mr-2"></i>
            Add New Banner
        </h2>

        <form id="create-banner-form" class="space-y-4" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="create-title"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="Main banner title"
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                    <input
                        type="text"
                        id="create-subtitle"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="Optional subtitle"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center mt-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="create-active" class="form-checkbox h-5 w-5 text-blue-600 rounded" checked>
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Banner Image <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="file"
                        id="create-image"
                        accept="image/*"
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg px-4 py-3 bg-gray-50 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        required
                    >
                    <p class="mt-1 text-xs text-gray-500">Recommended ratio ~16:9. Max size 5MB.</p>
                </div>
                <div class="flex flex-col items-center">
                    <span class="block text-sm font-medium text-gray-700 mb-2">Preview</span>
                    <div id="create-preview" class="w-full h-32 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                        <span class="text-gray-400 text-sm">No image selected</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button
                    type="submit"
                    id="create-submit-btn"
                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-colors"
                >
                    <i class="fas fa-save mr-2"></i>
                    <span id="create-submit-text">Save Banner</span>
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

    <!-- Existing Banners -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-images text-purple-500 mr-2"></i>
                Existing Banners
            </h2>
            <button
                type="button"
                id="refresh-banners-btn"
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Image</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="banners-table-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                                <p class="text-gray-500 text-sm">Loading banners...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Banner Modal -->
<div id="edit-banner-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 relative">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-edit text-blue-500 mr-2"></i>
                Edit Banner
            </h3>
            <button id="edit-banner-close-btn" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <form id="edit-banner-form" class="px-6 py-5 space-y-4" enctype="multipart/form-data">
            <input type="hidden" id="edit-banner-id">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="edit-banner-title"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                    <input
                        type="text"
                        id="edit-banner-subtitle"
                        class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    >
                </div>
            </div>

            <div class="flex items-center">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="edit-banner-active" class="form-checkbox h-5 w-5 text-blue-600 rounded">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Banner Image (optional)</label>
                    <input
                        type="file"
                        id="edit-banner-image"
                        accept="image/*"
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg px-4 py-3 bg-gray-50 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    >
                    <p class="mt-1 text-xs text-gray-500">Leave empty to keep the current image.</p>
                </div>
                <div class="flex flex-col items-center">
                    <span class="block text-sm font-medium text-gray-700 mb-2">Current Preview</span>
                    <div id="edit-banner-preview" class="w-full h-32 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                        <span class="text-gray-400 text-sm">No preview</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 pb-4">
                <button
                    type="button"
                    id="edit-banner-cancel-btn"
                    class="inline-flex items-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors"
                >
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </button>
                <button
                    type="submit"
                    id="edit-banner-save-btn"
                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-colors"
                >
                    <i class="fas fa-save mr-2"></i>
                    <span id="edit-banner-save-text">Update Banner</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const bannersApiBase = '/admin/banners';

    /* ------------ Helpers ------------ */

    function resetCreateForm() {
        document.getElementById('create-title').value = '';
        document.getElementById('create-subtitle').value = '';
        document.getElementById('create-active').checked = true;
        document.getElementById('create-image').value = '';

        const preview = document.getElementById('create-preview');
        preview.innerHTML = '<span class="text-gray-400 text-sm">No image selected</span>';
    }

    function renderPreview(inputEl, previewId) {
        const preview = document.getElementById(previewId);
        const file = inputEl.files[0];

        if (!file) {
            preview.innerHTML = '<span class="text-gray-400 text-sm">No image selected</span>';
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(file);
    }

    /* ------------ Load & List Banners ------------ */

    function loadBanners() {
        const tbody = document.getElementById('banners-table-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-10 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p class="text-gray-500 text-sm">Loading banners...</p>
                    </div>
                </td>
            </tr>
        `;

        fetchWithAuth(bannersApiBase)
            .then(res => res.json())
            .then(data => {
                if (!data.success || !Array.isArray(data.data)) {
                    showToast('Failed to load banners', 'error');
                    tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-6 text-center text-red-500 text-sm">Failed to load banners.</td></tr>';
                    return;
                }

                const banners = data.data;
                if (banners.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-images text-gray-300 text-4xl mb-2"></i>
                                    <p class="text-gray-500 text-sm">No banners created yet</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = banners.map(banner => `
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="w-24 h-14 rounded-lg overflow-hidden bg-gray-100">
                                <img src="${banner.image_url}" alt="${banner.title}" class="w-full h-full object-cover">
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-semibold text-gray-900">${banner.title}</div>
                            <div class="text-xs text-gray-500">${banner.subtitle ?? ''}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                                banner.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'
                            }">
                                <i class="fas ${banner.is_active ? 'fa-check-circle' : 'fa-pause-circle'} mr-1"></i>
                                ${banner.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center space-x-2">
                                <button
                                    type="button"
                                    class="px-3 py-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 text-xs font-semibold transition-colors"
                                    onclick='openEditModal(${JSON.stringify(banner)})'
                                >
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-1 bg-red-50 text-red-600 rounded hover:bg-red-100 text-xs font-semibold transition-colors"
                                    onclick="deleteBanner(${banner.id})"
                                >
                                    <i class="fas fa-trash-alt mr-1"></i>Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            })
            .catch(err => {
                console.error('Error loading banners:', err);
                showToast('Error loading banners', 'error');
                tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-6 text-center text-red-500 text-sm">Error loading banners.</td></tr>';
            });
    }

    /* ------------ Create Banner ------------ */

    document.getElementById('create-image').addEventListener('change', function () {
        renderPreview(this, 'create-preview');
    });

    document.getElementById('create-reset-btn').addEventListener('click', function () {
        resetCreateForm();
    });

    document.getElementById('refresh-banners-btn').addEventListener('click', function () {
        loadBanners();
    });

    document.getElementById('create-banner-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const title = document.getElementById('create-title').value.trim();
        if (!title) {
            showToast('Title is required', 'error');
            return;
        }

        const imageInput = document.getElementById('create-image');
        if (!imageInput.files[0]) {
            showToast('Please select a banner image', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('title', title);
        formData.append('subtitle', document.getElementById('create-subtitle').value);
        formData.append('is_active', document.getElementById('create-active').checked ? '1' : '0');
        formData.append('image', imageInput.files[0]);

        const submitBtn = document.getElementById('create-submit-btn');
        const submitText = document.getElementById('create-submit-text');
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        submitText.textContent = 'Saving...';

        fetchWithAuth(bannersApiBase, {
            method: 'POST',
            body: formData
        })
            .then(async res => {
                let data = null;
                try { data = await res.json(); } catch (e) {}

                if (res.ok && data && data.success) {
                    showToast('Banner created successfully', 'success');
                    resetCreateForm();
                    loadBanners();
                } else {
                    const message = (data && data.message) || 'Failed to create banner';
                    showToast(message, 'error');
                }
            })
            .catch(err => {
                console.error('Error creating banner:', err);
                showToast('Error creating banner', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                submitText.textContent = 'Save Banner';
            });
    });

    /* ------------ Edit Banner ------------ */

    function openEditModal(banner) {
        const modal = document.getElementById('edit-banner-modal');
        document.getElementById('edit-banner-id').value = banner.id;
        document.getElementById('edit-banner-title').value = banner.title ?? '';
        document.getElementById('edit-banner-subtitle').value = banner.subtitle ?? '';
        document.getElementById('edit-banner-active').checked = !!banner.is_active;

        const preview = document.getElementById('edit-banner-preview');
        if (banner.image_url) {
            preview.innerHTML = `<img src="${banner.image_url}" alt="${banner.title}" class="w-full h-full object-cover">`;
        } else {
            preview.innerHTML = '<span class="text-gray-400 text-sm">No preview</span>';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal() {
        const modal = document.getElementById('edit-banner-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('edit-banner-form').reset();
        document.getElementById('edit-banner-preview').innerHTML = '<span class="text-gray-400 text-sm">No preview</span>';
    }

    document.getElementById('edit-banner-close-btn').addEventListener('click', closeEditModal);
    document.getElementById('edit-banner-cancel-btn').addEventListener('click', closeEditModal);

    document.getElementById('edit-banner-image').addEventListener('change', function () {
        renderPreview(this, 'edit-banner-preview');
    });

    document.getElementById('edit-banner-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const id = document.getElementById('edit-banner-id').value;
        if (!id) {
            showToast('No banner selected for editing', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('title', document.getElementById('edit-banner-title').value);
        formData.append('subtitle', document.getElementById('edit-banner-subtitle').value);
        formData.append('is_active', document.getElementById('edit-banner-active').checked ? '1' : '0');

        const imageInput = document.getElementById('edit-banner-image');
        if (imageInput.files[0]) {
            formData.append('image', imageInput.files[0]);
        }

        const saveBtn = document.getElementById('edit-banner-save-btn');
        const saveText = document.getElementById('edit-banner-save-text');
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-70', 'cursor-not-allowed');
        saveText.textContent = 'Updating...';

        fetch(`${window.location.origin}/api${bannersApiBase}/${id}`, {
            method: 'POST', // use POST with _method override if PUT causes issues
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('access_token') || ''}`,
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        })
            .then(async res => {
                let data = null;
                try { data = await res.json(); } catch (e) {}

                if (res.ok && data && data.success) {
                    showToast('Banner updated successfully', 'success');
                    closeEditModal();
                    loadBanners();
                } else {
                    const message = (data && data.message) || 'Failed to update banner';
                    showToast(message, 'error');
                }
            })
            .catch(err => {
                console.error('Error updating banner:', err);
                showToast('Error updating banner', 'error');
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                saveText.textContent = 'Update Banner';
            });
    });

    /* ------------ Delete Banner ------------ */

    function deleteBanner(id) {
        if (!confirm('Are you sure you want to delete this banner?')) return;

        fetchWithAuth(`${bannersApiBase}/${id}`, {
            method: 'DELETE'
        })
            .then(res => {
                if (res.status === 204) {
                    showToast('Banner deleted successfully', 'success');
                    loadBanners();
                    return;
                }
                return res.json();
            })
            .then(data => {
                if (data && !data.success) {
                    showToast(data.message || 'Failed to delete banner', 'error');
                }
            })
            .catch(err => {
                console.error('Error deleting banner:', err);
                showToast('Error deleting banner', 'error');
            });
    }

    // Initial load
    resetCreateForm();
    loadBanners();
</script>
@endsection
