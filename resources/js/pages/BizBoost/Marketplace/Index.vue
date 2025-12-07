<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    BuildingStorefrontIcon,
    GlobeAltIcon,
    EyeIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowTopRightOnSquareIcon,
    SparklesIcon,
    ShoppingBagIcon,
    PhoneIcon,
    MapPinIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline';

interface Settings {
    is_listed: boolean;
    listing_title: string | null;
    listing_description: string | null;
    listing_category: string | null;
    featured_products: number[];
    show_contact: boolean;
    show_location: boolean;
}

interface Props {
    settings: Settings;
    business: {
        name: string;
        slug: string;
        logo_url: string | null;
    };
    categories: string[];
    hasMarketplaceAccess: boolean;
}

const props = defineProps<Props>();

const form = useForm({
    marketplace_description: props.settings.listing_description || '',
    show_products: true,
    show_contact: props.settings.show_contact,
});

const isToggling = ref(false);

const toggleListing = () => {
    isToggling.value = true;
    router.post('/bizboost/marketplace/toggle', {}, {
        onFinish: () => {
            isToggling.value = false;
        },
    });
};

const updateListing = () => {
    form.put('/bizboost/marketplace/listing');
};
</script>

<template>
    <Head title="Marketplace Settings - BizBoost" />
    <BizBoostLayout title="Marketplace">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header Card -->
            <div class="bg-gradient-to-br from-violet-600 via-violet-700 to-indigo-700 rounded-2xl p-6 md:p-8 text-white relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <defs>
                            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                                <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                            </pattern>
                        </defs>
                        <rect width="100" height="100" fill="url(#grid)" />
                    </svg>
                </div>
                
                <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <BuildingStorefrontIcon class="h-8 w-8 text-white" aria-hidden="true" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">MyGrowNet Marketplace</h1>
                            <p class="text-violet-100 mt-1">
                                Get discovered by thousands of potential customers
                            </p>
                        </div>
                    </div>
                    <a
                        href="/marketplace"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-violet-700 font-semibold rounded-xl hover:bg-violet-50 transition-colors shadow-lg"
                    >
                        <GlobeAltIcon class="h-5 w-5" aria-hidden="true" />
                        Browse Marketplace
                        <ArrowTopRightOnSquareIcon class="h-4 w-4" aria-hidden="true" />
                    </a>
                </div>
            </div>

            <!-- Listing Status Card -->
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Listing Status</h2>
                </div>
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div
                                :class="[
                                    'w-14 h-14 rounded-xl flex items-center justify-center',
                                    settings.is_listed 
                                        ? 'bg-emerald-100' 
                                        : 'bg-gray-100'
                                ]"
                            >
                                <CheckCircleIcon 
                                    v-if="settings.is_listed" 
                                    class="h-7 w-7 text-emerald-600" 
                                    aria-hidden="true" 
                                />
                                <XCircleIcon 
                                    v-else 
                                    class="h-7 w-7 text-gray-400" 
                                    aria-hidden="true" 
                                />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-lg">
                                    {{ settings.is_listed ? 'Listed on Marketplace' : 'Not Listed' }}
                                </p>
                                <p class="text-gray-500">
                                    {{ settings.is_listed 
                                        ? 'Your business is visible to all MyGrowNet members' 
                                        : 'Your business is not visible on the marketplace' 
                                    }}
                                </p>
                            </div>
                        </div>
                        <button
                            @click="toggleListing"
                            :disabled="!hasMarketplaceAccess || isToggling"
                            :class="[
                                'px-6 py-3 rounded-xl text-sm font-semibold transition-all shadow-sm',
                                settings.is_listed
                                    ? 'bg-red-50 text-red-700 hover:bg-red-100 ring-1 ring-red-200'
                                    : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-emerald-500/30',
                                (!hasMarketplaceAccess || isToggling) && 'opacity-50 cursor-not-allowed'
                            ]"
                        >
                            <span v-if="isToggling">Processing...</span>
                            <span v-else>{{ settings.is_listed ? 'Remove Listing' : 'List My Business' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upgrade Notice -->
            <div v-if="!hasMarketplaceAccess" class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-6">
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <SparklesIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-amber-900">Upgrade to List Your Business</h3>
                        <p class="text-amber-700 mt-1">
                            Marketplace listing is available on Professional and Business tiers. Upgrade now to get discovered by thousands of potential customers.
                        </p>
                        <Link
                            href="/bizboost/upgrade"
                            class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700 transition-colors shadow-lg shadow-amber-500/30"
                        >
                            Upgrade Now
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Listing Details Form -->
            <form 
                v-if="hasMarketplaceAccess" 
                @submit.prevent="updateListing" 
                class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden"
            >
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Listing Details</h2>
                    <p class="text-gray-500 text-sm mt-1">Customize how your business appears on the marketplace</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Marketplace Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.marketplace_description"
                            rows="4"
                            class="w-full rounded-xl border-gray-200 focus:border-violet-500 focus:ring-violet-500 resize-none"
                            placeholder="Tell potential customers what makes your business special..."
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                            <InformationCircleIcon class="h-4 w-4" aria-hidden="true" />
                            This description will be shown on your marketplace listing
                        </p>
                    </div>

                    <!-- Visibility Options -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-medium text-gray-900">Visibility Options</h3>
                        
                        <label class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 hover:border-violet-200 hover:bg-violet-50/50 transition-colors cursor-pointer">
                            <input
                                id="show_products"
                                v-model="form.show_products"
                                type="checkbox"
                                class="h-5 w-5 rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                            />
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <ShoppingBagIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                    <span class="font-medium text-gray-900">Show Products</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-0.5">Display your products on your marketplace listing</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 hover:border-violet-200 hover:bg-violet-50/50 transition-colors cursor-pointer">
                            <input
                                id="show_contact"
                                v-model="form.show_contact"
                                type="checkbox"
                                class="h-5 w-5 rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                            />
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <PhoneIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                    <span class="font-medium text-gray-900">Show Contact Information</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-0.5">Allow customers to see your phone and email</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-semibold hover:bg-violet-700 disabled:opacity-50 transition-colors shadow-lg shadow-violet-500/30"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>

            <!-- Preview Card -->
            <div v-if="settings.is_listed" class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Your Marketplace Profile</h2>
                </div>
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl bg-violet-100 flex items-center justify-center overflow-hidden">
                                <img
                                    v-if="business.logo_url"
                                    :src="business.logo_url"
                                    :alt="business.name"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-xl font-bold text-violet-600">{{ business.name[0] }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ business.name }}</p>
                                <p class="text-sm text-gray-500">mygrownet.com/biz/{{ business.slug }}</p>
                            </div>
                        </div>
                        <a
                            :href="`/biz/${business.slug}`"
                            target="_blank"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-violet-50 text-violet-700 rounded-xl text-sm font-semibold hover:bg-violet-100 transition-colors"
                        >
                            <EyeIcon class="h-5 w-5" aria-hidden="true" />
                            Preview Listing
                            <ArrowTopRightOnSquareIcon class="h-4 w-4" aria-hidden="true" />
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="bg-gradient-to-br from-indigo-50 to-violet-50 rounded-2xl p-6 border border-indigo-100">
                <h3 class="font-semibold text-indigo-900 mb-3">Tips for a Great Listing</h3>
                <ul class="space-y-2 text-sm text-indigo-700">
                    <li class="flex items-start gap-2">
                        <CheckCircleIcon class="h-5 w-5 text-indigo-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                        <span>Add a compelling description that highlights what makes your business unique</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <CheckCircleIcon class="h-5 w-5 text-indigo-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                        <span>Upload high-quality product images to attract more customers</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <CheckCircleIcon class="h-5 w-5 text-indigo-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                        <span>Keep your contact information up to date for easy customer reach</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <CheckCircleIcon class="h-5 w-5 text-indigo-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                        <span>Complete your business profile for better visibility in search results</span>
                    </li>
                </ul>
            </div>
        </div>
    </BizBoostLayout>
</template>
