<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
  </Head>

  <div class="min-h-screen bg-white text-gray-900">
    <!-- Magazine Header -->
    <header class="border-b-8 border-black">
      <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Magazine Masthead -->
        <div class="text-center mb-8">
          <div class="text-sm font-bold tracking-[0.3em] uppercase mb-2">WEDDING EDITION</div>
          <div class="text-6xl font-serif font-light tracking-tight">THE LOVE STORY</div>
          <div class="text-sm font-medium tracking-[0.2em] uppercase mt-2">VOLUME ONE • ISSUE ONE</div>
        </div>
      </div>
    </header>

    <!-- Editorial Hero Spread -->
    <section class="relative">
      <div class="grid grid-cols-2 h-screen">
        <!-- Left Column - Headline -->
        <div class="flex flex-col justify-center px-12 border-r-4 border-black">
          <div class="space-y-8">
            <div class="text-sm font-bold tracking-[0.3em] uppercase text-red-600">EXCLUSIVE</div>
            <h1 class="text-5xl md:text-7xl font-serif leading-tight">
              A Modern
              <br />
              <span class="font-black">Love Affair</span>
            </h1>
            <div class="space-y-4">
              <p class="text-2xl font-light font-serif">{{ weddingEvent.groom_name }}</p>
              <div class="flex items-center space-x-4">
                <div class="h-px w-16 bg-black"></div>
                <div class="text-3xl font-serif">&</div>
                <div class="h-px w-16 bg-black"></div>
              </div>
              <p class="text-2xl font-light font-serif">{{ weddingEvent.bride_name }}</p>
            </div>
            <div class="text-sm font-medium tracking-[0.2em] uppercase">
              {{ formatDate(weddingEvent.wedding_date) }} • {{ weddingEvent.venue_name }}
            </div>
          </div>
        </div>

        <!-- Right Column - Hero Image -->
        <div class="relative">
          <img 
            :src="weddingEvent.hero_image || '/images/Wedding/main.jpg'" 
            :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
            class="w-full h-full object-cover"
          />
          <!-- Magazine Caption -->
          <div class="absolute bottom-0 left-0 right-0 bg-white p-4 border-t-4 border-black">
            <p class="text-xs font-bold tracking-[0.2em] uppercase">PHOTOGRAPHY</p>
            <p class="text-sm font-light">The couple's engagement portrait captures their modern romance</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Editorial Content Grid -->
    <section class="max-w-7xl mx-auto px-6 py-16">
      <!-- Feature Story -->
      <article class="mb-24">
        <div class="grid grid-cols-12 gap-8">
          <!-- Pull Quote -->
          <div class="col-span-4">
            <div class="sticky top-8">
              <div class="text-6xl font-serif font-light leading-tight text-gray-200">
                "Two hearts,
                <br />
                <span class="font-black">one story</span>
              </div>
              <div class="mt-4 text-sm font-medium tracking-[0.2em] uppercase">— THE COUPLE</div>
            </div>
          </div>

          <!-- Main Story -->
          <div class="col-span-8">
            <div class="space-y-8">
              <div>
                <h2 class="text-3xl font-serif font-light mb-4">The Beginning</h2>
                <div class="prose prose-lg max-w-none">
                  <p class="text-gray-700 leading-relaxed text-lg">
                    In a world where modern romance meets timeless tradition, {{ weddingEvent.groom_name }} and {{ weddingEvent.bride_name }} found their perfect match. Their story begins like many great love affairs—with a chance encounter that would change everything.
                  </p>
                  <p class="text-gray-700 leading-relaxed text-lg mt-4">
                    What started as a simple introduction quickly blossomed into something extraordinary. Through shared dreams, countless conversations, and moments that felt straight out of a fairy tale, they discovered that they had found not just love, but their soulmate.
                  </p>
                </div>
              </div>

              <!-- Drop Cap -->
              <div class="flex items-start space-x-4">
                <div class="text-8xl font-serif font-light text-gray-300 leading-none">T</div>
                <div class="flex-1">
                  <p class="text-gray-700 leading-relaxed text-lg">
                    heir journey together has been marked by adventure, growth, and an unwavering commitment to one another. From late-night conversations to early morning adventures, every moment has strengthened their bond and deepened their love.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </article>

      <!-- Magazine Style Gallery -->
      <div class="mb-24">
        <h3 class="text-4xl font-serif font-light mb-8">Picture Perfect</h3>
        <div class="grid grid-cols-3 gap-4">
          <div class="col-span-2 row-span-2">
            <div class="relative h-full">
              <img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800&q=80" alt="Gallery 1" class="w-full h-full object-cover" />
              <div class="absolute bottom-0 left-0 right-0 bg-white p-3 border-t-2 border-black">
                <p class="text-xs font-bold tracking-[0.2em] uppercase">MAIN FEATURE</p>
              </div>
            </div>
          </div>
          <div class="space-y-4">
            <div class="relative">
              <img src="https://images.unsplash.com/photo-1519741497674-63548a8048b6?w=600&q=80" alt="Gallery 2" class="w-full h-48 object-cover" />
              <div class="absolute bottom-0 left-0 right-0 bg-white p-2 border-t border-black">
                <p class="text-xs font-bold tracking-[0.1em] uppercase">SIDEBAR</p>
              </div>
            </div>
            <div class="relative">
              <img src="https://images.unsplash.com/photo-1522673659358-4054d49d5d0e?w=600&q=80" alt="Gallery 3" class="w-full h-48 object-cover" />
              <div class="absolute bottom-0 left-0 right-0 bg-white p-2 border-t border-black">
                <p class="text-xs font-bold tracking-[0.1em] uppercase">INSERT</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Event Details as Editorial -->
      <div class="mb-24">
        <div class="grid grid-cols-12 gap-8">
          <div class="col-span-8">
            <h3 class="text-4xl font-serif font-light mb-8">The Celebration</h3>
            <div class="space-y-8">
              <div class="border-l-4 border-black pl-8">
                <h4 class="text-xl font-serif font-light mb-2">Date & Time</h4>
                <p class="text-gray-700">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
                <p class="text-gray-600">{{ weddingEvent.ceremony_time || '6:00 PM' }}</p>
              </div>
              
              <div class="border-l-4 border-black pl-8">
                <h4 class="text-xl font-serif font-light mb-2">Location</h4>
                <p class="text-gray-700">{{ weddingEvent.venue_name }}</p>
                <p class="text-gray-600">{{ weddingEvent.venue_address }}</p>
              </div>

              <div class="border-l-4 border-black pl-8">
                <h4 class="text-xl font-serif font-light mb-2">The Story Continues</h4>
                <p class="text-gray-700 leading-relaxed">
                  Join us as we celebrate the beginning of forever. Your presence is the only gift we desire as we embark on this new chapter together.
                </p>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="col-span-4">
            <div class="border-2 border-black p-6 sticky top-8">
              <h4 class="text-sm font-bold tracking-[0.3em] uppercase mb-4">RSVP</h4>
              <p class="text-sm text-gray-600 mb-6">Kindly respond by {{ formatDate(weddingEvent.rsvp_deadline) }}</p>
              <button 
                @click="showRSVPModal = true"
                class="w-full bg-black text-white py-3 text-sm font-bold tracking-[0.2em] uppercase hover:bg-gray-800 transition-colors"
              >
                Confirm Attendance
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Magazine Footer -->
    <footer class="border-t-8 border-black mt-24">
      <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="grid grid-cols-3 gap-8 text-center">
          <div>
            <p class="text-sm font-bold tracking-[0.2em] uppercase">Volume One</p>
            <p class="text-xs text-gray-600 mt-1">The Wedding Issue</p>
          </div>
          <div>
            <p class="text-sm font-bold tracking-[0.2em] uppercase">Published</p>
            <p class="text-xs text-gray-600 mt-1">{{ formatDate(weddingEvent.wedding_date) }}</p>
          </div>
          <div>
            <p class="text-sm font-bold tracking-[0.2em] uppercase">Edition</p>
            <p class="text-xs text-gray-600 mt-1">Limited Release</p>
          </div>
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
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@200;300;400;500;600;700;900&family=Inter:wght@200;300;400;500;600;700;800;900&display=swap');

.font-serif {
  font-family: 'Playfair Display', serif;
}

.prose {
  font-family: 'Inter', sans-serif;
}

.sticky {
  position: sticky;
}
</style>
