<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { CheckCircleIcon, DocumentTextIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';

interface Investment {
    id: number;
    amount: number;
    shares: number;
    payment_method: string;
    payment_reference: string;
    status: string;
    created_at: string;
    venture: {
        id: number;
        title: string;
        slug: string;
    };
}

interface Receipt {
    id: number;
    receipt_number: string;
    amount: number;
    created_at: string;
}

const props = defineProps<{
    investment: Investment;
    receipt?: Receipt;
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Investment Successful" />

    <MemberLayout>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <!-- Success Header -->
                <div class="mb-8 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                        <CheckCircleIcon class="h-10 w-10 text-green-600" />
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Investment Successful!</h1>
                    <p class="mt-2 text-lg text-gray-600">
                        Congratulations! You are now a shareholder in {{ investment.venture.title }}
                    </p>
                </div>

                <!-- Investment Details -->
                <div class="mb-8 rounded-lg border border-green-200 bg-green-50 p-6">
                    <h2 class="mb-4 text-lg font-semibold text-green-900">Investment Summary</h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <div class="text-sm text-green-700">Investment Amount</div>
                            <div class="text-xl font-bold text-green-900">{{ formatCurrency(investment.amount) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-green-700">Shares Allocated</div>
                            <div class="text-xl font-bold text-green-900">{{ investment.shares.toLocaleString() }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-green-700">Payment Method</div>
                            <div class="font-semibold text-green-900">
                                {{ investment.payment_method === 'wallet' ? 'MyGrow Wallet' : 'Mobile Money' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-green-700">Transaction Date</div>
                            <div class="font-semibold text-green-900">{{ formatDate(investment.created_at) }}</div>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-green-200 pt-4">
                        <div class="text-sm text-green-700">Reference Number</div>
                        <div class="font-mono text-sm font-semibold text-green-900">{{ investment.payment_reference }}</div>
                    </div>
                </div>

                <!-- What Happens Next -->
                <div class="mb-8 rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">What Happens Next?</h2>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-600">1</div>
                            <div>
                                <div class="font-semibold text-gray-900">Company Formation</div>
                                <div class="text-sm text-gray-600">Once funding is complete, a new company will be registered with you as a legal shareholder.</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-600">2</div>
                            <div>
                                <div class="font-semibold text-gray-900">Share Certificates</div>
                                <div class="text-sm text-gray-600">You'll receive official share certificates and shareholder documentation.</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-600">3</div>
                            <div>
                                <div class="font-semibold text-gray-900">Business Operations</div>
                                <div class="text-sm text-gray-600">The company will begin operations and you'll receive regular updates on progress.</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-600">4</div>
                            <div>
                                <div class="font-semibold text-gray-900">Dividend Payments</div>
                                <div class="text-sm text-gray-600">As the company generates profits, you'll receive dividend payments to your wallet.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <Link
                        :href="route('ventures.show', investment.venture.slug)"
                        class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-500 transition-colors"
                    >
                        View Venture
                        <ArrowRightIcon class="h-4 w-4" />
                    </Link>
                    
                    <Link
                        :href="route('mygrownet.ventures.my-investments')"
                        class="flex items-center justify-center gap-2 rounded-lg bg-gray-600 px-6 py-3 text-sm font-semibold text-white hover:bg-gray-500 transition-colors"
                    >
                        My Investments
                    </Link>
                    
                    <Link
                        v-if="receipt"
                        :href="route('receipts.show', receipt.id)"
                        class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        <DocumentTextIcon class="h-4 w-4" />
                        View Receipt
                    </Link>
                </div>

                <!-- Important Notice -->
                <div class="mt-8 rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold">Important Information</p>
                            <p class="mt-1">This is a real business investment. Returns are not guaranteed and depend on the company's performance. You will receive regular updates and financial reports as a shareholder.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
