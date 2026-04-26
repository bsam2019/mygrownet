<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { PlusIcon, PencilIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Category {
  id: number;
  name: string;
  code: string;
  description: string | null;
  icon: string;
  color: string;
  sort_order: number;
  is_active: boolean;
  materials_count: number;
}

const props = defineProps<{
  categories: Category[];
}>();

const showModal = ref(false);
const editingCategory = ref<Category | null>(null);

const form = useForm({
  name: '',
  code: '',
  description: '',
  icon: 'cube',
  color: 'blue',
  sort_order: 0,
  is_active: true,
});

const openCreateModal = () => {
  editingCategory.value = null;
  form.reset();
  form.clearErrors();
  showModal.value = true;
};

const openEditModal = (category: Category) => {
  editingCategory.value = category;
  form.name = category.name;
  form.code = category.code || '';
  form.description = category.description || '';
  form.icon = category.icon || 'cube';
  form.color = category.color || 'blue';
  form.sort_order = category.sort_order || 0;
  form.is_active = category.is_active;
  form.clearErrors();
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingCategory.value = null;
  form.reset();
};

const submit = () => {
  if (editingCategory.value) {
    form.put(route('cms.material-categories.update', editingCategory.value.id), {
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route('cms.material-categories.store'), {
      onSuccess: () => closeModal(),
    });
  }
};

const deleteCategory = (category: Category) => {
  if (category.materials_count > 0) {
    alert(`Cannot delete category "${category.name}" because it has ${category.materials_count} materials. Please reassign or delete those materials first.`);
    return;
  }

  if (confirm(`Are you sure you want to delete the category "${category.name}"?`)) {
    router.delete(route('cms.material-categories.destroy', category.id));
  }
};

const colorOptions = [
  { value: 'gray', label: 'Gray', class: 'bg-gray-100 text-gray-800' },
  { value: 'blue', label: 'Blue', class: 'bg-blue-100 text-blue-800' },
  { value: 'green', label: 'Green', class: 'bg-green-100 text-green-800' },
  { value: 'yellow', label: 'Yellow', class: 'bg-yellow-100 text-yellow-800' },
  { value: 'red', label: 'Red', class: 'bg-red-100 text-red-800' },
  { value: 'purple', label: 'Purple', class: 'bg-purple-100 text-purple-800' },
  { value: 'pink', label: 'Pink', class: 'bg-pink-100 text-pink-800' },
  { value: 'indigo', label: 'Indigo', class: 'bg-indigo-100 text-indigo-800' },
  { value: 'orange', label: 'Orange', class: 'bg-orange-100 text-orange-800' },
];

const iconOptions = [
  'cube', 'cube-transparent', 'rectangle-stack', 'square-2-stack', 
  'wrench-screwdriver', 'beaker', 'bars-3-bottom-left', 'squares-2x2',
  'bolt', 'wrench', 'cog', 'hammer', 'shield-check'
];

const getColorClass = (color: string) => {
  return colorOptions.find(c => c.value === color)?.class || 'bg-gray-100 text-gray-800';
};
</script>

<template>
  <CMSLayout title="Material Categories">
    <Head title="Material Categories" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Material Categories</h1>
          <p class="mt-1 text-sm text-gray-500">Organize your materials into categories</p>
        </div>
        <div class="flex items-center gap-3">
          <Link
            :href="route('cms.materials.index')"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
          >
            Back to Materials
          </Link>
          <button
            @click="openCreateModal"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
            Add Category
          </button>
        </div>
      </div>

      <!-- Categories Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="category in categories"
          :key="category.id"
          class="bg-white rounded-lg shadow p-6 hover:shadow-md transition"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getColorClass(category.color)]">
                  {{ category.name }}
                </span>
                <span v-if="!category.is_active" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                  Inactive
                </span>
              </div>
              <p v-if="category.code" class="text-sm text-gray-500 mb-2">
                Code: {{ category.code }}
              </p>
              <p v-if="category.description" class="text-sm text-gray-600 mb-3">
                {{ category.description }}
              </p>
              <p class="text-sm font-medium text-gray-900">
                {{ category.materials_count }} {{ category.materials_count === 1 ? 'material' : 'materials' }}
              </p>
            </div>
            <div class="flex items-center gap-2 ml-4">
              <button
                @click="openEditModal(category)"
                class="p-2 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition"
                title="Edit category"
              >
                <PencilIcon class="h-5 w-5" aria-hidden="true" />
              </button>
              <button
                @click="deleteCategory(category)"
                class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition"
                :disabled="category.materials_count > 0"
                :class="{ 'opacity-50 cursor-not-allowed': category.materials_count > 0 }"
                title="Delete category"
              >
                <TrashIcon class="h-5 w-5" aria-hidden="true" />
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="categories.length === 0" class="col-span-full text-center py-12">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
            <PlusIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No categories yet</h3>
          <p class="text-sm text-gray-500 mb-4">Get started by creating your first material category.</p>
          <button
            @click="openCreateModal"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
            Add Category
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="submit">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                  {{ editingCategory ? 'Edit Category' : 'Add Category' }}
                </h3>
                <button type="button" @click="closeModal" class="text-gray-400 hover:text-gray-500">
                  <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                </button>
              </div>

              <div class="space-y-4">
                <!-- Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g., Aluminium Profiles"
                  />
                  <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <!-- Code -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                  <input
                    v-model="form.code"
                    type="text"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g., ALU_PROF"
                  />
                  <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
                </div>

                <!-- Description -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <textarea
                    v-model="form.description"
                    rows="2"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Optional description"
                  ></textarea>
                </div>

                <!-- Color -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                  <select
                    v-model="form.color"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                  >
                    <option v-for="color in colorOptions" :key="color.value" :value="color.value">
                      {{ color.label }}
                    </option>
                  </select>
                </div>

                <!-- Sort Order -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                  <input
                    v-model.number="form.sort_order"
                    type="number"
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    placeholder="0"
                  />
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 block text-sm text-gray-900">Active</label>
                </div>
              </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                {{ form.processing ? 'Saving...' : (editingCategory ? 'Update' : 'Create') }}
              </button>
              <button
                type="button"
                @click="closeModal"
                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>
