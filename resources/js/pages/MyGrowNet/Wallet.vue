<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { BanknoteIcon, ArrowUpIcon, ArrowDownIcon, ClockIcon, GiftIcon, TrophyIcon, ShieldCheckIcon, AlertCircleIcon, InfoIcon } from 'lucide-vue-next';
import { ref } from 'vue';

interface Transaction {
    id: number;
    type: string;
    amount: number;
    status: string;
    date: string;
    description: string;
}

interface VerificationLimits {
    daily_withdrawal: number;
    monthly_withdrawal: number;
    single_transaction: number;
}

const props = withDefaults(defineProps<{
    balance?: number;
    bonusBalance?: number;
    loyaltyPoints?: number;
    totalEarnings?: number;
    totalWithdrawals?: number;
    recentTransactions?: Transaction[];
    pendingWithdrawals?: number;
    commissionEarnings?: number;
    profitEarnings?: number;
    walletTopups?: number;
    workshopExpenses?: number;
    verificationLevel?: string;
    verificationLimits?: VerificationLimits;
    remainingDailyLimit?: number;
    policyAccepted?: boolean;
}>(), {
    balance: 0,
    bonusBalance: 0,
    loyaltyPoints: 0,
    totalEarnings: 0,
    totalWithdrawals: 0,
    recentTransactions: () => [],
    pendingWithdrawals: 0,
    commissionEarnings: 0,
    profitEarnings: 0,
    walletTopups: 0,
    workshopExpenses: 0,
    verificationLevel: 'basic',
    verificationLimits: () => ({ daily_withdrawal: 1000, monthly_withdrawal: 10000, single_transaction: 500 }),
    remainingDailyLimit: 1000,
    policyAccepted: false,
});

// Now we can safely use props
const showPolicyModal = ref(!props.policyAccepted);
const showLgrTransferModal = ref(false);
const transferAmount = ref(0);
const transferProcessing = ref(false);

const acceptPolicy = () => {
    router.post(route('mygrownet.wallet.accept-policy'), {}, {
        onSuccess: () => {
            showPolicyModal.value = false;
        }
    });
};

const submitLgrTransfer = () => {
    if (transferAmount.value <= 0) {
        alert('Please enter a valid amount');
        return;
    }
    
    if (transferAmount.value > props.loyaltyPoints) {
        alert('Insufficient LGR balance');
        return;
    }
    
    transferProcessing.value = true;
    
    router.post(route('mygrownet.wallet.lgr-transfer'), {
        amount: transferAmount.value
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('Transfer success:', page);
            showLgrTransferModal.value = false;
            transferAmount.value = 0;
            transferProcessing.value = false;
        },
        onError: (errors) => {
            console.error('Transfer error:', errors);
            alert('Transfer failed: ' + (errors.amount || errors.error || 'Unknown error'));
            transferProcessing.value = false;
        }
    });
};

const getVerificationBadgeColor = (level: string) => {
    switch(level) {
        case 'premium': return 'bg-purple-100 text-purple-800 border-purple-200';
        case 'enhanced': return 'bg-blue-100 text-blue-800 border-blue-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getVerificationLabel = (level: string) => {
    switch(level) {
        case 'premium': return 'Premium';
        case 'enhanced': return 'Enhanced';
        default: return 'Basic';
    }
};

const formatCurrency = (amount: number | undefined | null) => {
    const value = amount ?? 0;
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(value);
};
</script>

<template>
    <MemberLayout>
        <Head title="My Wallet" />

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Wallet</h1>
                <p class="mt-2 text-sm text-gray-600">Manage your earnings, deposits, and withdrawals in one place</p>
            </div>

            <!-- Policy Acceptance Modal -->
            <div v-if="showPolicyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col">
                    <!-- Fixed Header -->
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50">
                        <div class="flex items-center gap-3">
                            <div class="bg-blue-600 p-3 rounded-lg">
                                <ShieldCheckIcon class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">MyGrowNet Wallet Policy</h2>
                                <p class="text-sm text-gray-600">Please review before using your wallet</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Scrollable Content -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        <!-- Important Notice -->
                        <div class="bg-amber-50 border-2 border-amber-300 rounded-lg p-5">
                            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <AlertCircleIcon class="h-5 w-5 text-amber-600" />
                                Important Notice
                            </h3>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="font-semibold text-gray-900 mb-2">This Wallet is NOT:</p>
                                    <ul class="space-y-1 text-gray-700">
                                        <li>❌ A bank account</li>
                                        <li>❌ An investment product</li>
                                        <li>❌ A deposit-taking service</li>
                                        <li>❌ A money transfer service</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 mb-2">This Wallet IS:</p>
                                    <ul class="space-y-1 text-gray-700">
                                        <li>✅ A prepaid digital account</li>
                                        <li>✅ For platform transactions only</li>
                                        <li>✅ Fully compliant with regulations</li>
                                        <li>✅ A convenience feature</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- What You Can Do -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-5">
                            <h3 class="font-bold text-gray-900 mb-3">✅ What You CAN Do:</h3>
                            <div class="grid md:grid-cols-2 gap-3 text-sm text-gray-700">
                                <div class="flex items-start gap-2">
                                    <span class="text-green-600 mt-0.5">✓</span>
                                    <span>Store earnings and platform funds securely</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-green-600 mt-0.5">✓</span>
                                    <span>Purchase products and subscriptions</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-green-600 mt-0.5">✓</span>
                                    <span>Withdraw to mobile money or bank account</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-green-600 mt-0.5">✓</span>
                                    <span>Receive bonuses and rewards</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-green-600 mt-0.5">✓</span>
                                    <span>Pay for training and workshops</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-green-600 mt-0.5">✓</span>
                                    <span>Track all transaction history</span>
                                </div>
                            </div>
                        </div>

                        <!-- What You Cannot Do -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-5">
                            <h3 class="font-bold text-gray-900 mb-3">❌ What You CANNOT Do:</h3>
                            <div class="grid md:grid-cols-2 gap-3 text-sm text-gray-700">
                                <div class="flex items-start gap-2">
                                    <span class="text-red-600 mt-0.5">✗</span>
                                    <span>Earn interest on wallet balance</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-red-600 mt-0.5">✗</span>
                                    <span>Transfer funds to other individuals</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-red-600 mt-0.5">✗</span>
                                    <span>Use for non-platform transactions</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-red-600 mt-0.5">✗</span>
                                    <span>Treat as an investment account</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-red-600 mt-0.5">✗</span>
                                    <span>Lend or borrow funds</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-red-600 mt-0.5">✗</span>
                                    <span>Withdraw bonus credits as cash</span>
                                </div>
                            </div>
                        </div>

                        <!-- Key Limits & Terms -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                            <h3 class="font-bold text-gray-900 mb-3">📋 Key Limits & Terms:</h3>
                            <div class="grid md:grid-cols-2 gap-4 text-sm text-gray-700">
                                <div>
                                    <p class="font-semibold mb-1">Withdrawals:</p>
                                    <ul class="space-y-1 ml-4">
                                        <li>• Minimum: K50</li>
                                        <li>• Maximum daily: K50,000</li>
                                        <li>• Processing: 1-5 business days</li>
                                        <li>• Fees may apply</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-semibold mb-1">Rewards & Bonuses:</p>
                                    <ul class="space-y-1 ml-4">
                                        <li>• Promotional incentives only</li>
                                        <li>• NOT investment returns</li>
                                        <li>• Redeemable for platform use</li>
                                        <li>• Cannot be withdrawn as cash</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-semibold mb-1">Security:</p>
                                    <ul class="space-y-1 ml-4">
                                        <li>• Funds in segregated accounts</li>
                                        <li>• Encrypted transactions</li>
                                        <li>• KYC verification required</li>
                                        <li>• Transaction monitoring</li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-semibold mb-1">Your Responsibilities:</p>
                                    <ul class="space-y-1 ml-4">
                                        <li>• Keep credentials secure</li>
                                        <li>• Use for authorized purposes only</li>
                                        <li>• Report issues immediately</li>
                                        <li>• Comply with all terms</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Legal Compliance -->
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-5">
                            <h3 class="font-bold text-gray-900 mb-3">⚖️ Legal Compliance:</h3>
                            <p class="text-sm text-gray-700 mb-3">
                                The MyGrowNet Wallet operates in full compliance with:
                            </p>
                            <div class="grid md:grid-cols-2 gap-2 text-sm text-gray-700">
                                <div>✓ Companies Act of Zambia</div>
                                <div>✓ Bank of Zambia Regulations</div>
                                <div>✓ Securities and Exchange Commission</div>
                                <div>✓ Anti-Money Laundering (AML) Laws</div>
                            </div>
                        </div>

                        <!-- Contact & Support -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-5">
                            <h3 class="font-bold text-gray-900 mb-3">📞 Need Help?</h3>
                            <div class="text-sm text-gray-700 space-y-2">
                                <p><strong>Email:</strong> support@mygrownet.com</p>
                                <p><strong>Response Time:</strong> 24-48 hours</p>
                                <p><strong>Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM CAT</p>
                            </div>
                        </div>

                        <!-- Acknowledgment -->
                        <div class="border-t-2 border-gray-200 pt-4">
                            <p class="text-sm text-gray-600 italic">
                                By clicking "Accept & Continue", you acknowledge that you have read and understood this wallet policy, 
                                agree to comply with all terms and conditions, and accept the risks associated with digital transactions.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Fixed Footer -->
                    <div class="p-6 border-t-2 border-gray-200 bg-gray-50">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a
                                :href="route('wallet.policy')"
                                target="_blank"
                                class="sm:flex-none bg-white border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-50 transition-colors text-center inline-flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Full Policy
                            </a>
                            <button
                                @click="acceptPolicy"
                                class="flex-1 bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg"
                            >
                                I Accept - Continue to Wallet
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Balance Card -->
            <div class="mb-6 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-blue-100 text-sm">Available Balance</p>
                            <span 
                                :class="['text-xs px-2 py-1 rounded-full border', getVerificationBadgeColor(verificationLevel)]"
                            >
                                {{ getVerificationLabel(verificationLevel) }}
                            </span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-bold mt-1">{{ formatCurrency(balance) }}</h2>
                    </div>
                    <BanknoteIcon class="h-12 w-12 text-blue-200" />
                </div>
                
                <!-- Bonus & Loyalty Points -->
                <div class="grid grid-cols-2 gap-3 mb-4 pt-4 border-t border-blue-400">
                    <div class="flex items-center gap-2">
                        <GiftIcon class="h-5 w-5 text-blue-200" />
                        <div>
                            <p class="text-xs text-blue-100">Bonus Balance</p>
                            <p class="text-sm font-semibold">{{ formatCurrency(bonusBalance) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <TrophyIcon class="h-5 w-5 text-blue-200" />
                        <div>
                            <p class="text-xs text-blue-100">LGR Balance</p>
                            <p class="text-sm font-semibold">{{ formatCurrency(loyaltyPoints) }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-3 mt-6">
                    <Link
                        :href="route('mygrownet.payments.create', { type: 'wallet_topup' })"
                        class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium text-center hover:bg-blue-50 transition-colors text-sm"
                    >
                        Top Up
                    </Link>
                    <Link
                        :href="route('withdrawals.index')"
                        class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium text-center hover:bg-blue-50 transition-colors text-sm"
                    >
                        Withdraw
                    </Link>
                    <Link
                        :href="route('transactions')"
                        class="bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-center hover:bg-blue-800 transition-colors text-sm"
                    >
                        History
                    </Link>
                </div>
            </div>

            <!-- Balance Breakdown -->
            <div class="mb-6 bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Balance Breakdown</h3>
                    <p class="text-sm text-gray-600 mt-1">Your available funds across different balance types</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Main Wallet -->
                        <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-600 p-2 rounded-lg">
                                    <BanknoteIcon class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Main Wallet</p>
                                    <p class="text-xs text-gray-600">From commissions & profit shares</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-bold text-gray-900">{{ formatCurrency(balance) }}</p>
                                <p class="text-xs text-green-600 font-medium">100% withdrawable</p>
                            </div>
                        </div>

                        <!-- LGR Balance -->
                        <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="bg-yellow-500 p-2 rounded-lg">
                                        <TrophyIcon class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">LGR Balance</p>
                                        <p class="text-xs text-gray-600">Loyalty Growth Reward credits</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-bold text-gray-900">{{ formatCurrency(loyaltyPoints) }}</p>
                                    <p class="text-xs text-amber-600 font-medium">Up to {{ formatCurrency(loyaltyPoints * 1.0) }} withdrawable (100%)</p>
                                </div>
                            </div>
                            <button
                                v-if="loyaltyPoints > 0"
                                @click="showLgrTransferModal = true"
                                class="w-full px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-600 text-white text-sm font-semibold rounded-lg hover:from-yellow-600 hover:to-amber-700 transition-all shadow-sm"
                            >
                                Transfer to Main Wallet
                            </button>
                        </div>

                        <!-- Bonus Balance -->
                        <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg border border-purple-200">
                            <div class="flex items-center gap-3">
                                <div class="bg-purple-600 p-2 rounded-lg">
                                    <GiftIcon class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Bonus Balance</p>
                                    <p class="text-xs text-gray-600">Promotional credits</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-bold text-gray-900">{{ formatCurrency(bonusBalance) }}</p>
                                <p class="text-xs text-gray-600 font-medium">Platform use only</p>
                            </div>
                        </div>

                        <!-- Total Available -->
                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border-2 border-green-300">
                            <div>
                                <p class="font-semibold text-gray-900">Total Available</p>
                                <p class="text-xs text-gray-600">All balances combined</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-green-700">{{ formatCurrency(balance + loyaltyPoints + bonusBalance) }}</p>
                                <p class="text-xs text-green-600 font-medium">Max withdrawable: {{ formatCurrency(balance + (loyaltyPoints * 1.0)) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Note -->
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex gap-2">
                            <InfoIcon class="h-4 w-4 text-blue-600 mt-0.5 flex-shrink-0" />
                            <div class="text-xs text-blue-800">
                                <p class="font-medium mb-1">Withdrawal Rules:</p>
                                <ul class="space-y-1 ml-4 list-disc">
                                    <li>Main Wallet: Withdraw 100% anytime</li>
                                    <li>LGR Balance: Withdraw up to 40% as cash, use 100% on platform</li>
                                    <li>Bonus Balance: Platform purchases only (not withdrawable)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Withdrawal Limits Info -->
            <div v-if="verificationLimits" class="mb-6 bg-white rounded-lg shadow p-4">
                <div class="flex items-start gap-3">
                    <AlertCircleIcon class="h-5 w-5 text-blue-600 mt-0.5" />
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 mb-2">Your Withdrawal Limits</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Daily Limit</p>
                                <p class="font-semibold text-gray-900">{{ formatCurrency(verificationLimits.daily_withdrawal) }}</p>
                                <p class="text-xs text-gray-500 mt-1">Remaining: {{ formatCurrency(remainingDailyLimit) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Monthly Limit</p>
                                <p class="font-semibold text-gray-900">{{ formatCurrency(verificationLimits.monthly_withdrawal) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Per Transaction</p>
                                <p class="font-semibold text-gray-900">{{ formatCurrency(verificationLimits.single_transaction) }}</p>
                            </div>
                        </div>
                        <p v-if="verificationLevel === 'basic'" class="text-xs text-blue-600 mt-3">
                            💡 Upgrade to Enhanced or Premium verification for higher limits
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Income</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(totalEarnings) }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <ArrowUpIcon class="h-6 w-6 text-green-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Earnings + Deposits</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Withdrawn</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(totalWithdrawals) }}</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-lg">
                            <ArrowDownIcon class="h-6 w-6 text-red-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Approved withdrawals</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pending</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(pendingWithdrawals) }}</p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-lg">
                            <ClockIcon class="h-6 w-6 text-amber-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Awaiting approval</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Expenses</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(workshopExpenses) }}</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <BanknoteIcon class="h-6 w-6 text-purple-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Workshop payments</p>
                </div>
            </div>

            <!-- Earnings Breakdown -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Income Breakdown</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600">Referral Commissions</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(commissionEarnings) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-blue-600 h-2 rounded-full" 
                                    :style="{ width: totalEarnings > 0 ? `${(commissionEarnings / totalEarnings) * 100}%` : '0%' }"
                                ></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600">Profit Shares</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(profitEarnings) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-green-600 h-2 rounded-full" 
                                    :style="{ width: totalEarnings > 0 ? `${(profitEarnings / totalEarnings) * 100}%` : '0%' }"
                                ></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600">Wallet Top-ups</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(walletTopups) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-purple-600 h-2 rounded-full" 
                                    :style="{ width: totalEarnings > 0 ? `${(walletTopups / totalEarnings) * 100}%` : '0%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    <div
                        v-for="transaction in recentTransactions"
                        :key="transaction.id"
                        class="px-6 py-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ transaction.description }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ transaction.date }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-green-600">+{{ formatCurrency(transaction.amount) }}</p>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mt-1"
                                    :class="{
                                        'bg-green-100 text-green-800': transaction.status === 'paid' || transaction.status === 'verified',
                                        'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                                        'bg-gray-100 text-gray-800': transaction.status === 'processing',
                                    }"
                                >
                                    {{ transaction.status === 'verified' ? 'Completed' : transaction.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="recentTransactions.length === 0" class="px-6 py-8 text-center text-gray-500">
                        No transactions yet
                    </div>
                </div>
                
                <div v-if="recentTransactions.length > 0" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <Link
                        :href="route('transactions')"
                        class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                    >
                        View all transactions →
                    </Link>
                </div>
            </div>
        </div>
    </div>

    <!-- LGR Transfer Modal -->
    <div v-if="showLgrTransferModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Transfer LGR to Wallet</h3>
                <button @click="showLgrTransferModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Available LGR Balance:</p>
                <p class="text-2xl font-bold text-yellow-600">{{ formatCurrency(loyaltyPoints) }}</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Transfer Amount
                </label>
                <input
                    v-model.number="transferAmount"
                    type="number"
                    step="0.01"
                    min="10"
                    :max="loyaltyPoints"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                    placeholder="Enter amount"
                />
                <p class="text-xs text-gray-500 mt-1">Minimum: K10.00</p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <p class="text-xs text-blue-800">
                    <strong>Note:</strong> Transferred funds will be added to your main wallet balance and can be used for purchases or withdrawn.
                </p>
            </div>

            <div class="flex gap-3">
                <button
                    @click="showLgrTransferModal = false"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Cancel
                </button>
                <button
                    @click="submitLgrTransfer"
                    :disabled="transferProcessing || transferAmount <= 0"
                    class="flex-1 px-4 py-2 bg-gradient-to-r from-yellow-500 to-amber-600 text-white rounded-lg hover:from-yellow-600 hover:to-amber-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="transferProcessing">Processing...</span>
                    <span v-else>Transfer</span>
                </button>
            </div>
        </div>
    </div>

    </MemberLayout>
</template>
