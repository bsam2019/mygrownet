<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import MarketplaceAdminLayout from '@/layouts/MarketplaceAdminLayout.vue';
import { FolderIcon, PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface Category {
  id: number;
  name: string;
  slug: string;
  icon?: string;
  description?: string;
  sort_order: number;
  is_active: boolean;
  products_count?: number;
}

interface Props {
  categories: Category[];
}

const props = defineProps<Props>();

const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedCategory = ref<Category | null>(null);

const createForm = useForm({
  name: '',
  slug: '',
  icon: '',
  description: '',
  sort_order: 0,
});

const editForm = useForm({
  name: '',
  slug: '',
  icon: '',
  description: '',
  sort_order: 0,
});

const openCreateModal = () => {
  createForm.reset();
  showCreateModal.value = true;
};

const createCategory = () => {
  createForm.post(route('admin.marketplace.categories.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
    },
  });
};

const openEditModal = (category: Category) => {
  selectedCategory.value = category;
  editForm.name = category.name;
  editForm.slug = category.slug;
  editForm.icon = category.icon || '';
  editForm.description = category.description || '';
  editForm.sort_order = category.sort_order;
  showEditModal.value = true;
};

const updateCategory = () => {
  if (!selectedCategory.value) return;
  
  editForm.put(route('admin.marketplace.categories.update', selectedCategory.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showEditModal.value = false;
      selectedCategory.value = null;
    },
  });
};

const deleteCategory = (category: Category) => {
  if (!confirm(`Delete category "${category.name}"? This action cannot be undone.`)) return;
  
  router.delete(route('admin.marketplace.categories.destroy', category.id), {
    preserveScroll: true,
  });
};

const generateSlug = (name: string) => {
  return name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
};

const onNameChange = (isEdit: boolean = false) => {
  const form = isEdit ? editForm : createForm;
  if (!form.slug || form.slug === generateSlug(form.name)) {
    form.slug = generateSlug(form.name);
  }
};
</script>

<template>
  <Head title="Categories Management - Admin" />

  <MarketplaceAdminLayout title="Categories Management">
    <!-- Header with Add Button -->
    <div class="flex justify-between items-center mb-6">
      <p class="text-gray-600">Manage product categories for the marketplace</p>
      <button
        @click="openCreateModal"
        class="flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
      >
        <PlusIcon class="h-5 w-5" aria-hidden="true" />
        Add Category
      </button>
    </div>

    <!-- Categories List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort Order</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div v-if="category.icon" class="text-2xl">{{ category.icon }}</div>
                  <FolderIcon v-else class="h-6 w-6 text-gray-400" aria-hidden="true" />
                  <div>
                    <p class="font-medium text-gray-900">{{ category.name }}</p>
                    <p v-if="category.description" class="text-sm text-gray-500">{{ category.description }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <code class="text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ category.slug }}</code>
              </td>
              <td class="px-6 py-4 text-gray-900">
                {{ category.products_count || 0 }}
              </td>
              <td class="px-6 py-4 text-gray-900">
                {{ category.sort_order }}
              </td>
              <td class="px-6 py-4">
                <span
                  :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                  class="px-2.5 py-1 rounded-full text-xs font-medium"
                >
                  {{ category.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="openEditModal(category)"
                    class="text-orange-600 hover:text-orange-700 text-sm font-medium"
                  >
                    <PencilIcon class="h-4 w-4" aria-hidden="true" />
                  </button>
                  <button
                    @click="deleteCategory(category)"
                    class="text-red-600 hover:text-red-700 text-sm font-medium"
                  >
                    <TrashIcon class="h-4 w-4" aria-hidden="true" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="categories.length === 0" class="text-center py-12">
        <FolderIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No categories</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new category</p>
        <button
          @click="openCreateModal"
          class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Add Category
        </button>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-lg w-full p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Create Category</h3>
        <form @submit.prevent="createCategory">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
              <input
                v-model="createForm.name"
                @input="onNameChange(false)"
                type="text"
                required
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              />
              <p v-if="createForm.errors.name" class="mt-1 text-sm text-red-600">{{ createForm.errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
              <input
                v-model="createForm.slug"
                type="text"
                required
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              />
              <p v-if="createForm.errors.slug" class="mt-1 text-sm text-red-600">{{ createForm.errors.slug }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Icon (Emoji)</label>
              <input
                v-model="createForm.icon"
                type="text"
                placeholder="📦"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="createForm.description"
                rows="3"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
              <input
                v-model.number="createForm.sort_order"
                type="number"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              />
            </div>
          </div>

          <div class="mt-6 flex gap-3 justify-end">
            <button
              type="button"
              @click="showCreateModal = false"
              :disabled="createForm.processing"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="createForm.processing"
              class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50"
            >
              {{ createForm.processing ? 'Creating...' : 'Create Category' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-lg w-full p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Edit Category</h3>
        <form @submit.prevent="updateCategory">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
              <input
                v-model="editForm.name"
                @input="onNameChange(true)"
                type="text"
                required
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              />
              <p v-if="editForm.errors.name" class="mt-1 text-sm text-red-600">{{ editForm.errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
              <input
                v-model="editForm.slug"
                type="text"
                required
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              />
              <p v-if="editForm.errors.slug" class="mt-1 text-sm text-red-600">{{ editForm.errors.slug }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Icon (Emoji)</label>
              <input
                v-model="editForm.icon"
                type="text"
                placeholder="📦"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="editForm.description"
                rows="3"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
              <input
                v-model.number="editForm.sort_order"
                type="number"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              />
            </div>
          </div>

          <div class="mt-6 flex gap-3 justify-end">
            <button
              type="button"
              @click="showEditModal = false"
              :disabled="editForm.processing"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="editForm.processing"
              class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50"
            >
              {{ editForm.processing ? 'Updating...' : 'Update Category' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </MarketplaceAdminLayout>
</template>
