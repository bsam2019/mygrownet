<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, type Component } from 'vue';
import { useHaptics } from '@/composables/useHaptics';
import { PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface FABAction {
    label: string;
    icon: Component;
    onClick: () => void;
    color?: string;
}

interface Props {
    /** Main FAB icon */
    icon?: Component;
    /** Label shown when expanded */
    label?: string;
    /** Actions for expandable FAB */
    actions?: FABAction[];
    /** Position */
    position?: 'bottom-right' | 'bottom-center';
    /** Shrink on scroll */
    shrinkOnScroll?: boolean;
    /** Bottom offset (for bottom nav) */
    bottomOffset?: number;
}

const props = withDefaults(defineProps<Props>(), {
    icon: () => PlusIcon,
    position: 'bottom-right',
    shrinkOnScroll: true,
    bottomOffset: 80, // Above bottom nav
});

const emit = defineEmits<{
    (e: 'click'): void;
}>();

const { light, medium } = useHaptics();

// State
const isExpanded = ref(false);
const isShrunken = ref(false);
const lastScrollY = ref(0);

// Scroll handler
const handleScroll = () => {
    if (!props.shrinkOnScroll) return;

    const currentScrollY = window.scrollY;
    const diff = currentScrollY - lastScrollY.value;

    if (diff > 10 && currentScrollY > 100) {
        isShrunken.value = true;
    } else if (diff < -10) {
        isShrunken.value = false;
    }

    lastScrollY.value = currentScrollY;
};

// Click handlers
const handleMainClick = () => {
    light();
    
    if (props.actions?.length) {
        isExpanded.value = !isExpanded.value;
        if (isExpanded.value) {
            medium();
        }
    } else {
        emit('click');
    }
};

const handleActionClick = (action: FABAction) => {
    light();
    action.onClick();
    isExpanded.value = false;
};

const handleBackdropClick = () => {
    isExpanded.value = false;
};

// Position classes
const positionClasses = computed(() => {
    const base = 'fixed z-50 lg:hidden';
    
    if (props.position === 'bottom-center') {
        return `${base} bottom-0 left-1/2 -translate-x-1/2`;
    }
    
    return `${base} bottom-0 right-4`;
});

// Main button classes
const buttonClasses = computed(() => {
    const base = [
        'flex items-center justify-center rounded-full shadow-lg transition-all duration-300',
        'bg-gradient-to-r from-violet-600 to-violet-700 text-white',
        'hover:shadow-xl hover:from-violet-700 hover:to-violet-800',
        'focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2',
    ];

    if (isShrunken.value && !isExpanded.value) {
        base.push('w-12 h-12');
    } else {
        base.push('h-14', props.label && !isExpanded.value ? 'px-5' : 'w-14');
    }

    return base.join(' ');
});

onMounted(() => {
    if (props.shrinkOnScroll) {
        window.addEventListener('scroll', handleScroll, { passive: true });
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div
        :class="positionClasses"
        :style="{ bottom: `${bottomOffset}px` }"
    >
        <!-- Backdrop when expanded -->
        <Transition name="fade">
            <div
                v-if="isExpanded"
                class="fixed inset-0 bg-black/30"
                @click="handleBackdropClick"
            />
        </Transition>

        <!-- Action buttons -->
        <Transition name="fab-actions">
            <div
                v-if="isExpanded && actions?.length"
                class="absolute bottom-16 right-0 flex flex-col-reverse gap-3 mb-2"
            >
                <button
                    v-for="(action, index) in actions"
                    :key="index"
                    class="flex items-center gap-3 pl-4 pr-3 py-2 rounded-full bg-white dark:bg-slate-800 shadow-lg text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all"
                    :style="{ transitionDelay: `${index * 50}ms` }"
                    @click="handleActionClick(action)"
                    :aria-label="action.label"
                >
                    <span class="text-sm font-medium whitespace-nowrap">{{ action.label }}</span>
                    <div
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center',
                            action.color || 'bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400',
                        ]"
                    >
                        <component :is="action.icon" class="h-5 w-5" aria-hidden="true" />
                    </div>
                </button>
            </div>
        </Transition>

        <!-- Main FAB -->
        <button
            :class="buttonClasses"
            @click="handleMainClick"
            :aria-label="isExpanded ? 'Close menu' : (label || 'Open menu')"
            :aria-expanded="actions?.length ? isExpanded : undefined"
        >
            <component
                :is="isExpanded ? XMarkIcon : icon"
                :class="[
                    'transition-transform duration-300',
                    isShrunken && !isExpanded ? 'h-5 w-5' : 'h-6 w-6',
                    isExpanded && 'rotate-90',
                ]"
                aria-hidden="true"
            />
            <span
                v-if="label && !isShrunken && !isExpanded"
                class="ml-2 font-medium"
            >
                {{ label }}
            </span>
        </button>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fab-actions-enter-active,
.fab-actions-leave-active {
    transition: all 0.3s ease;
}

.fab-actions-enter-from,
.fab-actions-leave-to {
    opacity: 0;
    transform: translateY(20px) scale(0.9);
}
</style>
