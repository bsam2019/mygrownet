<template>
  <div class="min-h-full w-full bg-gradient-to-br from-teal-600 via-emerald-600 to-green-700 flex flex-col items-center justify-center p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -ml-32 -mt-32"></div>
    <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/5 rounded-full -mr-24 -mb-24"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-6">
        <span class="text-white text-sm font-semibold">🌍 Community Rewards</span>
      </div>
      
      <h2 class="text-3xl md:text-4xl font-bold mb-3">
        Earn from Company Success!
      </h2>
      <p class="text-emerald-200 mb-6">
        60% of project profits distributed to active members
      </p>
      
      <!-- How it works -->
      <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20 mb-6">
        <h3 class="font-bold text-lg mb-4">How Community Rewards Work</h3>
        
        <!-- Visual flow -->
        <div class="flex items-center justify-center gap-2 mb-6 flex-wrap">
          <div class="bg-blue-500/30 rounded-xl p-3 text-center">
            <div class="text-2xl mb-1">🏢</div>
            <div class="text-xs">MyGrowNet<br/>Investments</div>
          </div>
          <div class="text-emerald-300 text-xl">→</div>
          <div class="bg-green-500/30 rounded-xl p-3 text-center">
            <div class="text-2xl mb-1">💰</div>
            <div class="text-xs">Project<br/>Profits</div>
          </div>
          <div class="text-emerald-300 text-xl">→</div>
          <div class="bg-amber-500/30 rounded-xl p-3 text-center">
            <div class="text-2xl mb-1">👥</div>
            <div class="text-xs">Active<br/>Members</div>
          </div>
        </div>
        
        <!-- Distribution breakdown -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-green-500/20 rounded-xl p-4">
            <div class="text-3xl font-bold text-green-400">60%</div>
            <div class="text-sm text-emerald-200">To Members</div>
            <div class="text-xs text-emerald-300 mt-1">Quarterly distribution</div>
          </div>
          <div class="bg-blue-500/20 rounded-xl p-4">
            <div class="text-3xl font-bold text-blue-400">40%</div>
            <div class="text-sm text-emerald-200">Company</div>
            <div class="text-xs text-emerald-300 mt-1">Operations & growth</div>
          </div>
        </div>
      </div>
      
      <!-- Investment projects -->
      <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 mb-4">
        <h3 class="font-bold text-sm mb-3">Company Investment Projects</h3>
        <div class="grid grid-cols-3 gap-2 text-xs">
          <div class="flex flex-col items-center gap-1 p-2 bg-white/10 rounded-lg">
            <span class="text-xl">🌾</span>
            <span>Agriculture</span>
          </div>
          <div class="flex flex-col items-center gap-1 p-2 bg-white/10 rounded-lg">
            <span class="text-xl">🏭</span>
            <span>Manufacturing</span>
          </div>
          <div class="flex flex-col items-center gap-1 p-2 bg-white/10 rounded-lg">
            <span class="text-xl">🏠</span>
            <span>Real Estate</span>
          </div>
          <div class="flex flex-col items-center gap-1 p-2 bg-white/10 rounded-lg">
            <span class="text-xl">🚚</span>
            <span>Transport</span>
          </div>
          <div class="flex flex-col items-center gap-1 p-2 bg-white/10 rounded-lg">
            <span class="text-xl">💼</span>
            <span>Services</span>
          </div>
          <div class="flex flex-col items-center gap-1 p-2 bg-white/10 rounded-lg">
            <span class="text-xl">💻</span>
            <span>Technology</span>
          </div>
        </div>
      </div>
      
      <!-- Key point -->
      <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 rounded-xl p-4 border border-green-400/30">
        <p class="text-sm">
          <span class="text-xl mr-2">💡</span>
          <span class="font-bold">No direct investment required!</span> MyGrowNet invests, 
          you benefit from the success through your membership.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
