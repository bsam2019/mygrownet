<script setup lang="ts">
import { computed } from 'vue';
import { useLifePlusAccess } from '@/composables/useLifePlusAccess';
import UpgradePrompt from './UpgradePrompt.vue';

const props = defineProps<{
    feature: string;
    title?: string;
    message?: string;
    showBenefits?: boolean;
    compact?: boolean;
    hideIfLocked?: boolean;
}>();

const { canAccess, hasReachedLimit } = useLifePlusAccess();

const isLocked = computed(() => {
    // Check if feature is disabled
    if (!canAccess(props.feature)) {
        return true;
    }
    
    // Check if limit is reached (for features like tasks, habits)
    if (props.feature.endsWith('_limit')) {
        const baseFeature = props.feature.replace('_limit', '');
        return hasReachedLimit(baseFeature);
    }
    
    return false;
});
</script>

<template>
    <!-- Hide completely if locked and hideIfLocked is true -->
    <template v-if="hideIfLocked && isLocked">
        <!-- Nothing rendered -->
    </template>
    
    <!-- Show upgrade prompt if locked -->
    <template v-else-if="isLocked">
        <UpgradePrompt
            :feature="feature"
            :title="title"
            :message="message"
            :show-benefits="showBenefits"
            :compact="compact"
        />
    </template>
    
    <!-- Show content if unlocked -->
    <template v-else>
        <slot />
    </template>
</template>
