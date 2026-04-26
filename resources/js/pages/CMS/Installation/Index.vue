<template>
  <CMSLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Installation Management</h1>
          <p class="mt-1 text-sm text-gray-500">Schedule and track installations</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
          Schedule Installation
        </button>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <CalendarIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
            <div class="ml-4">
              <p class="text-sm text-gray-500">Scheduled</p>
              <p class="text-2xl font-bold text-gray-900">{{ statistics.total_scheduled }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <WrenchIcon class="h-8 w-8 text-amber-600" aria-hidden="true" />
            <div class="ml-4">
              <p class="text-sm text-gray-500">In Progress</p>
              <p class="text-2xl font-bold text-gray-900">{{ statistics.in_progress }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <CheckCircleIcon class="h-8 w-8 text-green-600" aria-hidden="true" />
            <div class="ml-4">
              <p class="text-sm text-gray-500">Completed</p>
              <p class="text-2xl font-bold text-gray-900">{{ statistics.completed }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <ExclamationTriangleIcon class="h-8 w-8 text-red-600" aria-hidden="true" />
            <div class="ml-4">
              <p class="text-sm text-gray-500">Open Defects</p>
              <p class="text-2xl font-bold text-gray-900">{{ statistics.open_defects }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex gap-4">
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="rounded-lg border-gray-300"
          >
            <option value="">All Status</option>
            <option value="scheduled">Scheduled</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>

      <!-- Schedules Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Team Leader</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="schedule in schedules.data" :key="schedule.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ schedule.job.job_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ schedule.job.customer.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(schedule.scheduled_date) }}
                <span v-if="schedule.scheduled_time" class="text-gray-400">
                  {{ schedule.scheduled_time }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ schedule.team_leader.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(schedule.status)" class="px-2 py-1 text-xs rounded-full">
                  {{ schedule.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <Link
                  :href="route('cms.installation.show', schedule.id)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  View
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="schedules.links" class="flex justify-center">
        <nav class="flex gap-2">
          <Link
            v-for="link in schedules.links"
            :key="link.label"
            :href="link.url"
            :class="[
              'px-3 py-2 rounded',
              link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'
            ]"
            v-html="link.label"
          />
        </nav>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { PlusIcon, CalendarIcon, WrenchIcon, CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

interface Props {
  schedules: any;
  statistics: {
    total_scheduled: number;
    in_progress: number;
    completed: number;
    open_defects: number;
  };
  filters: {
    status?: string;
  };
}

const props = defineProps<Props>();

const showCreateModal = ref(false);
const filters = ref({ ...props.filters });

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const getStatusClass = (status: string) => {
  const classes = {
    scheduled: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-amber-100 text-amber-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const applyFilters = () => {
  router.get(route('cms.installation.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>
