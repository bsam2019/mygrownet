<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Investor Announcements</h1>
          <p class="text-gray-600 mt-1">Manage announcements for your investors</p>
        </div>
        <Link
          :href="route('admin.investor-announcements.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
          Create Announcement
        </Link>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-600">Total Announcements</div>
          <div class="text-2xl font-bold text-gray-900">{{ announcements.length }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-600">Published</div>
          <div class="text-2xl font-bold text-green-600">{{ publishedCount }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-600">Drafts</div>
          <div class="text-2xl font-bold text-amber-600">{{ draftCount }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-600">Pinned</div>
          <div class="text-2xl font-bold text-blue-600">{{ pinnedCount }}</div>
        </div>
      </div>

      <!-- Announcements Table -->
      <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Announcement
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Type
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Priority
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Read Count
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="announcement in announcements" :key="announcement.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div v-if="announcement.is_pinned" class="mr-2">
                      <BookmarkIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                    </div>
                    <div>
                      <div class="font-medium text-gray-900">{{ announcement.title }}</div>
                      <div class="text-sm text-gray-500">{{ truncate(announcement.summary || announcement.content, 60) }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="getTypeBadgeClass(announcement.type_color)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ announcement.type_label }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span :class="getPriorityBadgeClass(announcement.priority_color)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ announcement.priority_label }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span v-if="announcement.published_at" class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                    Published
                  </span>
                  <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                    Draft
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ announcement.read_count }} reads
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <Link
                      :href="route('admin.investor-announcements.show', announcement.id)"
                      class="text-blue-600 hover:text-blue-800"
                    >
                      <EyeIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    <Link
                      :href="route('admin.investor-announcements.edit', announcement.id)"
                      class="text-gray-600 hover:text-gray-800"
                    >
                      <PencilIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    <button
                      v-if="!announcement.published_at"
                      @click="publishAnnouncement(announcement.id)"
                      class="text-green-600 hover:text-green-800"
                      title="Publish"
                    >
                      <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                    <button
                      v-else
                      @click="unpublishAnnouncement(announcement.id)"
                      class="text-amber-600 hover:text-amber-800"
                      title="Unpublish"
                    >
                      <XCircleIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                    <button
                      @click="deleteAnnouncement(announcement.id)"
                      class="text-red-600 hover:text-red-800"
                    >
                      <TrashIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="announcements.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  <MegaphoneIcon class="h-12 w-12 mx-auto mb-4 text-gray-400" aria-hidden="true" />
                  <p>No announcements yet</p>
                  <Link
                    :href="route('admin.investor-announcements.create')"
                    class="text-blue-600 hover:text-blue-800 mt-2 inline-block"
                  >
                    Create your first announcement
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  PlusIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  BookmarkIcon,
  CheckCircleIcon,
  XCircleIcon,
  MegaphoneIcon,
} from '@heroicons/vue/24/outline'

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
  read_count: number
}

const props = defineProps<{
  announcements: Announcement[]
  types: { value: string; label: string; color: string }[]
  priorities: { value: string; label: string; color: string }[]
}>()

const publishedCount = computed(() => props.announcements.filter(a => a.published_at).length)
const draftCount = computed(() => props.announcements.filter(a => !a.published_at).length)
const pinnedCount = computed(() => props.announcements.filter(a => a.is_pinned).length)

const truncate = (text: string, length: number) => {
  if (text.length <= length) return text
  return text.substring(0, length) + '...'
}

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

const publishAnnouncement = (id: number) => {
  if (confirm('Publish this announcement? It will be visible to all investors.')) {
    router.post(route('admin.investor-announcements.publish', id))
  }
}

const unpublishAnnouncement = (id: number) => {
  if (confirm('Unpublish this announcement? It will no longer be visible to investors.')) {
    router.post(route('admin.investor-announcements.unpublish', id))
  }
}

const deleteAnnouncement = (id: number) => {
  if (confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
    router.delete(route('admin.investor-announcements.destroy', id))
  }
}
</script>
