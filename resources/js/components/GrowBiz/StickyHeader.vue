<script setup lang="ts">
/**
 * Sticky header that shrinks/transforms on scroll
 * Native app-style collapsing header
 */
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps<{
    title: string;
    subtitle?: string;
    showBackButton?: boolean;
}>();

const emit = defineEmits<{
    back: [];
}>();

const scrollY = ref(0);
const headerRef = ref<HTMLElement | null>(null);

const COLLAPSE_THRESHOLD = 60;

const isCollapsed = computed(() => scrollY.value > COLLAPSE_THRESHOLD);
const headerOpacity = computed(() => {
    if (scrollY.value <= 0) return 0;
    return Math.min(scrollY.value / COLLAPSE_THRESHOLD, 1);
});

const handleScroll = () => {
    scrollY.value = window.scrollY;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div ref="headerRef">
        <!-- Expanded header (visible when not scrolled) -->
        <div 
            class="expanded-header"
            :style="{ 
                opacity: 1 - headerOpacity,
                transform: `translateY(-${scrollY * 0.5}px)`
            }"
        >
            <slot name="expanded">
                <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
                <p v-if="subtitle" class="text-sm text-gray-500 mt-1">{{ subtitle }}</p>
            </slot>
        </div>

        <!-- Collapsed header (sticky, visible when scrolled) -->
        <div 
            class="collapsed-header"
            :class="{ 'is-visible': isCollapsed }"
            :style="{ opacity: headerOpacity }"
        >
            <slot name="collapsed">
                <h2 class="text-lg font-semibold text-gray-900 truncate">{{ title }}</h2>
            </slot>
        </div>
    </div>
</template>

<style scoped>
.expanded-header {
    padding: 1rem 0;
    will-change: transform, opacity;
}

.collapsed-header {
    position: fixed;
    top: 56px; /* Below mobile header */
    left: 0;
    right: 0;
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.75rem 1rem;
    z-index: 35;
    transform: translateY(-100%);
    transition: transform 0.2s ease;
    will-change: transform;
}

.collapsed-header.is-visible {
    transform: translateY(0);
}

@media (min-width: 1024px) {
    .collapsed-header {
        left: 16rem; /* Account for desktop sidebar */
        top: 64px;
    }
}
</style>
