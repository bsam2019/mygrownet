<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-900">
        Loyalty Growth Reward Management
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <p class="text-sm font-medium text-gray-500">Qualified Members</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">
                {{ stats.total_qualified }}
              </p>
            </div>
          </div>

          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <p class="text-sm font-medium text-gray-500">Active Cycles</p>
              <p class="mt-2 text-3xl font-bold text-blue-600">
                {{ stats.active_cycles }}
              </p>
            </div>
          </div>

          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <p class="text-sm font-medium text-gray-500">Completed Cycles</p>
              <p class="mt-2 text-3xl font-bold text-green-600">
                {{ stats.completed_cycles }}
              </p>
            </div>
          </div>

          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <p class="text-sm font-medium text-gray-500">Total Paid Out</p>
              <p class="mt-2 text-3xl font-bold text-emerald-600">
                K{{ stats.total_paid_out.toFixed(2) }}
              </p>
            </div>
          </div>

          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <p class="text-sm font-medium text-gray-500">Pool Balance</p>
              <p class="mt-2 text-3xl font-bold text-indigo-600">
                K{{ stats.current_pool_balance.toFixed(2) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid gap-6 sm:grid-cols-3">
          <Link
            :href="route('admin.lgr.cycles')"
            class="block overflow-hidden bg-white shadow-sm transition hover:shadow-md sm:rounded-lg"
          >
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900">Manage Cycles</h3>
              <p class="mt-2 text-sm text-gray-600">
                View and manage all member reward cycles
              </p>
            </div>
          </Link>

          <Link
            :href="route('admin.lgr.pool')"
            class="block overflow-hidden bg-white shadow-sm transition hover:shadow-md sm:rounded-lg"
          >
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900">Pool Management</h3>
              <p class="mt-2 text-sm text-gray-600">
                Monitor pool balance and contributions
              </p>
            </div>
          </Link>

          <Link
            :href="route('admin.lgr.settings')"
            class="block overflow-hidden bg-white shadow-sm transition hover:shadow-md sm:rounded-lg"
          >
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900">System Settings</h3>
              <p class="mt-2 text-sm text-gray-600">
                Configure LGR system parameters
              </p>
            </div>
          </Link>
        </div>

        <!-- Recent Cycles -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Recent Cycles</h3>
            <div class="mt-4 overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                      Member
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                      Status
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                      Progress
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                      Earned
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                      Dates
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="cycle in recentCycles" :key="cycle.id">
                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                      <div>
                        <div class="font-medium text-gray-900">{{ cycle.user.name }}</div>
                        <div class="text-gray-500">{{ cycle.user.email }}</div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                      <span
                        :class="[
                          'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                          cycle.status === 'active'
                            ? 'bg-blue-100 text-blue-800'
                            : cycle.status === 'completed'
                              ? 'bg-green-100 text-green-800'
                              : 'bg-gray-100 text-gray-800',
                        ]"
                      >
                        {{ cycle.status }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                      <div class="flex items-center">
                        <span class="mr-2 text-gray-900">{{ cycle.active_days }}/70</span>
                        <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
                          <div
                            class="h-full bg-blue-600"
                            :style="{ width: cycle.completion_rate + '%' }"
                          ></div>
                        </div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-green-600">
                      K{{ cycle.total_earned_lgc.toFixed(2) }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                      {{ cycle.start_date }} - {{ cycle.end_date }}
                    </td>
                  </tr>
                </tbody>
              </table>
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
  stats: {
    total_qualified: number;
    active_cycles: number;
    completed_cycles: number;
    total_paid_out: number;
    current_pool_balance: number;
  };
  recentCycles: Array<any>;
  poolHistory: Array<any>;
}

defineProps<Props>();
</script>

