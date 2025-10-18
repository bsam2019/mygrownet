<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-semibold text-gray-900">Referral Dashboard</h1>
      <div class="flex items-center space-x-4">
        <button
          @click="copyReferralLink"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <DocumentDuplicateIcon class="h-5 w-5 mr-2" />
          Copy Referral Link
        </button>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <UserGroupIcon class="h-6 w-6 text-gray-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Referrals</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">{{ stats.total_referrals }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <UserIcon class="h-6 w-6 text-gray-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Active Referrals</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">{{ stats.active_referrals }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CurrencyDollarIcon class="h-6 w-6 text-gray-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Commission</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">{{ formatCurrency(stats.total_commission) }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ClockIcon class="h-6 w-6 text-gray-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Pending Commission</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">{{ formatCurrency(stats.pending_commission) }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Commission Structure -->
    <div class="bg-white shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Commission Structure</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="border rounded-lg p-4">
          <h4 class="font-medium text-gray-900 mb-2">Direct Referrals (Level 1)</h4>
          <p class="text-3xl font-bold text-indigo-600">5%</p>
          <p class="text-sm text-gray-500 mt-1">Commission on direct referral investments</p>
        </div>
        <div class="border rounded-lg p-4">
          <h4 class="font-medium text-gray-900 mb-2">Indirect Referrals (Level 2)</h4>
          <p class="text-3xl font-bold text-indigo-600">2%</p>
          <p class="text-sm text-gray-500 mt-1">Commission on indirect referral investments</p>
        </div>
      </div>
    </div>

    <!-- Referral Link -->
    <div class="bg-white shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Your Referral Link</h3>
      <div class="flex items-center space-x-4">
        <input
          type="text"
          :value="referralLink"
          readonly
          class="flex-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
        <button
          @click="copyReferralLink"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Copy Link
        </button>
      </div>
    </div>

    <!-- Referral Tree -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Your Referral Network</h3>
        <div class="mt-4">
          <ReferralTree :data="referralTree" />
        </div>
      </div>
    </div>

    <!-- Commission History -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Commission History</h3>
        <div class="mt-4">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referral</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="commission in commissions" :key="commission.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(commission.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ commission.referral.name }}</div>
                  <div class="text-sm text-gray-500">{{ commission.referral.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      commission.level === 1
                        ? 'bg-green-100 text-green-800'
                        : 'bg-blue-100 text-blue-800',
                      'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                    ]"
                  >
                    {{ commission.level === 1 ? 'Direct' : 'Indirect' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(commission.amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      commission.status === 'paid'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-yellow-100 text-yellow-800',
                      'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                    ]"
                  >
                    {{ commission.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { DocumentDuplicateIcon, UserGroupIcon, UserIcon, CurrencyDollarIcon, ClockIcon } from '@heroicons/vue/24/outline'
import ReferralTree from './ReferralTree.vue'
import axios from 'axios'
import { formatCurrency } from '@/utils/formatting'

const stats = ref({
  total_referrals: 0,
  active_referrals: 0,
  total_commission: 0,
  pending_commission: 0
})

const referralTree = ref([])
const commissions = ref([])
const referralLink = ref('')

const fetchData = async () => {
  try {
    const [statsResponse, treeResponse, commissionsResponse, linkResponse] = await Promise.all([
      axios.get('/api/referral/stats'),
      axios.get('/api/referral/tree'),
      axios.get('/api/referral/commissions'),
      axios.get('/api/referral/link')
    ])

    stats.value = statsResponse.data.data
    referralTree.value = treeResponse.data.data
    commissions.value = commissionsResponse.data.data
    referralLink.value = linkResponse.data.data.link
  } catch (error) {
    console.error('Error fetching referral data:', error)
  }
}

const copyReferralLink = async () => {
  try {
    await navigator.clipboard.writeText(referralLink.value)
    // Show success notification
  } catch (error) {
    console.error('Error copying referral link:', error)
    // Show error notification
  }
}

const formatNumber = (number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(number)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(() => {
  fetchData()
})
</script> 