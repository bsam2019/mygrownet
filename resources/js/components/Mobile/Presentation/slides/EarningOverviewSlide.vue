<template>
  <div class="min-h-full w-full bg-gradient-to-br from-green-600 via-emerald-600 to-teal-700 flex flex-col items-center justify-center p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -ml-32 -mt-32"></div>
    <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/5 rounded-full -mr-24 -mb-24"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-6">
        <span class="text-white text-sm font-semibold">💰 Earning Opportunities</span>
      </div>
      
      <h2 class="text-3xl md:text-4xl font-bold mb-3">
        Multiple Income Streams
      </h2>
      <p class="text-emerald-200 mb-8">
        6 ways to earn with MyGrowNet
      </p>
      
      <!-- Income streams grid -->
      <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <!-- Referral Bonus -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 hover:bg-white/20 transition-colors">
          <div class="text-3xl mb-2">👥</div>
          <h3 class="font-bold text-sm mb-1">Referral Bonus</h3>
          <p class="text-xs text-emerald-200">15% on direct referrals</p>
        </div>
        
        <!-- Level Commissions -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 hover:bg-white/20 transition-colors">
          <div class="text-3xl mb-2">📊</div>
          <h3 class="font-bold text-sm mb-1">7-Level Commissions</h3>
          <p class="text-xs text-emerald-200">Earn from 7 levels deep</p>
        </div>
        
        <!-- Performance Bonus -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 hover:bg-white/20 transition-colors">
          <div class="text-3xl mb-2">🏆</div>
          <h3 class="font-bold text-sm mb-1">Performance Bonus</h3>
          <p class="text-xs text-emerald-200">Up to +30% monthly</p>
        </div>
        
        <!-- Position Bonus -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 hover:bg-white/20 transition-colors">
          <div class="text-3xl mb-2">🎯</div>
          <h3 class="font-bold text-sm mb-1">Position Bonus</h3>
          <p class="text-xs text-emerald-200">Level advancement rewards</p>
        </div>
        
        <!-- LGR -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 hover:bg-white/20 transition-colors">
          <div class="text-3xl mb-2">🎁</div>
          <h3 class="font-bold text-sm mb-1">LGR Rewards</h3>
          <p class="text-xs text-emerald-200">Loyalty gift program</p>
        </div>
        
        <!-- Community Rewards -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 hover:bg-white/20 transition-colors">
          <div class="text-3xl mb-2">🌍</div>
          <h3 class="font-bold text-sm mb-1">Community Rewards</h3>
          <p class="text-xs text-emerald-200">60% of company profits</p>
        </div>
      </div>
      
      <!-- Earning potential highlight -->
      <div class="mt-8 bg-white/15 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
        <h3 class="text-lg font-bold mb-3">Potential Monthly Earnings</h3>
        <div class="flex items-center justify-center gap-4 flex-wrap">
          <div class="text-center">
            <div class="text-2xl font-bold text-amber-300">K500+</div>
            <div class="text-xs text-emerald-200">Associate</div>
          </div>
          <div class="text-emerald-300">→</div>
          <div class="text-center">
            <div class="text-2xl font-bold text-amber-300">K5,000+</div>
            <div class="text-xs text-emerald-200">Manager</div>
          </div>
          <div class="text-emerald-300">→</div>
          <div class="text-center">
            <div class="text-2xl font-bold text-amber-300">K16,000+</div>
            <div class="text-xs text-emerald-200">Ambassador</div>
          </div>
        </div>
        <p class="text-xs text-emerald-200 mt-3">*Based on full network with active members</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
