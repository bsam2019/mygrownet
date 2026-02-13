<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4 shadow-lg">
          <BuildingOfficeIcon class="h-10 w-10 text-white" aria-hidden="true" />
        </div>
        <h2 class="text-3xl font-bold text-gray-900">Welcome back</h2>
        <p class="mt-2 text-sm text-gray-600">Sign in to your business dashboard</p>
      </div>

      <!-- Login Form -->
      <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        <form @submit.prevent="submit" class="space-y-5">
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-900 mb-2">
              Email Address *
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              :class="{ 'border-red-300 bg-red-50': form.errors.email }"
              placeholder="you@company.com"
            />
            <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-900 mb-2">
              Password *
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              required
              class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              :class="{ 'border-red-300 bg-red-50': form.errors.password }"
              placeholder="••••••••"
            />
            <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember"
                v-model="form.remember"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <label for="remember" class="ml-2 block text-sm text-gray-700">
                Remember me
              </label>
            </div>

            <div class="text-sm">
              <a href="#" class="font-semibold text-blue-600 hover:text-blue-700">
                Forgot password?
              </a>
            </div>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-blue-600 text-white py-3.5 px-4 rounded-lg font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg hover:shadow-xl"
          >
            <span v-if="!form.processing">Sign In</span>
            <span v-else class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Signing in...
            </span>
          </button>

          <!-- Sign Up Link -->
          <div class="text-center pt-4 border-t border-gray-100">
            <p class="text-sm text-gray-600">
              Don't have an account?
              <Link :href="route('cms.register')" class="text-blue-600 hover:text-blue-700 font-semibold">
                Sign up for free
              </Link>
            </p>
          </div>
        </form>
      </div>

      <!-- Back to Home -->
      <div class="text-center mt-6">
        <Link :href="route('cms.landing')" class="text-sm text-gray-600 hover:text-gray-900 font-medium">
          ← Back to home
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { BuildingOfficeIcon } from '@heroicons/vue/24/outline'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post(route('cms.login'), {
    onFinish: () => {
      form.password = ''
    },
  })
}
</script>
