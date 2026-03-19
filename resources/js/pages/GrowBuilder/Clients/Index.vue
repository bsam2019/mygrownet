<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Clients</h1>
                    <p class="mt-2 text-sm text-gray-600">Manage your agency's clients</p>
                </div>
                <Link :href="route('growbuilder.clients.create')"
                      class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                    Add Client
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
                        class="py-2 px-1 border-b-2 border-blue-500 text-blue-600 font-medium text-sm"
                    >
                        <UsersIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                        Clients
                    </Link>
                    <Link 
                        :href="route('growbuilder.services.index')"
                        class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm"
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
                        <CogIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                        Agency
                    </Link>
                </nav>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Total Clients</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Active</div>
                    <div class="mt-2 text-3xl font-bold text-green-600">{{ stats.active }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Leads</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600">{{ stats.leads }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Suspended</div>
                    <div class="mt-2 text-3xl font-bold text-red-600">{{ stats.suspended }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search clients..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                @input="debouncedSearch"
                            />
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <select
                                v-model="statusFilter"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                @change="applyFilters"
                            >
                                <option value="all">All Statuses</option>
                                <option value="lead">Lead</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>

                        <!-- Type Filter -->
                        <div>
                            <select
                                v-model="typeFilter"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                @change="applyFilters"
                            >
                                <option value="all">All Types</option>
                                <option value="individual">Individual</option>
                                <option value="business">Business</option>
                                <option value="institution">Institution</option>
                                <option value="church">Church</option>
                                <option value="school">School</option>
                                <option value="ngo">NGO</option>
                                <option value="government">Government</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tag Filter -->
                    <div v-if="available_tags.length > 0" class="mt-4 flex flex-wrap gap-2">
                        <span class="text-sm text-gray-600">Filter by tag:</span>
                        <button
                            v-for="tag in available_tags"
                            :key="tag.id"
                            @click="toggleTagFilter(tag.id)"
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium transition-colors"
                            :class="tagFilter === tag.id 
                                ? 'bg-blue-100 text-blue-800 ring-2 ring-blue-500' 
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        >
                            <span class="w-2 h-2 rounded-full mr-2" :style="{ backgroundColor: tag.color }"></span>
                            {{ tag.name }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Client List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div v-if="clients.data.length > 0">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Client
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Contact
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sites
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tags
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="client in clients.data" :key="client.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ client.client_name }}</div>
                                        <div v-if="client.company_name" class="text-sm text-gray-500">{{ client.company_name }}</div>
                                        <div class="text-xs text-gray-400">{{ client.client_code }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900 capitalize">{{ client.client_type }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="client.primary_contact">
                                        <div class="text-sm text-gray-900">{{ client.primary_contact.name }}</div>
                                        <div class="text-sm text-gray-500">{{ client.primary_contact.email }}</div>
                                    </div>
                                    <div v-else class="text-sm text-gray-400">No contact</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ client.sites_count }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="getStatusClass(client.status)">
                                        {{ client.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="tag in client.tags"
                                            :key="tag.id"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                            :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                                        >
                                            {{ tag.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="route('growbuilder.clients.show', client.id)"
                                          class="text-blue-600 hover:text-blue-900 mr-3">
                                        View
                                    </Link>
                                    <Link :href="route('growbuilder.clients.edit', client.id)"
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
                                Showing {{ clients.from }} to {{ clients.to }} of {{ clients.total }} clients
                            </div>
                            <div class="flex gap-2">
                                <template v-for="link in clients.links" :key="link.label">
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
                                            'px-3 py-2 text-sm rounded-lg',
                                            link.active 
                                                ? 'bg-blue-600 text-white' 
                                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
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
                    <UsersIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No clients found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding your first client.</p>
                    <div class="mt-6">
                        <Link :href="route('growbuilder.clients.create')"
                              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                            Add Client
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { PlusIcon, UsersIcon, GlobeAltIcon, CogIcon, CurrencyDollarIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
import { debounce } from 'lodash';

interface Client {
    id: number;
    client_code: string;
    client_name: string;
    company_name: string | null;
    email: string;
    phone: string;
    client_type: string;
    status: string;
    onboarding_status: string;
    sites_count: number;
    primary_contact: {
        name: string;
        email: string;
    } | null;
    tags: Array<{
        id: number;
        name: string;
        color: string;
    }>;
    created_at: string;
}

interface Props {
    clients: {
        data: Client[];
        from: number;
        to: number;
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    filters: {
        status: string;
        type: string;
        tag: number | null;
        search: string;
        sort_by: string;
        sort_order: string;
    };
    available_tags: Array<{
        id: number;
        name: string;
        color: string;
    }>;
    stats: {
        total: number;
        active: number;
        leads: number;
        suspended: number;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const typeFilter = ref(props.filters.type || 'all');
const tagFilter = ref(props.filters.tag || null);

function getStatusClass(status: string): string {
    const classes = {
        lead: 'bg-blue-100 text-blue-800',
        active: 'bg-green-100 text-green-800',
        suspended: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
        archived: 'bg-gray-100 text-gray-600',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
}

function applyFilters() {
    router.get(route('growbuilder.clients.index'), {
        status: statusFilter.value,
        type: typeFilter.value,
        tag: tagFilter.value,
        search: searchQuery.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

const debouncedSearch = debounce(() => {
    applyFilters();
}, 300);

function toggleTagFilter(tagId: number) {
    tagFilter.value = tagFilter.value === tagId ? null : tagId;
    applyFilters();
}
</script>
