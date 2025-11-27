<template>
  <div class="min-h-full w-full bg-gradient-to-br from-sky-600 via-blue-600 to-indigo-700 flex flex-col p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -ml-32 -mt-32"></div>
    <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/5 rounded-full -mr-24 -mb-24"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-6">
        <span class="text-white text-sm font-semibold">📦 Products & Services</span>
      </div>
      
      <h2 class="text-3xl md:text-4xl font-bold mb-3">
        Real Value, Real Products
      </h2>
      <p class="text-sky-200 mb-6">
        Not just an opportunity — a complete ecosystem
      </p>
      
      <!-- Products grid -->
      <div class="grid grid-cols-2 gap-3 mb-6">
        <!-- MyGrow Learn -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-blue-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">📚</span>
            </div>
            <h3 class="font-bold text-sm">MyGrow Learn</h3>
          </div>
          <p class="text-xs text-sky-200">E-books, video courses, audio lessons, and certifications</p>
        </div>
        
        <!-- MyGrow Shop -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-green-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">🛒</span>
            </div>
            <h3 class="font-bold text-sm">MyGrow Shop</h3>
          </div>
          <p class="text-xs text-sky-200">Digital products, tools, and partner merchandise</p>
        </div>
        
        <!-- MyGrow Coaching -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-purple-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">🎯</span>
            </div>
            <h3 class="font-bold text-sm">Coaching</h3>
          </div>
          <p class="text-xs text-sky-200">Group sessions, 1-on-1 mentorship, and workshops</p>
        </div>
        
        <!-- MyGrow Save -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-amber-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">💳</span>
            </div>
            <h3 class="font-bold text-sm">MyGrow Save</h3>
          </div>
          <p class="text-xs text-sky-200">Digital wallet for earnings, purchases, and withdrawals</p>
        </div>
        
        <!-- MyGrow Connect -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-pink-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">🤝</span>
            </div>
            <h3 class="font-bold text-sm">MyGrow Connect</h3>
          </div>
          <p class="text-xs text-sky-200">Service provider directory and business networking</p>
        </div>
        
        <!-- Tools -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-cyan-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">🛠️</span>
            </div>
            <h3 class="font-bold text-sm">Business Tools</h3>
          </div>
          <p class="text-xs text-sky-200">Marketing templates, calculators, and planning tools</p>
        </div>
      </div>
      
      <!-- Subscription tiers -->
      <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
        <h3 class="font-bold text-sm mb-3">Subscription Tiers (Optional)</h3>
        <div class="grid grid-cols-4 gap-2 text-xs">
          <div class="text-center p-2 bg-white/10 rounded-lg">
            <div class="font-bold">Basic</div>
            <div class="text-sky-200">K150/mo</div>
            <div class="text-sky-300">1.0x</div>
          </div>
          <div class="text-center p-2 bg-white/10 rounded-lg">
            <div class="font-bold">Silver</div>
            <div class="text-sky-200">K300/mo</div>
            <div class="text-sky-300">1.2x</div>
          </div>
          <div class="text-center p-2 bg-white/10 rounded-lg">
            <div class="font-bold">Gold</div>
            <div class="text-sky-200">K500/mo</div>
            <div class="text-sky-300">1.5x</div>
          </div>
          <div class="text-center p-2 bg-amber-500/20 rounded-lg border border-amber-400/30">
            <div class="font-bold text-amber-300">Platinum</div>
            <div class="text-amber-200">K1,000/mo</div>
            <div class="text-amber-300">2.0x</div>
          </div>
        </div>
        <p class="text-xs text-sky-200 mt-2">Higher tiers = Higher point multipliers!</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
