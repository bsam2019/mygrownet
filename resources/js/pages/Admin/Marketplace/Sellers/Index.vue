<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    MagnifyingGlassIcon,
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
    EyeIcon,
    BuildingStorefrontIcon,
} from '@heroicons/vue/24/outline';

interface Seller {
    id: number;
    business_name: string;
    business_type: string;
    province: string;
    district: string;
    phone: string;
    kyc_status: string;
    is_active: boolean;
    rating: number;
    total_orders: number;
    created_at: string;
    user: {
        name: string;
        email: string;
    };
}

interface Filters {
    status?: string;
    search?: string;
}

const props = defineProps<{
    sellers: {
        data: Seller[];
        links: any[];
        meta: any;
    };
    filters: Filters;
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const applyFilters = () => {
    router.get(route('admin.marketplace.sellers.index'), {
        status: statusFilter.value,
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getStatusBadge = (status: string) => {
    return {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
    }[status] || 'bg-gray-100 text-gray-800';
};

const getStatusIcon = (status: string) => {
    return {
        'pending': ClockIcon,
        'approved': CheckCircleIcon,
        'rejected': XCircleIcon,
    }[status] || ClockIcon;
};
</script>

<template>
    <Head title="Sellers - Marketplace Admin" />
    
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <Link :href="route('admin.marketplace.dashboard')" class="text-sm text-gray-500 hover:text-gray-700 mb-1 block">
                            ← Back to Dashboard
                        </Link>
                        <h1 class="text-2xl font-bold text-gray-900">Seller Management</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Filters -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                            <input
                                v-model="search"
                                @keyup.enter="applyFilters"
                                type="text"
                                placeholder="Search by business name, owner name, or email..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            />
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            v-model="statusFilter"
                            @change="applyFilters"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Sellers Table -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="sellers.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No sellers found
                            </td>
                        </tr>
                        <tr v-for="seller in sellers.data" :key="seller.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="font-medium text-gray-900">{{ seller.business_name }}</p>
                                    <p class="text-sm text-gray-500">{{ seller.business_type }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="text-sm text-gray-900">{{ seller.user.name }}</p>
                                    <p class="text-sm text-gray-500">{{ seller.user.email }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="text-sm text-gray-900">{{ seller.district }}</p>
                                    <p class="text-sm text-gray-500">{{ seller.province }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusBadge(seller.kyc_status)]">
                                    <component :is="getStatusIcon(seller.kyc_status)" class="h-3 w-3" aria-hidden="true" />
                                    {{ seller.kyc_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ seller.total_orders }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ seller.rating.toFixed(1) }} ⭐
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <Link
                                        :href="route('admin.marketplace.sellers.show', seller.id)"
                                        class="inline-flex items-center gap-1 text-orange-600 hover:text-orange-900"
                                    >
                                        <EyeIcon class="h-4 w-4" aria-hidden="true" />
                                        View
                                    </Link>
                                    <Link
                                        :href="route('marketplace.seller.show', seller.id)"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-900"
                                    >
                                        <BuildingStorefrontIcon class="h-4 w-4" aria-hidden="true" />
                                        Shop
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="sellers.links.length > 3" class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ sellers.meta.from }} to {{ sellers.meta.to }} of {{ sellers.meta.total }} results
                        </div>
                        <div class="flex gap-1">
                            <Link
                                v-for="(link, index) in sellers.links"
                                :key="index"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1 text-sm rounded',
                                    link.active ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
