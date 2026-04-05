<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
  </Head>

  <div class="min-h-screen bg-black text-white overflow-x-hidden">
    <!-- Cinematic Background -->
    <div class="fixed inset-0 z-0">
      <div class="absolute inset-0 bg-gradient-to-br from-black via-gray-900 to-black opacity-90"></div>
      <div class="absolute inset-0">
        <div class="absolute top-20 left-20 w-96 h-96 bg-purple-900 rounded-full filter blur-3xl opacity-20"></div>
        <div class="absolute bottom-20 right-20 w-80 h-80 bg-blue-900 rounded-full filter blur-3xl opacity-20"></div>
        <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-amber-900 rounded-full filter blur-3xl opacity-10"></div>
      </div>
    </div>

    <!-- Asymmetrical Hero Section -->
    <section class="relative min-h-screen flex items-center z-10">
      <!-- Left Panel - Names -->
      <div class="w-1/2 pl-20 pr-8">
        <div class="space-y-8">
          <div class="overflow-hidden">
            <h1 class="text-6xl md:text-8xl font-thin tracking-wider transform translate-x-0 opacity-100 transition-all duration-1000">
              {{ weddingEvent.groom_name }}
            </h1>
          </div>
          
          <div class="flex items-center space-x-8">
            <div class="h-px w-24 bg-gradient-to-r from-transparent via-amber-500 to-transparent"></div>
            <div class="text-4xl font-light tracking-widest text-amber-500">&</div>
            <div class="h-px w-24 bg-gradient-to-r from-amber-500 to-transparent"></div>
          </div>
          
          <div class="overflow-hidden">
            <h2 class="text-6xl md:text-8xl font-thin tracking-wider transform translate-x-0 opacity-100 transition-all duration-1000 delay-200">
              {{ weddingEvent.bride_name }}
            </h2>
          </div>
          
          <div class="pt-12 space-y-4">
            <p class="text-sm tracking-[0.3em] uppercase text-gray-400">{{ formatDate(weddingEvent.wedding_date) }}</p>
            <p class="text-sm tracking-[0.2em] uppercase text-gray-500">{{ weddingEvent.venue_name }}</p>
          </div>
        </div>
      </div>

      <!-- Right Panel - Dramatic Image -->
      <div class="w-1/2 relative h-screen">
        <div class="absolute inset-0 bg-gradient-to-l from-black via-transparent to-transparent"></div>
        <img 
          :src="weddingEvent.hero_image || '/images/Wedding/main.jpg'" 
          :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
          class="w-full h-full object-cover object-center"
        />
        
        <!-- Floating Elements -->
        <div class="absolute top-20 right-20 w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
        <div class="absolute top-32 right-40 w-1 h-1 bg-amber-400 rounded-full animate-pulse delay-75"></div>
        <div class="absolute bottom-40 right-16 w-3 h-3 bg-amber-500 rounded-full animate-pulse delay-150"></div>
        
        <!-- Geometric Overlay -->
        <div class="absolute inset-0 pointer-events-none">
          <div class="absolute top-0 right-0 w-32 h-32 border-r-2 border-t-2 border-amber-500 opacity-30"></div>
          <div class="absolute bottom-0 left-0 w-48 h-48 border-l-2 border-b-2 border-amber-500 opacity-20"></div>
        </div>
      </div>
    </section>

    <!-- Asymmetrical Content Grid -->
    <section class="relative z-10 py-32 px-20">
      <div class="grid grid-cols-12 gap-8">
        <!-- Offset Story Block -->
        <div class="col-span-7 col-start-2 space-y-12">
          <div class="space-y-6">
            <h3 class="text-4xl font-thin tracking-wider">Our Story</h3>
            <div class="h-px w-32 bg-amber-500"></div>
            <p class="text-gray-300 leading-relaxed text-lg">
              Two souls destined to meet, a love written in the stars. Our journey began with a single glance and evolved into an eternal bond that transcends time and space.
            </p>
          </div>
        </div>

        <!-- Floating Details Card -->
        <div class="col-span-4 col-start-10">
          <div class="bg-gray-900 bg-opacity-50 backdrop-blur-lg border border-gray-800 p-8 space-y-8">
            <div class="space-y-4">
              <h4 class="text-sm tracking-[0.2em] uppercase text-gray-400">Date</h4>
              <p class="text-2xl font-light">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
            </div>
            <div class="space-y-4">
              <h4 class="text-sm tracking-[0.2em] uppercase text-gray-400">Time</h4>
              <p class="text-2xl font-light">{{ weddingEvent.ceremony_time || '6:00 PM' }}</p>
            </div>
            <div class="space-y-4">
              <h4 class="text-sm tracking-[0.2em] uppercase text-gray-400">Venue</h4>
              <p class="text-lg font-light">{{ weddingEvent.venue_name }}</p>
              <p class="text-sm text-gray-400">{{ weddingEvent.venue_address }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Dramatic Gallery Section -->
      <div class="mt-32 grid grid-cols-12 gap-4">
        <div class="col-span-5">
          <div class="aspect-[3/4] bg-gray-900 overflow-hidden group">
            <img src="/images/Wedding/gallery1.jpg" alt="Gallery 1" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent opacity-60"></div>
          </div>
        </div>
        <div class="col-span-7 space-y-4">
          <div class="aspect-[16/9] bg-gray-900 overflow-hidden group">
            <img src="/images/Wedding/gallery2.jpg" alt="Gallery 2" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent opacity-40"></div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="aspect-square bg-gray-900 overflow-hidden group">
              <img src="/images/Wedding/gallery3.jpg" alt="Gallery 3" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
            </div>
            <div class="aspect-square bg-gray-900 overflow-hidden group">
              <img src="/images/Wedding/gallery4.jpg" alt="Gallery 4" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- RSVP Section - Off-Center -->
    <section class="relative z-10 py-32 px-20">
      <div class="max-w-2xl">
        <div class="text-center space-y-12">
          <div class="space-y-6">
            <h3 class="text-5xl font-thin tracking-wider">Join Us</h3>
            <div class="flex items-center justify-center space-x-8">
              <div class="h-px w-24 bg-gradient-to-r from-transparent via-amber-500 to-transparent"></div>
              <div class="text-2xl text-amber-500">✦</div>
              <div class="h-px w-24 bg-gradient-to-r from-amber-500 to-transparent"></div>
            </div>
          </div>
          
          <button 
            @click="showRSVPModal = true"
            class="group relative px-16 py-6 bg-transparent border border-amber-500 text-amber-500 tracking-widest uppercase overflow-hidden transition-all duration-500 hover:text-black"
          >
            <div class="absolute inset-0 bg-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
            <span class="relative z-10">RSVP</span>
          </button>
        </div>
      </div>
    </section>

    <!-- RSVP Modal -->
    <RSVPModal 
      :isOpen="showRSVPModal" 
      :weddingEventId="weddingEvent.id"
      @close="showRSVPModal = false" 
      @submitted="onRSVPSubmitted"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import RSVPModal from '@/components/Wedding/RSVPModal.vue'

const props = defineProps({
  weddingEvent: Object,
  template: Object,
  galleryImages: Array,
  ogMeta: {
    type: Object,
    default: () => ({})
  },
  isPreview: Boolean,
})

const showRSVPModal = ref(false)

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  }).toUpperCase()
}

const formatDateFull = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  })
}

const onRSVPSubmitted = () => {
  showRSVPModal.value = false
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@200;300;400;500;600;700&family=Inter:wght@200;300;400;500;600&display=swap');

.font-thin {
  font-weight: 200;
}

.animate-pulse {
  animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.delay-75 {
  animation-delay: 75ms;
}

.delay-150 {
  animation-delay: 150ms;
}
</style>
