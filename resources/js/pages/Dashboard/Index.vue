<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { 
    WalletIcon, 
    CubeIcon,
    AcademicCapIcon,
    GiftIcon,
    ShoppingCartIcon,
    SparklesIcon,
    UsersIcon,
    BanknotesIcon,
    ClipboardDocumentCheckIcon,
    HeartIcon,
    RocketLaunchIcon,
    ChevronRightIcon,
    GlobeAltIcon,
    WrenchScrewdriverIcon,
    DocumentTextIcon
} from '@heroicons/vue/24/solid';

interface Module {
    id: string;
    name: string;
    slug: string;
    color: string | null;
    has_access: boolean;
    primary_route: string;
    status: string;
    description?: string | null;
}

interface Props {
    walletBalance?: number;
    bonusBalance?: number;
    totalDeposits?: number;
    totalWithdrawals?: number;
    commissions?: number;
    profitShares?: number;
    modules?: Module[];
    user?: any;
    accountType?: string;
    isAdmin?: boolean;
    isManager?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    walletBalance: 0,
    bonusBalance: 0,
    totalDeposits: 0,
    totalWithdrawals: 0,
    commissions: 0,
    profitShares: 0,
    modules: () => [],
    isAdmin: false,
    isManager: false,
});

// Filter primary modules
const primaryModuleSlugs = ['bizboost', 'growfinance', 'growbiz', 'growmarket', 'growbuilder', 'grownet', 'lifeplus'];
const primaryModules = computed(() => {
    return props.modules
        .filter(m => primaryModuleSlugs.includes(m.slug) && m.has_access)
        .sort((a, b) => primaryModuleSlugs.indexOf(a.slug) - primaryModuleSlugs.indexOf(b.slug));
});

const additionalModules = computed(() => {
    return props.modules.filter(m => !primaryModuleSlugs.includes(m.slug) && m.has_access);
});

const formatCurrency = (amount: number | undefined | null) => {
    const value = amount ?? 0;
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(value);
};

const getModuleIcon = (slug: string) => {
    const iconMap: Record<string, any> = {
        'grownet': UsersIcon,
        'growbiz': ClipboardDocumentCheckIcon,
        'growfinance': BanknotesIcon,
        'bizboost': SparklesIcon,
        'growmarket': ShoppingCartIcon,
        'growbuilder': GlobeAltIcon,
        'lifeplus': HeartIcon,
    };
    return iconMap[slug] || CubeIcon;
};

const getModuleDescription = (slug: string): string => {
    const descriptions: Record<string, string> = {
        'bizboost': 'Business management & marketing',
        'growfinance': 'Accounting & financial management',
        'growbiz': 'Team & employee management',
        'growmarket': 'Shop products & services',
        'growbuilder': 'Build professional websites',
        'grownet': 'Community & referral rewards',
        'lifeplus': 'Health & wellness companion',
    };
    return descriptions[slug] || '';
};

const handleModuleClick = (module: Module) => {
    router.visit(module.primary_route);
};
</script>

<template>
    <ClientLayout :is-admin="isAdmin" :is-manager="isManager">
        <Head title="Dashboard" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left Column - Main Content -->
                <div class="flex-1 min-w-0">
                    <!-- Welcome Banner -->
                    <div class="mb-6 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <h1 class="text-2xl font-bold mb-1">Welcome back, {{ user?.name || 'Member' }}!</h1>
                                <p class="text-blue-100 text-sm">Your business empowerment platform</p>
                            </div>
                            <RocketLaunchIcon class="w-12 h-12 text-blue-200 opacity-50 hidden sm:block" aria-hidden="true" />
                        </div>
                    </div>

                    <!-- Primary Apps Section -->
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-4">
                            <SparklesIcon class="w-5 h-5 text-blue-600" aria-hidden="true" />
                            <h2 class="text-lg font-bold text-gray-900">Your Business Tools</h2>
                        </div>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <button
                                v-for="module in primaryModules"
                                :key="module.id"
                                @click="handleModuleClick(module)"
                                class="group relative bg-white rounded-xl p-4 flex flex-col items-center justify-center gap-3 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 active:scale-95 shadow-sm border border-gray-100"
                            >
                                <div 
                                    class="w-12 h-12 flex items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110"
                                    :style="{ backgroundColor: module.color || '#3B82F6' }"
                                >
                                    <component 
                                        :is="getModuleIcon(module.slug)" 
                                        class="w-6 h-6 text-white"
                                        aria-hidden="true"
                                    />
                                </div>
                                
                                <span class="text-gray-800 text-center font-semibold text-sm">
                                    {{ module.name }}
                                </span>

                                <span v-if="getModuleDescription(module.slug)" class="text-[11px] text-gray-500 text-center leading-tight">
                                    {{ getModuleDescription(module.slug) }}
                                </span>

                                <span 
                                    v-if="module.status === 'beta'" 
                                    class="absolute top-2 right-2 px-1.5 py-0.5 text-[9px] font-bold uppercase bg-amber-100 text-amber-700 rounded"
                                >
                                    Beta
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Additional Services -->
                    <div v-if="additionalModules.length > 0">
                        <div class="flex items-center gap-2 mb-4">
                            <CubeIcon class="w-5 h-5 text-gray-600" aria-hidden="true" />
                            <h2 class="text-lg font-bold text-gray-900">Additional Services</h2>
                        </div>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <button
                                v-for="module in additionalModules"
                                :key="module.id"
                                @click="handleModuleClick(module)"
                                class="group relative bg-white rounded-xl p-4 flex flex-col items-center justify-center gap-3 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 active:scale-95 shadow-sm border border-gray-100"
                            >
                                <div 
                                    class="w-12 h-12 flex items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110"
                                    :style="{ backgroundColor: module.color || '#3B82F6' }"
                                >
                                    <component 
                                        :is="getModuleIcon(module.slug)" 
                                        class="w-6 h-6 text-white"
                                        aria-hidden="true"
                                    />
                                </div>
                                
                                <span class="text-gray-800 text-center font-semibold text-sm">
                                    {{ module.name }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Free Tools Section -->
                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <WrenchScrewdriverIcon class="w-5 h-5 text-amber-600" aria-hidden="true" />
                                <h2 class="text-lg font-bold text-gray-900">Free Tools</h2>
                            </div>
                            <Link 
                                href="/tools" 
                                class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1"
                            >
                                View All
                                <ChevronRightIcon class="w-4 h-4" aria-hidden="true" />
                            </Link>
                        </div>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <!-- Quick Invoice -->
                            <a
                                href="/quick-invoice"
                                class="group relative bg-white rounded-xl p-4 flex flex-col items-center justify-center gap-3 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 active:scale-95 shadow-sm border border-gray-100"
                            >
                                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-amber-500 transition-transform duration-300 group-hover:scale-110">
                                    <DocumentTextIcon class="w-6 h-6 text-white" aria-hidden="true" />
                                </div>
                                
                                <span class="text-gray-800 text-center font-semibold text-sm">
                                    Quick Invoice
                                </span>

                                <span class="text-[11px] text-gray-500 text-center leading-tight">
                                    Create invoices & receipts
                                </span>

                                <span class="absolute top-2 right-2 px-1.5 py-0.5 text-[9px] font-bold uppercase bg-green-100 text-green-700 rounded">
                                    Free
                                </span>
                            </a>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="primaryModules.length === 0" class="text-center py-16">
                        <CubeIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No apps available</h3>
                        <p class="text-gray-500">Contact support for assistance.</p>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="lg:w-72 flex-shrink-0">
                    <div class="lg:sticky lg:top-24 space-y-4">
                        <!-- Wallet Card -->
                        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-4 text-white shadow-md">
                            <div class="flex items-center gap-2 mb-2">
                                <WalletIcon class="w-5 h-5 text-green-100" aria-hidden="true" />
                                <span class="text-sm text-green-100">Wallet Balance</span>
                            </div>
                            <p class="text-2xl font-bold mb-3">{{ formatCurrency(walletBalance) }}</p>
                            <Link 
                                href="/wallet" 
                                class="flex items-center justify-center gap-1 w-full bg-white/20 hover:bg-white/30 text-white text-sm font-medium py-2 rounded-lg transition-colors"
                            >
                                View Wallet
                                <ChevronRightIcon class="w-4 h-4" aria-hidden="true" />
                            </Link>
                        </div>

                        <!-- Quick Stats -->
                        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Quick Stats</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <CubeIcon class="w-4 h-4 text-blue-500" aria-hidden="true" />
                                        <span class="text-sm text-gray-600">Active Apps</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">{{ primaryModules.length + additionalModules.length }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <AcademicCapIcon class="w-4 h-4 text-emerald-500" aria-hidden="true" />
                                        <span class="text-sm text-gray-600">Training</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">0</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <GiftIcon class="w-4 h-4 text-indigo-500" aria-hidden="true" />
                                        <span class="text-sm text-gray-600">Rewards</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900">0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Quick Actions</h3>
                            <div class="space-y-2">
                                <Link
                                    href="/wallet"
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <WalletIcon class="w-5 h-5 text-green-600" aria-hidden="true" />
                                    <span class="text-sm text-gray-700">My Wallet</span>
                                </Link>
                                <Link
                                    href="/marketplace"
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <ShoppingCartIcon class="w-5 h-5 text-blue-600" aria-hidden="true" />
                                    <span class="text-sm text-gray-700">Marketplace</span>
                                </Link>
                                <Link
                                    href="/training"
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <AcademicCapIcon class="w-5 h-5 text-emerald-600" aria-hidden="true" />
                                    <span class="text-sm text-gray-700">Learning</span>
                                </Link>
                                <Link
                                    href="/grownet/orders"
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <ClipboardDocumentCheckIcon class="w-5 h-5 text-purple-600" aria-hidden="true" />
                                    <span class="text-sm text-gray-700">Orders</span>
                                </Link>
                                <Link
                                    href="/rewards"
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <GiftIcon class="w-5 h-5 text-indigo-600" aria-hidden="true" />
                                    <span class="text-sm text-gray-700">Rewards</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
