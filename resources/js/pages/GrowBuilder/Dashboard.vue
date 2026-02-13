<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CreateSiteWizard from '@/components/GrowBuilder/CreateSiteWizard.vue';
import WebsiteGeneratorModal from '@/pages/GrowBuilder/Editor/components/modals/WebsiteGeneratorModal.vue';
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
    CpuChipIcon,
    WrenchScrewdriverIcon,
    EnvelopeIcon,
    ChatBubbleLeftEllipsisIcon,
    ExclamationTriangleIcon,
    LightBulbIcon,
    PhotoIcon,
    MagnifyingGlassIcon,
    PhoneIcon,
    ShareIcon,
    DocumentIcon,
} from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';

interface SiteTemplatePage {
    title: string;
    slug: string;
    isHomepage: boolean;
}

interface SiteTemplate {
    id: number;
    name: string;
    slug: string;
    description: string;
    industry: string;
    thumbnail: string | null;
    theme: Record<string, string> | null;
    isPremium: boolean;
    pagesCount: number;
    pages: SiteTemplatePage[];
}

interface Industry {
    slug: string;
    name: string;
    icon: string;
}

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
    storageUsed?: number;
    storageLimit?: number;
    storageUsedFormatted?: string;
    storageLimitFormatted?: string;
    storagePercentage?: number;
    messagesCount?: number;
    unreadMessages?: number;
    healthSuggestions?: HealthSuggestion[];
}

interface HealthSuggestion {
    type: 'warning' | 'info';
    icon: string;
    message: string;
    action: string;
    actionRoute: string;
}

interface RecentMessage {
    id: number;
    siteId: number;
    siteName: string;
    siteSubdomain: string;
    name: string;
    email: string;
    subject: string | null;
    message: string;
    createdAt: string;
}

interface Stats {
    totalSites: number;
    publishedSites: number;
    totalPageViews: number;
    totalOrders: number;
    totalRevenue: number;
    totalMessages?: number;
    unreadMessages?: number;
}

interface Subscription {
    tier: string;
    tierName: string;
    sitesLimit: number;
    sitesUsed: number;
    canCreateSite: boolean;
    expiresAt: string | null;
}

interface AIUsage {
    limit: number;
    used: number;
    remaining: number;
    is_unlimited: boolean;
    percentage: number;
    month: string;
    features: string[];
    has_priority: boolean;
}

interface TierRestrictions {
    tier: string;
    tier_name: string;
    sites_limit: number;
    storage_limit: number;
    storage_limit_formatted: string;
    products_limit: number;
    products_unlimited: boolean;
    ai_prompts_limit: number;
    ai_unlimited: boolean;
    features: Record<string, boolean>;
}

interface AvailableTier {
    key: string;
    name: string;
}

const props = defineProps<{
    sites: Site[];
    stats?: Stats;
    subscription?: Subscription;
    aiUsage?: AIUsage;
    tierRestrictions?: TierRestrictions;
    availableTiers?: AvailableTier[] | null;
    isAdmin?: boolean;
    siteTemplates?: SiteTemplate[];
    industries?: Industry[];
    recentMessages?: RecentMessage[];
}>();

// Create site wizard modal
const showCreateWizard = ref(false);
const showAIGenerator = ref(false);

// Check if user has premium access (not on free tier)
const hasGrowBuilderSubscription = computed(() => {
    return props.subscription?.tier !== 'free';
});

const openCreateWizard = () => {
    showCreateWizard.value = true;
};

const closeCreateWizard = () => {
    showCreateWizard.value = false;
};

const openAIGenerator = () => {
    showAIGenerator.value = true;
};

const closeAIGenerator = () => {
    showAIGenerator.value = false;
};

// Admin tier switcher
const showTierDropdown = ref(false);
const tierForm = useForm({
    tier: props.subscription?.tier || 'free',
});

const switchTier = (tierKey: string) => {
    tierForm.tier = tierKey;
    tierForm.post(route('growbuilder.switch-tier'), {
        preserveScroll: true,
        onSuccess: () => {
            showTierDropdown.value = false;
        },
    });
};

// Helper to check if feature is available
const hasFeature = (feature: string): boolean => {
    return props.tierRestrictions?.features?.[feature] ?? false;
};

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
                        <!-- Admin Tier Switcher -->
                        <div v-if="isAdmin && availableTiers" class="relative">
                            <button
                                @click="showTierDropdown = !showTierDropdown"
                                class="inline-flex items-center gap-2 px-3 py-2 text-sm bg-amber-100 text-amber-800 font-medium rounded-lg hover:bg-amber-200 transition border border-amber-300"
                                :disabled="tierForm.processing"
                            >
                                <WrenchScrewdriverIcon class="h-4 w-4" aria-hidden="true" />
                                <span>Test: {{ subscription?.tierName || 'Free' }}</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <!-- Dropdown -->
                            <div 
                                v-if="showTierDropdown"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                            >
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                    Switch Tier (Admin)
                                </div>
                                <button
                                    v-for="tier in availableTiers"
                                    :key="tier.key"
                                    @click="switchTier(tier.key)"
                                    :disabled="tierForm.processing"
                                    :class="[
                                        'w-full text-left px-3 py-2 text-sm hover:bg-gray-50 transition flex items-center justify-between',
                                        subscription?.tier === tier.key ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700'
                                    ]"
                                >
                                    <span>{{ tier.name }}</span>
                                    <span v-if="subscription?.tier === tier.key" class="text-blue-500">✓</span>
                                </button>
                            </div>
                            
                            <!-- Click outside to close -->
                            <div 
                                v-if="showTierDropdown" 
                                class="fixed inset-0 z-40" 
                                @click="showTierDropdown = false"
                            ></div>
                        </div>
                        
                        <Link
                            :href="route('growbuilder.subscription.index')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-indigo-600 font-medium rounded-xl hover:bg-indigo-50 transition"
                        >
                            <SparklesIcon class="h-5 w-5" aria-hidden="true" />
                            <span class="hidden sm:inline">{{ subscription?.tierName || 'Free' }} Plan</span>
                        </Link>
                        <button
                            v-if="subscription?.canCreateSite !== false"
                            type="button"
                            @click="openAIGenerator"
                            disabled
                            class="relative inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-gray-400 to-gray-500 text-white font-semibold rounded-xl cursor-not-allowed opacity-75"
                            title="AI Express - Coming Soon"
                        >
                            <SparklesIcon class="h-5 w-5" aria-hidden="true" />
                            <span class="hidden sm:inline">AI Express</span>
                            <span class="sm:hidden">AI</span>
                            <span class="absolute -top-2 -right-2 bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                                Soon
                            </span>
                        </button>
                        <button
                            v-if="subscription?.canCreateSite !== false"
                            type="button"
                            @click="openCreateWizard"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 shadow-sm transition"
                        >
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            <span class="hidden sm:inline">Pro Builder</span>
                            <span class="sm:hidden">Create</span>
                        </button>
                        <template v-else>
                            <Link
                                v-if="subscription?.tier !== 'agency'"
                                :href="route('growbuilder.subscription.index')"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 shadow-sm transition"
                            >
                                <ArrowUpCircleIcon class="h-5 w-5" aria-hidden="true" />
                                Buy Another Site
                            </Link>
                            <span
                                v-else
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-400 text-white font-medium rounded-xl cursor-not-allowed"
                                title="You've reached the maximum site limit for Agency plan"
                            >
                                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                                Site Limit Reached
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Subscription Banner (when at limit) -->
                <div v-if="subscription && !subscription.canCreateSite" class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-4 mb-6 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <SparklesIcon class="h-6 w-6" aria-hidden="true" />
                        <div>
                            <p class="font-medium">You've reached your site limit ({{ subscription.sitesUsed }}/{{ subscription.sitesLimit }})</p>
                            <p class="text-sm text-indigo-100">
                                {{ subscription.tier === 'agency' ? 'Contact support if you need more sites' : 'Purchase another subscription to create more websites' }}
                            </p>
                        </div>
                    </div>
                    <Link 
                        v-if="subscription.tier !== 'agency'"
                        :href="route('growbuilder.subscription.index')" 
                        class="px-4 py-2 bg-white text-indigo-600 font-medium rounded-lg hover:bg-indigo-50 transition"
                    >
                        Buy Another Site
                    </Link>
                    <a 
                        v-else
                        href="mailto:support@mygrownet.com"
                        class="px-4 py-2 bg-white text-indigo-600 font-medium rounded-lg hover:bg-indigo-50 transition"
                    >
                        Contact Support
                    </a>
                </div>

                <!-- Available Plans Card (for non-agency users) -->
                <div v-if="subscription && subscription.tier !== 'agency' && sites.length > 0" class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-gray-900">Need More Sites?</h3>
                            <p class="text-sm text-gray-500">Each subscription includes 1 website with its own storage</p>
                        </div>
                        <Link 
                            :href="route('growbuilder.subscription.index')"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        >
                            View All Plans →
                        </Link>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Starter Plan -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-sm transition">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-gray-900">Starter</span>
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">1 GB Storage</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mb-1">K120<span class="text-sm font-normal text-gray-500">/mo</span></p>
                            <p class="text-xs text-gray-500 mb-3">Perfect for small businesses</p>
                            <Link 
                                :href="route('growbuilder.subscription.index') + '?plan=starter'"
                                class="block w-full text-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
                            >
                                Get Starter
                            </Link>
                        </div>
                        <!-- Business Plan -->
                        <div class="border-2 border-blue-500 rounded-lg p-4 relative">
                            <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 bg-blue-500 text-white text-xs px-2 py-0.5 rounded-full">Popular</span>
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-gray-900">Business</span>
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">2 GB Storage</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mb-1">K350<span class="text-sm font-normal text-gray-500">/mo</span></p>
                            <p class="text-xs text-gray-500 mb-3">E-commerce & more features</p>
                            <Link 
                                :href="route('growbuilder.subscription.index') + '?plan=business'"
                                class="block w-full text-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
                            >
                                Get Business
                            </Link>
                        </div>
                        <!-- Agency Plan -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-sm transition">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-gray-900">Agency</span>
                                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">10 GB Total</span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mb-1">K900<span class="text-sm font-normal text-gray-500">/mo</span></p>
                            <p class="text-xs text-gray-500 mb-3">Up to 20 sites (500MB each)</p>
                            <Link 
                                :href="route('growbuilder.subscription.index') + '?plan=agency'"
                                class="block w-full text-center px-3 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition"
                            >
                                Get Agency
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div v-if="stats && sites.length > 0" class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
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
                    <!-- Messages Card -->
                    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-amber-100 rounded-lg relative">
                                <EnvelopeIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                                <span 
                                    v-if="stats.unreadMessages && stats.unreadMessages > 0"
                                    class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                                >
                                    {{ stats.unreadMessages > 9 ? '9+' : stats.unreadMessages }}
                                </span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.totalMessages || 0) }}</p>
                                <p class="text-sm text-gray-500">
                                    Messages
                                    <span v-if="stats.unreadMessages" class="text-amber-600">({{ stats.unreadMessages }} new)</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- AI Usage Card -->
                    <div v-if="aiUsage" class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-indigo-100 rounded-lg">
                                <CpuChipIcon class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ aiUsage.is_unlimited ? '∞' : aiUsage.remaining }}
                                    </p>
                                    <span v-if="aiUsage.has_priority" class="text-xs bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-full">Priority</span>
                                </div>
                                <p class="text-sm text-gray-500">
                                    {{ aiUsage.is_unlimited ? 'Unlimited AI' : 'AI Prompts Left' }}
                                </p>
                                <div v-if="!aiUsage.is_unlimited" class="mt-2">
                                    <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-indigo-500 rounded-full transition-all"
                                            :style="{ width: Math.min(aiUsage.percentage, 100) + '%' }"
                                        ></div>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">{{ aiUsage.used }}/{{ aiUsage.limit }} used</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Messages Panel -->
                <div v-if="recentMessages && recentMessages.length > 0" class="bg-white rounded-xl border border-gray-200 shadow-sm mb-8 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-amber-100 rounded-lg">
                                <ChatBubbleLeftEllipsisIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Recent Messages</h3>
                                <p class="text-sm text-gray-500">New inquiries from your websites</p>
                            </div>
                        </div>
                        <span class="text-xs bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full font-medium">
                            {{ recentMessages.length }} unread
                        </span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div 
                            v-for="message in recentMessages" 
                            :key="message.id"
                            class="px-5 py-4 hover:bg-gray-50 transition cursor-pointer"
                        >
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                                    {{ message.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900">{{ message.name }}</span>
                                            <span class="text-xs text-gray-400">•</span>
                                            <span class="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">{{ message.siteName }}</span>
                                        </div>
                                        <span class="text-xs text-gray-400 flex-shrink-0">{{ message.createdAt }}</span>
                                    </div>
                                    <p v-if="message.subject" class="text-sm font-medium text-gray-700 mb-0.5">{{ message.subject }}</p>
                                    <p class="text-sm text-gray-500 truncate">{{ message.message }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ message.email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="sites.length === 0" class="text-center py-20 bg-white rounded-2xl border-2 border-dashed border-gray-200">
                    <div class="w-20 h-20 mx-auto bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mb-6">
                        <SparklesIcon class="h-10 w-10 text-indigo-600" aria-hidden="true" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Create your first website</h3>
                    <p class="text-gray-600 mb-8 max-w-md mx-auto">
                        Choose how you want to build: Let AI do it for you, or use our powerful builder.
                    </p>
                    
                    <!-- Two Options -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                        <button
                            type="button"
                            @click="openAIGenerator"
                            disabled
                            class="relative group inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-gray-400 to-gray-500 text-white font-semibold rounded-xl cursor-not-allowed opacity-75"
                            title="AI Express - Coming Soon"
                        >
                            <SparklesIcon class="h-6 w-6" aria-hidden="true" />
                            <div class="text-left">
                                <div class="text-lg">AI Express</div>
                                <div class="text-xs text-gray-100 font-normal">Describe your business, get a website</div>
                            </div>
                            <span class="absolute -top-2 -right-2 px-2.5 py-1 bg-amber-500 text-white text-xs font-bold rounded-full shadow-lg">
                                Coming Soon
                            </span>
                        </button>
                        
                        <div class="text-gray-400 font-medium">or</div>
                        
                        <button
                            type="button"
                            @click="openCreateWizard"
                            class="inline-flex items-center gap-3 px-8 py-4 bg-white border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:border-blue-500 hover:text-blue-600 transition-all"
                        >
                            <PlusIcon class="h-6 w-6" aria-hidden="true" />
                            <div class="text-left">
                                <div class="text-lg">Pro Builder</div>
                                <div class="text-xs text-gray-500 font-normal">Full control with templates</div>
                            </div>
                        </button>
                    </div>
                    
                    <!-- Features -->
                    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl mx-auto text-left">
                        <div class="p-4">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mb-3">
                                <SparklesIcon class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                            </div>
                            <h4 class="font-medium text-gray-900 mb-1">AI-Powered</h4>
                            <p class="text-sm text-gray-500">Generate complete websites from text descriptions</p>
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

                <!-- Sites Grid - Modern Card Design -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="site in sites"
                        :key="site.id"
                        class="group bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-xl hover:border-gray-300 transition-all duration-300"
                    >
                        <!-- Live Preview Thumbnail -->
                        <div class="relative h-48 bg-gray-100 overflow-hidden">
                            <!-- Iframe Preview (scaled down) -->
                            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                                <iframe
                                    :src="route('growbuilder.preview', { subdomain: site.subdomain })"
                                    class="w-[200%] h-[200%] origin-top-left scale-50 border-0"
                                    :title="`Preview of ${site.name}`"
                                    loading="lazy"
                                ></iframe>
                            </div>
                            
                            <!-- Gradient overlay for better text visibility -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <!-- Status Badge - Top Left -->
                            <div class="absolute top-3 left-3 z-10">
                                <span :class="[
                                    'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full shadow-sm backdrop-blur-sm',
                                    site.status === 'published' ? 'bg-green-500/90 text-white' :
                                    site.status === 'draft' ? 'bg-amber-500/90 text-white' :
                                    'bg-red-500/90 text-white'
                                ]">
                                    <span :class="[
                                        'w-1.5 h-1.5 rounded-full',
                                        site.status === 'published' ? 'bg-green-200 animate-pulse' :
                                        site.status === 'draft' ? 'bg-amber-200' :
                                        'bg-red-200'
                                    ]"></span>
                                    {{ site.status === 'published' ? 'Live' : site.status === 'draft' ? 'Draft' : 'Offline' }}
                                </span>
                            </div>
                            
                            <!-- Plan Badge - Top Right -->
                            <div class="absolute top-3 right-3 z-10">
                                <span :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full shadow-sm backdrop-blur-sm capitalize',
                                    site.plan === 'agency' ? 'bg-purple-500/90 text-white' :
                                    site.plan === 'business' ? 'bg-blue-500/90 text-white' :
                                    'bg-gray-700/80 text-white'
                                ]">
                                    {{ site.plan }}
                                </span>
                            </div>
                            
                            <!-- Quick Actions - Appear on Hover -->
                            <div class="absolute inset-0 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300 z-10">
                                <Link
                                    :href="route('growbuilder.editor', site.id)"
                                    class="p-3 bg-white/95 backdrop-blur-sm rounded-xl text-gray-900 hover:bg-white hover:scale-105 transition-all shadow-lg"
                                    aria-label="Edit site"
                                >
                                    <PencilSquareIcon class="h-5 w-5" aria-hidden="true" />
                                </Link>
                                <a
                                    v-if="site.isPublished"
                                    :href="site.url"
                                    target="_blank"
                                    class="p-3 bg-white/95 backdrop-blur-sm rounded-xl text-gray-900 hover:bg-white hover:scale-105 transition-all shadow-lg"
                                    aria-label="View live site"
                                >
                                    <EyeIcon class="h-5 w-5" aria-hidden="true" />
                                </a>
                                <Link
                                    :href="route('growbuilder.sites.settings', site.id)"
                                    class="p-3 bg-white/95 backdrop-blur-sm rounded-xl text-gray-900 hover:bg-white hover:scale-105 transition-all shadow-lg"
                                    aria-label="Site settings"
                                >
                                    <Cog6ToothIcon class="h-5 w-5" aria-hidden="true" />
                                </Link>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-5">
                            <!-- Site Info -->
                            <div class="mb-4">
                                <h3 class="font-semibold text-gray-900 text-lg truncate">{{ site.name }}</h3>
                                <a 
                                    :href="site.url" 
                                    target="_blank"
                                    class="text-sm text-gray-500 hover:text-blue-600 transition flex items-center gap-1"
                                >
                                    <GlobeAltIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                    {{ site.subdomain }}.mygrownet.com
                                </a>
                            </div>
                            
                            <!-- Quick Stats Row -->
                            <div class="flex items-center gap-3 mb-4 py-3 px-4 bg-gray-50 rounded-xl">
                                <Link
                                    :href="route('growbuilder.sites.analytics', site.id)"
                                    class="flex-1 text-center hover:bg-white rounded-lg py-1 transition"
                                >
                                    <p class="text-lg font-bold text-gray-900">{{ formatNumber(site.pageViews || 0) }}</p>
                                    <p class="text-xs text-gray-500">Views</p>
                                </Link>
                                <div class="w-px h-8 bg-gray-200"></div>
                                <Link
                                    :href="route('growbuilder.products.index', site.id)"
                                    class="flex-1 text-center hover:bg-white rounded-lg py-1 transition"
                                >
                                    <p class="text-lg font-bold text-gray-900">{{ site.ordersCount || 0 }}</p>
                                    <p class="text-xs text-gray-500">Orders</p>
                                </Link>
                                <div class="w-px h-8 bg-gray-200"></div>
                                <Link
                                    :href="route('growbuilder.sites.messages', site.id)"
                                    class="flex-1 text-center hover:bg-white rounded-lg py-1 transition relative"
                                >
                                    <p class="text-lg font-bold text-gray-900">{{ site.messagesCount || 0 }}</p>
                                    <p class="text-xs text-gray-500">Messages</p>
                                    <span 
                                        v-if="site.unreadMessages && site.unreadMessages > 0"
                                        class="absolute -top-1 right-0 w-5 h-5 bg-amber-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                                    >
                                        {{ site.unreadMessages > 9 ? '9+' : site.unreadMessages }}
                                    </span>
                                </Link>
                            </div>

                            <!-- Primary Action -->
                            <Link
                                :href="route('growbuilder.editor', site.id)"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition group/btn"
                            >
                                <PencilSquareIcon class="h-4 w-4 group-hover/btn:rotate-12 transition-transform" aria-hidden="true" />
                                Edit Site
                            </Link>

                            <!-- Health Suggestions -->
                            <div v-if="site.healthSuggestions && site.healthSuggestions.length > 0" class="mt-4 space-y-2">
                                <div 
                                    v-for="(suggestion, idx) in site.healthSuggestions.slice(0, 2)" 
                                    :key="idx"
                                    :class="[
                                        'flex items-center gap-2 p-2 rounded-lg text-xs',
                                        suggestion.type === 'warning' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700'
                                    ]"
                                >
                                    <LightBulbIcon class="h-4 w-4 flex-shrink-0" aria-hidden="true" />
                                    <span class="flex-1 truncate">{{ suggestion.message }}</span>
                                    <Link
                                        :href="route(suggestion.actionRoute, site.id)"
                                        class="text-xs font-medium hover:underline flex-shrink-0"
                                    >
                                        {{ suggestion.action }} →
                                    </Link>
                                </div>
                            </div>
                            
                            <!-- Footer Info -->
                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-1 text-xs text-gray-400">
                                    <ClockIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                    {{ formatDate(site.createdAt) }}
                                </div>
                                
                                <!-- Storage Indicator (compact) -->
                                <div v-if="site.storageUsed !== undefined" class="flex items-center gap-2">
                                    <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div 
                                            :class="[
                                                'h-full rounded-full transition-all',
                                                (site.storagePercentage || 0) >= 90 ? 'bg-red-500' : 
                                                (site.storagePercentage || 0) >= 80 ? 'bg-yellow-500' : 'bg-blue-500'
                                            ]"
                                            :style="{ width: Math.min(site.storagePercentage || 0, 100) + '%' }"
                                        ></div>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ site.storageUsedFormatted }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Site Wizard Modal -->
        <CreateSiteWizard
            :show="showCreateWizard"
            :site-templates="siteTemplates || []"
            :industries="industries || []"
            :has-grow-builder-subscription="hasGrowBuilderSubscription"
            @close="closeCreateWizard"
        />
        
        <!-- AI Website Generator Modal -->
        <WebsiteGeneratorModal
            v-if="showAIGenerator"
            @close="closeAIGenerator"
        />
    </AppLayout>
</template>
