<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, ChartBarIcon, CurrencyDollarIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline';

interface SalesByDay {
    date: string;
    total: number;
    count: number;
}

interface TopProduct {
    product_id: number;
    product_name: string;
    total_quantity: number;
    total_revenue: number;
}

interface Props {
    salesByDay: SalesByDay[];
    topProducts: TopProduct[];
    topCustomers: { customer_id: number; customer_name: string; total_spent: number }[];
    period: string;
}

const props = defineProps<Props>();

const formatPrice = (price: number) => `K${price.toLocaleString()}`;

const totalRevenue = props.salesByDay.reduce((sum, day) => sum + day.total, 0);
const totalSales = props.salesByDay.reduce((sum, day) => sum + day.count, 0);
</script>

<template>
    <Head title="Sales Reports - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.sales.index')" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Sales
                    </Link>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <ChartBarIcon class="h-7 w-7 text-blue-600" aria-hidden="true" />
                            Sales Reports
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">{{ period }}</p>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <CurrencyDollarIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-gray-900">{{ formatPrice(totalRevenue) }}</div>
                                <div class="text-sm text-gray-500">Total Revenue</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <ShoppingBagIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-gray-900">{{ totalSales }}</div>
                                <div class="text-sm text-gray-500">Total Sales</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <ChartBarIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-gray-900">{{ formatPrice(totalSales > 0 ? totalRevenue / totalSales : 0) }}</div>
                                <div class="text-sm text-gray-500">Average Sale</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Products -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-semibold text-gray-900">Top Products</h2>
                        </div>
                        <div v-if="topProducts.length === 0" class="p-8 text-center text-gray-500">
                            No sales data available.
                        </div>
                        <div v-else class="divide-y">
                            <div v-for="(product, index) in topProducts" :key="product.product_id" class="p-4 flex items-center gap-4">
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ product.product_name }}</div>
                                    <div class="text-sm text-gray-500">{{ product.total_quantity }} sold</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-gray-900">{{ formatPrice(product.total_revenue) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales by Day -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b">
                            <h2 class="text-lg font-semibold text-gray-900">Daily Sales</h2>
                        </div>
                        <div v-if="salesByDay.length === 0" class="p-8 text-center text-gray-500">
                            No sales data available.
                        </div>
                        <div v-else class="divide-y max-h-96 overflow-y-auto">
                            <div v-for="day in salesByDay" :key="day.date" class="p-4 flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">{{ day.date }}</div>
                                    <div class="text-sm text-gray-500">{{ day.count }} sales</div>
                                </div>
                                <div class="font-semibold text-gray-900">{{ formatPrice(day.total) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
