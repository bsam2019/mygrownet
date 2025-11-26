<template>
  <!-- Open Graph Meta Tags for Social Sharing -->
  <Head>
    <title>{{ ogMeta.title || `${weddingEvent.groom_name} & ${weddingEvent.bride_name} Wedding` }}</title>
    <meta name="description" :content="ogMeta.description || `You are invited to celebrate the wedding of ${weddingEvent.groom_name} & ${weddingEvent.bride_name}`" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" :content="ogMeta.type || 'website'" />
    <meta property="og:url" :content="ogMeta.url || ''" />
    <meta property="og:title" :content="ogMeta.title || `${weddingEvent.groom_name} & ${weddingEvent.bride_name} Wedding`" />
    <meta property="og:description" :content="ogMeta.description || `You are invited to celebrate the wedding of ${weddingEvent.groom_name} & ${weddingEvent.bride_name}`" />
    <meta property="og:image" :content="ogMeta.image || ''" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:url" :content="ogMeta.url || ''" />
    <meta name="twitter:title" :content="ogMeta.title || `${weddingEvent.groom_name} & ${weddingEvent.bride_name} Wedding`" />
    <meta name="twitter:description" :content="ogMeta.description || `You are invited to celebrate the wedding of ${weddingEvent.groom_name} & ${weddingEvent.bride_name}`" />
    <meta name="twitter:image" :content="ogMeta.image || ''" />
    
    <!-- WhatsApp specific -->
    <meta property="og:site_name" content="Wedding Invitation" />
  </Head>

  <div class="min-h-screen relative bg-gradient-to-br from-pink-50 via-purple-50 to-blue-50 overflow-hidden">
    <!-- Flora decorative background - top header decoration -->
    <!-- On mobile: starts below the fixed nav (58px to account for border), on desktop: starts at top -->
    <div class="absolute top-[58px] md:top-0 left-0 right-0 z-0 pointer-events-none">
      <div class="h-[30vh] md:h-[40vh]">
        <img 
          src="/images/Wedding/flora.jpg" 
          alt="" 
          aria-hidden="true"
          class="w-full h-full object-cover object-top"
        />
      </div>
    </div>

    <!-- Fixed Mobile Header with Active Tab Name - with background -->
    <div class="md:hidden fixed top-0 left-0 right-0 z-40 bg-white/90 backdrop-blur-sm border-b border-purple-100 shadow-sm">
      <div class="flex items-center justify-between px-4 py-3">
        <!-- Hamburger Menu Button -->
        <button 
          @click="toggleMobileMenu"
          aria-label="Toggle navigation menu"
          class="w-10 h-10 flex items-center justify-center rounded-sm text-purple-600 hover:text-purple-800 hover:bg-purple-100 transition-all duration-200"
        >
          <!-- Custom Hamburger Icon with wider lines and more spacing -->
          <div v-if="!mobileMenuOpen" class="flex flex-col justify-center items-center space-y-1.5" aria-hidden="true">
            <span class="block w-6 h-0.5 bg-current"></span>
            <span class="block w-6 h-0.5 bg-current"></span>
            <span class="block w-6 h-0.5 bg-current"></span>
          </div>
          <XMarkIcon v-else class="h-7 w-7" aria-hidden="true" />
        </button>
        
        <!-- Active Tab Name - Centered -->
        <h2 class="text-sm font-medium text-purple-700 tracking-[0.15em] uppercase">
          {{ activeTabLabel }}
        </h2>
        
        <!-- Share Button -->
        <button 
          @click="toggleShareMenu"
          aria-label="Share wedding invitation"
          class="w-10 h-10 flex items-center justify-center rounded-sm text-purple-600 hover:text-purple-800 hover:bg-purple-100 transition-all duration-200"
        >
          <ShareIcon class="h-5 w-5" aria-hidden="true" />
        </button>
      </div>
    </div>

    <!-- Share Menu Dropdown -->
    <div 
      v-if="shareMenuOpen" 
      class="fixed top-14 right-4 z-50 bg-white rounded-lg shadow-lg border border-gray-200 py-2 min-w-48"
    >
      <button 
        @click="shareViaWhatsApp"
        class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors"
      >
        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        <span>Share via WhatsApp</span>
      </button>
      <button 
        @click="shareViaFacebook"
        class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors"
      >
        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
        </svg>
        <span>Share on Facebook</span>
      </button>
      <button 
        @click="copyLink"
        class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors"
      >
        <LinkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
        <span>{{ linkCopied ? 'Link Copied!' : 'Copy Link' }}</span>
      </button>
    </div>

    <!-- Share Menu Backdrop -->
    <div 
      v-if="shareMenuOpen" 
      @click="shareMenuOpen = false"
      class="fixed inset-0 z-40"
      aria-hidden="true"
    ></div>

    <!-- Main Content Wrapper - sits above background -->
    <div class="relative z-10">
    <!-- Top Spacing (accounts for fixed mobile header + flora visibility) -->
    <div class="h-[12vh] md:h-12 lg:h-16"></div>

    <!-- Header Section - transparent to show flora behind -->
    <header class="relative pt-1 md:pt-20 lg:pt-24 pb-4 md:pb-6 text-center">
      <div class="max-w-4xl mx-auto px-4">

        <!-- Couple Names - Hidden on mobile, shown on desktop -->
        <h1 class="hidden md:flex md:flex-col md:items-center text-4xl md:text-6xl lg:text-7xl bg-gradient-to-r from-pink-600 via-purple-600 to-blue-600 bg-clip-text text-transparent mb-4 drop-shadow-lg" style="font-family: 'Great Vibes', cursive;">
          <span>{{ weddingEvent.groom_name }}</span>
          <span class="text-2xl md:text-3xl bg-gradient-to-r from-pink-500 to-purple-500 bg-clip-text text-transparent">&</span>
          <span>{{ weddingEvent.bride_name }}</span>
        </h1>
        <p class="hidden md:block text-sm md:text-base text-purple-700 font-semibold tracking-[0.2em] mb-6 uppercase">
          {{ formatWeddingDate(weddingEvent.wedding_date) }}
        </p>
        
        <!-- Desktop Navigation Tabs - with subtle background for readability -->
        <nav class="hidden md:block relative z-10 border-b-2 border-purple-200 bg-white backdrop-blur-md rounded-t-lg -mx-4 px-4 shadow-md">
          <div class="flex justify-center items-center">
            <div class="flex space-x-6 md:space-x-10 text-xs md:text-sm font-normal tracking-[0.1em]">
              <a 
                href="javascript:void(0)" 
                :class="activeTab === 'home' ? 'text-purple-700 border-b-2 border-purple-500 pb-4 font-semibold' : 'text-purple-400 hover:text-purple-600 pb-4'"
                @click.prevent="setActiveTab('home')"
                class="transition-colors"
              >
                Home
              </a>
              <a 
                href="javascript:void(0)" 
                :class="activeTab === 'program' ? 'text-purple-700 border-b-2 border-purple-500 pb-4 font-semibold' : 'text-purple-400 hover:text-purple-600 pb-4'"
                @click.prevent="setActiveTab('program')"
                class="transition-colors"
              >
                Wedding Program
              </a>
              <a 
                href="javascript:void(0)" 
                :class="activeTab === 'qa' ? 'text-purple-700 border-b-2 border-purple-500 pb-4 font-semibold' : 'text-purple-400 hover:text-purple-600 pb-4'"
                @click.prevent="setActiveTab('qa')"
                class="transition-colors"
              >
                Q + A
              </a>
              <a 
                href="javascript:void(0)" 
                :class="activeTab === 'location' ? 'text-purple-700 border-b-2 border-purple-500 pb-4 font-semibold' : 'text-purple-400 hover:text-purple-600 pb-4'"
                @click.prevent="setActiveTab('location')"
                class="transition-colors"
              >
                Location
              </a>
              <button 
                :class="activeTab === 'rsvp' ? 'text-purple-700 border-b-2 border-purple-500 pb-4 font-semibold' : 'text-purple-400 hover:text-purple-600 pb-4'"
                @click="openRSVPModal"
                class="transition-colors"
              >
                RSVP
              </button>
              <!-- Desktop Share Button -->
              <div class="relative">
                <button 
                  @click="toggleShareMenu"
                  class="text-purple-400 hover:text-purple-600 pb-4 transition-colors flex items-center gap-1"
                  aria-label="Share wedding invitation"
                >
                  <ShareIcon class="h-4 w-4" aria-hidden="true" />
                  <span>Share</span>
                </button>
              </div>
            </div>
          </div>
        </nav>

        <!-- Mobile Menu Overlay -->
        <Transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="-translate-y-full opacity-0"
          enter-to-class="translate-y-0 opacity-100"
          leave-active-class="transition-all duration-300 ease-in"
          leave-from-class="translate-y-0 opacity-100"
          leave-to-class="-translate-y-full opacity-0"
        >
          <div 
            v-if="mobileMenuOpen" 
            class="md:hidden fixed inset-0 bg-white z-50 flex flex-col items-center justify-center"
          >
            <!-- Close Button - Matching Style -->
            <button 
              @click="toggleMobileMenu"
              aria-label="Close navigation menu"
              class="absolute top-4 left-4 w-10 h-10 flex items-center justify-center rounded-sm bg-gray-50 border border-gray-200 text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-all duration-200"
            >
              <XMarkIcon class="h-5 w-5" aria-hidden="true" />
            </button>

            <!-- Centered Menu Items -->
            <nav class="flex flex-col items-center space-y-8 text-lg font-light tracking-[0.15em]">
              <a 
                href="javascript:void(0)" 
                :class="activeTab === 'home' ? 'text-gray-700' : 'text-gray-400'"
                @click.prevent="setActiveTabMobile('home')"
                class="hover:text-gray-600 transition-colors"
              >
                Home
              </a>
              <a 
                href="javascript:void(0)" 
                :class="activeTab === 'program' ? 'text-gray-700' : 'text-gray-400'"
                @click.prevent="setActiveTabMobile('program')"
                class="hover:text-gray-600 transition-colors"
              >
                Wedding Program
              </a>
              <a 
                href="javascript:void(0)" 
                :class="activeTab === 'qa' ? 'text-gray-700' : 'text-gray-400'"
                @click.prevent="setActiveTabMobile('qa')"
                class="hover:text-gray-600 transition-colors"
              >
                Q + A
              </a>
              <a 
                href="javascript:void(0)" 
                :class="activeTab === 'location' ? 'text-gray-700' : 'text-gray-400'"
                @click.prevent="setActiveTabMobile('location')"
                class="hover:text-gray-600 transition-colors"
              >
                Location
              </a>
              <button 
                :class="activeTab === 'rsvp' ? 'text-gray-700' : 'text-gray-400'"
                @click="openRSVPModalMobile"
                class="hover:text-gray-600 transition-colors"
              >
                RSVP
              </button>
            </nav>
          </div>
        </Transition>
      </div>
    </header>

    <!-- Tab Content -->
    <main>
      <!-- Home Tab -->
      <section v-show="activeTab === 'home'" id="home" class="pt-2 pb-4 md:py-12 text-center">
        <div class="max-w-4xl mx-auto px-4">
          <!-- Mobile Couple Names - Shown above hero on mobile only -->
          <div class="md:hidden text-center mb-2">
            <h1 class="flex flex-col items-center mb-1" style="font-family: 'Great Vibes', cursive;">
              <span class="text-5xl bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">{{ weddingEvent.groom_name }}</span>
              <span class="text-3xl bg-gradient-to-r from-pink-500 to-purple-500 bg-clip-text text-transparent">&</span>
              <span class="text-5xl bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">{{ weddingEvent.bride_name }}</span>
            </h1>
            <p class="text-xs text-purple-700 font-semibold tracking-[0.15em] uppercase">
              {{ formatWeddingDate(weddingEvent.wedding_date) }}
            </p>
          </div>

          <!-- Hero Image -->
          <div class="relative w-full mx-auto mb-6 md:mb-10 -mx-4 md:mx-0">
            <div class="flex justify-center">
              <div class="w-full md:w-auto md:max-w-sm lg:max-w-md xl:max-w-lg">
                <div class="overflow-hidden shadow-2xl md:rounded-lg border-4 border-white">
                  <img 
                    :src="weddingEvent.hero_image || '/images/Wedding/main.jpg'" 
                    :alt="`${weddingEvent.bride_name} and ${weddingEvent.groom_name}`"
                    class="w-full h-auto object-cover object-center"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Countdown Timer -->
          <div class="max-w-md mx-auto mb-8 md:mb-10 px-2">
            <div class="bg-white/80 backdrop-blur-sm rounded-lg shadow-lg p-4 md:p-6 border border-purple-100">
              <p class="text-xs text-purple-600 tracking-[0.2em] uppercase mb-4 text-center font-medium">Counting Down To</p>
              <div class="grid grid-cols-4 gap-2 md:gap-4">
                <!-- Days -->
                <div class="text-center">
                  <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg shadow-sm p-2 md:p-3 border border-purple-200">
                    <div class="text-xl md:text-3xl font-bold text-purple-700">
                      {{ countdown.days }}
                    </div>
                    <div class="text-[10px] md:text-xs text-purple-600 mt-1 tracking-wide uppercase font-medium">Days</div>
                  </div>
                </div>
                <!-- Hours -->
                <div class="text-center">
                  <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg shadow-sm p-2 md:p-3 border border-purple-200">
                    <div class="text-xl md:text-3xl font-bold text-purple-700">
                      {{ countdown.hours }}
                    </div>
                    <div class="text-[10px] md:text-xs text-purple-600 mt-1 tracking-wide uppercase font-medium">Hours</div>
                  </div>
                </div>
                <!-- Minutes -->
                <div class="text-center">
                  <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg shadow-sm p-2 md:p-3 border border-purple-200">
                    <div class="text-xl md:text-3xl font-bold text-purple-700">
                      {{ countdown.minutes }}
                    </div>
                    <div class="text-[10px] md:text-xs text-purple-600 mt-1 tracking-wide uppercase font-medium">Minutes</div>
                  </div>
                </div>
                <!-- Seconds -->
                <div class="text-center">
                  <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg shadow-sm p-2 md:p-3 border border-purple-200">
                    <div class="text-xl md:text-3xl font-bold text-purple-700">
                      {{ countdown.seconds }}
                    </div>
                    <div class="text-[10px] md:text-xs text-purple-600 mt-1 tracking-wide uppercase font-medium">Seconds</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Wedding Invitation Message -->
          <div class="max-w-2xl mx-auto mb-8 md:mb-12 px-4">
            <div class="text-center space-y-4 md:space-y-6">
              <p class="text-gray-800 text-base md:text-lg leading-relaxed font-light">
                Kaoma and Mubanga warmly invite you to join us as we celebrate the gift of love and the blessing of becoming one in Christ Jesus.
              </p>
              
              <div class="py-4">
                <div class="w-16 h-px bg-gray-400 mx-auto mb-4"></div>
                <p class="text-gray-900 text-sm md:text-base font-normal tracking-wide">
                  Saturday, 6th December 2035
                </p>
                <p class="text-gray-800 text-sm md:text-base mt-2">
                  11:00 in the morning
                </p>
                <p class="text-gray-800 text-sm md:text-base mt-2 italic">
                  3 Sixty Convention Centre
                </p>
                <div class="w-16 h-px bg-gray-400 mx-auto mt-4"></div>
              </div>
              
              <p class="text-gray-800 text-base md:text-lg leading-relaxed font-light">
                A joyful reception will follow at the same venue as we continue the celebration with family and friends.
              </p>
              
              <div class="pt-4">
                <p class="text-gray-700 text-xs md:text-sm tracking-wider uppercase">
                  Kindly RSVP by {{ formatRSVPDate(weddingEvent.rsvp_deadline) }}
                </p>
              </div>
            </div>
          </div>

          <!-- RSVP Button -->
          <div class="mb-2 md:mb-10">
            <button 
              @click="openRSVPModal"
              class="inline-block bg-gradient-to-r from-pink-500 to-purple-600 text-white px-12 py-4 text-sm font-semibold tracking-[0.15em] hover:from-pink-600 hover:to-purple-700 transition-all duration-300 rounded-full shadow-lg hover:shadow-xl transform hover:scale-105"
            >
              RSVP NOW
            </button>
          </div>
        </div>
      </section>

      <!-- Wedding Program Tab -->
      <section v-show="activeTab === 'program'" id="program" class="py-16">
        <div class="max-w-4xl mx-auto px-4">
          <!-- Hidden on mobile since mobile header shows tab name -->
          <div class="hidden md:block text-center mb-16">
            <span class="text-3xl">💒</span>
            <h2 class="text-2xl font-light text-gray-700 mb-4 tracking-[0.15em] mt-2">WEDDING PROGRAM</h2>
          </div>
          
          <div class="space-y-4">
            <!-- Arrival of Guests -->
            <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-lg p-6 border border-rose-100">
              <div class="flex items-center gap-4 mb-3">
                <div class="w-12 h-12 bg-rose-200 rounded-full flex items-center justify-center">
                  <HeartIcon class="h-6 w-6 text-rose-700" aria-hidden="true" />
                </div>
                <div>
                  <h3 class="text-base font-medium text-rose-900 tracking-[0.1em]">ARRIVAL OF GUESTS</h3>
                  <p class="text-sm text-rose-700">📍 The Chapel</p>
                  <p class="text-sm text-rose-600">� 10:20 AM -0 10:45 AM</p>
                </div>
              </div>
            </div>

            <!-- Marriage Blessing Ceremony -->
            <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-lg p-6 border border-rose-100">
              <div class="flex items-center gap-4 mb-3">
                <div class="w-12 h-12 bg-rose-200 rounded-full flex items-center justify-center">
                  <HeartIcon class="h-6 w-6 text-rose-700" aria-hidden="true" />
                </div>
                <div>
                  <h3 class="text-base font-medium text-rose-900 tracking-[0.1em]">MARRIAGE BLESSING CEREMONY</h3>
                  <p class="text-sm text-rose-700">📍 The Chapel</p>
                  <p class="text-sm text-rose-600">🕚 11:00 AM - 12:00 PM</p>
                </div>
              </div>
              <ul class="space-y-1.5 text-rose-800 text-sm ml-16">
                <li>• Processional & Entry of the Bride</li>
                <li>• Opening Prayer & Welcome</li>
                <li>• Marriage Blessing</li>
                <li>• Closing Prayer & Recessional</li>
              </ul>
            </div>

            <!-- Photography Session -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-6 border border-amber-100">
              <div class="flex items-center gap-4 mb-3">
                <div class="w-12 h-12 bg-amber-200 rounded-full flex items-center justify-center">
                  <CameraIcon class="h-6 w-6 text-amber-700" aria-hidden="true" />
                </div>
                <div>
                  <h3 class="text-base font-medium text-amber-900 tracking-[0.1em]">PHOTOGRAPHY SESSION</h3>
                  <p class="text-sm text-amber-700">📍 Chapel Gardens</p>
                  <p class="text-sm text-amber-600">🕧 12:30 PM – 1:30 PM</p>
                </div>
              </div>
              <ul class="space-y-1.5 text-amber-800 text-sm ml-16">
                <li>• Official Photos with the Bride and Groom</li>
                <li>• Family and Bridal Party Portraits</li>
                <li>• Guest Photo Moments</li>
              </ul>
            </div>

            <!-- Cocktail Hour -->
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-lg p-6 border border-emerald-100">
              <div class="flex items-center gap-4 mb-3">
                <div class="w-12 h-12 bg-emerald-200 rounded-full flex items-center justify-center">
                  <SparklesIcon class="h-6 w-6 text-emerald-700" aria-hidden="true" />
                </div>
                <div>
                  <h3 class="text-base font-medium text-emerald-900 tracking-[0.1em]">COCKTAIL HOUR</h3>
                  <p class="text-sm text-emerald-700">📍 Cocktail Area</p>
                  <p class="text-sm text-emerald-600">🕜 1:30 PM – 2:30 PM</p>
                </div>
              </div>
              <ul class="space-y-1.5 text-emerald-800 text-sm ml-16">
                <li>• Guests Proceed to Cocktail Area</li>
                <li>• Light Refreshments</li>
              </ul>
            </div>

            <!-- Main Reception -->
            <div class="bg-gradient-to-br from-purple-50 to-fuchsia-50 rounded-lg p-6 border border-purple-200 shadow-md">
              <div class="flex items-center gap-4 mb-3">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-fuchsia-500 rounded-full flex items-center justify-center shadow-md">
                  <MusicalNoteIcon class="h-6 w-6 text-white" aria-hidden="true" />
                </div>
                <div>
                  <h3 class="text-base font-semibold text-purple-900 tracking-[0.1em]">MAIN RECEPTION</h3>
                  <p class="text-sm text-purple-700">📍 Main Hall</p>
                  <p class="text-sm text-purple-600">🕞 3:30 PM – 5:30 PM</p>
                </div>
              </div>
              <ul class="space-y-1.5 text-purple-800 text-sm ml-16">
                <li>• Arrival of the Newlyweds</li>
                <li>• Opening Remarks & Prayer</li>
                <li>• Food & Drinks</li>
                <li>• Toasts & Speeches</li>
                <li>• Cutting of the Cake</li>
                <li>• Entertainment & Guest Dancing</li>
                <li>• Vote of Thanks & Closing</li>
              </ul>
            </div>

            <!-- End of Program -->
            <div class="text-center py-4">
              <p class="text-lg font-medium text-gray-700">🕕 6:00 PM</p>
              <p class="text-sm text-gray-600 mt-1">End of Program</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Q&A Tab -->
      <section v-show="activeTab === 'qa'" id="qa" class="py-8">
        <div class="max-w-3xl mx-auto px-4">
          <!-- Hidden on mobile since mobile header shows tab name -->
          <div class="hidden md:block text-center mb-10">
            <h2 class="text-2xl font-light text-gray-700 mb-4 tracking-[0.15em]">Q + A</h2>
          </div>

          <div class="space-y-12">
            <div class="text-center">
              <h3 class="text-lg font-medium text-gray-800 mb-4">When is the RSVP deadline?</h3>
              <p class="text-gray-800 text-sm leading-relaxed">Please confirm your attendance by 28 November to help us finalise arrangements.</p>
            </div>
            
            <div class="text-center">
              <h3 class="text-lg font-medium text-gray-800 mb-4">Are children welcome?</h3>
              <p class="text-gray-800 text-sm leading-relaxed">As much as we love little ones, this will be an adults-only celebration.</p>
            </div>
            
            <div class="text-center">
              <h3 class="text-lg font-medium text-gray-800 mb-4">Can I bring a plus-one?</h3>
              <p class="text-gray-800 text-sm leading-relaxed">Due to limited seating, we're only able to accommodate guests listed on the invitation.</p>
            </div>
            
            <div class="text-center">
              <h3 class="text-lg font-medium text-gray-800 mb-4">What is the dress code?</h3>
              <p class="text-gray-800 text-sm leading-relaxed">Formal / Smart Elegant.</p>
            </div>
            
            <div class="text-center">
              <h3 class="text-lg font-medium text-gray-800 mb-4">What time should I arrive?</h3>
              <p class="text-gray-800 text-sm leading-relaxed">Guests are kindly requested to be seated by 11:00 AM.</p>
            </div>
            
            <div class="text-center">
              <h3 class="text-lg font-medium text-gray-800 mb-4">Will there be a reception after the ceremony?</h3>
              <p class="text-gray-800 text-sm leading-relaxed">Yes! Please join us immediately after the ceremony for a celebration of love, food, and joy.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Location Tab -->
      <section v-show="activeTab === 'location'" id="location" class="py-8 md:py-16">
        <div class="max-w-3xl mx-auto px-4">
          <!-- Hidden on mobile since mobile header shows tab name -->
          <div class="hidden md:block text-center mb-10">
            <span class="text-3xl">📍</span>
            <h2 class="text-2xl font-light text-gray-700 mb-4 tracking-[0.15em] mt-2">LOCATION</h2>
          </div>

          <!-- Venue Details -->
          <div class="bg-white/80 backdrop-blur-sm rounded-lg shadow-lg p-6 md:p-8 mb-6 border border-purple-100">
            <div class="text-center mb-6">
              <h3 class="text-xl font-semibold text-purple-700 mb-2">{{ weddingEvent.venue_name }}</h3>
              <p class="text-gray-700 text-sm md:text-base">{{ weddingEvent.venue_address }}</p>
            </div>
            
            <!-- Map Placeholder -->
            <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden mb-6 border border-gray-200">
              <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3846.123456789!2d28.123456!3d-15.123456!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTXCsDA3JzI0LjQiUyAyOMKwMDcnMjQuNCJF!5e0!3m2!1sen!2szm!4v1234567890"
                width="100%" 
                height="100%" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade"
                class="w-full h-full"
              ></iframe>
            </div>

            <!-- Directions Button -->
            <div class="text-center">
              <a 
                :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_name + ' ' + weddingEvent.venue_address)}`"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Get Directions
              </a>
            </div>
          </div>

          <!-- Additional Info -->
          <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white/80 backdrop-blur-sm rounded-lg shadow-md p-6 border border-purple-100">
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-semibold text-gray-800 mb-1">Parking</h4>
                  <p class="text-sm text-gray-600">Ample parking available at the venue for all guests.</p>
                </div>
              </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-lg shadow-md p-6 border border-purple-100">
              <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-semibold text-gray-800 mb-1">Arrival Time</h4>
                  <p class="text-sm text-gray-600">Please arrive by 10:45 AM to be seated before the ceremony begins.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- RSVP Tab -->
      <section v-show="activeTab === 'rsvp'" id="rsvp" class="py-16">
        <div class="max-w-2xl mx-auto px-4">
          <div class="text-center mb-16">
            <h2 class="text-2xl font-light text-gray-800 mb-4 tracking-[0.15em]">RSVP</h2>
            <p class="text-gray-800 text-sm">
              Please let us know if you'll be joining us on our special day
            </p>
          </div>

          <div class="bg-gray-50 p-8 rounded-sm">
            <form @submit.prevent="submitRSVP" class="space-y-6">
              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                  <input 
                    v-model="rsvpForm.first_name"
                    type="text" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-1 focus:ring-gray-500 focus:border-gray-500 text-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                  <input 
                    v-model="rsvpForm.last_name"
                    type="text" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-1 focus:ring-gray-500 focus:border-gray-500 text-sm"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input 
                  v-model="rsvpForm.email"
                  type="email" 
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-1 focus:ring-gray-500 focus:border-gray-500 text-sm"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Will you be attending?</label>
                <div class="flex space-x-8">
                  <label class="flex items-center">
                    <input 
                      v-model="rsvpForm.attending" 
                      type="radio" 
                      value="yes" 
                      class="text-gray-600 focus:ring-gray-500"
                    />
                    <span class="ml-2 text-gray-700 text-sm">Yes, I'll be there!</span>
                  </label>
                  <label class="flex items-center">
                    <input 
                      v-model="rsvpForm.attending" 
                      type="radio" 
                      value="no" 
                      class="text-gray-600 focus:ring-gray-500"
                    />
                    <span class="ml-2 text-gray-700 text-sm">Sorry, can't make it</span>
                  </label>
                </div>
              </div>

              <div v-if="rsvpForm.attending === 'yes'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Number of Guests</label>
                <select 
                  v-model="rsvpForm.guest_count"
                  class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-1 focus:ring-gray-500 focus:border-gray-500 text-sm"
                >
                  <option value="1">1 person</option>
                  <option value="2">2 people</option>
                  <option value="3">3 people</option>
                  <option value="4">4 people</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dietary Restrictions</label>
                <textarea 
                  v-model="rsvpForm.dietary_restrictions"
                  rows="3"
                  placeholder="Please let us know about any dietary restrictions..."
                  class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-1 focus:ring-gray-500 focus:border-gray-500 text-sm"
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message for the Couple</label>
                <textarea 
                  v-model="rsvpForm.message"
                  rows="3"
                  placeholder="Share your excitement or well wishes..."
                  class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-1 focus:ring-gray-500 focus:border-gray-500 text-sm"
                ></textarea>
              </div>

              <div class="text-center pt-4">
                <button 
                  type="submit"
                  :disabled="submittingRSVP"
                  class="bg-gray-600 text-white px-8 py-3 text-sm font-medium tracking-[0.1em] hover:bg-gray-700 transition-colors disabled:opacity-50"
                >
                  <span v-if="submittingRSVP">SUBMITTING...</span>
                  <span v-else>SUBMIT RSVP</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>
    </main>

    <!-- Monogram Section -->
    <section class="pt-2 pb-4 md:py-8">
      <div class="max-w-4xl mx-auto px-4 text-center">
        <!-- Elegant Monogram -->
        <div class="mb-2 md:mb-3">
          <h2 class="text-2xl md:text-3xl font-light text-gray-400 tracking-[0.2em] font-serif">
            {{ getMonogramInitials() }}
          </h2>
          <div class="w-16 md:w-24 h-px bg-gray-300 mx-auto mt-2 md:mt-3"></div>
        </div>
        
        <p class="text-xs text-gray-400 tracking-[0.15em] mb-2 md:mb-6">{{ formatMonogramDate(weddingEvent.wedding_date) }}</p>
        
        <p class="text-xs text-gray-400 mb-4">Created on MyGrowNet</p>
        <p class="text-xs text-gray-400 leading-relaxed">
          Getting married? <a href="/weddings" class="underline hover:no-underline">Create your wedding website now.</a>
        </p>
        <p class="text-xs text-gray-400 mt-2">
          <a href="#" class="underline hover:no-underline">Your Privacy Choices</a>
        </p>
      </div>
    </section>

    <!-- Footer - Minimal -->
    <footer class="py-6 border-t border-gray-100">
      <div class="max-w-4xl mx-auto px-4 text-center">
        <p class="text-xs text-gray-300">© {{ new Date().getFullYear() }} Wedding Website</p>
      </div>
    </footer>
    </div><!-- End Main Content Wrapper -->

    <!-- RSVP Success Modal -->
    <div v-if="showRSVPSuccess" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-sm p-8 max-w-md mx-4 text-center">
        <CheckCircleIcon class="h-16 w-16 text-green-500 mx-auto mb-4" aria-hidden="true" />
        <h3 class="text-xl font-medium text-gray-900 mb-2">RSVP Submitted!</h3>
        <p class="text-gray-600 mb-6 text-sm">
          Thank you for your response. We can't wait to celebrate with you!
        </p>
        <button 
          @click="showRSVPSuccess = false"
          class="bg-gray-600 text-white px-6 py-2 text-sm font-medium tracking-[0.1em] hover:bg-gray-700 transition-colors"
        >
          CLOSE
        </button>
      </div>
    </div>

    <!-- RSVP Modal -->
    <RSVPModal 
      :isOpen="showRSVPModal" 
      :weddingEventId="weddingEvent.id"
      @close="closeRSVPModal" 
      @submitted="onRSVPSubmitted"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { 
  HeartIcon, 
  MusicalNoteIcon,
  CheckCircleIcon,
  CameraIcon,
  ArrowRightIcon,
  Bars3Icon,
  XMarkIcon,
  ShareIcon,
  LinkIcon,
  SparklesIcon
} from '@heroicons/vue/24/outline'
import RSVPModal from '@/components/Wedding/RSVPModal.vue'

const props = defineProps({
  weddingEvent: Object,
  galleryImages: Array,
  ogMeta: {
    type: Object,
    default: () => ({})
  }
})

// RSVP Form
const rsvpForm = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  attending: '',
  guest_count: '1',
  dietary_restrictions: '',
  message: ''
})

const submittingRSVP = ref(false)
const showRSVPSuccess = ref(false)
const activeTab = ref('home')
const showRSVPModal = ref(false)
const mobileMenuOpen = ref(false)
const shareMenuOpen = ref(false)
const linkCopied = ref(false)

// Countdown timer
const countdown = reactive({
  days: 0,
  hours: 0,
  minutes: 0,
  seconds: 0
})

let countdownInterval = null

const updateCountdown = () => {
  const weddingDate = new Date(props.weddingEvent.wedding_date)
  const now = new Date()
  const difference = weddingDate - now

  if (difference > 0) {
    countdown.days = Math.floor(difference / (1000 * 60 * 60 * 24))
    countdown.hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
    countdown.minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60))
    countdown.seconds = Math.floor((difference % (1000 * 60)) / 1000)
  } else {
    countdown.days = 0
    countdown.hours = 0
    countdown.minutes = 0
    countdown.seconds = 0
    if (countdownInterval) {
      clearInterval(countdownInterval)
    }
  }
}

onMounted(() => {
  initializeTabFromURL()
  window.addEventListener('popstate', handlePopState)
  
  // Initialize countdown
  updateCountdown()
  countdownInterval = setInterval(updateCountdown, 1000)
})

onUnmounted(() => {
  window.removeEventListener('popstate', handlePopState)
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
})

// Tab labels mapping
const tabLabels = {
  home: 'Home',
  program: 'Wedding Program',
  qa: 'Q + A',
  location: 'Location',
  rsvp: 'RSVP'
}

// Computed active tab label
const activeTabLabel = computed(() => tabLabels[activeTab.value] || 'Home')

// Methods
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatWeddingDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  }).toUpperCase()
}

const formatShortDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'numeric',
    day: 'numeric',
    year: 'numeric'
  })
}

const formatRSVPDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  })
}

const submitRSVP = async () => {
  submittingRSVP.value = true
  
  try {
    const response = await fetch(`/wedding/${props.weddingEvent.id}/rsvp`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify(rsvpForm)
    })
    
    if (response.ok) {
      showRSVPSuccess.value = true
      
      // Reset form
      Object.keys(rsvpForm).forEach(key => {
        rsvpForm[key] = key === 'guest_count' ? '1' : ''
      })
    } else {
      throw new Error('RSVP submission failed')
    }
  } catch (error) {
    console.error('RSVP submission failed:', error)
    alert('Sorry, there was an error submitting your RSVP. Please try again.')
  } finally {
    submittingRSVP.value = false
  }
}

const setActiveTab = (tab) => {
  activeTab.value = tab
  // Update URL without page reload using History API
  const basePath = window.location.pathname.replace(/\/(program|qa|travel|rsvp)$/, '')
  const newPath = tab === 'home' ? basePath : `${basePath}/${tab}`
  window.history.pushState({ tab }, '', newPath)
}

const setActiveTabMobile = (tab) => {
  activeTab.value = tab
  mobileMenuOpen.value = false
  // Update URL without page reload using History API
  const basePath = window.location.pathname.replace(/\/(program|qa|travel|rsvp)$/, '')
  const newPath = tab === 'home' ? basePath : `${basePath}/${tab}`
  window.history.pushState({ tab }, '', newPath)
}

// Share functions
const toggleShareMenu = () => {
  shareMenuOpen.value = !shareMenuOpen.value
}

const getShareUrl = () => {
  return window.location.href
}

const getShareText = () => {
  return `You're invited to ${props.weddingEvent.groom_name} & ${props.weddingEvent.bride_name}'s Wedding on ${formatWeddingDate(props.weddingEvent.wedding_date)}!`
}

const shareViaWhatsApp = () => {
  const text = encodeURIComponent(getShareText() + '\n\n' + getShareUrl())
  window.open(`https://wa.me/?text=${text}`, '_blank')
  shareMenuOpen.value = false
}

const shareViaFacebook = () => {
  const url = encodeURIComponent(getShareUrl())
  window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400')
  shareMenuOpen.value = false
}

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(getShareUrl())
    linkCopied.value = true
    setTimeout(() => {
      linkCopied.value = false
    }, 2000)
  } catch (err) {
    // Fallback for older browsers
    const textArea = document.createElement('textarea')
    textArea.value = getShareUrl()
    document.body.appendChild(textArea)
    textArea.select()
    document.execCommand('copy')
    document.body.removeChild(textArea)
    linkCopied.value = true
    setTimeout(() => {
      linkCopied.value = false
    }, 2000)
  }
}

// Handle browser back/forward buttons
const handlePopState = (event) => {
  if (event.state && event.state.tab) {
    activeTab.value = event.state.tab
  } else {
    // Detect tab from URL
    const path = window.location.pathname
    const match = path.match(/\/(program|qa|travel|rsvp)$/)
    activeTab.value = match ? match[1] : 'home'
  }
}

// Initialize tab from URL on mount
const initializeTabFromURL = () => {
  const path = window.location.pathname
  const match = path.match(/\/(program|qa|travel|rsvp)$/)
  if (match) {
    activeTab.value = match[1]
    // Set initial state
    window.history.replaceState({ tab: match[1] }, '', path)
  } else {
    activeTab.value = 'home'
    window.history.replaceState({ tab: 'home' }, '', path)
  }
}

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const openRSVPModal = () => {
  showRSVPModal.value = true
}

const openRSVPModalMobile = () => {
  mobileMenuOpen.value = false
  showRSVPModal.value = true
}

const closeRSVPModal = () => {
  showRSVPModal.value = false
}

const onRSVPSubmitted = () => {
  // Optional: Show success message or track submission
  console.log('RSVP submitted successfully')
}

const getMonogram = () => {
  const brideInitial = props.weddingEvent?.bride_name?.charAt(0)?.toUpperCase() || 'B'
  const groomInitial = props.weddingEvent?.groom_name?.charAt(0)?.toUpperCase() || 'G'
  return `${groomInitial}&${brideInitial}`
}

const getMonogramInitials = () => {
  const brideInitial = props.weddingEvent?.bride_name?.charAt(0)?.toUpperCase() || 'B'
  const groomInitial = props.weddingEvent?.groom_name?.charAt(0)?.toUpperCase() || 'G'
  return `${groomInitial} & ${brideInitial}`
}

const formatMonogramDate = (date) => {
  const d = new Date(date)
  const month = d.getMonth() + 1
  const day = d.getDate()
  const year = d.getFullYear()
  return `${month} . ${day} . ${year}`
}

// Smooth scrolling for navigation
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute('href'))
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        })
      }
    })
  })
})
</script>

<style scoped>
/* Custom font for elegant typography */
.font-serif {
  font-family: 'Playfair Display', 'Times New Roman', serif;
}

/* Ensure proper letter spacing */
.tracking-wide {
  letter-spacing: 0.05em;
}

</style>