<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import POSLayout from '@/layouts/POSLayout.vue';
import {
    ClockIcon,
    PlayIcon,
    StopIcon,
    BanknotesIcon,
    DocumentTextIcon,
} from '@heroicons/vue/24/outline';

interface Shift {
    id: number;
    shift_number: string;
    status: 'open' | 'closed';
    opening_cash: number;
    closing_cash: number | null;
    total_sales: number;
    transaction_count: number;
    started_at: string;
    ended_at: string | null;
}

interface Props {
    shifts: {
        data: Shift[];
        links: any;
    };
    filters: Record<string, any>;
}

const props = withDefaults(defineProps<Props>(), {
    shifts: () => ({ data: [], links: {} }),
    filters: () => ({}),
});

const showStartModal = ref(false);
const showCloseModal = ref<number | null>(null);

const startForm = useForm({
    opening_cash: 0,
    opening_notes: '',
});

const closeForm = useForm({
    closing_cash: 0,
    closing_notes: '',
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-ZM', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};

const startShift = () => {
    startForm.post(route('pos.shifts.start'), {
        onSuccess: () => {
            showStartModal.value = false;
            startForm.reset();
        },
    });
};

const closeShift = (shiftId: number) => {
    closeForm.post(route('pos.shifts.close', shiftId), {
        onSuccess: () => {
            showCloseModal.value = null;
            closeForm.reset();
        },
    });
};
</script>

<template>
    <POSLayout title="Shifts">
        <Head title="POS Shifts" />

        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Shift Management</h1>
                        <p class="mt-1 text-sm text-gray-500">Start, manage, and close shifts</p>
                    </div>
                    <button
                        @click="showStartModal = true"
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700"
                    >
                        <PlayIcon class="h-5 w-5" aria-hidden="true" />
                        Start New Shift
                    </button>
                </div>

                <!-- Shifts List -->
                <div class="rounded-xl bg-white shadow-sm">
                    <div v-if="shifts.data.length === 0" class="p-8 text-center">
                        <ClockIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                        <p class="mt-2 text-gray-500">No shifts found</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div
                            v-for="shift in shifts.data"
                            :key="shift.id"
                            class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    :class="[
                                        'rounded-full p-2',
                                        shift.status === 'open' ? 'bg-green-100' : 'bg-gray-100',
                                    ]"
                                >
                                    <ClockIcon
                                        :class="[
                                            'h-6 w-6',
                                            shift.status === 'open' ? 'text-green-600' : 'text-gray-400',
                                        ]"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ shift.shift_number }}</p>
                                    <p class="text-sm text-gray-500">
                                        Started: {{ formatDateTime(shift.started_at) }}
                                    </p>
                                    <p v-if="shift.ended_at" class="text-sm text-gray-500">
                                        Ended: {{ formatDateTime(shift.ended_at) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-6">
                                <div class="text-center">
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ formatCurrency(shift.total_sales) }}
                                    </p>
                                    <p class="text-xs text-gray-500">Total Sales</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ shift.transaction_count }}
                                    </p>
                                    <p class="text-xs text-gray-500">Transactions</p>
                                </div>
                                <span
                                    :class="[
                                        'rounded-full px-3 py-1 text-xs font-medium',
                                        shift.status === 'open'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-100 text-gray-700',
                                    ]"
                                >
                                    {{ shift.status === 'open' ? 'Active' : 'Closed' }}
                                </span>
                                <button
                                    v-if="shift.status === 'open'"
                                    @click="showCloseModal = shift.id"
                                    class="rounded-lg bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-200"
                                >
                                    Close Shift
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Start Shift Modal -->
        <div v-if="showStartModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Start New Shift</h3>
                <form @submit.prevent="startShift">
                    <div class="mb-4">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Opening Cash</label>
                        <input
                            v-model.number="startForm.opening_cash"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500"
                        />
                    </div>
                    <div class="mb-4">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Notes (optional)</label>
                        <textarea
                            v-model="startForm.opening_notes"
                            rows="2"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500"
                        ></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="showStartModal = false"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="startForm.processing"
                            class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 disabled:opacity-50"
                        >
                            Start Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Close Shift Modal -->
        <div v-if="showCloseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Close Shift</h3>
                <form @submit.prevent="closeShift(showCloseModal!)">
                    <div class="mb-4">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Closing Cash</label>
                        <input
                            v-model.number="closeForm.closing_cash"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500"
                        />
                    </div>
                    <div class="mb-4">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Notes (optional)</label>
                        <textarea
                            v-model="closeForm.closing_notes"
                            rows="2"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500"
                        ></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="showCloseModal = null"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="closeForm.processing"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            Close Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </POSLayout>
</template>
