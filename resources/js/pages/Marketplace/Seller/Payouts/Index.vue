<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import { 
    BanknotesIcon, 
    ClockIcon, 
    CheckCircleIcon,
    XCircleIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';

interface Payout {
    id: number;
    reference: string;
    amount: number;
    net_amount: number;
    payout_method: string;
    account_number: string;
    status: string;
    status_label: string;
    status_color: string;
    formatted_amount: string;
    formatted_net_amount: string;
    created_at: string;
    approved_at?: string;
    processed_at?: string;
}

interface Props {
    payouts: {
        data: Payout[];
        links: any;
        meta: any;
    };
    availableBalance: number;
    minimumPayout: number;
    canRequest: {
        can_request: boolean;
        reason?: string;
    };
    payoutMethods: Record<string, any>;
}

const props = defineProps<Props>();

const formatAmount = (amount: number) => {
    return 'K' + (amount / 100).toFixed(2);
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed': return CheckCircleIcon;
        case 'rejected':
        case 'failed': return XCircleIcon;
        case 'processing': return ArrowPathIcon;
        default: return ClockIcon;
    }
};

const getMethodLabel = (method: string) => {
    return props.payoutMethods[method]?.label || method.toUpperCase();
};
</script>

<template>
    <MarketplaceLayout>
        <div class="max-w-6xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Payouts</h1>
                    <p class="text-gray-500">Manage your earnings withdrawals</p>
                </div>
                <Link 
                    v-if="canRequest.can_request"
                    :href="route('marketplace.seller.payouts.create')"
                    class="px-6 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600"
                >
                    Request Payout
                </Link>
            </div>

            <!-- Balance Card -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm mb-1">Available Balance</p>
                        <p class="text-3xl font-bold">{{ formatAmount(availableBalance) }}</p>
                        <p class="text-orange-100 text-sm mt-2">
                            Minimum payout: {{ formatAmount(minimumPayout) }}
                        </p>
                    </div>
                    <BanknotesIcon class="h-16 w-16 text-orange-200" aria-hidden="true" />
                </div>
                
                <div v-if="!canRequest.can_request" class="mt-4 p-3 bg-orange-400/30 rounded-lg">
                    <p class="text-sm">{{ canRequest.reason }}</p>
                </div>
            </div>

            <!-- Payouts List -->
            <div class="bg-white rounded-xl border">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold text-gray-900">Payout History</h2>
                </div>

                <div v-if="payouts.data.length === 0" class="p-12 text-center">
                    <BanknotesIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                    <p class="text-gray-500">No payouts yet</p>
                    <p class="text-sm text-gray-400 mt-1">Request your first payout when you have earnings</p>
                </div>

                <div v-else class="divide-y">
                    <div 
                        v-for="payout in payouts.data" 
                        :key="payout.id"
                        class="p-6 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-4">
                                <component 
                                    :is="getStatusIcon(payout.status)" 
                                    :class="[
                                        'h-6 w-6 flex-shrink-0',
                                        payout.status_color === 'green' ? 'text-green-600' :
                                        payout.status_color === 'red' ? 'text-red-600' :
                                        payout.status_color === 'blue' ? 'text-blue-600' :
                                        payout.status_color === 'yellow' ? 'text-yellow-600' :
                                        'text-gray-600'
                                    ]"
                                    aria-hidden="true"
                                />
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <p class="font-semibold text-gray-900">{{ payout.formatted_net_amount }}</p>
                                        <span :class="[
                                            'px-2 py-0.5 text-xs font-medium rounded-full',
                                            payout.status_color === 'green' ? 'bg-green-100 text-green-700' :
                                            payout.status_color === 'red' ? 'bg-red-100 text-red-700' :
                                            payout.status_color === 'blue' ? 'bg-blue-100 text-blue-700' :
                                            payout.status_color === 'yellow' ? 'bg-yellow-100 text-yellow-700' :
                                            'bg-gray-100 text-gray-700'
                                        ]">
                                            {{ payout.status_label }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        {{ getMethodLabel(payout.payout_method) }} • {{ payout.account_number }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Ref: {{ payout.reference }} • {{ new Date(payout.created_at).toLocaleDateString() }}
                                    </p>
                                </div>
                            </div>
                            
                            <Link 
                                :href="route('marketplace.seller.payouts.show', payout.id)"
                                class="text-sm text-orange-600 hover:text-orange-700 font-medium"
                            >
                                View Details
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="payouts.data.length > 0" class="p-4 border-t bg-gray-50">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            Showing {{ payouts.meta.from }} to {{ payouts.meta.to }} of {{ payouts.meta.total }} payouts
                        </p>
                        <div class="flex gap-2">
                            <Link 
                                v-for="link in payouts.links" 
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1 text-sm rounded',
                                    link.active ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
