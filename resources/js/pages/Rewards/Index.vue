<template>
  <div class="min-h-screen bg-gray-50">
    <Navigation />

    <!-- Hero Section -->
    <div class="py-12 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg shadow-xl p-8 md:p-12 relative overflow-hidden">
          <!-- Decorative circles -->
          <div class="absolute top-10 left-20 w-48 h-48 bg-white bg-opacity-5 rounded-full"></div>
          <div class="absolute bottom-16 right-16 w-32 h-32 bg-white bg-opacity-5 rounded-full"></div>

          <div class="relative z-10">
            <h1 class="text-4xl font-bold text-white leading-tight">Rewards & Loyalty Benefits</h1>
            <p class="mt-4 text-lg text-white text-opacity-90 max-w-3xl">
              Earn points and unlock exclusive perks through platform activity and engagement
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- How to Earn Points -->
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-800 text-center mb-12">How to Earn Points</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Buy Products -->
          <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="h-16 w-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-6 mx-auto">
              <ShoppingCartIcon class="h-8 w-8" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-800 text-center mb-3">Buy Products</h3>
            <p class="text-gray-600 text-center">
              Earn points when you purchase from the marketplace. Every purchase contributes to your loyalty rewards.
            </p>
          </div>

          <!-- Complete Training -->
          <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="h-16 w-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-6 mx-auto">
              <AcademicCapIcon class="h-8 w-8" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-800 text-center mb-3">Complete Training</h3>
            <p class="text-gray-600 text-center">
              Get bonus points for finishing courses and developing your business skills.
            </p>
          </div>

          <!-- Stay Active -->
          <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="h-16 w-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-6 mx-auto">
              <UsersIcon class="h-8 w-8" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-800 text-center mb-3">Stay Active</h3>
            <p class="text-gray-600 text-center">
              Monthly activity bonuses for engaged members who regularly use the platform.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Redeem Your Points -->
    <div class="py-12 bg-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-800 text-center mb-12">Redeem Your Points</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Discounts -->
          <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="flex items-start">
              <div class="h-12 w-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <TagIcon class="h-6 w-6" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Discounts</h3>
                <p class="text-gray-600">
                  Use points for discounts on marketplace purchases. Save on products and services you need.
                </p>
              </div>
            </div>
          </div>

          <!-- Member-Only Offers -->
          <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="flex items-start">
              <div class="h-12 w-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <SparklesIcon class="h-6 w-6" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Member-Only Offers</h3>
                <p class="text-gray-600">
                  Access exclusive deals and promotions available only to loyalty members.
                </p>
              </div>
            </div>
          </div>

          <!-- Store Credit -->
          <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="flex items-start">
              <div class="h-12 w-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <CreditCardIcon class="h-6 w-6" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Store Credit</h3>
                <p class="text-gray-600">
                  Convert points to store credit that can be used for future purchases on the platform.
                </p>
              </div>
            </div>
          </div>

          <!-- Training Access -->
          <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="flex items-start">
              <div class="h-12 w-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <BookOpenIcon class="h-6 w-6" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Training Access</h3>
                <p class="text-gray-600">
                  Unlock premium courses with points. Invest in your skills and business development.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Additional Benefits -->
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-8 md:p-12">
          <h2 class="text-3xl font-bold text-gray-800 mb-6">Additional Member Benefits</h2>
          <div class="space-y-4">
            <div class="flex items-start">
              <div class="h-6 w-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 mt-1">
                <CheckIcon class="h-4 w-4" aria-hidden="true" />
              </div>
              <p class="ml-3 text-gray-600">
                <strong class="text-gray-800">Priority Support:</strong> Active members receive priority customer support and assistance
              </p>
            </div>
            <div class="flex items-start">
              <div class="h-6 w-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 mt-1">
                <CheckIcon class="h-4 w-4" aria-hidden="true" />
              </div>
              <p class="ml-3 text-gray-600">
                <strong class="text-gray-800">Early Access:</strong> Be the first to know about new products and features
              </p>
            </div>
            <div class="flex items-start">
              <div class="h-6 w-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 mt-1">
                <CheckIcon class="h-4 w-4" aria-hidden="true" />
              </div>
              <p class="ml-3 text-gray-600">
                <strong class="text-gray-800">Community Events:</strong> Invitations to exclusive member events and workshops
              </p>
            </div>
            <div class="flex items-start">
              <div class="h-6 w-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 mt-1">
                <CheckIcon class="h-4 w-4" aria-hidden="true" />
              </div>
              <p class="ml-3 text-gray-600">
                <strong class="text-gray-800">Training Benefits:</strong> Additional training benefits for active members
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- How Points Work -->
    <div class="py-12 bg-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-8 md:p-12">
          <h2 class="text-3xl font-bold text-gray-800 mb-6">How Points Work</h2>
          <div class="space-y-6">
            <div>
              <h3 class="text-xl font-semibold text-gray-800 mb-3">Earning Points</h3>
              <p class="text-gray-600 mb-4">
                Points are tied to specific product purchases and platform activities. The more you engage with the platform, the more points you earn.
              </p>
              <ul class="space-y-2 text-gray-600">
                <li class="flex items-start">
                  <span class="text-blue-600 mr-2">•</span>
                  <span>Marketplace purchases: Earn 1 point per K10 spent</span>
                </li>
                <li class="flex items-start">
                  <span class="text-blue-600 mr-2">•</span>
                  <span>Course completion: Earn 50-200 points per course</span>
                </li>
                <li class="flex items-start">
                  <span class="text-blue-600 mr-2">•</span>
                  <span>Monthly activity: Earn 100 bonus points for consistent engagement</span>
                </li>
              </ul>
            </div>

            <div>
              <h3 class="text-xl font-semibold text-gray-800 mb-3">Redeeming Points</h3>
              <p class="text-gray-600 mb-4">
                Points can be redeemed for various platform benefits. Check your account dashboard to see your current point balance and available redemption options.
              </p>
              <ul class="space-y-2 text-gray-600">
                <li class="flex items-start">
                  <span class="text-blue-600 mr-2">•</span>
                  <span>100 points = K10 discount on marketplace purchases</span>
                </li>
                <li class="flex items-start">
                  <span class="text-blue-600 mr-2">•</span>
                  <span>500 points = Access to premium training module</span>
                </li>
                <li class="flex items-start">
                  <span class="text-blue-600 mr-2">•</span>
                  <span>1000 points = K100 store credit</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Important Disclaimer -->
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-amber-50 border-l-4 border-amber-500 rounded-lg p-6">
          <div class="flex items-start">
            <InformationCircleIcon class="h-6 w-6 text-amber-600 flex-shrink-0 mt-1" aria-hidden="true" />
            <div class="ml-3">
              <h3 class="text-lg font-semibold text-amber-900 mb-2">Important Information</h3>
              <p class="text-amber-800">
                Points are for platform benefits only and cannot be withdrawn as cash. Points have no monetary value outside of the MyGrowNet platform and are intended solely for redeeming platform benefits, discounts, and services.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Call to Action -->
    <div class="py-12 bg-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Ready to Start Earning Rewards?</h2>
        <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
          Join MyGrowNet today and start earning loyalty points through platform activity and engagement.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a
            href="/starter-kits"
            class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors"
          >
            Join Now
          </a>
          <a
            href="/marketplace"
            class="inline-flex items-center justify-center px-8 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors"
          >
            Browse Marketplace
          </a>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';
import {
  ShoppingCartIcon,
  AcademicCapIcon,
  UsersIcon,
  TagIcon,
  SparklesIcon,
  CreditCardIcon,
  BookOpenIcon,
  CheckIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline';
</script>
