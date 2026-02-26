<template>
    <div class="py-12 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg shadow-xl p-8 text-center">
          <h2 class="text-3xl font-bold text-white mb-4">Ready to Grow Your Business or Income?</h2>
          <p class="text-lg text-blue-100 mb-8 max-w-2xl mx-auto">
            Start building your online presence, access powerful business tools, and join income opportunities today.
          </p>
          <div class="flex flex-col sm:flex-row justify-center gap-4">
            <Link :href="route('growbuilder.index')" class="inline-flex items-center justify-center px-8 py-3 bg-white text-blue-600 font-bold rounded-full shadow-md hover:bg-gray-100 hover:scale-105 transition-all duration-300">
              Start Your Business
            </Link>
            <Link :href="route('grownet.dashboard')" class="inline-flex items-center justify-center px-8 py-3 border-2 border-white text-white font-bold rounded-full hover:bg-white/10 hover:scale-105 transition-all duration-300">
              Join GrowNet
            </Link>
            <Link :href="route('marketplace.home')" class="inline-flex items-center justify-center px-8 py-3 border-2 border-white text-white font-bold rounded-full hover:bg-white/10 hover:scale-105 transition-all duration-300">
              Browse Products
            </Link>
          </div>
          <div class="mt-6 text-sm text-blue-200">
            Build websites • Manage business • Earn income • Shop products
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  import { Link } from '@inertiajs/vue3';

  export default {
    components: {
      Link
    }
  }
  </script>
