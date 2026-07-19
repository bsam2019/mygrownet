<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({ layout: CMSLayout })

interface Props {
  customers: Array<{ id: number; name: string }>
  templates: Array<{ id: number; name: string }>
}

defineProps<Props>()

const form = useForm({
  title: '',
  customer_id: '',
  template_id: '',
  description: '',
  start_date: '',
  end_date: '',
  total_value: 0,
  currency: 'ZMW',
  terms: '',
  notes: '',
})

const submit = () => {
  form.post(route('cms.contracts.store'))
}
</script>

<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Link :href="route('cms.contracts.index')" class="text-sm text-blue-600 hover:text-blue-700 mb-4 inline-flex items-center gap-1">
      <ArrowLeftIcon class="h-4 w-4" /> Back to Contracts
    </Link>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">New Contract</h1>

    <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
          <input v-model="form.title" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
          <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
          <select v-model="form.customer_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">Select customer</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Template</label>
          <select v-model="form.template_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">No template</option>
            <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input v-model="form.start_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
          <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input v-model="form.end_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Total Value</label>
          <input v-model.number="form.total_value" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
          <p v-if="form.errors.total_value" class="mt-1 text-sm text-red-600">{{ form.errors.total_value }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
          <input v-model="form.currency" maxlength="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm uppercase" />
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea v-model="form.description" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Terms & Conditions</label>
          <textarea v-model="form.terms" rows="5" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono"></textarea>
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
          <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <Link :href="route('cms.contracts.index')" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">Cancel</Link>
        <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
          {{ form.processing ? 'Creating...' : 'Create Contract' }}
        </button>
      </div>
    </form>
  </div>
</template>
