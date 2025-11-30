<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { computed, watch } from 'vue';
import { ArrowLeftIcon, CalendarDaysIcon } from '@heroicons/vue/24/outline';

interface Balance {
    type: string;
    label: string;
    allowance: number;
    used: number;
    remaining: number;
    available: number;
}

interface Props {
    balances: Record<string, Balance>;
}

const props = defineProps<Props>();

const form = useForm({
    type: 'annual',
    start_date: '',
    end_date: '',
    days_requested: 1,
    reason: '',
});

const selectedBalance = computed(() => props.balances[form.type]);

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

const submit = () => {
    form.post(route('employee.portal.time-off.store'));
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
</script>

<template>
    <Head title="Request Time Off" />

    <EmployeePortalLayout>
        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('employee.portal.time-off.index')"
                    class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    aria-label="Back to time off">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Request Time Off</h1>
                    <p class="text-gray-500 mt-1">Submit a new leave request</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <!-- Leave Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Leave Type
                    </label>
                    <select id="type" v-model="form.type"
                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option v-for="type in leaveTypes" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                </div>

                <!-- Balance Info -->
                <div v-if="selectedBalance" class="bg-blue-50 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <CalendarDaysIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
                        <div>
                            <p class="text-sm text-blue-700">{{ selectedBalance.label }} Balance</p>
                            <p class="text-lg font-bold text-blue-900">
                                {{ selectedBalance.available }} days available
                                <span class="text-sm font-normal text-blue-600">
                                    ({{ selectedBalance.remaining }} remaining, {{ selectedBalance.used }} used)
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Start Date
                        </label>
                        <input type="date" id="start_date" v-model="form.start_date"
                            :min="today"
                            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            End Date
                        </label>
                        <input type="date" id="end_date" v-model="form.end_date"
                            :min="form.start_date || today"
                            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">{{ form.errors.end_date }}</p>
                    </div>
                </div>

                <!-- Days Requested -->
                <div>
                    <label for="days_requested" class="block text-sm font-medium text-gray-700 mb-2">
                        Days Requested
                    </label>
                    <input type="number" id="days_requested" v-model.number="form.days_requested"
                        min="0.5" step="0.5"
                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                    <p class="mt-1 text-sm text-gray-500">
                        You can request half days (0.5) if needed
                    </p>
                    <p v-if="form.errors.days_requested" class="mt-1 text-sm text-red-600">{{ form.errors.days_requested }}</p>
                </div>

                <!-- Reason -->
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason (Optional)
                    </label>
                    <textarea id="reason" v-model="form.reason" rows="3"
                        placeholder="Provide additional details about your request..."
                        class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </textarea>
                    <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600">{{ form.errors.reason }}</p>
                </div>

                <!-- Warning if insufficient balance -->
                <div v-if="selectedBalance && form.days_requested > selectedBalance.available"
                    class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-700">
                        You don't have enough {{ selectedBalance.label.toLowerCase() }} balance for this request.
                        Available: {{ selectedBalance.available }} days, Requested: {{ form.days_requested }} days.
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                    <Link :href="route('employee.portal.time-off.index')"
                        class="px-4 py-2 text-gray-700 hover:text-gray-900 transition-colors">
                        Cancel
                    </Link>
                    <button type="submit"
                        :disabled="form.processing || (selectedBalance && form.days_requested > selectedBalance.available)"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        {{ form.processing ? 'Submitting...' : 'Submit Request' }}
                    </button>
                </div>
            </form>
        </div>
    </EmployeePortalLayout>
</template>
