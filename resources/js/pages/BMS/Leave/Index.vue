<template>
  <CMSLayout title="Leave Management">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Leave Management</h1>
          <p class="mt-1 text-sm text-gray-500">Manage employee leave requests and balances</p>
        </div>
        <Link
          :href="route('cms.leave.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Leave Request
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              @change="applyFilters"
              class="w-full rounded-lg border-gray-300"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
            <select
              v-model="filters.worker_id"
              @change="applyFilters"
              class="w-full rounded-lg border-gray-300"
            >
              <option value="">All Employees</option>
              <option v-for="worker in workers" :key="worker.id" :value="worker.id">
                {{ worker.name }} ({{ worker.worker_number }})
              </option>
            </select>
          </div>
          <div class="flex items-end">
            <Link
              :href="route('cms.leave.balance')"
              class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-center"
            >
              View Leave Balances
            </Link>
          </div>
        </div>
      </div>

      <!-- Leave Requests Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leave Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="leave in leaveRequests.data" :key="leave.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ leave.leave_request_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ leave.worker.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ leave.leave_type.leave_type_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ leave.total_days }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="{
                    'bg-yellow-100 text-yellow-800': leave.status === 'pending',
                    'bg-green-100 text-green-800': leave.status === 'approved',
                    'bg-red-100 text-red-800': leave.status === 'rejected',
                    'bg-gray-100 text-gray-800': leave.status === 'cancelled',
                  }"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ leave.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <Link
                  :href="route('cms.leave.show', leave.id)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  View
                </Link>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="leaveRequests.data.length > 0" class="px-6 py-4 border-t">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
              Showing {{ leaveRequests.from }} to {{ leaveRequests.to }} of {{ leaveRequests.total }} results
            </div>
            <div class="flex gap-2">
              <Link
                v-for="link in leaveRequests.links"
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
        <div v-if="leaveRequests.data.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No leave requests</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new leave request.</p>
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
  leaveRequests: any;
  workers: any[];
  filters: {
    status?: string;
    worker_id?: number;
  };
}

const props = defineProps<Props>();

const filters = ref({
  status: props.filters.status || '',
  worker_id: props.filters.worker_id || '',
});

const applyFilters = () => {
  router.get(route('cms.leave.index'), filters.value, {
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
</script>
