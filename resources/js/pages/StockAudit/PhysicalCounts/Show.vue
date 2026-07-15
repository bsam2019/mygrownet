<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import CommentSection from '@/components/StockAudit/CommentSection.vue';
import { useCurrency } from '@/composables/useCurrency';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

interface CountItem {
    id: number;
    sa_item_id: number;
    sa_bin_id: number | null;
    system_quantity: number;
    physical_quantity: number;
    variance: number;
    unit_price: number;
    variance_value: number;
}

interface PhysicalCount {
    id: number;
    title: string;
    count_date: string;
    status: string;
    notes: string | null;
    items: CountItem[];
    created_at: string;
}

interface Props {
    count: PhysicalCount;
}

const props = defineProps<Props>();
const { success, error: notifyError } = useNotifications();
const confirm = useConfirmDialog();

const editingItems = ref<Record<number, number>>({});
const errors = ref<Record<string, string>>({});

const { formatCurrency } = useCurrency();

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
};

const canEdit = props.count.status === 'draft' || props.count.status === 'in_progress';
const canComplete = props.count.status === 'in_progress';
const canGenerateAudit = props.count.status === 'completed';

const setPhysicalQty = (itemId: number, qty: number) => {
    editingItems.value[itemId] = qty;
};

const saveItems = () => {
    const items = Object.entries(editingItems.value).map(([id, physical_quantity]) => ({
        id: parseInt(id),
        physical_quantity,
    }));

    if (items.length === 0) return;

    router.put(route('stockflow.sub.physical-counts.update-items', props.count.id), { items }, {
        onSuccess: () => success('Items updated'),
        onError: (err) => { errors.value = err; notifyError('Failed to update items'); },
    });
};

const completeCount = async () => {
    const ok = await confirm.show('Complete this physical count? This will update system quantities.', 'Complete Count');
    if (ok) {
        router.post(route('stockflow.sub.physical-counts.complete', props.count.id), {}, {
            onSuccess: () => success('Count completed'),
            onError: (err) => { errors.value = err; notifyError('Failed to complete count'); },
        });
    }
};

const generateAudit = async () => {
    const ok = await confirm.show('Generate audit from this count?', 'Generate Audit');
    if (ok) {
        router.post(route('stockflow.sub.physical-counts.generate-audit', props.count.id), {}, {
            onSuccess: () => success('Audit generated'),
            onError: () => notifyError('Failed to generate audit'),
        });
    }
};
</script>

<template>
    <StockAuditLayout :title="count.title">
        <Head :title="`${count.title} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link :href="route('stockflow.sub.physical-counts.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Counts</Link>
                        <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ count.title }}</h1>
                        <p class="text-sm text-gray-500">{{ count.count_date }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span :class="[statusColors[count.status] || 'bg-gray-100 text-gray-800', 'rounded-full px-3 py-1 text-sm font-medium capitalize']">
                            {{ count.status.replace('_', ' ') }}
                        </span>
                        <button v-if="canEdit && Object.keys(editingItems).length > 0" @click="saveItems" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            Save Changes
                        </button>
                        <button v-if="canComplete" @click="completeCount" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            Complete Count
                        </button>
                        <button v-if="canGenerateAudit" @click="generateAudit" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
                            Generate Audit
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Item ID</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">System</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Physical</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Variance</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Var Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="item in count.items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ item.item_name || 'Item #' + item.sa_item_id }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700">{{ item.system_quantity }}</td>
                                <td class="px-6 py-4 text-right">
                                    <input
                                        v-if="canEdit"
                                        :value="editingItems[item.sa_item_id] !== undefined ? editingItems[item.sa_item_id] : item.physical_quantity"
                                        @input="(e) => setPhysicalQty(item.sa_item_id, parseFloat((e.target as HTMLInputElement).value) || 0)"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-24 rounded border-gray-300 text-right text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                    />
                                    <span v-else class="text-sm text-gray-900">{{ item.physical_quantity }}</span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm" :class="item.variance < 0 ? 'text-red-600 font-medium' : item.variance > 0 ? 'text-emerald-600 font-medium' : 'text-gray-700'">
                                    {{ item.variance > 0 ? '+' : '' }}{{ item.variance }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium" :class="item.variance_value < 0 ? 'text-red-600' : item.variance_value > 0 ? 'text-emerald-600' : 'text-gray-700'">
                                    {{ formatCurrency(item.variance_value) }}
                                </td>
                            </tr>
                            <tr v-if="count.items.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No items in this count</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="count.notes" class="mt-6 rounded-xl bg-white p-6 shadow-sm">
                    <p class="text-xs text-gray-500">Notes</p>
                    <p class="mt-1 text-sm text-gray-700">{{ count.notes }}</p>
                </div>

                <!-- Comments -->
                <div class="mt-6">
                    <CommentSection type="physical_count" :id="count.id" />
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
