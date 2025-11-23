<template>
  <MemberLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">My Business Plans</h1>
            <p class="mt-2 text-gray-600">Manage and edit your business plans</p>
          </div>
          <button
            @click="createNew"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create New Plan
          </button>
        </div>
      </div>

      <!-- Plans List -->
      <div v-if="plans.data.length > 0" class="space-y-4">
        <div
          v-for="plan in plans.data"
          :key="plan.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-xl font-bold text-gray-900">{{ plan.business_name }}</h3>
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="{
                    'bg-green-100 text-green-800': plan.status === 'completed',
                    'bg-yellow-100 text-yellow-800': plan.status === 'draft',
                    'bg-gray-100 text-gray-800': plan.status === 'archived',
                  }"
                >
                  {{ plan.status }}
                </span>
              </div>
              
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div>
                  <p class="text-xs text-gray-500">Industry</p>
                  <p class="text-sm font-medium text-gray-900">{{ plan.industry || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Location</p>
                  <p class="text-sm font-medium text-gray-900">{{ plan.city }}, {{ plan.country }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Progress</p>
                  <p class="text-sm font-medium text-gray-900">Step {{ plan.current_step || 1 }} of 10</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Last Updated</p>
                  <p class="text-sm font-medium text-gray-900">{{ formatDate(plan.updated_at) }}</p>
                </div>
              </div>
            </div>

            <div class="flex flex-col gap-2 ml-4">
              <button
                @click="editPlan(plan.id)"
                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
              >
                {{ plan.status === 'completed' ? 'View' : 'Continue' }}
              </button>
              <button
                @click="exportPlan(plan.id)"
                class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200"
              >
                Export
              </button>
              <button
                @click="deletePlan(plan.id)"
                class="px-4 py-2 bg-red-50 text-red-600 text-sm font-medium rounded-lg hover:bg-red-100"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No business plans yet</h3>
        <p class="mt-2 text-sm text-gray-500">Get started by creating your first business plan.</p>
        <button
          @click="createNew"
          class="mt-6 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700"
        >
          Create Your First Plan
        </button>
      </div>

      <!-- Pagination -->
      <div v-if="plans.data.length > 0 && (plans.prev_page_url || plans.next_page_url)" class="mt-8 flex justify-center gap-2">
        <button
          v-if="plans.prev_page_url"
          @click="goToPage(plans.current_page - 1)"
          class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          Previous
        </button>
        <span class="px-4 py-2 text-gray-700">
          Page {{ plans.current_page }} of {{ plans.last_page }}
        </span>
        <button
          v-if="plans.next_page_url"
          @click="goToPage(plans.current_page + 1)"
          class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          Next
        </button>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

interface Props {
  plans: {
    data: any[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
  };
  userTier: string;
}

const props = defineProps<Props>();

const createNew = () => {
  router.visit(route('mygrownet.tools.business-plan-generator'));
};

const editPlan = (planId: number) => {
  router.visit(route('mygrownet.tools.business-plan-generator', { plan: planId }));
};

const exportPlan = (planId: number) => {
  window.location.href = route('mygrownet.tools.business-plan.export', {
    business_plan_id: planId,
    export_type: 'pdf',
  });
};

const deletePlan = (planId: number) => {
  if (confirm('Are you sure you want to delete this business plan? This action cannot be undone.')) {
    router.delete(route('mygrownet.tools.business-plan.delete', planId), {
      onSuccess: () => {
        alert('Business plan deleted successfully');
      },
    });
  }
};

const goToPage = (page: number) => {
  router.visit(route('mygrownet.tools.business-plans.list', { page }));
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};
</script>
