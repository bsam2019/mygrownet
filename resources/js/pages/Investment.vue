<template>
    <div class="min-h-screen bg-gray-50">
      <Navigation />

      <!-- Hero Section -->
      <div class="py-16 bg-gradient-to-r from-blue-600 to-blue-800 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-64 h-64 bg-white opacity-5 rounded-full"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-96 h-96 bg-white opacity-5 rounded-full"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div class="text-center">
            <h1 class="text-5xl font-bold text-white mb-6 leading-tight">
              Investment Opportunities
            </h1>
            <p class="text-xl text-blue-100 max-w-3xl mx-auto leading-relaxed">
              Join our exclusive investment community and benefit from our innovative approach to sustainable wealth creation
            </p>
          </div>
        </div>
      </div>

      <!-- Investment Tiers -->
      <InvestmentTiers />

      <!-- Reward System Overview -->
      <RewardSystem />

      <!-- Investment Benefits -->
      <div class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Invest With Us?</h2>
            <p class="text-xl text-gray-500 max-w-3xl mx-auto">
              Experience the advantages of our community-driven investment approach
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Sustainable Returns -->
            <div class="group hover:scale-105 transition-all duration-300">
              <div class="h-full bg-gradient-to-br from-blue-50 to-white rounded-xl p-8 shadow-sm border border-blue-100 hover:shadow-md">
                <div class="flex items-start space-x-4">
                  <div class="flex-shrink-0">
                    <div class="p-3 bg-blue-600 rounded-xl shadow-inner">
                      <ChartBarIcon class="h-6 w-6 text-white" />
                    </div>
                  </div>
                  <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">
                      Sustainable Returns
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                      Our structured investment approach focuses on long-term, sustainable growth while maintaining consistent returns
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Community Network -->
            <div class="group hover:scale-105 transition-all duration-300">
              <div class="h-full bg-gradient-to-br from-blue-50 to-white rounded-xl p-8 shadow-sm border border-blue-100 hover:shadow-md">
                <div class="flex items-start space-x-4">
                  <div class="flex-shrink-0">
                    <div class="p-3 bg-blue-600 rounded-xl shadow-inner">
                      <UsersIcon class="h-6 w-6 text-white" />
                    </div>
                  </div>
                  <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">
                      Community Network
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                      Benefit from our growing network of investors and exclusive community-driven opportunities
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Protected Growth -->
            <div class="group hover:scale-105 transition-all duration-300">
              <div class="h-full bg-gradient-to-br from-blue-50 to-white rounded-xl p-8 shadow-sm border border-blue-100 hover:shadow-md">
                <div class="flex items-start space-x-4">
                  <div class="flex-shrink-0">
                    <div class="p-3 bg-blue-600 rounded-xl shadow-inner">
                      <ShieldCheckIcon class="h-6 w-6 text-white" />
                    </div>
                  </div>
                  <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">
                      Protected Growth
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                      Our investment policies and governance structure ensure your investments are protected
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Investment Process -->
      <div class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Getting Started</h2>
            <p class="text-xl text-gray-500 max-w-3xl mx-auto">
              Simple steps to begin your investment journey
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="group hover:scale-105 transition-all duration-300">
              <div class="text-center bg-white rounded-xl p-8 shadow-sm hover:shadow-md">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white text-xl font-bold mx-auto shadow-inner">
                  <ClipboardDocumentCheckIcon class="h-8 w-8 text-white" />
                </div>
                <h3 class="mt-6 text-lg font-semibold text-gray-900">Choose Your Tier</h3>
                <p class="mt-3 text-gray-500 leading-relaxed">Select an investment level that matches your goals</p>
              </div>
            </div>

            <div class="group hover:scale-105 transition-all duration-300">
              <div class="text-center bg-white rounded-xl p-8 shadow-sm hover:shadow-md">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white text-xl font-bold mx-auto shadow-inner">
                  <CalendarDaysIcon class="h-8 w-8 text-white" />
                </div>
                <h3 class="mt-6 text-lg font-semibold text-gray-900">Schedule Consultation</h3>
                <p class="mt-3 text-gray-500 leading-relaxed">Meet with our investment advisors</p>
              </div>
            </div>

            <div class="group hover:scale-105 transition-all duration-300">
              <div class="text-center bg-white rounded-xl p-8 shadow-sm hover:shadow-md">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white text-xl font-bold mx-auto shadow-inner">
                  <DocumentCheckIcon class="h-8 w-8 text-white" />
                </div>
                <h3 class="mt-6 text-lg font-semibold text-gray-900">Complete Registration</h3>
                <p class="mt-3 text-gray-500 leading-relaxed">Join our investment community</p>
              </div>
            </div>

            <div class="group hover:scale-105 transition-all duration-300">
              <div class="text-center bg-white rounded-xl p-8 shadow-sm hover:shadow-md">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white text-xl font-bold mx-auto shadow-inner">
                  <RocketLaunchIcon class="h-8 w-8 text-white" />
                </div>
                <h3 class="mt-6 text-lg font-semibold text-gray-900">Start Growing</h3>
                <p class="mt-3 text-gray-500 leading-relaxed">Begin your wealth creation journey</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <CallToAction />
      <Footer />
    </div>
  </template>

  <script>
  import {
    ChartBarIcon,
    UsersIcon,
    ShieldCheckIcon,
    ClipboardDocumentCheckIcon,
    CalendarDaysIcon,
    DocumentCheckIcon,
    RocketLaunchIcon
  } from '@heroicons/vue/24/outline'
  import Navigation from '@/components/custom/Navigation.vue'
  import InvestmentTiers from '@/components/custom/InvestmentTiers.vue'
  import RewardSystem from '@/components/custom/RewardSystem.vue'
  import CallToAction from '@/components/custom/CallToAction.vue'
  import Footer from '@/components/custom/Footer.vue'

  export default {
    name: 'Investment',
    components: {
      Navigation,
      InvestmentTiers,
      RewardSystem,
      CallToAction,
      Footer,
      ChartBarIcon,
      UsersIcon,
      ShieldCheckIcon,
      ClipboardDocumentCheckIcon,
      CalendarDaysIcon,
      DocumentCheckIcon,
      RocketLaunchIcon
    }
  }
  </script>
