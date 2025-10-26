<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { ShoppingBagIcon, CheckCircleIcon, AlertCircleIcon } from 'lucide-vue-next';

interface Props {
    price: number;
    shopCredit: number;
    walletBalance: number;
    paymentMethods: Array<{
        id: string;
        name: string;
        description: string;
        icon: string;
    }>;
}

const props = defineProps<Props>();

const form = useForm({
    payment_method: 'mobile_money',
    terms_accepted: false,
});

const submit = () => {
    form.post(route('mygrownet.starter-kit.store'));
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};
</script>

<template>
    <Head title="Purchase Starter Kit" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Back Button -->
                <div class="mb-6">
                    <Link :href="route('mygrownet.starter-kit.show')" class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Starter Kit
                    </Link>
                </div>

                <!-- Purchase Form -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-8 text-white">
                        <h1 class="text-3xl font-bold">Complete Your Purchase</h1>
                        <p class="mt-2 text-blue-100">Get instant access to your Starter Kit</p>
                    </div>

                    <!-- Value Summary -->
                    <div class="px-6 py-6 bg-gray-50 border-b">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">MyGrowNet Starter Kit</h2>
                                <p class="text-sm text-gray-600">Total Value: K1,050</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 line-through">K1,050</p>
                                <p class="text-3xl font-bold text-green-600">{{ formatCurrency(price) }}</p>
                                <p class="text-sm text-green-600">Save K550 (52%)</p>
                            </div>
                        </div>

                        <!-- What's Included Breakdown -->
                        <div class="border-t pt-6">
                            <h3 class="font-semibold text-gray-900 mb-4">What's Included:</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Training Modules -->
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-start">
                                        <CheckCircleIcon class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
                                        <div>
                                            <h4 class="font-semibold text-gray-900">3 Training Modules</h4>
                                            <ul class="text-sm text-gray-600 mt-1 space-y-1">
                                                <li>• Business Fundamentals</li>
                                                <li>• Network Building Strategies</li>
                                                <li>• Financial Success Planning</li>
                                            </ul>
                                            <p class="text-xs text-gray-500 mt-2">Value: K300</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- eBooks -->
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-start">
                                        <CheckCircleIcon class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
                                        <div>
                                            <h4 class="font-semibold text-gray-900">3 Premium eBooks</h4>
                                            <ul class="text-sm text-gray-600 mt-1 space-y-1">
                                                <li>• Success Guide</li>
                                                <li>• Network Building Mastery</li>
                                                <li>• Financial Freedom Blueprint</li>
                                            </ul>
                                            <p class="text-xs text-gray-500 mt-2">Value: K150</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Video Tutorials -->
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-start">
                                        <CheckCircleIcon class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
                                        <div>
                                            <h4 class="font-semibold text-gray-900">3 Video Tutorials</h4>
                                            <ul class="text-sm text-gray-600 mt-1 space-y-1">
                                                <li>• Platform Navigation</li>
                                                <li>• Building Your Network</li>
                                                <li>• Maximizing Earnings</li>
                                            </ul>
                                            <p class="text-xs text-gray-500 mt-2">Value: K200</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tools & Resources -->
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-start">
                                        <CheckCircleIcon class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Marketing Tools</h4>
                                            <ul class="text-sm text-gray-600 mt-1 space-y-1">
                                                <li>• Marketing Templates</li>
                                                <li>• Pitch Deck</li>
                                                <li>• Social Media Content</li>
                                            </ul>
                                            <p class="text-xs text-gray-500 mt-2">Value: K100</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Library Access -->
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-start">
                                        <CheckCircleIcon class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Digital Library</h4>
                                            <ul class="text-sm text-gray-600 mt-1 space-y-1">
                                                <li>• 50+ Business eBooks</li>
                                                <li>• 30-Day Access</li>
                                                <li>• Renewable Subscription</li>
                                            </ul>
                                            <p class="text-xs text-gray-500 mt-2">Value: K200</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bonuses -->
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-start">
                                        <CheckCircleIcon class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Instant Bonuses</h4>
                                            <ul class="text-sm text-gray-600 mt-1 space-y-1">
                                                <li>• K{{ shopCredit }} Shop Credit (90 days)</li>
                                                <li>• +50 Lifetime Points</li>
                                                <li>• Achievement Badges</li>
                                            </ul>
                                            <p class="text-xs text-gray-500 mt-2">Value: K{{ shopCredit + 100 }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="px-6 py-8">
                        <!-- Wallet Balance Alert -->
                        <div v-if="walletBalance >= price" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <CheckCircleIcon class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
                                <div>
                                    <h4 class="font-semibold text-green-900">You have sufficient wallet balance!</h4>
                                    <p class="text-sm text-green-700 mt-1">
                                        Your wallet balance: <span class="font-semibold">{{ formatCurrency(walletBalance) }}</span>
                                    </p>
                                    <p class="text-sm text-green-700">
                                        You can pay directly from your wallet for instant access.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="walletBalance > 0" class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <AlertCircleIcon class="w-5 h-5 text-amber-600 mt-0.5 mr-3 flex-shrink-0" />
                                <div>
                                    <h4 class="font-semibold text-amber-900">Insufficient wallet balance</h4>
                                    <p class="text-sm text-amber-700 mt-1">
                                        Your current balance: <span class="font-semibold">{{ formatCurrency(walletBalance) }}</span>
                                    </p>
                                    <p class="text-sm text-amber-700">
                                        You need {{ formatCurrency(price - walletBalance) }} more. Please use Mobile Money or Bank Transfer.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Payment Method *
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Wallet Balance Payment Option -->
                                <div
                                    @click="walletBalance >= price ? form.payment_method = 'wallet' : null"
                                    :class="[
                                        'border-2 rounded-lg p-4 transition',
                                        form.payment_method === 'wallet'
                                            ? 'border-green-600 bg-green-50'
                                            : 'border-gray-200',
                                        walletBalance >= price ? 'cursor-pointer hover:border-green-300' : 'cursor-not-allowed opacity-60'
                                    ]"
                                >
                                    <div class="text-3xl mb-2">💰</div>
                                    <h3 class="font-semibold text-gray-900">Wallet Balance</h3>
                                    <p class="text-sm text-gray-600">Instant payment</p>
                                    <p class="text-xs text-gray-500 mt-1">Available: {{ formatCurrency(walletBalance) }}</p>
                                    <p v-if="walletBalance >= price" class="text-xs text-green-600 font-semibold mt-1">✓ Instant Access</p>
                                    <p v-else class="text-xs text-red-600 mt-1">Need {{ formatCurrency(price - walletBalance) }} more</p>
                                </div>

                                <!-- Other Payment Methods -->
                                <div
                                    v-for="method in paymentMethods"
                                    :key="method.id"
                                    @click="form.payment_method = method.id"
                                    :class="[
                                        'border-2 rounded-lg p-4 cursor-pointer transition',
                                        form.payment_method === method.id
                                            ? 'border-blue-600 bg-blue-50'
                                            : 'border-gray-200 hover:border-blue-300'
                                    ]"
                                >
                                    <div class="text-3xl mb-2">{{ method.icon }}</div>
                                    <h3 class="font-semibold text-gray-900">{{ method.name }}</h3>
                                    <p class="text-sm text-gray-600">{{ method.description }}</p>
                                </div>
                            </div>
                            <p v-if="form.errors.payment_method" class="mt-2 text-sm text-red-600">{{ form.errors.payment_method }}</p>
                        </div>

                        <!-- Payment Instructions -->
                        <div v-if="form.payment_method !== 'wallet'" class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-900 mb-2">Next Step: Submit Payment</h4>
                            <p class="text-sm text-blue-700 mb-3">
                                After clicking "Continue", you'll be redirected to submit your payment details for verification.
                            </p>
                            <div class="space-y-2 text-sm text-blue-700">
                                <div class="flex items-start">
                                    <span class="font-semibold mr-2">1.</span>
                                    <span>Make payment of {{ formatCurrency(price) }} via {{ form.payment_method === 'mobile_money' ? 'MTN MoMo or Airtel Money' : 'Bank Transfer' }}</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-semibold mr-2">2.</span>
                                    <span>Submit your payment reference and details</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-semibold mr-2">3.</span>
                                    <span>Admin will verify and grant access (usually within 24 hours)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Wallet Payment Confirmation -->
                        <div v-else class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-semibold text-green-900 mb-2">✓ Instant Access with Wallet</h4>
                            <p class="text-sm text-green-700">
                                {{ formatCurrency(price) }} will be deducted from your wallet balance.
                            </p>
                            <p class="text-sm text-green-700 mt-1">
                                New balance after purchase: <span class="font-semibold">{{ formatCurrency(walletBalance - price) }}</span>
                            </p>
                            <p class="text-sm text-green-700 mt-2 font-semibold">
                                You'll get instant access - no waiting for verification!
                            </p>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-6">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <h3 class="font-semibold text-gray-900 mb-2">Terms and Conditions</h3>
                                <div class="text-sm text-gray-700 space-y-2 max-h-48 overflow-y-auto">
                                    <p>By purchasing the MyGrowNet Starter Kit, you agree to:</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>This is a one-time purchase of digital products and services</li>
                                        <li>All digital content is delivered instantly and is non-refundable once accessed</li>
                                        <li>K{{ shopCredit }} shop credit expires in 90 days</li>
                                        <li>Digital library access is valid for 30 days (renewable)</li>
                                        <li>Content is for personal use only and cannot be resold</li>
                                        <li>This is NOT an investment and does not guarantee any returns</li>
                                    </ul>
                                </div>
                            </div>

                            <label class="flex items-start mt-4 cursor-pointer">
                                <input
                                    v-model="form.terms_accepted"
                                    type="checkbox"
                                    class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    required
                                />
                                <span class="ml-2 text-sm text-gray-700">
                                    I have read and agree to the Terms and Conditions and understand that this purchase is non-refundable once I access the digital content.
                                </span>
                            </label>
                            <p v-if="form.errors.terms_accepted" class="mt-2 text-sm text-red-600">{{ form.errors.terms_accepted }}</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between">
                            <Link
                                :href="route('mygrownet.starter-kit.show')"
                                class="text-gray-600 hover:text-gray-800"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                <span v-if="form.processing">Processing...</span>
                                <span v-else-if="form.payment_method === 'wallet'">Complete Purchase - {{ formatCurrency(price) }}</span>
                                <span v-else>Continue to Payment Submission</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- What Happens Next -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">What You'll Receive</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">✓</div>
                            <div class="ml-4">
                                <p class="font-medium text-blue-900">Complete Training Package</p>
                                <p class="text-sm text-blue-700">3 modules, 3 eBooks, 3 videos + marketing tools</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">✓</div>
                            <div class="ml-4">
                                <p class="font-medium text-blue-900">K{{ shopCredit }} Shop Credit</p>
                                <p class="text-sm text-blue-700">Use in MyGrowNet shop (valid 90 days)</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">✓</div>
                            <div class="ml-4">
                                <p class="font-medium text-blue-900">Progressive Content Unlocking</p>
                                <p class="text-sm text-blue-700">New content unlocks over 30 days for optimal learning</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">✓</div>
                            <div class="ml-4">
                                <p class="font-medium text-blue-900">+37.5 Lifetime Points</p>
                                <p class="text-sm text-blue-700">Instant LP bonus to boost your level</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
