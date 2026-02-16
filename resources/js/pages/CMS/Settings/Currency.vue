<template>
  <CMSLayout page-title="Currency Settings">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Currency Settings</h1>
        <p class="text-sm text-gray-600 mt-1">Manage currencies and exchange rates for your business</p>
      </div>

      <!-- Base Currency & Multi-Currency Toggle -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Base Currency Configuration</h2>
        
        <form @submit.prevent="updateSettings" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Base Currency</label>
              <select
                v-model="form.base_currency"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                  {{ currency.code }} - {{ currency.name }} ({{ currency.symbol }})
                </option>
              </select>
              <p class="text-xs text-gray-500 mt-1">Your primary operating currency for financial reporting</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Multi-Currency</label>
              <div class="flex items-center gap-3 h-10">
                <button
                  type="button"
                  @click="form.multi_currency_enabled = !form.multi_currency_enabled"
                  :class="[
                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                    form.multi_currency_enabled ? 'bg-blue-600' : 'bg-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      form.multi_currency_enabled ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  />
                </button>
                <span class="text-sm text-gray-700">
                  {{ form.multi_currency_enabled ? 'Enabled' : 'Disabled' }}
                </span>
              </div>
              <p class="text-xs text-gray-500 mt-1">Allow invoices and payments in multiple currencies</p>
            </div>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save Settings' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Exchange Rates -->
      <div v-if="multiCurrencyEnabled" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Exchange Rates</h2>
          <button
            @click="showAddRateModal = true"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2"
          >
            <PlusIcon class="h-5 w-5" aria-hidden="true" />
            Add Rate
          </button>
        </div>

        <div v-if="exchangeRates.length === 0" class="text-center py-8">
          <CurrencyDollarIcon class="h-12 w-12 text-gray-300 mx-auto mb-2" aria-hidden="true" />
          <p class="text-gray-600">No exchange rates configured</p>
          <p class="text-sm text-gray-500 mt-1">Add exchange rates to enable multi-currency transactions</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Effective Date</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="rate in exchangeRates" :key="rate.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ rate.from_currency }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ rate.to_currency }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ formatRate(rate.rate) }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(rate.effective_date) }}</td>
                <td class="px-4 py-3">
                  <span :class="getSourceBadgeClass(rate.source)">
                    {{ rate.source }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <button
                    @click="editRate(rate)"
                    class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                  >
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Currency Converter -->
      <div v-if="multiCurrencyEnabled" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Currency Converter</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
            <input
              v-model.number="converter.amount"
              type="number"
              step="0.01"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="100.00"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">From</label>
            <select
              v-model="converter.from_currency"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                {{ currency.code }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
            <select
              v-model="converter.to_currency"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                {{ currency.code }}
              </option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="convertCurrency"
              class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
              Convert
            </button>
          </div>
        </div>

        <div v-if="converter.result" class="mt-4 p-4 bg-blue-50 rounded-lg">
          <p class="text-sm text-gray-700">
            <span class="font-semibold">{{ converter.amount }} {{ converter.from_currency }}</span>
            =
            <span class="font-semibold text-blue-600">{{ formatNumber(converter.result.converted_amount) }} {{ converter.to_currency }}</span>
          </p>
          <p class="text-xs text-gray-600 mt-1">
            Exchange rate: 1 {{ converter.from_currency }} = {{ formatRate(converter.result.rate) }} {{ converter.to_currency }}
          </p>
        </div>
      </div>

      <!-- Add/Edit Rate Modal -->
      <TransitionRoot :show="showAddRateModal" as="template">
        <Dialog as="div" class="relative z-50" @close="showAddRateModal = false">
          <TransitionChild
            enter="ease-out duration-300"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="ease-in duration-200"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="fixed inset-0 bg-gray-900/75 transition-opacity" />
          </TransitionChild>

          <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
              <TransitionChild
                enter="ease-out duration-300"
                enter-from="opacity-0 translate-y-4"
                enter-to="opacity-100 translate-y-0"
                leave="ease-in duration-200"
                leave-from="opacity-100 translate-y-0"
                leave-to="opacity-0 translate-y-4"
              >
                <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                      {{ editingRate ? 'Edit Exchange Rate' : 'Add Exchange Rate' }}
                    </h3>

                    <form @submit.prevent="saveRate" class="space-y-4">
                      <div class="grid grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-2">From Currency</label>
                          <select
                            v-model="rateForm.from_currency"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          >
                            <option value="">Select...</option>
                            <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                              {{ currency.code }}
                            </option>
                          </select>
                        </div>

                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-2">To Currency</label>
                          <select
                            v-model="rateForm.to_currency"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          >
                            <option value="">Select...</option>
                            <option v-for="currency in currencies" :key="currency.code" :value="currency.code">
                              {{ currency.code }}
                            </option>
                          </select>
                        </div>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Exchange Rate</label>
                        <input
                          v-model.number="rateForm.rate"
                          type="number"
                          step="0.0000000001"
                          required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="25.50"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                          1 {{ rateForm.from_currency }} = {{ rateForm.rate || '?' }} {{ rateForm.to_currency }}
                        </p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Effective Date</label>
                        <input
                          v-model="rateForm.effective_date"
                          type="date"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                      </div>

                      <div class="flex justify-end gap-3 mt-6">
                        <button
                          type="button"
                          @click="showAddRateModal = false"
                          class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                        >
                          Cancel
                        </button>
                        <button
                          type="submit"
                          :disabled="rateForm.processing"
                          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
                        >
                          {{ rateForm.processing ? 'Saving...' : 'Save Rate' }}
                        </button>
                      </div>
                    </form>
                  </div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </Dialog>
      </TransitionRoot>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue';
import { PlusIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import axios from 'axios';

interface Props {
  currencies: any[];
  baseCurrency: string;
  multiCurrencyEnabled: boolean;
  exchangeRates: any[];
}

const props = defineProps<Props>();

const form = useForm({
  base_currency: props.baseCurrency,
  multi_currency_enabled: props.multiCurrencyEnabled,
});

const showAddRateModal = ref(false);
const editingRate = ref<any>(null);

const rateForm = useForm({
  from_currency: '',
  to_currency: '',
  rate: null as number | null,
  effective_date: new Date().toISOString().split('T')[0],
});

const converter = reactive({
  amount: 100,
  from_currency: 'USD',
  to_currency: props.baseCurrency,
  result: null as any,
});

const updateSettings = () => {
  form.post(route('cms.settings.currency.update'), {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by flash message
    },
  });
};

const saveRate = () => {
  rateForm.post(route('cms.settings.currency.exchange-rate.set'), {
    preserveScroll: true,
    onSuccess: () => {
      showAddRateModal.value = false;
      editingRate.value = null;
      rateForm.reset();
      router.reload({ only: ['exchangeRates'] });
    },
  });
};

const editRate = (rate: any) => {
  editingRate.value = rate;
  rateForm.from_currency = rate.from_currency;
  rateForm.to_currency = rate.to_currency;
  rateForm.rate = parseFloat(rate.rate);
  rateForm.effective_date = rate.effective_date;
  showAddRateModal.value = true;
};

const convertCurrency = async () => {
  try {
    const response = await axios.post(route('cms.settings.currency.convert'), {
      amount: converter.amount,
      from_currency: converter.from_currency,
      to_currency: converter.to_currency,
    });
    converter.result = response.data;
  } catch (error) {
    console.error('Conversion failed:', error);
  }
};

const formatRate = (rate: number) => {
  return parseFloat(rate.toString()).toFixed(6);
};

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-ZM', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-ZM', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getSourceBadgeClass = (source: string) => {
  const baseClass = 'px-2 py-1 text-xs font-medium rounded-full';
  switch (source) {
    case 'manual':
      return `${baseClass} bg-blue-100 text-blue-800`;
    case 'api':
      return `${baseClass} bg-green-100 text-green-800`;
    default:
      return `${baseClass} bg-gray-100 text-gray-800`;
  }
};
</script>
