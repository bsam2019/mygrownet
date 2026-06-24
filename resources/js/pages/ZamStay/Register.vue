<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('zamstay.register'), {
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
          <Link :href="route('zamstay.home')" class="text-2xl font-bold text-emerald-600">ZamStay</Link>
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
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
          Already have an account?
          <Link :href="route('zamstay.login')" class="text-emerald-600 hover:text-emerald-700 font-medium">Sign in</Link>
        </p>
      </div>
    </div>
  </div>
</template>
