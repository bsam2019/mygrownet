<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ArrowLeftIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
  projectNumber: string;
}>();

const form = useForm({
  name: '',
  description: '',
  customer_id: null,
  site_location: '',
  site_address: '',
  priority: 'medium',
  budget: null,
  start_date: '',
  end_date: '',
  project_manager_id: null,
  milestones: [] as Array<{
    name: string;
    target_date: string;
    payment_percentage: number | null;
  }>,
});

const addMilestone = () => {
  form.milestones.push({
    name: '',
    target_date: '',
    payment_percentage: null,
  });
};

const removeMilestone = (index: number) => {
  form.milestones.splice(index, 1);
};

const submit = () => {
  form.post(route('cms.projects.store'));
};
</script>

<template>
  <Head title="Create Project" />
  
  <CMSLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('cms.projects.index')"
          class="p-2 hover:bg-gray-100 rounded-lg"
        >
          <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Create Project</h1>
          <p class="mt-1 text-sm text-gray-500">Project #{{ projectNumber }}</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Project Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full rounded-lg border-gray-300"
                placeholder="e.g., Office Building Construction"
              />
              <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full rounded-lg border-gray-300"
                placeholder="Project description..."
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
              <select v-model="form.priority" class="w-full rounded-lg border-gray-300">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Budget</label>
              <input
                v-model="form.budget"
                type="number"
                step="0.01"
                class="w-full rounded-lg border-gray-300"
                placeholder="0.00"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
              <input
                v-model="form.start_date"
                type="date"
                class="w-full rounded-lg border-gray-300"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
              <input
                v-model="form.end_date"
                type="date"
                class="w-full rounded-lg border-gray-300"
              />
            </div>
          </div>
        </div>

        <!-- Site Information -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <h2 class="text-lg font-semibold text-gray-900">Site Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Site Location</label>
              <input
                v-model="form.site_location"
                type="text"
                class="w-full rounded-lg border-gray-300"
                placeholder="e.g., Lusaka"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Site Address</label>
              <input
                v-model="form.site_address"
                type="text"
                class="w-full rounded-lg border-gray-300"
                placeholder="Full address"
              />
            </div>
          </div>
        </div>

        <!-- Milestones -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Milestones</h2>
            <button
              type="button"
              @click="addMilestone"
              class="inline-flex items-center gap-2 px-3 py-1.5 text-sm bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100"
            >
              <PlusIcon class="h-4 w-4" aria-hidden="true" />
              Add Milestone
            </button>
          </div>

          <div v-if="form.milestones.length === 0" class="text-center py-8 text-gray-500">
            No milestones added yet
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="(milestone, index) in form.milestones"
              :key="index"
              class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg"
            >
              <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                  <input
                    v-model="milestone.name"
                    type="text"
                    placeholder="Milestone name"
                    class="w-full rounded-lg border-gray-300"
                    required
                  />
                </div>
                <div>
                  <input
                    v-model="milestone.target_date"
                    type="date"
                    class="w-full rounded-lg border-gray-300"
                    required
                  />
                </div>
                <div>
                  <input
                    v-model="milestone.payment_percentage"
                    type="number"
                    step="0.01"
                    placeholder="Payment %"
                    class="w-full rounded-lg border-gray-300"
                  />
                </div>
              </div>
              <button
                type="button"
                @click="removeMilestone(index)"
                class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
              >
                <TrashIcon class="h-5 w-5" aria-hidden="true" />
              </button>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <Link
            :href="route('cms.projects.index')"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Creating...' : 'Create Project' }}
          </button>
        </div>
      </form>
    </div>
  </CMSLayout>
</template>
