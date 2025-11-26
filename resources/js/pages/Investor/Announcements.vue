<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-lg">{{ getInitials(investor.name) }}</span>
            </div>
            <div>
              <h1 class="text-xl font-bold text-gray-900">{{ investor.name }}</h1>
              <p class="text-sm text-gray-600">Investor Portal - Announcements</p>
            </div>
          </div>
          <nav class="flex items-center gap-3">
            <Link :href="route('investor.dashboard')" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
              Dashboard
            </Link>
            <Link :href="route('investor.reports')" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
              Reports
            </Link>
            <Link :href="route('investor.documents')" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
              Documents
            </Link>
            <Link :href="route('investor.messages')" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
              Messages
            </Link>
            <Link :href="route('investor.logout')" method="post" as="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
              Logout
            </Link>
          </nav>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Announcements</h2>
        <p class="text-gray-600 mt-1">Stay updated with the latest news and updates from MyGrowNet</p>
      </div>

      <!-- Filter Tabs -->
      <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <button
          @click="activeFilter = 'all'"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap',
            activeFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'
          ]"
        >
          All
        </button>
        <button
          v-for="type in types"
          :key="type.value"
          @click="activeFilter = type.value"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap',
            activeFilter === type.value ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'
          ]"
        >
          {{ type.label }}
        </button>
      </div>

      <!-- Unread Count -->
      <div v-if="unreadCount > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-2">
          <BellIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
          <span class="text-blue-800">You have <strong>{{ unreadCount }}</strong> unread announcement{{ unreadCount > 1 ? 's' : '' }}</span>
        </div>
      </div>

      <!-- Pinned Announcements -->
      <div v-if="pinnedAnnouncements.length > 0" class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <BookmarkIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
          Pinned
        </h3>
        <div class="space-y-4">
          <AnnouncementCard
            v-for="item in pinnedAnnouncements"
            :key="item.announcement.id"
            :announcement="item.announcement"
            :is-read="item.is_read"
            :pinned="true"
            @mark-read="markAsRead"
          />
        </div>
      </div>

      <!-- Regular Announcements -->
      <div>
        <h3 v-if="pinnedAnnouncements.length > 0" class="text-lg font-semibold text-gray-900 mb-4">
          Recent Announcements
        </h3>
        <div class="space-y-4">
          <AnnouncementCard
            v-for="item in filteredAnnouncements"
            :key="item.announcement.id"
            :announcement="item.announcement"
            :is-read="item.is_read"
            @mark-read="markAsRead"
          />
        </div>

        <!-- Empty State -->
        <div v-if="filteredAnnouncements.length === 0 && pinnedAnnouncements.length === 0" class="bg-white rounded-xl shadow-lg p-12 text-center">
          <MegaphoneIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" aria-hidden="true" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Announcements</h3>
          <p class="text-gray-600">
            {{ activeFilter === 'all' ? 'There are no announcements at this time.' : `No ${activeFilter} announcements found.` }}
          </p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { BellIcon, BookmarkIcon, MegaphoneIcon } from '@heroicons/vue/24/outline'
import AnnouncementCard from '@/components/Investor/AnnouncementCard.vue'

interface Announcement {
  id: number
  title: string
  content: string
  summary: string | null
  type: string
  priority: string
  is_pinned: boolean
  published_at: string
}

interface AnnouncementWithRead {
  announcement: Announcement
  is_read: boolean
}

interface Investor {
  id: number
  name: string
  email: string
}

interface TypeOption {
  value: string
  label: string
}

const props = defineProps<{
  investor: Investor
  announcements: AnnouncementWithRead[]
  types: TypeOption[]
}>()

const activeFilter = ref('all')

const getInitials = (name: string): string => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
}

const unreadCount = computed(() => props.announcements.filter(a => !a.is_read).length)

const pinnedAnnouncements = computed(() => {
  return props.announcements.filter(a => a.announcement.is_pinned)
})

const filteredAnnouncements = computed(() => {
  let filtered = props.announcements.filter(a => !a.announcement.is_pinned)
  
  if (activeFilter.value !== 'all') {
    filtered = filtered.filter(a => a.announcement.type === activeFilter.value)
  }
  
  return filtered
})

const markAsRead = (id: number) => {
  router.post(route('investor.announcements.read', id), {}, {
    preserveScroll: true,
  })
}
</script>
