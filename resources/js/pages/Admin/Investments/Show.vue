<template>
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Investment Details</h2>
                        <div class="flex space-x-3">
                            <button 
                                v-if="investment.status_badge.text === 'Pending Approval'"
                                @click="approveInvestment" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            >
                                <CheckIcon class="w-4 h-4 mr-2" />
                                Approve Investment
                            </button>
                            <button 
                                v-if="investment.status_badge.text === 'Pending Approval'"
                                @click="rejectInvestment" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                <XMarkIcon class="w-4 h-4 mr-2" />
                                Reject Investment
                            </button>
                            <button 
                                @click="$inertia.visit(route('admin.investments.index'))"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                                Back to List
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Status Banner -->
                <div class="mb-6">
                    <div class="rounded-lg p-4" :class="statusBannerClass">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <component :is="statusIcon" class="h-5 w-5" />
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium">{{ statusMessage }}</h3>
                                <div class="mt-1 text-sm">
                                    {{ statusDescription }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Investment Overview Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-900">Investment Overview</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getStatusBadgeClass(investment.status)">
                                        {{ investment.status }}
                                    </span>
                                </div>
                            </div>
                            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <dl class="space-y-4">
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Principal Amount</dt>
                                                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                                    {{ formatCurrency(investment.amount) }}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Expected Return</dt>
                                                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                                    {{ formatCurrency(investment.expected_return) }}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Investment Date</dt>
                                                <dd class="mt-1 text-lg text-gray-900">
                                                    {{ formatDate(investment.created_at) }}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Lock-in Period End</dt>
                                                <dd class="mt-1 text-lg text-gray-900">
                                                    {{ formatDate(investment.lock_in_period_end) }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 mb-4">Category Information</h4>
                                        <dl class="space-y-4">
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Category Name</dt>
                                                <dd class="mt-1 text-lg text-gray-900">
                                                    {{ investment.category?.name || 'N/A' }}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Risk Level</dt>
                                                <dd class="mt-1 text-lg text-gray-900">
                                                    {{ investment.category?.risk_level || 'N/A' }}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Return Rate</dt>
                                                <dd class="mt-1 text-lg text-gray-900">
                                                    {{ investment.category?.return_rate ? `${investment.category.return_rate}%` : 'N/A' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Metrics -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium text-gray-900">Performance Metrics</h3>
                            </div>
                            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div v-for="(metric, key) in metrics" :key="key" class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-gray-500">{{ metric.label }}</h4>
                                        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ metric.value }}</p>
                                        <p v-if="metric.change" class="mt-1 text-sm" :class="metric.change >= 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ metric.change >= 0 ? '+' : '' }}{{ metric.change }}%
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction History -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-900">Transaction History</h3>
                                    <button class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        View All
                                    </button>
                                </div>
                            </div>
                            <div class="border-t border-gray-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="transaction in investment.profit_shares" :key="transaction.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ formatDate(transaction.created_at) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ transaction.type }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ formatCurrency(transaction.amount) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getTransactionStatusClass(transaction.status)">
                                                        {{ transaction.status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Investor Information -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium text-gray-900">Investor Information</h3>
                            </div>
                            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                                <div class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                                        <dd class="mt-1 text-lg text-gray-900">{{ investment.user?.name || 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-lg text-gray-900">{{ investment.user?.email || 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                                        <dd class="mt-1 text-lg text-gray-900">{{ formatDate(investment.user?.created_at) }}</dd>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                            </div>
                            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                                <div class="space-y-3">
                                    <button 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        @click="downloadStatement"
                                    >
                                        <DocumentArrowDownIcon class="w-4 h-4 mr-2" />
                                        Download Statement
                                    </button>
                                    <button 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        @click="sendMessage"
                                    >
                                        <ChatBubbleLeftIcon class="w-4 h-4 mr-2" />
                                        Send Message
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Investment Timeline -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium text-gray-900">Investment Timeline</h3>
                            </div>
                            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                                <div class="flow-root">
                                    <ul class="-mb-8">
                                        <li v-for="(event, index) in timelineEvents" :key="index">
                                            <div class="relative pb-8">
                                                <span v-if="index !== timelineEvents.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white" :class="event.iconBg">
                                                            <component :is="event.icon" class="h-5 w-5 text-white" aria-hidden="true" />
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-500">{{ event.content }}</p>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            <time :datetime="event.datetime">{{ event.date }}</time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { formatCurrency, formatDate } from '@/utils/formatting';
import {
    CheckIcon,
    XMarkIcon,
    ArrowLeftIcon,
    DocumentArrowDownIcon,
    ChatBubbleLeftIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';
import Swal from 'sweetalert2';

interface Investment {
    id: number;
    amount: number;
    status: string;
    created_at: string;
    category: {
        name: string;
        description: string;
    };
    user: {
        name: string;
        email: string;
        phone: string;
    };
    payment_proof: string;
    expected_return: number;
    next_payment_date: string | null;
    approved_at: string | null;
    approved_by: {
        name: string;
    } | null;
    rejected_at: string | null;
    rejected_by: {
        name: string;
    } | null;
    rejection_reason: string | null;
}

const props = defineProps<{
    investment: Investment;
    metrics: any;
}>();

const processing = ref(false);

const form = useForm({});

const statusBannerClass = computed(() => {
    return {
        'bg-yellow-50': props.investment.status === 'pending',
        'bg-green-50': props.investment.status === 'active',
        'bg-red-50': props.investment.status === 'rejected',
        'bg-gray-50': props.investment.status === 'withdrawn'
    };
});

const statusIcon = computed(() => {
    switch (props.investment.status) {
        case 'pending':
            return ClockIcon;
        case 'active':
            return CheckCircleIcon;
        case 'rejected':
            return XCircleIcon;
        default:
            return ExclamationTriangleIcon;
    }
});

const statusMessage = computed(() => {
    switch (props.investment.status) {
        case 'pending':
            return 'Investment Pending Approval';
        case 'active':
            return 'Investment Active';
        case 'rejected':
            return 'Investment Rejected';
        default:
            return 'Investment Status';
    }
});

const statusDescription = computed(() => {
    switch (props.investment.status) {
        case 'pending':
            return 'This investment is awaiting administrator approval.';
        case 'active':
            return 'This investment is currently active and generating returns.';
        case 'rejected':
            return 'This investment was rejected by the administrator.';
        default:
            return '';
    }
});

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'withdrawn':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getTransactionStatusClass = (status) => {
    switch (status) {
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'failed':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const timelineEvents = computed(() => {
    const events = [
        {
            content: 'Investment created',
            date: formatDate(props.investment.created_at),
            icon: ClockIcon,
            iconBg: 'bg-blue-500'
        }
    ];

    if (props.investment.approved_at) {
        events.push({
            content: 'Investment approved',
            date: formatDate(props.investment.approved_at),
            icon: CheckCircleIcon,
            iconBg: 'bg-green-500'
        });
    }

    if (props.investment.rejected_at) {
        events.push({
            content: 'Investment rejected',
            date: formatDate(props.investment.rejected_at),
            icon: XCircleIcon,
            iconBg: 'bg-red-500'
        });
    }

    return events;
});

const approveInvestment = async () => {
    const result = await Swal.fire({
        title: 'Approve Investment?',
        text: 'Are you sure you want to approve this investment?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, approve it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        processing.value = true;
        try {
            await form.patch(route('admin.investments.approve', props.investment.id));
            Swal.fire({
                title: 'Approved!',
                text: 'The investment has been approved successfully.',
                icon: 'success',
                confirmButtonColor: '#10B981'
            });
        } catch (error) {
            Swal.fire({
                title: 'Error!',
                text: 'There was a problem approving the investment.',
                icon: 'error',
                confirmButtonColor: '#EF4444'
            });
        } finally {
            processing.value = false;
        }
    }
};

const rejectInvestment = async () => {
    const { value: reason } = await Swal.fire({
        title: 'Reject Investment',
        text: 'Please provide a reason for rejection:',
        input: 'textarea',
        inputPlaceholder: 'Enter rejection reason here...',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Reject',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason!';
            }
        }
    });

    if (reason) {
        processing.value = true;
        try {
            await form.patch(route('admin.investments.reject', props.investment.id), {
                data: {
                    reason: reason
                }
            });
            Swal.fire({
                title: 'Rejected!',
                text: 'The investment has been rejected successfully.',
                icon: 'success',
                confirmButtonColor: '#10B981'
            });
        } catch (error) {
            Swal.fire({
                title: 'Error!',
                text: 'There was a problem rejecting the investment.',
                icon: 'error',
                confirmButtonColor: '#EF4444'
            });
        } finally {
            processing.value = false;
        }
    }
};

const downloadStatement = () => {
    // Implement statement download logic
};

const sendMessage = () => {
    // Implement message sending logic
};
</script>
