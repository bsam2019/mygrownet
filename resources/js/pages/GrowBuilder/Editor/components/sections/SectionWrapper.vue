<script setup lang="ts">
/**
 * Section Wrapper Component
 * Wraps each section with selection, hover, and action controls
 */
import {
    ArrowUpIcon,
    ArrowDownIcon,
    ArrowsUpDownIcon,
    DocumentDuplicateIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isSelected: boolean;
    isHovered: boolean;
    isResizing: boolean;
    hasSectionContentOffset: boolean;
}>();

const emit = defineEmits<{
    select: [id: string];
    hover: [id: string | null];
    moveUp: [id: string];
    moveDown: [id: string];
    duplicate: [id: string];
    delete: [id: string];
    startResize: [event: MouseEvent, id: string];
    startContentDrag: [event: MouseEvent, id: string];
    resetContentOffset: [id: string];
}>();

const handleClick = (e: MouseEvent) => {
    emit('select', props.section.id);
};
</script>

<template>
    <div
        :data-section-id="section.id"
        :class="[
            'relative group transition-all',
            isSelected ? 'ring-2 ring-blue-500 ring-inset' : '',
            isHovered && !isSelected ? 'ring-1 ring-blue-300 ring-inset' : '',
            isResizing ? 'ring-2 ring-blue-500' : ''
        ]"
        :style="{ minHeight: section.style?.minHeight ? `${section.style.minHeight}px` : undefined }"
        @click="handleClick"
        @mouseenter="emit('hover', section.id)"
        @mouseleave="emit('hover', null)"
    >
        <!-- Section Actions Toolbar -->
        <div
            v-if="isHovered || isSelected"
            class="absolute top-2 right-2 z-20 flex items-center gap-1 bg-white rounded-lg shadow-lg border border-gray-200 p-1"
        >
            <!-- Content Position Drag Handle -->
            <button
                @click.stop
                @mousedown.stop.prevent="emit('startContentDrag', $event, section.id)"
                class="p-1.5 hover:bg-blue-100 rounded cursor-ns-resize"
                :class="hasSectionContentOffset ? 'bg-blue-50 text-blue-600' : ''"
                aria-label="Drag content vertically"
                title="Drag to reposition content vertically"
            >
                <ArrowsUpDownIcon
                    class="w-4 h-4"
                    :class="hasSectionContentOffset ? 'text-blue-600' : 'text-gray-600'"
                    aria-hidden="true"
                />
            </button>
            <div class="w-px h-4 bg-gray-200"></div>
            <button
                @click.stop="emit('moveUp', section.id)"
                class="p-1.5 hover:bg-gray-100 rounded"
                aria-label="Move up"
            >
                <ArrowUpIcon class="w-4 h-4 text-gray-600" aria-hidden="true" />
            </button>
            <button
                @click.stop="emit('moveDown', section.id)"
                class="p-1.5 hover:bg-gray-100 rounded"
                aria-label="Move down"
            >
                <ArrowDownIcon class="w-4 h-4 text-gray-600" aria-hidden="true" />
            </button>
            <button
                @click.stop="emit('duplicate', section.id)"
                class="p-1.5 hover:bg-gray-100 rounded"
                aria-label="Duplicate"
            >
                <DocumentDuplicateIcon class="w-4 h-4 text-gray-600" aria-hidden="true" />
            </button>
            <button
                @click.stop="emit('delete', section.id)"
                class="p-1.5 hover:bg-red-100 rounded"
                aria-label="Delete"
            >
                <TrashIcon class="w-4 h-4 text-red-500" aria-hidden="true" />
            </button>
        </div>

        <!-- Reset Content Position Button -->
        <div
            v-if="(isHovered || isSelected) && hasSectionContentOffset"
            class="absolute top-2 left-2 z-20"
        >
            <button
                @click.stop="emit('resetContentOffset', section.id)"
                class="px-2 py-1 text-xs font-medium rounded bg-white shadow-lg border border-gray-200 text-gray-600 hover:bg-gray-100 transition-colors"
                title="Reset content position"
            >
                Reset Position
            </button>
        </div>

        <!-- Section Content -->
        <slot></slot>

        <!-- Resize Handle -->
        <div
            v-if="isHovered || isSelected || isResizing"
            class="absolute bottom-0 left-0 right-0 h-3 flex items-center justify-center cursor-ns-resize group/resize z-20 hover:bg-blue-100/50 transition-colors"
            @mousedown="emit('startResize', $event, section.id)"
        >
            <div class="w-12 h-1 bg-gray-300 rounded-full group-hover/resize:bg-blue-500 transition-colors"></div>
        </div>
    </div>
</template>
