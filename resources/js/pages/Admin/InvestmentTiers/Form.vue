<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        {{ isEditing ? 'Edit Investment Tier' : 'Create Investment Tier' }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6 bg-white border-b border-gray-200">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <!-- Basic Information -->
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Tier Name</label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': form.errors.name }"
                  />
                  <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                    {{ form.errors.name }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Description</label>
                  <textarea
                    v-model="form.description"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': form.errors.description }"
                  ></textarea>
                  <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                    {{ form.errors.description }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Minimum Investment</label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input
                      v-model="form.minimum_investment"
                      type="number"
                      step="0.01"
                      class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-500': form.errors.minimum_investment }"
                    />
                  </div>
                  <div v-if="form.errors.minimum_investment" class="text-red-500 text-sm mt-1">
                    {{ form.errors.minimum_investment }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Order</label>
                  <input
                    v-model="form.order"
                    type="number"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': form.errors.order }"
                  />
                  <div v-if="form.errors.order" class="text-red-500 text-sm mt-1">
                    {{ form.errors.order }}
                  </div>
                </div>
              </div>

              <!-- Rates and Settings -->
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Fixed Profit Rate (%)</label>
                  <input
                    v-model="form.fixed_profit_rate"
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': form.errors.fixed_profit_rate }"
                  />
                  <div v-if="form.errors.fixed_profit_rate" class="text-red-500 text-sm mt-1">
                    {{ form.errors.fixed_profit_rate }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Direct Referral Rate (%)</label>
                  <input
                    v-model="form.direct_referral_rate"
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': form.errors.direct_referral_rate }"
                  />
                  <div v-if="form.errors.direct_referral_rate" class="text-red-500 text-sm mt-1">
                    {{ form.errors.direct_referral_rate }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Level 2 Referral Rate (%)</label>
                  <input
                    v-model="form.level2_referral_rate"
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': form.errors.level2_referral_rate }"
                  />
                  <div v-if="form.errors.level2_referral_rate" class="text-red-500 text-sm mt-1">
                    {{ form.errors.level2_referral_rate }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Level 3 Referral Rate (%)</label>
                  <input
                    v-model="form.level3_referral_rate"
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': form.errors.level3_referral_rate }"
                  />
                  <div v-if="form.errors.level3_referral_rate" class="text-red-500 text-sm mt-1">
                    {{ form.errors.level3_referral_rate }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Tier Settings -->
            <div class="mt-8">
              <h3 class="text-lg font-medium text-gray-900">Tier Settings</h3>
              <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Early Withdrawal Penalty (%)</label>
                  <input
                    v-model="form.settings.early_withdrawal_penalty"
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': form.errors['settings.early_withdrawal_penalty'] }"
                  />
                  <div v-if="form.errors['settings.early_withdrawal_penalty']" class="text-red-500 text-sm mt-1">
                    {{ form.errors['settings.early_withdrawal_penalty'] }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Partial Withdrawal Limit (%)</label>
                  <input
                    v-model="form.settings.partial_withdrawal_limit"
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': form.errors['settings.partial_withdrawal_limit'] }"
                  />
                  <div v-if="form.errors['settings.partial_withdrawal_limit']" class="text-red-500 text-sm mt-1">
                    {{ form.errors['settings.partial_withdrawal_limit'] }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Minimum Lock-in Period (months)</label>
                  <input
                    v-model="form.settings.minimum_lock_in_period"
                    type="number"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': form.errors['settings.minimum_lock_in_period'] }"
                  />
                  <div v-if="form.errors['settings.minimum_lock_in_period']" class="text-red-500 text-sm mt-1">
                    {{ form.errors['settings.minimum_lock_in_period'] }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Performance Bonus Rate (%)</label>
                  <input
                    v-model="form.settings.performance_bonus_rate"
                    type="number"
                    step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': form.errors['settings.performance_bonus_rate'] }"
                  />
                  <div v-if="form.errors['settings.performance_bonus_rate']" class="text-red-500 text-sm mt-1">
                    {{ form.errors['settings.performance_bonus_rate'] }}
                  </div>
                </div>
              </div>

              <div class="mt-4 space-y-4">
                <div class="flex items-center">
                  <input
                    v-model="form.settings.requires_kyc"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 block text-sm text-gray-900">Requires KYC</label>
                </div>

                <div class="flex items-center">
                  <input
                    v-model="form.settings.requires_approval"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 block text-sm text-gray-900">Requires Approval</label>
                </div>
              </div>
            </div>

            <!-- Benefits -->
            <div class="mt-8">
              <h3 class="text-lg font-medium text-gray-900">Benefits</h3>
              <div class="mt-4">
                <div v-for="(benefit, index) in form.benefits" :key="index" class="flex items-center space-x-2 mb-2">
                  <input
                    v-model="form.benefits[index]"
                    type="text"
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  />
                  <button
                    type="button"
                    @click="removeBenefit(index)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Remove
                  </button>
                </div>
                <button
                  type="button"
                  @click="addBenefit"
                  class="mt-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Add Benefit
                </button>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end">
              <Link
                :href="route('admin.investment-tiers.index')"
                class="mr-4 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </Link>
              <button
                type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                :disabled="form.processing"
              >
                {{ isEditing ? 'Update Tier' : 'Create Tier' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

const props = defineProps({
  tier: {
    type: Object,
    default: () => ({
      name: '',
      description: '',
      minimum_investment: 0,
      fixed_profit_rate: 0,
      direct_referral_rate: 0,
      level2_referral_rate: null,
      level3_referral_rate: null,
      benefits: [],
      order: 0,
      settings: {
        early_withdrawal_penalty: 50,
        partial_withdrawal_limit: 50,
        minimum_lock_in_period: 12,
        performance_bonus_rate: null,
        requires_kyc: true,
        requires_approval: true
      }
    })
  }
})

const isEditing = computed(() => !!props.tier.id)

const form = useForm({
  name: props.tier.name,
  description: props.tier.description,
  minimum_investment: props.tier.minimum_investment,
  fixed_profit_rate: props.tier.fixed_profit_rate,
  direct_referral_rate: props.tier.direct_referral_rate,
  level2_referral_rate: props.tier.level2_referral_rate,
  level3_referral_rate: props.tier.level3_referral_rate,
  benefits: [...props.tier.benefits],
  order: props.tier.order,
  settings: { ...props.tier.settings }
})

const addBenefit = () => {
  form.benefits.push('')
}

const removeBenefit = (index) => {
  form.benefits.splice(index, 1)
}

const submit = () => {
  if (isEditing.value) {
    form.put(route('admin.investment-tiers.update', props.tier.id))
  } else {
    form.post(route('admin.investment-tiers.store'))
  }
}
</script> 