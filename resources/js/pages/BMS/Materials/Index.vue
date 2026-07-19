<template>
  <CMSLayout title="Materials">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Materials</h1>
          <p class="mt-1 text-sm text-gray-500">Manage your material catalog and pricing</p>
        </div>
        <div class="flex items-center gap-3">
          <Link
            :href="route('cms.material-categories.index')"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
          >
            <FolderIcon class="h-5 w-5 mr-2" aria-hidden="true" />
            Manage Categories
          </Link>
          <Link
            :href="route('cms.materials.create')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
            Add Material
          </Link>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search materials..."
              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select
              v-model="filters.category_id"
              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
              @change="applyFilters"
            >
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.active"
              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
              @change="applyFilters"
            >
              <option value="">All</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Materials Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Material
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Category
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Unit
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Current Price
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Supplier
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="material in materials.data" :key="material.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-900">{{ material.name }}</span>
                    <span class="text-xs text-gray-500">{{ material.code }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span v-if="material.category" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    {{ material.category.name }}
                  </span>
                  <span v-else class="text-sm text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ material.unit }}
                </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                  K {{ parseFloat(material.current_price).toFixed(2) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ material.supplier || '-' }}
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      material.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ material.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                  <Link
                    :href="route('cms.materials.edit', material.id)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Edit
                  </Link>
                  <Link
                    :href="route('cms.materials.price-history', material.id)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    History
                  </Link>
                </td>
              </tr>
              <tr v-if="materials.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                  <CubeIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                  <p class="mt-2">No materials found</p>
                  <Link
                    :href="route('cms.materials.create')"
                    class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-700"
                  >
                    <PlusIcon class="h-5 w-5 mr-1" aria-hidden="true" />
                    Add your first material
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="materials.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <Pagination :links="materials.links" />
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { PlusIcon, CubeIcon, FolderIcon } from '@heroicons/vue/24/outline'
import { debounce } from 'lodash'

const props = defineProps({
  materials: Object,
  categories: Array,
  filters: Object,
})

const filters = reactive({
  search: props.filters.search || '',
  category_id: props.filters.category_id || '',
  active: props.filters.active || '',
})

const applyFilters = () => {
  router.get(route('cms.materials.index'), filters, {
    preserveState: true,
    preserveScroll: true,
  })
}

const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const clearFilters = () => {
  filters.search = ''
  filters.category_id = ''
  filters.active = ''
  applyFilters()
}
</script>
