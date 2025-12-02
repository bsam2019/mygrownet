<script setup lang="ts">
/**
 * Button with native app-style haptic feedback
 * Includes ripple effect and scale animation on press
 */
import { ref } from 'vue';

defineProps<{
    variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
    size?: 'sm' | 'md' | 'lg';
    disabled?: boolean;
    loading?: boolean;
    fullWidth?: boolean;
}>();

const emit = defineEmits<{
    click: [e: MouseEvent];
}>();

const buttonRef = ref<HTMLButtonElement | null>(null);
const ripples = ref<Array<{ id: number; x: number; y: number }>>([]);
let rippleId = 0;

const handleClick = (e: MouseEvent) => {
    if (buttonRef.value) {
        const rect = buttonRef.value.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const id = rippleId++;
        ripples.value.push({ id, x, y });
        
        // Remove ripple after animation
        setTimeout(() => {
            ripples.value = ripples.value.filter(r => r.id !== id);
        }, 600);
        
        // Trigger haptic feedback if available
        if ('vibrate' in navigator) {
            navigator.vibrate(10);
        }
    }
    
    emit('click', e);
};
</script>

<template>
    <button
        ref="buttonRef"
        :disabled="disabled || loading"
        class="haptic-button"
        :class="[
            variant || 'primary',
            size || 'md',
            { 'w-full': fullWidth, 'opacity-50 cursor-not-allowed': disabled }
        ]"
        @click="handleClick"
    >
        <!-- Ripple effects -->
        <span 
            v-for="ripple in ripples" 
            :key="ripple.id"
            class="ripple"
            :style="{ left: `${ripple.x}px`, top: `${ripple.y}px` }"
        />
        
        <!-- Loading spinner -->
        <svg 
            v-if="loading" 
            class="animate-spin h-5 w-5 mr-2" 
            fill="none" 
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
        
        <slot />
    </button>
</template>

<style scoped>
.haptic-button {
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    border-radius: 0.75rem;
    transition: all 0.15s ease;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

.haptic-button:active:not(:disabled) {
    transform: scale(0.97);
}

/* Variants */
.haptic-button.primary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.haptic-button.primary:hover:not(:disabled) {
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.haptic-button.secondary {
    background: #f3f4f6;
    color: #374151;
}

.haptic-button.secondary:hover:not(:disabled) {
    background: #e5e7eb;
}

.haptic-button.danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}

.haptic-button.ghost {
    background: transparent;
    color: #6b7280;
}

.haptic-button.ghost:hover:not(:disabled) {
    background: #f3f4f6;
}

/* Sizes */
.haptic-button.sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.haptic-button.md {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
}

.haptic-button.lg {
    padding: 1rem 2rem;
    font-size: 1.125rem;
}

/* Ripple effect */
.ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.4);
    transform: translate(-50%, -50%) scale(0);
    animation: ripple-animation 0.6s ease-out;
    pointer-events: none;
}

@keyframes ripple-animation {
    to {
        transform: translate(-50%, -50%) scale(4);
        opacity: 0;
    }
}
</style>
