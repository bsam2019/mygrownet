<template>
    <div class="py-16 bg-gradient-to-b from-gray-50 to-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-12">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">Member Success Stories</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Real members sharing how MyGrowNet's tools and training helped them grow
          </p>
        </div>

        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Testimonial 1 -->
          <div class="group hover:scale-105 transition-all duration-300">
            <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100 relative h-full">
              <div class="flex items-center mb-6">
                <div class="flex-shrink-0">
                  <div class="h-14 w-14 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-lg shadow-md">
                    TM
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-xl font-bold text-gray-900">Thomas M.</h3>
                  <p class="text-indigo-600 font-medium">Business Owner</p>
                </div>
              </div>

              <div class="relative">
                <QuoteIcon class="h-8 w-8 text-indigo-100 absolute -top-4 -left-4" />
                <p class="text-gray-600 leading-relaxed relative z-10 pl-4">
                  "BizBoost transformed my marketing approach. I automated my social media campaigns and saw a 40% increase in customer engagement. The training modules taught me strategies I never knew existed."
                </p>
              </div>
            </div>
          </div>

          <!-- Testimonial 2 -->
          <div class="group hover:scale-105 transition-all duration-300">
            <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100 relative h-full">
              <div class="flex items-center mb-6">
                <div class="flex-shrink-0">
                  <div class="h-14 w-14 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-500 flex items-center justify-center text-white font-semibold text-lg shadow-md">
                    SB
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-xl font-bold text-gray-900">Sarah B.</h3>
                  <p class="text-emerald-600 font-medium">Entrepreneur</p>
                </div>
              </div>

              <div class="relative">
                <QuoteIcon class="h-8 w-8 text-emerald-100 absolute -top-4 -left-4" />
                <p class="text-gray-600 leading-relaxed relative z-10 pl-4">
                  "The financial literacy training was eye-opening. I learned how to manage my business finances properly. GrowFinance helped me organize my accounting and make better business decisions."
                </p>
              </div>
            </div>
          </div>

          <!-- Testimonial 3 -->
          <div class="group hover:scale-105 transition-all duration-300">
            <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100 relative h-full">
              <div class="flex items-center mb-6">
                <div class="flex-shrink-0">
                  <div class="h-14 w-14 rounded-full bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center text-white font-semibold text-lg shadow-md">
                    JK
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-xl font-bold text-gray-900">James K.</h3>
                  <p class="text-blue-600 font-medium">Small Business Owner</p>
                </div>
              </div>

              <div class="relative">
                <QuoteIcon class="h-8 w-8 text-blue-100 absolute -top-4 -left-4" />
                <p class="text-gray-600 leading-relaxed relative z-10 pl-4">
                  "The training content is practical and easy to follow. I'm now using the business tools daily and seeing real improvements in how I run my operations. The mentorship has been invaluable."
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
