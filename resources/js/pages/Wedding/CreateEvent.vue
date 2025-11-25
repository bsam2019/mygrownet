<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 to-purple-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Your Wedding Event</h1>
        <p class="text-gray-600">Let's start planning your perfect wedding day</p>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-pink-500 to-purple-600 px-6 py-4">
          <h2 class="text-xl font-semibold text-white">Wedding Details</h2>
        </div>
        
        <form @submit.prevent="submit" class="p-6 space-y-6">
          <!-- Partner Name -->
          <div>
            <label for="partner_name" class="block text-sm font-medium text-gray-700 mb-2">
              Partner's Name
            </label>
            <input
              id="partner_name"
              v-model="form.partner_name"
              type="text"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
              placeholder="Enter your partner's name"
              required
            />
            <div v-if="errors.partner_name" class="text-red-600 text-sm mt-1">
              {{ errors.partner_name }}
            </div>
          </div>

          <!-- Wedding Date -->
          <div>
            <label for="wedding_date" class="block text-sm font-medium text-gray-700 mb-2">
              Wedding Date
            </label>
            <input
              id="wedding_date"
              v-model="form.wedding_date"
              type="date"
              :min="minDate"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
              required
            />
            <div v-if="errors.wedding_date" class="text-red-600 text-sm mt-1">
              {{ errors.wedding_date }}
            </div>
          </div>

          <!-- Budget -->
          <div>
            <label for="budget" class="block text-sm font-medium text-gray-700 mb-2">
              Total Budget (Kwacha)
            </label>
            <div class="relative">
              <span class="absolute left-3 top-3 text-gray-500">K</span>
              <input
                id="budget"
                v-model="form.budget"
                type="number"
                min="0"
                step="100"
                class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                placeholder="50000"
                required
              />
            </div>
            <div v-if="errors.budget" class="text-red-600 text-sm mt-1">
              {{ errors.budget }}
            </div>
            <p class="text-gray-500 text-sm mt-1">
              Not sure about your budget? Use our 
              <Link :href="route('wedding.budget-calculator')" class="text-pink-600 hover:text-pink-700">
                budget calculator
              </Link>
            </p>
          </div>

          <!-- Guest Count -->
          <div>
            <label for="guest_count" class="block text-sm font-medium text-gray-700 mb-2">
              Expected Number of Guests
            </label>
            <input
              id="guest_count"
              v-model="form.guest_count"
              type="number"
              min="0"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
              placeholder="100"
              required
            />
            <div v-if="errors.guest_count" class="text-red-600 text-sm mt-1">
              {{ errors.guest_count }}
            </div>
          </div>

          <!-- Wedding Style Preview -->
          <div v-if="form.budget && form.guest_count" class="bg-pink-50 rounded-lg p-4">
            <h3 class="font-semibold text-gray-900 mb-2">Budget Preview</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-600">Cost per guest:</span>
                <span class="font-semibold ml-2">{{ formatCurrency(costPerGuest) }}</span>
              </div>
              <div>
                <span class="text-gray-600">Wedding style:</span>
                <span class="font-semibold ml-2 capitalize">{{ weddingStyle }}</span>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errors.error" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="text-red-800">{{ errors.error }}</div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-between items-center pt-6">
            <Link
              :href="route('wedding.index')"
              class="text-gray-600 hover:text-gray-800 transition-colors"
            >
              ← Back to Dashboard
            </Link>
            
            <button
              type="submit"
              :disabled="processing"
              class="bg-pink-600 text-white px-8 py-3 rounded-lg hover:bg-pink-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
            >
              <span v-if="processing">Creating...</span>
              <span v-else>Create Wedding Event</span>
              <HeartIcon v-if="!processing" class="h-5 w-5 ml-2" aria-hidden="true" />
            </button>
          </div>
        </form>
      </div>

      <!-- Tips -->
      <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h3 class="font-semibold text-gray-900 mb-4">💡 Planning Tips</h3>
        <ul class="space-y-2 text-gray-600">
          <li>• Start planning 12-18 months before your wedding date</li>
          <li>• Allocate 40-50% of your budget for venue and catering</li>
          <li>• Book popular vendors early, especially photographers</li>
          <li>• Consider the season - some months are more expensive</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { HeartIcon } from '@heroicons/vue/24/outline'

const form = useForm({
  partner_name: '',
  wedding_date: '',
  budget: '',
  guest_count: ''
})

const errors = ref({})
const processing = ref(false)

// Minimum date is tomorrow
const minDate = computed(() => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  return tomorrow.toISOString().split('T')[0]
})

const costPerGuest = computed(() => {
  if (!form.budget || !form.guest_count || form.guest_count === 0) return 0
  return parseFloat(form.budget) / parseInt(form.guest_count)
})

const weddingStyle = computed(() => {
  const cost = costPerGuest.value
  if (cost < 800) return 'budget'
  if (cost < 1500) return 'standard'
  if (cost < 2500) return 'premium'
  return 'luxury'
})

const formatCurrency = (amount) => {
  return `K${parseFloat(amount).toLocaleString()}`
}

const submit = () => {
  processing.value = true
  errors.value = {}
  
  form.post(route('wedding.store'), {
    onSuccess: () => {
      // Redirect handled by controller
    },
    onError: (formErrors) => {
      errors.value = formErrors
      processing.value = false
    },
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>