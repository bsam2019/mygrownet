<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    CalendarDaysIcon,
    ClockIcon,
    UserIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowPathIcon,
    ArrowLeftIcon,
    SunIcon,
} from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';

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
}

interface Statistics {
    total: number;
    pending: number;
    confirmed: number;
    completed: number;
    cancelled: number;
    revenue: number;
}

const props = defineProps<{
    appointments: Appointment[];
    statistics: Statistics;
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
                { label: 'Start', status: 'in_progress', icon: ArrowPathIcon, color: 'text-indigo-600 bg-indigo-50' },
                { label: 'No Show', status: 'no_show', icon: XCircleIcon, color: 'text-gray-600 bg-gray-50' },
            ];
        case 'in_progress':
            return [
                { label: 'Complete', status: 'completed', icon: CheckCircleIcon, color: 'text-green-600 bg-green-50' },
            ];
        default:
            return [];
    }
};

const now = new Date();
const formattedDate = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
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
                    <h1 class="text-xl font-bold text-gray-900">Today's Schedule</h1>
                    <p class="text-sm text-gray-500">{{ formattedDate }}</p>
                </div>
                <div class="p-2 bg-amber-100 rounded-lg">
                    <SunIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-4 gap-3">
                <div class="bg-white rounded-xl p-3 border border-gray-100 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ statistics.total }}</p>
                    <p class="text-xs text-gray-500">Total</p>
                </div>
                <div class="bg-white rounded-xl p-3 border border-gray-100 text-center">
                    <p class="text-2xl font-bold text-yellow-600">{{ statistics.pending }}</p>
                    <p class="text-xs text-yellow-600">Pending</p>
                </div>
                <div class="bg-white rounded-xl p-3 border border-gray-100 text-center">
                    <p class="text-2xl font-bold text-green-600">{{ statistics.completed }}</p>
                    <p class="text-xs text-green-600">Done</p>
                </div>
                <div class="bg-white rounded-xl p-3 border border-gray-100 text-center">
                    <p class="text-2xl font-bold text-emerald-600">K{{ statistics.revenue.toLocaleString() }}</p>
                    <p class="text-xs text-gray-500">Revenue</p>
                </div>
            </div>

            <!-- Appointments Timeline -->
            <div class="space-y-3">
                <div v-if="appointments.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
                    <CalendarDaysIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                    <p class="text-gray-500">No appointments scheduled for today</p>
                    <Link :href="route('growbiz.appointments.index')" class="mt-3 inline-block text-emerald-600 font-medium">
                        View all appointments
                    </Link>
                </div>

                <div
                    v-for="apt in appointments"
                    :key="apt.id"
                    class="bg-white rounded-xl border border-gray-100 overflow-hidden"
                >
                    <div class="flex">
                        <!-- Time Column -->
                        <div class="w-20 bg-gray-50 p-3 flex flex-col items-center justify-center border-r border-gray-100">
                            <span class="text-lg font-bold text-gray-900">{{ apt.start_time.slice(0, 5) }}</span>
                            <span class="text-xs text-gray-400">{{ apt.end_time.slice(0, 5) }}</span>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 p-3">
                            <Link :href="route('growbiz.appointments.show', apt.id)" class="block">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-2 h-2 rounded-full"
                                                :style="{ backgroundColor: apt.service?.color || '#6b7280' }"
                                            ></div>
                                            <span class="font-semibold text-gray-900">{{ apt.service?.name || 'Service' }}</span>
                                        </div>
                                        <div v-if="apt.customer" class="flex items-center gap-1 mt-1 text-sm text-gray-600">
                                            <UserIcon class="h-4 w-4" aria-hidden="true" />
                                            {{ apt.customer.name }}
                                            <span v-if="apt.customer.phone" class="text-gray-400">· {{ apt.customer.phone }}</span>
                                        </div>
                                        <div v-if="apt.provider" class="text-xs text-gray-400 mt-1">
                                            with {{ apt.provider.name }}
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
                            
                            <!-- Quick Actions -->
                            <div v-if="getStatusActions(apt.status).length > 0" class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
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
            </div>
        </div>
    </GrowBizLayout>
</template>
