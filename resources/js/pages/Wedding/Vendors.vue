<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 to-purple-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Wedding Vendors</h1>
            <p class="text-gray-600 mt-1">Find the perfect vendors for your special day</p>
          </div>
          <Link
            :href="route('wedding.index')"
            class="text-purple-600 hover:text-purple-700 flex items-center gap-2"
          >
            <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
            Back to Dashboard
          </Link>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4 text-center">
          <p class="text-2xl font-bold text-purple-600">{{ vendorStats?.total || 0 }}</p>
          <p class="text-sm text-gray-600">Total Vendors</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
          <p class="text-2xl font-bold text-pink-600">{{ vendorStats?.categories || 0 }}</p>
          <p class="text-sm text-gray-600">Categories</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
          <p class="text-2xl font-bold text-green-600">{{ vendorStats?.verified || 0 }}</p>
          <p class="text-sm text-gray-600">Verified</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
          <p class="text-2xl font-bold text-amber-600">{{ vendorStats?.avgRating || '0.0' }}</p>
          <p class="text-sm text-gray-600">Avg Rating</p>
        </div>
      </div>

      <!-- Featured Vendors -->
      <div v-if="featuredVendors?.length" class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Featured Vendors</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div 
            v-for="vendor in featuredVendors" 
            :key="vendor.id"
            class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow"
          >
            <div class="h-32 bg-gradient-to-br from-purple-400 to-pink-400"></div>
            <div class="p-4">
              <h3 class="font-semibold text-gray-900">{{ vendor.name }}</h3>
              <p class="text-sm text-gray-500">{{ vendor.category }}</p>
              <div class="flex items-center gap-1 mt-2">
                <StarIcon class="h-4 w-4 text-amber-400" aria-hidden="true" />
                <span class="text-sm text-gray-600">{{ vendor.rating || '0.0' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Rated -->
      <div v-if="topRatedVendors?.length">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Top Rated</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div 
            v-for="vendor in topRatedVendors" 
            :key="vendor.id"
            class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow"
          >
            <div class="h-32 bg-gradient-to-br from-amber-400 to-orange-400"></div>
            <div class="p-4">
              <h3 class="font-semibold text-gray-900">{{ vendor.name }}</h3>
              <p class="text-sm text-gray-500">{{ vendor.category }}</p>
              <div class="flex items-center gap-1 mt-2">
                <StarIcon class="h-4 w-4 text-amber-400" aria-hidden="true" />
                <span class="text-sm text-gray-600">{{ vendor.rating || '0.0' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!featuredVendors?.length && !topRatedVendors?.length" class="text-center py-12">
        <BuildingStorefrontIcon class="h-16 w-16 text-gray-300 mx-auto mb-4" aria-hidden="true" />
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Coming Soon</h2>
        <p class="text-gray-600">We're building our vendor marketplace. Check back soon!</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import {
  ArrowLeftIcon,
  StarIcon,
  BuildingStorefrontIcon,
} from '@heroicons/vue/24/outline'
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid'

defineProps({
  featuredVendors: Array,
  topRatedVendors: Array,
  vendorStats: Object,
})
</script>
