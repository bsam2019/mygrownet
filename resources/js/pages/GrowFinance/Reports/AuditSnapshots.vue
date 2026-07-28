<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/GrowFinanceLayout.vue';

const page = usePage();
const snapshots = computed(() => (page.props as any).snapshots || []);
const currentType = computed(() => (page.props as any).currentType || 'trial_balance');

const selectedType = ref(currentType.value);
const asOfDate = ref(new Date().toISOString().split('T')[0]);
const showVerifyModal = ref(false);
const verifyResult = ref<any>(null);
const verifyingId = ref<number | null>(null);

const reportTypes = [
    { value: 'trial_balance', label: 'Trial Balance' },
    { value: 'profit_and_loss', label: 'Profit & Loss' },
    { value: 'balance_sheet', label: 'Balance Sheet' },
    { value: 'cash_flow', label: 'Cash Flow' },
];

const switchType = (type: string) => {
    selectedType.value = type;
    router.get(route('growfinance.audit-snapshots.index'), { type }, { preserveState: true });
};

const takeSnapshot = () => {
    router.post(route('growfinance.audit-snapshots.take'), {
        type: selectedType.value,
        as_of: asOfDate.value,
    }, { preserveState: true });
};

const verifySnapshot = async (id: number) => {
    verifyingId.value = id;
    try {
        const res = await fetch(route('growfinance.audit-snapshots.verify', id));
        verifyResult.value = await res.json();
        showVerifyModal.value = true;
    } catch (e) {
        console.error('Verification failed', e);
    } finally {
        verifyingId.value = null;
    }
};

const lockSnapshot = (id: number) => {
    router.post(route('growfinance.audit-snapshots.lock', id), {}, { preserveState: true });
};

const formatHash = (hash: string) => {
    if (!hash) return '-';
    return hash.substring(0, 16) + '...';
};

const truncate = (text: string, len: number) => {
    if (!text || text.length <= len) return text || '-';
    return text.substring(0, len) + '...';
};
</script>

<template>
    <AppLayout>
        <div class="p-4 sm:p-6 max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Audit Snapshots</h1>
                    <p class="text-sm text-gray-500 mt-1">Capture and verify report snapshots with integrity hashing</p>
                </div>
            </div>

            <!-- Report Type Tabs -->
            <div class="flex flex-wrap gap-2 mb-6">
                <button
                    v-for="t in reportTypes"
                    :key="t.value"
                    @click="switchType(t.value)"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors border',
                        selectedType === t.value
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
                    ]"
                >
                    {{ t.label }}
                </button>
            </div>

            <!-- Take Snapshot Controls -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6 flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">As of date</label>
                    <input
                        v-model="asOfDate"
                        type="date"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    />
                </div>
                <button
                    @click="takeSnapshot"
                    class="px-5 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors"
                >
                    Take Snapshot
                </button>
            </div>

            <!-- Snapshots Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600">Integrity Hash</th>
                                <th class="text-center px-4 py-3 font-medium text-gray-600">Locked</th>
                                <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="s in snapshots" :key="s.id" class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-900">{{ s.as_of_date }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                        {{ s.report_type?.replace(/_/g, ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <code class="text-xs font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-600">
                                        {{ formatHash(s.integrity_hash) }}
                                    </code>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        v-if="s.locked_at"
                                        class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700"
                                    >
                                        Locked
                                    </span>
                                    <span v-else class="text-gray-400 text-xs">—</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="verifySnapshot(s.id)"
                                            :disabled="verifyingId === s.id"
                                            class="px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors disabled:opacity-50"
                                        >
                                            {{ verifyingId === s.id ? '...' : 'Verify' }}
                                        </button>
                                        <button
                                            v-if="!s.locked_at"
                                            @click="lockSnapshot(s.id)"
                                            class="px-3 py-1.5 text-xs font-medium text-amber-600 bg-amber-50 rounded-lg hover:bg-amber-100 transition-colors"
                                        >
                                            Lock
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="snapshots.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">
                                    No snapshots yet. Click "Take Snapshot" to capture the current {{ currentType.replace(/_/g, ' ') }}.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Verification Result Modal -->
        <Teleport to="body">
            <div v-if="showVerifyModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showVerifyModal = false" />
                <div class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center',
                                verifyResult?.valid ? 'bg-green-100' : 'bg-red-100'
                            ]"
                        >
                            <span v-if="verifyResult?.valid" class="text-green-600 text-xl">✓</span>
                            <span v-else class="text-red-600 text-xl">✗</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ verifyResult?.valid ? 'Snapshot Valid' : 'Snapshot Tampered' }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ verifyResult?.valid ? 'The data integrity is verified' : 'Hash mismatch detected' }}
                            </p>
                        </div>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">Original Hash</div>
                            <code class="text-xs font-mono break-all text-gray-700">{{ verifyResult?.original_hash || '—' }}</code>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">Computed Hash</div>
                            <code class="text-xs font-mono break-all text-gray-700">{{ verifyResult?.computed_hash || '—' }}</code>
                        </div>
                    </div>
                    <button
                        @click="showVerifyModal = false"
                        class="mt-4 w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors"
                    >
                        Close
                    </button>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
