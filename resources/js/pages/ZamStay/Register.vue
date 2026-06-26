<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

const isSubdomain = computed(() => window.location.hostname === 'zamstay.mygrownet.com');
const loginRoute = computed(() => isSubdomain.value ? 'zamstay.sub.login' : 'zamstay.login');
const registerRoute = computed(() => isSubdomain.value ? 'zamstay.sub.register' : 'zamstay.register');
const homeRoute = computed(() => isSubdomain.value ? 'zamstay.sub.home' : 'zamstay.home');

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const currentUrl = encodeURIComponent(window.location.href);

const submit = () => {
  form.post(route(registerRoute.value), {
    onError: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <Head title="Create Account - ZamStay" />

  <div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <div class="text-center mb-8">
          <Link :href="route(homeRoute)" class="text-2xl font-bold text-emerald-600">ZamStay</Link>
          <p class="text-gray-500 text-sm mt-1">Create your ZamStay account</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input id="name" v-model="form.name" type="text" autocomplete="name"
              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
              :class="{ 'border-red-400': form.errors.name }" />
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input id="email" v-model="form.email" type="email" autocomplete="email"
              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
              :class="{ 'border-red-400': form.errors.email }" />
            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input id="password" v-model="form.password" type="password" autocomplete="new-password"
              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
              :class="{ 'border-red-400': form.errors.password }" />
            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password"
              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
              :class="{ 'border-red-400': form.errors.password_confirmation }" />
            <p v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</p>
          </div>

          <button type="submit" :disabled="form.processing"
            class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-colors text-sm disabled:opacity-50">
            {{ form.processing ? 'Creating account...' : 'Create Account' }}
          </button>

          <!-- Google signup -->
          <div class="relative my-4">
            <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-gray-200"></span></div>
            <div class="relative flex justify-center text-xs"><span class="bg-white px-3 text-gray-500">or sign up with</span></div>
          </div>
          <a :href="`/auth/google?redirect=${currentUrl}`" class="flex items-center justify-center gap-3 w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-300 transition-all">
            <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            Sign up with Google
          </a>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
          Already have an account?
          <Link :href="route(loginRoute)" class="text-emerald-600 hover:text-emerald-700 font-medium">Sign in</Link>
        </p>
      </div>
    </div>
  </div>
</template>
