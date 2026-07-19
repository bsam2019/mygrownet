<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-xl mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Change Password</h1>
        <p v-if="forced" class="mt-2 text-sm text-amber-600">
          Your password has expired. Please change it to continue.
        </p>
        <p v-else class="mt-2 text-sm text-gray-600">
          Update your password to keep your account secure
        </p>
      </div>

      <!-- Change Password Form -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Current Password -->
          <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
              Current Password
            </label>
            <input
              id="current_password"
              v-model="form.current_password"
              type="password"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              :class="{ 'border-red-500': form.errors.current_password }"
            />
            <p v-if="form.errors.current_password" class="mt-1 text-sm text-red-600">
              {{ form.errors.current_password }}
            </p>
          </div>

          <!-- New Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              New Password
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              :class="{ 'border-red-500': form.errors.password }"
            />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
              <span v-if="Array.isArray(form.errors.password)">
                <span v-for="(error, index) in form.errors.password" :key="index" class="block">
                  {{ error }}
                </span>
              </span>
              <span v-else>{{ form.errors.password }}</span>
            </p>
            
            <!-- Password Requirements -->
            <div class="mt-2 text-xs text-gray-500 space-y-1">
              <p>Password must contain:</p>
              <ul class="list-disc list-inside pl-2 space-y-0.5">
                <li>At least 8 characters</li>
                <li>One uppercase letter</li>
                <li>One lowercase letter</li>
                <li>One number</li>
                <li>One special character</li>
              </ul>
            </div>
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
              Confirm New Password
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            />
          </div>

          <!-- Error Message -->
          <div v-if="form.errors.error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-600">{{ form.errors.error }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing">Changing Password...</span>
            <span v-else>Change Password</span>
          </button>

          <!-- Cancel Link (only if not forced) -->
          <div v-if="!forced" class="text-center">
            <Link
              :href="route('cms.dashboard')"
              class="text-sm text-gray-600 hover:text-gray-900"
            >
              Cancel and return to dashboard
            </Link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'

interface Props {
  forced?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  forced: false,
})

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('cms.password.update'), {
    preserveScroll: true,
  })
}
</script>
