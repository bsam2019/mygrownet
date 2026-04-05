<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
  </Head>

  <div class="min-h-screen bg-white text-black">
    <!-- Brutalist Hero -->
    <section class="h-screen flex items-center justify-center relative">
      <!-- Geometric Background Elements -->
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-20 left-20 w-32 h-32 border-4 border-black transform rotate-45"></div>
        <div class="absolute bottom-20 right-20 w-24 h-24 border-2 border-black"></div>
        <div class="absolute top-1/3 right-1/4 w-16 h-16 bg-black opacity-10"></div>
      </div>

      <!-- Minimal Content -->
      <div class="text-center space-y-8 z-10">
        <div class="space-y-4">
          <h1 class="text-6xl md:text-8xl font-black tracking-tight">
            {{ weddingEvent.groom_name }}
          </h1>
          <div class="text-2xl font-light tracking-widest">&</div>
          <h2 class="text-6xl md:text-8xl font-black tracking-tight">
            {{ weddingEvent.bride_name }}
          </h2>
        </div>
        
        <div class="w-64 h-px bg-black mx-auto"></div>
        
        <div class="space-y-2 text-sm font-medium tracking-[0.3em] uppercase">
          <p>{{ formatDate(weddingEvent.wedding_date) }}</p>
          <p>{{ weddingEvent.venue_name }}</p>
        </div>
      </div>
    </section>

    <!-- Geometric Image Block -->
    <section class="relative">
      <div class="grid grid-cols-3 h-96">
        <div class="col-span-2 bg-black relative overflow-hidden">
          <img 
            :src="weddingEvent.hero_image || '/images/Wedding/main.jpg'" 
            :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
            class="w-full h-full object-cover opacity-80"
          />
        </div>
        <div class="bg-white border-l-4 border-black flex items-center justify-center">
          <div class="text-center space-y-4 p-8">
            <div class="text-xs font-bold tracking-[0.3em] uppercase">Date</div>
            <div class="text-lg font-light">{{ formatDateFull(weddingEvent.wedding_date) }}</div>
            <div class="w-16 h-px bg-black mx-auto"></div>
            <div class="text-xs font-bold tracking-[0.3em] uppercase">Time</div>
            <div class="text-lg font-light">{{ weddingEvent.ceremony_time || '6:00 PM' }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Negative Space Story -->
    <section class="py-32 px-16">
      <div class="max-w-2xl mx-auto">
        <div class="space-y-16">
          <!-- Single Statement -->
          <div class="text-center">
            <p class="text-2xl font-light leading-relaxed">
              We invite you to witness the beginning of our forever.
            </p>
          </div>

          <!-- Geometric Divider -->
          <div class="flex items-center justify-center">
            <div class="w-24 h-24 border-2 border-black transform rotate-45"></div>
          </div>

          <!-- Location Info -->
          <div class="text-center space-y-4">
            <div class="text-xs font-bold tracking-[0.3em] uppercase">Location</div>
            <p class="text-lg font-light">{{ weddingEvent.venue_name }}</p>
            <p class="text-sm text-gray-600">{{ weddingEvent.venue_address }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Brutalist Grid Gallery -->
    <section class="py-16 px-16">
      <div class="grid grid-cols-4 gap-4 h-96">
        <div class="col-span-2 row-span-2 bg-gray-100 relative">
          <img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80" alt="Gallery 1" class="w-full h-full object-cover" />
        </div>
        <div class="bg-black relative">
          <img src="https://images.unsplash.com/photo-1519741497674-63548a8048b6?w=600&q=80" alt="Gallery 2" class="w-full h-full object-cover opacity-60" />
        </div>
        <div class="bg-gray-100 relative">
          <img src="https://images.unsplash.com/photo-1522673659358-4054d49d5d0e?w=600&q=80" alt="Gallery 3" class="w-full h-full object-cover" />
        </div>
        <div class="bg-black relative">
          <img src="https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=600&q=80" alt="Gallery 4" class="w-full h-full object-cover opacity-40" />
        </div>
      </div>
    </section>

    <!-- Minimal RSVP -->
    <section class="py-32 px-16">
      <div class="max-w-md mx-auto text-center">
        <div class="space-y-8">
          <div class="space-y-4">
            <h3 class="text-xs font-bold tracking-[0.3em] uppercase">RSVP</h3>
            <p class="text-sm text-gray-600">Kindly respond by {{ formatDate(weddingEvent.rsvp_deadline) }}</p>
          </div>
          
          <button 
            @click="showRSVPModal = true"
            class="w-full py-4 bg-black text-white text-sm font-bold tracking-[0.3em] uppercase hover:bg-gray-800 transition-colors"
          >
            Confirm Attendance
          </button>
        </div>
      </div>
    </section>

    <!-- Brutalist Footer -->
    <footer class="border-t-4 border-black py-8">
      <div class="text-center">
        <p class="text-xs font-bold tracking-[0.3em] uppercase">{{ formatDate(weddingEvent.wedding_date) }}</p>
      </div>
    </footer>

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
  })
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
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;500;600;700;800;900&display=swap');

.font-black {
  font-weight: 900;
}

.font-light {
  font-weight: 300;
}
</style>
