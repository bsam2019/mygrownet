<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { BanknoteIcon, ClockIcon, CheckCircleIcon, XCircleIcon } from 'lucide-vue-next';

interface Withdrawal {
    id: number;
    amount: number;
    status: string;
    created_at: string;
    processed_at?: string;
}

const props = defineProps<{
    withdrawals: {
        data: Withdrawal[];
        links: Array<{ url?: string; label: string; active: boolean }>;
    };
    availableBalance: number;
    minimumWithdrawal: number;
    userPhone?: string;
    userName?: string;
}>();

const showNewWithdrawal = ref(false);

// Initialize form with user's registered details
const form = useForm({
    amount: '',
    phone_number: props.userPhone || '',
    account_name: props.userName || '',
});

// Log to debug (remove after testing)
console.log('User Phone:', props.userPhone);
console.log('User Name:', props.userName);
console.log('Form initialized:', form.phone_number, form.account_name);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'processing': 'bg-blue-100 text-blue-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusIcon = (status: string) => {
    if (status === 'approved') return CheckCircleIcon;
    if (status === 'rejected') return XCircleIcon;
    return ClockIcon;
};

const handleNewWithdrawal = () => {
    if (!props.userPhone) {
        // Redirect to profile page if no phone number
        window.location.href = route('profile.edit');
        return;
    }
    showNewWithdrawal.value = true;
};

const submitWithdrawal = () => {
    form.post(route('withdrawals.store'), {
        onSuccess: () => {
            showNewWithdrawal.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Withdrawals" />

    <MemberLayout>
        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Withdrawals</h1>
                        <p class="mt-2 text-sm text-gray-600">Request withdrawals from your wallet balance</p>
                    </div>
                    <button
                        @click="handleNewWithdrawal"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                    >
                        <BanknoteIcon class="h-5 w-5" />
                        <span>New Withdrawal</span>
                    </button>
                </div>

                <!-- Phone Number Required Alert -->
                <div v-if="!userPhone" class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-amber-800">Phone Number Required</h3>
                            <p class="mt-1 text-sm text-amber-700">
                                You need to add a mobile money number to your profile before you can request withdrawals.
                            </p>
                            <a 
                                :href="route('profile.edit')" 
                                class="mt-3 inline-flex items-center px-3 py-2 bg-amber-600 text-white text-sm font-medium rounded-md hover:bg-amber-700"
                            >
                                Update Profile
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Balance Card -->
                <div class="mb-6 bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                    <p class="text-green-100 text-sm">Available Balance</p>
                    <p class="text-3xl font-bold mt-2">{{ formatCurrency(availableBalance) }}</p>
                    <p class="text-sm text-green-100 mt-2">Minimum withdrawal: {{ formatCurrency(minimumWithdrawal) }}</p>
                </div>

                <!-- Withdrawal History -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Withdrawal History</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Processed</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="withdrawal in withdrawals.data" :key="withdrawal.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ new Date(withdrawal.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ formatCurrency(withdrawal.amount) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-900">Mobile Money</span>
                                            <span class="text-xs text-gray-500">{{ withdrawal.wallet_address || '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium', getStatusColor(withdrawal.status)]">
                                            <component :is="getStatusIcon(withdrawal.status)" class="h-3 w-3" />
                                            {{ withdrawal.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ withdrawal.processed_at ? new Date(withdrawal.processed_at).toLocaleDateString() : '-' }}
                                    </td>
                                </tr>
                                <tr v-if="withdrawals.data.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No withdrawal requests yet
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- New Withdrawal Modal -->
                <div v-if="showNewWithdrawal" class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showNewWithdrawal = false"></div>
                        
                        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Withdrawal</h3>
                            
                            <form @submit.prevent="submitWithdrawal" class="space-y-4">
                                <!-- Info Banner -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <p class="text-sm text-blue-800">
                                        <strong>Mobile Money Withdrawal</strong><br>
                                        Funds will be sent to your mobile money account (MTN or Airtel)
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (ZMW)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-gray-500">K</span>
                                        <input
                                            v-model="form.amount"
                                            type="number"
                                            step="0.01"
                                            :min="minimumWithdrawal"
                                            :max="availableBalance"
                                            class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="0.00"
                                            required
                                        />
                                    </div>
                                    <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Available: {{ formatCurrency(availableBalance) }} | Minimum: {{ formatCurrency(minimumWithdrawal) }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Money Number</label>
                                    <input
                                        v-model="form.phone_number"
                                        type="tel"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 cursor-not-allowed"
                                        placeholder="0971234567 or +260971234567"
                                        readonly
                                        required
                                    />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Using your registered phone number for security
                                    </p>
                                    <p v-if="form.errors.phone_number" class="mt-1 text-sm text-red-600">{{ form.errors.phone_number }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                    <input
                                        v-model="form.account_name"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 cursor-not-allowed"
                                        placeholder="Full name as registered"
                                        readonly
                                        required
                                    />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Using your registered name for security
                                    </p>
                                    <p v-if="form.errors.account_name" class="mt-1 text-sm text-red-600">{{ form.errors.account_name }}</p>
                                </div>

                                <!-- Processing Info -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <p class="text-xs text-gray-600">
                                        <strong>Processing Time:</strong> Withdrawals are typically processed within 24-48 hours during business days.
                                    </p>
                                </div>

                                <div class="flex gap-3 pt-4">
                                    <button
                                        type="button"
                                        @click="showNewWithdrawal = false"
                                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="form.processing || parseFloat(form.amount) < minimumWithdrawal || parseFloat(form.amount) > availableBalance"
                                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        {{ form.processing ? 'Submitting...' : 'Submit Request' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
