<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import PortalLayout from '@/Layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

interface Props {
  email: string
  token: string
}

const props = defineProps<Props>()

const form = useForm({
  email: props.email || '',
  token: props.token,
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('portal.password.update'), {
    onError: () => form.reset('password', 'password_confirmation'),
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
        <h1 class="text-2xl font-bold text-gray-900">Reset Password</h1>
        <p class="text-gray-500 mt-1">Enter your new password</p>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input v-model="form.email" type="email" readonly class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
          <input v-model="form.password" type="password" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500" placeholder="••••••••" />
          <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
          <input v-model="form.password_confirmation" type="password" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500" placeholder="••••••••" />
        </div>
        <button type="submit" :disabled="form.processing" class="w-full py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50">
          {{ form.processing ? 'Resetting...' : 'Reset Password' }}
        </button>
        <p class="text-center text-sm text-gray-500">
          <Link :href="route('portal.login')" class="text-blue-600 hover:text-blue-700 font-medium">Back to sign in</Link>
        </p>
      </form>
    </div>
  </div>
</template>
