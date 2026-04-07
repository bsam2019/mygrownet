<script setup lang="ts">
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    DevicePhoneMobileIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    GlobeAltIcon,
    UsersIcon,
    SparklesIcon,
    BanknotesIcon,
    ClipboardDocumentCheckIcon,
    ShoppingCartIcon,
    WalletIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    hasGrowNetPackage?: boolean;
    availableApps?: Array<{
        slug: string;
        name: string;
        description: string;
        icon: string;
    }>;
}

const props = withDefaults(defineProps<Props>(), {
    hasGrowNetPackage: false,
    availableApps: () => [],
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const form = useForm({
    pwa_default_app: user.value?.pwa_default_app || null,
});

const submit = () => {
    form.patch(route('settings.pwa.update'), {
        preserveScroll: true,
    });
};

const appIcons: Record<string, any> = {
    'grownet': UsersIcon,
    'growbuilder': GlobeAltIcon,
    'bizboost': SparklesIcon,
    'growfinance': BanknotesIcon,
    'growbiz': ClipboardDocumentCheckIcon,
    'marketplace': ShoppingCartIcon,
    'wallet': WalletIcon,
};

const getAppIcon = (slug: string) => {
    return appIcons[slug] || GlobeAltIcon;
};

const appOptions = [
    {
        slug: null,
        name: 'Dashboard',
        description: 'Default dashboard with all apps',
        icon: 'dashboard'
    },
    {
        slug: 'growbuilder',
        name: 'GrowBuilder',
        description: 'Website builder and management',
        icon: 'growbuilder'
    },
    {
        slug: 'bizboost',
        name: 'BizBoost',
        description: 'Business management & marketing',
        icon: 'bizboost'
    },
    {
        slug: 'growfinance',
        name: 'GrowFinance',
        description: 'Accounting & financial management',
        icon: 'growfinance'
    },
    {
        slug: 'growbiz',
        name: 'GrowBiz',
        description: 'Team & employee management',
        icon: 'growbiz'
    },
    {
        slug: 'marketplace',
        name: 'GrowMarket',
        description: 'Shop products & services',
        icon: 'marketplace'
    },
    {
        slug: 'wallet',
        name: 'MyGrow Save',
        description: 'Wallet & transactions',
        icon: 'wallet'
    },
];
</script>

<template>
    <AppLayout>
        <Head title="PWA Settings" />

        <div class="max-w-2xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <Link
                    href="/settings/profile"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-4"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Profile
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">PWA Settings</h1>
                <p class="text-gray-500 mt-1">Configure your installed app experience</p>
            </div>

            <!-- Success Message -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
            >
                <div v-if="$page.props.flash?.success" class="mb-6 bg-emerald-50 border border-emerald-200 rounded-2xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                            <CheckCircleIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                        </div>
                        <p class="text-sm font-medium text-emerald-800">{{ $page.props.flash.success }}</p>
                    </div>
                </div>
            </Transition>

            <!-- GrowNet Notice -->
            <div v-if="hasGrowNetPackage" class="mb-6 bg-blue-50 border border-blue-200 rounded-2xl p-4">
                <div class="flex items-start gap-3">
                    <UsersIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                    <div>
                        <p class="text-sm text-blue-800 font-medium">GrowNet Member</p>
                        <p class="text-sm text-blue-700 mt-1">
                            As a GrowNet member, you'll automatically be directed to the GrowNet dashboard when opening the installed app.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 px-6 py-8 text-center">
                    <div class="w-20 h-20 mx-auto rounded-full bg-white/20 flex items-center justify-center mb-4">
                        <DevicePhoneMobileIcon class="h-12 w-12 text-white" aria-hidden="true" />
                    </div>
                    <h2 class="text-xl font-bold text-white">Default App</h2>
                    <p class="text-indigo-100 text-sm">Choose where to start when you open the app</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="p-6">
                    <div class="space-y-3">
                        <label
                            v-for="app in appOptions"
                            :key="app.slug || 'dashboard'"
                            class="relative flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-indigo-300 hover:bg-indigo-50/50"
                            :class="{
                                'border-indigo-500 bg-indigo-50': form.pwa_default_app === app.slug,
                                'border-gray-200': form.pwa_default_app !== app.slug
                            }"
                        >
                            <input
                                v-model="form.pwa_default_app"
                                type="radio"
                                :value="app.slug"
                                :disabled="hasGrowNetPackage"
                                class="sr-only"
                            />
                            
                            <div class="flex items-start gap-4 flex-1">
                                <div 
                                    class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                                    :class="{
                                        'bg-indigo-500': form.pwa_default_app === app.slug,
                                        'bg-gray-100': form.pwa_default_app !== app.slug
                                    }"
                                >
                                    <component 
                                        :is="getAppIcon(app.icon)" 
                                        class="w-6 h-6"
                                        :class="{
                                            'text-white': form.pwa_default_app === app.slug,
                                            'text-gray-600': form.pwa_default_app !== app.slug
                                        }"
                                        aria-hidden="true"
                                    />
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <p 
                                        class="font-semibold text-sm"
                                        :class="{
                                            'text-indigo-900': form.pwa_default_app === app.slug,
                                            'text-gray-900': form.pwa_default_app !== app.slug
                                        }"
                                    >
                                        {{ app.name }}
                                    </p>
                                    <p 
                                        class="text-xs mt-0.5"
                                        :class="{
                                            'text-indigo-700': form.pwa_default_app === app.slug,
                                            'text-gray-600': form.pwa_default_app !== app.slug
                                        }"
                                    >
                                        {{ app.description }}
                                    </p>
                                </div>

                                <div 
                                    v-if="form.pwa_default_app === app.slug"
                                    class="flex-shrink-0"
                                >
                                    <CheckCircleIcon class="h-6 w-6 text-indigo-600" aria-hidden="true" />
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Info Box -->
                    <div class="mt-6 bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-600 leading-relaxed">
                            <strong>Note:</strong> This setting only applies when you open the installed app. 
                            When accessing via browser, you'll always see the dashboard.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button
                            type="submit"
                            :disabled="form.processing || hasGrowNetPackage"
                            class="w-full py-3 px-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-600 hover:to-purple-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Preference' }}
                        </button>
                        
                        <Transition
                            enter-active-class="transition ease-out duration-300"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in duration-200"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="form.recentlySuccessful" class="text-center text-sm text-emerald-600 mt-3 font-medium">
                                ✓ Preference saved successfully
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
