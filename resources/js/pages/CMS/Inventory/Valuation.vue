<script setup lang="ts">
import { ref, computed } from 'vue'
import { DocumentChartBarIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface ValuationRecord {
  id: number
  material: { id: number; name: string; code: string; unit: string }
  location: { id: number; name: string }
  valuation_date: string
  quantity: number
  unit_cost: number
  total_value: number
  valuation_method: string
}

interface Props {
  valuations: { data: ValuationRecord[] }
  locations: { id: number; name: string }[]
}

const props = defineProps<Props>()
const selectedLocation = ref('')
const selectedMethod = ref('')

const filteredValuations = computed(() => {
  let items = props.valuations.data
  if (selectedLocation.value) {
    items = items.filter(v => v.location.id === Number(selectedLocation.value))
  }
  if (selectedMethod.value) {
    items = items.filter(v => v.valuation_method === selectedMethod.value)
  }
  return items
})

const totalValue = computed(() =>
  filteredValuations.value.reduce((sum, v) => sum + v.total_value, 0)
)

const formatNumber = (value: number) =>
  new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value)

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', {
  year: 'numeric', month: 'short', day: 'numeric',
})
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Stock Valuation</h1>
        <p class="mt-1 text-sm text-gray-500">Historical stock valuation records (FIFO/LIFO/Average)</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 text-sm">
          <span class="font-medium text-blue-700">Total Value: K{{ formatNumber(totalValue) }}</span>
        </div>
      </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6 p-4">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="w-full sm:w-64">
          <select v-model="selectedLocation" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">All Locations</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
          </select>
        </div>
        <div class="w-full sm:w-48">
          <select v-model="selectedMethod" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">All Methods</option>
            <option value="fifo">FIFO</option>
            <option value="lifo">LIFO</option>
            <option value="average">Average</option>
          </select>
        </div>
      </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Value</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Method</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="val in filteredValuations" :key="val.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ val.material.name }}</div>
                <div class="text-xs text-gray-500">{{ val.material.code }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ val.location.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(val.valuation_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ val.quantity }} {{ val.material.unit }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">K{{ formatNumber(val.unit_cost) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">K{{ formatNumber(val.total_value) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  val.valuation_method === 'fifo' ? 'bg-blue-100 text-blue-700' :
                  val.valuation_method === 'lifo' ? 'bg-purple-100 text-purple-700' :
                  'bg-green-100 text-green-700'
                ]">
                  {{ val.valuation_method.toUpperCase() }}
                </span>
              </td>
            </tr>
            <tr v-if="filteredValuations.length === 0">
              <td colspan="7" class="px-6 py-12 text-center">
                <DocumentChartBarIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                <p class="mt-2 text-sm text-gray-500">No valuation records found</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
