<template>
  <div class="space-y-6">
    <!-- Active Programs Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Active Incentive Programs</h3>
        <span class="text-sm text-gray-500">{{ activePrograms.length }} active</span>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div 
          v-for="program in activePrograms" 
          :key="program.program_id"
          class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
        >
          <div class="flex items-start justify-between mb-2">
            <h4 class="font-medium text-gray-900">{{ program.name }}</h4>
            <span 
              :class="getProgramTypeColor(program.type)"
              class="px-2 py-1 text-xs font-medium rounded-full"
            >
              {{ formatProgramType(program.type) }}
            </span>
          </div>
          
          <div class="space-y-2 text-sm text-gray-600">
            <div class="flex justify-between">
              <span>Your Score:</span>
              <span class="font-medium">{{ program.qualification_score.toFixed(1) }}</span>
            </div>
            <div class="flex justify-between" v-if="program.current_position">
              <span>Current Position:</span>
              <span class="font-medium text-blue-600">#{{ program.current_position }}</span>
            </div>
            <div class="flex justify-between">
              <span>Ends:</span>
              <span class="font-medium">{{ formatDate(program.end_date) }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="activePrograms.length === 0" class="text-center py-8 text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p>No active incentive programs at the moment</p>
      </div>
    </div>

    <!-- Raffle Entries Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Your Raffle Entries</h3>
        <span class="text-sm text-gray-500">{{ raffleEntries.length }} entries</span>
      </div>
      
      <div class="space-y-4">
        <div 
          v-for="entry in raffleEntries" 
          :key="entry.user_id + '-' + entry.incentive_program_id"
          class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
        >
          <div>
            <h4 class="font-medium text-gray-900">{{ entry.program_name || 'Raffle Program' }}</h4>
            <p class="text-sm text-gray-600">{{ entry.user_tier }} Tier</p>
          </div>
          
          <div class="text-right">
            <div class="text-lg font-bold text-blue-600">{{ entry.entry_count }}</div>
            <div class="text-sm text-gray-500">entries</div>
            <div v-if="entry.is_winner" class="text-xs font-medium text-green-600 mt-1">
              🏆 Winner!
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="raffleEntries.length === 0" class="text-center py-8 text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 011 1v1a1 1 0 01-1 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7a1 1 0 01-1-1V5a1 1 0 011-1h4z" />
        </svg>
        <p>No raffle entries yet</p>
      </div>
    </div>

    <!-- Certificates Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Your Certificates</h3>
        <span class="text-sm text-gray-500">{{ certificates.length }} earned</span>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div 
          v-for="certificate in certificates" 
          :key="certificate.certificate_number"
          class="border border-gray-200 rounded-lg p-4 hover:border-yellow-300 transition-colors"
        >
          <div class="flex items-start justify-between mb-2">
            <div class="flex-1">
              <h4 class="font-medium text-gray-900">{{ certificate.title }}</h4>
              <p class="text-sm text-gray-600 mt-1">{{ formatCertificateType(certificate.type) }}</p>
            </div>
            <svg class="h-6 w-6 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </div>
          
          <div class="space-y-1 text-sm text-gray-600">
            <div class="flex justify-between">
              <span>Certificate #:</span>
              <span class="font-mono text-xs">{{ certificate.certificate_number }}</span>
            </div>
            <div class="flex justify-between">
              <span>Issued:</span>
              <span>{{ formatDate(certificate.issued_date) }}</span>
            </div>
          </div>
          
          <div class="mt-3">
            <a 
              :href="certificate.verification_url" 
              target="_blank"
              class="text-xs text-blue-600 hover:text-blue-800 underline"
            >
              Verify Certificate
            </a>
          </div>
        </div>
      </div>
      
      <div v-if="certificates.length === 0" class="text-center py-8 text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
        </svg>
        <p>No certificates earned yet</p>
      </div>
    </div>

    <!-- Upcoming Events Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Upcoming Recognition Events</h3>
        <span class="text-sm text-gray-500">{{ upcomingEvents.length }} events</span>
      </div>
      
      <div class="space-y-4">
        <div 
          v-for="event in upcomingEvents" 
          :key="event.event_id"
          class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-purple-300 transition-colors"
        >
          <div class="flex-1">
            <h4 class="font-medium text-gray-900">{{ event.name }}</h4>
            <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600">
              <span>{{ formatDate(event.event_date) }}</span>
              <span class="flex items-center">
                <svg v-if="event.is_virtual" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <svg v-else class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ event.location }}
              </span>
            </div>
            <div class="text-xs text-gray-500 mt-1">
              Registration deadline: {{ formatDate(event.registration_deadline) }}
            </div>
          </div>
          
          <button class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
            Register
          </button>
        </div>
      </div>
      
      <div v-if="upcomingEvents.length === 0" class="text-center py-8 text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1h3z" />
        </svg>
        <p>No upcoming events</p>
      </div>
    </div>

    <!-- Incentive Earnings Summary -->
    <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg border border-green-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Incentive Earnings Summary</h3>
        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
        </svg>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-green-600">K{{ formatCurrency(totalIncentiveEarnings) }}</div>
          <div class="text-sm text-gray-600">Total Incentive Earnings</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-blue-600">{{ programHistory.length }}</div>
          <div class="text-sm text-gray-600">Programs Participated</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-purple-600">{{ certificates.length }}</div>
          <div class="text-sm text-gray-600">Certificates Earned</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  incentiveSummary: {
    type: Object,
    required: true
  }
})

const activePrograms = computed(() => props.incentiveSummary.active_programs || [])
const raffleEntries = computed(() => props.incentiveSummary.raffle_entries || [])
const certificates = computed(() => props.incentiveSummary.certificates_earned || [])
const upcomingEvents = computed(() => props.incentiveSummary.upcoming_events || [])
const totalIncentiveEarnings = computed(() => props.incentiveSummary.total_incentive_earnings || 0)
const programHistory = computed(() => props.incentiveSummary.program_history || [])

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US').format(amount)
}

const formatProgramType = (type) => {
  const types = {
    'competition': 'Competition',
    'raffle': 'Raffle',
    'bonus_multiplier': 'Bonus',
    'recognition': 'Recognition'
  }
  return types[type] || type
}

const formatCertificateType = (type) => {
  const types = {
    'achievement': 'Achievement',
    'tier_advancement': 'Tier Advancement',
    'course_completion': 'Course Completion',
    'recognition_award': 'Recognition Award',
    'leadership': 'Leadership',
    'community_service': 'Community Service',
    'mentorship': 'Mentorship',
    'annual_recognition': 'Annual Recognition'
  }
  return types[type] || type
}

const getProgramTypeColor = (type) => {
  const colors = {
    'competition': 'bg-blue-100 text-blue-800',
    'raffle': 'bg-purple-100 text-purple-800',
    'bonus_multiplier': 'bg-green-100 text-green-800',
    'recognition': 'bg-yellow-100 text-yellow-800'
  }
  return colors[type] || 'bg-gray-100 text-gray-800'
}
</script>