<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Points Statistics</h1>
            <p class="mt-2 text-gray-600">Detailed analytics and insights</p>
          </div>
          
          <!-- Period Selector -->
          <div class="flex gap-2">
            <Link
              v-for="p in periods"
              :key="p.value"
              :href="route('admin.points.statistics', { period: p.value })"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium transition',
                period === p.value
                  ? 'bg-blue-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
              ]"
            >
              {{ p.label }}
            </Link>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">LP Awarded</p>
                <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.lp_awarded) }}</p>
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
                <p class="text-sm font-medium text-gray-500">MAP Awarded</p>
                <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.map_awarded) }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Transactions</p>
                <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.transactions_count) }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-amber-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="ml-5">
                <p class="text-sm font-medium text-gray-500">Unique Users</p>
                <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.unique_users) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Points by Source -->
        <div class="bg-white rounded-lg shadow mb-8">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Points by Source</h2>
          </div>
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">LP Awarded</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">MAP Awarded</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transactions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="source in bySource" :key="source.source" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getSourceBadgeClass(source.source)">
                        {{ formatSource(source.source) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ formatNumber(source.total_lp) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ formatNumber(source.total_map) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatNumber(source.count) }}
                    </td>
                  </tr>
                  <tr v-if="bySource.length === 0">
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                      No data available for this period
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Top Users -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Top Users by Points</h2>
          </div>
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">LP Earned</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">MAP Earned</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(userStat, index) in topUsers" :key="userStat.user_id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="text-lg font-bold" :class="getRankColor(index)">
                        #{{ index + 1 }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ userStat.user?.name || 'Unknown' }}</div>
                        <div class="text-sm text-gray-500">{{ userStat.user?.email || 'N/A' }}</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ formatNumber(userStat.total_lp) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ formatNumber(userStat.total_map) }}
                    </td>
                  </tr>
                  <tr v-if="topUsers.length === 0">
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                      No data available for this period
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

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  period: String,
  stats: Object,
  bySource: Array,
  dailyTrend: Array,
  topUsers: Array,
});

const periods = [
  { value: 'day', label: 'Today' },
  { value: 'week', label: 'This Week' },
  { value: 'month', label: 'This Month' },
  { value: 'year', label: 'This Year' },
];

const formatNumber = (num) => {
  return Math.round(num || 0).toLocaleString();
};

const formatSource = (source) => {
  return source.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getSourceBadgeClass = (source) => {
  const classes = {
    referral: 'bg-blue-100 text-blue-800',
    product_sale: 'bg-green-100 text-green-800',
    course_completion: 'bg-purple-100 text-purple-800',
    daily_login: 'bg-yellow-100 text-yellow-800',
    admin_award: 'bg-red-100 text-red-800',
    admin_bulk_award: 'bg-red-100 text-red-800',
    admin_deduction: 'bg-red-100 text-red-800',
  };
  return classes[source] || 'bg-gray-100 text-gray-800';
};

const getRankColor = (index) => {
  if (index === 0) return 'text-yellow-500'; // Gold
  if (index === 1) return 'text-gray-400'; // Silver
  if (index === 2) return 'text-amber-600'; // Bronze
  return 'text-gray-600';
};
</script>
