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
            <h1 class="text-4xl font-bold text-white leading-tight">Our Roadmap</h1>
            <p class="mt-4 text-lg text-white text-opacity-90 max-w-3xl">
              Discover what we're building and where we're headed. Our transparent roadmap shows our commitment to continuous improvement and growth.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Roadmap Timeline -->
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-8">
          <!-- Q1 -->
          <div class="bg-white rounded-lg shadow-lg p-8 md:p-12 border-l-4 border-green-500">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h2 class="text-2xl font-bold text-gray-800">Q1 2025</h2>
                <span class="inline-block mt-2 px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                  Completed
                </span>
              </div>
              <div class="text-green-600">
                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Foundation Launch</h3>
            <ul class="space-y-2 text-gray-600">
              <li class="flex items-start">
                <span class="text-green-600 mr-2">✓</span>
                <span><strong>Marketplace:</strong> Launch e-commerce platform with initial product catalog</span>
              </li>
              <li class="flex items-start">
                <span class="text-green-600 mr-2">✓</span>
                <span><strong>Membership System:</strong> Implement subscription tiers and member benefits</span>
              </li>
              <li class="flex items-start">
                <span class="text-green-600 mr-2">✓</span>
                <span><strong>Starter Kits:</strong> Create and launch initial starter kit offerings</span>
              </li>
            </ul>
          </div>

          <!-- Q2 -->
          <div class="bg-white rounded-lg shadow-lg p-8 md:p-12 border-l-4 border-blue-500">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h2 class="text-2xl font-bold text-gray-800">Q2 2025</h2>
                <span class="inline-block mt-2 px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                  In Progress
                </span>
              </div>
              <div class="text-blue-600">
                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Content & Community Growth</h3>
            <ul class="space-y-2 text-gray-600">
              <li class="flex items-start">
                <span class="text-blue-600 mr-2">→</span>
                <span><strong>Training Expansion:</strong> Add comprehensive business skills courses and workshops</span>
              </li>
              <li class="flex items-start">
                <span class="text-blue-600 mr-2">→</span>
                <span><strong>Vendor Onboarding:</strong> Open marketplace to third-party sellers (beta program)</span>
              </li>
              <li class="flex items-start">
                <span class="text-blue-600 mr-2">→</span>
                <span><strong>Enhanced Tools:</strong> Launch additional business management tools</span>
              </li>
            </ul>
          </div>

          <!-- Q3 -->
          <div class="bg-white rounded-lg shadow-lg p-8 md:p-12 border-l-4 border-purple-500">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h2 class="text-2xl font-bold text-gray-800">Q3 2025</h2>
                <span class="inline-block mt-2 px-3 py-1 bg-purple-100 text-purple-800 text-sm font-semibold rounded-full">
                  Planned
                </span>
              </div>
              <div class="text-purple-600">
                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Innovation & Investment</h3>
            <ul class="space-y-2 text-gray-600">
              <li class="flex items-start">
                <span class="text-purple-600 mr-2">○</span>
                <span><strong>Venture Builder Pilot:</strong> Launch co-investment opportunities in vetted businesses</span>
              </li>
              <li class="flex items-start">
                <span class="text-purple-600 mr-2">○</span>
                <span><strong>Advanced Analytics:</strong> Provide members with detailed business insights</span>
              </li>
              <li class="flex items-start">
                <span class="text-purple-600 mr-2">○</span>
                <span><strong>Community Projects:</strong> Introduce collaborative business initiatives</span>
              </li>
            </ul>
          </div>

          <!-- Q4 -->
          <div class="bg-white rounded-lg shadow-lg p-8 md:p-12 border-l-4 border-indigo-500">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h2 class="text-2xl font-bold text-gray-800">Q4 2025</h2>
                <span class="inline-block mt-2 px-3 py-1 bg-indigo-100 text-indigo-800 text-sm font-semibold rounded-full">
                  Planned
                </span>
              </div>
              <div class="text-indigo-600">
                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Scale & Mobile</h3>
            <ul class="space-y-2 text-gray-600">
              <li class="flex items-start">
                <span class="text-indigo-600 mr-2">○</span>
                <span><strong>Mobile App:</strong> Launch native iOS and Android applications</span>
              </li>
              <li class="flex items-start">
                <span class="text-indigo-600 mr-2">○</span>
                <span><strong>Scaled Products:</strong> Expand marketplace inventory and product categories</span>
              </li>
              <li class="flex items-start">
                <span class="text-indigo-600 mr-2">○</span>
                <span><strong>API Platform:</strong> Enable third-party integrations and extensions</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Disclaimer -->
    <div class="py-12 bg-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-amber-50 border-l-4 border-amber-500 rounded-lg p-6">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-semibold text-amber-800 mb-2">Important Note</h3>
              <p class="text-amber-700">
                This roadmap represents our current plans and aspirations. Features, timelines, and priorities are subject to change based on member feedback, market conditions, and business needs. We remain committed to transparency and will update this roadmap regularly.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Long-term Vision -->
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-8 md:p-12">
          <h2 class="text-3xl font-bold text-gray-800 mb-6">Beyond 2025</h2>
          <div class="space-y-4 text-gray-600">
            <p class="text-lg">
              Our long-term vision extends beyond the immediate roadmap. We're committed to building a comprehensive business ecosystem that includes:
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
              <div class="flex items-start">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-4">
                  <span class="text-xl">🌍</span>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-800 mb-1">Regional Expansion</h3>
                  <p class="text-sm text-gray-600">Extending our services across Southern Africa</p>
                </div>
              </div>
              <div class="flex items-start">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-4">
                  <span class="text-xl">🤝</span>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-800 mb-1">Strategic Partnerships</h3>
                  <p class="text-sm text-gray-600">Collaborating with established businesses and institutions</p>
                </div>
              </div>
              <div class="flex items-start">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-4">
                  <span class="text-xl">🎓</span>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-800 mb-1">Certification Programs</h3>
                  <p class="text-sm text-gray-600">Accredited business training and professional development</p>
                </div>
              </div>
              <div class="flex items-start">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-4">
                  <span class="text-xl">💼</span>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-800 mb-1">Enterprise Solutions</h3>
                  <p class="text-sm text-gray-600">Tailored tools for larger organizations</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stay Updated CTA -->
    <div class="py-12 bg-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-xl p-8 md:p-12 text-center">
          <h2 class="text-3xl font-bold text-white mb-4">Stay Updated</h2>
          <p class="text-lg text-white text-opacity-90 mb-6 max-w-2xl mx-auto">
            Want to be the first to know about new features and updates? Join MyGrowNet today and be part of our growing community.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a
              href="/starter-kits"
              class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 transition-colors"
            >
              Join Now
            </a>
            <a
              href="/about"
              class="inline-flex items-center justify-center px-6 py-3 border-2 border-white text-base font-medium rounded-md text-white hover:bg-white hover:text-blue-600 transition-colors"
            >
              Learn More
            </a>
          </div>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';
</script>
