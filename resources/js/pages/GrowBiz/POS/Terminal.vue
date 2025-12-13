<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ShoppingCartIcon,
    PlusIcon,
    MinusIcon,
    TrashIcon,
    MagnifyingGlassIcon,
    BanknotesIcon,
    DevicePhoneMobileIcon,
    CreditCardIcon,
    XMarkIcon,
    CheckCircleIcon,
    ClockIcon,
    ReceiptPercentIcon,
} from '@heroicons/vue/24/outline';

interface QuickProduct {
    id: number;
    name: string;
    price: number;
    color: string;
    inventory_item_id: number | null;
}

interface CartItem {
    id: string;
    product_name: string;
    inventory_item_id: number | null;
    quantity: number;
    unit_price: number;
    discount: number;
    total: number;
}

interface Settings {
    currency_symbol: string;
    enable_tax: boolean;
    default_tax_rate: number;
    quick_amounts: number[];
}

interface Shift {
    id: number;
    shift_number: string;
    total_sales: number;
    transaction_count: number;
}

interface TodayStats {
    total_sales: number;
    total_transactions: number;
}

const props = defineProps<{
    activeShift: Shift | null;
    quickProducts: QuickProduct[];
    settings: Settings;
    todayStats: TodayStats;
}>();

const cart = ref<CartItem[]>([]);
const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const showSearch = ref(false);
const showPayment = ref(false);
const showShiftModal = ref(false);
const isSubmitting = ref(false);
const selectedPaymentMethod = ref('cash');
const amountPaid = ref(0);
const customerName = ref('');
const customerPhone = ref('');
const openingCash = ref(0);

const subtotal = computed(() => cart.value.reduce((sum, item) => sum + item.total, 0));
const taxAmount = computed(() => props.settings.enable_tax ? subtotal.value * (props.settings.default_tax_rate / 100) : 0);
const totalAmount = computed(() => subtotal.value + taxAmount.value);
const changeAmount = computed(() => Math.max(0, amountPaid.value - totalAmount.value));

const addToCart = (product: QuickProduct | any) => {
    const existingIndex = cart.value.findIndex(
        item => item.product_name === product.name && item.unit_price === product.price
    );

    if (existingIndex >= 0) {
        cart.value[existingIndex].quantity++;
        cart.value[existingIndex].total = cart.value[existingIndex].quantity * cart.value[existingIndex].unit_price;
    } else {
        cart.value.push({
            id: Date.now().toString(),
            product_name: product.name,
            inventory_item_id: product.inventory_item_id || product.id || null,
            quantity: 1,
            unit_price: product.price || product.selling_price || 0,
            discount: 0,
            total: product.price || product.selling_price || 0,
        });
    }
};

const updateQuantity = (index: number, delta: number) => {
    const item = cart.value[index];
    item.quantity = Math.max(1, item.quantity + delta);
    item.total = item.quantity * item.unit_price - item.discount;
};

const removeFromCart = (index: number) => {
    cart.value.splice(index, 1);
};

const clearCart = () => {
    cart.value = [];
    customerName.value = '';
    customerPhone.value = '';
};

const searchProducts = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }

    try {
        const response = await fetch(route('growbiz.pos.products.search') + '?q=' + encodeURIComponent(searchQuery.value));
        searchResults.value = await response.json();
    } catch (e) {
        searchResults.value = [];
    }
};

const selectSearchResult = (product: any) => {
    addToCart(product);
    searchQuery.value = '';
    searchResults.value = [];
    showSearch.value = false;
};

const openPaymentModal = () => {
    if (cart.value.length === 0) return;
    amountPaid.value = totalAmount.value;
    showPayment.value = true;
};

const setQuickAmount = (amount: number) => {
    amountPaid.value = amount;
};

const completeSale = () => {
    if (isSubmitting.value || cart.value.length === 0) return;
    isSubmitting.value = true;

    router.post(route('growbiz.pos.sales.store'), {
        items: cart.value.map(item => ({
            product_name: item.product_name,
            inventory_item_id: item.inventory_item_id,
            quantity: item.quantity,
            unit_price: item.unit_price,
            discount: item.discount,
        })),
        customer_name: customerName.value || null,
        customer_phone: customerPhone.value || null,
        tax_amount: taxAmount.value,
        payment_method: selectedPaymentMethod.value,
        amount_paid: amountPaid.value,
    }, {
        onSuccess: () => {
            showPayment.value = false;
            clearCart();
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const openShift = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    router.post(route('growbiz.pos.shifts.open'), {
        opening_cash: openingCash.value,
    }, {
        onSuccess: () => {
            showShiftModal.value = false;
            openingCash.value = 0;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const formatCurrency = (amount: number) => {
    return props.settings.currency_symbol + amount.toFixed(2);
};
</script>

<template>
    <GrowBizLayout>
        <div class="h-[calc(100vh-8rem)] flex flex-col">
            <!-- Header Stats -->
            <div class="flex-shrink-0 p-3 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Point of Sale</h1>
                        <p v-if="activeShift" class="text-xs text-emerald-600">
                            Shift #{{ activeShift.shift_number }} · {{ activeShift.transaction_count }} sales
                        </p>
                        <p v-else class="text-xs text-amber-600">No active shift</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-emerald-600">{{ formatCurrency(todayStats.total_sales) }}</p>
                        <p class="text-xs text-gray-500">Today's Sales</p>
                    </div>
                </div>
            </div>

            <!-- No Shift Warning -->
            <div v-if="!activeShift" class="flex-shrink-0 p-4 bg-amber-50 border-b border-amber-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <ClockIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                        <span class="text-sm text-amber-800">Open a shift to start selling</span>
                    </div>
                    <button
                        @click="showShiftModal = true"
                        class="px-3 py-1.5 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700"
                    >
                        Open Shift
                    </button>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Quick Products Grid -->
                <div class="flex-shrink-0 p-3 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center gap-2 mb-2">
                        <button
                            @click="showSearch = true"
                            class="flex-1 flex items-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded-lg text-gray-500 text-sm"
                        >
                            <MagnifyingGlassIcon class="h-4 w-4" aria-hidden="true" />
                            Search products...
                        </button>
                    </div>
                    <div class="grid grid-cols-4 gap-2">
                        <button
                            v-for="product in quickProducts.slice(0, 8)"
                            :key="product.id"
                            @click="addToCart(product)"
                            :disabled="!activeShift"
                            class="p-2 rounded-lg text-white text-xs font-medium text-center truncate disabled:opacity-50"
                            :style="{ backgroundColor: product.color }"
                        >
                            {{ product.name }}
                            <br>
                            <span class="text-[10px] opacity-80">{{ formatCurrency(product.price) }}</span>
                        </button>
                    </div>
                </div>

                <!-- Cart -->
                <div class="flex-1 overflow-y-auto p-3 bg-white">
                    <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400">
                        <ShoppingCartIcon class="h-12 w-12 mb-2" aria-hidden="true" />
                        <p class="text-sm">Cart is empty</p>
                        <p class="text-xs">Tap products to add</p>
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="(item, index) in cart"
                            :key="item.id"
                            class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 text-sm truncate">{{ item.product_name }}</p>
                                <p class="text-xs text-gray-500">{{ formatCurrency(item.unit_price) }} each</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    @click="updateQuantity(index, -1)"
                                    class="p-1 bg-gray-200 rounded hover:bg-gray-300"
                                    aria-label="Decrease quantity"
                                >
                                    <MinusIcon class="h-4 w-4" aria-hidden="true" />
                                </button>
                                <span class="w-8 text-center font-medium">{{ item.quantity }}</span>
                                <button
                                    @click="updateQuantity(index, 1)"
                                    class="p-1 bg-gray-200 rounded hover:bg-gray-300"
                                    aria-label="Increase quantity"
                                >
                                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                                </button>
                            </div>
                            <p class="w-20 text-right font-semibold text-gray-900">{{ formatCurrency(item.total) }}</p>
                            <button
                                @click="removeFromCart(index)"
                                class="p-1 text-red-500 hover:bg-red-50 rounded"
                                aria-label="Remove item"
                            >
                                <TrashIcon class="h-4 w-4" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cart Footer -->
                <div class="flex-shrink-0 p-3 bg-white border-t border-gray-200 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-medium">{{ formatCurrency(subtotal) }}</span>
                    </div>
                    <div v-if="settings.enable_tax" class="flex justify-between text-sm">
                        <span class="text-gray-500">Tax ({{ settings.default_tax_rate }}%)</span>
                        <span class="font-medium">{{ formatCurrency(taxAmount) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total</span>
                        <span class="text-emerald-600">{{ formatCurrency(totalAmount) }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            @click="clearCart"
                            :disabled="cart.length === 0"
                            class="py-3 bg-gray-100 text-gray-700 font-medium rounded-xl disabled:opacity-50"
                        >
                            Clear
                        </button>
                        <button
                            @click="openPaymentModal"
                            :disabled="cart.length === 0 || !activeShift"
                            class="py-3 bg-emerald-600 text-white font-medium rounded-xl disabled:opacity-50"
                        >
                            Pay {{ formatCurrency(totalAmount) }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Modal -->
        <Teleport to="body">
            <div v-if="showSearch" class="fixed inset-0 z-50 bg-black/50" @click="showSearch = false"></div>
            <div v-if="showSearch" class="fixed inset-x-0 top-0 z-50 bg-white p-4 safe-area-top">
                <div class="flex items-center gap-2">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search products..."
                        class="flex-1 border-gray-200 rounded-lg"
                        @input="searchProducts"
                        autofocus
                    />
                    <button @click="showSearch = false" class="p-2" aria-label="Close search">
                        <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                </div>
                <div v-if="searchResults.length > 0" class="mt-2 max-h-60 overflow-y-auto">
                    <button
                        v-for="product in searchResults"
                        :key="product.id"
                        @click="selectSearchResult(product)"
                        class="w-full p-3 text-left hover:bg-gray-50 border-b border-gray-100"
                    >
                        <p class="font-medium">{{ product.name }}</p>
                        <p class="text-sm text-emerald-600">{{ formatCurrency(product.selling_price) }}</p>
                    </button>
                </div>
            </div>
        </Teleport>

        <!-- Payment Modal -->
        <Teleport to="body">
            <div v-if="showPayment" class="fixed inset-0 z-50 bg-black/50" @click="showPayment = false"></div>
            <div v-if="showPayment" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl max-h-[90vh] overflow-y-auto safe-area-bottom">
                <div class="p-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Payment</h2>
                        <button @click="showPayment = false" class="p-2" aria-label="Close">
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                    <p class="text-2xl font-bold text-emerald-600 mt-2">{{ formatCurrency(totalAmount) }}</p>
                </div>
                <div class="p-4 space-y-4">
                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                @click="selectedPaymentMethod = 'cash'"
                                class="p-3 rounded-xl border-2 flex flex-col items-center gap-1"
                                :class="selectedPaymentMethod === 'cash' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200'"
                            >
                                <BanknotesIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                                <span class="text-xs font-medium">Cash</span>
                            </button>
                            <button
                                @click="selectedPaymentMethod = 'mobile_money'"
                                class="p-3 rounded-xl border-2 flex flex-col items-center gap-1"
                                :class="selectedPaymentMethod === 'mobile_money' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200'"
                            >
                                <DevicePhoneMobileIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                                <span class="text-xs font-medium">Mobile</span>
                            </button>
                            <button
                                @click="selectedPaymentMethod = 'card'"
                                class="p-3 rounded-xl border-2 flex flex-col items-center gap-1"
                                :class="selectedPaymentMethod === 'card' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200'"
                            >
                                <CreditCardIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                                <span class="text-xs font-medium">Card</span>
                            </button>
                        </div>
                    </div>

                    <!-- Amount Paid (for cash) -->
                    <div v-if="selectedPaymentMethod === 'cash'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount Received</label>
                        <input
                            v-model.number="amountPaid"
                            type="number"
                            step="0.01"
                            class="w-full text-xl font-bold text-center border-gray-200 rounded-lg"
                        />
                        <div class="flex flex-wrap gap-2 mt-2">
                            <button
                                v-for="amount in settings.quick_amounts"
                                :key="amount"
                                @click="setQuickAmount(amount)"
                                class="px-3 py-1.5 bg-gray-100 rounded-lg text-sm font-medium hover:bg-gray-200"
                            >
                                {{ formatCurrency(amount) }}
                            </button>
                            <button
                                @click="setQuickAmount(totalAmount)"
                                class="px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-medium"
                            >
                                Exact
                            </button>
                        </div>
                        <div v-if="changeAmount > 0" class="mt-3 p-3 bg-amber-50 rounded-lg">
                            <p class="text-sm text-amber-800">Change: <span class="font-bold">{{ formatCurrency(changeAmount) }}</span></p>
                        </div>
                    </div>

                    <!-- Customer Info (optional) -->
                    <div class="grid grid-cols-2 gap-2">
                        <input
                            v-model="customerName"
                            type="text"
                            placeholder="Customer name (optional)"
                            class="text-sm border-gray-200 rounded-lg"
                        />
                        <input
                            v-model="customerPhone"
                            type="tel"
                            placeholder="Phone (optional)"
                            class="text-sm border-gray-200 rounded-lg"
                        />
                    </div>

                    <button
                        @click="completeSale"
                        :disabled="isSubmitting || (selectedPaymentMethod === 'cash' && amountPaid < totalAmount)"
                        class="w-full py-4 bg-emerald-600 text-white font-semibold rounded-xl disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                        {{ isSubmitting ? 'Processing...' : 'Complete Sale' }}
                    </button>
                </div>
            </div>
        </Teleport>

        <!-- Open Shift Modal -->
        <Teleport to="body">
            <div v-if="showShiftModal" class="fixed inset-0 z-50 bg-black/50" @click="showShiftModal = false"></div>
            <div v-if="showShiftModal" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl safe-area-bottom">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold">Open Shift</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Opening Cash</label>
                        <input
                            v-model.number="openingCash"
                            type="number"
                            step="0.01"
                            class="w-full border-gray-200 rounded-lg"
                            placeholder="0.00"
                        />
                    </div>
                    <button
                        @click="openShift"
                        :disabled="isSubmitting"
                        class="w-full py-3 bg-emerald-600 text-white font-medium rounded-xl disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Opening...' : 'Start Shift' }}
                    </button>
                </div>
            </div>
        </Teleport>
    </GrowBizLayout>
</template>
