<template>
  <div class="min-h-full w-full bg-gradient-to-br from-amber-500 via-orange-500 to-red-600 flex flex-col p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -mr-36 -mt-36"></div>
    <div class="absolute bottom-0 left-0 w-56 h-56 bg-white/5 rounded-full -ml-28 -mb-28"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-6">
        <span class="text-white text-sm font-semibold">🏆 Performance Bonus</span>
      </div>
      
      <h2 class="text-3xl md:text-4xl font-bold mb-3">
        Earn Up to 30% Extra!
      </h2>
      <p class="text-amber-100 mb-8">
        Monthly performance tiers reward your activity
      </p>
      
      <!-- Performance tiers -->
      <div class="space-y-3 mb-6">
        <!-- Bronze -->
        <div class="flex items-center gap-4 bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
          <div class="w-14 h-14 bg-gradient-to-br from-amber-600 to-amber-800 rounded-xl flex items-center justify-center flex-shrink-0">
            <span class="text-2xl">🥉</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-bold">Bronze Tier</div>
            <div class="text-sm text-amber-200">500+ Monthly Activity Points</div>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold text-green-400">+10%</div>
            <div class="text-xs text-amber-200">bonus</div>
          </div>
        </div>
        
        <!-- Silver -->
        <div class="flex items-center gap-4 bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
          <div class="w-14 h-14 bg-gradient-to-br from-gray-300 to-gray-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <span class="text-2xl">🥈</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-bold">Silver Tier</div>
            <div class="text-sm text-amber-200">1,000+ Monthly Activity Points</div>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold text-green-400">+15%</div>
            <div class="text-xs text-amber-200">bonus</div>
          </div>
        </div>
        
        <!-- Gold -->
        <div class="flex items-center gap-4 bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
          <div class="w-14 h-14 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl flex items-center justify-center flex-shrink-0">
            <span class="text-2xl">🥇</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-bold">Gold Tier</div>
            <div class="text-sm text-amber-200">2,000+ Monthly Activity Points</div>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold text-green-400">+20%</div>
            <div class="text-xs text-amber-200">bonus</div>
          </div>
        </div>
        
        <!-- Platinum -->
        <div class="flex items-center gap-4 bg-gradient-to-r from-purple-500/30 to-pink-500/30 backdrop-blur-sm rounded-xl p-4 border-2 border-purple-400/50">
          <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-pink-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <span class="text-2xl">💎</span>
          </div>
          <div class="flex-1 text-left">
            <div class="font-bold">Platinum Tier</div>
            <div class="text-sm text-purple-200">3,500+ Monthly Activity Points</div>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold text-green-400">+30%</div>
            <div class="text-xs text-purple-200">bonus</div>
          </div>
        </div>
      </div>
      
      <!-- How to earn points -->
      <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
        <h3 class="font-bold mb-3 text-sm">How to Earn Activity Points:</h3>
        <div class="grid grid-cols-2 gap-2 text-xs">
          <div class="flex items-center gap-2">
            <span class="text-green-400">✓</span>
            <span>Referrals</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-green-400">✓</span>
            <span>Purchases</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-green-400">✓</span>
            <span>Training completion</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-green-400">✓</span>
            <span>Team activity</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
