<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

defineOptions({ layout: ZamStayLayout });

const props = defineProps<{
  property: any;
  checkIn: string;
  checkOut: string;
  guests: number;
}>();

const specialRequests = ref('');
const submitting = ref(false);

const nights = computed(() => {
  if (!props.checkIn || !props.checkOut) return 0;
  const start = new Date(props.checkIn);
  const end = new Date(props.checkOut);
  return Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24));
});

const totalPrice = computed(() => {
  return nights.value * Number(props.property.price_per_night);
});

const submitBooking = () => {
  submitting.value = true;
  router.post(route('zamstay.bookings.store'), {
    property_id: props.property.id,
    check_in: props.checkIn,
    check_out: props.checkOut,
    guests: props.guests,
    special_requests: specialRequests.value || null,
  }, {
    preserveScroll: true,
    onFinish: () => { submitting.value = false; },
  });
};
</script>

<template>
  <Head :title="'Book ' + property.title" />

  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Link :href="route('zamstay.properties.show', property.id)" class="inline-flex items-center text-sm text-gray-500 hover:text-emerald-600 mb-6">
      &larr; Back to property
    </Link>

    <h1 class="text-2xl font-bold text-gray-900 mb-8">Complete Your Booking</h1>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
      <!-- Booking Form -->
      <div class="lg:col-span-3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Booking Details</h2>

          <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Check In</label>
              <p class="text-sm font-medium text-gray-900">{{ checkIn || 'Not set' }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Check Out</label>
              <p class="text-sm font-medium text-gray-900">{{ checkOut || 'Not set' }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Guests</label>
              <p class="text-sm font-medium text-gray-900">{{ guests }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Nights</label>
              <p class="text-sm font-medium text-gray-900">{{ nights }}</p>
            </div>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Special Requests (optional)</label>
            <textarea
              v-model="specialRequests"
              rows="3"
              placeholder="Any special requests for your stay..."
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm"
            />
          </div>

          <button
            @click="submitBooking"
            :disabled="submitting"
            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 text-white font-semibold rounded-xl transition-colors"
          >
            {{ submitting ? 'Processing...' : 'Confirm Booking' }}
          </button>
        </div>
      </div>

      <!-- Price Summary -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-24">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Price Summary</h2>

          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-xl flex items-center justify-center">
              <span class="text-lg font-bold text-emerald-500">{{ property.title?.charAt(0) }}</span>
            </div>
            <div>
              <p class="font-medium text-gray-900 text-sm">{{ property.title }}</p>
              <p class="text-xs text-gray-500">{{ property.location }}</p>
            </div>
          </div>

          <div class="border-t border-gray-100 pt-4 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">ZMW {{ Number(property.price_per_night).toFixed(2) }} x {{ nights }} {{ nights === 1 ? 'night' : 'nights' }}</span>
              <span class="text-gray-900">ZMW {{ totalPrice.toFixed(2) }}</span>
            </div>
            <div class="border-t border-gray-100 pt-2 flex justify-between font-semibold">
              <span class="text-gray-900">Total</span>
              <span class="text-emerald-600 text-lg">ZMW {{ totalPrice.toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
