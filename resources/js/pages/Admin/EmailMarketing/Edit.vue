<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({
  layout: AdminLayout,
});

interface Props {
  campaign: any;
}

const props = defineProps<Props>();

const form = useForm({
  name: props.campaign.name,
  subject: props.campaign.subject,
  content: props.campaign.content,
  type: props.campaign.type,
  status: props.campaign.status,
});

const submit = () => {
  form.put(route('admin.email-marketing.update', props.campaign.id));
};
</script>

<template>
  <Head title="Edit Campaign - Email Marketing" />

  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Campaign</h1>
        <p class="mt-1 text-sm text-gray-500">Update your email campaign details</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700">Campaign Name</label>
            <input
              v-model="form.name"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Subject</label>
            <input
              v-model="form.subject"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Content</label>
            <textarea
              v-model="form.content"
              rows="10"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              required
            />
          </div>

          <div class="flex justify-end gap-3">
            <button
              type="button"
              @click="$inertia.visit(route('admin.email-marketing.index'))"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
            >
              Update Campaign
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
