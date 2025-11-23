<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto bg-white">
    <!-- Header -->
    <div class="sticky top-0 bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between z-10">
      <button @click="$emit('close')" class="p-2 text-gray-500 hover:text-gray-700">
        <ChevronLeftIcon class="h-6 w-6" />
      </button>
      <h2 class="text-lg font-semibold text-gray-900">My Business Plans</h2>
      <button @click="createNew" class="p-2 text-blue-600 hover:text-blue-700">
        <PlusIcon class="h-6 w-6" />
      </button>
    </div>

    <!-- Content -->
    <div class="p-4">
      <!-- Plans List -->
      <div v-if="plans && plans.length > 0" class="space-y-3">
        <div
          v-for="plan in plans"
          :key="plan.id"
          class="bg-white rounded-lg border border-gray-200 p-4 active:bg-gray-50"
          @click="openPlan(plan)"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <h3 class="text-base font-bold text-gray-900">{{ plan.business_name }}</h3>
              <p class="text-xs text-gray-500 mt-1">{{ plan.industry }}</p>
            </div>
            <span
              class="px-2 py-1 text-xs font-medium rounded-full flex-shrink-0 ml-2"
              :class="{
                'bg-green-100 text-green-800': plan.status === 'completed',
                'bg-yellow-100 text-yellow-800': plan.status === 'draft',
                'bg-blue-100 text-blue-800': plan.status === 'in_progress',
                'bg-gray-100 text-gray-800': plan.status === 'archived',
              }"
            >
              {{ formatStatus(plan.status) }}
            </span>
          </div>

          <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
              <p class="text-xs text-gray-500">Location</p>
              <p class="text-sm font-medium text-gray-900">{{ plan.city || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Progress</p>
              <p class="text-sm font-medium text-gray-900">Step {{ plan.current_step || 1 }}/10</p>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="mb-3">
            <div class="w-full bg-gray-200 rounded-full h-1.5">
              <div 
                class="bg-blue-600 h-1.5 rounded-full transition-all"
                :style="{ width: `${((plan.current_step || 1) / 10) * 100}%` }"
              ></div>
            </div>
          </div>

          <div class="flex items-center justify-between text-xs text-gray-500">
            <span>Updated {{ formatDate(plan.updated_at) }}</span>
            <button
              @click.stop="showPlanActions(plan)"
              class="text-blue-600 font-medium"
            >
              Actions
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-20 w-20 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No business plans yet</h3>
        <p class="mt-2 text-sm text-gray-500">Create your first business plan to get started.</p>
        <button
          @click="createNew"
          class="mt-6 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg active:scale-95 transition-transform"
        >
          Create Your First Plan
        </button>
      </div>
    </div>

    <!-- Action Sheet -->
    <div
      v-if="selectedPlan"
      class="fixed inset-0 z-60 bg-black bg-opacity-50"
      @click="selectedPlan = null"
    >
      <div
        class="fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl p-4 animate-slide-up"
        @click.stop
      >
        <div class="w-12 h-1 bg-gray-300 rounded-full mx-auto mb-4"></div>
        
        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ selectedPlan.business_name }}</h3>
        <p class="text-sm text-gray-500 mb-1">Choose an action</p>
        <p v-if="selectedPlan.status === 'completed'" class="text-xs text-green-600 mb-4">✓ Plan complete - You can still edit it anytime</p>
        <p v-else class="text-sm text-gray-500 mb-4"></p>

        <div class="space-y-2">
          <button
            @click="editPlan(selectedPlan)"
            class="w-full flex items-center gap-3 px-4 py-3 bg-blue-50 text-blue-700 rounded-lg font-medium active:bg-blue-100"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            {{ selectedPlan.status === 'completed' ? 'View & Edit Plan' : 'Continue Editing' }}
          </button>

          <button
            @click="exportPlan(selectedPlan)"
            class="w-full flex items-center gap-3 px-4 py-3 bg-gray-50 text-gray-700 rounded-lg font-medium active:bg-gray-100"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export as PDF
          </button>

          <button
            @click="sharePlan(selectedPlan)"
            class="w-full flex items-center gap-3 px-4 py-3 bg-gray-50 text-gray-700 rounded-lg font-medium active:bg-gray-100"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
            </svg>
            Share Plan
          </button>

          <button
            @click="deletePlan(selectedPlan)"
            class="w-full flex items-center gap-3 px-4 py-3 bg-red-50 text-red-600 rounded-lg font-medium active:bg-red-100"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete Plan
          </button>

          <button
            @click="selectedPlan = null"
            class="w-full px-4 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium active:bg-gray-200"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { ChevronLeftIcon, PlusIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

interface Props {
  show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'open-plan']);

const plans = ref<any[]>([]);
const selectedPlan = ref<any>(null);
const loading = ref(false);

// Load plans when modal opens
watch(() => props.show, async (isShown) => {
  if (isShown) {
    await loadPlans();
  }
}, { immediate: true });

const loadPlans = async () => {
  loading.value = true;
  try {
    const response = await axios.get(route('mygrownet.tools.business-plans.api'));
    plans.value = response.data.plans || [];
  } catch (error) {
    console.error('Failed to load plans:', error);
  } finally {
    loading.value = false;
  }
};

const createNew = () => {
  emit('open-plan', null);
  nextTick(() => {
    emit('close');
  });
};

const openPlan = (plan: any) => {
  console.log('openPlan called, emitting open-plan event with:', plan);
  // Emit open-plan first, then close
  emit('open-plan', plan);
  // Use nextTick to ensure the event is processed before closing
  nextTick(() => {
    emit('close');
  });
};

const showPlanActions = (plan: any) => {
  selectedPlan.value = plan;
};

const editPlan = (plan: any) => {
  console.log('editPlan called with:', plan);
  selectedPlan.value = null;
  openPlan(plan);
};

const exportPlan = async (plan: any) => {
  selectedPlan.value = null;
  try {
    window.open(route('mygrownet.tools.business-plan.export', {
      business_plan_id: plan.id,
      export_type: 'pdf',
    }), '_blank');
  } catch (error) {
    alert('Failed to export plan');
  }
};

const sharePlan = async (plan: any) => {
  selectedPlan.value = null;
  
  if (navigator.share) {
    try {
      await navigator.share({
        title: plan.business_name,
        text: `Check out my business plan: ${plan.business_name}`,
        url: window.location.origin + route('mygrownet.tools.business-plan.view', plan.id),
      });
    } catch (error) {
      console.log('Share cancelled');
    }
  } else {
    // Fallback: copy link
    const url = window.location.origin + route('mygrownet.tools.business-plan.view', plan.id);
    navigator.clipboard.writeText(url);
    alert('Link copied to clipboard!');
  }
};

const deletePlan = async (plan: any) => {
  if (confirm(`Are you sure you want to delete "${plan.business_name}"? This cannot be undone.`)) {
    selectedPlan.value = null;
    try {
      await axios.delete(route('mygrownet.tools.business-plan.delete', plan.id));
      await loadPlans();
      alert('Plan deleted successfully');
    } catch (error) {
      alert('Failed to delete plan');
    }
  }
};

const formatStatus = (status: string) => {
  const statusMap: Record<string, string> = {
    'draft': 'Draft',
    'in_progress': 'In Progress',
    'completed': 'Complete',
    'archived': 'Archived',
  };
  return statusMap[status] || status;
};

const formatDate = (date: string) => {
  const d = new Date(date);
  const now = new Date();
  const diffTime = Math.abs(now.getTime() - d.getTime());
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays === 0) return 'Today';
  if (diffDays === 1) return 'Yesterday';
  if (diffDays < 7) return `${diffDays} days ago`;
  if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
  
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<style scoped>
@keyframes slide-up {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}
</style>
