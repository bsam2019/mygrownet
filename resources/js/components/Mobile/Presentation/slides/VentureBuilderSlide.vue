<template>
  <div class="min-h-full w-full bg-gradient-to-br from-slate-800 via-slate-900 to-black flex flex-col items-center justify-center p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 right-0 w-72 h-72 bg-purple-500/10 rounded-full -mr-36 -mt-36"></div>
    <div class="absolute bottom-0 left-0 w-56 h-56 bg-blue-500/10 rounded-full -ml-28 -mb-28"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-r from-purple-500/5 to-blue-500/5 rounded-full blur-3xl"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500/30 to-blue-500/30 border border-purple-400/30 rounded-full mb-6">
        <span class="text-purple-300 text-sm font-semibold">🚀 Venture Builder</span>
        <span class="ml-2 px-2 py-0.5 bg-purple-500 text-white text-xs rounded-full">NEW</span>
      </div>
      
      <h2 class="text-3xl md:text-4xl font-bold mb-3 bg-gradient-to-r from-purple-400 to-blue-400 bg-clip-text text-transparent">
        Become a Business Owner
      </h2>
      <p class="text-gray-400 mb-8">
        Co-invest in real businesses and become a shareholder
      </p>
      
      <!-- How it works -->
      <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 mb-6">
        <h3 class="font-bold text-lg mb-4">How Venture Builder Works</h3>
        
        <div class="space-y-4">
          <!-- Step 1 -->
          <div class="flex items-start gap-4 text-left">
            <div class="w-10 h-10 bg-purple-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <span class="font-bold">1</span>
            </div>
            <div>
              <div class="font-semibold">Browse Vetted Projects</div>
              <div class="text-sm text-gray-400">MyGrowNet publishes pre-assessed business opportunities</div>
            </div>
          </div>
          
          <!-- Step 2 -->
          <div class="flex items-start gap-4 text-left">
            <div class="w-10 h-10 bg-blue-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <span class="font-bold">2</span>
            </div>
            <div>
              <div class="font-semibold">Pledge Your Investment</div>
              <div class="text-sm text-gray-400">Contribute toward the startup capital target</div>
            </div>
          </div>
          
          <!-- Step 3 -->
          <div class="flex items-start gap-4 text-left">
            <div class="w-10 h-10 bg-green-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <span class="font-bold">3</span>
            </div>
            <div>
              <div class="font-semibold">Become a Shareholder</div>
              <div class="text-sm text-gray-400">A new limited company is formed, you're a documented owner!</div>
            </div>
          </div>
          
          <!-- Step 4 -->
          <div class="flex items-start gap-4 text-left">
            <div class="w-10 h-10 bg-amber-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <span class="font-bold">4</span>
            </div>
            <div>
              <div class="font-semibold">Earn Dividends</div>
              <div class="text-sm text-gray-400">Receive quarterly/annual dividends from profitable ventures</div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Project categories -->
      <div class="grid grid-cols-3 gap-2 mb-6">
        <div class="bg-white/5 rounded-xl p-3 border border-white/10">
          <div class="text-xl mb-1">🌾</div>
          <div class="text-xs text-gray-400">Agriculture</div>
        </div>
        <div class="bg-white/5 rounded-xl p-3 border border-white/10">
          <div class="text-xl mb-1">🚚</div>
          <div class="text-xs text-gray-400">Transport</div>
        </div>
        <div class="bg-white/5 rounded-xl p-3 border border-white/10">
          <div class="text-xl mb-1">🏠</div>
          <div class="text-xs text-gray-400">Real Estate</div>
        </div>
      </div>
      
      <!-- Key benefits -->
      <div class="bg-gradient-to-r from-purple-500/10 to-blue-500/10 rounded-xl p-4 border border-purple-400/20">
        <div class="flex items-center justify-center gap-4 text-sm">
          <div class="flex items-center gap-1">
            <span class="text-green-400">✓</span>
            <span>Legal ownership</span>
          </div>
          <div class="flex items-center gap-1">
            <span class="text-green-400">✓</span>
            <span>Real dividends</span>
          </div>
          <div class="flex items-center gap-1">
            <span class="text-green-400">✓</span>
            <span>Diversified portfolio</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
