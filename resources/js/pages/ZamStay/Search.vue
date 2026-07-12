<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';
import ZamStayMap from '@/components/ZamStayMap.vue';

defineOptions({ layout: ZamStayLayout });

const props = defineProps<{
  properties: any;
  filters: {
    location?: string;
    property_type?: string;
    min_price?: string;
    max_price?: string;
    guests?: string;
  };
}>();

const locationFilter = ref(props.filters.location || '');
const propertyTypeFilter = ref(props.filters.property_type || '');
const viewMode = ref<'list' | 'map'>('list');

const hasCoords = computed(() => {
  return props.properties?.data?.some((p: any) => p.latitude && p.longitude);
});

const mapMarkers = computed(() => {
  return props.properties?.data
    ?.filter((p: any) => p.latitude && p.longitude)
    .map((p: any) => ({
      lat: p.latitude,
      lng: p.longitude,
      title: p.title,
      price: p.price_per_night,
      id: p.id,
    })) || [];
});

const mapCenter = computed(() => {
  const first = props.properties?.data?.find((p: any) => p.latitude && p.longitude);
  return first ? { lat: first.latitude, lng: first.longitude } : { lat: -14.5, lng: 28.0 };
});

const applyFilters = () => {
  router.get(route('zamstay.search'), {
    location: locationFilter.value || undefined,
    property_type: propertyTypeFilter.value || undefined,
  }, { preserveState: true });
};
</script>

<template>
  <Head title="Search Properties - Travel Zambia" />

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Search Filters -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200 mb-8">
      <div class="flex flex-col md:flex-row gap-3">
        <div class="flex-1">
          <input
            v-model="locationFilter"
            type="text"
            placeholder="Where are you going?"
            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm"
            @keyup.enter="applyFilters"
          />
        </div>
        <div class="md:w-44">
          <select
            v-model="propertyTypeFilter"
            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Types</option>
            <option value="hotel">Hotels</option>
            <option value="lodge">Lodges</option>
            <option value="guest_house">Guest Houses</option>
            <option value="home_stay">Home Stays</option>
          </select>
        </div>
        <button @click="applyFilters" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-colors text-sm">
          Search
        </button>
      </div>
    </div>

    <!-- View Toggle -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-gray-900">
        {{ properties?.data?.length ? 'Available Properties' : 'No properties found' }}
      </h2>
      <div v-if="hasCoords" class="flex bg-gray-100 rounded-xl p-0.5">
        <button @click="viewMode = 'list'" :class="['px-3 py-1.5 text-sm rounded-lg font-medium transition-colors', viewMode === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']">List</button>
        <button @click="viewMode = 'map'" :class="['px-3 py-1.5 text-sm rounded-lg font-medium transition-colors', viewMode === 'map' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']">Map</button>
      </div>
    </div>

    <!-- Content -->
    <template v-if="properties?.data?.length">
      <!-- Map View -->
      <div v-if="viewMode === 'map' && hasCoords" class="mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
          <ZamStayMap :markers="mapMarkers" height="400px" />
          <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
            <div v-for="p in properties.data" :key="p.id" class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
              <Link :href="route('zamstay.properties.show', p.id)" class="text-xs text-gray-600 hover:text-emerald-600 truncate">{{ p.title }}</Link>
            </div>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-if="viewMode === 'list'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="property in properties.data" :key="property.id" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
          <div class="h-48 bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center overflow-hidden">
            <img v-if="property.images?.[0]" :src="property.images[0]" :alt="property.title" class="w-full h-full object-cover" />
            <span v-else class="text-4xl text-emerald-400">{{ property.title?.charAt(0) }}</span>
          </div>
          <div class="p-4">
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full capitalize">{{ property.property_type?.replace('_', ' ') }}</span>
              <span class="text-xs text-gray-400">{{ property.max_guests }} guests max</span>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">{{ property.title }}</h3>
            <p class="text-sm text-gray-500 mb-1">{{ property.location }}</p>
            <p class="text-xs text-gray-400 mb-3">{{ property.bedrooms }} bed &middot; {{ property.bathrooms }} bath</p>
            <div class="flex items-center justify-between">
              <div>
                <span class="text-lg font-bold text-emerald-600">ZMW {{ Number(property.price_per_night).toFixed(2) }}</span>
                <span class="text-sm text-gray-400">/ night</span>
              </div>
              <Link :href="route('zamstay.properties.show', property.id)" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                View Details
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-else class="text-center py-16">
      <p class="text-gray-500 text-lg mb-4">No properties match your search criteria.</p>
      <Link :href="route('zamstay.search')" class="text-emerald-600 hover:text-emerald-700 font-medium">Clear filters</Link>
    </div>
  </div>
</template>
