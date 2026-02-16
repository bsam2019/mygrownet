<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { CubeIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface Props {
  items: any
  categories: string[]
  lowStockCount: number
  filters: any
}

defineProps<Props>()

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Inventory</h1>
        <p class="mt-1 text-sm text-gray-500">Manage your inventory items and stock levels</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <Link
          :href="route('cms.inventory.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
        >
          Add Item
        </Link>
      </div>
    </div>

    <!-- Low Stock Alert -->
    <div v-if="lowStockCount > 0" class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
      <div class="flex items-center gap-3">
        <ExclamationTriangleIcon class="h-6 w-6 text-amber-600 flex-shrink-0" aria-hidden="true" />
        <div class="flex-1">
          <p class="text-sm font-medium text-amber-900">
            {{ lowStockCount }} item{{ lowStockCount > 1 ? 's' : '' }} running low on stock
          </p>
        </div>
        <Link
          :href="route('cms.inventory.low-stock-alerts')"
          class="text-sm font-medium text-amber-700 hover:text-amber-800"
        >
          View Alerts →
        </Link>
      </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Code</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in items.data" :key="item.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <Link
                  :href="route('cms.inventory.show', item.id)"
                  class="text-sm font-medium text-blue-600 hover:text-blue-800"
                >
                  {{ item.item_code }}
                </Link>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                <div v-if="item.description" class="text-sm text-gray-500 truncate max-w-xs">
                  {{ item.description }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.category }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <span :class="[
                    'text-sm font-medium',
                    item.current_stock <= item.minimum_stock ? 'text-red-600' : 'text-gray-900'
                  ]">
                    {{ item.current_stock }} {{ item.unit }}
                  </span>
                  <ExclamationTriangleIcon
                    v-if="item.current_stock <= item.minimum_stock"
                    class="h-4 w-4 text-amber-500"
                    aria-hidden="true"
                  />
                </div>
                <div class="text-xs text-gray-500">Min: {{ item.minimum_stock }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                K{{ formatNumber(item.unit_cost) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <Link
                  :href="route('cms.inventory.show', item.id)"
                  class="text-blue-600 hover:text-blue-800 font-medium"
                >
                  View
                </Link>
              </td>
            </tr>
            <tr v-if="items.data.length === 0">
              <td colspan="6" class="px-6 py-12 text-center">
                <CubeIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                <p class="mt-2 text-sm text-gray-500">No inventory items yet</p>
                <Link
                  :href="route('cms.inventory.create')"
                  class="mt-3 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700"
                >
                  Add your first item →
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
