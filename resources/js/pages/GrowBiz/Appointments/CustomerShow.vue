<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    UserIcon,
    EnvelopeIcon,
    PhoneIcon,
    CalendarDaysIcon,
    ClockIcon,
    PencilIcon,
    TrashIcon,
    XMarkIcon,
    CurrencyDollarIcon,
} from '@heroicons/vue/24/outline';

interface Appointment {
    id: number;
    booking_reference: string;
    appointment_date: string;
    formatted_date: string;
    start_time: string;
    formatted_time: string;
    status: string;
    status_label: string;
    status_bg_color: string;
    formatted_price: string;
    service: {
        id: number;
        name: string;
        color: string;
    } | null;
}

interface Customer {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    notes: string | null;
    initials: string;
    total_appointments: number;
    completed_appointments: number;
    total_spent: number;
    formatted_total_spent: string;
    last_visit: string | null;
    appointments: Appointment[];
    created_at: string;
}

const props = defineProps<{
    customer: Customer;
}>();

const showEditModal = ref(false);
const isSubmitting = ref(false);

const form = ref({
    name: props.customer.name,
    email: props.customer.email || '',
    phone: props.customer.phone || '',
    notes: props.customer.notes || '',
});

const updateCustomer = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    router.put(route('growbiz.appointments.customers.update', props.customer.id), form.value, {
        onSuccess: () => {
            showEditModal.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const deleteCustomer = () => {
    if (!confirm(`Are you sure you want to delete ${props.customer.name}? This will not delete their appointments.`)) return;
    
    router.delete(route('growbiz.appointments.customers.destroy', props.customer.id), {
        onSuccess: () => {
            router.visit(route('growbiz.appointments.customers'));
        },
    });
};
</script>

<template>
    <GrowBizLayout>
        <div class="p-4 space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('growbiz.appointments.customers')" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Back to customers">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                    </Link>
                    <h1 class="text-xl font-bold text-gray-900">Customer Details</h1>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="showEditModal = true"
                        class="p-2 hover:bg-gray-100 rounded-lg"
                        aria-label="Edit customer"
                    >
                        <PencilIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                    </button>
                    <button
                        @click="deleteCustomer"
                        class="p-2 hover:bg-red-50 rounded-lg"
                        aria-label="Delete customer"
                    >
                        <TrashIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <!-- Customer Card -->
            <div class="bg-white rounded-xl p-6 border border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center">
                        <span class="text-2xl font-bold text-emerald-600">{{ customer.initials }}</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ customer.name }}</h2>
                        <div class="flex flex-col gap-1 mt-1">
                            <span v-if="customer.email" class="flex items-center gap-1 text-sm text-gray-500">
                                <EnvelopeIcon class="h-4 w-4" aria-hidden="true" />
                                {{ customer.email }}
                            </span>
                            <span v-if="customer.phone" class="flex items-center gap-1 text-sm text-gray-500">
                                <PhoneIcon class="h-4 w-4" aria-hidden="true" />
                                {{ customer.phone }}
                            </span>
                        </div>
                    </div>
                </div>
                <p v-if="customer.notes" class="mt-4 text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                    {{ customer.notes }}
                </p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white rounded-xl p-4 border border-gray-100 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ customer.total_appointments }}</p>
                    <p class="text-xs text-gray-500">Total Visits</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-100 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ customer.completed_appointments }}</p>
                    <p class="text-xs text-gray-500">Completed</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-100 text-center">
                    <p class="text-2xl font-bold text-emerald-600">{{ customer.formatted_total_spent }}</p>
                    <p class="text-xs text-gray-500">Total Spent</p>
                </div>
            </div>

            <!-- Last Visit -->
            <div v-if="customer.last_visit" class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                <div class="flex items-center gap-2 text-blue-700">
                    <CalendarDaysIcon class="h-5 w-5" aria-hidden="true" />
                    <span class="text-sm font-medium">Last visit: {{ customer.last_visit }}</span>
                </div>
            </div>

            <!-- Appointment History -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Appointment History</h3>
                
                <div v-if="customer.appointments.length === 0" class="text-center py-8 bg-white rounded-xl border border-gray-100">
                    <CalendarDaysIcon class="h-10 w-10 mx-auto text-gray-300 mb-2" aria-hidden="true" />
                    <p class="text-gray-500">No appointments yet</p>
                </div>

                <div class="space-y-2">
                    <Link
                        v-for="apt in customer.appointments"
                        :key="apt.id"
                        :href="route('growbiz.appointments.show', apt.id)"
                        class="block bg-white rounded-xl p-4 border border-gray-100 hover:border-emerald-200 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-1 h-10 rounded-full"
                                    :style="{ backgroundColor: apt.service?.color || '#6b7280' }"
                                ></div>
                                <div>
                                    <span class="font-medium text-gray-900">{{ apt.service?.name || 'Service' }}</span>
                                    <div class="flex items-center gap-2 mt-0.5 text-sm text-gray-500">
                                        <span>{{ apt.formatted_date }}</span>
                                        <span>·</span>
                                        <span>{{ apt.formatted_time }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span :class="apt.status_bg_color" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                    {{ apt.status_label }}
                                </span>
                                <p class="text-sm font-medium text-gray-900 mt-1">{{ apt.formatted_price }}</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showEditModal" class="fixed inset-0 z-50 bg-black/50" @click="showEditModal = false"></div>
            </Transition>
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-from-class="translate-y-0"
                leave-to-class="translate-y-full"
            >
                <div v-if="showEditModal" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl max-h-[90vh] overflow-y-auto safe-area-bottom">
                    <div class="sticky top-0 bg-white border-b border-gray-100 px-4 py-3 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Edit Customer</h2>
                        <button @click="showEditModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                    <form @submit.prevent="updateCustomer" class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input v-model="form.name" type="text" required class="w-full border-gray-200 rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="form.email" type="email" class="w-full border-gray-200 rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input v-model="form.phone" type="tel" class="w-full border-gray-200 rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="form.notes" rows="3" class="w-full border-gray-200 rounded-lg" placeholder="Any notes about this customer..."></textarea>
                        </div>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="w-full py-3 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Saving...' : 'Update Customer' }}
                        </button>
                    </form>
                </div>
            </Transition>
        </Teleport>
    </GrowBizLayout>
</template>
