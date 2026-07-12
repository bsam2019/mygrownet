<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';

const props = defineProps<{
    business: { id: number; name: string; slug: string; phone: string | null; whatsapp: string | null; currency: string; profile: any };
    order: {
        id: number;
        order_number: string;
        customer_name: string;
        customer_phone: string;
        customer_email: string | null;
        delivery_address: string | null;
        notes: string | null;
        subtotal: number;
        delivery_fee: number;
        total: number;
        currency: string;
        payment_method: string;
        payment_status: string;
        order_status: string;
        created_at: string;
        items: { product_name: string; unit_price: number; quantity: number; subtotal: number }[];
    };
}>();

const formatPrice = (amount: number) => {
    const cur = props.order.currency || 'ZMW';
    return `${cur} ${Number(amount).toFixed(2)}`;
};

const paymentLabel = computed(() => ({
    airtel_money: 'Airtel Money',
    mtn_money: 'MTN Mobile Money',
    cash_on_delivery: 'Cash on Delivery',
    bank_transfer: 'Bank Transfer',
}[props.order.payment_method] || props.order.payment_method));

const whatsappLink = computed(() => {
    const phone = props.business.whatsapp || props.business.phone;
    if (!phone) return '#';
    const msg = `Hi ${props.business.name}, I just placed order #${props.order.order_number}. Can you confirm?`;
    return `https://wa.me/${phone.replace(/\D/g, '')}?text=${encodeURIComponent(msg)}`;
});
</script>

<template>
    <Head :title="`Order Confirmed - ${business.name}`" />
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-white rounded-2xl shadow-sm border p-8">
                <CheckCircleIcon class="h-16 w-16 text-emerald-500 mx-auto mb-4" />
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Order Placed!</h1>
                <p class="text-gray-500 mb-1">Thank you, {{ order.customer_name }}!</p>
                <p class="text-gray-500 mb-6">Your order #{{ order.order_number }} has been received.</p>

                <div class="bg-gray-50 rounded-xl p-4 mb-6 text-left space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Order #</span><span class="font-medium">{{ order.order_number }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Payment</span><span class="font-medium">{{ paymentLabel }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Status</span><span class="font-medium text-amber-600 capitalize">{{ order.order_status }}</span></div>
                    <div v-if="order.delivery_address" class="flex justify-between"><span class="text-gray-500">Delivery</span><span class="font-medium text-right max-w-[200px]">{{ order.delivery_address }}</span></div>
                    <div class="border-t pt-2 mt-2 flex justify-between font-bold text-lg"><span>Total</span><span class="text-emerald-600">{{ formatPrice(order.total) }}</span></div>
                </div>

                <div class="text-left mb-6">
                    <h3 class="font-medium text-gray-900 mb-2 text-sm">Items Ordered</h3>
                    <div v-for="(item, i) in order.items" :key="i" class="flex justify-between text-sm py-1.5 border-b border-gray-100 last:border-0">
                        <span class="text-gray-600">{{ item.product_name }} x{{ item.quantity }}</span>
                        <span class="font-medium">{{ formatPrice(item.subtotal) }}</span>
                    </div>
                </div>

                <a :href="whatsappLink" target="_blank"
                    class="block w-full py-3 bg-green-500 text-white rounded-xl font-medium hover:bg-green-600 mb-3 flex items-center justify-center gap-2">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Confirm via WhatsApp
                </a>

                <Link :href="route('bizboost.public.shop', business.slug)"
                    class="block w-full py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200">
                    Continue Shopping
                </Link>
            </div>
        </div>
    </div>
</template>
