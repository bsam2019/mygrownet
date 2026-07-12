<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

interface UserSummary {
    name: string; email: string; phone: string; status: string; account_type: string;
    has_starter_kit: boolean; total_commissions: number; pending_commissions: number;
    total_profit_shares: number; bonus_points: number; bonus_balance: number;
    loyalty_points: number; lifetime_points: number; referral_count: number;
}

interface Commission { id: number; level: number; amount: number; status: string; commission_type: string; package_type: string; paid_at: string | null; created_at: string; }
interface ProfitShare { id: number; amount: number; status: string; distribution_type: string; tier_at_distribution: string; paid_at: string | null; created_at: string; }
interface PointTx { id: number; source: string; lp_amount: number; bp_amount: number; description: string; created_at: string; }
interface Transaction { id: number; transaction_type: string; amount: number; status: string; description: string; created_at: string; }

const props = defineProps<{
    user: UserSummary;
    commissions: Commission[];
    profitShares: ProfitShare[];
    pointTransactions: PointTx[];
    transactions: Transaction[];
}>();

const adjustAmount = ref(0);
const adjustReason = ref('');
const showAdjustModal = ref(false);
const adjusting = ref(false);

const openAdjustModal = () => { adjustAmount.value = 0; adjustReason.value = ''; showAdjustModal.value = true; };
const submitAdjust = () => {
    if (!adjustReason.value || adjustAmount.value === 0) return;
    adjusting.value = true;
    router.post(route('admin.grownet.earnings.adjust-bonus', props.user.id), { amount: adjustAmount.value, reason: adjustReason.value }, {
        preserveScroll: true, onSuccess: () => { showAdjustModal.value = false; adjusting.value = false; },
        onError: () => { adjusting.value = false; }
    });
};

const formatCurrency = (value: number) => `K${value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const activeTab = ref<'commissions' | 'profits' | 'points' | 'transactions'>('commissions');
</script>

<template>
    <Head :title="`Earnings - ${user.name}`" />
    <AdminLayout>
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.grownet.earnings')" class="text-blue-600 hover:text-blue-800">&larr; Back</Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ user.name }}</h1>
                        <p class="text-sm text-gray-600">{{ user.email }} &middot; {{ user.phone || 'No phone' }}</p>
                    </div>
                </div>
                <button @click="openAdjustModal" class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors">
                    Adjust Bonus Balance
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs text-gray-600">Status</p>
                    <span :class="['px-2 py-0.5 text-xs font-medium rounded-full inline-block mt-1', user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800']">{{ user.status }}</span>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs text-gray-600">Account Type</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ user.account_type }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs text-gray-600">Starter Kit</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ user.has_starter_kit ? 'Yes' : 'No' }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs text-gray-600">Referrals</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ user.referral_count }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs text-gray-600">Bonus Points</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ user.bonus_points?.toLocaleString() || 0 }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-xs text-gray-600">Bonus Balance</p>
                    <p class="text-sm font-semibold text-amber-700 mt-1">{{ formatCurrency(user.bonus_balance || 0) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-5 text-white">
                    <p class="text-blue-100 text-xs font-medium">Total Commissions</p>
                    <p class="text-2xl font-bold mt-1">{{ formatCurrency(user.total_commissions) }}</p>
                    <p v-if="user.pending_commissions > 0" class="text-blue-100 text-xs mt-1">{{ formatCurrency(user.pending_commissions) }} pending</p>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-5 text-white">
                    <p class="text-green-100 text-xs font-medium">Profit Shares</p>
                    <p class="text-2xl font-bold mt-1">{{ formatCurrency(user.total_profit_shares) }}</p>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg shadow p-5 text-white">
                    <p class="text-amber-100 text-xs font-medium">LGR / LP</p>
                    <p class="text-2xl font-bold mt-1">{{ formatCurrency(user.loyalty_points || 0) }}</p>
                    <p class="text-amber-100 text-xs mt-1">Lifetime LP: {{ user.lifetime_points?.toLocaleString() || 0 }}</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow">
                <div class="border-b border-gray-200">
                    <nav class="flex gap-0">
                        <button v-for="tab in [{ k: 'commissions', l: 'Commissions' }, { k: 'profits', l: 'Profit Shares' }, { k: 'points', l: 'Point Transactions' }, { k: 'transactions', l: 'Wallet Transactions' }]" :key="tab.k"
                            @click="activeTab = tab.k as any"
                            :class="['px-6 py-3 text-sm font-medium border-b-2 transition-colors', activeTab === tab.k ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700']">
                            {{ tab.l }}
                        </button>
                    </nav>
                </div>

                <!-- Commissions Tab -->
                <div v-if="activeTab === 'commissions'" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Level</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Package</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Amount</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="c in commissions" :key="c.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-700">{{ c.created_at }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">Level {{ c.level }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ c.commission_type }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ c.package_type || '-' }}</td>
                                <td class="px-4 py-2 text-sm font-medium text-right text-gray-900">{{ formatCurrency(c.amount) }}</td>
                                <td class="px-4 py-2 text-right">
                                    <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', c.status === 'paid' ? 'bg-green-100 text-green-800' : c.status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800']">{{ c.status }}</span>
                                </td>
                            </tr>
                            <tr v-if="commissions.length === 0"><td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">No commissions</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Profit Shares Tab -->
                <div v-if="activeTab === 'profits'" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tier</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Amount</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="p in profitShares" :key="p.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-700">{{ p.created_at }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ p.distribution_type || 'Quarterly' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ p.tier_at_distribution || '-' }}</td>
                                <td class="px-4 py-2 text-sm font-medium text-right text-gray-900">{{ formatCurrency(p.amount) }}</td>
                                <td class="px-4 py-2 text-right">
                                    <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', p.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800']">{{ p.status }}</span>
                                </td>
                            </tr>
                            <tr v-if="profitShares.length === 0"><td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">No profit shares</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Point Transactions Tab -->
                <div v-if="activeTab === 'points'" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Source</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">LP</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">BP</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="pt in pointTransactions" :key="pt.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-700">{{ pt.created_at }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ pt.source }}<br><span class="text-xs text-gray-500">{{ pt.description }}</span></td>
                                <td class="px-4 py-2 text-sm font-medium text-right text-gray-900">{{ pt.lp_amount }}</td>
                                <td class="px-4 py-2 text-sm font-medium text-right text-gray-900">{{ pt.bp_amount }}</td>
                            </tr>
                            <tr v-if="pointTransactions.length === 0"><td colspan="4" class="px-4 py-8 text-center text-gray-500 text-sm">No point transactions</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Wallet Transactions Tab -->
                <div v-if="activeTab === 'transactions'" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Description</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Amount</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="t in transactions" :key="t.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-700">{{ t.created_at }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ t.transaction_type }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 max-w-xs truncate">{{ t.description || '-' }}</td>
                                <td class="px-4 py-2 text-sm font-medium text-right text-gray-900">{{ formatCurrency(t.amount) }}</td>
                                <td class="px-4 py-2 text-right">
                                    <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', t.status === 'completed' || t.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800']">{{ t.status }}</span>
                                </td>
                            </tr>
                            <tr v-if="transactions.length === 0"><td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">No wallet transactions</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Adjust Bonus Modal -->
            <div v-if="showAdjustModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="showAdjustModal = false">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Adjust Bonus Balance</h3>
                    <p class="text-sm text-gray-600 mb-4">Current balance: <strong>{{ formatCurrency(user.bonus_balance || 0) }}</strong></p>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (use negative to deduct)</label>
                        <input v-model.number="adjustAmount" type="number" step="0.01" placeholder="e.g. 100 or -50"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                        <textarea v-model="adjustReason" rows="3" placeholder="Why is this adjustment being made?"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button @click="showAdjustModal = false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">Cancel</button>
                        <button @click="submitAdjust" :disabled="adjusting || adjustAmount === 0 || !adjustReason"
                            class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors text-sm font-medium disabled:opacity-50">
                            {{ adjusting ? 'Processing...' : 'Submit' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
