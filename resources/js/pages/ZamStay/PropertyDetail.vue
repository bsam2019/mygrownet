<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';
import ZamStayMap from '@/components/ZamStayMap.vue';

defineOptions({ layout: ZamStayLayout });

const props = defineProps<{
  property: any;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);

const selectedImage = ref(0);
const checkIn = ref('');
const checkOut = ref('');
const guests = ref(1);
const availability = ref<any>(null);
const checking = ref(false);

const property = computed(() => props.property);
const isOwner = computed(() => user.value && Number(user.value.id) === Number(property.value.owner_id));

const checkAvailability = async () => {
  if (!checkIn.value || !checkOut.value) return;
  checking.value = true;
  try {
    const res = await fetch(`/zamstay/availability?property_id=${property.value.id}&check_in=${checkIn.value}&check_out=${checkOut.value}`, {
      headers: { Accept: 'application/json' }
    });
    availability.value = await res.json();
  } finally {
    checking.value = false;
  }
};

const canBook = computed(() => {
  return availability.value?.available;
});

const proceedToCheckout = () => {
  router.get(route('zamstay.checkout', property.value.id), {
    check_in: checkIn.value,
    check_out: checkOut.value,
    guests: guests.value,
  });
};
</script>

<template>
  <Head :title="property.title + ' - Travel Zambia'" />

  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back Link -->
    <Link :href="route('zamstay.search')" class="inline-flex items-center text-sm text-gray-500 hover:text-emerald-600 mb-6">
      &larr; Back to search
    </Link>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Main Content -->
      <div class="lg:col-span-2">
        <!-- Image Gallery -->
        <div class="mb-6">
          <div class="h-64 md:h-80 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-2xl flex items-center justify-center overflow-hidden">
            <img v-if="property.images?.[selectedImage]" :src="property.images[selectedImage]" :alt="property.title" class="w-full h-full object-cover" />
            <span v-else class="text-6xl text-emerald-300">{{ property.title?.charAt(0) }}</span>
          </div>
          <div v-if="property.images?.length > 1" class="flex gap-2 mt-2">
            <button v-for="(img, i) in property.images" :key="i" @click="selectedImage = i" class="w-16 h-12 rounded-lg overflow-hidden border-2 shrink-0" :class="i === selectedImage ? 'border-emerald-500' : 'border-transparent'">
              <img :src="img" alt="" class="w-full h-full object-cover" />
            </button>
          </div>
        </div>

        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{{ property.title }}</h1>
        <div class="flex items-center gap-2 text-gray-500 mb-4">
          <span class="text-sm">{{ property.location }}</span>
          <span class="text-xs bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full capitalize">{{ property.property_type?.replace('_', ' ') }}</span>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-3 gap-4 mb-6">
          <div class="bg-white rounded-xl p-4 text-center border border-gray-200">
            <p class="text-lg font-bold text-gray-900">{{ property.max_guests }}</p>
            <p class="text-xs text-gray-500">Max Guests</p>
          </div>
          <div class="bg-white rounded-xl p-4 text-center border border-gray-200">
            <p class="text-lg font-bold text-gray-900">{{ property.bedrooms }}</p>
            <p class="text-xs text-gray-500">Bedrooms</p>
          </div>
          <div class="bg-white rounded-xl p-4 text-center border border-gray-200">
            <p class="text-lg font-bold text-gray-900">{{ property.bathrooms }}</p>
            <p class="text-xs text-gray-500">Bathrooms</p>
          </div>
        </div>

        <!-- Description -->
        <div class="mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-2">About this place</h2>
          <p class="text-gray-600 leading-relaxed">{{ property.description }}</p>
        </div>

        <!-- Amenities -->
        <div v-if="property.amenities?.length" class="mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-2">Amenities</h2>
          <div class="flex flex-wrap gap-2">
            <span v-for="amenity in property.amenities" :key="amenity" class="px-3 py-1 bg-gray-100 rounded-full text-sm text-gray-700">
              {{ amenity }}
            </span>
          </div>
        </div>

        <!-- Location Map -->
        <div v-if="property.latitude && property.longitude" class="mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-2">Location</h2>
          <ZamStayMap :latitude="property.latitude" :longitude="property.longitude" :title="property.title" />
        </div>

        <!-- Reviews -->
        <div v-if="property.reviews?.length" class="mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Reviews ({{ property.reviews.length }})</h2>
          <div v-for="review in property.reviews" :key="review.id" class="bg-white rounded-xl p-4 border border-gray-200 mb-3">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                  <span class="text-sm font-semibold text-emerald-600">{{ review.user?.name?.charAt(0) || '?' }}</span>
                </div>
                <span class="text-sm font-medium text-gray-900">{{ review.user?.name || 'Anonymous' }}</span>
              </div>
              <span class="text-sm font-semibold text-amber-500">{{ review.rating }}/5</span>
            </div>
            <p v-if="review.comment" class="text-sm text-gray-600">{{ review.comment }}</p>
          </div>
        </div>
      </div>

      <!-- Booking Sidebar -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-24">
          <div class="text-2xl font-bold text-emerald-600 mb-4">
            ZMW {{ Number(property.price_per_night).toFixed(2) }}
            <span class="text-sm font-normal text-gray-400">/ night</span>
          </div>

          <div class="space-y-3 mb-4">
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Check In</label>
              <input
                v-model="checkIn"
                type="date"
                :min="new Date().toISOString().split('T')[0]"
                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm"
                @change="checkAvailability"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Check Out</label>
              <input
                v-model="checkOut"
                type="date"
                :min="checkIn || new Date().toISOString().split('T')[0]"
                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm"
                @change="checkAvailability"
              />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Guests</label>
              <select v-model.number="guests" class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                <option v-for="n in property.max_guests" :key="n" :value="n">{{ n }} {{ n === 1 ? 'Guest' : 'Guests' }}</option>
              </select>
            </div>
          </div>

          <!-- Availability Result -->
          <div v-if="availability" class="mb-4">
            <div v-if="canBook" class="bg-emerald-50 text-emerald-700 text-sm p-3 rounded-xl">
              Available - ZMW {{ Number(availability.total_price).toFixed(2) }} for {{ availability.nights }} {{ availability.nights === 1 ? 'night' : 'nights' }}
            </div>
            <div v-else class="bg-red-50 text-red-600 text-sm p-3 rounded-xl">
              Not available for selected dates
            </div>
          </div>

          <div v-if="checking" class="text-center text-sm text-gray-500 mb-4">Checking availability...</div>

          <template v-if="user && !isOwner">
            <button
              v-if="canBook"
              @click="proceedToCheckout"
              class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-colors"
            >
              Reserve Now
            </button>
            <button
              v-else
              disabled
              class="w-full py-3 bg-gray-300 text-gray-500 font-semibold rounded-xl cursor-not-allowed"
            >
              Select Dates
            </button>
          </template>
          <template v-else-if="!user">
            <Link :href="route('login')" class="block w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-colors text-center">
              Sign In to Book
            </Link>
          </template>
          <p v-else-if="isOwner" class="text-sm text-amber-600 text-center">You own this property</p>
        </div>
      </div>
    </div>
  </div>
</template>
