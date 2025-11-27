<template>
  <div class="min-h-full w-full bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex flex-col items-center justify-center p-6 py-8 text-white relative overflow-hidden">
    <!-- Background pattern -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute top-10 left-10 w-20 h-20 border border-blue-400 rounded-lg rotate-12"></div>
      <div class="absolute bottom-20 right-10 w-16 h-16 border border-purple-400 rounded-full"></div>
      <div class="absolute top-1/3 right-1/4 w-12 h-12 border border-green-400 rounded-lg -rotate-12"></div>
    </div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-blue-500/20 border border-blue-400/30 rounded-full mb-6">
        <span class="text-blue-300 text-sm font-semibold">About MyGrowNet</span>
      </div>
      
      <h2 class="text-3xl md:text-4xl font-bold mb-6">
        A Community Empowerment Platform
      </h2>
      
      <p class="text-lg text-gray-300 mb-8 leading-relaxed">
        MyGrowNet is a <span class="text-blue-400 font-semibold">Private Limited Company</span> 
        designed to equip members with practical life skills, mentorship, and income opportunities 
        through a sustainable 7-level community model.
      </p>
      
      <!-- Key points -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
              <span class="text-xl">✅</span>
            </div>
            <div>
              <h3 class="font-semibold text-white mb-1">Legally Registered</h3>
              <p class="text-sm text-gray-400">Private Limited Company, not an investment scheme</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
              <span class="text-xl">📚</span>
            </div>
            <div>
              <h3 class="font-semibold text-white mb-1">Product-Based</h3>
              <p class="text-sm text-gray-400">Real educational value and services</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
              <span class="text-xl">🤝</span>
            </div>
            <div>
              <h3 class="font-semibold text-white mb-1">Community Focused</h3>
              <p class="text-sm text-gray-400">Empowerment over exploitation</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
              <span class="text-xl">💎</span>
            </div>
            <div>
              <h3 class="font-semibold text-white mb-1">Sustainable Model</h3>
              <p class="text-sm text-gray-400">Multiple revenue streams for long-term success</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Mission statement -->
      <div class="mt-8 p-4 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-xl border border-blue-400/20">
        <p class="text-blue-200 italic">
          "Creating a community of empowered individuals who grow together through skills, 
          entrepreneurship, and shared rewards."
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed
</script>
