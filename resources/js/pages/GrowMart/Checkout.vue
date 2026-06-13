<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import GrowMartLayout from '@/layouts/GrowMartLayout.vue';
import { ShoppingBagIcon, TruckIcon, BuildingStorefrontIcon, MapPinIcon, TagIcon, XMarkIcon, DevicePhoneMobileIcon, CreditCardIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';

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

interface DeliveryMethod {
    id: string;
    name: string;
    description: string;
    fee: number;
}

interface PaymentMethod {
    id: string;
    name: string;
    description: string;
    icon: string;
    coming_soon: boolean;
}

interface CouponResult {
    valid: boolean;
    coupon_id?: number;
    code?: string;
    discount?: number;
    discount_formatted?: string;
    message?: string;
    description?: string;
}

interface Props {
    cart: Cart;
    cartCount: number;
    deliveryMethods: DeliveryMethod[];
    paymentMethods: PaymentMethod[];
}

const props = defineProps<Props>();

const couponCode = ref('');
const appliedCoupon = ref<CouponResult | null>(null);
const couponError = ref('');
const couponLoading = ref(false);

const form = useForm({
    delivery_method: 'yango',
    payment_method: 'mobile_money',
    delivery_zone: 'Lusaka',
    delivery_address: '',
    contact_phone: '',
    special_instructions: '',
    coupon_code: '',
});

async function applyCoupon() {
    if (!couponCode.value.trim()) return;
    couponLoading.value = true;
    couponError.value = '';
    try {
        const res = await fetch(route('growmart.checkout.validate-coupon').url(), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content ?? '' },
            body: JSON.stringify({ code: couponCode.value, subtotal: props.cart.subtotal }),
        });
        const result: CouponResult = await res.json();
        if (result.valid) {
            appliedCoupon.value = result;
            form.coupon_code = result.code ?? '';
            couponCode.value = '';
        } else {
            couponError.value = result.message ?? 'Invalid coupon';
        }
    } catch {
        couponError.value = 'Failed to validate coupon.';
    }
    couponLoading.value = false;
}

function removeCoupon() {
    appliedCoupon.value = null;
    form.coupon_code = '';
}

const submit = () => {
    form.post(route('growmart.checkout.store'), {
        preserveScroll: true,
    });
};

const selectedFee = (): number => {
    const method = props.deliveryMethods.find(m => m.id === form.delivery_method);
    return method?.fee ?? 0;
};

const discountAmount = computed(() => appliedCoupon.value?.discount ?? 0);

const total = computed(() => {
    return props.cart.subtotal + selectedFee() - discountAmount.value;
});

const formatPrice = (ngwee: number): string => {
    return 'K' + (ngwee / 100).toFixed(2);
};
</script>

<template>
    <Head title="Checkout - GrowMart" />

    <GrowMartLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="mb-6">
                <Link :href="route('growmart.cart')" class="text-sm text-emerald-600 hover:text-emerald-700">← Back to Cart</Link>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>

            <!-- Validation Errors -->
            <div v-if="Object.keys(form.errors).length > 0" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-sm font-medium text-red-800 mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-0.5">
                    <li v-for="(msg, field) in form.errors" :key="field">{{ msg }}</li>
                </ul>
            </div>

            <form @submit.prevent="submit" class="grid md:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="md:col-span-2 space-y-4">
                    <!-- Delivery Method -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Delivery Method</h2>
                        <div class="space-y-2">
                            <label v-for="method in deliveryMethods" :key="method.id"
                                class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-colors"
                                :class="form.delivery_method === method.id ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <input type="radio" v-model="form.delivery_method" :value="method.id" class="sr-only" />
                                <TruckIcon v-if="method.id === 'yango'" class="h-6 w-6 text-emerald-600" />
                                <MapPinIcon v-else-if="method.id === 'pickup'" class="h-6 w-6 text-blue-600" />
                                <BuildingStorefrontIcon v-else class="h-6 w-6 text-orange-600" />
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ method.name }}</p>
                                    <p class="text-sm text-gray-500">{{ method.description }}</p>
                                </div>
                                <span class="text-sm font-medium" :class="method.fee === 0 ? 'text-green-600' : 'text-gray-900'">
                                    {{ method.fee === 0 ? 'Free' : formatPrice(method.fee) }}
                                </span>
                            </label>
                        </div>
                        <p v-if="form.errors.delivery_method" class="mt-2 text-xs text-red-600">{{ form.errors.delivery_method }}</p>
                    </div>

                    <!-- Delivery Details -->
                    <div v-if="form.delivery_method !== 'pickup'" class="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Delivery Details</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Zone</label>
                                <input v-model="form.delivery_zone" type="text" placeholder="e.g., Lusaka, Woodlands" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" :class="{ 'border-red-400': form.errors.delivery_zone }" />
                                <p v-if="form.errors.delivery_zone" class="mt-1 text-xs text-red-600">{{ form.errors.delivery_zone }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address *</label>
                                <textarea v-model="form.delivery_address" rows="2" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" :class="{ 'border-red-400': form.errors.delivery_address }" placeholder="Street name, house number, landmarks"></textarea>
                                <p v-if="form.errors.delivery_address" class="mt-1 text-xs text-red-600">{{ form.errors.delivery_address }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone *</label>
                                <input v-model="form.contact_phone" type="tel" required placeholder="097XXXXXXX" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" :class="{ 'border-red-400': form.errors.contact_phone }" />
                                <p v-if="form.errors.contact_phone" class="mt-1 text-xs text-red-600">{{ form.errors.contact_phone }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                                <textarea v-model="form.special_instructions" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Any special requests..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Payment Method</h2>
                        <div class="grid grid-cols-2 gap-3">
                            <label v-for="pm in paymentMethods" :key="pm.id"
                                class="flex flex-col items-center gap-2 p-4 border rounded-xl cursor-pointer transition-all text-center"
                                :class="[
                                    form.payment_method === pm.id ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-500/20' : 'border-gray-200 hover:border-gray-300',
                                    pm.coming_soon ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <input type="radio" v-model="form.payment_method" :value="pm.id" :disabled="pm.coming_soon" class="sr-only" />
                                <DevicePhoneMobileIcon v-if="pm.icon === 'mobile'" class="h-8 w-8 text-blue-600" />
                                <CurrencyDollarIcon v-else-if="pm.icon === 'bank'" class="h-8 w-8 text-amber-600" />
                                <CurrencyDollarIcon v-else-if="pm.icon === 'crypto'" class="h-8 w-8 text-purple-600" />
                                <CreditCardIcon v-else-if="pm.icon === 'card'" class="h-8 w-8 text-purple-600" />
                                <CurrencyDollarIcon v-else class="h-8 w-8 text-gray-400" />
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ pm.name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ pm.description }}</p>
                                </div>
                                <span v-if="pm.coming_soon" class="text-[10px] font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Coming Soon</span>
                            </label>
                        </div>
                        <p v-if="form.errors.payment_method" class="mt-2 text-xs text-red-600">{{ form.errors.payment_method }}</p>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="md:col-span-1 space-y-4">
                    <!-- Coupon -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <TagIcon class="w-4 h-4" />
                            Coupon Code
                        </h3>

                        <div v-if="appliedCoupon" class="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                            <div class="min-w-0 flex-1">
                                <span class="text-sm font-medium text-emerald-700">{{ appliedCoupon.code }}</span>
                                <span class="text-xs text-emerald-600 ml-2">-{{ appliedCoupon.discount_formatted }}</span>
                            </div>
                            <button @click="removeCoupon" class="shrink-0 ml-2 text-emerald-500 hover:text-emerald-700">
                                <XMarkIcon class="w-4 h-4" />
                            </button>
                        </div>

                        <div v-else class="flex gap-2">
                            <input v-model="couponCode" type="text" placeholder="Enter code"
                                @keyup.enter="applyCoupon"
                                class="min-w-0 flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                            <button @click="applyCoupon" :disabled="couponLoading || !couponCode.trim()"
                                class="shrink-0 px-3 py-2 text-sm font-medium bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 disabled:opacity-50 transition-colors">
                                {{ couponLoading ? '...' : 'Apply' }}
                            </button>
                        </div>
                        <p v-if="couponError" class="text-xs text-red-600 mt-1">{{ couponError }}</p>
                    </div>

                    <div class="bg-white rounded-lg border border-gray-200 p-6 sticky top-24">
                        <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>
                        <div class="space-y-3 text-sm">
                            <div v-for="item in cart.items" :key="item.id" class="flex justify-between">
                                <span class="text-gray-600 truncate max-w-[140px]">{{ item.quantity }}x {{ item.name }}</span>
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
                            <span :class="selectedFee() === 0 ? 'text-green-600' : 'text-gray-900'">{{ selectedFee() === 0 ? 'Free' : formatPrice(selectedFee()) }}</span>
                        </div>
                        <div v-if="discountAmount > 0" class="flex justify-between text-sm mt-1">
                            <span class="text-green-600">Discount</span>
                            <span class="text-green-600">-{{ formatPrice(discountAmount) }}</span>
                        </div>
                        <hr class="my-3" />
                        <div class="flex justify-between font-bold text-lg">
                            <span class="text-gray-900">Total</span>
                            <span class="text-emerald-700">{{ formatPrice(total) }}</span>
                        </div>

                        <button type="submit" :disabled="form.processing" class="mt-6 w-full bg-emerald-600 text-white py-3 rounded-lg font-semibold hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                            {{ form.processing ? 'Continuing...' : 'Continue to Payment' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </GrowMartLayout>
</template>
