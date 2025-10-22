<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <Link
                            :href="route('admin.subscriptions.index')"
                            class="text-gray-500 hover:text-gray-700"
                        >
                            <ArrowLeftIcon class="h-5 w-5" />
                        </Link>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ user.name }}</h1>
                            <p class="text-gray-600">{{ user.email }} • {{ user.phone }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <button
                            @click="sendNotification"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                        >
                            Send Notification
                        </button>
                        <button
                            @click="viewProfile"
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
                        >
                            View Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Current Subscription Status -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Subscription</h2>
                        
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Professional Level</label>
                                        <p class="text-lg font-semibold text-gray-900">{{ capitalizeLevel(user.current_professional_level) }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Status</label>
                                        <p>
                                            <span :class="[
                                                'px-3 py-1 text-sm font-medium rounded-full',
                                                getStatusColor()
                                            ]">
                                                {{ getSubscriptionStatus() }}
                                            </span>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Life Points (LP)</label>
                                        <p class="text-lg font-semibold text-purple-600">{{ user.life_points?.toLocaleString() || 0 }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Expires On</label>
                                        <p class="text-lg font-semibold text-gray-900">
                                            {{ user.subscription_expires_at ? formatDate(user.subscription_expires_at) : 'N/A' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Days Remaining</label>
                                        <p :class="[
                                            'text-lg font-semibold',
                                            getDaysRemainingColor()
                                        ]">
                                            {{ getDaysRemaining() }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Monthly Activity Points (BP)</label>
                                        <p class="text-lg font-semibold text-green-600">{{ user.monthly_activity_points?.toLocaleString() || 0 }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex space-x-3">
                                <button
                                    @click="openActionModal('status')"
                                    class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors"
                                >
                                    Update Status
                                </button>
                                <button
                                    @click="openActionModal('extend')"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                                >
                                    Extend Subscription
                                </button>
                                <button
                                    @click="openActionModal('upgrade')"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors"
                                >
                                    Force Upgrade
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Member Since</span>
                                <span class="text-sm font-medium text-gray-900">{{ formatDate(user.created_at) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Referrals</span>
                                <span class="text-sm font-medium text-gray-900">{{ user.total_referrals || 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Wallet Balance</span>
                                <span class="text-sm font-medium text-green-600">K{{ user.wallet_balance?.toFixed(2) || '0.00' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Commissions</span>
                                <span class="text-sm font-medium text-green-600">K{{ user.total_commissions?.toFixed(2) || '0.00' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription History -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Subscription History</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="subscription in subscriptionHistory" :key="subscription.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(subscription.created_at) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ subscription.package?.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ subscription.billing_cycle }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">K{{ subscription.amount?.toFixed(2) }}</td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        subscription.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                    ]">
                                        {{ subscription.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(subscription.expires_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment History</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="payment in paymentHistory" :key="payment.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(payment.created_at) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ payment.type }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">K{{ payment.amount?.toFixed(2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ payment.payment_method }}</td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        payment.status === 'completed' ? 'bg-green-100 text-green-800' :
                                        payment.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                        'bg-red-100 text-red-800'
                                    ]">
                                        {{ payment.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ payment.reference || 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

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
                                <option value="associate">Associate</option>
                                <option value="professional">Professional</option>
                                <option value="senior">Senior</option>
                                <option value="manager">Manager</option>
                                <option value="director">Director</option>
                                <option value="executive">Executive</option>
                                <option value="ambassador">Ambassador</option>
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
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Modal from '@/components/Modal.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface User {
    id: number;
    name: string;
    email: string;
    phone: string;
    current_professional_level: string;
    subscription_status: string;
    subscription_expires_at: string;
    life_points: number;
    monthly_activity_points: number;
    wallet_balance: number;
    total_referrals: number;
    total_commissions: number;
    created_at: string;
}

interface Props {
    user: User;
    subscriptionHistory: any[];
    paymentHistory: any[];
}

const props = defineProps<Props>();

const showActionModal = ref(false);
const actionType = ref<'status' | 'extend' | 'upgrade'>('status');

const actionForm = ref({
    status: props.user.subscription_status || 'active',
    days: 30,
    level: props.user.current_professional_level || 'associate',
    reason: '',
});

const openActionModal = (type: 'status' | 'extend' | 'upgrade') => {
    actionType.value = type;
    actionForm.value = {
        status: props.user.subscription_status || 'active',
        days: 30,
        level: props.user.current_professional_level || 'associate',
        reason: '',
    };
    showActionModal.value = true;
};

const closeActionModal = () => {
    showActionModal.value = false;
};

const executeAction = () => {
    const routes = {
        status: 'admin.subscriptions.update-status',
        extend: 'admin.subscriptions.extend',
        upgrade: 'admin.subscriptions.force-upgrade',
    };

    router.post(route(routes[actionType.value], props.user.id), actionForm.value, {
        onSuccess: () => {
            closeActionModal();
        },
    });
};

const sendNotification = () => {
    alert('Notification feature coming soon');
};

const viewProfile = () => {
    router.visit(route('admin.users.show', props.user.id));
};

const getSubscriptionStatus = () => {
    if (props.user.subscription_status === 'suspended') return 'Suspended';
    if (!props.user.subscription_expires_at) return 'No Subscription';
    if (new Date(props.user.subscription_expires_at) <= new Date()) return 'Expired';
    return 'Active';
};

const getStatusColor = () => {
    const status = getSubscriptionStatus();
    switch (status) {
        case 'Active': return 'bg-green-100 text-green-800';
        case 'Expired': return 'bg-red-100 text-red-800';
        case 'Suspended': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getDaysRemaining = () => {
    if (!props.user.subscription_expires_at) return 'N/A';
    const expiry = new Date(props.user.subscription_expires_at);
    const today = new Date();
    const diff = Math.ceil((expiry.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
    if (diff < 0) return 'Expired';
    return `${diff} days`;
};

const getDaysRemainingColor = () => {
    if (!props.user.subscription_expires_at) return 'text-gray-500';
    const expiry = new Date(props.user.subscription_expires_at);
    const today = new Date();
    const diff = Math.ceil((expiry.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
    if (diff < 0) return 'text-red-600';
    if (diff <= 7) return 'text-yellow-600';
    return 'text-green-600';
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

const capitalizeLevel = (level: string) => {
    if (!level) return 'Associate';
    return level.charAt(0).toUpperCase() + level.slice(1).toLowerCase();
};
</script>
