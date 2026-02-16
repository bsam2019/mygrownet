<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { AcademicCapIcon, PlusIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
  programs: any;
  filters: any;
  statistics: any;
}>();

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const statusFilter = ref(props.filters.status || '');

const applyFilters = () => {
  router.get(route('cms.training.programs'), {
    search: search.value,
    type: typeFilter.value,
    status: statusFilter.value,
  }, { preserveState: true });
};

const getTypeColor = (type: string) => {
  const colors: Record<string, string> = {
    internal: 'bg-blue-100 text-blue-800',
    external: 'bg-purple-100 text-purple-800',
    online: 'bg-green-100 text-green-800',
    workshop: 'bg-orange-100 text-orange-800',
    certification: 'bg-indigo-100 text-indigo-800',
    mentorship: 'bg-pink-100 text-pink-800',
  };
  return colors[type] || 'bg-gray-100 text-gray-800';
};

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    active: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
  <Head title="Training Programs" />
  
  <CMSLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Training Programs</h1>
          <p class="mt-1 text-sm text-gray-500">Manage training programs and courses</p>
        </div>
        <button
          @click="router.visit(route('cms.training.programs.create'))"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Program
        </button>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-lg border border-gray-200">
          <div class="text-sm text-gray-500">Total Programs</div>
          <div class="text-2xl font-semibold text-gray-900">{{ statistics.total_programs }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200">
          <div class="text-sm text-gray-500">Active Sessions</div>
          <div class="text-2xl font-semibold text-blue-600">{{ statistics.active_sessions }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200">
          <div class="text-sm text-gray-500">Enrollments</div>
          <div class="text-2xl font-semibold text-gray-900">{{ statistics.total_enrollments }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200">
          <div class="text-sm text-gray-500">Completed</div>
          <div class="text-2xl font-semibold text-green-600">{{ statistics.completed_trainings }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200">
          <div class="text-sm text-gray-500">Completion Rate</div>
          <div class="text-2xl font-semibold text-gray-900">{{ statistics.completion_rate }}%</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white p-4 rounded-lg border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
            <input
              v-model="search"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Search programs..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg"
            />
          </div>
          <select v-model="typeFilter" @change="applyFilters" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">All Types</option>
            <option value="internal">Internal</option>
            <option value="external">External</option>
            <option value="online">Online</option>
            <option value="workshop">Workshop</option>
            <option value="certification">Certification</option>
            <option value="mentorship">Mentorship</option>
          </select>
          <select v-model="statusFilter" @change="applyFilters" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <button @click="search = ''; typeFilter = ''; statusFilter = ''; applyFilters()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Programs List -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sessions</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="program in programs.data" :key="program.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <AcademicCapIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
                    <div>
                      <div class="font-medium text-gray-900">{{ program.title }}</div>
                      <div class="text-sm text-gray-500">{{ program.level }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="getTypeColor(program.type)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ program.type }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ program.category }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ program.duration_hours }}h</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ program.sessions?.length || 0 }}</td>
                <td class="px-6 py-4">
                  <span :class="getStatusColor(program.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ program.status }}
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
