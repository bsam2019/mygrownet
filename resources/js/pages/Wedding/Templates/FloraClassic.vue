<template>
  <div class="min-h-screen relative overflow-hidden" :style="{ backgroundColor: colors.background }">
    <!-- Flora decorative background -->
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
          @click="$emit('toggle-menu')"
          aria-label="Toggle navigation menu"
          class="w-10 h-10 flex items-center justify-center rounded-sm transition-all duration-200"
          :style="{ color: colors.primary }"
        >
          <div v-if="!mobileMenuOpen" class="flex flex-col justify-center items-center space-y-1.5">
            <span class="block w-6 h-0.5 bg-current"></span>
            <span class="block w-6 h-0.5 bg-current"></span>
            <span class="block w-6 h-0.5 bg-current"></span>
          </div>
          <XMarkIcon v-else class="h-7 w-7" />
        </button>
        
        <h2 class="text-sm font-medium tracking-[0.15em] uppercase" :style="{ color: colors.primary }">
          {{ activeTabLabel }}
        </h2>
        
        <button 
          @click="$emit('toggle-share')"
          aria-label="Share wedding invitation"
          class="w-10 h-10 flex items-center justify-center rounded-sm transition-all duration-200"
          :style="{ color: colors.primary }"
        >
          <ShareIcon class="h-5 w-5" />
        </button>
      </div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10">
      <div class="h-[12vh] md:h-12 lg:h-16"></div>

      <!-- Header -->
      <header class="relative pt-1 md:pt-20 lg:pt-24 pb-4 md:pb-6 text-center">
        <div class="max-w-4xl mx-auto px-4">
          <!-- Desktop Names -->
          <h1 
            class="hidden md:flex md:flex-col md:items-center text-4xl md:text-6xl lg:text-7xl mb-4 drop-shadow-lg"
            :style="{ 
              fontFamily: fonts.heading + ', cursive',
              background: `linear-gradient(to right, ${colors.primary}, ${colors.secondary})`,
              WebkitBackgroundClip: 'text',
              WebkitTextFillColor: 'transparent'
            }"
          >
            <span>{{ wedding.groom_name }}</span>
            <span class="text-2xl md:text-3xl">&</span>
            <span>{{ wedding.bride_name }}</span>
          </h1>
          <p class="hidden md:block text-sm md:text-base font-semibold tracking-[0.2em] mb-6 uppercase" :style="{ color: colors.primary }">
            {{ formatDate(wedding.wedding_date) }}
          </p>
        </div>
        
        <!-- Desktop Navigation -->
        <nav class="hidden md:block relative z-10 border-b-2 bg-white shadow-md" :style="{ borderColor: colors.primary + '40' }">
          <div class="flex justify-center items-center">
            <div class="flex space-x-6 md:space-x-10 text-xs md:text-sm font-normal tracking-[0.1em]">
              <button 
                v-for="tab in tabs" 
                :key="tab.id"
                @click="$emit('set-tab', tab.id)"
                :class="activeTab === tab.id ? 'font-semibold border-b-2 pb-4' : 'pb-4'"
                :style="{ 
                  color: activeTab === tab.id ? colors.primary : colors.primary + '80',
                  borderColor: activeTab === tab.id ? colors.primary : 'transparent'
                }"
                class="transition-colors"
              >
                {{ tab.label }}
              </button>
            </div>
          </div>
        </nav>
      </header>

      <!-- Content Slot -->
      <main>
        <slot 
          :colors="colors" 
          :fonts="fonts" 
          :wedding="wedding"
          :active-tab="activeTab"
        />
      </main>
    </div>

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
        <button 
          @click="$emit('toggle-menu')"
          aria-label="Close navigation menu"
          class="absolute top-4 left-4 w-10 h-10 flex items-center justify-center rounded-sm bg-gray-50 border border-gray-200"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>

        <nav class="flex flex-col items-center space-y-8 text-lg font-light tracking-[0.15em]">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            @click="$emit('set-tab-mobile', tab.id)"
            :style="{ color: activeTab === tab.id ? colors.text : colors.textLight }"
            class="hover:opacity-80 transition-colors"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { XMarkIcon, ShareIcon } from '@heroicons/vue/24/outline'

interface TemplateColors {
  primary: string
  secondary: string
  accent: string
  background: string
  text: string
  textLight: string
}

interface TemplateDecorations {
  headerImage?: string
  backgroundPattern?: string
}

interface Wedding {
  groom_name: string
  bride_name: string
  wedding_date: string
  venue_name: string
  venue_location: string
}

interface Tab {
  id: string
  label: string
}

defineProps<{
  colors: TemplateColors
  fonts: { heading: string; body: string }
  decorations: TemplateDecorations
  wedding: Wedding
  activeTab: string
  activeTabLabel: string
  mobileMenuOpen: boolean
  tabs: Tab[]
}>()

defineEmits(['toggle-menu', 'toggle-share', 'set-tab', 'set-tab-mobile'])

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>
