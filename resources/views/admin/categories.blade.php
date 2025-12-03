@extends('admin.layout')

@section('title', 'Category Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Category Management</h1>
            <p class="text-gray-600">Create categories and configure their variant fields.</p>
        </div>
        <button
            id="refresh-btn"
            class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 transition"
        >
            <i class="fas fa-rotate mr-2 text-blue-500"></i>
            Refresh
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-plus-circle text-blue-500 mr-2"></i>
                Create Category
            </h2>
            <form id="create-category-form" class="space-y-4" enctype="multipart/form-data">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" id="create-name" class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                    <select id="create-parent" class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="">— Root Category —</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <input type="file" id="create-image" accept="image/*" class="w-full border-2 border-dashed border-gray-300 rounded-lg px-4 py-3 text-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Variants</label>
                        <button type="button" id="add-create-variant" class="text-sm text-blue-600 hover:text-blue-700 font-semibold flex items-center">
                            <i class="fas fa-plus mr-1"></i>
                            Add Variant
                        </button>
                    </div>
                    <div id="create-variants" class="space-y-3"></div>
                    <p class="text-xs text-gray-500 mt-2">Variants describe product attributes for this category (e.g., Size, Color, Material).</p>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition">
                    Create Category
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                Tips
            </h2>
            <ul class="space-y-4 text-sm text-gray-600">
                <li class="flex items-start">
                    <i class="fas fa-layer-group text-blue-400 mt-1 mr-3"></i>
                    Organize categories into parent/child hierarchies to build a navigable catalog tree.
                </li>
                <li class="flex items-start">
                    <i class="fas fa-tags text-blue-400 mt-1 mr-3"></i>
                    Use variants to capture required product attributes (e.g., a clothing category can require Size and Color).
                </li>
                <li class="flex items-start">
                    <i class="fas fa-image text-blue-400 mt-1 mr-3"></i>
                    Category images appear in the storefront. Upload square images for best results.
                </li>
                <li class="flex items-start">
                    <i class="fas fa-sync text-blue-400 mt-1 mr-3"></i>
                    Updating variants replaces the entire set for that category to keep things in sync.
                </li>
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Categories</h2>
                <p class="text-sm text-gray-500">Manage existing categories and their variant schemas.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Parent</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Variants</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Updated</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="categories-table-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center text-gray-500 space-y-2">
                                <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                                <p>Loading categories...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">Edit Category</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="edit-category-form" class="p-6 space-y-4" enctype="multipart/form-data">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                <input type="text" id="edit-name" class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                <select id="edit-parent" class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="">— Root Category —</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Replace Image</label>
                <input type="file" id="edit-image" accept="image/*" class="w-full border-2 border-dashed border-gray-300 rounded-lg px-4 py-3 text-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                <p id="current-image" class="text-xs text-gray-500 mt-1"></p>
            </div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700">Variants</label>
                    <button type="button" id="add-edit-variant" class="text-sm text-blue-600 hover:text-blue-700 font-semibold flex items-center">
                        <i class="fas fa-plus mr-1"></i>
                        Add Variant
                    </button>
                </div>
                <div id="edit-variants" class="space-y-3"></div>
            </div>
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const categoriesState = {
        list: [],
        selectedId: null,
    };

    const createForm = document.getElementById('create-category-form');
    const createVariantsContainer = document.getElementById('create-variants');
    const editForm = document.getElementById('edit-category-form');
    const editVariantsContainer = document.getElementById('edit-variants');
    const categoriesTableBody = document.getElementById('categories-table-body');
    const refreshBtn = document.getElementById('refresh-btn');
    const createParentSelect = document.getElementById('create-parent');
    const editParentSelect = document.getElementById('edit-parent');
    const editModal = document.getElementById('edit-modal');
    const addCreateVariantBtn = document.getElementById('add-create-variant');
    const addEditVariantBtn = document.getElementById('add-edit-variant');

    const variantTemplate = (idPrefix, data = {}) => `
        <div class="variant-row border border-gray-200 rounded-lg p-3 space-y-3">
            <div class="flex items-center justify-between">
                <div class="w-full mr-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Variant Name</label>
                    <input type="text" class="variant-name w-full border border-gray-200 rounded px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" value="${data.name ?? ''}" placeholder="e.g., Size" required>
                </div>
                <label class="inline-flex items-center mt-5">
                    <input type="checkbox" class="variant-required form-checkbox h-5 w-5 text-blue-600" ${data.is_required ? 'checked' : ''}>
                    <span class="ml-2 text-sm text-gray-600">Required</span>
                </label>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Options</label>
                <input type="text" class="variant-options w-full border border-gray-200 rounded px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" value="${(data.options || []).join(', ')}" placeholder="Comma separated list (e.g., S, M, L, XL)">
            </div>
            <button type="button" class="remove-variant text-sm text-red-500 hover:text-red-600 flex items-center">
                <i class="fas fa-trash mr-1"></i> Remove
            </button>
        </div>
    `;

    function addVariantRow(container, data = {}) {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = variantTemplate(container.id, data);
        const row = wrapper.firstElementChild;
        row.querySelector('.remove-variant').addEventListener('click', () => row.remove());
        container.appendChild(row);
    }

    function gatherVariants(container) {
        return Array.from(container.querySelectorAll('.variant-row')).map((row, index) => ({
            name: row.querySelector('.variant-name').value.trim(),
            options: row.querySelector('.variant-options').value.trim(),
            is_required: row.querySelector('.variant-required').checked,
            position: index,
        })).filter(variant => variant.name.length > 0);
    }

    function populateParentSelects(categories) {
        const options = categories
            .map(cat => `<option value="${cat.id}">${cat.name}</option>`)
            .join('');

        const defaultOption = '<option value="">— Root Category —</option>';
        createParentSelect.innerHTML = defaultOption + options;
        editParentSelect.innerHTML = defaultOption + options;
    }

    function renderCategoriesTable(categories) {
        if (!categories.length) {
            categoriesTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                        <div class="flex flex-col items-center space-y-2">
                            <i class="fas fa-folder-open text-4xl text-gray-300"></i>
                            <p>No categories found. Create your first category above.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        categoriesTableBody.innerHTML = categories.map(category => `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-semibold mr-3">
                            ${category.name?.charAt(0)?.toUpperCase() ?? '?'}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">${category.name}</p>
                            ${category.image ? `<p class="text-xs text-blue-500 break-all"><a href="${category.image}" target="_blank" class="hover:underline">Image</a></p>` : ''}
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">${category.parent?.name ?? 'Root'}</td>
                <td class="px-6 py-4">
                    ${category.variants?.length
                        ? `<div class="flex flex-wrap gap-2">
                                ${category.variants.map(variant => `
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        ${variant.name}
                                        ${variant.is_required ? '<span class="text-red-500">*</span>' : ''}
                                        ${variant.options?.length ? `<span class="ml-1 text-gray-400">(${variant.options.length})</span>` : ''}
                                    </span>
                                `).join('')}
                           </div>`
                        : '<span class="text-sm text-gray-400">No variants</span>'
                    }
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">${formatDate(category.updated_at)}</td>
                <td class="px-6 py-4 text-right space-x-2">
                    <button onclick="openEditModal(${category.id})" class="inline-flex items-center px-3 py-1.5 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 text-sm">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                    <button onclick="deleteCategory(${category.id})" class="inline-flex items-center px-3 py-1.5 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 text-sm">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function formatDate(dateString) {
        if (!dateString) return '—';
        return new Date(dateString).toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }

    async function loadCategories(showToastOnSuccess = false) {
        try {
            const response = await fetchWithAuth('/admin/categories?all=true');
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to load categories');
            }
            categoriesState.list = result.data || [];
            populateParentSelects(categoriesState.list);
            renderCategoriesTable(categoriesState.list);
            if (showToastOnSuccess) {
                showToast('Categories refreshed');
            }
        } catch (error) {
            console.error(error);
            showToast(error.message, 'error');
            categoriesTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-red-500">
                        ${error.message}
                    </td>
                </tr>
            `;
        }
    }

    createForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('name', document.getElementById('create-name').value.trim());
        if (createParentSelect.value) {
            formData.append('parent_id', createParentSelect.value);
        }
        const image = document.getElementById('create-image').files[0];
        if (image) {
            formData.append('image', image);
        }

        const variants = gatherVariants(createVariantsContainer);
        variants.forEach((variant, index) => {
            formData.append(`variants[${index}][name]`, variant.name);
            formData.append(`variants[${index}][options]`, variant.options);
            formData.append(`variants[${index}][is_required]`, variant.is_required ? 1 : 0);
            formData.append(`variants[${index}][position]`, index);
        });

        try {
            const response = await fetchWithAuth('/admin/categories', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to create category');
            }
            showToast('Category created successfully');
            createForm.reset();
            createVariantsContainer.innerHTML = '';
            loadCategories();
        } catch (error) {
            console.error(error);
            showToast(error.message, 'error');
        }
    });

    async function deleteCategory(id) {
        if (!confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetchWithAuth(`/admin/categories/${id}`, { method: 'DELETE' });
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to delete category');
            }
            showToast('Category deleted');
            loadCategories();
        } catch (error) {
            console.error(error);
            showToast(error.message, 'error');
        }
    }

    window.deleteCategory = deleteCategory;

    function openEditModal(categoryId) {
        const category = categoriesState.list.find(cat => cat.id === categoryId);
        if (!category) {
            showToast('Category not found', 'error');
            return;
        }

        categoriesState.selectedId = category.id;
        document.getElementById('edit-name').value = category.name ?? '';

        const options = categoriesState.list
            .filter(cat => cat.id !== category.id)
            .map(cat => `<option value="${cat.id}" ${category.parent_id === cat.id ? 'selected' : ''}>${cat.name}</option>`)
            .join('');
        editParentSelect.innerHTML = '<option value="">— Root Category —</option>' + options;

        document.getElementById('current-image').textContent = category.image ? `Current image: ${category.image}` : 'No image uploaded';
        editVariantsContainer.innerHTML = '';
        (category.variants || []).forEach(variant => addVariantRow(editVariantsContainer, variant));
        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
    }

    window.openEditModal = openEditModal;

    function closeEditModal() {
        editModal.classList.add('hidden');
        editModal.classList.remove('flex');
        categoriesState.selectedId = null;
        editVariantsContainer.innerHTML = '';
        editForm.reset();
    }

    window.closeEditModal = closeEditModal;

    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!categoriesState.selectedId) {
            return;
        }

        const formData = new FormData();
        formData.append('name', document.getElementById('edit-name').value.trim());

        if (editParentSelect.value) {
            formData.append('parent_id', editParentSelect.value);
        } else {
            formData.append('parent_id', '');
        }

        const image = document.getElementById('edit-image').files[0];
        if (image) {
            formData.append('image', image);
        }

        const variants = gatherVariants(editVariantsContainer);
        variants.forEach((variant, index) => {
            formData.append(`variants[${index}][name]`, variant.name);
            formData.append(`variants[${index}][options]`, variant.options);
            formData.append(`variants[${index}][is_required]`, variant.is_required ? 1 : 0);
            formData.append(`variants[${index}][position]`, index);
        });

        try {
            const response = await fetchWithAuth(`/admin/categories/${categoriesState.selectedId}`, {
                method: 'POST',
                headers: { 'X-HTTP-Method-Override': 'PUT' },
                body: formData,
            });
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to update category');
            }
            showToast('Category updated successfully');
            closeEditModal();
            loadCategories();
        } catch (error) {
            console.error(error);
            showToast(error.message, 'error');
        }
    });

    addCreateVariantBtn.addEventListener('click', () => addVariantRow(createVariantsContainer));
    addEditVariantBtn.addEventListener('click', () => addVariantRow(editVariantsContainer));
    refreshBtn.addEventListener('click', () => loadCategories(true));

    loadCategories();
</script>
@endsection

