<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { PaperAirplaneIcon, CheckIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import CMSLayout from '@/Layouts/CMSLayout.vue';

defineOptions({
  layout: CMSLayout
})

interface QuotationItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    tax_rate: number;
    discount_rate: number;
    line_total: number;
}

interface Quotation {
    id: number;
    quotation_number: string;
    quotation_date: string;
    expiry_date: string | null;
    status: string;
    total_amount: number;
    tax_amount: number;
    discount_amount: number;
    notes: string | null;
    terms: string | null;
    customer: {
        id: number;
        name: string;
        email: string;
        phone: string;
        address: string | null;
    };
    items: QuotationItem[];
    createdBy: {
        user: {
            name: string;
        };
    };
    convertedToJob: {
        id: number;
        job_number: string;
    } | null;
}

interface Props {
    quotation: Quotation;
}

const props = defineProps<Props>();

const sendQuotation = () => {
    if (confirm('Mark this quotation as sent?')) {
        router.post(route('cms.quotations.send', props.quotation.id), {}, {
            preserveScroll: true,
        });
    }
};

const convertToJob = () => {
    if (confirm('Convert this quotation to a job?')) {
        router.post(route('cms.quotations.convertToJob', props.quotation.id), {}, {
            preserveScroll: true,
        });
    }
};

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const calculateSubtotal = () => {
    return props.quotation.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'gray',
        sent: 'blue',
        accepted: 'green',
        rejected: 'red',
        expired: 'amber',
        converted: 'purple',
    };
    return colors[status] || 'gray';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        draft: 'Draft',
        sent: 'Sent',
        accepted: 'Accepted',
        rejected: 'Rejected',
        expired: 'Expired',
        converted: 'Converted',
    };
    return labels[status] || status;
};
</script>

<template>
    <Head :title="`Quotation ${quotation.quotation_number} - CMS`" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ quotation.quotation_number }}</h1>
                <p class="mt-1 text-sm text-gray-600">Quotation Details</p>
            </div>
            <div class="flex gap-3">
                <button
                    v-if="quotation.status === 'draft'"
                    @click="sendQuotation"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    <PaperAirplaneIcon class="h-5 w-5" aria-hidden="true" />
                    Send Quotation
                </button>
                <button
                    v-if="quotation.status === 'accepted' && !quotation.convertedToJob"
                    @click="convertToJob"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                >
                    <ArrowPathIcon class="h-5 w-5" aria-hidden="true" />
                    Convert to Job
                </button>
                <a
                    :href="route('cms.quotations.index')"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Back to List
                </a>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
            <span
                :class="[
                    'inline-flex rounded-full px-3 py-1 text-sm font-semibold',
                    getStatusColor(quotation.status) === 'gray' && 'bg-gray-100 text-gray-800',
                    getStatusColor(quotation.status) === 'blue' && 'bg-blue-100 text-blue-800',
                    getStatusColor(quotation.status) === 'green' && 'bg-green-100 text-green-800',
                    getStatusColor(quotation.status) === 'red' && 'bg-red-100 text-red-800',
                    getStatusColor(quotation.status) === 'amber' && 'bg-amber-100 text-amber-800',
                    getStatusColor(quotation.status) === 'purple' && 'bg-purple-100 text-purple-800',
                ]"
            >
                {{ getStatusLabel(quotation.status) }}
            </span>
            <span v-if="quotation.convertedToJob" class="ml-3 text-sm text-gray-600">
                Converted to Job: 
                <a :href="route('cms.jobs.show', quotation.convertedToJob.id)" class="text-blue-600 hover:text-blue-800">
                    {{ quotation.convertedToJob.job_number }}
                </a>
            </span>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer Info -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Customer Information</h2>
                    <div class="space-y-2">
                        <div>
                            <span class="text-sm font-medium text-gray-600">Name:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ quotation.customer.name }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Email:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ quotation.customer.email }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Phone:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ quotation.customer.phone }}</span>
                        </div>
                        <div v-if="quotation.customer.address">
                            <span class="text-sm font-medium text-gray-600">Address:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ quotation.customer.address }}</span>
                        </div>
                    </div>
                </div>

                <!-- Line Items -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Line Items</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Description</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Qty</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Price</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Discount</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Tax</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="item in quotation.items" :key="item.id">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ item.description }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ item.quantity }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ item.discount_rate }}%</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600">{{ item.tax_rate }}%</td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ formatCurrency(item.line_total) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="mt-6 border-t pt-4">
                        <div class="flex justify-end">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium">{{ formatCurrency(calculateSubtotal()) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Discount:</span>
                                    <span class="font-medium text-red-600">-{{ formatCurrency(quotation.discount_amount) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tax:</span>
                                    <span class="font-medium">{{ formatCurrency(quotation.tax_amount) }}</span>
                                </div>
                                <div class="flex justify-between border-t pt-2 text-lg font-bold">
                                    <span>Total:</span>
                                    <span>{{ formatCurrency(quotation.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes & Terms -->
                <div v-if="quotation.notes || quotation.terms" class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Additional Information</h2>
                    <div class="space-y-4">
                        <div v-if="quotation.notes">
                            <h3 class="text-sm font-medium text-gray-700">Notes</h3>
                            <p class="mt-1 text-sm text-gray-600 whitespace-pre-wrap">{{ quotation.notes }}</p>
                        </div>
                        <div v-if="quotation.terms">
                            <h3 class="text-sm font-medium text-gray-700">Terms & Conditions</h3>
                            <p class="mt-1 text-sm text-gray-600 whitespace-pre-wrap">{{ quotation.terms }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quotation Details -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Details</h2>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-600">Quotation Date:</span>
                            <div class="mt-1 text-sm text-gray-900">{{ formatDate(quotation.quotation_date) }}</div>
                        </div>
                        <div v-if="quotation.expiry_date">
                            <span class="text-sm font-medium text-gray-600">Expiry Date:</span>
                            <div class="mt-1 text-sm text-gray-900">{{ formatDate(quotation.expiry_date) }}</div>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Created By:</span>
                            <div class="mt-1 text-sm text-gray-900">{{ quotation.createdBy.user.name }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
