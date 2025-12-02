<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    XMarkIcon,
    UserCircleIcon,
    Cog6ToothIcon,
    BellIcon,
    ShieldCheckIcon,
    PaintBrushIcon,
    QuestionMarkCircleIcon,
    ArrowRightOnRectangleIcon,
    ChevronRightIcon,
    HomeIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    show: boolean;
    appName?: string;
    appColor?: string;
    homeRoute?: string;
    settingsRoute?: string;
    profileRoute?: string;
    showHomeHub?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    appName: 'App',
    appColor: '#10B981',
    homeRoute: 'home',
    settingsRoute: 'settings.profile',
    profileRoute: 'settings.profile',
    showHomeHub: true,
});

const emit = defineEmits(['close']);

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);

const getInitials = (name: string) => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

const handleLogout = () => {
    emit('close');
    router.post(route('logout'));
};

const navigateTo = (routeName: string) => {
    emit('close');
    router.visit(route(routeName));
};

// Close on escape key
const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        emit('close');
    }
};

watch(() => props.show, (isOpen) => {
    if (isOpen) {
        document.addEventListener('keydown', handleKeydown);
        document.body.style.overflow = 'hidden';
    } else {
        document.removeEventListener('keydown', handleKeydown);
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="show" 
                class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"
                @click="$emit('close')"
            />
        </Transition>

        <!-- Bottom Sheet -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div 
                v-if="show"
                class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-50 bg-white rounded-t-3xl shadow-2xl"
                style="padding-bottom: env(safe-area-inset-bottom);"
            >
                <!-- Handle bar -->
                <div class="flex justify-center pt-3 pb-2">
                    <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                </div>
                
                <!-- User Profile Card -->
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-4">
                        <div 
                            class="h-16 w-16 rounded-full flex items-center justify-center shadow-lg"
                            :style="{ 
                                background: `linear-gradient(135deg, ${appColor}, ${appColor}dd)`,
                                boxShadow: `0 10px 15px -3px ${appColor}33`
                            }"
                        >
                            <span class="text-xl font-bold text-white">{{ getInitials(user?.name || '') }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-lg text-gray-900">{{ user?.name }}</p>
                            <p class="text-sm text-gray-500">{{ user?.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Menu Items -->
                <div class="px-4 pt-2 pb-3 max-h-[60vh] overflow-y-auto">
                    <!-- Settings (main settings page for the app) -->
                    <button 
                        @click="navigateTo(settingsRoute)"
                        class="flex items-center justify-between w-full px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center">
                                <UserCircleIcon class="h-5 w-5 text-blue-600" />
                            </div>
                            <div class="text-left">
                                <span class="font-medium text-sm">Settings</span>
                                <p class="text-xs text-gray-500">Profile, security, and preferences</p>
                            </div>
                        </div>
                        <ChevronRightIcon class="h-5 w-5 text-gray-400" />
                    </button>

                    <!-- App Settings Slot -->
                    <slot name="app-settings" />

                    <!-- Divider -->
                    <div class="my-1.5 border-t border-gray-100"></div>

                    <!-- Home Hub -->
                    <button 
                        v-if="showHomeHub"
                        @click="navigateTo(homeRoute)"
                        class="flex items-center justify-between w-full px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center">
                                <HomeIcon class="h-5 w-5 text-indigo-600" />
                            </div>
                            <div class="text-left">
                                <span class="font-medium text-sm">Home Hub</span>
                                <p class="text-xs text-gray-500">Switch to other apps</p>
                            </div>
                        </div>
                        <ChevronRightIcon class="h-5 w-5 text-gray-400" />
                    </button>

                    <!-- Help -->
                    <button 
                        class="flex items-center justify-between w-full px-3 py-2.5 rounded-xl text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-teal-50 flex items-center justify-center">
                                <QuestionMarkCircleIcon class="h-5 w-5 text-teal-600" />
                            </div>
                            <div class="text-left">
                                <span class="font-medium text-sm">Help & Support</span>
                                <p class="text-xs text-gray-500">Get help with {{ appName }}</p>
                            </div>
                        </div>
                        <ChevronRightIcon class="h-5 w-5 text-gray-400" />
                    </button>

                    <!-- Logout -->
                    <button 
                        @click="handleLogout"
                        class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-red-600 hover:bg-red-50 active:bg-red-100 transition-colors"
                    >
                        <div class="w-9 h-9 rounded-full bg-red-50 flex items-center justify-center">
                            <ArrowRightOnRectangleIcon class="h-5 w-5 text-red-500" />
                        </div>
                        <div class="text-left">
                            <span class="font-medium text-sm">Sign Out</span>
                            <p class="text-xs text-red-400">Log out of your account</p>
                        </div>
                    </button>
                </div>

                <!-- Cancel Button -->
                <div class="px-4 pb-4">
                    <button 
                        @click="$emit('close')"
                        class="w-full py-3.5 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 active:bg-gray-300 transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
