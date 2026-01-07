<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
  </Head>

  <div class="min-h-screen bg-gradient-to-b from-rose-50 via-white to-rose-50">
    <!-- Romantic Header -->
    <header class="relative bg-gradient-to-r from-rose-900 via-red-800 to-rose-900 text-white py-16">
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-8 left-12 text-6xl">💕</div>
        <div class="absolute top-16 right-16 text-5xl">❤️</div>
        <div class="absolute bottom-12 left-20 text-7xl">💑</div>
        <div class="absolute bottom-8 right-12 text-6xl">💕</div>
      </div>
      
      <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
        <div class="flex items-center justify-center mb-6">
          <div class="h-px w-20 bg-rose-400"></div>
          <div class="mx-4 text-rose-300 text-3xl">❤️</div>
          <div class="h-px w-20 bg-rose-400"></div>
        </div>
        
        <h1 class="font-serif text-5xl md:text-7xl mb-4">
          {{ celebrationEvent.couple_name }}
        </h1>
        
        <p class="text-3xl md:text-4xl mb-6 text-rose-200">
          {{ celebrationEvent.years_together }} Years of Love
        </p>
        
        <div class="flex items-center justify-center mb-6">
          <div class="h-px w-20 bg-rose-400"></div>
          <div class="mx-4 text-rose-300 text-3xl">❤️</div>
          <div class="h-px w-20 bg-rose-400"></div>
        </div>
        
        <p class="text-xl tracking-wider text-rose-100">
          {{ formatDate(celebrationEvent.event_date) }}
        </p>
      </div>
    </header>

    <!-- Elegant Navigation -->
    <nav class="sticky top-0 z-40 bg-white/95 backdrop-blur-sm border-b-2 border-rose-200 shadow-sm">
      <div class="max-w-4xl mx-auto px-6">
        <div class="flex justify-center items-center py-4 gap-8">
          <button v-for="tab in navTabs" :key="tab.id"
             @click="activeTab = tab.id"
             :class="[
               'font-serif text-sm tracking-wider uppercase cursor-pointer transition-all',
               activeTab === tab.id 
                 ? 'text-rose-800 font-semibold border-b-2 border-rose-600 pb-1' 
                 : 'text-gray-600 hover:text-rose-700'
             ]">
            {{ tab.label }}
          </button>
        </div>
      </div>
    </nav>

    <!-- Home Section -->
    <section v-show="activeTab === 'home'" class="py-16">
      <div class="max-w-4xl mx-auto px-6">
        <!-- Hero Image with Elegant Frame -->
        <div class="relative mb-16">
          <div class="absolute -inset-4 border-4 border-rose-300 opacity-50 rounded-lg"></div>
          <div class="absolute -inset-2 border border-rose-500 rounded-lg"></div>
          <img 
            :src="celebrationEvent.hero_image" 
            :alt="celebrationEvent.couple_name"
            class="w-full h-[500px] object-cover object-[center_30%] relative z-10 rounded-lg shadow-2xl"
          />
          <div class="absolute -top-6 -left-6 text-6xl">💕</div>
          <div class="absolute -bottom-6 -right-6 text-6xl">❤️</div>
        </div>

        <!-- Anniversary Quote -->
        <div class="text-center mb-12">
          <div class="flex items-center justify-center mb-6">
            <div class="h-px w-16 bg-rose-400"></div>
            <div class="mx-3 text-rose-500 text-2xl">❤️</div>
            <div class="h-px w-16 bg-rose-400"></div>
          </div>
          
          <p class="font-serif text-3xl text-gray-700 italic leading-relaxed max-w-2xl mx-auto">
            "{{ celebrationEvent.love_quote || 'Love is not about how many days, months, or years you have been together. Love is about how much you love each other every single day.' }}"
          </p>
          
          <div class="flex items-center justify-center mt-6">
            <div class="h-px w-16 bg-rose-400"></div>
            <div class="mx-3 text-rose-500 text-2xl">❤️</div>
            <div class="h-px w-16 bg-rose-400"></div>
          </div>
        </div>

        <!-- Countdown -->
        <div class="bg-gradient-to-r from-rose-50 via-white to-rose-50 border-2 border-rose-300 rounded-lg p-8 mb-16">
          <p class="text-center font-serif text-2xl text-rose-900 mb-6">Celebrating In</p>
          <div class="grid grid-cols-4 gap-4">
            <div v-for="unit in countdownUnits" :key="unit.key" class="text-center">
              <div class="bg-white border-2 border-rose-400 rounded-lg p-4 mb-2 shadow-md">
                <div class="text-4xl font-serif text-rose-900">{{ countdown[unit.key] }}</div>
              </div>
              <div class="text-xs tracking-wider uppercase text-rose-700">{{ unit.label }}</div>
            </div>
          </div>
        </div>

        <!-- Celebration Details -->
        <div class="bg-rose-50 border-l-4 border-r-4 border-rose-600 rounded-lg py-8 px-10 mb-12">
          <div class="text-center mb-8">
            <div class="text-5xl mb-4">💑</div>
            <h2 class="font-serif text-3xl text-rose-900 mb-4">Join Our Celebration</h2>
            <p class="text-lg text-gray-700 leading-relaxed max-w-2xl mx-auto">
              {{ celebrationEvent.invitation_message || `Please join us as we celebrate ${celebrationEvent.years_together} wonderful years of love, laughter, and memories together.` }}
            </p>
          </div>
          
          <div class="grid md:grid-cols-2 gap-6 max-w-2xl mx-auto mb-8">
            <div class="bg-white border-2 border-rose-300 rounded-lg p-6 text-center">
              <div class="text-3xl mb-2">📅</div>
              <h3 class="font-serif text-xl text-rose-900 mb-2">Date & Time</h3>
              <p class="text-gray-700">{{ formatDateFull(celebrationEvent.event_date) }}</p>
              <p class="text-rose-600 font-medium mt-1">{{ celebrationEvent.event_time }}</p>
            </div>
            
            <div class="bg-white border-2 border-rose-300 rounded-lg p-6 text-center">
              <div class="text-3xl mb-2">📍</div>
              <h3 class="font-serif text-xl text-rose-900 mb-2">Venue</h3>
              <p class="text-gray-700">{{ celebrationEvent.venue_name }}</p>
              <p class="text-rose-600 text-sm mt-1">{{ celebrationEvent.venue_address }}</p>
            </div>
          </div>
          
          <div class="text-center">
            <div class="flex items-center justify-center mb-6">
              <div class="h-px w-12 bg-rose-400"></div>
              <div class="mx-3 text-rose-500">❤️</div>
              <div class="h-px w-12 bg-rose-400"></div>
            </div>
            
            <button 
              @click="showRSVPModal = true"
              class="bg-gradient-to-r from-rose-700 to-red-600 text-white px-16 py-4 font-serif text-lg tracking-wider uppercase hover:from-rose-800 hover:to-red-700 transition-all shadow-lg rounded-full"
            >
              ❤️ RSVP with Love
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Journey Section -->
    <section v-show="activeTab === 'journey'" class="py-16">
      <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-12">
          <div class="text-5xl mb-4">💕</div>
          <h2 class="font-serif text-4xl text-rose-900">Our Journey Together</h2>
        </div>
        
        <div class="space-y-12">
          <!-- Timeline Item -->
          <div class="bg-white border-2 border-rose-300 rounded-lg p-8">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-16 h-16 bg-gradient-to-br from-rose-400 to-red-500 rounded-full flex items-center justify-center text-2xl text-white font-serif">
                {{ celebrationEvent.wedding_year || '❤️' }}
              </div>
              <div>
                <h3 class="font-serif text-2xl text-rose-900">The Beginning</h3>
                <p class="text-rose-600">Where it all started</p>
              </div>
            </div>
            <p class="text-gray-700 leading-relaxed">
              {{ celebrationEvent.how_we_met || 'Our love story began many years ago, and every day since has been an adventure filled with love, laughter, and countless precious memories.' }}
            </p>
          </div>
          
          <!-- Milestones -->
          <div v-if="celebrationEvent.milestones && celebrationEvent.milestones.length > 0">
            <div v-for="(milestone, index) in celebrationEvent.milestones" :key="index" 
                 class="bg-white border-2 border-rose-300 rounded-lg p-8">
              <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 bg-gradient-to-br from-rose-400 to-red-500 rounded-full flex items-center justify-center text-2xl">
                  {{ milestone.icon || '💕' }}
                </div>
                <div>
                  <h3 class="font-serif text-2xl text-rose-900">{{ milestone.title }}</h3>
                  <p class="text-rose-600">{{ milestone.year }}</p>
                </div>
              </div>
              <p class="text-gray-700 leading-relaxed">{{ milestone.description }}</p>
            </div>
          </div>
          
          <!-- Photo Gallery -->
          <div v-if="galleryImages.length > 0">
            <div class="text-center mb-8">
              <div class="text-4xl mb-4">📸</div>
              <h3 class="font-serif text-2xl text-rose-900">Cherished Memories</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div v-for="(image, index) in galleryImages" :key="index" 
                   class="h-48 border-2 border-rose-300 rounded-lg overflow-hidden hover:border-rose-500 transition-colors shadow-md">
                <img :src="image.url" :alt="`Memory ${index + 1}`" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Details Section -->
    <section v-show="activeTab === 'details'" class="py-16">
      <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-12">
          <div class="text-5xl mb-4">ℹ️</div>
          <h2 class="font-serif text-4xl text-rose-900">Celebration Details</h2>
        </div>
        
        <div class="space-y-6">
          <div class="bg-white border-2 border-rose-300 rounded-lg p-6">
            <div class="flex items-center gap-4 mb-3">
              <div class="w-16 h-16 bg-gradient-to-br from-rose-400 to-red-500 rounded-full flex items-center justify-center text-2xl">
                👔
              </div>
              <div>
                <h3 class="font-serif text-xl text-rose-900">Dress Code</h3>
                <p class="text-gray-700">{{ celebrationEvent.dress_code || 'Semi-formal attire' }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white border-2 border-rose-300 rounded-lg p-6">
            <div class="flex items-center gap-4 mb-3">
              <div class="w-16 h-16 bg-gradient-to-br from-rose-400 to-red-500 rounded-full flex items-center justify-center text-2xl">
                🍽️
              </div>
              <div>
                <h3 class="font-serif text-xl text-rose-900">Dinner & Reception</h3>
                <p class="text-gray-700">{{ celebrationEvent.food_info || 'A delightful dinner will be served' }}</p>
              </div>
            </div>
          </div>
          
          <div v-if="celebrationEvent.gift_registry" class="bg-white border-2 border-rose-300 rounded-lg p-6">
            <div class="flex items-center gap-4 mb-3">
              <div class="w-16 h-16 bg-gradient-to-br from-rose-400 to-red-500 rounded-full flex items-center justify-center text-2xl">
                🎁
              </div>
              <div>
                <h3 class="font-serif text-xl text-rose-900">Gifts</h3>
                <p class="text-gray-700">{{ celebrationEvent.gift_registry }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Location Section -->
    <section v-show="activeTab === 'location'" class="py-16">
      <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-12">
          <div class="text-5xl mb-4">📍</div>
          <h2 class="font-serif text-4xl text-rose-900">Venue</h2>
        </div>
        
        <div class="bg-white border-2 border-rose-300 rounded-lg p-8 text-center mb-8">
          <h3 class="font-serif text-2xl text-rose-900 mb-2">{{ celebrationEvent.venue_name }}</h3>
          <p class="text-gray-700 mb-4">{{ celebrationEvent.venue_address }}</p>
          <div class="flex items-center justify-center">
            <div class="h-px w-8 bg-rose-400"></div>
            <div class="mx-2 text-rose-500">❤️</div>
            <div class="h-px w-8 bg-rose-400"></div>
          </div>
        </div>
        
        <div class="w-full h-96 border-4 border-rose-400 rounded-lg overflow-hidden mb-6 relative group cursor-pointer"
             @click="openGoogleMaps">
          <div class="absolute inset-0 flex items-center justify-center bg-rose-900/50 group-hover:bg-rose-900/70 transition-colors">
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
            :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(celebrationEvent.venue_address)}`"
            target="_blank"
            class="inline-block bg-gradient-to-r from-rose-700 to-red-600 text-white px-10 py-3 font-serif tracking-wider uppercase hover:from-rose-800 hover:to-red-700 transition-all shadow-lg rounded-full"
          >
            Get Directions
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
  { id: 'home', label: 'Home' },
  { id: 'journey', label: 'Our Journey' },
  { id: 'details', label: 'Details' },
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
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');

.font-serif {
  font-family: 'Playfair Display', serif;
}
</style>
