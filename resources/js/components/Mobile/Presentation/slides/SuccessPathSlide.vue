<template>
  <div class="min-h-full w-full bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 flex flex-col p-4 py-6 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -mr-36 -mt-36"></div>
    <div class="absolute bottom-0 left-0 w-56 h-56 bg-white/5 rounded-full -ml-28 -mb-28"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center flex-1 flex flex-col">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-3 mx-auto">
        <span class="text-white text-sm font-semibold">🗺️ Your Success Path</span>
      </div>
      
      <h2 class="text-xl md:text-2xl font-bold mb-1">
        7-Level Professional Journey
      </h2>
      <p class="text-purple-200 mb-3 text-xs">
        From Associate to Ambassador — Life Points (LP) &amp; Education Level progression
      </p>
      
      <!-- Timeline - All 7 Levels -->
      <div class="relative flex-1 overflow-y-auto">
        <!-- Vertical line -->
        <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gradient-to-b from-gray-400 via-blue-400 via-emerald-400 via-purple-400 via-indigo-400 via-pink-400 to-amber-400"></div>
        
        <div class="space-y-2">
          <!-- Level 1: Associate -->
          <div class="flex items-start gap-2 text-left">
            <div class="w-10 h-10 bg-gray-500 rounded-full flex items-center justify-center flex-shrink-0 z-10 border-2 border-purple-700 shadow-lg">
              <span class="text-xs font-bold">L1</span>
            </div>
            <div class="flex-1 bg-white/10 backdrop-blur-sm rounded-lg p-2 border border-white/20">
              <div class="flex items-center justify-between">
                <span class="font-bold text-sm">Associate (0 LP)</span>
                <span class="text-[10px] text-purple-200 bg-white/10 px-1.5 py-0.5 rounded">Month 1</span>
              </div>
              <div class="flex items-center justify-between mt-1">
                <span class="text-[10px] text-purple-200">3 referrals • 1.0x profit share</span>
                <span class="text-[10px] text-green-400 font-medium">📦 Starter Kit</span>
              </div>
            </div>
          </div>
          
          <!-- Level 2: Professional -->
          <div class="flex items-start gap-2 text-left">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 z-10 border-2 border-purple-700 shadow-lg">
              <span class="text-xs font-bold">L2</span>
            </div>
            <div class="flex-1 bg-white/10 backdrop-blur-sm rounded-lg p-2 border border-white/20">
              <div class="flex items-center justify-between">
                <span class="font-bold text-sm">Professional (2,500 LP)</span>
                <span class="text-[10px] text-purple-200 bg-white/10 px-1.5 py-0.5 rounded">Month 2-3</span>
              </div>
              <div class="flex items-center justify-between mt-1">
                <span class="text-[10px] text-purple-200">9 network • 1.2x profit share</span>
                <span class="text-[10px] text-green-400 font-medium">💰 K500 Cash</span>
              </div>
            </div>
          </div>
          
          <!-- Level 3: Senior -->
          <div class="flex items-start gap-2 text-left">
            <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 z-10 border-2 border-purple-700 shadow-lg">
              <span class="text-xs font-bold">L3</span>
            </div>
            <div class="flex-1 bg-white/10 backdrop-blur-sm rounded-lg p-2 border border-white/20">
              <div class="flex items-center justify-between">
                <span class="font-bold text-sm">Senior (4,000 LP)</span>
                <span class="text-[10px] text-purple-200 bg-white/10 px-1.5 py-0.5 rounded">Month 3-6</span>
              </div>
              <div class="flex items-center justify-between mt-1">
                <span class="text-[10px] text-purple-200">27 network • 1.5x profit share</span>
                <span class="text-[10px] text-green-400 font-medium">📱 Smartphone</span>
              </div>
            </div>
          </div>
          
          <!-- Level 4: Manager -->
          <div class="flex items-start gap-2 text-left">
            <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0 z-10 border-2 border-purple-700 shadow-lg">
              <span class="text-xs font-bold">L4</span>
            </div>
            <div class="flex-1 bg-white/10 backdrop-blur-sm rounded-lg p-2 border border-white/20">
              <div class="flex items-center justify-between">
                <span class="font-bold text-sm">Manager (12,500 LP)</span>
                <span class="text-[10px] text-purple-200 bg-white/10 px-1.5 py-0.5 rounded">Month 6-12</span>
              </div>
              <div class="flex items-center justify-between mt-1">
                <span class="text-[10px] text-purple-200">81 network • 2.0x profit share</span>
                <span class="text-[10px] text-amber-400 font-medium">🛵 Motorbike + K5,000</span>
              </div>
            </div>
          </div>
          
          <!-- Level 5: Director -->
          <div class="flex items-start gap-2 text-left">
            <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0 z-10 border-2 border-purple-700 shadow-lg">
              <span class="text-xs font-bold">L5</span>
            </div>
            <div class="flex-1 bg-white/10 backdrop-blur-sm rounded-lg p-2 border border-white/20">
              <div class="flex items-center justify-between">
                <span class="font-bold text-sm">Director (60,000 LP)</span>
                <span class="text-[10px] text-purple-200 bg-white/10 px-1.5 py-0.5 rounded">Month 12-18</span>
              </div>
              <div class="flex items-center justify-between mt-1">
                <span class="text-[10px] text-purple-200">243 network • 2.5x profit share</span>
                <span class="text-[10px] text-amber-400 font-medium">🚗 Car + K15,000</span>
              </div>
            </div>
          </div>
          
          <!-- Level 6: Executive -->
          <div class="flex items-start gap-2 text-left">
            <div class="w-10 h-10 bg-pink-500 rounded-full flex items-center justify-center flex-shrink-0 z-10 border-2 border-purple-700 shadow-lg">
              <span class="text-xs font-bold">L6</span>
            </div>
            <div class="flex-1 bg-white/10 backdrop-blur-sm rounded-lg p-2 border border-white/20">
              <div class="flex items-center justify-between">
                <span class="font-bold text-sm">Executive (160,000 LP)</span>
                <span class="text-[10px] text-purple-200 bg-white/10 px-1.5 py-0.5 rounded">Month 18-24</span>
              </div>
              <div class="flex items-center justify-between mt-1">
                <span class="text-[10px] text-purple-200">729 network • 3.0x profit share</span>
                <span class="text-[10px] text-amber-400 font-medium">🚘 Luxury Car + K50,000</span>
              </div>
            </div>
          </div>
          
          <!-- Level 7: Ambassador -->
          <div class="flex items-start gap-2 text-left">
            <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center flex-shrink-0 z-10 border-2 border-amber-300/50 shadow-lg shadow-amber-500/30">
              <span class="text-xs font-bold">L7</span>
            </div>
            <div class="flex-1 bg-gradient-to-r from-amber-500/20 to-orange-500/20 backdrop-blur-sm rounded-lg p-2 border border-amber-400/30">
              <div class="flex items-center justify-between">
                <span class="font-bold text-sm text-amber-300">Ambassador (350,000 LP)</span>
                <span class="text-[10px] text-amber-200 bg-amber-500/20 px-1.5 py-0.5 rounded">Month 24+</span>
              </div>
              <div class="flex items-center justify-between mt-1">
                <span class="text-[10px] text-amber-200">2,187 network • 4.0x profit share</span>
                <span class="text-[10px] text-amber-300 font-medium">🏠 Property + K150,000</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Bottom summary -->
      <div class="mt-3 bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20">
        <div class="grid grid-cols-3 gap-2 text-center">
          <div>
            <div class="text-lg font-bold text-green-400">K150,000</div>
            <div class="text-[10px] text-purple-200">Max Cash Bonus</div>
          </div>
          <div>
            <div class="text-lg font-bold text-amber-400">2,187</div>
            <div class="text-[10px] text-purple-200">3x7 Matrix Max</div>
          </div>
          <div>
            <div class="text-lg font-bold text-blue-400">4.0x</div>
            <div class="text-[10px] text-purple-200">Max Profit Share</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
