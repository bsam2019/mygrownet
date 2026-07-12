<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimeEdgeAuthLayout from '@/layouts/PrimeEdgeAuthLayout.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    company: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('primeedge.register.store'), {
        onError: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <PrimeEdgeAuthLayout title="Create Your Account" description="Join PrimeEdge Advisory and take control of your finances">
        <Head title="Register - PrimeEdge Advisory" />

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="register-name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                <input v-model="form.name" id="register-name" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="Your full name" />
                <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
            </div>
            <div>
                <label for="register-email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input v-model="form.email" id="register-email" type="email" autocomplete="email" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="you@example.com" />
                <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="register-phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input v-model="form.phone" id="register-phone" type="tel" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="+260 XXX XXX XXX" />
                </div>
                <div>
                    <label for="register-company" class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                    <input v-model="form.company" id="register-company" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="Your company" />
                </div>
            </div>
            <div>
                <label for="register-password" class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                <input v-model="form.password" id="register-password" type="password" autocomplete="new-password" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="Min. 8 characters" />
                <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
            </div>
            <div>
                <label for="register-password-confirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                <input v-model="form.password_confirmation" id="register-password-confirm" type="password" autocomplete="new-password" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="Repeat password" />
            </div>
            <button type="submit" :disabled="form.processing" class="w-full px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all disabled:opacity-50 shadow-md">
                {{ form.processing ? 'Creating account...' : 'Create Account' }}
            </button>
        </form>
        <p class="text-center text-sm text-gray-600 mt-6">
            Already have an account? <Link :href="route('primeedge.login')" class="text-emerald-700 font-medium hover:underline">Sign in</Link>
        </p>
    </PrimeEdgeAuthLayout>
</template>