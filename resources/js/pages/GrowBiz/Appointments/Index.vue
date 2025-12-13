<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    CalendarDaysIcon,
    PlusIcon,
    FunnelIcon,
    MagnifyingGlassIcon,
    ClockIcon,
    UserIcon,
    XMarkIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowPathIcon,
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
    is_upcoming: boolean;
}

interface Statistics {
    total: number;
    pending: number;
    confirmed: number;
    completed: number;
    cancelled: number;
    today_count: number;
    upcoming_count: number;
    revenue: number;
}

const props = defineProps<{
    appointments: Appointment[];
    services: Service[];
    providers: Provider[];
    statistics: Statistics;
    filters: Record<string, string>;
}>();

const search = ref(props.filters.search || '');
const showFilters = ref(false);
const showNewModal = ref(false);
const selectedStatus = ref(props.filters.status || '');
const selectedService = ref(props.filters.service_id || '');
const selectedProvider = ref(props.filters.provider_id || '');

// New appointment form
const newForm = ref({
    service_id: '',
    provider_id: '',
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    appointment_date: new Date().toISOString().split('T')[0],
    start_time: '09:00',
    customer_notes: '',
    booking_source: 'phone',
});

const isSubmitting = ref(false);

const filteredAppointments = computed(() => {
    let result = props.appointments;
    
    if (search.value) {
        const s = search.value.toLowerCase();
        result = result.filter(a => 
            a.booking_reference.toLowerCase().includes(s) ||
            a.customer?.name.toLowerCase().includes(s) ||
            a.customer?.phone?.includes(s) ||
            a.service?.name.toLowerCase().includes(s)
        );
    }
    
    return result;
});

const applyFilters = () => {
    router.get(route('growbiz.appointments.index'), {
        status: selectedStatus.value || undefined,
        service_id: selectedService.value || undefined,
        provider_id: selectedProvider.value || undefined,
        search: search.value || undefined,
    }, { preserveState: true });
};

const clearFilters = () => {
    selectedStatus.value = '';
    selectedService.value = '';
    selectedProvider.value = '';
    search.value = '';
    router.get(route('growbiz.appointments.index'));
};

const createAppointment = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    router.post(route('growbiz.appointments.store'), newForm.value, {
        onSuccess: () => {
            showNewModal.value = false;
            resetForm();
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const resetForm = () => {
    newForm.value = {
        service_id: '',
        provider_id: '',
        customer_name: '',
        customer_phone: '',
        customer_email: '',
        appointment_date: new Date().toISOString().split('T')[0],
        start_time: '09:00',
        customer_notes: '',
        booking_source: 'phone',
    };
};

const updateStatus = (id: number, status: string) => {
    router.patch(route('growbiz.appointments.status', id), { status }, {
        preserveScroll: true,
    });
};

const getStatusActions = (status: string) => {
    switch (status) {
        case 'pending':
            return [
                { label: 'Confirm', status: 'confirmed', icon: CheckCircleIcon, color: 'text-blue-600' },
                { label: 'Cancel', status: 'cancelled', icon: XCircleIcon, color: 'text-red-600' },
            ];
        case 'confirmed':
            return [
                { label: 'Start', status: 'in_progress', icon: ArrowPathIcon, color: 'text-indigo-600' },
                { label: 'No Show', status: 'no_show', icon: XCircleIcon, color: 'text-gray-600' },
                { label: 'Cancel', status: 'cancelled', icon: XCircleIcon, color: 'text-red-600' },
            ];
        case 'in_progress':
            return [
                { label: 'Complete', status: 'completed', icon: CheckCircleIcon, color: 'text-green-600' },
            ];
        default:
            return [];
    }
};
</script>

<template>
    <GrowBizLayout>
        <div class="p-4 space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Appointments</h1>
                    <p class="text-sm text-gray-500">{{ statistics.today_count }} today · {{ statistics.upcoming_count }} upcoming</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('growbiz.appointments.calendar')"
                        class="p-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200"
                        aria-label="Calendar view"
                    >
                        <CalendarDaysIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    <button
                        @click="showNewModal = true"
                        class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
                    >
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        <span class="hidden sm:inline">Book</span>
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-4 gap-3">
                <div class="bg-white rounded-xl p-3 border border-gray-100">
                    <p class="text-2xl font-bold text-gray-900">{{ statistics.pending }}</p>
                    <p class="text-xs text-yellow-600">Pending</p>
                </div>
                <div class="bg-white rounded-xl p-3 border border-gray-100">
                    <p class="text-2xl font-bold text-gray-900">{{ statistics.confirmed }}</p>
                    <p class="text-xs text-blue-600">Confirmed</p>
                </div>
                <div class="bg-white rounded-xl p-3 border border-gray-100">
                    <p class="text-2xl font-bold text-gray-900">{{ statistics.completed }}</p>
                    <p class="text-xs text-green-600">Completed</p>
                </div>
                <div class="bg-white rounded-xl p-3 border border-gray-100">
                    <p class="text-2xl font-bold text-gray-900">K{{ statistics.revenue.toLocaleString() }}</p>
                    <p class="text-xs text-gray-500">Revenue</p>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="flex items-center gap-2">
                <div class="flex-1 relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search appointments..."
                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <button
                    @click="showFilters = !showFilters"
                    class="p-2.5 bg-white border border-gray-200 rounded-xl hover:bg-gray-50"
                    :class="{ 'bg-emerald-50 border-emerald-200': showFilters }"
                    aria-label="Toggle filters"
                >
                    <FunnelIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
            </div>

            <!-- Filter Panel -->
            <div v-if="showFilters" class="bg-white rounded-xl p-4 border border-gray-200 space-y-3">
                <div class="grid grid-cols-3 gap-3">
                    <select v-model="selectedStatus" class="text-sm border-gray-200 rounded-lg">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select v-model="selectedService" class="text-sm border-gray-200 rounded-lg">
                        <option value="">All Services</option>
                        <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <select v-model="selectedProvider" class="text-sm border-gray-200 rounded-lg">
                        <option value="">All Staff</option>
                        <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="clearFilters" class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900">Clear</button>
                    <button @click="applyFilters" class="px-3 py-1.5 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Apply</button>
                </div>
            </div>

            <!-- Appointments List -->
            <div class="space-y-3">
                <div v-if="filteredAppointments.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
                    <CalendarDaysIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                    <p class="text-gray-500">No appointments found</p>
                    <button @click="showNewModal = true" class="mt-3 text-emerald-600 font-medium">Book your first appointment</button>
                </div>

                <Link
                    v-for="apt in filteredAppointments"
                    :key="apt.id"
                    :href="route('growbiz.appointments.show', apt.id)"
                    class="block bg-white rounded-xl p-4 border border-gray-100 hover:border-emerald-200 hover:shadow-sm transition-all"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <!-- Service Color Indicator -->
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
                                        <CalendarDaysIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ apt.formatted_date }}
                                    </span>
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
                    
                    <!-- Quick Actions -->
                    <div v-if="getStatusActions(apt.status).length > 0" class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100" @click.prevent>
                        <button
                            v-for="action in getStatusActions(apt.status)"
                            :key="action.status"
                            @click="updateStatus(apt.id, action.status)"
                            class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-50 hover:bg-gray-100"
                            :class="action.color"
                        >
                            <component :is="action.icon" class="h-4 w-4" aria-hidden="true" />
                            {{ action.label }}
                        </button>
                    </div>
                </Link>
            </div>
        </div>

        <!-- New Appointment Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showNewModal" class="fixed inset-0 z-50 bg-black/50" @click="showNewModal = false"></div>
            </Transition>
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-from-class="translate-y-0"
                leave-to-class="translate-y-full"
            >
                <div v-if="showNewModal" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl max-h-[90vh] overflow-y-auto safe-area-bottom">
                    <div class="sticky top-0 bg-white border-b border-gray-100 px-4 py-3 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">New Appointment</h2>
                        <button @click="showNewModal = false" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                    <form @submit.prevent="createAppointment" class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service *</label>
                            <select v-model="newForm.service_id" required class="w-full border-gray-200 rounded-lg">
                                <option value="">Select service</option>
                                <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Staff (Optional)</label>
                            <select v-model="newForm.provider_id" class="w-full border-gray-200 rounded-lg">
                                <option value="">Any available</option>
                                <option v-for="p in providers" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                                <input v-model="newForm.appointment_date" type="date" required class="w-full border-gray-200 rounded-lg" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Time *</label>
                                <input v-model="newForm.start_time" type="time" required class="w-full border-gray-200 rounded-lg" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
                            <input v-model="newForm.customer_name" type="text" required class="w-full border-gray-200 rounded-lg" placeholder="John Doe" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input v-model="newForm.customer_phone" type="tel" class="w-full border-gray-200 rounded-lg" placeholder="+260..." />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input v-model="newForm.customer_email" type="email" class="w-full border-gray-200 rounded-lg" placeholder="email@example.com" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="newForm.customer_notes" rows="2" class="w-full border-gray-200 rounded-lg" placeholder="Any special requests..."></textarea>
                        </div>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="w-full py-3 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Booking...' : 'Book Appointment' }}
                        </button>
                    </form>
                </div>
            </Transition>
        </Teleport>
    </GrowBizLayout>
</template>
