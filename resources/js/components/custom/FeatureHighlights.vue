<template>
    <div class="pb-16 bg-gradient-to-b from-gray-50 to-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <!-- 7-Level Commissions -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-blue-50 to-white rounded-xl border border-blue-100 shadow-sm">
                <div class="p-3 bg-blue-600 rounded-xl shadow-md mb-4">
                  <CurrencyDollarIcon class="h-6 w-6 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  7-Level Commissions
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Earn from 7 levels deep with rates up to 15% on Level 1
                </p>
              </div>
            </div>

            <!-- Asset Rewards -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-green-50 to-white rounded-xl border border-green-100 shadow-sm">
                <div class="p-3 bg-green-600 rounded-xl shadow-md mb-4">
                  <BuildingOffice2Icon class="h-6 w-6 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Asset Rewards
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Earn physical assets and generate passive income streams
                </p>
              </div>
            </div>

            <!-- Community Projects -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-purple-50 to-white rounded-xl border border-purple-100 shadow-sm">
                <div class="p-3 bg-purple-600 rounded-xl shadow-md mb-4">
                  <UserGroupIcon class="h-6 w-6 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Community Projects
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Participate in collective investments and profit sharing
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
                  Educational Content
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Access tier-based learning materials and skill development
                </p>
              </div>
            </div>

            <!-- Performance Bonuses -->
            <div class="group hover:scale-105 transition-transform duration-300">
              <div class="flex flex-col items-center p-6 bg-gradient-to-br from-indigo-50 to-white rounded-xl border border-indigo-100 shadow-sm">
                <div class="p-3 bg-indigo-600 rounded-xl shadow-md mb-4">
                  <ChartBarIcon class="h-6 w-6 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                  Performance Bonuses
                </h3>
                <p class="text-sm text-gray-600 text-center">
                  Team volume bonuses and leadership rewards up to 10%
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
