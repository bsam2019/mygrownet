<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GrowStreamLayout from '@/layouts/GrowStreamLayout.vue';

interface Props {
    title: string;
    subtitle?: string;
}

withDefaults(defineProps<Props>(), {
    subtitle: '',
});
</script>

<template>
    <GrowStreamLayout :title="`${title} — GrowStream`" show-promo>
        <Head :title="`${title} — GrowStream`" />

        <div class="max-w-3xl mx-auto">
            <!-- Page hero -->
            <div class="pt-8 pb-10 text-center">
                <h1 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg font-extrabold text-on-surface mb-3">{{ title }}</h1>
                <p v-if="subtitle" class="font-body-lg text-body-lg text-on-surface-variant">{{ subtitle }}</p>
            </div>

            <!-- Content -->
            <div class="space-y-10">
                <slot />
            </div>
        </div>
    </GrowStreamLayout>
</template>
