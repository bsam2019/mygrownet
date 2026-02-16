<template>
  <CMSLayout title="Security Settings">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-semibold text-gray-900">Security Settings</h2>
        <p class="mt-1 text-sm text-gray-600">
          Configure security policies for your company
        </p>
      </div>

      <!-- Password Policy -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Password Policy</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Password Expiry (days)
            </label>
            <input
              v-model.number="form.password_expiry_days"
              type="number"
              min="30"
              max="365"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
            <p class="mt-1 text-sm text-gray-500">
              Users must change password every {{ form.password_expiry_days }} days
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              Minimum Password Length
            </label>
            <input
              v-model.number="form.password_min_length"
              type="number"
              min="8"
              max="32"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>
      </div>

      <!-- Account Lockout -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Account Lockout</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Max Login Attempts
            </label>
            <input
              v-model.number="form.max_login_attempts"
              type="number"
              min="3"
              max="10"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
            <p class="mt-1 text-sm text-gray-500">
              Account locks after {{ form.max_login_attempts }} failed attempts
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              Lockout Duration (minutes)
            </label>
            <input
              v-model.number="form.lockout_duration_minutes"
              type="number"
              min="15"
              max="120"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>
      </div>

      <!-- Session Management -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Session Management</h3>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Session Timeout (minutes)
          </label>
          <input
            v-model.number="form.session_timeout_minutes"
            type="number"
            min="30"
            max="480"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
          <p class="mt-1 text-sm text-gray-500">
            Users auto-logout after {{ form.session_timeout_minutes }} minutes of inactivity
          </p>
        </div>
      </div>

      <!-- Two-Factor Authentication -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Two-Factor Authentication</h3>
        
        <div class="flex items-center">
          <input
            v-model="form.require_2fa"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label class="ml-2 block text-sm text-gray-900">
            Require 2FA for all users
          </label>
        </div>
        <p class="mt-1 text-sm text-gray-500">
          When enabled, all users must set up two-factor authentication
        </p>
      </div>

      <!-- Security Alerts -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Security Alerts</h3>
        
        <div class="flex items-center">
          <input
            v-model="form.enable_security_alerts"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label class="ml-2 block text-sm text-gray-900">
            Enable email alerts for suspicious activity
          </label>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3">
        <button
          type="button"
          @click="resetForm"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          Reset
        </button>
        <button
          type="button"
          @click="saveSettings"
          :disabled="processing"
          class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
        >
          {{ processing ? 'Saving...' : 'Save Settings' }}
        </button>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

const props = defineProps<{
  settings: {
    password_expiry_days: number;
    max_login_attempts: number;
    lockout_duration_minutes: number;
    session_timeout_minutes: number;
    require_2fa: boolean;
    password_min_length: number;
    enable_security_alerts: boolean;
  };
}>();

const form = reactive({ ...props.settings });
const processing = ref(false);

const resetForm = () => {
  Object.assign(form, props.settings);
};

const saveSettings = () => {
  processing.value = true;
  router.post(route('cms.security.settings.update'), form, {
    onFinish: () => {
      processing.value = false;
    },
  });
};
</script>
