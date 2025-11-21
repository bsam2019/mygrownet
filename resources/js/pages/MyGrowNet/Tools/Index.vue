<template>
  <MemberLayout>
    <div class="max-w-7xl mx-auto p-4 sm:p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">MyGrowNet Tools</h1>
        <p class="mt-2 text-gray-600">Powerful tools to grow your business</p>
      </div>

      <!-- Tools Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Commission Calculator -->
        <Link
          :href="route('mygrownet.tools.commission-calculator')"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all"
        >
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Commission Calculator</h3>
          <p class="text-sm text-gray-600">Calculate potential earnings from your network</p>
          <div class="mt-4 text-sm text-green-600 font-medium">Open Tool →</div>
        </Link>

        <!-- Goal Tracker -->
        <Link
          :href="route('mygrownet.tools.goal-tracker')"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all"
        >
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Goal Tracker</h3>
          <p class="text-sm text-gray-600">Set and track your income and team goals</p>
          <div class="mt-4 text-sm text-blue-600 font-medium">Open Tool →</div>
        </Link>

        <!-- Network Visualizer -->
        <Link
          :href="route('mygrownet.tools.network-visualizer')"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all"
        >
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Network Visualizer</h3>
          <p class="text-sm text-gray-600">Visualize your 7-level network structure</p>
          <div class="mt-4 text-sm text-purple-600 font-medium">Open Tool →</div>
        </Link>

        <!-- Business Plan Generator (Premium) -->
        <Link
          :href="route('mygrownet.tools.business-plan-generator')"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all relative"
          :class="{ 'opacity-60': userTier !== 'premium' }"
        >
          <div v-if="userTier !== 'premium'" class="absolute top-3 right-3">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
              Premium
            </span>
          </div>
          <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Business Plan Generator</h3>
          <p class="text-sm text-gray-600">Create a comprehensive business plan</p>
          <div class="mt-4 text-sm text-indigo-600 font-medium">
            {{ userTier === 'premium' ? 'Open Tool →' : 'Upgrade to Premium' }}
          </div>
        </Link>

        <!-- ROI Calculator (Premium) -->
        <Link
          :href="route('mygrownet.tools.roi-calculator')"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all relative"
          :class="{ 'opacity-60': userTier !== 'premium' }"
        >
          <div v-if="userTier !== 'premium'" class="absolute top-3 right-3">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">
              Premium
            </span>
          </div>
          <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="h-6 w-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">ROI Calculator</h3>
          <p class="text-sm text-gray-600">Calculate return on investment projections</p>
          <div class="mt-4 text-sm text-teal-600 font-medium">
            {{ userTier === 'premium' ? 'Open Tool →' : 'Upgrade to Premium' }}
          </div>
        </Link>
      </div>

      <!-- Premium Upgrade CTA -->
      <div v-if="userTier !== 'premium'" class="mt-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h3 class="text-xl font-bold mb-2">Unlock Premium Tools</h3>
            <p class="text-indigo-100 mb-4">
              Upgrade to Premium Starter Kit and get access to Business Plan Generator, ROI Calculator, and more exclusive tools!
            </p>
            <ul class="space-y-2 text-sm text-indigo-100 mb-4">
              <li class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Business Plan Generator
              </li>
              <li class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                ROI Calculator
              </li>
              <li class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                3 Exclusive E-books
              </li>
              <li class="flex items-center">
                <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                10 Premium Videos
              </li>
            </ul>
            <Link
              :href="route('mygrownet.starter-kit.purchase')"
              class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-indigo-50 transition-colors"
            >
              Upgrade to Premium
              <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

interface Props {
  userTier: string;
}

defineProps<Props>();
</script>
