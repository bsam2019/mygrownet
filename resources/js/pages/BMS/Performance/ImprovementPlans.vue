<template>
  <CMSLayout title="Performance Improvement Plans">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Performance Improvement Plans</h1>
          <p class="text-sm text-gray-600 mt-1">Manage employee improvement plans</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Create PIP
        </button>
      </div>

      <!-- PIPs List -->
      <div class="grid gap-4">
        <div
          v-for="pip in pips"
          :key="pip.id"
          class="bg-white rounded-lg shadow p-6"
        >
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900">{{ pip.plan_title }}</h3>
              <p class="text-sm text-gray-600 mt-1">{{ pip.worker?.name }}</p>
            </div>
            <span
              :class="[
                'px-3 py-1 text-xs font-medium rounded-full',
                pip.status === 'successful' ? 'bg-green-100 text-green-800' :
                pip.status === 'active' ? 'bg-blue-100 text-blue-800' :
                pip.status === 'unsuccessful' ? 'bg-red-100 text-red-800' :
                'bg-gray-100 text-gray-800'
              ]"
            >
              {{ pip.status }}
            </span>
          </div>

          <div class="space-y-2 mb-4">
            <div>
              <span class="text-sm font-medium text-gray-700">Performance Issues:</span>
              <p class="text-sm text-gray-600 mt-1">{{ pip.performance_issues }}</p>
            </div>
            <div>
              <span class="text-sm font-medium text-gray-700">Improvement Actions:</span>
              <p class="text-sm text-gray-600 mt-1">{{ pip.improvement_actions }}</p>
            </div>
          </div>

          <!-- Milestones -->
          <div v-if="pip.milestones?.length" class="mb-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Milestones:</h4>
            <div class="space-y-2">
              <div
                v-for="milestone in pip.milestones"
                :key="milestone.id"
                class="flex items-center justify-between p-2 bg-gray-50 rounded"
              >
                <div class="flex items-center gap-2">
                  <input
                    type="checkbox"
                    :checked="milestone.is_completed"
                    @change="completeMilestone(milestone.id)"
                    class="h-4 w-4 text-blue-600 rounded"
                  />
                  <span class="text-sm" :class="milestone.is_completed ? 'line-through text-gray-500' : 'text-gray-900'">
                    {{ milestone.milestone_title }}
                  </span>
                </div>
                <span class="text-xs text-gray-500">{{ milestone.target_date }}</span>
              </div>
            </div>
          </div>

          <div class="flex justify-between items-center text-sm">
            <div class="text-gray-600">
              {{ pip.start_date }} to {{ pip.end_date }} • Review: {{ pip.review_date }}
            </div>
            <button
              v-if="pip.status === 'active'"
              @click="closePip(pip)"
              class="text-blue-600 hover:text-blue-800 font-medium"
            >
              Close PIP
            </button>
          </div>
        </div>
      </div>

      <!-- Create PIP Modal -->
      <div
        v-if="showCreateModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Create Performance Improvement Plan</h2>
          <form @submit.prevent="createPip" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Plan Title</label>
              <input
                v-model="pipForm.plan_title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Performance Issues</label>
              <textarea
                v-model="pipForm.performance_issues"
                rows="3"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Improvement Actions</label>
              <textarea
                v-model="pipForm.improvement_actions"
                rows="3"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              ></textarea>
            </div>
            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input
                  v-model="pipForm.start_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Review Date</label>
                <input
                  v-model="pipForm.review_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input
                  v-model="pipForm.end_date"
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
                Create PIP
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
  pips: any[];
}>();

const showCreateModal = ref(false);
const pipForm = ref({
  worker_id: null,
  plan_title: '',
  performance_issues: '',
  improvement_actions: '',
  start_date: '',
  review_date: '',
  end_date: '',
});

const createPip = () => {
  router.post(route('cms.performance.pips.store'), pipForm.value, {
    onSuccess: () => {
      showCreateModal.value = false;
      pipForm.value = {
        worker_id: null,
        plan_title: '',
        performance_issues: '',
        improvement_actions: '',
        start_date: '',
        review_date: '',
        end_date: '',
      };
    },
  });
};

const completeMilestone = (milestoneId: number) => {
  router.post(route('cms.performance.pips.milestone-complete', milestoneId), {
    notes: 'Milestone completed',
  });
};

const closePip = (pip: any) => {
  const status = prompt('Close PIP as (successful/unsuccessful/extended/cancelled):', 'successful');
  const notes = prompt('Outcome notes:');
  
  if (status && notes) {
    router.post(route('cms.performance.pips.close', pip.id), {
      status: status,
      outcome_notes: notes,
    });
  }
};
</script>
