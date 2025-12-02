<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    ShieldCheckIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    userRole: string;
}

const props = defineProps<Props>();

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('growbiz.settings.security.password'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <GrowBizLayout>
        <Head title="Security - GrowBiz" />
        
        <div class="px-4 py-4 pb-6">
            <!-- Page Header -->
            <div class="flex items-center gap-3 mb-6">
                <Link 
                    :href="route('growbiz.settings.index')" 
                    class="p-2 -ml-2 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition-colors"
                    aria-label="Back to settings"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">Security</h1>
            </div>

            <!-- Security Icon -->
            <div class="flex justify-center mb-6">
                <div class="h-20 w-20 rounded-full bg-amber-50 flex items-center justify-center">
                    <ShieldCheckIcon class="h-10 w-10 text-amber-600" aria-hidden="true" />
                </div>
            </div>

            <p class="text-center text-gray-600 mb-6">
                Keep your account secure by using a strong password
            </p>

            <!-- Password Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <div class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                            Current Password
                        </label>
                        <div class="relative">
                            <input
                                id="current_password"
                                v-model="form.current_password"
                                :type="showCurrentPassword ? 'text' : 'password'"
                                class="w-full pl-10 pr-12 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-colors"
                                placeholder="Enter current password"
                            />
                            <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                            <button
                                type="button"
                                @click="showCurrentPassword = !showCurrentPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600"
                                :aria-label="showCurrentPassword ? 'Hide password' : 'Show password'"
                            >
                                <EyeSlashIcon v-if="showCurrentPassword" class="h-5 w-5" aria-hidden="true" />
                                <EyeIcon v-else class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                        <p v-if="form.errors.current_password" class="mt-1 text-sm text-red-600">{{ form.errors.current_password }}</p>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            New Password
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showNewPassword ? 'text' : 'password'"
                                class="w-full pl-10 pr-12 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-colors"
                                placeholder="Enter new password"
                            />
                            <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                            <button
                                type="button"
                                @click="showNewPassword = !showNewPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600"
                                :aria-label="showNewPassword ? 'Hide password' : 'Show password'"
                            >
                                <EyeSlashIcon v-if="showNewPassword" class="h-5 w-5" aria-hidden="true" />
                                <EyeIcon v-else class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirm New Password
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                class="w-full pl-10 pr-12 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-colors"
                                placeholder="Confirm new password"
                            />
                            <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600"
                                :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                            >
                                <EyeSlashIcon v-if="showConfirmPassword" class="h-5 w-5" aria-hidden="true" />
                                <EyeIcon v-else class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Password Requirements -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Password requirements:</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            At least 8 characters long
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Mix of letters and numbers recommended
                        </li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 py-3.5 px-4 bg-amber-600 text-white font-semibold rounded-xl hover:bg-amber-700 active:bg-amber-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <CheckIcon v-if="!form.processing" class="h-5 w-5" aria-hidden="true" />
                    <span v-if="form.processing">Updating...</span>
                    <span v-else>Update Password</span>
                </button>
            </form>
        </div>
    </GrowBizLayout>
</template>
