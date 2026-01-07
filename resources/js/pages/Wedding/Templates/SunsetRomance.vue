<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
  </Head>

  <div class="min-h-screen bg-gradient-to-b from-orange-50 via-pink-50 to-rose-50">
    <!-- Gradient Header -->
    <header class="relative bg-gradient-to-r from-orange-400 via-pink-500 to-rose-500 text-white">
      <div class="absolute inset-0 bg-black/10"></div>
      <div class="relative max-w-6xl mx-auto px-6 py-12">
        <div class="text-center">
          <div class="text-5xl mb-4">💕</div>
          <h1 class="text-5xl md:text-6xl font-script mb-3" style="font-family: 'Brush Script MT', cursive;">
            {{ weddingEvent.groom_name }} & {{ weddingEvent.bride_name }}
          </h1>
          <div class="flex items-center justify-center gap-3 mb-6">
            <div class="h-px w-16 bg-white/50"></div>
            <p class="text-sm tracking-[0.3em] uppercase">
              {{ formatDate(weddingEvent.wedding_date) }}
            </p>
            <div class="h-px w-16 bg-white/50"></div>
          </div>
          
          <!-- Floating Navigation -->
          <nav class="inline-flex items-center gap-6 bg-white/20 backdrop-blur-sm rounded-full px-8 py-3 text-sm">
            <a v-for="tab in navTabs" :key="tab.id" 
               @click.prevent="activeTab = tab.id"
               :class="activeTab === tab.id ? 'text-white font-medium' : 'text-white/70 hover:text-white'"
               class="cursor-pointer transition-colors">
              {{ tab.label }}
            </a>
          </nav>
        </div>
      </div>
      <!-- Wave Bottom -->
      <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
          <path d="M0 0L60 10C120 20 240 40 360 46.7C480 53 600 47 720 43.3C840 40 960 40 1080 46.7C1200 53 1320 67 1380 73.3L1440 80V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0V0Z" fill="white"/>
        </svg>
      </div>
    </header>

    <!-- Hero Section with Romantic Layout -->
    <section v-show="activeTab === 'home'" class="py-12 -mt-1">
      <div class="max-w-5xl mx-auto px-6">
        <!-- Romantic Quote -->
        <div class="text-center mb-12">
          <p class="text-2xl md:text-3xl font-light text-gray-700 italic leading-relaxed">
            "Love is not just looking at each other,<br/>it's looking in the same direction"
          </p>
          <div class="mt-4 text-pink-400">❤️</div>
        </div>

        <!-- Image with Gradient Overlay -->
        <div class="relative mb-12 rounded-3xl overflow-hidden shadow-2xl">
          <div class="absolute inset-0 bg-gradient-to-t from-pink-900/50 via-transparent to-transparent z-10"></div>
          <img 
            :src="weddingEvent.hero_image" 
            :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
            class="w-full h-[600px] object-cover object-center"
          />
          <!-- Floating Info Card -->
          <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 bg-white/95 backdrop-blur-sm rounded-2xl p-6 shadow-xl max-w-md w-full mx-4">
            <div class="text-center">
              <h3 class="text-xl font-medium text-gray-900 mb-2">{{ weddingEvent.venue_name }}</h3>
              <p class="text-gray-600 text-sm mb-1">{{ weddingEvent.venue_address }}</p>
              <p class="text-pink-600 font-medium">{{ weddingEvent.ceremony_time }}</p>
            </div>
          </div>
        </div>

        <!-- Countdown with Gradient Cards -->
        <div v-if="countdown" class="grid grid-cols-4 gap-4 mb-12">
          <div v-for="unit in countdownUnits" :key="unit.key" 
               class="bg-gradient-to-br from-orange-100 via-pink-100 to-rose-100 rounded-2xl p-6 text-center shadow-lg">
            <div class="text-4xl font-light bg-gradient-to-r from-orange-600 to-pink-600 bg-clip-text text-transparent">
              {{ countdown[unit.key] }}
            </div>
            <div class="text-xs tracking-wider uppercase text-gray-500 mt-2">{{ unit.label }}</div>
          </div>
        </div>

        <!-- Love Story -->
        <div class="bg-white rounded-3xl p-8 md:p-12 shadow-xl mb-8">
          <div class="text-center mb-8">
            <div class="text-3xl mb-3">💑</div>
            <h2 class="text-3xl font-light text-gray-800">Our Love Story</h2>
          </div>
          <p class="text-gray-600 leading-relaxed text-center max-w-2xl mx-auto">
            {{ weddingEvent.how_we_met }}
          </p>
        </div>

        <!-- RSVP Button -->
        <div class="text-center">
          <button 
            @click="showRSVPModal = true"
            class="px-16 py-5 bg-gradient-to-r from-orange-500 via-pink-500 to-rose-500 text-white text-lg rounded-full hover:shadow-2xl hover:scale-105 transition-all duration-300 font-medium"
          >
            ✨ RSVP with Love
          </button>
        </div>
      </div>
    </section>

    <!-- Our Story Section -->
    <section v-show="activeTab === 'story'" class="py-16">
      <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-12">
          <div class="text-5xl mb-4">💕</div>
          <h2 class="text-5xl bg-gradient-to-r from-orange-600 to-pink-600 bg-clip-text text-transparent font-bold mb-2">
            Our Love Story
          </h2>
          <div class="text-5xl mt-4">✨</div>
        </div>
        
        <div class="space-y-12">
          <!-- How We Met -->
          <div class="bg-gradient-to-br from-orange-50 to-pink-50 rounded-[3rem] p-10 shadow-xl border-2 border-pink-200">
            <div class="grid md:grid-cols-2 gap-8 items-center">
              <div>
                <div class="text-4xl mb-4 text-center">💑</div>
                <h3 class="text-3xl bg-gradient-to-r from-orange-600 to-pink-600 bg-clip-text text-transparent font-semibold mb-4 text-center">
                  How We Met
                </h3>
                <p class="text-gray-700 leading-relaxed text-lg">{{ weddingEvent.how_we_met }}</p>
              </div>
              <div class="h-64 rounded-3xl border-2 border-pink-300 overflow-hidden">
                <img v-if="weddingEvent.story_image" :src="weddingEvent.story_image" alt="Our story" class="w-full h-full object-cover object-center" />
              </div>
            </div>
          </div>
          
          <!-- The Proposal -->
          <div class="bg-gradient-to-br from-pink-50 to-rose-50 rounded-[3rem] p-10 shadow-xl border-2 border-pink-200">
            <div class="grid md:grid-cols-2 gap-8 items-center">
              <div class="order-2 md:order-1 h-64 rounded-3xl border-2 border-pink-300 overflow-hidden">
                <img v-if="galleryImages[0]" :src="galleryImages[0].url" alt="Proposal" class="w-full h-full object-cover object-center" />
              </div>
              <div class="order-1 md:order-2">
                <div class="text-4xl mb-4 text-center">💍</div>
                <h3 class="text-3xl bg-gradient-to-r from-pink-600 to-rose-600 bg-clip-text text-transparent font-semibold mb-4 text-center">
                  The Proposal
                </h3>
                <p class="text-gray-700 leading-relaxed text-lg">{{ weddingEvent.proposal_story }}</p>
              </div>
            </div>
          </div>
          
          <!-- Photo Gallery -->
          <div v-if="galleryImages.length > 0">
            <div class="text-center mb-8">
              <div class="text-5xl mb-4">📸</div>
              <h3 class="text-3xl bg-gradient-to-r from-orange-600 to-pink-600 bg-clip-text text-transparent font-semibold">
                Our Journey Together
              </h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
              <div v-for="(image, index) in galleryImages" :key="index" class="h-48 rounded-3xl border-2 border-pink-200 overflow-hidden hover:border-pink-400 transition-colors">
                <img :src="image.url" :alt="`Gallery ${index + 1}`" class="w-full h-full object-cover object-center hover:scale-110 transition-transform duration-300" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Program Section -->
    <section v-show="activeTab === 'program'" class="py-12">
      <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-12">
          <div class="text-4xl mb-3">📅</div>
          <h2 class="text-3xl font-light text-gray-800">Celebration Schedule</h2>
        </div>
        
        <div class="space-y-6">
          <div class="bg-gradient-to-r from-orange-50 to-pink-50 rounded-2xl p-8 shadow-lg border-l-4 border-pink-400">
            <div class="flex items-center gap-6">
              <div class="w-24 h-24 bg-gradient-to-br from-orange-400 to-pink-500 rounded-full flex items-center justify-center text-white text-xl font-medium shadow-lg">
                {{ weddingEvent.ceremony_time?.split(':')[0] }}:{{ weddingEvent.ceremony_time?.split(':')[1]?.split(' ')[0] }}
              </div>
              <div class="flex-1">
                <h3 class="text-2xl font-medium text-gray-900 mb-1">Wedding Ceremony</h3>
                <p class="text-gray-600">{{ weddingEvent.venue_name }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ weddingEvent.venue_address }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-gradient-to-r from-pink-50 to-rose-50 rounded-2xl p-8 shadow-lg border-l-4 border-rose-400">
            <div class="flex items-center gap-6">
              <div class="w-24 h-24 bg-gradient-to-br from-pink-400 to-rose-500 rounded-full flex items-center justify-center text-white text-xl font-medium shadow-lg">
                {{ weddingEvent.reception_time?.split(':')[0] }}:{{ weddingEvent.reception_time?.split(':')[1]?.split(' ')[0] }}
              </div>
              <div class="flex-1">
                <h3 class="text-2xl font-medium text-gray-900 mb-1">Reception & Celebration</h3>
                <p class="text-gray-600">{{ weddingEvent.reception_venue }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ weddingEvent.reception_address }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Location Section -->
    <section v-show="activeTab === 'location'" class="py-16">
      <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-12">
          <div class="text-5xl mb-4">📍</div>
          <h2 class="text-5xl bg-gradient-to-r from-orange-600 to-pink-600 bg-clip-text text-transparent font-bold mb-2">
            Where Love Blooms
          </h2>
          <div class="text-5xl mt-4">🌅</div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-50 to-pink-50 rounded-[3rem] p-10 shadow-xl border-2 border-pink-200 text-center mb-10">
          <div class="text-5xl mb-6">💖</div>
          <h3 class="text-3xl bg-gradient-to-r from-orange-600 to-pink-600 bg-clip-text text-transparent font-semibold mb-4">
            {{ weddingEvent.venue_name }}
          </h3>
          <p class="text-xl text-gray-700 mb-2">{{ weddingEvent.venue_address }}</p>
          <div class="mt-6 text-4xl">✨</div>
        </div>
        
        <div class="w-full h-96 rounded-[3rem] border-4 border-pink-300 shadow-xl overflow-hidden mb-8 relative group cursor-pointer"
             @click="openGoogleMaps">
          <div class="absolute inset-0 flex items-center justify-center bg-pink-900/50 group-hover:bg-pink-900/70 transition-colors">
            <div class="text-center text-white">
              <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <p class="text-lg font-semibold">💖 Click to view map</p>
            </div>
          </div>
        </div>
        
        <div class="text-center">
          <a 
            :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_address)}`"
            target="_blank"
            class="inline-block bg-gradient-to-r from-orange-500 via-pink-500 to-rose-500 text-white px-12 py-4 rounded-full text-lg tracking-wide uppercase hover:shadow-2xl transition-all transform hover:scale-105 font-semibold"
          >
            🗺️ Get Directions
          </a>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import RSVPModal from '@/components/Wedding/RSVPModal.vue'

const props = defineProps({
  weddingEvent: Object,
  template: Object,
  galleryImages: Array,
  ogMeta: Object,
  isPreview: Boolean,
})

const activeTab = ref('home')
const showRSVPModal = ref(false)
const countdown = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 })

const navTabs = [
  { id: 'home', label: 'Home' },
  { id: 'story', label: 'Our Story' },
  { id: 'program', label: 'Program' },
  { id: 'location', label: 'Location' },
]

const countdownUnits = [
  { key: 'days', label: 'Days' },
  { key: 'hours', label: 'Hours' },
  { key: 'minutes', label: 'Minutes' },
  { key: 'seconds', label: 'Seconds' },
]

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
}

const updateCountdown = () => {
  const now = new Date().getTime()
  const weddingDate = new Date(props.weddingEvent.wedding_date).getTime()
  const distance = weddingDate - now

  if (distance > 0) {
    countdown.value = {
      days: Math.floor(distance / (1000 * 60 * 60 * 24)),
      hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
      minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
      seconds: Math.floor((distance % (1000 * 60)) / 1000)
    }
  }
}

const onRSVPSubmitted = () => {
  showRSVPModal.value = false
}

let countdownInterval
onMounted(() => {
  updateCountdown()
  countdownInterval = setInterval(updateCountdown, 1000)
})

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval)
})
</script>
