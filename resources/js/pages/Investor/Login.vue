<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
      <!-- Logo and Header -->
      <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900">Investor Portal</h2>
        <p class="mt-2 text-sm text-gray-600">Access your investment dashboard</p>
      </div>

      <!-- Login Form -->
      <div class="bg-white rounded-lg shadow-xl p-8">
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email Address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="investor@example.com"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <label for="access_code" class="block text-sm font-medium text-gray-700">
              Access Code
            </label>
            <input
              id="access_code"
              v-model="form.access_code"
              type="text"
              required
              autocomplete="off"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter your access code"
            />
            <p v-if="form.errors.access_code" class="mt-1 text-sm text-red-600">
              {{ form.errors.access_code }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
              Your access code was sent to your email when your investment was recorded.
            </p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!form.processing">Sign In</span>
            <span v-else class="flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Signing in...
            </span>
          </button>
        </form>

        <!-- Help Text -->
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            Don't have an access code?
            <a href="mailto:investors@mygrownet.com" class="font-medium text-blue-600 hover:text-blue-500">
              Contact us
            </a>
          </p>
        </div>
      </div>

      <!-- Back to Home -->
      <div class="mt-6 text-center">
        <Link
          :href="route('home')"
          class="text-sm text-gray-600 hover:text-gray-900"
        >
          ← Back to MyGrowNet
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const form = useForm({
  email: '',
  access_code: '',
});

const submit = () => {
  form.post(route('investor.login'));
};
</script>
