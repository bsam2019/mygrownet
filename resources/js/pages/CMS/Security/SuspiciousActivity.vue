<template>
  <CMSLayout title="Suspicious Activity">
    <div class="space-y-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
              <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Activities</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.total }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
              <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Pending Review</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.pending }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
              <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">High Severity</p>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.high_severity }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              @change="applyFilters"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="reviewed">Reviewed</option>
              <option value="false_positive">False Positive</option>
              <option value="confirmed">Confirmed</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
            <select
              v-model="filters.severity"
              @change="applyFilters"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">All Severities</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Activities List -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="divide-y divide-gray-200">
          <div
            v-for="activity in activities.data"
            :key="activity.id"
            class="p-6 hover:bg-gray-50"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <span :class="getSeverityClass(activity.severity)">
                    {{ activity.severity }}
                  </span>
                  <span :class="getStatusClass(activity.status)">
                    {{ formatStatus(activity.status) }}
                  </span>
                  <span class="text-sm text-gray-500">
                    {{ formatDate(activity.detected_at) }}
                  </span>
                </div>

                <h4 class="text-lg font-medium text-gray-900 mb-1">
                  {{ formatActivityType(activity.activity_type) }}
                </h4>
                <p class="text-sm text-gray-600 mb-2">{{ activity.description }}</p>

                <div class="flex items-center gap-4 text-sm text-gray-500">
                  <span v-if="activity.user">
                    <strong>User:</strong> {{ activity.user.name }}
                  </span>
                  <span>
                    <strong>IP:</strong> {{ activity.ip_address }}
                  </span>
                </div>

                <div v-if="activity.review_notes" class="mt-3 p-3 bg-gray-50 rounded-md">
                  <p class="text-sm text-gray-700">
                    <strong>Review Notes:</strong> {{ activity.review_notes }}
                  </p>
                  <p class="text-xs text-gray-500 mt-1">
                    Reviewed {{ formatDate(activity.reviewed_at) }}
                  </p>
                </div>
              </div>

              <div v-if="activity.status === 'pending'" class="ml-4">
                <button
                  @click="openReviewModal(activity)"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700"
                >
                  Review
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="activities.data.length > 0" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ activities.from }} to {{ activities.to }} of {{ activities.total }} results
            </div>
            <div class="flex gap-2">
              <button
                v-for="link in activities.links"
                :key="link.label"
                @click="changePage(link.url)"
                :disabled="!link.url"
                v-html="link.label"
                :class="[
                  'px-3 py-1 text-sm rounded',
                  link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                  !link.url && 'opacity-50 cursor-not-allowed'
                ]"
              />
            </div>
          </div>
        </div>

        <div v-else class="px-6 py-12 text-center text-gray-500">
          No suspicious activities found
        </div>
      </div>
    </div>

    <!-- Review Modal -->
    <div v-if="showReviewModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Review Activity</h3>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                v-model="reviewForm.status"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="reviewed">Reviewed</option>
                <option value="false_positive">False Positive</option>
                <option value="confirmed">Confirmed Threat</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea
                v-model="reviewForm.notes"
                rows="3"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Add review notes..."
              />
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button
              @click="closeReviewModal"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="submitReview"
              :disabled="processing"
              class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
            >
              {{ processing ? 'Submitting...' : 'Submit Review' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

const props = defineProps<{
  activities: any;
  stats: {
    total: number;
    pending: number;
    high_severity: number;
  };
  filters: {
    status?: string;
    severity?: string;
  };
}>();

const filters = reactive({ ...props.filters });
const showReviewModal = ref(false);
const processing = ref(false);
const selectedActivity = ref<any>(null);
const reviewForm = reactive({
  status: 'reviewed',
  notes: '',
});

const applyFilters = () => {
  router.get(route('cms.security.suspicious-activity'), filters, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  filters.status = '';
  filters.severity = '';
  applyFilters();
};

const changePage = (url: string | null) => {
  if (!url) return;
  router.get(url, {}, {
    preserveState: true,
    preserveScroll: true,
  });
};

const openReviewModal = (activity: any) => {
  selectedActivity.value = activity;
  reviewForm.status = 'reviewed';
  reviewForm.notes = '';
  showReviewModal.value = true;
};

const closeReviewModal = () => {
  showReviewModal.value = false;
  selectedActivity.value = null;
};

const submitReview = () => {
  if (!selectedActivity.value) return;
  
  processing.value = true;
  router.post(
    route('cms.security.suspicious-activity.review', selectedActivity.value.id),
    reviewForm,
    {
      onFinish: () => {
        processing.value = false;
        closeReviewModal();
      },
    }
  );
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleString();
};

const formatActivityType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const formatStatus = (status: string) => {
  return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getSeverityClass = (severity: string) => {
  const classes: Record<string, string> = {
    low: 'px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
    medium: 'px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800',
    high: 'px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800',
  };
  return classes[severity] || 'px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800';
};

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    pending: 'px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800',
    reviewed: 'px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
    false_positive: 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800',
    confirmed: 'px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800',
  };
  return classes[status] || 'px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800';
};
</script>
