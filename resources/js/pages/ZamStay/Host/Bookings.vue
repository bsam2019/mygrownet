<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

defineOptions({ layout: ZamStayLayout });

const props = defineProps<{
  bookings: any;
}>();

const confirmBooking = (id: number) => {
  if (confirm('Confirm this booking?')) {
    router.post(route('zamstay.bookings.confirm', id));
  }
};

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
  <Head title="Booking Requests - ZamStay" />

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Booking Requests</h1>

    <div v-if="bookings?.data?.length">
      <div v-for="booking in bookings.data" :key="booking.id" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-4">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-xl flex items-center justify-center">
              <span class="text-xl font-bold text-emerald-500">{{ booking.property?.title?.charAt(0) || '?' }}</span>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">{{ booking.property?.title }}</h3>
              <p class="text-sm text-gray-500">
                Guest: {{ booking.user?.name }} &middot; {{ booking.user?.email }}
              </p>
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
        <div v-if="booking.status === 'pending'" class="mt-3 pt-3 border-t border-gray-100 flex gap-2">
          <button @click="confirmBooking(booking.id)" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-colors">
            Confirm Booking
          </button>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-16">
      <p class="text-gray-500 text-lg">No booking requests yet.</p>
    </div>
  </div>
</template>
