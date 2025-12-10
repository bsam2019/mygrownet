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

        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Training Center -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-purple-50 to-white rounded-xl border border-purple-100 shadow-sm">
                <div class="p-3 bg-purple-600 rounded-xl shadow-md mb-4">
                  <AcademicCapIcon class="h-6 w-6 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Training Center
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Structured courses, workshops, and skill development programs
                </p>
              </div>
            </div>

            <!-- Mentorship Program -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-emerald-50 to-white rounded-xl border border-emerald-100 shadow-sm">
                <div class="p-3 bg-emerald-600 rounded-xl shadow-md mb-4">
                  <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Mentorship Program
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Connect with experienced mentors for personalized guidance
                </p>
              </div>
            </div>

            <!-- Marketplace (Physical Products) -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-blue-50 to-white rounded-xl border border-blue-100 shadow-sm">
                <div class="p-3 bg-blue-600 rounded-xl shadow-md mb-4">
                  <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Marketplace
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Shop physical products from trusted vendors and suppliers
                </p>
              </div>
            </div>

            <!-- Digital Product Store -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-cyan-50 to-white rounded-xl border border-cyan-100 shadow-sm">
                <div class="p-3 bg-cyan-600 rounded-xl shadow-md mb-4">
                  <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                  </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Digital Product Store
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  E-books, courses, templates, and digital downloads
                </p>
              </div>
            </div>

            <!-- Business Apps Suite -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-indigo-50 to-white rounded-xl border border-indigo-100 shadow-sm">
                <div class="p-3 bg-indigo-600 rounded-xl shadow-md mb-4">
                  <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                  </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Business Apps Suite
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  GrowFinance, GrowBiz, and BizBoost for business management
                </p>
              </div>
            </div>

            <!-- Venture Builder -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-amber-50 to-white rounded-xl border border-amber-100 shadow-sm">
                <div class="p-3 bg-amber-600 rounded-xl shadow-md mb-4">
                  <BuildingOffice2Icon class="h-6 w-6 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Venture Builder
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Join vetted venture-building initiatives and opportunities
                </p>
              </div>
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
