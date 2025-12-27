<script setup lang="ts">
/**
 * Services Section Preview Component
 */
import { computed } from 'vue';
import { Cog6ToothIcon, PencilIcon } from '@heroicons/vue/24/outline';
import { getBackgroundStyle } from '../../composables/useBackgroundStyle';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
    gridCols3: string;
    // Inline editing
    isEditing: (sectionId: string, field: string, itemIndex?: number) => boolean;
    editingValue: string;
    startInlineEdit: (sectionId: string, field: string, value: string, itemIndex?: number) => void;
    saveInlineEdit: () => void;
    handleInlineKeydown: (e: KeyboardEvent) => void;
    // Content transform
    getSectionContentTransform: (section: Section) => string;
}>();

const emit = defineEmits<{
    'update:editingValue': [value: string];
}>();

const content = computed(() => props.section.content);
const style = computed(() => props.section.style);

const bgStyle = computed(() => getBackgroundStyle(style.value, '#ffffff', '#111827'));

// Compute text alignment class
const textAlignClass = computed(() => {
    const align = content.value?.textPosition || style.value?.textAlign || 'center';
    return {
        'text-left': align === 'left',
        'text-center': align === 'center',
        'text-right': align === 'right',
    };
});

// Compute items alignment class for grid
const itemsJustifyClass = computed(() => {
    const align = style.value?.itemsAlign || 'center';
    if (align === 'start') return 'justify-items-start';
    if (align === 'end') return 'justify-items-end';
    if (align === 'stretch') return 'justify-items-stretch';
    return 'justify-items-center';
});
</script>

<template>
    <div
        class="h-full flex flex-col justify-center overflow-hidden"
        :class="spacing.section"
        :style="bgStyle"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <h2 :class="[textSize.h2, 'font-bold mb-4', textAlignClass]">
                {{ content.title || 'Our Services' }}
            </h2>
            <p
                v-if="content.subtitle"
                class="text-gray-600 mb-8 max-w-2xl"
                :class="[textSize.p, textAlignClass, { 'mx-auto': style?.textAlign !== 'left' && style?.textAlign !== 'right' }]"
            >
                {{ content.subtitle }}
            </p>
            <div v-else class="mb-6"></div>
            
            <div class="grid" :class="[gridCols3, spacing.gap, itemsJustifyClass]">
                <div
                    v-for="(item, idx) in content.items || []"
                    :key="idx"
                    class="p-4 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow w-full"
                >
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                        <Cog6ToothIcon class="w-5 h-5 text-blue-600" aria-hidden="true" />
                    </div>
                    <h3
                        v-if="isEditing(section.id, 'title', idx)"
                        class="relative"
                    >
                        <input
                            :value="editingValue"
                            @input="emit('update:editingValue', ($event.target as HTMLInputElement).value)"
                            @blur="saveInlineEdit"
                            @keydown="handleInlineKeydown"
                            class="inline-edit-input font-semibold border border-blue-400 rounded px-2 py-1 w-full text-gray-900"
                            :class="textSize.h3"
                        />
                    </h3>
                    <h3
                        v-else
                        @click.stop="startInlineEdit(section.id, 'title', item.title, idx)"
                        class="font-semibold mb-2 cursor-pointer hover:bg-gray-100 rounded px-1 transition-colors group/item text-gray-900"
                        :class="textSize.h3"
                    >
                        {{ item.title }}
                        <PencilIcon class="w-4 h-4 inline opacity-0 group-hover/item:opacity-50 ml-1" aria-hidden="true" />
                    </h3>
                    <p class="text-gray-600 leading-relaxed" :class="textSize.p">
                        {{ item.description }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
