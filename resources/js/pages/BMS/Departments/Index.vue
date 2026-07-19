<template>
  <CMSLayout title="Departments">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Departments</h1>
          <p class="mt-1 text-sm text-gray-500">Manage company departments and organizational structure</p>
        </div>
        <Link
          :href="route('cms.departments.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Department
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              @input="applyFilters"
              type="text"
              placeholder="Search departments..."
              class="w-full rounded-lg border-gray-300"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
            <select
              v-model="filters.branch_id"
              @change="applyFilters"
              class="w-full rounded-lg border-gray-300"
            >
              <option value="">All Branches</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                {{ branch.branch_name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Departments Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employees</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="dept in departments.data" :key="dept.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ dept.department_code }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ dept.department_name }}</div>
                  <div v-if="dept.description" class="text-sm text-gray-500 truncate max-w-xs">
                    {{ dept.description }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ dept.branch?.branch_name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ dept.manager?.name || 'Not assigned' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ dept.workers_count || 0 }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="{
                    'bg-green-100 text-green-800': dept.is_active,
                    'bg-gray-100 text-gray-800': !dept.is_active,
                  }"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ dept.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm space-x-3">
                <Link
                  :href="route('cms.departments.edit', dept.id)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  Edit
                </Link>
                <button
                  @click="confirmDelete(dept)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="departments.data.length > 0" class="px-6 py-4 border-t">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
              Showing {{ departments.from }} to {{ departments.to }} of {{ departments.total }} results
            </div>
            <div class="flex gap-2">
              <Link
                v-for="link in departments.links"
                :key="link.label"
                :href="link.url"
                :class="{
                  'bg-blue-600 text-white': link.active,
                  'bg-white text-gray-700 hover:bg-gray-50': !link.active,
                  'opacity-50 cursor-not-allowed': !link.url,
                }"
                class="px-3 py-2 text-sm rounded-lg border"
                v-html="link.label"
              />
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="departments.data.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No departments</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new department.</p>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Delete Department</h3>
        <p class="text-sm text-gray-600 mb-6">
          Are you sure you want to delete <strong>{{ departmentToDelete?.department_name }}</strong>?
          This action cannot be undone.
        </p>
        <div class="flex gap-3 justify-end">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
          >
            Cancel
          </button>
          <button
            @click="deleteDepartment"
            :disabled="deleteForm.processing"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
          >
            {{ deleteForm.processing ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

interface Props {
  departments: any;
  branches: any[];
  filters: {
    search?: string;
    branch_id?: number;
  };
}

const props = defineProps<Props>();

const filters = ref({
  search: props.filters.search || '',
  branch_id: props.filters.branch_id || '',
});

const showDeleteModal = ref(false);
const departmentToDelete = ref<any>(null);

const deleteForm = useForm({});

const applyFilters = () => {
  router.get(route('cms.departments.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const confirmDelete = (department: any) => {
  departmentToDelete.value = department;
  showDeleteModal.value = true;
};

const deleteDepartment = () => {
  if (!departmentToDelete.value) return;
  
  deleteForm.delete(route('cms.departments.destroy', departmentToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false;
      departmentToDelete.value = null;
    },
  });
};
</script>
