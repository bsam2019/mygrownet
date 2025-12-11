<template>
    <!-- PWA Welcome Screen - Shows only when app is opened as installed PWA -->
    <PWAWelcomeScreen module="growfinance" :duration="2500" storage-key="growfinance-pwa-welcome-shown" />
    
    <GrowFinanceLayout>
        <!-- Desktop Layout -->
        <div class="hidden lg:block px-6 py-6">
            <!-- Welcome Header with Date -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ userName }}!</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ currentDate }}</p>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live data</span>
                </div>
            </div>

            <!-- Financial Summary Cards - Desktop Style -->
            <div class="grid grid-cols-3 gap-4 mb-4">
                <!-- Total Income Card -->
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-5 text-white relative overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -mr-12 -mt-12"></div>
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-white/90">Total Income</p>
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                            <ArrowUpIcon class="h-4 w-4" aria-hidden="true" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold mb-1">{{ formatMoney(financialSummary.monthly_income) }}</p>
                    <div class="flex items-center gap-1 text-xs text-white/80">
                        <ArrowUpIcon class="h-3 w-3" aria-hidden="true" />
                        <span>+{{ incomeChangePercent }}% from last month</span>
                    </div>
                </div>
                
                <!-- Total Expenses Card -->
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-5 text-white relative overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -mr-12 -mt-12"></div>
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-white/90">Total Expenses</p>
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                            <ArrowDownIcon class="h-4 w-4" aria-hidden="true" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold mb-1">{{ formatMoney(financialSummary.monthly_expenses) }}</p>
                    <div class="flex items-center gap-1 text-xs text-white/80">
                        <ArrowDownIcon class="h-3 w-3" aria-hidden="true" />
                        <span>+{{ expenseChangePercent }}% from last month</span>
                    </div>
                </div>
                
                <!-- Net Balance Card -->
                <div class="bg-white rounded-xl p-5 border border-gray-200 relative overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gray-100/50 rounded-full -mr-12 -mt-12"></div>
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-gray-600">Net Balance</p>
                        <div :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center',
                            financialSummary.net_income >= 0 ? 'bg-emerald-100' : 'bg-red-100'
                        ]">
                            <ChartBarSquareIcon :class="[
                                'h-4 w-4',
                                financialSummary.net_income >= 0 ? 'text-emerald-600' : 'text-red-600'
                            ]" aria-hidden="true" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold mb-1" :class="financialSummary.net_income >= 0 ? 'text-gray-900' : 'text-red-600'">
                        {{ formatMoney(financialSummary.net_income) }}
                    </p>
                    <div class="flex items-center gap-1 text-xs text-gray-500">
                        <span>{{ financialSummary.net_income >= 0 ? 'Profit' : 'Loss' }} this month</span>
                    </div>
                </div>
            </div>

            <!-- Secondary Stats Row -->
            <div class="grid grid-cols-4 gap-3 mb-6">
                <div class="bg-white rounded-lg p-3 border border-gray-100 hover:border-gray-200 transition-colors">
                    <p class="text-xs font-medium text-gray-500 mb-0.5">Cash on Hand</p>
                    <p class="text-base font-bold text-gray-900">{{ formatMoney(financialSummary.total_cash) }}</p>
                </div>
                <div class="bg-white rounded-lg p-3 border border-gray-100 hover:border-gray-200 transition-colors">
                    <p class="text-xs font-medium text-gray-500 mb-0.5">Receivables</p>
                    <p class="text-base font-bold text-blue-600">{{ formatMoney(financialSummary.accounts_receivable) }}</p>
                </div>
                <div class="bg-white rounded-lg p-3 border border-gray-100 hover:border-gray-200 transition-colors">
                    <p class="text-xs font-medium text-gray-500 mb-0.5">Payables</p>
                    <p class="text-base font-bold text-amber-600">{{ formatMoney(financialSummary.accounts_payable) }}</p>
                </div>
                <div class="bg-white rounded-lg p-3 border border-gray-100 hover:border-gray-200 transition-colors">
                    <p class="text-xs font-medium text-gray-500 mb-0.5">Overdue</p>
                    <p class="text-base font-bold" :class="invoiceStats.overdue > 0 ? 'text-red-600' : 'text-gray-900'">
                        {{ invoiceStats.overdue || 0 }} invoices
                    </p>
                </div>
            </div>

            <!-- Financial Overview Chart -->
            <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">Financial Overview</h3>
                    <div class="flex items-center gap-4 text-xs">
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                            <span class="text-gray-600">Income</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <span class="text-gray-600">Expenses</span>
                        </div>
                    </div>
                </div>
                <div class="h-56">
                    <canvas ref="chartCanvas"></canvas>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Recent Transactions</h3>
                    <Link 
                        :href="route('growfinance.sales.index')"
                        class="text-sm text-emerald-600 hover:text-emerald-700 font-medium"
                    >
                        View All
                    </Link>
                </div>
                <ul v-if="recentTransactions.length > 0" class="divide-y divide-gray-50">
                    <li v-for="tx in recentTransactions.slice(0, 5)" :key="`${tx.type}-${tx.id}`" class="px-5 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center',
                                    tx.type === 'income' ? 'bg-emerald-100' : 'bg-red-100'
                                ]">
                                    <component 
                                        :is="tx.type === 'income' ? CheckCircleIcon : ExclamationCircleIcon" 
                                        :class="[
                                            'h-5 w-5',
                                            tx.type === 'income' ? 'text-emerald-600' : 'text-red-600'
                                        ]"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ tx.type === 'income' ? `Invoice #${tx.invoice_number || 'INV-2025-' + String(tx.id).padStart(3, '0')}` : tx.description }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Status: <span :class="tx.type === 'income' ? 'text-emerald-600' : 'text-red-600'">{{ tx.status || 'Paid' }}</span>
                                    </p>
                                </div>
                            </div>
                            <p :class="['font-semibold text-base', tx.type === 'income' ? 'text-emerald-600' : 'text-red-600']">
                                {{ formatMoney(tx.amount) }}
                            </p>
                        </div>
                    </li>
                </ul>
                <div v-else class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <BanknotesIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
                    </div>
                    <p class="text-gray-500 mb-2">No transactions yet</p>
                    <p class="text-sm text-gray-400">Start by recording your first sale or expense</p>
                </div>
            </div>
        </div>

        <!-- Mobile Layout (unchanged) -->
        <div class="lg:hidden px-4 py-4 pb-6">
            <!-- Welcome Header -->
            <div class="mb-5">
                <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ userName }}!</h1>
            </div>

            <!-- Financial Summary Cards - 3 horizontal cards -->
            <div class="flex gap-2.5 mb-6 overflow-x-auto pb-1 -mx-1 px-1">
                <!-- Total Income Card -->
                <div class="stat-card flex-1 min-w-[105px] bg-emerald-500 rounded-xl p-3 text-white shadow-md">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-medium text-white/90">Total Income</span>
                    </div>
                    <p class="text-lg font-bold leading-tight">{{ formatMoney(financialSummary.monthly_income) }}</p>
                    <div class="flex items-center gap-1 mt-1.5">
                        <ArrowUpIcon class="h-3 w-3" aria-hidden="true" />
                        <span class="text-[10px] text-white/80">+{{ incomeChangePercent }}% this month</span>
                    </div>
                </div>
                
                <!-- Total Expenses Card -->
                <div class="stat-card flex-1 min-w-[105px] bg-red-500 rounded-xl p-3 text-white shadow-md">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-medium text-white/90">Total Expenses</span>
                    </div>
                    <p class="text-lg font-bold leading-tight">{{ formatMoney(financialSummary.monthly_expenses) }}</p>
                    <div class="flex items-center gap-1 mt-1.5">
                        <ArrowDownIcon class="h-3 w-3" aria-hidden="true" />
                        <span class="text-[10px] text-white/80">+{{ expenseChangePercent }}% this month</span>
                    </div>
                </div>
                
                <!-- Net Balance Card -->
                <div class="stat-card flex-1 min-w-[105px] bg-blue-600 rounded-xl p-3 text-white shadow-md">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-medium text-white/90">Net Balance</span>
                    </div>
                    <p class="text-lg font-bold leading-tight">{{ formatMoney(financialSummary.net_income) }}</p>
                    <div class="flex items-center gap-1 mt-1.5">
                        <ChartBarSquareIcon class="h-3 w-3" aria-hidden="true" />
                        <span class="text-[10px] text-white/80">+{{ netChangePercent }}% this month</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-6">
                <h3 class="font-semibold text-gray-900 mb-3">Quick Actions</h3>
                <div class="grid grid-cols-3 gap-3">
                    <button
                        @click="openSaleModal"
                        class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl shadow-sm hover:shadow-md active:scale-[0.98] transition-all"
                    >
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                            <BanknotesIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                        </div>
                        <span class="text-xs font-medium text-gray-700">Record Sale</span>
                    </button>
                    <button
                        @click="openExpenseModal"
                        class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl shadow-sm hover:shadow-md active:scale-[0.98] transition-all"
                    >
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <ClipboardDocumentListIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                        </div>
                        <span class="text-xs font-medium text-gray-700">Add Expense</span>
                    </button>
                    <button
                        @click="openInvoiceModal"
                        class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl shadow-sm hover:shadow-md active:scale-[0.98] transition-all"
                    >
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <DocumentTextIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <span class="text-xs font-medium text-gray-700">New Invoice</span>
                    </button>
                </div>
            </div>

            <!-- Financial Overview Chart -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-4">Financial Overview</h3>
                <div class="h-40">
                    <canvas ref="mobileChartCanvas"></canvas>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="px-4 py-3 flex items-center justify-between border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Recent Transactions</h3>
                    <span class="text-xs text-gray-500">{{ currentDate }}</span>
                </div>
                <ul v-if="recentTransactions.length > 0" class="divide-y divide-gray-50">
                    <li v-for="tx in recentTransactions.slice(0, 5)" :key="`${tx.type}-${tx.id}`" class="px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-gray-900 truncate">
                                        {{ tx.type === 'income' ? `Invoice #${tx.invoice_number || 'INV-' + tx.id}` : tx.description }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span v-if="tx.type === 'income'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-700">
                                        Status: {{ tx.status || 'Paid' }}
                                    </span>
                                    <span v-else class="text-xs text-gray-500">Category: {{ tx.category || 'Expense' }}</span>
                                </div>
                            </div>
                            <p :class="['font-semibold text-sm whitespace-nowrap ml-3', tx.type === 'income' ? 'text-emerald-600' : 'text-red-600']">
                                {{ tx.type === 'income' ? '+' : '-' }} {{ formatMoney(tx.amount) }}
                            </p>
                        </div>
                    </li>
                </ul>
                <div v-else class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <BanknotesIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                    </div>
                    <p class="text-gray-500 text-sm">No transactions yet</p>
                    <button @click="router.visit(route('growfinance.sales.index'))" class="text-blue-600 text-sm font-medium mt-2">
                        Record your first sale
                    </button>
                </div>
            </div>

            <!-- Overdue Invoices Alert -->
            <div v-if="overdueInvoices.length > 0" class="mb-6">
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl p-4 text-white shadow-md">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <ExclamationTriangleIcon class="h-5 w-5" aria-hidden="true" />
                            <span class="font-semibold text-sm">Overdue Invoices</span>
                        </div>
                        <span class="bg-white/20 text-[10px] font-medium px-2 py-0.5 rounded-full">
                            {{ overdueInvoices.length }} pending
                        </span>
                    </div>
                    <div class="space-y-2">
                        <button 
                            v-for="invoice in overdueInvoices.slice(0, 2)" 
                            :key="invoice.id"
                            @click="router.visit(route('growfinance.invoices.show', invoice.id))"
                            class="w-full bg-white/10 rounded-lg p-2.5 text-left active:bg-white/20 transition-colors"
                        >
                            <p class="font-medium text-sm truncate">{{ invoice.customer_name }}</p>
                            <p class="text-xs text-white/80">{{ formatMoney(invoice.balance_due) }} • {{ invoice.days_overdue }} days overdue</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Entry Modals -->
        <RecordSaleModal 
            :show="showSaleModal" 
            :customers="customers"
            @close="showSaleModal = false"
            @success="handleModalSuccess"
        />
        <RecordExpenseModal 
            :show="showExpenseModal" 
            :vendors="vendors"
            :accounts="expenseAccounts"
            @close="showExpenseModal = false"
            @success="handleModalSuccess"
        />
        <QuickInvoiceModal 
            :show="showInvoiceModal" 
            :customers="customers"
            @close="showInvoiceModal = false"
            @success="handleModalSuccess"
        />
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import RecordSaleModal from '@/Components/GrowFinance/RecordSaleModal.vue';
import RecordExpenseModal from '@/Components/GrowFinance/RecordExpenseModal.vue';
import QuickInvoiceModal from '@/Components/GrowFinance/QuickInvoiceModal.vue';
import PWAWelcomeScreen from '@/components/pwa/PWAWelcomeScreen.vue';
import { Link } from '@inertiajs/vue3';
import {
    BanknotesIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ChartBarSquareIcon,
    ExclamationTriangleIcon,
    ClipboardDocumentListIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
} from '@heroicons/vue/24/outline';
import Chart from 'chart.js/auto';

interface Props {
    financialSummary: {
        total_cash: number;
        monthly_income: number;
        monthly_expenses: number;
        net_income: number;
        accounts_receivable: number;
        accounts_payable: number;
    };
    invoiceStats: {
        total: number;
        draft: number;
        sent: number;
        paid: number;
        partial: number;
        overdue: number;
    };
    recentTransactions: Array<{
        id: number;
        type: 'income' | 'expense';
        description: string;
        amount: number;
        date: string;
        status: string;
        invoice_number?: string;
        category?: string;
    }>;
    overdueInvoices: Array<{
        id: number;
        invoice_number: string;
        customer_name: string;
        total_amount: number;
        balance_due: number;
        due_date: string;
        days_overdue: number;
    }>;
    expensesByCategory: Array<{
        category: string;
        total: number;
    }>;
    monthlyTrend?: Array<{
        month: string;
        income: number;
        expenses: number;
    }>;
}

const props = defineProps<Props>();
const page = usePage();

// Quick entry modals
const showSaleModal = ref(false);
const showExpenseModal = ref(false);
const showInvoiceModal = ref(false);

// Quick entry data from page props
const quickEntryData = computed(() => (page.props as any).quickEntryData || {});
const customers = computed(() => quickEntryData.value.customers || []);
const vendors = computed(() => quickEntryData.value.vendors || []);
const expenseAccounts = computed(() => quickEntryData.value.expenseAccounts || []);

const openSaleModal = () => { showSaleModal.value = true; };
const openExpenseModal = () => { showExpenseModal.value = true; };
const openInvoiceModal = () => { showInvoiceModal.value = true; };

const handleModalSuccess = () => {
    router.reload({ only: ['financialSummary', 'recentTransactions', 'invoiceStats'] });
};

const userName = computed(() => {
    const name = (page.props.auth as any)?.user?.name || 'User';
    return name.split(' ')[0]; // First name only
});

const currentDate = computed(() => {
    return new Date().toLocaleDateString('en-US', { 
        month: 'long', 
        day: 'numeric', 
        year: 'numeric' 
    });
});

// Percentage changes (mock data - would come from backend in real app)
const incomeChangePercent = computed(() => 12);
const expenseChangePercent = computed(() => 8);
const netChangePercent = computed(() => 9);

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// Chart setup
const chartCanvas = ref<HTMLCanvasElement | null>(null);
const mobileChartCanvas = ref<HTMLCanvasElement | null>(null);
let chartInstance: Chart | null = null;
let mobileChartInstance: Chart | null = null;

const getChartConfig = (isMobile = false) => {
    // Generate sample data if no trend data provided - use realistic month labels
    const trendData = props.monthlyTrend || [
        { month: 'Jan', income: 2500, expenses: 1800 },
        { month: 'Feb', income: 3200, expenses: 2100 },
        { month: 'Mar', income: 2800, expenses: 1900 },
        { month: 'Apr', income: 4100, expenses: 2800 },
        { month: 'May', income: 3800, expenses: 2400 },
        { month: 'Jun', income: 4500, expenses: 3100 },
        { month: 'Jul', income: 5200, expenses: 3500 },
    ];

    return {
        type: 'line' as const,
        data: {
            labels: trendData.map(d => d.month),
            datasets: [
                {
                    label: 'Income',
                    data: trendData.map(d => d.income),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Expenses',
                    data: trendData.map(d => d.expenses),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index' as const,
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: 'white',
                    titleColor: '#111827',
                    titleFont: { weight: '600' as const },
                    bodyColor: '#6b7280',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                    boxPadding: 4,
                    callbacks: {
                        label: (context: any) => {
                            return `${context.dataset.label}: K${context.parsed.y.toLocaleString()}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        font: {
                            size: isMobile ? 10 : 11,
                        },
                        color: '#9ca3af',
                    },
                    border: {
                        display: false,
                    },
                },
                y: {
                    grid: {
                        color: '#f3f4f6',
                        drawBorder: false,
                    },
                    ticks: {
                        font: {
                            size: isMobile ? 10 : 11,
                        },
                        color: '#9ca3af',
                        padding: 8,
                        callback: (value: any) => {
                            if (typeof value === 'number') {
                                return value >= 1000 ? `K${(value / 1000).toFixed(0)}k` : `K${value}`;
                            }
                            return value;
                        },
                    },
                    border: {
                        display: false,
                    },
                },
            },
        },
    };
};

const createChart = () => {
    // Desktop chart
    if (chartCanvas.value) {
        if (chartInstance) {
            chartInstance.destroy();
        }
        const ctx = chartCanvas.value.getContext('2d');
        if (ctx) {
            chartInstance = new Chart(ctx, getChartConfig(false));
        }
    }
    
    // Mobile chart
    if (mobileChartCanvas.value) {
        if (mobileChartInstance) {
            mobileChartInstance.destroy();
        }
        const ctx = mobileChartCanvas.value.getContext('2d');
        if (ctx) {
            mobileChartInstance = new Chart(ctx, getChartConfig(true));
        }
    }
};

onMounted(() => {
    // Small delay to ensure canvas elements are rendered
    setTimeout(createChart, 150);
});

watch(() => props.monthlyTrend, () => {
    createChart();
}, { deep: true });
</script>

<style scoped>
.stat-card {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}
</style>
