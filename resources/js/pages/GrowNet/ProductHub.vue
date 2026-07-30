<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import {
    PlayCircleIcon, AcademicCapIcon, DocumentTextIcon,
    CloudArrowUpIcon, CpuChipIcon, UsersIcon,
    RocketLaunchIcon, ChevronRightIcon, UserCircleIcon,
    Cog6ToothIcon, ArrowRightOnRectangleIcon,
    UserGroupIcon, CreditCardIcon, BellIcon,
    ChartBarIcon, GiftIcon, ChevronDownIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => (page.props as any).user ?? {});
const stats = computed(() => (page.props as any).stats ?? {});

const dropdownOpen = ref(false);
const dropdownEl = ref<HTMLElement | null>(null);

function toggleDropdown() { dropdownOpen.value = !dropdownOpen.value; }

function onClickOutside(e: MouseEvent) {
    if (dropdownEl.value && !dropdownEl.value.contains(e.target as Node)) {
        dropdownOpen.value = false;
    }
}

function logout() {
    router.post(route('logout'));
}

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));

const products = [
    {
        slug: 'growstream', name: 'GrowStream', tagline: 'Film & Comedy',
        icon: PlayCircleIcon, gradient: 'from-fuchsia-500 to-pink-600',
        bg: 'bg-fuchsia-50', text: 'text-fuchsia-600',
        description: 'Zambian movies, comedy shows, and drama series from local creators.',
        status: 'Coming Soon',
    },
    {
        slug: 'growlearn', name: 'GrowLearn', tagline: 'Practical Skills',
        icon: AcademicCapIcon, gradient: 'from-violet-500 to-purple-600',
        bg: 'bg-violet-50', text: 'text-violet-600',
        description: 'Locally relevant skills training with live workshops.',
        status: 'Coming Soon',
    },
    {
        slug: 'growtemplates', name: 'GrowTemplates', tagline: 'Documents & Templates',
        icon: DocumentTextIcon, gradient: 'from-amber-500 to-orange-600',
        bg: 'bg-amber-50', text: 'text-amber-600',
        description: 'Business plans, CVs, invoices & more — one-time or subscribe.',
        status: 'Coming Soon',
    },
    {
        slug: 'growbackup', name: 'GrowBackup', tagline: 'Cloud Storage',
        icon: CloudArrowUpIcon, gradient: 'from-sky-500 to-blue-600',
        bg: 'bg-sky-50', text: 'text-sky-600',
        description: 'Secure file storage, accessible from anywhere.',
        status: 'Coming Soon',
    },
    {
        slug: 'growai', name: 'GrowAI', tagline: 'AI Tools',
        icon: CpuChipIcon, gradient: 'from-emerald-500 to-teal-600',
        bg: 'bg-emerald-50', text: 'text-emerald-600',
        description: 'AI-powered content creation & productivity tools.',
        status: 'Coming Soon',
    },
];
</script>

<template>
    <Head title="GrowNet" />

    <div class="min-h-screen bg-slate-50">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-violet-600 to-fuchsia-600 flex items-center justify-center shadow-sm">
                            <RocketLaunchIcon class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h1 class="text-base font-bold text-slate-900 leading-tight">GrowNet</h1>
                            <p class="text-[11px] text-slate-500 leading-tight">Product Platform</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('grownet.network')"
                            class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
                        >
                            <UserGroupIcon class="w-4 h-4" />
                            Network
                        </Link>

                        <button class="relative w-9 h-9 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                            <BellIcon class="w-5 h-5" />
                        </button>

                        <div ref="dropdownEl" class="relative">
                            <button @click="toggleDropdown"
                                class="flex items-center gap-2 pl-2 pr-2.5 py-1.5 rounded-lg hover:bg-slate-100 transition-colors border border-transparent hover:border-slate-200"
                            >
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                    {{ (user.name || 'U').charAt(0).toUpperCase() }}
                                </div>
                                <ChevronDownIcon class="w-3.5 h-3.5 text-slate-400" :class="{ 'rotate-180': dropdownOpen }" />
                            </button>

                            <div v-if="dropdownOpen"
                                class="absolute right-0 top-full mt-1.5 w-56 bg-white rounded-xl shadow-lg border border-slate-200 py-1.5 z-50"
                            >
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ user.name }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ user.email }}</p>
                                </div>
                                <Link :href="route('workspace')"
                                    class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors"
                                >
                                    <RocketLaunchIcon class="w-4 h-4 text-slate-400" />
                                    Workspace
                                </Link>
                                <Link :href="route('grownet.network')"
                                    class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors sm:hidden"
                                >
                                    <UsersIcon class="w-4 h-4 text-slate-400" />
                                    Network Dashboard
                                </Link>
                                <button @click="logout"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                >
                                    <ArrowRightOnRectangleIcon class="w-4 h-4" />
                                    Sign Out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900">Welcome back{{ user.name ? ', ' + user.name.split(' ')[0] : '' }}</h2>
                <p class="text-sm text-slate-500 mt-1">Access all GrowNet products from one place.</p>
            </div>

            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-slate-900">Products</h3>
                <span class="text-xs text-slate-400">5 products</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                <div
                    v-for="product in products"
                    :key="product.slug"
                    class="group bg-white rounded-xl border border-slate-200 p-5 hover:border-slate-300 hover:shadow-md transition-all cursor-pointer"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-sm transition-transform group-hover:scale-110" :class="product.bg">
                            <component :is="product.icon" class="w-5.5 h-5.5" :class="product.text" />
                        </div>
                        <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full shrink-0">{{ product.status }}</span>
                    </div>
                    <h4 class="text-sm font-semibold text-slate-900">{{ product.name }}</h4>
                    <p class="text-xs text-slate-500 mt-0.5">{{ product.tagline }}</p>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">{{ product.description }}</p>
                </div>
            </div>

            <details class="group bg-white rounded-xl border border-slate-200 mb-6 open:shadow-sm transition-shadow">
                <summary class="flex items-center justify-between p-4 cursor-pointer list-none text-sm font-medium text-slate-600 hover:text-slate-900">
                    <span class="flex items-center gap-2">
                        <ChartBarIcon class="w-4 h-4 text-slate-400" />
                        Network Stats
                    </span>
                    <ChevronDownIcon class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform" />
                </summary>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-slate-100 border-t border-slate-200">
                    <div class="bg-white p-4">
                        <p class="text-xs text-slate-500">Referrals</p>
                        <p class="text-lg font-bold text-violet-600">{{ stats.referrals ?? 0 }}</p>
                        <p class="text-[10px] text-slate-400">Direct referrals</p>
                    </div>
                    <div class="bg-white p-4">
                        <p class="text-xs text-slate-500">Earnings</p>
                        <p class="text-lg font-bold text-fuchsia-600">K{{ stats.earnings || '0' }}</p>
                        <p class="text-[10px] text-slate-400">Total earned</p>
                    </div>
                    <div class="bg-white p-4">
                        <p class="text-xs text-slate-500">Team</p>
                        <p class="text-lg font-bold text-amber-600">{{ stats.team ?? 0 }}</p>
                        <p class="text-[10px] text-slate-400">Network size</p>
                    </div>
                    <div class="bg-white p-4">
                        <p class="text-xs text-slate-500">Tier</p>
                        <p class="text-lg font-bold text-emerald-600">{{ stats.tier || '—' }}</p>
                        <p class="text-[10px] text-slate-400">Current rank</p>
                    </div>
                </div>
            </details>

            <div class="bg-gradient-to-r from-violet-600 to-fuchsia-600 rounded-xl p-5 sm:p-6 text-white">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                            <UserGroupIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Network Dashboard</p>
                            <p class="text-xs text-white/70">Track referrals, commissions, and team performance</p>
                        </div>
                    </div>
                    <Link :href="route('grownet.network')"
                        class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-white text-violet-700 text-sm font-medium rounded-lg hover:bg-violet-50 transition-colors shadow-sm"
                    >
                        Open
                        <ChevronRightIcon class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </main>
    </div>
</template>
