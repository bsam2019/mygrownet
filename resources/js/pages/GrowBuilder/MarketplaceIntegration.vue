<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    ArrowLeftIcon,
    ShoppingBagIcon,
    CheckCircleIcon,
    XCircleIcon,
    SparklesIcon,
    ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';

interface Site {
    id: number;
    name: string;
    subdomain: string;
    status: string;
    marketplace_enabled: boolean;
    marketplace_seller_id: number | null;
    marketplace_linked_at: string | null;
}

interface Seller {
    id: number;
    business_name: string;
    trust_level: string;
    trust_label: string;
    is_active: boolean;
    total_products: number;
    total_orders: number;
}

interface IntegrationStatus {
    enabled: boolean;
    has_seller: boolean;
    seller: Seller | null;
    linked_at: string | null;
    can_enable: boolean;
}

const props = defineProps<{
    site: Site;
    integrationStatus: IntegrationStatus;
}>();

const processing = ref(false);

const enableIntegration = () => {
    if (processing.value) return;
    processing.value = true;
    
    router.post(
        route('growbuilder.marketplace.enable', props.site.id),
        {},
        {
            onFinish: () => {
                processing.value = false;
            },
        }
    );
};

const disableIntegration = () => {
    if (processing.value) return;
    
    if (!confirm('Are you sure you want to disable marketplace integration? Your products will remain on the marketplace, but they won\'t be linked to this site.')) {
        return;
    }
    
    processing.value = true;
    
    router.post(
        route('growbuilder.marketplace.disable', props.site.id),
        {},
        {
            onFinish: () => {
                processing.value = false;
            },
        }
    );
};
</script>

<template>
    <AppLayout>
        <Head :title="`Marketplace Integration - ${site.name}`" />

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.sites.settings', site.id)"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Settings
                    </Link>

                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl">
                            <ShoppingBagIcon class="h-6 w-6 text-white" aria-hidden="true" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Marketplace Integration</h1>
                            <p class="text-sm text-gray-500">Sell your products on MyGrowNet Marketplace</p>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4">
                            <div :class="[
                                'p-3 rounded-xl',
                                integrationStatus.enabled ? 'bg-green-100' : 'bg-gray-100'
                            ]">
                                <component
                                    :is="integrationStatus.enabled ? CheckCircleIcon : XCircleIcon"
                                    :class="[
                                        'h-6 w-6',
                                        integrationStatus.enabled ? 'text-green-600' : 'text-gray-400'
                                    ]"
                                    aria-hidden="true"
                                />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">
                                    {{ integrationStatus.enabled ? 'Integration Active' : 'Integration Inactive' }}
                                </h2>
                                <p class="text-sm text-gray-600 mt-1">
                                    <template v-if="integrationStatus.enabled">
                                        Your site is connected to the marketplace. Products from your shop will be available on your website.
                                    </template>
                                    <template v-else>
                                        Connect your site to the marketplace to display and sell products directly from your website.
                                    </template>
                                </p>
                                
                                <div v-if="integrationStatus.enabled && integrationStatus.linked_at" class="mt-2 text-xs text-gray-500">
                                    Connected {{ new Date(integrationStatus.linked_at).toLocaleDateString() }}
                                </div>
                            </div>
                        </div>

                        <button
                            v-if="integrationStatus.enabled"
                            type="button"
                            @click="disableIntegration"
                            :disabled="processing"
                            class="px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-50"
                        >
                            {{ processing ? 'Disabling...' : 'Disable' }}
                        </button>
                        <button
                            v-else
                            type="button"
                            @click="enableIntegration"
                            :disabled="processing || !integrationStatus.can_enable"
                            class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ processing ? 'Enabling...' : 'Enable Integration' }}
                        </button>
                    </div>
                </div>

                <!-- Seller Info (if has seller) -->
                <div v-if="integrationStatus.has_seller && integrationStatus.seller" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Marketplace Shop</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <div class="text-sm text-gray-600">Business Name</div>
                            <div class="text-lg font-semibold text-gray-900 mt-1">
                                {{ integrationStatus.seller.business_name }}
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <div class="text-sm text-gray-600">Trust Level</div>
                            <div class="text-lg font-semibold text-gray-900 mt-1">
                                {{ integrationStatus.seller.trust_label }}
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <div class="text-sm text-gray-600">Status</div>
                            <div class="text-lg font-semibold mt-1" :class="integrationStatus.seller.is_active ? 'text-green-600' : 'text-red-600'">
                                {{ integrationStatus.seller.is_active ? 'Active' : 'Inactive' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="p-4 bg-blue-50 rounded-xl">
                            <div class="text-sm text-blue-600">Total Products</div>
                            <div class="text-2xl font-bold text-blue-900 mt-1">
                                {{ integrationStatus.seller.total_products || 0 }}
                            </div>
                        </div>
                        <div class="p-4 bg-green-50 rounded-xl">
                            <div class="text-sm text-green-600">Total Orders</div>
                            <div class="text-2xl font-bold text-green-900 mt-1">
                                {{ integrationStatus.seller.total_orders || 0 }}
                            </div>
                        </div>
                    </div>

                    <Link
                        :href="route('marketplace.seller.dashboard')"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                    >
                        <ShoppingBagIcon class="h-4 w-4" aria-hidden="true" />
                        Manage Marketplace Shop
                        <ArrowTopRightOnSquareIcon class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>

                <!-- How It Works -->
                <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-6 border border-purple-200">
                    <div class="flex items-start gap-3 mb-4">
                        <SparklesIcon class="h-6 w-6 text-purple-600 flex-shrink-0" aria-hidden="true" />
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">How It Works</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Connect your GrowBuilder site to the MyGrowNet Marketplace
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-purple-600 text-white rounded-full flex items-center justify-center text-xs font-bold">
                                1
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Enable Integration</div>
                                <div class="text-sm text-gray-600">
                                    Click "Enable Integration" to connect your site. If you don't have a marketplace shop, one will be created automatically.
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-purple-600 text-white rounded-full flex items-center justify-center text-xs font-bold">
                                2
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Add Products</div>
                                <div class="text-sm text-gray-600">
                                    Manage your products through the Marketplace Seller Dashboard. All products will be available on both your site and the marketplace.
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-purple-600 text-white rounded-full flex items-center justify-center text-xs font-bold">
                                3
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Display on Your Site</div>
                                <div class="text-sm text-gray-600">
                                    Add a "Products" section to your site pages in the editor. Your marketplace products will automatically appear.
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-purple-600 text-white rounded-full flex items-center justify-center text-xs font-bold">
                                4
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Start Selling</div>
                                <div class="text-sm text-gray-600">
                                    Customers can purchase from your branded site or the marketplace. All orders are managed through your seller dashboard.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requirements Notice -->
                <div v-if="!integrationStatus.can_enable && !integrationStatus.enabled" class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                    <div class="flex items-start gap-3">
                        <XCircleIcon class="h-5 w-5 text-yellow-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                        <div>
                            <div class="font-medium text-yellow-900">Site Must Be Published</div>
                            <div class="text-sm text-yellow-700 mt-1">
                                Your site must be published before you can enable marketplace integration. 
                                <Link :href="route('growbuilder.sites.show', site.id)" class="underline hover:text-yellow-900">
                                    Publish your site
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
