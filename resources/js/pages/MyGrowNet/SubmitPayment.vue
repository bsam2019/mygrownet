<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { CreditCardIcon, PhoneIcon, BanknoteIcon, WalletIcon } from 'lucide-vue-next';

interface PaymentContext {
    type: string;
    amount: number;
    description: string;
}

const props = defineProps<{
    userPhone?: string;
    paymentContext?: PaymentContext;
}>();

const form = useForm({
    amount: props.paymentContext?.amount?.toString() || '',
    payment_method: 'mtn_momo',
    payment_reference: '',
    phone_number: props.userPhone || '',
    payment_type: props.paymentContext?.type === 'starter_kit' ? 'product' : 'wallet_topup',
    notes: props.paymentContext?.description || '',
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
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                        {{ paymentContext?.type === 'starter_kit' ? 'Submit Starter Kit Payment' : 'Top Up Wallet' }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">Send money to the numbers below, then submit proof of payment</p>
                </div>

                <!-- Starter Kit Context Alert -->
                <div v-if="paymentContext?.type === 'starter_kit'" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-900 mb-2">📦 MyGrowNet Starter Kit Purchase</h3>
                    <p class="text-sm text-green-700">
                        Amount: <span class="font-bold">K{{ paymentContext.amount }}</span>
                    </p>
                    <p class="text-sm text-green-700 mt-1">
                        After verification, you'll receive instant access to all starter kit content and K100 shop credit.
                    </p>
                </div>

                <!-- Payment Instructions (Top) -->
                <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-lg p-6 shadow-md">
                    <h3 class="font-semibold text-blue-900 mb-4 flex items-center gap-2 text-lg">
                        <BanknoteIcon class="h-6 w-6" />
                        How to Make Payment:
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- MTN - Withdraw Method -->
                        <div class="bg-white rounded-lg p-4 border-2 border-yellow-400 shadow-sm">
                            <div class="flex items-center gap-2 mb-3">
                                <PhoneIcon class="h-5 w-5 text-yellow-600" />
                                <span class="font-bold text-gray-900 text-lg">MTN Mobile Money</span>
                            </div>
                            <div class="space-y-3">
                                <div class="bg-yellow-50 rounded px-3 py-2">
                                    <p class="text-xs text-gray-600 mb-1">Company Number</p>
                                    <p class="text-2xl font-bold text-gray-900 tracking-wide">0760491206</p>
                                </div>
                                <p class="text-sm text-gray-700">Name: <strong class="text-gray-900">Rockshield Investments Ltd</strong></p>
                                <div class="bg-yellow-100 border border-yellow-300 rounded p-3 mt-2">
                                    <p class="text-xs font-semibold text-yellow-900 mb-1">⚠️ IMPORTANT:</p>
                                    <p class="text-xs text-yellow-800">This is a registered company account. You must <strong>WITHDRAW</strong> from this number to your phone, not send money to it.</p>
                                </div>
                                <div class="text-xs text-gray-700 space-y-1 mt-2">
                                    <p class="font-semibold">Steps:</p>
                                    <p>1. Dial *115# and call</p>
                                    <p>2. Choose option 2 (Withdraw)</p>
                                    <p>3. Choose Cash Out</p>
                                    <p>4. Enter 1 to choose Agent Number</p>
                                    <p>5. Enter Agent Number: 0760491206</p>
                                    <p>6. Enter the amount</p>
                                    <p>7. Enter your PIN</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Airtel - Send Money Method -->
                        <div class="bg-white rounded-lg p-4 border-2 border-red-400 shadow-sm">
                            <div class="flex items-center gap-2 mb-3">
                                <PhoneIcon class="h-5 w-5 text-red-600" />
                                <span class="font-bold text-gray-900 text-lg">Airtel Money</span>
                            </div>
                            <div class="space-y-3">
                                <div class="bg-red-50 rounded px-3 py-2">
                                    <p class="text-xs text-gray-600 mb-1">Phone Number</p>
                                    <p class="text-2xl font-bold text-gray-900 tracking-wide">0979230669</p>
                                </div>
                                <p class="text-sm text-gray-700">Name: <strong class="text-gray-900">Kafula Mbulo</strong></p>
                                <div class="bg-red-100 border border-red-300 rounded p-3 mt-2">
                                    <p class="text-xs font-semibold text-red-900 mb-1">📱 Regular Account:</p>
                                    <p class="text-xs text-red-800">Send money normally to this number using Airtel Money.</p>
                                </div>
                                <div class="text-xs text-gray-700 space-y-1 mt-2">
                                    <p class="font-semibold">Steps:</p>
                                    <p>1. Dial *115#</p>
                                    <p>2. Select "Send Money"</p>
                                    <p>3. Enter: 0979230669</p>
                                    <p>4. Enter amount</p>
                                    <p>5. Confirm transaction</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-blue-200">
                        <p class="text-sm font-medium text-blue-900 flex items-start gap-2">
                            <span class="text-lg">💡</span>
                            <span>After completing your transaction, fill out the form below with your transaction details for verification.</span>
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
