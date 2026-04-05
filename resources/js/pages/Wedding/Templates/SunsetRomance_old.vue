<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
  </Head>

  <div class="min-h-screen relative overflow-x-hidden">
    <!-- Animated Gradient Background -->
    <div class="fixed inset-0 bg-gradient-to-br from-pink-100 via-rose-50 to-orange-50">
      <div class="absolute inset-0 bg-gradient-to-tr from-orange-200/20 via-pink-200/20 to-rose-200/20 animate-pulse"></div>
    </div>
    
    <!-- Diagonal Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
      <!-- Diagonal Background Layers -->
      <div class="absolute inset-0">
        <div class="absolute -top-48 -left-48 w-96 h-96 bg-gradient-to-br from-pink-300 to-rose-400 rounded-full blur-3xl opacity-30 animate-pulse"></div>
        <div class="absolute -bottom-48 -right-48 w-80 h-80 bg-gradient-to-br from-orange-300 to-pink-400 rounded-full blur-3xl opacity-30 animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-gradient-to-br from-rose-300 to-orange-400 rounded-full blur-3xl opacity-30 animate-pulse" style="animation-delay: 2s;"></div>
      </div>
      
      <!-- Diagonal Content Container -->
      <div class="relative z-10 w-full max-w-7xl mx-auto px-8">
        <div class="grid md:grid-cols-2 gap-16 items-center">
          <!-- Left Content - Floating Card -->
          <div class="transform -rotate-3 hover:rotate-0 transition-transform duration-700">
            <div class="bg-white/80 backdrop-blur-md rounded-3xl p-12 shadow-2xl border border-pink-200">
              <div class="text-6xl mb-6 text-center animate-bounce">💕</div>
              <h1 class="text-6xl md:text-7xl font-serif mb-6 bg-gradient-to-r from-pink-600 to-rose-600 bg-clip-text text-transparent">
                {{ weddingEvent.groom_name }}
              </h1>
              <div class="text-5xl text-center mb-6 text-rose-400 font-serif">&</div>
              <h1 class="text-6xl md:text-7xl font-serif mb-8 bg-gradient-to-r from-rose-600 to-orange-600 bg-clip-text text-transparent">
                {{ weddingEvent.bride_name }}
              </h1>
              <p class="text-xl text-center text-rose-600 font-light tracking-wide">
                {{ formatDate(weddingEvent.wedding_date) }}
              </p>
            </div>
          </div>
          
          <!-- Right Content - Floating Image -->
          <div class="transform rotate-3 hover:rotate-0 transition-transform duration-700">
            <div class="relative group">
              <!-- Floating Frame -->
              <div class="absolute -inset-4 bg-gradient-to-br from-pink-300 to-rose-400 rounded-3xl opacity-60 blur-xl group-hover:blur-2xl transition-all duration-500"></div>
              <div class="relative bg-white/80 backdrop-blur-md p-4 rounded-3xl shadow-2xl border border-rose-200 overflow-hidden">
                <img 
                  :src="weddingEvent.hero_image" 
                  :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
                  class="w-full h-96 object-cover object-center rounded-2xl group-hover:scale-110 transition-transform duration-700"
                />
                <!-- Overlay Effect -->
                <div class="absolute inset-0 bg-gradient-to-t from-rose-600/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Floating Hearts Animation -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-20 left-10 text-4xl animate-bounce" style="animation-delay: 0.5s;">💕</div>
        <div class="absolute top-40 right-20 text-3xl animate-pulse" style="animation-delay: 1.5s;">💖</div>
        <div class="absolute bottom-32 left-32 text-4xl animate-bounce" style="animation-delay: 2.5s;">💕</div>
        <div class="absolute bottom-20 right-16 text-3xl animate-pulse" style="animation-delay: 3.5s;">💖</div>
      </div>
    </section>

    <!-- Floating Navigation -->
    <nav class="fixed top-8 left-1/2 transform -translate-x-1/2 z-50">
      <div class="bg-white/80 backdrop-blur-md rounded-full px-8 py-4 shadow-2xl border border-pink-200">
        <div class="flex items-center gap-6">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-pink-500 rounded-full animate-pulse"></div>
            <div class="w-2 h-2 bg-rose-500 rounded-full animate-pulse" style="animation-delay: 0.3s;"></div>
            <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse" style="animation-delay: 0.6s;"></div>
          </div>
          
          <a v-for="tab in navTabs" :key="tab.id"
             @click.prevent="activeTab = tab.id"
             :class="[
               'px-4 py-2 rounded-full text-sm font-serif tracking-wide cursor-pointer transition-all duration-300 relative group',
               activeTab === tab.id 
                 ? 'bg-gradient-to-r from-pink-500 to-rose-500 text-white shadow-lg transform scale-110' 
                 : 'text-rose-700 hover:bg-gradient-to-r hover:from-pink-100 hover:to-rose-100'
             ]">
            <span class="relative z-10">{{ tab.label }}</span>
            <div v-if="activeTab === tab.id" class="absolute inset-0 bg-gradient-to-r from-pink-400 to-rose-400 rounded-full animate-pulse"></div>
          </a>
          
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse" style="animation-delay: 0.9s;"></div>
            <div class="w-2 h-2 bg-rose-500 rounded-full animate-pulse" style="animation-delay: 1.2s;"></div>
            <div class="w-2 h-2 bg-pink-500 rounded-full animate-pulse" style="animation-delay: 1.5s;"></div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Diagonal Home Section with Floating Cards -->
    <section v-show="activeTab === 'home'" class="relative min-h-screen py-20">
      <!-- Diagonal Background -->
      <div class="absolute inset-0 bg-gradient-to-br from-pink-50 via-rose-50 to-orange-50">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-br from-pink-100 to-rose-100 transform -skew-y-6 origin-bottom"></div>
        <div class="absolute bottom-0 right-0 w-full h-1/2 bg-gradient-to-br from-rose-100 to-orange-100 transform skew-y-6 origin-top"></div>
      </div>
      
      <div class="relative z-10 max-w-7xl mx-auto px-8">
        <!-- Floating Countdown Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-20">
          <div v-for="(unit, index) in countdownUnits" :key="unit.key" 
               class="transform hover:scale-110 transition-all duration-500"
               :style="`animation: float ${3 + index * 0.5}s ease-in-out infinite;`">
            <div class="bg-white/80 backdrop-blur-md rounded-3xl p-6 shadow-2xl border border-pink-200 text-center group hover:bg-gradient-to-br hover:from-pink-100 hover:to-rose-100 transition-all duration-300">
              <div class="text-5xl font-bold bg-gradient-to-r from-pink-600 to-rose-600 bg-clip-text text-transparent group-hover:from-pink-700 group-hover:to-rose-700 transition-colors">
                {{ countdown[unit.key] }}
              </div>
              <div class="text-sm font-serif tracking-wider uppercase text-rose-600 mt-2">{{ unit.label }}</div>
            </div>
          </div>
        </div>

        <!-- Floating Invitation Cards -->
        <div class="grid md:grid-cols-3 gap-8 mb-20">
          <!-- Left Card - Tilted -->
          <div class="transform -rotate-6 hover:rotate-0 transition-transform duration-700">
            <div class="bg-white/80 backdrop-blur-md rounded-3xl p-8 shadow-2xl border border-rose-200 h-full">
              <div class="text-4xl mb-4 text-center animate-pulse">💕</div>
              <h3 class="text-2xl font-serif text-rose-800 mb-4 text-center">Join Us</h3>
              <p class="text-rose-700 leading-relaxed text-center">
                As we begin our forever journey together, surrounded by love and sunset dreams.
              </p>
            </div>
          </div>
          
          <!-- Center Card - Main Details -->
          <div class="transform rotate-3 hover:rotate-0 transition-transform duration-700">
            <div class="bg-gradient-to-br from-pink-100 to-rose-100 rounded-3xl p-8 shadow-2xl border border-pink-200 h-full">
              <div class="text-5xl mb-6 text-center animate-bounce">💖</div>
              <h3 class="text-3xl font-serif text-pink-800 mb-6 text-center">{{ formatDateFull(weddingEvent.wedding_date) }}</h3>
              <div class="text-center space-y-3">
                <p class="text-rose-700 font-serif text-lg">{{ weddingEvent.ceremony_time }}</p>
                <div class="w-16 h-px bg-rose-300 mx-auto"></div>
                <p class="text-pink-800 font-serif text-xl">{{ weddingEvent.venue_name }}</p>
                <p class="text-rose-600">{{ weddingEvent.venue_address }}</p>
              </div>
            </div>
          </div>
          
          <!-- Right Card - Tilted -->
          <div class="transform rotate-6 hover:rotate-0 transition-transform duration-700">
            <div class="bg-white/80 backdrop-blur-md rounded-3xl p-8 shadow-2xl border border-pink-200 h-full">
              <div class="text-4xl mb-4 text-center animate-pulse">💕</div>
              <h3 class="text-2xl font-serif text-rose-800 mb-4 text-center">RSVP</h3>
              <button 
                @click="showRSVPModal = true"
                class="w-full bg-gradient-to-r from-pink-500 to-rose-500 text-white px-6 py-4 rounded-full font-serif text-lg tracking-wide hover:from-pink-600 hover:to-rose-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105"
              >
                Confirm Your Presence
              </button>
            </div>
          </div>
        </div>
        
        <!-- Floating Hearts -->
        <div class="absolute inset-0 pointer-events-none">
          <div class="absolute top-20 left-10 text-4xl animate-bounce" style="animation-delay: 0.5s;">💕</div>
          <div class="absolute top-40 right-20 text-3xl animate-pulse" style="animation-delay: 1.5s;">💖</div>
          <div class="absolute bottom-32 left-32 text-4xl animate-bounce" style="animation-delay: 2.5s;">💕</div>
          <div class="absolute bottom-20 right-16 text-3xl animate-pulse" style="animation-delay: 3.5s;">💖</div>
        </div>
      </div>
    </section>

    <!-- Floating Story Cards -->
    <section v-show="activeTab === 'story'" class="relative min-h-screen py-20">
      <div class="relative z-10 max-w-7xl mx-auto px-8">
        <div class="text-center mb-16">
          <h2 class="text-5xl font-serif bg-gradient-to-r from-pink-600 to-rose-600 bg-clip-text text-transparent mb-4">Our Love Story</h2>
        </div>
        
        <!-- Floating Story Cards -->
        <div class="space-y-32">
          <!-- How We Met - Floating Left -->
          <div class="flex items-center justify-center">
            <div class="transform -rotate-12 hover:rotate-0 transition-all duration-700 max-w-md">
              <div class="bg-white/90 backdrop-blur-md rounded-3xl p-10 shadow-2xl border border-pink-200">
                <div class="text-5xl mb-6 text-center animate-pulse">💑</div>
                <h3 class="text-3xl font-serif text-rose-800 mb-6 text-center">How We Met</h3>
                <p class="text-rose-700 leading-relaxed text-lg">{{ weddingEvent.how_we_met }}</p>
              </div>
            </div>
          </div>
          
          <!-- Story Image - Floating Right -->
          <div class="flex items-center justify-center">
            <div class="transform rotate-12 hover:rotate-0 transition-all duration-700 max-w-md">
              <div class="relative group">
                <div class="absolute -inset-4 bg-gradient-to-br from-rose-300 to-pink-400 rounded-3xl opacity-60 blur-xl group-hover:blur-2xl transition-all duration-500"></div>
                <div class="relative bg-white/90 backdrop-blur-md p-4 rounded-3xl shadow-2xl border border-rose-200 overflow-hidden">
                  <img v-if="weddingEvent.story_image" :src="weddingEvent.story_image" alt="Our story" class="w-full h-64 object-cover object-center rounded-2xl group-hover:scale-110 transition-transform duration-700" />
                </div>
              </div>
            </div>
          </div>
          
          <!-- The Proposal - Floating Right -->
          <div class="flex items-center justify-center">
            <div class="transform rotate-12 hover:rotate-0 transition-all duration-700 max-w-md">
              <div class="bg-gradient-to-br from-rose-100 to-pink-100 rounded-3xl p-10 shadow-2xl border border-rose-200">
                <div class="text-5xl mb-6 text-center animate-bounce">💍</div>
                <h3 class="text-3xl font-serif text-pink-800 mb-6 text-center">The Proposal</h3>
                <p class="text-rose-700 leading-relaxed text-lg">{{ weddingEvent.proposal_story }}</p>
              </div>
            </div>
          </div>
          
          <!-- Gallery Images - Floating Grid -->
          <div v-if="galleryImages.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            <div v-for="(image, index) in galleryImages" :key="index" 
                 class="transform hover:rotate-0 transition-all duration-500"
                 :class="index % 2 === 0 ? '-rotate-6' : 'rotate-6'">
              <div class="relative group">
                <div class="absolute -inset-2 bg-gradient-to-br from-pink-300 to-rose-400 rounded-2xl opacity-0 group-hover:opacity-60 blur-xl transition-all duration-500"></div>
                <div class="relative bg-white/90 backdrop-blur-md p-2 rounded-2xl shadow-2xl border border-rose-200 overflow-hidden">
                  <img :src="image.url" :alt="`Gallery ${index + 1}`" class="w-full h-48 object-cover object-center rounded-xl group-hover:scale-110 transition-transform duration-700" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Floating Program Cards -->
    <section v-show="activeTab === 'program'" class="relative min-h-screen py-20">
      <div class="relative z-10 max-w-7xl mx-auto px-8">
        <div class="text-center mb-16">
          <h2 class="text-5xl font-serif bg-gradient-to-r from-rose-600 to-orange-600 bg-clip-text text-transparent mb-4">Celebration Timeline</h2>
        </div>
        
        <!-- Floating Timeline Cards -->
        <div class="space-y-24">
          <!-- Ceremony Card -->
          <div class="flex items-center justify-center">
            <div class="transform -rotate-6 hover:rotate-0 transition-all duration-700 max-w-md w-full">
              <div class="bg-white/90 backdrop-blur-md rounded-3xl p-10 shadow-2xl border border-rose-200">
                <div class="text-center mb-6">
                  <div class="w-20 h-20 bg-gradient-to-br from-pink-400 to-rose-500 rounded-full flex items-center justify-center text-white text-2xl font-serif mx-auto mb-4 shadow-xl">
                    🌸
                  </div>
                  <div class="text-3xl font-bold text-rose-700 mb-2">{{ weddingEvent.ceremony_time }}</div>
                </div>
                <h3 class="text-2xl font-serif text-rose-800 mb-4 text-center">Wedding Ceremony</h3>
                <p class="text-rose-600 text-center mb-2">{{ weddingEvent.venue_name }}</p>
                <p class="text-rose-500 text-center">{{ weddingEvent.venue_address }}</p>
              </div>
            </div>
          </div>
          
          <!-- Reception Card -->
          <div class="flex items-center justify-center">
            <div class="transform rotate-6 hover:rotate-0 transition-all duration-700 max-w-md w-full">
              <div class="bg-gradient-to-br from-rose-100 to-orange-100 rounded-3xl p-10 shadow-2xl border border-orange-200">
                <div class="text-center mb-6">
                  <div class="w-20 h-20 bg-gradient-to-br from-rose-400 to-orange-500 rounded-full flex items-center justify-center text-white text-2xl font-serif mx-auto mb-4 shadow-xl">
                    🎉
                  </div>
                  <div class="text-3xl font-bold text-orange-700 mb-2">{{ weddingEvent.reception_time }}</div>
                </div>
                <h3 class="text-2xl font-serif text-orange-800 mb-4 text-center">Sunset Reception</h3>
                <p class="text-orange-600 text-center mb-2">{{ weddingEvent.reception_venue }}</p>
                <p class="text-orange-500 text-center">{{ weddingEvent.reception_address }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Floating Location -->
    <section v-show="activeTab === 'location'" class="relative min-h-screen py-20">
      <div class="relative z-10 max-w-7xl mx-auto px-8">
        <div class="text-center mb-16">
          <h2 class="text-5xl font-serif bg-gradient-to-r from-orange-600 to-pink-600 bg-clip-text text-transparent mb-4">Find Our Paradise</h2>
        </div>
        
        <!-- Floating Location Card -->
        <div class="flex items-center justify-center mb-16">
          <div class="transform rotate-3 hover:rotate-0 transition-all duration-700 max-w-2xl w-full">
            <div class="bg-white/90 backdrop-blur-md rounded-3xl p-12 shadow-2xl border border-orange-200">
              <div class="text-center mb-8">
                <div class="text-6xl mb-6 animate-pulse">🌅</div>
                <h3 class="text-3xl font-serif text-orange-800 mb-4">{{ weddingEvent.venue_name }}</h3>
                <p class="text-orange-600 text-lg">{{ weddingEvent.venue_address }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Floating Map -->
        <div class="flex items-center justify-center">
          <div class="transform -rotate-3 hover:rotate-0 transition-all duration-700 max-w-4xl w-full">
            <div class="relative group cursor-pointer" @click="openGoogleMaps">
              <div class="absolute -inset-4 bg-gradient-to-br from-orange-300 to-pink-400 rounded-3xl opacity-60 blur-xl group-hover:blur-2xl transition-all duration-500"></div>
              <div class="relative bg-white/90 backdrop-blur-md p-4 rounded-3xl shadow-2xl border border-orange-200 overflow-hidden">
                <div class="h-96 bg-orange-100 rounded-2xl flex items-center justify-center">
                  <div class="text-center">
                    <div class="text-6xl mb-4">📍</div>
                    <p class="text-2xl font-serif text-orange-700">Click to View Map</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
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
import { ref, onMounted, onUnmounted } from 'vue'
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

const formatDateFull = (date) => {
  return new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}

const openGoogleMaps = () => {
  const address = encodeURIComponent(props.weddingEvent.venue_address)
  window.open(`https://www.google.com/maps/search/?api=1&query=${address}`, '_blank')
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

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
}
</style>
