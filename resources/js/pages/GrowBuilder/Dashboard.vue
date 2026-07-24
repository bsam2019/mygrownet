<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import GrowBuilderLayout from '@/layouts/GrowBuilderLayout.vue';
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
    StarIcon,
    PaperAirplaneIcon,
    Squares2X2Icon,
    ListBulletIcon,
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
    sparkline?: number[];
    client?: {
        id: number;
        name: string;
        company: string | null;
        type: string;
    } | null;
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
    status: string;
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

interface ActivityEntry {
    id: number;
    actionType: string;
    entityType: string;
    description: string;
    userName: string;
    createdAt: string;
}

interface Subscription {
    tier: string;
    tierName: string;
    sitesLimit: number;
    sitesUsed: number;
    canCreateSite: boolean;
    storageLimit: number;
    pagesLimit: number;
    productsLimit: number;
    price: number;
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
    price: number;
    storageLimit: number;
    sitesLimit: number;
    pagesLimit: number;
    productsLimit: number;
}

const isSubdomain = computed(() => {
    try { return (route().current() as string).startsWith('growbuilder.sub.'); } catch { return false; }
});

function gb(name: string): string {
    return isSubdomain.value ? 'growbuilder.sub.' + name : 'growbuilder.' + name;
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
    recentActivity?: ActivityEntry[];
    clients?: any[];
}>();

// Create site wizard modal
const showCreateWizard = ref(false);
const showAIGenerator = ref(false);

// Check if user has premium access (not on free tier)
const hasGrowBuilderSubscription = computed(() => {
    return props.subscription?.tier !== 'free';
});

// Helper function to format storage
const formatStorage = (mb: number) => {
    if (mb >= 1000) {
        return `${(mb / 1000).toFixed(0)}GB`;
    }
    return `${mb}MB`;
};

// Helper function to format limits
const formatLimit = (limit: number) => {
    if (limit === -1) return 'Unlimited';
    return limit.toString();
};

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

// ── Message Actions ──
const replyingTo = ref<number | null>(null);
const replyTexts = ref<Record<number, string>>({});
const submittingReply = ref(false);

function toggleReply(messageId: number) {
    replyingTo.value = replyingTo.value === messageId ? null : messageId;
}

function markRead(message: RecentMessage) {
    router.put(route(gb('sites.messages.status'), { id: message.siteId, messageId: message.id }), {
        status: 'read',
    }, {
        preserveScroll: true,
        onSuccess: () => {
            const idx = props.recentMessages?.findIndex(m => m.id === message.id);
            if (idx !== undefined && idx !== -1) {
                props.recentMessages?.splice(idx, 1);
            }
        },
    });
}

function archiveMessage(message: RecentMessage) {
    router.put(route(gb('sites.messages.status'), { id: message.siteId, messageId: message.id }), {
        status: 'archived',
    }, {
        preserveScroll: true,
        onSuccess: () => {
            const idx = props.recentMessages?.findIndex(m => m.id === message.id);
            if (idx !== undefined && idx !== -1) {
                props.recentMessages?.splice(idx, 1);
            }
        },
    });
}

function submitReply(message: RecentMessage) {
    const text = replyTexts.value[message.id]?.trim();
    if (!text) return;

    submittingReply.value = true;
    router.post(route(gb('sites.messages.reply'), { id: message.siteId, messageId: message.id }), {
        reply: text,
    }, {
        preserveScroll: true,
        onFinish: () => { submittingReply.value = false; },
        onSuccess: () => {
            replyTexts.value[message.id] = '';
            replyingTo.value = null;
            const idx = props.recentMessages?.findIndex(m => m.id === message.id);
            if (idx !== undefined && idx !== -1) {
                props.recentMessages?.splice(idx, 1);
            }
        },
    });
}

// Admin tier switcher
const showTierDropdown = ref(false);
const tierForm = useForm({
    tier: props.subscription?.tier || 'free',
});

const switchTier = (tierKey: string) => {
    tierForm.tier = tierKey;
    tierForm.post(route(gb('switch-tier')), {
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

// ── Multi-select Batch Operations ──
const selectedSiteIds = ref<Set<number>>(new Set());

const allSelected = computed(() =>
    unpinnedSites.value.length > 0 && unpinnedSites.value.every(s => selectedSiteIds.value.has(s.id))
);

const someSelected = computed(() =>
    selectedSiteIds.value.size > 0 && !allSelected.value
);

function toggleSelect(siteId: number) {
    const next = new Set(selectedSiteIds.value);
    if (next.has(siteId)) next.delete(siteId); else next.add(siteId);
    selectedSiteIds.value = next;
}

function toggleSelectAll() {
    if (allSelected.value) {
        selectedSiteIds.value = new Set();
    } else {
        selectedSiteIds.value = new Set(unpinnedSites.value.map(s => s.id));
    }
}

function clearSelection() {
    selectedSiteIds.value = new Set();
}

// ── View mode ──
const viewMode = ref<'grid' | 'table'>('grid');

// ── Sparkline SVG ──
function sparklinePath(data: number[]): string {
    if (!data || data.length === 0) return '';
    const w = 120, h = 28;
    const max = Math.max(...data, 1);
    const pad = 1;
    const points = data.map((v, i) => {
        const x = pad + (i / (data.length - 1)) * (w - 2 * pad);
        const y = h - pad - ((v / max) * (h - 2 * pad));
        return `${x.toFixed(1)},${y.toFixed(1)}`;
    });
    return points.join(' ');
}

function formatActionType(type: string): string {
    const map: Record<string, string> = {
        created: 'Created',
        updated: 'Updated',
        deleted: 'Deleted',
        published: 'Published',
        suspended: 'Suspended',
        invited: 'Invited',
        joined: 'Joined',
        removed: 'Removed',
        paid: 'Payment',
    };
    return map[type] || type.charAt(0).toUpperCase() + type.slice(1);
}

function activityIcon(type: string): string {
    const map: Record<string, string> = {
        created: 'PlusCircleIcon',
        published: 'GlobeAltIcon',
        suspended: 'ExclamationTriangleIcon',
        deleted: 'TrashIcon',
        paid: 'CurrencyDollarIcon',
        invited: 'UserPlusIcon',
        joined: 'UserIcon',
        removed: 'UserMinusIcon',
    };
    return map[type] || 'ClockIcon';
}

function batchAction(action: 'publish' | 'unpublish' | 'delete') {
    const ids = Array.from(selectedSiteIds.value);
    if (ids.length === 0) return;
    if (action === 'delete' && !confirm(`Delete ${ids.length} site(s)? This cannot be undone.`)) return;

    const loading = (window as any).__batchLoading;
    if (loading) return;

    (window as any).__batchLoading = true;
    router.post(route(gb('sites.batch')), {
        site_ids: ids,
        action,
    }, {
        preserveScroll: true,
        onFinish: () => { (window as any).__batchLoading = false; },
        onSuccess: () => { clearSelection(); },
    });
}

// ── Period Tabs ──
const activePeriod = ref('all');
const periods = [
    { key: '7d', label: '7 days' },
    { key: '30d', label: '30 days' },
    { key: '90d', label: '90 days' },
    { key: 'all', label: 'All time' },
];

// ── Site Health Score ──
function computeHealthScore(site: Site): { score: number; level: 'good' | 'fair' | 'poor'; label: string } {
    if (!site.healthSuggestions || site.healthSuggestions.length === 0) {
        return { score: 100, level: 'good', label: 'Excellent' };
    }
    let deductions = 0;
    for (const s of site.healthSuggestions) {
        deductions += s.type === 'warning' ? 20 : 5;
    }
    const score = Math.max(0, 100 - deductions);
    if (score >= 80) return { score, level: 'good', label: 'Good' };
    if (score >= 50) return { score, level: 'fair', label: 'Needs Attention' };
    return { score, level: 'poor', label: 'Critical' };
}

const overallHealth = computed(() => {
    if (props.sites.length === 0) return null;
    const scores = props.sites.map(s => computeHealthScore(s).score);
    const avg = Math.round(scores.reduce((a, b) => a + b, 0) / scores.length);
    if (avg >= 80) return { score: avg, level: 'good', label: 'Good' };
    if (avg >= 50) return { score: avg, level: 'fair', label: 'Needs Attention' };
    return { score: avg, level: 'poor', label: 'Critical' };
});

// ── Getting Started Checklist ──
const checklistItems = computed(() => [
    { key: 'create', label: 'Create your first website', done: props.sites.length > 0 },
    { key: 'publish', label: 'Publish a website live', done: (props.stats?.publishedSites || 0) > 0 },
    { key: 'domain', label: 'Connect a custom domain', done: props.sites.some(s => !!s.customDomain) },
    { key: 'message', label: 'Get your first inquiry', done: (props.stats?.totalMessages || 0) > 0 },
    { key: 'sale', label: 'Make your first sale', done: (props.stats?.totalOrders || 0) > 0 },
]);

const checklistDone = computed(() => checklistItems.value.filter(i => i.done).length);
const checklistTotal = computed(() => checklistItems.value.length);
const checklistComplete = computed(() => checklistDone.value >= checklistTotal.value);
const checklistProgress = computed(() => Math.round((checklistDone.value / checklistTotal.value) * 100));

// ── Period display (static — backend filtering would need date params) ──
function periodDisplay(key: string): string {
    const p = periods.find(p => p.key === key);
    return p ? p.label : 'All time';
}

// ── Pinned / Favorite Sites ──
const PIN_KEY = 'mg-pinned-sites';
const pinnedIds = ref<number[]>([]);

function loadPinned() {
    try {
        const raw = localStorage.getItem(PIN_KEY);
        if (raw) pinnedIds.value = JSON.parse(raw);
    } catch {}
}

function savePinned() {
    try { localStorage.setItem(PIN_KEY, JSON.stringify(pinnedIds.value)); } catch {}
}

function togglePin(siteId: number) {
    const idx = pinnedIds.value.indexOf(siteId);
    if (idx >= 0) {
        pinnedIds.value.splice(idx, 1);
    } else {
        pinnedIds.value.unshift(siteId);
    }
    savePinned();
}

function isPinned(siteId: number): boolean {
    return pinnedIds.value.includes(siteId);
}

const pinnedSites = computed(() => {
    return props.sites.filter(s => pinnedIds.value.includes(s.id));
});

const unpinnedSites = computed(() => {
    return props.sites.filter(s => !pinnedIds.value.includes(s.id));
});

const hasPinnedSites = computed(() => pinnedSites.value.length > 0);

loadPinned();
</script>

<template>
    <GrowBuilderLayout>
        <Head title="GrowBuilder - My Websites" />
                <!-- Header - Compact Layout -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <!-- Title Section -->
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-gray-900">GrowBuilder</h1>
                        <p class="text-sm text-gray-500">
                            Build beautiful websites for your business
                        </p>
                    </div>
                    
                    <!-- Action Buttons - Compact Layout -->
                    <div class="flex items-center gap-2">
                        <!-- Admin Tier Switcher -->
                        <div v-if="isAdmin && availableTiers" class="relative">
                            <button
                                @click="showTierDropdown = !showTierDropdown"
                                class="inline-flex items-center gap-1 px-2 py-1.5 text-xs bg-amber-100 text-amber-800 font-medium rounded-lg hover:bg-amber-200 transition border border-amber-300"
                                :disabled="tierForm.processing"
                            >
                                <WrenchScrewdriverIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                <span class="hidden sm:inline">Test:</span>
                                <span class="truncate max-w-16 sm:max-w-none">{{ subscription?.tierName || 'Free' }}</span>
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        
                        <!-- Plan Link -->
                        <Link
                            :href="route(gb('subscription.index'))"
                            class="inline-flex items-center gap-1 px-2 py-1.5 text-indigo-600 font-medium rounded-lg hover:bg-indigo-50 transition text-xs"
                        >
                            <SparklesIcon class="h-3.5 w-3.5" aria-hidden="true" />
                            <span class="hidden sm:inline">{{ subscription?.tierName || 'Free' }} Plan</span>
                            <span class="sm:hidden">{{ subscription?.tierName || 'Free' }}</span>
                        </Link>
                        
                        <!-- AI Express Button -->
                        <button
                            v-if="subscription?.canCreateSite !== false"
                            type="button"
                            @click="openAIGenerator"
                            class="inline-flex items-center gap-1 px-2 py-1.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-medium rounded-lg hover:from-indigo-600 hover:to-purple-700 shadow-sm transition text-xs"
                        >
                            <SparklesIcon class="h-3.5 w-3.5" aria-hidden="true" />
                            <span class="hidden sm:inline">AI Express</span>
                            <span class="sm:hidden">AI</span>
                        </button>
                        
                        <!-- Pro Builder Button -->
                        <button
                            v-if="subscription?.canCreateSite !== false"
                            type="button"
                            @click="openCreateWizard"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-sm transition text-xs"
                        >
                            <PlusIcon class="h-3.5 w-3.5" aria-hidden="true" />
                            <span class="hidden sm:inline">Pro Builder</span>
                            <span class="sm:hidden">Create</span>
                        </button>
                        
                        <!-- Alternative buttons when at limit -->
                        <template v-else>
                            <Link
                                v-if="subscription?.tier !== 'agency'"
                                :href="route(gb('subscription.index'))"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 shadow-sm transition text-xs"
                            >
                                <ArrowUpCircleIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                <span class="hidden sm:inline">Buy Another Site</span>
                                <span class="sm:hidden">Buy Site</span>
                            </Link>
                            <span
                                v-else
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed text-xs"
                                title="You've reached the maximum site limit for Agency plan"
                            >
                                <PlusIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                <span class="hidden sm:inline">Site Limit Reached</span>
                                <span class="sm:hidden">Limit</span>
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
                        :href="route(gb('subscription.index'))" 
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
                            :href="route(gb('subscription.index'))"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        >
                            View All Plans →
                        </Link>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Dynamic Tier Plans -->
                        <div 
                            v-for="tier in availableTiers?.slice(1, 4)" 
                            :key="tier.key"
                            :class="[
                                'border rounded-lg p-4 hover:shadow-sm transition',
                                tier.key === 'business' ? 'border-2 border-blue-500 relative' : 'border border-gray-200 hover:border-blue-300'
                            ]"
                        >
                            <span 
                                v-if="tier.key === 'business'" 
                                class="absolute -top-2.5 left-1/2 -translate-x-1/2 bg-blue-500 text-white text-xs px-2 py-0.5 rounded-full"
                            >
                                Popular
                            </span>
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-gray-900">{{ tier.name }}</span>
                                <span 
                                    :class="[
                                        'text-xs px-2 py-0.5 rounded-full',
                                        tier.key === 'agency' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'
                                    ]"
                                >
                                    {{ formatStorage(tier.storageLimit) }} Storage
                                </span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mb-1">
                                K{{ tier.price }}<span class="text-sm font-normal text-gray-500">/mo</span>
                            </p>
                            <p class="text-xs text-gray-500 mb-3">
                                {{ tier.key === 'starter' ? 'Perfect for small businesses' : 
                                   tier.key === 'business' ? 'E-commerce & more features' : 
                                   'Up to ' + formatLimit(tier.sitesLimit) + ' sites' }}
                            </p>
                            <Link 
                                :href="route(gb('subscription.index')) + '?plan=' + tier.key"
                                :class="[
                                    'block w-full text-center px-3 py-2 text-white text-sm font-medium rounded-lg transition',
                                    tier.key === 'agency' ? 'bg-purple-600 hover:bg-purple-700' : 'bg-blue-600 hover:bg-blue-700'
                                ]"
                            >
                                Get {{ tier.name }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Getting Started Checklist -->
                <div v-if="!checklistComplete && sites.length > 0" class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                <SparklesIcon class="w-4 h-4 text-indigo-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Website Launch Checklist</h3>
                                <p class="text-xs text-gray-500">{{ checklistDone }}/{{ checklistTotal }} completed</p>
                            </div>
                        </div>
                        <div class="w-24 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full transition-all" :style="{ width: checklistProgress + '%' }"></div>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="item in checklistItems" :key="item.key" class="flex items-center gap-3 px-5 py-2.5" :class="{ 'opacity-50': item.done }">
                            <div :class="[
                                'w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0',
                                item.done ? 'bg-green-500 text-white' : 'border-2 border-gray-300'
                            ]">
                                <svg v-if="item.done" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <span class="text-sm" :class="item.done ? 'text-gray-400 line-through' : 'text-gray-700'">{{ item.label }}</span>
                        </div>
                    </div>
                </div>

                <!-- Period Tabs + Health Score -->
                <div v-if="stats && sites.length > 0" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-500">Period:</span>
                        <div class="flex gap-1">
                            <button
                                v-for="p in periods"
                                :key="p.key"
                                @click="activePeriod = p.key"
                                :class="[
                                    'px-2.5 py-1 text-xs font-medium rounded-md transition',
                                    activePeriod === p.key
                                        ? 'bg-gray-900 text-white'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                ]"
                            >{{ p.label }}</button>
                        </div>
                    </div>
                    <div v-if="overallHealth" class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-500">Site Health:</span>
                        <div class="flex items-center gap-1.5">
                            <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all"
                                    :class="overallHealth.level === 'good' ? 'bg-green-500' : overallHealth.level === 'fair' ? 'bg-yellow-500' : 'bg-red-500'"
                                    :style="{ width: overallHealth.score + '%' }"
                                ></div>
                            </div>
                            <span class="text-xs font-medium" :class="overallHealth.level === 'good' ? 'text-green-600' : overallHealth.level === 'fair' ? 'text-yellow-600' : 'text-red-600'">
                                {{ overallHealth.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards - Compact Grid -->
                <div v-if="stats && sites.length > 0" class="grid grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
                    <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded-md">
                                <GlobeAltIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold text-gray-900">{{ stats.publishedSites }}</p>
                                <p class="text-xs text-gray-500 truncate">Live Sites</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-green-100 rounded-md">
                                <ChartBarIcon class="h-4 w-4 text-green-600" aria-hidden="true" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold text-gray-900">{{ formatNumber(stats.totalPageViews) }}</p>
                                <p class="text-xs text-gray-500 truncate">Page Views</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-purple-100 rounded-md">
                                <ShoppingBagIcon class="h-4 w-4 text-purple-600" aria-hidden="true" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold text-gray-900">{{ formatNumber(stats.totalOrders) }}</p>
                                <p class="text-xs text-gray-500 truncate">Orders</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-emerald-100 rounded-md">
                                <CurrencyDollarIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold text-gray-900">{{ formatCurrency(stats.totalRevenue) }}</p>
                                <p class="text-xs text-gray-500 truncate">Revenue</p>
                            </div>
                        </div>
                    </div>
                    <!-- Messages Card -->
                    <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-amber-100 rounded-md relative">
                                <EnvelopeIcon class="h-4 w-4 text-amber-600" aria-hidden="true" />
                                <span 
                                    v-if="stats.unreadMessages && stats.unreadMessages > 0"
                                    class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-red-500 text-white text-[8px] font-bold rounded-full flex items-center justify-center"
                                >
                                    {{ stats.unreadMessages > 9 ? '9+' : stats.unreadMessages }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold text-gray-900">{{ formatNumber(stats.totalMessages || 0) }}</p>
                                <p class="text-xs text-gray-500 truncate">
                                    Messages
                                    <span v-if="stats.unreadMessages" class="text-amber-600">({{ stats.unreadMessages }})</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- AI Usage Card -->
                    <div v-if="aiUsage" class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-indigo-100 rounded-md">
                                <CpuChipIcon class="h-4 w-4 text-indigo-600" aria-hidden="true" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-lg font-bold text-gray-900">
                                        {{ aiUsage.is_unlimited ? '∞' : aiUsage.remaining }}
                                    </p>
                                    <span v-if="aiUsage.has_priority" class="text-[8px] bg-indigo-100 text-indigo-700 px-1 py-0.5 rounded-full">Pro</span>
                                </div>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ aiUsage.is_unlimited ? 'Unlimited AI' : 'AI Left' }}
                                </p>
                                <div v-if="!aiUsage.is_unlimited" class="mt-1">
                                    <div class="h-1 bg-gray-200 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-indigo-500 rounded-full transition-all"
                                            :style="{ width: Math.min(aiUsage.percentage, 100) + '%' }"
                                        ></div>
                                    </div>
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
                            class="px-5 py-4 hover:bg-gray-50 transition"
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
                                    <div class="flex items-center gap-2 mt-2">
                                        <button
                                            type="button"
                                            @click="markRead(message)"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
                                        >Mark Read</button>
                                        <span class="text-gray-300">|</span>
                                        <button
                                            type="button"
                                            @click="toggleReply(message.id)"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
                                        >{{ replyingTo === message.id ? 'Cancel' : 'Reply' }}</button>
                                        <span class="text-gray-300">|</span>
                                        <button
                                            type="button"
                                            @click="archiveMessage(message)"
                                            class="text-xs text-gray-500 hover:text-red-600 font-medium transition-colors"
                                        >Archive</button>
                                    </div>
                                    <!-- Inline Reply Form -->
                                    <div v-if="replyingTo === message.id" class="mt-3 border-t border-gray-100 pt-3">
                                        <textarea
                                            v-model="replyTexts[message.id]"
                                            rows="3"
                                            placeholder="Type your reply..."
                                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                                        ></textarea>
                                        <div class="flex justify-end mt-2">
                                            <button
                                                type="button"
                                                @click="submitReply(message)"
                                                :disabled="submittingReply || !replyTexts[message.id]?.trim()"
                                                class="inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                            >
                                                <svg v-if="submittingReply" class="animate-spin h-3 w-3" viewBox="0 0 24 24" fill="none">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                                </svg>
                                                <PaperAirplaneIcon class="h-3 w-3" />
                                                Send Reply
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="sites.length === 0" class="text-center py-16 bg-white rounded-2xl border-2 border-dashed border-gray-200 shadow-sm">
                    <div class="w-20 h-20 mx-auto bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mb-6">
                        <SparklesIcon class="h-10 w-10 text-indigo-600" aria-hidden="true" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Create your first website</h3>
                    <p class="text-gray-600 mb-4 max-w-md mx-auto">
                        Choose how you want to build — AI-powered or hands-on with our builder.
                    </p>
                    
                    <!-- Getting Started Steps -->
                    <div class="max-w-lg mx-auto mb-10 text-left bg-gray-50 rounded-xl p-5">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <SparklesIcon class="w-4 h-4 text-indigo-500" />
                            Your Launch Roadmap
                        </h4>
                        <div class="space-y-2">
                            <div v-for="(step, idx) in [
                                { label: 'Create your website with Pro Builder', done: false },
                                { label: 'Customize with templates & AI', done: false },
                                { label: 'Publish live on the web', done: false },
                                { label: 'Add products and accept payments', done: false },
                                { label: 'Connect your own domain name', done: false },
                            ]" :key="idx" class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-gray-400">{{ idx + 1 }}</span>
                                </div>
                                <span class="text-sm text-gray-600">{{ step.label }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Two Options -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <button
                            type="button"
                            @click="openAIGenerator"
                            class="group inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-600 hover:to-purple-700 shadow-sm hover:shadow-md transition-all"
                        >
                            <SparklesIcon class="h-6 w-6" aria-hidden="true" />
                            <div class="text-left">
                                <div class="text-lg">AI Express</div>
                                <div class="text-xs text-white/80 font-normal">Describe your business, we build it</div>
                            </div>
                        </button>
                        
                        <span class="text-gray-300 font-medium text-sm">or</span>
                        
                        <button
                            type="button"
                            @click="openCreateWizard"
                            class="inline-flex items-center gap-3 px-8 py-4 bg-white border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:border-blue-500 hover:text-blue-600 hover:shadow-md transition-all"
                        >
                            <PlusIcon class="h-6 w-6" aria-hidden="true" />
                            <div class="text-left">
                                <div class="text-lg">Pro Builder</div>
                                <div class="text-xs text-gray-500 font-normal">Choose a template, customize fully</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Pinned Sites -->
                <div v-if="hasPinnedSites" class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <StarIcon class="w-4 h-4 text-yellow-500" />
                        <h3 class="text-sm font-semibold text-gray-900">Pinned Sites</h3>
                        <span class="text-xs text-gray-400">({{ pinnedSites.length }})</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="site in pinnedSites"
                            :key="'pinned-' + site.id"
                            :class="[
                                'group bg-white rounded-2xl border-2 overflow-hidden hover:shadow-xl transition-all duration-300 relative',
                                selectedSiteIds.has(site.id) ? 'border-indigo-400 ring-2 ring-indigo-200' : 'border-yellow-200 hover:border-yellow-300'
                            ]"
                        >
                            <!-- Checkbox (visible when any selected or on hover) -->
                            <div :class="['absolute top-3 left-3 z-30 transition-opacity', selectedSiteIds.size > 0 ? 'opacity-100' : 'opacity-0 group-hover:opacity-100']">
                                <input
                                    type="checkbox"
                                    :checked="selectedSiteIds.has(site.id)"
                                    @click.stop="toggleSelect(site.id)"
                                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer shadow-sm"
                                />
                            </div>
                            <!-- Pin button (shifted right when checkbox might overlap) -->
                            <button @click.stop="togglePin(site.id)" :class="['absolute z-20 w-7 h-7 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center shadow-sm hover:bg-white transition', selectedSiteIds.size > 0 ? 'top-3 left-10' : 'top-3 left-3']" title="Unpin site">
                                <StarIcon class="w-3.5 h-3.5 text-yellow-500 fill-yellow-500" />
                            </button>
                            <!-- Reuse same card content via clone -->
                            <template v-for="s in [site]">
                                <!-- Card thumbnail (duplicated from below — simplified) -->
                                <div class="relative h-40 bg-gradient-to-br from-yellow-50 to-amber-100 overflow-hidden">
                                    <div class="absolute inset-2 bg-white rounded-lg shadow-sm overflow-hidden">
                                        <div class="h-5 bg-gray-100 border-b border-gray-200 flex items-center px-2 gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span>
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                                            <div class="ml-2 flex-1 h-2.5 bg-white rounded-sm border border-gray-200 flex items-center px-1.5">
                                                <span class="text-[5px] text-gray-400 truncate">{{ site.subdomain }}.mygrownet.com</span>
                                            </div>
                                        </div>
                                        <div class="p-2 space-y-1.5">
                                            <div class="h-3 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-sm"></div>
                                            <div class="h-6 rounded-sm bg-gray-100 flex items-center justify-center">
                                                <div class="w-3 h-3 rounded-full bg-white/80 flex items-center justify-center">
                                                    <GlobeAltIcon class="w-2 h-2 text-yellow-600" />
                                                </div>
                                            </div>
                                            <div class="flex gap-1.5">
                                                <div class="flex-1 space-y-1"><div class="h-1.5 rounded-sm bg-gray-200 w-3/4"></div><div class="h-1.5 rounded-sm bg-gray-100 w-1/2"></div></div>
                                                <div class="flex-1 space-y-1"><div class="h-1.5 rounded-sm bg-gray-200 w-2/3"></div><div class="h-1.5 rounded-sm bg-gray-100 w-3/4"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Status badge -->
                                    <div class="absolute top-3 right-3 z-10">
                                        <span :class="['px-2 py-1 text-xs font-medium rounded-full shadow-sm backdrop-blur-sm capitalize', site.status === 'published' ? 'bg-green-500/90 text-white' : site.status === 'draft' ? 'bg-amber-500/90 text-white' : 'bg-red-500/90 text-white']">
                                            {{ site.status === 'published' ? 'Live' : site.status === 'draft' ? 'Draft' : 'Offline' }}
                                        </span>
                                    </div>
                                </div>
                                <!-- Card content condensed -->
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-900 text-sm truncate">{{ site.name }}</h4>
                                        <Link :href="route(gb('editor'), site.id)" class="text-xs text-blue-600 hover:underline font-medium">Edit</Link>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span>{{ formatNumber(site.pageViews || 0) }} views</span>
                                        <span>{{ site.ordersCount || 0 }} orders</span>
                                    </div>
                                    <div v-if="site.sparkline && site.sparkline.length > 0" class="mt-2">
                                        <svg viewBox="0 0 120 20" class="w-full h-5" preserveAspectRatio="none">
                                            <polyline :points="sparklinePath(site.sparkline)" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-400" />
                                        </svg>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Sites Grid -->
                <div v-if="sites.length > 0" class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer" @click.prevent="toggleSelectAll">
                                <input
                                    type="checkbox"
                                    :checked="allSelected"
                                    :indeterminate="someSelected && !allSelected"
                                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                />
                                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">{{ hasPinnedSites ? 'Other Sites' : 'All Sites' }}</span>
                                <span class="text-xs text-gray-400">({{ unpinnedSites.length }})</span>
                            </label>
                        </div>
                        <!-- Batch Action Bar -->
                        <div v-if="selectedSiteIds.size > 0" class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">{{ selectedSiteIds.size }} selected</span>
                            <button
                                type="button"
                                @click="batchAction('publish')"
                                class="text-xs font-semibold px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50"
                            >Publish</button>
                            <button
                                type="button"
                                @click="batchAction('unpublish')"
                                class="text-xs font-semibold px-3 py-1.5 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition disabled:opacity-50"
                            >Unpublish</button>
                            <button
                                type="button"
                                @click="batchAction('delete')"
                                class="text-xs font-semibold px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50"
                            >Delete</button>
                            <button
                                type="button"
                                @click="clearSelection"
                                class="text-xs text-gray-400 hover:text-gray-600 font-medium ml-1"
                            >Clear</button>
                            <!-- View mode toggle -->
                            <div v-if="selectedSiteIds.size === 0" class="flex items-center gap-1 bg-gray-100 rounded-lg p-0.5">
                                <button
                                    type="button"
                                    @click="viewMode = 'grid'"
                                    :class="['px-2.5 py-1.5 text-xs font-medium rounded-md transition', viewMode === 'grid' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']"
                                    title="Grid view"
                                >
                                    <Squares2X2Icon class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    type="button"
                                    @click="viewMode = 'table'"
                                    :class="['px-2.5 py-1.5 text-xs font-medium rounded-md transition', viewMode === 'table' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']"
                                    title="Table view"
                                >
                                    <ListBulletIcon class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="site in unpinnedSites"
                        :key="site.id"
                        :class="[
                            'group bg-white rounded-2xl border overflow-hidden hover:shadow-xl transition-all duration-300 relative',
                            selectedSiteIds.has(site.id) ? 'border-indigo-400 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-gray-300'
                        ]"
                    >
                        <!-- Checkbox (visible when any selected or on hover) -->
                        <div :class="['absolute top-3 left-3 z-30 transition-opacity', selectedSiteIds.size > 0 ? 'opacity-100' : 'opacity-0 group-hover:opacity-100']">
                            <input
                                type="checkbox"
                                :checked="selectedSiteIds.has(site.id)"
                                @click.stop="toggleSelect(site.id)"
                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer shadow-sm"
                            />
                        </div>
                        <!-- Pin button (shifted right when checkbox might overlap) -->
                        <button @click.stop="togglePin(site.id)" :class="['absolute z-20 w-7 h-7 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center shadow-sm hover:bg-white transition', selectedSiteIds.size > 0 ? 'top-3 left-10 opacity-100' : 'top-3 left-3 opacity-0 group-hover:opacity-100']" title="Pin site">
                            <StarIcon class="w-3.5 h-3.5 text-gray-400 hover:text-yellow-500" />
                        </button>
                        <!-- Live Preview Thumbnail -->
                        <div class="relative h-48 bg-gradient-to-br from-blue-50 to-indigo-100 overflow-hidden">
                            <!-- Mini Browser Mockup -->
                            <div class="absolute inset-2 bg-white rounded-lg shadow-sm overflow-hidden">
                                <!-- Browser Chrome -->
                                <div class="h-5 bg-gray-100 border-b border-gray-200 flex items-center px-2 gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                                    <div class="ml-2 flex-1 h-2.5 bg-white rounded-sm border border-gray-200 flex items-center px-1.5">
                                        <span class="text-[5px] text-gray-400 truncate">{{ site.subdomain }}.mygrownet.com</span>
                                    </div>
                                </div>
                                <!-- Page Content -->
                                <div class="p-2 space-y-1.5">
                                    <!-- Header bar -->
                                    <div class="h-3 bg-gradient-to-r rounded-sm" :class="site.status === 'published' ? 'from-blue-500 to-blue-600' : 'from-gray-300 to-gray-400'"></div>
                                    <!-- Hero area -->
                                    <div class="h-6 rounded-sm bg-gray-100 flex items-center justify-center">
                                        <div class="w-3 h-3 rounded-full bg-white/80 flex items-center justify-center shadow-sm">
                                            <GlobeAltIcon class="w-2 h-2" :class="site.status === 'published' ? 'text-blue-600' : 'text-gray-400'" />
                                        </div>
                                    </div>
                                    <!-- Content rows -->
                                    <div class="flex gap-1.5">
                                        <div class="flex-1 space-y-1">
                                            <div class="h-1.5 rounded-sm bg-gray-200 w-3/4"></div>
                                            <div class="h-1.5 rounded-sm bg-gray-100 w-1/2"></div>
                                            <div class="h-1.5 rounded-sm bg-gray-100 w-2/3"></div>
                                        </div>
                                        <div class="flex-1 space-y-1">
                                            <div class="h-1.5 rounded-sm bg-gray-200 w-2/3"></div>
                                            <div class="h-1.5 rounded-sm bg-gray-100 w-3/4"></div>
                                            <div class="h-1.5 rounded-sm bg-gray-100 w-1/2"></div>
                                        </div>
                                    </div>
                                    <!-- Footer -->
                                    <div class="h-2 rounded-sm bg-gray-100"></div>
                                </div>
                            </div>
                            
                            <!-- Hover overlay with site info -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <div class="w-full h-full bg-white/90 backdrop-blur-sm flex flex-col items-center justify-center p-4">
                                    <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mb-3 shadow-lg">
                                        <GlobeAltIcon class="w-6 h-6 text-white" aria-hidden="true" />
                                    </div>
                                    <h4 class="text-sm font-semibold text-gray-900 mb-1">{{ site.name }}</h4>
                                    <p class="text-xs text-gray-500 text-center">{{ site.status === 'published' ? 'Live Website' : 'Draft Website' }}</p>
                                    <div class="mt-2 flex items-center gap-1.5 text-xs text-gray-400">
                                        <span>{{ site.subdomain }}.mygrownet.com</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Gradient overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
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
                            
                            <!-- Health Score - Top Right -->
                            <div class="absolute top-3 right-3 z-10 flex items-center gap-1.5">
                                <div v-if="computeHealthScore(site).score < 100" :title="computeHealthScore(site).label" class="flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-medium backdrop-blur-sm"
                                    :class="computeHealthScore(site).level === 'good' ? 'bg-green-500/80 text-white' : computeHealthScore(site).level === 'fair' ? 'bg-yellow-500/80 text-white' : 'bg-red-500/80 text-white'"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="computeHealthScore(site).level === 'good' ? 'bg-green-200' : computeHealthScore(site).level === 'fair' ? 'bg-yellow-200' : 'bg-red-200'"></span>
                                    {{ computeHealthScore(site).score }}%
                                </div>
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
                                    :href="route(gb('editor'), site.id)"
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
                                    :href="route(gb('sites.settings'), site.id)"
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
                                <!-- Client Name (if assigned) -->
                                <div v-if="site.client" class="flex items-center gap-1 text-sm text-gray-600 mb-1">
                                    <UsersIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                    <span class="truncate">{{ site.client.company || site.client.name }}</span>
                                </div>
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
                                    :href="route(gb('sites.analytics'), site.id)"
                                    class="flex-1 text-center hover:bg-white rounded-lg py-1 transition"
                                >
                                    <p class="text-lg font-bold text-gray-900">{{ formatNumber(site.pageViews || 0) }}</p>
                                    <p class="text-xs text-gray-500">Views</p>
                                </Link>
                                <div class="w-px h-8 bg-gray-200"></div>
                                <Link
                                    :href="route(gb('products.index'), site.id)"
                                    class="flex-1 text-center hover:bg-white rounded-lg py-1 transition"
                                >
                                    <p class="text-lg font-bold text-gray-900">{{ site.ordersCount || 0 }}</p>
                                    <p class="text-xs text-gray-500">Orders</p>
                                </Link>
                                <div class="w-px h-8 bg-gray-200"></div>
                                <Link
                                    :href="route(gb('sites.messages'), site.id)"
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

                            <!-- Action Buttons Row -->
                            <div class="flex gap-2 mb-4">
                                <Link
                                    :href="route(gb('editor'), site.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
                                >
                                    <PencilSquareIcon class="h-4 w-4" aria-hidden="true" />
                                    Edit
                                </Link>
                                <Link
                                    :href="route(gb('sites.analytics'), site.id)"
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition"
                                >
                                    <ChartBarIcon class="h-4 w-4" aria-hidden="true" />
                                    Analytics
                                </Link>
                            </div>

                            <!-- Traffic Sparkline -->
                            <div v-if="site.sparkline && site.sparkline.length > 0" class="mb-3 px-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <ArrowTrendingUpIcon class="h-3.5 w-3.5 text-gray-400" />
                                    <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">14-Day Traffic</span>
                                </div>
                                <svg viewBox="0 0 120 28" class="w-full h-7" preserveAspectRatio="none">
                                    <polyline
                                        :points="sparklinePath(site.sparkline)"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="text-indigo-500"
                                    />
                                </svg>
                            </div>

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

                    <!-- Table View -->
                    <div v-if="viewMode === 'table'" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-4 py-3 w-8">
                                        <input
                                            type="checkbox"
                                            :checked="allSelected"
                                            :indeterminate="someSelected && !allSelected"
                                            @change="toggleSelectAll"
                                            class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                    </th>
                                    <th class="px-4 py-3">Site</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Views</th>
                                    <th class="px-4 py-3 text-right">Messages</th>
                                    <th class="px-4 py-3 text-right hidden md:table-cell">Storage</th>
                                    <th class="px-4 py-3 hidden md:table-cell">Health</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="site in unpinnedSites"
                                    :key="'t-' + site.id"
                                    :class="['hover:bg-gray-50 transition', selectedSiteIds.has(site.id) ? 'bg-indigo-50/50' : '']"
                                >
                                    <td class="px-4 py-3">
                                        <input
                                            type="checkbox"
                                            :checked="selectedSiteIds.has(site.id)"
                                            @change="toggleSelect(site.id)"
                                            class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                                {{ site.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 truncate max-w-[200px]">{{ site.name }}</div>
                                                <div class="text-xs text-gray-400">{{ site.subdomain }}.mygrownet.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span :class="[
                                            'inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full',
                                            site.status === 'published' ? 'bg-green-100 text-green-700' : 
                                            site.status === 'draft' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'
                                        ]">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="site.status === 'published' ? 'bg-green-500' : site.status === 'draft' ? 'bg-amber-500' : 'bg-red-500'"></span>
                                            {{ site.status === 'published' ? 'Live' : site.status === 'draft' ? 'Draft' : 'Offline' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-900 font-medium">{{ formatNumber(site.pageViews || 0) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-gray-900 font-medium">{{ site.messagesCount || 0 }}</span>
                                        <span v-if="site.unreadMessages" class="ml-1 text-xs text-amber-600 font-semibold">({{ site.unreadMessages }})</span>
                                    </td>
                                    <td class="px-4 py-3 text-right hidden md:table-cell">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-xs text-gray-500">{{ site.storageUsedFormatted }}</span>
                                            <div class="w-12 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full" :class="(site.storagePercentage || 0) >= 90 ? 'bg-red-500' : (site.storagePercentage || 0) >= 80 ? 'bg-yellow-500' : 'bg-blue-500'" :style="{ width: Math.min(site.storagePercentage || 0, 100) + '%' }"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 hidden md:table-cell">
                                        <div class="flex items-center gap-2">
                                            <div :class="['w-2 h-2 rounded-full', computeHealthScore(site).level === 'good' ? 'bg-green-500' : computeHealthScore(site).level === 'fair' ? 'bg-yellow-500' : 'bg-red-500']"></div>
                                            <span class="text-xs text-gray-500">{{ computeHealthScore(site).score }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <Link :href="route(gb('editor'), site.id)" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                                <PencilSquareIcon class="h-4 w-4" />
                                            </Link>
                                            <a v-if="site.isPublished" :href="site.url" target="_blank" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="View live">
                                                <EyeIcon class="h-4 w-4" />
                                            </a>
                                            <Link :href="route(gb('sites.settings'), site.id)" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition" title="Settings">
                                                <Cog6ToothIcon class="h-4 w-4" />
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="unpinnedSites.length === 0" class="text-center py-12 text-sm text-gray-500">
                            No sites found.
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Feed -->
                <div v-if="recentActivity && recentActivity.length > 0" class="bg-white rounded-xl border border-gray-200 shadow-sm mb-8 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="p-2 bg-indigo-100 rounded-lg">
                            <ClockIcon class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Recent Activity</h3>
                            <p class="text-sm text-gray-500">Latest actions across your sites and team</p>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="entry in recentActivity" :key="entry.id" class="px-5 py-3 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700">{{ entry.description }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">{{ formatActionType(entry.actionType) }}</span>
                                    <span class="text-xs text-gray-400">by {{ entry.userName }}</span>
                                    <span class="text-xs text-gray-400">{{ entry.createdAt }}</span>
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
            :clients="clients || []"
            :has-grow-builder-subscription="hasGrowBuilderSubscription"
            @close="closeCreateWizard"
        />
        
        <!-- AI Website Generator Modal -->
        <WebsiteGeneratorModal
            v-if="showAIGenerator"
            @close="closeAIGenerator"
        />
    </GrowBuilderLayout>
</template>
