<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    ArrowLeftIcon,
    BuildingStorefrontIcon,
    DocumentTextIcon,
    MegaphoneIcon,
    CurrencyDollarIcon,
    SparklesIcon,
    WalletIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

interface Post {
    id: number;
    title: string;
    caption: string;
    status: string;
    created_at: string;
}

interface Sale {
    id: number;
    amount: number;
    product_name: string;
    payment_method: string;
    created_at: string;
}

interface Props {
    business: {
        id: number;
        name: string;
        slug: string;
        industry: string | null;
        description: string | null;
        phone: string | null;
        email: string | null;
        website: string | null;
        city: string | null;
        province: string | null;
        is_active: boolean;
        onboarding_completed: boolean;
        created_at: string;
        user_name: string;
        user_email: string;
        posts_count: number;
        campaigns_count: number;
        sales_count: number;
        products_count: number;
        customers_count: number;
        social_links: Record<string, string> | null;
    };
    recentPosts: Post[];
    recentSales: Sale[];
    aiUsage: {
        total_credits: number;
        total_calls: number;
        successful_calls: number;
    };
    wallet: {
        balance: number;
        locked_balance: number;
        available: number;
    } | null;
}

defineProps<Props>();
</script>

<template>
    <Head :title="`${business.name} - BizBoost Admin`" />
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <Link :href="route('admin.bizboost.businesses.index')" class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6 transition-colors">
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to Businesses
                </Link>

                <!-- Business Header -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6 mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-violet-100 to-fuchsia-100 dark:from-violet-900/40 dark:to-fuchsia-900/20 flex items-center justify-center text-xl font-bold text-violet-600 dark:text-violet-400 ring-1 ring-violet-200/50 dark:ring-violet-700/30">
                                {{ business.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ business.name }}</h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ business.industry || 'N/A' }} · {{ business.city || 'No location' }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span :class="['inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full', business.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400']">
                                        <CheckCircleIcon v-if="business.is_active" class="h-3 w-3" />
                                        <XCircleIcon v-else class="h-3 w-3" />
                                        {{ business.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', business.onboarding_completed ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400']">
                                        {{ business.onboarding_completed ? 'Onboarded' : 'Setup Pending' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right text-sm text-gray-500 dark:text-gray-400">
                            <p>Owner: <span class="font-medium text-gray-900 dark:text-white">{{ business.user_name }}</span></p>
                            <p>{{ business.user_email }}</p>
                            <p class="mt-1">Joined {{ business.created_at }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-4 text-center">
                        <DocumentTextIcon class="h-5 w-5 mx-auto text-blue-500 mb-1" />
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ business.posts_count }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Posts</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-4 text-center">
                        <MegaphoneIcon class="h-5 w-5 mx-auto text-amber-500 mb-1" />
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ business.campaigns_count }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Campaigns</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-4 text-center">
                        <CurrencyDollarIcon class="h-5 w-5 mx-auto text-green-500 mb-1" />
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ business.sales_count }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Sales</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-4 text-center">
                        <BuildingStorefrontIcon class="h-5 w-5 mx-auto text-violet-500 mb-1" />
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ business.products_count }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Products</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-4 text-center">
                        <SparklesIcon class="h-5 w-5 mx-auto text-purple-500 mb-1" />
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ aiUsage.total_credits }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">AI Credits</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-4 text-center">
                        <WalletIcon class="h-5 w-5 mx-auto text-teal-500 mb-1" />
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ wallet ? `$${wallet.available}` : 'N/A' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Wallet</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Posts -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Recent Posts</h2>
                        <div v-if="recentPosts.length === 0" class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No posts yet.</div>
                        <div v-else class="space-y-3">
                            <div v-for="p in recentPosts" :key="p.id" class="p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ p.title || 'Untitled' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">{{ p.caption }}</p>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span :class="['text-xs px-1.5 py-0.5 rounded-full font-medium capitalize', p.status === 'published' ? 'bg-green-100 text-green-700' : p.status === 'scheduled' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600']">
                                        {{ p.status }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ p.created_at }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Sales -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Recent Sales</h2>
                        <div v-if="recentSales.length === 0" class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No sales yet.</div>
                        <div v-else class="space-y-3">
                            <div v-for="s in recentSales" :key="s.id" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ s.product_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ s.payment_method }} · {{ s.created_at }}</p>
                                </div>
                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">${{ s.amount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
