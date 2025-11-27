<template>
  <div class="min-h-full w-full bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 flex flex-col p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -ml-32 -mt-32"></div>
    <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/5 rounded-full -mr-24 -mb-24"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-4">
        <span class="text-white text-sm font-semibold">📊 7-Level Commissions</span>
      </div>
      
      <h2 class="text-2xl md:text-3xl font-bold mb-2">
        Earn From 7 Levels Deep
      </h2>
      <p class="text-purple-200 mb-6 text-sm">
        Build once, earn forever from your entire network
      </p>
      
      <!-- Commission table -->
      <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20 mb-4">
        <div class="space-y-2">
          <!-- Level 1 -->
          <div class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-500/30 to-blue-600/30 rounded-xl">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center font-bold text-sm">1</div>
              <div class="text-left">
                <div class="font-semibold text-sm">Associate</div>
                <div class="text-xs text-purple-200">3 positions</div>
              </div>
            </div>
            <div class="text-right">
              <div class="font-bold text-green-400">15%</div>
              <div class="text-xs text-purple-200">K75/referral</div>
            </div>
          </div>
          
          <!-- Level 2 -->
          <div class="flex items-center justify-between p-3 bg-gradient-to-r from-indigo-500/30 to-indigo-600/30 rounded-xl">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center font-bold text-sm">2</div>
              <div class="text-left">
                <div class="font-semibold text-sm">Professional</div>
                <div class="text-xs text-purple-200">9 positions</div>
              </div>
            </div>
            <div class="text-right">
              <div class="font-bold text-green-400">10%</div>
              <div class="text-xs text-purple-200">K50/referral</div>
            </div>
          </div>
          
          <!-- Level 3 -->
          <div class="flex items-center justify-between p-3 bg-gradient-to-r from-purple-500/30 to-purple-600/30 rounded-xl">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center font-bold text-sm">3</div>
              <div class="text-left">
                <div class="font-semibold text-sm">Senior</div>
                <div class="text-xs text-purple-200">27 positions</div>
              </div>
            </div>
            <div class="text-right">
              <div class="font-bold text-green-400">8%</div>
              <div class="text-xs text-purple-200">K40/referral</div>
            </div>
          </div>
          
          <!-- Levels 4-7 compact -->
          <div class="grid grid-cols-2 gap-2 pt-2">
            <div class="flex items-center justify-between p-2 bg-white/10 rounded-lg">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-pink-500 rounded-full flex items-center justify-center font-bold text-xs">4</div>
                <span class="text-xs">Manager</span>
              </div>
              <span class="text-green-400 font-bold text-sm">6%</span>
            </div>
            <div class="flex items-center justify-between p-2 bg-white/10 rounded-lg">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-rose-500 rounded-full flex items-center justify-center font-bold text-xs">5</div>
                <span class="text-xs">Director</span>
              </div>
              <span class="text-green-400 font-bold text-sm">4%</span>
            </div>
            <div class="flex items-center justify-between p-2 bg-white/10 rounded-lg">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center font-bold text-xs">6</div>
                <span class="text-xs">Executive</span>
              </div>
              <span class="text-green-400 font-bold text-sm">3%</span>
            </div>
            <div class="flex items-center justify-between p-2 bg-white/10 rounded-lg">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center font-bold text-xs">7</div>
                <span class="text-xs">Ambassador</span>
              </div>
              <span class="text-green-400 font-bold text-sm">2%</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Total potential -->
      <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-sm rounded-xl p-4 border border-green-400/30">
        <div class="text-sm text-green-200 mb-1">Full Network Potential (3,279 members)</div>
        <div class="text-3xl font-bold text-green-400">K16,740+</div>
        <div class="text-xs text-green-200">per month from commissions alone!</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
