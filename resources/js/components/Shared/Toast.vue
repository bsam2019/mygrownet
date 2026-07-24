<script setup lang="ts">
/**
 * Toast Notification Component
 * 
 * Individual toast notification item.
 * Used by ToastContainer for displaying notifications.
 */
import { 
    CheckCircleIcon, 
    ExclamationCircleIcon, 
    InformationCircleIcon,
    XMarkIcon 
} from '@heroicons/vue/24/outline';

interface Props {
    message: string;
    type?: 'success' | 'error' | 'info' | 'warning';
    duration?: number;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'info',
    duration: 3000,
});

const emit = defineEmits<{
    close: [];
}>();

const icons = {
    success: CheckCircleIcon,
    error: ExclamationCircleIcon,
    info: InformationCircleIcon,
    warning: ExclamationCircleIcon,
};

const colors = {
    success: 'bg-emerald-50 text-emerald-800 border-emerald-200',
    error: 'bg-red-50 text-red-800 border-red-200',
    info: 'bg-blue-50 text-blue-800 border-blue-200',
    warning: 'bg-amber-50 text-amber-800 border-amber-200',
};

const iconColors = {
    success: 'text-emerald-500',
    error: 'text-red-500',
    info: 'text-blue-500',
    warning: 'text-amber-500',
};

const close = () => {
    emit('close');
};
</script>

<template>
    <div 
        :class="[
            'flex items-center gap-3 p-4 rounded-2xl border shadow-lg backdrop-blur-sm',
            colors[type]
        ]"
        role="alert"
    >
        <component 
            :is="icons[type]" 
            :class="['h-5 w-5 flex-shrink-0', iconColors[type]]" 
            aria-hidden="true" 
        />
        <p class="flex-1 text-sm font-medium">{{ message }}</p>
        <button 
            @click="close"
            class="p-1 rounded-full hover:bg-black/5 transition-colors"
            aria-label="Dismiss notification"
        >
            <XMarkIcon class="h-4 w-4" aria-hidden="true" />
        </button>
    </div>
</template>
