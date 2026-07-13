<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    company: {
        id: number;
        name: string;
        subdomain: string | null;
        tagline: string | null;
        logo_path: string | null;
        brand_color: string;
    } | null;
}>();

const brandColor = computed(() => props.company?.brand_color ?? '#059669');
const companyName = computed(() => props.company?.name ?? 'StockFlow');
const tagline = computed(() => props.company?.tagline ?? 'Inventory Management System');
const logoUrl = computed(() => props.company?.logo_path ?? null);
const account = computed(() => props.company?.subdomain ?? '');

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('stockflow.sub.login.store', { account: account.value }), {
        onError: () => form.reset('password'),
    });
};
</script>

<template>
    <Head :title="`Sign In — ${companyName}`" />

    <div class="min-h-screen flex flex-col" :style="{ backgroundColor: brandColor }">
        <div class="flex-1 flex items-center justify-center p-4">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <div v-if="logoUrl" class="mb-4">
                        <img :src="logoUrl" :alt="`${companyName} logo`" class="h-16 mx-auto" />
                    </div>
                    <h1 class="text-2xl font-bold text-white">{{ companyName }}</h1>
                    <p class="text-white/70 mt-1">{{ tagline }}</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="form.email" id="email" type="email" autocomplete="email" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="you@example.com" />
                            <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input v-model="form.password" id="password" type="password" autocomplete="current-password" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="Your password" />
                            <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                                <span class="text-sm text-gray-600">Remember me</span>
                            </label>
                        </div>

                        <button type="submit" :disabled="form.processing" class="w-full px-6 py-3 rounded-xl text-white font-semibold shadow-md hover:brightness-110 transition-all disabled:opacity-50" :style="{ backgroundColor: brandColor }">
                            {{ form.processing ? 'Signing in...' : 'Sign In' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="py-4 text-center text-white/50 text-sm">
            Powered by <span class="text-white/70 font-medium">StockFlow</span>
        </div>
    </div>
</template>
