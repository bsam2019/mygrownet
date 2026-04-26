<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { MagnifyingGlassIcon, PlusIcon, FunnelIcon } from '@heroicons/vue/24/outline';

interface Project {
  id: number;
  project_number: string;
  name: string;
  customer?: { name: string };
  status: string;
  priority: string;
  budget: number;
  start_date: string;
  end_date: string;
  progress_percentage: number;
  project_manager?: { name: string };
}

const props = defineProps<{
  projects: {
    data: Project[];
    current_page: number;
    last_page: number;
  };
  filters: {
    status?: string;
    search?: string;
  };
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const statusColors = {
  planning: 'bg-gray-100 text-gray-800',
  active: 'bg-blue-100 text-blue-800',
  on_hold: 'bg-yellow-100 text-yellow-800',
  completed: 'bg-green-100 text-green-800',
  cancelled: 'bg-red-100 text-red-800',
};

const priorityColors = {
  low: 'bg-gray-100 text-gray-600',
  medium: 'bg-blue-100 text-blue-600',
  high: 'bg-orange-100 text-orange-600',
  urgent: 'bg-red-100 text-red-600',
};

const applyFilters = () => {
  router.get(route('cms.projects.index'), {
    search: search.value,
    status: statusFilter.value,
  }, { preserveState: true });
};
</script>

<template>
  <Head title="Projects" />
  
  <CMSLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
          <p class="mt-1 text-sm text-gray-500">Manage construction projects and timelines</p>
        </div>
        <Link
          :href="route('cms.projects.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Project
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
              <input
                v-model="search"
                type="text"
                placeholder="Search projects..."
                class="pl-10 w-full rounded-lg border-gray-300"
                @keyup.enter="applyFilters"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="statusFilter" class="w-full rounded-lg border-gray-300" @change="applyFilters">
              <option value="">All Statuses</option>
              <option value="planning">Planning</option>
              <option value="active">Active</option>
              <option value="on_hold">On Hold</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="applyFilters"
              class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
            >
              <FunnelIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
              Apply Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Projects List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Timeline</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="project in projects.data" :key="project.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div>
                  <Link :href="route('cms.projects.show', project.id)" class="font-medium text-blue-600 hover:text-blue-800">
                    {{ project.name }}
                  </Link>
                  <div class="text-sm text-gray-500">{{ project.project_number }}</div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ project.customer?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4">
                <span :class="statusColors[project.status]" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ project.status.replace('_', ' ').toUpperCase() }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span :class="priorityColors[project.priority]" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ project.priority.toUpperCase() }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-blue-600 h-2 rounded-full"
                      :style="{ width: `${project.progress_percentage}%` }"
                    ></div>
                  </div>
                  <span class="text-sm text-gray-600">{{ project.progress_percentage }}%</span>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                K{{ project.budget?.toLocaleString() || '0' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                <div>{{ project.start_date }}</div>
                <div>{{ project.end_date }}</div>
              </td>
              <td class="px-6 py-4 text-right text-sm">
                <Link
                  :href="route('cms.projects.show', project.id)"
                  class="text-blue-600 hover:text-blue-800"
                >
                  View
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="projects.last_page > 1" class="flex justify-center gap-2">
        <button
          v-for="page in projects.last_page"
          :key="page"
          @click="router.get(route('cms.projects.index', { page }))"
          :class="[
            'px-4 py-2 rounded-lg',
            page === projects.current_page
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-100'
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </CMSLayout>
</template>
