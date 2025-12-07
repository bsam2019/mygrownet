<template>
    <div>
        <!-- Feature is available -->
        <slot v-if="hasAccess" />
        
        <!-- Feature is locked -->
        <div v-else>
            <!-- Custom locked slot if provided -->
            <slot name="locked" v-if="$slots.locked" />
            
            <!-- Default locked state -->
            <div 
                v-else
                class="relative rounded-xl border-2 border-dashed border-gray-200 p-6 text-center"
                :class="{ 'bg-gray-50': !inline }"
            >
                <div v-if="!inline" class="mb-4">
                    <div class="mx-auto w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <LockClosedIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                    </div>
                </div>
                
                <h3 v-if="!inline" class="text-lg font-semibold text-gray-900 mb-2">
                    {{ title || 'Premium Feature' }}
                </h3>
                
                <p class="text-gray-600 mb-4" :class="{ 'text-sm': inline }">
                    {{ message || `This feature requires the ${requiredTier} plan or higher.` }}
                </p>
                
                <Link 
                    :href="route('growbiz.upgrade')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                    :class="{ 'text-sm px-3 py-1.5': inline }"
                >
                    <SparklesIcon class="h-4 w-4" aria-hidden="true" />
                    Upgrade to {{ requiredTier }}
                </Link>
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
    message?: string;
    inline?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    requiredTier: 'Professional',
    inline: false,
});

const page = usePage();

// Tier hierarchy for comparison
const tierHierarchy: Record<string, number> = {
    free: 0,
    basic: 1,
    professional: 2,
    business: 3,
};

// Feature to minimum tier mapping
const featureTiers: Record<string, string> = {
    // Basic tier features
    time_tracking: 'basic',
    csv_export: 'basic',
    task_templates: 'basic',
    task_priorities: 'basic',
    task_categories: 'basic',
    
    // Professional tier features
    projects: 'professional',
    gantt_charts: 'professional',
    task_dependencies: 'professional',
    custom_fields: 'professional',
    recurring_tasks: 'professional',
    integrations: 'professional',
    pdf_export: 'professional',
    performance_reports: 'professional',
    
    // Business tier features
    multi_location: 'business',
    advanced_analytics: 'business',
    api_access: 'business',
    white_label: 'business',
    custom_integrations: 'business',
    audit_trail: 'business',
    sso: 'business',
};

const currentTier = computed(() => {
    return (page.props as any).subscription_tier || 'free';
});

const minimumTier = computed(() => {
    return featureTiers[props.feature] || props.requiredTier?.toLowerCase() || 'professional';
});

const hasAccess = computed(() => {
    const currentLevel = tierHierarchy[currentTier.value] || 0;
    const requiredLevel = tierHierarchy[minimumTier.value] || 2;
    return currentLevel >= requiredLevel;
});

const requiredTier = computed(() => {
    const tier = minimumTier.value;
    return tier.charAt(0).toUpperCase() + tier.slice(1);
});
</script>
