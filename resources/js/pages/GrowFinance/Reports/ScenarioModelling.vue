<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import {
    ArrowLeftIcon,
    BeakerIcon,
    PlusIcon,
    XMarkIcon,
    ChevronDownIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
} from '@heroicons/vue/24/outline';

interface PnlData {
    total_income: number;
    total_expenses: number;
    net_income: number;
}

interface ImpactData {
    income: number;
    expenses: number;
    net_income: number;
    income_pct: number;
    expenses_pct: number;
    net_income_pct: number;
}

interface PeriodData {
    from: string;
    to: string;
}

interface SimulationResult {
    parameters: Record<string, any>;
    base_pnl: PnlData;
    projected_pnl: PnlData;
    impact: ImpactData;
    period: PeriodData;
}

interface Account {
    code: string;
    name: string;
    type: string;
}

interface SavedScenario {
    id: number;
    business_id: number;
    name: string;
    parameters: Record<string, any>;
    results: Record<string, any>;
    created_at: string;
    updated_at: string;
}

interface ScenarioStep {
    key: number;
    type: 'revenue' | 'expense' | 'account';
    percentage?: number;
    account_code?: string;
    new_amount?: number;
}

const props = defineProps<{
    savedScenarios?: SavedScenario[];
    basePnl?: PnlData;
    accounts?: Account[];
    filters?: { from: string; to: string };
    activeScenario?: SavedScenario | null;
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (date: string) => {
    const d = new Date(date + 'T00:00:00');
    return d.toLocaleDateString('en-ZM', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatPct = (val: number) => {
    const sign = val >= 0 ? '+' : '';
    return `${sign}${val.toFixed(1)}%`;
};

// State
const scenarioType = ref<'revenue' | 'expense' | 'account' | 'combined'>('revenue');
const revenuePct = ref(10);
const expensePct = ref(-10);
const accountCode = ref('');
const accountNewAmount = ref(0);
const from = ref(props.filters?.from || '');
const to = ref(props.filters?.to || '');
const result = ref<SimulationResult | null>(null);
const loading = ref(false);
const showSaveDialog = ref(false);
const scenarioName = ref('');
const scenarioSteps = ref<ScenarioStep[]>([{ key: Date.now(), type: 'revenue', percentage: 10 }]);
let stepCounter = ref(1);

// For the account scenario, find the current amount for selected account
const selectedAccountCurrentAmount = computed(() => {
    if (!accountCode.value || !props.basePnl) return 0;
    const allLines = [...(props.basePnl as any).income || [], ...(props.basePnl as any).expenses || []];
    const found = allLines.find((l: any) => l.account_code === accountCode.value);
    return found ? found.amount : 0;
});

watch(accountCode, (code) => {
    if (code && !accountNewAmount.value) {
        accountNewAmount.value = selectedAccountCurrentAmount.value;
    }
});

// Available accounts for dropdown
const incomeAccounts = computed(() => (props.accounts || []).filter(a => a.type === 'income'));
const expenseAccounts = computed(() => (props.accounts || []).filter(a => a.type === 'expense'));

const simulate = async () => {
    loading.value = true;
    try {
        const payload: Record<string, any> = {
            type: scenarioType.value,
            from: from.value,
            to: to.value,
        };

        if (scenarioType.value === 'revenue') {
            payload.percentage = revenuePct.value;
        } else if (scenarioType.value === 'expense') {
            payload.percentage = expensePct.value;
        } else if (scenarioType.value === 'account') {
            payload.account_code = accountCode.value;
            payload.new_amount = accountNewAmount.value;
        } else if (scenarioType.value === 'combined') {
            payload.scenarios = scenarioSteps.value.map(s => ({
                type: s.type,
                percentage: s.type !== 'account' ? s.percentage : undefined,
                account_code: s.type === 'account' ? s.account_code : undefined,
                new_amount: s.type === 'account' ? s.new_amount : undefined,
            }));
        }

        const res = await axios.post(route('growfinance.scenarios.simulate'), payload);
        result.value = res.data;
    } catch (e) {
        console.error('Simulation failed', e);
    } finally {
        loading.value = false;
    }
};

const saveScenario = async () => {
    if (!scenarioName.value || !result.value) return;
    try {
        await axios.post(route('growfinance.scenarios.save'), {
            name: scenarioName.value,
            parameters: result.value.parameters,
            results: result.value,
        });
        showSaveDialog.value = false;
        scenarioName.value = '';
    } catch (e) {
        console.error('Save failed', e);
    }
};

const loadScenario = (scenario: SavedScenario) => {
    const r = scenario.results as any;
    if (r && r.parameters) {
        result.value = r as SimulationResult;
    }
};

const addStep = () => {
    scenarioSteps.value.push({ key: Date.now() + stepCounter.value++, type: 'revenue', percentage: 10 });
};

const removeStep = (key: number) => {
    if (scenarioSteps.value.length > 1) {
        scenarioSteps.value = scenarioSteps.value.filter(s => s.key !== key);
    }
};

const netIncomeColor = (val: number) => val >= 0 ? 'text-green-600' : 'text-red-600';

const impactIcon = (val: number) => {
    if (val > 0) return ArrowTrendingUpIcon;
    if (val < 0) return ArrowTrendingDownIcon;
    return 'span';
};

const impactColor = (val: number, inverse = false) => {
    if (val === 0) return 'text-gray-500';
    const positive = inverse ? val < 0 : val > 0;
    return positive ? 'text-green-600' : 'text-red-600';
};
</script>

<template>
    <GrowFinanceLayout>
        <Head title="What-If Scenarios" />

        <div class="p-4 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('growfinance.dashboard')"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">What-If Scenarios</h1>
                        <p class="text-sm text-gray-500">Model financial outcomes by changing revenue, expenses, or specific accounts</p>
                    </div>
                </div>
            </div>

            <!-- Period Filter -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="grid grid-cols-2 gap-4 max-w-sm">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                        <input
                            type="date"
                            v-model="from"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                        <input
                            type="date"
                            v-model="to"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                    </div>
                </div>
            </div>

            <!-- Base P&L Panel -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Base P&L</h2>
                </div>
                <div class="grid grid-cols-3 divide-x divide-gray-200">
                    <div class="p-6 text-center">
                        <p class="text-xs font-medium text-gray-500 uppercase">Total Income</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ basePnl ? formatCurrency(basePnl.total_income) : '-' }}</p>
                    </div>
                    <div class="p-6 text-center">
                        <p class="text-xs font-medium text-gray-500 uppercase">Total Expenses</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ basePnl ? formatCurrency(basePnl.total_expenses) : '-' }}</p>
                    </div>
                    <div class="p-6 text-center">
                        <p class="text-xs font-medium text-gray-500 uppercase">Net Income</p>
                        <p class="text-2xl font-bold mt-1" :class="basePnl ? netIncomeColor(basePnl.net_income) : ''">
                            {{ basePnl ? formatCurrency(basePnl.net_income) : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Scenario Type Tabs -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex">
                        <button
                            v-for="tab in [
                                { key: 'revenue', label: 'Revenue Change' },
                                { key: 'expense', label: 'Expense Change' },
                                { key: 'account', label: 'Account Change' },
                                { key: 'combined', label: 'Combined' },
                            ]"
                            :key="tab.key"
                            @click="scenarioType = tab.key as any"
                            :class="[
                                'px-4 py-3 text-sm font-medium border-b-2 transition-colors',
                                scenarioType === tab.key
                                    ? 'border-emerald-500 text-emerald-700'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <div class="p-6 space-y-4">
                    <!-- Revenue Change Controls -->
                    <div v-if="scenarioType === 'revenue'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Change Revenue by <span :class="revenuePct >= 0 ? 'text-green-600' : 'text-red-600'">{{ formatPct(revenuePct) }}</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <input
                                type="range"
                                min="-100"
                                max="100"
                                step="1"
                                v-model.number="revenuePct"
                                class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-500"
                            />
                            <input
                                type="number"
                                v-model.number="revenuePct"
                                class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <span class="text-sm text-gray-500">%</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                            <span>-100%</span>
                            <span>0%</span>
                            <span>+100%</span>
                        </div>
                    </div>

                    <!-- Expense Change Controls -->
                    <div v-if="scenarioType === 'expense'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Change Expenses by <span :class="expensePct >= 0 ? 'text-red-600' : 'text-green-600'">{{ formatPct(expensePct) }}</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <input
                                type="range"
                                min="-100"
                                max="100"
                                step="1"
                                v-model.number="expensePct"
                                class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-red-500"
                            />
                            <input
                                type="number"
                                v-model.number="expensePct"
                                class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <span class="text-sm text-gray-500">%</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                            <span>-100%</span>
                            <span>0%</span>
                            <span>+100%</span>
                        </div>
                    </div>

                    <!-- Account Change Controls -->
                    <div v-if="scenarioType === 'account'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Account</label>
                            <select
                                v-model="accountCode"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            >
                                <option value="">-- Select an account --</option>
                                <optgroup label="Income Accounts">
                                    <option v-for="a in incomeAccounts" :key="a.code" :value="a.code">
                                        {{ a.code }} - {{ a.name }}
                                    </option>
                                </optgroup>
                                <optgroup label="Expense Accounts">
                                    <option v-for="a in expenseAccounts" :key="a.code" :value="a.code">
                                        {{ a.code }} - {{ a.name }}
                                    </option>
                                </optgroup>
                            </select>
                        </div>
                        <div v-if="accountCode" class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Current Amount</label>
                                <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(selectedAccountCurrentAmount) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Amount</label>
                                <input
                                    type="number"
                                    v-model.number="accountNewAmount"
                                    step="0.01"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Combined Controls -->
                    <div v-if="scenarioType === 'combined'" class="space-y-4">
                        <div v-for="(step, idx) in scenarioSteps" :key="step.key" class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-gray-700">Scenario {{ idx + 1 }}</span>
                                <button
                                    v-if="scenarioSteps.length > 1"
                                    @click="removeStep(step.key)"
                                    class="p-1 text-gray-400 hover:text-red-500 rounded hover:bg-red-50 transition-colors"
                                >
                                    <XMarkIcon class="h-4 w-4" />
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                                    <select
                                        v-model="step.type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    >
                                        <option value="revenue">Revenue Change</option>
                                        <option value="expense">Expense Change</option>
                                        <option value="account">Account Change</option>
                                    </select>
                                </div>
                                <div v-if="step.type !== 'account'">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Percentage</label>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="number"
                                            v-model.number="step.percentage"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                        />
                                        <span class="text-sm text-gray-500">%</span>
                                    </div>
                                </div>
                                <div v-else>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Account</label>
                                    <select
                                        v-model="step.account_code"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    >
                                        <option value="">-- Select --</option>
                                        <optgroup label="Income">
                                            <option v-for="a in incomeAccounts" :key="a.code" :value="a.code">{{ a.code }} - {{ a.name }}</option>
                                        </optgroup>
                                        <optgroup label="Expenses">
                                            <option v-for="a in expenseAccounts" :key="a.code" :value="a.code">{{ a.code }} - {{ a.name }}</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div v-if="step.type === 'account'">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">New Amount</label>
                                    <input
                                        type="number"
                                        v-model.number="step.new_amount"
                                        step="0.01"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                </div>
                            </div>
                        </div>
                        <button
                            @click="addStep"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition-colors"
                        >
                            <PlusIcon class="h-4 w-4" />
                            Add Scenario
                        </button>
                    </div>

                    <!-- Simulate Button -->
                    <button
                        @click="simulate"
                        :disabled="loading"
                        class="w-full py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold rounded-lg hover:from-emerald-600 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm"
                    >
                        <span v-if="loading">Simulating...</span>
                        <span v-else>Run Simulation</span>
                    </button>
                </div>
            </div>

            <!-- Results Panel -->
            <div v-if="result" class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <BeakerIcon class="h-5 w-5 text-emerald-500" />
                            <h2 class="text-lg font-semibold text-gray-900">Simulation Results</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400">{{ formatDate(result.period.from) }} - {{ formatDate(result.period.to) }}</span>
                            <button
                                @click="showSaveDialog = true"
                                class="px-3 py-1.5 text-sm font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition-colors"
                            >
                                Save Scenario
                            </button>
                        </div>
                    </div>

                    <!-- Side-by-side comparison -->
                    <div class="grid grid-cols-3 divide-x divide-gray-200">
                        <div class="p-6">
                            <p class="text-xs font-medium text-gray-500 uppercase mb-3">Current</p>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-400">Income</p>
                                    <p class="text-lg font-semibold text-green-600">{{ formatCurrency(result.base_pnl.total_income) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Expenses</p>
                                    <p class="text-lg font-semibold text-red-600">{{ formatCurrency(result.base_pnl.total_expenses) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Net Income</p>
                                    <p class="text-lg font-semibold" :class="netIncomeColor(result.base_pnl.net_income)">{{ formatCurrency(result.base_pnl.net_income) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 bg-emerald-50/30">
                            <p class="text-xs font-medium text-emerald-700 uppercase mb-3">Projected</p>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-400">Income</p>
                                    <p class="text-lg font-semibold text-green-600">{{ formatCurrency(result.projected_pnl.total_income) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Expenses</p>
                                    <p class="text-lg font-semibold text-red-600">{{ formatCurrency(result.projected_pnl.total_expenses) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Net Income</p>
                                    <p class="text-lg font-semibold" :class="netIncomeColor(result.projected_pnl.net_income)">{{ formatCurrency(result.projected_pnl.net_income) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-xs font-medium text-gray-500 uppercase mb-3">Impact</p>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-400">Income</p>
                                    <p class="text-lg font-semibold" :class="impactColor(result.impact.income)">
                                        {{ result.impact.income >= 0 ? '+' : '' }}{{ formatCurrency(result.impact.income) }}
                                        <span class="text-sm ml-1">({{ formatPct(result.impact.income_pct) }})</span>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Expenses</p>
                                    <p class="text-lg font-semibold" :class="impactColor(result.impact.expenses, true)">
                                        {{ result.impact.expenses >= 0 ? '+' : '' }}{{ formatCurrency(result.impact.expenses) }}
                                        <span class="text-sm ml-1">({{ formatPct(result.impact.expenses_pct) }})</span>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Net Income</p>
                                    <p class="text-lg font-semibold" :class="impactColor(result.impact.net_income)">
                                        {{ result.impact.net_income >= 0 ? '+' : '' }}{{ formatCurrency(result.impact.net_income) }}
                                        <span class="text-sm ml-1">({{ formatPct(result.impact.net_income_pct) }})</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Saved Scenarios List -->
            <div v-if="savedScenarios && savedScenarios.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Saved Scenarios</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="s in savedScenarios"
                        :key="s.id"
                        @click="loadScenario(s)"
                        :class="[
                            'px-6 py-4 flex items-center justify-between hover:bg-gray-50 cursor-pointer transition-colors',
                            activeScenario?.id === s.id ? 'bg-emerald-50' : ''
                        ]"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ s.name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Saved {{ formatDate(s.created_at) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold" :class="netIncomeColor((s.results as any)?.projected_pnl?.net_income || 0)">
                                {{ formatCurrency((s.results as any)?.projected_pnl?.net_income || 0) }}
                            </p>
                            <p class="text-xs text-gray-500">Projected net</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Dialog -->
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showSaveDialog" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center" @click="showSaveDialog = false">
                    <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full mx-4" @click.stop>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Save Scenario</h3>
                        <input
                            v-model="scenarioName"
                            placeholder="Enter scenario name..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 mb-4"
                            @keyup.enter="saveScenario"
                        />
                        <div class="flex items-center justify-end gap-3">
                            <button
                                @click="showSaveDialog = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                @click="saveScenario"
                                :disabled="!scenarioName"
                                class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </GrowFinanceLayout>
</template>
