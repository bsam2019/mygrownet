<template>
  <div class="min-h-full w-full bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-700 flex flex-col p-4 py-6 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -mr-36 -mt-36"></div>
    <div class="absolute bottom-0 left-0 w-56 h-56 bg-white/5 rounded-full -ml-28 -mb-28"></div>
    
    <div class="relative z-10 max-w-4xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-4">
        <span class="text-white text-sm font-semibold">🎁 Starter Kits</span>
      </div>
      
      <h2 class="text-2xl md:text-3xl font-bold mb-2">
        Choose Your Starting Point
      </h2>
      <p class="text-purple-200 text-sm mb-4">
        Everything you need to begin your journey
      </p>
      
      <!-- Starter Kit Cards - 4 Tiers -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <!-- Lite Kit -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20 text-left">
          <div class="mb-2">
            <span class="text-sm font-bold">Lite</span>
          </div>
          
          <div class="mb-2">
            <span class="text-2xl font-bold">K300</span>
          </div>
          
          <ul class="space-y-1 text-xs mb-3">
            <li class="flex items-center gap-1">
              <span class="text-green-400">✓</span>
              <span>Basic Content</span>
            </li>
            <li class="flex items-center gap-1">
              <span class="text-green-400">✓</span>
              <span>15 Lifetime Points</span>
            </li>
            <li class="flex items-center gap-1">
              <span class="text-green-400">✓</span>
              <span>Community Access</span>
            </li>
          </ul>
          
          <div class="text-xs text-purple-200">
            Entry level
          </div>
        </div>

        <!-- Basic Kit -->
        <div class="bg-blue-500/20 backdrop-blur-sm rounded-xl p-3 border border-blue-400/30 text-left">
          <div class="mb-2">
            <span class="text-sm font-bold">Basic</span>
          </div>
          
          <div class="mb-2">
            <span class="text-2xl font-bold">K500</span>
          </div>
          
          <ul class="space-y-1 text-xs mb-3">
            <li class="flex items-center gap-1">
              <span class="text-blue-400">✓</span>
              <span>All Content</span>
            </li>
            <li class="flex items-center gap-1">
              <span class="text-blue-400">✓</span>
              <span>25 Lifetime Points</span>
            </li>
            <li class="flex items-center gap-1">
              <span class="text-blue-400">✓</span>
              <span>Full Platform</span>
            </li>
          </ul>
          
          <div class="text-xs text-blue-200">
            Great start
          </div>
        </div>
        
        <!-- Growth Plus Kit -->
        <div class="bg-gradient-to-br from-emerald-500/30 to-green-500/30 backdrop-blur-sm rounded-xl p-3 border border-emerald-400/50 text-left relative">
          <div class="absolute -top-2 left-1/2 -translate-x-1/2 px-2 py-0.5 bg-emerald-500 rounded-full text-[10px] font-bold text-white">
            POPULAR
          </div>
          
          <div class="mb-2 mt-1">
            <span class="text-sm font-bold">Growth Plus</span>
          </div>
          
          <div class="mb-2">
            <span class="text-2xl font-bold">K1,000</span>
          </div>
          
          <ul class="space-y-1 text-xs mb-3">
            <li class="flex items-center gap-1">
              <span class="text-emerald-400">✓</span>
              <span>All + Extras</span>
            </li>
            <li class="flex items-center gap-1">
              <span class="text-emerald-400">✓</span>
              <span>50 Lifetime Points</span>
            </li>
            <li class="flex items-center gap-1">
              <span class="text-emerald-400">⭐</span>
              <span class="text-emerald-200">Priority Support</span>
            </li>
          </ul>
          
          <div class="text-xs text-emerald-200">
            Best choice
          </div>
        </div>

        <!-- Pro Kit -->
        <div class="bg-gradient-to-br from-amber-500/30 to-orange-500/30 backdrop-blur-sm rounded-xl p-3 border-2 border-amber-400/50 text-left relative">
          <div class="absolute -top-2 left-1/2 -translate-x-1/2 px-2 py-0.5 bg-gradient-to-r from-amber-400 to-orange-400 rounded-full text-[10px] font-bold text-black">
            BEST VALUE
          </div>
          
          <div class="mb-2 mt-1">
            <span class="text-sm font-bold">Pro</span>
          </div>
          
          <div class="mb-2">
            <span class="text-2xl font-bold">K2,000</span>
          </div>
          
          <ul class="space-y-1 text-xs mb-3">
            <li class="flex items-center gap-1">
              <span class="text-amber-400">✓</span>
              <span>Full Library</span>
            </li>
            <li class="flex items-center gap-1">
              <span class="text-amber-400">✓</span>
              <span>100 Lifetime Points</span>
            </li>
            <li class="flex items-center gap-1">
              <span class="text-amber-400">⭐</span>
              <span class="text-amber-200">Premium Benefits</span>
            </li>
          </ul>
          
          <div class="text-xs text-amber-200">
            Maximum value
          </div>
        </div>
      </div>
      
      <!-- Value highlight -->
      <div class="mt-4 p-3 bg-white/10 backdrop-blur-sm rounded-xl">
        <p class="text-purple-100 text-sm">
          <span class="text-lg mr-1">💡</span>
          All kits include <span class="font-bold text-white">7-level commission eligibility</span> and 
          <span class="font-bold text-white">progressive content unlocks</span>!
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
