<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    CalendarDaysIcon,
    ClockIcon,
    UserIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowPathIcon,
    ArrowLeftIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';

interface Service {
    id: number;
    name: string;
    color: string;
}

interface Provider {
    id: number;
    name: string;
    initials: string;
}

interface Customer {
    id: number;
    name: string;
    phone: string;
    initials: string;
}

interface Appointment {
    id: number;
    booking_reference: string;
    appointment_date: string;
    formatted_date: string;
    start_time: string;
    end_time: string;
    formatted_time: string;
    status: string;
    status_label: string;
    status_bg_color: string;
    formatted_price: string;
    service: Service | null;
    provider: Provider | null;
    customer: Customer | null;
    is_today: boolean;
    days_until: number;
}

const props = defineProps<{
    appointments: Appointment[];
}>();

const updateStatus = (id: number, status: string) => {
    router.patch(route('growbiz.appointments.status', id), { status }, {
        preserveScroll: true,
    });
};

const getStatusActions = (status: string) => {
    switch (status) {
        case 'pending':
            return [
                { label: 'Confirm', status: 'confirmed', icon: CheckCircleIcon, color: 'text-blue-600 bg-blue-50' },
                { label: 'Cancel', status: 'cancelled', icon: XCircleIcon, color: 'text-red-600 bg-red-50' },
            ];
        case 'confirmed':
            return [
                { label: 'Cancel', status: 'cancelled', icon: XCircleIcon, color: 'text-red-600 bg-red-50' },
            ];
        default:
            return [];
    }
};

// Group appointments by date
const groupedAppointments = computed(() => {
    const groups: Record<string, Appointment[]> = {};
    props.appointments.forEach(apt => {
        if (!groups[apt.appointment_date]) {
            groups[apt.appointment_date] = [];
        }
        groups[apt.appointment_date].push(apt);
    });
    return groups;
});

const formatDateHeader = (dateStr: string) => {
    const date = new Date(dateStr);
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    if (date.toDateString() === today.toDateString()) {
        return 'Today';
    }
    if (date.toDateString() === tomorrow.toDateString()) {
        return 'Tomorrow';
    }
    return date.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
};
</script>

<template>
    <GrowBizLayout>
        <div class="p-4 space-y-4">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <Link :href="route('growbiz.appointments.index')" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Back to appointments">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">Upcoming Appointments</h1>
                    <p class="text-sm text-gray-500">{{ appointments.length }} scheduled</p>
                </div>
                <div class="p-2 bg-blue-100 rounded-lg">
                    <CalendarIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="appointments.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
                <CalendarDaysIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                <p class="text-gray-500">No upcoming appointments</p>
                <Link :href="route('growbiz.appointments.index')" class="mt-3 inline-block text-emerald-600 font-medium">
                    Book an appointment
                </Link>
            </div>

            <!-- Grouped Appointments -->
            <div v-for="(dayAppointments, date) in groupedAppointments" :key="date" class="space-y-3">
                <!-- Date Header -->
                <div class="flex items-center gap-2 pt-2">
                    <CalendarDaysIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                    <span class="text-sm font-semibold text-gray-700">{{ formatDateHeader(date) }}</span>
                    <span class="text-xs text-gray-400">({{ dayAppointments.length }})</span>
                </div>

                <!-- Appointments for this date -->
                <div
                    v-for="apt in dayAppointments"
                    :key="apt.id"
                    class="bg-white rounded-xl border border-gray-100 overflow-hidden"
                >
                    <Link :href="route('growbiz.appointments.show', apt.id)" class="block p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-1 h-12 rounded-full"
                                    :style="{ backgroundColor: apt.service?.color || '#6b7280' }"
                                ></div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-900">{{ apt.service?.name || 'Service' }}</span>
                                        <span :class="apt.status_bg_color" class="px-2 py-0.5 rounded-full text-xs font-medium">
                                            {{ apt.status_label }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                            {{ apt.formatted_time }}
                                        </span>
                                    </div>
                                    <div v-if="apt.customer" class="flex items-center gap-1 mt-1 text-sm text-gray-600">
                                        <UserIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ apt.customer.name }}
                                        <span v-if="apt.customer.phone" class="text-gray-400">· {{ apt.customer.phone }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ apt.formatted_price }}</p>
                                <p class="text-xs text-gray-400">{{ apt.booking_reference }}</p>
                            </div>
                        </div>
                    </Link>
                    
                    <!-- Quick Actions -->
                    <div v-if="getStatusActions(apt.status).length > 0" class="flex items-center gap-2 px-4 pb-3">
                        <button
                            v-for="action in getStatusActions(apt.status)"
                            :key="action.status"
                            @click="updateStatus(apt.id, action.status)"
                            class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg"
                            :class="action.color"
                        >
                            <component :is="action.icon" class="h-4 w-4" aria-hidden="true" />
                            {{ action.label }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GrowBizLayout>
</template>
