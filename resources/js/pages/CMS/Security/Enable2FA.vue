<template>
  <CMSLayoutNew title="Enable Two-Factor Authentication">
    <div class="max-w-2xl mx-auto">
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">
          Enable Two-Factor Authentication
        </h2>
        <p class="text-sm text-gray-600 mb-6">
          Add an extra layer of security to your account by enabling two-factor authentication.
        </p>

        <!-- Step 1: Scan QR Code -->
        <div class="mb-8">
          <h3 class="text-lg font-medium text-gray-900 mb-3">
            Step 1: Scan QR Code
          </h3>
          <p class="text-sm text-gray-600 mb-4">
            Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)
          </p>
          
          <div class="flex justify-center p-6 bg-gray-50 rounded-lg">
            <div class="text-center">
              <div class="inline-block p-4 bg-white rounded-lg shadow">
                <img
                  :src="`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrCode)}`"
                  alt="QR Code"
                  class="w-48 h-48"
                />
              </div>
              <p class="mt-4 text-sm text-gray-500">
                Can't scan? Use this code: <code class="px-2 py-1 bg-gray-100 rounded">{{ secret }}</code>
              </p>
            </div>
          </div>
        </div>

        <!-- Step 2: Verify Code -->
        <div class="mb-8">
          <h3 class="text-lg font-medium text-gray-900 mb-3">
            Step 2: Verify Code
          </h3>
          <p class="text-sm text-gray-600 mb-4">
            Enter the 6-digit code from your authenticator app to verify setup
          </p>

          <form @submit.prevent="verify">
            <div class="max-w-xs">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Verification Code
              </label>
              <input
                v-model="form.code"
                type="text"
                maxlength="6"
                pattern="[0-9]{6}"
                placeholder="000000"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-center text-2xl tracking-widest"
                required
              />
            </div>

            <div class="mt-6 flex gap-3">
              <button
                type="button"
                @click="cancel"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="processing || form.code.length !== 6"
                class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
              >
                {{ processing ? 'Verifying...' : 'Verify & Enable' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Security Notice -->
        <div class="p-4 bg-blue-50 rounded-lg">
          <div class="flex">
            <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
              <h4 class="text-sm font-medium text-blue-800">Important</h4>
              <p class="mt-1 text-sm text-blue-700">
                Save your backup codes in a safe place. You'll need them if you lose access to your authenticator app.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CMSLayoutNew>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue';

const props = defineProps<{
  secret: string;
  qrCode: string;
}>();

const form = reactive({
  code: '',
  secret: props.secret,
});

const processing = ref(false);

const verify = () => {
  processing.value = true;
  router.post(route('cms.security.2fa.verify'), form, {
    onFinish: () => {
      processing.value = false;
    },
  });
};

const cancel = () => {
  router.visit(route('cms.dashboard'));
};
</script>
