<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { CheckIcon, BanknotesIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface Props {
  payrollRun: any
}

const props = defineProps<Props>()

const showMarkPaidModal = ref(false)
const markPaidForm = useForm({
  paid_date: new Date().toISOString().split('T')[0],
})

const approvePayroll = () => {
  useForm({}).post(route('cms.payroll.approve', props.payrollRun.id))
}

const submitMarkPaid = () => {
  markPaidForm.post(route('cms.payroll.mark-paid', props.payrollRun.id), {
    onSuccess: () => {
      showMarkPaidModal.value = false
    }
  })
}

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getStatusClass = (status: string) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    approved: 'bg-green-100 text-green-800',
    paid: 'bg-blue-100 text-blue-800',
  }
  return classes[status as keyof typeof classes] || classes.draft
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <Link :href="route('cms.payroll.index')" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">
        ← Back to Payroll Runs
      </Link>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ payrollRun.payroll_number }}</h1>
          <p class="text-sm text-gray-500">
            {{ formatDate(payrollRun.period_start) }} - {{ formatDate(payrollRun.period_end) }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <span :class="['px-3 py-1 text-sm font-medium rounded-full', getStatusClass(payrollRun.status)]">
            {{ payrollRun.status.toUpperCase() }}
          </span>
          <button
            v-if="payrollRun.status === 'draft'"
            @click="approvePayroll"
            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium"
          >
            <CheckIcon class="h-4 w-4" aria-hidden="true" />
            Approve
          </button>
          <button
            v-if="payrollRun.status === 'approved'"
            @click="showMarkPaidModal = true"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
          >
            <BanknotesIcon class="h-4 w-4" aria-hidden="true" />
            Mark as Paid
          </button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <!-- Payroll Items -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Payroll Items</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Worker/Staff
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                    Wages
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                    Commissions
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                    Net Pay
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in payrollRun.items" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ item.worker ? item.worker.name : item.cms_user.user.name }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ item.worker ? item.worker.worker_number : 'Staff' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="text-sm text-gray-900">K{{ formatNumber(item.wages) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="text-sm text-gray-900">K{{ formatNumber(item.commissions) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="text-sm font-medium text-gray-900">K{{ formatNumber(item.net_pay) }}</div>
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50">
                <tr>
                  <td class="px-6 py-4 text-sm font-semibold text-gray-900">Total</td>
                  <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                    K{{ formatNumber(payrollRun.total_wages) }}
                  </td>
                  <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                    K{{ formatNumber(payrollRun.total_commissions) }}
                  </td>
                  <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                    K{{ formatNumber(payrollRun.total_net_pay) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Summary -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Summary</h3>
          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Period Type</label>
              <p class="text-gray-900 capitalize">{{ payrollRun.period_type.replace('-', ' ') }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Total Wages</label>
              <p class="text-gray-900 font-semibold">K{{ formatNumber(payrollRun.total_wages) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Total Commissions</label>
              <p class="text-gray-900 font-semibold">K{{ formatNumber(payrollRun.total_commissions) }}</p>
            </div>
            <div class="pt-3 border-t">
              <label class="text-sm font-medium text-gray-500">Total Net Pay</label>
              <p class="text-gray-900 font-bold text-lg">K{{ formatNumber(payrollRun.total_net_pay) }}</p>
            </div>
          </div>
        </div>

        <!-- Status Info -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Status Information</h3>
          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Created By</label>
              <p class="text-gray-900">{{ payrollRun.created_by.user.name }}</p>
              <p class="text-sm text-gray-500">{{ formatDate(payrollRun.created_at) }}</p>
            </div>
            <div v-if="payrollRun.approved_by">
              <label class="text-sm font-medium text-gray-500">Approved By</label>
              <p class="text-gray-900">{{ payrollRun.approved_by.user.name }}</p>
              <p class="text-sm text-gray-500">{{ formatDate(payrollRun.approved_at) }}</p>
            </div>
            <div v-if="payrollRun.paid_date">
              <label class="text-sm font-medium text-gray-500">Paid Date</label>
              <p class="text-gray-900">{{ formatDate(payrollRun.paid_date) }}</p>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="payrollRun.notes" class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Notes</h3>
          <p class="text-sm text-gray-600">{{ payrollRun.notes }}</p>
        </div>
      </div>
    </div>

    <!-- Mark as Paid Modal -->
    <div v-if="showMarkPaidModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Mark Payroll as Paid</h3>
          <form @submit.prevent="submitMarkPaid" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Payment Date *</label>
              <input
                v-model="markPaidForm.paid_date"
                type="date"
                required
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <p class="text-sm text-yellow-800">
                This will mark all attendance and commission records as paid. This action cannot be undone.
              </p>
            </div>

            <div class="flex justify-end gap-3 pt-4">
              <button
                type="button"
                @click="showMarkPaidModal = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="markPaidForm.processing"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                {{ markPaidForm.processing ? 'Processing...' : 'Mark as Paid' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
