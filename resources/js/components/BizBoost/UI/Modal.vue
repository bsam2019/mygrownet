<script setup lang="ts">
import { watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

interface Props {
    show: boolean;
    title?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
    closeable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    closeable: true,
});

const emit = defineEmits<{
    close: [];
}>();

const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
};

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

// Prevent body scroll when modal is open
watch(() => props.show, (show) => {
    if (show) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div
                        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm dark:bg-gray-900/80"
                        @click="close"
                    ></div>

                    <!-- Modal Panel -->
                    <Transition
                        enter-active-class="duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="show"
                            :class="[
                                'relative w-full rounded-2xl bg-white shadow-2xl dark:bg-gray-800',
                                sizeClasses[size],
                            ]"
                        >
                            <!-- Header -->
                            <div v-if="title || closeable" class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                <h3 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ title }}
                                </h3>
                                <button
                                    v-if="closeable"
                                    type="button"
                                    class="ml-auto rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                    aria-label="Close modal"
                                    @click="close"
                                >
                                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="p-6">
                                <slot />
                            </div>

                            <!-- Footer -->
                            <div v-if="$slots.footer" class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                                <slot name="footer" />
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
