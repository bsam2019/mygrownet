<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import VenturesGuestLayout from '@/layouts/VenturesGuestLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{ token: string; email: string }>();

const showPassword = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('venture.sub.password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
        onError: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
    });
};
</script>

<template>
    <VenturesGuestLayout>
        <Head title="Reset Password - Venture Builder" />
        <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Set new password</h2>
                    <p class="text-gray-600 mt-1">Choose a strong password for your account</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" autocomplete="email" v-model="form.email" class="mt-1 bg-gray-50" readonly disabled />
                            <InputError :message="form.errors.email" class="mt-1" />
                        </div>
                        <div>
                            <Label for="password">New Password</Label>
                            <div class="relative mt-1">
                                <Input id="password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" v-model="form.password" placeholder="Enter new password" class="pr-10" />
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                </button>
                            </div>
                            <InputError :message="form.errors.password" class="mt-1" />
                        </div>
                        <div>
                            <Label for="password_confirmation">Confirm Password</Label>
                            <Input id="password_confirmation" type="password" autocomplete="new-password" v-model="form.password_confirmation" placeholder="Confirm new password" class="mt-1" />
                            <InputError :message="form.errors.password_confirmation" class="mt-1" />
                        </div>
                        <Button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-medium py-5 shadow-sm" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                            Reset password
                        </Button>
                    </form>
                    <div class="text-center mt-6">
                        <Link :href="route('venture.sub.login')" class="text-sm text-amber-600 hover:text-amber-700 font-medium">&larr; Back to sign in</Link>
                    </div>
                </div>
                <div class="text-center mt-6">
                    <Link :href="route('venture.sub.welcome')" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to home</Link>
                </div>
            </div>
        </div>
    </VenturesGuestLayout>
</template>
