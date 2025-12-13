<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    CalendarDaysIcon,
    ClockIcon,
    UserIcon,
    PhoneIcon,
    EnvelopeIcon,
    CurrencyDollarIcon,
    PencilIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowPathIcon,
    DocumentTextIcon,
} from '@heroicons/vue/24/outline';

interface Appointment {
    id: number;
    booking_reference: string;
    appointment_date: string;
    formatted_date: string;
    start_time: string;
    end_time: string;
    formatted_time: string;
    duration_minutes: number;
    status: string;
    status_label: string;
    status_bg_color: string;
    price: number;
    formatted_price: string;
    currency: string;
    payment_status: string;
    amount_paid: number;
    balance_due: number;
    customer_notes: string | null;
    internal_notes: string | null;
    booking_source: string;
    booking_source_label: string;
    cancellation_reason: string | null;
    service: { id: number; name: string; color: string } | null;
    provider: { id: number; name: string; initials: string } | null;
    customer: { id: number; name: string; email: string; phone: string; initials: string } | null;
}

const props = defineProps<{
    appointment: Appointment;
    services: { id: number; name: string }[];
    providers: { id: number; name: string }[];
}>();

const showRescheduleModal = ref(false);
const showCancelModal = ref(false);
const isSubmitting = ref(false);

const rescheduleForm = ref({
    appointment_date: props.appointment.appointment_date,
    start_time: props.appointment.start_time.substring(0, 5),
});

const cancelReason = ref('');

const updateStatus = (status: string) => {
    router.patch(route('growbiz.appointments.status', props.appointment.id), { status }, {
        preserveScroll: true,
    });
};

const reschedule = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    router.post(route('growbiz.appointments.reschedule', props.appointment.id), rescheduleForm.value, {
        onSuccess: () => {
            showRescheduleModal.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const cancelAppointment = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    router.patch(route('growbiz.appointments.status', props.appointment.id), {
        status: 'cancelled',
        reason: cancelReason.value,
    }, {
        onSuccess: () => {
            showCancelModal.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const deleteAppointment = () => {
    if (!confirm('Delete this appointment? This cannot be undone.')) return;
    router.delete(route('growbiz.appointments.destroy', props.appointment.id));
};

const getPaymentStatusColor = (status: string) => {
    switch (status) {
        case 'paid': return 'bg-green-100 text-green-800';
        case 'partial': return 'bg-yellow-100 text-yellow-800';
        case 'refunded': return 'bg-gray-100 text-gray-800';
        default: return 'bg-red-100 text-red-800';
    }
};

const canModify = ['pending', 'confirmed'].includes(props.appointment.status);
</script>

<template>
    <GrowBizLayout>
        <div class="p-4 space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('growbiz.appointments.index')" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Back">
                        <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ appointment.booking_reference }}</h1>
                        <span :class="appointment.status_bg_color" class="px-2 py-0.5 rounded-full text-xs font-medium">
                            {{ appointment.status_label }}
                        </span>
                    </div>
                </div>
                <div v-if="canModify" class="flex items-center gap-2">
                    <button
                        @click="showRescheduleModal = true"
                        class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg"
                        aria-label="Reschedule"
                    >
                        <ArrowPathIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                    <button
                        @click="deleteAppointment"
                        class="p-2 text-red-500 hover:bg-red-50 rounded-lg"
                        aria-label="Delete"
                    >
                        <TrashIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <!-- Service Card -->
            <div class="bg-white rounded-xl p-4 border border-gray-100">
                <div class="flex items-start gap-3">
                    <div
                        class="w-2 h-16 rounded-full"
                        :style="{ backgroundColor: appointment.service?.color || '#6b7280' }"
                    ></div>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-gray-900">{{ appointment.service?.name || 'Service' }}</h2>
                        <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-gray-500">
                            <span class="flex items-center gap-1">
                                <CalendarDaysIcon class="h-4 w-4" aria-hidden="true" />
                                {{ appointment.formatted_date }}
                            </span>
                            <span class="flex items-center gap-1">
                                <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                {{ appointment.formatted_time }}
                            </span>
                            <span class="flex items-center gap-1">
                                <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                {{ appointment.duration_minutes }} min
                            </span>
                        </div>
                        <div v-if="appointment.provider" class="mt-2 text-sm text-gray-600">
                            Staff: {{ appointment.provider.name }}
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-gray-900">{{ appointment.formatted_price }}</p>
                        <span :class="getPaymentStatusColor(appointment.payment_status)" class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">
                            {{ appointment.payment_status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer Card -->
            <div v-if="appointment.customer" class="bg-white rounded-xl p-4 border border-gray-100">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Customer</h3>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                        <span class="text-lg font-semibold text-emerald-600">{{ appointment.customer.initials }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900">{{ appointment.customer.name }}</p>
                        <div class="flex flex-wrap items-center gap-3 mt-1 text-sm text-gray-500">
                            <a v-if="appointment.customer.phone" :href="`tel:${appointment.customer.phone}`" class="flex items-center gap-1 hover:text-emerald-600">
                                <PhoneIcon class="h-4 w-4" aria-hidden="true" />
                                {{ appointment.customer.phone }}
                            </a>
                            <a v-if="appointment.customer.email" :href="`mailto:${appointment.customer.email}`" class="flex items-center gap-1 hover:text-emerald-600">
                                <EnvelopeIcon class="h-4 w-4" aria-hidden="true" />
                                {{ appointment.customer.email }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="appointment.customer_notes || appointment.internal_notes" class="bg-white rounded-xl p-4 border border-gray-100 space-y-3">
                <div v-if="appointment.customer_notes">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Customer Notes</h3>
                    <p class="text-gray-700">{{ appointment.customer_notes }}</p>
                </div>
                <div v-if="appointment.internal_notes">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Internal Notes</h3>
                    <p class="text-gray-700">{{ appointment.internal_notes }}</p>
                </div>
            </div>

            <!-- Cancellation Reason -->
            <div v-if="appointment.cancellation_reason" class="bg-red-50 rounded-xl p-4 border border-red-100">
                <h3 class="text-sm font-medium text-red-800 mb-1">Cancellation Reason</h3>
                <p class="text-red-700">{{ appointment.cancellation_reason }}</p>
            </div>

            <!-- Payment Details -->
            <div v-if="appointment.balance_due > 0" class="bg-yellow-50 rounded-xl p-4 border border-yellow-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-yellow-800">Balance Due</h3>
                        <p class="text-lg font-bold text-yellow-900">{{ appointment.currency }} {{ appointment.balance_due.toFixed(2) }}</p>
                    </div>
                    <button class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm font-medium">
                        Record Payment
                    </button>
                </div>
            </div>

            <!-- Quick Actions -->
            <div v-if="canModify" class="bg-white rounded-xl p-4 border border-gray-100">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        v-if="appointment.status === 'pending'"
                        @click="updateStatus('confirmed')"
                        class="flex items-center justify-center gap-2 py-3 bg-blue-50 text-blue-700 rounded-xl hover:bg-blue-100 font-medium"
                    >
                        <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                        Confirm
                    </button>
                    <button
                        v-if="appointment.status === 'confirmed'"
                        @click="updateStatus('in_progress')"
                        class="flex items-center justify-center gap-2 py-3 bg-indigo-50 text-indigo-700 rounded-xl hover:bg-indigo-100 font-medium"
                    >
                        <ArrowPathIcon class="h-5 w-5" aria-hidden="true" />
                        Start
                    </button>
                    <button
                        v-if="appointment.status === 'confirmed'"
                        @click="updateStatus('no_show')"
                        class="flex items-center justify-center gap-2 py-3 bg-gray-50 text-gray-700 rounded-xl hover:bg-gray-100 font-medium"
                    >
                        <XCircleIcon class="h-5 w-5" aria-hidden="true" />
                        No Show
                    </button>
                    <button
                        @click="showCancelModal = true"
                        class="flex items-center justify-center gap-2 py-3 bg-red-50 text-red-700 rounded-xl hover:bg-red-100 font-medium"
                    >
                        <XCircleIcon class="h-5 w-5" aria-hidden="true" />
                        Cancel
                    </button>
                </div>
            </div>

            <!-- Complete Button -->
            <button
                v-if="appointment.status === 'in_progress'"
                @click="updateStatus('completed')"
                class="w-full py-4 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 flex items-center justify-center gap-2"
            >
                <CheckCircleIcon class="h-6 w-6" aria-hidden="true" />
                Complete Appointment
            </button>

            <!-- Booking Info -->
            <div class="text-center text-sm text-gray-400 py-4">
                Booked via {{ appointment.booking_source_label }}
            </div>
        </div>

        <!-- Reschedule Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
            >
                <div v-if="showRescheduleModal" class="fixed inset-0 z-50 bg-black/50" @click="showRescheduleModal = false"></div>
            </Transition>
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-from-class="translate-y-0"
                leave-to-class="translate-y-full"
            >
                <div v-if="showRescheduleModal" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl p-4 safe-area-bottom">
                    <h2 class="text-lg font-semibold mb-4">Reschedule Appointment</h2>
                    <form @submit.prevent="reschedule" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Date</label>
                            <input v-model="rescheduleForm.appointment_date" type="date" required class="w-full border-gray-200 rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Time</label>
                            <input v-model="rescheduleForm.start_time" type="time" required class="w-full border-gray-200 rounded-lg" />
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showRescheduleModal = false" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl">
                                Cancel
                            </button>
                            <button type="submit" :disabled="isSubmitting" class="flex-1 py-3 bg-emerald-600 text-white rounded-xl disabled:opacity-50">
                                {{ isSubmitting ? 'Saving...' : 'Reschedule' }}
                            </button>
                        </div>
                    </form>
                </div>
            </Transition>
        </Teleport>

        <!-- Cancel Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-150"
            >
                <div v-if="showCancelModal" class="fixed inset-0 z-50 bg-black/50" @click="showCancelModal = false"></div>
            </Transition>
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-to-class="translate-y-full"
            >
                <div v-if="showCancelModal" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl p-4 safe-area-bottom">
                    <h2 class="text-lg font-semibold mb-4">Cancel Appointment</h2>
                    <form @submit.prevent="cancelAppointment" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label>
                            <textarea v-model="cancelReason" rows="3" class="w-full border-gray-200 rounded-lg" placeholder="Why is this being cancelled?"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showCancelModal = false" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl">
                                Keep Appointment
                            </button>
                            <button type="submit" :disabled="isSubmitting" class="flex-1 py-3 bg-red-600 text-white rounded-xl disabled:opacity-50">
                                {{ isSubmitting ? 'Cancelling...' : 'Cancel Appointment' }}
                            </button>
                        </div>
                    </form>
                </div>
            </Transition>
        </Teleport>
    </GrowBizLayout>
</template>
