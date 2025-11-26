<template>
  <AdminLayout>
    <div class="p-6 max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <Link
          :href="route('admin.investor-announcements.index')"
          class="text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-2"
        >
          <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
          Back to Announcements
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Create Announcement</h1>
        <p class="text-gray-600 mt-1">Create a new announcement for your investors</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white rounded-xl shadow-lg p-6 space-y-6">
        <!-- Title -->
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
            Title <span class="text-red-500">*</span>
          </label>
          <input
            id="title"
            v-model="form.title"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Enter announcement title"
            required
          />
          <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
        </div>

        <!-- Type & Priority -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
              Type <span class="text-red-500">*</span>
            </label>
            <select
              id="type"
              v-model="form.type"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option v-for="type in types" :key="type.value" :value="type.value">
                {{ type.label }}
              </option>
            </select>
          </div>
          <div>
            <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
              Priority <span class="text-red-500">*</span>
            </label>
            <select
              id="priority"
              v-model="form.priority"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option v-for="priority in priorities" :key="priority.value" :value="priority.value">
                {{ priority.label }}
              </option>
            </select>
          </div>
        </div>

        <!-- Summary -->
        <div>
          <label for="summary" class="block text-sm font-medium text-gray-700 mb-1">
            Summary
          </label>
          <input
            id="summary"
            v-model="form.summary"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Brief summary (shown in lists)"
            maxlength="500"
          />
          <p class="mt-1 text-xs text-gray-500">Optional. Max 500 characters.</p>
        </div>

        <!-- Content -->
        <div>
          <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
            Content <span class="text-red-500">*</span>
          </label>
          <textarea
            id="content"
            v-model="form.content"
            rows="8"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Write your announcement content here..."
            required
          ></textarea>
          <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
        </div>

        <!-- Expires At -->
        <div>
          <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">
            Expiration Date
          </label>
          <input
            id="expires_at"
            v-model="form.expires_at"
            type="datetime-local"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
          <p class="mt-1 text-xs text-gray-500">Optional. Leave empty for no expiration.</p>
        </div>

        <!-- Options -->
        <div class="space-y-3">
          <label class="flex items-center gap-3">
            <input
              v-model="form.is_pinned"
              type="checkbox"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="text-sm text-gray-700">Pin this announcement (appears at top)</span>
          </label>
          <label class="flex items-center gap-3">
            <input
              v-model="form.send_email"
              type="checkbox"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="text-sm text-gray-700">Send email notification to investors</span>
          </label>
          <label class="flex items-center gap-3">
            <input
              v-model="form.publish_immediately"
              type="checkbox"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="text-sm text-gray-700">Publish immediately</span>
          </label>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t">
          <Link
            :href="route('admin.investor-announcements.index')"
            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
          >
            {{ form.processing ? 'Creating...' : 'Create Announcement' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

interface TypeOption {
  value: string
  label: string
  color: string
}

const props = defineProps<{
  types: TypeOption[]
  priorities: TypeOption[]
}>()

const form = useForm({
  title: '',
  content: '',
  summary: '',
  type: 'general',
  priority: 'normal',
  is_pinned: false,
  send_email: false,
  expires_at: '',
  publish_immediately: false,
})

const submit = () => {
  form.post(route('admin.investor-announcements.store'))
}
</script>
