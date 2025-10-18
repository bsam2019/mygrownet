<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import InvestorLayout from './InvestorLayout.vue';

interface Props {
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'MyGrowNet'
});
</script>

<template>
    <Head :title="title" />
    <InvestorLayout>
        <slot />
    </InvestorLayout>
</template>
