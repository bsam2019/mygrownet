<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    title?: string;
    viewAllLink?: string;
}

const props = defineProps<Props>();
</script>

<template>
    <div>
        <div v-if="props.title" class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-bold text-[var(--gs-text)]">{{ props.title }}</h2>
            <slot name="header-action">
                <a
                    v-if="props.viewAllLink"
                    :href="props.viewAllLink"
                    class="text-sm font-medium text-[var(--gs-accent)] hover:opacity-85"
                >
                    View All →
                </a>
            </slot>
        </div>
        <div class="gs-row">
            <slot />
        </div>
    </div>
</template>
