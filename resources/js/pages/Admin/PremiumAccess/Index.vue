<template>
    <AdminLayout title="Premium Template Access Management">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Premium Template Access Management</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Grant or revoke premium template access for GrowBuilder users
                    </p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Total Users</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_users }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Premium Access Granted</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.granted_access }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input
                                id="search"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by name or email..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @input="debouncedSearch"
                            />
                        </div>

                        <div>
                            <label for="filter" class="block text-sm font-medium text-gray-700 mb-1">Filter</label>
                            <select
                                id="filter"
                                v-model="filterType"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @change="applyFilters"
                            >
                                <option value="all">All Users</option>
                                <option value="granted">Premium Access Granted</option>
                                <option value="not_granted">No Premium Access</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button
                                v-if="selectedUsers.length > 0"
                                @click="showBulkModal = true"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                            >
                                Bulk Actions ({{ selectedUsers.length }})
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="allSelected"
                                        @change="toggleSelectAll"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Granted By
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <input
                                        type="checkbox"
                                        :value="user.id"
                                        v-model="selectedUsers"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        v-if="user.premium_template_tier"
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            user.premium_template_tier === 'starter' ? 'bg-blue-100 text-blue-800' :
                                            user.premium_template_tier === 'business' ? 'bg-green-100 text-green-800' :
                                            'bg-purple-100 text-purple-800'
                                        ]"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        {{ user.premium_template_tier === 'starter' ? 'Starter (K120)' :
                                           user.premium_template_tier === 'business' ? 'Business (K350)' :
                                           'Agency (K900)' }}
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                    >
                                        No Access
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div v-if="user.premium_template_tier">
                                        <div>{{ user.premium_access_granted_by?.name || 'N/A' }}</div>
                                        <div class="text-xs text-gray-400">
                                            {{ formatDate(user.premium_access_granted_at) }}
                                        </div>
                                    </div>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <button
                                        v-if="!user.premium_template_tier"
                                        @click="openGrantModal(user)"
                                        class="text-green-600 hover:text-green-900 mr-3"
                                    >
                                        Grant Access
                                    </button>
                                    <button
                                        v-else
                                        @click="revokeAccess(user)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Revoke Access
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="users.links && users.links.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
                            </div>
                            <div class="flex gap-2">
                                <component
                                    :is="link.url ? Link : 'span'"
                                    v-for="(link, index) in users.links"
                                    :key="index"
                                    :href="link.url || undefined"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-md',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : link.url
                                            ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 cursor-pointer'
                                            : 'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed'
                                    ]"
                                    :preserve-state="true"
                                    :preserve-scroll="true"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grant Access Modal -->
        <Modal :show="showGrantModal" @close="showGrantModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Grant Premium Template Access
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Grant premium template access to <strong>{{ selectedUser?.name }}</strong>
                </p>
                
                <div class="mb-4">
                    <label for="tier" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Tier <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="tier"
                        v-model="grantForm.tier"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="starter">Starter - K120/month (Basic premium templates)</option>
                        <option value="business">Business - K350/month (All premium templates + features)</option>
                        <option value="agency">Agency - K900/month (Full access + white-label)</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Choose the tier level that matches the features you want to grant
                    </p>
                </div>
                
                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (Optional)
                    </label>
                    <textarea
                        id="notes"
                        v-model="grantForm.notes"
                        rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Add any notes about this grant..."
                    ></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button
                        @click="showGrantModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="grantAccess"
                        :disabled="grantForm.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50"
                    >
                        {{ grantForm.processing ? 'Granting...' : 'Grant Access' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Bulk Actions Modal -->
        <Modal :show="showBulkModal" @close="showBulkModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Bulk Actions
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    {{ selectedUsers.length }} users selected
                </p>
                
                <div class="mb-4">
                    <label for="bulk-tier" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Tier <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="bulk-tier"
                        v-model="bulkForm.tier"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="starter">Starter - K120/month</option>
                        <option value="business">Business - K350/month</option>
                        <option value="agency">Agency - K900/month</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="bulk-notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (Optional)
                    </label>
                    <textarea
                        id="bulk-notes"
                        v-model="bulkForm.notes"
                        rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Add any notes..."
                    ></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button
                        @click="showBulkModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="bulkRevoke"
                        :disabled="bulkForm.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50"
                    >
                        Revoke Access
                    </button>
                    <button
                        @click="bulkGrant"
                        :disabled="bulkForm.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50"
                    >
                        Grant Access
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Modal from '@/components/Modal.vue';
import { Link } from '@inertiajs/vue3';

interface User {
    id: number;
    name: string;
    email: string;
    premium_template_tier: 'starter' | 'business' | 'agency' | null;
    premium_access_granted_at: string | null;
    premium_access_granted_by: { name: string } | null;
    premium_access_notes: string | null;
}

interface Props {
    users: {
        data: User[];
        from: number;
        to: number;
        total: number;
        links: Array<{ label: string; url: string | null; active: boolean }>;
    };
    filters: {
        search: string | null;
        filter: string;
    };
    stats: {
        total_users: number;
        granted_access: number;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const filterType = ref(props.filters.filter);
const selectedUsers = ref<number[]>([]);
const showGrantModal = ref(false);
const showBulkModal = ref(false);
const selectedUser = ref<User | null>(null);

const grantForm = reactive({
    tier: 'starter' as 'starter' | 'business' | 'agency',
    notes: '',
    processing: false,
});

const bulkForm = reactive({
    tier: 'starter' as 'starter' | 'business' | 'agency',
    notes: '',
    processing: false,
});

const allSelected = computed(() => {
    return props.users.data.length > 0 && selectedUsers.value.length === props.users.data.length;
});

let searchTimeout: ReturnType<typeof setTimeout>;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const applyFilters = () => {
    const params: Record<string, string> = {};
    
    if (searchQuery.value) {
        params.search = searchQuery.value;
    }
    
    if (filterType.value && filterType.value !== 'all') {
        params.filter = filterType.value;
    }
    
    router.get(route('admin.premium-access.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedUsers.value = [];
    } else {
        selectedUsers.value = props.users.data.map(u => u.id);
    }
};

const openGrantModal = (user: User) => {
    selectedUser.value = user;
    grantForm.tier = 'starter';
    grantForm.notes = '';
    showGrantModal.value = true;
};

const grantAccess = () => {
    if (!selectedUser.value) return;

    grantForm.processing = true;
    router.post(route('admin.premium-access.grant', selectedUser.value.id), {
        tier: grantForm.tier,
        notes: grantForm.notes,
    }, {
        onSuccess: () => {
            showGrantModal.value = false;
            grantForm.tier = 'starter';
            grantForm.notes = '';
        },
        onFinish: () => {
            grantForm.processing = false;
        },
    });
};

const revokeAccess = (user: User) => {
    if (!confirm(`Are you sure you want to revoke premium template access for ${user.name}?`)) {
        return;
    }

    router.post(route('admin.premium-access.revoke', user.id), {}, {
        preserveScroll: true,
    });
};

const bulkGrant = () => {
    bulkForm.processing = true;
    router.post(route('admin.premium-access.bulk-grant'), {
        user_ids: selectedUsers.value,
        tier: bulkForm.tier,
        notes: bulkForm.notes,
    }, {
        onSuccess: () => {
            showBulkModal.value = false;
            selectedUsers.value = [];
            bulkForm.tier = 'starter';
            bulkForm.notes = '';
        },
        onFinish: () => {
            bulkForm.processing = false;
        },
    });
};

const bulkRevoke = () => {
    if (!confirm(`Are you sure you want to revoke premium template access for ${selectedUsers.value.length} users?`)) {
        return;
    }

    bulkForm.processing = true;
    router.post(route('admin.premium-access.bulk-revoke'), {
        user_ids: selectedUsers.value,
    }, {
        onSuccess: () => {
            showBulkModal.value = false;
            selectedUsers.value = [];
        },
        onFinish: () => {
            bulkForm.processing = false;
        },
    });
};

const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>
