<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed } from 'vue';
import {
    BuildingOfficeIcon,
    PlusIcon,
    ArrowRightIcon,
    ArrowLeftIcon,
    ArrowRightOnRectangleIcon,
    HomeIcon,
    SparklesIcon,
    ShieldCheckIcon,
    ClockIcon,
    ChartBarIcon,
    UsersIcon,
    DocumentTextIcon,
    StarIcon as StarOutline,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarSolid } from '@heroicons/vue/24/solid';

interface Metrics {
    active_jobs: number;
    pending_invoices: number;
    outstanding: number;
    monthly_revenue: number;
    total_customers: number;
}

interface Company {
    company_id: number;
    company_name: string;
    industry: string | null;
    logo: string | null;
    role: string;
    status: string;
    metrics: Metrics;
}

const props = defineProps<{
    companies: Company[];
    defaultCompanyId: number | null;
}>();

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user ?? null);
const firstName = computed(() => {
    const name: string = user.value?.name ?? '';
    return name.split(' ')[0] || name || 'there';
});

const isSubdomain = typeof window !== 'undefined' && window.location.hostname !== 'mygrownet.com';
const mainAppUrl = isSubdomain ? 'https://mygrownet.com/workspace' : '/workspace';

const fmt = (n: number) => n.toLocaleString('en-ZM', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function enter(companyId: number) {
    router.post(route('bms.companies.enter', companyId));
}

function toggleDefault(companyId: number) {
    const newId = props.defaultCompanyId === companyId ? null : companyId;
    router.post(route('bms.companies.set-default'), {
        company_id: newId,
    });
}

function signOut() {
    router.post(route('bms.logout'));
}

const features = [
    { icon: DocumentTextIcon, title: 'Invoicing & Payments', desc: 'Create professional invoices, track payments, and get paid faster.' },
    { icon: ChartBarIcon, title: 'Business Analytics', desc: 'Real-time revenue, expense, and performance insights at a glance.' },
    { icon: UsersIcon, title: 'Customers & CRM', desc: 'Manage contacts, history, and follow-ups all in one place.' },
    { icon: ClockIcon, title: 'Jobs & Projects', desc: 'Track jobs from quote to completion with deadlines and statuses.' },
];

const steps = [
    { title: 'Create your company', desc: 'Set up your business profile in under a minute — no card required.' },
    { title: 'Invite your team', desc: 'Add teammates with role-based access and permissions.' },
    { title: 'Run your business', desc: 'Invoice, track jobs, manage inventory, and grow with confidence.' },
];
</script>

<template>
    <Head title="My Companies - GrowSuite" />

    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Top navigation -->
        <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-200">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="/bms" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                        <BuildingOfficeIcon class="h-5 w-5 text-white" aria-hidden="true" />
                    </div>
                    <div class="leading-tight">
                        <p class="text-sm font-bold text-gray-900">GrowSuite</p>
                        <p class="text-[11px] text-gray-500">Business Management Suite</p>
                    </div>
                </a>

                <div class="flex items-center gap-2 sm:gap-3">
                    <a
                        :href="mainAppUrl"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition"
                    >
                        <HomeIcon class="h-4 w-4" aria-hidden="true" />
                        All Apps
                    </a>
                    <button
                        type="button"
                        @click="signOut"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                    >
                        <ArrowRightOnRectangleIcon class="h-4 w-4" aria-hidden="true" />
                        <span class="hidden sm:inline">Sign out</span>
                    </button>
                </div>
            </div>
        </header>

        <main class="flex-1 w-full">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <!-- Welcome / hero -->
                <div class="mb-10">
                    <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 uppercase tracking-wide mb-2">
                        <SparklesIcon class="h-4 w-4" aria-hidden="true" />
                        Welcome to GrowSuite
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 leading-tight">
                        Hi {{ firstName }}, let's get your business set up.
                    </h1>
                    <p class="mt-3 text-base text-gray-600 max-w-2xl leading-relaxed">
                        GrowSuite is the complete business management suite for growing SMEs — jobs, customers,
                        invoicing, inventory, payroll, and analytics, all in one place. Your 14-day free trial is
                        already active; no credit card needed.
                    </p>
                </div>

                <!-- Existing companies -->
                <template v-if="companies.length > 0">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Your Companies</h2>
                        <span class="text-sm text-gray-500">{{ companies.length }} {{ companies.length === 1 ? 'workspace' : 'workspaces' }}</span>
                    </div>

                    <div class="space-y-3 mb-8">
                        <div
                            v-for="c in companies"
                            :key="c.company_id"
                            class="group flex items-center gap-3 bg-white rounded-xl border border-gray-200 shadow-sm hover:border-blue-400 hover:shadow-md transition"
                        >
                            <!-- Default star toggle -->
                            <button
                                @click.stop="toggleDefault(c.company_id)"
                                class="ml-4 flex-shrink-0 text-gray-300 hover:text-yellow-400 transition"
                                :title="c.company_id === defaultCompanyId ? 'Remove as default' : 'Set as default'"
                            >
                                <StarSolid v-if="c.company_id === defaultCompanyId" class="h-5 w-5 text-yellow-400" aria-hidden="true" />
                                <StarOutline v-else class="h-5 w-5" aria-hidden="true" />
                            </button>

                            <!-- Company button -->
                            <button
                                @click="enter(c.company_id)"
                                class="flex-1 flex items-center gap-4 py-5 pr-5 text-left"
                            >
                                <!-- Logo / initials -->
                                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition">
                                    <img v-if="c.logo" :src="c.logo" :alt="c.company_name" class="w-12 h-12 rounded-xl object-cover" />
                                    <span v-else class="text-lg font-bold text-blue-600 group-hover:text-white transition">
                                        {{ c.company_name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-base font-semibold text-gray-900 truncate">{{ c.company_name }}</p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ c.role }}
                                        <span v-if="c.industry" class="ml-2 text-gray-400">· {{ c.industry.replace(/_/g, ' ') }}</span>
                                        <span v-if="c.company_id === defaultCompanyId" class="ml-2 text-xs text-yellow-600 font-medium">Default</span>
                                    </p>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs">
                                        <span class="text-blue-600 font-medium">{{ c.metrics.active_jobs }}</span>
                                        <span class="text-gray-400">jobs</span>
                                        <span class="text-amber-600 font-medium">{{ c.metrics.pending_invoices }}</span>
                                        <span class="text-gray-400">pending</span>
                                        <span class="text-red-600 font-medium">K{{ fmt(c.metrics.outstanding) }}</span>
                                        <span class="text-gray-400">due</span>
                                        <span class="text-green-600 font-medium">K{{ fmt(c.metrics.monthly_revenue) }}</span>
                                        <span class="text-gray-400">rev</span>
                                        <span class="text-gray-600 font-medium">{{ c.metrics.total_customers }}</span>
                                        <span class="text-gray-400">cust</span>
                                    </div>
                                </div>

                                <ArrowRightIcon class="h-5 w-5 text-gray-400 group-hover:text-blue-600 transition flex-shrink-0" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Empty state: first-time signup -->
                <template v-else>
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
                        <div class="p-8 sm:p-10">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center flex-shrink-0">
                                    <BuildingOfficeIcon class="h-6 w-6 text-white" aria-hidden="true" />
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">Create your first company</h2>
                                    <p class="mt-1.5 text-sm text-gray-600 leading-relaxed">
                                        You're almost there. Set up your business profile and you'll have a fully working
                                        workspace — jobs, invoices, customers, inventory and more — ready in minutes.
                                    </p>
                                </div>
                            </div>

                            <!-- Onboarding steps -->
                            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div
                                    v-for="(s, i) in steps"
                                    :key="s.title"
                                    class="relative p-4 rounded-xl border border-gray-100 bg-gray-50"
                                >
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">{{ i + 1 }}</span>
                                        <p class="text-sm font-semibold text-gray-900">{{ s.title }}</p>
                                    </div>
                                    <p class="text-xs text-gray-500 leading-relaxed">{{ s.desc }}</p>
                                </div>
                            </div>

                            <!-- CTA -->
                            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                                <Link
                                    :href="route('bms.companies.create')"
                                    class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white rounded-xl px-6 py-3.5 font-semibold hover:bg-blue-700 transition shadow-lg hover:shadow-xl"
                                >
                                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                                    Create Your Company
                                </Link>
                                <a
                                    :href="mainAppUrl"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl border border-gray-300 bg-white text-gray-700 font-semibold hover:bg-gray-50 transition"
                                >
                                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                                    Explore Other Apps
                                </a>
                            </div>

                            <p class="mt-4 flex items-center gap-1.5 text-xs text-gray-400">
                                <ShieldCheckIcon class="h-4 w-4 text-green-500" aria-hidden="true" />
                                14-day free trial · No credit card required · Cancel anytime
                            </p>
                        </div>
                    </div>

                    <!-- Feature highlights -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Everything you get with GrowSuite</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div
                                v-for="f in features"
                                :key="f.title"
                                class="flex items-start gap-3 p-5 bg-white rounded-xl border border-gray-200 shadow-sm"
                            >
                                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                    <component :is="f.icon" class="h-5 w-5 text-blue-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ f.title }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 leading-relaxed">{{ f.desc }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Create another company (secondary, always visible) -->
                <div v-if="companies.length > 0" class="mb-8">
                    <Link
                        :href="route('bms.companies.create')"
                        class="w-full flex items-center justify-center gap-2 border-2 border-dashed border-gray-300 text-gray-500 rounded-xl py-4 font-semibold hover:border-blue-400 hover:text-blue-600 transition"
                    >
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Create New Company
                    </Link>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-white">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-400">
                    © {{ new Date().getFullYear() }} GrowSuite by MyGrowNet. All rights reserved.
                </p>
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <a href="mailto:support@mygrownet.com" class="hover:text-gray-800 transition">Support</a>
                    <a href="#" class="hover:text-gray-800 transition">Privacy</a>
                    <a href="#" class="hover:text-gray-800 transition">Terms</a>
                </div>
            </div>
        </footer>
    </div>
</template>
