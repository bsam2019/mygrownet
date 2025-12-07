<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, CreditCardIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';

interface TierConfig {
    name: string;
    price_monthly: number;
    price_yearly: number;
    features: string[];
}

interface Props {
    selectedTier: string;
    tierConfig: TierConfig;
    billingCycle: 'monthly' | 'yearly';
    price: number;
}

const props = defineProps<Props>();

const form = useForm({
    tier: props.selectedTier,
    billing_cycle: props.billingCycle,
    payment_method: 'mobile_money',
    phone_number: '',
});

const submit = () => {
    form.post(route('bizboost.subscription.process'));
};

const formatPrice = (price: number) => `K${price.toLocaleString()}`;
</script>

<template>
    <Head title="Checkout - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.subscription.index')" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Plans
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6 border-b bg-gray-50">
                        <h1 class="text-2xl font-bold text-gray-900">Complete Your Subscription</h1>
                    </div>

                    <!-- Order Summary -->
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                        <div class="flex items-center justify-between py-3 border-b">
                            <div>
                                <div class="font-medium text-gray-900">{{ tierConfig.name }} Plan</div>
                                <div class="text-sm text-gray-500">{{ billingCycle === 'monthly' ? 'Monthly' : 'Yearly' }} billing</div>
                            </div>
                            <div class="text-lg font-bold text-gray-900">{{ formatPrice(price) }}</div>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <div class="font-semibold text-gray-900">Total</div>
                            <div class="text-xl font-bold text-blue-600">{{ formatPrice(price) }}</div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer" :class="form.payment_method === 'mobile_money' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                    <input v-model="form.payment_method" type="radio" value="mobile_money" class="text-blue-600" />
                                    <div>
                                        <div class="font-medium">Mobile Money</div>
                                        <div class="text-sm text-gray-500">MTN MoMo, Airtel Money</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div v-if="form.payment_method === 'mobile_money'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input
                                v-model="form.phone_number"
                                type="tel"
                                class="w-full rounded-md border-gray-300"
                                placeholder="e.g., 0971234567"
                                required
                            />
                            <p class="mt-1 text-sm text-gray-500">You'll receive a payment prompt on this number</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 flex items-start gap-3">
                            <ShieldCheckIcon class="h-5 w-5 text-green-600 mt-0.5" aria-hidden="true" />
                            <div class="text-sm text-gray-600">
                                Your payment is secure. You can cancel anytime from your account settings.
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <CreditCardIcon class="h-5 w-5" aria-hidden="true" />
                            {{ form.processing ? 'Processing...' : `Pay ${formatPrice(price)}` }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
