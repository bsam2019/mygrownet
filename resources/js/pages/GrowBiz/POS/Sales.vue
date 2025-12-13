<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    MagnifyingGlassIcon,
    FunnelIcon,
    EyeIcon,
    XCircleIcon,
    BanknotesIcon,
    DevicePhoneMobileIcon,
    CreditCardIcon,
    ClockIcon,
    ReceiptPercentIcon,
} from '@heroicons/vue/24/outline';

interface SaleItem {
    id: number;
    product_name: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Sale {
    id: number;
    sale_number: string;
    customer_name: string | null;
    subtotal: number;
    discount_amount: number;
    tax_amount: number;
    total_amount: number;
    amount_paid: number;
    change_amount: number;
    payment_method: string;
    status: string;
    created_at: string;
    items: SaleItem[];
}

interface Props {
    sales: Sale[];
    filters: {
        date?: string;
        date_from?: string;
        date_to?: string;
        payment_method?: string;
        search?: string;
    };
    todayStats: {
        total_sales: number;
        total_revenue: number;
        total_transactions: number;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const paymentMethod = ref(props.filters.payment_method || '');
const showFilters = ref(false);
const selectedSale = ref<Sale | null>(null);
const showSaleModal = ref(false);

const filteredSales = computed(() => {
    let result = props.sales;
    if (search.value) {
        const q = search.value.toLowerCase();
        result = result.filter(s => 
            s.sale_number.toLowerCase().includes(q) ||
            (s.customer_name && s.customer_name.toLowerCase().includes(q))
        );
    }
    return result;
});

const applyFilters = () => {
    router.get(route('growbiz.pos.sales'), {
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        payment_method: paymentMethod.value || undefined,
        search: search.value || undefined,
    }, { preserveState: true });
};

const clearFilters = () => {
    search.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    paymentMethod.value = '';
    router.get(route('growbiz.pos.sales'));
};

const viewSale = (sale: Sale) => {
    selectedSale.value = sale;
    showSaleModal.value = true;
};

const voidSale = (sale: Sale) => {
    if (confirm(`Void sale #${sale.sale_number}? This cannot be undone.`)) {
        router.post(route('growbiz.pos.sales.void', sale.id), {
            reason: 'Voided by user',
        });
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW' }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-ZM', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};

const getPaymentIcon = (method: string) => {
    switch (method) {
        case 'cash': return BanknotesIcon;
        case 'mobile_money': return DevicePhoneMobileIcon;
        case 'card': return CreditCardIcon;
        default: return BanknotesIcon;
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed': return 'bg-green-100 text-green-800';
        case 'voided': return 'bg-red-100 text-red-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <GrowBizLayout>
        <Head title="Sales History - POS" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Sales History</h1>
                    <p class="text-sm text-gray-500 mt-1">View and manage your sales transactions</p>
                </div>
                <Link
                    :href="route('growbiz.pos.index')"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    <ReceiptPercentIcon class="w-5 h-5" aria-hidden="true" />
                    <span>New Sale</span>
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-xs sm:text-sm text-gray-500">Today's Sales</p>
                    <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ todayStats.total_transactions }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-xs sm:text-sm text-gray-500">Revenue</p>
                    <p class="text-lg sm:text-2xl font-bold text-green-600">{{ formatCurrency(todayStats.total_revenue) }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-xs sm:text-sm text-gray-500">Avg Sale</p>
                    <p class="text-lg sm:text-2xl font-bold text-gray-900">
                        {{ formatCurrency(todayStats.total_transactions > 0 ? todayStats.total_revenue / todayStats.total_transactions : 0) }}
                    </p>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by sale # or customer..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <button
                        @click="showFilters = !showFilters"
                        class="flex items-center justify-center gap-2 px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50"
                    >
                        <FunnelIcon class="w-5 h-5" />
                        <span>Filters</span>
                    </button>
                </div>

                <!-- Filter Panel -->
                <div v-if="showFilters" class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input
                            v-model="dateFrom"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input
                            v-model="dateTo"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <select
                            v-model="paymentMethod"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                        >
                            <option value="">All Methods</option>
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="card">Card</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="sm:col-span-3 flex gap-2">
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            Apply Filters
                        </button>
                        <button
                            @click="clearFilters"
                            class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sales List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="filteredSales.length === 0" class="p-8 text-center">
                    <ReceiptPercentIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-500">No sales found</p>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-for="sale in filteredSales"
                        :key="sale.id"
                        class="p-4 hover:bg-gray-50 cursor-pointer"
                        @click="viewSale(sale)"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <component :is="getPaymentIcon(sale.payment_method)" class="w-5 h-5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ sale.sale_number }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ sale.customer_name || 'Walk-in Customer' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ formatCurrency(sale.total_amount) }}</p>
                                <span :class="['text-xs px-2 py-0.5 rounded-full', getStatusColor(sale.status)]">
                                    {{ sale.status }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <ClockIcon class="w-4 h-4" />
                                {{ formatDate(sale.created_at) }}
                            </span>
                            <span>{{ sale.items?.length || 0 }} items</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sale Detail Modal -->
        <div v-if="showSaleModal && selectedSale" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-end sm:items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="showSaleModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Sale {{ selectedSale.sale_number }}</h3>
                        <button @click="showSaleModal = false" class="p-1 hover:bg-gray-100 rounded-full">
                            <XCircleIcon class="w-6 h-6 text-gray-400" />
                        </button>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Date</span>
                            <span>{{ formatDate(selectedSale.created_at) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Customer</span>
                            <span>{{ selectedSale.customer_name || 'Walk-in' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Payment</span>
                            <span class="capitalize">{{ selectedSale.payment_method.replace('_', ' ') }}</span>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Items</p>
                            <div class="space-y-2">
                                <div
                                    v-for="item in selectedSale.items"
                                    :key="item.id"
                                    class="flex justify-between text-sm"
                                >
                                    <span>{{ item.product_name }} × {{ item.quantity }}</span>
                                    <span>{{ formatCurrency(item.total) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Subtotal</span>
                                <span>{{ formatCurrency(selectedSale.subtotal) }}</span>
                            </div>
                            <div v-if="selectedSale.discount_amount > 0" class="flex justify-between text-sm">
                                <span class="text-gray-500">Discount</span>
                                <span class="text-red-600">-{{ formatCurrency(selectedSale.discount_amount) }}</span>
                            </div>
                            <div v-if="selectedSale.tax_amount > 0" class="flex justify-between text-sm">
                                <span class="text-gray-500">Tax</span>
                                <span>{{ formatCurrency(selectedSale.tax_amount) }}</span>
                            </div>
                            <div class="flex justify-between font-semibold">
                                <span>Total</span>
                                <span>{{ formatCurrency(selectedSale.total_amount) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-100 flex gap-2">
                        <button
                            v-if="selectedSale.status === 'completed'"
                            @click="voidSale(selectedSale)"
                            class="flex-1 px-4 py-2 border border-red-200 text-red-600 rounded-lg hover:bg-red-50"
                        >
                            Void Sale
                        </button>
                        <button
                            @click="showSaleModal = false"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GrowBizLayout>
</template>
