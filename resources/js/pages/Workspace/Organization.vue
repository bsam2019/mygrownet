<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import AppGrid from '@/Components/Workspace/AppGrid.vue';
import {
    BuildingOfficeIcon,
    UsersIcon,
    ArrowRightIcon,
    ArrowLeftIcon,
    Cog6ToothIcon,
    CreditCardIcon,
    ShieldCheckIcon,
    CubeIcon,
    Squares2X2Icon,
} from '@heroicons/vue/24/solid';
import { computed, ref } from 'vue';

interface InstalledApp {
    id: number;
    name: string;
    slug: string;
    url?: string;
    category?: string;
}

interface Member {
    id: number;
    user: { id: number; name: string; email: string; profile?: { avatar?: string } };
    role: string;
}

interface App {
    id: number;
    name: string;
    slug: string;
    url?: string;
}

interface IntendedApp {
    id: number;
    name: string;
    slug: string;
    url?: string;
}

const props = defineProps<{
    organization: {
        id: number;
        name: string;
        slug: string;
        type?: string;
        country?: string;
        currency?: string;
        timezone?: string;
        language?: string;
        status?: string;
        installed_apps: InstalledApp[];
    };
    apps: Record<string, App[]>;
    members: Member[];
    context: Record<string, any>;
    userRole?: string;
    intendedApp?: IntendedApp | null;
}>();

const appSwitcherOpen = ref(false);

const allApps = computed(() => {
    const apps: App[] = [];
    Object.values(props.apps).forEach((appList) => {
        apps.push(...appList);
    });
    return apps;
});

function launchApp(app: App) {
    appSwitcherOpen.value = false;
    router.post(route('workspace.launch', { application: app.id }));
}
</script>

<template>
    <WorkspaceLayout>
        <Head :title="organization.name" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Back -->
            <Link
                :href="route('workspace')"
                class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 mb-6 transition-colors"
            >
                <ArrowLeftIcon class="w-3.5 h-3.5" />
                Back to Workspace
            </Link>

            <!-- Intended App Banner -->
            <div
                v-if="intendedApp"
                class="mb-6 px-5 py-3 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-between"
            >
                <div>
                    <p class="text-xs text-indigo-500">Continue where you left off</p>
                    <p class="text-sm font-semibold text-indigo-800 mt-0.5">{{ intendedApp.name }}</p>
                </div>
                <button
                    @click="launchApp(intendedApp)"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
                >
                    Continue
                    <ArrowRightIcon class="w-3.5 h-3.5" />
                </button>
            </div>

            <!-- Org Header -->
            <div class="bg-white rounded-xl border border-gray-200/80 p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-[#0b1120] text-white shadow-sm">
                            <BuildingOfficeIcon class="w-7 h-7" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h1 class="text-xl font-bold text-gray-900">{{ organization.name }}</h1>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ organization.type || 'Organization' }}
                                <span v-if="organization.country"> &middot; {{ organization.country }}</span>
                                <span v-if="organization.currency"> &middot; {{ organization.currency }}</span>
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span
                                    v-if="userRole"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-indigo-50 text-indigo-700 capitalize"
                                >
                                    {{ userRole }}
                                </span>
                                <span
                                    :class="[
                                        'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium capitalize',
                                        organization.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600',
                                    ]"
                                >
                                    {{ organization.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- App Switcher -->
                    <div class="relative">
                        <button
                            @click="appSwitcherOpen = !appSwitcherOpen"
                            class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                        >
                            <Squares2X2Icon class="w-3.5 h-3.5" />
                            <span class="hidden sm:inline">Switch</span>
                        </button>
                        <div
                            v-if="appSwitcherOpen"
                            class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-200 p-3 z-50"
                        >
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2 px-1">Switch to</p>
                            <button
                                v-for="app in allApps"
                                :key="app.id"
                                @click="launchApp(app)"
                                class="w-full text-left px-2.5 py-2 text-sm text-gray-700 hover:bg-indigo-50 rounded-lg transition-colors flex items-center gap-2"
                            >
                                <div class="w-6 h-6 rounded bg-indigo-50 flex items-center justify-center flex-shrink-0">
                                    <CubeIcon class="w-3.5 h-3.5 text-indigo-600" />
                                </div>
                                {{ app.name }}
                            </button>
                            <div class="border-t border-gray-100 mt-2 pt-2">
                                <Link
                                    :href="route('workspace')"
                                    class="block w-full text-left px-2.5 py-2 text-sm text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium"
                                >
                                    MyGrowNet Platform
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
                <div class="bg-white rounded-xl border border-gray-200/80 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                        <CubeIcon class="w-5 h-5 text-indigo-600" />
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-gray-900 tabular-nums">{{ organization.installed_apps.length }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Active Applications</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200/80 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                        <UsersIcon class="w-5 h-5 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-gray-900 tabular-nums">{{ members.length }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Team Members</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200/80 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                        <CreditCardIcon class="w-5 h-5 text-amber-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ organization.currency || 'ZMW' }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Default Currency</p>
                    </div>
                </div>
            </div>

            <!-- App Grid -->
            <section class="mb-8">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Installed Applications</h2>
                <AppGrid :apps="apps" />
            </section>

            <!-- Members -->
            <section class="mb-8">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <UsersIcon class="w-4 h-4 text-gray-400" />
                    Team Members
                </h2>
                <div class="bg-white rounded-xl border border-gray-200/80 divide-y divide-gray-100">
                    <div v-for="member in members" :key="member.id" class="flex items-center gap-3 p-4">
                        <div class="w-8 h-8 rounded-full bg-[#0b1120] flex items-center justify-center text-xs font-medium text-white">
                            {{ member.user.name?.charAt(0) || '?' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ member.user.name }}</p>
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ member.user.email }}</p>
                        </div>
                        <span class="text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded-full capitalize">
                            {{ member.role }}
                        </span>
                    </div>
                    <div v-if="members.length === 0" class="p-6 text-sm text-gray-400 text-center">
                        No members found.
                    </div>
                </div>
            </section>

            <!-- Settings -->
            <section class="mb-8">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <Cog6ToothIcon class="w-4 h-4 text-gray-400" />
                    Settings
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <Link
                        href="/settings"
                        class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-200/80 hover:border-gray-300 hover:shadow-sm transition-all"
                    >
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                            <Cog6ToothIcon class="w-5 h-5 text-gray-500" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">General Settings</p>
                            <p class="text-xs text-gray-400 mt-0.5">Preferences, billing, notifications</p>
                        </div>
                    </Link>
                    <div class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-200/80">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                            <ShieldCheckIcon class="w-5 h-5 text-gray-500" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ organization.timezone || 'Africa/Lusaka' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">Timezone &middot; {{ organization.language || 'en' }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </WorkspaceLayout>
</template>
