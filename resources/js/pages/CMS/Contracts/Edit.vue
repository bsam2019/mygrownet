<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({ layout: CMSLayout })

interface Props {
  contract: any
  customers: Array<{ id: number; name: string }>
  templates: Array<{ id: number; name: string }>
}

const props = defineProps<Props>()

const form = useForm({
  title: props.contract.title || '',
  customer_id: props.contract.customer_id || '',
  template_id: props.contract.template_id || '',
  description: props.contract.description || '',
  start_date: props.contract.start_date || '',
  end_date: props.contract.end_date || '',
  total_value: props.contract.total_value || 0,
  currency: props.contract.currency || 'ZMW',
  status: props.contract.status || 'draft',
  terms: props.contract.terms || '',
  notes: props.contract.notes || '',
})

const submit = () => {
  form.put(route('cms.contracts.update', props.contract.id))
}
</script>

<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Link :href="route('cms.contracts.show', contract.id)" class="text-sm text-blue-600 hover:text-blue-700 mb-4 inline-flex items-center gap-1">
      <ArrowLeftIcon class="h-4 w-4" /> Back to Contract
    </Link>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Contract</h1>

    <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
          <input v-model="form.title" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
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
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input v-model="form.end_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Total Value</label>
          <input v-model.number="form.total_value" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
          <input v-model="form.currency" maxlength="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm uppercase" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="form.status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="draft">Draft</option>
            <option value="active">Active</option>
            <option value="expired">Expired</option>
            <option value="terminated">Terminated</option>
          </select>
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
        <Link :href="route('cms.contracts.show', contract.id)" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">Cancel</Link>
        <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
          {{ form.processing ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </form>
  </div>
</template>
