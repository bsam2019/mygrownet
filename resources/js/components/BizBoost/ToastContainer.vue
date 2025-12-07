<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import {
    CheckCircleIcon,
    ExclamationCircleIcon,
    InformationCircleIcon,
    XCircleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Toast {
    id: string;
    type: 'success' | 'error' | 'warning' | 'info';
    title: string;
    message?: string;
    duration?: number;
    action?: { label: string; onClick: () => void };
}

const toasts = ref<Toast[]>([]);

const icons = {
    success: CheckCircleIcon,
    error: XCircleIcon,
    warning: ExclamationCircleIcon,
    info: InformationCircleIcon,
};

const colors = {
    success: {
        bg: 'bg-emerald-50',
        border: 'border-emerald-200',
        icon: 'text-emerald-500',
        title: 'text-emerald-800',
        message: 'text-emerald-700',
    },
    error: {
        bg: 'bg-red-50',
        border: 'border-red-200',
        icon: 'text-red-500',
        title: 'text-red-800',
        message: 'text-red-700',
    },
    warning: {
        bg: 'bg-amber-50',
        border: 'border-amber-200',
        icon: 'text-amber-500',
        title: 'text-amber-800',
        message: 'text-amber-700',
    },
    info: {
        bg: 'bg-blue-50',
        border: 'border-blue-200',
        icon: 'text-blue-500',
        title: 'text-blue-800',
        message: 'text-blue-700',
    },
};

const addToast = (toast: Omit<Toast, 'id'>) => {
    const id = `toast-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    const newToast: Toast = { ...toast, id };
    toasts.value.push(newToast);

    // Auto-remove after duration
    const duration = toast.duration ?? 5000;
    if (duration > 0) {
        setTimeout(() => removeToast(id), duration);
    }
};

const removeToast = (id: string) => {
    const index = toasts.value.findIndex(t => t.id === id);
    if (index > -1) {
        toasts.value.splice(index, 1);
    }
};

// Expose methods globally
const handleToastEvent = (event: CustomEvent<Omit<Toast, 'id'>>) => {
    addToast(event.detail);
};

onMounted(() => {
    window.addEventListener('bizboost:toast', handleToastEvent as EventListener);
});

onUnmounted(() => {
    window.removeEventListener('bizboost:toast', handleToastEvent as EventListener);
});

// Expose for direct usage
defineExpose({ addToast, removeToast });
</script>

<template>
    <Teleport to="body">
        <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none">
            <TransitionGroup
                enter-active-class="transform transition duration-300 ease-out"
                enter-from-class="translate-x-full opacity-0"
                enter-to-class="translate-x-0 opacity-100"
                leave-active-class="transform transition duration-200 ease-in"
                leave-from-class="translate-x-0 opacity-100"
                leave-to-class="translate-x-full opacity-0"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'pointer-events-auto rounded-xl border p-4 shadow-lg backdrop-blur-sm',
                        colors[toast.type].bg,
                        colors[toast.type].border,
                    ]"
                >
                    <div class="flex items-start gap-3">
                        <!-- Icon -->
                        <component
                            :is="icons[toast.type]"
                            :class="['h-5 w-5 shrink-0 mt-0.5', colors[toast.type].icon]"
                            aria-hidden="true"
                        />

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p :class="['text-sm font-semibold', colors[toast.type].title]">
                                {{ toast.title }}
                            </p>
                            <p v-if="toast.message" :class="['text-sm mt-1', colors[toast.type].message]">
                                {{ toast.message }}
                            </p>
                            <button
                                v-if="toast.action"
                                @click="toast.action.onClick(); removeToast(toast.id)"
                                class="mt-2 text-sm font-medium text-violet-600 hover:text-violet-700"
                            >
                                {{ toast.action.label }}
                            </button>
                        </div>

                        <!-- Close button -->
                        <button
                            @click="removeToast(toast.id)"
                            class="shrink-0 p-1 rounded-lg hover:bg-white/50 transition-colors"
                            aria-label="Dismiss notification"
                        >
                            <XMarkIcon class="h-4 w-4 text-slate-400" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
