<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import PortalLayout from '@/Layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post(route('portal.store-login'), {
    onError: () => form.reset('password'),
  })
}
</script>

<template>
  <div class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <span class="text-white font-bold text-2xl">P</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Customer Portal</h1>
        <p class="text-gray-500 mt-1">Sign in to view your invoices, quotes, and payments</p>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input v-model="form.email" type="email" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500" placeholder="you@example.com" />
          <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input v-model="form.password" type="password" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500" placeholder="••••••••" />
          <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
        </div>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <input v-model="form.remember" type="checkbox" id="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
            <label for="remember" class="text-sm text-gray-600">Remember me</label>
          </div>
          <Link :href="route('portal.password.request')" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Forgot password?</Link>
        </div>
        <button type="submit" :disabled="form.processing" class="w-full py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50">
          {{ form.processing ? 'Signing in...' : 'Sign In' }}
        </button>
        <p class="text-center text-sm text-gray-500">
          Don't have an account?
          <Link :href="route('portal.register')" class="text-blue-600 hover:text-blue-700 font-medium">Register</Link>
        </p>
      </form>
    </div>
  </div>
</template>
