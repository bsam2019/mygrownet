<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import { ShieldCheckIcon, PhoneIcon, ClipboardDocumentIcon } from '@heroicons/vue/24/outline';

interface Order {
    id: number;
    order_number: string;
    total: number;
    formatted_total: string;
}

const props = defineProps<{ order: Order }>();

const form = useForm({ payment_reference: '' });
const copied = ref(false);

const copyOrderNumber = () => {
    navigator.clipboard.writeText(props.order.order_number);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
};

const submit = () => {
    form.post(route('marketplace.orders.confirm-payment', props.order.id));
};
</script>

<template>
    <Head title="Complete Payment - Marketplace" />
    <MarketplaceLayout>
        <div class="max-w-lg mx-auto px-4 py-12">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h1 class="text-xl font-bold text-gray-900 mb-2">Complete Payment</h1>
                <p class="text-gray-500 mb-6">Order: {{ order.order_number }}</p>
                
                <div class="text-center p-6 bg-orange-50 rounded-xl mb-6">
                    <p class="text-sm text-gray-600 mb-1">Amount to Pay</p>
                    <p class="text-3xl font-bold text-orange-600">{{ order.formatted_total }}</p>
                </div>

                <div class="space-y-4 mb-6">
                    <h3 class="font-semibold text-gray-900">Payment Instructions</h3>
                    
                    <!-- Mobile Money Options -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="p-4 border-2 border-orange-500 rounded-lg bg-orange-50">
                            <div class="flex items-center gap-2 mb-2">
                                <PhoneIcon class="h-5 w-5 text-orange-600" aria-hidden="true" />
                                <span class="font-semibold text-gray-900">MTN MoMo</span>
                            </div>
                            <p class="text-xs text-gray-600">Dial *303#</p>
                        </div>
                        <div class="p-4 border-2 border-red-500 rounded-lg bg-red-50">
                            <div class="flex items-center gap-2 mb-2">
                                <PhoneIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                                <span class="font-semibold text-gray-900">Airtel Money</span>
                            </div>
                            <p class="text-xs text-gray-600">Dial *778#</p>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg text-sm space-y-3 text-gray-700">
                        <div class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            <p>Send <strong class="text-gray-900">{{ order.formatted_total }}</strong> via MTN MoMo or Airtel Money to the merchant</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            <div>
                                <p class="mb-1">Use this order number as reference:</p>
                                <button 
                                    type="button"
                                    @click="copyOrderNumber" 
                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-orange-600 font-mono text-sm hover:bg-gray-50 transition-colors"
                                >
                                    {{ order.order_number }}
                                    <ClipboardDocumentIcon class="h-4 w-4" aria-hidden="true" />
                                </button>
                                <span v-if="copied" class="text-green-600 text-xs ml-2 font-medium">✓ Copied!</span>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            <p>After payment, enter your transaction reference number below and click "Confirm Payment"</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-orange-500 text-white rounded-full flex items-center justify-center text-xs font-bold">4</span>
                            <p>Your payment will be held in escrow until you confirm delivery</p>
                        </div>
                    </div>

                    <!-- Help Text -->
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>Need help?</strong> Contact seller or visit our <Link :href="route('marketplace.help')" class="underline hover:text-blue-900">Help Center</Link>
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Reference *</label>
                        <input v-model="form.payment_reference" type="text" 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                            placeholder="Enter MoMo/Airtel transaction ID" />
                        <p v-if="form.errors.payment_reference" class="mt-1 text-sm text-red-600">
                            {{ form.errors.payment_reference }}
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-2 p-3 bg-green-50 rounded-lg">
                        <ShieldCheckIcon class="h-5 w-5 text-green-600" />
                        <span class="text-sm text-green-800">Payment protected by escrow</span>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full py-3 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 disabled:opacity-50">
                        {{ form.processing ? 'Confirming...' : 'Confirm Payment' }}
                    </button>
                </form>
            </div>
        </div>
    </MarketplaceLayout>
</template>
