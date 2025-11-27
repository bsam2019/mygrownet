<template>
  <div class="min-h-full w-full bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-700 flex flex-col p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -mr-36 -mt-36"></div>
    <div class="absolute bottom-0 left-0 w-56 h-56 bg-white/5 rounded-full -ml-28 -mb-28"></div>
    
    <div class="relative z-10 max-w-3xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-6">
        <span class="text-white text-sm font-semibold">🎁 Starter Kits</span>
      </div>
      
      <h2 class="text-3xl md:text-4xl font-bold mb-3">
        Choose Your Starting Point
      </h2>
      <p class="text-purple-200 mb-8">
        Everything you need to begin your journey
      </p>
      
      <!-- Starter Kit Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Basic Kit -->
        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20 text-left">
          <div class="flex items-center justify-between mb-4">
            <span class="text-lg font-bold">Basic Kit</span>
            <span class="px-3 py-1 bg-white/20 rounded-full text-sm">Popular</span>
          </div>
          
          <div class="mb-4">
            <span class="text-4xl font-bold">K500</span>
            <span class="text-purple-200 ml-2">one-time</span>
          </div>
          
          <ul class="space-y-3 mb-6">
            <li class="flex items-center gap-2">
              <span class="text-green-400">✓</span>
              <span>E-books & Training Videos</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-400">✓</span>
              <span>K100 Shop Credit (90 days)</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-400">✓</span>
              <span>25 Lifetime Points</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-400">✓</span>
              <span>Full Platform Access</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-green-400">✓</span>
              <span>7-Level Commission Eligibility</span>
            </li>
          </ul>
          
          <div class="text-sm text-purple-200">
            Perfect for getting started
          </div>
        </div>
        
        <!-- Premium Kit -->
        <div class="bg-gradient-to-br from-amber-500/30 to-orange-500/30 backdrop-blur-sm rounded-2xl p-6 border-2 border-amber-400/50 text-left relative">
          <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 bg-gradient-to-r from-amber-400 to-orange-400 rounded-full text-xs font-bold text-black">
            RECOMMENDED
          </div>
          
          <div class="flex items-center justify-between mb-4 mt-2">
            <span class="text-lg font-bold">Premium Kit</span>
            <span class="px-3 py-1 bg-amber-400/30 rounded-full text-sm text-amber-200">Best Value</span>
          </div>
          
          <div class="mb-4">
            <span class="text-4xl font-bold">K1,000</span>
            <span class="text-purple-200 ml-2">one-time</span>
          </div>
          
          <ul class="space-y-3 mb-6">
            <li class="flex items-center gap-2">
              <span class="text-amber-400">✓</span>
              <span>Everything in Basic</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-amber-400">✓</span>
              <span>K200 Shop Credit (90 days)</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-amber-400">✓</span>
              <span>50 Lifetime Points</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-amber-400">⭐</span>
              <span class="font-semibold text-amber-200">LGR Qualification</span>
            </li>
            <li class="flex items-center gap-2">
              <span class="text-amber-400">⭐</span>
              <span class="font-semibold text-amber-200">Quarterly Profit Sharing!</span>
            </li>
          </ul>
          
          <div class="text-sm text-amber-200">
            Unlock maximum earning potential
          </div>
        </div>
      </div>
      
      <!-- Value highlight -->
      <div class="mt-6 p-4 bg-white/10 backdrop-blur-sm rounded-xl">
        <p class="text-purple-100">
          <span class="text-xl mr-2">💡</span>
          Both kits include <span class="font-bold text-white">progressive content unlocks</span> over 30 days 
          to ensure you master each module!
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
