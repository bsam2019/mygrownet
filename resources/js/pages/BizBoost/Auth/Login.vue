<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import BizBoostGuestLayout from '@/layouts/BizBoostGuestLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('bizboost.sub.login'), {
        onFinish: () => {
            form.reset('password');
        },
        onError: () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    });
};
</script>

<template>
    <BizBoostGuestLayout>
        <Head title="Sign in - BizBoost" />

        <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-600 to-purple-700 flex items-center justify-center shadow-lg mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
                    <p class="text-gray-600 mt-1">Sign in with your MyGrowNet account</p>
                </div>

                <!-- Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
                    <!-- Shared account notice -->
                    <div class="mb-5 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                        <p class="text-xs text-purple-800 leading-relaxed">
                            BizBoost uses your <strong>MyGrowNet</strong> account.
                            Already have one? Sign in below.
                            New here?
                            <Link :href="route(&#39;bizboost.sub.register&#39;)" class="text-purple-700 font-medium hover:underline">Create an account</Link>
                            &mdash; it&#39;s shared across all MyGrowNet services.
                        </p>
                    </div>

                    <!-- Status Message -->
                    <div v-if="status" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                        {{ status }}
                    </div>

                    <!-- Error Message -->
                    <div v-if="form.hasErrors" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                        {{ form.errors.email || form.errors.password }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="email">Email or Phone</Label>
                            <Input
                                id="email"
                                type="text"
                                autofocus
                                autocomplete="username"
                                v-model="form.email"
                                placeholder="email@example.com or 0977123456"
                                class="mt-1"
                            />
                            <InputError :message="form.errors.email" class="mt-1" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <Label for="password">Password</Label>
                                <Link :href="route('bizboost.sub.password.request')" class="text-xs text-purple-600 hover:text-purple-700 font-medium">
                                    Forgot?
                                </Link>
                            </div>
                            <Input
                                id="password"
                                type="password"
                                autocomplete="current-password"
                                v-model="form.password"
                                placeholder="Enter your password"
                                class="mt-1"
                            />
                            <InputError :message="form.errors.password" class="mt-1" />
                        </div>

                        <Button
                            type="submit"
                            class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-medium py-5 shadow-sm"
                            :disabled="form.processing"
                        >
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                            Sign in
                        </Button>

                        <div class="text-center text-sm text-gray-600 pt-2">
                            Don't have an account?
                            <Link :href="route('bizboost.sub.register')" class="text-purple-600 hover:text-purple-700 font-medium">Sign up</Link>
                        </div>
                    </form>
                </div>

                <!-- Back to welcome -->
                <div class="text-center mt-6">
                    <Link :href="route('bizboost.sub.welcome')" class="text-sm text-gray-500 hover:text-gray-700">
                        &larr; Back to home
                    </Link>
                </div>
            </div>
        </div>
    </BizBoostGuestLayout>
</template>
