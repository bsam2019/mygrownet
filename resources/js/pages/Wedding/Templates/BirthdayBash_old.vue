<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
  </Head>

  <div class="min-h-screen bg-gradient-to-br from-purple-100 via-pink-100 to-yellow-100">
    <!-- Playful Header with Balloons -->
    <header class="relative bg-gradient-to-r from-purple-600 via-pink-500 to-yellow-500 text-white overflow-hidden">
      <!-- Floating Balloons Background -->
      <div class="absolute inset-0 opacity-20">
        <div class="absolute top-4 left-8 text-6xl animate-bounce">🎈</div>
        <div class="absolute top-12 right-16 text-5xl animate-bounce" style="animation-delay: 0.2s;">🎉</div>
        <div class="absolute bottom-8 left-20 text-7xl animate-bounce" style="animation-delay: 0.4s;">🎂</div>
        <div class="absolute bottom-4 right-12 text-6xl animate-bounce" style="animation-delay: 0.6s;">🎈</div>
      </div>
      
      <div class="max-w-5xl mx-auto px-6 py-16 text-center relative z-10">
        <div class="text-7xl mb-6 animate-pulse">🎉</div>
        <h1 class="text-6xl md:text-8xl font-bold mb-4 drop-shadow-lg">
          {{ celebrationEvent.celebrant_name }}
        </h1>
        <p class="text-3xl md:text-4xl mb-6 font-light">is turning {{ celebrationEvent.age }}!</p>
        <div class="text-7xl mb-4 animate-pulse">🎂</div>
        <p class="text-xl tracking-wider">
          {{ formatDate(celebrationEvent.event_date) }}
        </p>
      </div>
      
      <!-- Confetti Wave -->
      <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" fill="none" class="w-full">
          <path d="M0 0L60 10C120 20 240 40 360 46.7C480 53 600 47 720 43.3C840 40 960 40 1080 46.7C1200 53 1320 67 1380 73.3L1440 80V120H0V0Z" fill="white"/>
        </svg>
      </div>
    </header>

    <!-- Fun Navigation -->
    <nav class="sticky top-0 z-40 bg-white/95 backdrop-blur-sm shadow-md">
      <div class="max-w-5xl mx-auto px-6">
        <div class="flex justify-center items-center py-4 gap-4 flex-wrap">
          <button v-for="tab in navTabs" :key="tab.id"
             @click="activeTab = tab.id"
             :class="[
               'px-6 py-2 rounded-full text-sm font-medium uppercase cursor-pointer transition-all transform hover:scale-105',
               activeTab === tab.id 
                 ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' 
                 : 'bg-purple-100 text-purple-700 hover:bg-purple-200'
             ]">
            {{ tab.icon }} {{ tab.label }}
          </button>
        </div>
      </div>
    </nav>

    <!-- Home Section -->
    <section v-show="activeTab === 'home'" class="py-16">
      <div class="max-w-5xl mx-auto px-6">
        <!-- Hero Image with Fun Border -->
        <div class="relative mb-16">
          <div class="absolute -inset-4 bg-gradient-to-r from-purple-400 via-pink-400 to-yellow-400 rounded-3xl transform rotate-2"></div>
          <div class="relative bg-white p-3 rounded-3xl shadow-2xl transform -rotate-1">
            <img 
              :src="celebrationEvent.hero_image" 
              :alt="celebrationEvent.celebrant_name"
              class="w-full h-[500px] object-cover object-[center_30%] rounded-2xl"
            />
          </div>
          <div class="absolute -top-8 -right-8 text-8xl animate-spin-slow">🎈</div>
          <div class="absolute -bottom-8 -left-8 text-8xl">🎁</div>
        </div>

        <!-- Countdown with Party Theme -->
        <div class="bg-gradient-to-r from-purple-200 via-pink-200 to-yellow-200 rounded-3xl p-8 mb-16 shadow-xl border-4 border-white">
          <div class="text-center mb-6">
            <span class="text-5xl animate-bounce">🎊</span>
            <p class="text-3xl font-bold text-purple-900 mt-3">Party Countdown!</p>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="unit in countdownUnits" :key="unit.key" class="text-center">
              <div class="bg-white rounded-2xl p-6 shadow-lg border-4 border-purple-300 transform hover:scale-110 transition-transform">
                <div class="text-5xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                  {{ countdown[unit.key] }}
                </div>
                <div class="text-sm tracking-wider uppercase text-purple-700 mt-2 font-semibold">{{ unit.label }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Party Details -->
        <div class="bg-white rounded-3xl p-10 shadow-xl mb-12 border-4 border-purple-200">
          <div class="text-center mb-8">
            <div class="text-6xl mb-4">🎉 🎂 🎉</div>
            <h2 class="text-4xl font-bold text-purple-900 mb-4">You're Invited!</h2>
            <p class="text-xl text-gray-700 leading-relaxed max-w-2xl mx-auto">
              {{ celebrationEvent.invitation_message || `Join us for an unforgettable celebration as ${celebrationEvent.celebrant_name} turns ${celebrationEvent.age}!` }}
            </p>
          </div>
          
          <div class="grid md:grid-cols-2 gap-8 max-w-3xl mx-auto">
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-6 border-2 border-purple-200">
              <div class="text-4xl mb-3 text-center">📅</div>
              <h3 class="text-xl font-bold text-purple-900 mb-2 text-center">When</h3>
              <p class="text-lg text-gray-700 text-center">{{ formatDateFull(celebrationEvent.event_date) }}</p>
              <p class="text-purple-600 font-medium text-center mt-1">{{ celebrationEvent.event_time }}</p>
            </div>
            
            <div class="bg-gradient-to-br from-pink-50 to-yellow-50 rounded-2xl p-6 border-2 border-pink-200">
              <div class="text-4xl mb-3 text-center">📍</div>
              <h3 class="text-xl font-bold text-pink-900 mb-2 text-center">Where</h3>
              <p class="text-lg text-gray-700 text-center">{{ celebrationEvent.venue_name }}</p>
              <p class="text-pink-600 text-sm text-center mt-1">{{ celebrationEvent.venue_address }}</p>
            </div>
          </div>
          
          <div class="text-center mt-10">
            <div class="text-5xl mb-4">🎈 🎁 🎈</div>
            <button 
              @click="showRSVPModal = true"
              class="bg-gradient-to-r from-purple-600 via-pink-600 to-yellow-500 text-white px-16 py-5 rounded-full text-xl font-bold uppercase hover:shadow-2xl transform hover:scale-110 transition-all"
            >
              🎉 RSVP Now!
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Gallery Section -->
    <section v-show="activeTab === 'gallery'" class="py-16">
      <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-12">
          <div class="text-6xl mb-4">📸</div>
          <h2 class="text-4xl font-bold text-purple-900">Memory Lane</h2>
        </div>
        
        <div v-if="galleryImages.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-6">
          <div v-for="(image, index) in galleryImages" :key="index" 
               class="relative group cursor-pointer transform hover:scale-105 transition-transform">
            <div class="absolute -inset-1 bg-gradient-to-r from-purple-400 to-pink-400 rounded-2xl opacity-75 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative h-64 rounded-2xl overflow-hidden border-4 border-white shadow-lg">
              <img :src="image.url" :alt="`Memory ${index + 1}`" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Details Section -->
    <section v-show="activeTab === 'details'" class="py-16">
      <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-12">
          <div class="text-6xl mb-4">ℹ️</div>
          <h2 class="text-4xl font-bold text-purple-900">Party Details</h2>
        </div>
        
        <div class="space-y-6">
          <div class="bg-white rounded-3xl p-8 shadow-xl border-4 border-purple-200">
            <div class="flex items-start gap-6">
              <div class="w-20 h-20 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-4xl flex-shrink-0">
                🎵
              </div>
              <div>
                <h3 class="text-2xl font-bold text-purple-900 mb-2">Dress Code</h3>
                <p class="text-gray-700 text-lg">{{ celebrationEvent.dress_code || 'Come as you are - casual and comfortable!' }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-3xl p-8 shadow-xl border-4 border-pink-200">
            <div class="flex items-start gap-6">
              <div class="w-20 h-20 bg-gradient-to-br from-pink-400 to-yellow-500 rounded-full flex items-center justify-center text-4xl flex-shrink-0">
                🍰
              </div>
              <div>
                <h3 class="text-2xl font-bold text-pink-900 mb-2">Food & Drinks</h3>
                <p class="text-gray-700 text-lg">{{ celebrationEvent.food_info || 'Delicious food and refreshments will be served!' }}</p>
              </div>
            </div>
          </div>
          
          <div v-if="celebrationEvent.gift_registry" class="bg-white rounded-3xl p-8 shadow-xl border-4 border-yellow-200">
            <div class="flex items-start gap-6">
              <div class="w-20 h-20 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center text-4xl flex-shrink-0">
                🎁
              </div>
              <div>
                <h3 class="text-2xl font-bold text-yellow-900 mb-2">Gift Registry</h3>
                <p class="text-gray-700 text-lg">{{ celebrationEvent.gift_registry }}</p>
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
          <div class="text-6xl mb-4">🗺️</div>
          <h2 class="text-4xl font-bold text-purple-900">Find the Party!</h2>
        </div>
        
        <div class="bg-white rounded-3xl p-8 shadow-xl border-4 border-purple-200 text-center mb-8">
          <div class="text-5xl mb-4">📍</div>
          <h3 class="text-2xl font-bold text-purple-900 mb-3">{{ celebrationEvent.venue_name }}</h3>
          <p class="text-gray-700 text-lg">{{ celebrationEvent.venue_address }}</p>
        </div>
        
        <div class="w-full h-96 rounded-3xl border-4 border-purple-300 shadow-xl overflow-hidden mb-6 relative group cursor-pointer"
             @click="openGoogleMaps">
          <div class="absolute inset-0 flex items-center justify-center bg-purple-900/50 group-hover:bg-purple-900/70 transition-colors">
            <div class="text-center text-white">
              <div class="text-6xl mb-4">🎈</div>
              <p class="text-2xl font-bold">Click to view map</p>
            </div>
          </div>
        </div>
        
        <div class="text-center">
          <a 
            :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(celebrationEvent.venue_address)}`"
            target="_blank"
            class="inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white px-12 py-4 rounded-full text-lg font-bold uppercase hover:shadow-2xl transition-all transform hover:scale-105"
          >
            🗺️ Get Directions
          </a>
        </div>
      </div>
    </section>

    <!-- RSVP Modal -->
    <RSVPModal 
      :isOpen="showRSVPModal" 
      :weddingEventId="celebrationEvent.id"
      @close="showRSVPModal = false" 
      @submitted="onRSVPSubmitted"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import RSVPModal from '@/components/Wedding/RSVPModal.vue'

const props = defineProps({
  celebrationEvent: Object,
  template: Object,
  galleryImages: Array,
  ogMeta: Object,
  isPreview: Boolean,
})

const activeTab = ref('home')
const showRSVPModal = ref(false)
const countdown = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 })

const navTabs = [
  { id: 'home', label: 'Home', icon: '🎉' },
  { id: 'gallery', label: 'Photos', icon: '📸' },
  { id: 'details', label: 'Details', icon: 'ℹ️' },
  { id: 'location', label: 'Location', icon: '📍' },
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

const formatDateFull = (date) => {
  return new Date(date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
}

const updateCountdown = () => {
  const now = new Date().getTime()
  const eventDate = new Date(props.celebrationEvent.event_date).getTime()
  const distance = eventDate - now

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

const openGoogleMaps = () => {
  const address = encodeURIComponent(props.celebrationEvent.venue_address)
  window.open(`https://www.google.com/maps/search/?api=1&query=${address}`, '_blank')
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

<style scoped>
@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin-slow {
  animation: spin-slow 20s linear infinite;
}
</style>
