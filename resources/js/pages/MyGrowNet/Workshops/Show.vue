<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import MemberLayout from '@/layouts/MemberLayout.vue'
import { Calendar, MapPin, Users, Award, TrendingUp, Video, CheckCircle, Clock } from 'lucide-vue-next'

interface Workshop {
  id: number
  title: string
  slug: string
  description: string
  category: string
  delivery_format: string
  price: string
  price_raw: number
  lp_reward: number
  bp_reward: number
  start_date: string
  end_date: string
  location: string | null
  meeting_link: string | null
  requirements: string | null
  learning_outcomes: string | null
  instructor_name: string | null
  instructor_bio: string | null
  featured_image: string | null
  registered_count: number
  available_slots: number | null
  is_full: boolean
}

interface UserRegistration {
  id: number
  status: string
  registered_at: string
}

const props = defineProps<{
  workshop: Workshop
  user_registration: UserRegistration | null
  wallet_balance: string
  wallet_balance_raw: number
}>()

import { ref } from 'vue'

const showConfirmModal = ref(false)

const openConfirmModal = () => {
  showConfirmModal.value = true
}

const register = () => {
  router.post(route('mygrownet.workshops.register', props.workshop.id), {
    payment_method: 'wallet',
    notes: null
  })
  showConfirmModal.value = false
}

const getStatusColor = (status: string) => {
  const colors = {
    pending_payment: 'bg-yellow-100 text-yellow-800',
    registered: 'bg-green-100 text-green-800',
    attended: 'bg-blue-100 text-blue-800',
    completed: 'bg-purple-100 text-purple-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status: string) => {
  const labels = {
    pending_payment: 'Pending Payment',
    registered: 'Registered',
    attended: 'Attended',
    completed: 'Completed',
    cancelled: 'Cancelled',
  }
  return labels[status as keyof typeof labels] || status
}
</script>

<template>
  <Head :title="workshop.title" />
  <MemberLayout>
    <div class="p-6 max-w-5xl mx-auto">
      <!-- Header Image -->
      <div v-if="workshop.featured_image" class="h-64 rounded-lg overflow-hidden mb-6">
        <img :src="workshop.featured_image" :alt="workshop.title" class="w-full h-full object-cover" />
      </div>
      <div v-else class="h-64 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mb-6">
        <Award class="w-24 h-24 text-white opacity-50" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ workshop.title }}</h1>
            <p class="text-gray-600 mb-6">{{ workshop.description }}</p>

            <!-- Workshop Details -->
            <div class="grid grid-cols-2 gap-4 mb-6">
              <div class="flex items-center text-gray-600">
                <Calendar class="w-5 h-5 mr-2" />
                <div>
                  <p class="text-xs text-gray-500">Start Date</p>
                  <p class="font-medium">{{ workshop.start_date }}</p>
                </div>
              </div>
              <div class="flex items-center text-gray-600">
                <Clock class="w-5 h-5 mr-2" />
                <div>
                  <p class="text-xs text-gray-500">End Date</p>
                  <p class="font-medium">{{ workshop.end_date }}</p>
                </div>
              </div>
              <div v-if="workshop.location" class="flex items-center text-gray-600">
                <MapPin class="w-5 h-5 mr-2" />
                <div>
                  <p class="text-xs text-gray-500">Location</p>
                  <p class="font-medium">{{ workshop.location }}</p>
                </div>
              </div>
              <div v-if="workshop.meeting_link" class="flex items-center text-gray-600">
                <Video class="w-5 h-5 mr-2" />
                <div>
                  <p class="text-xs text-gray-500">Format</p>
                  <p class="font-medium capitalize">{{ workshop.delivery_format }}</p>
                </div>
              </div>
            </div>

            <!-- Requirements -->
            <div v-if="workshop.requirements" class="mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Requirements</h3>
              <p class="text-gray-600">{{ workshop.requirements }}</p>
            </div>

            <!-- Learning Outcomes -->
            <div v-if="workshop.learning_outcomes" class="mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">What You'll Learn</h3>
              <p class="text-gray-600">{{ workshop.learning_outcomes }}</p>
            </div>

            <!-- Instructor -->
            <div v-if="workshop.instructor_name" class="border-t pt-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Instructor</h3>
              <p class="font-medium text-gray-900">{{ workshop.instructor_name }}</p>
              <p v-if="workshop.instructor_bio" class="text-gray-600 mt-2">{{ workshop.instructor_bio }}</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Registration Status -->
          <div v-if="user_registration" class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Your Registration</h3>
            <div class="space-y-3">
              <div>
                <p class="text-sm text-gray-600">Status</p>
                <span :class="['inline-block px-3 py-1 text-sm font-semibold rounded-full mt-1', getStatusColor(user_registration.status)]">
                  {{ getStatusLabel(user_registration.status) }}
                </span>
              </div>
              <div>
                <p class="text-sm text-gray-600">Registered On</p>
                <p class="font-medium text-gray-900">{{ user_registration.registered_at }}</p>
              </div>
            </div>
          </div>

          <!-- Registration Card -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center mb-4">
              <p class="text-3xl font-bold text-gray-900">K{{ workshop.price }}</p>
              <p class="text-sm text-gray-500">Workshop Fee</p>
            </div>

            <div class="space-y-3 mb-6">
              <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                <span class="text-sm text-gray-600">LP Reward</span>
                <span class="font-bold text-blue-600">{{ workshop.lp_reward }} LP</span>
              </div>
              <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                <span class="text-sm text-gray-600">BP Reward</span>
                <span class="font-bold text-green-600">{{ workshop.bp_reward }} BP</span>
              </div>
            </div>

            <div class="mb-4">
              <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-gray-600">Participants</span>
                <span class="font-medium">{{ workshop.registered_count }} registered</span>
              </div>
              <div v-if="workshop.available_slots !== null" class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Available Slots</span>
                <span :class="['font-medium', workshop.available_slots === 0 ? 'text-red-600' : 'text-green-600']">
                  {{ workshop.available_slots }} left
                </span>
              </div>
            </div>

            <button
              v-if="!user_registration"
              @click="openConfirmModal"
              :disabled="workshop.is_full || wallet_balance_raw < workshop.price_raw"
              :class="[
                'w-full py-3 rounded-lg font-semibold transition-colors',
                workshop.is_full || wallet_balance_raw < workshop.price_raw
                  ? 'bg-gray-300 text-gray-600 cursor-not-allowed'
                  : 'bg-blue-600 text-white hover:bg-blue-700'
              ]"
            >
              {{ workshop.is_full ? 'Workshop Full' : wallet_balance_raw < workshop.price_raw ? 'Insufficient Balance' : 'Register Now' }}
            </button>

            <!-- Insufficient Balance Notice -->
            <div v-if="!user_registration && wallet_balance_raw < workshop.price_raw && !workshop.is_full" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
              <p class="text-sm text-yellow-800">
                <span class="font-semibold">Insufficient wallet balance.</span> You need K{{ workshop.price }} but have K{{ wallet_balance }}.
              </p>
              <Link
                :href="route('mygrownet.payments.create', { type: 'wallet_topup' })"
                class="inline-block mt-2 text-sm text-blue-600 hover:text-blue-700 font-medium"
              >
                Top up your wallet →
              </Link>
            </div>

            <!-- Confirmation Modal -->
            <div v-if="showConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="showConfirmModal = false">
              <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4" @click.stop>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Registration</h3>
                
                <div class="mb-6">
                  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-gray-700 mb-2">Workshop: <span class="font-semibold">{{ workshop.title }}</span></p>
                    <p class="text-sm text-gray-700 mb-2">Price: <span class="font-semibold">K{{ workshop.price }}</span></p>
                    <p class="text-sm text-gray-700">Your Balance: <span class="font-semibold text-green-600">K{{ wallet_balance }}</span></p>
                  </div>
                  <p class="text-sm text-gray-600">
                    K{{ workshop.price }} will be deducted from your wallet. Registration is instant and you'll earn {{ workshop.lp_reward }} LP and {{ workshop.bp_reward }} BP upon completion.
                  </p>
                </div>

                <div class="flex space-x-3">
                  <button
                    @click="showConfirmModal = false"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                  >
                    Cancel
                  </button>
                  <button
                    @click="register"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                  >
                    Confirm & Pay
                  </button>
                </div>
              </div>
            </div>

            <div v-else-if="user_registration" class="flex items-center justify-center text-green-600 py-3">
              <CheckCircle class="w-5 h-5 mr-2" />
              <span class="font-medium">You're Registered!</span>
            </div>
          </div>

          <!-- Info Card -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-semibold text-blue-900 mb-2">Why Attend?</h4>
            <ul class="text-sm text-blue-800 space-y-1">
              <li>✓ Earn valuable LP & BP points</li>
              <li>✓ Get certified upon completion</li>
              <li>✓ Learn from expert instructors</li>
              <li>✓ Network with other members</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>
