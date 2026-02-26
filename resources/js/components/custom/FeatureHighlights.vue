<template>
    <div class="py-16 bg-gradient-to-b from-gray-50 to-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-12">
          <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-100 text-emerald-700 text-sm font-medium mb-4">
            <SparklesIcon class="w-4 h-4" aria-hidden="true" />
            Products & Services
          </span>
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
            Everything You Need to Succeed
          </h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Access training, mentorship, marketplace, and business tools all in one platform.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
          <!-- GrowBuilder & GrowNet - Priority Cards -->
          <div class="group">
            <div class="flex flex-col p-8 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-2xl border-2 border-indigo-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
              <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 flex items-center justify-center bg-indigo-600 rounded-2xl shadow-sm group-hover:scale-105 transition-transform">
                  <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-gray-900">GrowBuilder</h3>
                  <p class="text-sm text-indigo-600 font-medium">Website Builder</p>
                </div>
              </div>
              <p class="text-sm text-gray-700 mb-4">
                Create professional websites without coding - perfect for businesses, portfolios, and online stores
              </p>
              <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                <div>• Drag-and-drop builder</div>
                <div>• Professional templates</div>
                <div>• Mobile responsive</div>
                <div>• Custom domains</div>
              </div>
            </div>
          </div>

          <div class="group">
            <div class="flex flex-col p-8 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl border-2 border-emerald-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
              <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 flex items-center justify-center bg-emerald-600 rounded-2xl shadow-sm group-hover:scale-105 transition-transform">
                  <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-gray-900">GrowNet</h3>
                  <p class="text-sm text-emerald-600 font-medium">Community Network</p>
                </div>
              </div>
              <p class="text-sm text-gray-700 mb-4">
                Join our community and earn through referrals, training, and business opportunities
              </p>
              <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                <div>• Referral commissions</div>
                <div>• Business training</div>
                <div>• Profit-sharing</div>
                <div>• Community support</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Other Services -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Business Apps Suite -->
          <div class="group">
            <div class="flex flex-col items-center p-6 bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl border border-blue-100/50 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-14 h-14 flex items-center justify-center bg-blue-600 rounded-2xl shadow-sm mb-4 group-hover:scale-105 transition-transform">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                Business Tools
              </h3>
              <p class="text-sm text-gray-600 text-center mb-3">
                Manage finances, team, and operations
              </p>
              <div class="text-xs text-blue-600 font-medium space-y-1 text-center">
                <div>• GrowFinance</div>
                <div>• GrowBiz</div>
                <div>• BizBoost</div>
              </div>
            </div>
          </div>

          <!-- Marketplace -->
          <div class="group">
            <div class="flex flex-col items-center p-6 bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl border border-purple-100/50 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-14 h-14 flex items-center justify-center bg-purple-600 rounded-2xl shadow-sm mb-4 group-hover:scale-105 transition-transform">
                <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                Marketplace
              </h3>
              <p class="text-sm text-gray-600 text-center">
                Buy and sell products with trusted sellers
              </p>
            </div>
          </div>

          <!-- Training Center -->
          <div class="group">
            <div class="flex flex-col items-center p-6 bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border border-amber-100/50 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-14 h-14 flex items-center justify-center bg-amber-600 rounded-2xl shadow-sm mb-4 group-hover:scale-105 transition-transform">
                <AcademicCapIcon class="h-7 w-7 text-white" />
              </div>
              <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                Training
              </h3>
              <p class="text-sm text-gray-600 text-center">
                Courses and skill development programs
              </p>
            </div>
          </div>

          <!-- Mentorship -->
          <div class="group">
            <div class="flex flex-col items-center p-6 bg-gradient-to-br from-cyan-50 to-blue-50 rounded-2xl border border-cyan-100/50 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
              <div class="w-14 h-14 flex items-center justify-center bg-cyan-600 rounded-2xl shadow-sm mb-4 group-hover:scale-105 transition-transform">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                Mentorship
              </h3>
              <p class="text-sm text-gray-600 text-center">
                Connect with experienced mentors
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  import {
    CurrencyDollarIcon,
    ChartBarIcon,
    BuildingOffice2Icon,
    AcademicCapIcon,
    SparklesIcon
  } from '@heroicons/vue/24/outline'

  export default {
    components: {
      CurrencyDollarIcon,
      ChartBarIcon,
      BuildingOffice2Icon,
      AcademicCapIcon,
      SparklesIcon
    }
  }
  </script>
