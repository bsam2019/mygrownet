<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

defineOptions({ layout: ZamStayLayout });

const props = defineProps<{
  booking: any;
}>();

const reviewRating = ref(5);
const reviewComment = ref('');
const submittingReview = ref(false);

const paymentMethod = ref('mobile_money');
const paymentPhone = ref('');
const paying = ref(false);

const submitReview = () => {
  submittingReview.value = true;
  router.post(route('zamstay.reviews.store'), {
    booking_id: props.booking.id,
    rating: reviewRating.value,
    comment: reviewComment.value,
  }, {
    preserveScroll: true,
    onFinish: () => { submittingReview.value = false; },
  });
};

const payNow = () => {
  if (!paymentPhone.value && paymentMethod.value === 'mobile_money') return;
  paying.value = true;
  router.post(route('zamstay.bookings.pay', props.booking.id), {
    payment_method: paymentMethod.value,
    payment_phone: paymentPhone.value,
  }, {
    preserveScroll: true,
    onFinish: () => { paying.value = false; },
  });
};

const cancelBooking = () => {
  if (confirm('Are you sure you want to cancel this booking?')) {
    router.post(route('zamstay.bookings.cancel', props.booking.id));
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
  <Head :title="'Booking #' + booking.id + ' - Travel Zambia'" />

  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Link :href="route('zamstay.bookings.index')" class="inline-flex items-center text-sm text-gray-500 hover:text-emerald-600 mb-6">
      &larr; Back to bookings
    </Link>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Main -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
          <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-900">Booking #{{ booking.id }}</h1>
            <span class="px-3 py-1 rounded-full text-xs font-medium capitalize" :class="statusColor(booking.status)">
              {{ booking.status }}
            </span>
          </div>

          <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-xl flex items-center justify-center">
              <span class="text-2xl font-bold text-emerald-500">{{ booking.property?.title?.charAt(0) || '?' }}</span>
            </div>
            <div>
              <h2 class="font-semibold text-gray-900">{{ booking.property?.title }}</h2>
              <p class="text-sm text-gray-500">{{ booking.property?.location }}</p>
              <p class="text-xs text-gray-400 capitalize">{{ booking.property?.property_type?.replace('_', ' ') }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-gray-500">Check In</p>
              <p class="font-medium text-gray-900">{{ booking.check_in }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Check Out</p>
              <p class="font-medium text-gray-900">{{ booking.check_out }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Guests</p>
              <p class="font-medium text-gray-900">{{ booking.guests }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Total Price</p>
              <p class="font-semibold text-emerald-600">ZMW {{ Number(booking.total_price).toFixed(2) }}</p>
            </div>
          </div>

          <div v-if="booking.special_requests" class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500 mb-1">Special Requests</p>
            <p class="text-sm text-gray-700">{{ booking.special_requests }}</p>
          </div>

          <!-- Payment Section -->
          <div v-if="booking.status === 'pending'" class="mt-6 pt-6 border-t border-gray-100">
            <h3 class="font-semibold text-gray-900 mb-3">Complete Payment</h3>
            <p class="text-sm text-gray-500 mb-4">Pay via mobile money to confirm your booking instantly.</p>

            <div class="space-y-3 mb-4">
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Payment Method</label>
                <select v-model="paymentMethod" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                  <option value="mobile_money">Mobile Money</option>
                  <option value="bank_transfer">Bank Transfer</option>
                </select>
              </div>
              <div v-if="paymentMethod === 'mobile_money'">
                <label class="block text-xs font-medium text-gray-500 mb-1">Phone Number</label>
                <input v-model="paymentPhone" type="tel" placeholder="0977xxxxxx" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" />
              </div>
              <div v-if="paymentMethod === 'bank_transfer'" class="bg-gray-50 rounded-xl p-3 text-sm text-gray-600">
                <p class="font-medium mb-1">Bank Transfer Details</p>
                <p>Bank: Zanaco</p>
                <p>Account: 1234567890</p>
                <p>Name: ZamStay Ltd</p>
                <p class="text-xs text-gray-400 mt-1">Use booking #{{ booking.id }} as reference</p>
              </div>
            </div>

            <div class="flex gap-2">
              <button @click="payNow" :disabled="paying" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 text-white font-medium rounded-xl transition-colors text-sm">
                {{ paying ? 'Processing...' : 'Pay ZMW ' + Number(booking.total_price).toFixed(2) }}
              </button>
              <button @click="cancelBooking" class="px-4 py-2.5 text-sm font-medium text-red-600 border border-red-200 rounded-xl hover:bg-red-50 transition-colors">
                Cancel
              </button>
            </div>
          </div>
        </div>

        <!-- Review Form -->
        <div v-if="booking.status === 'confirmed' && !booking.review" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Leave a Review</h2>
          <form @submit.prevent="submitReview">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
              <div class="flex gap-2">
                <button v-for="n in 5" :key="n" type="button" @click="reviewRating = n"
                  :class="['w-10 h-10 rounded-lg text-lg font-bold transition-colors', n <= reviewRating ? 'bg-amber-400 text-white' : 'bg-gray-100 text-gray-400']">
                  {{ n }}
                </button>
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Comment (optional)</label>
              <textarea v-model="reviewComment" rows="3" placeholder="Share your experience..." class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm" />
            </div>
            <button type="submit" :disabled="submittingReview" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 text-white font-medium rounded-xl transition-colors text-sm">
              {{ submittingReview ? 'Submitting...' : 'Submit Review' }}
            </button>
          </form>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-24">
          <h3 class="font-semibold text-gray-900 mb-3">Need Help?</h3>
          <p class="text-sm text-gray-500 mb-4">
            If you need to modify or cancel this booking, please contact the host.
          </p>
          <Link :href="route('zamstay.host.dashboard')" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
            Contact Host
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>
