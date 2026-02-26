<script setup lang="ts">
/**
 * Toast Container
 * 
 * Displays all active toast notifications.
 * Place this once in your layout.
 */
import { useToast } from '@/composables/useToast';
import Toast from './Toast.vue';

const { toasts, remove } = useToast();
</script>

<template>
    <Teleport to="body">
        <TransitionGroup
            tag="div"
            class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-x-4 scale-95"
            enter-to-class="opacity-100 translate-x-0 scale-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-x-0 scale-100"
            leave-to-class="opacity-0 translate-x-4 scale-95"
            move-class="transition-transform duration-300"
        >
            <div 
                v-for="toast in toasts" 
                :key="toast.id"
                class="pointer-events-auto w-full max-w-sm"
            >
                <Toast
                    :message="toast.message"
                    :type="toast.type"
                    :duration="0"
                    @close="remove(toast.id)"
                />
            </div>
        </TransitionGroup>
    </Teleport>
</template>
