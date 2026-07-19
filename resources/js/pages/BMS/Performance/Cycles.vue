<template>
  <CMSLayout title="Performance Cycles">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Performance Cycles</h1>
          <p class="text-sm text-gray-600 mt-1">Manage performance review cycles</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Create Cycle
        </button>
      </div>

      <!-- Cycles List -->
      <div class="grid gap-4">
        <div
          v-for="cycle in cycles"
          :key="cycle.id"
          class="bg-white rounded-lg shadow p-6"
        >
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ cycle.cycle_name }}</h3>
              <p class="text-sm text-gray-600 mt-1">
                {{ cycle.cycle_type.replace('_', ' ') }} • {{ cycle.start_date }} to {{ cycle.end_date }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              <span
                :class="[
                  'px-3 py-1 text-xs font-medium rounded-full',
                  cycle.status === 'active' ? 'bg-green-100 text-green-800' :
                  cycle.status === 'completed' ? 'bg-gray-100 text-gray-800' :
                  'bg-yellow-100 text-yellow-800'
                ]"
              >
                {{ cycle.status }}
              </span>
              <button
                v-if="cycle.status === 'draft'"
                @click="activateCycle(cycle.id)"
                class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                Activate
              </button>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
              <span class="text-gray-600">Total Reviews:</span>
              <span class="ml-2 font-medium">{{ cycle.reviews?.length || 0 }}</span>
            </div>
            <div>
              <span class="text-gray-600">Review Deadline:</span>
              <span class="ml-2 font-medium">{{ cycle.review_deadline || 'N/A' }}</span>
            </div>
            <div>
              <Link
                :href="route('cms.performance.reviews.index', { cycle_id: cycle.id })"
                class="text-blue-600 hover:text-blue-800 font-medium"
              >
                View Reviews →
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Create Cycle Modal -->
      <div
        v-if="showCreateModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Create Performance Cycle</h2>
          <form @submit.prevent="createCycle" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Cycle Name</label>
              <input
                v-model="cycleForm.cycle_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Cycle Type</label>
              <select
                v-model="cycleForm.cycle_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              >
                <option value="annual">Annual</option>
                <option value="semi_annual">Semi-Annual</option>
                <option value="quarterly">Quarterly</option>
                <option value="probation">Probation</option>
                <option value="project">Project</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input
                  v-model="cycleForm.start_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input
                  v-model="cycleForm.end_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Review Deadline</label>
              <input
                v-model="cycleForm.review_deadline"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              />
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
                Create Cycle
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
import { router, Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

const props = defineProps<{
  cycles: any[];
}>();

const showCreateModal = ref(false);
const cycleForm = ref({
  cycle_name: '',
  cycle_type: 'annual',
  start_date: '',
  end_date: '',
  review_deadline: '',
});

const createCycle = () => {
  router.post(route('cms.performance.cycles.store'), cycleForm.value, {
    onSuccess: () => {
      showCreateModal.value = false;
      cycleForm.value = {
        cycle_name: '',
        cycle_type: 'annual',
        start_date: '',
        end_date: '',
        review_deadline: '',
      };
    },
  });
};

const activateCycle = (cycleId: number) => {
  if (confirm('Activate this performance cycle?')) {
    router.post(route('cms.performance.cycles.activate', cycleId));
  }
};
</script>
