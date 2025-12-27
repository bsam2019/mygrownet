<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    PlusIcon,
    GlobeAltIcon,
    PencilSquareIcon,
    Cog6ToothIcon,
    EyeIcon,
    ChartBarIcon,
    ShoppingBagIcon,
    CurrencyDollarIcon,
    UsersIcon,
    ArrowTrendingUpIcon,
    ClockIcon,
    SparklesIcon,
    ArrowUpCircleIcon,
} from '@heroicons/vue/24/outline';

interface Site {
    id: number;
    name: string;
    subdomain: string;
    customDomain: string | null;
    status: 'draft' | 'published' | 'suspended';
    plan: string;
    url: string;
    isPublished: boolean;
    createdAt: string;
    pageViews?: number;
    ordersCount?: number;
    revenue?: number;
    thumbnail?: string;
}

interface Stats {
    totalSites: number;
    publishedSites: number;
    totalPageViews: number;
    totalOrders: number;
    totalRevenue: number;
}

interface Subscription {
    tier: string;
    tierName: string;
    sitesLimit: number;
    sitesUsed: number;
    canCreateSite: boolean;
    expiresAt: string | null;
}

const props = defineProps<{
    sites: Site[];
    stats?: Stats;
    subscription?: Subscription;
}>();

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'published':
            return 'bg-green-100 text-green-800';
        case 'draft':
            return 'bg-yellow-100 text-yellow-800';
        case 'suspended':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getPlanBadge = (plan: string) => {
    switch (plan) {
        case 'pro':
            return 'bg-purple-100 text-purple-800';
        case 'business':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const formatCurrency = (amount: number) => `K${(amount / 100).toLocaleString('en-ZM', { minimumFractionDigits: 2 })}`;
const formatNumber = (num: number) => num.toLocaleString();
const formatDate = (date: string) => new Date(date).toLocaleDateString('en-ZM', { month: 'short', day: 'numeric', year: 'numeric' });
</script>

<template>
    <AppLayout>
        <Head title="GrowBuilder - My Websites" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">GrowBuilder</h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Build beautiful websites for your business
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('growbuilder.subscription.index')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-indigo-600 font-medium rounded-xl hover:bg-indigo-50 transition"
                        >
                            <SparklesIcon class="h-5 w-5" aria-hidden="true" />
                            <span class="hidden sm:inline">{{ subscription?.tierName || 'Free' }} Plan</span>
                        </Link>
                        <Link
                            v-if="subscription?.canCreateSite !== false"
                            :href="route('growbuilder.sites.create')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 shadow-sm transition"
                        >
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Create Website
                        </Link>
                        <Link
                            v-else
                            :href="route('growbuilder.subscription.index')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 shadow-sm transition"
                        >
                            <ArrowUpCircleIcon class="h-5 w-5" aria-hidden="true" />
                            Upgrade to Create More
                        </Link>
                    </div>
                </div>

                <!-- Subscription Banner (when at limit) -->
                <div v-if="subscription && !subscription.canCreateSite" class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-4 mb-6 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <SparklesIcon class="h-6 w-6" aria-hidden="true" />
                        <div>
                            <p class="font-medium">You've reached your site limit ({{ subscription.sitesUsed }}/{{ subscription.sitesLimit }})</p>
                            <p class="text-sm text-indigo-100">Upgrade your plan to create more websites</p>
                        </div>
                    </div>
                    <Link :href="route('growbuilder.subscription.index')" class="px-4 py-2 bg-white text-indigo-600 font-medium rounded-lg hover:bg-indigo-50 transition">
                        View Plans
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div v-if="stats && sites.length > 0" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-blue-100 rounded-lg">
                                <GlobeAltIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.publishedSites }}</p>
                                <p class="text-sm text-gray-500">Live Sites</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-green-100 rounded-lg">
                                <ChartBarIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.totalPageViews) }}</p>
                                <p class="text-sm text-gray-500">Page Views</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-purple-100 rounded-lg">
                                <ShoppingBagIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.totalOrders) }}</p>
                                <p class="text-sm text-gray-500">Orders</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-emerald-100 rounded-lg">
                                <CurrencyDollarIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.totalRevenue) }}</p>
                                <p class="text-sm text-gray-500">Revenue</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="sites.length === 0" class="text-center py-20 bg-white rounded-2xl border-2 border-dashed border-gray-200">
                    <div class="w-20 h-20 mx-auto bg-blue-100 rounded-full flex items-center justify-center mb-6">
                        <GlobeAltIcon class="h-10 w-10 text-blue-600" aria-hidden="true" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Create your first website</h3>
                    <p class="text-gray-500 mb-8 max-w-md mx-auto">
                        Build a professional website for your business in minutes. No coding required.
                    </p>
                    <Link
                        :href="route('growbuilder.sites.create')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 shadow-sm transition"
                    >
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Get Started
                    </Link>
                    
                    <!-- Features -->
                    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl mx-auto text-left">
                        <div class="p-4">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mb-3">
                                <ArrowTrendingUpIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                            </div>
                            <h4 class="font-medium text-gray-900 mb-1">Easy to Use</h4>
                            <p class="text-sm text-gray-500">Drag and drop builder with beautiful templates</p>
                        </div>
                        <div class="p-4">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                                <ShoppingBagIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                            </div>
                            <h4 class="font-medium text-gray-900 mb-1">Sell Online</h4>
                            <p class="text-sm text-gray-500">Accept MoMo & Airtel Money payments</p>
                        </div>
                        <div class="p-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                                <UsersIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                            </div>
                            <h4 class="font-medium text-gray-900 mb-1">Grow Your Business</h4>
                            <p class="text-sm text-gray-500">Analytics and tools to help you succeed</p>
                        </div>
                    </div>
                </div>

                <!-- Sites Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="site in sites"
                        :key="site.id"
                        class="group bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg hover:border-blue-200 transition-all duration-200"
                    >
                        <!-- Preview -->
                        <div class="relative h-44 bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-600 overflow-hidden">
                            <img
                                v-if="site.thumbnail"
                                :src="site.thumbnail"
                                :alt="site.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="absolute inset-0 flex items-center justify-center">
                                <span class="text-white/90 text-5xl font-bold">{{ site.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            
                            <!-- Quick Actions Overlay -->
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                <Link
                                    :href="route('growbuilder.editor', site.id)"
                                    class="p-3 bg-white rounded-full text-gray-900 hover:bg-blue-50 transition"
                                    aria-label="Edit site"
                                >
                                    <PencilSquareIcon class="h-5 w-5" aria-hidden="true" />
                                </Link>
                                <a
                                    v-if="site.isPublished"
                                    :href="site.url"
                                    target="_blank"
                                    class="p-3 bg-white rounded-full text-gray-900 hover:bg-blue-50 transition"
                                    aria-label="View live site"
                                >
                                    <EyeIcon class="h-5 w-5" aria-hidden="true" />
                                </a>
                                <Link
                                    :href="route('growbuilder.sites.settings', site.id)"
                                    class="p-3 bg-white rounded-full text-gray-900 hover:bg-blue-50 transition"
                                    aria-label="Site settings"
                                >
                                    <Cog6ToothIcon class="h-5 w-5" aria-hidden="true" />
                                </Link>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="absolute top-3 right-3">
                                <span :class="[getStatusBadge(site.status), 'px-2.5 py-1 text-xs font-semibold rounded-full capitalize shadow-sm']">
                                    {{ site.status }}
                                </span>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="font-semibold text-gray-900 text-lg">{{ site.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ site.subdomain }}.mygrownet.com</p>
                                </div>
                                <span :class="[getPlanBadge(site.plan), 'px-2 py-0.5 text-xs font-medium rounded-full capitalize']">
                                    {{ site.plan }}
                                </span>
                            </div>
                            
                            <!-- Site Stats -->
                            <Link
                                :href="route('growbuilder.sites.analytics', site.id)"
                                class="flex items-center gap-4 text-sm text-gray-500 mb-4 hover:text-blue-600 transition"
                            >
                                <div class="flex items-center gap-1">
                                    <ChartBarIcon class="h-4 w-4" aria-hidden="true" />
                                    <span>{{ formatNumber(site.pageViews || 0) }} views</span>
                                </div>
                                <div v-if="site.ordersCount" class="flex items-center gap-1">
                                    <ShoppingBagIcon class="h-4 w-4" aria-hidden="true" />
                                    <span>{{ site.ordersCount }} orders</span>
                                </div>
                            </Link>

                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('growbuilder.editor', site.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition"
                                >
                                    <PencilSquareIcon class="h-4 w-4" aria-hidden="true" />
                                    Edit Site
                                </Link>
                                <Link
                                    :href="route('growbuilder.products.index', site.id)"
                                    class="p-2.5 text-gray-600 hover:bg-gray-100 rounded-xl transition"
                                    aria-label="Manage products"
                                >
                                    <ShoppingBagIcon class="h-5 w-5" aria-hidden="true" />
                                </Link>
                            </div>
                            
                            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                                <div class="flex items-center gap-1">
                                    <ClockIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                    Created {{ formatDate(site.createdAt) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
