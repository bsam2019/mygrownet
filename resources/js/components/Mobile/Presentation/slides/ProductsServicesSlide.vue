<template>
  <div class="min-h-full w-full bg-gradient-to-br from-sky-600 via-blue-600 to-indigo-700 flex flex-col p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -ml-32 -mt-32"></div>
    <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/5 rounded-full -mr-24 -mb-24"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-6">
        <span class="text-white text-sm font-semibold">📦 Products &amp; Ecosystem Services</span>
      </div>
      
      <h2 class="text-3xl md:text-4xl font-bold mb-3">
        Real Value, Real Ecosystem
      </h2>
      <p class="text-sky-200 mb-6">
        Connected across MyGrowNet, GrowStream Video &amp; GrowMusic Subdomains
      </p>
      
      <!-- Products grid -->
      <div class="grid grid-cols-2 gap-3 mb-6">
        <!-- GrowStream Video -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-purple-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">🎥</span>
            </div>
            <h3 class="font-bold text-sm">GrowStream Video</h3>
          </div>
          <p class="text-xs text-sky-200">Movies, series, live workshops &amp; B2B Creator Hubs</p>
        </div>
        
        <!-- GrowMusic Portal -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-amber-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">🎵</span>
            </div>
            <h3 class="font-bold text-sm">GrowMusic Subdomain</h3>
          </div>
          <p class="text-xs text-sky-200">Zambian music streaming, artist fan clubs &amp; ZAMCO royalties</p>
        </div>
        
        <!-- Digital Library Downloads -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-green-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">📚</span>
            </div>
            <h3 class="font-bold text-sm">Digital Kit Downloads</h3>
          </div>
          <p class="text-xs text-sky-200">Downloadable audiobooks, e-pubs &amp; PDF action pamphlets</p>
        </div>
        
        <!-- Facilitator & Workshops -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-pink-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">🎤</span>
            </div>
            <h3 class="font-bold text-sm">Facilitator Portal</h3>
          </div>
          <p class="text-xs text-sky-200">Mobile QR scanner check-ins &amp; voice-note audio exams</p>
        </div>
        
        <!-- 3x7 Matrix Engine -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-blue-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">🌳</span>
            </div>
            <h3 class="font-bold text-sm">3x7 Matrix Engine</h3>
          </div>
          <p class="text-xs text-sky-200">Forced downline matrix structure with automatic spillover</p>
        </div>
        
        <!-- LGR Profit Pool -->
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20 text-left">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-cyan-500/30 rounded-lg flex items-center justify-center">
              <span class="text-xl">💎</span>
            </div>
            <h3 class="font-bold text-sm">LGR Profit Pool</h3>
          </div>
          <p class="text-xs text-sky-200">90-day cycle 60% profit pool participation</p>
        </div>
      </div>
      
      <!-- Key note -->
      <div class="bg-gradient-to-r from-blue-500/20 to-indigo-500/20 backdrop-blur-sm rounded-xl p-4 border border-blue-400/30">
        <p class="text-sm">
          <span class="text-xl mr-2">💡</span>
          <span class="font-bold">Unlocked via Membership &amp; Education:</span> Gain instant access to learning resources, streaming portals, and business infrastructure!
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
