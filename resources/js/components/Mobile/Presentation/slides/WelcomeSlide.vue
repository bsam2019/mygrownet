<template>
  <div class="min-h-full w-full bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 flex flex-col items-center justify-center p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -mr-48 -mt-48"></div>
    <div class="absolute bottom-0 left-0 w-72 h-72 bg-white/5 rounded-full -ml-36 -mb-36"></div>
    <div class="absolute top-1/2 left-1/4 w-32 h-32 bg-purple-400/20 rounded-full blur-xl"></div>
    
    <!-- Content -->
    <div class="relative z-10 text-center max-w-lg mx-auto">
      <!-- Logo -->
      <div class="mb-8 animate-fade-in">
        <div class="inline-flex items-center justify-center bg-white rounded-2xl p-4 shadow-2xl">
          <img 
            src="/logo.png" 
            alt="MyGrowNet" 
            class="h-16 w-auto"
          />
        </div>
      </div>
      
      <!-- Tagline -->
      <h1 class="text-4xl md:text-5xl font-bold mb-4 animate-slide-up">
        Learn. Earn. Grow.
      </h1>
      
      <p class="text-xl md:text-2xl text-blue-100 mb-8 animate-slide-up" style="animation-delay: 0.1s">
        Your Journey to Financial Freedom Starts Here
      </p>
      
      <!-- Key highlights -->
      <div class="flex flex-wrap justify-center gap-4 mb-12 animate-fade-in" style="animation-delay: 0.2s">
        <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
          <span class="text-2xl">📚</span>
          <span class="font-medium">Learn Skills</span>
        </div>
        <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
          <span class="text-2xl">💰</span>
          <span class="font-medium">Earn Income</span>
        </div>
        <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
          <span class="text-2xl">🚀</span>
          <span class="font-medium">Grow Together</span>
        </div>
      </div>
      
      <!-- Swipe hint -->
      <div class="animate-bounce text-white/60 text-sm flex items-center justify-center gap-2">
        <span>Swipe to explore</span>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed for welcome slide
</script>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slide-up {
  from { 
    opacity: 0;
    transform: translateY(20px);
  }
  to { 
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out forwards;
}

.animate-slide-up {
  animation: slide-up 0.6s ease-out forwards;
}
</style>
