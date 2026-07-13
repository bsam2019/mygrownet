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
const tagline = computed(() => props.company?.tagline ?? 'Inventory Management System');
const logoUrl = computed(() => props.company?.logo_path ?? null);
</script>

<template>
    <Head :title="`${companyName} — StockFlow`" />

    <div class="min-h-screen flex flex-col" :style="{ backgroundColor: brandColor }">
        <div class="flex-1 flex items-center justify-center p-6">
            <div class="w-full max-w-lg text-center text-white">
                <div v-if="logoUrl" class="mb-6">
                    <img :src="logoUrl" :alt="`${companyName} logo`" class="h-20 mx-auto" />
                </div>
                <div v-else class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/20">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>

                <h1 class="text-4xl font-bold mb-3">{{ companyName }}</h1>
                <p class="text-xl text-white/80 mb-8">{{ tagline }}</p>

                <Link
                    :href="route('stockflow.sub.login')"
                    class="inline-block px-8 py-3 bg-white font-semibold rounded-xl shadow-lg hover:bg-white/90 transition-all"
                    :style="{ color: brandColor }"
                >
                    Sign In
                </Link>
            </div>
        </div>

        <div class="py-4 text-center text-white/50 text-sm">
            Powered by <span class="text-white/70 font-medium">StockFlow</span>
        </div>
    </div>
</template>
