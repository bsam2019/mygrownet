<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-900">
        All Reward Cycles
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="grid gap-4 md:grid-cols-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select
                  v-model="filters.status"
                  @change="applyFilters"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="">All Statuses</option>
                  <option value="active">Active</option>
                  <option value="completed">Completed</option>
                  <option value="suspended">Suspended</option>
                  <option value="terminated">Terminated</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Tier</label>
                <select
                  v-model="filters.tier"
                  @change="applyFilters"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="">All Tiers</option>
                  <option value="lite">Lite (K12.50/day)</option>
                  <option value="basic">Basic (K25/day)</option>
                  <option value="growth_plus">Growth Plus (K37.50/day)</option>
                  <option value="pro">Pro (K62.50/day)</option>
                  <option value="premium">Premium (Legacy)</option>
                </select>
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Search Member</label>
                <input
                  v-model="filters.search"
                  @input="applyFilters"
                  type="text"
                  placeholder="Name or email..."
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Cycles Table -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Member</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Tier</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Progress</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Daily Rate</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Earned</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Dates</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-if="!cycles || cycles.data.length === 0">
                  <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                    No cycles found
                  </td>
                </tr>
                <tr v-for="cycle in cycles?.data || []" :key="cycle.id">
                  <td class="whitespace-nowrap px-6 py-4">
                    <div>
                      <div class="font-medium text-gray-900">{{ cycle.user.name }}</div>
                      <div class="text-sm text-gray-500">{{ cycle.user.email }}</div>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4">
                    <span
                      :class="[
                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold capitalize',
                        cycle.tier === 'lite'
                          ? 'bg-gray-100 text-gray-800'
                          : cycle.tier === 'basic'
                            ? 'bg-blue-100 text-blue-800'
                            : cycle.tier === 'growth_plus'
                              ? 'bg-emerald-100 text-emerald-800'
                              : cycle.tier === 'pro'
                                ? 'bg-purple-100 text-purple-800'
                                : 'bg-indigo-100 text-indigo-800',
                      ]"
                    >
                      {{ cycle.tier?.replace('_', ' ') || 'basic' }}
                    </span>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4">
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
                  <td class="whitespace-nowrap px-6 py-4">
                    <div class="flex items-center">
                      <span class="mr-2 text-sm text-gray-900">{{ cycle.active_days }}/70</span>
                      <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
                        <div
                          class="h-full bg-blue-600"
                          :style="{ width: cycle.completion_rate + '%' }"
                        ></div>
                      </div>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-blue-600">
                    K{{ cycle.daily_rate?.toFixed(2) || '25.00' }}
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-green-600">
                    K{{ cycle.total_earned_lgc.toFixed(2) }}
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                    <div>Start: {{ cycle.start_date }}</div>
                    <div>End: {{ cycle.end_date }}</div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm">
                    <button class="text-blue-600 hover:text-blue-800">View Details</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="cycles && cycles.data && cycles.data.length > 0" class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ cycles.from }} to {{ cycles.to }} of {{ cycles.total }} cycles
          </div>
          <div class="flex space-x-2">
            <Link
              v-for="link in cycles.links"
              :key="link.label"
              :href="link.url"
              :class="[
                'rounded-md px-3 py-2 text-sm',
                link.active
                  ? 'bg-blue-600 text-white'
                  : link.url
                    ? 'bg-white text-gray-700 hover:bg-gray-50'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed',
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Props {
  cycles?: any;
  filters?: any;
}

const props = withDefaults(defineProps<Props>(), {
  cycles: () => ({ data: [], links: [], from: 0, to: 0, total: 0 }),
  filters: () => ({ status: '', tier: '', search: '' }),
});

const filters = ref(props.filters || { status: '', tier: '', search: '' });

const applyFilters = () => {
  router.get(route('admin.lgr.cycles'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

