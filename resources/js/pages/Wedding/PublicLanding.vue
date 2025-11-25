<template>
  <div class="min-h-screen bg-gradient-to-br from-rose-50 via-white to-amber-50">
    <!-- Floral Background Decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-0 left-0 w-96 h-96 opacity-20">
        <img src="/images/Wedding/flora.jpg" alt="" class="w-full h-full object-contain" />
      </div>
      <div class="absolute top-0 right-0 w-96 h-96 opacity-20 transform scale-x-[-1]">
        <img src="/images/Wedding/flora.jpg" alt="" class="w-full h-full object-contain" />
      </div>
    </div>

    <!-- Navigation -->
    <nav class="relative z-10 px-6 py-8">
      <div class="max-w-4xl mx-auto">
        <ul class="flex justify-center space-x-8 text-sm tracking-wider text-gray-600">
          <li><a href="#home" class="hover:text-rose-600 transition-colors">Home</a></li>
          <li><a href="#our-story" class="hover:text-rose-600 transition-colors">Our Story</a></li>
          <li><a href="#wedding-party" class="hover:text-rose-600 transition-colors">Wedding Party</a></li>
          <li><a href="#qa" class="hover:text-rose-600 transition-colors">Q & A</a></li>
          <li><a href="#travel" class="hover:text-rose-600 transition-colors">Travel</a></li>
          <li><button @click="openRSVPModal" class="hover:text-rose-600 transition-colors">RSVP</button></li>
        </ul>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 px-6">
      <div class="max-w-4xl mx-auto text-center">
        <!-- Couple Names -->
        <div class="mb-8">
          <h1 class="text-4xl md:text-6xl font-light text-gray-700 tracking-widest mb-4">
            ALINUSWE & USONJE
          </h1>
          <p class="text-sm tracking-wider text-gray-500 uppercase">
            Saturday, April 5, 2025
          </p>
        </div>

        <!-- Hero Image -->
        <div class="mb-12">
          <div class="relative mx-auto max-w-2xl">
            <img 
              src="/images/couple-hero.jpg" 
              alt="Alinuswe & Usonje" 
              class="w-full h-96 object-cover rounded-lg shadow-2xl grayscale hover:grayscale-0 transition-all duration-700"
            />
            
            <!-- RSVP Button Overlay -->
            <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2">
              <button 
                @click="openRSVPModal"
                class="bg-gray-600 hover:bg-rose-600 text-white px-8 py-3 text-sm tracking-widest uppercase transition-colors duration-300"
              >
                RSVP
              </button>
            </div>
          </div>
        </div>

        <!-- Wedding Details -->
        <div class="mb-16">
          <h2 class="text-2xl font-light text-gray-700 tracking-wider mb-6 uppercase">
            Wedding Ceremony
          </h2>
          
          <div class="space-y-2 text-gray-600">
            <p class="text-sm tracking-wide">St Margaret's Congregation, United Church of Zambia</p>
            <p class="text-sm tracking-wide">Corner of Independence/Obote Avenue, Kitwe</p>
            <p class="text-sm tracking-wide mt-4">Kindly RSVP by 15th March 2025</p>
          </div>
        </div>

        <!-- Monogram -->
        <div class="mb-16">
          <div class="inline-block border-2 border-gray-300 p-8">
            <h3 class="text-6xl font-light text-gray-700 tracking-wider">A&U</h3>
          </div>
          <p class="text-sm tracking-wider text-gray-500 mt-4">4.5.2025</p>
        </div>

        <!-- Footer -->
        <footer class="text-center text-xs text-gray-400 tracking-wider">
          <p class="mb-2">Created on The Knot</p>
          <p>Getting married? <a href="#" class="underline hover:text-rose-600">Create your wedding website for free</a></p>
          <p class="mt-2">Your Design Choices</p>
        </footer>
      </div>
    </main>

    <!-- RSVP Modal -->
    <RSVPModal 
      :isOpen="showRSVPModal" 
      @close="closeRSVPModal" 
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import RSVPModal from '@/components/Wedding/RSVPModal.vue'

const showRSVPModal = ref(false)

// Open RSVP Modal
const openRSVPModal = () => {
  showRSVPModal.value = true
}

const closeRSVPModal = () => {
  showRSVPModal.value = false
}

// Add some elegant animations on mount
onMounted(() => {
  // Fade in animation for main content
  const main = document.querySelector('main')
  if (main) {
    main.style.opacity = '0'
    main.style.transform = 'translateY(20px)'
    
    setTimeout(() => {
      main.style.transition = 'all 1s ease-out'
      main.style.opacity = '1'
      main.style.transform = 'translateY(0)'
    }, 100)
  }
})
</script>

<style scoped>
/* Custom font styling for elegant typography */
h1, h2, h3 {
  font-family: 'Playfair Display', serif;
}

/* Elegant hover effects */
.hover\:grayscale-0:hover {
  filter: grayscale(0%);
}

/* Smooth transitions for all interactive elements */
a, button {
  transition: all 0.3s ease;
}

/* Custom background pattern */
.bg-gradient-to-br {
  background-image: 
    radial-gradient(circle at 25% 25%, rgba(255, 182, 193, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 75% 75%, rgba(255, 218, 185, 0.1) 0%, transparent 50%);
}
</style>