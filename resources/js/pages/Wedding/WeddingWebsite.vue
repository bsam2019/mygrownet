<template>
  <!-- Open Graph Meta Tags for Social Sharing -->
  <Head>
    <title>{{ ogMeta.title || `${weddingEvent.groom_name} & ${weddingEvent.bride_name} Wedding` }}</title>
    <meta name="description" :content="ogMeta.description || `You are invited to celebrate the wedding of ${weddingEvent.groom_name} & ${weddingEvent.bride_name}`" />
    <meta property="og:type" :content="ogMeta.type || 'website'" />
    <meta property="og:url" :content="ogMeta.url || ''" />
    <meta property="og:title" :content="ogMeta.title || `${weddingEvent.groom_name} & ${weddingEvent.bride_name} Wedding`" />
    <meta property="og:description" :content="ogMeta.description || `You are invited to celebrate the wedding of ${weddingEvent.groom_name} & ${weddingEvent.bride_name}`" />
    <meta property="og:image" :content="ogMeta.image || ''" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:url" :content="ogMeta.url || ''" />
    <meta name="twitter:title" :content="ogMeta.title || `${weddingEvent.groom_name} & ${weddingEvent.bride_name} Wedding`" />
    <meta name="twitter:description" :content="ogMeta.description || `You are invited to celebrate the wedding of ${weddingEvent.groom_name} & ${weddingEvent.bride_name}`" />
    <meta name="twitter:image" :content="ogMeta.image || ''" />
    <meta property="og:site_name" content="Wedding Invitation" />
  </Head>

  <div class="min-h-screen relative overflow-hidden" :style="{ backgroundColor: colors.background }">
    <!-- Flora decorative background - top header decoration -->
    <div class="absolute top-[58px] md:top-0 left-0 right-0 z-0 pointer-events-none" :style="{ backgroundColor: colors.background }">
      <div class="h-[30vh] md:h-[40vh]">
        <img 
          :src="decorations.headerImage || '/images/Wedding/flora.jpg'" 
          alt="" 
          aria-hidden="true"
          class="w-full h-full object-cover object-top"
        />
      </div>
    </div>

    <!-- Fixed Mobile Header -->
    <div class="md:hidden fixed top-0 left-0 right-0 z-40 bg-white/90 backdrop-blur-sm shadow-sm" :style="{ borderColor: colors.primary + '20' }">
      <div class="flex items-center justify-between px-4 py-3">
        <button 
          @click="toggleMobileMenu"
          aria-label="Toggle navigation menu"
          class="w-10 h-10 flex items-center justify-center rounded-sm transition-all duration-200"
          :style="{ color: colors.primary }"
        >
          <div v-if="!mobileMenuOpen" class="flex flex-col justify-center items-center space-y-1.5" aria-hidden="true">
            <span class="block w-6 h-0.5 bg-current"></span>
            <span class="block w-6 h-0.5 bg-current"></span>
            <span class="block w-6 h-0.5 bg-current"></span>
          </div>
          <XMarkIcon v-else class="h-7 w-7" aria-hidden="true" />
        </button>
        
        <h2 class="text-sm font-medium tracking-[0.15em] uppercase" :style="{ color: colors.primary }">
          {{ activeTabLabel }}
        </h2>
        
        <button 
          @click="toggleShareMenu"
          aria-label="Share wedding invitation"
          class="w-10 h-10 flex items-center justify-center rounded-sm transition-all duration-200"
          :style="{ color: colors.primary }"
        >
          <ShareIcon class="h-5 w-5" aria-hidden="true" />
        </button>
      </div>
    </div>

    <!-- Share Menu Dropdown -->
    <div v-if="shareMenuOpen" class="fixed top-14 right-4 z-50 bg-white rounded-lg shadow-lg border border-gray-200 py-2 min-w-48">
      <button @click="shareViaWhatsApp" class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors">
        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        <span>Share via WhatsApp</span>
      </button>
      <button @click="shareViaFacebook" class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors">
        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
        </svg>
        <span>Share on Facebook</span>
      </button>
      <button @click="copyLink" class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors">
        <LinkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
        <span>{{ linkCopied ? 'Link Copied!' : 'Copy Link' }}</span>
      </button>
    </div>

    <!-- Share Menu Backdrop -->
    <div v-if="shareMenuOpen" @click="shareMenuOpen = false" class="fixed inset-0 z-40" aria-hidden="true"></div>

    <!-- Main Content Wrapper -->
    <div class="relative z-10">
      <div class="h-[12vh] md:h-12 lg:h-16"></div>

      <!-- Header Section -->
      <header class="relative pt-1 md:pt-20 lg:pt-24 pb-4 md:pb-6 text-center">
        <div class="max-w-4xl mx-auto px-4">
          <!-- Couple Names - Desktop only -->
          <h1 
            class="hidden md:flex md:flex-col md:items-center text-4xl md:text-6xl lg:text-7xl mb-4 drop-shadow-lg"
            :style="{ 
              fontFamily: fonts.heading + ', cursive',
              background: `linear-gradient(to right, ${colors.primary}, ${colors.secondary})`,
              WebkitBackgroundClip: 'text',
              WebkitTextFillColor: 'transparent',
              backgroundClip: 'text'
            }"
          >
            <span>{{ weddingEvent.groom_name }}</span>
            <span class="text-2xl md:text-3xl">&</span>
            <span>{{ weddingEvent.bride_name }}</span>
          </h1>
          <p class="hidden md:block text-sm md:text-base font-semibold tracking-[0.2em] mb-6 uppercase" :style="{ color: colors.primary }">
            {{ formatWeddingDate(weddingEvent.wedding_date) }}
          </p>
        </div>
          
        <!-- Desktop Navigation -->
        <nav class="hidden md:block relative z-10 border-b-2 bg-white shadow-md" :style="{ borderColor: colors.primary + '40' }">
          <div class="flex justify-center items-center">
            <div class="flex space-x-6 md:space-x-10 text-xs md:text-sm font-normal tracking-[0.1em]">
              <a 
                v-for="tab in navTabs" 
                :key="tab.id"
                href="javascript:void(0)" 
                :class="activeTab === tab.id ? 'font-semibold border-b-2 pb-4' : 'pb-4'"
                :style="{ 
                  color: activeTab === tab.id ? colors.primary : colors.primary + '80',
                  borderColor: activeTab === tab.id ? colors.primary : 'transparent'
                }"
                @click.prevent="tab.id === 'rsvp' ? openRSVPModal() : setActiveTab(tab.id)"
                class="transition-colors"
              >
                {{ tab.label }}
              </a>
              <div class="relative">
                <button 
                  @click="toggleShareMenu"
                  class="pb-4 transition-colors flex items-center gap-1"
                  :style="{ color: colors.primary + '80' }"
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
          <div v-if="mobileMenuOpen" class="md:hidden fixed inset-0 bg-white z-50 flex flex-col items-center justify-center">
            <button 
              @click="toggleMobileMenu"
              aria-label="Close navigation menu"
              class="absolute top-4 left-4 w-10 h-10 flex items-center justify-center rounded-sm bg-gray-50 border border-gray-200 text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-all duration-200"
            >
              <XMarkIcon class="h-5 w-5" aria-hidden="true" />
            </button>

            <nav class="flex flex-col items-center space-y-8 text-lg font-light tracking-[0.15em]">
              <a 
                v-for="tab in navTabs" 
                :key="tab.id"
                href="javascript:void(0)" 
                :class="activeTab === tab.id ? 'text-gray-700' : 'text-gray-400'"
                @click.prevent="tab.id === 'rsvp' ? openRSVPModalMobile() : setActiveTabMobile(tab.id)"
                class="hover:text-gray-600 transition-colors"
              >
                {{ tab.label }}
              </a>
            </nav>
          </div>
        </Transition>
      </header>

      <!-- Tab Content -->
      <main>
        <!-- Home Tab -->
        <section v-show="activeTab === 'home'" id="home" class="pt-2 pb-4 md:py-12 text-center">
          <div class="max-w-4xl mx-auto px-4">
            <!-- Mobile Couple Names -->
            <div class="md:hidden text-center mb-2">
              <h1 
                class="flex flex-col items-center mb-1"
                :style="{ fontFamily: fonts.heading + ', cursive' }"
              >
                <span 
                  class="text-5xl bg-clip-text text-transparent"
                  :style="{ background: `linear-gradient(to right, ${colors.secondary}, ${colors.primary})`, WebkitBackgroundClip: 'text' }"
                >{{ weddingEvent.groom_name }}</span>
                <span 
                  class="text-3xl bg-clip-text text-transparent"
                  :style="{ background: `linear-gradient(to right, ${colors.secondary}, ${colors.primary})`, WebkitBackgroundClip: 'text' }"
                >&</span>
                <span 
                  class="text-5xl bg-clip-text text-transparent"
                  :style="{ background: `linear-gradient(to right, ${colors.primary}, ${colors.secondary})`, WebkitBackgroundClip: 'text' }"
                >{{ weddingEvent.bride_name }}</span>
              </h1>
              <p class="text-xs font-semibold tracking-[0.15em] uppercase" :style="{ color: colors.primary }">
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
            <div v-if="layout.showCountdown" class="max-w-md mx-auto mb-8 md:mb-10 px-2">
              <div 
                class="bg-white/80 backdrop-blur-sm rounded-lg shadow-lg p-4 md:p-6"
                :style="{ borderColor: colors.primary + '20', borderWidth: '1px' }"
              >
                <p class="text-xs tracking-[0.2em] uppercase mb-4 text-center font-medium" :style="{ color: colors.primary }">Counting Down To</p>
                <div class="grid grid-cols-4 gap-2 md:gap-4">
                  <div v-for="unit in countdownUnits" :key="unit.key" class="text-center">
                    <div 
                      class="rounded-lg shadow-sm p-2 md:p-3"
                      :style="{ 
                        background: `linear-gradient(to bottom right, ${colors.primary}10, ${colors.secondary}10)`,
                        borderColor: colors.primary + '30',
                        borderWidth: '1px'
                      }"
                    >
                      <div class="text-xl md:text-3xl font-bold" :style="{ color: colors.primary }">
                        {{ countdown[unit.key] }}
                      </div>
                      <div class="text-[10px] md:text-xs mt-1 tracking-wide uppercase font-medium" :style="{ color: colors.primary + 'cc' }">{{ unit.label }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Wedding Invitation Message -->
            <div class="max-w-2xl mx-auto mb-8 md:mb-12 px-4">
              <div class="text-center space-y-4 md:space-y-6">
                <p class="text-gray-800 text-base md:text-lg leading-relaxed font-light">
                  Together with our families, {{ weddingEvent.groom_name }} and {{ weddingEvent.bride_name }} invite you to celebrate with us as we unite in marriage under God's gracious guidance.
                </p>
                
                <div class="py-4">
                  <div class="w-16 h-px bg-gray-400 mx-auto mb-4"></div>
                  <p class="text-gray-900 text-sm md:text-base font-normal tracking-wide">
                    {{ formatWeddingDateFull(weddingEvent.wedding_date) }}
                  </p>
                  <p class="text-gray-800 text-sm md:text-base mt-2">
                    {{ weddingEvent.ceremony_time || '11:00 in the morning' }}
                  </p>
                  <p class="text-gray-800 text-sm md:text-base mt-2 italic">
                    {{ weddingEvent.venue_name || 'Venue TBA' }}
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
                class="inline-block text-white px-12 py-4 text-sm font-semibold tracking-[0.15em] transition-all duration-300 rounded-full shadow-lg hover:shadow-xl transform hover:scale-105"
                :style="{ 
                  background: `linear-gradient(to right, ${colors.secondary}, ${colors.primary})`,
                }"
              >
                RSVP NOW
              </button>
            </div>
          </div>
        </section>

        <!-- Wedding Program Tab -->
        <section v-show="activeTab === 'program'" id="program" class="py-16">
          <div class="max-w-4xl mx-auto px-4">
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
                    <p class="text-sm text-rose-600">🕙 10:20 AM - 10:45 AM</p>
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
              <div 
                class="rounded-lg p-6 shadow-md"
                :style="{ 
                  background: `linear-gradient(to bottom right, ${colors.primary}10, ${colors.secondary}10)`,
                  borderColor: colors.primary + '30',
                  borderWidth: '1px'
                }"
              >
                <div class="flex items-center gap-4 mb-3">
                  <div 
                    class="w-12 h-12 rounded-full flex items-center justify-center shadow-md"
                    :style="{ background: `linear-gradient(to bottom right, ${colors.primary}, ${colors.secondary})` }"
                  >
                    <MusicalNoteIcon class="h-6 w-6 text-white" aria-hidden="true" />
                  </div>
                  <div>
                    <h3 class="text-base font-semibold tracking-[0.1em]" :style="{ color: colors.primary }">MAIN RECEPTION</h3>
                    <p class="text-sm" :style="{ color: colors.primary + 'cc' }">📍 Main Hall</p>
                    <p class="text-sm" :style="{ color: colors.primary + 'aa' }">🕞 3:30 PM – 5:30 PM</p>
                  </div>
                </div>
                <ul class="space-y-1.5 text-sm ml-16" :style="{ color: colors.primary + 'dd' }">
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
            <div class="hidden md:block text-center mb-10">
              <h2 class="text-2xl font-light text-gray-700 mb-4 tracking-[0.15em]">Q + A</h2>
            </div>

            <div class="space-y-12">
              <div class="text-center">
                <h3 class="text-lg font-medium text-gray-800 mb-4">When is the RSVP deadline?</h3>
                <p class="text-gray-800 text-sm leading-relaxed">Please confirm your attendance by {{ formatRSVPDate(weddingEvent.rsvp_deadline) }} to help us finalise arrangements.</p>
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
                <h3 class="text-lg font-medium text-gray-800 mb-4">What should I wear?</h3>
                <p class="text-gray-800 text-sm leading-relaxed">We'd love for you to dress in semi-formal or cocktail attire. Think elegant and comfortable!</p>
              </div>
              
              <div class="text-center">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Is there parking available?</h3>
                <p class="text-gray-800 text-sm leading-relaxed">Yes, there is ample parking at the venue. Please follow the signs upon arrival.</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Location Tab -->
        <section v-show="activeTab === 'location'" id="location" class="py-8">
          <div class="max-w-4xl mx-auto px-4">
            <div class="hidden md:block text-center mb-10">
              <h2 class="text-2xl font-light text-gray-700 mb-4 tracking-[0.15em]">LOCATION</h2>
            </div>

            <!-- Venue Details -->
            <div 
              class="bg-white/90 backdrop-blur-sm rounded-xl shadow-xl p-6 md:p-8 mb-6"
              :style="{ borderColor: colors.primary + '30', borderWidth: '2px' }"
            >
              <div class="text-center mb-8">
                <div 
                  class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 shadow-lg"
                  :style="{ background: `linear-gradient(to bottom right, ${colors.primary}, ${colors.secondary})` }"
                >
                  <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <h3 class="text-xl md:text-2xl font-semibold text-gray-800 mb-2">{{ weddingEvent.venue_name || 'Venue TBA' }}</h3>
                <p class="text-gray-600 text-sm md:text-base">{{ weddingEvent.venue_address || 'Address TBA' }}</p>
              </div>

              <!-- Map placeholder -->
              <div class="bg-gray-100 rounded-lg h-64 flex items-center justify-center">
                <div class="text-center text-gray-500">
                  <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                  </svg>
                  <p class="text-sm">Map will be displayed here</p>
                </div>
              </div>

              <!-- Directions button -->
              <div class="mt-6 text-center">
                <a 
                  :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(weddingEvent.venue_address || weddingEvent.venue_name || '')}`"
                  target="_blank"
                  class="inline-flex items-center gap-2 px-6 py-3 rounded-full text-white text-sm font-medium transition-all hover:shadow-lg"
                  :style="{ background: `linear-gradient(to right, ${colors.primary}, ${colors.secondary})` }"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  </svg>
                  Get Directions
                </a>
              </div>
            </div>
          </div>
        </section>

        <!-- RSVP Tab -->
        <section v-show="activeTab === 'rsvp'" id="rsvp" class="py-16">
          <div class="max-w-2xl mx-auto px-4">
            <div class="text-center mb-16">
              <h2 class="text-2xl font-light text-gray-800 mb-4 tracking-[0.15em]">RSVP</h2>
              <p class="text-gray-800 text-sm">Please let us know if you'll be joining us on our special day</p>
            </div>

            <div class="bg-gray-50 p-8 rounded-sm">
              <form @submit.prevent="submitRSVP" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                    <input v-model="rsvpForm.first_name" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-1 focus:ring-gray-500 focus:border-gray-500 text-sm" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                    <input v-model="rsvpForm.last_name" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-1 focus:ring-gray-500 focus:border-gray-500 text-sm" />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                  <input v-model="rsvpForm.email" type="email" required class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-1 focus:ring-gray-500 focus:border-gray-500 text-sm" />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-4">Will you be attending?</label>
                  <div class="flex space-x-8">
                    <label class="flex items-center">
                      <input v-model="rsvpForm.attending" type="radio" value="yes" class="text-gray-600 focus:ring-gray-500" />
                      <span class="ml-2 text-gray-700 text-sm">Yes, I'll be there!</span>
                    </label>
                    <label class="flex items-center">
                      <input v-model="rsvpForm.attending" type="radio" value="no" class="text-gray-600 focus:ring-gray-500" />
                      <span class="ml-2 text-gray-700 text-sm">Sorry, can't make it</span>
                    </label>
                  </div>
                </div>

                <div v-if="rsvpForm.attending === 'yes'">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Number of Guests</label>
                  <select v-model="rsvpForm.guest_count" class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-1 focus:ring-gray-500 focus:border-gray-500 text-sm">
                    <option value="1">1 person</option>
                    <option value="2">2 people</option>
                    <option value="3">3 people</option>
                    <option value="4">4 people</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Dietary Restrictions</label>
                  <textarea v-model="rsvpForm.dietary_restrictions" rows="3" placeholder="Please let us know about any dietary restrictions..." class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-1 focus:ring-gray-500 focus:border-gray-500 text-sm"></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Message for the Couple</label>
                  <textarea v-model="rsvpForm.message" rows="3" placeholder="Share your excitement or well wishes..." class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-1 focus:ring-gray-500 focus:border-gray-500 text-sm"></textarea>
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
          <div class="mb-2 md:mb-3">
            <h2 class="text-2xl md:text-3xl font-light text-gray-400 tracking-[0.2em] font-serif">
              {{ getMonogramInitials() }}
            </h2>
            <div class="w-16 md:w-24 h-px bg-gray-300 mx-auto mt-2 md:mt-3"></div>
          </div>
          
          <p class="text-xs text-gray-400 tracking-[0.15em] mb-2 md:mb-6">{{ formatMonogramDate(weddingEvent.wedding_date) }}</p>
          
          <p class="text-xs text-gray-400 mb-4">Created with GrowCelebrate</p>
          <p class="text-xs text-gray-400 leading-relaxed">
            Planning a celebration? <a href="/celebrations" class="underline hover:no-underline">Get your custom invitation website now.</a>
          </p>
        </div>
      </section>

      <!-- Footer -->
      <footer class="py-6 border-t border-gray-100">
        <div class="max-w-4xl mx-auto px-4 text-center">
          <p class="text-xs text-gray-300">© {{ new Date().getFullYear() }} Wedding Website</p>
        </div>
      </footer>
    </div>

    <!-- RSVP Success Modal -->
    <div v-if="showRSVPSuccess" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-sm p-8 max-w-md mx-4 text-center">
        <CheckCircleIcon class="h-16 w-16 text-green-500 mx-auto mb-4" aria-hidden="true" />
        <h3 class="text-xl font-medium text-gray-900 mb-2">RSVP Submitted!</h3>
        <p class="text-gray-600 mb-6 text-sm">Thank you for your response. We can't wait to celebrate with you!</p>
        <button @click="showRSVPSuccess = false" class="bg-gray-600 text-white px-6 py-2 text-sm font-medium tracking-[0.1em] hover:bg-gray-700 transition-colors">
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
  },
  template: {
    type: Object,
    default: () => ({
      id: 1,
      name: 'Flora Classic',
      slug: 'flora-classic',
      settings: {
        colors: {
          primary: '#9333ea',
          secondary: '#ec4899',
          accent: '#f59e0b',
          background: '#ffffff',
          text: '#1f2937',
          textLight: '#6b7280',
        },
        fonts: {
          heading: 'Great Vibes',
          body: 'Inter',
        },
        layout: {
          heroStyle: 'centered',
          navigationStyle: 'tabs',
          showCountdown: true,
          showGallery: true,
        },
        decorations: {
          backgroundPattern: 'flora',
          headerImage: '/images/Wedding/flora.jpg',
          borderStyle: 'elegant',
        },
      }
    })
  }
})

// Template-based computed styles
const colors = computed(() => props.template?.settings?.colors || {
  primary: '#9333ea',
  secondary: '#ec4899',
  accent: '#f59e0b',
  background: '#ffffff',
  text: '#1f2937',
  textLight: '#6b7280',
})

const fonts = computed(() => props.template?.settings?.fonts || {
  heading: 'Great Vibes',
  body: 'Inter',
})

const decorations = computed(() => props.template?.settings?.decorations || {
  backgroundPattern: 'flora',
  headerImage: '/images/Wedding/flora.jpg',
  borderStyle: 'elegant',
})

const layout = computed(() => props.template?.settings?.layout || {
  heroStyle: 'centered',
  navigationStyle: 'tabs',
  showCountdown: true,
  showGallery: true,
})

// Navigation tabs
const navTabs = [
  { id: 'home', label: 'Home' },
  { id: 'program', label: 'Wedding Program' },
  { id: 'qa', label: 'Q + A' },
  { id: 'location', label: 'Location' },
  { id: 'rsvp', label: 'RSVP' },
]

// Tab labels mapping
const tabLabels = {
  home: 'Home',
  program: 'Wedding Program',
  qa: 'Q + A',
  location: 'Location',
  rsvp: 'RSVP'
}

// Countdown units
const countdownUnits = [
  { key: 'days', label: 'Days' },
  { key: 'hours', label: 'Hours' },
  { key: 'minutes', label: 'Minutes' },
  { key: 'seconds', label: 'Seconds' },
]

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

// Computed active tab label
const activeTabLabel = computed(() => tabLabels[activeTab.value] || 'Home')

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
    if (countdownInterval) clearInterval(countdownInterval)
  }
}

onMounted(() => {
  initializeTabFromURL()
  window.addEventListener('popstate', handlePopState)
  updateCountdown()
  countdownInterval = setInterval(updateCountdown, 1000)
})

onUnmounted(() => {
  window.removeEventListener('popstate', handlePopState)
  if (countdownInterval) clearInterval(countdownInterval)
})

// Date formatting methods
const formatWeddingDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  }).toUpperCase()
}

const formatWeddingDateFull = (date) => {
  const d = new Date(date)
  const day = d.getDate()
  const suffix = day === 1 || day === 21 || day === 31 ? 'st' : day === 2 || day === 22 ? 'nd' : day === 3 || day === 23 ? 'rd' : 'th'
  return d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', year: 'numeric' }).replace(/(\d+)/, `${day}${suffix}`)
}

const formatRSVPDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  })
}

const formatMonogramDate = (date) => {
  const d = new Date(date)
  return `${d.getMonth() + 1} . ${d.getDate()} . ${d.getFullYear()}`
}

const getMonogramInitials = () => {
  const brideInitial = props.weddingEvent?.bride_name?.charAt(0)?.toUpperCase() || 'B'
  const groomInitial = props.weddingEvent?.groom_name?.charAt(0)?.toUpperCase() || 'G'
  return `${groomInitial} & ${brideInitial}`
}

// Navigation methods
const setActiveTab = (tab) => {
  activeTab.value = tab
  const basePath = window.location.pathname.replace(/\/(home|program|qa|location|rsvp)$/, '')
  const newPath = tab === 'home' ? basePath : `${basePath}/${tab}`
  window.history.pushState({ tab }, '', newPath)
}

const setActiveTabMobile = (tab) => {
  activeTab.value = tab
  mobileMenuOpen.value = false
  const basePath = window.location.pathname.replace(/\/(home|program|qa|location|rsvp)$/, '')
  const newPath = tab === 'home' ? basePath : `${basePath}/${tab}`
  window.history.pushState({ tab }, '', newPath)
}

const handlePopState = (event) => {
  if (event.state && event.state.tab) {
    activeTab.value = event.state.tab
  } else {
    const path = window.location.pathname
    const match = path.match(/\/(home|program|qa|location|rsvp)$/)
    activeTab.value = match ? match[1] : 'home'
  }
}

const initializeTabFromURL = () => {
  const path = window.location.pathname
  const match = path.match(/\/(home|program|qa|location|rsvp)$/)
  if (match) {
    activeTab.value = match[1]
    window.history.replaceState({ tab: match[1] }, '', path)
  } else {
    activeTab.value = 'home'
    window.history.replaceState({ tab: 'home' }, '', path)
  }
}

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

// RSVP methods
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
  console.log('RSVP submitted successfully')
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

// Share methods
const toggleShareMenu = () => {
  shareMenuOpen.value = !shareMenuOpen.value
}

const getShareUrl = () => window.location.href

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
    setTimeout(() => { linkCopied.value = false }, 2000)
  } catch (err) {
    const textArea = document.createElement('textarea')
    textArea.value = getShareUrl()
    document.body.appendChild(textArea)
    textArea.select()
    document.execCommand('copy')
    document.body.removeChild(textArea)
    linkCopied.value = true
    setTimeout(() => { linkCopied.value = false }, 2000)
  }
}
</script>

<style scoped>
.font-serif {
  font-family: 'Playfair Display', 'Times New Roman', serif;
}

.tracking-wide {
  letter-spacing: 0.05em;
}
</style>
