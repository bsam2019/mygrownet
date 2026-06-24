<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('zamstay.login'), {
    onError: () => form.reset('password'),
  });
};
</script>

<template>
  <Head title="Sign In - ZamStay" />

  <div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <div class="text-center mb-8">
          <Link :href="route('zamstay.home')" class="text-2xl font-bold text-emerald-600">ZamStay</Link>
          <p class="text-gray-500 text-sm mt-1">Sign in to your account</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input id="email" v-model="form.email" type="email" autocomplete="email"
              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
              :class="{ 'border-red-400': form.errors.email }" />
            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input id="password" v-model="form.password" type="password" autocomplete="current-password"
              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
              :class="{ 'border-red-400': form.errors.password }" />
            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
              <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
              Remember me
            </label>
          </div>

          <button type="submit" :disabled="form.processing"
            class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-colors text-sm disabled:opacity-50">
            {{ form.processing ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
          Don't have an account?
          <Link :href="route('zamstay.register')" class="text-emerald-600 hover:text-emerald-700 font-medium">Create one</Link>
        </p>
      </div>
    </div>
  </div>
</template>
