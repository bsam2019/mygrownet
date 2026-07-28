<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Profitability</h1>
                <p class="text-gray-500 text-sm">Branch/Department P&amp;L</p>
            </div>

            <!-- Date Range -->
            <div class="flex gap-3 mb-6">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                    <input type="date" v-model="fromDate"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        @change="refresh" />
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                    <input type="date" v-model="toDate"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        @change="refresh" />
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Dimension</label>
                    <select v-model="dimensionKey"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        @change="refresh">
                        <option value="">None</option>
                        <option value="cost_centre">Cost Centre</option>
                        <option value="department">Department</option>
                        <option value="project">Project</option>
                        <option value="location">Location</option>
                    </select>
                </div>
            </div>

            <!-- Branch/Group P&L -->
            <div v-if="byGroup.length > 0" class="bg-white rounded-2xl shadow-sm p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-4">Profitability by Branch/Group</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-left text-gray-500 text-xs uppercase">
                                <th class="py-2 pr-4">Org</th>
                                <th class="py-2 pr-4">Type</th>
                                <th class="py-2 pr-4 text-right">Income</th>
                                <th class="py-2 pr-4 text-right">Expenses</th>
                                <th class="py-2 pr-4 text-right">Net Income</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in byGroup" :key="row.org_id" class="border-b border-gray-50">
                                <td class="py-2 pr-4 font-medium">{{ row.org_id }}</td>
                                <td class="py-2 pr-4 text-gray-500">{{ row.relationship_type }}</td>
                                <td class="py-2 pr-4 text-right text-emerald-600">{{ formatMoney(row.profit_and_loss.total_income) }}</td>
                                <td class="py-2 pr-4 text-right text-red-600">{{ formatMoney(row.profit_and_loss.total_expenses) }}</td>
                                <td class="py-2 pr-4 text-right font-semibold" :class="row.net_income >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                    {{ formatMoney(row.net_income) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-else class="bg-white rounded-2xl shadow-sm p-6 mb-6 text-center text-gray-400 text-sm">
                No branch/group relationships found. Add subsidiaries via consolidation to see branch profitability.
            </div>

            <!-- Dimension Breakdown -->
            <div v-if="Object.keys(byDimension).length > 0" class="bg-white rounded-2xl shadow-sm p-4">
                <h3 class="font-semibold text-gray-900 mb-4">Breakdown by {{ dimensionKey || 'Dimension' }}</h3>
                <div v-for="(dim, key) in byDimension" :key="key" class="mb-4 pb-4 border-b border-gray-100 last:border-0">
                    <h4 class="font-medium text-gray-800 mb-2 capitalize">{{ key }}</h4>
                    <div class="text-xs text-gray-500 mb-2">
                        Income: <span class="text-emerald-600 font-medium">{{ formatMoney(dim.total_income) }}</span> &middot;
                        Expenses: <span class="text-red-600 font-medium">{{ formatMoney(dim.total_expenses) }}</span> &middot;
                        Net: <span :class="dim.total_income - dim.total_expenses >= 0 ? 'text-emerald-600' : 'text-red-600'" class="font-medium">
                            {{ formatMoney(dim.total_income - dim.total_expenses) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';

interface PnlRow {
    account_code: string;
    account_name: string;
    amount: number;
}

interface PnlData {
    total_income: number;
    total_expenses: number;
    income: PnlRow[];
    expenses: PnlRow[];
}

interface GroupRow {
    org_id: number;
    relationship_type: string;
    profit_and_loss: PnlData;
    net_income: number;
}

interface DimensionData {
    income: PnlRow[];
    expenses: PnlRow[];
    total_income: number;
    total_expenses: number;
}

interface Props {
    byGroup: GroupRow[];
    byDimension: Record<string, DimensionData>;
    filters: {
        from: string;
        to: string;
        dimension: string | null;
    };
}

const props = defineProps<Props>();

const fromDate = ref(props.filters.from);
const toDate = ref(props.filters.to);
const dimensionKey = ref(props.filters.dimension || '');

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const refresh = () => {
    router.get(route('growfinance.profitability.index'), {
        from: fromDate.value,
        to: toDate.value,
        dimension: dimensionKey.value || null,
    }, { preserveState: true, preserveScroll: true });
};
</script>
