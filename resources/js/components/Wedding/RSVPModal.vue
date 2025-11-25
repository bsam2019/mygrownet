<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto bg-white">
    <!-- Clean white background matching wedding page - FIXED position to cover entire viewport -->
    <div class="fixed inset-0 bg-white z-0">
      <!-- Subtle decorative line pattern -->
      <div class="absolute inset-0 opacity-5">
        <div class="h-full w-full" style="background-image: repeating-linear-gradient(45deg, #6b7280 0, #6b7280 1px, transparent 0, transparent 50%); background-size: 20px 20px;"></div>
      </div>
    </div>

    <!-- Animated Balloons Background -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
      <div
        v-for="balloon in balloons"
        :key="balloon.id"
        class="balloon absolute"
        :class="{ 'balloon-pop': balloon.popping }"
        :style="{
          left: balloon.x + '%',
          bottom: balloon.startY + 'px',
          '--float-duration': balloon.duration + 's',
          '--float-delay': balloon.delay + 's',
          '--sway-amount': balloon.sway + 'px',
          '--balloon-color': balloon.color,
          '--balloon-size': balloon.size + 'px',
          animationDelay: balloon.delay + 's'
        }"
      >
        <svg 
          :width="balloon.size" 
          :height="balloon.size * 1.2" 
          viewBox="0 0 50 60" 
          class="balloon-svg"
        >
          <!-- Balloon body -->
          <ellipse 
            cx="25" 
            cy="22" 
            rx="20" 
            ry="22" 
            :fill="balloon.color"
            class="balloon-body"
          />
          <!-- Balloon highlight -->
          <ellipse 
            cx="18" 
            cy="15" 
            rx="6" 
            ry="8" 
            fill="rgba(255,255,255,0.3)"
          />
          <!-- Balloon knot -->
          <polygon 
            points="25,44 22,48 28,48" 
            :fill="balloon.color"
          />
          <!-- Balloon string -->
          <path 
            d="M25,48 Q27,52 24,56 Q21,60 25,64" 
            stroke="#999" 
            stroke-width="1" 
            fill="none"
            class="balloon-string"
          />
        </svg>
      </div>
    </div>

    <!-- Confetti Burst Container -->
    <div class="fixed inset-0 pointer-events-none z-30 overflow-hidden" aria-hidden="true">
      <div
        v-for="confetti in confettiPieces"
        :key="confetti.id"
        class="confetti absolute"
        :style="{
          left: confetti.x + '%',
          top: confetti.startY + '%',
          '--fall-duration': confetti.duration + 's',
          '--fall-delay': confetti.delay + 's',
          '--drift-amount': confetti.drift + 'px',
          '--rotation': confetti.rotation + 'deg',
          '--confetti-color': confetti.color,
          width: confetti.width + 'px',
          height: confetti.height + 'px',
          backgroundColor: confetti.color,
          borderRadius: confetti.shape === 'circle' ? '50%' : '2px',
          animationDelay: confetti.delay + 's'
        }"
      ></div>
    </div>

    <!-- Fixed Header with Title and Progress -->
    <div class="relative z-20 bg-white border-b border-gray-100">
      <!-- Header Row -->
      <div class="flex items-center justify-between px-6 py-4">
        <!-- Spacer for balance -->
        <div class="w-10"></div>
        
        <!-- Centered Title -->
        <h1 class="text-base font-medium text-gray-600 tracking-[0.2em] uppercase">RSVP</h1>
        
        <!-- Close Button -->
        <button
          @click="closeModal"
          class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors rounded-full hover:bg-gray-50"
          aria-label="Close RSVP modal"
        >
          <XMarkIcon class="h-5 w-5" aria-hidden="true" />
        </button>
      </div>
      
      <!-- Progress Bar - Elegant stepped design -->
      <div class="px-6 pb-4">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs text-gray-400 tracking-wide">Step {{ currentStep }} of {{ totalSteps }}</span>
          <span class="text-xs text-gray-400 tracking-wide">{{ Math.round((currentStep / totalSteps) * 100) }}%</span>
        </div>
        <div class="relative h-1.5 bg-gray-100 rounded-full overflow-hidden">
          <div 
            class="absolute inset-y-0 left-0 bg-gradient-to-r from-gray-400 to-gray-500 rounded-full transition-all duration-700 ease-out"
            :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
          ></div>
        </div>
        <!-- Step indicators -->
        <div class="flex justify-between mt-3">
          <div 
            v-for="step in totalSteps" 
            :key="step"
            class="flex flex-col items-center"
          >
            <div 
              :class="[
                'w-2.5 h-2.5 rounded-full transition-all duration-300',
                step <= currentStep ? 'bg-gray-500' : 'bg-gray-200'
              ]"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Container -->
    <div class="relative z-10 overflow-y-auto" style="max-height: calc(100vh - 140px);">
      <div class="min-h-[calc(100vh-140px)] flex flex-col justify-center py-10 px-6 sm:px-8">
        <div class="w-full max-w-xl mx-auto">
        <!-- Content Card - Clean, minimal with subtle shadow -->
        <div class="bg-white rounded-sm">
          <div class="p-8 sm:p-12">
            
            <!-- Step 1: Search Guest -->
            <div v-if="currentStep === 1" class="text-center space-y-8">
              <!-- Wedding Header -->
              <div class="space-y-5">
                <h2 class="text-3xl md:text-4xl font-light text-gray-600 tracking-[0.15em] font-serif">
                  KAOMA & MUBANGA
                </h2>
                <div class="flex items-center justify-center gap-5">
                  <div class="w-16 h-px bg-gray-300"></div>
                  <HeartIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                  <div class="w-16 h-px bg-gray-300"></div>
                </div>
                <p class="text-base text-gray-500 font-light leading-relaxed max-w-md mx-auto">
                  If you're responding for you and a guest, you'll be able to RSVP for your entire group.
                </p>
              </div>

              <!-- Search Form -->
              <div class="max-w-md mx-auto space-y-5 pt-2">
                <div class="relative">
                  <input
                    v-model="guestName"
                    type="text"
                    placeholder="Enter your full name"
                    class="w-full px-5 py-4 text-base border border-gray-200 rounded-sm focus:ring-2 focus:ring-gray-300 focus:border-gray-400 transition-all duration-200 text-center bg-white placeholder:text-gray-400"
                    @keyup.enter="searchGuest"
                  >
                </div>
                <button
                  @click="searchGuest"
                  :disabled="!guestName.trim() || isSearching"
                  class="w-full bg-gray-500 hover:bg-gray-600 disabled:bg-gray-200 disabled:text-gray-400 text-white py-4 px-8 text-sm font-medium tracking-[0.15em] transition-colors rounded-sm"
                >
                  <span v-if="isSearching" class="flex items-center justify-center space-x-3">
                    <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    <span>SEARCHING...</span>
                  </span>
                  <span v-else>FIND YOUR INVITATION</span>
                </button>
              </div>
            </div>

            <!-- Step 2: Confirm Guest -->
            <div v-if="currentStep === 2" class="text-center space-y-8">
              <div class="space-y-5">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gray-100 mb-2">
                  <CheckIcon class="w-7 h-7 text-gray-500" aria-hidden="true" />
                </div>
                <p class="text-sm text-gray-400 tracking-[0.3em] uppercase">FOUND YOU</p>
                <h2 class="text-2xl md:text-3xl font-light text-gray-600 tracking-[0.1em] font-serif">Guest Confirmation</h2>
                <p class="text-base text-gray-500 font-light leading-relaxed max-w-md mx-auto">
                  Please confirm your name below to continue with your RSVP.
                </p>
              </div>

              <div class="max-w-md mx-auto space-y-6">
                <!-- Guest Name Confirmation -->
                <div class="bg-gray-50 border border-gray-200 rounded-sm p-5 hover:border-gray-300 transition-colors">
                  <label class="flex items-center justify-center space-x-4 cursor-pointer">
                    <input
                      v-model="confirmGuest"
                      type="checkbox"
                      id="confirmName"
                      class="h-5 w-5 text-gray-500 rounded border-gray-300 focus:ring-gray-400"
                    >
                    <span class="text-lg font-light text-gray-700 tracking-wide">
                      {{ foundGuest.name }}
                    </span>
                  </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col space-y-4">
                  <button
                    @click="confirmGuestIdentity"
                    :disabled="!confirmGuest"
                    class="w-full bg-gray-500 hover:bg-gray-600 disabled:bg-gray-200 disabled:text-gray-400 text-white py-4 px-8 text-sm font-medium tracking-[0.15em] transition-colors rounded-sm"
                  >
                    CONTINUE
                  </button>
                  <button
                    @click="searchAgain"
                    class="w-full bg-white hover:bg-gray-50 text-gray-500 py-4 px-8 text-sm font-medium tracking-[0.15em] border border-gray-200 transition-colors rounded-sm"
                  >
                    SEARCH AGAIN
                  </button>
                </div>
              </div>
            </div>

            <!-- Step 3: RSVP Response -->
            <div v-if="currentStep === 3" class="text-center space-y-8">
              <!-- Wedding Day Card -->
              <div class="bg-gradient-to-b from-gray-50 to-white border border-gray-200 rounded-sm p-6 md:p-8">
                <p class="text-sm text-gray-400 tracking-[0.3em] uppercase mb-3">Wedding Day</p>
                <div class="flex items-center justify-center gap-2 mb-2">
                  <CalendarIcon class="w-5 h-5 text-gray-400" aria-hidden="true" />
                  <h2 class="text-xl md:text-2xl font-light text-gray-600 tracking-[0.08em] font-serif">Saturday, December 06, 2025</h2>
                </div>
                <p class="text-base text-gray-500 font-light">at 11:00 AM</p>
                <div class="w-20 h-px bg-gray-300 mx-auto my-5"></div>
                <p class="text-lg font-light text-gray-700 tracking-wide">{{ foundGuest.name }}</p>
              </div>

              <div class="max-w-md mx-auto space-y-6">
                <!-- RSVP Response Buttons -->
                <div class="space-y-4">
                  <p class="text-sm text-gray-500 tracking-[0.15em] uppercase">Will you be attending?</p>
                  <div class="grid grid-cols-2 gap-4">
                    <button
                      @click="setResponse('accepted')"
                      :class="[
                        'py-5 px-5 text-sm font-medium tracking-[0.1em] transition-all duration-200 border rounded-sm',
                        rsvpResponse === 'accepted'
                          ? 'bg-gray-500 text-white border-gray-500 shadow-md'
                          : 'bg-white hover:bg-gray-50 text-gray-500 border-gray-200 hover:border-gray-300'
                      ]"
                    >
                      <div class="flex flex-col items-center space-y-3">
                        <CheckIcon class="w-6 h-6" aria-hidden="true" />
                        <span>ACCEPT</span>
                      </div>
                    </button>
                    <button
                      @click="setResponse('declined')"
                      :class="[
                        'py-5 px-5 text-sm font-medium tracking-[0.1em] transition-all duration-200 border rounded-sm',
                        rsvpResponse === 'declined'
                          ? 'bg-gray-500 text-white border-gray-500 shadow-md'
                          : 'bg-white hover:bg-gray-50 text-gray-500 border-gray-200 hover:border-gray-300'
                      ]"
                    >
                      <div class="flex flex-col items-center space-y-3">
                        <XMarkIcon class="w-6 h-6" aria-hidden="true" />
                        <span>DECLINE</span>
                      </div>
                    </button>
                  </div>
                </div>

                <!-- Continue Button -->
                <button
                  @click="proceedToEmail"
                  :disabled="!rsvpResponse"
                  class="w-full bg-gray-500 hover:bg-gray-600 disabled:bg-gray-200 disabled:text-gray-400 text-white py-4 px-8 text-sm font-medium tracking-[0.15em] transition-colors rounded-sm"
                >
                  CONTINUE
                </button>
              </div>
            </div>

            <!-- Step 4: Email Confirmation -->
            <div v-if="currentStep === 4" class="text-center space-y-8">
              <div class="space-y-5">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gray-100 mb-2">
                  <EnvelopeIcon class="w-7 h-7 text-gray-500" aria-hidden="true" />
                </div>
                <p class="text-sm text-gray-400 tracking-[0.3em] uppercase">LAST STEP</p>
                <h2 class="text-2xl md:text-3xl font-light text-gray-600 tracking-[0.1em] font-serif">Send Your RSVP</h2>
                <p class="text-base text-gray-500 font-light leading-relaxed max-w-md mx-auto">
                  Send your RSVP to Kaoma & Mubanga
                </p>
              </div>
              
              <div class="max-w-md mx-auto space-y-5">
                <!-- Email Confirmation Option -->
                <div class="bg-gray-50 border border-gray-200 rounded-sm p-5 hover:border-gray-300 transition-colors">
                  <label class="flex items-center justify-center space-x-4 cursor-pointer">
                    <input
                      v-model="sendEmailConfirmation"
                      type="checkbox"
                      class="h-5 w-5 text-gray-500 rounded border-gray-300 focus:ring-gray-400"
                    >
                    <span class="text-base font-light text-gray-600">
                      Send me an RSVP confirmation by email
                    </span>
                  </label>
                </div>

                <!-- Email Input -->
                <div v-if="sendEmailConfirmation" class="space-y-2">
                  <input
                    v-model="email"
                    type="email"
                    placeholder="Enter your email address"
                    class="w-full px-5 py-4 text-base border border-gray-200 rounded-sm focus:ring-2 focus:ring-gray-300 focus:border-gray-400 transition-all duration-200 bg-white placeholder:text-gray-400"
                  >
                </div>

                <!-- Send Button -->
                <button
                  @click="sendRSVP"
                  :disabled="isSending || (sendEmailConfirmation && !email)"
                  class="w-full bg-gray-500 hover:bg-gray-600 disabled:bg-gray-200 disabled:text-gray-400 text-white py-4 px-8 text-sm font-medium tracking-[0.15em] transition-colors rounded-sm"
                >
                  <span v-if="isSending" class="flex items-center justify-center space-x-3">
                    <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    <span>SENDING...</span>
                  </span>
                  <span v-else>SEND RSVP</span>
                </button>
              </div>
            </div>

            <!-- Step 5: Success -->
            <div v-if="currentStep === 5" class="text-center space-y-8">
              <!-- Success Icon -->
              <div class="flex items-center justify-center">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center shadow-inner">
                  <CheckIcon class="w-10 h-10 text-gray-500" aria-hidden="true" />
                </div>
              </div>

              <div class="space-y-5">
                <p class="text-sm text-gray-400 tracking-[0.3em] uppercase">COMPLETE</p>
                <h2 class="text-2xl md:text-3xl font-light text-gray-600 tracking-[0.1em] font-serif">RSVP Submitted</h2>
                <p class="text-base text-gray-500 font-light leading-relaxed max-w-md mx-auto">
                  Thank you for your response. Here's what we sent Kaoma & Mubanga.
                </p>
              </div>

              <div class="max-w-md mx-auto space-y-5">
                <!-- RSVP Details -->
                <div class="space-y-4">
                  <!-- Response Card -->
                  <div class="bg-gray-50 border border-gray-200 rounded-sm p-5 text-left hover:border-gray-300 transition-colors">
                    <div class="flex items-center justify-between">
                      <div>
                        <p class="text-xs text-gray-400 tracking-[0.1em] uppercase mb-2">Your Response</p>
                        <p class="text-base text-gray-700 capitalize font-medium">{{ rsvpResponse }}</p>
                      </div>
                      <button
                        @click="updateResponse"
                        class="text-sm text-gray-500 hover:text-gray-700 underline transition-colors tracking-wide"
                      >
                        Update
                      </button>
                    </div>
                  </div>

                  <!-- Wedding Day Card -->
                  <div class="bg-gray-50 border border-gray-200 rounded-sm p-5 text-left hover:border-gray-300 transition-colors">
                    <div class="flex items-center justify-between">
                      <div>
                        <p class="text-xs text-gray-400 tracking-[0.1em] uppercase mb-2">Wedding Day</p>
                        <p class="text-base text-gray-700">December 06, 2025 • 11:00 AM</p>
                      </div>
                      <button
                        @click="addToCalendar"
                        class="text-sm text-gray-500 hover:text-gray-700 underline transition-colors tracking-wide"
                      >
                        Add to Calendar
                      </button>
                    </div>
                  </div>

                  <!-- Guest Name -->
                  <div class="border border-gray-200 rounded-sm p-6 text-center bg-white">
                    <p class="text-xl font-light text-gray-700 tracking-wide font-serif">{{ foundGuest.name }}</p>
                    <p class="text-sm text-gray-400 mt-3 tracking-wide">Thank you for your response</p>
                  </div>
                </div>

                <!-- Back Button -->
                <button
                  @click="closeModal"
                  class="w-full bg-gray-500 hover:bg-gray-600 text-white py-4 px-8 text-sm font-medium tracking-[0.15em] transition-colors rounded-sm"
                >
                  BACK TO HOMEPAGE
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { XMarkIcon, CheckIcon, HeartIcon, CalendarIcon, EnvelopeIcon } from '@heroicons/vue/24/outline'

interface Props {
  isOpen: boolean
  weddingEventId?: number
}

interface Emits {
  (e: 'close'): void
  (e: 'submitted'): void
}

const props = withDefaults(defineProps<Props>(), {
  weddingEventId: 1
})
const emit = defineEmits<Emits>()

// State
const currentStep = ref(1)
const totalSteps = 5
const guestName = ref('')
const confirmGuest = ref(false)
const foundGuest = ref<{ name: string; id: number | null; firstName?: string; lastName?: string; allowedGuests?: number }>({ name: '', id: null })
const rsvpResponse = ref('')
const sendEmailConfirmation = ref(true)
const email = ref('')
const isSearching = ref(false)
const isSending = ref(false)
const searchError = ref('')

// Balloon animation state
const balloons = ref<Array<{
  id: number
  x: number
  startY: number
  duration: number
  delay: number
  sway: number
  color: string
  size: number
  popping: boolean
}>>([])
let balloonInterval: ReturnType<typeof setInterval> | null = null
let balloonIdCounter = 0

// Wedding-themed pastel colors
const balloonColors = [
  '#F8BBD9', // Soft pink
  '#E1BEE7', // Lavender
  '#BBDEFB', // Light blue
  '#C8E6C9', // Mint green
  '#FFF9C4', // Cream yellow
  '#FFCCBC', // Peach
  '#D1C4E9', // Light purple
  '#B2EBF2', // Aqua
  '#F5F5F5', // White
  '#FFE0B2'  // Light gold
]

const createBalloon = () => {
  const id = balloonIdCounter++
  const balloon = {
    id,
    x: Math.random() * 100,
    startY: -100,
    duration: 12 + Math.random() * 8,
    delay: Math.random() * 2,
    sway: 20 + Math.random() * 30,
    color: balloonColors[Math.floor(Math.random() * balloonColors.length)],
    size: 30 + Math.random() * 25,
    popping: false
  }
  
  balloons.value.push(balloon)
  
  const totalTime = (balloon.duration + balloon.delay) * 1000
  setTimeout(() => {
    const idx = balloons.value.findIndex(b => b.id === id)
    if (idx !== -1) {
      balloons.value[idx].popping = true
      setTimeout(() => {
        balloons.value = balloons.value.filter(b => b.id !== id)
      }, 300)
    }
  }, totalTime - 500)
}

const startBalloonAnimation = () => {
  for (let i = 0; i < 8; i++) {
    setTimeout(() => createBalloon(), i * 400)
  }
  
  balloonInterval = setInterval(() => {
    if (balloons.value.length < 20) {
      createBalloon()
    }
  }, 1500)
}

// Confetti state
const confettiPieces = ref<Array<{
  id: number
  x: number
  startY: number
  duration: number
  delay: number
  drift: number
  rotation: number
  color: string
  width: number
  height: number
  shape: 'rect' | 'circle'
}>>([])
let confettiIdCounter = 0

// Wedding celebration colors for confetti
const confettiColors = [
  '#FFD700', // Gold
  '#F8BBD9', // Pink
  '#FFFFFF', // White
  '#E1BEE7', // Lavender
  '#FFE0B2', // Light gold
  '#FFCCBC', // Peach
  '#C8E6C9', // Mint
  '#B2EBF2'  // Aqua
]

const triggerConfetti = () => {
  // Create 120 confetti pieces spread across the full page
  for (let i = 0; i < 120; i++) {
    const id = confettiIdCounter++
    const confetti = {
      id,
      x: Math.random() * 100, // Full width spread (0-100%)
      startY: -5 - Math.random() * 10, // Staggered start heights
      duration: 3 + Math.random() * 3, // 3-6 seconds to fall
      delay: Math.random() * 0.8, // Staggered start
      drift: -50 + Math.random() * 100, // Horizontal drift
      rotation: Math.random() * 1080, // More spin (up to 3 rotations)
      color: confettiColors[Math.floor(Math.random() * confettiColors.length)],
      width: 8 + Math.random() * 10, // 8-18px wide
      height: 6 + Math.random() * 12, // 6-18px tall
      shape: Math.random() > 0.5 ? 'rect' : 'circle' as 'rect' | 'circle'
    }
    
    confettiPieces.value.push(confetti)
    
    // Remove confetti after animation
    setTimeout(() => {
      confettiPieces.value = confettiPieces.value.filter(c => c.id !== id)
    }, (confetti.duration + confetti.delay) * 1000 + 500)
  }
}

const stopBalloonAnimation = () => {
  if (balloonInterval) {
    clearInterval(balloonInterval)
    balloonInterval = null
  }
  balloons.value = []
}

// Lock body scroll when modal is open
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = 'hidden'
    startBalloonAnimation()
  } else {
    document.body.style.overflow = ''
    stopBalloonAnimation()
  }
}, { immediate: true })

onUnmounted(() => {
  document.body.style.overflow = ''
  stopBalloonAnimation()
})

// Methods
const closeModal = () => {
  emit('close')
  resetModal()
}

const resetModal = () => {
  currentStep.value = 1
  guestName.value = ''
  confirmGuest.value = false
  foundGuest.value = { name: '', id: null }
  rsvpResponse.value = ''
  sendEmailConfirmation.value = true
  email.value = ''
  isSearching.value = false
  isSending.value = false
}

const searchGuest = async () => {
  if (!guestName.value.trim()) return
  
  isSearching.value = true
  searchError.value = ''
  
  try {
    const response = await fetch(`/wedding/${props.weddingEventId}/search-guest`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({ name: guestName.value.trim() })
    })

    const data = await response.json()

    if (data.success && data.found) {
      // Guest found in invited list
      foundGuest.value = { 
        name: data.guest.name, 
        id: data.guest.id,
        firstName: data.guest.first_name,
        lastName: data.guest.last_name,
        allowedGuests: data.guest.allowed_guests
      }
      currentStep.value = 2
    } else {
      // Guest not found - allow them to proceed anyway (open RSVP)
      // This allows uninvited guests to still RSVP
      foundGuest.value = { name: guestName.value.trim(), id: null }
      currentStep.value = 2
    }
  } catch (error) {
    console.error('Guest search failed:', error)
    // On error, still allow them to proceed
    foundGuest.value = { name: guestName.value.trim(), id: null }
    currentStep.value = 2
  } finally {
    isSearching.value = false
  }
}

const searchAgain = () => {
  currentStep.value = 1
  guestName.value = ''
  confirmGuest.value = false
}

const confirmGuestIdentity = () => {
  if (confirmGuest.value) {
    currentStep.value = 3
  }
}

const setResponse = (response: string) => {
  rsvpResponse.value = response
}

const proceedToEmail = () => {
  if (rsvpResponse.value) {
    currentStep.value = 4
  }
}

const sendRSVP = async () => {
  if (sendEmailConfirmation.value && !email.value) return
  
  isSending.value = true
  
  try {
    const response = await fetch(`/wedding/${props.weddingEventId}/rsvp`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        guest_id: foundGuest.value.id,
        first_name: foundGuest.value.firstName || foundGuest.value.name.split(' ')[0] || '',
        last_name: foundGuest.value.lastName || foundGuest.value.name.split(' ').slice(1).join(' ') || '',
        email: sendEmailConfirmation.value ? email.value : '',
        attending: rsvpResponse.value,
        guest_count: rsvpResponse.value === 'accepted' ? (foundGuest.value.allowedGuests || 1) : 0,
        message: '',
      })
    })

    const data = await response.json()

    if (data.success) {
      currentStep.value = 5
      // Trigger confetti celebration!
      triggerConfetti()
      emit('submitted')
    } else {
      console.error('RSVP submission failed:', data.error)
      alert(data.error || 'Failed to submit RSVP. Please try again.')
    }
  } catch (error) {
    console.error('RSVP submission error:', error)
    alert('Failed to submit RSVP. Please try again.')
  } finally {
    isSending.value = false
  }
}

const updateResponse = () => {
  currentStep.value = 3
}

const addToCalendar = () => {
  const event = {
    title: "Kaoma & Mubanga's Wedding",
    start: new Date('2025-12-06T11:00:00'),
    end: new Date('2025-12-06T16:00:00')
  }
  
  const startDate = event.start.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z'
  const endDate = event.end.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z'
  const calendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(event.title)}&dates=${startDate}/${endDate}`
  
  window.open(calendarUrl, '_blank')
}
</script>

<style scoped>
/* Minimal animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fixed.inset-0 {
  animation: fadeIn 0.2s ease-out;
}

/* Loading spinner */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Input focus */
input:focus {
  outline: none;
}

/* Checkbox styling */
input[type="checkbox"]:checked {
  background-color: #6b7280;
  border-color: #6b7280;
}

/* Balloon Animations */
.balloon {
  animation: 
    floatUp var(--float-duration, 15s) linear forwards,
    sway 3s ease-in-out infinite;
  opacity: 0.7;
  filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
  transform-origin: center bottom;
}

.balloon-svg {
  transition: transform 0.3s ease;
}

.balloon-string {
  animation: stringWave 2s ease-in-out infinite;
}

/* Float up animation */
@keyframes floatUp {
  0% {
    bottom: -100px;
    opacity: 0;
  }
  5% {
    opacity: 0.7;
  }
  90% {
    opacity: 0.7;
  }
  100% {
    bottom: 110vh;
    opacity: 0;
  }
}

/* Gentle side-to-side sway */
@keyframes sway {
  0%, 100% {
    transform: translateX(0) rotate(-2deg);
  }
  25% {
    transform: translateX(calc(var(--sway-amount, 20px) * 0.5)) rotate(1deg);
  }
  50% {
    transform: translateX(var(--sway-amount, 20px)) rotate(2deg);
  }
  75% {
    transform: translateX(calc(var(--sway-amount, 20px) * 0.5)) rotate(-1deg);
  }
}

/* String wave animation */
@keyframes stringWave {
  0%, 100% {
    d: path('M25,48 Q27,52 24,56 Q21,60 25,64');
  }
  50% {
    d: path('M25,48 Q23,52 26,56 Q29,60 25,64');
  }
}

/* Pop animation */
.balloon-pop {
  animation: pop 0.3s ease-out forwards !important;
}

@keyframes pop {
  0% {
    transform: scale(1);
    opacity: 0.7;
  }
  50% {
    transform: scale(1.3);
    opacity: 0.5;
  }
  100% {
    transform: scale(0);
    opacity: 0;
  }
}

/* Confetti Animations */
.confetti {
  animation: 
    confettiFall var(--fall-duration, 4s) ease-out forwards,
    confettiSpin var(--fall-duration, 4s) linear forwards;
  opacity: 1;
  transform-origin: center center;
}

@keyframes confettiFall {
  0% {
    top: -5%;
    opacity: 1;
  }
  10% {
    opacity: 1;
  }
  90% {
    opacity: 0.8;
  }
  100% {
    top: 105%;
    transform: translateX(var(--drift-amount, 0px));
    opacity: 0;
  }
}

@keyframes confettiSpin {
  0% {
    transform: translateX(0) rotate(0deg) scale(1);
  }
  25% {
    transform: translateX(calc(var(--drift-amount, 0px) * 0.25)) rotate(calc(var(--rotation, 360deg) * 0.25)) scale(0.9);
  }
  50% {
    transform: translateX(calc(var(--drift-amount, 0px) * 0.5)) rotate(calc(var(--rotation, 360deg) * 0.5)) scale(1.1);
  }
  75% {
    transform: translateX(calc(var(--drift-amount, 0px) * 0.75)) rotate(calc(var(--rotation, 360deg) * 0.75)) scale(0.95);
  }
  100% {
    transform: translateX(var(--drift-amount, 0px)) rotate(var(--rotation, 360deg)) scale(1);
  }
}

/* Reduce motion for accessibility */
@media (prefers-reduced-motion: reduce) {
  .balloon,
  .confetti {
    animation: none;
    display: none;
  }
}
</style>
