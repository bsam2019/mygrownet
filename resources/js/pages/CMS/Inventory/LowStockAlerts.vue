<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ExclamationTriangleIcon, ArrowRightIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface Props {
  alerts: any[]
}

const props = defineProps<Props>()

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getStockPercentage = (current: number, minimum: number) => {
  if (minimum === 0) return 100
  return Math.round((current / minimum) * 100)
}

const getAlertClass = (percentage: number) => {
  if (percentage <= 25) return 'bg-red-100 text-red-800'
  if (percentage <= 50) return 'bg-orange-100 text-orange-800'
  return 'bg-yellow-100 text-yellow-800'
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-6">
      <Link :href="route('cms.inventory.index')" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">
        ← Back to Inventory
      </Link>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Low Stock Alerts</h1>
          <p class="text-sm text-gray-500">Items that need restocking</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-800">
            {{ alerts.length }} Active Alert{{ alerts.length !== 1 ? 's' : '' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Alerts List -->
    <div v-if="alerts.length > 0" class="bg-white rounded-lg shadow">
      <div class="divide-y divide-gray-200">
        <div
          v-for="alert in alerts"
          :key="alert.id"
          class="p-6 hover:bg-gray-50 transition-colors"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-start gap-4 flex-1">
              <div class="flex-shrink-0">
                <ExclamationTriangleIcon class="h-8 w-8 text-red-600" aria-hidden="true" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="text-lg font-semibold text-gray-900">
                    {{ alert.inventory_item.name }}
                  </h3>
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      getAlertClass(getStockPercentage(alert.current_stock, alert.minimum_stock))
                    ]"
                  >
                    {{ getStockPercentage(alert.current_stock, alert.minimum_stock) }}% of minimum
                  </span>
                </div>
                <p class="text-sm text-gray-600 mb-3">
                  {{ alert.inventory_item.item_code }} • {{ alert.inventory_item.category }}
                </p>
                
                <div class="grid grid-cols-3 gap-4 mb-3">
                  <div>
                    <p class="text-xs text-gray-500">Current Stock</p>
                    <p class="text-lg font-bold text-red-600">
                      {{ alert.current_stock }}
                    </p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-500">Minimum Stock</p>
                    <p class="text-lg font-semibold text-gray-900">
                      {{ alert.minimum_stock }}
                    </p>
                  </div>
                  <div v-if="alert.inventory_item.reorder_quantity">
                    <p class="text-xs text-gray-500">Reorder Quantity</p>
                    <p class="text-lg font-semibold text-blue-600">
                      {{ alert.inventory_item.reorder_quantity }}
                    </p>
                  </div>
                </div>

                <div class="flex items-center gap-4 text-sm text-gray-500">
                  <span>Alert created: {{ formatDate(alert.created_at) }}</span>
                  <span v-if="alert.inventory_item.supplier">
                    Supplier: {{ alert.inventory_item.supplier }}
                  </span>
                </div>
              </div>
            </div>
            <div class="flex-shrink-0 ml-4">
              <Link
                :href="route('cms.inventory.show', alert.inventory_item.id)"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
              >
                View Item
                <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-lg shadow p-12 text-center">
      <ExclamationTriangleIcon class="h-16 w-16 text-gray-300 mx-auto mb-4" aria-hidden="true" />
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No Low Stock Alerts</h3>
      <p class="text-gray-500 mb-6">
        All inventory items are above their minimum stock levels
      </p>
      <Link
        :href="route('cms.inventory.index')"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
      >
        View All Inventory
      </Link>
    </div>
  </div>
</template>
