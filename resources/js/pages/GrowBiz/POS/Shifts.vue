<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    PlayIcon,
    StopIcon,
    ClockIcon,
    BanknotesIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

interface Shift {
    id: number;
    shift_number: string;
    opening_cash: number;
    closing_cash: number | null;
    expected_cash: number | null;
    cash_difference: number | null;
    total_sales: number;
    total_transactions: number;
    status: string;
    opened_at: string;
    closed_at: string | null;
    notes: string | null;
}

interface Props {
    activeShift: Shift | null;
    shiftHistory: Shift[];
}

const props = defineProps<Props>();

const showOpenModal = ref(false);
const showCloseModal = ref(false);
const openingCash = ref(0);
const closingCash = ref(0);
const notes = ref('');
const isSubmitting = ref(false);

const openShift = () => {
    isSubmitting.value = true;
    router.post(route('growbiz.pos.shifts.open'), {
        opening_cash: openingCash.value,
        notes: notes.value || null,
    }, {
        onSuccess: () => {
            showOpenModal.value = false;
            openingCash.value = 0;
            notes.value = '';
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const closeShift = () => {
    if (!props.activeShift) return;
    isSubmitting.value = true;
    router.post(route('growbiz.pos.shifts.close', props.activeShift.id), {
        closing_cash: closingCash.value,
        notes: notes.value || null,
    }, {
        onSuccess: () => {
            showCloseModal.value = false;
            closingCash.value = 0;
            notes.value = '';
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW' }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-ZM', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};

const formatDuration = (start: string, end: string | null) => {
    const startDate = new Date(start);
    const endDate = end ? new Date(end) : new Date();
    const diff = endDate.getTime() - startDate.getTime();
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    return `${hours}h ${minutes}m`;
};
</script>

<template>
    <GrowBizLayout>
        <Head title="Shift Management - POS" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 sm:py-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Shift Management</h1>
                <p class="text-sm text-gray-500 mt-1">Track cash and sales per shift</p>
            </div>

            <!-- Active Shift Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <div v-if="activeShift" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                <PlayIcon class="w-6 h-6 text-green-600" />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Shift {{ activeShift.shift_number }}</p>
                                <p class="text-sm text-green-600">Active</p>
                            </div>
                        </div>
                        <button
                            @click="showCloseModal = true"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2"
                        >
                            <StopIcon class="w-5 h-5" />
                            <span>End Shift</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
                        <div>
                            <p class="text-xs text-gray-500">Started</p>
                            <p class="font-medium">{{ formatDate(activeShift.opened_at) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Duration</p>
                            <p class="font-medium">{{ formatDuration(activeShift.opened_at, null) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Opening Cash</p>
                            <p class="font-medium">{{ formatCurrency(activeShift.opening_cash) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Sales</p>
                            <p class="font-medium text-green-600">{{ formatCurrency(activeShift.total_sales) }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <ClockIcon class="w-8 h-8 text-gray-400" />
                    </div>
                    <p class="text-gray-500 mb-4">No active shift</p>
                    <button
                        @click="showOpenModal = true"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center gap-2"
                    >
                        <PlayIcon class="w-5 h-5" />
                        <span>Start New Shift</span>
                    </button>
                </div>
            </div>

            <!-- Shift History -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Shift History</h2>
                </div>

                <div v-if="shiftHistory.length === 0" class="p-8 text-center">
                    <CalendarDaysIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-500">No shift history yet</p>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-for="shift in shiftHistory"
                        :key="shift.id"
                        class="p-4"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center',
                                    shift.status === 'closed' ? 'bg-gray-100' : 'bg-green-100'
                                ]">
                                    <component
                                        :is="shift.status === 'closed' ? CheckCircleIcon : PlayIcon"
                                        :class="[
                                            'w-5 h-5',
                                            shift.status === 'closed' ? 'text-gray-600' : 'text-green-600'
                                        ]"
                                    />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ shift.shift_number }}</p>
                                    <p class="text-xs text-gray-500">{{ formatDate(shift.opened_at) }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-green-600">{{ formatCurrency(shift.total_sales) }}</p>
                                <p class="text-xs text-gray-500">{{ shift.total_transactions }} sales</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-2 text-xs bg-gray-50 rounded-lg p-2">
                            <div>
                                <p class="text-gray-500">Opening</p>
                                <p class="font-medium">{{ formatCurrency(shift.opening_cash) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Closing</p>
                                <p class="font-medium">{{ shift.closing_cash !== null ? formatCurrency(shift.closing_cash) : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Expected</p>
                                <p class="font-medium">{{ shift.expected_cash !== null ? formatCurrency(shift.expected_cash) : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Difference</p>
                                <p :class="[
                                    'font-medium',
                                    shift.cash_difference !== null && shift.cash_difference < 0 ? 'text-red-600' : 'text-green-600'
                                ]">
                                    {{ shift.cash_difference !== null ? formatCurrency(shift.cash_difference) : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Open Shift Modal -->
        <div v-if="showOpenModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-end sm:items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="showOpenModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Start New Shift</h3>
                        <button @click="showOpenModal = false" class="p-1 hover:bg-gray-100 rounded-full">
                            <XCircleIcon class="w-6 h-6 text-gray-400" />
                        </button>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Opening Cash (ZMW)</label>
                            <div class="relative">
                                <BanknotesIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                <input
                                    v-model.number="openingCash"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="0.00"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                            <textarea
                                v-model="notes"
                                rows="2"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Any notes for this shift..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-100 flex gap-2">
                        <button
                            @click="showOpenModal = false"
                            class="flex-1 px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="openShift"
                            :disabled="isSubmitting"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Starting...' : 'Start Shift' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Close Shift Modal -->
        <div v-if="showCloseModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-end sm:items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="showCloseModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">End Shift</h3>
                        <button @click="showCloseModal = false" class="p-1 hover:bg-gray-100 rounded-full">
                            <XCircleIcon class="w-6 h-6 text-gray-400" />
                        </button>
                    </div>
                    <div class="p-4 space-y-4">
                        <div v-if="activeShift" class="bg-blue-50 rounded-lg p-3 text-sm">
                            <p class="text-blue-800">
                                <span class="font-medium">Expected cash:</span>
                                {{ formatCurrency(activeShift.opening_cash + activeShift.total_sales) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Closing Cash (ZMW)</label>
                            <div class="relative">
                                <BanknotesIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                <input
                                    v-model.number="closingCash"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="Count your cash drawer"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                            <textarea
                                v-model="notes"
                                rows="2"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="End of shift notes..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-100 flex gap-2">
                        <button
                            @click="showCloseModal = false"
                            class="flex-1 px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="closeShift"
                            :disabled="isSubmitting"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Closing...' : 'End Shift' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GrowBizLayout>
</template>
