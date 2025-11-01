<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-900">
        Reward Pool Management
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Current Pool Status -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Current Pool Status</h3>
            <div class="mt-4 grid gap-4 sm:grid-cols-4">
              <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                <p class="text-sm text-blue-600">Available Balance</p>
                <p class="mt-2 text-2xl font-bold text-blue-900">
                  K{{ currentPool?.available_for_distribution?.toFixed(2) || '0.00' }}
                </p>
              </div>
              <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                <p class="text-sm text-green-600">Total Contributions</p>
                <p class="mt-2 text-2xl font-bold text-green-900">
                  K{{ currentPool?.contributions?.toFixed(2) || '0.00' }}
                </p>
              </div>
              <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm text-amber-600">Total Allocated</p>
                <p class="mt-2 text-2xl font-bold text-amber-900">
                  K{{ currentPool?.allocations?.toFixed(2) || '0.00' }}
                </p>
              </div>
              <div class="rounded-lg border border-purple-200 bg-purple-50 p-4">
                <p class="text-sm text-purple-600">Reserve Amount</p>
                <p class="mt-2 text-2xl font-bold text-purple-900">
                  K{{ currentPool?.reserve_amount?.toFixed(2) || '0.00' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Pool History -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Pool History (Last 30 Days)</h3>
            <div class="mt-4 overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Opening</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Contributions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Allocations</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Closing</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Available</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="pool in poolHistory" :key="pool.id">
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                      {{ pool.pool_date }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                      K{{ pool.opening_balance.toFixed(2) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-green-600">
                      +K{{ pool.contributions.toFixed(2) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-red-600">
                      -K{{ pool.allocations.toFixed(2) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                      K{{ pool.closing_balance.toFixed(2) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-blue-600">
                      K{{ pool.available_for_distribution.toFixed(2) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Settings -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Pool Settings</h3>
              <Link
                :href="route('admin.lgr.settings')"
                class="text-sm font-medium text-blue-600 hover:text-blue-800"
              >
                Edit Settings →
              </Link>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
              <div>
                <p class="text-sm text-gray-500">Reserve Percentage</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">
                  {{ settings.lgr_pool_reserve_percentage || 30 }}%
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Registration Fee Allocation</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">
                  {{ settings.lgr_registration_fee_percentage || 20 }}%
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Product Sales Allocation</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">
                  {{ settings.lgr_product_sale_percentage || 15 }}%
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Props {
  currentPool: any;
  poolHistory: Array<any>;
  settings: Record<string, any>;
}

defineProps<Props>();
</script>

