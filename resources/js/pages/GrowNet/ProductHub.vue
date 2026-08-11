<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import {
    PlayCircleIcon, AcademicCapIcon, DocumentTextIcon,
    MusicalNoteIcon, CpuChipIcon, UsersIcon,
    RocketLaunchIcon, ChevronRightIcon, UserCircleIcon,
    Cog6ToothIcon, ArrowRightOnRectangleIcon,
    UserGroupIcon, CreditCardIcon, BellIcon,
    ChartBarIcon, GiftIcon, ChevronDownIcon, FolderIcon,
    SparklesIcon, WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline';
import GrowNetHeaderBadge from '@/Components/GrowNet/GrowNetHeaderBadge.vue';

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

// Product Ecosystem aligned 100% with Section 1A of the GrowNet Specification
const products = [
    {
        slug: 'education',
        name: 'Education & Workshops',
        tagline: '7 Education Levels & Regional Workshops',
        icon: AcademicCapIcon,
        gradient: 'from-indigo-600 to-purple-600',
        bg: 'bg-indigo-50',
        text: 'text-indigo-600',
        description: 'Native multi-format curriculum (video, audio, workshops, practical tasks, oral & written assessments).',
        url: route('grownet.learning.index'),
        status: 'Active',
        external: false,
    },
    {
        slug: 'growstream',
        name: 'GrowStream',
        tagline: 'Video & Film Streaming',
        icon: PlayCircleIcon,
        gradient: 'from-purple-600 to-fuchsia-600',
        bg: 'bg-purple-50',
        text: 'text-purple-600',
        description: 'Zambian movies, series, shows, and creator channels with SSO authentication.',
        url: 'https://growstream.mygrownet.com',
        status: 'Active',
        external: true,
    },
    {
        slug: 'growmusic',
        name: 'GrowMusic',
        tagline: 'Music & Audio Portal',
        icon: MusicalNoteIcon,
        gradient: 'from-rose-500 to-pink-600',
        bg: 'bg-rose-50',
        text: 'text-rose-600',
        description: 'Audio tracks, podcasts, artist fan hubs, and educational audio streams.',
        url: 'https://growmusic.mygrownet.com',
        status: 'Active',
        external: true,
    },
    {
        slug: 'library',
        name: 'Digital Resource Library',
        tagline: 'Kit Resources & Downloads',
        icon: FolderIcon,
        gradient: 'from-emerald-600 to-teal-600',
        bg: 'bg-emerald-50',
        text: 'text-emerald-600',
        description: 'Downloadable training manuals, PDFs, starter kit materials, and business guides.',
        url: route('grownet.sub.library.index'),
        status: 'Active',
        external: false,
    },
    {
        slug: 'tools',
        name: 'Business Plan Generator & Tools',
        tagline: 'AI Business Planning & Calculators',
        icon: WrenchScrewdriverIcon,
        gradient: 'from-amber-500 to-orange-600',
        bg: 'bg-amber-50',
        text: 'text-amber-600',
        description: 'Create 20-module business plans, calculate commissions, and simulate growth ROI.',
        url: route('grownet.sub.business-plan-generator'),
        status: 'Active',
        external: false,
    },
];
</script>

<template>
    <Head title="GrowNet Products" />

    <div class="min-h-screen bg-slate-50">
        <!-- Header -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center shadow-sm text-white">
                            <RocketLaunchIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <h1 class="text-base font-extrabold text-slate-900 leading-tight">MyGrowNet</h1>
                            <p class="text-[11px] text-slate-500 font-medium leading-tight">GrowNet Member Engagement Portal</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- GrowNet Active Status / In-Platform Activation Badge -->
                        <GrowNetHeaderBadge />

                        <Link
                            :href="route('grownet.network')"
                            class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        >
                            <UserGroupIcon class="w-4 h-4 text-indigo-600" />
                            Member Dashboard
                        </Link>

                        <div ref="dropdownEl" class="relative">
                            <button @click="toggleDropdown"
                                class="flex items-center gap-2 pl-2 pr-2.5 py-1.5 rounded-xl hover:bg-slate-100 transition-colors border border-slate-200"
                            >
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                    {{ (user.name || 'U').charAt(0).toUpperCase() }}
                                </div>
                                <ChevronDownIcon class="w-3.5 h-3.5 text-slate-400" :class="{ 'rotate-180': dropdownOpen }" />
                            </button>

                            <div v-if="dropdownOpen"
                                class="absolute right-0 top-full mt-1.5 w-56 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 z-50 text-xs"
                            >
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="font-bold text-slate-900 truncate">{{ user.name }}</p>
                                    <p class="text-[11px] text-slate-500 truncate">{{ user.email }}</p>
                                </div>
                                <Link :href="route('workspace')"
                                    class="flex items-center gap-2.5 px-4 py-2.5 text-slate-700 hover:bg-slate-50 transition-colors font-medium"
                                >
                                    <RocketLaunchIcon class="w-4 h-4 text-slate-400" />
                                    Platform Workspace
                                </Link>
                                <Link :href="route('grownet.network')"
                                    class="flex items-center gap-2.5 px-4 py-2.5 text-slate-700 hover:bg-slate-50 transition-colors font-medium"
                                >
                                    <UsersIcon class="w-4 h-4 text-indigo-600" />
                                    Member Dashboard
                                </Link>
                                <button @click="logout"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-red-600 hover:bg-red-50 transition-colors font-medium text-left"
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

        <!-- Main Content -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            
            <!-- Hero Welcome Card -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-2xl p-6 text-white shadow-xl border border-white/10 relative overflow-hidden">
                <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 text-indigo-300 text-xs font-semibold mb-2 border border-white/15">
                            <SparklesIcon class="w-3.5 h-3.5 text-emerald-400" />
                            Content & Engagement Ecosystem
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-white">Welcome back{{ user.name ? ', ' + user.name.split(' ')[0] : '' }}! 👋</h2>
                        <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">Access Education, GrowStream video, GrowMusic audio, and business tools from your single account.</p>
                    </div>

                    <Link :href="route('grownet.network')" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all shadow-md flex items-center gap-1.5 flex-shrink-0">
                        <UserGroupIcon class="w-4 h-4" />
                        Open Member Dashboard
                    </Link>
                </div>
            </div>

            <!-- Product Grid Aligned 100% with Spec -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900">Participating Content & Services</h3>
                    <span class="text-xs text-slate-500 font-medium">5 Core Modules Active</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="item in products" :key="item.slug"
                        class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shadow-xs', item.bg, item.text]">
                                    <component :is="item.icon" class="w-5 h-5" />
                                </div>
                                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ item.status }}
                                </span>
                            </div>

                            <h4 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ item.name }}</h4>
                            <p class="text-xs font-semibold text-slate-500 mb-2">{{ item.tagline }}</p>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ item.description }}</p>
                        </div>

                        <div class="pt-5 mt-4 border-t border-slate-100 flex items-center justify-between">
                            <a v-if="item.external" :href="item.url" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                Launch Platform
                                <ChevronRightIcon class="w-4 h-4" />
                            </a>
                            <Link v-else :href="item.url" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                Access Module
                                <ChevronRightIcon class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>
