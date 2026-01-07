<template>
  <Head title="GrowCelebrate - Digital Invitations & Event Services" />
  
  <div class="min-h-screen bg-gradient-to-b from-purple-50 via-white to-pink-50">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center gap-2">
            <img src="/images/growcelebrate/logo.png" alt="GrowCelebrate" class="h-10 w-auto" />
          </div>
          <div class="hidden md:flex items-center gap-8">
            <a href="#portfolio" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">Our Work</a>
            <a href="#services" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">Services</a>
            <a href="#how-it-works" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">How It Works</a>
            <a href="/celebrations/contact" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">Contact</a>
          </div>
          <div class="flex items-center gap-4">
            <a 
              href="/celebrations/contact" 
              class="hidden sm:block px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-full hover:bg-purple-700 transition-colors"
            >
              Get a Quote
            </a>
            <!-- Mobile menu button -->
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="md:hidden p-2 text-gray-600 hover:text-purple-600 transition-colors"
              aria-label="Toggle menu"
            >
              <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
              </svg>
              <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        
        <!-- Mobile menu -->
        <div v-if="mobileMenuOpen" class="md:hidden py-4 border-t border-gray-100">
          <div class="flex flex-col space-y-4">
            <a href="#portfolio" @click="mobileMenuOpen = false" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">Our Work</a>
            <a href="#services" @click="mobileMenuOpen = false" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">Services</a>
            <a href="#how-it-works" @click="mobileMenuOpen = false" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">How It Works</a>
            <a href="/celebrations/contact" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">Contact</a>
            <a 
              href="/celebrations/contact" 
              class="inline-block text-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-full hover:bg-purple-700 transition-colors"
            >
              Get a Quote
            </a>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4">
      <div class="max-w-5xl mx-auto text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-purple-100 text-purple-700 rounded-full text-sm font-medium mb-6">
          <SparklesIcon class="h-4 w-4" aria-hidden="true" />
          Weddings • Birthdays • Anniversaries • Parties
        </div>
        <h1 class="text-5xl md:text-7xl font-bold text-gray-900 mb-6 leading-tight">
          We Create Stunning
          <span class="bg-gradient-to-r from-purple-600 via-pink-600 to-yellow-500 bg-clip-text text-transparent"> Celebration Experiences</span>
        </h1>
        <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
          From beautiful digital invitations to elegant event decor, we bring your celebrations to life. 
          Professional service for weddings, birthdays, anniversaries, and all special moments.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
          <a 
            href="/celebrations/contact" 
            class="px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-full hover:shadow-lg hover:scale-105 transition-all"
          >
            Get a Quote
          </a>
          <a 
            href="#portfolio" 
            class="px-8 py-4 bg-white text-gray-700 font-semibold rounded-full border border-gray-200 hover:border-purple-300 hover:shadow-lg transition-all flex items-center gap-2"
          >
            <EyeIcon class="h-5 w-5" aria-hidden="true" />
            View Our Work
          </a>
        </div>
        
        <!-- Celebration Types -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
          <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition-shadow">
            <div class="text-4xl mb-2">💍</div>
            <p class="font-semibold text-gray-900">Weddings</p>
            <p class="text-sm text-gray-500">Beautiful wedding sites</p>
          </div>
          <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition-shadow">
            <div class="text-4xl mb-2">🎂</div>
            <p class="font-semibold text-gray-900">Birthdays</p>
            <p class="text-sm text-gray-500">Fun party invites</p>
          </div>
          <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition-shadow">
            <div class="text-4xl mb-2">💕</div>
            <p class="font-semibold text-gray-900">Anniversaries</p>
            <p class="text-sm text-gray-500">Romantic celebrations</p>
          </div>
          <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition-shadow">
            <div class="text-4xl mb-2">🎉</div>
            <p class="font-semibold text-gray-900">Parties</p>
            <p class="text-sm text-gray-500">Any celebration</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 px-4 bg-white">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Our Services</h2>
          <p class="text-lg text-gray-600">Complete celebration solutions tailored to your needs</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
          <!-- Digital Invitations -->
          <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-8 hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mb-4">
              <DevicePhoneMobileIcon class="h-8 w-8 text-white" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Digital Invitations</h3>
            <p class="text-gray-600 mb-4">Beautiful, responsive websites for your event with RSVP management, photo galleries, and countdown timers.</p>
            <ul class="space-y-2 text-sm text-gray-600">
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-purple-600" aria-hidden="true" />
                Custom design
              </li>
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-purple-600" aria-hidden="true" />
                RSVP tracking
              </li>
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-purple-600" aria-hidden="true" />
                Easy sharing
              </li>
            </ul>
          </div>

          <!-- Event Decor -->
          <div class="bg-gradient-to-br from-pink-50 to-yellow-50 rounded-2xl p-8 hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-yellow-500 rounded-2xl flex items-center justify-center mb-4">
              <SparklesIcon class="h-8 w-8 text-white" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Event Decor</h3>
            <p class="text-gray-600 mb-4">Professional decoration services to transform your venue into a stunning celebration space.</p>
            <ul class="space-y-2 text-sm text-gray-600">
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-pink-600" aria-hidden="true" />
                Theme design
              </li>
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-pink-600" aria-hidden="true" />
                Setup & teardown
              </li>
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-pink-600" aria-hidden="true" />
                Custom arrangements
              </li>
            </ul>
          </div>

          <!-- Wedding Stationery -->
          <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-8 hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-2xl flex items-center justify-center mb-4">
              <ClipboardDocumentCheckIcon class="h-8 w-8 text-white" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Wedding Stationery</h3>
            <p class="text-gray-600 mb-4">Custom printed invitations, programs, menus, and thank you cards for your special day.</p>
            <ul class="space-y-2 text-sm text-gray-600">
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                Custom designs
              </li>
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                Quality printing
              </li>
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                Fast delivery
              </li>
            </ul>
          </div>

          <!-- Full Planning -->
          <div class="bg-gradient-to-br from-emerald-50 to-cyan-50 rounded-2xl p-8 hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-2xl flex items-center justify-center mb-4">
              <ClipboardDocumentCheckIcon class="h-8 w-8 text-white" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Event Planning</h3>
            <p class="text-gray-600 mb-4">Complete event planning and coordination from start to finish.</p>
            <ul class="space-y-2 text-sm text-gray-600">
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                Full coordination
              </li>
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                Vendor management
              </li>
              <li class="flex items-center gap-2">
                <CheckIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                Day-of coordination
              </li>
            </ul>
          </div>
        </div>

        <!-- Features Grid -->
        <div class="mt-16 grid md:grid-cols-3 gap-6">
          <div class="flex items-start gap-4 p-6 bg-gray-50 rounded-xl">
            <ClockIcon class="h-6 w-6 text-purple-600 flex-shrink-0" aria-hidden="true" />
            <div>
              <h4 class="font-semibold text-gray-900 mb-1">Fast Turnaround</h4>
              <p class="text-sm text-gray-600">Your invitation ready in 3-5 business days</p>
            </div>
          </div>
          <div class="flex items-start gap-4 p-6 bg-gray-50 rounded-xl">
            <ShareIcon class="h-6 w-6 text-purple-600 flex-shrink-0" aria-hidden="true" />
            <div>
              <h4 class="font-semibold text-gray-900 mb-1">Easy Sharing</h4>
              <p class="text-sm text-gray-600">Share via WhatsApp, social media, or link</p>
            </div>
          </div>
          <div class="flex items-start gap-4 p-6 bg-gray-50 rounded-xl">
            <MapPinIcon class="h-6 w-6 text-purple-600 flex-shrink-0" aria-hidden="true" />
            <div>
              <h4 class="font-semibold text-gray-900 mb-1">Venue Integration</h4>
              <p class="text-sm text-gray-600">Google Maps directions for your guests</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Portfolio Section -->
    <section id="portfolio" class="py-20 px-4">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Our Work</h2>
          <p class="text-lg text-gray-600 mb-8">Browse our portfolio of beautiful celebration designs</p>
          
          <!-- Category Filter -->
          <div class="inline-flex items-center gap-2 bg-white rounded-full p-2 shadow-md">
            <button
              v-for="category in categories"
              :key="category.id"
              @click="selectedCategory = category.id"
              :class="[
                'px-6 py-2 rounded-full text-sm font-medium transition-all',
                selectedCategory === category.id
                  ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-md'
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <span class="mr-2">{{ category.icon }}</span>
              {{ category.name }}
            </button>
          </div>
        </div>
        
        <p v-if="!filteredTemplates || filteredTemplates.length === 0" class="text-center text-gray-500 mb-8">
          No examples available for this category yet. Check back soon!
        </p>
        
        <div v-if="filteredTemplates && filteredTemplates.length > 0" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div 
            v-for="template in filteredTemplates" 
            :key="template.id"
            class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300"
          >
            <!-- Template Preview -->
            <div 
              class="h-64 relative overflow-hidden"
              :style="{ 
                background: template.settings?.colors?.primary 
                  ? `linear-gradient(135deg, ${template.settings.colors.primary}, ${template.settings.colors.secondary || template.settings.colors.primary})` 
                  : 'linear-gradient(135deg, #9333ea, #ec4899)'
              }"
            >
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center text-white p-6">
                  <div class="text-5xl mb-3">{{ template.category_icon || '💍' }}</div>
                  <p 
                    class="text-3xl font-bold mb-2"
                    :style="{ fontFamily: (template.settings?.fonts?.heading || 'inherit') }"
                  >
                    {{ template.preview_text || 'Your Event' }}
                  </p>
                  <p class="text-sm opacity-90">{{ template.category_name || 'Celebration' }}</p>
                </div>
              </div>
              
              <!-- Category Badge -->
              <span 
                class="absolute top-3 left-3 bg-white/90 text-gray-900 text-xs font-bold px-3 py-1 rounded-full"
              >
                {{ template.category_name || 'Wedding' }}
              </span>
              
              <!-- Premium Badge -->
              <span 
                v-if="template.is_premium" 
                class="absolute top-3 right-3 bg-amber-400 text-amber-900 text-xs font-bold px-2 py-1 rounded-full"
              >
                PREMIUM
              </span>
              
              <!-- Preview Button -->
              <button
                @click="openPreview(template)"
                class="absolute inset-0 bg-black/0 group-hover:bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all"
              >
                <span class="px-6 py-3 bg-white text-gray-900 rounded-full text-sm font-medium flex items-center gap-2 shadow-lg">
                  <EyeIcon class="h-4 w-4" aria-hidden="true" />
                  View Example
                </span>
              </button>
            </div>
            
            <!-- Template Info -->
            <div class="p-6">
              <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ template.name }}</h3>
              <p class="text-sm text-gray-600 line-clamp-2 mb-4">{{ template.description }}</p>
              
              <!-- Features -->
              <div class="flex flex-wrap gap-2 mb-4">
                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full">Responsive</span>
                <span class="text-xs bg-pink-100 text-pink-700 px-2 py-1 rounded-full">RSVP</span>
                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">Gallery</span>
              </div>
              
              <!-- Color Palette -->
              <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500">Colors:</span>
                <div 
                  class="w-6 h-6 rounded-full shadow-sm border border-gray-200" 
                  :style="{ backgroundColor: template.settings?.colors?.primary || '#9333ea' }"
                  :title="'Primary: ' + (template.settings?.colors?.primary || '#9333ea')"
                ></div>
                <div 
                  class="w-6 h-6 rounded-full shadow-sm border border-gray-200" 
                  :style="{ backgroundColor: template.settings?.colors?.secondary || '#ec4899' }"
                  :title="'Secondary: ' + (template.settings?.colors?.secondary || '#ec4899')"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 px-4 bg-gradient-to-b from-white to-purple-50">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
          <p class="text-lg text-gray-600">We make it easy - just 3 simple steps</p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-12">
          <div class="text-center">
            <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-6 text-white text-3xl font-bold shadow-lg">
              1
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Contact Us</h3>
            <p class="text-gray-600">Tell us about your event - date, type, and what services you need. We'll provide a custom quote.</p>
          </div>
          
          <div class="text-center">
            <div class="w-20 h-20 bg-gradient-to-br from-pink-500 to-yellow-500 rounded-full flex items-center justify-center mx-auto mb-6 text-white text-3xl font-bold shadow-lg">
              2
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">We Create</h3>
            <p class="text-gray-600">Our team designs your invitation, plans your decor, and handles all the details professionally.</p>
          </div>
          
          <div class="text-center">
            <div class="w-20 h-20 bg-gradient-to-br from-yellow-500 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-6 text-white text-3xl font-bold shadow-lg">
              3
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">You Celebrate</h3>
            <p class="text-gray-600">Share your beautiful invitation and enjoy your perfectly decorated event - stress-free!</p>
          </div>
        </div>

        <!-- CTA in How It Works -->
        <div class="text-center mt-12">
          <a 
            href="/celebrations/contact" 
            class="inline-block px-10 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-full hover:shadow-lg hover:scale-105 transition-all"
          >
            Get Started - Request a Quote
          </a>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4">
      <div class="max-w-4xl mx-auto text-center">
        <div class="bg-gradient-to-r from-purple-600 via-pink-600 to-yellow-500 rounded-3xl p-12 text-white">
          <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready for Your Perfect Celebration?</h2>
          <p class="text-lg text-purple-100 mb-8">
            Join hundreds of satisfied clients who trusted us with their special moments. Let us handle the details while you enjoy the celebration!
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a 
              href="/celebrations/contact" 
              class="inline-block px-8 py-4 bg-white text-purple-600 font-semibold rounded-full hover:shadow-lg hover:scale-105 transition-all"
            >
              Request a Quote
            </a>
            <a 
              href="tel:+260976244690" 
              class="inline-block px-8 py-4 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-full hover:bg-white/30 transition-all flex items-center gap-2"
            >
              <PhoneIcon class="h-5 w-5" aria-hidden="true" />
              Call Us Now
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 px-4 bg-gray-900 text-white">
      <div class="max-w-6xl mx-auto">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
          <div>
            <div class="flex items-center gap-2 mb-4">
              <SparklesIcon class="h-6 w-6 text-purple-400" aria-hidden="true" />
              <span class="text-lg font-semibold">GrowCelebrate</span>
            </div>
            <p class="text-gray-400 text-sm">
              Beautiful digital invitations for weddings, birthdays, anniversaries, and all celebrations.
            </p>
          </div>
          <div>
            <h4 class="font-semibold mb-4">Services</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li><a href="#portfolio" class="hover:text-white transition-colors">Digital Invitations</a></li>
              <li><a href="#services" class="hover:text-white transition-colors">Event Decor</a></li>
              <li><a href="#services" class="hover:text-white transition-colors">Wedding Stationery</a></li>
              <li><a href="#services" class="hover:text-white transition-colors">Event Planning</a></li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold mb-4">Company</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li><a href="/about" class="hover:text-white transition-colors">About Us</a></li>
              <li><a href="#how-it-works" class="hover:text-white transition-colors">How It Works</a></li>
              <li><a href="/celebrations/contact" class="hover:text-white transition-colors">Contact</a></li>
              <li><a href="/privacy" class="hover:text-white transition-colors">Privacy Policy</a></li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold mb-4">Contact</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li>celebrations@mygrownet.com</li>
              <li>+260 976 244 690</li>
              <li>Lusaka, Zambia</li>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-500">
          <p>© {{ new Date().getFullYear() }} GrowCelebrate. All rights reserved.</p>
        </div>
      </div>
    </footer>

    <!-- Template Preview Modal -->
    <Teleport to="body">
      <div v-if="showPreview" class="fixed inset-0 z-50 overflow-hidden bg-white">
        <!-- Close Button (Floating) -->
        <button 
          @click="closePreview"
          class="absolute top-4 right-4 z-20 p-3 bg-white/90 hover:bg-white rounded-full shadow-lg transition-all"
          aria-label="Close preview"
        >
          <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        
        <!-- Device Selector (Floating Bottom) -->
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 bg-white/95 backdrop-blur-sm rounded-full shadow-2xl border border-gray-200 px-4 py-3">
          <div class="flex items-center gap-3">
            <button
              v-for="device in devices"
              :key="device.name"
              @click="selectedDevice = device.name"
              :class="[
                'flex items-center gap-2 px-4 py-2 rounded-full transition-all',
                selectedDevice === device.name
                  ? 'bg-purple-600 text-white shadow-md'
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
              :aria-label="`Switch to ${device.name} view`"
            >
              <component :is="device.icon" class="h-5 w-5" aria-hidden="true" />
              <span class="text-sm font-medium">{{ device.name }}</span>
            </button>
          </div>
        </div>
        
        <!-- Full Screen Preview Content with Device Frame -->
        <div class="w-full h-full flex items-center justify-center bg-gray-100">
          <div 
            :class="[
              'bg-white shadow-2xl transition-all duration-300',
              deviceFrameClass
            ]"
          >
            <iframe 
              v-if="previewUrl"
              :src="previewUrl" 
              class="w-full h-full border-0"
              title="Template preview"
            ></iframe>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { 
  HeartIcon, 
  SparklesIcon, 
  EyeIcon, 
  CheckIcon,
  DevicePhoneMobileIcon,
  DeviceTabletIcon,
  ComputerDesktopIcon,
  ClipboardDocumentCheckIcon,
  ShareIcon,
  MapPinIcon,
  ClockIcon,
  PhotoIcon,
  PhoneIcon,
} from '@heroicons/vue/24/outline'

interface TemplateSettings {
  colors?: {
    primary?: string
    secondary?: string
    background?: string
  }
  fonts?: {
    heading?: string
  }
}

interface Template {
  id: number
  name: string
  slug: string
  description: string
  preview_image: string
  is_premium: boolean
  settings: TemplateSettings
}

interface Package {
  name: string
  price: number
  currency: string
  features: string[]
  popular: boolean
}

const props = defineProps<{
  templates?: Template[]
  packages?: Package[]
}>()

// Debug logging
console.log('LandingPage Props:', {
  templates: props.templates,
  templatesLength: props.templates?.length,
  packages: props.packages,
  packagesLength: props.packages?.length
})

// Provide defaults if props are empty
const templateList = computed(() => {
  const list = props.templates || []
  console.log('templateList computed:', list.length, list)
  return list
})
const packageList = computed(() => props.packages || [])

// Category filtering
const selectedCategory = ref('all')
const categories = [
  { id: 'all', name: 'All', icon: '✨' },
  { id: 'wedding', name: 'Weddings', icon: '💍' },
  { id: 'birthday', name: 'Birthdays', icon: '🎂' },
  { id: 'anniversary', name: 'Anniversaries', icon: '💕' },
  { id: 'party', name: 'Parties', icon: '🎉' },
]

const filteredTemplates = computed(() => {
  console.log('filteredTemplates computing...', {
    selectedCategory: selectedCategory.value,
    templateListLength: templateList.value?.length,
    templates: templateList.value
  })
  
  if (selectedCategory.value === 'all') {
    return templateList.value
  }
  
  const filtered = templateList.value.filter(t => {
    console.log('Checking template:', t.name, 'category:', t.category)
    return t.category === selectedCategory.value
  })
  
  console.log('Filtered result:', filtered)
  return filtered
})

// Preview modal state
const showPreview = ref(false)
const selectedTemplate = ref<Template | null>(null)
const selectedDevice = ref('Desktop')
const mobileMenuOpen = ref(false)

const devices = [
  { name: 'Mobile', icon: DevicePhoneMobileIcon, width: 'w-[375px]', height: 'h-[667px]' },
  { name: 'Tablet', icon: DeviceTabletIcon, width: 'w-[768px]', height: 'h-[1024px]' },
  { name: 'Desktop', icon: ComputerDesktopIcon, width: 'w-full', height: 'h-full' },
]

const deviceFrameClass = computed(() => {
  const device = devices.find(d => d.name === selectedDevice.value)
  if (!device) return 'w-full h-full'
  
  if (device.name === 'Desktop') {
    return 'w-full h-full'
  }
  
  return `${device.width} ${device.height} rounded-3xl overflow-hidden`
})

const previewUrl = computed(() => {
  if (!selectedTemplate.value) return ''
  return `/weddings/templates/${selectedTemplate.value.slug}/preview`
})

const openPreview = (template: Template) => {
  selectedTemplate.value = template
  selectedDevice.value = 'Desktop'
  showPreview.value = true
  // Prevent body scroll when modal is open
  document.body.style.overflow = 'hidden'
}

const closePreview = () => {
  showPreview.value = false
  selectedTemplate.value = null
  selectedDevice.value = 'Desktop'
  // Restore body scroll
  document.body.style.overflow = ''
}
</script>
