<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
  </Head>

  <div class="min-h-screen bg-gradient-to-br from-amber-50 via-rose-50 to-pink-50 relative overflow-hidden">
    <!-- Elegant Floral Background -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute top-0 left-0 w-96 h-96 bg-gradient-to-br from-rose-200 to-pink-200 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 right-0 w-80 h-80 bg-gradient-to-br from-amber-200 to-rose-200 rounded-full blur-3xl"></div>
      <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-gradient-to-br from-pink-200 to-amber-200 rounded-full blur-3xl"></div>
    </div>

    <!-- Ornate Frame Header -->
    <header class="relative py-20 px-8">
      <!-- Decorative Corner Elements -->
      <div class="absolute inset-0 max-w-6xl mx-auto">
        <!-- Top Left Corner -->
        <div class="absolute top-8 left-8 w-16 h-16 border-t-4 border-l-4 border-rose-300"></div>
        <!-- Top Right Corner -->
        <div class="absolute top-8 right-8 w-16 h-16 border-t-4 border-r-4 border-rose-300"></div>
        <!-- Bottom Left Corner -->
        <div class="absolute bottom-8 left-8 w-16 h-16 border-b-4 border-l-4 border-rose-300"></div>
        <!-- Bottom Right Corner -->
        <div class="absolute bottom-8 right-8 w-16 h-16 border-b-4 border-r-4 border-rose-300"></div>
      </div>

      <!-- Centered Content -->
      <div class="relative z-10 max-w-4xl mx-auto text-center">
        <!-- Decorative Top Elements -->
        <div class="mb-8">
          <div class="text-6xl mb-4">🌸</div>
          <div class="w-32 h-px bg-gradient-to-r from-transparent via-rose-300 to-transparent mx-auto"></div>
        </div>

        <!-- Couple Names -->
        <h1 class="text-6xl md:text-8xl font-serif mb-6 bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent">
          {{ weddingEvent.groom_name }}
        </h1>
        
        <div class="text-4xl md:text-5xl text-rose-400 mb-6 font-serif">&</div>
        
        <h1 class="text-6xl md:text-8xl font-serif mb-8 bg-gradient-to-r from-pink-600 to-rose-600 bg-clip-text text-transparent">
          {{ weddingEvent.bride_name }}
        </h1>

        <!-- Wedding Date -->
        <p class="text-xl md:text-2xl text-rose-600 font-light tracking-widest uppercase mb-8">
          {{ formatDate(weddingEvent.wedding_date) }}
        </p>

        <!-- Decorative Bottom Elements -->
        <div class="mt-8">
          <div class="w-32 h-px bg-gradient-to-r from-transparent via-rose-300 to-transparent mx-auto mb-4"></div>
          <div class="text-6xl">🌸</div>
        </div>
      </div>
    </header>

    <!-- Elegant Navigation -->
    <nav class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-y border-rose-200 shadow-lg">
      <div class="max-w-4xl mx-auto px-8">
        <div class="flex justify-center items-center py-6 gap-12">
          <!-- Decorative Left Element -->
          <div class="text-2xl">🌿</div>
          
          <a v-for="tab in navTabs" :key="tab.id"
             @click.prevent="activeTab = tab.id"
             :class="[
               'px-6 py-3 text-sm font-serif tracking-wide uppercase cursor-pointer transition-all duration-300 relative group',
               activeTab === tab.id 
                 ? 'text-rose-700 font-semibold' 
                 : 'text-rose-500 hover:text-rose-600'
             ]">
            {{ tab.label }}
            <div v-if="activeTab === tab.id" class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-rose-400 to-pink-400"></div>
          </a>
          
          <!-- Decorative Right Element -->
          <div class="text-2xl">🌿</div>
        </div>
      </div>
    </nav>

    <!-- Home Section -->
    <section v-show="activeTab === 'home'" class="py-20">
      <div class="max-w-6xl mx-auto px-8">
        <!-- Hero Image with Ornate Frame -->
        <div class="relative mb-16 group">
          <!-- Ornate Border Frame -->
          <div class="absolute -inset-6 border-4 border-rose-200 rounded-3xl transform rotate-1 group-hover:rotate-0 transition-transform duration-700"></div>
          <div class="absolute -inset-4 border-2 border-pink-200 rounded-3xl transform -rotate-1 group-hover:rotate-0 transition-transform duration-700"></div>
          
          <div class="relative bg-white p-6 rounded-3xl shadow-2xl overflow-hidden">
            <img 
              :src="weddingEvent.hero_image" 
              :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
              class="w-full h-[500px] object-cover object-center rounded-2xl group-hover:scale-105 transition-transform duration-700"
            />
            <!-- Overlay Effect -->
            <div class="absolute inset-0 bg-gradient-to-t from-rose-900/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          </div>
          
          <!-- Corner Decorations -->
          <div class="absolute -top-4 -left-4 text-3xl">🌸</div>
          <div class="absolute -top-4 -right-4 text-3xl">🌸</div>
          <div class="absolute -bottom-4 -left-4 text-3xl">🌸</div>
          <div class="absolute -bottom-4 -right-4 text-3xl">🌸</div>
        </div>

        <!-- Elegant Invitation -->
        <div class="bg-white rounded-3xl p-12 shadow-2xl border-2 border-rose-200 text-center mb-16 relative overflow-hidden">
          <!-- Background Pattern -->
          <div class="absolute inset-0 opacity-5">
            <div class="absolute top-4 left-4 text-6xl">🌿</div>
            <div class="absolute top-8 right-8 text-5xl">🌸</div>
            <div class="absolute bottom-4 left-8 text-6xl">🌿</div>
            <div class="absolute bottom-8 right-4 text-5xl">🌸</div>
          </div>
          
          <div class="relative z-10">
            <div class="text-5xl mb-6">💕</div>
            <p class="text-xl text-gray-700 leading-relaxed mb-8 font-serif">
              Together with their families, they invite you to share in their joy as they exchange vows and begin their life together.
            </p>
            
            <div class="bg-gradient-to-r from-rose-50 to-pink-50 rounded-2xl p-8 mb-8 border border-rose-200">
              <div class="text-4xl mb-4">🌹</div>
              <p class="text-2xl text-rose-800 mb-4 font-serif">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
              <p class="text-rose-600 mb-4">{{ weddingEvent.ceremony_time }}</p>
              <div class="w-24 h-px bg-rose-300 mx-auto mb-4"></div>
              <p class="text-xl text-rose-700 mb-2 font-serif">{{ weddingEvent.venue_name }}</p>
              <p class="text-rose-600">{{ weddingEvent.venue_address }}</p>
              <div class="text-4xl mt-4">🌹</div>
            </div>
            
            <button 
              @click="showRSVPModal = true"
              class="bg-gradient-to-r from-rose-500 to-pink-500 text-white px-12 py-4 rounded-full text-lg font-serif tracking-wide hover:from-rose-600 hover:to-pink-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105"
            >
              RSVP with Joy
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Story Section -->
    <section v-show="activeTab === 'story'" class="py-20">
      <div class="max-w-6xl mx-auto px-8">
        <div class="text-center mb-16">
          <h2 class="text-4xl md:text-5xl font-serif bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent mb-4">Our Love Story</h2>
          <div class="w-32 h-px bg-gradient-to-r from-transparent via-rose-300 to-transparent mx-auto"></div>
        </div>
        
        <div class="grid md:grid-cols-2 gap-12">
          <!-- How We Met -->
          <div class="bg-white rounded-3xl p-10 shadow-2xl border-2 border-rose-200 relative overflow-hidden">
            <div class="absolute top-4 right-4 text-3xl opacity-20">🌸</div>
            <div class="text-4xl mb-6 text-center">💑</div>
            <h3 class="text-2xl font-serif text-rose-800 mb-6 text-center">How We Met</h3>
            <p class="text-gray-700 leading-relaxed">{{ weddingEvent.how_we_met }}</p>
          </div>
          
          <!-- The Proposal -->
          <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-3xl p-10 shadow-2xl border-2 border-pink-200 relative overflow-hidden">
            <div class="absolute top-4 right-4 text-3xl opacity-20">💍</div>
            <div class="text-4xl mb-6 text-center">💕</div>
            <h3 class="text-2xl font-serif text-pink-800 mb-6 text-center">The Proposal</h3>
            <p class="text-gray-700 leading-relaxed">{{ weddingEvent.proposal_story }}</p>
          </div>
        </div>
        
        <!-- Gallery -->
        <div v-if="galleryImages.length > 0" class="mt-16">
          <div class="text-center mb-12">
            <h3 class="text-3xl font-serif text-rose-800">Our Memories</h3>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div v-for="(image, index) in galleryImages" :key="index" class="relative group">
              <div class="absolute -inset-2 border-2 border-rose-200 rounded-2xl opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
              <div class="relative overflow-hidden rounded-2xl border-2 border-rose-100">
                <img :src="image.url" :alt="`Gallery ${index + 1}`" class="w-full h-40 object-cover object-center group-hover:scale-110 transition-transform duration-500" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Program Section -->
    <section v-show="activeTab === 'program'" class="py-20">
      <div class="max-w-4xl mx-auto px-8">
        <div class="text-center mb-16">
          <h2 class="text-4xl md:text-5xl font-serif bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent mb-4">Celebration Details</h2>
          <div class="w-32 h-px bg-gradient-to-r from-transparent via-rose-300 to-transparent mx-auto"></div>
        </div>
        
        <div class="space-y-8">
          <!-- Ceremony -->
          <div class="bg-white rounded-3xl p-10 shadow-2xl border-2 border-rose-200 text-center">
            <div class="text-4xl mb-6">🌸</div>
            <h3 class="text-2xl font-serif text-rose-800 mb-4">Ceremony</h3>
            <p class="text-rose-600 text-lg mb-2">{{ weddingEvent.ceremony_time }}</p>
            <p class="text-rose-700 font-serif mb-2">{{ weddingEvent.venue_name }}</p>
            <p class="text-rose-600">{{ weddingEvent.venue_address }}</p>
          </div>
          
          <!-- Reception -->
          <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-3xl p-10 shadow-2xl border-2 border-pink-200 text-center">
            <div class="text-4xl mb-6">🎉</div>
            <h3 class="text-2xl font-serif text-pink-800 mb-4">Reception</h3>
            <p class="text-pink-600 text-lg mb-2">{{ weddingEvent.reception_time }}</p>
            <p class="text-pink-700 font-serif mb-2">{{ weddingEvent.reception_venue }}</p>
            <p class="text-pink-600">{{ weddingEvent.reception_address }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Location Section -->
    <section v-show="activeTab === 'location'" class="py-20">
      <div class="max-w-4xl mx-auto px-8">
        <div class="text-center mb-16">
          <h2 class="text-4xl md:text-5xl font-serif bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent mb-4">Location</h2>
          <div class="w-32 h-px bg-gradient-to-r from-transparent via-rose-300 to-transparent mx-auto"></div>
        </div>
        
        <div class="bg-white rounded-3xl p-12 shadow-2xl border-2 border-rose-200 text-center mb-12">
          <div class="text-5xl mb-6">🏛️</div>
          <h3 class="text-3xl font-serif text-rose-800 mb-4">{{ weddingEvent.venue_name }}</h3>
          <p class="text-xl text-rose-600 mb-8">{{ weddingEvent.venue_address }}</p>
          
          <div class="w-full h-64 bg-rose-50 rounded-2xl flex items-center justify-center mb-8 cursor-pointer hover:bg-rose-100 transition-colors" @click="openGoogleMaps">
            <div class="text-center">
              <div class="text-4xl mb-2">📍</div>
              <p class="text-rose-700 font-serif">Click to View Map</p>
            </div>
          </div>
          
          <a 
            :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_address)}`"
            target="_blank"
            class="inline-block bg-gradient-to-r from-rose-500 to-pink-500 text-white px-10 py-3 rounded-full text-lg font-serif tracking-wide hover:from-rose-600 hover:to-pink-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105"
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
import { ref } from 'vue'
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

const navTabs = [
  { id: 'home', label: 'Home' },
  { id: 'story', label: 'Our Story' },
  { id: 'program', label: 'Program' },
  { id: 'location', label: 'Location' },
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

const onRSVPSubmitted = () => {
  showRSVPModal.value = false
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Crimson+Text:wght@400;600&display=swap');

.font-serif {
  font-family: 'Playfair Display', 'Crimson Text', serif;
}
</style>
