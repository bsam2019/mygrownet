<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

const props = defineProps<{
  stats: {
    totalProperties: number;
    totalBookings: number;
    totalAgents: number;
    totalUsers: number;
    pendingAgents: number;
    totalRevenue: number;
    cancelledBookings: number;
    pendingBookings: number;
    confirmedBookings: number;
  };
  recentBookings: any[];
  pendingAgentRequests: any[];
  recentProperties: any[];
}>();

const statusBadge = (status: string) => {
  const map: Record<string, string> = {
    confirmed: 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
    pending: 'bg-amber-100 text-amber-800 ring-amber-600/20',
    cancelled: 'bg-red-100 text-red-800 ring-red-600/20',
  };
  return map[status] || 'bg-gray-100 text-gray-800 ring-gray-600/20';
};

const formatCurrency = (v: number) => 'ZMW ' + (v || 0).toLocaleString();
</script>

<template>
  <Head title="Admin Dashboard - ZamStay" />

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 sm:p-8 mb-8 text-white shadow-lg">
      <div class="flex items-center justify-between">
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
            <span class="px-3 py-0.5 bg-white/20 backdrop-blur rounded-full text-xs font-semibold">Admin</span>
          </div>
          <p class="text-slate-400 text-sm mt-1">Full oversight of the ZamStay platform</p>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-sm text-slate-400">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          Live
        </div>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Properties</p>
          <div class="h-8 w-8 rounded-lg bg-indigo-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ stats.totalProperties }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Bookings</p>
          <div class="h-8 w-8 rounded-lg bg-emerald-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ stats.totalBookings }}</p>
        <div class="flex gap-2 mt-1 text-xs">
          <span class="text-emerald-600">{{ stats.confirmedBookings }} confirmed</span>
          <span class="text-amber-600">{{ stats.pendingBookings }} pending</span>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Agents</p>
          <div class="h-8 w-8 rounded-lg bg-amber-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-amber-600">{{ stats.totalAgents }}</p>
        <p v-if="stats.pendingAgents" class="text-xs text-amber-500 mt-1">{{ stats.pendingAgents }} pending approval</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Users</p>
          <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-blue-600">{{ stats.totalUsers }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Revenue</p>
          <div class="h-8 w-8 rounded-lg bg-emerald-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-emerald-600">{{ formatCurrency(stats.totalRevenue) }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cancelled</p>
          <div class="h-8 w-8 rounded-lg bg-red-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
          </div>
        </div>
        <p class="text-2xl font-bold text-red-600">{{ stats.cancelledBookings }}</p>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <Link :href="route('zamstay.host.properties')" class="group bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-3 hover:shadow-md hover:border-gray-300 transition-all">
        <div class="h-10 w-10 rounded-lg bg-indigo-50 flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
          <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
        </div>
        <div>
          <p class="font-semibold text-gray-900 text-sm">Manage Properties</p>
          <p class="text-xs text-gray-500">View and edit all listings</p>
        </div>
      </Link>
      <Link :href="route('zamstay.host.bookings')" class="group bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-3 hover:shadow-md hover:border-gray-300 transition-all">
        <div class="h-10 w-10 rounded-lg bg-emerald-50 flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
          <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>
        </div>
        <div>
          <p class="font-semibold text-gray-900 text-sm">All Bookings</p>
          <p class="text-xs text-gray-500">Manage reservations</p>
        </div>
      </Link>
      <Link :href="route('zamstay.agents.index')" class="group bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-3 hover:shadow-md hover:border-gray-300 transition-all">
        <div class="h-10 w-10 rounded-lg bg-amber-50 flex items-center justify-center group-hover:bg-amber-100 transition-colors">
          <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
        </div>
        <div>
          <p class="font-semibold text-gray-900 text-sm">Agents</p>
          <p class="text-xs text-gray-500">Approve and manage agents</p>
        </div>
      </Link>
      <Link :href="route('zamstay.properties.index')" class="group bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-3 hover:shadow-md hover:border-gray-300 transition-all">
        <div class="h-10 w-10 rounded-lg bg-gray-50 flex items-center justify-center group-hover:bg-gray-100 transition-colors">
          <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
        </div>
        <div>
          <p class="font-semibold text-gray-900 text-sm">Public View</p>
          <p class="text-xs text-gray-500">See the site as guests do</p>
        </div>
      </Link>
    </div>

    <!-- Two column layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Pending Agent Approvals -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
            <h2 class="font-semibold text-gray-900">Pending Agent Approvals</h2>
          </div>
          <span v-if="pendingAgentRequests?.length" class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">{{ stats.pendingAgents }}</span>
        </div>
        <div v-if="pendingAgentRequests?.length" class="divide-y divide-gray-100">
          <div v-for="agent in pendingAgentRequests" :key="agent.id" class="px-5 py-3.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
              <div class="h-9 w-9 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center shrink-0">
                <span class="text-sm font-bold text-white">{{ (agent.business_name || agent.user?.name || '?').charAt(0) }}</span>
              </div>
              <div class="min-w-0">
                <p class="font-medium text-gray-900 text-sm truncate">{{ agent.business_name || agent.user?.name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ agent.license_number }} &middot; {{ agent.created_at ? new Date(agent.created_at).toLocaleDateString() : '' }}</p>
              </div>
            </div>
            <Link :href="route('zamstay.agents.show', agent.id)" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 shrink-0 ml-3">Review &rarr;</Link>
          </div>
        </div>
        <div v-else class="px-5 py-8 text-center">
          <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <p class="text-gray-400 text-sm">No pending approvals</p>
        </div>
      </div>

      <!-- Recent Properties -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
            <h2 class="font-semibold text-gray-900">Recent Properties</h2>
          </div>
          <Link :href="route('zamstay.host.properties')" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">View all &rarr;</Link>
        </div>
        <div v-if="recentProperties?.length" class="divide-y divide-gray-100">
          <div v-for="prop in recentProperties" :key="prop.id" class="px-5 py-3.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
              <div class="h-9 w-9 rounded-lg bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819" /></svg>
              </div>
              <div class="min-w-0">
                <p class="font-medium text-gray-900 text-sm truncate">{{ prop.title }}</p>
                <p class="text-xs text-gray-500 truncate">{{ prop.location || prop.city || '' }} &middot; {{ prop.property_type || '' }}</p>
              </div>
            </div>
            <span v-if="prop.price_per_night" class="text-sm font-medium text-gray-900 shrink-0">ZMW {{ prop.price_per_night.toLocaleString() }}/night</span>
          </div>
        </div>
        <div v-else class="px-5 py-8 text-center">
          <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
          <p class="text-gray-400 text-sm">No properties listed yet</p>
        </div>
      </div>
    </div>

    <!-- Recent Bookings -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
          <h2 class="font-semibold text-gray-900">Recent Bookings</h2>
        </div>
        <Link :href="route('zamstay.host.bookings')" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">View all &rarr;</Link>
      </div>
      <div v-if="recentBookings?.length" class="divide-y divide-gray-100">
        <div v-for="booking in recentBookings" :key="booking.id" class="px-5 py-3.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
          <div class="flex items-center gap-3 min-w-0">
            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center shrink-0">
              <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
            </div>
            <div class="min-w-0">
              <p class="font-medium text-gray-900 text-sm truncate">{{ booking.property?.title || 'Property' }}</p>
              <p class="text-xs text-gray-500 truncate">{{ booking.user?.name }} &middot; {{ booking.check_in }} &middot; ZMW {{ booking.total_price?.toLocaleString() }}</p>
            </div>
          </div>
          <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset capitalize', statusBadge(booking.status)]">
            {{ booking.status }}
          </span>
        </div>
      </div>
      <div v-else class="px-5 py-8 text-center">
        <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
        <p class="text-gray-400 text-sm">No bookings yet</p>
      </div>
    </div>
  </div>
</template>
