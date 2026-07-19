<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { PlusIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

interface BOQ {
  id: number;
  boq_number: string;
  title: string;
  project?: { name: string };
  status: string;
  total_amount: number;
  items_count: number;
}

const props = defineProps<{
  boqs: {
    data: BOQ[];
  };
}>();

const statusColors = {
  draft: 'bg-gray-100 text-gray-800',
  approved: 'bg-green-100 text-green-800',
  in_progress: 'bg-blue-100 text-blue-800',
  completed: 'bg-purple-100 text-purple-800',
};
</script>

<template>
  <Head title="Bill of Quantities" />
  
  <CMSLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Bill of Quantities (BOQ)</h1>
          <p class="mt-1 text-sm text-gray-500">Manage BOQs and track quantities</p>
        </div>
        <Link
          :href="route('cms.boq.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Create BOQ
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link
          v-for="boq in boqs.data"
          :key="boq.id"
          :href="route('cms.boq.show', boq.id)"
          class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-purple-100 rounded-lg">
                <DocumentTextIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
              </div>
              <div>
                <h3 class="font-semibold text-gray-900">{{ boq.title }}</h3>
                <p class="text-sm text-gray-500">{{ boq.boq_number }}</p>
              </div>
            </div>
            <span :class="statusColors[boq.status]" class="px-2 py-1 text-xs font-medium rounded-full">
              {{ boq.status.replace('_', ' ').toUpperCase() }}
            </span>
          </div>

          <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">Project</span>
              <span class="font-medium text-gray-900">{{ boq.project?.name || 'N/A' }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">Items</span>
              <span class="font-medium text-gray-900">{{ boq.items_count }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">Total Amount</span>
              <span class="font-medium text-gray-900">K{{ boq.total_amount?.toLocaleString() }}</span>
            </div>
          </div>
        </Link>
      </div>
    </div>
  </CMSLayout>
</template>
