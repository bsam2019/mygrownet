<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Services</h1>
                    <p class="mt-2 text-sm text-gray-600">Manage client services and subscriptions</p>
                </div>
                <Link :href="route('growbuilder.services.create')"
                      class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                    Add Service
                </Link>
            </div>

            <!-- Navigation Tabs -->
            <div class="border-b border-gray-200 mb-8">
                <nav class="-mb-px flex space-x-8">
                    <Link 
                        :href="route('growbuilder.dashboard')"
                        class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm"
                    >
                        <GlobeAltIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                        Sites
                    </Link>
                    <Link 
                        :href="route('growbuilder.clients.index')"
                        class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm"
                    >
                        <UsersIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                        Clients
                    </Link>
                    <Link 
                        :href="route('growbuilder.services.index')"
                        class="py-2 px-1 border-b-2 border-blue-500 text-blue-600 font-medium text-sm"
                    >
                        <CurrencyDollarIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                        Services
                    </Link>
                    <Link 
                        :href="route('growbuilder.invoices.index')"
                        class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm"
                    >
                        <DocumentTextIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                        Invoices
                    </Link>
                    <Link 
                        :href="route('growbuilder.agency.dashboard')"
                        class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm"
                    >
                        <Cog6ToothIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                        Agency
                    </Link>
                </nav>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Total Services</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Active</div>
                    <div class="mt-2 text-3xl font-bold text-green-600">{{ stats.active }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Due for Renewal</div>
                    <div class="mt-2 text-3xl font-bold text-amber-600">{{ stats.due_for_renewal }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Overdue</div>
                    <div class="mt-2 text-3xl font-bold text-red-600">{{ stats.overdue }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Client Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Client</label>
                            <select
                                v-model="clientFilter"
                                @change="applyFilters"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">All Clients</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">
                                    {{ client.client_name }}{{ client.company_name ? ` (${client.company_name})` : '' }}
                                </option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select
                                v-model="statusFilter"
                                @change="applyFilters"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="all">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="paused">Paused</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <!-- Service Type Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Service Type</label>
                            <select
                                v-model="serviceTypeFilter"
                                @change="applyFilters"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="all">All Types</option>
                                <option value="website">Website</option>
                                <option value="hosting">Hosting</option>
                                <option value="domain_management">Domain Management</option>
                                <option value="seo">SEO</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="ads">Ads</option>
                                <option value="redesign">Redesign</option>
                                <option value="content_updates">Content Updates</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div v-if="services.data.length > 0">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Billing</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Renewal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="service in services.data" :key="service.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ service.service_name }}</div>
                                    <div class="text-sm text-gray-500 capitalize">{{ service.service_type.replace('_', ' ') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ service.client.name }}</div>
                                    <div v-if="service.client.company_name" class="text-sm text-gray-500">
                                        {{ service.client.company_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-900 capitalize">{{ service.billing_model }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        K{{ service.total_price.toFixed(2) }}
                                    </div>
                                    <div v-if="service.quantity > 1" class="text-xs text-gray-500">
                                        {{ service.quantity }} × K{{ service.unit_price.toFixed(2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="service.renewal_date" class="text-sm">
                                        <span :class="service.is_overdue ? 'text-red-600 font-medium' : 'text-gray-900'">
                                            {{ service.renewal_date }}
                                        </span>
                                        <div v-if="service.is_overdue" class="text-xs text-red-600">Overdue</div>
                                    </div>
                                    <span v-else class="text-sm text-gray-400">N/A</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="getStatusClass(service.status)">
                                        {{ service.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <Link :href="route('growbuilder.services.show', service.id)"
                                          class="text-blue-600 hover:text-blue-900 mr-3">
                                        View
                                    </Link>
                                    <Link :href="route('growbuilder.services.edit', service.id)"
                                          class="text-gray-600 hover:text-gray-900">
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ services.from }} to {{ services.to }} of {{ services.total }} services
                            </div>
                            <div class="flex gap-2">
                                <template v-for="link in services.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="[
                                            'px-3 py-2 text-sm rounded-lg',
                                            link.active 
                                                ? 'bg-blue-600 text-white' 
                                                : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                        ]"
                                        v-html="link.label"
                                    />
                                    <button
                                        v-else
                                        :class="[
                                            'px-3 py-2 text-sm rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed'
                                        ]"
                                        disabled
                                        v-html="link.label"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <CurrencyDollarIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No services found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a service for your clients.</p>
                    <div class="mt-6">
                        <Link :href="route('growbuilder.services.create')"
                              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                            Add Service
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { 
    PlusIcon, 
    CurrencyDollarIcon,
    GlobeAltIcon,
    UsersIcon,
    DocumentTextIcon,
    Cog6ToothIcon,
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
    renewal_date: string | null;
    status: string;
    is_overdue: boolean;
    site: {
        id: number;
        name: string;
    } | null;
}

interface Props {
    services: {
        data: Service[];
        from: number;
        to: number;
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    clients: Array<{
        id: number;
        client_name: string;
        company_name: string | null;
    }>;
    filters: {
        client_id: number | null;
        status: string;
        service_type: string;
    };
    stats: {
        total: number;
        active: number;
        due_for_renewal: number;
        overdue: number;
    };
}

const props = defineProps<Props>();

const clientFilter = ref(props.filters.client_id || '');
const statusFilter = ref(props.filters.status || 'all');
const serviceTypeFilter = ref(props.filters.service_type || 'all');

function getStatusClass(status: string): string {
    const classes = {
        active: 'bg-green-100 text-green-800',
        paused: 'bg-amber-100 text-amber-800',
        cancelled: 'bg-gray-100 text-gray-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
}

function applyFilters() {
    router.get(route('growbuilder.services.index'), {
        client_id: clientFilter.value,
        status: statusFilter.value,
        service_type: serviceTypeFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>
