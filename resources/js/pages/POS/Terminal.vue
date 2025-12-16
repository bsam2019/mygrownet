<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import POSLayout from '@/layouts/POSLayout.vue';
import { ref, computed } from 'vue';
import {
    PlusIcon,
    MinusIcon,
    TrashIcon,
    BanknotesIcon,
    DevicePhoneMobileIcon,
    CreditCardIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';

interface CartItem {
    id: string;
    name: string;
    quantity: number;
    unit_price: number;
    inventory_item_id: number | null;
}

interface QuickProduct {
    id: number;
    name: string;
    price: number;
    color: string;
    inventory_item_id: number | null;
}

interface InventoryItem {
    id: number;
    name: string;
    selling_price: number;
    current_stock: number;
}

const props = defineProps<{
    currentShift: { id: number; shift_number: string } | null;
    quickProducts: QuickProduct[];
    settings: { currency_symbol: string; enable_tax: boolean; default_tax_rate: number };
    inventoryItems: { data: InventoryItem[] };
}>();

const cart = ref<CartItem[]>([]);
const search = ref('');
const paymentMethod = ref<'cash' | 'mobile_money' | 'card'>('cash');
const amountPaid = ref(0);
const processing = ref(false);

const subtotal = computed(() => cart.value.reduce((sum, item) => sum + item.quantity * item.unit_price, 0));
const tax = computed(() => (props.settings.enable_tax ? subtotal.value * (props.settings.default_tax_rate / 100) : 0));
const total = computed(() => subtotal.value + tax.value);
const change = computed(() => Math.max(0, amountPaid.value - total.value));

const formatCurrency = (amount: number) => `${props.settings.currency_symbol || 'K'}${amount.toFixed(2)}`;

const addToCart = (product: { name: string; price: number; inventory_item_id?: number | null }) => {
    const existing = cart.value.find((i) => i.name === product.name);
    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({
            id: Date.now().toString(),
            name: product.name,
            quantity: 1,
            unit_price: product.price,
            inventory_item_id: product.inventory_item_id || null,
        });
    }
};

const updateQuantity = (item: CartItem, delta: number) => {
    item.quantity = Math.max(1, item.quantity + delta);
};

const removeFromCart = (item: CartItem) => {
    cart.value = cart.value.filter((i) => i.id !== item.id);
};

const clearCart = () => {
    cart.value = [];
    amountPaid.value = 0;
};

const completeSale = async () => {
    if (cart.value.length === 0 || !props.currentShift) return;
    if (amountPaid.value < total.value && paymentMethod.value === 'cash') {
        alert('Amount paid is less than total');
        return;
    }
    processing.value = true;
    try {
        const response = await fetch(route('pos.sales.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                items: cart.value.map((i) => ({
                    product_name: i.name,
                    quantity: i.quantity,
                    unit_price: i.unit_price,
                    inventory_item_id: i.inventory_item_id,
                })),
                payment_method: paymentMethod.value,
                amount_paid: amountPaid.value,
            }),
        });
        const data = await response.json();
        if (data.success) {
            clearCart();
            alert('Sale completed!');
        } else {
            alert(data.message || 'Error completing sale');
        }
    } catch {
        alert('Error completing sale');
    }
    processing.value = false;
};

const filteredItems = computed(() => {
    if (!search.value) return [];
    const q = search.value.toLowerCase();
    return props.inventoryItems.data.filter((i) => i.name.toLowerCase().includes(q)).slice(0, 10);
});
</script>

<template>
    <POSLayout title="Terminal">
        <Head title="POS Terminal" />
        <div class="flex h-full bg-gray-100">
            <!-- Left: Products -->
            <div class="flex-1 overflow-y-auto p-4">
                <!-- No Shift Warning -->
                <div v-if="!currentShift" class="mb-4 rounded-lg bg-amber-100 p-4 text-amber-800">
                    <p class="font-medium">No active shift</p>
                    <p class="text-sm">Start a shift to begin recording sales</p>
                </div>

                <!-- Search -->
                <div class="relative mb-4">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                    <input v-model="search" type="text" placeholder="Search products..." class="w-full rounded-lg border-gray-300 pl-10" />
                    <div v-if="filteredItems.length > 0" class="absolute left-0 right-0 top-full z-10 mt-1 rounded-lg border bg-white shadow-lg">
                        <button
                            v-for="item in filteredItems"
                            :key="item.id"
                            class="flex w-full items-center justify-between px-4 py-2 text-left hover:bg-gray-50"
                            @click="addToCart({ name: item.name, price: item.selling_price, inventory_item_id: item.id }); search = ''"
                        >
                            <span>{{ item.name }}</span>
                            <span class="text-gray-500">{{ formatCurrency(item.selling_price) }}</span>
                        </button>
                    </div>
                </div>

                <!-- Quick Products -->
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-5">
                    <button
                        v-for="product in quickProducts"
                        :key="product.id"
                        :style="{ backgroundColor: product.color }"
                        class="rounded-lg p-4 text-center text-white shadow transition hover:opacity-90"
                        @click="addToCart(product)"
                    >
                        <p class="font-medium">{{ product.name }}</p>
                        <p class="text-sm opacity-90">{{ formatCurrency(product.price) }}</p>
                    </button>
                </div>
            </div>

            <!-- Right: Cart -->
            <div class="w-96 flex-shrink-0 border-l bg-white p-4">
                <h2 class="mb-4 text-lg font-semibold">Cart</h2>

                <!-- Cart Items -->
                <div class="mb-4 max-h-64 space-y-2 overflow-y-auto">
                    <div v-for="item in cart" :key="item.id" class="flex items-center justify-between rounded-lg bg-gray-50 p-2">
                        <div class="flex-1">
                            <p class="text-sm font-medium">{{ item.name }}</p>
                            <p class="text-xs text-gray-500">{{ formatCurrency(item.unit_price) }} each</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="rounded bg-gray-200 p-1 hover:bg-gray-300" @click="updateQuantity(item, -1)">
                                <MinusIcon class="h-4 w-4" aria-hidden="true" />
                            </button>
                            <span class="w-8 text-center">{{ item.quantity }}</span>
                            <button class="rounded bg-gray-200 p-1 hover:bg-gray-300" @click="updateQuantity(item, 1)">
                                <PlusIcon class="h-4 w-4" aria-hidden="true" />
                            </button>
                            <button class="rounded p-1 text-red-500 hover:bg-red-50" @click="removeFromCart(item)">
                                <TrashIcon class="h-4 w-4" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                    <div v-if="cart.length === 0" class="py-8 text-center text-gray-400">
                        <p>Cart is empty</p>
                    </div>
                </div>

                <!-- Totals -->
                <div class="space-y-2 border-t pt-4">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal</span>
                        <span>{{ formatCurrency(subtotal) }}</span>
                    </div>
                    <div v-if="settings.enable_tax" class="flex justify-between text-sm text-gray-500">
                        <span>Tax ({{ settings.default_tax_rate }}%)</span>
                        <span>{{ formatCurrency(tax) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total</span>
                        <span>{{ formatCurrency(total) }}</span>
                    </div>
                </div>

                <!-- Payment -->
                <div class="mt-4 space-y-3">
                    <div class="flex gap-2">
                        <button
                            :class="paymentMethod === 'cash' ? 'border-purple-500 bg-purple-100' : 'bg-gray-50'"
                            class="flex-1 rounded-lg border p-2 text-center"
                            @click="paymentMethod = 'cash'"
                        >
                            <BanknotesIcon class="mx-auto h-5 w-5" aria-hidden="true" />
                            <span class="text-xs">Cash</span>
                        </button>
                        <button
                            :class="paymentMethod === 'mobile_money' ? 'border-purple-500 bg-purple-100' : 'bg-gray-50'"
                            class="flex-1 rounded-lg border p-2 text-center"
                            @click="paymentMethod = 'mobile_money'"
                        >
                            <DevicePhoneMobileIcon class="mx-auto h-5 w-5" aria-hidden="true" />
                            <span class="text-xs">Mobile</span>
                        </button>
                        <button
                            :class="paymentMethod === 'card' ? 'border-purple-500 bg-purple-100' : 'bg-gray-50'"
                            class="flex-1 rounded-lg border p-2 text-center"
                            @click="paymentMethod = 'card'"
                        >
                            <CreditCardIcon class="mx-auto h-5 w-5" aria-hidden="true" />
                            <span class="text-xs">Card</span>
                        </button>
                    </div>

                    <div v-if="paymentMethod === 'cash'">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Amount Paid</label>
                        <input v-model.number="amountPaid" type="number" step="0.01" class="w-full rounded-lg border-gray-300" />
                        <p v-if="change > 0" class="mt-1 text-sm text-green-600">Change: {{ formatCurrency(change) }}</p>
                    </div>

                    <div class="flex gap-2">
                        <button class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" @click="clearCart">
                            Clear
                        </button>
                        <button
                            :disabled="cart.length === 0 || !currentShift || processing"
                            class="flex-1 rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="completeSale"
                        >
                            {{ processing ? 'Processing...' : 'Complete Sale' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </POSLayout>
</template>
