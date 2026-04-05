<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
  </Head>

  <div class="min-h-screen bg-gradient-to-b from-green-50 via-white to-rose-50 text-gray-800">
    <!-- Vintage Botanical Header -->
    <header class="relative py-16">
      <!-- Ornate Vintage Frame -->
      <div class="max-w-4xl mx-auto px-8">
        <div class="relative">
          <!-- Corner Decorations -->
          <div class="absolute top-0 left-0 w-16 h-16 border-l-4 border-t-4 border-green-700"></div>
          <div class="absolute top-0 right-0 w-16 h-16 border-r-4 border-t-4 border-green-700"></div>
          <div class="absolute bottom-0 left-0 w-16 h-16 border-l-4 border-b-4 border-green-700"></div>
          <div class="absolute bottom-0 right-0 w-16 h-16 border-r-4 border-b-4 border-green-700"></div>
          
          <!-- Botanical Illustrations -->
          <div class="absolute -top-8 -left-8 text-4xl opacity-60">🌿</div>
          <div class="absolute -top-8 -right-8 text-4xl opacity-60">🌸</div>
          <div class="absolute -bottom-8 -left-8 text-4xl opacity-60">🌹</div>
          <div class="absolute -bottom-8 -right-8 text-4xl opacity-60">🌺</div>
          
          <!-- Central Content -->
          <div class="text-center py-12">
            <!-- Vintage Ornamental Divider -->
            <div class="flex items-center justify-center mb-8">
              <div class="flex-1 h-px bg-gradient-to-r from-transparent via-green-600 to-transparent"></div>
              <div class="mx-6 text-3xl text-green-600">✦</div>
              <div class="flex-1 h-px bg-gradient-to-r from-green-600 to-transparent"></div>
            </div>
            
            <!-- Classical Names -->
            <div class="space-y-6 mb-8">
              <h1 class="text-5xl md:text-7xl font-serif text-green-800" style="font-family: 'Playfair Display', serif;">
                {{ weddingEvent.groom_name }}
              </h1>
              <div class="text-3xl md:text-4xl text-rose-600 font-serif" style="font-family: 'Playfair Display', serif;">
                &amp;
              </div>
              <h2 class="text-5xl md:text-7xl font-serif text-green-800" style="font-family: 'Playfair Display', serif;">
                {{ weddingEvent.bride_name }}
              </h2>
            </div>
            
            <!-- Date in Classic Style -->
            <p class="text-xl md:text-2xl text-gray-600 font-serif" style="font-family: 'Crimson Text', serif;">
              {{ formatDateFull(weddingEvent.wedding_date) }}
            </p>
            
            <!-- Bottom Ornamental Divider -->
            <div class="flex items-center justify-center mt-8">
              <div class="flex-1 h-px bg-gradient-to-r from-transparent via-rose-400 to-transparent"></div>
              <div class="mx-6 text-2xl text-rose-500">❦</div>
              <div class="flex-1 h-px bg-gradient-to-r from-rose-400 to-transparent"></div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Garden-Inspired Hero -->
    <section class="relative py-16">
      <div class="max-w-4xl mx-auto px-8">
        <div class="relative">
          <!-- Vintage Frame -->
          <div class="absolute -inset-4 border-8 border-green-700 opacity-20 rounded-lg"></div>
          <div class="absolute -inset-2 border-4 border-rose-600 opacity-30 rounded-lg"></div>
          
          <!-- Hero Image -->
          <img 
            :src="weddingEvent.hero_image || '/images/Wedding/main.jpg'" 
            :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
            class="w-full h-96 object-cover rounded-lg relative z-10"
          />
          
          <!-- Botanical Overlay -->
          <div class="absolute top-4 left-4 text-2xl text-green-600 opacity-70">🌿</div>
          <div class="absolute top-4 right-4 text-2xl text-rose-500 opacity-70">🌸</div>
          <div class="absolute bottom-4 left-4 text-2xl text-green-600 opacity-70">🌹</div>
          <div class="absolute bottom-4 right-4 text-2xl text-rose-500 opacity-70">🌺</div>
        </div>
      </div>
    </section>

    <!-- Classical Love Story -->
    <section class="relative py-16">
      <div class="max-w-3xl mx-auto px-8 text-center">
        <!-- Ornamental Title -->
        <div class="mb-12">
          <div class="flex items-center justify-center mb-4">
            <div class="flex-1 h-px bg-gradient-to-r from-transparent via-green-600 to-transparent"></div>
            <div class="mx-6 text-2xl text-green-600">❦</div>
            <div class="flex-1 h-px bg-gradient-to-r from-green-600 to-transparent"></div>
          </div>
          <h3 class="text-3xl md:text-4xl font-serif text-green-800" style="font-family: 'Playfair Display', serif;">
            Our Love Story
          </h3>
          <div class="flex items-center justify-center mt-4">
            <div class="flex-1 h-px bg-gradient-to-r from-transparent via-rose-400 to-transparent"></div>
            <div class="mx-6 text-2xl text-rose-500">✦</div>
            <div class="flex-1 h-px bg-gradient-to-r from-rose-400 to-transparent"></div>
          </div>
        </div>

        <!-- Classical Text -->
        <div class="space-y-8" style="font-family: 'Crimson Text', serif;">
          <p class="text-lg md:text-xl text-gray-700 leading-relaxed italic">
            "In the garden of life, where love blooms eternal, two souls found each other among the roses of destiny."
          </p>
          <p class="text-lg md:text-xl text-gray-700 leading-relaxed">
            Our journey began like a delicate flower bud, slowly unfolding into a beautiful bloom. 
            Through seasons of joy and challenges, our love has grown stronger, rooted in trust 
            and nourished by devotion.
          </p>
          <p class="text-lg md:text-xl text-rose-600 leading-relaxed italic">
            "And now, as we stand beneath the open sky, surrounded by nature's beauty, 
            we vow to nurture our love for all eternity."
          </p>
        </div>
      </div>
    </section>

    <!-- Botanical Details -->
    <section class="relative py-16">
      <div class="max-w-4xl mx-auto px-8">
        <div class="grid md:grid-cols-2 gap-8">
          <!-- Date Card -->
          <div class="relative group">
            <div class="absolute -inset-2 bg-gradient-to-r from-green-100 to-rose-100 rounded-lg opacity-50"></div>
            <div class="relative bg-white/90 backdrop-blur-sm border-2 border-green-600 rounded-lg p-8 text-center">
              <div class="text-3xl mb-4">📅</div>
              <h4 class="text-xl font-serif mb-4 text-green-800" style="font-family: 'Playfair Display', serif;">
                The Date
              </h4>
              <p class="text-lg text-gray-700" style="font-family: 'Crimson Text', serif;">
                {{ formatDateFull(weddingEvent.wedding_date) }}
              </p>
              <p class="text-md text-gray-600 mt-2" style="font-family: 'Crimson Text', serif;">
                {{ weddingEvent.ceremony_time || '6:00 PM' }}
              </p>
            </div>
          </div>

          <!-- Venue Card -->
          <div class="relative group">
            <div class="absolute -inset-2 bg-gradient-to-r from-rose-100 to-green-100 rounded-lg opacity-50"></div>
            <div class="relative bg-white/90 backdrop-blur-sm border-2 border-rose-600 rounded-lg p-8 text-center">
              <div class="text-3xl mb-4">📍</div>
              <h4 class="text-xl font-serif mb-4 text-rose-800" style="font-family: 'Playfair Display', serif;">
                The Venue
              </h4>
              <p class="text-lg text-gray-700" style="font-family: 'Crimson Text', serif;">
                {{ weddingEvent.venue_name }}
              </p>
              <p class="text-md text-gray-600 mt-2" style="font-family: 'Crimson Text', serif;">
                {{ weddingEvent.venue_address }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Vintage Gallery -->
    <section class="relative py-16">
      <div class="max-w-5xl mx-auto px-8">
        <!-- Ornamental Title -->
        <div class="text-center mb-12">
          <div class="flex items-center justify-center mb-4">
            <div class="flex-1 h-px bg-gradient-to-r from-transparent via-green-600 to-transparent"></div>
            <div class="mx-6 text-2xl text-green-600">🌿</div>
            <div class="flex-1 h-px bg-gradient-to-r from-green-600 to-transparent"></div>
          </div>
          <h3 class="text-3xl md:text-4xl font-serif text-green-800" style="family: 'Playfair Display', serif;">
            Garden of Memories
          </h3>
          <div class="flex items-center justify-center mt-4">
            <div class="flex-1 h-px bg-gradient-to-r from-transparent via-rose-400 to-transparent"></div>
            <div class="mx-6 text-2xl text-rose-500">🌸</div>
            <div class="flex-1 h-px bg-gradient-to-r from-rose-400 to-transparent"></div>
          </div>
        </div>

        <!-- Vintage Grid -->
        <div class="grid grid-cols-3 gap-4">
          <div class="col-span-2 row-span-2 relative">
            <div class="absolute -inset-2 border-4 border-green-600 opacity-20 rounded-lg"></div>
            <img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80" alt="Memory 1" class="w-full h-full object-cover rounded-lg" />
            <div class="absolute top-2 left-2 text-xl text-green-600">🌹</div>
          </div>
          <div class="relative">
            <div class="absolute -inset-1 border-2 border-rose-600 opacity-20 rounded-lg"></div>
            <img src="https://images.unsplash.com/photo-1519741497674-63548a8048b6?w=600&q=80" alt="Memory 2" class="w-full h-full object-cover rounded-lg" />
            <div class="absolute top-2 right-2 text-lg text-rose-500">🌸</div>
          </div>
          <div class="relative">
            <div class="absolute -inset-1 border-2 border-green-600 opacity-20 rounded-lg"></div>
            <img src="https://images.unsplash.com/photo-1522673659358-4054d49d5d0e?w=600&q=80" alt="Memory 3" class="w-full h-full object-cover rounded-lg" />
            <div class="absolute bottom-2 left-2 text-lg text-green-600">🌿</div>
          </div>
          <div class="relative">
            <div class="absolute -inset-1 border-2 border-rose-600 opacity-20 rounded-lg"></div>
            <img src="https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=600&q=80" alt="Memory 4" class="w-full h-full object-cover rounded-lg" />
            <div class="absolute bottom-2 right-2 text-lg text-rose-500">🌺</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Classical RSVP -->
    <section class="relative py-16">
      <div class="max-w-md mx-auto text-center">
        <div class="relative">
          <!-- Vintage Frame -->
          <div class="absolute -inset-4 border-4 border-green-700 opacity-20 rounded-lg"></div>
          <div class="absolute -inset-2 border-2 border-rose-600 opacity-30 rounded-lg"></div>
          
          <!-- Content -->
          <div class="relative bg-white/90 backdrop-blur-sm rounded-lg p-8 border border-green-600">
            <!-- Ornamental Divider -->
            <div class="flex items-center justify-center mb-6">
              <div class="flex-1 h-px bg-gradient-to-r from-transparent via-green-600 to-transparent"></div>
              <div class="mx-4 text-xl text-green-600">❦</div>
              <div class="flex-1 h-px bg-gradient-to-r from-green-600 to-transparent"></div>
            </div>
            
            <h3 class="text-2xl font-serif mb-4 text-green-800" style="font-family: 'Playfair Display', serif;">
              Join Our Celebration
            </h3>
            <p class="text-gray-600 mb-6" style="font-family: 'Crimson Text', serif;">
              Kindly respond by {{ formatDate(weddingEvent.rsvp_deadline) }}
            </p>
            
            <button 
              @click="showRSVPModal = true"
              class="w-full bg-gradient-to-r from-green-600 to-rose-600 text-white py-3 rounded-lg font-serif hover:from-green-700 hover:to-rose-700 transition-colors"
              style="font-family: 'Playfair Display', serif;"
            >
              RSVP with Love
            </button>
            
            <!-- Bottom Ornament -->
            <div class="flex items-center justify-center mt-6">
              <div class="flex-1 h-px bg-gradient-to-r from-transparent via-rose-400 to-transparent"></div>
              <div class="mx-4 text-xl text-rose-500">🌹</div>
              <div class="flex-1 h-px bg-gradient-to-r from-rose-400 to-transparent"></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Vintage Footer -->
    <footer class="relative py-12 border-t-2 border-green-700">
      <div class="text-center">
        <div class="flex items-center justify-center mb-4">
          <div class="flex-1 h-px bg-gradient-to-r from-transparent via-green-600 to-transparent"></div>
          <div class="mx-6 text-2xl text-green-600">🌿</div>
          <div class="flex-1 h-px bg-gradient-to-r from-green-600 to-transparent"></div>
        </div>
        <p class="text-lg text-gray-600 font-serif" style="font-family: 'Crimson Text', serif;">
          {{ formatDate(weddingEvent.wedding_date) }}
        </p>
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
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800;1,900&family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap');

.font-serif {
  font-family: 'Playfair Display', serif;
}
</style>
