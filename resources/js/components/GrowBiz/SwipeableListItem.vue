<script setup lang="ts">
import { ref, computed } from 'vue';
import { PencilIcon, TrashIcon, EyeIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    showView?: boolean;
    showEdit?: boolean;
    showDelete?: boolean;
    deleteColor?: string;
}>();

const emit = defineEmits<{
    view: [];
    edit: [];
    delete: [];
}>();

const swipeX = ref(0);
const startX = ref(0);
const isDragging = ref(false);
const containerRef = ref<HTMLElement | null>(null);

const ACTION_WIDTH = 70; // Width per action button
const rightActionsCount = computed(() => {
    let count = 0;
    if (props.showView) count++;
    if (props.showEdit) count++;
    if (props.showDelete) count++;
    return count || 2; // Default to 2 if none specified
});
const MAX_SWIPE = computed(() => ACTION_WIDTH * rightActionsCount.value);

const handleTouchStart = (e: TouchEvent) => {
    startX.value = e.touches[0].clientX;
    isDragging.value = true;
};

const handleTouchMove = (e: TouchEvent) => {
    if (!isDragging.value) return;
    
    const diff = startX.value - e.touches[0].clientX;
    
    // Only allow left swipe (positive diff)
    if (diff > 0) {
        swipeX.value = Math.min(diff, MAX_SWIPE.value);
    } else {
        swipeX.value = Math.max(diff * 0.3, -20); // Slight resistance for right swipe
    }
};

const handleTouchEnd = () => {
    isDragging.value = false;
    
    // Snap to open or closed
    if (swipeX.value > MAX_SWIPE.value * 0.3) {
        swipeX.value = MAX_SWIPE.value;
    } else {
        swipeX.value = 0;
    }
};

const closeSwipe = () => {
    swipeX.value = 0;
};

const handleAction = (action: 'view' | 'edit' | 'delete') => {
    closeSwipe();
    emit(action);
};

const handleContentClick = () => {
    if (swipeX.value > 0) {
        // If swiped open, close it
        closeSwipe();
    } else {
        // If not swiped, emit view action (tap to open)
        emit('view');
    }
};

// Expose close method
defineExpose({ closeSwipe });
</script>

<template>
    <li class="swipeable-container" ref="containerRef">
        <!-- Action buttons (revealed on swipe) -->
        <div class="swipe-actions">
            <button 
                v-if="showView !== false"
                @click="handleAction('view')"
                class="action-btn bg-blue-500 text-white"
                aria-label="View"
            >
                <EyeIcon class="h-5 w-5" aria-hidden="true" />
                <span class="text-xs mt-1">View</span>
            </button>
            <button 
                v-if="showEdit !== false"
                @click="handleAction('edit')"
                class="action-btn bg-amber-500 text-white"
                aria-label="Edit"
            >
                <PencilIcon class="h-5 w-5" aria-hidden="true" />
                <span class="text-xs mt-1">Edit</span>
            </button>
            <button 
                v-if="showDelete !== false"
                @click="handleAction('delete')"
                :class="['action-btn text-white', deleteColor || 'bg-red-500']"
                aria-label="Delete"
            >
                <TrashIcon class="h-5 w-5" aria-hidden="true" />
                <span class="text-xs mt-1">Delete</span>
            </button>
        </div>

        <!-- Main content -->
        <div 
            class="swipe-content"
            :class="{ 'is-dragging': isDragging }"
            :style="{ transform: `translateX(-${swipeX}px)` }"
            @touchstart="handleTouchStart"
            @touchmove="handleTouchMove"
            @touchend="handleTouchEnd"
            @click="handleContentClick"
        >
            <slot />
        </div>
    </li>
</template>

<style scoped>
.swipeable-container {
    position: relative;
    overflow: hidden;
}

.swipe-actions {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: stretch;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 70px;
    padding: 0.5rem;
    transition: opacity 0.2s;
}

.action-btn:active {
    opacity: 0.8;
}

.swipe-content {
    position: relative;
    background: white;
    z-index: 1;
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    touch-action: pan-y;
}

.swipe-content.is-dragging {
    transition: none;
}
</style>
