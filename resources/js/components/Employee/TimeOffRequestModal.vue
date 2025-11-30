<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    XMarkIcon,
    CalendarDaysIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Balance {
    type: string;
    label: string;
    allowance: number;
    used: number;
    remaining: number;
    available: number;
}

interface Props {
    show: boolean;
    balances: Record<string, Balance>;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'success'): void;
}>();

const form = useForm({
    type: 'annual',
    start_date: '',
    end_date: '',
    days_requested: 1,
    reason: '',
});

const selectedBalance = computed(() => props.balances?.[form.type]);

const calculateDays = () => {
    if (form.start_date && form.end_date) {
        const start = new Date(form.start_date);
        const end = new Date(form.end_date);
        const diffTime = Math.abs(end.getTime() - start.getTime());
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        form.days_requested = diffDays;
    }
};

watch([() => form.start_date, () => form.end_date], calculateDays);

// Reset form when modal opens
watch(() => props.show, (newVal) => {
    if (newVal) {
        form.reset();
        form.clearErrors();
    }
});

const submit = () => {
    form.post(route('employee.portal.time-off.store'), {
        onSuccess: () => {
            emit('success');
            emit('close');
        },
    });
};

const close = () => {
    if (!form.processing) {
        emit('close');
    }
};

const leaveTypes = [
    { value: 'annual', label: 'Annual Leave' },
    { value: 'sick', label: 'Sick Leave' },
    { value: 'personal', label: 'Personal Leave' },
    { value: 'maternity', label: 'Maternity Leave' },
    { value: 'paternity', label: 'Paternity Leave' },
    { value: 'bereavement', label: 'Bereavement Leave' },
    { value: 'unpaid', label: 'Unpaid Leave' },
    { value: 'study', label: 'Study Leave' },
];

const today = new Date().toISOString().split('T')[0];

const insufficientBalance = computed(() => {
    return selectedBalance.value && form.days_requested > selectedBalance.value.available;
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="close"></div>

                <!-- Modal -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95">
                        <div v-if="show" 
                            class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl">
                            <!-- Header -->
                            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <CalendarDaysIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-900">Request Time Off</h2>
                                        <p class="text-sm text-gray-500">Submit a new leave request</p>
                                    </div>
                                </div>
                                <button @click="close" 
                                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                    aria-label="Close modal">
                                    <XMarkIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </button>
                            </div>

                            <!-- Body -->
                            <form @submit.prevent="submit" class="p-5 space-y-5">
                                <!-- Leave Type -->
                                <div>
                                    <label for="modal-type" class="block text-sm font-medium text-gray-700 mb-2">
                                        Leave Type
                                    </label>
                                    <select id="modal-type" v-model="form.type"
                                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="type in leaveTypes" :key="type.value" :value="type.value">
                                            {{ type.label }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                                </div>

                                <!-- Balance Info -->
                                <div v-if="selectedBalance" class="bg-blue-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-blue-700">{{ selectedBalance.label }} Balance</p>
                                            <p class="text-lg font-bold text-blue-900">
                                                {{ selectedBalance.available }} days available
                                            </p>
                                        </div>
                                        <div class="text-right text-sm text-blue-600">
                                            <p>{{ selectedBalance.used }} used</p>
                                            <p>{{ selectedBalance.remaining }} remaining</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Date Range -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="modal-start" class="block text-sm font-medium text-gray-700 mb-2">
                                            Start Date
                                        </label>
                                        <input type="date" id="modal-start" v-model="form.start_date"
                                            :min="today"
                                            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                                        <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.start_date }}
                                        </p>
                                    </div>
                                    <div>
                                        <label for="modal-end" class="block text-sm font-medium text-gray-700 mb-2">
                                            End Date
                                        </label>
                                        <input type="date" id="modal-end" v-model="form.end_date"
                                            :min="form.start_date || today"
                                            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                                        <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.end_date }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Days Summary -->
                                <div v-if="form.start_date && form.end_date" 
                                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Days Requested</span>
                                    <span class="text-lg font-bold text-gray-900">{{ form.days_requested }} days</span>
                                </div>

                                <!-- Reason -->
                                <div>
                                    <label for="modal-reason" class="block text-sm font-medium text-gray-700 mb-2">
                                        Reason <span class="text-gray-400">(Optional)</span>
                                    </label>
                                    <textarea id="modal-reason" v-model="form.reason" rows="3"
                                        placeholder="Provide additional details about your request..."
                                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    </textarea>
                                    <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.reason }}
                                    </p>
                                </div>

                                <!-- Warning if insufficient balance -->
                                <div v-if="insufficientBalance"
                                    class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <ExclamationTriangleIcon class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                    <div class="text-sm text-red-700">
                                        <p class="font-medium">Insufficient Balance</p>
                                        <p>You only have {{ selectedBalance?.available }} days available, 
                                           but you're requesting {{ form.days_requested }} days.</p>
                                    </div>
                                </div>
                            </form>

                            <!-- Footer -->
                            <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                                <button type="button" @click="close"
                                    :disabled="form.processing"
                                    class="px-4 py-2 text-gray-700 hover:text-gray-900 transition-colors">
                                    Cancel
                                </button>
                                <button @click="submit"
                                    :disabled="form.processing || insufficientBalance || !form.start_date || !form.end_date"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                    <span v-if="form.processing" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Submitting...
                                    </span>
                                    <span v-else>Submit Request</span>
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
