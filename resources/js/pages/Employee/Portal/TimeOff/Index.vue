<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import TimeOffRequestModal from '@/components/Employee/TimeOffRequestModal.vue';
import { ref, watch } from 'vue';
import {
    PlusIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

interface TimeOffRequest {
    id: number;
    type: string;
    start_date: string;
    end_date: string;
    days_requested: number;
    reason: string;
    status: string;
    reviewed_at: string;
    reviewer?: { full_name: string };
}

interface Balance {
    type: string;
    label: string;
    allowance: number;
    used: number;
    pending: number;
    remaining: number;
    available: number;
}

interface Props {
    requests: TimeOffRequest[];
    balances: Record<string, Balance>;
    filters: {
        status?: string;
        type?: string;
        year?: string;
    };
}

const props = defineProps<Props>();

const selectedStatus = ref(props.filters.status || '');
const selectedType = ref(props.filters.type || '');
const selectedYear = ref(props.filters.year || new Date().getFullYear().toString());

// Modal state
const showRequestModal = ref(false);

const handleRequestSuccess = () => {
    router.reload();
};

const applyFilters = () => {
    router.get(route('employee.portal.time-off.index'), {
        status: selectedStatus.value || undefined,
        type: selectedType.value || undefined,
        year: selectedYear.value,
    }, { preserveState: true });
};

watch([selectedStatus, selectedType, selectedYear], applyFilters);

const getStatusIcon = (status: string) => {
    const icons: Record<string, any> = {
        'pending': ClockIcon,
        'approved': CheckCircleIcon,
        'rejected': XCircleIcon,
        'cancelled': XCircleIcon,
    };
    return icons[status] || ClockIcon;
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        'pending': 'text-amber-500 bg-amber-50',
        'approved': 'text-green-500 bg-green-50',
        'rejected': 'text-red-500 bg-red-50',
        'cancelled': 'text-gray-500 bg-gray-50',
    };
    return colors[status] || 'text-gray-500 bg-gray-50';
};

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        'annual': 'Annual Leave',
        'sick': 'Sick Leave',
        'personal': 'Personal Leave',
        'maternity': 'Maternity Leave',
        'paternity': 'Paternity Leave',
        'bereavement': 'Bereavement Leave',
        'unpaid': 'Unpaid Leave',
        'study': 'Study Leave',
    };
    return labels[type] || type;
};

const cancelRequest = (requestId: number) => {
    if (confirm('Are you sure you want to cancel this request?')) {
        router.delete(route('employee.portal.time-off.cancel', requestId));
    }
};

const years = Array.from({ length: 3 }, (_, i) => new Date().getFullYear() - i);
</script>

<template>
    <Head title="Time Off" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Time Off</h1>
                    <p class="text-gray-500 mt-1">Manage your leave requests and balances</p>
                </div>
                <button @click="showRequestModal = true"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                    Request Time Off
                </button>
            </div>

            <!-- Balances -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(balance, key) in balances" :key="key"
                    class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">{{ balance.label }}</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-bold text-gray-900">{{ balance.remaining }}</span>
                        <span class="text-sm text-gray-400">/ {{ balance.allowance }}</span>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                        <div class="bg-blue-500 h-1.5 rounded-full"
                            :style="{ width: `${(balance.used / balance.allowance) * 100}%` }">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ balance.used }} used</p>
                </div>
            </div>

            <!-- Requests List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-4 border-b border-gray-100 flex flex-wrap items-center gap-4">
                    <h2 class="font-semibold text-gray-900">My Requests</h2>
                    <div class="flex-1"></div>
                    <select v-model="selectedYear"
                        class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option v-for="year in years" :key="year" :value="year.toString()">{{ year }}</option>
                    </select>
                    <select v-model="selectedStatus"
                        class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select v-model="selectedType"
                        class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        <option v-for="(balance, key) in balances" :key="key" :value="key">
                            {{ balance.label }}
                        </option>
                    </select>
                </div>

                <div class="divide-y divide-gray-100">
                    <div v-for="request in requests" :key="request.id" class="p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-4">
                                <div :class="getStatusColor(request.status)"
                                    class="p-2 rounded-lg">
                                    <component :is="getStatusIcon(request.status)" class="h-5 w-5" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ getTypeLabel(request.type) }}</p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ new Date(request.start_date).toLocaleDateString() }}
                                        <span v-if="request.start_date !== request.end_date">
                                            - {{ new Date(request.end_date).toLocaleDateString() }}
                                        </span>
                                        <span class="text-gray-400 ml-2">({{ request.days_requested }} days)</span>
                                    </p>
                                    <p v-if="request.reason" class="text-sm text-gray-500 mt-1">
                                        {{ request.reason }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span :class="[
                                    'px-3 py-1 text-sm font-medium rounded-full',
                                    request.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                                    request.status === 'approved' ? 'bg-green-100 text-green-700' :
                                    request.status === 'rejected' ? 'bg-red-100 text-red-700' :
                                    'bg-gray-100 text-gray-700'
                                ]">
                                    {{ request.status }}
                                </span>
                                <button v-if="request.status === 'pending'"
                                    @click="cancelRequest(request.id)"
                                    class="block mt-2 text-sm text-red-600 hover:text-red-700">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="requests.length === 0" class="p-12 text-center">
                        <CalendarDaysIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                        <h3 class="text-lg font-medium text-gray-900">No requests found</h3>
                        <p class="text-gray-500 mt-1">You haven't made any time off requests yet.</p>
                        <button @click="showRequestModal = true"
                            class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                            Request Time Off
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Off Request Modal -->
        <TimeOffRequestModal 
            :show="showRequestModal"
            :balances="balances"
            @close="showRequestModal = false"
            @success="handleRequestSuccess"
        />
    </EmployeePortalLayout>
</template>
