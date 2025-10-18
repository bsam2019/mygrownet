<template>
  <div class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-4xl font-bold text-gray-900 mb-4">Platform Capabilities</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Built for scale with powerful features to support your growth journey
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Network Capacity -->
        <div class="text-center group">
          <div class="bg-gradient-to-br from-blue-50 to-white rounded-2xl p-8 shadow-lg border border-blue-100 hover:shadow-xl transition-all duration-300 group-hover:scale-105">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full mb-6 shadow-md">
              <UsersIcon class="h-8 w-8 text-white" />
            </div>
            <div class="text-4xl font-bold text-gray-900 mb-2">3,279</div>
            <div class="text-lg font-medium text-gray-600 mb-2">Network Capacity</div>
            <div class="text-sm text-gray-500">Maximum 3×3 matrix positions</div>
          </div>
        </div>

        <!-- Professional Levels -->
        <div class="text-center group">
          <div class="bg-gradient-to-br from-green-50 to-white rounded-2xl p-8 shadow-lg border border-green-100 hover:shadow-xl transition-all duration-300 group-hover:scale-105">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-600 rounded-full mb-6 shadow-md">
              <ChartBarIcon class="h-8 w-8 text-white" />
            </div>
            <div class="text-4xl font-bold text-gray-900 mb-2">7</div>
            <div class="text-lg font-medium text-gray-600 mb-2">Professional Levels</div>
            <div class="text-sm text-gray-500">Associate to Ambassador progression</div>
          </div>
        </div>

        <!-- Income Streams -->
        <div class="text-center group">
          <div class="bg-gradient-to-br from-purple-50 to-white rounded-2xl p-8 shadow-lg border border-purple-100 hover:shadow-xl transition-all duration-300 group-hover:scale-105">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-600 rounded-full mb-6 shadow-md">
              <CurrencyDollarIcon class="h-8 w-8 text-white" />
            </div>
            <div class="text-4xl font-bold text-gray-900 mb-2">6</div>
            <div class="text-lg font-medium text-gray-600 mb-2">Income Streams</div>
            <div class="text-sm text-gray-500">Referrals, commissions & profit-sharing</div>
          </div>
        </div>

        <!-- Profit Distribution -->
        <div class="text-center group">
          <div class="bg-gradient-to-br from-yellow-50 to-white rounded-2xl p-8 shadow-lg border border-yellow-100 hover:shadow-xl transition-all duration-300 group-hover:scale-105">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-600 rounded-full mb-6 shadow-md">
              <BuildingOffice2Icon class="h-8 w-8 text-white" />
            </div>
            <div class="text-4xl font-bold text-gray-900 mb-2">60%</div>
            <div class="text-lg font-medium text-gray-600 mb-2">Profit Distribution</div>
            <div class="text-sm text-gray-500">Project profits shared quarterly</div>
          </div>
        </div>
      </div>

      <!-- Platform Features -->
      <div class="mt-16 bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-8 text-white">
        <div class="text-center mb-8">
          <h3 class="text-2xl font-bold mb-4">Platform Features</h3>
          <p class="text-blue-100 text-lg">Built with transparency, education, and community growth in mind</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-white bg-opacity-20 rounded-full mb-4">
              <AcademicCapIcon class="h-6 w-6 text-white" />
            </div>
            <div class="text-xl font-bold mb-2">Learning & Development</div>
            <div class="text-blue-200 text-sm">E-books, coaching, and skills training</div>
          </div>
          
          <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-white bg-opacity-20 rounded-full mb-4">
              <TrophyIcon class="h-6 w-6 text-white" />
            </div>
            <div class="text-xl font-bold mb-2">Mobile Money Integration</div>
            <div class="text-blue-200 text-sm">MTN MoMo & Airtel Money support</div>
          </div>
          
          <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-white bg-opacity-20 rounded-full mb-4">
              <ShieldCheckIcon class="h-6 w-6 text-white" />
            </div>
            <div class="text-xl font-bold mb-2">Legal Compliance</div>
            <div class="text-blue-200 text-sm">Private limited company structure</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  UsersIcon,
  CurrencyDollarIcon,
  BuildingOffice2Icon,
  ChartBarIcon,
  TrophyIcon,
  AcademicCapIcon,
  ShieldCheckIcon
} from '@heroicons/vue/24/outline'

export default {
  components: {
    UsersIcon,
    CurrencyDollarIcon,
    BuildingOffice2Icon,
    ChartBarIcon,
    TrophyIcon,
    AcademicCapIcon,
    ShieldCheckIcon
  }
}
</script>