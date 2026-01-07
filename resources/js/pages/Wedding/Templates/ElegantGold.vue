<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
  </Head>

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50">
    <!-- Ornate Header with Gold Accents -->
    <header class="relative bg-gradient-to-r from-amber-900 via-amber-800 to-amber-900 text-white py-8">
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-32 h-32 border-t-2 border-l-2 border-amber-400"></div>
        <div class="absolute top-0 right-0 w-32 h-32 border-t-2 border-r-2 border-amber-400"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 border-b-2 border-l-2 border-amber-400"></div>
        <div class="absolute bottom-0 right-0 w-32 h-32 border-b-2 border-r-2 border-amber-400"></div>
      </div>
      
      <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
        <!-- Ornamental Divider -->
        <div class="flex items-center justify-center mb-6">
          <div class="h-px w-16 bg-amber-400"></div>
          <div class="mx-4 text-amber-400">✦</div>
          <div class="h-px w-16 bg-amber-400"></div>
        </div>
        
        <h1 class="font-serif text-5xl md:text-7xl mb-4 tracking-wide">
          {{ weddingEvent.groom_name }} <span class="text-amber-400">&</span> {{ weddingEvent.bride_name }}
        </h1>
        
        <div class="flex items-center justify-center mb-6">
          <div class="h-px w-16 bg-amber-400"></div>
          <div class="mx-4 text-amber-400">✦</div>
          <div class="h-px w-16 bg-amber-400"></div>
        </div>
        
        <p class="text-lg tracking-[0.2em] uppercase text-amber-200">
          {{ formatDate(weddingEvent.wedding_date) }}
        </p>
      </div>
    </header>

    <!-- Navigation with Gold Border -->
    <nav class="sticky top-0 z-40 bg-white border-y-2 border-amber-300 shadow-sm">
      <div class="max-w-4xl mx-auto px-6">
        <div class="flex justify-center items-center py-4 gap-8">
          <a v-for="tab in navTabs" :key="tab.id"
             @click.prevent="activeTab = tab.id"
             :class="[
               'font-serif text-sm tracking-wider uppercase cursor-pointer transition-all',
               activeTab === tab.id 
                 ? 'text-amber-800 font-semibold border-b-2 border-amber-600 pb-1' 
                 : 'text-gray-600 hover:text-amber-700'
             ]">
            {{ tab.label }}
          </a>
        </div>
      </div>
    </nav>

    <!-- Home Section -->
    <section v-show="activeTab === 'home'" class="py-16">
      <div class="max-w-4xl mx-auto px-6">
        <!-- Hero Image with Gold Frame -->
        <div class="relative mb-16">
          <div class="absolute -inset-4 border-4 border-amber-400 opacity-50"></div>
          <div class="absolute -inset-2 border border-amber-600"></div>
          <img 
            :src="weddingEvent.hero_image" 
            :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
            class="w-full h-[500px] object-cover object-center relative z-10"
          />
        </div>

        <!-- Elegant Countdown -->
        <div class="bg-gradient-to-r from-amber-50 via-white to-amber-50 border-2 border-amber-300 rounded-lg p-8 mb-16">
          <p class="text-center font-serif text-2xl text-amber-900 mb-6">Counting Down to Forever</p>
          <div class="grid grid-cols-4 gap-4">
            <div v-for="unit in countdownUnits" :key="unit.key" class="text-center">
              <div class="bg-white border-2 border-amber-400 rounded-lg p-4 mb-2">
                <div class="text-4xl font-serif text-amber-900">{{ countdown[unit.key] }}</div>
              </div>
              <div class="text-xs tracking-wider uppercase text-amber-700">{{ unit.label }}</div>
            </div>
          </div>
        </div>

        <!-- Invitation Text -->
        <div class="text-center max-w-2xl mx-auto mb-12">
          <div class="flex items-center justify-center mb-6">
            <div class="h-px w-12 bg-amber-400"></div>
            <div class="mx-3 text-amber-600">❖</div>
            <div class="h-px w-12 bg-amber-400"></div>
          </div>
          
          <p class="font-serif text-xl text-gray-700 leading-relaxed mb-6">
            Together with our families, we joyfully invite you to witness and celebrate our union in marriage.
          </p>
          
          <div class="bg-amber-50 border-l-4 border-r-4 border-amber-600 py-6 px-8 mb-6">
            <p class="font-serif text-2xl text-amber-900 mb-2">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
            <p class="text-amber-700">{{ weddingEvent.ceremony_time }}</p>
            <p class="font-serif text-lg text-amber-800 mt-4">{{ weddingEvent.venue_name }}</p>
            <p class="text-sm text-amber-600">{{ weddingEvent.venue_address }}</p>
          </div>
          
          <div class="flex items-center justify-center mb-8">
            <div class="h-px w-12 bg-amber-400"></div>
            <div class="mx-3 text-amber-600">❖</div>
            <div class="h-px w-12 bg-amber-400"></div>
          </div>
          
          <button 
            @click="showRSVPModal = true"
            class="bg-gradient-to-r from-amber-700 to-amber-600 text-white px-12 py-4 font-serif text-lg tracking-wider uppercase hover:from-amber-800 hover:to-amber-700 transition-all shadow-lg"
          >
            Kindly RSVP
          </button>
        </div>
      </div>
    </section>

    <!-- Our Story Section -->
    <section v-show="activeTab === 'story'" class="py-16">
      <div class="max-w-4xl mx-auto px-6">
        <h2 class="font-serif text-4xl text-center text-amber-900 mb-12">Our Love Story</h2>
        
        <div class="space-y-16">
          <!-- How We Met -->
          <div class="bg-white border-2 border-amber-300 rounded-lg p-8">
            <div class="grid md:grid-cols-2 gap-8 items-center">
              <div>
                <div class="flex items-center justify-center mb-4">
                  <div class="h-px w-8 bg-amber-400"></div>
                  <div class="mx-2 text-amber-600">❖</div>
                  <div class="h-px w-8 bg-amber-400"></div>
                </div>
                <h3 class="font-serif text-2xl text-amber-900 mb-4 text-center">How We Met</h3>
                <p class="text-amber-800 leading-relaxed">{{ weddingEvent.how_we_met }}</p>
              </div>
              <div class="h-64 border-2 border-amber-400 rounded-lg overflow-hidden">
                <img v-if="weddingEvent.story_image" :src="weddingEvent.story_image" alt="Our story" class="w-full h-full object-cover object-center" />
              </div>
            </div>
          </div>
          
          <!-- The Proposal -->
          <div class="bg-white border-2 border-amber-300 rounded-lg p-8">
            <div class="grid md:grid-cols-2 gap-8 items-center">
              <div class="order-2 md:order-1 h-64 border-2 border-amber-400 rounded-lg overflow-hidden">
                <img v-if="galleryImages[0]" :src="galleryImages[0].url" alt="Proposal" class="w-full h-full object-cover object-center" />
              </div>
              <div class="order-1 md:order-2">
                <div class="flex items-center justify-center mb-4">
                  <div class="h-px w-8 bg-amber-400"></div>
                  <div class="mx-2 text-amber-600">❖</div>
                  <div class="h-px w-8 bg-amber-400"></div>
                </div>
                <h3 class="font-serif text-2xl text-amber-900 mb-4 text-center">The Proposal</h3>
                <p class="text-amber-800 leading-relaxed">{{ weddingEvent.proposal_story }}</p>
              </div>
            </div>
          </div>
          
          <!-- Photo Gallery -->
          <div v-if="galleryImages.length > 0">
            <div class="flex items-center justify-center mb-8">
              <div class="h-px w-12 bg-amber-400"></div>
              <div class="mx-3 text-amber-600">❖</div>
              <div class="h-px w-12 bg-amber-400"></div>
            </div>
            <h3 class="font-serif text-2xl text-amber-900 mb-8 text-center">Our Journey Together</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div v-for="(image, index) in galleryImages" :key="index" class="h-48 border-2 border-amber-300 rounded-lg overflow-hidden hover:border-amber-500 transition-colors">
                <img :src="image.url" :alt="`Gallery ${index + 1}`" class="w-full h-full object-cover object-center hover:scale-110 transition-transform duration-300" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Program Section -->
    <section v-show="activeTab === 'program'" class="py-16">
      <div class="max-w-3xl mx-auto px-6">
        <h2 class="font-serif text-4xl text-center text-amber-900 mb-12">Order of Events</h2>
        
        <div class="space-y-6">
          <div class="bg-white border-2 border-amber-300 rounded-lg p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4 mb-3">
              <div class="w-20 h-20 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center text-white font-serif text-xl">
                {{ weddingEvent.ceremony_time }}
              </div>
              <div class="flex-1">
                <h3 class="font-serif text-2xl text-amber-900 mb-1">Wedding Ceremony</h3>
                <p class="text-amber-700">{{ weddingEvent.venue_name }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white border-2 border-amber-300 rounded-lg p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4 mb-3">
              <div class="w-20 h-20 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center text-white font-serif text-xl">
                {{ weddingEvent.reception_time }}
              </div>
              <div class="flex-1">
                <h3 class="font-serif text-2xl text-amber-900 mb-1">Reception</h3>
                <p class="text-amber-700">{{ weddingEvent.reception_venue }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Location Section -->
    <section v-show="activeTab === 'location'" class="py-16">
      <div class="max-w-3xl mx-auto px-6">
        <h2 class="font-serif text-4xl text-center text-amber-900 mb-12">Venue</h2>
        
        <div class="bg-white border-2 border-amber-300 rounded-lg p-8 text-center mb-8">
          <h3 class="font-serif text-2xl text-amber-900 mb-2">{{ weddingEvent.venue_name }}</h3>
          <p class="text-amber-700 mb-4">{{ weddingEvent.venue_address }}</p>
          <div class="flex items-center justify-center">
            <div class="h-px w-8 bg-amber-400"></div>
            <div class="mx-2 text-amber-600">❖</div>
            <div class="h-px w-8 bg-amber-400"></div>
          </div>
        </div>
        
        <div class="w-full h-96 border-4 border-amber-400 rounded-lg overflow-hidden mb-6 relative group cursor-pointer"
             @click="openGoogleMaps">
          <div class="absolute inset-0 flex items-center justify-center bg-amber-900/50 group-hover:bg-amber-900/70 transition-colors">
            <div class="text-center text-white">
              <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <p class="text-lg font-serif">Click to view map</p>
            </div>
          </div>
        </div>
        
        <div class="text-center">
          <a 
            :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_address)}`"
            target="_blank"
            class="inline-block bg-gradient-to-r from-amber-700 to-amber-600 text-white px-10 py-3 font-serif tracking-wider uppercase hover:from-amber-800 hover:to-amber-700 transition-all shadow-lg"
          >
            Get Directions
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

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');

.font-serif {
  font-family: 'Playfair Display', serif;
}
</style>
