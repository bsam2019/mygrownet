<template>
  <AdminLayout>
    <Head title="Asset Inventory Management" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Asset Inventory Management</h1>
          <p class="text-gray-600">Manage physical rewards and asset inventory</p>
        </div>
        
        <div class="flex items-center space-x-4">
          <button 
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <PlusIcon class="-ml-1 mr-2 h-4 w-4" />
            Add Asset
          </button>
          
          <button 
            @click="exportInventory"
            :disabled="exporting"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <DocumentArrowDownIcon v-if="exporting" class="animate-spin -ml-1 mr-2 h-4 w-4" />
            <DocumentArrowDownIcon v-else class="-ml-1 mr-2 h-4 w-4" />
            Export
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <select 
                v-model="filters.category"
                @change="applyFilters"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category" :value="category">
                  {{ category }}
                </option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select 
                v-model="filters.status"
                @change="applyFilters"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="">All Statuses</option>
                <option value="available">Available</option>
                <option value="allocated">Allocated</option>
                <option value="maintenance">Maintenance</option>
                <option value="retired">Retired</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
              <input 
                v-model="filters.search"
                @input="debounceSearch"
                type="text"
                placeholder="Search assets..."
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            
            <div class="flex items-end">
              <button 
                @click="clearFilters"
                class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Clear Filters
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Assets Table -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <input 
                      type="checkbox" 
                      @change="toggleSelectAll"
                      :checked="selectedAssets.length === assets.data.length && assets.data.length > 0"
                      class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Asset
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Category
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Value
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Location
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr 
                  v-for="asset in assets.data" 
                  :key="asset.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input 
                      type="checkbox" 
                      :value="asset.id"
                      v-model="selectedAssets"
                      class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                          <BuildingOffice2Icon class="h-5 w-5 text-gray-500" />
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ asset.name }}</div>
                        <div class="text-sm text-gray-500">{{ asset.description }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ asset.category }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    K{{ formatNumber(asset.value) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      asset.status === 'available' ? 'bg-green-100 text-green-800' :
                      asset.status === 'allocated' ? 'bg-blue-100 text-blue-800' :
                      asset.status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-gray-100 text-gray-800'
                    ]">
                      {{ asset.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ asset.location || 'Not specified' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center space-x-2">
                      <button 
                        @click="viewAsset(asset)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        <EyeIcon class="h-4 w-4" />
                      </button>
                      <button 
                        @click="editAsset(asset)"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        <PencilIcon class="h-4 w-4" />
                      </button>
                      <button 
                        v-if="asset.status === 'available'"
                        @click="allocateAsset(asset)"
                        class="text-green-600 hover:text-green-900"
                      >
                        <UserPlusIcon class="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div v-if="assets.links" class="mt-6">
            <nav class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <Link 
                  v-if="assets.prev_page_url"
                  :href="assets.prev_page_url"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                  Previous
                </Link>
                <Link 
                  v-if="assets.next_page_url"
                  :href="assets.next_page_url"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                  Next
                </Link>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Showing {{ assets.from }} to {{ assets.to }} of {{ assets.total }} results
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <Link 
                      v-for="link in assets.links" 
                      :key="link.label"
                      :href="link.url"
                      v-html="link.label"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        link.active 
                          ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' 
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                      ]"
                    />
                  </nav>
                </div>
              </div>
            </nav>
          </div>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedAssets.length > 0" class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              {{ selectedAssets.length }} asset(s) selected
            </div>
            <div class="flex items-center space-x-4">
              <button 
                @click="bulkUpdateStatus('maintenance')"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Mark as Maintenance
              </button>
              <button 
                @click="bulkUpdateStatus('available')"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Mark as Available
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Asset Modal -->
    <AssetModal 
      :show="showCreateModal || showEditModal"
      :asset="selectedAsset"
      @close="closeModal"
      @saved="handleAssetSaved"
    />

    <!-- Asset Details Modal -->
    <AssetDetailsModal 
      :show="showDetailsModal"
      :asset="selectedAsset"
      @close="showDetailsModal = false"
    />

    <!-- Allocation Modal -->
    <AllocationModal 
      :show="showAllocationModal"
      :asset="selectedAsset"
      @close="showAllocationModal = false"
      @allocated="handleAssetAllocated"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import AssetModal from '@/components/Admin/Assets/AssetModal.vue'
import AssetDetailsModal from '@/components/Admin/Assets/AssetDetailsModal.vue'
import AllocationModal from '@/components/Admin/Assets/AllocationModal.vue'
import {
  BuildingOffice2Icon,
  DocumentArrowDownIcon,
  EyeIcon,
  PencilIcon,
  PlusIcon,
  UserPlusIcon,
} from '@heroicons/vue/24/outline'

interface Props {
  assets: any
  categories: string[]
  filters: any
}

const props = defineProps<Props>()

const selectedAssets = ref<number[]>([])
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDetailsModal = ref(false)
const showAllocationModal = ref(false)
const selectedAsset = ref(null)
const exporting = ref(false)

const filters = reactive({
  category: props.filters.category || '',
  status: props.filters.status || '',
  search: props.filters.search || ''
})

let searchTimeout: NodeJS.Timeout

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(value)
}

const applyFilters = () => {
  router.get(route('admin.assets.inventory'), filters, {
    preserveState: true,
    replace: true
  })
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const clearFilters = () => {
  filters.category = ''
  filters.status = ''
  filters.search = ''
  applyFilters()
}

const toggleSelectAll = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.checked) {
    selectedAssets.value = props.assets.data.map((asset: any) => asset.id)
  } else {
    selectedAssets.value = []
  }
}

const viewAsset = (asset: any) => {
  selectedAsset.value = asset
  showDetailsModal.value = true
}

const editAsset = (asset: any) => {
  selectedAsset.value = asset
  showEditModal.value = true
}

const allocateAsset = (asset: any) => {
  selectedAsset.value = asset
  showAllocationModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedAsset.value = null
}

const handleAssetSaved = () => {
  closeModal()
  router.reload({ only: ['assets'] })
}

const handleAssetAllocated = () => {
  showAllocationModal.value = false
  selectedAsset.value = null
  router.reload({ only: ['assets'] })
}

const bulkUpdateStatus = async (status: string) => {
  try {
    const response = await fetch(route('admin.assets.bulk-update-assets'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        asset_ids: selectedAssets.value,
        status: status
      })
    })
    
    const data = await response.json()
    
    if (data.success) {
      selectedAssets.value = []
      router.reload({ only: ['assets'] })
    } else {
      alert('Failed to update assets: ' + data.message)
    }
  } catch (error) {
    console.error('Error updating assets:', error)
    alert('An error occurred while updating assets')
  }
}

const exportInventory = async () => {
  exporting.value = true
  
  try {
    window.open(route('admin.assets.export-inventory', filters), '_blank')
  } catch (error) {
    console.error('Error exporting inventory:', error)
    alert('An error occurred while exporting inventory')
  } finally {
    exporting.value = false
  }
}
</script>