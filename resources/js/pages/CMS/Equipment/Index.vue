<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { MagnifyingGlassIcon, PlusIcon, WrenchIcon } from '@heroicons/vue/24/outline';

interface Equipment {
  id: number;
  name: string;
  equipment_code: string;
  type: string;
  status: string;
  current_value: number;
  last_maintenance_date: string;
}

const props = defineProps<{
  equipment: {
    data: Equipment[];
    current_page: number;
    last_page: number;
  };
  filters: any;
}>();

const search = ref(props.filters.search || '');

const statusColors = {
  available: 'bg-green-100 text-green-800',
  in_use: 'bg-blue-100 text-blue-800',
  maintenance: 'bg-yellow-100 text-yellow-800',
  retired: 'bg-gray-100 text-gray-800',
};
</script>

<template>
  <Head title="Equipment" />
  
  <CMSLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Equipment</h1>
          <p class="mt-1 text-sm text-gray-500">Manage equipment and maintenance</p>
        </div>
        <Link
          :href="route('cms.equipment.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Add Equipment
        </Link>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipment</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Maintenance</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in equipment.data" :key="item.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div>
                  <Link :href="route('cms.equipment.show', item.id)" class="font-medium text-blue-600 hover:text-blue-800">
                    {{ item.name }}
                  </Link>
                  <div class="text-sm text-gray-500">{{ item.equipment_code }}</div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ item.type }}</td>
              <td class="px-6 py-4">
                <span :class="statusColors[item.status]" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ item.status.replace('_', ' ').toUpperCase() }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">K{{ item.current_value?.toLocaleString() }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ item.last_maintenance_date || 'N/A' }}</td>
              <td class="px-6 py-4 text-right text-sm">
                <Link :href="route('cms.equipment.show', item.id)" class="text-blue-600 hover:text-blue-800">
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
