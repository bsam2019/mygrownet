<template>
  <GuestLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
          <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Policies & Legal Documents
          </h1>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Review our policies, terms, and legal documents to understand how MyGrowNet operates 
            and your rights as a member.
          </p>
        </div>

        <!-- Policy Categories -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Platform Policies -->
          <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <h2 class="ml-3 text-xl font-semibold text-gray-900">Platform Policies</h2>
            </div>
            <ul class="space-y-3">
              <li>
                <Link :href="route('privacy')" class="flex items-center text-gray-700 hover:text-blue-600 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Privacy Policy
                </Link>
              </li>
              <li>
                <Link :href="route('terms')" class="flex items-center text-gray-700 hover:text-blue-600 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Terms of Service
                </Link>
              </li>
              <li>
                <Link v-if="route().has('compliance.information')" :href="route('compliance.information')" class="flex items-center text-gray-700 hover:text-blue-600 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Compliance Information
                </Link>
                <a v-else href="#" class="flex items-center text-gray-400 cursor-not-allowed">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Compliance Information (Coming Soon)
                </a>
              </li>
            </ul>
          </div>

          <!-- Venture Builder Policies -->
          <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <h2 class="ml-3 text-xl font-semibold text-gray-900">Venture Builder</h2>
            </div>
            <ul class="space-y-3">
              <li>
                <Link :href="route('ventures.policy')" class="flex items-center text-gray-700 hover:text-blue-600 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Investment Policy
                </Link>
              </li>
              <li>
                <Link :href="route('ventures.about')" class="flex items-center text-gray-700 hover:text-blue-600 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  About Venture Builder
                </Link>
              </li>
            </ul>
          </div>

          <!-- BGF Policies -->
          <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h2 class="ml-3 text-xl font-semibold text-gray-900">Business Growth Fund</h2>
            </div>
            <ul class="space-y-3">
              <li>
                <Link :href="route('bgf.policy')" class="flex items-center text-gray-700 hover:text-green-600 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Funding Policy
                </Link>
              </li>
              <li>
                <Link :href="route('bgf.terms')" class="flex items-center text-gray-700 hover:text-green-600 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Terms & Conditions
                </Link>
              </li>
              <li>
                <Link :href="route('bgf.how-it-works')" class="flex items-center text-gray-700 hover:text-green-600 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  How It Works
                </Link>
              </li>
              <li>
                <Link :href="route('bgf.about')" class="flex items-center text-gray-700 hover:text-green-600 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  About BGF
                </Link>
              </li>
            </ul>
          </div>

          <!-- Wallet Policies -->
          <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-purple-100 rounded-lg">
                <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
              </div>
              <h2 class="ml-3 text-xl font-semibold text-gray-900">MyGrow Wallet</h2>
            </div>
            <ul class="space-y-3">
              <li>
                <a href="#" class="flex items-center text-gray-400 cursor-not-allowed">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Wallet Policy (Coming Soon)
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center text-gray-400 cursor-not-allowed">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Terms of Use (Coming Soon)
                </a>
              </li>
            </ul>
          </div>

          <!-- Membership Policies -->
          <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-indigo-100 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <h2 class="ml-3 text-xl font-semibold text-gray-900">Membership</h2>
            </div>
            <ul class="space-y-3">
              <li>
                <Link :href="route('membership.index')" class="flex items-center text-gray-700 hover:text-indigo-600 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Membership Plans
                </Link>
              </li>
              <li>
                <a href="#" class="flex items-center text-gray-400 cursor-not-allowed">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Code of Conduct (Coming Soon)
                </a>
              </li>
            </ul>
          </div>

          <!-- Help & Support -->
          <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-yellow-100 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h2 class="ml-3 text-xl font-semibold text-gray-900">Help & Support</h2>
            </div>
            <ul class="space-y-3">
              <li>
                <Link :href="route('faq')" class="flex items-center text-gray-700 hover:text-yellow-600 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  FAQ
                </Link>
              </li>
              <li>
                <Link :href="route('contact')" class="flex items-center text-gray-700 hover:text-yellow-600 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Contact Us
                </Link>
              </li>
            </ul>
          </div>
        </div>

        <!-- Important Notice -->
        <div class="mt-12 bg-blue-50 border border-blue-200 rounded-lg p-6">
          <div class="flex items-start">
            <svg class="w-6 h-6 text-blue-600 mt-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-blue-900 mb-2">Important Notice</h3>
              <p class="text-blue-800 text-sm leading-relaxed">
                These policies are regularly updated to reflect changes in our services and legal requirements. 
                We recommend reviewing them periodically. By using MyGrowNet services, you agree to comply with 
                all applicable policies and terms. If you have questions about any policy, please 
                <Link :href="route('contact')" class="underline hover:text-blue-900">contact our support team</Link>.
              </p>
            </div>
          </div>
        </div>

        <!-- Last Updated -->
        <div class="mt-8 text-center text-gray-500 text-sm">
          <p>Policies last reviewed: October 31, 2025</p>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';
</script>
