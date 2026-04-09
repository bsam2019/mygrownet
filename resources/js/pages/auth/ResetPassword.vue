<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};

const togglePassword = (fieldId: string) => {
    const input = document.getElementById(fieldId) as HTMLInputElement;
    if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
    }
};
</script>

<template>
    <Head title="Reset Password" />

    <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 relative overflow-hidden">
        <!-- Background decoration -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_30%_20%,rgba(59,130,246,0.15)_0%,transparent_50%),radial-gradient(circle_at_70%_80%,rgba(16,185,129,0.1)_0%,transparent_50%)] animate-float"></div>
        </div>

        <div class="w-full max-width-[440px] relative z-10">
            <div class="bg-white/98 rounded-3xl shadow-2xl overflow-hidden backdrop-blur-xl">
                <!-- Header -->
                <div class="p-8 pb-6 text-center bg-gradient-to-b from-gray-50 to-white border-b border-gray-100">
                    <a href="/" class="inline-flex items-center justify-center mb-4 transition-transform hover:scale-105">
                        <img src="/logo.png" alt="MyGrowNet" class="h-14 w-auto drop-shadow-md" />
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Reset Password</h1>
                    <p class="text-sm text-gray-500 mt-1">Enter your new password below</p>
                </div>

                <!-- Form Body -->
                <div class="p-8 pt-6">
                    <!-- Error Alert -->
                    <div v-if="form.errors.email || form.errors.password" class="mb-6 p-4 rounded-xl bg-gradient-to-br from-red-50 to-red-100 border border-red-200 flex items-start gap-3 animate-slideIn">
                        <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-red-900">Unable to reset password</p>
                            <p class="text-sm text-red-800 mt-0.5">{{ form.errors.email || form.errors.password }}</p>
                        </div>
                    </div>

                    <form @submit.prevent="submit">
                        <!-- Email (readonly) -->
                        <div class="mb-5">
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                            <input 
                                id="email" 
                                type="email" 
                                v-model="form.email" 
                                readonly
                                class="w-full px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl transition-all bg-gray-50 text-gray-600 cursor-not-allowed"
                            />
                        </div>

                        <!-- New Password -->
                        <div class="mb-5">
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                            <div class="relative">
                                <input 
                                    id="password" 
                                    type="password" 
                                    v-model="form.password" 
                                    placeholder="Create a strong password"
                                    autocomplete="new-password"
                                    autofocus
                                    class="w-full px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10"
                                    :class="{ 'border-red-500 bg-red-50': form.errors.password }"
                                />
                                <button 
                                    type="button" 
                                    @click="togglePassword('password')"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="text-xs text-red-600 mt-1.5 font-medium">{{ form.errors.password }}</p>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-6">
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                            <div class="relative">
                                <input 
                                    id="password_confirmation" 
                                    type="password" 
                                    v-model="form.password_confirmation" 
                                    placeholder="Confirm your password"
                                    autocomplete="new-password"
                                    class="w-full px-4 py-3.5 text-base border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10"
                                />
                                <button 
                                    type="button" 
                                    @click="togglePassword('password_confirmation')"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full flex items-center justify-center gap-2 px-6 py-4 text-base font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg shadow-blue-600/40 transition-all hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-600/50 active:translate-y-0 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
                        >
                            <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <span>{{ form.processing ? 'Resetting password...' : 'Reset Password' }}</span>
                        </button>

                        <!-- Support Info -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <p class="text-sm text-gray-600 mb-2">
                                <span class="font-semibold text-gray-700">Need help?</span>
                            </p>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                If you're unable to reset your password, contact our support team:<br>
                                📧 <a href="mailto:support@mygrownet.com" class="text-blue-600 hover:underline">support@mygrownet.com</a><br>
                                📱 WhatsApp: <a href="https://wa.me/260779872676" target="_blank" class="text-blue-600 hover:underline">+260 779 872 676</a>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="text-center p-6 bg-gray-50 border-t border-gray-100">
                    <p class="text-sm text-gray-500">
                        Remember your password? 
                        <a href="/login" class="text-blue-600 font-medium hover:text-blue-700 hover:underline">Back to login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style scoped>
    @keyframes float {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        50% { transform: translate(-2%, 2%) rotate(1deg); }
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-float {
        animation: float 20s ease-in-out infinite;
    }

    .animate-slideIn {
        animation: slideIn 0.3s ease;
    }
    </style>
</template>
