<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MemberLayout from './MemberLayout.vue';

interface Props {
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'MyGrowNet'
});
</script>

<template>
    <Head :title="title" />
    <MemberLayout>
        <slot />
    </MemberLayout>
</template>
