<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

defineOptions({ layout: ZamStayLayout });

defineProps<{
  properties: any;
}>();
</script>

<template>
  <Head title="My Properties - ZamStay" />

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">My Properties</h1>
      <Link :href="route('zamstay.host.properties.create')" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-colors text-sm">
        + Add Property
      </Link>
    </div>

    <div v-if="properties?.data?.length">
      <div v-for="property in properties.data" :key="property.id" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-4">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-xl flex items-center justify-center overflow-hidden shrink-0">
              <img v-if="property.images?.[0]" :src="property.images[0]" :alt="property.title" class="w-full h-full object-cover" />
              <span v-else class="text-xl font-bold text-emerald-500">{{ property.title?.charAt(0) || '?' }}</span>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">{{ property.title }}</h3>
              <p class="text-sm text-gray-500">{{ property.location }}</p>
              <p class="text-xs text-gray-400 mt-1">
                ZMW {{ Number(property.price_per_night).toFixed(2) }}/night &middot;
                {{ property.bookings_count || 0 }} bookings &middot;
                {{ property.reviews_count || 0 }} reviews
              </p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <span :class="['text-xs font-medium px-2 py-1 rounded-full', property.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500']">
              {{ property.is_active ? 'Active' : 'Inactive' }}
            </span>
            <Link :href="route('zamstay.host.properties.edit', property.id)" class="px-3 py-1.5 text-sm text-emerald-600 hover:bg-emerald-50 border border-emerald-200 rounded-lg transition-colors">
              Edit
            </Link>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-16">
      <p class="text-gray-500 text-lg mb-4">You haven't listed any properties yet.</p>
      <Link :href="route('zamstay.host.properties.create')" class="inline-flex px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-colors">
        List Your First Property
      </Link>
    </div>
  </div>
</template>
