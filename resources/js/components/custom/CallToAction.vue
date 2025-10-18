<template>
    <div class="py-12 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg shadow-xl p-8 text-center">
          <h2 class="text-3xl font-bold text-white mb-4">Ready to Start Growing with MyGrowNet?</h2>
          <p class="text-lg text-blue-100 mb-8 max-w-2xl mx-auto">
            Join thousands of members already earning through our comprehensive reward system. 
            Start with any tier and grow your income potential.
          </p>
          <div class="flex flex-col sm:flex-row justify-center gap-4">
            <Link :href="route('register')" class="inline-flex items-center justify-center px-8 py-3 bg-white text-blue-600 font-bold rounded-full shadow-md hover:bg-gray-100 hover:scale-105 transition-all duration-300">
              Join MyGrowNet Today
            </Link>
            <Link :href="route().has('earnings-projection.index') ? route('earnings-projection.index') : '#'" class="inline-flex items-center justify-center px-8 py-3 border-2 border-white text-white font-bold rounded-full hover:bg-white/10 hover:scale-105 transition-all duration-300">
              Calculate Earnings
            </Link>
          </div>
          <div class="mt-6 text-sm text-blue-200">
            No long-term commitments • Start from K50/month • Upgrade anytime
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
