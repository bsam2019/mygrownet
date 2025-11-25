<template>
  <Head>
    <title>Wedding Admin Access - Kaoma & Mubanga</title>
    <meta name="description" content="Private admin access for managing wedding RSVPs and guest list for Kaoma & Mubanga's wedding celebration." />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Wedding Admin - Kaoma & Mubanga" />
    <meta property="og:description" content="Private admin access for managing wedding RSVPs and guest list." />
    <meta property="og:image" content="/images/wedding/OG.jpg" />
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Wedding Admin - Kaoma & Mubanga" />
    <meta name="twitter:description" content="Private admin access for managing wedding RSVPs and guest list." />
    <meta name="twitter:image" content="/images/wedding/OG.jpg" />
  </Head>

  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white flex items-center justify-center px-4">
    <div class="max-w-md w-full">
      <!-- Logo/Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
          <LockClosedIcon class="w-8 h-8 text-gray-500" aria-hidden="true" />
        </div>
        <h1 class="text-2xl font-light text-gray-700 tracking-[0.1em] font-serif">
          Wedding Admin
        </h1>
        <p class="text-sm text-gray-500 mt-2">
          Enter your access code to manage RSVPs
        </p>
      </div>

      <!-- Access Form -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <form @submit.prevent="verifyAccess">
          <div class="space-y-6">
            <div>
              <label for="access_code" class="block text-sm font-medium text-gray-700 mb-2">
                Access Code
              </label>
              <input
                id="access_code"
                v-model="form.access_code"
                type="password"
                required
                placeholder="Enter your access code"
                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-400 focus:border-gray-400 text-center tracking-[0.2em] uppercase"
                :class="{ 'border-red-500': errors.access_code }"
              />
              <p v-if="errors.access_code" class="mt-2 text-sm text-red-600">
                {{ errors.access_code }}
              </p>
            </div>

            <button
              type="submit"
              :disabled="isLoading || !form.access_code"
              class="w-full bg-gray-600 hover:bg-gray-700 disabled:bg-gray-300 text-white py-3 px-6 rounded-md text-sm font-medium tracking-[0.1em] transition-colors"
            >
              <span v-if="isLoading" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                VERIFYING...
              </span>
              <span v-else>ACCESS DASHBOARD</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Back Link -->
      <div class="text-center mt-6">
        <a 
          :href="`/${slug}`" 
          class="text-sm text-gray-500 hover:text-gray-700 transition-colors"
        >
          ← Back to Wedding Website
        </a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import { LockClosedIcon } from '@heroicons/vue/24/outline'

const props = defineProps<{
  slug: string
}>()

const form = reactive({
  access_code: ''
})

const errors = reactive<Record<string, string>>({})
const isLoading = ref(false)

const verifyAccess = () => {
  isLoading.value = true
  errors.access_code = ''

  router.post(`/wedding-admin/${props.slug}/verify`, form, {
    onError: (err) => {
      errors.access_code = err.access_code || 'Invalid access code'
    },
    onFinish: () => {
      isLoading.value = false
    }
  })
}
</script>
