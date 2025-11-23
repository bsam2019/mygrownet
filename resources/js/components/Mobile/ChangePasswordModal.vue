<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center"
    @click="$emit('close')"
  >
    <div
      class="bg-white rounded-t-3xl sm:rounded-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Header -->
      <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-4 rounded-t-3xl sm:rounded-t-2xl">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-bold">Change Password</h3>
              <p class="text-xs text-blue-100">Update your account password</p>
            </div>
          </div>
          <button
            @click="$emit('close')"
            class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center hover:bg-white/30 transition-colors"
          >
            <span class="text-xl">×</span>
          </button>
        </div>
      </div>

      <!-- Form Content -->
      <form @submit.prevent="updatePassword" class="p-6 space-y-6">
        <!-- Current Password -->
        <div>
          <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
            Current Password
          </label>
          <div class="relative">
            <input
              id="current_password"
              ref="currentPasswordInput"
              v-model="form.current_password"
              :type="showCurrentPassword ? 'text' : 'password'"
              class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter current password"
              autocomplete="current-password"
            />
            <button
              type="button"
              @click="showCurrentPassword = !showCurrentPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-gray-600 transition-colors"
              tabindex="-1"
            >
              <svg v-if="!showCurrentPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
              </svg>
            </button>
          </div>
          <p v-if="form.errors.current_password" class="mt-1 text-sm text-red-600">
            {{ form.errors.current_password }}
          </p>
        </div>

        <!-- New Password -->
        <div>
          <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
            New Password
          </label>
          <div class="relative">
            <input
              id="new_password"
              ref="passwordInput"
              v-model="form.password"
              :type="showNewPassword ? 'text' : 'password'"
              class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter new password"
              autocomplete="new-password"
            />
            <button
              type="button"
              @click="showNewPassword = !showNewPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-gray-600 transition-colors"
              tabindex="-1"
            >
              <svg v-if="!showNewPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
              </svg>
            </button>
          </div>
          <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
            {{ form.errors.password }}
          </p>
          <p class="mt-1 text-xs text-gray-500">
            Must be at least 8 characters long
          </p>
        </div>

        <!-- Confirm Password -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
            Confirm New Password
          </label>
          <div class="relative">
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              :type="showConfirmPassword ? 'text' : 'password'"
              class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Confirm new password"
              autocomplete="new-password"
            />
            <button
              type="button"
              @click="showConfirmPassword = !showConfirmPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-gray-600 transition-colors"
              tabindex="-1"
            >
              <svg v-if="!showConfirmPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
              </svg>
            </button>
          </div>
          <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">
            {{ form.errors.password_confirmation }}
          </p>
        </div>

        <!-- Success Message -->
        <div
          v-if="form.recentlySuccessful"
          class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3"
        >
          <svg class="h-5 w-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <p class="text-sm text-green-800 font-medium">Password updated successfully!</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3">
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!form.processing">Update Password</span>
            <span v-else class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Updating...
            </span>
          </button>
        </div>

        <!-- Security Tip -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
          <div class="flex gap-3">
            <svg class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="text-sm font-medium text-blue-900 mb-1">Security Tips</p>
              <ul class="text-xs text-blue-800 space-y-1">
                <li>• Use a unique password you don't use elsewhere</li>
                <li>• Include uppercase, lowercase, numbers, and symbols</li>
                <li>• Avoid personal information like birthdays</li>
              </ul>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

defineProps<{
  show: boolean;
}>();

const emit = defineEmits<{
  close: [];
  success: [message: string];
  error: [message: string];
}>();

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

// Show/hide password toggles
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updatePassword = () => {
  form.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      emit('success', 'Password updated successfully!');
      // Auto-close after 2 seconds
      setTimeout(() => {
        emit('close');
      }, 2000);
    },
    onError: (errors: any) => {
      if (errors.password) {
        form.reset('password', 'password_confirmation');
        if (passwordInput.value instanceof HTMLInputElement) {
          passwordInput.value.focus();
        }
      }

      if (errors.current_password) {
        form.reset('current_password');
        if (currentPasswordInput.value instanceof HTMLInputElement) {
          currentPasswordInput.value.focus();
        }
      }
      
      emit('error', 'Failed to update password. Please check your inputs.');
    },
  });
};
</script>
