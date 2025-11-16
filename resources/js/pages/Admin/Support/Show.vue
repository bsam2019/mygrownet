<template>
  <AdminLayout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-6">
        <Link
          :href="route('admin.support.index')"
          class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Tickets
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
              <div class="flex items-start justify-between mb-4">
                <div>
                  <div class="flex items-center gap-3 mb-2">
                    <span
                      :class="getStatusColor(ticket.status)"
                      class="px-3 py-1 text-sm font-medium rounded-full"
                    >
                      {{ getStatusLabel(ticket.status) }}
                    </span>
                    <span
                      :class="getPriorityColor(ticket.priority)"
                      class="px-3 py-1 text-sm font-medium rounded-full"
                    >
                      {{ getPriorityLabel(ticket.priority) }}
                    </span>
                  </div>
                  <h1 class="text-2xl font-bold text-gray-900">{{ ticket.subject }}</h1>
                  <p class="text-sm text-gray-500 mt-2">
                    Ticket #{{ ticket.id }} • {{ getCategoryLabel(ticket.category) }} • 
                    Created {{ formatDate(ticket.createdAt) }}
                  </p>
                </div>
              </div>

              <div class="prose max-w-none">
                <p class="text-gray-700 whitespace-pre-wrap">{{ ticket.description }}</p>
              </div>
            </div>

            <div class="p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Comments</h2>

              <div v-if="comments.length === 0" class="text-center py-8 text-gray-500">
                No comments yet
              </div>

              <div v-else class="space-y-4 mb-6">
                <div
                  v-for="comment in comments"
                  :key="comment.id"
                  :class="[
                    'rounded-lg p-4',
                    comment.isInternal ? 'bg-yellow-50 border border-yellow-200' : 'bg-gray-50'
                  ]"
                >
                  <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                      <span class="font-medium text-gray-900">
                        {{ comment.userId === ticket.userId ? 'Member' : 'Support Team' }}
                      </span>
                      <span v-if="comment.isInternal" class="text-xs bg-yellow-200 text-yellow-800 px-2 py-0.5 rounded">
                        Internal Note
                      </span>
                    </div>
                    <span class="text-sm text-gray-500">
                      {{ formatDateTime(comment.createdAt) }}
                    </span>
                  </div>
                  <p class="text-gray-700 whitespace-pre-wrap">{{ comment.comment }}</p>
                </div>
              </div>

              <form @submit.prevent="submitComment" class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Add Comment
                </label>
                <textarea
                  v-model="commentForm.comment"
                  rows="4"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                  placeholder="Type your response..."
                  required
                ></textarea>
                <div class="mt-3 flex items-center justify-between">
                  <label class="flex items-center">
                    <input
                      v-model="commentForm.is_internal"
                      type="checkbox"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="ml-2 text-sm text-gray-600">Internal note (not visible to member)</span>
                  </label>
                  <button
                    type="submit"
                    :disabled="commentForm.processing"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                  >
                    {{ commentForm.processing ? 'Sending...' : 'Send Comment' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ticket Actions</h3>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select
                  v-model="statusForm.status"
                  @change="updateStatus"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="open">Open</option>
                  <option value="in_progress">In Progress</option>
                  <option value="waiting">Waiting for Response</option>
                  <option value="resolved">Resolved</option>
                  <option value="closed">Closed</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Assign To</label>
                <input
                  v-model="assignForm.admin_id"
                  type="number"
                  placeholder="Admin User ID"
                  class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
                <button
                  @click="assignTicket"
                  :disabled="assignForm.processing || !assignForm.admin_id"
                  class="mt-2 w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 disabled:opacity-50"
                >
                  Assign
                </button>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ticket Info</h3>
            <dl class="space-y-3 text-sm">
              <div>
                <dt class="text-gray-500">Member ID</dt>
                <dd class="text-gray-900 font-medium">#{{ ticket.userId }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Category</dt>
                <dd class="text-gray-900">{{ getCategoryLabel(ticket.category) }}</dd>
              </div>
              <div>
                <dt class="text-gray-500">Created</dt>
                <dd class="text-gray-900">{{ formatDate(ticket.createdAt) }}</dd>
              </div>
              <div v-if="ticket.resolvedAt">
                <dt class="text-gray-500">Resolved</dt>
                <dd class="text-gray-900">{{ formatDate(ticket.resolvedAt) }}</dd>
              </div>
              <div v-if="ticket.assignedTo">
                <dt class="text-gray-500">Assigned To</dt>
                <dd class="text-gray-900">Admin #{{ ticket.assignedTo }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
  ticket: {
    id: number;
    userId: number;
    subject: string;
    description: string;
    category: string;
    priority: string;
    status: string;
    createdAt: string;
    resolvedAt?: string;
    assignedTo?: number;
  };
  comments: Array<{
    id: number;
    userId: number;
    comment: string;
    isInternal: boolean;
    createdAt: string;
  }>;
}>();

const commentForm = useForm({
  comment: '',
  is_internal: false,
});

const statusForm = ref({
  status: props.ticket.status,
});

const assignForm = useForm({
  admin_id: props.ticket.assignedTo || null,
});

const submitComment = () => {
  commentForm.post(route('admin.support.comment', props.ticket.id), {
    preserveScroll: true,
    onSuccess: () => {
      commentForm.reset();
    },
  });
};

const updateStatus = () => {
  router.post(
    route('admin.support.status', props.ticket.id),
    { status: statusForm.value.status },
    { preserveScroll: true }
  );
};

const assignTicket = () => {
  assignForm.post(route('admin.support.assign', props.ticket.id), {
    preserveScroll: true,
  });
};

const getStatusColor = (status: string) => {
  const colors = {
    open: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-amber-100 text-amber-800',
    waiting: 'bg-purple-100 text-purple-800',
    resolved: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800',
  };
  return colors[status as keyof typeof colors] || colors.open;
};

const getStatusLabel = (status: string) => {
  const labels = {
    open: 'Open',
    in_progress: 'In Progress',
    waiting: 'Waiting',
    resolved: 'Resolved',
    closed: 'Closed',
  };
  return labels[status as keyof typeof labels] || status;
};

const getPriorityColor = (priority: string) => {
  const colors = {
    low: 'bg-gray-100 text-gray-600',
    medium: 'bg-blue-100 text-blue-600',
    high: 'bg-amber-100 text-amber-600',
    urgent: 'bg-red-100 text-red-600',
  };
  return colors[priority as keyof typeof colors] || colors.medium;
};

const getPriorityLabel = (priority: string) => {
  return priority.charAt(0).toUpperCase() + priority.slice(1);
};

const getCategoryLabel = (category: string) => {
  const labels = {
    technical: 'Technical Support',
    financial: 'Financial Issue',
    account: 'Account Management',
    general: 'General Inquiry',
  };
  return labels[category as keyof typeof labels] || category;
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
  });
};

const formatDateTime = (date: string) => {
  return new Date(date).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
};
</script>
