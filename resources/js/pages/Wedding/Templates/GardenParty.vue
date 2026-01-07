<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
  </Head>

  <div class="min-h-screen bg-gradient-to-b from-green-50 via-emerald-50 to-green-50">
    <!-- Nature-Inspired Header -->
    <header class="relative bg-gradient-to-r from-emerald-700 via-green-600 to-emerald-700 text-white overflow-hidden">
      <!-- Leaf Pattern Background -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-4 left-4 text-6xl">🌿</div>
        <div class="absolute top-12 right-12 text-4xl">🍃</div>
        <div class="absolute bottom-8 left-16 text-5xl">🌱</div>
        <div class="absolute bottom-4 right-8 text-6xl">🌿</div>
      </div>
      
      <div class="max-w-5xl mx-auto px-6 py-12 text-center relative z-10">
        <div class="mb-4 text-green-200 text-2xl">🌸</div>
        <h1 class="text-5xl md:text-7xl mb-2 font-light tracking-wide">
          {{ weddingEvent.groom_name }}
        </h1>
        <div class="text-3xl text-green-300 my-3">&</div>
        <h1 class="text-5xl md:text-7xl mb-4 font-light tracking-wide">
          {{ weddingEvent.bride_name }}
        </h1>
        <div class="mt-4 text-green-200 text-2xl">🌸</div>
        <p class="mt-6 text-lg tracking-wider text-green-100">
          {{ formatDate(weddingEvent.wedding_date) }}
        </p>
      </div>
    </header>

    <!-- Organic Navigation -->
    <nav class="sticky top-0 z-40 bg-white/95 backdrop-blur-sm border-b-2 border-green-200 shadow-sm">
      <div class="max-w-5xl mx-auto px-6">
        <div class="flex justify-center items-center py-4 gap-6">
          <a v-for="tab in navTabs" :key="tab.id"
             @click.prevent="activeTab = tab.id"
             :class="[
               'px-4 py-2 rounded-full text-sm tracking-wide uppercase cursor-pointer transition-all',
               activeTab === tab.id 
                 ? 'bg-green-600 text-white shadow-md' 
                 : 'text-green-700 hover:bg-green-100'
             ]">
            {{ tab.label }}
          </a>
        </div>
      </div>
    </nav>

    <!-- Home Section -->
    <section v-show="activeTab === 'home'" class="py-16">
      <div class="max-w-5xl mx-auto px-6">
        <!-- Hero Image with Organic Border -->
        <div class="relative mb-16">
          <div class="absolute -inset-3 bg-gradient-to-br from-green-200 to-emerald-200 rounded-3xl transform rotate-1"></div>
          <div class="relative bg-white p-2 rounded-3xl shadow-xl">
            <img 
              :src="weddingEvent.hero_image" 
              :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
              class="w-full h-[500px] object-cover object-center rounded-2xl"
            />
          </div>
          <!-- Decorative Leaves -->
          <div class="absolute -top-6 -left-6 text-6xl">🌿</div>
          <div class="absolute -bottom-6 -right-6 text-6xl">🍃</div>
        </div>

        <!-- Nature-Inspired Countdown -->
        <div class="bg-gradient-to-r from-green-100 via-emerald-50 to-green-100 rounded-3xl p-8 mb-16 shadow-lg">
          <div class="text-center mb-6">
            <span class="text-3xl">🌱</span>
            <p class="text-2xl text-green-800 mt-2">Growing Together In</p>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="unit in countdownUnits" :key="unit.key" class="text-center">
              <div class="bg-white rounded-2xl p-6 shadow-md border-2 border-green-200 hover:border-green-400 transition-colors">
                <div class="text-4xl font-bold text-green-700">{{ countdown[unit.key] }}</div>
                <div class="text-sm tracking-wider uppercase text-green-600 mt-2">{{ unit.label }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Invitation Text with Botanical Theme -->
        <div class="text-center max-w-2xl mx-auto mb-12">
          <div class="mb-6 text-4xl">🌸 🌿 🌸</div>
          
          <p class="text-xl text-gray-700 leading-relaxed mb-8">
            Like two trees growing side by side, our roots have intertwined. Join us as we celebrate our love in the beauty of nature.
          </p>
          
          <div class="bg-white rounded-3xl p-8 shadow-lg border-2 border-green-200 mb-8">
            <div class="text-3xl mb-4">🌳</div>
            <p class="text-2xl text-green-800 mb-3">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
            <p class="text-green-600 mb-4">{{ weddingEvent.ceremony_time }}</p>
            <div class="h-px w-24 bg-green-300 mx-auto my-4"></div>
            <p class="text-xl text-green-700 mb-2">{{ weddingEvent.venue_name }}</p>
            <p class="text-green-600">{{ weddingEvent.venue_address }}</p>
            <div class="mt-4 text-2xl">🌿</div>
          </div>
          
          <div class="mb-8 text-4xl">🌸 🌿 🌸</div>
          
          <button 
            @click="showRSVPModal = true"
            class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-12 py-4 rounded-full text-lg tracking-wide uppercase hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105"
          >
            🌱 RSVP Now
          </button>
        </div>
      </div>
    </section>

    <!-- Our Story Section -->
    <section v-show="activeTab === 'story'" class="py-16">
      <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-12">
          <div class="text-4xl mb-4">🌸</div>
          <h2 class="text-4xl text-green-800 mb-2">Growing Together</h2>
          <div class="text-4xl mt-4">🌿</div>
        </div>
        
        <div class="space-y-12">
          <!-- How We Met -->
          <div class="bg-white rounded-3xl p-8 shadow-lg border-2 border-green-200">
            <div class="grid md:grid-cols-2 gap-8 items-center">
              <div>
                <div class="text-3xl mb-4 text-center">🌱</div>
                <h3 class="text-2xl text-green-800 mb-4 text-center">How We Met</h3>
                <p class="text-green-700 leading-relaxed">{{ weddingEvent.how_we_met }}</p>
              </div>
              <div class="h-64 rounded-2xl border-2 border-green-300 overflow-hidden">
                <img v-if="weddingEvent.story_image" :src="weddingEvent.story_image" alt="Our story" class="w-full h-full object-cover object-center" />
              </div>
            </div>
          </div>
          
          <!-- The Proposal -->
          <div class="bg-white rounded-3xl p-8 shadow-lg border-2 border-green-200">
            <div class="grid md:grid-cols-2 gap-8 items-center">
              <div class="order-2 md:order-1 h-64 rounded-2xl border-2 border-green-300 overflow-hidden">
                <img v-if="galleryImages[0]" :src="galleryImages[0].url" alt="Proposal" class="w-full h-full object-cover object-center" />
              </div>
              <div class="order-1 md:order-2">
                <div class="text-3xl mb-4 text-center">💍</div>
                <h3 class="text-2xl text-green-800 mb-4 text-center">The Proposal</h3>
                <p class="text-green-700 leading-relaxed">{{ weddingEvent.proposal_story }}</p>
              </div>
            </div>
          </div>
          
          <!-- Photo Gallery -->
          <div v-if="galleryImages.length > 0">
            <div class="text-center mb-8">
              <div class="text-4xl mb-4">📸</div>
              <h3 class="text-2xl text-green-800">Our Journey Together</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div v-for="(image, index) in galleryImages" :key="index" class="h-48 rounded-2xl border-2 border-green-200 overflow-hidden hover:border-green-400 transition-colors">
                <img :src="image.url" :alt="`Gallery ${index + 1}`" class="w-full h-full object-cover object-center hover:scale-110 transition-transform duration-300" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Program Section -->
    <section v-show="activeTab === 'program'" class="py-16">
      <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-12">
          <div class="text-4xl mb-4">🌿</div>
          <h2 class="text-4xl text-green-800 mb-2">Celebration Timeline</h2>
          <div class="text-4xl mt-4">🌿</div>
        </div>
        
        <div class="space-y-6">
          <div class="bg-white rounded-3xl p-8 shadow-lg border-2 border-green-200 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-6">
              <div class="w-24 h-24 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center text-white text-3xl shadow-lg">
                🌸
              </div>
              <div class="flex-1">
                <div class="text-sm text-green-600 mb-1">{{ weddingEvent.ceremony_time }}</div>
                <h3 class="text-2xl text-green-800 mb-2">Ceremony</h3>
                <p class="text-green-600">{{ weddingEvent.venue_name }}</p>
                <p class="text-sm text-gray-500">{{ weddingEvent.venue_address }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-3xl p-8 shadow-lg border-2 border-green-200 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-6">
              <div class="w-24 h-24 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white text-3xl shadow-lg">
                🎉
              </div>
              <div class="flex-1">
                <div class="text-sm text-green-600 mb-1">{{ weddingEvent.reception_time }}</div>
                <h3 class="text-2xl text-green-800 mb-2">Garden Reception</h3>
                <p class="text-green-600">{{ weddingEvent.reception_venue }}</p>
                <p class="text-sm text-gray-500">{{ weddingEvent.reception_address }}</p>
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
          <div class="text-4xl mb-4">🗺️</div>
          <h2 class="text-4xl text-green-800 mb-2">Find Us</h2>
          <div class="text-4xl mt-4">🌿</div>
        </div>
        
        <div class="bg-white rounded-3xl p-8 shadow-lg border-2 border-green-200 text-center mb-8">
          <div class="text-3xl mb-4">🌳</div>
          <h3 class="text-2xl text-green-800 mb-3">{{ weddingEvent.venue_name }}</h3>
          <p class="text-green-600 mb-2">{{ weddingEvent.venue_address }}</p>
          <div class="mt-4 text-2xl">🌿</div>
        </div>
        
        <div class="w-full h-96 rounded-3xl border-4 border-green-300 shadow-lg overflow-hidden mb-6 relative group cursor-pointer"
             @click="openGoogleMaps">
          <div class="absolute inset-0 flex items-center justify-center bg-green-900/50 group-hover:bg-green-900/70 transition-colors">
            <div class="text-center text-white">
              <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <p class="text-lg font-medium">🌿 Click to view map</p>
            </div>
          </div>
        </div>
        
        <div class="text-center">
          <a 
            :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_address)}`"
            target="_blank"
            class="inline-block bg-gradient-to-r from-green-600 to-emerald-600 text-white px-10 py-3 rounded-full tracking-wide uppercase hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg"
          >
            🌿 Get Directions
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

const formatDateFull = (date) => {
  return new Date(date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
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

const openGoogleMaps = () => {
  const address = encodeURIComponent(props.weddingEvent.venue_address)
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
