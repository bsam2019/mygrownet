<template>
  <div class="h-full w-full bg-gradient-to-br from-cyan-600 via-blue-600 to-indigo-700 flex flex-col items-center justify-center p-6 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -ml-32 -mt-32"></div>
    <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/5 rounded-full -mr-24 -mb-24"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-3">
        <span class="text-white text-sm font-semibold">🎯 Position Milestone &amp; Physical Rewards</span>
      </div>
      
      <h2 class="text-2xl md:text-3xl font-bold mb-1">
        Level Up &amp; Unlock Physical Rewards!
      </h2>
      <p class="text-cyan-200 mb-3 text-xs">
        Progression driven by Life Points (LP) &amp; Education Level completion
      </p>
      
      <!-- All 7 Level rewards -->
      <div class="space-y-2 overflow-y-auto max-h-[55vh]">
        <!-- Associate (L1) -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
          <div class="w-9 h-9 bg-gray-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L1</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm">Associate (0 LP)</div>
            <div class="text-xs text-cyan-200">MyGrowNet Starter Kit (Branded Merch &amp; Manual)</div>
          </div>
          <div class="text-right">
            <div class="text-xs font-bold text-cyan-300">📦 Starter Kit</div>
          </div>
        </div>

        <!-- Professional (L2) -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
          <div class="w-9 h-9 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L2</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm">Professional (2,500 LP)</div>
            <div class="text-xs text-cyan-200">Branded Electronics Kit (Power Bank &amp; Earbuds)</div>
          </div>
          <div class="text-right">
            <div class="text-sm font-bold text-green-400">K500 Cash</div>
            <div class="text-xs text-cyan-200">+ Tech Kit</div>
          </div>
        </div>

        <!-- Senior (L3) -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
          <div class="w-9 h-9 bg-emerald-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L3</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm">Senior (4,000 LP)</div>
            <div class="text-xs text-cyan-200">Smartphone / Tablet Package (Samsung / iPad)</div>
          </div>
          <div class="text-right">
            <div class="text-sm font-bold text-green-400">K1,500 Cash</div>
            <div class="text-xs text-amber-300">📱 Smartphone</div>
          </div>
        </div>
        
        <!-- Manager (L4) -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
          <div class="w-9 h-9 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L4</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm">Manager (12,500 LP)</div>
            <div class="text-xs text-cyan-200">125cc Honda Motorbike OR Office Equipment Kit</div>
          </div>
          <div class="text-right">
            <div class="text-sm font-bold text-green-400">K5,000 Cash</div>
            <div class="text-xs text-cyan-200">🛵 Motorbike</div>
          </div>
        </div>
        
        <!-- Director (L5) -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
          <div class="w-9 h-9 bg-indigo-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L5</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm">Director (60,000 LP)</div>
            <div class="text-xs text-cyan-200">Toyota Vehicle Package OR Property Down Payment</div>
          </div>
          <div class="text-right">
            <div class="text-sm font-bold text-green-400">K15,000 Cash</div>
            <div class="text-xs text-cyan-200">🚗 Vehicle</div>
          </div>
        </div>
        
        <!-- Executive (L6) -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
          <div class="w-9 h-9 bg-pink-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L6</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm">Executive (160,000 LP)</div>
            <div class="text-xs text-cyan-200">Executive Luxury Vehicle (Toyota Camry / BMW 3)</div>
          </div>
          <div class="text-right">
            <div class="text-sm font-bold text-green-400">K50,000 Cash</div>
            <div class="text-xs text-cyan-200">🚘 Luxury Car</div>
          </div>
        </div>
        
        <!-- Ambassador (L7) -->
        <div class="flex items-center gap-3 bg-gradient-to-r from-amber-500/30 to-orange-500/30 backdrop-blur-sm rounded-xl p-2.5 border-2 border-amber-400/50">
          <div class="w-9 h-9 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="font-bold text-xs">L7</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-semibold text-sm text-amber-200">Ambassador (350,000 LP)</div>
            <div class="text-xs text-amber-200">Multi-unit Residential or Commercial Property</div>
          </div>
          <div class="text-right">
            <div class="text-sm font-bold text-amber-300">K150,000 Cash</div>
            <div class="text-xs text-amber-200">🏠 Property</div>
          </div>
        </div>
      </div>
      
      <!-- Profit share multiplier -->
      <div class="mt-3 bg-white/10 backdrop-blur-sm rounded-xl p-2.5 border border-white/20">
        <h3 class="font-bold text-xs mb-1.5">Quarterly Profit Share Multiplier</h3>
        <div class="flex items-center justify-center gap-1.5 flex-wrap text-xs">
          <span class="px-2 py-0.5 bg-gray-500/30 rounded">L1: 1.0x</span>
          <span class="px-2 py-0.5 bg-blue-500/30 rounded">L2: 1.2x</span>
          <span class="px-2 py-0.5 bg-emerald-500/30 rounded">L3: 1.5x</span>
          <span class="px-2 py-0.5 bg-purple-500/30 rounded">L4: 2.0x</span>
          <span class="px-2 py-0.5 bg-indigo-500/30 rounded">L5: 2.5x</span>
          <span class="px-2 py-0.5 bg-pink-500/30 rounded">L6: 3.0x</span>
          <span class="px-2 py-0.5 bg-amber-500/30 rounded font-bold text-amber-300">L7: 4.0x</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
