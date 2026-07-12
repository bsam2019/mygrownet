<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

const props = defineProps<{
  recentBookings: any[];
  totalBookings: number;
  upcomingBookings: number;
  hostPropertiesCount: number;
  user: any;
}>();

const initials = computed(() => {
  if (!props.user?.name) return '?';
  const parts = props.user.name.split(' ');
  return parts.length >= 2
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : props.user.name.substring(0, 2).toUpperCase();
});

const statusBadge = (status: string) => {
  const map: Record<string, string> = {
    confirmed: 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
    pending: 'bg-amber-100 text-amber-800 ring-amber-600/20',
    cancelled: 'bg-red-100 text-red-800 ring-red-600/20',
  };
  return map[status] || 'bg-gray-100 text-gray-800 ring-gray-600/20';
};
</script>

<template>
  <Head title="Dashboard - ZamStay" />

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-br from-emerald-600 to-teal-600 rounded-2xl p-6 sm:p-8 mb-8 text-white shadow-lg">
      <div class="flex items-center gap-4">
        <div class="h-14 w-14 rounded-full bg-white/20 backdrop-blur flex items-center justify-center ring-2 ring-white/30 shrink-0">
          <span class="text-xl font-bold text-white">{{ initials }}</span>
        </div>
        <div class="flex-1 min-w-0">
          <h1 class="text-xl sm:text-2xl font-bold truncate">Welcome back, {{ user?.name }}</h1>
          <p class="text-emerald-100 text-sm mt-1">Here's what's happening with your stays</p>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Total Bookings</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ totalBookings }}</p>
          </div>
          <div class="h-10 w-10 rounded-lg bg-emerald-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Upcoming Stays</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ upcomingBookings }}</p>
          </div>
          <div class="h-10 w-10 rounded-lg bg-emerald-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">My Properties</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ hostPropertiesCount }}</p>
          </div>
          <div class="h-10 w-10 rounded-lg bg-emerald-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <h2 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <Link :href="route('zamstay.search')" class="group bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md hover:border-emerald-300 transition-all">
        <div class="h-10 w-10 rounded-lg bg-emerald-100 flex items-center justify-center mb-3 group-hover:bg-emerald-200 transition-colors">
          <svg class="w-5 h-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
        </div>
        <p class="font-semibold text-gray-900 group-hover:text-emerald-700 transition-colors">Search Stays</p>
        <p class="text-sm text-gray-500 mt-1">Find your next destination in Zambia</p>
      </Link>
      <Link :href="route('zamstay.bookings.index')" class="group bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md hover:border-blue-300 transition-all">
        <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors">
          <svg class="w-5 h-5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>
        </div>
        <p class="font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">My Bookings</p>
        <p class="text-sm text-gray-500 mt-1">View and manage reservations</p>
      </Link>
      <Link :href="route('zamstay.host.dashboard')" class="group bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md hover:border-purple-300 transition-all">
        <div class="h-10 w-10 rounded-lg bg-purple-100 flex items-center justify-center mb-3 group-hover:bg-purple-200 transition-colors">
          <svg class="w-5 h-5 text-purple-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
        </div>
        <p class="font-semibold text-gray-900 group-hover:text-purple-700 transition-colors">Host Properties</p>
        <p class="text-sm text-gray-500 mt-1">List and manage accommodations</p>
      </Link>
      <Link :href="route('zamstay.agent.dashboard')" class="group bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md hover:border-amber-300 transition-all">
        <div class="h-10 w-10 rounded-lg bg-amber-100 flex items-center justify-center mb-3 group-hover:bg-amber-200 transition-colors">
          <svg class="w-5 h-5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
        </div>
        <p class="font-semibold text-gray-900 group-hover:text-amber-700 transition-colors">Tour Agent</p>
        <p class="text-sm text-gray-500 mt-1">Book for clients and earn commission</p>
      </Link>
    </div>

    <!-- Recent Bookings -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
          <h2 class="font-semibold text-gray-900">Recent Bookings</h2>
        </div>
        <Link :href="route('zamstay.bookings.index')" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
          View all &rarr;
        </Link>
      </div>

      <div v-if="recentBookings?.length" class="divide-y divide-gray-100">
        <div v-for="booking in recentBookings" :key="booking.id" class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
          <div class="h-12 w-16 rounded-lg bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center shrink-0 overflow-hidden">
            <span v-if="booking.property?.images?.[0]" class="text-2xl">🏠</span>
            <svg v-else class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819" /></svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-900 text-sm truncate">{{ booking.property?.title || 'Property' }}</p>
            <p class="text-xs text-gray-500 mt-0.5">
              <span>{{ booking.check_in }}</span>
              <span class="mx-1">&rarr;</span>
              <span>{{ booking.check_out }}</span>
              <span v-if="booking.total_price" class="ml-2 font-medium text-gray-700">ZMW {{ booking.total_price.toLocaleString() }}</span>
            </p>
          </div>
          <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset capitalize', statusBadge(booking.status)]">
            {{ booking.status }}
          </span>
        </div>
      </div>
      <div v-else class="px-6 py-12 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
        <p class="text-gray-400 text-sm">No bookings yet</p>
        <Link :href="route('zamstay.search')" class="inline-block mt-3 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
          Start Exploring
        </Link>
      </div>
    </div>
  </div>
</template>
