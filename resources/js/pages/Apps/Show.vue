<script setup lang="ts">
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import {
    CubeIcon, ArrowLeftIcon, CheckCircleIcon, XCircleIcon,
    RocketLaunchIcon, CurrencyDollarIcon, DocumentTextIcon,
    ShoppingBagIcon, HomeModernIcon, ChartBarIcon,
    GlobeAltIcon, CloudIcon, BuildingOfficeIcon, BookOpenIcon,
    MegaphoneIcon, CommandLineIcon,
    PlusCircleIcon, MinusCircleIcon, ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/solid';

const page = usePage();

const app = computed(() => (page.props as any).app ?? {});
const access = computed(() => (page.props as any).access ?? {});
const context = computed(() => (page.props as any).context ?? {});
const flash = computed(() => (page.props as any).flash ?? {});

const toggling = ref(false);

const appIcons: Record<string, any> = {
    grownet: RocketLaunchIcon, growfinance: CurrencyDollarIcon,
    bizdocs: DocumentTextIcon, stockflow: ChartBarIcon,
    bms: BuildingOfficeIcon, bizboost: MegaphoneIcon,
    growmart: ShoppingBagIcon, growbuilder: GlobeAltIcon,
    growstorage: CloudIcon, zamstay: HomeModernIcon,
    primeedge: CommandLineIcon, lifeplus: BookOpenIcon,
    'quick-invoice': DocumentTextIcon,
};

const appColors: Record<string, string> = {
    grownet: 'from-green-500 to-green-600',
    growfinance: 'from-indigo-500 to-indigo-600',
    bizdocs: 'from-blue-500 to-blue-600',
    stockflow: 'from-teal-500 to-teal-600',
    bms: 'from-purple-500 to-purple-600',
    bizboost: 'from-orange-500 to-orange-600',
    growmart: 'from-red-500 to-red-600',
    growbuilder: 'from-cyan-500 to-cyan-600',
    growstorage: 'from-sky-500 to-sky-600',
    zamstay: 'from-emerald-500 to-emerald-600',
    primeedge: 'from-violet-500 to-violet-600',
    lifeplus: 'from-yellow-500 to-amber-600',
    'quick-invoice': 'from-blue-500 to-blue-600',
};

const appDescriptions: Record<string, string> = {
    bms: 'Business Management Suite — manage customers, invoices, inventory, expenses, budgets, loans, and staff across your entire operation.',
    stockflow: 'Stock management and inventory audit system for tracking items, sales, purchases, physical counts, cash registers, and stock adjustments.',
    growfinance: 'Full financial management with accounts, invoices, customers, budgets, and financial reporting for your business.',
    bizdocs: 'Document management system for storing, organizing, and sharing business documents securely.',
    growbuilder: 'Website builder and e-commerce platform for creating professional websites, online stores, and landing pages.',
    growmart: 'E-commerce marketplace for buying and selling products across Zambia with local delivery options.',
    grownet: 'Product platform hosting GrowStream (film & comedy), GrowLearn (practical skills), and GrowTemplates — digital products built for the Zambian market.',
    zamstay: 'Property booking platform for finding and reserving accommodations across Zambia.',
    primeedge: 'Investment advisory and financial planning tools to help you make informed investment decisions.',
    lifeplus: 'Health and wellness platform with fitness tracking, nutrition plans, and wellness resources.',
    bizboost: 'Business growth tools including marketing automation, WhatsApp integration, and customer engagement.',
    growstorage: 'Cloud storage service for backing up and accessing your files from anywhere.',
    'quick-invoice': 'Simple and fast invoicing tool for creating and sending professional invoices to your clients.',
};

const appFeatures: Record<string, string[]> = {
    bms: ['Customer management', 'Invoicing & billing', 'Inventory tracking', 'Expense management', 'Budget planning', 'Loan management', 'Staff & HR management'],
    stockflow: ['Item & inventory tracking', 'Sales recording', 'Purchase orders', 'Stock adjustments', 'Physical counts & audits', 'Cash register management', 'Expiry checks'],
    growfinance: ['Account management', 'Invoice creation', 'Customer tracking', 'Budget planning', 'Financial reporting', 'Transaction history'],
    bizdocs: ['Secure document storage', 'File organization', 'Document sharing', 'Version tracking', 'Search & filter'],
    growbuilder: ['Drag-and-drop site builder', 'E-commerce storefront', 'Custom domains', 'SEO tools', 'Analytics dashboard', 'Agency management'],
    growmart: ['Product listings', 'Shopping cart', 'Secure checkout', 'Order tracking', 'Local delivery', 'Vendor dashboard'],
    grownet: ['GrowStream — Zambian film & comedy streaming', 'GrowLearn — practical local skills training', 'GrowTemplates — business & career document templates', 'GrowBackup — cloud storage (coming soon)', 'GrowAI — AI-powered tools (coming soon)'],
    zamstay: ['Property search', 'Online booking', 'Payment processing', 'Booking management', 'Property listings'],
    primeedge: ['Investment advisory', 'Portfolio tracking', 'Market analysis', 'Financial planning', 'Risk assessment'],
    lifeplus: ['Fitness tracking', 'Nutrition planning', 'Wellness resources', 'Health goals', 'Progress monitoring'],
    bizboost: ['Marketing automation', 'WhatsApp integration', 'Customer engagement', 'Campaign management', 'Social media tools'],
    growstorage: ['File backup', 'File sharing', 'Secure storage', 'Cross-device access', 'Team folders'],
    'quick-invoice': ['Invoice creation', 'Invoice templates', 'Payment tracking', 'Client management', 'Invoice history'],
};

function launch() {
    router.post(route('workspace.launch', { application: app.value.id }));
}

function togglePin() {
    toggling.value = true;
    router.post(route('apps.toggle-pin'), { application_id: app.value.id }, {
        preserveScroll: true,
        onFinish: () => { toggling.value = false; },
    });
}
</script>

<template>
    <WorkspaceLayout>
        <Head :title="app.name" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Link
                :href="route('apps.catalog')"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-6 transition-colors"
            >
                <ArrowLeftIcon class="w-4 h-4" />
                Back to Catalog
            </Link>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div
                    :class="[
                        'px-6 sm:px-8 py-8 sm:py-10 bg-gradient-to-br text-white',
                        appColors[app.slug] || 'from-blue-500 to-blue-600',
                    ]"
                >
                    <div class="flex items-center gap-4 sm:gap-6">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm">
                            <component :is="appIcons[app.slug] || CubeIcon" class="w-8 h-8 sm:w-10 sm:h-10" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h1 class="text-2xl sm:text-3xl font-bold">{{ app.name }}</h1>
                            <p class="text-white/80 text-sm sm:text-base mt-1 capitalize">{{ app.category }} · {{ app.lifecycle }}</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 sm:px-8 py-6 space-y-8">
                    <!-- Flash message -->
                    <div v-if="flash.message" class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-700">
                        {{ flash.message }}
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 space-y-6">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 mb-2">About</h2>
                                <p class="text-gray-600 leading-relaxed">{{ appDescriptions[app.slug] || 'No description available.' }}</p>
                            </div>

                            <div v-if="appFeatures[app.slug]">
                                <h2 class="text-lg font-semibold text-gray-900 mb-3">Key Features</h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div v-for="feature in appFeatures[app.slug]" :key="feature"
                                        class="flex items-start gap-2 text-gray-600 text-sm"
                                    >
                                        <CheckCircleIcon class="w-4 h-4 text-green-500 mt-0.5 shrink-0" />
                                        <span>{{ feature }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                                <h3 class="text-sm font-semibold text-gray-900 mb-4">Access</h3>

                                <div v-if="access.can_access" class="space-y-3">
                                    <div class="flex items-center gap-2 text-sm text-green-600 font-medium">
                                        <CheckCircleIcon class="w-4 h-4" />
                                        Available
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        <template v-if="!app.subscription_required">This app is free to use.</template>
                                        <template v-else>An active subscription is required.</template>
                                    </p>
                                    <button @click="launch"
                                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-colors shadow-sm"
                                    >
                                        <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                                        Launch {{ app.name }}
                                    </button>
                                </div>

                                <div v-else class="space-y-3">
                                    <div class="flex items-center gap-2 text-sm text-amber-600 font-medium">
                                        <XCircleIcon class="w-4 h-4" />
                                        {{ access.reason || 'Unavailable' }}
                                    </div>

                                    <p v-if="access.reason === 'Requires Subscription'" class="text-xs text-gray-500">
                                        Subscribe to unlock full access to {{ app.name }} and all its features.
                                    </p>
                                    <p v-else-if="access.reason === 'Requires Organization'" class="text-xs text-gray-500">
                                        This app needs an organization context. Create or join an organization first.
                                    </p>
                                    <p v-else-if="access.reason === 'Not Installed for Organization'" class="text-xs text-gray-500">
                                        Ask your organization admin to install this app from the organization workspace.
                                    </p>
                                    <p v-else class="text-xs text-gray-500">
                                        This app is currently unavailable. Check back later.
                                    </p>

                                    <button
                                        v-if="context.type === 'organization' && access.reason === 'Not Installed for Organization'"
                                        @click="router.post(route('workspace.organization.install', { slug: context.organization_slug, application: app.id }))"
                                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-colors shadow-sm"
                                    >
                                        Install for Organization
                                    </button>
                                </div>

                                <hr class="my-4 border-gray-200" />

                                <div v-if="!app.requires_organization_context" class="space-y-3">
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Workspace</h4>
                                    <button @click="togglePin" :disabled="toggling"
                                        class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border transition-colors"
                                        :class="access.is_pinned
                                            ? 'text-red-600 border-red-200 hover:bg-red-50'
                                            : 'text-gray-700 border-gray-200 hover:bg-gray-50'"
                                    >
                                        <component :is="access.is_pinned ? MinusCircleIcon : PlusCircleIcon" class="w-4 h-4" />
                                        {{ access.is_pinned ? 'Remove from Workspace' : 'Add to Workspace' }}
                                    </button>
                                    <p class="text-xs text-gray-400">
                                        {{ access.is_pinned
                                            ? 'Pinned to your personal workspace for quick access.'
                                            : 'Add this app to your personal workspace for one-click access.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </WorkspaceLayout>
</template>
