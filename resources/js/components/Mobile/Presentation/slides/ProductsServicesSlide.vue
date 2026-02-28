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
        <!-- Cloud Storage -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-blue-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">☁️</span>
            </div>
            <h3 class="font-bold text-sm">Cloud Storage</h3>
          </div>
          <p class="text-xs text-sky-200">Secure file storage and sharing for your business</p>
        </div>
        
        <!-- Learning Resources -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-green-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">📚</span>
            </div>
            <h3 class="font-bold text-sm">Learning Resources</h3>
          </div>
          <p class="text-xs text-sky-200">E-books, courses, and educational materials</p>
        </div>
        
        <!-- Video Streaming -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-purple-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">🎥</span>
            </div>
            <h3 class="font-bold text-sm">Video Streaming</h3>
          </div>
          <p class="text-xs text-sky-200">Training videos, workshops, and webinars</p>
        </div>
        
        <!-- Music & Audio -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-pink-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">🎵</span>
            </div>
            <h3 class="font-bold text-sm">Music & Audio</h3>
          </div>
          <p class="text-xs text-sky-200">Motivational talks, podcasts, and audio content</p>
        </div>
        
        <!-- Product Marketplace -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-amber-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">🛒</span>
            </div>
            <h3 class="font-bold text-sm">Product Marketplace</h3>
          </div>
          <p class="text-xs text-sky-200">Digital and physical products for purchase</p>
        </div>
        
        <!-- Mobile Apps -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-cyan-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">📱</span>
            </div>
            <h3 class="font-bold text-sm">Mobile Apps</h3>
          </div>
          <p class="text-xs text-sky-200">Life-Plus and other productivity applications</p>
        </div>
      </div>
      
      <!-- Subscription tiers removed - using Starter Kits instead -->
      <div class="bg-gradient-to-r from-blue-500/20 to-indigo-500/20 backdrop-blur-sm rounded-xl p-4 border border-blue-400/30">
        <p class="text-sm">
          <span class="text-xl mr-2">💡</span>
          <span class="font-bold">Access through Starter Kits:</span> Choose from Lite (K300), 
          Basic (K500), Growth Plus (K1000), or Pro (K2000) to unlock platform features!
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
