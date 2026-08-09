<template>
    <div
        class="min-h-screen flex flex-col font-body-md antialiased text-on-background bg-background"
        :style="{ '--portal-primary': platform?.brand_color || '#e2571f' }"
    >
        <Head :title="`${platform?.brand_name || 'Creator Platform'} - Powered by GrowStream Hub`" />

        <!-- Branded Portal Header -->
        <header class="sticky top-0 z-40 bg-surface-container-lowest/95 backdrop-blur border-b border-outline-variant/60">
            <div class="max-w-6xl mx-auto px-margin-mobile md:px-margin-desktop h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img v-if="platform?.logo_url" :src="platform.logo_url" :alt="platform.brand_name" class="h-8 w-auto object-contain" />
                    <div v-else class="h-9 w-9 rounded-xl bg-primary text-on-primary flex items-center justify-center font-bold font-headline-sm">
                        {{ (platform?.brand_name || 'P').charAt(0).toUpperCase() }}
                    </div>
                    <span class="font-headline-sm text-headline-sm font-bold text-on-surface">{{ platform?.brand_name || 'Creator Platform' }}</span>
                </div>

                <div class="flex items-center gap-4 text-xs text-on-surface-variant">
                    <a :href="route('growstream.home')" class="hover:text-primary transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">open_in_new</span> GrowStream Main
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 max-w-6xl w-full mx-auto px-margin-mobile md:px-margin-desktop py-8">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-surface-container-lowest border-t border-outline-variant/60 py-6 text-center text-xs text-on-surface-variant">
            <p>&copy; {{ new Date().getFullYear() }} {{ platform?.brand_name || 'Creator Platform' }}. Powered by GrowStream Hub.</p>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Props {
    platform?: any;
}

defineProps<Props>();
</script>
