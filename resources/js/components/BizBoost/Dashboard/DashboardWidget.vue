<script setup lang="ts">
import { computed, ref } from 'vue';
import { useBizBoostDashboard, type WidgetConfig } from '@/composables/useBizBoostDashboard';
import {
    Bars3Icon,
    EyeSlashIcon,
    ArrowsPointingOutIcon,
    ChevronUpIcon,
    ChevronDownIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    widget: WidgetConfig;
    draggable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    draggable: true,
});

const emit = defineEmits<{
    (e: 'dragstart', widgetId: string): void;
    (e: 'dragend'): void;
    (e: 'drop', targetId: string, position: 'before' | 'after'): void;
}>();

const { isCustomizing, toggleWidget, draggedWidget } = useBizBoostDashboard();

const isDragging = ref(false);
const dropPosition = ref<'before' | 'after' | null>(null);

const isBeingDragged = computed(() => draggedWidget.value === props.widget.id);

// Size classes
const sizeClasses = computed(() => {
    switch (props.widget.size) {
        case 'small':
            return 'col-span-1';
        case 'medium':
            return 'col-span-1';
        case 'large':
            return 'lg:col-span-2';
        case 'full':
            return 'col-span-full';
        default:
            return 'col-span-1';
    }
});

// Drag handlers
const handleDragStart = (e: DragEvent) => {
    if (!isCustomizing.value) return;
    isDragging.value = true;
    
    // Set drag data
    e.dataTransfer?.setData('text/plain', props.widget.id);
    e.dataTransfer!.effectAllowed = 'move';
    
    // Create a custom drag image to prevent snapping issues
    const dragElement = e.currentTarget as HTMLElement;
    if (dragElement && e.dataTransfer) {
        // Use a semi-transparent clone
        e.dataTransfer.setDragImage(dragElement, dragElement.offsetWidth / 2, 20);
    }
    
    emit('dragstart', props.widget.id);
};

const handleDragEnd = () => {
    isDragging.value = false;
    emit('dragend');
};

const handleDragOver = (e: DragEvent) => {
    if (!isCustomizing.value || isBeingDragged.value) return;
    e.preventDefault();
    e.dataTransfer!.dropEffect = 'move';
    
    const rect = (e.currentTarget as HTMLElement).getBoundingClientRect();
    const midY = rect.top + rect.height / 2;
    dropPosition.value = e.clientY < midY ? 'before' : 'after';
};

const handleDragLeave = () => {
    dropPosition.value = null;
};

const handleDrop = (e: DragEvent) => {
    if (!isCustomizing.value) return;
    e.preventDefault();
    
    const sourceId = e.dataTransfer?.getData('text/plain');
    if (sourceId && sourceId !== props.widget.id && dropPosition.value) {
        emit('drop', props.widget.id, dropPosition.value);
    }
    dropPosition.value = null;
};
</script>

<template>
    <div
        :class="[
            'relative group transition-all duration-200',
            sizeClasses,
            isCustomizing && 'cursor-move',
            isBeingDragged && 'opacity-50 scale-95',
        ]"
        :draggable="isCustomizing && draggable"
        @dragstart="handleDragStart"
        @dragend="handleDragEnd"
        @dragover="handleDragOver"
        @dragleave="handleDragLeave"
        @drop="handleDrop"
    >
        <!-- Drop indicator -->
        <Transition
            enter-active-class="transition-all duration-150"
            enter-from-class="opacity-0 scale-x-0"
            enter-to-class="opacity-100 scale-x-100"
            leave-active-class="transition-all duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="dropPosition === 'before'"
                class="absolute -top-1.5 left-0 right-0 h-1.5 bg-violet-500 rounded-full z-20 shadow-lg shadow-violet-500/50"
            />
        </Transition>
        <Transition
            enter-active-class="transition-all duration-150"
            enter-from-class="opacity-0 scale-x-0"
            enter-to-class="opacity-100 scale-x-100"
            leave-active-class="transition-all duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="dropPosition === 'after'"
                class="absolute -bottom-1.5 left-0 right-0 h-1.5 bg-violet-500 rounded-full z-20 shadow-lg shadow-violet-500/50"
            />
        </Transition>

        <!-- Customization overlay -->
        <div
            v-if="isCustomizing"
            class="absolute inset-0 z-10 rounded-2xl border-2 border-dashed border-violet-300 dark:border-violet-600 bg-violet-50/50 dark:bg-violet-900/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
        >
            <div class="flex items-center gap-2">
                <button
                    @click.stop="toggleWidget(widget.id)"
                    class="p-2 rounded-lg bg-white dark:bg-slate-800 shadow-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-600 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                    :aria-label="`Hide ${widget.title}`"
                >
                    <EyeSlashIcon class="h-5 w-5" aria-hidden="true" />
                </button>
                <div class="p-2 rounded-lg bg-white dark:bg-slate-800 shadow-lg text-slate-400 dark:text-slate-500">
                    <Bars3Icon class="h-5 w-5" aria-hidden="true" />
                </div>
            </div>
        </div>

        <!-- Widget content -->
        <div :class="['h-full', isCustomizing && 'pointer-events-none']">
            <slot />
        </div>
    </div>
</template>
