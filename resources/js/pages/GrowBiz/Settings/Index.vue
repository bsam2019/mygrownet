<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    UserCircleIcon,
    BuildingOffice2Icon,
    BellIcon,
    ShieldCheckIcon,
    PaintBrushIcon,
    UsersIcon,
    ChevronRightIcon,
    HomeIcon,
    ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    businessProfile?: {
        id: number;
        business_name: string | null;
        business_type: string | null;
        industry: string | null;
    } | null;
    userRole: string;
}

const props = defineProps<Props>();
const page = usePage();
const user = (page.props as any).auth?.user;

const getInitials = (name: string) => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

const handleLogout = () => {
    router.post(route('logout'));
};

const settingsSections = [
    {
        title: 'Account',
        items: [
            {
                name: 'My Profile',
                description: 'View and edit your profile',
                icon: UserCircleIcon,
                iconBg: 'bg-blue-50',
                iconColor: 'text-blue-600',
                route: 'growbiz.settings.profile',
            },
            {
                name: 'Security',
                description: 'Password and security settings',
                icon: ShieldCheckIcon,
                iconBg: 'bg-amber-50',
                iconColor: 'text-amber-600',
                route: 'growbiz.settings.security',
            },
            {
                name: 'Notifications',
                description: 'Manage notification preferences',
                icon: BellIcon,
                iconBg: 'bg-purple-50',
                iconColor: 'text-purple-600',
                route: 'growbiz.settings.notifications',
                disabled: true,
            },
        ],
    },
    {
        title: 'Business',
        ownerOnly: true,
        items: [
            {
                name: 'Business Profile',
                description: 'Company name, logo, and details',
                icon: BuildingOffice2Icon,
                iconBg: 'bg-emerald-50',
                iconColor: 'text-emerald-600',
                route: 'growbiz.settings.business',
            },
            {
                name: 'Team Settings',
                description: 'Manage departments and roles',
                icon: UsersIcon,
                iconBg: 'bg-indigo-50',
                iconColor: 'text-indigo-600',
                route: 'growbiz.settings.team',
                disabled: true,
            },
        ],
    },
    {
        title: 'App',
        items: [
            {
                name: 'Appearance',
                description: 'Theme and display preferences',
                icon: PaintBrushIcon,
                iconBg: 'bg-pink-50',
                iconColor: 'text-pink-600',
                route: 'growbiz.settings.appearance',
                disabled: true,
            },
        ],
    },
];

const filteredSections = settingsSections.filter(section => {
    if (section.ownerOnly && props.userRole === 'employee') {
        return false;
    }
    return true;
});
</script>

<template>
    <GrowBizLayout>
        <Head title="Settings - GrowBiz" />
        
        <div class="px-4 py-4 pb-6">
            <!-- Page Header -->
            <div class="flex items-center gap-3 mb-6">
                <Link 
                    :href="route('growbiz.dashboard')" 
                    class="p-2 -ml-2 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition-colors"
                    aria-label="Back to dashboard"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">Settings</h1>
            </div>

            <!-- User Card -->
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
                <div class="flex items-center gap-4">
                    <div class="h-16 w-16 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-200">
                        <span class="text-xl font-bold text-white">{{ getInitials(user?.name || '') }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-lg text-gray-900">{{ user?.name }}</p>
                        <p class="text-sm text-gray-500">{{ user?.email }}</p>
                        <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                            {{ userRole === 'employee' ? 'Employee' : 'Business Owner' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Settings Sections -->
            <div class="space-y-6">
                <div v-for="section in filteredSections" :key="section.title">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">
                        {{ section.title }}
                    </h2>
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                        <div class="divide-y divide-gray-100">
                            <template v-for="item in section.items" :key="item.name">
                                <Link 
                                    v-if="!item.disabled"
                                    :href="route(item.route)"
                                    class="flex items-center justify-between p-4 hover:bg-gray-50 active:bg-gray-100 transition-colors"
                                >
                                    <div class="flex items-center gap-4">
                                        <div :class="['w-10 h-10 rounded-full flex items-center justify-center', item.iconBg]">
                                            <component :is="item.icon" :class="['h-5 w-5', item.iconColor]" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ item.name }}</p>
                                            <p class="text-sm text-gray-500">{{ item.description }}</p>
                                        </div>
                                    </div>
                                    <ChevronRightIcon class="h-5 w-5 text-gray-400" />
                                </Link>
                                <div 
                                    v-else
                                    class="flex items-center justify-between p-4 opacity-50 cursor-not-allowed"
                                >
                                    <div class="flex items-center gap-4">
                                        <div :class="['w-10 h-10 rounded-full flex items-center justify-center', item.iconBg]">
                                            <component :is="item.icon" :class="['h-5 w-5', item.iconColor]" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ item.name }}</p>
                                            <p class="text-sm text-gray-500">Coming soon</p>
                                        </div>
                                    </div>
                                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">Soon</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 space-y-3">
                <Link 
                    :href="route('home')"
                    class="flex items-center justify-between w-full p-4 bg-white rounded-2xl shadow-sm hover:bg-gray-50 active:bg-gray-100 transition-colors"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center">
                            <HomeIcon class="h-5 w-5 text-indigo-600" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Home Hub</p>
                            <p class="text-sm text-gray-500">Switch to other apps</p>
                        </div>
                    </div>
                    <ChevronRightIcon class="h-5 w-5 text-gray-400" />
                </Link>

                <button 
                    @click="handleLogout"
                    class="flex items-center gap-4 w-full p-4 bg-white rounded-2xl shadow-sm text-red-600 hover:bg-red-50 active:bg-red-100 transition-colors"
                >
                    <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center">
                        <ArrowRightOnRectangleIcon class="h-5 w-5 text-red-500" />
                    </div>
                    <div class="text-left">
                        <p class="font-medium">Sign Out</p>
                        <p class="text-sm text-red-400">Log out of your account</p>
                    </div>
                </button>
            </div>
        </div>
    </GrowBizLayout>
</template>
