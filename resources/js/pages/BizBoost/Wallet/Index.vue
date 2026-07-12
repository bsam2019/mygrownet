<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import Card from '@/Components/BizBoost/UI/Card.vue';
import Button from '@/Components/BizBoost/UI/Button.vue';
import axios from 'axios';
import {
    PlusIcon, BanknotesIcon, ArrowTrendingUpIcon, ArrowTrendingDownIcon,
    PhoneIcon, CurrencyDollarIcon, CheckCircleIcon, ArrowLeftIcon,
} from '@heroicons/vue/24/outline';

interface Transaction {
    id: number;
    amount: number;
    type: 'deposit' | 'withdrawal' | 'payment';
    description: string | null;
    status: 'pending' | 'completed' | 'failed';
    created_at: string;
}

interface Props {
    wallet: {
        balance: number;
        locked_balance: number;
        available_balance: number;
        currency: string;
    };
    transactions: {
        data: Transaction[];
        current_page: number;
        last_page: number;
        total: number;
    };
    userCurrency: string;
    manualPayment: {
        mtn: { number: string; name: string };
        airtel: { number: string; name: string };
    };
}

const props = defineProps<Props>();

const page = usePage();
const isZMW = computed(() => props.userCurrency === 'ZMW');
const currencySymbol = computed(() => isZMW.value ? 'K' : '$');
const locale = computed(() => isZMW.value ? 'en-ZM' : 'en-US');

const formatCurrency = (amount: number) =>
    new Intl.NumberFormat(locale.value, { style: 'currency', currency: props.userCurrency }).format(amount);

const showDepositOptions = ref(false);
const processing = ref(false);

const goToManualPayment = () => {
    router.visit(route('mygrownet.payments.create', { type: 'wallet_topup' }));
};

const submitCrypto = async () => {
    processing.value = true;
    try {
        const response = await axios.post('/api/payments/crypto/create', {
            order_id: `BIZBOOST-WALLET-${Date.now()}`,
            amount: 10,
            currency: props.userCurrency,
        });
        if (response.data.success && response.data.invoice_url) {
            window.location.href = response.data.invoice_url;
        } else {
            alert(response.data.message || 'Failed to create crypto payment');
        }
    } catch (error: any) {
        alert(error.response?.data?.message || 'Failed to initiate cryptocurrency payment');
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Head title="Wallet - BizBoost" />
    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Wallet</h1>
                        <span
                            class="px-2.5 py-0.5 text-xs font-semibold rounded-full"
                            :class="isZMW
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'"
                        >
                            {{ isZMW ? '🇿🇲 ZMW' : '🇺🇸 USD' }}
                        </span>
                    </div>
                    <Button variant="primary" @click="showDepositOptions = !showDepositOptions">
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Deposit Funds
                    </Button>
                </div>

                <!-- Deposit Section -->
                <div v-if="showDepositOptions" class="mb-6">
                    <!-- Back button when showing options -->
                    <button
                        v-if="showDepositOptions"
                        @click="showDepositOptions = false"
                        class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
                    >
                        <ArrowLeftIcon class="h-4 w-4" />
                        Back to Wallet
                    </button>

                    <Card>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Deposit Funds</h2>
                        <p class="text-sm text-gray-600 dark:text-slate-400 mb-6">
                            Your wallet is stored in <strong>{{ isZMW ? 'Zambian Kwacha (ZMW)' : 'US Dollars (USD)' }}</strong>.
                            {{ isZMW ? 'Send money via mobile money below, or use crypto from abroad.' : 'Pay with cryptocurrency.' }}
                        </p>

                        <!-- ============ ZMW: Manual Payment ============ -->
                        <template v-if="isZMW">
                            <div class="grid md:grid-cols-2 gap-4 mb-6">
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-300 dark:border-yellow-700 rounded-xl p-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-yellow-400 flex items-center justify-center">
                                            <span class="text-xs font-bold text-yellow-900">MTN</span>
                                        </div>
                                        <span class="font-bold text-gray-900 dark:text-white">MTN Mobile Money</span>
                                    </div>
                                    <div class="bg-white dark:bg-slate-800 rounded-lg px-3 py-2 mb-2">
                                        <p class="text-xs text-gray-500">Company Number</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ manualPayment.mtn.number }}</p>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-slate-300">{{ manualPayment.mtn.name }}</p>
                                    <div class="bg-yellow-100 dark:bg-yellow-900/40 border border-yellow-300 dark:border-yellow-700 rounded-lg p-2 mt-2">
                                        <p class="text-xs font-semibold text-yellow-900 dark:text-yellow-300">⚠️ Use WITHDRAW method (Agent Number), not Send Money</p>
                                    </div>
                                </div>

                                <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-700 rounded-xl p-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center">
                                            <span class="text-xs font-bold text-white">AIR</span>
                                        </div>
                                        <span class="font-bold text-gray-900 dark:text-white">Airtel Money</span>
                                    </div>
                                    <div class="bg-white dark:bg-slate-800 rounded-lg px-3 py-2 mb-2">
                                        <p class="text-xs text-gray-500">Phone Number</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ manualPayment.airtel.number }}</p>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-slate-300">{{ manualPayment.airtel.name }}</p>
                                    <div class="bg-red-100 dark:bg-red-900/40 border border-red-300 dark:border-red-700 rounded-lg p-2 mt-2">
                                        <p class="text-xs font-semibold text-red-900 dark:text-red-300">📱 Send money normally to this number</p>
                                    </div>
                                </div>
                            </div>

                            <button
                                @click="goToManualPayment"
                                class="w-full bg-gradient-to-r from-violet-600 to-purple-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-violet-700 hover:to-purple-700 transition-all shadow-lg flex items-center justify-center gap-2"
                            >
                                <PhoneIcon class="h-5 w-5" />
                                Submit Payment Proof
                            </button>
                        </template>

                        <!-- ============ USD: Crypto Payment ============ -->
                        <template v-else>
                            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-indigo-200 dark:border-indigo-700 mb-6">
                                <div class="flex items-center gap-3 mb-4">
                                    <CurrencyDollarIcon class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">Cryptocurrency Payment</h3>
                                        <p class="text-sm text-gray-600 dark:text-slate-400">Pay with crypto from anywhere in the world</p>
                                    </div>
                                </div>
                                <div class="space-y-2 text-sm text-gray-700 dark:text-slate-300 mb-4">
                                    <div class="flex items-center gap-2"><span class="text-green-600">✓</span>Instant confirmation</div>
                                    <div class="flex items-center gap-2"><span class="text-green-600">✓</span>Low fees (0.5% - 1%)</div>
                                    <div class="flex items-center gap-2"><span class="text-green-600">✓</span>300+ cryptocurrencies</div>
                                    <div class="flex items-center gap-2"><span class="text-green-600">✓</span>No account needed</div>
                                </div>
                                <button
                                    @click="submitCrypto"
                                    :disabled="processing"
                                    class="w-full bg-indigo-600 text-white py-3 px-6 rounded-xl font-semibold hover:bg-indigo-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                >
                                    <span v-if="processing">Processing...</span>
                                    <span v-else>Pay with Cryptocurrency</span>
                                </button>
                            </div>
                        </template>

                        <!-- Post-deposit note -->
                        <div
                            class="rounded-lg p-3 mt-4"
                            :class="isZMW
                                ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700'
                                : 'bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700'"
                        >
                            <p class="text-xs font-semibold" :class="isZMW ? 'text-green-900 dark:text-green-300' : 'text-indigo-900 dark:text-indigo-300'">
                                {{ isZMW ? '✅ After Payment:' : 'ℹ️ How it works:' }}
                            </p>
                            <ul v-if="isZMW" class="text-xs mt-1 space-y-0.5" :class="isZMW ? 'text-green-800 dark:text-green-400' : 'text-indigo-800 dark:text-indigo-400'">
                                <li>1. Send money to one of the numbers above</li>
                                <li>2. Click "Submit Payment Proof"</li>
                                <li>3. Enter amount and transaction reference</li>
                                <li>4. Our team will verify and credit your wallet</li>
                            </ul>
                            <ul v-else class="text-xs mt-1 space-y-0.5 text-indigo-800 dark:text-indigo-400">
                                <li>1. Click "Pay with Cryptocurrency"</li>
                                <li>2. You'll be redirected to our secure payment page</li>
                                <li>3. Pay with your preferred cryptocurrency</li>
                                <li>4. Funds are added instantly upon confirmation</li>
                            </ul>
                        </div>
                    </Card>
                </div>

                <!-- Balance Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <Card variant="elevated">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-violet-100 dark:bg-violet-900/30 rounded-xl ring-1 ring-violet-200/50 dark:ring-violet-700/30">
                                <BanknotesIcon class="h-6 w-6 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total Balance</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(wallet.balance) }}</p>
                            </div>
                        </div>
                    </Card>
                    <Card variant="elevated">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-amber-100 dark:bg-amber-900/30 rounded-xl ring-1 ring-amber-200/50 dark:ring-amber-700/30">
                                <ArrowTrendingUpIcon class="h-6 w-6 text-amber-600 dark:text-amber-400" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Locked in Campaigns</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(wallet.locked_balance) }}</p>
                            </div>
                        </div>
                    </Card>
                    <Card variant="elevated">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-xl ring-1 ring-green-200/50 dark:ring-green-700/30">
                                <ArrowTrendingDownIcon class="h-6 w-6 text-green-600 dark:text-green-400" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Available Balance</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(wallet.available_balance) }}</p>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Transaction History -->
                <Card>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Transaction History</h2>
                    </div>
                    <div v-if="transactions.data.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">No transactions yet.</div>
                    <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div v-for="txn in transactions.data" :key="txn.id" class="py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ txn.description || txn.type }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ txn.created_at }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span :class="['text-sm font-semibold', txn.type === 'deposit' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
                                    {{ txn.type === 'deposit' ? '+' : '-' }}{{ formatCurrency(txn.amount) }}
                                </span>
                                <span :class="['px-2.5 py-0.5 text-xs font-medium rounded-full', txn.status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : txn.status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400']">
                                    {{ txn.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="transactions.last_page > 1" class="flex justify-center gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <Link
                            v-for="page in transactions.last_page"
                            :key="page"
                            :href="route('bizboost.wallet.index', { page })"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                                page === transactions.current_page
                                    ? 'bg-violet-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                            ]"
                        >
                            {{ page }}
                        </Link>
                    </div>
                </Card>
            </div>
        </div>
    </BizBoostLayout>
</template>
