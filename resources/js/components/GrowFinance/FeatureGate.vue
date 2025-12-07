<template>
    <div>
        <!-- Feature Available -->
        <slot v-if="hasAccess" />
        
        <!-- Feature Locked -->
        <div v-else class="relative">
            <!-- Blurred/Disabled Content -->
            <div class="opacity-50 pointer-events-none select-none" :class="blurContent ? 'blur-sm' : ''">
                <slot name="preview" />
            </div>
            
            <!-- Upgrade Overlay -->
            <div 
                v-if="showOverlay"
                class="absolute inset-0 flex items-center justify-center bg-white/80 backdrop-blur-sm rounded-xl"
            >
                <div class="text-center p-6 max-w-sm">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                        <LockClosedIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">{{ title }}</h3>
                    <p class="text-sm text-gray-600 mb-4">{{ description }}</p>
                    <Link 
                        :href="route('growfinance.upgrade')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                    >
                        <SparklesIcon class="h-4 w-4" aria-hidden="true" />
                        Upgrade to {{ requiredTierName }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { LockClosedIcon, SparklesIcon } from '@heroicons/vue/24/outline';

interface Props {
    feature: string;
    requiredTier?: string;
    title?: string;
    description?: string;
    showOverlay?: boolean;
    blurContent?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    requiredTier: 'basic',
    title: 'Premium Feature',
    description: 'Upgrade your plan to unlock this feature.',
    showOverlay: true,
    blurContent: false,
});

const page = usePage();

// Get current tier from page props (shared by GrowFinanceStandalone middleware)
const currentTier = computed(() => {
    const props = page.props as any;
    return props.subscription_tier || 'free';
});

// Tier hierarchy for comparison
const tierHierarchy: Record<string, number> = {
    free: 0,
    basic: 1,
    professional: 2,
    business: 3,
};

const hasAccess = computed(() => {
    const currentLevel = tierHierarchy[currentTier.value] ?? 0;
    const requiredLevel = tierHierarchy[props.requiredTier] ?? 0;
    return currentLevel >= requiredLevel;
});

const requiredTierName = computed(() => {
    const names: Record<string, string> = {
        basic: 'Basic',
        professional: 'Professional',
        business: 'Business',
    };
    return names[props.requiredTier] || 'Premium';
});
</script>
