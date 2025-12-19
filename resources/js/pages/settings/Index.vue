<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import {
    UserCircleIcon,
    LockClosedIcon,
    PaintBrushIcon,
    BellIcon,
    ShieldCheckIcon,
    ArrowRightIcon,
    Cog6ToothIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const settingsOptions = [
    {
        title: 'Profile',
        description: 'Manage your personal information and contact details',
        icon: UserCircleIcon,
        href: '/settings/profile',
        gradient: 'from-blue-500 to-blue-600',
        iconBg: 'bg-blue-100',
        iconColor: 'text-blue-600',
    },
    {
        title: 'Password',
        description: 'Update your password and security settings',
        icon: LockClosedIcon,
        href: '/settings/password',
        gradient: 'from-indigo-500 to-purple-600',
        iconBg: 'bg-indigo-100',
        iconColor: 'text-indigo-600',
    },
    {
        title: 'Appearance',
        description: 'Customize theme and display preferences',
        icon: PaintBrushIcon,
        href: '/settings/appearance',
        gradient: 'from-pink-500 to-rose-600',
        iconBg: 'bg-pink-100',
        iconColor: 'text-pink-600',
    },
];
</script>

<template>
    <ClientLayout>
        <Head title="Settings" />

        <div class="max-w-4xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <Link
                    href="/dashboard"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-4"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Dashboard
                </Link>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                        <Cog6ToothIcon class="h-6 w-6 text-white" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
                        <p class="text-gray-500">Manage your account preferences</p>
                    </div>
                </div>
            </div>

            <!-- User Info Card -->
            <div class="mb-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center">
                        <UserCircleIcon class="h-10 w-10 text-white" aria-hidden="true" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">{{ user?.name }}</h2>
                        <p class="text-blue-100">{{ user?.email }}</p>
                    </div>
                </div>
            </div>

            <!-- Settings Grid -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <Link
                    v-for="option in settingsOptions"
                    :key="option.href"
                    :href="option.href"
                    class="group bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div :class="['w-14 h-14 rounded-xl flex items-center justify-center', option.iconBg]">
                            <component :is="option.icon" :class="['h-7 w-7', option.iconColor]" aria-hidden="true" />
                        </div>
                        <div class="w-8 h-8 rounded-full bg-gray-100 group-hover:bg-blue-100 flex items-center justify-center transition-colors">
                            <ArrowRightIcon class="h-4 w-4 text-gray-400 group-hover:text-blue-600 transition-colors" aria-hidden="true" />
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ option.title }}</h3>
                    <p class="text-sm text-gray-500">{{ option.description }}</p>
                </Link>
            </div>

            <!-- Additional Options -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">More Options</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <Link
                        href="/wallet"
                        class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Wallet</p>
                                <p class="text-sm text-gray-500">Manage your wallet and transactions</p>
                            </div>
                        </div>
                        <ArrowRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </Link>

                    <Link
                        href="/settings/profile"
                        class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                                <BellIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Notifications</p>
                                <p class="text-sm text-gray-500">Configure notification preferences</p>
                            </div>
                        </div>
                        <ArrowRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </Link>

                    <Link
                        href="/settings/profile"
                        class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                <ShieldCheckIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Privacy & Security</p>
                                <p class="text-sm text-gray-500">Control your privacy settings</p>
                            </div>
                        </div>
                        <ArrowRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </Link>
                </div>
            </div>

            <!-- Help Section -->
            <div class="mt-8 bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-6 border border-gray-200">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-1">Need Help?</h3>
                        <p class="text-sm text-gray-600 mb-3">
                            If you have questions about your account settings, check our help center or contact support.
                        </p>
                        <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                            Visit Help Center →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
