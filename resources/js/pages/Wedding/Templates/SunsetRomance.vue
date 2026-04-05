<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
  </Head>

  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-rose-50 to-purple-50 text-gray-800 overflow-hidden">
    <!-- Watercolor Background -->
    <div class="fixed inset-0 opacity-30 pointer-events-none">
      <div class="absolute top-10 left-10 w-96 h-96 bg-pink-300 rounded-full filter blur-3xl"></div>
      <div class="absolute top-40 right-20 w-80 h-80 bg-purple-300 rounded-full filter blur-3xl"></div>
      <div class="absolute bottom-20 left-1/3 w-72 h-72 bg-rose-300 rounded-full filter blur-3xl"></div>
      <div class="absolute bottom-40 right-1/4 w-64 h-64 bg-purple-200 rounded-full filter blur-3xl"></div>
    </div>

    <!-- Organic Flowing Header -->
    <header class="relative pt-20 pb-16">
      <!-- Handwritten Style Names -->
      <div class="text-center space-y-8 relative z-10">
        <!-- Flowing Top Decoration -->
        <div class="relative">
          <svg class="w-32 h-16 mx-auto" viewBox="0 0 100 50" fill="none">
            <path d="M10,25 Q30,5 50,25 T90,25" stroke="#ec4899" stroke-width="2" fill="none" opacity="0.6"/>
            <circle cx="10" cy="25" r="3" fill="#ec4899" opacity="0.4"/>
            <circle cx="90" cy="25" r="3" fill="#ec4899" opacity="0.4"/>
          </svg>
        </div>

        <!-- Handwritten Names -->
        <div class="space-y-4">
          <h1 class="text-6xl md:text-8xl" style="font-family: 'Dancing Script', cursive;">
            {{ weddingEvent.groom_name }}
          </h1>
          <div class="text-4xl md:text-5xl text-rose-400" style="font-family: 'Dancing Script', cursive;">
            &amp;
          </div>
          <h2 class="text-6xl md:text-8xl" style="font-family: 'Dancing Script', cursive;">
            {{ weddingEvent.bride_name }}
          </h2>
        </div>

        <!-- Flowing Bottom Decoration -->
        <div class="relative">
          <svg class="w-48 h-16 mx-auto" viewBox="0 0 150 50" fill="none">
            <path d="M20,25 Q40,40 75,25 T130,25" stroke="#ec4899" stroke-width="2" fill="none" opacity="0.6"/>
            <circle cx="20" cy="25" r="2" fill="#ec4899" opacity="0.4"/>
            <circle cx="75" cy="25" r="4" fill="#ec4899" opacity="0.6"/>
            <circle cx="130" cy="25" r="2" fill="#ec4899" opacity="0.4"/>
          </svg>
        </div>

        <!-- Date in Handwritten Style -->
        <p class="text-xl md:text-2xl text-gray-600" style="font-family: 'Dancing Script', cursive;">
          {{ formatDateFull(weddingEvent.wedding_date) }}
        </p>
      </div>
    </header>

    <!-- Dreamy Hero Image -->
    <section class="relative px-8 py-16">
      <div class="max-w-4xl mx-auto">
        <div class="relative group">
          <!-- Soft Edge Frame -->
          <div class="absolute -inset-4 bg-gradient-to-r from-pink-200 via-rose-200 to-purple-200 rounded-full opacity-50 blur-xl"></div>
          
          <!-- Image with Soft Edges -->
          <div class="relative overflow-hidden rounded-full">
            <img 
              :src="weddingEvent.hero_image || '/images/Wedding/main.jpg'" 
              :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
              class="w-full h-96 object-cover opacity-90 group-hover:opacity-100 transition-opacity duration-1000"
            />
            
            <!-- Soft Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-pink-100/50 via-transparent to-purple-100/30"></div>
            
            <!-- Floating Hearts -->
            <div class="absolute top-8 left-8 text-2xl text-pink-300 animate-float">💕</div>
            <div class="absolute top-16 right-12 text-xl text-rose-300 animate-float-delayed">💖</div>
            <div class="absolute bottom-12 left-16 text-2xl text-purple-300 animate-float">💗</div>
            <div class="absolute bottom-8 right-8 text-xl text-pink-300 animate-float-delayed">💝</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Handwritten Love Story -->
    <section class="relative px-8 py-16">
      <div class="max-w-3xl mx-auto text-center">
        <!-- Flowing Divider -->
        <div class="mb-12">
          <svg class="w-64 h-12 mx-auto" viewBox="0 0 200 40" fill="none">
            <path d="M10,20 Q50,5 100,20 T190,20" stroke="#ec4899" stroke-width="1.5" fill="none" opacity="0.4"/>
            <path d="M10,25 Q50,35 100,25 T190,25" stroke="#ec4899" stroke-width="1.5" fill="none" opacity="0.3"/>
          </svg>
        </div>

        <!-- Handwritten Message -->
        <div class="space-y-8" style="font-family: 'Dancing Script', cursive;">
          <p class="text-2xl md:text-3xl text-gray-700 leading-relaxed">
            Once upon a time, two hearts found each other...
          </p>
          <p class="text-xl md:text-2xl text-gray-600 leading-relaxed">
            Our love story began with a single moment that changed everything. 
            Through laughter and tears, adventures and quiet moments, 
            we discovered that together is where we belong.
          </p>
          <p class="text-2xl md:text-3xl text-rose-500">
            And now, our forever begins...
          </p>
        </div>

        <!-- Flowing Divider -->
        <div class="mt-12">
          <svg class="w-64 h-12 mx-auto" viewBox="0 0 200 40" fill="none">
            <path d="M10,15 Q50,30 100,15 T190,15" stroke="#ec4899" stroke-width="1.5" fill="none" opacity="0.3"/>
            <path d="M10,20 Q50,10 100,20 T190,20" stroke="#ec4899" stroke-width="1.5" fill="none" opacity="0.4"/>
          </svg>
        </div>
      </div>
    </section>

    <!-- Organic Details Section -->
    <section class="relative px-8 py-16">
      <div class="max-w-4xl mx-auto">
        <div class="grid md:grid-cols-2 gap-12">
          <!-- Date Card -->
          <div class="relative group">
            <div class="absolute -inset-2 bg-gradient-to-r from-pink-200 to-rose-200 rounded-3xl opacity-30 blur-lg"></div>
            <div class="relative bg-white/80 backdrop-blur-sm rounded-3xl p-8 text-center border border-pink-200">
              <div class="text-4xl mb-4">📅</div>
              <h3 class="text-xl font-light mb-2" style="font-family: 'Dancing Script', cursive;">When</h3>
              <p class="text-lg text-gray-700">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
              <p class="text-md text-gray-600">{{ weddingEvent.ceremony_time || '6:00 PM' }}</p>
            </div>
          </div>

          <!-- Venue Card -->
          <div class="relative group">
            <div class="absolute -inset-2 bg-gradient-to-r from-purple-200 to-pink-200 rounded-3xl opacity-30 blur-lg"></div>
            <div class="relative bg-white/80 backdrop-blur-sm rounded-3xl p-8 text-center border border-purple-200">
              <div class="text-4xl mb-4">📍</div>
              <h3 class="text-xl font-light mb-2" style="font-family: 'Dancing Script', cursive;">Where</h3>
              <p class="text-lg text-gray-700">{{ weddingEvent.venue_name }}</p>
              <p class="text-md text-gray-600">{{ weddingEvent.venue_address }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Dreamy Gallery -->
    <section class="relative px-8 py-16">
      <div class="max-w-5xl mx-auto">
        <!-- Flowing Title -->
        <div class="text-center mb-12">
          <h3 class="text-4xl md:text-5xl" style="font-family: 'Dancing Script', cursive;">
            Our Memories
          </h3>
        </div>

        <!-- Organic Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="col-span-2 row-span-2 relative group">
            <div class="absolute -inset-2 bg-gradient-to-r from-pink-300 to-purple-300 rounded-full opacity-20 blur-xl"></div>
            <img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80" alt="Memory 1" class="w-full h-full object-cover rounded-full opacity-90" />
          </div>
          <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-rose-300 to-pink-300 rounded-full opacity-20 blur-lg"></div>
            <img src="https://images.unsplash.com/photo-1519741497674-63548a8048b6?w=600&q=80" alt="Memory 2" class="w-full h-full object-cover rounded-full opacity-90" />
          </div>
          <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-purple-300 to-rose-300 rounded-full opacity-20 blur-lg"></div>
            <img src="https://images.unsplash.com/photo-1522673659358-4054d49d5d0e?w=600&q=80" alt="Memory 3" class="w-full h-full object-cover rounded-full opacity-90" />
          </div>
          <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-pink-300 to-purple-300 rounded-full opacity-20 blur-lg"></div>
            <img src="https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=600&q=80" alt="Memory 4" class="w-full h-full object-cover rounded-full opacity-90" />
          </div>
        </div>
      </div>
    </section>

    <!-- Handwritten RSVP -->
    <section class="relative px-8 py-16">
      <div class="max-w-md mx-auto text-center">
        <!-- Flowing Decoration -->
        <div class="mb-8">
          <svg class="w-32 h-16 mx-auto" viewBox="0 0 100 50" fill="none">
            <path d="M10,25 Q30,5 50,25 T90,25" stroke="#ec4899" stroke-width="2" fill="none" opacity="0.6"/>
            <circle cx="50" cy="25" r="3" fill="#ec4899" opacity="0.6"/>
          </svg>
        </div>

        <div class="space-y-6">
          <h3 class="text-3xl" style="font-family: 'Dancing Script', cursive;">
            Join Our Celebration
          </h3>
          <p class="text-gray-600" style="font-family: 'Dancing Script', cursive;">
            Kindly RSVP by {{ formatDate(weddingEvent.rsvp_deadline) }}
          </p>
          
          <button 
            @click="showRSVPModal = true"
            class="group relative px-12 py-4 bg-gradient-to-r from-pink-300 to-rose-300 text-white rounded-full overflow-hidden transition-all duration-500 hover:shadow-lg"
            style="font-family: 'Dancing Script', cursive; font-size: 1.25rem;"
          >
            <span class="relative z-10">RSVP with Love 💕</span>
            <div class="absolute inset-0 bg-gradient-to-r from-rose-400 to-pink-400 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
          </button>
        </div>

        <!-- Bottom Decoration -->
        <div class="mt-8">
          <svg class="w-32 h-16 mx-auto" viewBox="0 0 100 50" fill="none">
            <path d="M10,25 Q30,45 50,25 T90,25" stroke="#ec4899" stroke-width="2" fill="none" opacity="0.6"/>
            <circle cx="10" cy="25" r="2" fill="#ec4899" opacity="0.4"/>
            <circle cx="90" cy="25" r="2" fill="#ec4899" opacity="0.4"/>
          </svg>
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
@import url('https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;500;600;700&display=swap');

.animate-float {
  animation: float 3s ease-in-out infinite;
}

.animate-float-delayed {
  animation: float 3s ease-in-out infinite;
  animation-delay: 1.5s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}
</style>
