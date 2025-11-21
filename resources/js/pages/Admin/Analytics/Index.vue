<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
  UsersIcon, 
  CurrencyDollarIcon, 
  ChartBarIcon,
  SparklesIcon,
  ArrowPathIcon,
  TrashIcon
} from '@heroicons/vue/24/outline';

interface Props {
  platformStats: {
    total_members: number;
    active_members: number;
    premium_members: number;
    active_percentage: number;
    total_earnings: number;
    monthly_earnings: number;
    total_recommendations: number;
    active_recommendations: number;
  };
  topPerformers: any[];
  recentActivity: any[];
  recommendationStats: {
    by_type: Record<string, number>;
    by_priority: Record<string, number>;
    dismiss_rate: number;
    total: number;
    dismissed: number;
  };
}

const props = defineProps<Props>();

const processing = ref(false);
const bulkGenerateForm = ref({
  tier: '',
  active_only: true,
});

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-ZM', {
    style: 'currency',
    currency: 'ZMW',
    minimumFractionDigits: 0,
  }).format(amount);
};

const bulkGenerateRecommendations = async () => {
  if (!confirm('Generate recommendations for all matching members?')) return;
  
  processing.value = true;
  try {
    const response = await fetch(route('admin.analytics.recommendations.bulk-generate'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify(bulkGenerateForm.value),
    });
    
    const data = await response.json();
    if (data.success) {
      alert(data.message);
      window.location.reload();
    }
  } catch (error) {
    console.error('Failed to generate recommendations:', error);
    alert('Failed to generate recommendations');
  } finally {
    processing.value = false;
  }
};

const clearCache = async () => {
  if (!confirm('Clear all analytics cache? This will force recalculation for all members.')) return;
  
  processing.value = true;
  try {
    const response = await fetch(route('admin.analytics.cache.clear'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    });
    
    const data = await response.json();
    if (data.success) {
      alert(data.message);
    }
  } catch (error) {
    console.error('Failed to clear cache:', error);
    alert('Failed to clear cache');
  } finally {
    processing.value = false;
  }
};
</script>

<template>
  <Head title="Analytics Management" />

  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Analytics Management</h1>
          <p class="mt-1 text-sm text-gray-600">
            Platform-wide analytics and recommendation management
          </p>
        </div>

        <!-- Platform Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Total Members</p>
                <p class="text-2xl font-bold text-gray-900">{{ platformStats.total_members }}</p>
                <p class="text-xs text-gray-500 mt-1">
                  {{ platformStats.active_percentage }}% active
                </p>
              </div>
              <UsersIcon class="h-10 w-10 text-blue-600" />
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Premium Members</p>
                <p class="text-2xl font-bold text-gray-900">{{ platformStats.premium_members }}</p>
                <p class="text-xs text-gray-500 mt-1">
                  {{ Math.round((platformStats.premium_members / platformStats.total_members) * 100) }}% of total
                </p>
              </div>
              <SparklesIcon class="h-10 w-10 text-purple-600" />
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Total Earnings</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(platformStats.total_earnings) }}</p>
                <p class="text-xs text-gray-500 mt-1">All time</p>
              </div>
              <CurrencyDollarIcon class="h-10 w-10 text-green-600" />
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Active Recommendations</p>
                <p class="text-2xl font-bold text-gray-900">{{ platformStats.active_recommendations }}</p>
                <p class="text-xs text-gray-500 mt-1">
                  {{ platformStats.total_recommendations }} total
                </p>
              </div>
              <ChartBarIcon class="h-10 w-10 text-orange-600" />
            </div>
          </div>
        </div>

        <!-- Management Actions -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Management Actions</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Bulk Generate Recommendations -->
            <div class="border border-gray-200 rounded-lg p-4">
              <h3 class="font-medium text-gray-900 mb-3">Bulk Generate Recommendations</h3>
              <div class="space-y-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Tier Filter</label>
                  <select
                    v-model="bulkGenerateForm.tier"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  >
                    <option value="">All Tiers</option>
                    <option value="basic">Basic Only</option>
                    <option value="premium">Premium Only</option>
                  </select>
                </div>
                <div class="flex items-center">
                  <input
                    v-model="bulkGenerateForm.active_only"
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                  <label class="ml-2 text-sm text-gray-700">Active members only</label>
                </div>
                <button
                  @click="bulkGenerateRecommendations"
                  :disabled="processing"
                  class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                  <ArrowPathIcon class="h-5 w-5" :class="{ 'animate-spin': processing }" />
                  Generate Recommendations
                </button>
              </div>
            </div>

            <!-- Clear Cache -->
            <div class="border border-gray-200 rounded-lg p-4">
              <h3 class="font-medium text-gray-900 mb-3">Cache Management</h3>
              <p class="text-sm text-gray-600 mb-4">
                Clear analytics cache to force recalculation for all members. Use this after making changes to analytics logic.
              </p>
              <button
                @click="clearCache"
                :disabled="processing"
                class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                <TrashIcon class="h-5 w-5" />
                Clear Analytics Cache
              </button>
            </div>
          </div>
        </div>

        <!-- Recommendation Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recommendations by Type</h3>
            <div class="space-y-3">
              <div
                v-for="(count, type) in recommendationStats.by_type"
                :key="type"
                class="flex items-center justify-between"
              >
                <span class="text-sm text-gray-600 capitalize">{{ type.replace('_', ' ') }}</span>
                <span class="font-semibold text-gray-900">{{ count }}</span>
              </div>
              <div v-if="Object.keys(recommendationStats.by_type).length === 0" class="text-sm text-gray-500 text-center py-4">
                No active recommendations
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recommendations by Priority</h3>
            <div class="space-y-3">
              <div
                v-for="(count, priority) in recommendationStats.by_priority"
                :key="priority"
                class="flex items-center justify-between"
              >
                <span class="text-sm text-gray-600 capitalize flex items-center gap-2">
                  <span
                    class="w-3 h-3 rounded-full"
                    :class="{
                      'bg-red-500': priority === 'high',
                      'bg-yellow-500': priority === 'medium',
                      'bg-blue-500': priority === 'low',
                    }"
                  ></span>
                  {{ priority }}
                </span>
                <span class="font-semibold text-gray-900">{{ count }}</span>
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Dismiss Rate</span>
                <span class="font-semibold text-gray-900">{{ recommendationStats.dismiss_rate }}%</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Performers -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Performers</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Network</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Earnings</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="performer in topPerformers" :key="performer.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3">
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ performer.name }}</p>
                      <p class="text-xs text-gray-500">{{ performer.email }}</p>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="{
                        'bg-purple-100 text-purple-800': performer.tier === 'premium',
                        'bg-blue-100 text-blue-800': performer.tier === 'basic',
                        'bg-gray-100 text-gray-800': performer.tier === 'none',
                      }"
                    >
                      {{ performer.tier }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-900">{{ performer.network_size }}</td>
                  <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                    {{ formatCurrency(performer.total_earnings) }}
                  </td>
                  <td class="px-4 py-3">
                    <a
                      :href="route('admin.analytics.member', performer.id)"
                      class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                    >
                      View Details
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Analytics Events</h3>
          <div class="space-y-3">
            <div
              v-for="event in recentActivity.slice(0, 10)"
              :key="event.id"
              class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ event.user_name }}</p>
                <p class="text-xs text-gray-600">{{ event.event_type }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ new Date(event.created_at).toLocaleString() }}</p>
              </div>
            </div>
            <div v-if="recentActivity.length === 0" class="text-sm text-gray-500 text-center py-4">
              No recent activity
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
