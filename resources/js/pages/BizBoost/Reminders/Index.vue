<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ref } from 'vue';
import {
    BellIcon,
    PlusIcon,
    CheckIcon,
    XMarkIcon,
    EMarkIcon,
    ExclamationTriangleIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';

interface Reminder {
    id: number;
    customer_id: number | null;
    customer_name: string | null;
    customer_whatsapp: string | null;
    title: string;
    description: string | null;
    due_date: string;
    due_time: string;
    reminder_type: string;
    priority: string;
    status: string;
    completed_at: string | null;
    completion_notes: string | null;
}

interface Props {
    reminders: {
        data: Reminder[];
        current_page: number;
        last_page: number;
        total: number;
    };
    stats: {
        total: number;
        pending: number;
        overdue: number;
        completed_today: number;
    };
    customers: { id: number; name: string }[];
}

const props = defineProps<Props>();

const showCreateModal = ref(false);
const showCompleteModal = ref(false);
const selectedReminder = ref<Reminder | null>(null);

const form = useForm({
    customer_id: null as number | null,
    title: '',
    description: '',
    due_date: new Date().toISOString().split('T')[0],
    due_time: '09:00',
    reminder_type: 'follow_up',
    priority: 'medium',
});

const completeForm = useForm({
    notes: '',
});

const priorityColors: Record<string, string> = {
    low: 'bg-gray-100 text-gray-700',
    medium: 'bg-blue-100 text-blue-700',
    high: 'bg-red-100 text-red-700',
};

const typeLabels: Record<string, string> = {
    follow_up: 'Follow-up',
    payment: 'Payment',
    delivery: 'Delivery',
    appointment: 'Appointment',
    custom: 'Custom',
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const isOverdue = (reminder: Reminder) => {
    return reminder.status === 'pending' && new Date(reminder.due_date) < new Date(new Date().toDateString());
};

const createReminder = () => {
    form.post(route('bizboost.reminders.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const openCompleteModal = (reminder: Reminder) => {
    selectedReminder.value = reminder;
    completeForm.notes = '';
    showCompleteModal.value = true;
};

const completeReminder = () => {
    if (!selectedReminder.value) return;
    completeForm.post(route('bizboost.reminders.complete', selectedReminder.value.id), {
        onSuccess: () => {
            showCompleteModal.value = false;
            selectedReminder.value = null;
        },
    });
};

const deleteReminder = (id: number) => {
    if (confirm('Delete this reminder?')) {
        router.delete(route('bizboost.reminders.destroy', id));
    }
};
</script>

<template>
    <Head title="Follow-up Reminders - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <BellIcon class="h-7 w-7 text-blue-600" aria-hidden="true" />
                            Follow-up Reminders
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">Never miss a customer follow-up</p>
                    </div>
                    <button
                        @click="showCreateModal = true"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                        New Reminder
                    </button>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-2xl font-bold text-gray-900">{{ stats.total }}</div>
                        <div class="text-sm text-gray-500">Total</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</div>
                        <div class="text-sm text-gray-500">Pending</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-2xl font-bold text-red-600">{{ stats.overdue }}</div>
                        <div class="text-sm text-gray-500">Overdue</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-2xl font-bold text-green-600">{{ stats.completed_today }}</div>
                        <div class="text-sm text-gray-500">Completed Today</div>
                    </div>
                </div>

                <!-- Reminders List -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div v-if="reminders.data.length === 0" class="text-center py-12">
                        <BellIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No reminders</h3>
                        <p class="mt-1 text-sm text-gray-500">Create follow-up reminders for your customers.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div
                            v-for="reminder in reminders.data"
                            :key="reminder.id"
                            :class="['p-4 hover:bg-gray-50', isOverdue(reminder) ? 'bg-red-50' : '']"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-semibold text-gray-900">{{ reminder.title }}</span>
                                        <span :class="['px-2 py-0.5 rounded text-xs font-medium', priorityColors[reminder.priority]]">
                                            {{ reminder.priority }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                            {{ typeLabels[reminder.reminder_type] || reminder.reminder_type }}
                                        </span>
                                        <span v-if="isOverdue(reminder)" class="flex items-center gap-1 text-red-600 text-xs">
                                            <ExclamationTriangleIcon class="h-4 w-4" aria-hidden="true" />
                                            Overdue
                                        </span>
                                    </div>
                                    <p v-if="reminder.description" class="text-sm text-gray-600 mb-2">{{ reminder.description }}</p>
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        <span v-if="reminder.customer_name" class="flex items-center gap-1">
                                            Customer: {{ reminder.customer_name }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <CalendarIcon class="h-4 w-4" aria-hidden="true" />
                                            {{ formatDate(reminder.due_date) }} at {{ reminder.due_time }}
                                        </span>
                                    </div>
                                </div>
                                <div v-if="reminder.status === 'pending'" class="flex items-center gap-2">
                                    <button
                                        @click="openCompleteModal(reminder)"
                                        class="p-2 text-green-600 hover:bg-green-100 rounded"
                                        aria-label="Mark as complete"
                                    >
                                        <CheckIcon class="h-5 w-5" aria-hidden="true" />
                                    </button>
                                    <button
                                        @click="deleteReminder(reminder.id)"
                                        class="p-2 text-red-600 hover:bg-red-100 rounded"
                                        aria-label="Delete reminder"
                                    >
                                        <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                                    </button>
                                </div>
                                <span v-else class="text-sm text-green-600 flex items-center gap-1">
                                    <CheckIcon class="h-4 w-4" aria-hidden="true" />
                                    Completed
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Teleport to="body">
            <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
                    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold mb-4">New Reminder</h3>
                        <form @submit.prevent="createReminder" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                                <input v-model="form.title" type="text" required class="w-full border rounded-lg px-3 py-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Customer (optional)</label>
                                <select v-model="form.customer_id" class="w-full border rounded-lg px-3 py-2">
                                    <option :value="null">No customer</option>
                                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea v-model="form.description" rows="2" class="w-full border rounded-lg px-3 py-2"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date *</label>
                                    <input v-model="form.due_date" type="date" required class="w-full border rounded-lg px-3 py-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                                    <input v-model="form.due_time" type="time" class="w-full border rounded-lg px-3 py-2" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                    <select v-model="form.reminder_type" class="w-full border rounded-lg px-3 py-2">
                                        <option value="follow_up">Follow-up</option>
                                        <option value="payment">Payment</option>
                                        <option value="delivery">Delivery</option>
                                        <option value="appointment">Appointment</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                    <select v-model="form.priority" class="w-full border rounded-lg px-3 py-2">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 pt-4">
                                <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    Create Reminder
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Complete Modal -->
        <Teleport to="body">
            <div v-if="showCompleteModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50" @click="showCompleteModal = false"></div>
                    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold mb-4">Complete Reminder</h3>
                        <form @submit.prevent="completeReminder" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <textarea v-model="completeForm.notes" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="Add any notes about this follow-up..."></textarea>
                            </div>
                            <div class="flex justify-end gap-3 pt-4">
                                <button type="button" @click="showCompleteModal = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="completeForm.processing" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
                                    Mark Complete
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </BizBoostLayout>
</template>
