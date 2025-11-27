<template>
  <InvestorLayout :investor="investor" page-title="Announcements" active-page="announcements">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Announcements</h1>
      <p class="mt-1 text-gray-600">Stay updated with the latest news and updates from MyGrowNet</p>
    </div>

    <!-- Filter Tabs -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2 scrollbar-hide">
      <button
        @click="activeFilter = 'all'"
        :class="[
          'px-4 py-2.5 rounded-xl text-sm font-medium transition-all whitespace-nowrap',
          activeFilter === 'all' 
            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25' 
            : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'
        ]"
      >
        All
      </button>
      <button
        v-for="type in types"
        :key="type.value"
        @click="activeFilter = type.value"
        :class="[
          'px-4 py-2.5 rounded-xl text-sm font-medium transition-all whitespace-nowrap',
          activeFilter === type.value 
            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25' 
            : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'
        ]"
      >
        {{ type.label }}
      </button>
    </div>

    <!-- Unread Count Banner -->
    <div v-if="unreadCount > 0" class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-4 mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
          <BellIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
        </div>
        <span class="text-blue-800 font-medium">
          You have <span class="font-bold">{{ unreadCount }}</span> unread announcement{{ unreadCount > 1 ? 's' : '' }}
        </span>
      </div>
    </div>

    <!-- Pinned Announcements -->
    <div v-if="pinnedAnnouncements.length > 0" class="mb-8">
      <div class="flex items-center gap-2 mb-4">
        <BookmarkIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
        <h3 class="text-lg font-semibold text-gray-900">Pinned</h3>
      </div>
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
      <div v-if="filteredAnnouncements.length === 0 && pinnedAnnouncements.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <MegaphoneIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Announcements</h3>
        <p class="text-gray-500">
          {{ activeFilter === 'all' ? 'There are no announcements at this time.' : `No ${activeFilter} announcements found.` }}
        </p>
      </div>
    </div>
  </InvestorLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import AnnouncementCard from '@/components/Investor/AnnouncementCard.vue';
import { BellIcon, BookmarkIcon, MegaphoneIcon } from '@heroicons/vue/24/outline';

interface Announcement {
  id: number;
  title: string;
  content: string;
  summary: string | null;
  type: string;
  priority: string;
  is_pinned: boolean;
  published_at: string;
}

interface AnnouncementWithRead {
  announcement: Announcement;
  is_read: boolean;
}

interface Investor {
  id: number;
  name: string;
  email: string;
}

interface TypeOption {
  value: string;
  label: string;
}

const props = defineProps<{
  investor: Investor;
  announcements: AnnouncementWithRead[];
  types: TypeOption[];
}>();

const activeFilter = ref('all');

const unreadCount = computed(() => props.announcements.filter(a => !a.is_read).length);

const pinnedAnnouncements = computed(() => {
  return props.announcements.filter(a => a.announcement.is_pinned);
});

const filteredAnnouncements = computed(() => {
  let filtered = props.announcements.filter(a => !a.announcement.is_pinned);
  if (activeFilter.value !== 'all') {
    filtered = filtered.filter(a => a.announcement.type === activeFilter.value);
  }
  return filtered;
});

const markAsRead = (id: number) => {
  router.post(route('investor.announcements.read', id), {}, { preserveScroll: true });
};
</script>
