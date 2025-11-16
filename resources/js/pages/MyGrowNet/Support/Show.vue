<template>
  <AppLayout :title="`Ticket #${ticket.id}`">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-6">
        <Link
          :href="route('mygrownet.support.index')"
          class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Tickets
        </Link>
      </div>

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
              class="bg-gray-50 rounded-lg p-4"
            >
              <div class="flex items-start justify-between mb-2">
                <span class="font-medium text-gray-900">
                  {{ comment.userId === ticket.userId ? 'You' : 'Support Team' }}
                </span>
                <span class="text-sm text-gray-500">
                  {{ formatDateTime(comment.createdAt) }}
                </span>
              </div>
              <p class="text-gray-700 whitespace-pre-wrap">{{ comment.comment }}</p>
            </div>
          </div>

          <form v-if="!ticket.status.includes('closed')" @submit.prevent="submitComment" class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Add Comment
            </label>
            <textarea
              v-model="commentForm.comment"
              rows="4"
              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
              placeholder="Type your message..."
              required
            ></textarea>
            <p v-if="commentForm.errors.comment" class="mt-1 text-sm text-red-600">
              {{ commentForm.errors.comment }}
            </p>
            <button
              type="submit"
              :disabled="commentForm.processing"
              class="mt-3 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              {{ commentForm.processing ? 'Sending...' : 'Send Comment' }}
            </button>
          </form>

          <div v-else class="mt-6 p-4 bg-gray-100 rounded-lg text-center text-gray-600">
            This ticket is closed. No further comments can be added.
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

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
  };
  comments: Array<{
    id: number;
    userId: number;
    comment: string;
    createdAt: string;
  }>;
}>();

const commentForm = useForm({
  comment: '',
});

const submitComment = () => {
  commentForm.post(route('mygrownet.support.comment', props.ticket.id), {
    preserveScroll: true,
    onSuccess: () => {
      commentForm.reset();
    },
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
