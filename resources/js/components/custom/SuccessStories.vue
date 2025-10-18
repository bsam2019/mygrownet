<template>
    <div class="py-16 bg-gradient-to-b from-gray-50 to-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-12">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">MyGrowNet Success Stories</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Real members sharing their growth journey through our comprehensive reward system
          </p>
        </div>

        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Testimonial 1 -->
          <div class="group hover:scale-105 transition-all duration-300">
            <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100 relative h-full">
              <div class="absolute top-0 right-0 -mt-2 -mr-2">
                <span class="bg-gradient-to-r from-indigo-600 to-indigo-400 text-white text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
                  Elite Member
                </span>
              </div>

              <div class="flex items-center mb-6">
                <div class="flex-shrink-0">
                  <div class="h-14 w-14 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-lg shadow-md">
                    TM
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-xl font-bold text-gray-900">Thomas M.</h3>
                  <p class="text-indigo-600 font-medium">Asset Rewards & Leadership</p>
                </div>
              </div>

              <div class="relative">
                <QuoteIcon class="h-8 w-8 text-indigo-100 absolute -top-4 -left-4" />
                <p class="text-gray-600 leading-relaxed relative z-10 pl-4">
                  "Started with Gold membership and earned my first asset reward - a delivery vehicle that generates K2,000 monthly. The five-level commissions and leadership bonuses have exceeded all expectations."
                </p>
              </div>
            </div>
          </div>

          <!-- Testimonial 2 -->
          <div class="group hover:scale-105 transition-all duration-300">
            <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100 relative h-full">
              <div class="absolute top-0 right-0 -mt-2 -mr-2">
                <span class="bg-gradient-to-r from-yellow-500 to-yellow-300 text-white text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
                  Gold Member
                </span>
              </div>

              <div class="flex items-center mb-6">
                <div class="flex-shrink-0">
                  <div class="h-14 w-14 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-500 flex items-center justify-center text-white font-semibold text-lg shadow-md">
                    SB
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-xl font-bold text-gray-900">Sarah B.</h3>
                  <p class="text-yellow-600 font-medium">Community Projects & Education</p>
                </div>
              </div>

              <div class="relative">
                <QuoteIcon class="h-8 w-8 text-yellow-100 absolute -top-4 -left-4" />
                <p class="text-gray-600 leading-relaxed relative z-10 pl-4">
                  "The community projects have been amazing - I've earned K5,000 from our farming project alone. The educational content helped me understand business better and grow my team to 50+ members."
                </p>
              </div>
            </div>
          </div>

          <!-- Testimonial 3 -->
          <div class="group hover:scale-105 transition-all duration-300">
            <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100 relative h-full">
              <div class="absolute top-0 right-0 -mt-2 -mr-2">
                <span class="bg-gradient-to-r from-blue-400 to-blue-200 text-white text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
                  Silver Member
                </span>
              </div>

              <div class="flex items-center mb-6">
                <div class="flex-shrink-0">
                  <div class="h-14 w-14 rounded-full bg-gradient-to-br from-blue-300 to-blue-400 flex items-center justify-center text-white font-semibold text-lg shadow-md">
                    JK
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-xl font-bold text-gray-900">James K.</h3>
                  <p class="text-blue-600 font-medium">Growing Network</p>
                </div>
              </div>

              <div class="relative">
                <QuoteIcon class="h-8 w-8 text-blue-100 absolute -top-4 -left-4" />
                <p class="text-gray-600 leading-relaxed relative z-10 pl-4">
                  "Started with Bronze and upgraded to Silver in 3 months. My team volume hit K8,000 last month, earning me performance bonuses. The mobile money payments make everything so convenient!"
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  import { ChatBubbleBottomCenterIcon as QuoteIcon } from '@heroicons/vue/24/outline'

  export default {
    components: {
      QuoteIcon
    }
  }
  </script>
