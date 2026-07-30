<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import GrowNetGuestLayout from '@/layouts/GrowNetGuestLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    token: string;
    email: string;
}>();

const form = useForm({
    token: $props.token,
    email: $props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('grownet.sub.password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GrowNetGuestLayout>
        <Head title="Reset Password - GrowNet" />
        <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-600 to-fuchsia-600 flex items-center justify-center shadow-lg mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Reset your password</h2>
                    <p class="text-gray-600 mt-1">Choose a new password for your account.</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="form.email" disabled class="mt-1 bg-gray-50" />
                        </div>
                        <div>
                            <Label for="password">New Password</Label>
                            <Input id="password" type="password" autofocus autocomplete="new-password" v-model="form.password" placeholder="Enter new password" class="mt-1" />
                            <InputError :message="form.errors.password" class="mt-1" />
                        </div>
                        <div>
                            <Label for="password_confirmation">Confirm Password</Label>
                            <Input id="password_confirmation" type="password" autocomplete="new-password" v-model="form.password_confirmation" placeholder="Confirm new password" class="mt-1" />
                            <InputError :message="form.errors.password_confirmation" class="mt-1" />
                        </div>
                        <Button type="submit" class="w-full bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 text-white font-medium py-5 shadow-lg shadow-violet-500/25" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                            Reset Password
                        </Button>
                    </form>
                </div>
                <div class="text-center mt-6">
                    <Link :href="route('grownet.sub.login')" class="text-sm text-violet-600 hover:text-violet-700 font-medium">&larr; Back to login</Link>
                </div>
            </div>
        </div>
    </GrowNetGuestLayout>
</template>
