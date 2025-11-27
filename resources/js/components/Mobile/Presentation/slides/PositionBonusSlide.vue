<template>
  <div class="h-full w-full bg-gradient-to-br from-cyan-600 via-blue-600 to-indigo-700 flex flex-col items-center justify-center p-6 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -ml-32 -mt-32"></div>
    <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/5 rounded-full -mr-24 -mb-24"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-4">
        <span class="text-white text-sm font-semibold">🎯 Position Bonus (Milestone Rewards)</span>
      </div>
      
      <h2 class="text-2xl md:text-3xl font-bold mb-2">
        Level Up, Get Rewarded!
      </h2>
      <p class="text-cyan-200 mb-4 text-sm">
        Automatic advancement based on network growth
      </p>
      
      <!-- All 7 Level rewards -->
      <div class="space-y-2 overflow-y-auto max-h-[55vh]">
        <!-- Associate (L1) -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
          <div class="w-9 h-9 bg-gray-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L1</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm">Associate (3 referrals)</div>
            <div class="text-xs text-cyan-200">Welcome kit + Learning materials</div>
          </div>
          <div class="text-right">
            <div class="text-xs font-bold text-cyan-300">📚 Foundation</div>
          </div>
        </div>

        <!-- Professional (L2) -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
          <div class="w-9 h-9 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L2</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm">Professional (9 network)</div>
            <div class="text-xs text-cyan-200">Recognition + Bonus modules</div>
          </div>
          <div class="text-right">
            <div class="text-xs font-bold text-cyan-300">🏅 Certificate</div>
          </div>
        </div>

        <!-- Senior (L3) -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
          <div class="w-9 h-9 bg-emerald-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L3</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm">Senior (27 network)</div>
            <div class="text-xs text-cyan-200">Physical rewards + Mentor badge</div>
          </div>
          <div class="text-right">
            <div class="text-xs font-bold text-amber-300">📱 Smartphone</div>
          </div>
        </div>
        
        <!-- Manager (L4) -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
          <div class="w-9 h-9 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L4</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm">Manager (81 network)</div>
            <div class="text-xs text-cyan-200">Motorbike/Equipment + Leadership training</div>
          </div>
          <div class="text-right">
            <div class="text-sm font-bold text-green-400">K5,000</div>
            <div class="text-xs text-cyan-200">Booster Fund</div>
          </div>
        </div>
        
        <!-- Director (L5) -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
          <div class="w-9 h-9 bg-indigo-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L5</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm">Director (243 network)</div>
            <div class="text-xs text-cyan-200">Vehicle/Property deposit + Regional recognition</div>
          </div>
          <div class="text-right">
            <div class="text-sm font-bold text-green-400">K10,000</div>
            <div class="text-xs text-cyan-200">Booster Fund</div>
          </div>
        </div>
        
        <!-- Executive (L6) -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
          <div class="w-9 h-9 bg-pink-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L6</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm">Executive (729 network)</div>
            <div class="text-xs text-cyan-200">Car/Property + Travel incentives</div>
          </div>
          <div class="text-right">
            <div class="text-sm font-bold text-green-400">K25,000</div>
            <div class="text-xs text-cyan-200">Booster Fund</div>
          </div>
        </div>
        
        <!-- Ambassador (L7) -->
        <div class="flex items-center gap-3 bg-gradient-to-r from-amber-500/30 to-orange-500/30 backdrop-blur-sm rounded-xl p-2.5 border-2 border-amber-400/50">
          <div class="w-9 h-9 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L7</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm text-amber-200">Ambassador (2,187 network)</div>
            <div class="text-xs text-amber-200">Luxury vehicle/Property + Advisory board</div>
          </div>
          <div class="text-right">
            <div class="text-sm font-bold text-amber-300">K50,000+</div>
            <div class="text-xs text-amber-200">+ Int'l Travel!</div>
          </div>
        </div>
      </div>
      
      <!-- Profit share multiplier -->
      <div class="mt-4 bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20">
        <h3 class="font-bold text-xs mb-2">Quarterly Profit Share Multiplier</h3>
        <div class="flex items-center justify-center gap-1.5 flex-wrap text-xs">
          <span class="px-2 py-1 bg-gray-500/30 rounded">L1: 1.0x</span>
          <span class="px-2 py-1 bg-blue-500/30 rounded">L2: 1.2x</span>
          <span class="px-2 py-1 bg-emerald-500/30 rounded">L3: 1.5x</span>
          <span class="px-2 py-1 bg-purple-500/30 rounded">L4: 2.0x</span>
          <span class="px-2 py-1 bg-indigo-500/30 rounded">L5: 2.5x</span>
          <span class="px-2 py-1 bg-pink-500/30 rounded">L6: 3.0x</span>
          <span class="px-2 py-1 bg-amber-500/30 rounded font-bold">L7: 4.0x</span>
        </div>
      </div>

      <!-- Key note -->
      <p class="mt-3 text-xs text-cyan-200">
        <span class="mr-1">✨</span>
        Levels are permanent achievements. Progress is automatic based on network size!
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
