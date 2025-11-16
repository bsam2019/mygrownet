<template>
  <AppLayout title="Support Tickets">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Support Tickets</h1>
        <Link
          :href="route('mygrownet.support.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Ticket
        </Link>
      </div>

      <div v-if="tickets.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new support ticket.</p>
      </div>

      <div v-else class="space-y-4">
        <Link
          v-for="ticket in tickets"
          :key="ticket.id"
          :href="route('mygrownet.support.show', ticket.id)"
          class="block bg-white rounded-lg shadow hover:shadow-md transition p-6"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <span
                  :class="getStatusColor(ticket.status)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ getStatusLabel(ticket.status) }}
                </span>
                <span
                  :class="getPriorityColor(ticket.priority)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ getPriorityLabel(ticket.priority) }}
                </span>
              </div>

              <h3 class="text-lg font-semibold text-gray-900">{{ ticket.subject }}</h3>
              <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ ticket.description }}</p>
              <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                <span>{{ getCategoryLabel(ticket.category) }}</span>
                <span>•</span>
                <span>Created {{ formatDate(ticket.createdAt) }}</span>
              </div>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps<{
  tickets: Array<{
    id: number;
    subject: string;
    description: string;
    category: string;
    priority: string;
    status: string;
    createdAt: string;
  }>;
}>();

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
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};
</script>
