<template>
    <div class="py-16 bg-gradient-to-b from-gray-50 to-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">Membership Tiers</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Choose your membership level and unlock progressive benefits and earning potential
          </p>
        </div>

        <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
          <!-- Bronze Tier -->
          <div class="group bg-white rounded-xl shadow-lg overflow-hidden flex flex-col relative transform hover:-translate-y-1 transition-all duration-300">
            <div class="absolute inset-0 bg-gradient-to-b from-gray-50/50 to-transparent"></div>
            <div class="h-2 bg-gray-400"></div>
            <div class="px-6 py-8 flex-grow relative">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Bronze</h3>
                <BanknotesIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="flex items-baseline mb-2">
                <span class="text-4xl font-bold bg-gradient-to-r from-gray-600 to-gray-400 bg-clip-text text-transparent">K50</span>
              </div>
              <p class="text-sm text-gray-500 mb-6">Monthly membership</p>
              <div class="border-t border-gray-100 pt-6">
                <ul class="space-y-4">
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-gray-500" />
                    <span class="text-sm text-gray-600">Basic educational content</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-gray-500" />
                    <span class="text-sm text-gray-600">Community projects</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-gray-500" />
                    <span class="text-sm text-gray-600">Level 1-3 commissions</span>
                  </li>
                </ul>
              </div>
            </div>
            <div class="p-6">
              <Link :href="route('register')"
                    class="block w-full bg-gradient-to-r from-gray-100 to-gray-50 text-gray-600 font-semibold rounded-xl py-3 text-center hover:from-gray-200 hover:to-gray-100 transition-all duration-300 shadow-sm">
                Get Started
              </Link>
            </div>
          </div>

          <!-- Silver Tier -->
          <div class="group bg-white rounded-xl shadow-lg overflow-hidden flex flex-col relative transform hover:-translate-y-1 transition-all duration-300">
            <div class="absolute inset-0 bg-gradient-to-b from-blue-50/50 to-transparent"></div>
            <div class="h-2 bg-blue-400"></div>
            <div class="px-6 py-8 flex-grow relative">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Silver</h3>
                <ChartBarIcon class="h-6 w-6 text-blue-400" />
              </div>
              <div class="flex items-baseline mb-2">
                <span class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-blue-400 bg-clip-text text-transparent">K100</span>
              </div>
              <p class="text-sm text-gray-500 mb-6">Monthly membership</p>
              <div class="border-t border-gray-100 pt-6">
                <ul class="space-y-4">
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-blue-500" />
                    <span class="text-sm text-gray-600">Enhanced content</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-blue-500" />
                    <span class="text-sm text-gray-600">Priority project voting</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-blue-500" />
                    <span class="text-sm text-gray-600">Level 1-4 commissions</span>
                  </li>
                </ul>
              </div>
            </div>
            <div class="p-6">
              <Link :href="route('register')"
                    class="block w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-xl py-3 text-center hover:from-blue-600 hover:to-blue-700 transition-all duration-300 shadow-sm">
                Get Started
              </Link>
            </div>
          </div>

          <!-- Gold Tier - Popular -->
          <div class="group bg-white rounded-xl shadow-xl overflow-visible flex flex-col relative transform hover:-translate-y-1 transition-all duration-300">
            <!-- Updated Popular badge positioning and styling -->
            <div class="absolute -top-3 right-4 z-20">
                <span class="inline-flex items-center px-4 py-1 rounded-full text-sm font-semibold bg-gradient-to-r from-yellow-600 to-yellow-400 text-white shadow-lg border border-yellow-200">
                <StarIcon class="h-4 w-4 mr-1" />
                Popular
                </span>
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-yellow-50/50 to-transparent"></div>
            <div class="h-2 bg-yellow-500"></div>
            <div class="px-6 py-8 flex-grow relative">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Gold</h3>
                <UserGroupIcon class="h-6 w-6 text-yellow-500" />
              </div>
              <div class="flex items-baseline mb-2">
                <span class="text-4xl font-bold bg-gradient-to-r from-yellow-600 to-yellow-400 bg-clip-text text-transparent">K200</span>
              </div>
              <p class="text-sm text-gray-500 mb-6">Monthly membership</p>
              <div class="border-t border-gray-100 pt-6">
                <ul class="space-y-4">
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-yellow-500" />
                    <span class="text-sm text-gray-600">Premium content</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-yellow-500" />
                    <span class="text-sm text-gray-600">Project proposal rights</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-yellow-500" />
                    <span class="text-sm text-gray-600">Full 5-level commissions</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-yellow-500" />
                    <span class="text-sm text-gray-600">Asset reward eligibility</span>
                  </li>
                </ul>
              </div>
            </div>
            <div class="p-6">
              <Link :href="route('register')"
                    class="block w-full bg-gradient-to-r from-yellow-500 to-yellow-600 text-white font-semibold rounded-xl py-3 text-center hover:from-yellow-600 hover:to-yellow-700 transition-all duration-300 shadow-sm">
                Get Started
              </Link>
            </div>
          </div>

          <!-- Diamond Tier -->
          <div class="group bg-white rounded-xl shadow-xl overflow-hidden flex flex-col relative transform hover:-translate-y-1 transition-all duration-300">
            <div class="absolute inset-0 bg-gradient-to-b from-purple-50/50 to-transparent"></div>
            <div class="h-2 bg-purple-600"></div>
            <div class="px-6 py-8 flex-grow relative">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Diamond</h3>
                <StarIcon class="h-6 w-6 text-purple-600" />
              </div>
              <div class="flex items-baseline mb-2">
                <span class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-purple-400 bg-clip-text text-transparent">K500</span>
              </div>
              <p class="text-sm text-gray-500 mb-6">Monthly membership</p>
              <div class="border-t border-gray-100 pt-6">
                <ul class="space-y-4">
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-purple-500" />
                    <span class="text-sm text-gray-600">Exclusive content access</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-purple-500" />
                    <span class="text-sm text-gray-600">Leadership development</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-purple-500" />
                    <span class="text-sm text-gray-600">Maximum commission rates</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-purple-500" />
                    <span class="text-sm text-gray-600">Premium asset rewards</span>
                  </li>
                </ul>
              </div>
            </div>
            <div class="p-6">
              <Link :href="route('register')"
                    class="block w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-xl py-3 text-center hover:from-purple-700 hover:to-purple-800 transition-all duration-300 shadow-sm">
                Get Started
              </Link>
            </div>
          </div>

          <!-- Elite Tier -->
          <div class="group bg-white rounded-xl shadow-xl overflow-visible flex flex-col relative transform hover:-translate-y-1 transition-all duration-300">
            <!-- Updated Premium badge positioning and styling -->
            <div class="absolute -top-3 right-4 z-20">
                <span class="inline-flex items-center px-4 py-1 rounded-full text-sm font-semibold bg-gradient-to-r from-indigo-600 to-indigo-400 text-white shadow-lg border border-indigo-200">
                <SparklesIcon class="h-4 w-4 mr-1" />
                Premium
                </span>
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-indigo-50/50 to-transparent"></div>
            <div class="h-2 bg-indigo-600"></div>
            <div class="px-6 py-8 flex-grow relative">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Elite</h3>
                <SparklesIcon class="h-6 w-6 text-indigo-600" />
              </div>
              <div class="flex items-baseline mb-2">
                <span class="text-4xl font-bold bg-gradient-to-r from-indigo-600 to-indigo-400 bg-clip-text text-transparent">K1,000</span>
              </div>
              <p class="text-sm text-gray-500 mb-6">Monthly membership</p>
              <div class="border-t border-gray-100 pt-6">
                <ul class="space-y-4">
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-indigo-500" />
                    <span class="text-sm text-gray-600">VIP status & recognition</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-indigo-500" />
                    <span class="text-sm text-gray-600">Exclusive events access</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-indigo-500" />
                    <span class="text-sm text-gray-600">Maximum earning potential</span>
                  </li>
                  <li class="flex items-center space-x-3">
                    <CheckCircleIcon class="h-5 w-5 text-indigo-500" />
                    <span class="text-sm text-gray-600">Luxury asset rewards</span>
                  </li>
                </ul>
              </div>
            </div>
            <div class="p-6">
              <Link :href="route('register')"
                    class="block w-full bg-gradient-to-r from-indigo-500 to-indigo-600 text-white font-semibold rounded-xl py-3 text-center hover:from-indigo-600 hover:to-indigo-700 transition-all duration-300 shadow-sm">
                Get Started
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  import { Link } from '@inertiajs/vue3'
  import {
    BanknotesIcon,
    ChartBarIcon,
    CheckCircleIcon,
    UserGroupIcon,
    ShieldCheckIcon,
    StarIcon,
    SparklesIcon
  } from '@heroicons/vue/24/outline'

  export default {
    components: {
      Link,
      BanknotesIcon,
      ChartBarIcon,
      CheckCircleIcon,
      UserGroupIcon,
      ShieldCheckIcon,
      StarIcon,
      SparklesIcon
    }
  }
  </script>
