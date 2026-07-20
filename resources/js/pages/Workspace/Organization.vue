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
            <!-- Back to Platform -->
            <Link
                :href="route('workspace')"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-6 transition-colors"
            >
                <ArrowLeftIcon class="w-4 h-4" />
                Back to MyGrowNet
            </Link>

            <!-- Intended App Banner -->
            <div
                v-if="intendedApp"
                class="mb-6 p-4 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-between text-white shadow-md"
            >
                <div>
                    <p class="text-sm text-blue-100">Continue where you left off</p>
                    <p class="text-lg font-semibold">{{ intendedApp.name }}</p>
                </div>
                <button
                    @click="launchApp(intendedApp)"
                    class="flex items-center gap-1.5 px-4 py-2 bg-white text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition-colors"
                >
                    Continue
                    <ArrowRightIcon class="w-4 h-4" />
                </button>
            </div>

            <!-- Org Header -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-md">
                            <BuildingOfficeIcon class="w-7 h-7" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h1 class="text-2xl font-bold text-gray-900">{{ organization.name }}</h1>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ organization.type || 'Organization' }}
                                <span v-if="organization.country">· {{ organization.country }}</span>
                                <span v-if="organization.currency">· {{ organization.currency }}</span>
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span
                                    v-if="userRole"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 capitalize"
                                >
                                    {{ userRole }}
                                </span>
                                <span
                                    :class="[
                                        'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize',
                                        organization.status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-600',
                                    ]"
                                >
                                    {{ organization.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- App Switcher (Architecture §15) -->
                    <div class="relative">
                        <button
                            @click="appSwitcherOpen = !appSwitcherOpen"
                            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                        >
                            <Squares2X2Icon class="w-4 h-4" />
                            <span class="hidden sm:inline">Switch App</span>
                        </button>
                        <div
                            v-if="appSwitcherOpen"
                            class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-200 p-3 z-50"
                        >
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2 px-1">Switch to</p>
                            <button
                                v-for="app in allApps"
                                :key="app.id"
                                @click="launchApp(app)"
                                class="w-full text-left px-2 py-1.5 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors flex items-center gap-2"
                            >
                                <div class="w-6 h-6 rounded bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <CubeIcon class="w-3.5 h-3.5 text-blue-600" />
                                </div>
                                {{ app.name }}
                            </button>
                            <div class="border-t border-gray-100 mt-2 pt-2">
                                <Link
                                    :href="route('workspace')"
                                    class="block w-full text-left px-2 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg font-medium"
                                >
                                    MyGrowNet Platform
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                        <CubeIcon class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ organization.installed_apps.length }}</p>
                        <p class="text-xs text-gray-500">Active Applications</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                        <UsersIcon class="w-5 h-5 text-green-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ members.length }}</p>
                        <p class="text-xs text-gray-500">Team Members</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                        <CreditCardIcon class="w-5 h-5 text-amber-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ organization.currency || 'ZMW' }}</p>
                        <p class="text-xs text-gray-500">Default Currency</p>
                    </div>
                </div>
            </div>

            <!-- App Grid -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Installed Applications</h2>
                <AppGrid :apps="apps" />
            </section>

            <!-- Members -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <UsersIcon class="w-5 h-5 text-gray-500" />
                    Team Members
                </h2>
                <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                    <div v-for="member in members" :key="member.id" class="flex items-center gap-3 p-4">
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600">
                            {{ member.user.name?.charAt(0) || '?' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ member.user.name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ member.user.email }}</p>
                        </div>
                        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded-full capitalize">
                            {{ member.role }}
                        </span>
                    </div>
                    <div v-if="members.length === 0" class="p-4 text-sm text-gray-500 text-center">
                        No members found.
                    </div>
                </div>
            </section>

            <!-- Settings Section -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <Cog6ToothIcon class="w-5 h-5 text-gray-500" />
                    Settings
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <Link
                        href="/settings"
                        class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all"
                    >
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                            <Cog6ToothIcon class="w-5 h-5 text-gray-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">General Settings</p>
                            <p class="text-xs text-gray-500">Preferences, billing, notifications</p>
                        </div>
                    </Link>
                    <div class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-200">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                            <ShieldCheckIcon class="w-5 h-5 text-gray-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ organization.timezone || 'Africa/Lusaka' }}</p>
                            <p class="text-xs text-gray-500">Timezone · {{ organization.language || 'en' }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </WorkspaceLayout>
</template>
