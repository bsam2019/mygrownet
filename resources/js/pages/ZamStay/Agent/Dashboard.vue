<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

defineOptions({ layout: ZamStayLayout });

defineProps<{
  agent: any;
  bookings: any;
  stats: any;
}>();
</script>

<template>
  <Head title="Agent Dashboard - ZamStay" />

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Agent Dashboard</h1>
        <p class="text-gray-500 mt-1">{{ agent.business_name }}</p>
      </div>
      <span v-if="agent.is_approved" class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-medium">
        Approved
      </span>
      <span v-else class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-sm font-medium">
        Pending Approval
      </span>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <p class="text-sm text-gray-500">Total Bookings</p>
        <p class="text-2xl font-bold text-gray-900">{{ stats.total_bookings }}</p>
      </div>
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <p class="text-sm text-gray-500">Confirmed</p>
        <p class="text-2xl font-bold text-emerald-600">{{ stats.confirmed_bookings }}</p>
      </div>
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <p class="text-sm text-gray-500">Pending</p>
        <p class="text-2xl font-bold text-amber-600">{{ stats.pending_bookings }}</p>
      </div>
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <p class="text-sm text-gray-500">Commission Rate</p>
        <p class="text-2xl font-bold text-gray-900">{{ stats.commission_rate }}%</p>
      </div>
    </div>

    <div v-if="bookings?.data?.length" class="bg-white rounded-2xl shadow-sm border border-gray-200">
      <div class="p-5 border-b border-gray-100">
        <h2 class="font-semibold text-gray-900">Your Bookings</h2>
      </div>
      <div v-for="booking in bookings.data" :key="booking.id" class="p-5 border-b border-gray-100 last:border-0">
        <div class="flex items-center justify-between">
          <div>
            <Link :href="route('zamstay.bookings.show', booking.id)" class="font-medium text-gray-900 hover:text-emerald-600">
              {{ booking.property?.title }}
            </Link>
            <p class="text-sm text-gray-500 mt-1">Guest: {{ booking.user?.name }} &middot; {{ booking.check_in }} to {{ booking.check_out }}</p>
          </div>
          <span :class="['px-3 py-1 rounded-full text-xs font-medium',
            booking.status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' :
            booking.status === 'pending' ? 'bg-amber-100 text-amber-700' :
            'bg-red-100 text-red-700']">
            {{ booking.status }}
          </span>
        </div>
      </div>
    </div>
    <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">
      No bookings yet.
    </div>
  </div>
</template>
