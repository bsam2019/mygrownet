<template>
  <Modal :show="show" max-width="sm" @close="close" :closeable="true">
    <div class="p-5">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-base font-bold text-gray-900">{{ tab === 'login' ? 'Sign In' : 'Create Account' }}</h2>
        <button @click="close" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="flex mb-3 bg-gray-100 rounded-lg p-1">
        <button @click="tab = 'login'" :class="['flex-1 py-1.5 text-sm font-medium rounded-md transition-all', tab === 'login' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700']">Sign In</button>
        <button @click="tab = 'register'" :class="['flex-1 py-1.5 text-sm font-medium rounded-md transition-all', tab === 'register' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700']">Register</button>
      </div>

      <div v-if="errorMsg" class="mb-2 p-2 bg-red-50 border border-red-200 rounded text-sm text-red-700">{{ errorMsg }}</div>
      <div v-if="successMsg" class="mb-2 p-2 bg-green-50 border border-green-200 rounded text-sm text-green-700">{{ successMsg }}</div>

      <form v-if="tab === 'login'" @submit.prevent="submitLogin" class="space-y-2">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email or Phone</label>
          <input v-model="loginForm.email" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-1.5 border" placeholder="email@example.com" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input v-model="loginForm.password" type="password" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-1.5 border" placeholder="Enter password" required>
        </div>
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-1.5">
            <input v-model="loginForm.remember" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-gray-600">Remember me</span>
          </label>
          <a :href="route('password.request')" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
        </div>
        <button type="submit" :disabled="loginLoading" class="w-full py-1.5 px-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg font-medium text-sm hover:from-blue-600 hover:to-blue-700 disabled:opacity-50 transition-all">
          {{ loginLoading ? 'Signing in...' : 'Sign In' }}
        </button>
      </form>

      <form v-else @submit.prevent="submitRegister" class="space-y-2">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
          <input v-model="registerForm.name" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-1.5 border" placeholder="Your name" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email or Phone</label>
          <input v-model="registerForm.identifier" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-1.5 border" placeholder="Email or Phone (e.g. 0972..)" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input v-model="registerForm.password" type="password" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-1.5 border" placeholder="Create a password" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
          <input v-model="registerForm.password_confirmation" type="password" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-1.5 border" placeholder="Confirm password" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Referral Code <span class="text-gray-400 font-normal">(optional)</span></label>
          <input v-model="registerForm.referral_code" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-1.5 border" placeholder="Enter referral code">
        </div>
        <button type="submit" :disabled="registerLoading" class="w-full py-1.5 px-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg font-medium text-sm hover:from-blue-600 hover:to-blue-700 disabled:opacity-50 transition-all">
          {{ registerLoading ? 'Creating account...' : 'Create Account' }}
        </button>
      </form>

      <div class="mt-3 text-center">
        <div class="flex items-center gap-2 mb-3">
          <span class="flex-1 h-px bg-gray-200"></span>
          <span class="text-xs text-gray-400">or continue with</span>
          <span class="flex-1 h-px bg-gray-200"></span>
        </div>
        <a :href="route('auth.google')" class="flex items-center justify-center gap-2 w-full py-1.5 px-3 border-2 border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all">
          <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Google
        </a>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from './Modal.vue';

const props = defineProps({ show: Boolean });
const emit = defineEmits(['close']);

const tab = ref('login');
const loginLoading = ref(false);
const registerLoading = ref(false);
const errorMsg = ref('');
const successMsg = ref('');

const loginForm = ref({ email: '', password: '', remember: false });
const registerForm = ref({ name: '', identifier: '', password: '', password_confirmation: '', referral_code: '' });

watch(() => props.show, (val) => {
  if (val) {
    tab.value = 'login';
    errorMsg.value = '';
    successMsg.value = '';
    loginForm.value = { email: '', password: '', remember: false };
    registerForm.value = { name: '', identifier: '', password: '', password_confirmation: '', referral_code: '' };
  }
});

const close = () => emit('close');

const submitLogin = async () => {
  loginLoading.value = true;
  errorMsg.value = '';
  
  const loginRoute = route('login');
  console.log('[LoginModal] Submitting login to:', loginRoute);
  console.log('[LoginModal] Form data:', { email: loginForm.value.email, remember: loginForm.value.remember });
  
  router.post(loginRoute, loginForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('[LoginModal] Login successful');
      close();
      // Force full page reload to refresh auth state
      window.location.href = route('dashboard');
    },
    onError: (errors) => {
      console.error('[LoginModal] Login failed:', errors);
      errorMsg.value = Object.values(errors).flat().join(', ');
      loginLoading.value = false;
    },
    onFinish: () => { 
      console.log('[LoginModal] Login request finished');
      loginLoading.value = false; 
    },
  });
};

const submitRegister = async () => {
  registerLoading.value = true;
  errorMsg.value = '';
  router.post(route('register'), registerForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      successMsg.value = 'Account created! Redirecting...';
      setTimeout(() => {
        close();
        // Force full page reload to refresh auth state
        window.location.href = route('dashboard');
      }, 1500);
    },
    onError: (errors) => {
      errorMsg.value = Object.values(errors).flat().join(', ');
      registerLoading.value = false;
    },
    onFinish: () => { registerLoading.value = false; },
  });
};
</script>
