<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import CommentSection from '@/components/StockFlow/CommentSection.vue';
import { useCurrency } from '@/composables/useCurrency';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { ref } from 'vue';

const { route } = useStockflowRoute();

interface AuditItem {
    id: number;
    sa_item_id: number;
    item_name: string;
    unit_price: number;
    system_qty: number;
    physical_qty: number;
    gap_qty: number;
    system_value: number;
    physical_value: number;
    gap_value: number;
}

interface Audit {
    id: number;
    title: string;
    report_reference: string;
    audit_date: string;
    status: string;
    total_system_value: number;
    total_physical_value: number;
    total_variance: number;
    unaccounted_value: number;
    total_recorded_sales: number;
    executive_summary: string | null;
    recommendations: string | null;
    conclusion: string | null;
    prepared_for: string | null;
    prepared_by: string | null;
    items: AuditItem[];
    created_at: string;
}

interface Props {
    audit: Audit;
}

const props = defineProps<Props>();

const showFinalize = ref(false);
const finalizeForm = ref({
    executive_summary: props.audit.executive_summary || '',
    recommendations: props.audit.recommendations || '',
    conclusion: props.audit.conclusion || '',
    total_recorded_sales: props.audit.total_recorded_sales || 0,
});

const errors = ref<Record<string, string>>({});

const { formatCurrency } = useCurrency();

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    finalized: 'bg-green-100 text-green-800',
    archived: 'bg-blue-100 text-blue-800',
};

const isDraft = props.audit.status === 'draft';
const isFinalized = props.audit.status === 'finalized';

const finalizeAudit = () => {
    router.post(route('stockflow.sub.audits.finalize', props.audit.id), finalizeForm.value, {
        onSuccess: () => { showFinalize.value = false; },
        onError: (err) => { errors.value = err; },
    });
};

const exportCsv = () => {
    window.open(route('stockflow.sub.audits.export-csv', props.audit.id), '_blank');
};
</script>

<template>
    <StockFlowLayout :title="audit.report_reference">
        <Head :title="`${audit.report_reference} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link :href="route('stockflow.sub.audits.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Audits</Link>
                        <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ audit.report_reference }}</h1>
                        <p class="text-sm text-gray-500">{{ audit.title }} - {{ audit.audit_date }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span :class="[statusColors[audit.status] || 'bg-gray-100 text-gray-800', 'rounded-full px-3 py-1 text-sm font-medium capitalize']">
                            {{ audit.status }}
                        </span>
                        <button v-if="isFinalized" @click="exportCsv" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            Export CSV
                        </button>
                        <button v-if="isDraft" @click="showFinalize = !showFinalize" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
                            Finalize Audit
                        </button>
                    </div>
                </div>

                <!-- Finalize Form -->
                <div v-if="showFinalize" class="mb-6 rounded-xl bg-white p-6 shadow-sm border border-purple-200">
                    <h2 class="text-lg font-semibold text-purple-900">Finalize Audit</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recorded Sales (if known)</label>
                            <input v-model.number="finalizeForm.total_recorded_sales" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Executive Summary</label>
                            <textarea v-model="finalizeForm.executive_summary" rows="3" class="mt-1 w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Recommendations</label>
                            <textarea v-model="finalizeForm.recommendations" rows="3" class="mt-1 w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Conclusion</label>
                            <textarea v-model="finalizeForm.conclusion" rows="3" class="mt-1 w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"></textarea>
                        </div>
                        <p v-if="errors.total_recorded_sales" class="text-sm text-red-600">{{ errors.total_recorded_sales }}</p>
                        <div class="flex gap-3">
                            <button @click="finalizeAudit" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">Finalize</button>
                            <button @click="showFinalize = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">System Value</p>
                        <p class="text-xl font-bold text-gray-900">{{ formatCurrency(audit.total_system_value) }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Physical Value</p>
                        <p class="text-xl font-bold text-gray-900">{{ formatCurrency(audit.total_physical_value) }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Total Variance</p>
                        <p :class="['text-xl font-bold', audit.total_variance < 0 ? 'text-red-600' : 'text-emerald-600']">
                            {{ formatCurrency(audit.total_variance) }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Unaccounted</p>
                        <p :class="['text-xl font-bold', audit.unaccounted_value < 0 ? 'text-red-600' : 'text-gray-900']">
                            {{ formatCurrency(audit.unaccounted_value) }}
                        </p>
                    </div>
                </div>

                <div v-if="isFinalized" class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <p class="text-xs text-gray-500">Recorded Sales</p>
                        <p class="text-xl font-bold text-blue-600">{{ formatCurrency(audit.total_recorded_sales) }}</p>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-900">Audit Items</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Item</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Unit Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">System</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Physical</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Gap</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Sys Value</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Phys Value</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Gap Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="item in audit.items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ item.item_name }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ item.system_qty }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ item.physical_qty }}</td>
                                <td class="px-6 py-4 text-right text-sm font-medium" :class="item.gap_qty < 0 ? 'text-red-600' : item.gap_qty > 0 ? 'text-emerald-600' : 'text-gray-700'">
                                    {{ item.gap_qty > 0 ? '+' : '' }}{{ item.gap_qty }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ formatCurrency(item.system_value) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ formatCurrency(item.physical_value) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-medium" :class="item.gap_value < 0 ? 'text-red-600' : item.gap_value > 0 ? 'text-emerald-600' : 'text-gray-700'">
                                    {{ formatCurrency(item.gap_value) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Narrative -->
                <div v-if="audit.executive_summary || audit.recommendations || audit.conclusion" class="mt-6 grid gap-6 lg:grid-cols-3">
                    <div v-if="audit.executive_summary" class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-900">Executive Summary</h3>
                        <p class="mt-2 text-sm text-gray-700">{{ audit.executive_summary }}</p>
                    </div>
                    <div v-if="audit.recommendations" class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-900">Recommendations</h3>
                        <p class="mt-2 text-sm text-gray-700">{{ audit.recommendations }}</p>
                    </div>
                    <div v-if="audit.conclusion" class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="text-sm font-semibold text-gray-900">Conclusion</h3>
                        <p class="mt-2 text-sm text-gray-700">{{ audit.conclusion }}</p>
                    </div>
                </div>
                <!-- Comments -->
                <div class="mt-6">
                    <CommentSection type="audit" :id="audit.id" />
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
