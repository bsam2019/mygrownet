<script setup lang="ts">
import { computed } from 'vue';

type Variant = 'primary' | 'accent' | 'outline' | 'ghost';

interface Props {
    variant?: Variant;
    href?: string;
    type?: 'button' | 'submit';
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    type: 'button',
    disabled: false,
});

const variantClass = computed(() => {
    const map: Record<Variant, string> = {
        primary: 'gs-btn-primary',
        accent: 'gs-btn-accent',
        outline: 'gs-btn-outline',
        ghost: 'gs-btn-ghost',
    };
    return map[props.variant];
});
</script>

<template>
    <a
        v-if="href"
        :href="href"
        :class="['gs-btn', variantClass, disabled ? 'pointer-events-none opacity-50' : '']"
    >
        <slot />
    </a>
    <button
        v-else
        :type="type"
        :disabled="disabled"
        :class="['gs-btn', variantClass, disabled ? 'pointer-events-none opacity-50' : '']"
    >
        <slot />
    </button>
</template>
