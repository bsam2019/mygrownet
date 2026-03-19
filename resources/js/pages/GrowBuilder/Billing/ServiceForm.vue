<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, watch } from 'vue';
import {
    ArrowLeftIcon,
    BuildingOfficeIcon,
    CurrencyDollarIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';

interface Client {
    id: number;
    client_name: string;
    company_name: string | null;
    sites: Array<{
        id: number;
        name: string;
    }>;
}

interface Service {
    id: number;
    client_id: number;
    service_type: string;
    service_name: string;
    linked_site_id: number | null;
    billing_model: string;
    unit_price: number;
    quantity: number;
    start_date: string | null;
    renewal_date: string | null;
    status: string;
    notes: string | null;
}

interface Props {
    clients: Client[];
    service: Service | null;
    clientId?: number;
}

const props = defineProps<Props>();

const form = useForm({
    client_id: props.service?.client_id || props.clientId || null,
    service_type: props.service?.service_type || 'website',
    service_name: props.service?.service_name || '',
    linked_site_id: props.service?.linked_site_id || null,
    billing_model: props.service?.billing_model || 'monthly',
    unit_price: props.service?.unit_price || 0,
    quantity: props.service?.quantity || 1,
    start_date: props.service?.start_date || '',
    renewal_date: props.service?.renewal_date || '',
    status: props.service?.status || 'active',
    notes: props.service?.notes || '',
});

const selectedClient = computed(() => {
    return props.clients.find(c => c.id === form.client_id);
});

const totalPrice = computed(() => {
    return form.unit_price * form.quantity;
});

const serviceTypes = [
    { value: 'website', label: 'Website Development' },
    { value: 'hosting', label: 'Hosting' },
    { value: 'domain_management', label: 'Domain Management' },
    { value: 'seo', label: 'SEO Services' },
    { value: 'maintenance', label: 'Maintenance' },
    { value: 'ads', label: 'Advertising' },
    { value: 'redesign', label: 'Redesign' },
    { value: 'content_updates', label: 'Content Updates' },
    { value: 'other', label: 'Other' },
];

const billingModels = [
    { value: 'monthly', label: 'Monthly' },
    { value: 'quarterly', label: 'Quarterly' },
    { value: 'annual', label: 'Annual' },
    { value: 'one_time', label: 'One-Time' },
];

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'paused', label: 'Paused' },
    { value: 'cancelled', label: 'Cancelled' },
];

const submit = () => {
    if (props.service) {
        form.put(route('growbuilder.services.update', props.service.id));
    } else {
        form.post(route('growbuilder.services.store'));
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="service ? 'Edit Service' : 'Create Service'" />

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.services.index')"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                        Back to Services
                    </Link>
                    
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ service ? 'Edit Service' : 'Create New Service' }}
                    </h1>
                    <p class="text-gray-600">
                        {{ service ? 'Update service details' : 'Add a new service for a client' }}
                    </p>
                </div>

                <!-- Form -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Client Selection -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <BuildingOfficeIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                                Client Information
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Client *
                                    </label>
                                    <select
                                        id="client_id"
                                        v-model="form.client_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option :value="null">Select a client</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">
                                            {{ client.client_name }}{{ client.company_name ? ` (${client.company_name})` : '' }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.client_id }}
                                    </div>
                                </div>

                                <div v-if="selectedClient && selectedClient.sites.length > 0">
                                    <label for="linked_site_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Linked Site (Optional)
                                    </label>
                                    <select
                                        id="linked_site_id"
                                        v-model="form.linked_site_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option :value="null">No site linked</option>
                                        <option v-for="site in selectedClient.sites" :key="site.id" :value="site.id">
                                            {{ site.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Service Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <CurrencyDollarIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                                Service Details
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="service_type" class="block text-sm font-medium text-gray-700 mb-2">
                                        Service Type *
                                    </label>
                                    <select
                                        id="service_type"
                                        v-model="form.service_type"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option v-for="type in serviceTypes" :key="type.value" :value="type.value">
                                            {{ type.label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.service_type" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.service_type }}
                                    </div>
                                </div>

                                <div>
                                    <label for="service_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Service Name *
                                    </label>
                                    <input
                                        id="service_name"
                                        v-model="form.service_name"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="e.g., Monthly Website Maintenance"
                                    />
                                    <div v-if="form.errors.service_name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.service_name }}
                                    </div>
                                </div>

                                <div>
                                    <label for="billing_model" class="block text-sm font-medium text-gray-700 mb-2">
                                        Billing Model *
                                    </label>
                                    <select
                                        id="billing_model"
                                        v-model="form.billing_model"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option v-for="model in billingModels" :key="model.value" :value="model.value">
                                            {{ model.label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.billing_model" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.billing_model }}
                                    </div>
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                        Status *
                                    </label>
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                                            {{ status.label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.status }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="unit_price" class="block text-sm font-medium text-gray-700 mb-2">
                                        Unit Price (ZMW) *
                                    </label>
                                    <input
                                        id="unit_price"
                                        v-model.number="form.unit_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <div v-if="form.errors.unit_price" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.unit_price }}
                                    </div>
                                </div>

                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                        Quantity *
                                    </label>
                                    <input
                                        id="quantity"
                                        v-model.number="form.quantity"
                                        type="number"
                                        min="1"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <div v-if="form.errors.quantity" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.quantity }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Total Price
                                    </label>
                                    <div class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 font-semibold">
                                        ZMW {{ totalPrice.toFixed(2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <CalendarIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                                Dates
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Start Date
                                    </label>
                                    <input
                                        id="start_date"
                                        v-model="form.start_date"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <div v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.start_date }}
                                    </div>
                                </div>

                                <div>
                                    <label for="renewal_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Renewal Date
                                    </label>
                                    <input
                                        id="renewal_date"
                                        v-model="form.renewal_date"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                    <div v-if="form.errors.renewal_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.renewal_date }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes
                            </label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Any additional notes about this service..."
                            ></textarea>
                            <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">
                                {{ form.errors.notes }}
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <Link
                                :href="route('growbuilder.services.index')"
                                class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                <span v-if="form.processing">{{ service ? 'Updating...' : 'Creating...' }}</span>
                                <span v-else>{{ service ? 'Update Service' : 'Create Service' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
