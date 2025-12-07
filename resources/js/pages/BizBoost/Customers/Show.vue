<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    ArrowLeftIcon,
    PencilIcon,
    TrashIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    CalendarIcon,
    CurrencyDollarIcon,
    ShoppingBagIcon,
    TagIcon,
    ChatBubbleLeftIcon,
} from '@heroicons/vue/24/outline';
import Swal from 'sweetalert2';

interface Sale {
    id: number;
    product?: { name: string };
    quantity: number;
    total_amount: number;
    sale_date: string;
}

interface Tag {
    id: number;
    name: string;
    color: string;
}

interface Customer {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    whatsapp: string | null;
    address: string | null;
    notes: string | null;
    source: string | null;
    birthday: string | null;
    total_spent: number;
    total_orders: number;
    last_purchase_at: string | null;
    is_active: boolean;
    created_at: string;
    tags: Tag[];
    sales: Sale[];
}

interface Props {
    customer: Customer;
}

const props = defineProps<Props>();

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-ZM', { minimumFractionDigits: 2 })}`;
};

const openWhatsApp = () => {
    if (props.customer.whatsapp) {
        const phone = props.customer.whatsapp.replace(/\D/g, '');
        window.open(`https://wa.me/${phone}`, '_blank');
    }
};

const deleteCustomer = () => {
    Swal.fire({
        title: 'Delete Customer?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('bizboost.customers.destroy', props.customer.id));
        }
    });
};
</script>

<template>
    <Head :title="`${customer.name} - Customers`" />
    <BizBoostLayout :title="customer.name">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <Link
                    :href="route('bizboost.customers.index')"
                    class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Customers
                </Link>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('bizboost.customers.edit', customer.id)"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <PencilIcon class="h-4 w-4" aria-hidden="true" />
                        Edit
                    </Link>
                    <button
                        @click="deleteCustomer"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm font-medium text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors"
                    >
                        <TrashIcon class="h-4 w-4" aria-hidden="true" />
                        Delete
                    </button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Customer Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-200/60 dark:ring-gray-700/60 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ customer.name }}</h2>
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium mt-2',
                                        customer.is_active 
                                            ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                                    ]"
                                >
                                    {{ customer.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div v-if="customer.whatsapp" class="flex gap-2">
                                <button
                                    @click="openWhatsApp"
                                    class="p-2.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-xl hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors"
                                    aria-label="Open WhatsApp chat"
                                >
                                    <ChatBubbleLeftIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div v-if="customer.phone" class="flex items-center gap-3">
                                <div class="p-2.5 bg-gray-100 dark:bg-gray-700 rounded-xl">
                                    <PhoneIcon class="h-4 w-4 text-gray-600 dark:text-gray-400" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Phone</p>
                                    <a :href="`tel:${customer.phone}`" class="text-sm text-gray-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400">
                                        {{ customer.phone }}
                                    </a>
                                </div>
                            </div>
                            <div v-if="customer.email" class="flex items-center gap-3">
                                <div class="p-2.5 bg-gray-100 dark:bg-gray-700 rounded-xl">
                                    <EnvelopeIcon class="h-4 w-4 text-gray-600 dark:text-gray-400" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                                    <a :href="`mailto:${customer.email}`" class="text-sm text-gray-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400">
                                        {{ customer.email }}
                                    </a>
                                </div>
                            </div>
                            <div v-if="customer.address" class="flex items-center gap-3 sm:col-span-2">
                                <div class="p-2.5 bg-gray-100 dark:bg-gray-700 rounded-xl">
                                    <MapPinIcon class="h-4 w-4 text-gray-600 dark:text-gray-400" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Address</p>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ customer.address }}</p>
                                </div>
                            </div>
                            <div v-if="customer.birthday" class="flex items-center gap-3">
                                <div class="p-2.5 bg-gray-100 dark:bg-gray-700 rounded-xl">
                                    <CalendarIcon class="h-4 w-4 text-gray-600 dark:text-gray-400" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Birthday</p>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(customer.birthday) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div v-if="customer.tags?.length" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2 flex-wrap">
                                <TagIcon class="h-4 w-4 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                                <span
                                    v-for="tag in customer.tags"
                                    :key="tag.id"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium"
                                    :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                                >
                                    {{ tag.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="customer.notes" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Notes</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ customer.notes }}</p>
                        </div>
                    </div>

                    <!-- Recent Sales -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-200/60 dark:ring-gray-700/60 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Purchases</h3>
                        <div v-if="customer.sales?.length" class="space-y-3">
                            <div
                                v-for="sale in customer.sales"
                                :key="sale.id"
                                class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-violet-100 dark:bg-violet-900/30 rounded-xl">
                                        <ShoppingBagIcon class="h-4 w-4 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ sale.product?.name || 'Product' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Qty: {{ sale.quantity }} • {{ formatDate(sale.sale_date) }}
                                        </p>
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatCurrency(sale.total_amount) }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                            No purchases recorded yet.
                        </p>
                    </div>
                </div>

                <!-- Sidebar Stats -->
                <div class="space-y-6">
                    <!-- Stats Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-200/60 dark:ring-gray-700/60 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Customer Stats</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <CurrencyDollarIcon class="h-5 w-5 text-green-600 dark:text-green-400" aria-hidden="true" />
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Spent</span>
                                </div>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(customer.total_spent) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <ShoppingBagIcon class="h-5 w-5 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Orders</span>
                                </div>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ customer.total_orders }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <CalendarIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" aria-hidden="true" />
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Last Purchase</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatDate(customer.last_purchase_at) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Info -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-200/60 dark:ring-gray-700/60 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Details</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Source</span>
                                <span class="text-gray-900 dark:text-white">{{ customer.source || '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Added</span>
                                <span class="text-gray-900 dark:text-white">{{ formatDate(customer.created_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-gradient-to-br from-violet-600 to-violet-700 dark:from-violet-700 dark:to-violet-800 rounded-2xl p-5 text-white shadow-lg shadow-violet-500/20">
                        <h3 class="text-sm font-semibold mb-3">Quick Actions</h3>
                        <div class="space-y-2">
                            <Link
                                :href="route('bizboost.sales.create') + `?customer_id=${customer.id}`"
                                class="flex items-center gap-2 w-full p-2.5 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-medium transition-colors"
                            >
                                <CurrencyDollarIcon class="h-4 w-4" aria-hidden="true" />
                                Record Sale
                            </Link>
                            <button
                                v-if="customer.whatsapp"
                                @click="openWhatsApp"
                                class="flex items-center gap-2 w-full p-2.5 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-medium transition-colors"
                            >
                                <ChatBubbleLeftIcon class="h-4 w-4" aria-hidden="true" />
                                Send WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
