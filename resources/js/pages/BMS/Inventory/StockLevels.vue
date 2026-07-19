<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ExclamationTriangleIcon, CubeIcon, MapPinIcon } from '@heroicons/vue/24/outline'
import BMSLayout from '@/Layouts/BMSLayout.vue'

defineOptions({
  layout: BMSLayout
})

interface StockLevel {
  id: number
  location: { id: number; name: string; type: string }
  material: { id: number; name: string; code: string; unit: string }
  quantity: number
  reserved_quantity: number
  available_quantity: number
  reorder_level: number | null
  max_level: number | null
  last_counted_date: string | null
}

interface Props {
  levels: { data: StockLevel[] }
  locations: { id: number; name: string }[]
}

const props = defineProps<Props>()
const selectedLocation = ref('')
const searchQuery = ref('')

const filteredLevels = computed(() => {
  let items = props.levels.data
  if (selectedLocation.value) {
    items = items.filter(l => l.location.id === Number(selectedLocation.value))
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    items = items.filter(l =>
      l.material.name.toLowerCase().includes(q) ||
      l.material.code?.toLowerCase().includes(q) ||
      l.location.name.toLowerCase().includes(q)
    )
  }
  return items
})

const stockValue = (level: StockLevel) => level.quantity * (level.material as any).unit_cost || 0

const formatNumber = (value: number) =>
  new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value)
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Stock Levels</h1>
        <p class="mt-1 text-sm text-gray-500">View stock quantities across all locations</p>
      </div>
      <div class="mt-4 sm:mt-0 flex items-center gap-3">
        <Link
          :href="route('bms.inventory.low-stock-alerts')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 text-sm font-medium"
        >
          <ExclamationTriangleIcon class="h-5 w-5" aria-hidden="true" />
          Low Stock Alerts
        </Link>
        <Link
          :href="route('bms.inventory.locations.index')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-100 text-sm font-medium"
        >
          <MapPinIcon class="h-5 w-5" aria-hidden="true" />
          Locations
        </Link>
      </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6 p-4">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search materials..."
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div class="w-full sm:w-64">
          <select
            v-model="selectedLocation"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Locations</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">On Hand</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reserved</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Available</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reorder</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock Value</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="level in filteredLevels" :key="level.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ level.material.name }}</div>
                <div class="text-xs text-gray-500">{{ level.material.code }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ level.location.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                {{ level.quantity }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                {{ level.reserved_quantity }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <span
                  :class="[
                    'text-sm font-medium',
                    level.available_quantity <= level.reorder_level ? 'text-red-600' : 'text-green-600'
                  ]"
                >
                  {{ level.available_quantity }}
                </span>
                <ExclamationTriangleIcon
                  v-if="level.available_quantity <= level.reorder_level"
                  class="inline h-4 w-4 text-amber-500 ml-1"
                  aria-hidden="true"
                />
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                {{ level.reorder_level ?? '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                K{{ formatNumber(stockValue(level)) }}
              </td>
            </tr>
            <tr v-if="filteredLevels.length === 0">
              <td colspan="7" class="px-6 py-12 text-center">
                <CubeIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                <p class="mt-2 text-sm text-gray-500">No stock levels found</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
