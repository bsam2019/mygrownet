<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Subscription Management</h1>
                        <p class="text-gray-600 mt-1">Manage all member subscriptions, renewals, and upgrades</p>
                    </div>
                    <div class="flex space-x-3">
                        <button
                            @click="showCreateModal = true"
                            class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors"
                        >
                            Create Subscription
                        </button>
                        <button
                            @click="showBulkActions = !showBulkActions"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                        >
                            Bulk Actions
                        </button>
                        <button
                            @click="exportSubscriptions"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                        >
                            Export Data
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <UsersIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Subscriptions</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <CheckCircleIcon class="h-6 w-6 text-green-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Active</p>
                            <p class="text-2xl font-bold text-green-600">{{ stats.active.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <XCircleIcon class="h-6 w-6 text-red-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Expired</p>
                            <p class="text-2xl font-bold text-red-600">{{ stats.expired.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <ClockIcon class="h-6 w-6 text-yellow-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Expiring This Week</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ stats.expiring_this_week.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Revenue</h3>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm text-gray-600">This Month</p>
                            <p class="text-2xl font-bold text-green-600">K{{ revenueData.this_month.toLocaleString() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Growth</p>
                            <p :class="[
                                'text-lg font-semibold',
                                revenueData.growth_rate >= 0 ? 'text-green-600' : 'text-red-600'
                            ]">
                                {{ revenueData.growth_rate >= 0 ? '+' : '' }}{{ revenueData.growth_rate.toFixed(1) }}%
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Level Distribution</h3>
                    <div class="space-y-3">
                        <div v-for="(count, level) in stats.level_distribution" :key="level" class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">{{ level }}</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-blue-600 h-2 rounded-full" 
                                        :style="{ width: (count / stats.total * 100) + '%' }"
                                    ></div>
                                </div>
                                <span class="text-sm text-gray-600 w-8 text-right">{{ count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input
                            v-model="searchForm.search"
                            type="text"
                            placeholder="Name, email, or phone..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @input="debouncedSearch"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                        <select
                            v-model="searchForm.level"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">All Levels</option>
                            <option v-for="level in levels" :key="level" :value="level">{{ level }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select
                            v-model="searchForm.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="clearFilters"
                            class="w-full px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions Panel -->
            <div v-if="showBulkActions" class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Bulk Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                        <select
                            v-model="bulkForm.action"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Select Action</option>
                            <option value="suspend">Suspend</option>
                            <option value="activate">Activate</option>
                            <option value="extend">Extend</option>
                        </select>
                    </div>
                    <div v-if="bulkForm.action === 'extend'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Days</label>
                        <input
                            v-model.number="bulkForm.days"
                            type="number"
                            min="1"
                            max="365"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                        <input
                            v-model="bulkForm.reason"
                            type="text"
                            placeholder="Optional reason..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="executeBulkAction"
                            :disabled="!bulkForm.action || selectedUsers.length === 0"
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Execute ({{ selectedUsers.length }})
                        </button>
                    </div>
                </div>
            </div>

            <!-- Subscriptions Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        @change="toggleAllUsers"
                                        :checked="selectedUsers.length === subscriptions.data.length && subscriptions.data.length > 0"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="subscription in subscriptions.data" :key="subscription.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <input
                                        type="checkbox"
                                        :value="subscription.id"
                                        v-model="selectedUsers"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ subscription.name.charAt(0).toUpperCase() }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ subscription.name }}</div>
                                            <div class="text-sm text-gray-500">{{ subscription.email }}</div>
                                            <div class="text-sm text-gray-500">{{ subscription.phone }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        getLevelColor(subscription.current_professional_level)
                                    ]">
                                        {{ capitalizeLevel(subscription.current_professional_level) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        getStatusColor(subscription)
                                    ]">
                                        {{ getSubscriptionStatus(subscription) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ subscription.subscription_end_date ? formatDate(subscription.subscription_end_date) : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ subscription.transactions?.[0] ? formatDate(subscription.transactions[0].created_at) : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium space-x-2">
                                    <Link
                                        :href="route('admin.subscriptions.show', subscription.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        View
                                    </Link>
                                    <button
                                        @click="openActionModal(subscription, 'status')"
                                        class="text-yellow-600 hover:text-yellow-900"
                                    >
                                        Status
                                    </button>
                                    <button
                                        @click="openActionModal(subscription, 'extend')"
                                        class="text-green-600 hover:text-green-900"
                                    >
                                        Extend
                                    </button>
                                    <button
                                        @click="openActionModal(subscription, 'upgrade')"
                                        class="text-purple-600 hover:text-purple-900"
                                    >
                                        Upgrade
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    <Pagination :links="subscriptions.links" />
                </div>
            </div>

            <!-- Create Subscription Modal -->
            <Modal :show="showCreateModal" @close="closeCreateModal">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Create New Subscription
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                            <select
                                v-model="createForm.user_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Choose a user...</option>
                                <option v-for="user in allUsers" :key="user.id" :value="user.id">
                                    {{ user.name }} ({{ user.email }})
                                </option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Package</label>
                            <select
                                v-model="createForm.package_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Choose a package...</option>
                                <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                                    {{ pkg.name }} - K{{ pkg.price }}/month
                                </option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Duration (months)</label>
                            <input
                                v-model.number="createForm.duration_months"
                                type="number"
                                min="1"
                                max="12"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason (optional)</label>
                            <textarea
                                v-model="createForm.reason"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Reason for creating this subscription..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button
                            @click="closeCreateModal"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="executeCreateSubscription"
                            :disabled="!createForm.user_id || !createForm.package_id"
                            class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Create Subscription
                        </button>
                    </div>
                </div>
            </Modal>

            <!-- Action Modal -->
            <Modal :show="showActionModal" @close="closeActionModal">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ getModalTitle() }}
                    </h3>
                    
                    <!-- Status Update Form -->
                    <div v-if="actionType === 'status'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select
                                v-model="actionForm.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                            <textarea
                                v-model="actionForm.reason"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Optional reason for status change..."
                            ></textarea>
                        </div>
                    </div>

                    <!-- Extend Subscription Form -->
                    <div v-if="actionType === 'extend'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Extend by (days)</label>
                            <input
                                v-model.number="actionForm.days"
                                type="number"
                                min="1"
                                max="365"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                            <textarea
                                v-model="actionForm.reason"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Reason for extension..."
                            ></textarea>
                        </div>
                    </div>

                    <!-- Force Upgrade Form -->
                    <div v-if="actionType === 'upgrade'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Level</label>
                            <select
                                v-model="actionForm.level"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option v-for="level in levels" :key="level" :value="level">{{ capitalizeLevel(level) }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                            <textarea
                                v-model="actionForm.reason"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Reason for upgrade..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button
                            @click="closeActionModal"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="executeAction"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                        >
                            {{ getActionButtonText() }}
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Modal from '@/components/Modal.vue';
import Pagination from '@/components/Pagination.vue';
import { UsersIcon, CheckCircleIcon, XCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';
import { debounce } from 'lodash';

interface User {
    id: number;
    name: string;
    email: string;
    phone: string;
    current_professional_level: string;
    subscription_status: string;
    subscription_end_date: string;
    transactions: any[];
}

interface Props {
    subscriptions: {
        data: User[];
        links: any[];
    };
    stats: {
        total: number;
        active: number;
        expired: number;
        suspended: number;
        expiring_this_week: number;
        level_distribution: Record<string, number>;
    };
    revenueData: {
        this_month: number;
        last_month: number;
        growth_rate: number;
        by_level: Record<string, number>;
    };
    filters: {
        search?: string;
        level?: string;
        status?: string;
    };
    levels: string[];
    packages: any[];
    allUsers: any[];
}

const props = defineProps<Props>();

const showBulkActions = ref(false);
const showActionModal = ref(false);
const showCreateModal = ref(false);
const selectedUsers = ref<number[]>([]);
const selectedUser = ref<User | null>(null);
const actionType = ref<'status' | 'extend' | 'upgrade'>('status');

const searchForm = ref({
    search: props.filters.search || '',
    level: props.filters.level || '',
    status: props.filters.status || '',
});

const bulkForm = ref({
    action: '',
    days: 30,
    reason: '',
});

const createForm = ref({
    user_id: '',
    package_id: '',
    duration_months: 1,
    reason: '',
});

const actionForm = ref({
    status: 'active',
    days: 30,
    level: 'associate',
    reason: '',
});

const debouncedSearch = debounce(() => {
    applyFilters();
}, 500);

const applyFilters = () => {
    router.get(route('admin.subscriptions.index'), searchForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchForm.value = { search: '', level: '', status: '' };
    applyFilters();
};

const toggleAllUsers = () => {
    if (selectedUsers.value.length === props.subscriptions.data.length) {
        selectedUsers.value = [];
    } else {
        selectedUsers.value = props.subscriptions.data.map(user => user.id);
    }
};

const openActionModal = (user: User, type: 'status' | 'extend' | 'upgrade') => {
    selectedUser.value = user;
    actionType.value = type;
    
    actionForm.value = {
        status: user.subscription_status || 'active',
        days: 30,
        level: user.current_professional_level || 'associate',
        reason: '',
    };
    
    showActionModal.value = true;
};

const closeActionModal = () => {
    showActionModal.value = false;
    selectedUser.value = null;
};

const executeAction = () => {
    if (!selectedUser.value) return;

    const routes = {
        status: 'admin.subscriptions.update-status',
        extend: 'admin.subscriptions.extend',
        upgrade: 'admin.subscriptions.force-upgrade',
    };

    router.post(route(routes[actionType.value], selectedUser.value.id), actionForm.value, {
        onSuccess: () => {
            closeActionModal();
        },
    });
};

const executeBulkAction = () => {
    if (!bulkForm.value.action || selectedUsers.value.length === 0) return;

    router.post(route('admin.subscriptions.bulk-action'), {
        ...bulkForm.value,
        user_ids: selectedUsers.value,
    }, {
        onSuccess: () => {
            selectedUsers.value = [];
            bulkForm.value = { action: '', days: 30, reason: '' };
        },
    });
};

const exportSubscriptions = () => {
    window.open(route('admin.subscriptions.export', searchForm.value));
};

const getSubscriptionStatus = (user: User) => {
    if (user.subscription_status === 'suspended') return 'Suspended';
    if (!user.subscription_end_date) return 'No Subscription';
    if (new Date(user.subscription_end_date) <= new Date()) return 'Expired';
    return 'Active';
};

const getStatusColor = (user: User) => {
    const status = getSubscriptionStatus(user);
    switch (status) {
        case 'Active': return 'bg-green-100 text-green-800';
        case 'Expired': return 'bg-red-100 text-red-800';
        case 'Suspended': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getLevelColor = (level: string) => {
    const colors = {
        'associate': 'bg-blue-100 text-blue-800',
        'professional': 'bg-green-100 text-green-800',
        'senior': 'bg-yellow-100 text-yellow-800',
        'manager': 'bg-orange-100 text-orange-800',
        'director': 'bg-red-100 text-red-800',
        'executive': 'bg-purple-100 text-purple-800',
        'ambassador': 'bg-indigo-100 text-indigo-800',
    };
    return colors[level?.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

const capitalizeLevel = (level: string) => {
    if (!level) return 'Associate';
    return level.charAt(0).toUpperCase() + level.slice(1).toLowerCase();
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getModalTitle = () => {
    const titles = {
        status: 'Update Subscription Status',
        extend: 'Extend Subscription',
        upgrade: 'Force Level Upgrade',
    };
    return titles[actionType.value];
};

const getActionButtonText = () => {
    const texts = {
        status: 'Update Status',
        extend: 'Extend Subscription',
        upgrade: 'Upgrade Level',
    };
    return texts[actionType.value];
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.value = {
        user_id: '',
        package_id: '',
        duration_months: 1,
        reason: '',
    };
};

const executeCreateSubscription = () => {
    router.post(route('admin.subscriptions.create'), createForm.value, {
        onSuccess: () => {
            closeCreateModal();
        },
    });
};
</script>
