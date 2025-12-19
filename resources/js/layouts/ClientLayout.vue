<script setup lang="ts">
/**
 * ClientLayout - Clean layout for client dashboard and apps
 * 
 * Simple header with logo and profile button (no sidebar).
 * Used for the main dashboard and client-facing features.
 */
import { usePage, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { 
    UserCircleIcon,
    Cog6ToothIcon,
    ArrowRightOnRectangleIcon,
    XMarkIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    ShieldCheckIcon,
    UsersIcon,
    EnvelopeIcon,
    PhoneIcon,
    LockClosedIcon,
    PaintBrushIcon,
    SunIcon,
    MoonIcon,
    ComputerDesktopIcon,
    EyeIcon,
    EyeSlashIcon,
} from '@heroicons/vue/24/outline';
import { UserCircleIcon as UserCircleIconSolid } from '@heroicons/vue/24/solid';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useAppearance } from '@/composables/useAppearance';

interface Props {
    title?: string;
    isAdmin?: boolean;
    isManager?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'MyGrowNet',
    isAdmin: false,
    isManager: false,
});

const page = usePage();
const showProfileSlider = ref(false);
const showFlash = ref(false);
const flashMessage = ref('');
const flashType = ref<'success' | 'error'>('success');
const activeTab = ref<'profile' | 'password' | 'appearance'>('profile');

const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => props.isAdmin || page.props.isAdmin);
const isManager = computed(() => props.isManager || page.props.isManager);

// Watch for flash messages
watch(() => page.props.flash, (flash: any) => {
    if (flash?.success) {
        flashMessage.value = flash.success;
        flashType.value = 'success';
        showFlash.value = true;
        setTimeout(() => showFlash.value = false, 5000);
    } else if (flash?.error) {
        flashMessage.value = flash.error;
        flashType.value = 'error';
        showFlash.value = true;
        setTimeout(() => showFlash.value = false, 5000);
    }
}, { immediate: true });

const logout = () => {
    router.post('/logout');
};

// Profile form
const profileForm = useForm({
    name: user.value?.name || '',
    email: user.value?.email || '',
    phone: user.value?.phone || '',
});

const submitProfile = () => {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            showFlash.value = true;
            flashMessage.value = 'Profile updated successfully!';
            flashType.value = 'success';
            setTimeout(() => showFlash.value = false, 3000);
        },
    });
};

// Password form
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            showFlash.value = true;
            flashMessage.value = 'Password updated successfully!';
            flashType.value = 'success';
            setTimeout(() => showFlash.value = false, 3000);
        },
    });
};

// Appearance
const { appearance, updateAppearance } = useAppearance();

const themes = [
    { value: 'light', icon: SunIcon, label: 'Light', description: 'Clean and bright' },
    { value: 'dark', icon: MoonIcon, label: 'Dark', description: 'Easy on the eyes' },
    { value: 'system', icon: ComputerDesktopIcon, label: 'System', description: 'Follow device' },
] as const;
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Flash Messages -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform -translate-y-full opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform -translate-y-full opacity-0"
        >
            <div 
                v-if="showFlash" 
                :class="[
                    'fixed top-0 left-0 right-0 z-50 px-4 py-3 shadow-lg',
                    flashType === 'success' ? 'bg-green-500' : 'bg-red-500'
                ]"
            >
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div class="flex items-center gap-2 text-white">
                        <CheckCircleIcon v-if="flashType === 'success'" class="h-5 w-5" aria-hidden="true" />
                        <ExclamationCircleIcon v-else class="h-5 w-5" aria-hidden="true" />
                        <span class="text-sm font-medium">{{ flashMessage }}</span>
                    </div>
                    <button 
                        @click="showFlash = false" 
                        class="text-white/80 hover:text-white"
                        aria-label="Dismiss message"
                    >
                        <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <Link href="/dashboard" class="flex items-center gap-2">
                        <AppLogoIcon class="h-9 w-9" />
                    </Link>

                    <button
                        @click="showProfileSlider = true"
                        class="flex items-center gap-2 p-2 hover:bg-gray-100 rounded-full transition-colors"
                        aria-label="Open profile menu"
                    >
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                            <UserCircleIconSolid class="w-6 h-6 text-white" aria-hidden="true" />
                        </div>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="py-6 text-center text-sm text-gray-400">
            <p>&copy; {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.</p>
        </footer>

        <!-- Profile Slider -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="transform translate-x-full"
            enter-to-class="transform translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="transform translate-x-0"
            leave-to-class="transform translate-x-full"
        >
            <div v-if="showProfileSlider" class="fixed inset-y-0 right-0 z-50 w-80 bg-white shadow-2xl flex flex-col overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 flex-shrink-0">
                    <h2 class="text-lg font-semibold text-gray-900">Profile</h2>
                    <button
                        @click="showProfileSlider = false"
                        class="p-2 hover:bg-gray-100 rounded-full transition-colors"
                        aria-label="Close profile menu"
                    >
                        <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                <UserCircleIconSolid class="w-8 h-8 text-white" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ user?.name }}</p>
                                <p class="text-sm text-gray-500">{{ user?.email }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="isAdmin || isManager" class="p-4 border-b border-gray-200">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Admin Access</h3>
                        <div class="space-y-2">
                            <Link
                                v-if="isAdmin"
                                href="/admin/dashboard"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-indigo-50 text-indigo-700 transition-colors"
                            >
                                <ShieldCheckIcon class="w-5 h-5" aria-hidden="true" />
                                <span class="text-sm font-medium">Admin Panel</span>
                            </Link>
                            <Link
                                v-if="isManager"
                                href="/manager/dashboard"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-purple-50 text-purple-700 transition-colors"
                            >
                                <UsersIcon class="w-5 h-5" aria-hidden="true" />
                                <span class="text-sm font-medium">Manager Dashboard</span>
                            </Link>
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Account</h3>
                        <div class="space-y-1">
                            <Link href="/settings" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors">
                                <Cog6ToothIcon class="w-5 h-5 text-gray-400" aria-hidden="true" />
                                <span class="text-sm">Settings</span>
                            </Link>
                        </div>
                    </div>

                    <div class="p-4 border-t border-gray-200 mt-auto">
                        <button
                            @click="logout"
                            class="flex items-center gap-3 w-full px-3 py-2 rounded-lg hover:bg-red-50 text-red-600 transition-colors"
                        >
                            <ArrowRightOnRectangleIcon class="w-5 h-5" aria-hidden="true" />
                            <span class="text-sm font-medium">Logout</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Overlay -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showProfileSlider"
                @click="showProfileSlider = false"
                class="fixed inset-0 bg-black/50 z-40"
            />
        </Transition>
    </div>
</template>
