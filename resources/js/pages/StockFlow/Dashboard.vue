<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import { computed, ref } from 'vue';
import {
    ArchiveBoxIcon, CreditCardIcon, ShoppingCartIcon,
    CurrencyDollarIcon, ClipboardDocumentListIcon, DocumentTextIcon,
    ArrowTrendingUpIcon, BuildingStorefrontIcon, BuildingOfficeIcon, CubeIcon,
    SparklesIcon, CheckCircleIcon, ClockIcon, ExclamationCircleIcon, ExclamationTriangleIcon,
    XCircleIcon, HomeModernIcon, ChevronRightIcon, PlusIcon,
    ArrowPathIcon, TrendingUpIcon, MagnifyingGlassIcon,
    Cog6ToothIcon, BellIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolid } from '@heroicons/vue/24/solid';

interface Company {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    city: string | null;
    country: string | null;
    currency: string;
    status: string;
    contact_person: string | null;
}

interface CashRegister {
    id: number;
    register_date: string;
    status: string;
    opening_balance: number;
    total_sales: number;
    total_expenses: number;
    total_banking: number;
    expected_closing: number;
    actual_closing: number | null;
    variance: number | null;
    opened_by: number;
    closed_by: number | null;
}

interface Item {
    id: number;
    name: string;
    sku: string | null;
    system_quantity: number;
    reorder_level: number | null;
    unit_price: number;
    category: string | null;
}

interface PurchaseOrder {
    id: number;
    order_number: string;
    order_date: string;
    status: string;
    total: number;
    supplier_name?: string;
}

interface PhysicalCount {
    id: number;
    count_date: string;
    status: string;
}

interface Audit {
    id: number;
    title: string;
    audit_date: string;
    status: string;
}

interface Props {
    company: Company | null;
    companies: Company[];
    stats: {
        total_items: number;
        total_branches: number;
        total_audits: number;
        total_physical_counts: number;
        total_system_value: number;
        low_stock_count: number;
        out_of_stock_count: number;
        pending_po_count: number;
        partial_po_count: number;
        in_progress_count: number;
        unresolved_audit_count: number;
        todays_sales: number;
        has_open_register: boolean;
    };
    open_register: CashRegister | null;
    low_stock_items: Item[];
    out_of_stock_items: Item[];
    pending_pos: PurchaseOrder[];
    partial_pos: PurchaseOrder[];
    in_progress_counts: PhysicalCount[];
    unresolved_audits: Audit[];
    recent_audits: Audit[];
    recent_counts: PhysicalCount[];
}

const props = defineProps<Props>();

const page = usePage();
const isSubdomain = computed(() => {
    const routeName = page.props.routeName ?? '';
    return routeName.startsWith('stockflow.sub.');
});

const sf = (path: string) => isSubdomain.value ? path : '/stockflow' + path;

const statusConfig: Record<string, { label: string; icon: any; class: string }> = {
    active: { label: 'Active', icon: CheckCircleSolid, class: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' },
    trial: { label: 'Trial', icon: SparklesIcon, class: 'bg-blue-50 text-blue-700 ring-blue-600/20' },
    pending: { label: 'Pending Activation', icon: ClockIcon, class: 'bg-amber-50 text-amber-700 ring-amber-600/20' },
    suspended: { label: 'Suspended', icon: XCircleIcon, class: 'bg-red-50 text-red-700 ring-red-600/20' },
    inactive: { label: 'Inactive', icon: ExclamationCircleIcon, class: 'bg-gray-50 text-gray-700 ring-gray-600/20' },
};

const companyStatus = computed(() => {
    return statusConfig[props.company?.status || ''] || statusConfig.inactive;
});

const moduleGroups = computed(() => [
    {
        title: 'Operations',
        items: [
            { name: 'Items', href: sf('/items'), icon: ArchiveBoxIcon, desc: 'Manage your stock inventory', color: 'emerald' },
            { name: 'Sales', href: sf('/sales'), icon: CreditCardIcon, desc: 'Record daily transactions', color: 'blue' },
            { name: 'Purchases', href: sf('/purchases'), icon: ShoppingCartIcon, desc: 'Purchase orders & receiving', color: 'amber' },
            { name: 'Cash Register', href: sf('/cash'), icon: CurrencyDollarIcon, desc: 'Daily cash management', color: 'teal' },
        ],
    },
    {
        title: 'Audit & Control',
        items: [
            { name: 'Stock Movements', href: sf('/movements'), icon: ArrowTrendingUpIcon, desc: 'Complete stock change ledger', color: 'purple' },
            { name: 'Physical Counts', href: sf('/physical-counts'), icon: ClipboardDocumentListIcon, desc: 'Count and verify stock', color: 'violet' },
            { name: 'Audits', href: sf('/audits'), icon: DocumentTextIcon, desc: 'Generate audit reports', color: 'indigo' },
        ],
    },
    {
        title: 'Administration',
        items: [
            { name: 'Suppliers', href: sf('/suppliers'), icon: BuildingStorefrontIcon, desc: 'Manage vendor records', color: 'cyan' },
            { name: 'Branches', href: sf('/branches'), icon: HomeModernIcon, desc: 'Manage branch locations', color: 'pink' },
            { name: 'Departments', href: sf('/departments'), icon: BuildingOfficeIcon, desc: 'Organize by department', color: 'rose' },
            { name: 'Bins', href: sf('/bins'), icon: CubeIcon, desc: 'Storage locations', color: 'orange' },
        ],
    },
]);

const colorMap: Record<string, { bg: string; icon: string; hover: string }> = {
    emerald: { bg: 'bg-emerald-50', icon: 'text-emerald-600', hover: 'hover:bg-emerald-50' },
    blue: { bg: 'bg-blue-50', icon: 'text-blue-600', hover: 'hover:bg-blue-50' },
    amber: { bg: 'bg-amber-50', icon: 'text-amber-600', hover: 'hover:bg-amber-50' },
    teal: { bg: 'bg-teal-50', icon: 'text-teal-600', hover: 'hover:bg-teal-50' },
    purple: { bg: 'bg-purple-50', icon: 'text-purple-600', hover: 'hover:bg-purple-50' },
    violet: { bg: 'bg-violet-50', icon: 'text-violet-600', hover: 'hover:bg-violet-50' },
    indigo: { bg: 'bg-indigo-50', icon: 'text-indigo-600', hover: 'hover:bg-indigo-50' },
    cyan: { bg: 'bg-cyan-50', icon: 'text-cyan-600', hover: 'hover:bg-cyan-50' },
    rose: { bg: 'bg-rose-50', icon: 'text-rose-600', hover: 'hover:bg-rose-50' },
    orange: { bg: 'bg-orange-50', icon: 'text-orange-600', hover: 'hover:bg-orange-50' },
    pink: { bg: 'bg-pink-50', icon: 'text-pink-600', hover: 'hover:bg-pink-50' },
};

const { formatCurrency } = useCurrency();

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-ZM', { month: 'short', day: 'numeric', year: 'numeric' });
};

const switchCompany = (companyId: number) => {
    router.post(route('stockflow.sub.switch-company'), { company_id: companyId });
};

const openCompanyDropdown = ref(false);

const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 12) return 'Good morning';
    if (h < 17) return 'Good afternoon';
    return 'Good evening';
});

// Quick actions
const quickActions = [
    { label: 'Open Register', href: sf('/cash'), icon: CurrencyDollarIcon, color: 'emerald', show: props.company && !props.stats.has_open_register },
    { label: 'Record Sale', href: sf('/sales/create'), icon: CreditCardIcon, color: 'blue', show: props.company },
    { label: 'Create PO', href: sf('/purchases/create'), icon: ShoppingCartIcon, color: 'amber', show: props.company },
    { label: 'Start Count', href: sf('/physical-counts'), icon: ClipboardDocumentListIcon, color: 'violet', show: props.company },
];

// Alert items for dashboard
const alertItems = computed(() => {
    const alerts = [];
    if (props.stats.low_stock_count > 0) {
        alerts.push({
            label: `${props.stats.low_stock_count} Low Stock`,
            icon: ExclamationTriangleIcon,
            color: 'amber',
            href: sf('/items?filter=low_stock'),
        });
    }
    if (props.stats.out_of_stock_count > 0) {
        alerts.push({
            label: `${props.stats.out_of_stock_count} Out of Stock`,
            icon: XCircleIcon,
            color: 'red',
            href: sf('/items?filter=out_of_stock'),
        });
    }
    if (props.stats.pending_po_count > 0 || props.stats.partial_po_count > 0) {
        alerts.push({
            label: `${props.stats.pending_po_count + props.stats.partial_po_count} POs Pending`,
            icon: ArrowPathIcon,
            color: 'blue',
            href: sf('/purchases'),
        });
    }
    if (props.stats.in_progress_count > 0) {
        alerts.push({
            label: `${props.stats.in_progress_count} Counts In Progress`,
            icon: ClipboardDocumentListIcon,
            color: 'violet',
            href: sf('/physical-counts'),
        });
    }
    if (props.stats.unresolved_audit_count > 0) {
        alerts.push({
            label: `${props.stats.unresolved_audit_count} Unresolved Audits`,
            icon: DocumentTextIcon,
            color: 'indigo',
            href: sf('/audits'),
        });
    }
    return alerts;
});

const recentActivity = computed(() => {
    const activities = [];
    // Add recent audits
    props.recent_audits.forEach(a => {
        activities.push({
            type: 'audit',
            title: `Audit: ${a.title}`,
            date: a.audit_date,
            status: a.status,
            href: sf('/audits/' + a.id),
        });
    });
    // Add recent counts
    props.recent_counts.forEach(c => {
        activities.push({
            type: 'count',
            title: `Physical Count`,
            date: c.count_date,
            status: c.status,
            href: sf('/physical-counts/' + c.id),
        });
    });
    // Sort by date descending
    return activities.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime()).slice(0, 10);
});
</script>

<template>
    <StockFlowLayout title="Dashboard">
        <Head title="StockFlow Dashboard" />

        <div class="min-h-screen bg-gray-50">
            <!-- Hero -->
            <div v-if="company" class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-emerald-700 to-emerald-900">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxkZWZzPjxwYXR0ZXJuIGlkPSJhIiBwYXR0ZXJuVW5pdHM9InVzZXJTcGFjZU9uVXNlIiB3aWR0aD0iODAiIGhlaWdodD0iODAiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSgzMCkiPjxwYXRoIGQ9Ik0wIDQwaDgwTTQwIDB2ODAiIHN0cm9rZT0iI2ZmZiIgc3Ryb2tlLW9wYWNpdHk9Ii4wMyIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2EpIi8+PC9zdmc+')] opacity-50"></div>
                <div class="absolute top-0 right-0 -mt-16 -mr-16 h-64 w-64 rounded-full bg-white/5"></div>
                <div class="absolute bottom-0 left-1/3 -mb-20 -ml-20 h-48 w-48 rounded-full bg-white/5"></div>
                <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-5">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm text-2xl font-bold text-white shadow-lg ring-1 ring-white/25">
                                {{ company.name.charAt(0) }}
                            </div>
                            <div class="space-y-1.5">
                                <p class="text-sm font-medium text-emerald-100">{{ greeting }},</p>
                                <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ company.name }}</h1>
                                <div class="flex flex-wrap items-center gap-2.5 text-sm text-emerald-100">
                                    <span v-if="company.city || company.country" class="flex items-center gap-1">
                                        <HomeModernIcon class="h-3.5 w-3.5" />
                                        {{ [company.city, company.country].filter(Boolean).join(', ') }}
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <component :is="companyStatus.icon" class="h-4 w-4" />
                                        <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset', companyStatus.class]">
                                            {{ companyStatus.label }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-if="companies.length > 1" class="flex-shrink-0">
                            <div class="relative" @click="openCompanyDropdown = !openCompanyDropdown">
                                <button type="button" class="flex items-center gap-2 rounded-lg border-0 bg-white/20 backdrop-blur-sm px-4 py-2.5 text-sm text-white placeholder-emerald-200 shadow-sm ring-1 ring-inset ring-white/25 focus:outline-none focus:ring-2 focus:ring-white/50" aria-haspopup="true" :aria-expanded="openCompanyDropdown">
                                    <span class="truncate max-w-[180px]">{{ company.name }}</span>
                                    <ChevronRightIcon class="h-4 w-4 flex-shrink-0" :class="{ 'rotate-90': openCompanyDropdown }" />
                                </button>
                                <div v-if="openCompanyDropdown" class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-none animate-in fade-in-0 zoom-in-95" role="menu" aria-orientation="vertical">
                                    <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b">Switch Company</div>
                                    <button v-for="c in companies" :key="c.id" @click="switchCompany(c.id)" class="w-full text-left px-3 py-2 text-sm" :class="[c.id === company.id ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-gray-50']" role="menuitem">
                                        {{ c.name }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 -mt-4 relative z-10 pb-10">
                <!-- Stats Bar -->
                <div v-if="company" class="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-5">
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-emerald-50 p-2.5">
                                <ArchiveBoxIcon class="h-5 w-5 text-emerald-600" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Items</p>
                                <p class="text-xl font-bold text-gray-900">{{ stats.total_items }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-blue-50 p-2.5">
                                <CurrencyDollarIcon class="h-5 w-5 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Stock Value</p>
                                <p class="text-xl font-bold text-gray-900">{{ formatCurrency(stats.total_system_value) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-violet-50 p-2.5">
                                <ClipboardDocumentListIcon class="h-5 w-5 text-violet-600" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Counts</p>
                                <p class="text-xl font-bold text-gray-900">{{ stats.total_physical_counts }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-indigo-50 p-2.5">
                                <DocumentTextIcon class="h-5 w-5 text-indigo-600" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Audits</p>
                                <p class="text-xl font-bold text-gray-900">{{ stats.total_audits }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-teal-50 p-2.5">
                                <CurrencyDollarIcon class="h-5 w-5 text-teal-600" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Today's Sales</p>
                                <p class="text-xl font-bold text-gray-900">{{ formatCurrency(stats.todays_sales) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alerts / Action Required -->
                <div v-if="company && alertItems.length > 0" class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-px flex-1 bg-gradient-to-r from-gray-200 to-transparent"></div>
                        <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">Action Required</span>
                        <div class="h-px flex-1 bg-gradient-to-l from-gray-200 to-transparent"></div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <Link v-for="alert in alertItems" :key="alert.label" :href="alert.href" class="group relative overflow-hidden rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                            <div class="flex items-center gap-3">
                                <div :class="['rounded-lg p-3 ring-1 ring-inset ring-black/5 transition-colors', `bg-${alert.color}-50 text-${alert.color}-600`, 'group-hover:ring-black/10']">
                                    <component :is="alert.icon" class="h-6 w-6" aria-hidden="true" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-900 group-hover:text-emerald-600 transition-colors">{{ alert.label }}</p>
                                </div>
                                <ChevronRightIcon class="mt-1 h-4 w-4 flex-shrink-0 text-gray-300 transition-all group-hover:translate-x-0.5 group-hover:text-emerald-500" />
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Open Cash Register Status -->
                <div v-if="company && open_register" class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-px flex-1 bg-gradient-to-r from-gray-200 to-transparent"></div>
                        <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">Today's Cash Register</span>
                        <div class="h-px flex-1 bg-gradient-to-l from-gray-200 to-transparent"></div>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center">
                                <p class="text-xs font-medium text-emerald-700 uppercase tracking-wide">Opening Balance</p>
                                <p class="text-lg font-bold text-emerald-900">{{ formatCurrency(open_register.opening_balance) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs font-medium text-emerald-700 uppercase tracking-wide">Sales</p>
                                <p class="text-lg font-bold text-emerald-900">{{ formatCurrency(open_register.total_sales) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs font-medium text-emerald-700 uppercase tracking-wide">Expenses</p>
                                <p class="text-lg font-bold text-emerald-900">{{ formatCurrency(open_register.total_expenses) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs font-medium text-emerald-700 uppercase tracking-wide">Expected Close</p>
                                <p class="text-lg font-bold text-emerald-900">{{ formatCurrency(open_register.expected_closing) }}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-emerald-200 flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800">
                                <CheckCircleSolid class="h-3 w-3" />
                                Register Open
                            </span>
                            <Link :href="sf('/cash/' + open_register.id)" class="text-sm font-semibold text-emerald-700 hover:text-emerald-600 transition-colors">
                                View Details <ChevronRightIcon class="inline h-4 w-4 ml-1" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div v-if="company" class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-px flex-1 bg-gradient-to-r from-gray-200 to-transparent"></div>
                        <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">Quick Actions</span>
                        <div class="h-px flex-1 bg-gradient-to-l from-gray-200 to-transparent"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <template v-for="action in quickActions" :key="action.label">
                            <Link v-if="action.show" :href="action.href" class="group relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 flex flex-col items-center text-center">
                                <div :class="[colorMap[action.color].bg, 'rounded-lg p-3 ring-1 ring-inset ring-black/5 transition-colors group-hover:ring-black/10']">
                                    <component :is="action.icon" :class="[colorMap[action.color].icon, 'h-6 w-6']" aria-hidden="true" />
                                </div>
                                <p class="mt-3 text-sm font-semibold text-gray-900 group-hover:text-emerald-600 transition-colors">{{ action.label }}</p>
                            </Link>
                        </template>
                    </div>
                </div>

                <!-- Detailed Sections (Low Stock, Pending POs, etc.) -->
                <div v-if="company && (low_stock_items.length > 0 || pending_pos.length > 0 || in_progress_counts.length > 0 || unresolved_audits.length > 0)" class="mb-8 space-y-8">
                    <!-- Low Stock -->
                    <div v-if="low_stock_items.length > 0" class="rounded-xl border border-gray-100 bg-white shadow-sm">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">Low Stock Items</h3>
                            <Link :href="sf('/items?filter=low_stock')" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">View all</Link>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div v-for="item in low_stock_items.slice(0, 5)" :key="item.id" class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</p>
                                    <p class="text-xs text-gray-500">Qty: {{ item.system_quantity }} | Reorder: {{ item.reorder_level }}</p>
                                </div>
                                <Link :href="sf('/items/' + item.id)" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">View</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pending POs -->
                    <div v-if="pending_pos.length > 0 || partial_pos.length > 0" class="rounded-xl border border-gray-100 bg-white shadow-sm">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">Pending Purchase Orders</h3>
                            <Link :href="sf('/purchases')" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">View all</Link>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div v-for="po in [...pending_pos.slice(0, 3), ...partial_pos.slice(0, 2)]" :key="po.id" class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ po.order_number }}</p>
                                    <p class="text-xs text-gray-500">{{ po.supplier_name || 'Unknown Supplier' }} • {{ formatCurrency(po.total) }}</p>
                                </div>
                                <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', po.status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700']">
                                    {{ po.status.charAt(0).toUpperCase() + po.status.slice(1) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- In Progress Counts -->
                    <div v-if="in_progress_counts.length > 0" class="rounded-xl border border-gray-100 bg-white shadow-sm">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">Physical Counts In Progress</h3>
                            <Link :href="sf('/physical-counts')" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">View all</Link>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div v-for="count in in_progress_counts.slice(0, 5)" :key="count.id" class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Count #{{ count.id }}</p>
                                    <p class="text-xs text-gray-500">{{ formatDate(count.count_date) }}</p>
                                </div>
                                <Link :href="sf('/physical-counts/' + count.id)" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Continue</Link>
                            </div>
                        </div>
                    </div>

                    <!-- Unresolved Audits -->
                    <div v-if="unresolved_audits.length > 0" class="rounded-xl border border-gray-100 bg-white shadow-sm">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">Unresolved Audits</h3>
                            <Link :href="sf('/audits')" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">View all</Link>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div v-for="audit in unresolved_audits.slice(0, 5)" :key="audit.id" class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ audit.title }}</p>
                                    <p class="text-xs text-gray-500">{{ formatDate(audit.audit_date) }} • {{ audit.status }}</p>
                                </div>
                                <Link :href="sf('/audits/' + audit.id)" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Review</Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div v-if="company && recentActivity.length > 0" class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-px flex-1 bg-gradient-to-r from-gray-200 to-transparent"></div>
                        <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">Recent Activity</span>
                        <div class="h-px flex-1 bg-gradient-to-l from-gray-200 to-transparent"></div>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white shadow-sm divide-y divide-gray-100">
                        <Link v-for="activity in recentActivity" :key="activity.type + activity.date + activity.title" :href="activity.href" class="px-5 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                            <div class="flex-shrink-0">
                                <component :is="activity.type === 'audit' ? DocumentTextIcon : ClipboardDocumentListIcon" class="h-6 w-6 text-gray-400" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ activity.title }}</p>
                                <p class="text-xs text-gray-500">{{ formatDate(activity.date) }} • {{ activity.status }}</p>
                            </div>
                            <ChevronRightIcon class="h-4 w-4 text-gray-300" />
                        </Link>
                    </div>
                </div>

                <!-- Module Navigation -->
                <div v-if="company" v-for="group in moduleGroups" :key="group.title" class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-px flex-1 bg-gradient-to-r from-gray-200 to-transparent"></div>
                        <span class="text-xs font-semibold uppercase tracking-widest text-gray-400">{{ group.title }}</span>
                        <div class="h-px flex-1 bg-gradient-to-l from-gray-200 to-transparent"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4" :class="{ 'lg:grid-cols-3': group.items.length <= 3 }">
                        <Link
                            v-for="mod in group.items"
                            :key="mod.name"
                            :href="mod.href"
                            class="group relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
                        >
                            <div class="flex items-start gap-4">
                                <div :class="[colorMap[mod.color].bg, 'rounded-lg p-3 ring-1 ring-inset ring-black/5 transition-colors group-hover:ring-black/10']">
                                    <component :is="mod.icon" :class="[colorMap[mod.color].icon, 'h-6 w-6']" aria-hidden="true" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-900 group-hover:text-emerald-600 transition-colors">{{ mod.name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 leading-relaxed">{{ mod.desc }}</p>
                                </div>
                                <ChevronRightIcon class="mt-1 h-4 w-4 flex-shrink-0 text-gray-300 transition-all group-hover:translate-x-0.5 group-hover:text-emerald-500" />
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!company && companies.length === 0" class="mt-12 text-center">
                    <div class="mx-auto max-w-md">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 ring-1 ring-emerald-200">
                            <SparklesIcon class="h-10 w-10 text-emerald-500" />
                        </div>
                        <h2 class="mt-6 text-xl font-semibold text-gray-900">Welcome to StockFlow</h2>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                            Your company account is being set up. Once activated by an administrator, you'll find all your inventory management tools here.
                        </p>
                        <div class="mt-8 flex justify-center gap-6 text-xs text-gray-400">
                            <span class="flex items-center gap-1.5"><CheckCircleIcon class="h-4 w-4 text-emerald-400" /> Items & Stock</span>
                            <span class="flex items-center gap-1.5"><CheckCircleIcon class="h-4 w-4 text-emerald-400" /> Sales & Purchases</span>
                            <span class="flex items-center gap-1.5"><CheckCircleIcon class="h-4 w-4 text-emerald-400" /> Audits & Reports</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>