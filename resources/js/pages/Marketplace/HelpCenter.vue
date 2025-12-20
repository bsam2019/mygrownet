<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    ShoppingBagIcon,
    TruckIcon,
    CreditCardIcon,
    ShieldCheckIcon,
    UserGroupIcon,
    QuestionMarkCircleIcon,
    ChatBubbleLeftRightIcon,
    EnvelopeIcon,
} from '@heroicons/vue/24/outline';

const faqs = [
    {
        category: 'Buying',
        icon: ShoppingBagIcon,
        questions: [
            { q: 'How do I place an order?', a: 'Browse products, add items to your cart, and proceed to checkout. You\'ll need to be logged in to complete your purchase.' },
            { q: 'What payment methods are accepted?', a: 'We accept Mobile Money (MTN MoMo, Airtel Money) and bank transfers. All payments are processed securely through our escrow system.' },
            { q: 'Can I cancel my order?', a: 'You can cancel your order before the seller ships it. Once shipped, you\'ll need to wait for delivery and then request a return if needed.' },
        ]
    },
    {
        category: 'Delivery',
        icon: TruckIcon,
        questions: [
            { q: 'How long does delivery take?', a: 'Delivery times vary by seller location and your province. Most orders within the same province arrive in 2-5 business days.' },
            { q: 'How do I track my order?', a: 'Go to "My Orders" in your account to see the status of all your orders. You\'ll also receive SMS/email updates.' },
            { q: 'What if my order doesn\'t arrive?', a: 'Contact the seller first. If unresolved, open a dispute and our team will help. Your payment is protected by escrow.' },
        ]
    },
    {
        category: 'Payments & Escrow',
        icon: CreditCardIcon,
        questions: [
            { q: 'What is escrow protection?', a: 'When you pay, funds are held securely by MyGrowNet. The seller only receives payment after you confirm delivery. This protects both buyers and sellers.' },
            { q: 'When do I confirm delivery?', a: 'Confirm delivery within 7 days of receiving your order. If you don\'t confirm, funds are automatically released to the seller after 7 days.' },
            { q: 'How do refunds work?', a: 'If there\'s an issue with your order, open a dispute. If resolved in your favor, funds are returned to your payment method within 3-5 business days.' },
        ]
    },
    {
        category: 'Account & Security',
        icon: ShieldCheckIcon,
        questions: [
            { q: 'How do I create an account?', a: 'Click "Join" and fill in your details. Verify your email or phone number to activate your account.' },
            { q: 'Is my information secure?', a: 'Yes. We use industry-standard encryption and never share your personal information with third parties without consent.' },
            { q: 'How do I reset my password?', a: 'Click "Sign In", then "Forgot Password". Enter your email and follow the instructions sent to you.' },
        ]
    },
];

const contactOptions = [
    { icon: ChatBubbleLeftRightIcon, title: 'Live Chat', desc: 'Chat with our support team', action: 'Start Chat', available: true },
    { icon: EnvelopeIcon, title: 'Email Support', desc: 'support@mygrownet.com', action: 'Send Email', available: true },
    { icon: QuestionMarkCircleIcon, title: 'Community Forum', desc: 'Ask the community', action: 'Visit Forum', available: false },
];
</script>

<template>
    <Head title="Help Center - Marketplace" />
    
    <MarketplaceLayout>
        <div class="bg-gray-50 min-h-screen">
            <!-- Hero -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white py-16">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 class="text-3xl md:text-4xl font-bold mb-4">How can we help you?</h1>
                    <p class="text-orange-100 text-lg">Find answers to common questions or contact our support team</p>
                </div>
            </div>

            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Quick Links -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
                    <Link :href="route('marketplace.buyer-protection')" class="bg-white rounded-xl p-6 text-center hover:shadow-lg transition-shadow">
                        <ShieldCheckIcon class="h-8 w-8 text-orange-500 mx-auto mb-3" aria-hidden="true" />
                        <span class="font-medium text-gray-900">Buyer Protection</span>
                    </Link>
                    <Link :href="route('marketplace.seller-guide')" class="bg-white rounded-xl p-6 text-center hover:shadow-lg transition-shadow">
                        <UserGroupIcon class="h-8 w-8 text-orange-500 mx-auto mb-3" aria-hidden="true" />
                        <span class="font-medium text-gray-900">Seller Guide</span>
                    </Link>
                    <Link :href="route('marketplace.orders.index')" class="bg-white rounded-xl p-6 text-center hover:shadow-lg transition-shadow">
                        <ShoppingBagIcon class="h-8 w-8 text-orange-500 mx-auto mb-3" aria-hidden="true" />
                        <span class="font-medium text-gray-900">My Orders</span>
                    </Link>
                    <Link :href="route('marketplace.about')" class="bg-white rounded-xl p-6 text-center hover:shadow-lg transition-shadow">
                        <QuestionMarkCircleIcon class="h-8 w-8 text-orange-500 mx-auto mb-3" aria-hidden="true" />
                        <span class="font-medium text-gray-900">About Us</span>
                    </Link>
                </div>

                <!-- FAQs -->
                <div class="space-y-8">
                    <h2 class="text-2xl font-bold text-gray-900">Frequently Asked Questions</h2>
                    
                    <div v-for="section in faqs" :key="section.category" class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b flex items-center gap-3">
                            <component :is="section.icon" class="h-6 w-6 text-orange-500" aria-hidden="true" />
                            <h3 class="font-semibold text-gray-900">{{ section.category }}</h3>
                        </div>
                        <div class="divide-y">
                            <details v-for="(item, idx) in section.questions" :key="idx" class="group">
                                <summary class="px-6 py-4 cursor-pointer hover:bg-gray-50 flex items-center justify-between">
                                    <span class="font-medium text-gray-900">{{ item.q }}</span>
                                    <svg class="h-5 w-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </summary>
                                <div class="px-6 pb-4 text-gray-600">{{ item.a }}</div>
                            </details>
                        </div>
                    </div>
                </div>

                <!-- Contact Options -->
                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Still need help?</h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div 
                            v-for="option in contactOptions" 
                            :key="option.title"
                            :class="['bg-white rounded-xl p-6 text-center', option.available ? 'hover:shadow-lg cursor-pointer' : 'opacity-60']"
                        >
                            <component :is="option.icon" class="h-10 w-10 text-orange-500 mx-auto mb-3" aria-hidden="true" />
                            <h3 class="font-semibold text-gray-900 mb-1">{{ option.title }}</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ option.desc }}</p>
                            <button 
                                :disabled="!option.available"
                                class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                            >
                                {{ option.available ? option.action : 'Coming Soon' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
