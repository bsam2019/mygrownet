<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
</script>

<template>
  <section class="relative bg-gradient-to-br from-blue-700 via-blue-800 to-gray-900 text-white overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <!-- Content -->
        <div>
          <div class="inline-block bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium mb-4">
            ✨ Professional Printing & Branding Solutions
          </div>
          
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4 leading-tight">
            Transform Your Brand with
            <span class="text-red-400"> Quality Printing</span>
          </h1>
          
          <p class="text-xl text-blue-100 mb-6 max-w-xl">
            From business cards to large format printing, we deliver exceptional quality 
            and creative solutions that make your brand stand out.
          </p>

          <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <Link
              href="/geopamu/services"
              class="inline-flex items-center justify-center bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition-all shadow-lg hover:shadow-xl"
            >
              Explore Services
              <ArrowRightIcon class="ml-2 h-5 w-5" aria-hidden="true" />
            </Link>
            
            <Link
              href="/geopamu/portfolio"
              class="inline-flex items-center justify-center bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white/10 transition-all"
            >
              View Portfolio
            </Link>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-3 gap-6 mt-8">
            <div>
              <div class="text-3xl font-bold">500+</div>
              <div class="text-blue-200 text-sm">Projects</div>
            </div>
            <div>
              <div class="text-3xl font-bold">200+</div>
              <div class="text-blue-200 text-sm">Clients</div>
            </div>
            <div>
              <div class="text-3xl font-bold">10+</div>
              <div class="text-blue-200 text-sm">Years</div>
            </div>
          </div>
        </div>

        <!-- Hero Image -->
        <div class="relative">
          <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 shadow-2xl">
            <div class="rounded-lg h-96 overflow-hidden">
              <img 
                src="https://images.unsplash.com/photo-1611162617474-5b21e879e113?w=800&h=600&fit=crop" 
                alt="Professional printing services"
                class="w-full h-full object-cover"
              />
            </div>
          </div>
          
          <!-- Floating Elements -->
          <div class="absolute -top-4 -right-4 bg-red-600 text-white px-6 py-3 rounded-full font-bold shadow-lg">
            Quality Guaranteed
          </div>
          <div class="absolute -bottom-4 -left-4 bg-white text-blue-700 px-6 py-3 rounded-full font-bold shadow-lg">
            Fast Turnaround
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
