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
        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ announcement.title }}</h1>
            <div class="flex items-center gap-3 mt-2">
              <span :class="getTypeBadgeClass(announcement.type_color)" class="px-2 py-1 text-xs font-medium rounded-full">
                {{ announcement.type_label }}
              </span>
              <span :class="getPriorityBadgeClass(announcement.priority_color)" class="px-2 py-1 text-xs font-medium rounded-full">
                {{ announcement.priority_label }}
              </span>
              <span v-if="announcement.published_at" class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                Published
              </span>
              <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                Draft
              </span>
              <span v-if="announcement.is_pinned" class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                Pinned
              </span>
            </div>
          </div>
          <div class="flex gap-2">
            <Link
              :href="route('admin.investor-announcements.edit', announcement.id)"
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
            >
              Edit
            </Link>
            <button
              v-if="!announcement.published_at"
              @click="publish"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
            >
              Publish
            </button>
            <button
              v-else
              @click="unpublish"
              class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors"
            >
              Unpublish
            </button>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-600">Read by Investors</div>
          <div class="text-2xl font-bold text-blue-600">{{ stats.read_count }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-600">Published</div>
          <div class="text-lg font-medium text-gray-900">
            {{ announcement.published_at ? formatDate(announcement.published_at) : 'Not published' }}
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-600">Expires</div>
          <div class="text-lg font-medium text-gray-900">
            {{ announcement.expires_at ? formatDate(announcement.expires_at) : 'Never' }}
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div v-if="announcement.summary" class="mb-4 pb-4 border-b">
          <h3 class="text-sm font-medium text-gray-500 mb-1">Summary</h3>
          <p class="text-gray-700">{{ announcement.summary }}</p>
        </div>
        
        <div>
          <h3 class="text-sm font-medium text-gray-500 mb-2">Content</h3>
          <div class="prose max-w-none text-gray-700 whitespace-pre-wrap">{{ announcement.content }}</div>
        </div>

        <div class="mt-6 pt-4 border-t text-sm text-gray-500">
          <p>Created: {{ formatDate(announcement.created_at) }}</p>
          <p v-if="announcement.updated_at !== announcement.created_at">
            Last updated: {{ formatDate(announcement.updated_at) }}
          </p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

interface Announcement {
  id: number
  title: string
  content: string
  summary: string | null
  type: string
  type_label: string
  type_color: string
  priority: string
  priority_label: string
  priority_color: string
  is_pinned: boolean
  published_at: string | null
  expires_at: string | null
  created_at: string
  updated_at: string
}

interface Stats {
  read_count: number
}

const props = defineProps<{
  announcement: Announcement
  stats: Stats
}>()

const getTypeBadgeClass = (color: string) => {
  const colors: Record<string, string> = {
    blue: 'bg-blue-100 text-blue-800',
    green: 'bg-green-100 text-green-800',
    emerald: 'bg-emerald-100 text-emerald-800',
    purple: 'bg-purple-100 text-purple-800',
    red: 'bg-red-100 text-red-800',
    amber: 'bg-amber-100 text-amber-800',
  }
  return colors[color] || 'bg-gray-100 text-gray-800'
}

const getPriorityBadgeClass = (color: string) => {
  const colors: Record<string, string> = {
    gray: 'bg-gray-100 text-gray-800',
    blue: 'bg-blue-100 text-blue-800',
    amber: 'bg-amber-100 text-amber-800',
    red: 'bg-red-100 text-red-800',
  }
  return colors[color] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const publish = () => {
  if (confirm('Publish this announcement?')) {
    router.post(route('admin.investor-announcements.publish', props.announcement.id))
  }
}

const unpublish = () => {
  if (confirm('Unpublish this announcement?')) {
    router.post(route('admin.investor-announcements.unpublish', props.announcement.id))
  }
}
</script>
