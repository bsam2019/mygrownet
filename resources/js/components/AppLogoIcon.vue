<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();

const logoUrl = computed(() => '/logo.png');
</script>

<template>
    <img 
        :src="logoUrl" 
        alt="MyGrowNet Logo" 
        :class="className" 
        v-bind="$attrs"
        class="object-contain w-auto"
    />
</template>
