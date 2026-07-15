<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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
const tagline = computed(() => props.company?.tagline ?? 'Complete Stock Management & Audit Solution');
const logoUrl = computed(() => props.company?.logo_path ?? null);
const isMarketingPage = computed(() => !props.company); // True for stockflow.mygrownet.com
</script>

<template>
    <Head :title="companyName" />

    <div class="min-h-screen flex flex-col bg-gray-50 overflow-hidden">
        <!-- Background decoration -->
        <div class="fixed inset-0 pointer-events-none">
            <div
                class="absolute -top-40 -right-40 w-[500px] h-[500px] rounded-full opacity-[0.04]"
                :style="{ backgroundColor: brandColor }"
            />
            <div
                class="absolute -bottom-40 -left-40 w-[400px] h-[400px] rounded-full opacity-[0.03]"
                :style="{ backgroundColor: brandColor }"
            />
        </div>

        <!-- Header -->
        <header class="relative z-10 px-6 py-5">
            <div class="mx-auto max-w-6xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div v-if="logoUrl">
                        <img :src="logoUrl" :alt="`${companyName} logo`" class="h-9 w-auto" />
                    </div>
                    <div v-else class="flex items-center justify-center w-9 h-9 rounded-lg font-bold text-sm shadow-sm ring-1 ring-black/5" :style="{ backgroundColor: brandColor, color: '#fff' }">
                        {{ companyName.charAt(0) }}
                    </div>
                </div>

                <Link
                    v-if="isMarketingPage"
                    href="/admin/login"
                    class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold shadow-sm ring-1 ring-inset transition-all hover:shadow-md"
                    :style="{
                        backgroundColor: brandColor,
                        color: '#fff',
                    }"
                >
                    Admin Login
                </Link>
                <Link
                    v-else
                    :href="route('stockflow.sub.login', { account: company?.subdomain })"
                    class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold shadow-sm ring-1 ring-inset transition-all hover:shadow-md"
                    :style="{
                        backgroundColor: brandColor,
                        color: '#fff',
                    }"
                >
                    Sign in
                </Link>
            </div>
        </header>

        <!-- Hero -->
        <main class="relative z-10 flex-1 flex items-center justify-center px-6 py-20">
            <div class="mx-auto max-w-xl text-center">
                <div class="mb-10 flex justify-center">
                    <div
                        class="relative flex items-center justify-center w-24 h-24 rounded-[1.75rem] shadow-lg ring-1 ring-black/5 transition-transform hover:scale-[1.02]"
                        :style="{ backgroundColor: brandColor }"
                    >
                        <span class="text-4xl font-bold text-white">{{ companyName.charAt(0) }}</span>
                    </div>
                </div>

                <h1 class="text-5xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                    {{ companyName }}
                </h1>

                <p v-if="tagline" class="mt-5 text-lg text-gray-500 leading-relaxed max-w-md mx-auto">
                    {{ tagline }}
                </p>
                <p v-else-if="isMarketingPage" class="mt-5 text-lg text-gray-500 leading-relaxed max-w-md mx-auto">
                    Complete inventory management, stock audit, and business analytics solution for modern businesses.
                </p>
                <p v-else class="mt-5 text-lg text-gray-500 leading-relaxed max-w-md mx-auto">
                    Welcome to your inventory management portal.
                </p>

                <div class="mt-12">
                    <Link
                        v-if="isMarketingPage"
                        href="/admin/login"
                        class="inline-flex items-center gap-2.5 rounded-xl px-8 py-3.5 text-base font-semibold shadow-md transition-all hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0"
                        :style="{ backgroundColor: brandColor, color: '#fff' }"
                    >
                        Get Started
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </Link>
                    <Link
                        v-else
                        :href="route('stockflow.sub.login', { account: company?.subdomain })"
                        class="inline-flex items-center gap-2.5 rounded-xl px-8 py-3.5 text-base font-semibold shadow-md transition-all hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0"
                        :style="{ backgroundColor: brandColor, color: '#fff' }"
                    >
                        Sign in to Dashboard
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </Link>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 px-6 py-6">
            <div class="mx-auto max-w-6xl flex items-center justify-center gap-2">
                <span class="text-xs text-gray-400">Powered by</span>
                <span class="text-xs font-medium text-gray-500">StockFlow</span>
            </div>
        </footer>
    </div>
</template>
