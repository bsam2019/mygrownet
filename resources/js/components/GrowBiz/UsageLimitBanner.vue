<template>
    <div 
        v-if="showBanner"
        class="rounded-xl p-4 mb-4"
        :class="bannerClass"
    >
        <div class="flex items-start gap-3">
            <component 
                :is="bannerIcon" 
                class="h-5 w-5 flex-shrink-0 mt-0.5" 
                :class="iconClass"
                aria-hidden="true" 
            />
            <div class="flex-1 min-w-0">
                <p class="font-medium" :class="textClass">{{ title }}</p>
                <p class="text-sm mt-1" :class="subtextClass">{{ message }}</p>
                
                <!-- Progress bar for usage -->
                <div v-if="showProgress" class="mt-3">
                    <div class="flex justify-between text-xs mb-1" :class="subtextClass">
                        <span>{{ current }} / {{ limit }} {{ unit }}</span>
                        <span>{{ percentage }}%</span>
                    </div>
                    <div class="h-2 bg-white/30 rounded-full overflow-hidden">
                        <div 
                            class="h-full rounded-full transition-all duration-300"
                            :class="progressClass"
                            :style="{ width: `${Math.min(percentage, 100)}%` }"
                        />
                    </div>
                </div>
            </div>
            <Link 
                v-if="showUpgrade"
                :href="route('growbiz.upgrade')"
                class="flex-shrink-0 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                :class="buttonClass"
            >
                Upgrade
            </Link>
            <button 
                v-if="dismissible"
                @click="dismiss"
                class="flex-shrink-0 p-1 rounded-lg hover:bg-white/20 transition-colors"
                :class="subtextClass"
                aria-label="Dismiss"
            >
                <XMarkIcon class="h-4 w-4" aria-hidden="true" />
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    ExclamationTriangleIcon,
    InformationCircleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    type?: 'warning' | 'critical' | 'info';
    metric?: string;
    current?: number;
    limit?: number;
    unit?: string;
    customTitle?: string;
    customMessage?: string;
    showUpgrade?: boolean;
    dismissible?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'info',
    current: 0,
    limit: 0,
    unit: 'used',
    showUpgrade: true,
    dismissible: false,
});

const dismissed = ref(false);

const percentage = computed(() => {
    if (props.limit <= 0) return 0;
    return Math.round((props.current / props.limit) * 100);
});

const showProgress = computed(() => props.limit > 0);

const showBanner = computed(() => {
    if (dismissed.value) return false;
    if (props.customTitle || props.customMessage) return true;
    return percentage.value >= 80;
});

const effectiveType = computed(() => {
    if (props.type !== 'info') return props.type;
    if (percentage.value >= 100) return 'critical';
    if (percentage.value >= 90) return 'warning';
    return 'info';
});

const title = computed(() => {
    if (props.customTitle) return props.customTitle;
    if (effectiveType.value === 'critical') return 'Limit Reached';
    if (effectiveType.value === 'warning') return 'Approaching Limit';
    return 'Usage Update';
});

const message = computed(() => {
    if (props.customMessage) return props.customMessage;
    if (effectiveType.value === 'critical') {
        return `You've reached your ${props.metric || 'usage'} limit. Upgrade to continue.`;
    }
    if (effectiveType.value === 'warning') {
        return `You're at ${percentage.value}% of your ${props.metric || 'usage'} limit.`;
    }
    return `You've used ${percentage.value}% of your ${props.metric || 'allowance'}.`;
});

const bannerIcon = computed(() => {
    return effectiveType.value === 'info' ? InformationCircleIcon : ExclamationTriangleIcon;
});

const bannerClass = computed(() => {
    switch (effectiveType.value) {
        case 'critical':
            return 'bg-red-50 border border-red-200';
        case 'warning':
            return 'bg-amber-50 border border-amber-200';
        default:
            return 'bg-blue-50 border border-blue-200';
    }
});

const iconClass = computed(() => {
    switch (effectiveType.value) {
        case 'critical':
            return 'text-red-500';
        case 'warning':
            return 'text-amber-500';
        default:
            return 'text-blue-500';
    }
});

const textClass = computed(() => {
    switch (effectiveType.value) {
        case 'critical':
            return 'text-red-900';
        case 'warning':
            return 'text-amber-900';
        default:
            return 'text-blue-900';
    }
});

const subtextClass = computed(() => {
    switch (effectiveType.value) {
        case 'critical':
            return 'text-red-700';
        case 'warning':
            return 'text-amber-700';
        default:
            return 'text-blue-700';
    }
});

const progressClass = computed(() => {
    switch (effectiveType.value) {
        case 'critical':
            return 'bg-red-500';
        case 'warning':
            return 'bg-amber-500';
        default:
            return 'bg-blue-500';
    }
});

const buttonClass = computed(() => {
    switch (effectiveType.value) {
        case 'critical':
            return 'bg-red-600 hover:bg-red-700 text-white';
        case 'warning':
            return 'bg-amber-600 hover:bg-amber-700 text-white';
        default:
            return 'bg-blue-600 hover:bg-blue-700 text-white';
    }
});

const dismiss = () => {
    dismissed.value = true;
};
</script>
