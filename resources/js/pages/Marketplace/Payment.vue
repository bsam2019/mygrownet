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
                    <div class="p-4 bg-gray-50 rounded-lg text-sm space-y-2">
                        <p>1. Send <strong>{{ order.formatted_total }}</strong> via MTN MoMo or Airtel Money</p>
                        <p>2. Use order number as reference: 
                            <button @click="copyOrderNumber" class="inline-flex items-center gap-1 text-orange-600 font-mono">
                                {{ order.order_number }}
                                <ClipboardDocumentIcon class="h-4 w-4" />
                            </button>
                            <span v-if="copied" class="text-green-600 text-xs ml-2">Copied!</span>
                        </p>
                        <p>3. Enter your transaction reference below</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Reference *</label>
                        <input v-model="form.payment_reference" type="text" 
                            class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
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
