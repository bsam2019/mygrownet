<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { usePermissions } from '@/composables/usePermissions';
import {
    UserCircleIcon,
    EnvelopeIcon,
    PhoneIcon,
    ShieldCheckIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const { isAdmin } = usePermissions();

const form = useForm({
    name: user.value?.name || '',
    email: user.value?.email || '',
    phone: user.value?.phone || '',
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <ClientLayout>
        <Head title="Profile Settings" />

        <div class="max-w-2xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <Link
                    href="/dashboard"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-4"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Dashboard
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
                <p class="text-gray-500 mt-1">Manage your personal information</p>
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

            <!-- Profile Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Profile Header -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 px-6 py-8 text-center">
                    <div class="w-20 h-20 mx-auto rounded-full bg-white/20 flex items-center justify-center mb-4">
                        <UserCircleIcon class="h-12 w-12 text-white" aria-hidden="true" />
                    </div>
                    <h2 class="text-xl font-bold text-white">{{ user?.name }}</h2>
                    <p class="text-blue-100 text-sm">{{ user?.email }}</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Name Field -->
                    <div>
                        <label for="name" class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                            <UserCircleIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                            Full Name
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autocomplete="name"
                            placeholder="Enter your full name"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            :class="{ 'border-red-300': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                            <EnvelopeIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                            Email Address
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="username"
                            placeholder="Enter your email"
                            :disabled="!isAdmin"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors disabled:bg-gray-50 disabled:text-gray-500"
                            :class="{ 'border-red-300': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                        <p v-if="!isAdmin" class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                            <ShieldCheckIcon class="h-3 w-3" aria-hidden="true" />
                            Only administrators can change email addresses
                        </p>
                    </div>

                    <!-- Phone Field -->
                    <div>
                        <label for="phone" class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                            <PhoneIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                            Mobile Money Number
                        </label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            autocomplete="tel"
                            placeholder="0971234567"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            :class="{ 'border-red-300': form.errors.phone }"
                        />
                        <p v-if="form.errors.phone" class="mt-2 text-sm text-red-600">{{ form.errors.phone }}</p>
                        <p class="mt-2 text-xs text-gray-500">
                            Used for MTN/Airtel Money withdrawals
                        </p>
                    </div>

                    <!-- Email Verification Warning -->
                    <div v-if="mustVerifyEmail && !user?.email_verified_at" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <ExclamationTriangleIcon class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                            <div>
                                <p class="text-sm text-amber-800 font-medium">Email not verified</p>
                                <p class="text-sm text-amber-700 mt-1">
                                    <Link
                                        :href="route('verification.send')"
                                        method="post"
                                        as="button"
                                        class="underline hover:no-underline"
                                    >
                                        Click here to resend verification email
                                    </Link>
                                </p>
                                <p v-if="status === 'verification-link-sent'" class="text-sm text-emerald-600 mt-2 font-medium">
                                    ✓ Verification link sent!
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                        
                        <Transition
                            enter-active-class="transition ease-out duration-300"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in duration-200"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="form.recentlySuccessful" class="text-center text-sm text-emerald-600 mt-3 font-medium">
                                ✓ Changes saved successfully
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>

            <!-- Quick Links -->
            <div class="mt-6 grid grid-cols-2 gap-4">
                <Link
                    href="/settings/password"
                    class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow text-center"
                >
                    <ShieldCheckIcon class="h-6 w-6 text-gray-400 mx-auto mb-2" aria-hidden="true" />
                    <span class="text-sm font-medium text-gray-700">Change Password</span>
                </Link>
                <Link
                    href="/settings/appearance"
                    class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow text-center"
                >
                    <svg class="h-6 w-6 text-gray-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Appearance</span>
                </Link>
            </div>
        </div>
    </ClientLayout>
</template>
