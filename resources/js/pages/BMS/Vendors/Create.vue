<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({ layout: CMSLayout })

const form = useForm({
  name: '', email: '', phone: '', contact_person: '',
  address: '', city: '', country: '',
  tax_number: '', registration_number: '', vendor_type: '',
  payment_terms_days: 30, payment_method: '',
  bank_name: '', bank_account_number: '', mobile_money_number: '',
  notes: '',
})

const submit = () => form.post(route('cms.vendors.store'))
</script>

<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Link :href="route('cms.vendors.index')" class="text-sm text-blue-600 hover:text-blue-700 mb-4 inline-flex items-center gap-1">
      <ArrowLeftIcon class="h-4 w-4" /> Back to Vendors
    </Link>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">New Vendor</h1>

    <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Vendor Name</label>
          <input v-model="form.name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
        </div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input v-model="form.email" type="email" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label><input v-model="form.phone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label><input v-model="form.contact_person" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Vendor Type</label><input v-model="form.vendor_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Address</label><textarea v-model="form.address" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">City</label><input v-model="form.city" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Country</label><input v-model="form.country" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Tax Number</label><input v-model="form.tax_number" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Reg Number</label><input v-model="form.registration_number" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Payment Terms (days)</label><input v-model.number="form.payment_terms_days" type="number" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label><input v-model="form.payment_method" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label><input v-model="form.bank_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Bank Account</label><input v-model="form.bank_account_number" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Mobile Money</label><input v-model="form.mobile_money_number" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" /></div>
        <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label><textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea></div>
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <Link :href="route('cms.vendors.index')" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">Cancel</Link>
        <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">{{ form.processing ? 'Creating...' : 'Create Vendor' }}</button>
      </div>
    </form>
  </div>
</template>
