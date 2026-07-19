<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { PencilIcon } from '@heroicons/vue/24/outline'
import BMSLayout from '@/Layouts/BMSLayout.vue'

defineOptions({ layout: BMSLayout })

interface Props {
  vendor: any
}

defineProps<Props>()

const formatDate = (date: string) => date ? new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-'
const formatMoney = (amount: number) => 'K' + (amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Link :href="route('bms.vendors.index')" class="text-sm text-blue-600 hover:text-blue-700 mb-4 inline-flex items-center gap-1">← Back to Vendors</Link>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ vendor.name }}</h1>
          <p class="text-sm text-gray-500">{{ vendor.vendor_number }}</p>
        </div>
        <div class="flex items-center gap-2">
          <span :class="['px-3 py-1 text-sm font-medium rounded-full', vendor.status === 'active' ? 'bg-green-100 text-green-700' : vendor.status === 'blacklisted' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500']">{{ vendor.status }}</span>
          <Link :href="route('bms.vendors.edit', vendor.id)" class="p-2 text-gray-400 hover:text-gray-600"><PencilIcon class="h-5 w-5" /></Link>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4 text-sm">
        <div><label class="text-gray-500 font-medium">Email</label><p class="text-gray-900">{{ vendor.email || '-' }}</p></div>
        <div><label class="text-gray-500 font-medium">Phone</label><p class="text-gray-900">{{ vendor.phone || '-' }}</p></div>
        <div><label class="text-gray-500 font-medium">Contact Person</label><p class="text-gray-900">{{ vendor.contact_person || '-' }}</p></div>
        <div><label class="text-gray-500 font-medium">Vendor Type</label><p class="text-gray-900">{{ vendor.vendor_type || '-' }}</p></div>
        <div class="col-span-2" v-if="vendor.address"><label class="text-gray-500 font-medium">Address</label><p class="text-gray-900">{{ vendor.address }}{{ vendor.city ? ', ' + vendor.city : '' }}{{ vendor.country ? ', ' + vendor.country : '' }}</p></div>
        <div><label class="text-gray-500 font-medium">Tax Number</label><p class="text-gray-900">{{ vendor.tax_number || '-' }}</p></div>
        <div><label class="text-gray-500 font-medium">Registration</label><p class="text-gray-900">{{ vendor.registration_number || '-' }}</p></div>
        <div><label class="text-gray-500 font-medium">Payment Terms</label><p class="text-gray-900">{{ vendor.payment_terms_days ? vendor.payment_terms_days + ' days' : '-' }}</p></div>
        <div><label class="text-gray-500 font-medium">Payment Method</label><p class="text-gray-900">{{ vendor.payment_method || '-' }}</p></div>
        <div v-if="vendor.bank_name"><label class="text-gray-500 font-medium">Bank</label><p class="text-gray-900">{{ vendor.bank_name }} - {{ vendor.bank_account_number }}</p></div>
        <div v-if="vendor.mobile_money_number"><label class="text-gray-500 font-medium">Mobile Money</label><p class="text-gray-900">{{ vendor.mobile_money_number }}</p></div>
        <div><label class="text-gray-500 font-medium">Total Orders</label><p class="text-gray-900">{{ vendor.total_orders || 0 }}</p></div>
        <div><label class="text-gray-500 font-medium">Total Spent</label><p class="text-gray-900 font-semibold">{{ formatMoney(vendor.total_spent) }}</p></div>
      </div>
    </div>

    <div v-if="vendor.notes" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <h2 class="text-sm font-semibold text-gray-900 mb-2">Notes</h2>
      <p class="text-sm text-gray-700">{{ vendor.notes }}</p>
    </div>

    <div v-if="vendor.purchase_orders?.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-sm font-semibold text-gray-900 mb-3">Recent Purchase Orders</h2>
      <div class="space-y-2">
        <div v-for="po in vendor.purchase_orders" :key="po.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
          <div>
            <p class="text-sm font-medium text-gray-900">{{ po.po_number }}</p>
            <p class="text-xs text-gray-500">{{ formatDate(po.po_date) }} - {{ po.status }}</p>
          </div>
          <p class="text-sm font-semibold text-gray-900">{{ formatMoney(po.total_amount) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
