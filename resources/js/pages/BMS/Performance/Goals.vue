<template>
  <CMSLayout title="Goals & Objectives">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Goals & Objectives</h1>
          <p class="text-sm text-gray-600 mt-1">Track employee goals and progress</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Create Goal
        </button>
      </div>

      <!-- Goals List -->
      <div class="grid gap-4">
        <div
          v-for="goal in goals"
          :key="goal.id"
          class="bg-white rounded-lg shadow p-6"
        >
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900">{{ goal.goal_title }}</h3>
              <p class="text-sm text-gray-600 mt-1">{{ goal.worker?.name }}</p>
              <p class="text-sm text-gray-500 mt-2">{{ goal.description }}</p>
            </div>
            <span
              :class="[
                'px-3 py-1 text-xs font-medium rounded-full',
                goal.status === 'completed' ? 'bg-green-100 text-green-800' :
                goal.status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                goal.status === 'overdue' ? 'bg-red-100 text-red-800' :
                'bg-gray-100 text-gray-800'
              ]"
            >
              {{ goal.status.replace('_', ' ') }}
            </span>
          </div>

          <!-- Progress Bar -->
          <div class="mb-4">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
              <span>Progress</span>
              <span>{{ goal.progress_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                :style="{ width: `${goal.progress_percentage}%` }"
                class="bg-blue-600 h-2 rounded-full"
              ></div>
            </div>
          </div>

          <div class="flex justify-between items-center text-sm">
            <div class="text-gray-600">
              <span>Target: {{ goal.target_date }}</span>
              <span class="mx-2">•</span>
              <span :class="goal.priority === 'high' || goal.priority === 'critical' ? 'text-red-600 font-medium' : ''">
                {{ goal.priority }} priority
              </span>
            </div>
            <button
              @click="updateProgress(goal)"
              class="text-blue-600 hover:text-blue-800 font-medium"
            >
              Update Progress
            </button>
          </div>
        </div>
      </div>

      <!-- Create Goal Modal -->
      <div
        v-if="showCreateModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Create Goal</h2>
          <form @submit.prevent="createGoal" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Goal Title</label>
              <input
                v-model="goalForm.goal_title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea
                v-model="goalForm.description"
                rows="3"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              ></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input
                  v-model="goalForm.start_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Target Date</label>
                <input
                  v-model="goalForm.target_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
            </div>
            <div class="flex justify-end gap-4">
              <button
                type="button"
                @click="showCreateModal = false"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                Create Goal
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

const props = defineProps<{
  goals: any[];
}>();

const showCreateModal = ref(false);
const goalForm = ref({
  worker_id: null,
  goal_title: '',
  description: '',
  goal_type: 'individual',
  category: 'performance',
  start_date: '',
  target_date: '',
  priority: 'medium',
});

const createGoal = () => {
  router.post(route('cms.performance.goals.store'), goalForm.value, {
    onSuccess: () => {
      showCreateModal.value = false;
      goalForm.value = {
        worker_id: null,
        goal_title: '',
        description: '',
        goal_type: 'individual',
        category: 'performance',
        start_date: '',
        target_date: '',
        priority: 'medium',
      };
    },
  });
};

const updateProgress = (goal: any) => {
  const progress = prompt(`Update progress for "${goal.goal_title}" (0-100):`, goal.progress_percentage);
  const notes = prompt('Progress notes:');
  
  if (progress !== null && notes !== null) {
    router.post(route('cms.performance.goals.update-progress', goal.id), {
      progress_percentage: parseInt(progress),
      notes: notes,
    });
  }
};
</script>
