<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

defineOptions({ layout: ZamStayLayout });

defineProps<{
  bookings: any;
}>();

const statusColor = (status: string) => {
  switch (status) {
    case 'confirmed': return 'bg-emerald-100 text-emerald-700';
    case 'pending': return 'bg-amber-100 text-amber-700';
    case 'cancelled': return 'bg-red-100 text-red-700';
    default: return 'bg-gray-100 text-gray-700';
  }
};
</script>

<template>
  <Head title="My Bookings - ZamStay" />

  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Bookings</h1>

    <div v-if="bookings?.data?.length">
      <div v-for="booking in bookings.data" :key="booking.id" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-4">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-xl flex items-center justify-center">
              <span class="text-xl font-bold text-emerald-500">{{ booking.property?.title?.charAt(0) || '?' }}</span>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">{{ booking.property?.title || 'Property' }}</h3>
              <p class="text-sm text-gray-500">{{ booking.property?.location }}</p>
              <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                <span>{{ booking.check_in }} &rarr; {{ booking.check_out }}</span>
                <span>{{ booking.guests }} {{ booking.guests === 1 ? 'guest' : 'guests' }}</span>
              </div>
            </div>
          </div>
          <div class="text-right">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium capitalize" :class="statusColor(booking.status)">
              {{ booking.status }}
            </span>
            <p class="text-sm font-semibold text-gray-900 mt-1">ZMW {{ Number(booking.total_price).toFixed(2) }}</p>
          </div>
        </div>
        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
          <span class="text-xs text-gray-400">Booked {{ booking.created_at }}</span>
          <Link :href="route('zamstay.bookings.show', booking.id)" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
            View Details
          </Link>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-16">
      <p class="text-gray-500 text-lg mb-4">You haven't made any bookings yet.</p>
      <Link :href="route('zamstay.search')" class="inline-flex px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-colors">
        Browse Properties
      </Link>
    </div>
  </div>
</template>
