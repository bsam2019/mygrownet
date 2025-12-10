<template>
  <GuestLayout title="Join MyGrowNet - Starter Kits">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <h1 class="text-4xl font-extrabold text-white sm:text-5xl md:text-6xl">
            Join MyGrowNet
          </h1>
          <p class="mt-4 text-xl text-blue-100 max-w-3xl mx-auto">
            Choose your starter kit and begin your journey to business empowerment
          </p>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Starter Kits Grid -->
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
          Choose Your Starter Kit
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <!-- Basic Starter Kit -->
          <div class="bg-white rounded-lg shadow-lg overflow-hidden border-2 border-gray-200 hover:border-blue-500 transition">
            <div class="bg-gray-50 px-6 py-4">
              <h3 class="text-2xl font-bold text-gray-900">Basic</h3>
              <p class="text-gray-600 mt-1">Perfect for getting started</p>
            </div>
            <div class="px-6 py-8">
              <div class="mb-6">
                <span class="text-4xl font-extrabold text-gray-900">K500</span>
                <span class="text-gray-600 ml-2">one-time</span>
              </div>
              <ul class="space-y-3 mb-8">
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">3-module business course</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">K100 shop credit</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">Basic business tools</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">30-day library access</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">Community access</span>
                </li>
              </ul>
              <Link
                :href="route('register')"
                class="block w-full text-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition"
              >
                Get Started
              </Link>
            </div>
          </div>

          <!-- Professional Starter Kit -->
          <div class="bg-white rounded-lg shadow-xl overflow-hidden border-2 border-blue-500 relative">
            <div class="absolute top-0 right-0 bg-blue-500 text-white px-4 py-1 text-sm font-semibold rounded-bl-lg">
              Popular
            </div>
            <div class="bg-blue-50 px-6 py-4">
              <h3 class="text-2xl font-bold text-gray-900">Professional</h3>
              <p class="text-gray-600 mt-1">Most popular choice</p>
            </div>
            <div class="px-6 py-8">
              <div class="mb-6">
                <span class="text-4xl font-extrabold text-gray-900">K1,000</span>
                <span class="text-gray-600 ml-2">one-time</span>
              </div>
              <ul class="space-y-3 mb-8">
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">Everything in Basic</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">6-module advanced course</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">K250 shop credit</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">Premium business tools</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">90-day library access</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">Monthly webinars</span>
                </li>
              </ul>
              <Link
                :href="route('register')"
                class="block w-full text-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition"
              >
                Get Started
              </Link>
            </div>
          </div>

          <!-- Business Builder Starter Kit -->
          <div class="bg-white rounded-lg shadow-lg overflow-hidden border-2 border-gray-200 hover:border-blue-500 transition">
            <div class="bg-gray-50 px-6 py-4">
              <h3 class="text-2xl font-bold text-gray-900">Business Builder</h3>
              <p class="text-gray-600 mt-1">For serious entrepreneurs</p>
            </div>
            <div class="px-6 py-8">
              <div class="mb-6">
                <span class="text-4xl font-extrabold text-gray-900">K2,000</span>
                <span class="text-gray-600 ml-2">one-time</span>
              </div>
              <ul class="space-y-3 mb-8">
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">Everything in Professional</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">Complete training library</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">K500 shop credit</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">All business tools</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">Unlimited library access</span>
                </li>
                <li class="flex items-start">
                  <CheckIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span class="text-gray-700">1-on-1 coaching session</span>
                </li>
              </ul>
              <Link
                :href="route('register')"
                class="block w-full text-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition"
              >
                Get Started
              </Link>
            </div>
          </div>
        </div>
      </section>

      <!-- What's Included Section -->
      <section class="mb-16 bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
          What's Included
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
              <AcademicCapIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Training Modules</h3>
            <p class="text-gray-600 text-sm">
              Comprehensive business courses and skill development programs
            </p>
          </div>

          <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
              <ShoppingCartIcon class="h-8 w-8 text-green-600" aria-hidden="true" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Shop Credit</h3>
            <p class="text-gray-600 text-sm">
              Immediate credit to use on marketplace products
            </p>
          </div>

          <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
              <WrenchScrewdriverIcon class="h-8 w-8 text-purple-600" aria-hidden="true" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Business Tools</h3>
            <p class="text-gray-600 text-sm">
              Templates, calculators, and resources for your business
            </p>
          </div>

          <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-full mb-4">
              <UsersIcon class="h-8 w-8 text-indigo-600" aria-hidden="true" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Community Access</h3>
            <p class="text-gray-600 text-sm">
              Connect with other members and grow together
            </p>
          </div>
        </div>
      </section>

      <!-- Membership Benefits Section -->
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
          Membership Benefits
        </h2>
        
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-6 w-6 text-green-500" aria-hidden="true" />
              </div>
              <div class="ml-3">
                <h3 class="text-lg font-semibold text-gray-900">Marketplace Access</h3>
                <p class="text-gray-600">Browse and purchase from our growing product catalog</p>
              </div>
            </div>

            <div class="flex items-start">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-6 w-6 text-green-500" aria-hidden="true" />
              </div>
              <div class="ml-3">
                <h3 class="text-lg font-semibold text-gray-900">Training Library</h3>
                <p class="text-gray-600">Access educational content and skill development courses</p>
              </div>
            </div>

            <div class="flex items-start">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-6 w-6 text-green-500" aria-hidden="true" />
              </div>
              <div class="ml-3">
                <h3 class="text-lg font-semibold text-gray-900">Loyalty Rewards</h3>
                <p class="text-gray-600">Earn points on purchases and platform activity</p>
              </div>
            </div>

            <div class="flex items-start">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-6 w-6 text-green-500" aria-hidden="true" />
              </div>
              <div class="ml-3">
                <h3 class="text-lg font-semibold text-gray-900">Referral Program</h3>
                <p class="text-gray-600">Share MyGrowNet and earn referral rewards</p>
              </div>
            </div>

            <div class="flex items-start">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-6 w-6 text-green-500" aria-hidden="true" />
              </div>
              <div class="ml-3">
                <h3 class="text-lg font-semibold text-gray-900">Business Tools</h3>
                <p class="text-gray-600">Access BizBoost, GrowFinance, and other business apps</p>
              </div>
            </div>

            <div class="flex items-start">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-6 w-6 text-green-500" aria-hidden="true" />
              </div>
              <div class="ml-3">
                <h3 class="text-lg font-semibold text-gray-900">Community Support</h3>
                <p class="text-gray-600">Connect with members and access support resources</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Monthly Activity Requirements Section -->
      <section class="mb-16 bg-amber-50 rounded-lg p-8 border border-amber-200">
        <h2 class="text-3xl font-bold text-gray-900 mb-4 text-center">
          Stay Active
        </h2>
        <p class="text-center text-gray-600 mb-8 max-w-3xl mx-auto">
          To maintain your membership benefits and qualify for rewards, stay active on the platform each month
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white rounded-lg p-6 text-center">
            <ShoppingBagIcon class="h-12 w-12 text-blue-600 mx-auto mb-4" aria-hidden="true" />
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Make Purchases</h3>
            <p class="text-gray-600 text-sm">
              Shop from the marketplace or use your business tools
            </p>
          </div>

          <div class="bg-white rounded-lg p-6 text-center">
            <BookOpenIcon class="h-12 w-12 text-green-600 mx-auto mb-4" aria-hidden="true" />
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Complete Training</h3>
            <p class="text-gray-600 text-sm">
              Engage with courses and educational content
            </p>
          </div>

          <div class="bg-white rounded-lg p-6 text-center">
            <UserGroupIcon class="h-12 w-12 text-purple-600 mx-auto mb-4" aria-hidden="true" />
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Engage with Community</h3>
            <p class="text-gray-600 text-sm">
              Participate in forums, webinars, and community events
            </p>
          </div>
        </div>
      </section>

      <!-- How to Join Section -->
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
          Simple Steps to Get Started
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 text-white rounded-full text-xl font-bold mb-4">
              1
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Choose Your Kit</h3>
            <p class="text-gray-600 text-sm">
              Select the starter kit that fits your needs
            </p>
          </div>

          <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 text-white rounded-full text-xl font-bold mb-4">
              2
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Create Account</h3>
            <p class="text-gray-600 text-sm">
              Register with your details and complete payment
            </p>
          </div>

          <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 text-white rounded-full text-xl font-bold mb-4">
              3
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Access Platform</h3>
            <p class="text-gray-600 text-sm">
              Log in and explore your dashboard and tools
            </p>
          </div>

          <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 text-white rounded-full text-xl font-bold mb-4">
              4
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Start Growing</h3>
            <p class="text-gray-600 text-sm">
              Begin your training and use your business tools
            </p>
          </div>
        </div>
      </section>

      <!-- FAQ Section -->
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
          Frequently Asked Questions
        </h2>
        
        <div class="bg-white rounded-lg shadow-lg divide-y divide-gray-200">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
              What happens after I purchase a starter kit?
            </h3>
            <p class="text-gray-600">
              You'll immediately receive access to your member dashboard, training modules, shop credit, and all included tools. You can start learning and using the platform right away.
            </p>
          </div>

          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
              Are there any monthly fees?
            </h3>
            <p class="text-gray-600">
              The starter kit is a one-time purchase. To maintain active status and qualify for certain benefits, you'll need to stay engaged with the platform through purchases, training, or community participation.
            </p>
          </div>

          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
              Can I upgrade my starter kit later?
            </h3>
            <p class="text-gray-600">
              Yes! You can upgrade to a higher tier at any time. You'll only pay the difference between your current kit and the new one.
            </p>
          </div>

          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
              How do I earn rewards?
            </h3>
            <p class="text-gray-600">
              You earn loyalty points through marketplace purchases, completing training modules, and staying active on the platform. You can also earn referral rewards by sharing MyGrowNet with others.
            </p>
          </div>
        </div>
      </section>

      <!-- Final CTA -->
      <section class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-xl p-12 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">
          Ready to Start Your Journey?
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
          Join MyGrowNet today and get access to business tools, training, and a supportive community
        </p>
        <Link
          :href="route('register')"
          class="inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 transition"
        >
          Choose Your Starter Kit
          <ArrowRightIcon class="ml-2 h-5 w-5" aria-hidden="true" />
        </Link>
      </section>
    </div>
  </GuestLayout>
</template>

<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Link } from '@inertiajs/vue3';
import {
  CheckIcon,
  CheckCircleIcon,
  AcademicCapIcon,
  ShoppingCartIcon,
  WrenchScrewdriverIcon,
  UsersIcon,
  ShoppingBagIcon,
  BookOpenIcon,
  UserGroupIcon,
  ArrowRightIcon,
} from '@heroicons/vue/24/outline';
</script>
