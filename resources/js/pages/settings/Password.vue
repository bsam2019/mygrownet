<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import {
    LockClosedIcon,
    ShieldCheckIcon,
    CheckCircleIcon,
    ArrowLeftIcon,
    EyeIcon,
    EyeSlashIcon,
} from '@heroicons/vue/24/outline';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                if (passwordInput.value instanceof HTMLInputElement) {
                    passwordInput.value.focus();
                }
            }
            if (errors.current_password) {
                form.reset('current_password');
                if (currentPasswordInput.value instanceof HTMLInputElement) {
                    currentPasswordInput.value.focus();
                }
            }
        },
    });
};
</script>

<template>
    <ClientLayout>
        <Head title="Change Password" />

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
                <h1 class="text-2xl font-bold text-gray-900">Change Password</h1>
                <p class="text-gray-500 mt-1">Keep your account secure with a strong password</p>
            </div>

            <!-- Success Message -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
            >
                <div v-if="form.recentlySuccessful" class="mb-6 bg-emerald-50 border border-emerald-200 rounded-2xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                            <CheckCircleIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                        </div>
                        <p class="text-sm font-medium text-emerald-800">Password updated successfully!</p>
                    </div>
                </div>
            </Transition>

            <!-- Password Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 px-6 py-8 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-white/20 flex items-center justify-center mb-4">
                        <ShieldCheckIcon class="h-8 w-8 text-white" aria-hidden="true" />
                    </div>
                    <h2 class="text-xl font-bold text-white">Security Settings</h2>
                    <p class="text-indigo-100 text-sm mt-1">Update your password regularly</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="updatePassword" class="p-6 space-y-6">
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                            <LockClosedIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                            Current Password
                        </label>
                        <div class="relative">
                            <input
                                id="current_password"
                                ref="currentPasswordInput"
                                v-model="form.current_password"
                                :type="showCurrentPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="Enter current password"
                                class="w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                :class="{ 'border-red-300': form.errors.current_password }"
                            />
                            <button
                                type="button"
                                @click="showCurrentPassword = !showCurrentPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <EyeSlashIcon v-if="showCurrentPassword" class="h-5 w-5" aria-hidden="true" />
                                <EyeIcon v-else class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                        <p v-if="form.errors.current_password" class="mt-2 text-sm text-red-600">{{ form.errors.current_password }}</p>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                            <LockClosedIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                            New Password
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                ref="passwordInput"
                                v-model="form.password"
                                :type="showNewPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                placeholder="Enter new password"
                                class="w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                :class="{ 'border-red-300': form.errors.password }"
                            />
                            <button
                                type="button"
                                @click="showNewPassword = !showNewPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <EyeSlashIcon v-if="showNewPassword" class="h-5 w-5" aria-hidden="true" />
                                <EyeIcon v-else class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</p>
                        <p class="mt-2 text-xs text-gray-500">Use at least 8 characters with letters and numbers</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                            <LockClosedIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                            Confirm New Password
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                placeholder="Confirm new password"
                                class="w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                :class="{ 'border-red-300': form.errors.password_confirmation }"
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <EyeSlashIcon v-if="showConfirmPassword" class="h-5 w-5" aria-hidden="true" />
                                <EyeIcon v-else class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                        <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3 px-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-600 hover:to-purple-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ form.processing ? 'Updating...' : 'Update Password' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security Tips -->
            <div class="mt-6 bg-amber-50 border border-amber-200 rounded-2xl p-4">
                <h3 class="text-sm font-semibold text-amber-800 mb-2">Security Tips</h3>
                <ul class="text-xs text-amber-700 space-y-1">
                    <li>• Use a unique password you don't use elsewhere</li>
                    <li>• Include uppercase, lowercase, numbers, and symbols</li>
                    <li>• Avoid personal information like birthdays or names</li>
                    <li>• Consider using a password manager</li>
                </ul>
            </div>
        </div>
    </ClientLayout>
</template>
