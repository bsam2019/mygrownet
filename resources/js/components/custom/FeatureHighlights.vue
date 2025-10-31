<template>
    <div class="pb-16 bg-gradient-to-b from-gray-50 to-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Venture Builder -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-blue-50 to-white rounded-xl border border-blue-100 shadow-sm">
                <div class="p-3 bg-blue-600 rounded-xl shadow-md mb-4">
                  <BuildingOffice2Icon class="h-6 w-6 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Venture Builder
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Co-invest in vetted businesses and become a shareholder
                </p>
              </div>
            </div>

            <!-- Business Growth Fund -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-green-50 to-white rounded-xl border border-green-100 shadow-sm">
                <div class="p-3 bg-green-600 rounded-xl shadow-md mb-4">
                  <CurrencyDollarIcon class="h-6 w-6 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Business Growth Fund
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Access short-term financing for verified business orders
                </p>
              </div>
            </div>

            <!-- 7-Level Commissions -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-purple-50 to-white rounded-xl border border-purple-100 shadow-sm">
                <div class="p-3 bg-purple-600 rounded-xl shadow-md mb-4">
                  <UserGroupIcon class="h-6 w-6 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  7-Level Network
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Earn commissions from 7 levels deep in your network
                </p>
              </div>
            </div>

            <!-- Educational Content -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-yellow-50 to-white rounded-xl border border-yellow-100 shadow-sm">
                <div class="p-3 bg-yellow-600 rounded-xl shadow-md mb-4">
                  <AcademicCapIcon class="h-6 w-6 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Learning & Development
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Access training, coaching, and skill development resources
                </p>
              </div>
            </div>

            <!-- Profit Sharing -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-indigo-50 to-white rounded-xl border border-indigo-100 shadow-sm">
                <div class="p-3 bg-indigo-600 rounded-xl shadow-md mb-4">
                  <ChartBarIcon class="h-6 w-6 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Profit Sharing
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Quarterly distributions from community empowerment projects
                </p>
              </div>
            </div>

            <!-- Points & Rewards -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-pink-50 to-white rounded-xl border border-pink-100 shadow-sm">
                <div class="p-3 bg-pink-600 rounded-xl shadow-md mb-4">
                  <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                  </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Points & Rewards
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Earn points, unlock achievements, and advance your level
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
    UserGroupIcon,
    BuildingOffice2Icon,
    AcademicCapIcon
  } from '@heroicons/vue/24/outline'

  export default {
    components: {
      CurrencyDollarIcon,
      ChartBarIcon,
      UserGroupIcon,
      BuildingOffice2Icon,
      AcademicCapIcon
    }
  }
  </script>
