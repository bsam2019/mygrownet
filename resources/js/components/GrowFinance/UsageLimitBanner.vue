<template>
    <div v-if="showBanner" class="mb-4">
        <!-- Warning Banner (80%+ usage) -->
        <div 
            v-if="usagePercent >= 80 && usagePercent < 100"
            class="bg-amber-50 border border-amber-200 rounded-xl p-4"
        >
            <div class="flex items-start gap-3">
                <ExclamationTriangleIcon class="h-5 w-5 text-amber-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                <div class="flex-1">
                    <p class="font-medium text-amber-800">
                        {{ warningMessage }}
                    </p>
                    <p class="text-sm text-amber-700 mt-1">
                        You've used {{ used }} of {{ limit }} {{ resourceName }} this month.
                    </p>
                </div>
                <Link 
                    :href="route('growfinance.upgrade')"
                    class="text-sm font-medium text-amber-700 hover:text-amber-800 whitespace-nowrap"
                >
                    Upgrade →
                </Link>
            </div>
        </div>

        <!-- Limit Reached Banner -->
        <div 
            v-else-if="usagePercent >= 100"
            class="bg-red-50 border border-red-200 rounded-xl p-4"
        >
            <div class="flex items-start gap-3">
                <XCircleIcon class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                <div class="flex-1">
                    <p class="font-medium text-red-800">
                        {{ limitMessage }}
                    </p>
                    <p class="text-sm text-red-700 mt-1">
                        Upgrade your plan to continue adding {{ resourceName }}.
                    </p>
                </div>
                <Link 
                    :href="route('growfinance.upgrade')"
                    class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap"
                >
                    Upgrade Now
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ExclamationTriangleIcon, XCircleIcon } from '@heroicons/vue/24/outline';

interface Props {
    used: number;
    limit: number;
    resourceName: string;
    warningMessage?: string;
    limitMessage?: string;
}

const props = withDefaults(defineProps<Props>(), {
    warningMessage: 'You\'re approaching your monthly limit',
    limitMessage: 'You\'ve reached your monthly limit',
});

const usagePercent = computed(() => {
    if (props.limit <= 0) return 0;
    return Math.round((props.used / props.limit) * 100);
});

const showBanner = computed(() => {
    return props.limit > 0 && usagePercent.value >= 80;
});
</script>
