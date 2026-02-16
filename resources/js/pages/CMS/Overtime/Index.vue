<template>
  <CMSLayout title="Overtime Management">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Overtime Management</h1>
          <p class="mt-1 text-sm text-gray-500">Review and approve overtime records</p>
        </div>
        <Link
          :href="route('cms.overtime.summary')"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
        >
          View Summary
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Worker</label>
            <select v-model="filters.worker_id" @change="applyFilters" class="w-full rounded-lg border-gray-300">
              <option value="">All Workers</option>
              <option v-for="worker in workers" :key="worker.id" :value="worker.id">
                {{ worker.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="filters.status" @change="applyFilters" class="w-full rounded-lg border-gray-300">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input v-model="filters.start_date" type="date" @change="applyFilters" class="w-full rounded-lg border-gray-300" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input v-model="filters.end_date" type="date" @change="applyFilters" class="w-full rounded-lg border-gray-300" />
          </div>
        </div>
      </div>

      <!-- Overtime Records Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Worker</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="record in records.data" :key="record.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(record.overtime_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ record.worker.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span class="capitalize">{{ record.overtime_type.replace('_', ' ') }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatMinutes(record.overtime_minutes) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ record.rate_multiplier }}x
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="{
                    'bg-yellow-100 text-yellow-800': record.status === 'pending',
                    'bg-green-100 text-green-800': record.status === 'approved',
                    'bg-red-100 text-red-800': record.status === 'rejected',
                  }"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ record.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div v-if="record.status === 'pending'" class="flex gap-2">
                  <button
                    @click="approve(record.id)"
                    class="text-green-600 hover:text-green-900"
                  >
                    Approve
                  </button>
                  <button
                    @click="reject(record.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Reject
                  </button>
                </div>
                <span v-else class="text-gray-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="records.data.length > 0" class="px-6 py-4 border-t">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
              Showing {{ records.from }} to {{ records.to }} of {{ records.total }} results
            </div>
            <div class="flex gap-2">
              <Link
                v-for="link in records.links"
                :key="link.label"
                :href="link.url"
                :class="{
                  'bg-blue-600 text-white': link.active,
                  'bg-white text-gray-700 hover:bg-gray-50': !link.active,
                  'opacity-50 cursor-not-allowed': !link.url,
                }"
                class="px-3 py-2 text-sm rounded-lg border"
                v-html="link.label"
              />
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="records.data.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No overtime records</h3>
          <p class="mt-1 text-sm text-gray-500">No records found for the selected filters.</p>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

interface Props {
  records: any;
  workers: any[];
  filters: {
    worker_id?: number;
    status?: string;
    start_date?: string;
    end_date?: string;
  };
}

const props = defineProps<Props>();

const filters = ref({
  worker_id: props.filters.worker_id || '',
  status: props.filters.status || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
});

const applyFilters = () => {
  router.get(route('cms.overtime.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const approve = (id: number) => {
  if (confirm('Approve this overtime record?')) {
    router.post(route('cms.overtime.approve', id));
  }
};

const reject = (id: number) => {
  if (confirm('Reject this overtime record?')) {
    router.post(route('cms.overtime.reject', id));
  }
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatMinutes = (minutes: number): string => {
  const hours = Math.floor(minutes / 60);
  const mins = minutes % 60;
  return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;
};
</script>
