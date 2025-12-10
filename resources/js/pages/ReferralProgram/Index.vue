<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50">
    <Navigation />
    
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-purple-600 via-indigo-700 to-blue-800 py-20">
      <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>
      </div>
      
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center text-white">
          <div class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full mb-6">
            <span class="text-sm font-semibold">Community Growth Program</span>
          </div>
          <h1 class="text-5xl lg:text-6xl font-bold mb-6">
            Referral Program
          </h1>
          <p class="text-xl text-blue-100 max-w-3xl mx-auto leading-relaxed">
            Share MyGrowNet with others and earn rewards through our community growth program. 
            Build your network and unlock additional benefits as you help others discover our platform.
          </p>
        </div>
      </div>
    </section>

    <!-- Overview Section -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Our referral program rewards you for introducing others to MyGrowNet's products and services
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <!-- Step 1 -->
          <div class="bg-gradient-to-br from-blue-50 to-white rounded-2xl p-8 shadow-lg border border-blue-100">
            <div class="w-16 h-16 bg-blue-600 rounded-xl flex items-center justify-center mb-6 mx-auto">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">Share Your Link</h3>
            <p class="text-gray-600 text-center">
              Get your unique referral link from your dashboard and share it with friends, family, and colleagues
            </p>
          </div>

          <!-- Step 2 -->
          <div class="bg-gradient-to-br from-emerald-50 to-white rounded-2xl p-8 shadow-lg border border-emerald-100">
            <div class="w-16 h-16 bg-emerald-600 rounded-xl flex items-center justify-center mb-6 mx-auto">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">They Subscribe</h3>
            <p class="text-gray-600 text-center">
              When someone joins using your link and subscribes to MyGrowNet products or services
            </p>
          </div>

          <!-- Step 3 -->
          <div class="bg-gradient-to-br from-purple-50 to-white rounded-2xl p-8 shadow-lg border border-purple-100">
            <div class="w-16 h-16 bg-purple-600 rounded-xl flex items-center justify-center mb-6 mx-auto">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center">Earn Rewards</h3>
            <p class="text-gray-600 text-center">
              Receive referral rewards and bonuses based on their subscription and activity on the platform
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- 7-Level Network Structure -->
    <section class="py-16 bg-gradient-to-br from-gray-50 to-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">7-Level Community Network</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Our structured community model uses a 3×3 forced matrix system across 7 professional levels
          </p>
        </div>

        <!-- Professional Levels Component -->
        <ProfessionalLevels />

        <!-- Network Explanation -->
        <div class="mt-12 bg-white rounded-2xl p-8 shadow-lg border border-gray-200">
          <h3 class="text-2xl font-bold text-gray-900 mb-6">Understanding the Network Structure</h3>
          
          <div class="space-y-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="text-lg font-semibold text-gray-900 mb-2">3×3 Forced Matrix</h4>
                <p class="text-gray-600">
                  Each member can directly refer up to 3 people. Additional referrals automatically "spill over" to help build your team's network, creating a collaborative growth environment.
                </p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="text-lg font-semibold text-gray-900 mb-2">Level Advancement</h4>
                <p class="text-gray-600">
                  Progress through 7 professional levels (Associate → Ambassador) based on your network size, activity, and accumulated Lifetime Points. Higher levels unlock greater benefits and rewards.
                </p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="text-lg font-semibold text-gray-900 mb-2">Referral Rewards</h4>
                <p class="text-gray-600">
                  Earn rewards when your referred members subscribe to products, purchase from the marketplace, or participate in platform activities. Rewards are based on actual product sales and company revenue.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- MyGrowNet Stats -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <MyGrowNetStats />
      </div>
    </section>

    <!-- Reward System Overview -->
    <section class="py-16 bg-gradient-to-br from-blue-50 to-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">Reward System</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Multiple ways to earn rewards through community building and platform engagement
          </p>
        </div>

        <RewardSystem />
      </div>
    </section>

    <!-- Important Disclaimers -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-amber-50 border-l-4 border-amber-500 rounded-lg p-6">
          <div class="flex items-start gap-4">
            <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
              <h3 class="text-lg font-semibold text-amber-900 mb-2">Important Information</h3>
              <ul class="space-y-2 text-amber-800">
                <li class="flex items-start gap-2">
                  <span class="text-amber-600 mt-1">•</span>
                  <span>MyGrowNet is a subscription-based platform, not an investment scheme</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-amber-600 mt-1">•</span>
                  <span>Rewards are based on actual product sales and company revenue, not guaranteed returns</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-amber-600 mt-1">•</span>
                  <span>Members must maintain active subscriptions and meet monthly activity requirements to qualify for rewards</span>
                </li>
                <li class="flex items-start gap-2">
                  <span class="text-amber-600 mt-1">•</span>
                  <span>Network building is optional - you can benefit from MyGrowNet products without participating in referrals</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold text-white mb-6">Ready to Get Started?</h2>
        <p class="text-xl text-blue-100 mb-8">
          Join MyGrowNet today and start building your community while accessing valuable business tools and training
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Link
            :href="route('register')"
            class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 font-semibold rounded-xl hover:bg-blue-50 transition-colors shadow-lg"
          >
            Join Now
          </Link>
          <Link
            :href="route('about')"
            class="inline-flex items-center justify-center px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-semibold rounded-xl hover:bg-white/20 transition-colors border border-white/20"
          >
            Learn More
          </Link>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';
import ProfessionalLevels from '@/components/custom/ProfessionalLevels.vue';
import MyGrowNetStats from '@/components/custom/MyGrowNetStats.vue';
import RewardSystem from '@/components/custom/RewardSystem.vue';
</script>
