<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, type Component } from 'vue';
import { router } from '@inertiajs/vue3';
import { useHaptics } from '@/composables/useHaptics';
import { ChevronLeftIcon } from '@heroicons/vue/24/outline';

interface HeaderAction {
    icon: Component;
    onClick: () => void;
    ariaLabel: string;
    badge?: number;
}

interface Props {
    title: string;
    subtitle?: string;
    showBack?: boolean;
    backHref?: string;
    actions?: HeaderAction[];
    transparent?: boolean;
    collapseOnScroll?: boolean;
    gradient?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showBack: true,
    transparent: false,
    collapseOnScroll: false,
    gradient: true,
});

const emit = defineEmits<{
    (e: 'back'): void;
}>();

const { light } = useHaptics();

// Scroll-aware collapse
const isCollapsed = ref(false);
const lastScrollY = ref(0);
const scrollThreshold = 50;

const handleScroll = () => {
    if (!props.collapseOnScroll) return;

    const currentScrollY = window.scrollY;
    isCollapsed.value = currentScrollY > scrollThreshold;
    lastScrollY.value = currentScrollY;
};

const handleBack = () => {
    light();
    emit('back');
    
    if (props.backHref) {
        router.visit(props.backHref);
    } else {
        window.history.back();
    }
};

const handleActionClick = (action: HeaderAction) => {
    light();
    action.onClick();
};

// Header classes
const headerClasses = computed(() => {
    const classes = [
        'sticky top-0 z-40 lg:hidden transition-all duration-300',
    ];

    if (props.transparent && !isCollapsed.value) {
        classes.push('bg-transparent');
    } else if (props.gradient) {
        classes.push('bg-gradient-to-r from-violet-600 to-violet-700 text-white shadow-lg');
    } else {
        classes.push('bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700');
    }

    if (isCollapsed.value) {
        classes.push('py-2');
    } else {
        classes.push('py-3');
    }

    return classes.join(' ');
});

const titleClasses = computed(() => {
    const classes = ['font-semibold truncate transition-all duration-300'];
    
    if (props.gradient || (props.transparent && !isCollapsed.value)) {
        classes.push('text-white');
    } else {
        classes.push('text-slate-900 dark:text-white');
    }

    if (isCollapsed.value) {
        classes.push('text-base');
    } else {
        classes.push('text-lg');
    }

    return classes.join(' ');
});

onMounted(() => {
    if (props.collapseOnScroll) {
        window.addEventListener('scroll', handleScroll, { passive: true });
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <header
        :class="headerClasses"
        :style="{ paddingTop: 'env(safe-area-inset-top)' }"
    >
        <div class="flex items-center justify-between px-4 h-12">
            <!-- Left: Back button -->
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <button
                    v-if="showBack"
                    @click="handleBack"
                    class="p-2 -ml-2 rounded-full hover:bg-white/20 transition-colors"
                    :aria-label="backHref ? `Go back to ${backHref}` : 'Go back'"
                >
                    <ChevronLeftIcon class="h-6 w-6" aria-hidden="true" />
                </button>
                
                <div class="min-w-0 flex-1">
                    <h1 :class="titleClasses">{{ title }}</h1>
                    <p
                        v-if="subtitle && !isCollapsed"
                        class="text-sm opacity-80 truncate"
                    >
                        {{ subtitle }}
                    </p>
                </div>
            </div>

            <!-- Right: Actions -->
            <div v-if="actions?.length" class="flex items-center gap-1">
                <button
                    v-for="(action, index) in actions"
                    :key="index"
                    @click="handleActionClick(action)"
                    class="relative p-2 rounded-full hover:bg-white/20 transition-colors"
                    :aria-label="action.ariaLabel"
                >
                    <component
                        :is="action.icon"
                        class="h-6 w-6"
                        aria-hidden="true"
                    />
                    <span
                        v-if="action.badge && action.badge > 0"
                        class="absolute -top-1 -right-1 min-w-[18px] h-[18px] flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full px-1"
                    >
                        {{ action.badge > 99 ? '99+' : action.badge }}
                    </span>
                </button>
            </div>
        </div>
    </header>
</template>
