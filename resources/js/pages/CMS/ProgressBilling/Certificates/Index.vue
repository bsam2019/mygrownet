<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { PlusIcon, CheckIcon, XMarkIcon, DocumentCheckIcon } from '@heroicons/vue/24/outline';

interface Certificate {
  id: number;
  certificate_number: string;
  project: { name: string };
  period_from: string;
  period_to: string;
  gross_amount: number;
  retention_amount: number;
  net_amount: number;
  status: string;
}

const props = defineProps<{
  certificates: {
    data: Certificate[];
  };
}>();

const statusColors = {
  draft: 'bg-gray-100 text-gray-800',
  submitted: 'bg-blue-100 text-blue-800',
  approved: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800',
  paid: 'bg-purple-100 text-purple-800',
};

const approve = (id: number) => {
  router.post(route('cms.progress-billing.certificates.approve', id));
};

const reject = (id: number) => {
  const notes = prompt('Rejection reason:');
  if (notes) {
    router.post(route('cms.progress-billing.certificates.reject', id), { notes });
  }
};
</script>

<template>
  <Head title="Progress Certificates" />
  
  <CMSLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Progress Certificates</h1>
          <p class="mt-1 text-sm text-gray-500">Manage progress billing and retention</p>
        </div>
        <Link
          :href="route('cms.progress-billing.certificates.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Certificate
        </Link>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Certificate</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gross Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retention</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="cert in certificates.data" :key="cert.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <Link :href="route('cms.progress-billing.certificates.show', cert.id)" class="font-medium text-blue-600 hover:text-blue-800">
                  {{ cert.certificate_number }}
                </Link>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ cert.project.name }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">
                <div>{{ cert.period_from }}</div>
                <div>{{ cert.period_to }}</div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">K{{ cert.gross_amount?.toLocaleString() }}</td>
              <td class="px-6 py-4 text-sm text-red-600">K{{ cert.retention_amount?.toLocaleString() }}</td>
              <td class="px-6 py-4 text-sm font-medium text-gray-900">K{{ cert.net_amount?.toLocaleString() }}</td>
              <td class="px-6 py-4">
                <span :class="statusColors[cert.status]" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ cert.status.toUpperCase() }}
                </span>
              </td>
              <td class="px-6 py-4 text-right text-sm space-x-2">
                <button
                  v-if="cert.status === 'submitted'"
                  @click="approve(cert.id)"
                  class="text-green-600 hover:text-green-800"
                  title="Approve"
                >
                  <CheckIcon class="h-5 w-5 inline" aria-hidden="true" />
                </button>
                <button
                  v-if="cert.status === 'submitted'"
                  @click="reject(cert.id)"
                  class="text-red-600 hover:text-red-800"
                  title="Reject"
                >
                  <XMarkIcon class="h-5 w-5 inline" aria-hidden="true" />
                </button>
                <Link
                  :href="route('cms.progress-billing.certificates.show', cert.id)"
                  class="text-blue-600 hover:text-blue-800"
                >
                  View
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </CMSLayout>
</template>
