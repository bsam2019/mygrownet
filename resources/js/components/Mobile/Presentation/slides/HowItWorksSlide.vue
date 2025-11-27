<template>
  <div class="min-h-full w-full bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 flex flex-col items-center justify-center p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -ml-32 -mt-32"></div>
    <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/5 rounded-full -mr-24 -mb-24"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-6">
        <span class="text-white text-sm font-semibold">How It Works</span>
      </div>
      
      <h2 class="text-3xl md:text-4xl font-bold mb-8">
        3 Simple Steps to Success
      </h2>
      
      <!-- Steps -->
      <div class="space-y-6">
        <!-- Step 1 -->
        <div class="flex items-start gap-4 bg-white/10 backdrop-blur-sm rounded-2xl p-5 text-left">
          <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg">
            <span class="text-3xl">1️⃣</span>
          </div>
          <div class="flex-1">
            <h3 class="text-xl font-bold mb-2 flex items-center gap-2">
              <span>Subscribe</span>
              <span class="text-2xl">📝</span>
            </h3>
            <p class="text-emerald-100">
              Join MyGrowNet with a Starter Kit (from K500). Get instant access to learning resources, 
              shop credit, and earning opportunities.
            </p>
          </div>
        </div>
        
        <!-- Step 2 -->
        <div class="flex items-start gap-4 bg-white/10 backdrop-blur-sm rounded-2xl p-5 text-left">
          <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg">
            <span class="text-3xl">2️⃣</span>
          </div>
          <div class="flex-1">
            <h3 class="text-xl font-bold mb-2 flex items-center gap-2">
              <span>Learn & Share</span>
              <span class="text-2xl">🎓</span>
            </h3>
            <p class="text-emerald-100">
              Access e-books, video courses, and coaching. Share the opportunity with others 
              and build your network.
            </p>
          </div>
        </div>
        
        <!-- Step 3 -->
        <div class="flex items-start gap-4 bg-white/10 backdrop-blur-sm rounded-2xl p-5 text-left">
          <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg">
            <span class="text-3xl">3️⃣</span>
          </div>
          <div class="flex-1">
            <h3 class="text-xl font-bold mb-2 flex items-center gap-2">
              <span>Earn & Grow</span>
              <span class="text-2xl">💰</span>
            </h3>
            <p class="text-emerald-100">
              Earn commissions from 7 levels, receive performance bonuses, and qualify for 
              quarterly profit sharing!
            </p>
          </div>
        </div>
      </div>
      
      <!-- Bottom highlight -->
      <div class="mt-8 flex items-center justify-center gap-2 text-emerald-200">
        <span class="text-xl">⚡</span>
        <span class="font-medium">Start earning within 24 hours of your first referral!</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
