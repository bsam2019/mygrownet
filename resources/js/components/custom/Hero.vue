<template>
  <section class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>
    
    <!-- Floating Elements -->
    <div class="absolute top-20 left-10 w-72 h-72 bg-blue-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
    <div class="absolute top-40 right-10 w-72 h-72 bg-indigo-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
    <div class="absolute bottom-20 left-1/2 w-72 h-72 bg-purple-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-24 lg:pt-12 lg:pb-32">
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <!-- Left Content -->
        <div class="text-white space-y-6">
          <h1 class="text-5xl lg:text-6xl font-bold leading-tight">
            Your Gateway to Training, Business Tools, Products, and
            <span class="block bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">
              Venture Opportunities
            </span>
          </h1>
          
          <p class="text-xl text-blue-100 leading-relaxed">
            MyGrowNet helps you learn, access mentorship, shop trusted products, use powerful business apps, and join venture-building programs.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4">
            <Link 
              :href="route('starter-kits')"
              class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 font-semibold rounded-xl hover:bg-blue-50 transform hover:scale-105 transition-all duration-200 shadow-xl hover:shadow-2xl"
            >
              Explore Services
              <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </Link>
            
            <Link 
              :href="route('marketplace.index')"
              class="inline-flex items-center justify-center px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-semibold rounded-xl hover:bg-white/20 border-2 border-white/30 transition-all duration-200"
            >
              View Products
            </Link>
          </div>
          
          <!-- Product-Focused Metrics -->
          <div class="flex items-center gap-8 pt-8 border-t border-white/20">
            <div>
              <div class="text-3xl font-bold">100+</div>
              <div class="text-sm text-blue-200">Available Courses</div>
            </div>
            <div>
              <div class="text-3xl font-bold">50+</div>
              <div class="text-sm text-blue-200">Mentors</div>
            </div>
            <div>
              <div class="text-3xl font-bold">500+</div>
              <div class="text-sm text-blue-200">Products Listed</div>
            </div>
          </div>
        </div>
        
        <!-- Right Content - Feature Cards -->
        <div class="relative">
          <div class="grid grid-cols-2 gap-4">
            <!-- Card 1 - Training Center -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition-all duration-300 transform hover:-translate-y-2">
              <div class="w-12 h-12 bg-purple-400 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <h3 class="text-white font-semibold mb-2">Training Center</h3>
              <p class="text-blue-100 text-sm">Structured courses and skill development programs</p>
            </div>
            
            <!-- Card 2 - Mentorship -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition-all duration-300 transform hover:-translate-y-2 mt-8">
              <div class="w-12 h-12 bg-emerald-400 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <h3 class="text-white font-semibold mb-2">Mentorship Program</h3>
              <p class="text-blue-100 text-sm">Connect with experienced mentors for guidance</p>
            </div>
            
            <!-- Card 3 - Marketplace -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition-all duration-300 transform hover:-translate-y-2">
              <div class="w-12 h-12 bg-cyan-400 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
              <h3 class="text-white font-semibold mb-2">Marketplace</h3>
              <p class="text-blue-100 text-sm">Shop physical products from trusted vendors</p>
            </div>
            
            <!-- Card 4 - Business Apps -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 hover:bg-white/20 transition-all duration-300 transform hover:-translate-y-2 mt-8">
              <div class="w-12 h-12 bg-indigo-400 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
              </div>
              <h3 class="text-white font-semibold mb-2">Business Apps</h3>
              <p class="text-blue-100 text-sm">GrowFinance, GrowBiz, and BizBoost tools</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Wave Separator -->
    <div class="relative">
      <svg class="w-full h-24" viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 0L60 10C120 20 240 40 360 46.7C480 53 600 47 720 43.3C840 40 960 40 1080 46.7C1200 53 1320 67 1380 73.3L1440 80V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0V0Z" fill="white"/>
      </svg>
    </div>
  </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped>
@keyframes blob {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}
</style>
