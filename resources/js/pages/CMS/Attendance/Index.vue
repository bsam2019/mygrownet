<template>
  <CMSLayout title="Attendance Records">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Attendance Records</h1>
          <p class="mt-1 text-sm text-gray-500">View and manage attendance</p>
        </div>
        <div class="flex gap-3">
          <Link
            :href="route('cms.attendance.summary')"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
          >
            View Summary
          </Link>
          <Link
            :href="route('cms.attendance.index') + '?action=clock'"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Clock In/Out
          </Link>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Worker</label>
            <select
              v-model="filters.worker_id"
              @change="applyFilters"
              class="w-full rounded-lg border-gray-300"
            >
              <option value="">All Workers</option>
              <option v-for="worker in workers" :key="worker.id" :value="worker.id">
                {{ worker.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              @change="applyFilters"
              class="w-full rounded-lg border-gray-300"
            >
              <option value="">All Statuses</option>
              <option value="present">Present</option>
              <option value="late">Late</option>
              <option value="half_day">Half Day</option>
              <option value="absent">Absent</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input
              v-model="filters.start_date"
              type="date"
              @change="applyFilters"
              class="w-full rounded-lg border-gray-300"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input
              v-model="filters.end_date"
              type="date"
              @change="applyFilters"
              class="w-full rounded-lg border-gray-300"
            />
          </div>
        </div>
      </div>

      <!-- Records Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Worker</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock In</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock Out</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="!records?.data || records.data.length === 0">
              <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                No attendance records found
              </td>
            </tr>
            <tr v-for="record in records?.data || []" :key="record.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(record.attendance_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ record.worker.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ record.shift?.shift_name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ record.clock_in_time || '-' }}
                <span v-if="record.is_late" class="ml-1 text-xs text-orange-600">(Late)</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ record.clock_out_time || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatMinutes(record.total_minutes) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="{
                    'bg-green-100 text-green-800': record.status === 'present',
                    'bg-orange-100 text-orange-800': record.status === 'late',
                    'bg-yellow-100 text-yellow-800': record.status === 'half_day',
                    'bg-red-100 text-red-800': record.status === 'absent',
                  }"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ record.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="records?.data && records.data.length > 0" class="px-6 py-4 border-t">
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No attendance records</h3>
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

defineOptions({
  layout: CMSLayout
})

interface Props {
  records: {
    data: any[];
    links?: any[];
    from?: number;
    to?: number;
    total?: number;
  };
  workers: any[];
  filters: {
    worker_id?: number;
    status?: string;
    start_date?: string;
    end_date?: string;
  };
}

const props = withDefaults(defineProps<Props>(), {
  records: () => ({ data: [], links: [], from: 0, to: 0, total: 0 }),
  workers: () => [],
});

const filters = ref({
  worker_id: props.filters.worker_id || '',
  status: props.filters.status || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
});

const applyFilters = () => {
  router.get(route('cms.attendance.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatMinutes = (minutes: number | null): string => {
  if (!minutes) return '-';
  const hours = Math.floor(minutes / 60);
  const mins = minutes % 60;
  return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;
};
</script>
