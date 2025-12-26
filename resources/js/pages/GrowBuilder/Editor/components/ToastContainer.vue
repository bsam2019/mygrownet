<script setup lang="ts">
import { useToast } from '../composables/useToast';
import {
    CheckCircleIcon,
    XCircleIcon,
    InformationCircleIcon,
    ExclamationTriangleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const { toasts, removeToast } = useToast();

const getIcon = (type: string) => {
    switch (type) {
        case 'success':
            return CheckCircleIcon;
        case 'error':
            return XCircleIcon;
        case 'warning':
            return ExclamationTriangleIcon;
        default:
            return InformationCircleIcon;
    }
};

const getStyles = (type: string) => {
    switch (type) {
        case 'success':
            return 'bg-green-50 border-green-200 text-green-800';
        case 'error':
            return 'bg-red-50 border-red-200 text-red-800';
        case 'warning':
            return 'bg-amber-50 border-amber-200 text-amber-800';
        default:
            return 'bg-blue-50 border-blue-200 text-blue-800';
    }
};

const getIconStyles = (type: string) => {
    switch (type) {
        case 'success':
            return 'text-green-500';
        case 'error':
            return 'text-red-500';
        case 'warning':
            return 'text-amber-500';
        default:
            return 'text-blue-500';
    }
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none">
            <TransitionGroup
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 translate-x-8"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 translate-x-8"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg backdrop-blur-sm min-w-[280px] max-w-[400px]',
                        getStyles(toast.type)
                    ]"
                >
                    <component
                        :is="getIcon(toast.type)"
                        :class="['w-5 h-5 flex-shrink-0', getIconStyles(toast.type)]"
                        aria-hidden="true"
                    />
                    <p class="flex-1 text-sm font-medium">{{ toast.message }}</p>
                    <button
                        @click="removeToast(toast.id)"
                        class="p-1 rounded-lg hover:bg-black/5 transition-colors flex-shrink-0"
                        aria-label="Dismiss"
                    >
                        <XMarkIcon class="w-4 h-4 opacity-60" aria-hidden="true" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
