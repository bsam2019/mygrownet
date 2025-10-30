<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';
import { 
    BriefcaseIcon, 
    UsersIcon, 
    CalendarIcon, 
    DocumentTextIcon,
    ArrowLeftIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';

interface Venture {
    id: number;
    title: string;
    slug: string;
    description: string;
    business_model: string | null;
    funding_target: number;
    total_raised: number;
    minimum_investment: number;
    maximum_investment: number | null;
    investor_count: number;
    status: string;
    expected_roi_months: number | null;
    risk_factors: string | null;
    funding_start_date: string | null;
    funding_end_date: string | null;
    expected_launch_date: string | null;
    category: {
        name: string;
        icon: string;
    };
}

const props = defineProps<{
    venture: Venture;
    userInvestment?: any;
    fundingProgress: number;
    auth?: {
        user?: any;
    };
}>();

const isAuthenticated = computed(() => !!props.auth?.user);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string | null) => {
    if (!date) return 'TBD';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head :title="venture.title" />

    <div class="min-h-screen bg-gray-50">
        <Navigation />
        
        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Back Button -->
                <Link
                    :href="route('ventures.index')"
                    class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6"
                >
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to Marketplace
                </Link>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Header -->
                        <div class="rounded-lg bg-white p-8 shadow">
                            <div class="mb-4">
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800">
                                    {{ venture.category.name }}
                                </span>
                            </div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ venture.title }}</h1>
                            <p class="mt-4 text-lg text-gray-600">{{ venture.description }}</p>
                        </div>

                        <!-- Business Model -->
                        <div v-if="venture.business_model" class="rounded-lg bg-white p-8 shadow">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Business Model</h2>
                            <p class="text-gray-700 whitespace-pre-line">{{ venture.business_model }}</p>
                        </div>

                        <!-- Investment Highlights -->
                        <div class="rounded-lg bg-white p-8 shadow">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Investment Highlights</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex items-start gap-3">
                                    <CheckCircleIcon class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" />
                                    <div>
                                        <div class="font-semibold text-gray-900">Minimum Investment</div>
                                        <div class="text-gray-600">{{ formatCurrency(venture.minimum_investment) }}</div>
                                    </div>
                                </div>
                                <div v-if="venture.expected_roi_months" class="flex items-start gap-3">
                                    <CheckCircleIcon class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" />
                                    <div>
                                        <div class="font-semibold text-gray-900">Expected ROI</div>
                                        <div class="text-gray-600">{{ venture.expected_roi_months }} months</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <CheckCircleIcon class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" />
                                    <div>
                                        <div class="font-semibold text-gray-900">Current Investors</div>
                                        <div class="text-gray-600">{{ venture.investor_count }} members</div>
                                    </div>
                                </div>
                                <div v-if="venture.expected_launch_date" class="flex items-start gap-3">
                                    <CheckCircleIcon class="h-6 w-6 text-green-600 flex-shrink-0 mt-1" />
                                    <div>
                                        <div class="font-semibold text-gray-900">Expected Launch</div>
                                        <div class="text-gray-600">{{ formatDate(venture.expected_launch_date) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Risk Factors -->
                        <div v-if="venture.risk_factors" class="rounded-lg bg-amber-50 border border-amber-200 p-8">
                            <h2 class="text-xl font-semibold text-amber-900 mb-4">Risk Factors</h2>
                            <p class="text-amber-800 whitespace-pre-line">{{ venture.risk_factors }}</p>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Funding Progress -->
                        <div class="rounded-lg bg-white p-6 shadow sticky top-6">
                            <div class="mb-6">
                                <div class="mb-2 flex justify-between text-sm">
                                    <span class="text-gray-600">Funding Progress</span>
                                    <span class="font-semibold text-gray-900">{{ Math.round(fundingProgress) }}%</span>
                                </div>
                                <div class="h-3 w-full rounded-full bg-gray-200">
                                    <div
                                        class="h-3 rounded-full bg-green-600 transition-all"
                                        :style="{ width: `${fundingProgress}%` }"
                                    ></div>
                                </div>
                                <div class="mt-2 flex justify-between text-sm text-gray-500">
                                    <span>{{ formatCurrency(venture.total_raised) }}</span>
                                    <span>{{ formatCurrency(venture.funding_target) }}</span>
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="space-y-4 border-t border-gray-200 pt-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <UsersIcon class="h-5 w-5" />
                                        <span class="text-sm">Investors</span>
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ venture.investor_count }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <BriefcaseIcon class="h-5 w-5" />
                                        <span class="text-sm">Min. Investment</span>
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ formatCurrency(venture.minimum_investment) }}</span>
                                </div>
                                <div v-if="venture.funding_end_date" class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <CalendarIcon class="h-5 w-5" />
                                        <span class="text-sm">Closes</span>
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ formatDate(venture.funding_end_date) }}</span>
                                </div>
                            </div>

                            <!-- CTA -->
                            <div class="mt-6 border-t border-gray-200 pt-6">
                                <template v-if="isAuthenticated">
                                    <Link
                                        v-if="!userInvestment"
                                        :href="route('mygrownet.ventures.invest', venture.id)"
                                        class="block w-full rounded-lg bg-blue-600 px-6 py-3 text-center text-sm font-semibold text-white hover:bg-blue-500 transition-colors"
                                    >
                                        Invest Now
                                    </Link>
                                    <div v-else class="rounded-lg bg-green-50 border border-green-200 p-4 text-center">
                                        <CheckCircleIcon class="h-8 w-8 text-green-600 mx-auto mb-2" />
                                        <p class="text-sm font-semibold text-green-900">You're an Investor!</p>
                                        <p class="text-xs text-green-700 mt-1">{{ formatCurrency(userInvestment.amount) }} invested</p>
                                    </div>
                                </template>
                                <template v-else>
                                    <Link
                                        :href="route('register')"
                                        class="block w-full rounded-lg bg-blue-600 px-6 py-3 text-center text-sm font-semibold text-white hover:bg-blue-500 transition-colors mb-3"
                                    >
                                        Join to Invest
                                    </Link>
                                    <Link
                                        :href="route('login')"
                                        class="block w-full rounded-lg bg-white px-6 py-3 text-center text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
                                    >
                                        Already a Member? Login
                                    </Link>
                                </template>
                            </div>

                            <!-- Trust Badge -->
                            <div class="mt-6 rounded-lg bg-blue-50 p-4">
                                <p class="text-xs text-center text-blue-900">
                                    <strong>Secure Investment</strong><br>
                                    All ventures are vetted and approved by MyGrowNet
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>
