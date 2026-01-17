<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import { 
    BanknotesIcon, 
    ArrowLeftIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    availableBalance: number;
    minimumPayout: number;
    payoutMethods: Record<string, any>;
    seller: any;
}

const props = defineProps<Props>();

const form = useForm({
    amount: '',
    payout_method: 'momo',
    account_number: '',
    account_name: '',
    bank_name: '',
    notes: '',
});

const formatAmount = (amount: number) => {
    return 'K' + (amount / 100).toFixed(2);
};

const amountInNgwee = computed(() => {
    const amount = parseFloat(form.amount);
    return isNaN(amount) ? 0 : Math.round(amount * 100);
});

const isValidAmount = computed(() => {
    return amountInNgwee.value >= props.minimumPayout && 
           amountInNgwee.value <= props.availableBalance;
});

const setMaxAmount = () => {
    form.amount = (props.availableBalance / 100).toFixed(2);
};

const submit = () => {
    if (!isValidAmount.value) {
        return;
    }
    
    form.transform((data) => ({
        ...data,
        amount: amountInNgwee.value,
    })).post(route('marketplace.seller.payouts.store'));
};
</script>

<template>
    <Head title="Request Payout - Seller Dashboard" />
    
    <MarketplaceLayout>
        <div class="max-w-2xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('marketplace.seller.payouts.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Request Payout</h1>
                    <p class="text-gray-500">Withdraw your earnings</p>
                </div>
            </div>

            <!-- Balance Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <InformationCircleIcon class="h-6 w-6 text-blue-600 flex-shrink-0" aria-hidden="true" />
                    <div>
                        <p class="font-medium text-blue-900">Available Balance: {{ formatAmount(availableBalance) }}</p>
                        <p class="text-sm text-blue-700 mt-1">
                            Minimum payout: {{ formatAmount(minimumPayout) }} • Processing time: 1-2 business days
                        </p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border p-6 space-y-6">
                <!-- Global Errors -->
                <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <h3 class="text-sm font-semibold text-red-800 mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                    </ul>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (K) *</label>
                    <div class="relative">
                        <input 
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            placeholder="0.00"
                        />
                        <button 
                            type="button"
                            @click="setMaxAmount"
                            class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 text-sm text-orange-600 hover:text-orange-700 font-medium"
                        >
                            Max
                        </button>
                    </div>
                    <p v-if="!isValidAmount && form.amount" class="mt-1 text-sm text-red-600">
                        Amount must be between {{ formatAmount(minimumPayout) }} and {{ formatAmount(availableBalance) }}
                    </p>
                    <p v-else class="mt-1 text-xs text-gray-500">
                        Enter amount between {{ formatAmount(minimumPayout) }} and {{ formatAmount(availableBalance) }}
                    </p>
                </div>

                <!-- Payout Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payout Method *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label 
                            v-for="(method, key) in payoutMethods" 
                            :key="key"
                            v-show="method.enabled"
                            :class="[
                                'flex items-center gap-3 p-4 border rounded-lg cursor-pointer transition-colors',
                                form.payout_method === key ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            <input 
                                type="radio" 
                                v-model="form.payout_method" 
                                :value="key" 
                                class="text-orange-500"
                            />
                            <span class="text-sm font-medium text-gray-900">{{ method.label }}</span>
                        </label>
                    </div>
                </div>

                <!-- Account Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ form.payout_method === 'bank' ? 'Account Number' : 'Phone Number' }} *
                    </label>
                    <input 
                        v-model="form.account_number"
                        type="text"
                        required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        :placeholder="form.payout_method === 'bank' ? '1234567890' : '0977123456'"
                    />
                    <p v-if="form.errors.account_number" class="mt-1 text-sm text-red-600">{{ form.errors.account_number }}</p>
                </div>

                <!-- Account Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Name *</label>
                    <input 
                        v-model="form.account_name"
                        type="text"
                        required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Full name as registered"
                    />
                    <p v-if="form.errors.account_name" class="mt-1 text-sm text-red-600">{{ form.errors.account_name }}</p>
                </div>

                <!-- Bank Name (only for bank transfers) -->
                <div v-if="form.payout_method === 'bank'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name *</label>
                    <input 
                        v-model="form.bank_name"
                        type="text"
                        required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="e.g. Zanaco, FNB, Stanbic"
                    />
                    <p v-if="form.errors.bank_name" class="mt-1 text-sm text-red-600">{{ form.errors.bank_name }}</p>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea 
                        v-model="form.notes"
                        rows="3"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Any additional information..."
                    ></textarea>
                </div>

                <!-- Important Notice -->
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm font-medium text-yellow-900 mb-2">⚠️ Important:</p>
                    <ul class="text-sm text-yellow-800 space-y-1 list-disc list-inside">
                        <li>Ensure account details are correct to avoid delays</li>
                        <li>Payouts are processed within 1-2 business days</li>
                        <li>You'll receive a notification once processed</li>
                        <li>Amount will be deducted from your available balance immediately</li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4">
                    <Link 
                        :href="route('marketplace.seller.payouts.index')" 
                        class="px-6 py-2 border border-gray-300 text-gray-700 bg-white rounded-lg hover:bg-gray-50 font-medium"
                    >
                        Cancel
                    </Link>
                    <button 
                        type="submit"
                        :disabled="form.processing || !isValidAmount"
                        class="flex-1 px-6 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Submitting...' : 'Submit Payout Request' }}
                    </button>
                </div>
            </form>
        </div>
    </MarketplaceLayout>
</template>
