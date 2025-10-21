<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { GiftIcon, CheckCircleIcon, StarIcon, CalendarIcon, PackageIcon } from 'lucide-vue-next';

interface Props {
    starterKit: {
        received: boolean;
        package_name: string;
        package_description: string;
        received_date: string;
        features: string[];
        status: string;
        amount: number;
        start_date: string;
        end_date: string;
    } | null;
    initialPoints: {
        lp_amount: number;
        map_amount: number;
        awarded_at: string;
    } | null;
    user: {
        name: string;
        email: string;
        phone: string;
        joined_at: string;
    };
}

const props = defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        active: 'bg-green-100 text-green-800',
        expired: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
        pending: 'bg-yellow-100 text-yellow-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
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

                <!-- No Starter Kit Message -->
                <div v-if="!starterKit" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="flex items-start">
                        <GiftIcon class="h-6 w-6 text-yellow-600 mt-0.5" />
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">No Starter Kit Found</h3>
                            <p class="mt-2 text-sm text-yellow-700">
                                You haven't received a starter kit yet. Starter kits are automatically assigned when you register.
                                If you believe this is an error, please contact support.
                            </p>
                            <div class="mt-4">
                                <Link
                                    :href="route('dashboard')"
                                    class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors"
                                >
                                    Go to Dashboard
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Starter Kit Content -->
                <div v-else class="space-y-6">
                    <!-- Main Starter Kit Card -->
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg shadow-lg p-8 text-white">
                        <div class="flex items-start justify-between flex-wrap gap-6">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-3">
                                    <GiftIcon class="h-10 w-10 flex-shrink-0" />
                                    <div>
                                        <h2 class="text-2xl font-bold">{{ starterKit.package_name }}</h2>
                                        <p class="text-purple-100 text-sm mt-1">{{ starterKit.package_description }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-wrap gap-4 mt-4">
                                    <div class="flex items-center gap-2">
                                        <CalendarIcon class="h-5 w-5 text-purple-200" />
                                        <span class="text-sm">Received: {{ starterKit.received_date }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <PackageIcon class="h-5 w-5 text-purple-200" />
                                        <span :class="['text-xs font-semibold px-3 py-1 rounded-full', getStatusColor(starterKit.status)]">
                                            {{ starterKit.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex-shrink-0">
                                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-6 text-center min-w-[140px]">
                                    <p class="text-sm text-purple-100 mb-2">Package Value</p>
                                    <p class="text-3xl font-bold">{{ formatCurrency(starterKit.amount) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Points Awarded Card -->
                    <div v-if="initialPoints" class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <StarIcon class="h-6 w-6 text-yellow-500" />
                            Initial Points Bonus
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <p class="text-sm text-blue-600 font-medium">Life Points (LP)</p>
                                <p class="text-3xl font-bold text-blue-900 mt-2">{{ initialPoints.lp_amount }}</p>
                                <p class="text-xs text-blue-600 mt-1">Never expires</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4">
                                <p class="text-sm text-green-600 font-medium">Monthly Activity Points</p>
                                <p class="text-3xl font-bold text-green-900 mt-2">{{ initialPoints.map_amount }}</p>
                                <p class="text-xs text-green-600 mt-1">Resets monthly</p>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-4">
                                <p class="text-sm text-purple-600 font-medium">Awarded On</p>
                                <p class="text-xl font-bold text-purple-900 mt-2">{{ initialPoints.awarded_at }}</p>
                                <p class="text-xs text-purple-600 mt-1">Registration bonus</p>
                            </div>
                        </div>
                    </div>

                    <!-- Package Features -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">What's Included</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="(feature, index) in starterKit.features"
                                :key="index"
                                class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg"
                            >
                                <CheckCircleIcon class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                                <span class="text-sm text-gray-700">{{ feature }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Membership Period -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Membership Period</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-1">Start Date</p>
                                <p class="text-lg font-semibold text-gray-900">{{ starterKit.start_date }}</p>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-1">End Date</p>
                                <p class="text-lg font-semibold text-gray-900">{{ starterKit.end_date }}</p>
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
