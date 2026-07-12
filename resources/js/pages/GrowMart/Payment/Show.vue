<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import GrowMartLayout from '@/layouts/GrowMartLayout.vue';
import { ArrowLeftIcon, BanknoteIcon, CreditCardIcon, SmartphoneIcon, LandmarkIcon, CoinsIcon, ExternalLinkIcon, LoaderIcon } from 'lucide-vue-next';
import axios from 'axios';

interface CartItem {
    id: number;
    product_id: number;
    name: string;
    slug: string;
    image?: string;
    unit_price_formatted: string;
    quantity: number;
    total_formatted: string;
}

interface Cart {
    items: CartItem[];
    item_count: number;
    subtotal: number;
    subtotal_formatted: string;
}

interface CheckoutData {
    delivery_method: string;
    payment_method: string;
    delivery_zone?: string;
    delivery_address?: string;
    contact_phone?: string;
    special_instructions?: string;
    coupon_code?: string;
}

interface Props {
    checkoutData: CheckoutData;
    cart: Cart;
    cartCount: number;
    total: number;
    total_formatted: string;
}

const props = defineProps<Props>();

const methods = [
    { id: 'mobile_money', name: 'Mobile Money', desc: 'MTN MoMo / Airtel Money', icon: SmartphoneIcon },
    { id: 'bank_transfer', name: 'Bank Transfer', desc: 'Direct bank deposit', icon: LandmarkIcon },
    { id: 'crypto', name: 'Cryptocurrency (NOWPayments)', desc: 'Pay with crypto from abroad', icon: CoinsIcon },
];

const selectedMethod = ref(props.checkoutData.payment_method || 'mobile_money');
const cryptoLoading = ref(false);
const cryptoError = ref('');

const form = useForm({
    payment_method: selectedMethod.value,
    payment_reference: '',
    phone_number: '',
    notes: '',
});

const submitManualPayment = () => {
    form.payment_method = selectedMethod.value;
    form.post(route('growmart.payment.process'), {
        preserveScroll: true,
    });
};

const payWithCrypto = async () => {
    cryptoLoading.value = true;
    cryptoError.value = '';

    try {
        const response = await axios.post(route('growmart.payment.crypto'));

        if (response.data.success && response.data.invoice_url && response.data.order_id) {
            window.open(response.data.invoice_url, '_blank');
            router.visit(route('growmart.orders.show', response.data.order_id), {
                data: { crypto_payment: 'initiated' },
            });
        } else {
            cryptoError.value = response.data.message || 'Failed to create crypto payment.';
        }
    } catch (error: any) {
        cryptoError.value = error.response?.data?.message || 'Failed to initiate cryptocurrency payment. Please try again.';
    } finally {
        cryptoLoading.value = false;
    }
};

const formatPrice = (ngwee: number): string => {
    return 'K' + (ngwee / 100).toFixed(2);
};

const deliveryFee = computed(() => props.checkoutData.delivery_method === 'yango' ? 3000 : 0);
</script>

<template>
    <Head title="Complete Payment - GrowMart" />

    <GrowMartLayout>
        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('growmart.checkout')" class="inline-flex items-center gap-1 text-sm text-emerald-600 hover:text-emerald-700">
                        <ArrowLeftIcon class="w-4 h-4" />
                        Back to Checkout
                    </Link>
                </div>

                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-4">
                        <BanknoteIcon class="h-8 w-8 text-emerald-600" />
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Complete Your Payment</h1>
                    <p class="text-sm text-gray-500 mt-1">Review your order and submit payment</p>
                </div>

                <div class="mb-6 text-center p-6 bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl border border-emerald-100">
                    <p class="text-sm text-gray-600 mb-1">Amount Due</p>
                    <p class="text-3xl font-bold text-emerald-700">{{ total_formatted }}</p>
                </div>

                <!-- Order Summary -->
                <div class="mb-6 bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Order Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div v-for="item in cart.items" :key="item.id" class="flex justify-between">
                            <span class="text-gray-600 truncate max-w-[200px]">{{ item.quantity }}x {{ item.name }}</span>
                            <span class="text-gray-900 font-medium">{{ item.total_formatted }}</span>
                        </div>
                    </div>
                    <hr class="my-3" />
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-gray-900">{{ cart.subtotal_formatted }}</span>
                    </div>
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-gray-600">Delivery</span>
                        <span :class="deliveryFee === 0 ? 'text-green-600' : 'text-gray-900'">{{ deliveryFee === 0 ? 'Free' : formatPrice(deliveryFee) }}</span>
                    </div>
                    <hr class="my-3" />
                    <div class="flex justify-between font-bold text-lg">
                        <span class="text-gray-900">Total</span>
                        <span class="text-emerald-700">{{ total_formatted }}</span>
                    </div>
                </div>

                <!-- Delivery Details -->
                <div v-if="checkoutData.delivery_method !== 'pickup'" class="mb-6 bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Delivery Details</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Method</dt>
                            <dd class="font-medium text-gray-900 capitalize">{{ checkoutData.delivery_method.replace('_', ' ') }}</dd>
                        </div>
                        <div v-if="checkoutData.delivery_zone" class="flex justify-between">
                            <dt class="text-gray-500">Zone</dt>
                            <dd class="font-medium text-gray-900">{{ checkoutData.delivery_zone }}</dd>
                        </div>
                        <div v-if="checkoutData.delivery_address" class="flex justify-between">
                            <dt class="text-gray-500">Address</dt>
                            <dd class="font-medium text-gray-900 text-right max-w-[250px]">{{ checkoutData.delivery_address }}</dd>
                        </div>
                        <div v-if="checkoutData.contact_phone" class="flex justify-between">
                            <dt class="text-gray-500">Phone</dt>
                            <dd class="font-medium text-gray-900">{{ checkoutData.contact_phone }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Choose Payment Method</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <button v-for="m in methods" :key="m.id" @click="selectedMethod = m.id"
                            class="flex items-center gap-3 p-4 border-2 rounded-xl transition-all text-left"
                            :class="selectedMethod === m.id ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-500/20' : 'border-gray-200 hover:border-gray-300'"
                        >
                            <component :is="m.icon" class="h-6 w-6 shrink-0"
                                :class="selectedMethod === m.id ? 'text-emerald-600' : 'text-gray-400'" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ m.name }}</p>
                                <p class="text-xs text-gray-500">{{ m.desc }}</p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Mobile Money -->
                <div v-if="selectedMethod === 'mobile_money'" class="mb-6">
                    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-300 rounded-lg p-6 shadow-md">
                        <h3 class="font-semibold text-emerald-900 mb-4 flex items-center gap-2 text-lg">
                            <SmartphoneIcon class="h-6 w-6" />
                            How to Make Payment
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="bg-white rounded-lg p-4 border-2 border-yellow-400 shadow-sm">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="font-bold text-gray-900 text-lg">MTN Mobile Money</span>
                                </div>
                                <div class="space-y-3">
                                    <div class="bg-yellow-50 rounded px-3 py-2">
                                        <p class="text-xs text-gray-600 mb-1">Company Number</p>
                                        <p class="text-2xl font-bold text-gray-900 tracking-wide">0760491206</p>
                                    </div>
                                    <p class="text-sm text-gray-700">Name: <strong class="text-gray-900">Rockshield Investments Ltd</strong></p>
                                    <div class="bg-yellow-100 border border-yellow-300 rounded p-3 mt-2">
                                        <p class="text-xs font-semibold text-yellow-900 mb-1">⚠️ WITHDRAWAL:</p>
                                        <p class="text-xs text-yellow-800">Dial *115# → Withdraw → Cash Out → Agent Number → 0760491206</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg p-4 border-2 border-red-400 shadow-sm">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="font-bold text-gray-900 text-lg">Airtel Money</span>
                                </div>
                                <div class="space-y-3">
                                    <div class="bg-red-50 rounded px-3 py-2">
                                        <p class="text-xs text-gray-600 mb-1">Phone Number</p>
                                        <p class="text-2xl font-bold text-gray-900 tracking-wide">0979230669</p>
                                    </div>
                                    <p class="text-sm text-gray-700">Name: <strong class="text-gray-900">Kafula Mbulo</strong></p>
                                    <div class="bg-red-100 border border-red-300 rounded p-3 mt-2">
                                        <p class="text-xs font-semibold text-red-900 mb-1">📱 SEND MONEY:</p>
                                        <p class="text-xs text-red-800">Dial *115# → Send Money → 0979230669 → Amount → PIN</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 bg-white rounded-lg shadow-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Submit Payment Details</h3>
                        <form @submit.prevent="submitManualPayment" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Reference <span class="text-red-500">*</span></label>
                                <input v-model="form.payment_reference" type="text"
                                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Enter your transaction reference number" required />
                                <p v-if="form.errors.payment_reference" class="mt-1 text-sm text-red-600">{{ form.errors.payment_reference }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                <input v-model="form.phone_number" type="text"
                                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="+260..." required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                <textarea v-model="form.notes" rows="3"
                                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Any additional information..."></textarea>
                            </div>
                            <button type="submit" :disabled="form.processing"
                                class="w-full px-6 py-3 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-colors">
                                <CreditCardIcon class="h-5 w-5" />
                                {{ form.processing ? 'Submitting...' : 'Place Order & Submit for Verification' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Bank Transfer -->
                <div v-if="selectedMethod === 'bank_transfer'" class="mb-6">
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Bank Transfer Details</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between"><dt class="text-sm text-gray-500">Bank</dt><dd class="text-sm font-medium text-gray-900">Zanaco</dd></div>
                            <div class="flex justify-between"><dt class="text-sm text-gray-500">Account Name</dt><dd class="text-sm font-medium text-gray-900">MyGrowNet Ltd</dd></div>
                            <div class="flex justify-between"><dt class="text-sm text-gray-500">Account Number</dt><dd class="text-sm font-medium text-gray-900">1234567890</dd></div>
                            <div class="flex justify-between"><dt class="text-sm text-gray-500">Branch</dt><dd class="text-sm font-medium text-gray-900">Cairo Road, Lusaka</dd></div>
                        </dl>
                        <p class="text-sm text-gray-600 mt-4">Use your order number as the reference after placing.</p>
                    </div>
                    <div class="mt-4 bg-white rounded-lg shadow-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Submit Payment Details</h3>
                        <form @submit.prevent="submitManualPayment" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Reference <span class="text-red-500">*</span></label>
                                <input v-model="form.payment_reference" type="text"
                                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Enter your transaction reference number" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                <input v-model="form.phone_number" type="text"
                                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="+260..." required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                <textarea v-model="form.notes" rows="3"
                                    class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Any additional information..."></textarea>
                            </div>
                            <button type="submit" :disabled="form.processing"
                                class="w-full px-6 py-3 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-colors">
                                <CreditCardIcon class="h-5 w-5" />
                                {{ form.processing ? 'Submitting...' : 'Place Order & Submit for Verification' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Crypto via NOWPayments -->
                <div v-if="selectedMethod === 'crypto'" class="mb-6">
                    <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-lg border-2 border-purple-300 p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <CoinsIcon class="h-6 w-6 text-purple-600" />
                            <h3 class="text-lg font-semibold text-purple-900">Cryptocurrency via NOWPayments</h3>
                        </div>
                        <p class="text-sm text-gray-700 mb-4">
                            Pay securely with 300+ cryptocurrencies. You will be redirected to NOWPayments to complete your payment.
                        </p>
                        <div class="bg-white rounded-lg p-4 border border-purple-200 mb-4">
                            <p class="text-xs text-gray-500 mb-1">Supported Currencies</p>
                            <p class="text-sm text-gray-900">BTC, ETH, USDT, LTC, BNB, USDC, XRP, DOGE, and 300+ more</p>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-amber-800 flex items-start gap-2">
                                <span class="text-lg">🌍</span>
                                <span><strong>International customers:</strong> Your payment will be auto-converted from ZMW to USD equivalent at current rates.</span>
                            </p>
                        </div>

                        <button @click="payWithCrypto" :disabled="cryptoLoading"
                            class="w-full px-6 py-4 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-colors text-lg">
                            <LoaderIcon v-if="cryptoLoading" class="h-5 w-5 animate-spin" />
                            <ExternalLinkIcon v-else class="h-5 w-5" />
                            {{ cryptoLoading ? 'Creating Invoice...' : 'Place Order & Pay with NOWPayments' }}
                        </button>

                        <p v-if="cryptoError" class="mt-3 text-sm text-red-600 bg-red-50 border border-red-200 rounded p-3">
                            {{ cryptoError }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800 flex items-start gap-2">
                        <span class="text-lg">ℹ️</span>
                        <span>Mobile Money and Bank Transfer payments are verified manually (a few hours). Crypto payments via NOWPayments are verified automatically.</span>
                    </p>
                </div>
            </div>
        </div>
    </GrowMartLayout>
</template>
