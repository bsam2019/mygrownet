<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

defineOptions({ layout: ZamStayLayout });

const props = defineProps<{
  featured: any[];
  latest: any[];
  locations: string[];
}>();

const searchLocation = ref('');
const searchGuests = ref(1);

const handleSearch = () => {
  router.get(route('zamstay.search'), {
    location: searchLocation.value,
    guests: searchGuests.value > 1 ? searchGuests.value : undefined,
  });
};
</script>

<template>
  <Head title="ZamStay - Book Stays" />

  <!-- Hero Section -->
  <section class="relative bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32">
      <div class="max-w-3xl">
        <h1 class="text-4xl md:text-6xl font-bold mb-4 leading-tight">
          Discover Zambia
        </h1>
        <p class="text-lg md:text-xl text-emerald-100 mb-8">
          Book budget hotels, lodges, guest houses, and home stays across Zambia.
        </p>

        <!-- Search Bar -->
        <div class="bg-white rounded-2xl p-4 shadow-2xl">
          <div class="flex flex-col md:flex-row gap-3">
            <div class="flex-1">
              <label class="block text-xs font-medium text-gray-500 mb-1">Location</label>
              <input
                v-model="searchLocation"
                type="text"
                placeholder="Where are you going?"
                list="locations"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-gray-900"
              />
              <datalist id="locations">
                <option v-for="loc in locations" :key="loc" :value="loc" />
              </datalist>
            </div>
            <div class="md:w-32">
              <label class="block text-xs font-medium text-gray-500 mb-1">Guests</label>
              <select
                v-model.number="searchGuests"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-gray-900"
              >
                <option :value="1">1 Guest</option>
                <option :value="2">2 Guests</option>
                <option :value="3">3 Guests</option>
                <option :value="4">4 Guests</option>
                <option :value="5">5+ Guests</option>
              </select>
            </div>
            <div class="flex items-end">
              <button
                @click="handleSearch"
                class="w-full md:w-auto px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-colors"
              >
                Search
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Property Types -->
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Browse by Type</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <Link :href="route('zamstay.search', { property_type: 'hotel' })" class="group relative rounded-2xl overflow-hidden h-40 bg-gradient-to-br from-blue-500 to-blue-700">
        <div class="absolute inset-0 flex items-center justify-center">
          <span class="text-white text-lg font-bold">Hotels</span>
        </div>
      </Link>
      <Link :href="route('zamstay.search', { property_type: 'lodge' })" class="group relative rounded-2xl overflow-hidden h-40 bg-gradient-to-br from-amber-500 to-orange-700">
        <div class="absolute inset-0 flex items-center justify-center">
          <span class="text-white text-lg font-bold">Lodges</span>
        </div>
      </Link>
      <Link :href="route('zamstay.search', { property_type: 'guest_house' })" class="group relative rounded-2xl overflow-hidden h-40 bg-gradient-to-br from-purple-500 to-purple-700">
        <div class="absolute inset-0 flex items-center justify-center">
          <span class="text-white text-lg font-bold">Guest Houses</span>
        </div>
      </Link>
      <Link :href="route('zamstay.search', { property_type: 'home_stay' })" class="group relative rounded-2xl overflow-hidden h-40 bg-gradient-to-br from-pink-500 to-rose-700">
        <div class="absolute inset-0 flex items-center justify-center">
          <span class="text-white text-lg font-bold">Home Stays</span>
        </div>
      </Link>
    </div>
  </section>

  <!-- Featured Properties -->
  <section v-if="featured.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured Stays</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="property in featured" :key="property.id" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
        <div class="h-48 bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center overflow-hidden">
          <img v-if="property.images?.[0]" :src="property.images[0]" :alt="property.title" class="w-full h-full object-cover" />
          <span v-else class="text-4xl text-emerald-400">{{ property.title.charAt(0) }}</span>
        </div>
        <div class="p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full capitalize">{{ property.property_type?.replace('_', ' ') }}</span>
            <span class="text-xs text-gray-400">{{ property.reviews_count || 0 }} reviews</span>
          </div>
          <h3 class="font-semibold text-gray-900 mb-1">{{ property.title }}</h3>
          <p class="text-sm text-gray-500 mb-3">{{ property.location }}</p>
          <div class="flex items-center justify-between">
            <span class="text-lg font-bold text-emerald-600">ZMW {{ Number(property.price_per_night).toFixed(2) }} <span class="text-sm font-normal text-gray-400">/ night</span></span>
            <Link :href="route('zamstay.properties.show', property.id)" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">View Details</Link>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Latest Properties -->
  <section v-if="latest.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Latest Stays</h2>
      <Link :href="route('zamstay.search')" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">View All</Link>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div v-for="property in latest" :key="property.id" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
        <div class="h-40 bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center overflow-hidden">
          <img v-if="property.images?.[0]" :src="property.images[0]" :alt="property.title" class="w-full h-full object-cover" />
          <span v-else class="text-3xl text-emerald-400">{{ property.title.charAt(0) }}</span>
        </div>
        <div class="p-3">
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full capitalize">{{ property.property_type?.replace('_', ' ') }}</span>
          </div>
          <h3 class="font-semibold text-gray-900 text-sm mb-1">{{ property.title }}</h3>
          <p class="text-xs text-gray-500 mb-2">{{ property.location }}</p>
          <div class="flex items-center justify-between">
            <span class="text-sm font-bold text-emerald-600">ZMW {{ Number(property.price_per_night).toFixed(2) }} <span class="text-xs font-normal text-gray-400">/ night</span></span>
            <Link :href="route('zamstay.properties.show', property.id)" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">View</Link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
