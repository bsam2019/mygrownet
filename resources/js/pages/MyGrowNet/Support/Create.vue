<template>
  <AppLayout title="Create Support Ticket">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

      <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Support Ticket</h1>

        <form @submit.prevent="submit">
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Category <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.category"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                required
              >
                <option value="">Select a category</option>
                <option value="technical">Technical Support</option>
                <option value="financial">Financial Issue</option>
                <option value="account">Account Management</option>
                <option value="general">General Inquiry</option>
              </select>
              <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">
                {{ form.errors.category }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Priority
              </label>
              <select
                v-model="form.priority"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Subject <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.subject"
                type="text"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                placeholder="Brief description of your issue"
                required
              />
              <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">
                {{ form.errors.subject }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Description <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="form.description"
                rows="6"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                placeholder="Please provide detailed information about your issue..."
                required
              ></textarea>
              <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                {{ form.errors.description }}
              </p>
              <p class="mt-1 text-sm text-gray-500">
                Minimum 10 characters
              </p>
            </div>

            <div class="flex gap-3">
              <button
                type="submit"
                :disabled="form.processing"
                class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                {{ form.processing ? 'Creating...' : 'Create Ticket' }}
              </button>
              <Link
                :href="route('mygrownet.support.index')"
                class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </Link>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
  category: '',
  priority: 'medium',
  subject: '',
  description: '',
});

const submit = () => {
  form.post(route('mygrownet.support.store'));
};
</script>
