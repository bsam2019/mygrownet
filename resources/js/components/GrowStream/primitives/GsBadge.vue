<script setup lang="ts">
import { computed } from 'vue';

type Variant = 'primary' | 'accent' | 'muted';

interface Props {
    variant?: Variant;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
});

const variantClass = computed(() => {
    const map: Record<Variant, string> = {
        primary: 'gs-chip-primary',
        accent: 'gs-chip-accent',
        muted: 'bg-[var(--gs-bg-elevated)] text-[var(--gs-muted)]',
    };
    return map[props.variant];
});
</script>

<template>
    <span :class="['gs-chip', variantClass]">
        <slot />
    </span>
</template>
