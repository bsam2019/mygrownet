<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const props = withDefaults(defineProps<{
    show: boolean;
    title?: string;
    subtitle?: string;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
}>(), {
    show: false,
    title: '',
    subtitle: '',
    maxWidth: 'xl',
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown);
});

watch(() => props.show, (val) => {
    if (val) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 overflow-hidden"
                @click="close"
            >
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <Transition
                            enter-active-class="transform transition duration-300 ease-out"
                            enter-from-class="translate-x-full"
                            enter-to-class="translate-x-0"
                            leave-active-class="transform transition duration-200 ease-in"
                            leave-from-class="translate-x-0"
                            leave-to-class="translate-x-full"
                        >
                            <div
                                v-if="show"
                                @click.stop
                                :class="[
                                    'pointer-events-auto w-screen bg-white shadow-2xl flex flex-col',
                                    maxWidth === 'sm' ? 'max-w-sm' : '',
                                    maxWidth === 'md' ? 'max-w-md' : '',
                                    maxWidth === 'lg' ? 'max-w-lg' : '',
                                    maxWidth === 'xl' ? 'max-w-xl' : '',
                                    maxWidth === '2xl' ? 'max-w-2xl' : '',
                                ]"
                            >
                                <!-- Header -->
                                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-slate-900 text-white flex-shrink-0">
                                    <div>
                                        <h3 v-if="title" class="text-base font-bold text-white">{{ title }}</h3>
                                        <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5">{{ subtitle }}</p>
                                    </div>
                                    <button
                                        @click="close"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
                                    >
                                        <XMarkIcon class="w-5 h-5" />
                                    </button>
                                </div>

                                <!-- Body -->
                                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                                    <slot />
                                </div>

                                <!-- Footer (optional) -->
                                <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex-shrink-0 flex justify-end gap-3">
                                    <slot name="footer" />
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
