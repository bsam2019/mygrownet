<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
  </Head>

  <div class="min-h-screen bg-white">
    <!-- Minimal Header -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100">
      <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="text-sm tracking-[0.3em] uppercase text-gray-400">
          {{ formatDate(weddingEvent.wedding_date) }}
        </div>
        <nav class="hidden md:flex items-center gap-8 text-sm tracking-wider uppercase">
          <a v-for="tab in navTabs" :key="tab.id" 
             @click.prevent="activeTab = tab.id"
             :class="activeTab === tab.id ? 'text-gray-900 font-medium' : 'text-gray-400 hover:text-gray-600'"
             class="cursor-pointer transition-colors">
            {{ tab.label }}
          </a>
        </nav>
        <button @click="shareMenuOpen = !shareMenuOpen" class="text-gray-400 hover:text-gray-600">
          <ShareIcon class="h-5 w-5" aria-hidden="true" />
        </button>
      </div>
    </header>

    <!-- Hero Section -->
    <section v-show="activeTab === 'home'" class="pt-24 pb-16">
      <div class="max-w-4xl mx-auto px-6 text-center">
        <!-- Large Couple Names -->
        <div class="mb-8">
          <h1 class="text-6xl md:text-8xl font-light tracking-tight text-gray-900 mb-2">
            {{ weddingEvent.groom_name }}
          </h1>
          <div class="text-2xl text-gray-300 my-4">&</div>
          <h1 class="text-6xl md:text-8xl font-light tracking-tight text-gray-900">
            {{ weddingEvent.bride_name }}
          </h1>
        </div>

        <!-- Date -->
        <p class="text-sm tracking-[0.3em] uppercase text-gray-400 mb-12">
          {{ formatDateFull(weddingEvent.wedding_date) }}
        </p>

        <!-- Hero Image - Full Width -->
        <div class="w-full h-[70vh] mb-12 rounded-lg overflow-hidden shadow-lg">
          <img 
            :src="weddingEvent.hero_image" 
            :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
            class="w-full h-full object-cover object-[center_30%]"
          />
        </div>

        <!-- Simple Countdown -->
        <div v-if="countdown" class="flex items-center justify-center gap-12 mb-12">
          <div v-for="unit in countdownUnits" :key="unit.key" class="text-center">
            <div class="text-4xl font-light text-gray-900">{{ countdown[unit.key] }}</div>
            <div class="text-xs tracking-wider uppercase text-gray-400 mt-1">{{ unit.label }}</div>
          </div>
        </div>

        <!-- Venue Info -->
        <div class="max-w-md mx-auto space-y-2 text-gray-600">
          <p class="text-lg">{{ weddingEvent.venue_name }}</p>
          <p class="text-sm">{{ weddingEvent.venue_address }}</p>
          <p class="text-sm">{{ weddingEvent.ceremony_time }}</p>
        </div>

        <!-- RSVP Button -->
        <button 
          @click="showRSVPModal = true"
          class="mt-12 px-12 py-4 bg-gray-900 text-white text-sm tracking-wider uppercase hover:bg-gray-800 transition-colors"
        >
          RSVP
        </button>
      </div>
    </section>

    <!-- Our Story Section -->
    <section v-show="activeTab === 'story'" class="pt-24 pb-16">
      <div class="max-w-4xl mx-auto px-6">
        <h2 class="text-3xl font-light text-center mb-16 tracking-tight">Our Story</h2>
        
        <!-- Story Content -->
        <div class="space-y-16">
          <!-- How We Met -->
          <div class="grid md:grid-cols-2 gap-8 items-center">
            <div>
              <h3 class="text-2xl font-medium mb-4">How We Met</h3>
              <p class="text-gray-600 leading-relaxed">{{ weddingEvent.how_we_met }}</p>
            </div>
            <div class="h-64 bg-gray-100 rounded-lg overflow-hidden">
              <img v-if="weddingEvent.story_image" :src="weddingEvent.story_image" alt="Our story" class="w-full h-full object-cover object-center" />
            </div>
          </div>
          
          <!-- The Proposal -->
          <div class="grid md:grid-cols-2 gap-8 items-center">
            <div class="order-2 md:order-1 h-64 bg-gray-100 rounded-lg overflow-hidden">
              <img v-if="galleryImages[0]" :src="galleryImages[0].url" alt="Proposal" class="w-full h-full object-cover object-center" />
            </div>
            <div class="order-1 md:order-2">
              <h3 class="text-2xl font-medium mb-4">The Proposal</h3>
              <p class="text-gray-600 leading-relaxed">{{ weddingEvent.proposal_story }}</p>
            </div>
          </div>
          
          <!-- Photo Gallery -->
          <div v-if="galleryImages.length > 0">
            <h3 class="text-2xl font-medium mb-8 text-center">Our Journey Together</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div v-for="(image, index) in galleryImages" :key="index" class="h-48 bg-gray-100 rounded-lg overflow-hidden">
                <img :src="image.url" :alt="`Gallery ${index + 1}`" class="w-full h-full object-cover object-center hover:scale-110 transition-transform duration-300" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Program Section -->
    <section v-show="activeTab === 'program'" class="pt-24 pb-16">
      <div class="max-w-3xl mx-auto px-6">
        <h2 class="text-3xl font-light text-center mb-16 tracking-tight">Schedule</h2>
        <div class="space-y-8">
          <div class="flex items-start gap-8 pb-8 border-b border-gray-100">
            <div class="w-24 text-sm text-gray-400">{{ weddingEvent.ceremony_time }}</div>
            <div class="flex-1">
              <h3 class="text-lg font-medium mb-2">Ceremony</h3>
              <p class="text-gray-600">{{ weddingEvent.venue_name }}</p>
            </div>
          </div>
          <div class="flex items-start gap-8 pb-8 border-b border-gray-100">
            <div class="w-24 text-sm text-gray-400">{{ weddingEvent.reception_time }}</div>
            <div class="flex-1">
              <h3 class="text-lg font-medium mb-2">Reception</h3>
              <p class="text-gray-600">{{ weddingEvent.reception_venue }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Location Section -->
    <section v-show="activeTab === 'location'" class="pt-24 pb-16">
      <div class="max-w-3xl mx-auto px-6">
        <h2 class="text-3xl font-light text-center mb-16 tracking-tight">Location</h2>
        <div class="space-y-8">
          <div class="text-center">
            <h3 class="text-lg font-medium mb-2">{{ weddingEvent.venue_name }}</h3>
            <p class="text-gray-600">{{ weddingEvent.venue_address }}</p>
          </div>
          <!-- Google Maps Link -->
          <div class="w-full h-96 bg-gray-100 rounded-lg overflow-hidden relative group cursor-pointer"
               @click="openGoogleMaps">
            <div class="absolute inset-0 flex items-center justify-center bg-gray-900/50 group-hover:bg-gray-900/70 transition-colors">
              <div class="text-center text-white">
                <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-lg font-medium">Click to view map</p>
              </div>
            </div>
          </div>
          <div class="text-center">
            <a 
              :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_address)}`"
              target="_blank"
              class="inline-block px-8 py-3 bg-gray-900 text-white text-sm tracking-wider uppercase hover:bg-gray-800 transition-colors"
            >
              Get Directions
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section v-show="activeTab === 'contact'" class="pt-24 pb-16">
      <div class="max-w-3xl mx-auto px-6">
        <h2 class="text-3xl font-light text-center mb-16 tracking-tight">Get in Touch</h2>
        
        <div class="grid md:grid-cols-2 gap-12 mb-12">
          <!-- Contact Info -->
          <div class="space-y-6">
            <div>
              <h3 class="text-lg font-medium mb-3 text-gray-900">Bride</h3>
              <p class="text-gray-600 mb-1">{{ weddingEvent.bride_name }}</p>
              <p v-if="weddingEvent.bride_email" class="text-sm text-gray-500">{{ weddingEvent.bride_email }}</p>
              <p v-if="weddingEvent.bride_phone" class="text-sm text-gray-500">{{ weddingEvent.bride_phone }}</p>
            </div>
            
            <div>
              <h3 class="text-lg font-medium mb-3 text-gray-900">Groom</h3>
              <p class="text-gray-600 mb-1">{{ weddingEvent.groom_name }}</p>
              <p v-if="weddingEvent.groom_email" class="text-sm text-gray-500">{{ weddingEvent.groom_email }}</p>
              <p v-if="weddingEvent.groom_phone" class="text-sm text-gray-500">{{ weddingEvent.groom_phone }}</p>
            </div>
            
            <div v-if="weddingEvent.contact_person">
              <h3 class="text-lg font-medium mb-3 text-gray-900">Event Coordinator</h3>
              <p class="text-gray-600 mb-1">{{ weddingEvent.contact_person }}</p>
              <p v-if="weddingEvent.contact_email" class="text-sm text-gray-500">{{ weddingEvent.contact_email }}</p>
              <p v-if="weddingEvent.contact_phone" class="text-sm text-gray-500">{{ weddingEvent.contact_phone }}</p>
            </div>
          </div>
          
          <!-- Quick Message Form -->
          <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4 text-gray-900">Send a Message</h3>
            <form @submit.prevent="sendMessage" class="space-y-4">
              <div>
                <input 
                  v-model="contactForm.name"
                  type="text" 
                  placeholder="Your Name"
                  class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:border-gray-400"
                  required
                />
              </div>
              <div>
                <input 
                  v-model="contactForm.email"
                  type="email" 
                  placeholder="Your Email"
                  class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:border-gray-400"
                  required
                />
              </div>
              <div>
                <textarea 
                  v-model="contactForm.message"
                  placeholder="Your Message"
                  rows="4"
                  class="w-full px-4 py-2 border border-gray-200 rounded focus:outline-none focus:border-gray-400"
                  required
                ></textarea>
              </div>
              <button 
                type="submit"
                :disabled="contactForm.sending"
                class="w-full px-6 py-3 bg-gray-900 text-white text-sm tracking-wider uppercase hover:bg-gray-800 transition-colors disabled:opacity-50"
              >
                {{ contactForm.sending ? 'Sending...' : 'Send Message' }}
              </button>
              <p v-if="contactForm.success" class="text-sm text-green-600 text-center">Message sent successfully!</p>
              <p v-if="contactForm.error" class="text-sm text-red-600 text-center">{{ contactForm.error }}</p>
            </form>
          </div>
        </div>
        
        <!-- Social Links -->
        <div v-if="weddingEvent.social_links" class="text-center">
          <h3 class="text-lg font-medium mb-4 text-gray-900">Follow Our Journey</h3>
          <div class="flex items-center justify-center gap-4">
            <a v-if="weddingEvent.social_links.instagram" 
               :href="weddingEvent.social_links.instagram" 
               target="_blank"
               class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
              <span class="sr-only">Instagram</span>
              <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>
            </a>
            <a v-if="weddingEvent.social_links.facebook" 
               :href="weddingEvent.social_links.facebook" 
               target="_blank"
               class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
              <span class="sr-only">Facebook</span>
              <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>
            <a v-if="weddingEvent.social_links.twitter" 
               :href="weddingEvent.social_links.twitter" 
               target="_blank"
               class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
              <span class="sr-only">Twitter</span>
              <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
              </svg>
            </a>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { ShareIcon } from '@heroicons/vue/24/outline'
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
const shareMenuOpen = ref(false)
const countdown = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 })
const contactForm = ref({
  name: '',
  email: '',
  message: '',
  sending: false,
  success: false,
  error: null
})

const navTabs = [
  { id: 'home', label: 'Home' },
  { id: 'story', label: 'Our Story' },
  { id: 'program', label: 'Program' },
  { id: 'location', label: 'Location' },
  { id: 'contact', label: 'Contact' },
]

const countdownUnits = [
  { key: 'days', label: 'Days' },
  { key: 'hours', label: 'Hours' },
  { key: 'minutes', label: 'Minutes' },
  { key: 'seconds', label: 'Seconds' },
]

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
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

const sendMessage = async () => {
  contactForm.value.sending = true
  contactForm.value.error = null
  contactForm.value.success = false
  
  try {
    // In a real implementation, this would send to your backend
    await new Promise(resolve => setTimeout(resolve, 1000))
    contactForm.value.success = true
    contactForm.value.name = ''
    contactForm.value.email = ''
    contactForm.value.message = ''
  } catch (error) {
    contactForm.value.error = 'Failed to send message. Please try again.'
  } finally {
    contactForm.value.sending = false
  }
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
