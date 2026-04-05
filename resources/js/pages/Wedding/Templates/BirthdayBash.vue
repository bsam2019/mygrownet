<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
  </Head>

  <div class="min-h-screen bg-gradient-to-br from-purple-600 via-pink-500 to-orange-400 text-white overflow-hidden relative">
    <!-- Animated Confetti Background -->
    <div class="fixed inset-0 pointer-events-none">
      <div class="confetti-piece" style="left: 10%; animation-delay: 0s; background: #FFD700;"></div>
      <div class="confetti-piece" style="left: 20%; animation-delay: 0.5s; background: #FF69B4;"></div>
      <div class="confetti-piece" style="left: 30%; animation-delay: 1s; background: #00CED1;"></div>
      <div class="confetti-piece" style="left: 40%; animation-delay: 1.5s; background: #FF6347;"></div>
      <div class="confetti-piece" style="left: 50%; animation-delay: 2s; background: #32CD32;"></div>
      <div class="confetti-piece" style="left: 60%; animation-delay: 2.5s; background: #FFD700;"></div>
      <div class="confetti-piece" style="left: 70%; animation-delay: 3s; background: #FF69B4;"></div>
      <div class="confetti-piece" style="left: 80%; animation-delay: 3.5s; background: #00CED1;"></div>
      <div class="confetti-piece" style="left: 90%; animation-delay: 4s; background: #FF6347;"></div>
    </div>

    <!-- Festive Hero -->
    <header class="relative py-20 px-8">
      <div class="max-w-6xl mx-auto text-center">
        <!-- Animated Banner -->
        <div class="mb-8">
          <div class="inline-block bg-yellow-400 text-purple-900 px-8 py-4 transform rotate-2 hover:rotate-1 transition-transform duration-300">
            <h1 class="text-2xl font-black tracking-wider">🎉 PARTY TIME! 🎉</h1>
          </div>
        </div>

        <!-- Bold Names -->
        <div class="space-y-6 mb-12">
          <h2 class="text-6xl md:text-8xl font-black transform -rotate-3 hover:rotate-2 transition-transform duration-300">
            {{ weddingEvent.groom_name }}
          </h2>
          <div class="text-5xl md:text-7xl font-black animate-bounce">
            💕 + 💕
          </div>
          <h3 class="text-6xl md:text-8xl font-black transform rotate-3 hover:rotate-2 transition-transform duration-300">
            {{ weddingEvent.bride_name }}
          </h3>
        </div>

        <!-- Date Badge -->
        <div class="inline-block bg-white text-purple-600 px-8 py-6 rounded-full transform -rotate-1 hover:rotate-0 transition-transform duration-300">
          <p class="text-2xl font-black">{{ formatDate(weddingEvent.wedding_date) }}</p>
          <p class="text-lg font-bold">{{ weddingEvent.venue_name }}</p>
        </div>
      </div>
    </header>

    <!-- Dynamic Grid Content -->
    <section class="relative px-8 py-16">
      <div class="max-w-7xl mx-auto">
        <!-- Asymmetrical Grid -->
        <div class="grid grid-cols-4 gap-6">
          <!-- Hero Image Block -->
          <div class="col-span-3 row-span-2 relative group">
            <div class="absolute -inset-4 bg-yellow-400 rounded-3xl transform rotate-1 group-hover:rotate-0 transition-transform duration-300"></div>
            <img 
              :src="weddingEvent.hero_image || '/images/Wedding/main.jpg'" 
              :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
              class="w-full h-full object-cover rounded-3xl relative z-10"
            />
            <!-- Floating Emoji -->
            <div class="absolute top-4 left-4 text-4xl animate-spin">🎊</div>
            <div class="absolute bottom-4 right-4 text-4xl animate-bounce">🎈</div>
          </div>

          <!-- Party Details Card -->
          <div class="col-span-1 row-span-1">
            <div class="bg-orange-500 rounded-3xl p-6 text-center transform -rotate-2 hover:rotate-0 transition-transform duration-300 h-full">
              <div class="text-4xl mb-2">📅</div>
              <h4 class="text-lg font-black mb-2">DATE</h4>
              <p class="text-sm font-bold">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
              <p class="text-lg font-black mt-2">{{ weddingEvent.ceremony_time || '6:00 PM' }}</p>
            </div>
          </div>

          <!-- Location Card -->
          <div class="col-span-1 row-span-1">
            <div class="bg-pink-500 rounded-3xl p-6 text-center transform rotate-2 hover:rotate-0 transition-transform duration-300 h-full">
              <div class="text-4xl mb-2">📍</div>
              <h4 class="text-lg font-black mb-2">WHERE</h4>
              <p class="text-sm font-bold">{{ weddingEvent.venue_name }}</p>
              <p class="text-xs mt-1">{{ weddingEvent.venue_address }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Party Message -->
    <section class="relative px-8 py-16">
      <div class="max-w-4xl mx-auto text-center">
        <div class="bg-yellow-400 text-purple-900 rounded-3xl p-12 transform rotate-1 hover:rotate-0 transition-transform duration-300">
          <h3 class="text-4xl font-black mb-6">🎉 LET'S CELEBRATE! 🎉</h3>
          <p class="text-xl font-bold leading-relaxed">
            Get ready for the party of the year! We're tying the knot and want to celebrate with all our favorite people. 
            Join us for an evening of love, laughter, and dancing!
          </p>
          <div class="mt-8 text-6xl animate-bounce">🥳</div>
        </div>
      </div>
    </section>

    <!-- Dynamic Gallery Grid -->
    <section class="relative px-8 py-16">
      <div class="max-w-6xl mx-auto">
        <h3 class="text-5xl font-black text-center mb-12 transform -rotate-1">📸 PARTY PHOTOS 📸</h3>
        
        <!-- Scattered Grid -->
        <div class="grid grid-cols-3 gap-4">
          <div class="col-span-2 space-y-4">
            <div class="relative group transform rotate-1 hover:rotate-0 transition-transform duration-300">
              <img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80" alt="Party 1" class="w-full h-64 object-cover rounded-2xl" />
              <div class="absolute top-2 right-2 bg-yellow-400 text-purple-900 px-3 py-1 rounded-full text-xs font-black">
                MAIN EVENT
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="relative group transform -rotate-2 hover:rotate-0 transition-transform duration-300">
                <img src="https://images.unsplash.com/photo-1519741497674-63548a8048b6?w=600&q=80" alt="Party 2" class="w-full h-40 object-cover rounded-2xl" />
                <div class="absolute top-2 right-2 bg-pink-500 text-white px-3 py-1 rounded-full text-xs font-black">
                  DANCE
                </div>
              </div>
              <div class="relative group transform rotate-2 hover:rotate-0 transition-transform duration-300">
                <img src="https://images.unsplash.com/photo-1522673659358-4054d49d5d0e?w=600&q=80" alt="Party 3" class="w-full h-40 object-cover rounded-2xl" />
                <div class="absolute top-2 right-2 bg-orange-500 text-white px-3 py-1 rounded-full text-xs font-black">
                  FUN
                </div>
              </div>
            </div>
          </div>
          <div class="space-y-4">
            <div class="relative group transform -rotate-1 hover:rotate-0 transition-transform duration-300">
              <img src="https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=600&q=80" alt="Party 4" class="w-full h-48 object-cover rounded-2xl" />
              <div class="absolute top-2 right-2 bg-purple-500 text-white px-3 py-1 rounded-full text-xs font-black">
                CELEBRATION
              </div>
            </div>
            <div class="relative group transform rotate-2 hover:rotate-0 transition-transform duration-300">
              <img src="https://images.unsplash.com/photo-1511795409834-04dfd9978b14?w=600&q=80" alt="Party 5" class="w-full h-48 object-cover rounded-2xl" />
              <div class="absolute top-2 right-2 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-black">
                LOVE
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Party RSVP -->
    <section class="relative px-8 py-16">
      <div class="max-w-md mx-auto text-center">
        <div class="bg-gradient-to-r from-yellow-400 via-pink-500 to-orange-500 rounded-3xl p-8 transform rotate-1 hover:rotate-0 transition-transform duration-300">
          <h3 class="text-3xl font-black mb-4">🎉 JOIN THE PARTY! 🎉</h3>
          <p class="text-lg font-bold mb-6">RSVP by {{ formatDate(weddingEvent.rsvp_deadline) }}</p>
          
          <button 
            @click="showRSVPModal = true"
            class="w-full bg-white text-purple-600 py-4 rounded-2xl text-xl font-black hover:bg-yellow-300 transition-colors transform hover:scale-105 transition-transform duration-300"
          >
            🎊 I'M COMING! 🎊
          </button>
          
          <div class="mt-6 text-4xl animate-bounce">🥳</div>
        </div>
      </div>
    </section>

    <!-- Party Footer -->
    <footer class="relative py-12 px-8">
      <div class="text-center">
        <div class="inline-block bg-yellow-400 text-purple-900 px-8 py-4 rounded-full transform -rotate-2">
          <p class="text-lg font-black">🎉 {{ formatDate(weddingEvent.wedding_date) }} 🎉</p>
        </div>
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
@import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Black+Ops+One&family=Permanent+Marker&display=swap');

.font-black {
  font-family: 'Black Ops One', cursive;
}

.confetti-piece {
  position: absolute;
  width: 10px;
  height: 10px;
  top: -10px;
  animation: fall linear infinite;
}

@keyframes fall {
  to {
    transform: translateY(100vh) rotate(360deg);
  }
}

.animate-spin {
  animation: spin 3s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
