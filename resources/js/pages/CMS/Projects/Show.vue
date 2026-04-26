<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ArrowLeftIcon, PencilIcon, CheckCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface Project {
  id: number;
  project_number: string;
  name: string;
  description: string;
  status: string;
  priority: string;
  budget: number;
  start_date: string;
  end_date: string;
  progress_percentage: number;
  customer?: { name: string };
  project_manager?: { name: string };
  milestones: Array<{
    id: number;
    name: string;
    target_date: string;
    status: string;
    payment_percentage: number;
  }>;
  jobs: Array<{
    id: number;
    job_number: string;
    title: string;
    status: string;
  }>;
}

const props = defineProps<{
  project: Project;
  stats: {
    total_jobs: number;
    completed_jobs: number;
    total_spent: number;
    budget_remaining: number;
    milestones_completed: number;
    total_milestones: number;
  };
}>();

const statusColors = {
  planning: 'bg-gray-100 text-gray-800',
  active: 'bg-blue-100 text-blue-800',
  on_hold: 'bg-yellow-100 text-yellow-800',
  completed: 'bg-green-100 text-green-800',
  cancelled: 'bg-red-100 text-red-800',
};

const updateStatus = (status: string) => {
  router.post(route('cms.projects.status', props.project.id), { status });
};

const completeMilestone = (milestoneId: number) => {
  router.post(route('cms.projects.milestones.complete', [props.project.id, milestoneId]));
};
</script>

<template>
  <Head :title="project.name" />
  
  <CMSLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-start justify-between">
        <div class="flex items-start gap-4">
          <Link
            :href="route('cms.projects.index')"
            class="p-2 hover:bg-gray-100 rounded-lg"
          >
            <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-gray-900">{{ project.name }}</h1>
              <span :class="statusColors[project.status]" class="px-3 py-1 text-sm font-medium rounded-full">
                {{ project.status.replace('_', ' ').toUpperCase() }}
              </span>
            </div>
            <p class="mt-1 text-sm text-gray-500">{{ project.project_number }}</p>
          </div>
        </div>
        <Link
          :href="route('cms.projects.edit', project.id)"
          class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          <PencilIcon class="h-5 w-5" aria-hidden="true" />
          Edit
        </Link>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm text-gray-500">Progress</div>
          <div class="mt-2 text-2xl font-bold text-gray-900">{{ project.progress_percentage }}%</div>
          <div class="mt-2 bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full" :style="{ width: `${project.progress_percentage}%` }"></div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm text-gray-500">Jobs</div>
          <div class="mt-2 text-2xl font-bold text-gray-900">{{ stats.completed_jobs }}/{{ stats.total_jobs }}</div>
          <div class="mt-2 text-sm text-gray-600">Completed</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm text-gray-500">Budget</div>
          <div class="mt-2 text-2xl font-bold text-gray-900">K{{ project.budget?.toLocaleString() }}</div>
          <div class="mt-2 text-sm text-gray-600">K{{ stats.budget_remaining?.toLocaleString() }} remaining</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="text-sm text-gray-500">Milestones</div>
          <div class="mt-2 text-2xl font-bold text-gray-900">{{ stats.milestones_completed }}/{{ stats.total_milestones }}</div>
          <div class="mt-2 text-sm text-gray-600">Completed</div>
        </div>
      </div>

      <!-- Details -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Description -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Description</h2>
            <p class="text-gray-700">{{ project.description || 'No description provided' }}</p>
          </div>

          <!-- Milestones -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Milestones</h2>
            <div v-if="project.milestones.length === 0" class="text-center py-8 text-gray-500">
              No milestones defined
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="milestone in project.milestones"
                :key="milestone.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center gap-3">
                  <CheckCircleIcon
                    :class="[
                      'h-6 w-6',
                      milestone.status === 'completed' ? 'text-green-600' : 'text-gray-300'
                    ]"
                    aria-hidden="true"
                  />
                  <div>
                    <div class="font-medium text-gray-900">{{ milestone.name }}</div>
                    <div class="text-sm text-gray-500">Target: {{ milestone.target_date }}</div>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span v-if="milestone.payment_percentage" class="text-sm text-gray-600">
                    {{ milestone.payment_percentage }}% payment
                  </span>
                  <button
                    v-if="milestone.status !== 'completed'"
                    @click="completeMilestone(milestone.id)"
                    class="px-3 py-1 text-sm bg-green-50 text-green-600 rounded-lg hover:bg-green-100"
                  >
                    Complete
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Jobs -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Related Jobs</h2>
            <div v-if="project.jobs.length === 0" class="text-center py-8 text-gray-500">
              No jobs linked to this project
            </div>
            <div v-else class="space-y-2">
              <Link
                v-for="job in project.jobs"
                :key="job.id"
                :href="route('cms.jobs.show', job.id)"
                class="block p-4 bg-gray-50 rounded-lg hover:bg-gray-100"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-gray-900">{{ job.title }}</div>
                    <div class="text-sm text-gray-500">{{ job.job_number }}</div>
                  </div>
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                    {{ job.status }}
                  </span>
                </div>
              </Link>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Project Info -->
          <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Project Details</h2>
            
            <div>
              <div class="text-sm text-gray-500">Customer</div>
              <div class="mt-1 font-medium text-gray-900">{{ project.customer?.name || 'N/A' }}</div>
            </div>

            <div>
              <div class="text-sm text-gray-500">Project Manager</div>
              <div class="mt-1 font-medium text-gray-900">{{ project.project_manager?.name || 'N/A' }}</div>
            </div>

            <div>
              <div class="text-sm text-gray-500">Priority</div>
              <div class="mt-1">
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">
                  {{ project.priority.toUpperCase() }}
                </span>
              </div>
            </div>

            <div>
              <div class="text-sm text-gray-500">Timeline</div>
              <div class="mt-1 text-sm text-gray-900">
                <div>Start: {{ project.start_date }}</div>
                <div>End: {{ project.end_date }}</div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="bg-white rounded-lg shadow p-6 space-y-3">
            <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
            
            <select
              @change="updateStatus($event.target.value)"
              :value="project.status"
              class="w-full rounded-lg border-gray-300"
            >
              <option value="planning">Planning</option>
              <option value="active">Active</option>
              <option value="on_hold">On Hold</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>

            <Link
              :href="route('cms.projects.timeline', project.id)"
              class="block w-full px-4 py-2 text-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100"
            >
              <ClockIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
              View Timeline
            </Link>
          </div>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>
