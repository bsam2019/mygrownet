<script setup lang="ts">
/**
 * Native app-style Floating Action Button (FAB)
 * Supports single action or expandable menu
 */
import { ref } from 'vue';
import { PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface FabAction {
    icon: any;
    label: string;
    onClick: () => void;
    color?: string;
}

const props = defineProps<{
    actions?: FabAction[];
    icon?: any;
    color?: string;
    position?: 'bottom-right' | 'bottom-center';
}>();

const emit = defineEmits<{
    click: [];
}>();

const isExpanded = ref(false);

const handleMainClick = () => {
    if (props.actions && props.actions.length > 0) {
        isExpanded.value = !isExpanded.value;
    } else {
        emit('click');
    }
};

const handleActionClick = (action: FabAction) => {
    isExpanded.value = false;
    action.onClick();
};
</script>

<template>
    <div 
        class="fab-container"
        :class="position || 'bottom-right'"
    >
        <!-- Backdrop when expanded -->
        <Transition name="fade">
            <div 
                v-if="isExpanded"
                class="fab-backdrop"
                @click="isExpanded = false"
            />
        </Transition>

        <!-- Action buttons -->
        <TransitionGroup name="fab-actions" tag="div" class="fab-actions">
            <button
                v-for="(action, index) in (isExpanded ? actions : [])"
                :key="action.label"
                class="fab-action"
                :style="{ 
                    '--delay': `${index * 50}ms`,
                    backgroundColor: action.color || '#6b7280'
                }"
                @click="handleActionClick(action)"
            >
                <component :is="action.icon" class="h-5 w-5 text-white" aria-hidden="true" />
                <span class="fab-action-label">{{ action.label }}</span>
            </button>
        </TransitionGroup>

        <!-- Main FAB -->
        <button
            class="fab-main"
            :class="{ 'is-expanded': isExpanded }"
            :style="{ backgroundColor: color || '#10b981' }"
            @click="handleMainClick"
            :aria-label="isExpanded ? 'Close menu' : 'Open menu'"
        >
            <component 
                :is="isExpanded ? XMarkIcon : (icon || PlusIcon)" 
                class="h-6 w-6 text-white transition-transform duration-200"
                :class="{ 'rotate-45': isExpanded && !icon }"
                aria-hidden="true"
            />
        </button>
    </div>
</template>

<style scoped>
.fab-container {
    position: fixed;
    z-index: 30;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.fab-container.bottom-right {
    right: 1rem;
    bottom: 5.5rem; /* Above bottom nav */
}

.fab-container.bottom-center {
    left: 50%;
    transform: translateX(-50%);
    bottom: 5.5rem;
}

@media (min-width: 1024px) {
    .fab-container.bottom-right {
        bottom: 1.5rem;
    }
    .fab-container.bottom-center {
        bottom: 1.5rem;
    }
}

.fab-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.3);
    z-index: -1;
}

.fab-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
}

.fab-action {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.2s ease;
}

.fab-action:active {
    transform: scale(0.95);
}

.fab-action-label {
    position: absolute;
    right: 100%;
    margin-right: 0.75rem;
    padding: 0.5rem 0.75rem;
    background: white;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.fab-main {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.4);
    transition: all 0.2s ease;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

.fab-main:active {
    transform: scale(0.95);
}

.fab-main.is-expanded {
    background: #374151 !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fab-actions-enter-active {
    transition: all 0.2s ease;
    transition-delay: var(--delay);
}

.fab-actions-leave-active {
    transition: all 0.15s ease;
}

.fab-actions-enter-from,
.fab-actions-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.8);
}
</style>
