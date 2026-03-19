<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    ArrowLeftIcon,
    PencilIcon,
    TrashIcon,
    DocumentTextIcon,
    CalendarIcon,
    CurrencyDollarIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Service {
    id: number;
    service_name: string;
    service_type: string;
    client: {
        id: number;
        name: string;
        company_name: string | null;
    };
    billing_model: string;
    unit_price: number;
    quantity: number;
    total_price: number;
    start_date: string | null;
    renewal_date: string | null;
    status: string;
    notes: string | null;
    is_overdue: boolean;
    site: {
        id: number;
        name: string;
    } | null;
    invoices: Array<{
        id: number;
        invoice_number: string;
        invoice_date: string;
        total: number;
        payment_status: string;
    }>;
}

interface Props {
    service: Service;
}

const props = defineProps<Props>();

const getStatusClass = (status: string): string => {
    const classes = {
        active: 'bg-green-100 text-green-800',
        paused: 'bg-amber-100 text-amber-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const getPaymentStatusClass = (status: string): string => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        sent: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        partial: 'bg-amber-100 text-amber-800',
        overdue: 'bg-red-100 text-red-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const deleteService = () => {
    if (confirm(`Are you sure you want to delete the service "${props.service.service_name}"?`)) {
        router.delete(route('growbuilder.services.destroy', props.service.id));
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`Service: ${service.service_name}`" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.services.index')"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                        Back to Services
                    </Link>
                    
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ service.service_name }}</h1>
                            <p class="text-gray-600">{{ service.service_type.replace('_', ' ').toUpperCase() }}</p>
                            <div class="mt-2 flex items-center gap-3">
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                                      :class="getStatusClass(service.status)">
                                    {{ service.status }}
                                </span>
                                <span v-if="service.is_overdue" class="inline-flex items-center gap-1 px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                    <ExclamationTriangleIcon class="h-4 w-4" aria-hidden="true" />
                                    Overdue
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <Link
                                :href="route('growbuilder.services.edit', service.id)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                            >
                                <PencilIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Edit
                            </Link>
                            <button
                                @click="deleteService"
                                class="inline-flex items-center px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50"
                            >
                                <TrashIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Service Details -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Service Details</h2>
                            <dl class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Client</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <Link :href="route('growbuilder.clients.show', service.client.id)" class="text-blue-600 hover:text-blue-700">
                                            {{ service.client.name }}
                                            <span v-if="service.client.company_name" class="text-gray-500">
                                                ({{ service.client.company_name }})
                                            </span>
                                        </Link>
                                    </dd>
                                </div>
                                <div v-if="service.site">
                                    <dt class="text-sm font-medium text-gray-500">Linked Site</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ service.site.name }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Billing Model</dt>
                                    <dd class="mt-1 text-sm text-gray-900 capitalize">{{ service.billing_model.replace('_', ' ') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                              :class="getStatusClass(service.status)">
                                            {{ service.status }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Pricing -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <CurrencyDollarIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                                Pricing
                            </h2>
                            <dl class="grid grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Unit Price</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">ZMW {{ service.unit_price.toFixed(2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ service.quantity }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Price</dt>
                                    <dd class="mt-1 text-lg font-semibold text-green-600">ZMW {{ service.total_price.toFixed(2) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Dates -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <CalendarIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                                Dates
                            </h2>
                            <dl class="grid grid-cols-2 gap-4">
                                <div v-if="service.start_date">
                                    <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ service.start_date }}</dd>
                                </div>
                                <div v-if="service.renewal_date">
                                    <dt class="text-sm font-medium text-gray-500">Renewal Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ service.renewal_date }}
                                        <span v-if="service.is_overdue" class="ml-2 text-red-600 font-semibold">(Overdue)</span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Notes -->
                        <div v-if="service.notes" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Notes</h2>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ service.notes }}</p>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Related Invoices -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <DocumentTextIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                                Related Invoices
                            </h2>
                            
                            <div v-if="service.invoices && service.invoices.length > 0" class="space-y-3">
                                <Link
                                    v-for="invoice in service.invoices"
                                    :key="invoice.id"
                                    :href="route('growbuilder.invoices.show', invoice.id)"
                                    class="block p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition"
                                >
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-900">{{ invoice.invoice_number }}</span>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                              :class="getPaymentStatusClass(invoice.payment_status)">
                                            {{ invoice.payment_status }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>{{ invoice.invoice_date }}</span>
                                        <span class="font-semibold text-gray-900">ZMW {{ invoice.total.toFixed(2) }}</span>
                                    </div>
                                </Link>
                            </div>
                            
                            <div v-else class="text-center py-6">
                                <DocumentTextIcon class="mx-auto h-8 w-8 text-gray-400" aria-hidden="true" />
                                <p class="mt-2 text-sm text-gray-500">No invoices yet</p>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                            <div class="space-y-2">
                                <Link
                                    :href="route('growbuilder.invoices.create', { client_id: service.client.id })"
                                    class="block w-full px-4 py-2 text-center bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                >
                                    Create Invoice
                                </Link>
                                <Link
                                    :href="route('growbuilder.clients.show', service.client.id)"
                                    class="block w-full px-4 py-2 text-center border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                                >
                                    View Client
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
