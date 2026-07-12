<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { MapPinIcon, PlusIcon, BuildingOffice2Icon, WrenchScrewdriverIcon, TruckIcon, DocumentTextIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface Location {
  id: number
  name: string
  code: string
  type: string
  address: string | null
  manager: { id: number; name: string } | null
  is_active: boolean
  created_at: string
  stock_levels_count?: number
}

interface Props {
  locations: Location[]
}

const props = defineProps<Props>()

const showCreateModal = ref(false)

const form = useForm({
  name: '',
  code: '',
  type: 'warehouse',
  address: '',
  manager_id: '' as string | number,
  is_active: true,
})

const typeIcons: Record<string, any> = {
  warehouse: BuildingOffice2Icon,
  workshop: WrenchScrewdriverIcon,
  site: MapPinIcon,
  vehicle: TruckIcon,
  other: DocumentTextIcon,
}

const typeColors: Record<string, string> = {
  warehouse: 'bg-blue-100 text-blue-700',
  workshop: 'bg-orange-100 text-orange-700',
  site: 'bg-green-100 text-green-700',
  vehicle: 'bg-purple-100 text-purple-700',
  other: 'bg-gray-100 text-gray-700',
}

const submitForm = () => {
  form.post(route('cms.inventory.locations.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    },
  })
}

const typeLabel = (type: string) => type.charAt(0).toUpperCase() + type.slice(1)
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Stock Locations</h1>
        <p class="mt-1 text-sm text-gray-500">Manage warehouses, workshops, and site locations</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <button
          @click="showCreateModal = true"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Add Location
        </button>
      </div>
    </div>

    <div v-if="locations.length === 0" class="bg-white shadow-sm rounded-lg border border-gray-200 p-12 text-center">
      <BuildingOffice2Icon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
      <p class="mt-2 text-sm text-gray-500">No locations yet</p>
      <button @click="showCreateModal = true" class="mt-3 text-sm font-medium text-blue-600 hover:text-blue-700">
        Add your first location →
      </button>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="location in locations"
        :key="location.id"
        class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow"
      >
        <div class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div :class="['p-2 rounded-lg', typeColors[location.type] || 'bg-gray-100']">
                <component :is="typeIcons[location.type] || DocumentTextIcon" class="h-6 w-6" aria-hidden="true" />
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ location.name }}</h3>
                <p class="text-sm text-gray-500">{{ location.code }}</p>
              </div>
            </div>
            <span
              :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                location.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
              ]"
            >
              {{ location.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>

          <div class="space-y-2 text-sm">
            <div class="flex items-center gap-2 text-gray-600">
              <span class="font-medium">Type:</span>
              <span>{{ typeLabel(location.type) }}</span>
            </div>
            <div v-if="location.address" class="flex items-start gap-2 text-gray-600">
              <span class="font-medium whitespace-nowrap">Address:</span>
              <span>{{ location.address }}</span>
            </div>
            <div v-if="location.manager" class="flex items-center gap-2 text-gray-600">
              <span class="font-medium">Manager:</span>
              <span>{{ location.manager.name }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Location Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateModal = false" />
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="submitForm">
            <div class="bg-white px-6 py-5 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Add Stock Location</h3>
            </div>
            <div class="px-6 py-4 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location Name *</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="e.g. Main Warehouse"
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location Code *</label>
                <input
                  v-model="form.code"
                  type="text"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="e.g. WH-001"
                />
                <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                <select
                  v-model="form.type"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="warehouse">Warehouse</option>
                  <option value="workshop">Workshop</option>
                  <option value="site">Site</option>
                  <option value="vehicle">Vehicle</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea
                  v-model="form.address"
                  rows="2"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Optional address"
                />
              </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex items-center justify-end gap-3">
              <button
                type="button"
                @click="showCreateModal = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                {{ form.processing ? 'Saving...' : 'Save Location' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
