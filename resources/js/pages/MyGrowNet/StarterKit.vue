<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { GiftIcon, CheckCircleIcon, StarIcon, CalendarIcon, PackageIcon, ShoppingBagIcon, BookOpenIcon, VideoIcon, AwardIcon } from 'lucide-vue-next';

interface ContentItem {
    id: number;
    title: string;
    description: string | null;
    category: string;
    unlock_day: number;
    estimated_value: number;
    category_label: string;
}

interface Tier {
    name?: string;
    price: number;
    shopCredit: number;
    lgrMultiplier?: number;
}

interface Props {
    hasStarterKit: boolean;
    hasPendingPayment?: boolean;
    pendingPayment?: {
        amount: number;
        payment_method: string;
        payment_reference: string;
        submitted_at: string;
    };
    tiers?: {
        basic: Tier;
        premium: Tier;
    };
    purchaseUrl?: string;
    contentItems?: Record<string, ContentItem[]>;
    purchase?: {
        invoice_number: string;
        purchased_at: string;
        amount: number;
        days_since_purchase: number;
    };
    shopCredit?: {
        amount: number;
        expiry: string;
        days_remaining: number;
    };
    progress?: {
        total_unlocks: number;
        unlocked: number;
        locked: number;
        next_unlock: any;
    };
    content?: {
        courses: any[];
        videos: any[];
        ebooks: any[];
        tools: any[];
        library: any[];
    };
    achievements?: any[];
    user?: {
        name: string;
        email: string;
        phone: string;
        joined_at: string;
        starter_kit_tier?: string;
    };
}

const props = defineProps<Props>();

const totalContentValue = computed(() => {
    if (!props.contentItems) return 0;
    let total = 0;
    Object.values(props.contentItems).forEach(items => {
        items.forEach(item => {
            total += item.estimated_value;
        });
    });
    return total;
});

// Get basic tier for display (default option)
const basicTier = computed(() => props.tiers?.basic || { price: 500, shopCredit: 100 });
const premiumTier = computed(() => props.tiers?.premium || { price: 1000, shopCredit: 200 });

const getCategoryIcon = (category: string) => {
    const icons: Record<string, string> = {
        training: '📚',
        ebook: '📖',
        video: '🎥',
        tool: '🛠️',
        library: '📚',
    };
    return icons[category] || '📄';
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Head title="My Starter Kit" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">My Starter Kit</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Your welcome package and initial benefits
                    </p>
                </div>

                <!-- Payment Pending Message -->
                <div v-if="!hasStarterKit && hasPendingPayment" class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex items-start">
                        <CalendarIcon class="h-6 w-6 text-blue-600 mt-0.5" />
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-blue-800">Payment Pending Verification</h3>
                            <p class="mt-2 text-sm text-blue-700">
                                Your payment is being verified by our admin team. You'll receive access once confirmed (usually within 24 hours).
                            </p>
                            <div class="mt-4 bg-white rounded-lg p-4 border border-blue-200">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Payment Details</h4>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Amount:</span>
                                        <span class="font-semibold text-gray-900">K{{ pendingPayment?.amount }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Method:</span>
                                        <span class="font-semibold text-gray-900 capitalize">{{ pendingPayment?.payment_method?.replace('_', ' ') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Reference:</span>
                                        <span class="font-semibold text-gray-900">{{ pendingPayment?.payment_reference }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Submitted:</span>
                                        <span class="font-semibold text-gray-900">{{ pendingPayment?.submitted_at }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 flex gap-3">
                                <Link
                                    :href="route('mygrownet.payments.index')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                                >
                                    View Payment History
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Starter Kit Message -->
                <div v-else-if="!hasStarterKit && !hasPendingPayment" class="space-y-6">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-8 text-white">
                        <div class="flex items-center justify-between flex-wrap gap-6">
                            <div>
                                <h2 class="text-3xl font-bold mb-2">MyGrowNet Starter Kit</h2>
                                <p class="text-blue-100 text-lg">Everything you need to succeed on the platform</p>
                                <p class="text-blue-200 mt-2">Total Value: K{{ totalContentValue + basicTier.shopCredit }} • Starting from K{{ basicTier.price }}</p>
                            </div>
                            <Link
                                :href="purchaseUrl"
                                class="inline-flex items-center px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-colors"
                            >
                                Choose Your Tier
                            </Link>
                        </div>
                    </div>

                    <!-- What's Included -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-6">What's Included</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="(items, category) in contentItems"
                                :key="category"
                                class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
                            >
                                <div class="flex items-start gap-3">
                                    <span class="text-3xl">{{ getCategoryIcon(category) }}</span>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 mb-2">
                                            {{ items.length }} {{ items[0]?.category_label || category }}{{ items.length > 1 ? 's' : '' }}
                                        </h4>
                                        <ul class="space-y-1 text-sm text-gray-600">
                                            <li v-for="item in items" :key="item.id" class="flex items-start">
                                                <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                                                <span>{{ item.title }}</span>
                                            </li>
                                        </ul>
                                        <p class="text-xs text-gray-500 mt-2">
                                            Value: K{{ items.reduce((sum, item) => sum + item.estimated_value, 0) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Basic Tier Bonuses -->
                            <div class="border border-green-200 bg-green-50 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <span class="text-3xl">🎁</span>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 mb-2">Basic Tier (K{{ basicTier.price }})</h4>
                                        <ul class="space-y-1 text-sm text-gray-600">
                                            <li class="flex items-start">
                                                <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                                                <span>K{{ basicTier.shopCredit }} Shop Credit (90 days)</span>
                                            </li>
                                            <li class="flex items-start">
                                                <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                                                <span>+37.5 Lifetime Points</span>
                                            </li>
                                            <li class="flex items-start">
                                                <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                                                <span>Full Platform Access</span>
                                            </li>
                                        </ul>
                                        <p class="text-xs text-gray-500 mt-2">Value: K{{ basicTier.shopCredit }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Premium Tier Bonuses -->
                            <div class="border border-purple-200 bg-purple-50 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <span class="text-3xl">✨</span>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 mb-2">Premium Tier (K{{ premiumTier.price }})</h4>
                                        <ul class="space-y-1 text-sm text-gray-600">
                                            <li class="flex items-start">
                                                <CheckCircleIcon class="w-4 h-4 text-purple-500 mr-2 mt-0.5 flex-shrink-0" />
                                                <span>K{{ premiumTier.shopCredit }} Shop Credit (90 days)</span>
                                            </li>
                                            <li class="flex items-start">
                                                <CheckCircleIcon class="w-4 h-4 text-purple-500 mr-2 mt-0.5 flex-shrink-0" />
                                                <span>+37.5 Lifetime Points</span>
                                            </li>
                                            <li class="flex items-start">
                                                <CheckCircleIcon class="w-4 h-4 text-purple-500 mr-2 mt-0.5 flex-shrink-0" />
                                                <span>LGR Qualification 🚀</span>
                                            </li>
                                            <li class="flex items-start">
                                                <CheckCircleIcon class="w-4 h-4 text-purple-500 mr-2 mt-0.5 flex-shrink-0" />
                                                <span>Quarterly Profit Sharing</span>
                                            </li>
                                        </ul>
                                        <p class="text-xs text-gray-500 mt-2">Value: K{{ premiumTier.shopCredit }} + LGR Access</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 text-center">
                            <Link
                                :href="purchaseUrl"
                                class="inline-flex items-center px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Choose Your Tier
                            </Link>
                            <p class="text-sm text-gray-600 mt-3">
                                Basic: K{{ basicTier.price }} • Premium: K{{ premiumTier.price }} (includes LGR qualification)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Starter Kit Content -->
                <div v-else-if="hasStarterKit && purchase" class="space-y-6">
                    <!-- Main Starter Kit Card -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg shadow-lg p-8 text-white">
                        <div class="flex items-start justify-between flex-wrap gap-6">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-3">
                                    <GiftIcon class="h-10 w-10 flex-shrink-0" />
                                    <div>
                                        <h2 class="text-2xl font-bold">MyGrowNet Starter Kit</h2>
                                        <p class="text-blue-100 text-sm mt-1">Your complete onboarding package</p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-wrap gap-4 mt-4">
                                    <div class="flex items-center gap-2">
                                        <CalendarIcon class="h-5 w-5 text-blue-200" />
                                        <span class="text-sm">Purchased: {{ purchase.purchased_at }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <PackageIcon class="h-5 w-5 text-blue-200" />
                                        <span class="text-sm">Invoice: {{ purchase.invoice_number }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex-shrink-0">
                                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-6 text-center min-w-[140px]">
                                    <p class="text-sm text-blue-100 mb-2">Package Value</p>
                                    <p class="text-3xl font-bold">{{ formatCurrency(purchase.amount) }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Access -->
                        <div class="mt-6 flex gap-3">
                            <Link
                                :href="route('mygrownet.library.index')"
                                class="flex items-center px-4 py-2 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-colors"
                            >
                                <BookOpenIcon class="w-5 h-5 mr-2" />
                                Access Library
                            </Link>
                        </div>
                    </div>
                    
                    <!-- Upgrade to Premium Banner (for Basic tier members) -->
                    <div v-if="user && user.starter_kit_tier === 'basic'" class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-start justify-between flex-wrap gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <StarIcon class="h-6 w-6 text-yellow-300" />
                                    <h3 class="text-xl font-bold">Upgrade to Premium</h3>
                                </div>
                                <p class="text-purple-100 mb-4">Unlock LGR quarterly profit sharing and enhanced benefits for only K500!</p>
                                <ul class="space-y-2 text-sm text-purple-100">
                                    <li class="flex items-center gap-2">
                                        <CheckCircleIcon class="w-4 h-4 text-green-300" />
                                        <span>LGR Qualification - Quarterly profit sharing</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckCircleIcon class="w-4 h-4 text-green-300" />
                                        <span>Additional K100 shop credit</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckCircleIcon class="w-4 h-4 text-green-300" />
                                        <span>Priority support access</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="flex-shrink-0">
                                <Link
                                    :href="route('mygrownet.starter-kit.upgrade')"
                                    class="inline-flex items-center px-6 py-3 bg-white text-purple-600 font-bold rounded-lg hover:bg-purple-50 transition-colors shadow-lg"
                                >
                                    <StarIcon class="w-5 h-5 mr-2" />
                                    Upgrade Now
                                </Link>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shop Credit Card -->
                    <div v-if="shopCredit" class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <ShoppingBagIcon class="h-6 w-6 text-green-600" />
                            Shop Credit
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-green-50 rounded-lg p-4">
                                <p class="text-sm text-green-600 font-medium">Available Credit</p>
                                <p class="text-3xl font-bold text-green-900 mt-2">{{ formatCurrency(shopCredit.amount) }}</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4">
                                <p class="text-sm text-blue-600 font-medium">Expires On</p>
                                <p class="text-xl font-bold text-blue-900 mt-2">{{ shopCredit.expiry }}</p>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-4">
                                <p class="text-sm text-purple-600 font-medium">Days Remaining</p>
                                <p class="text-3xl font-bold text-purple-900 mt-2">{{ shopCredit.days_remaining }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Card -->
                    <div v-if="progress" class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Progress</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <p class="text-sm text-blue-600 font-medium">Unlocked Content</p>
                                <p class="text-3xl font-bold text-blue-900 mt-2">{{ progress.unlocked }}</p>
                                <p class="text-xs text-blue-600 mt-1">of {{ progress.total_unlocks }} items</p>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-4">
                                <p class="text-sm text-yellow-600 font-medium">Locked Content</p>
                                <p class="text-3xl font-bold text-yellow-900 mt-2">{{ progress.locked }}</p>
                                <p class="text-xs text-yellow-600 mt-1">Coming soon</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4">
                                <p class="text-sm text-green-600 font-medium">Days Active</p>
                                <p class="text-3xl font-bold text-green-900 mt-2">{{ purchase.days_since_purchase }}</p>
                                <p class="text-xs text-green-600 mt-1">Since purchase</p>
                            </div>
                        </div>
                    </div>

                    <!-- Available Content -->
                    <div v-if="content" class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Content</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Courses -->
                            <div v-if="content.courses && content.courses.length > 0">
                                <h4 class="font-medium text-gray-900 mb-3 flex items-center gap-2">
                                    <BookOpenIcon class="h-5 w-5 text-blue-600" />
                                    Training Modules ({{ content.courses.length }})
                                </h4>
                                <div class="space-y-2">
                                    <div v-for="course in content.courses" :key="course.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm text-gray-700">{{ course.name }}</span>
                                        <CheckCircleIcon v-if="course.viewed" class="h-5 w-5 text-green-600" />
                                    </div>
                                </div>
                            </div>

                            <!-- eBooks -->
                            <div v-if="content.ebooks && content.ebooks.length > 0">
                                <h4 class="font-medium text-gray-900 mb-3 flex items-center gap-2">
                                    <BookOpenIcon class="h-5 w-5 text-green-600" />
                                    Premium eBooks ({{ content.ebooks.length }})
                                </h4>
                                <div class="space-y-2">
                                    <div v-for="ebook in content.ebooks" :key="ebook.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm text-gray-700">{{ ebook.name }}</span>
                                        <CheckCircleIcon v-if="ebook.viewed" class="h-5 w-5 text-green-600" />
                                    </div>
                                </div>
                            </div>

                            <!-- Videos -->
                            <div v-if="content.videos && content.videos.length > 0">
                                <h4 class="font-medium text-gray-900 mb-3 flex items-center gap-2">
                                    <VideoIcon class="h-5 w-5 text-purple-600" />
                                    Video Tutorials ({{ content.videos.length }})
                                </h4>
                                <div class="space-y-2">
                                    <div v-for="video in content.videos" :key="video.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm text-gray-700">{{ video.name }}</span>
                                        <CheckCircleIcon v-if="video.viewed" class="h-5 w-5 text-green-600" />
                                    </div>
                                </div>
                            </div>

                            <!-- Marketing Tools -->
                            <div v-if="content.tools && content.tools.length > 0">
                                <h4 class="font-medium text-gray-900 mb-3 flex items-center gap-2">
                                    <PackageIcon class="h-5 w-5 text-orange-600" />
                                    Marketing Tools ({{ content.tools.length }})
                                </h4>
                                <div class="space-y-2">
                                    <div v-for="tool in content.tools" :key="tool.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm text-gray-700">{{ tool.name }}</span>
                                        <CheckCircleIcon v-if="tool.viewed" class="h-5 w-5 text-green-600" />
                                    </div>
                                </div>
                            </div>

                            <!-- Digital Library -->
                            <div v-if="content.library && content.library.length > 0" class="md:col-span-2">
                                <h4 class="font-medium text-gray-900 mb-3 flex items-center gap-2">
                                    <BookOpenIcon class="h-5 w-5 text-indigo-600" />
                                    Digital Library ({{ content.library.length }})
                                </h4>
                                <div class="space-y-2">
                                    <div v-for="item in content.library" :key="item.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm text-gray-700">{{ item.name }}</span>
                                        <CheckCircleIcon v-if="item.viewed" class="h-5 w-5 text-green-600" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Achievements -->
                    <div v-if="achievements && achievements.length > 0" class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <AwardIcon class="h-6 w-6 text-yellow-500" />
                            Your Achievements
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="achievement in achievements" :key="achievement.id" class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                                <span class="text-3xl">{{ achievement.icon }}</span>
                                <div>
                                    <p class="font-medium text-gray-900">{{ achievement.name }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ achievement.description }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ achievement.earned_at }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-blue-900 mb-3">Next Steps</h3>
                        <ul class="space-y-2 text-sm text-blue-800">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 mt-0.5">→</span>
                                <span>Complete your profile to unlock more features</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 mt-0.5">→</span>
                                <span>Explore workshops and training to earn more points</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 mt-0.5">→</span>
                                <span>Invite friends to grow your network and earn commissions</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 mt-0.5">→</span>
                                <span>Check your wallet regularly for earnings and bonuses</span>
                            </li>
                        </ul>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <Link
                                :href="route('mygrownet.workshops.index')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Browse Workshops
                            </Link>
                            <Link
                                :href="route('my-team.index')"
                                class="inline-flex items-center px-4 py-2 bg-white text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50 transition-colors"
                            >
                                Invite Friends
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
