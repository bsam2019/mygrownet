<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ShieldCheckIcon, PlusIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
  certifications: any;
  expiring: any;
  filters: any;
}>();

const statusFilter = ref(props.filters.status || '');

const applyFilters = () => {
  router.get(route('cms.training.certifications'), {
    status: statusFilter.value,
  }, { preserveState: true });
};

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    active: 'bg-green-100 text-green-800',
    expired: 'bg-red-100 text-red-800',
    revoked: 'bg-gray-100 text-gray-800',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
  <Head title="Certifications" />
  
  <CMSLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Certifications</h1>
          <p class="mt-1 text-sm text-gray-500">Track employee certifications and renewals</p>
        </div>
        <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Add Certification
        </button>
      </div>

      <!-- Expiring Soon Alert -->
      <div v-if="expiring.length > 0" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <ExclamationTriangleIcon class="h-6 w-6 text-yellow-600 flex-shrink-0" aria-hidden="true" />
          <div>
            <h3 class="font-medium text-yellow-900">Certifications Expiring Soon</h3>
            <p class="text-sm text-yellow-700 mt-1">{{ expiring.length }} certification(s) expiring in the next 30 days</p>
            <ul class="mt-2 space-y-1">
              <li v-for="cert in expiring.slice(0, 3)" :key="cert.id" class="text-sm text-yellow-700">
                {{ cert.worker.first_name }} {{ cert.worker.last_name }} - {{ cert.certification_name }} ({{ formatDate(cert.expiry_date) }})
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white p-4 rounded-lg border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <select v-model="statusFilter" @change="applyFilters" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="expired">Expired</option>
            <option value="revoked">Revoked</option>
          </select>
          <button @click="statusFilter = ''; applyFilters()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Certifications List -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Certification</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="cert in certifications.data" :key="cert.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="font-medium text-gray-900">{{ cert.worker.first_name }} {{ cert.worker.last_name }}</div>
                  <div class="text-sm text-gray-500">{{ cert.worker.job_title }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <ShieldCheckIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                    <div>
                      <div class="font-medium text-gray-900">{{ cert.certification_name }}</div>
                      <div v-if="cert.certificate_number" class="text-xs text-gray-500">{{ cert.certificate_number }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ cert.issuing_organization }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(cert.issue_date) }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ cert.expiry_date ? formatDate(cert.expiry_date) : 'No expiry' }}
                </td>
                <td class="px-6 py-4">
                  <span :class="getStatusColor(cert.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ cert.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right text-sm">
                  <button class="text-blue-600 hover:text-blue-800">View</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>
