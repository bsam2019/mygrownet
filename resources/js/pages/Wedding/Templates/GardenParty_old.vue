<template>
  <Head>
    <title>{{ ogMeta.title }}</title>
    <meta name="description" :content="ogMeta.description" />
    <meta property="og:title" :content="ogMeta.title" />
    <meta property="og:description" :content="ogMeta.description" />
    <meta property="og:image" :content="ogMeta.image" />
  </Head>

  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 relative overflow-hidden">
    <!-- Animated Botanical Background -->
    <div class="absolute inset-0 opacity-10">
      <!-- Floating Leaves -->
      <div class="absolute top-10 left-10 text-6xl animate-pulse">🌿</div>
      <div class="absolute top-20 right-20 text-4xl animate-bounce" style="animation-delay: 0.5s;">🍃</div>
      <div class="absolute bottom-32 left-32 text-5xl animate-pulse" style="animation-delay: 1s;">🌱</div>
      <div class="absolute bottom-20 right-16 text-6xl animate-bounce" style="animation-delay: 1.5s;">🌿</div>
      <div class="absolute top-1/2 left-1/4 text-4xl animate-pulse" style="animation-delay: 2s;">🍃</div>
      <div class="absolute top-1/3 right-1/3 text-5xl animate-bounce" style="animation-delay: 2.5s;">🌱</div>
      
      <!-- Organic Shapes -->
      <div class="absolute top-0 left-0 w-96 h-96 bg-gradient-to-br from-green-200 to-emerald-200 rounded-full blur-3xl opacity-30 animate-pulse"></div>
      <div class="absolute bottom-0 right-0 w-80 h-80 bg-gradient-to-br from-emerald-200 to-teal-200 rounded-full blur-3xl opacity-30 animate-pulse" style="animation-delay: 1s;"></div>
      <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-gradient-to-br from-teal-200 to-green-200 rounded-full blur-3xl opacity-30 animate-pulse" style="animation-delay: 2s;"></div>
    </div>
    
    <!-- Organic Header with Nature Elements -->
    <header class="relative bg-gradient-to-r from-emerald-700 via-green-600 to-teal-700 text-white overflow-hidden">
      <!-- Animated Vine Border -->
      <div class="absolute inset-0">
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-green-400 via-emerald-400 to-teal-400"></div>
        <div class="absolute bottom-0 left-0 right-0 h-2 bg-gradient-to-r from-teal-400 via-emerald-400 to-green-400"></div>
      </div>
      
      <!-- Floating Botanical Elements -->
      <div class="absolute inset-0 opacity-20">
        <div class="absolute top-4 left-8 text-3xl animate-bounce">🌸</div>
        <div class="absolute top-8 right-12 text-2xl animate-pulse">🌿</div>
        <div class="absolute bottom-4 left-16 text-3xl animate-bounce" style="animation-delay: 0.7s;">🍃</div>
        <div class="absolute bottom-8 right-8 text-2xl animate-pulse" style="animation-delay: 1.2s;">🌱</div>
      </div>
      
      <div class="max-w-7xl mx-auto px-8 py-16 text-center relative z-10">
        <div class="mb-6">
          <div class="text-6xl mb-4 animate-pulse">🌸</div>
          <h1 class="text-6xl md:text-8xl font-light tracking-wide mb-4 transform hover:scale-105 transition-transform duration-300">
            {{ weddingEvent.groom_name }}
          </h1>
          <div class="text-4xl text-green-200 my-6 animate-pulse">&</div>
          <h1 class="text-6xl md:text-8xl font-light tracking-wide mb-6 transform hover:scale-105 transition-transform duration-300">
            {{ weddingEvent.bride_name }}
          </h1>
          <div class="text-6xl mt-4 animate-pulse">🌸</div>
        </div>
        <p class="text-xl tracking-wider text-green-100 font-light">
          {{ formatDate(weddingEvent.wedding_date) }}
        </p>
      </div>
      
      <!-- Organic Wave Bottom -->
      <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
          <path d="M0 40C60 20 120 60 180 50C240 40 300 20 360 30C420 40 480 60 540 50C600 40 660 20 720 30C780 40 840 60 900 50C960 40 1020 20 1080 30C1140 40 1200 60 1260 50C1320 40 1380 20 1440 30V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0V40Z" fill="url(#gradient1)"/>
          <defs>
            <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stop-color="#065f46"/>
              <stop offset="50%" stop-color="#047857"/>
              <stop offset="100%" stop-color="#0d9488"/>
            </linearGradient>
          </defs>
        </svg>
      </div>
    </header>

    <!-- Organic Navigation with Nature Elements -->
    <nav class="sticky top-0 z-40 bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 border-b-4 border-green-300 shadow-lg backdrop-blur-sm">
      <div class="max-w-7xl mx-auto px-8">
        <div class="flex justify-center items-center py-6 gap-8">
          <!-- Nature Decorations -->
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
            <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse" style="animation-delay: 0.3s;"></div>
            <div class="w-3 h-3 bg-teal-500 rounded-full animate-pulse" style="animation-delay: 0.6s;"></div>
          </div>
          
          <a v-for="tab in navTabs" :key="tab.id"
             @click.prevent="activeTab = tab.id"
             :class="[
               'px-6 py-3 rounded-full text-sm font-light tracking-wide uppercase cursor-pointer transition-all duration-300 relative group overflow-hidden',
               activeTab === tab.id 
                 ? 'bg-gradient-to-r from-green-600 to-emerald-600 text-white shadow-lg transform scale-105' 
                 : 'text-green-700 hover:bg-gradient-to-r hover:from-green-100 hover:to-emerald-100 hover:text-green-800 hover:shadow-md'
             ]">
            <span class="relative z-10">{{ tab.label }}</span>
            <div v-if="activeTab === tab.id" class="absolute inset-0 bg-gradient-to-r from-green-500 to-emerald-500 opacity-20 animate-pulse"></div>
            <!-- Hover Effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-emerald-400 opacity-0 group-hover:opacity-10 transition-opacity"></div>
          </a>
          
          <!-- Nature Decorations -->
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-teal-500 rounded-full animate-pulse" style="animation-delay: 0.9s;"></div>
            <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse" style="animation-delay: 1.2s;"></div>
            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse" style="animation-delay: 1.5s;"></div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Organic Home Section with Nature Elements -->
    <section v-show="activeTab === 'home'" class="py-20">
      <div class="max-w-7xl mx-auto px-8">
        <!-- Hero Image with Organic Frame -->
        <div class="relative mb-20 group">
          <!-- Animated Organic Border -->
          <div class="absolute -inset-6 bg-gradient-to-br from-green-200 via-emerald-200 to-teal-200 rounded-3xl transform rotate-1 group-hover:rotate-0 transition-transform duration-500 opacity-60"></div>
          <div class="absolute -inset-4 bg-gradient-to-br from-emerald-200 via-teal-200 to-green-200 rounded-3xl transform -rotate-1 group-hover:rotate-0 transition-transform duration-500 opacity-40"></div>
          
          <!-- Floating Botanical Elements -->
          <div class="absolute -top-8 -left-8 text-6xl animate-bounce">🌿</div>
          <div class="absolute -top-6 -right-6 text-5xl animate-pulse" style="animation-delay: 0.5s;">🍃</div>
          <div class="absolute -bottom-8 -left-6 text-6xl animate-pulse" style="animation-delay: 1s;">🌱</div>
          <div class="absolute -bottom-6 -right-8 text-5xl animate-bounce" style="animation-delay: 1.5s;">🌿</div>
          
          <div class="relative bg-white p-4 rounded-3xl shadow-2xl overflow-hidden">
            <img 
              :src="weddingEvent.hero_image" 
              :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
              class="w-full h-[600px] object-cover object-center rounded-2xl group-hover:scale-105 transition-transform duration-700"
            />
            <!-- Overlay Effect -->
            <div class="absolute inset-0 bg-gradient-to-t from-green-900/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          </div>
        </div>

        <!-- Animated Nature-Inspired Countdown -->
        <div class="bg-gradient-to-r from-green-100 via-emerald-50 to-teal-100 rounded-3xl p-12 mb-20 shadow-2xl relative overflow-hidden">
          <!-- Background Animation -->
          <div class="absolute inset-0 opacity-10">
            <div class="absolute top-4 left-4 text-4xl animate-pulse">🌱</div>
            <div class="absolute top-8 right-8 text-3xl animate-bounce" style="animation-delay: 0.7s;">🍃</div>
            <div class="absolute bottom-4 left-8 text-4xl animate-bounce" style="animation-delay: 1.4s;">🌿</div>
            <div class="absolute bottom-8 right-4 text-3xl animate-pulse" style="animation-delay: 2.1s;">🌱</div>
          </div>
          
          <div class="text-center mb-10 relative z-10">
            <div class="text-5xl mb-4 animate-pulse">🌱</div>
            <p class="text-3xl text-green-800 mb-2 font-light">Growing Together In</p>
          </div>
          
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6 relative z-10">
            <div v-for="unit in countdownUnits" :key="unit.key" class="text-center group">
              <div class="bg-white rounded-3xl p-8 shadow-lg border-2 border-green-200 hover:border-green-400 transition-all duration-300 transform hover:scale-105 hover:rotate-1">
                <div class="text-5xl font-bold text-green-700 group-hover:text-green-800 transition-colors">{{ countdown[unit.key] }}</div>
              </div>
              <div class="text-sm tracking-wider uppercase text-green-600 mt-3 font-light">{{ unit.label }}</div>
            </div>
          </div>
        </div>

        <!-- Organic Invitation with Botanical Theme -->
        <div class="text-center max-w-5xl mx-auto mb-16">
          <div class="bg-white rounded-3xl p-12 shadow-2xl border-4 border-green-200 relative overflow-hidden group">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 opacity-5">
              <div class="absolute top-8 left-8 text-6xl animate-pulse">🌸</div>
              <div class="absolute top-12 right-12 text-5xl animate-bounce" style="animation-delay: 0.8s;">🌿</div>
              <div class="absolute bottom-8 left-12 text-6xl animate-bounce" style="animation-delay: 1.6s;">🍃</div>
              <div class="absolute bottom-12 right-8 text-5xl animate-pulse" style="animation-delay: 2.4s;">🌸</div>
            </div>
            
            <div class="relative z-10">
              <div class="mb-8">
                <div class="text-6xl mb-6 animate-pulse">🌸 🌿 🌸</div>
              </div>
              
              <p class="text-2xl text-gray-700 leading-relaxed mb-10 font-light">
                Like two trees growing side by side, our roots have intertwined. Join us as we celebrate our love in the beauty of nature.
              </p>
              
              <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-3xl p-10 mb-10 border-2 border-green-300">
                <div class="text-5xl mb-6 animate-pulse">🌳</div>
                <p class="text-3xl text-green-800 mb-4 font-light">{{ formatDateFull(weddingEvent.wedding_date) }}</p>
                <p class="text-xl text-green-600 mb-6">{{ weddingEvent.ceremony_time }}</p>
                <div class="w-32 h-px bg-green-300 mx-auto mb-6"></div>
                <p class="text-2xl text-green-700 mb-3 font-light">{{ weddingEvent.venue_name }}</p>
                <p class="text-lg text-green-600">{{ weddingEvent.venue_address }}</p>
                <div class="mt-6 text-5xl animate-pulse">🌿</div>
              </div>
              
              <div class="mb-10">
                <div class="text-6xl animate-pulse">🌸 🌿 🌸</div>
              </div>
              
              <button 
                @click="showRSVPModal = true"
                class="bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 text-white px-16 py-6 rounded-full text-xl tracking-wide uppercase hover:from-green-700 hover:via-emerald-700 hover:to-teal-700 transition-all duration-300 shadow-2xl hover:shadow-3xl transform hover:scale-105 font-light group relative overflow-hidden"
              >
                <span class="relative z-10">🌱 RSVP Now</span>
                <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-emerald-400 opacity-0 group-hover:opacity-20 transition-opacity"></div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Animated Story Section with Nature Elements -->
    <section v-show="activeTab === 'story'" class="py-20">
      <div class="max-w-7xl mx-auto px-8">
        <div class="text-center mb-16">
          <div class="text-6xl mb-6 animate-pulse">🌸</div>
          <h2 class="text-5xl text-green-800 mb-4 font-light">Growing Together</h2>
          <div class="text-6xl mt-6 animate-pulse">🌿</div>
        </div>
        
        <div class="space-y-24">
          <!-- How We Met -->
          <div class="bg-white rounded-3xl p-12 shadow-2xl border-4 border-green-200 relative overflow-hidden group">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 opacity-5">
              <div class="absolute top-6 left-6 text-5xl animate-pulse">🌱</div>
              <div class="absolute top-10 right-10 text-4xl animate-bounce" style="animation-delay: 0.6s;">🍃</div>
              <div class="absolute bottom-6 left-10 text-5xl animate-bounce" style="animation-delay: 1.2s;">🌿</div>
              <div class="absolute bottom-10 right-6 text-4xl animate-pulse" style="animation-delay: 1.8s;">🌱</div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-12 items-center relative z-10">
              <div>
                <div class="text-5xl mb-6 text-center animate-pulse">🌱</div>
                <h3 class="text-3xl text-green-800 mb-6 text-center font-light">How We Met</h3>
                <p class="text-green-700 leading-relaxed text-lg">{{ weddingEvent.how_we_met }}</p>
              </div>
              <div class="relative group">
                <div class="absolute -inset-3 border-3 border-green-300 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative overflow-hidden rounded-2xl border-2 border-green-300">
                  <img v-if="weddingEvent.story_image" :src="weddingEvent.story_image" alt="Our story" class="w-full h-80 object-cover object-center group-hover:scale-110 transition-transform duration-700" />
                </div>
                <!-- Floating Leaves -->
                <div class="absolute -top-4 -right-4 text-3xl animate-bounce">🍃</div>
                <div class="absolute -bottom-4 -left-4 text-3xl animate-pulse" style="animation-delay: 0.8s;">🌿</div>
              </div>
            </div>
          </div>
          
          <!-- The Proposal -->
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-3xl p-12 shadow-2xl border-4 border-emerald-200 relative overflow-hidden group">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 opacity-5">
              <div class="absolute top-6 left-6 text-5xl animate-bounce" style="animation-delay: 0.4s;">🍃</div>
              <div class="absolute top-10 right-10 text-4xl animate-pulse" style="animation-delay: 1s;">🌱</div>
              <div class="absolute bottom-6 left-10 text-5xl animate-pulse" style="animation-delay: 1.6s;">🌿</div>
              <div class="absolute bottom-10 right-6 text-4xl animate-bounce" style="animation-delay: 2.2s;">🍃</div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-12 items-center relative z-10">
              <div class="order-2 md:order-1 relative group">
                <div class="absolute -inset-3 border-3 border-emerald-300 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative overflow-hidden rounded-2xl border-2 border-emerald-300">
                  <img v-if="galleryImages[0]" :src="galleryImages[0].url" alt="Proposal" class="w-full h-80 object-cover object-center group-hover:scale-110 transition-transform duration-700" />
                </div>
                <!-- Floating Leaves -->
                <div class="absolute -top-4 -left-4 text-3xl animate-pulse">🌿</div>
                <div class="absolute -bottom-4 -right-4 text-3xl animate-bounce" style="animation-delay: 0.9s;">🍃</div>
              </div>
              <div class="order-1 md:order-2">
                <div class="text-5xl mb-6 text-center animate-pulse">💍</div>
                <h3 class="text-3xl text-green-800 mb-6 text-center font-light">The Proposal</h3>
                <p class="text-green-700 leading-relaxed text-lg">{{ weddingEvent.proposal_story }}</p>
              </div>
            </div>
          </div>
          
          <!-- Photo Gallery -->
          <div v-if="galleryImages.length > 0">
            <div class="text-center mb-12">
              <div class="text-6xl mb-6 animate-pulse">📸</div>
              <h3 class="text-4xl text-green-800 font-light">Our Journey Together</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
              <div v-for="(image, index) in galleryImages" :key="index" class="relative group">
                <div class="absolute -inset-2 border-2 border-green-300 rounded-2xl opacity-0 group-hover:opacity-100 transition-transform duration-300 group-hover:rotate-1"></div>
                <div class="relative overflow-hidden rounded-2xl border-2 border-green-200">
                  <img :src="image.url" :alt="`Gallery ${index + 1}`" class="w-full h-56 object-cover object-center group-hover:scale-110 transition-transform duration-700" />
                </div>
                <!-- Floating Elements -->
                <div class="absolute -top-2 -right-2 text-2xl opacity-0 group-hover:opacity-100 transition-opacity animate-pulse">🌿</div>
                <div class="absolute -bottom-2 -left-2 text-2xl opacity-0 group-hover:opacity-100 transition-opacity animate-bounce" style="animation-delay: 0.3s;">🍃</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Animated Program Section with Nature Elements -->
    <section v-show="activeTab === 'program'" class="py-20">
      <div class="max-w-7xl mx-auto px-8">
        <div class="text-center mb-16">
          <div class="text-6xl mb-6 animate-pulse">🌿</div>
          <h2 class="text-5xl text-green-800 mb-4 font-light">Celebration Timeline</h2>
          <div class="text-6xl mt-6 animate-pulse">🌿</div>
        </div>
        
        <div class="space-y-12">
          <div class="bg-white rounded-3xl p-12 shadow-2xl border-4 border-green-200 hover:shadow-3xl transition-all duration-300 relative overflow-hidden group">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 opacity-5">
              <div class="absolute top-6 left-6 text-4xl animate-pulse">🌱</div>
              <div class="absolute top-8 right-8 text-3xl animate-bounce" style="animation-delay: 0.5s;">🍃</div>
              <div class="absolute bottom-6 left-8 text-4xl animate-bounce" style="animation-delay: 1s;">🌿</div>
              <div class="absolute bottom-8 right-6 text-3xl animate-pulse" style="animation-delay: 1.5s;">🌱</div>
            </div>
            
            <div class="flex items-center gap-8 relative z-10">
              <div class="relative group">
                <div class="w-28 h-28 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center text-white text-4xl shadow-2xl border-4 border-green-200 transform group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                  🌸
                </div>
                <div class="absolute -top-2 -right-2 w-6 h-6 bg-green-500 rounded-full animate-pulse"></div>
              </div>
              <div class="flex-1">
                <div class="text-sm text-green-600 mb-2 font-light tracking-wide uppercase">{{ weddingEvent.ceremony_time }}</div>
                <h3 class="text-3xl text-green-800 mb-3 font-light">Ceremony</h3>
                <p class="text-green-600 text-lg mb-2">{{ weddingEvent.venue_name }}</p>
                <p class="text-green-500">{{ weddingEvent.venue_address }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-gradient-to-r from-emerald-50 to-green-50 rounded-3xl p-12 shadow-2xl border-4 border-emerald-200 hover:shadow-3xl transition-all duration-300 relative overflow-hidden group">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0 opacity-5">
              <div class="absolute top-6 left-6 text-4xl animate-bounce" style="animation-delay: 0.7s;">🍃</div>
              <div class="absolute top-8 right-8 text-3xl animate-pulse" style="animation-delay: 1.2s;">🌱</div>
              <div class="absolute bottom-6 left-8 text-4xl animate-pulse" style="animation-delay: 1.7s;">🌿</div>
              <div class="absolute bottom-8 right-6 text-3xl animate-bounce" style="animation-delay: 2.2s;">🍃</div>
            </div>
            
            <div class="flex items-center gap-8 relative z-10">
              <div class="relative group">
                <div class="w-28 h-28 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white text-4xl shadow-2xl border-4 border-emerald-200 transform group-hover:scale-110 group-hover:-rotate-6 transition-all duration-300">
                  🎉
                </div>
                <div class="absolute -top-2 -right-2 w-6 h-6 bg-emerald-500 rounded-full animate-pulse" style="animation-delay: 0.3s;"></div>
              </div>
              <div class="flex-1">
                <div class="text-sm text-emerald-600 mb-2 font-light tracking-wide uppercase">{{ weddingEvent.reception_time }}</div>
                <h3 class="text-3xl text-green-800 mb-3 font-light">Garden Reception</h3>
                <p class="text-emerald-600 text-lg mb-2">{{ weddingEvent.reception_venue }}</p>
                <p class="text-emerald-500">{{ weddingEvent.reception_address }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Animated Location Section with Nature Elements -->
    <section v-show="activeTab === 'location'" class="py-20">
      <div class="max-w-7xl mx-auto px-8">
        <div class="text-center mb-16">
          <div class="text-6xl mb-6 animate-pulse">🗺️</div>
          <h2 class="text-5xl text-green-800 mb-4 font-light">Find Us</h2>
          <div class="text-6xl mt-6 animate-pulse">🌿</div>
        </div>
        
        <div class="bg-white rounded-3xl p-12 shadow-2xl border-4 border-green-200 text-center mb-12 relative overflow-hidden group">
          <!-- Animated Background Elements -->
          <div class="absolute inset-0 opacity-5">
            <div class="absolute top-6 left-6 text-5xl animate-pulse">🌳</div>
            <div class="absolute top-10 right-10 text-4xl animate-bounce" style="animation-delay: 0.6s;">🍃</div>
            <div class="absolute bottom-6 left-10 text-5xl animate-bounce" style="animation-delay: 1.2s;">🌿</div>
            <div class="absolute bottom-10 right-6 text-4xl animate-pulse" style="animation-delay: 1.8s;">🌳</div>
          </div>
          
          <div class="relative z-10">
            <div class="text-5xl mb-6 animate-pulse">🌳</div>
            <h3 class="text-4xl text-green-800 mb-4 font-light">{{ weddingEvent.venue_name }}</h3>
            <p class="text-xl text-green-600 mb-6">{{ weddingEvent.venue_address }}</p>
            <div class="text-5xl mt-6 animate-pulse">🌿</div>
          </div>
        </div>
        
        <div class="relative mb-12 group">
          <!-- Animated Organic Borders -->
          <div class="absolute -inset-6 border-4 border-green-300 rounded-3xl opacity-60 transform rotate-1 group-hover:rotate-0 transition-transform duration-500"></div>
          <div class="absolute -inset-4 border-3 border-emerald-300 rounded-3xl opacity-40 transform -rotate-1 group-hover:rotate-0 transition-transform duration-500"></div>
          <div class="absolute -inset-2 border-2 border-teal-300 rounded-3xl opacity-30 transform rotate-1 group-hover:rotate-0 transition-transform duration-500"></div>
          
          <!-- Floating Botanical Elements -->
          <div class="absolute -top-8 -left-8 text-6xl animate-bounce">🌿</div>
          <div class="absolute -top-6 -right-6 text-5xl animate-pulse" style="animation-delay: 0.5s;">🍃</div>
          <div class="absolute -bottom-8 -left-6 text-6xl animate-pulse" style="animation-delay: 1s;">🌱</div>
          <div class="absolute -bottom-6 -right-8 text-5xl animate-bounce" style="animation-delay: 1.5s;">🌿</div>
          
          <div class="relative h-[500px] bg-green-100 rounded-3xl overflow-hidden group cursor-pointer"
               @click="openGoogleMaps">
            <div class="absolute inset-0 flex items-center justify-center bg-green-900/80 group-hover:bg-green-900/90 transition-colors">
              <div class="text-center text-white">
                <svg class="w-20 h-20 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-2xl font-light mb-2">🌿 Click to View Map</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="text-center">
          <a 
            :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_address)}`"
            target="_blank"
            class="inline-block bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 text-white px-16 py-6 rounded-full text-xl tracking-wide uppercase hover:from-green-700 hover:via-emerald-700 hover:to-teal-700 transition-all duration-300 shadow-2xl hover:shadow-3xl transform hover:scale-105 font-light group relative overflow-hidden"
          >
            <span class="relative z-10">🌿 Get Directions</span>
            <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-emerald-400 opacity-0 group-hover:opacity-20 transition-opacity"></div>
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
