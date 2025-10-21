<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { CreditCardIcon, PhoneIcon, BanknoteIcon, WalletIcon } from 'lucide-vue-next';

const props = defineProps<{
    userPhone?: string;
}>();

const form = useForm({
    amount: '',
    payment_method: 'mtn_momo',
    payment_reference: '',
    phone_number: props.userPhone || '',
    payment_type: 'wallet_topup',
    notes: '',
});

const submit = () => {
    form.post(route('mygrownet.payments.store'));
};
</script>

<template>
    <MemberLayout>
        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                        <WalletIcon class="h-8 w-8 text-blue-600" />
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Top Up Wallet</h1>
                    <p class="mt-2 text-sm text-gray-600">Send money to the numbers below, then submit proof of payment</p>
                </div>

                <!-- Payment Instructions (Top) -->
                <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-lg p-6 shadow-md">
                    <h3 class="font-semibold text-blue-900 mb-4 flex items-center gap-2 text-lg">
                        <BanknoteIcon class="h-6 w-6" />
                        Send Money To:
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white rounded-lg p-4 border-2 border-yellow-400 shadow-sm">
                            <div class="flex items-center gap-2 mb-3">
                                <PhoneIcon class="h-5 w-5 text-yellow-600" />
                                <span class="font-bold text-gray-900 text-lg">MTN Mobile Money</span>
                            </div>
                            <div class="space-y-2">
                                <div class="bg-yellow-50 rounded px-3 py-2">
                                    <p class="text-xs text-gray-600 mb-1">Phone Number</p>
                                    <p class="text-2xl font-bold text-gray-900 tracking-wide">0963426511</p>
                                </div>
                                <p class="text-sm text-gray-700">Name: <strong class="text-gray-900">Kafula Mbulo</strong></p>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg p-4 border-2 border-red-400 shadow-sm">
                            <div class="flex items-center gap-2 mb-3">
                                <PhoneIcon class="h-5 w-5 text-red-600" />
                                <span class="font-bold text-gray-900 text-lg">Airtel Money</span>
                            </div>
                            <div class="space-y-2">
                                <div class="bg-red-50 rounded px-3 py-2">
                                    <p class="text-xs text-gray-600 mb-1">Phone Number</p>
                                    <p class="text-2xl font-bold text-gray-900 tracking-wide">0979230669</p>
                                </div>
                                <p class="text-sm text-gray-700">Name: <strong class="text-gray-900">Kafula Mbulo</strong></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-blue-200">
                        <p class="text-sm font-medium text-blue-900 flex items-start gap-2">
                            <span class="text-lg">💡</span>
                            <span>After sending money, fill out the form below with your transaction details for verification.</span>
                        </p>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="bg-white rounded-lg shadow-lg p-6 sm:p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Amount to Top Up <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">K</span>
                                <input
                                    v-model="form.amount"
                                    type="number"
                                    step="0.01"
                                    min="50"
                                    class="w-full pl-8 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="0.00"
                                    required
                                />
                            </div>
                            <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                            <p class="mt-1 text-xs text-gray-500">Minimum top-up amount is K50</p>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Payment Method <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-blue-300 hover:bg-blue-50"
                                    :class="form.payment_method === 'mtn_momo' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                    <input v-model="form.payment_method" type="radio" value="mtn_momo" class="sr-only" />
                                    <PhoneIcon class="h-5 w-5 text-yellow-600 mr-3 flex-shrink-0" />
                                    <span class="font-medium">MTN MoMo</span>
                                </label>
                                <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-blue-300 hover:bg-blue-50"
                                    :class="form.payment_method === 'airtel_money' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                    <input v-model="form.payment_method" type="radio" value="airtel_money" class="sr-only" />
                                    <PhoneIcon class="h-5 w-5 text-red-600 mr-3 flex-shrink-0" />
                                    <span class="font-medium">Airtel Money</span>
                                </label>
                            </div>
                        </div>

                        <!-- Payment Reference -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Transaction Reference/ID <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.payment_reference"
                                type="text"
                                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter transaction reference number"
                                required
                            />
                            <p v-if="form.errors.payment_reference" class="mt-1 text-sm text-red-600">{{ form.errors.payment_reference }}</p>
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Phone Number Used <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.phone_number"
                                type="text"
                                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="+260..."
                                required
                            />
                            <p v-if="form.errors.phone_number" class="mt-1 text-sm text-red-600">{{ form.errors.phone_number }}</p>
                            <p v-if="userPhone" class="mt-1 text-xs text-gray-500">Change this only if you used a different number for payment</p>
                            <p v-else class="mt-1 text-xs text-gray-500">Enter the phone number you used for this payment</p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Additional Notes (Optional)
                            </label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Any additional information about this payment..."
                            ></textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-4">
                            <button
                                type="button"
                                @click="$inertia.visit(route('mygrownet.payments.index'))"
                                class="flex-1 px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-colors"
                            >
                                <CreditCardIcon class="h-5 w-5" />
                                {{ form.processing ? 'Submitting...' : 'Submit Payment' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Additional Info -->
                <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm text-green-800 flex items-start gap-2">
                        <span class="text-lg">✅</span>
                        <span>Your wallet will be credited within a few hours once payment is verified by our team.</span>
                    </p>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
