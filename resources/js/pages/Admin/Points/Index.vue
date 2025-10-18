<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Points System Management</h1>
          <p class="mt-2 text-gray-600">Monitor and manage the MyGrowNet points system</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Total LP Awarded</p>
                <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.total_lp_awarded) }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Total MAP Awarded</p>
                <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.total_map_awarded) }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Qualified This Month</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats.qualified_users_this_month }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Users with Points</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_users_with_points }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Average LP</p>
                <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.average_lp) }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-pink-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Average MAP</p>
                <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.average_map) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Level Distribution -->
        <div class="bg-white rounded-lg shadow mb-8">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Level Distribution</h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
              <div v-for="(count, level) in levelDistribution" :key="level" class="text-center">
                <div class="text-3xl font-bold" :class="getLevelColor(level)">{{ count }}</div>
                <div class="text-sm text-gray-600 capitalize mt-1">{{ level }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow mb-8">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Link :href="route('admin.points.users')" class="flex items-center justify-center px-6 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Manage User Points
              </Link>

              <button @click="showBulkAwardModal = true" class="flex items-center justify-center px-6 py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Bulk Award Points
              </button>

              <a :href="route('admin.points.statistics')" class="flex items-center justify-center px-6 py-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                View Statistics
              </a>
            </div>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Recent Transactions</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">LP</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">MAP</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="transaction in recentTransactions" :key="transaction.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Link :href="route('admin.points.show', transaction.user.id)" class="text-blue-600 hover:text-blue-800">
                      {{ transaction.user.name }}
                    </Link>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full" :class="getSourceBadgeClass(transaction.source)">
                      {{ formatSource(transaction.source) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap" :class="transaction.lp_amount >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ transaction.lp_amount >= 0 ? '+' : '' }}{{ transaction.lp_amount }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap" :class="transaction.map_amount >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ transaction.map_amount >= 0 ? '+' : '' }}{{ transaction.map_amount }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">{{ transaction.description }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(transaction.created_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Award Modal -->
    <div v-if="showBulkAwardModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showBulkAwardModal = false">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">Bulk Award Points</h3>
          <button @click="showBulkAwardModal = false" class="text-gray-400 hover:text-gray-500">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="submitBulkAward">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Select Users</label>
              <p class="text-sm text-gray-500 mb-2">Go to "Manage User Points" to select specific users, or use filters there.</p>
              <p class="text-sm text-red-600">Note: This feature requires user selection from the Users page.</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Lifetime Points (LP)</label>
              <input
                v-model="bulkAwardForm.lp_amount"
                type="number"
                min="0"
                max="10000"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                required
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Activity Points (MAP)</label>
              <input
                v-model="bulkAwardForm.map_amount"
                type="number"
                min="0"
                max="10000"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                required
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
              <textarea
                v-model="bulkAwardForm.reason"
                rows="3"
                maxlength="500"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                required
              ></textarea>
            </div>
          </div>
          
          <div class="mt-6 flex justify-end gap-3">
            <button
              type="button"
              @click="showBulkAwardModal = false"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
            >
              Award Points
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  stats: Object,
  levelDistribution: Object,
  recentTransactions: Array,
});

const showBulkAwardModal = ref(false);

const bulkAwardForm = reactive({
  user_ids: [],
  lp_amount: 0,
  map_amount: 0,
  reason: '',
});

const submitBulkAward = () => {
  if (bulkAwardForm.user_ids.length === 0) {
    alert('Please select users from the "Manage User Points" page first.');
    return;
  }
  
  router.post(route('admin.points.bulk-award'), bulkAwardForm, {
    onSuccess: () => {
      showBulkAwardModal.value = false;
      bulkAwardForm.lp_amount = 0;
      bulkAwardForm.map_amount = 0;
      bulkAwardForm.reason = '';
    },
  });
};

const formatNumber = (num) => {
  return Math.round(num).toLocaleString();
};

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};

const formatSource = (source) => {
  return source.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getLevelColor = (level) => {
  const colors = {
    associate: 'text-gray-600',
    professional: 'text-blue-600',
    senior: 'text-emerald-600',
    manager: 'text-purple-600',
    director: 'text-indigo-600',
    executive: 'text-pink-600',
    ambassador: 'text-amber-600',
  };
  return colors[level] || 'text-gray-600';
};

const getSourceBadgeClass = (source) => {
  const classes = {
    referral: 'bg-blue-100 text-blue-800',
    product_sale: 'bg-green-100 text-green-800',
    course_completion: 'bg-purple-100 text-purple-800',
    daily_login: 'bg-yellow-100 text-yellow-800',
    admin_award: 'bg-red-100 text-red-800',
    admin_deduction: 'bg-red-100 text-red-800',
  };
  return classes[source] || 'bg-gray-100 text-gray-800';
};
</script>
