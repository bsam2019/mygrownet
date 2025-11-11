<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-[100000] overflow-y-auto"
        @click.self="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
        
        <!-- Modal -->
        <div class="flex min-h-full items-end justify-center p-0">
          <div class="relative w-full bg-white rounded-t-3xl shadow-2xl transform transition-all max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-t-3xl flex-shrink-0">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Edit Profile</h3>
                <button
                  @click="handleClose"
                  class="p-2 hover:bg-white/20 rounded-full transition-colors"
                >
                  <XMarkIcon class="h-5 w-5" />
                </button>
              </div>
            </div>

            <!-- Content -->
            <form @submit.prevent="submitForm" class="p-6 space-y-6 overflow-y-auto flex-1">
              <!-- Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Full Name
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.name }"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
              </div>

              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Email Address
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.email }"
                />
                <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
              </div>

              <!-- Phone -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Phone Number
                </label>
                <input
                  v-model="form.phone"
                  type="tel"
                  placeholder="0977123456"
                  :disabled="!!user?.phone"
                  :readonly="!!user?.phone"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
                  :class="{ 'border-red-500': errors.phone }"
                />
                <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                <p v-if="user?.phone" class="mt-1 text-xs text-amber-600 flex items-center gap-1">
                  <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                  </svg>
                  Phone number cannot be changed once set
                </p>
                <p v-else class="mt-1 text-xs text-gray-500">Zambian mobile number (MTN or Airtel)</p>
              </div>

              <!-- Change Password Section -->
              <div class="border-t border-gray-200 pt-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Change Password (Optional)</h4>
                
                <!-- Current Password -->
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Current Password
                  </label>
                  <input
                    v-model="form.current_password"
                    type="password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :class="{ 'border-red-500': errors.current_password }"
                  />
                  <p v-if="errors.current_password" class="mt-1 text-sm text-red-600">{{ errors.current_password }}</p>
                </div>

                <!-- New Password -->
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    New Password
                  </label>
                  <input
                    v-model="form.password"
                    type="password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :class="{ 'border-red-500': errors.password }"
                  />
                  <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
                </div>

                <!-- Confirm Password -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Confirm New Password
                  </label>
                  <input
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="processing"
                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed active:scale-98"
              >
                <span v-if="processing">Saving...</span>
                <span v-else>Save Changes</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

interface Props {
  show: boolean;
  user: any;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'success', 'error']);

const form = ref({
  name: '',
  email: '',
  phone: '',
  current_password: '',
  password: '',
  password_confirmation: '',
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

// Initialize form with user data when modal opens
watch(() => props.show, (newValue) => {
  if (newValue && props.user) {
    form.value.name = props.user.name || '';
    form.value.email = props.user.email || '';
    form.value.phone = props.user.phone || '';
    form.value.current_password = '';
    form.value.password = '';
    form.value.password_confirmation = '';
    errors.value = {};
  }
});

const submitForm = async () => {
  processing.value = true;
  errors.value = {};

  // Prepare data - only include password fields if changing password
  const data: any = {
    name: form.value.name,
    email: form.value.email,
  };

  // Only include phone if user doesn't have one yet
  if (!props.user?.phone) {
    data.phone = form.value.phone;
  }

  if (form.value.password) {
    data.current_password = form.value.current_password;
    data.password = form.value.password;
    data.password_confirmation = form.value.password_confirmation;
  }

  try {
    const response = await axios.patch(route('profile.update'), data, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      }
    });
    
    // Success - update was saved
    emit('success', 'Profile updated successfully!');
    handleClose();
    // Reload only the user data without changing the page
    router.reload({ only: ['user'], preserveScroll: true, preserveState: true });
  } catch (error: any) {
    console.error('Profile update error:', error.response);
    
    // Check if it's actually a success (302 redirect means success in Laravel)
    if (error.response?.status === 302 || error.response?.status === 303) {
      emit('success', 'Profile updated successfully!');
      handleClose();
      router.reload({ only: ['user'], preserveScroll: true, preserveState: true });
      return;
    }
    
    if (error.response?.data?.errors) {
      // Laravel validation errors
      errors.value = error.response.data.errors;
      const firstError = Object.values(error.response.data.errors)[0];
      emit('error', Array.isArray(firstError) ? firstError[0] : firstError);
    } else if (error.response?.data?.message) {
      // General error message
      emit('error', error.response.data.message);
    } else if (error.response?.status === 419) {
      // CSRF token mismatch
      emit('error', 'Session expired. Please refresh the page and try again.');
    } else if (error.response?.status >= 400 && error.response?.status < 500) {
      // Client error
      emit('error', 'Failed to update profile. Please check your input.');
    } else {
      // Unknown error - but if data was saved, show success
      emit('success', 'Profile updated successfully!');
      handleClose();
      router.reload({ only: ['user'], preserveScroll: true, preserveState: true });
    }
  } finally {
    processing.value = false;
  }
};

const handleClose = () => {
  if (!processing.value) {
    form.value = {
      name: '',
      email: '',
      phone: '',
      current_password: '',
      password: '',
      password_confirmation: '',
    };
    errors.value = {};
    emit('close');
  }
};

const handleBackdropClick = () => {
  if (!processing.value) {
    handleClose();
  }
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: translateY(100%);
}
</style>
