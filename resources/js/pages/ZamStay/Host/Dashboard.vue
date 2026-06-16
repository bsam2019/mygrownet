<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

defineOptions({ layout: ZamStayLayout });

defineProps<{
  stats: {
    total_properties: number;
    active_properties: number;
    total_bookings: number;
    pending_bookings: number;
    confirmed_bookings: number;
    total_revenue: number;
  };
  recentBookings: any[];
  properties: any[];
}>();
</script>

<template>
  <Head title="Host Dashboard - ZamStay" />

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Host Dashboard</h1>
      <Link :href="route('zamstay.host.properties.create')" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-colors text-sm">
        + Add Property
      </Link>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
        <p class="text-2xl font-bold text-gray-900">{{ stats.total_properties }}</p>
        <p class="text-sm text-gray-500">Total Properties</p>
      </div>
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
        <p class="text-2xl font-bold text-emerald-600">{{ stats.active_properties }}</p>
        <p class="text-sm text-gray-500">Active</p>
      </div>
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
        <p class="text-2xl font-bold text-amber-600">{{ stats.pending_bookings }}</p>
        <p class="text-sm text-gray-500">Pending Bookings</p>
      </div>
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
        <p class="text-2xl font-bold text-emerald-600">ZMW {{ Number(stats.total_revenue).toFixed(2) }}</p>
        <p class="text-sm text-gray-500">Total Revenue</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- My Properties -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">My Properties</h2>
          <Link :href="route('zamstay.host.properties')" class="text-sm text-emerald-600 hover:text-emerald-700">View All</Link>
        </div>
        <div v-if="properties.length">
          <div v-for="property in properties" :key="property.id" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <div>
              <p class="font-medium text-gray-900 text-sm">{{ property.title }}</p>
              <p class="text-xs text-gray-500">{{ property.location }} &middot; ZMW {{ Number(property.price_per_night).toFixed(2) }}/night</p>
            </div>
            <span :class="['text-xs font-medium px-2 py-1 rounded-full', property.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500']">
              {{ property.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>
        <p v-else class="text-sm text-gray-500 text-center py-4">No properties yet.</p>
      </div>

      <!-- Recent Bookings -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Recent Bookings</h2>
          <Link :href="route('zamstay.host.bookings')" class="text-sm text-emerald-600 hover:text-emerald-700">View All</Link>
        </div>
        <div v-if="recentBookings.length">
          <div v-for="booking in recentBookings" :key="booking.id" class="py-3 border-b border-gray-100 last:border-0">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900 text-sm">{{ booking.property?.title }}</p>
                <p class="text-xs text-gray-500">{{ booking.user?.name }} &middot; {{ booking.check_in }} &rarr; {{ booking.check_out }}</p>
              </div>
              <span :class="['text-xs font-medium px-2 py-1 rounded-full capitalize',
                booking.status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' :
                booking.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                'bg-red-100 text-red-700']">
                {{ booking.status }}
              </span>
            </div>
          </div>
        </div>
        <p v-else class="text-sm text-gray-500 text-center py-4">No bookings yet.</p>
      </div>
    </div>
  </div>
</template>
