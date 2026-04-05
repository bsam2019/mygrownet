<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
  </Head>

  <div class="min-h-screen bg-gradient-to-br from-amber-50 via-amber-25 to-yellow-50">
    <!-- Ornate Background Pattern -->
    <div class="absolute inset-0 opacity-5">
      <div class="absolute top-0 left-0 w-64 h-64 border-8 border-amber-900 rounded-full"></div>
      <div class="absolute top-0 right-0 w-48 h-48 border-4 border-amber-700 transform rotate-45"></div>
      <div class="absolute bottom-0 left-0 w-56 h-56 border-6 border-amber-800 rounded-full transform -rotate-12"></div>
      <div class="absolute bottom-0 right-0 w-40 h-40 border-4 border-amber-600 transform rotate-90"></div>
    </div>
    
    <!-- Luxurious Header with Ornate Frame -->
    <header class="relative bg-gradient-to-r from-amber-900 via-amber-800 to-amber-900 text-white py-12 overflow-hidden">
      <!-- Ornate Corner Decorations -->
      <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-32 h-32 border-t-8 border-l-8 border-amber-400"></div>
        <div class="absolute top-0 right-0 w-32 h-32 border-t-8 border-r-8 border-amber-400"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 border-b-8 border-l-8 border-amber-400"></div>
        <div class="absolute bottom-0 right-0 w-32 h-32 border-b-8 border-r-8 border-amber-400"></div>
      </div>
      
      <!-- Central Ornamental Divider -->
      <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96">
        <div class="flex items-center justify-center">
          <div class="flex-1 h-px bg-gradient-to-r from-transparent via-amber-400 to-transparent"></div>
          <div class="mx-8 text-4xl text-amber-400">✦</div>
          <div class="flex-1 h-px bg-gradient-to-r from-transparent via-amber-400 to-transparent"></div>
        </div>
      </div>
      
      <div class="max-w-6xl mx-auto px-8 text-center relative z-10">
        <div class="mb-8">
          <div class="text-6xl font-serif mb-4 tracking-wide">{{ weddingEvent.groom_name }}</div>
          <div class="text-5xl text-amber-300 mb-4 font-serif">&</div>
          <div class="text-6xl font-serif tracking-wide">{{ weddingEvent.bride_name }}</div>
        </div>
        
        <div class="text-xl tracking-[0.4em] uppercase text-amber-200 font-serif">
          {{ formatDate(weddingEvent.wedding_date) }}
        </div>
      </div>
    </header>

    <!-- Ornate Navigation with Gold Accents -->
    <nav class="sticky top-0 z-40 bg-gradient-to-r from-amber-100 via-amber-50 to-amber-100 border-y-4 border-amber-300 shadow-lg">
      <div class="max-w-6xl mx-auto px-8">
        <div class="flex justify-center items-center py-6 gap-12">
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-amber-600 rounded-full"></div>
            <div class="w-4 h-4 bg-amber-500 rounded-full"></div>
            <div class="w-4 h-4 bg-amber-600 rounded-full"></div>
          </div>
          <a v-for="tab in navTabs" :key="tab.id"
             @click.prevent="activeTab = tab.id"
             :class="[
               'font-serif text-lg tracking-wider uppercase cursor-pointer transition-all duration-300 relative group',
               activeTab === tab.id 
                 ? 'text-amber-900 font-bold' 
                 : 'text-amber-700 hover:text-amber-900'
             ]">
            <span class="relative z-10">{{ tab.label }}</span>
            <div v-if="activeTab === tab.id" class="absolute -bottom-2 left-0 right-0 h-1 bg-gradient-to-r from-amber-600 to-amber-800 rounded-full"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-amber-200 to-amber-100 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity -z-10"></div>
          </a>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-amber-600 rounded-full"></div>
            <div class="w-4 h-4 bg-amber-500 rounded-full"></div>
            <div class="w-4 h-4 bg-amber-600 rounded-full"></div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Luxurious Home Section -->
    <section v-show="activeTab === 'home'" class="py-20">
      <div class="max-w-6xl mx-auto px-8">
        <!-- Ornate Hero Image Frame -->
        <div class="relative mb-20">
          <!-- Multiple Ornate Borders -->
          <div class="absolute -inset-8 border-8 border-amber-400 opacity-60 rounded-lg"></div>
          <div class="absolute -inset-6 border-4 border-amber-600 opacity-40 rounded-lg"></div>
          <div class="absolute -inset-4 border-2 border-amber-700 opacity-30 rounded-lg"></div>
          
          <!-- Corner Ornaments -->
          <div class="absolute -top-4 -left-4 w-16 h-16 text-4xl text-amber-600">❈</div>
          <div class="absolute -top-4 -right-4 w-16 h-16 text-4xl text-amber-600">❈</div>
          <div class="absolute -bottom-4 -left-4 w-16 h-16 text-4xl text-amber-600">❈</div>
          <div class="absolute -bottom-4 -right-4 w-16 h-16 text-4xl text-amber-600">❈</div>
          
          <img 
            :src="weddingEvent.hero_image" 
            :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
            class="w-full h-[600px] object-cover object-center relative z-10 rounded-lg"
          />
        </div>

        <!-- Ornate Countdown -->
        <div class="bg-gradient-to-r from-amber-100 via-yellow-50 to-amber-100 border-8 border-amber-300 rounded-2xl p-12 mb-20 shadow-2xl">
          <div class="text-center mb-12">
            <div class="flex items-center justify-center mb-6">
              <div class="w-16 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
              <div class="mx-6 text-3xl text-amber-700">❈</div>
              <div class="w-16 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
            </div>
            <p class="font-serif text-4xl text-amber-900 mb-2">Counting Down</p>
            <p class="font-serif text-2xl text-amber-700">to Our Forever</p>
          </div>
          
          <div class="grid grid-cols-4 gap-6">
            <div v-for="unit in countdownUnits" :key="unit.key" class="text-center">
              <div class="relative">
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 border-4 border-amber-400 rounded-xl p-6 shadow-lg">
                  <div class="font-serif text-5xl text-amber-900">{{ countdown[unit.key] }}</div>
                </div>
                <div class="absolute -top-2 -right-2 w-6 h-6 bg-amber-600 rounded-full"></div>
                <div class="absolute -bottom-2 -left-2 w-6 h-6 bg-amber-600 rounded-full"></div>
              </div>
              <div class="font-serif text-sm tracking-wider uppercase text-amber-700 mt-4">{{ unit.label }}</div>
            </div>
          </div>
        </div>

        <!-- Ornate Invitation -->
        <div class="text-center max-w-4xl mx-auto mb-16">
          <div class="bg-white border-8 border-amber-300 rounded-3xl p-12 shadow-2xl relative">
            <!-- Corner Decorations -->
            <div class="absolute top-4 left-4 text-3xl text-amber-500">✦</div>
            <div class="absolute top-4 right-4 text-3xl text-amber-500">✦</div>
            <div class="absolute bottom-4 left-4 text-3xl text-amber-500">✦</div>
            <div class="absolute bottom-4 right-4 text-3xl text-amber-500">✦</div>
            
            <div class="mb-8">
              <div class="flex items-center justify-center mb-6">
                <div class="w-20 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
                <div class="mx-6 text-4xl text-amber-600">❈</div>
                <div class="w-20 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
              </div>
            </div>
            
            <p class="font-serif text-2xl text-amber-900 leading-relaxed mb-8">
              Together with our families, we joyfully invite you to witness and celebrate our sacred union in marriage.
            </p>
            
            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 border-4 border-amber-400 rounded-2xl py-8 px-12 mb-8">
              <p class="font-serif text-3xl text-amber-900 mb-3">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
              <p class="font-serif text-xl text-amber-700 mb-4">{{ weddingEvent.ceremony_time }}</p>
              <div class="w-24 h-px bg-amber-400 mx-auto mb-4"></div>
              <p class="font-serif text-2xl text-amber-800 mb-2">{{ weddingEvent.venue_name }}</p>
              <p class="font-serif text-lg text-amber-600">{{ weddingEvent.venue_address }}</p>
            </div>
            
            <button 
              @click="showRSVPModal = true"
              class="bg-gradient-to-r from-amber-700 via-amber-600 to-amber-700 text-white px-16 py-6 font-serif text-xl tracking-wider uppercase hover:from-amber-800 hover:via-amber-700 hover:to-amber-800 transition-all duration-300 shadow-2xl border-4 border-amber-500 rounded-lg"
            >
              Kindly RSVP
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Ornate Story Section -->
    <section v-show="activeTab === 'story'" class="py-20">
      <div class="max-w-6xl mx-auto px-8">
        <div class="text-center mb-16">
          <div class="flex items-center justify-center mb-8">
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
            <div class="mx-8 text-5xl text-amber-700">❈</div>
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
          </div>
          <h2 class="font-serif text-5xl text-amber-900 mb-4">Our Love Story</h2>
          <div class="flex items-center justify-center mt-8">
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
            <div class="mx-8 text-5xl text-amber-700">❈</div>
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
          </div>
        </div>
        
        <div class="space-y-24">
          <!-- How We Met -->
          <div class="bg-gradient-to-r from-amber-50 via-yellow-50 to-amber-50 border-8 border-amber-300 rounded-3xl p-12 shadow-2xl relative">
            <!-- Corner Ornaments -->
            <div class="absolute top-4 left-4 text-3xl text-amber-500">✦</div>
            <div class="absolute top-4 right-4 text-3xl text-amber-500">✦</div>
            <div class="absolute bottom-4 left-4 text-3xl text-amber-500">✦</div>
            <div class="absolute bottom-4 right-4 text-3xl text-amber-500">✦</div>
            
            <div class="grid md:grid-cols-2 gap-12 items-center">
              <div>
                <div class="flex items-center justify-center mb-8">
                  <div class="w-16 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
                  <div class="mx-6 text-3xl text-amber-700">❈</div>
                  <div class="w-16 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
                </div>
                <h3 class="font-serif text-3xl text-amber-900 mb-6 text-center">How We Met</h3>
                <p class="font-serif text-amber-800 leading-relaxed text-lg">{{ weddingEvent.how_we_met }}</p>
              </div>
              <div class="relative">
                <div class="absolute -inset-4 border-4 border-amber-400 rounded-xl"></div>
                <div class="relative overflow-hidden rounded-xl">
                  <img v-if="weddingEvent.story_image" :src="weddingEvent.story_image" alt="Our story" class="w-full h-80 object-cover object-center" />
                </div>
              </div>
            </div>
          </div>
          
          <!-- The Proposal -->
          <div class="bg-gradient-to-r from-yellow-50 via-amber-50 to-yellow-50 border-8 border-amber-300 rounded-3xl p-12 shadow-2xl relative">
            <!-- Corner Ornaments -->
            <div class="absolute top-4 left-4 text-3xl text-amber-500">✦</div>
            <div class="absolute top-4 right-4 text-3xl text-amber-500">✦</div>
            <div class="absolute bottom-4 left-4 text-3xl text-amber-500">✦</div>
            <div class="absolute bottom-4 right-4 text-3xl text-amber-500">✦</div>
            
            <div class="grid md:grid-cols-2 gap-12 items-center">
              <div class="order-2 md:order-1 relative">
                <div class="absolute -inset-4 border-4 border-amber-400 rounded-xl"></div>
                <div class="relative overflow-hidden rounded-xl">
                  <img v-if="galleryImages[0]" :src="galleryImages[0].url" alt="Proposal" class="w-full h-80 object-cover object-center" />
                </div>
              </div>
              <div class="order-1 md:order-2">
                <div class="flex items-center justify-center mb-8">
                  <div class="w-16 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
                  <div class="mx-6 text-3xl text-amber-700">❈</div>
                  <div class="w-16 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
                </div>
                <h3 class="font-serif text-3xl text-amber-900 mb-6 text-center">The Proposal</h3>
                <p class="font-serif text-amber-800 leading-relaxed text-lg">{{ weddingEvent.proposal_story }}</p>
              </div>
            </div>
          </div>
          
          <!-- Photo Gallery -->
          <div v-if="galleryImages.length > 0">
            <div class="text-center mb-12">
              <div class="flex items-center justify-center mb-8">
                <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
                <div class="mx-8 text-5xl text-amber-700">❈</div>
                <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
              </div>
              <h3 class="font-serif text-4xl text-amber-900 mb-4">Our Journey Together</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
              <div v-for="(image, index) in galleryImages" :key="index" class="relative group">
                <div class="absolute -inset-2 border-2 border-amber-400 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative overflow-hidden rounded-lg border-4 border-amber-300">
                  <img :src="image.url" :alt="`Gallery ${index + 1}`" class="w-full h-56 object-cover object-center group-hover:scale-110 transition-transform duration-500" />
                </div>
                <div class="absolute top-2 right-2 w-6 h-6 bg-amber-600 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Ornate Program Section -->
    <section v-show="activeTab === 'program'" class="py-20">
      <div class="max-w-6xl mx-auto px-8">
        <div class="text-center mb-16">
          <div class="flex items-center justify-center mb-8">
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
            <div class="mx-8 text-5xl text-amber-700">❈</div>
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
          </div>
          <h2 class="font-serif text-5xl text-amber-900 mb-4">Order of Events</h2>
          <div class="flex items-center justify-center mt-8">
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
            <div class="mx-8 text-5xl text-amber-700">❈</div>
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
          </div>
        </div>
        
        <div class="space-y-12">
          <div class="bg-gradient-to-r from-amber-50 via-yellow-50 to-amber-50 border-8 border-amber-300 rounded-3xl p-10 shadow-2xl hover:shadow-3xl transition-shadow relative">
            <!-- Corner Ornaments -->
            <div class="absolute top-4 left-4 text-3xl text-amber-500">✦</div>
            <div class="absolute top-4 right-4 text-3xl text-amber-500">✦</div>
            <div class="absolute bottom-4 left-4 text-3xl text-amber-500">✦</div>
            <div class="absolute bottom-4 right-4 text-3xl text-amber-500">✦</div>
            
            <div class="flex items-center gap-8">
              <div class="relative">
                <div class="w-28 h-28 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center text-white font-serif text-2xl shadow-2xl border-4 border-amber-300">
                  {{ weddingEvent.ceremony_time }}
                </div>
                <div class="absolute -top-2 -right-2 w-6 h-6 bg-amber-600 rounded-full"></div>
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-4 mb-3">
                  <div class="w-12 h-px bg-amber-600"></div>
                  <h3 class="font-serif text-3xl text-amber-900">Wedding Ceremony</h3>
                </div>
                <p class="font-serif text-xl text-amber-700 mb-2">{{ weddingEvent.venue_name }}</p>
                <p class="font-serif text-amber-600">{{ weddingEvent.venue_address }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-gradient-to-r from-yellow-50 via-amber-50 to-yellow-50 border-8 border-amber-300 rounded-3xl p-10 shadow-2xl hover:shadow-3xl transition-shadow relative">
            <!-- Corner Ornaments -->
            <div class="absolute top-4 left-4 text-3xl text-amber-500">✦</div>
            <div class="absolute top-4 right-4 text-3xl text-amber-500">✦</div>
            <div class="absolute bottom-4 left-4 text-3xl text-amber-500">✦</div>
            <div class="absolute bottom-4 right-4 text-3xl text-amber-500">✦</div>
            
            <div class="flex items-center gap-8">
              <div class="relative">
                <div class="w-28 h-28 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center text-white font-serif text-2xl shadow-2xl border-4 border-amber-300">
                  {{ weddingEvent.reception_time }}
                </div>
                <div class="absolute -top-2 -right-2 w-6 h-6 bg-amber-600 rounded-full"></div>
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-4 mb-3">
                  <div class="w-12 h-px bg-amber-600"></div>
                  <h3 class="font-serif text-3xl text-amber-900">Grand Reception</h3>
                </div>
                <p class="font-serif text-xl text-amber-700 mb-2">{{ weddingEvent.reception_venue }}</p>
                <p class="font-serif text-amber-600">{{ weddingEvent.reception_address }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Ornate Location Section -->
    <section v-show="activeTab === 'location'" class="py-20">
      <div class="max-w-6xl mx-auto px-8">
        <div class="text-center mb-16">
          <div class="flex items-center justify-center mb-8">
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
            <div class="mx-8 text-5xl text-amber-700">❈</div>
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
          </div>
          <h2 class="font-serif text-5xl text-amber-900 mb-4">Grand Venue</h2>
          <div class="flex items-center justify-center mt-8">
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
            <div class="mx-8 text-5xl text-amber-700">❈</div>
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
          </div>
        </div>
        
        <div class="bg-gradient-to-r from-amber-50 via-yellow-50 to-amber-50 border-8 border-amber-300 rounded-3xl p-12 shadow-2xl text-center mb-12 relative">
          <!-- Corner Ornaments -->
          <div class="absolute top-4 left-4 text-3xl text-amber-500">✦</div>
          <div class="absolute top-4 right-4 text-3xl text-amber-500">✦</div>
          <div class="absolute bottom-4 left-4 text-3xl text-amber-500">✦</div>
          <div class="absolute bottom-4 right-4 text-3xl text-amber-500">✦</div>
          
          <div class="mb-8">
            <div class="flex items-center justify-center mb-6">
              <div class="w-16 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
              <div class="mx-6 text-4xl text-amber-700">❈</div>
              <div class="w-16 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
            </div>
          </div>
          
          <h3 class="font-serif text-4xl text-amber-900 mb-4">{{ weddingEvent.venue_name }}</h3>
          <p class="font-serif text-xl text-amber-700 mb-6">{{ weddingEvent.venue_address }}</p>
          
          <div class="flex items-center justify-center">
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
            <div class="mx-6 text-3xl text-amber-600">❈</div>
            <div class="w-24 h-px bg-gradient-to-r from-transparent via-amber-600 to-transparent"></div>
          </div>
        </div>
        
        <div class="relative mb-12">
          <!-- Multiple Ornate Borders -->
          <div class="absolute -inset-6 border-6 border-amber-400 opacity-60 rounded-2xl"></div>
          <div class="absolute -inset-4 border-4 border-amber-600 opacity-40 rounded-2xl"></div>
          <div class="absolute -inset-2 border-2 border-amber-700 opacity-30 rounded-2xl"></div>
          
          <!-- Corner Ornaments -->
          <div class="absolute -top-4 -left-4 w-16 h-16 text-4xl text-amber-600">❈</div>
          <div class="absolute -top-4 -right-4 w-16 h-16 text-4xl text-amber-600">❈</div>
          <div class="absolute -bottom-4 -left-4 w-16 h-16 text-4xl text-amber-600">❈</div>
          <div class="absolute -bottom-4 -right-4 w-16 h-16 text-4xl text-amber-600">❈</div>
          
          <div class="relative h-[500px] bg-amber-100 rounded-2xl overflow-hidden group cursor-pointer"
               @click="openGoogleMaps">
            <div class="absolute inset-0 flex items-center justify-center bg-amber-900/80 group-hover:bg-amber-900/90 transition-colors">
              <div class="text-center text-white">
                <svg class="w-20 h-20 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-2xl font-serif">Click to View Map</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="text-center">
          <a 
            :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_address)}`"
            target="_blank"
            class="inline-block bg-gradient-to-r from-amber-700 via-amber-600 to-amber-700 text-white px-16 py-6 font-serif text-xl tracking-wider uppercase hover:from-amber-800 hover:via-amber-700 hover:to-amber-800 transition-all duration-300 shadow-2xl border-4 border-amber-500 rounded-lg"
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
