<script setup lang="ts">
/**
 * Page Header Section Preview Component
 * Used for inner page title banners
 */
import { computed } from 'vue';
import { PencilIcon } from '@heroicons/vue/24/outline';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
    getSectionContentTransform: (section: Section) => string;
    isEditing: (sectionId: string, field: string, itemIndex?: number) => boolean;
    editingValue: string;
    startInlineEdit: (sectionId: string, field: string, value: string, itemIndex?: number) => void;
    saveInlineEdit: () => void;
    handleInlineKeydown: (e: KeyboardEvent) => void;
}>();

const emit = defineEmits<{
    (e: 'update:editingValue', value: string): void;
}>();

const content = computed(() => props.section.content);

const editValue = computed({
    get: () => props.editingValue,
    set: (val) => emit('update:editingValue', val)
});

// Computed styles for font sizes from section.style
const titleStyle = computed(() => ({
    fontSize: props.section.style?.titleFontSize ? `${props.section.style.titleFontSize}px` : undefined,
}));

const subtitleStyle = computed(() => ({
    fontSize: props.section.style?.subtitleFontSize ? `${props.section.style.subtitleFontSize}px` : undefined,
}));
</script>

<template>
    <div
        class="h-full flex flex-col justify-center relative overflow-hidden"
        :class="[
            spacing.section,
            {
                'text-left items-start': content.textPosition === 'left',
                'text-center items-center': !content.textPosition || content.textPosition === 'center',
                'text-right items-end': content.textPosition === 'right'
            }
        ]"
        :style="{
            backgroundColor: content.backgroundColor || '#1e40af',
            color: content.textColor || '#ffffff',
            minHeight: section.style?.minHeight ? `${section.style.minHeight}px` : '180px'
        }"
    >
        <!-- Background Image -->
        <div v-if="content.backgroundImage" class="absolute inset-0">
            <img :src="content.backgroundImage" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black/40"></div>
        </div>
        
        <div
            class="relative z-10 w-full"
            :class="{
                'mx-auto max-w-4xl': !content.textPosition || content.textPosition === 'center',
                'ml-auto max-w-4xl': content.textPosition === 'right'
            }"
            :style="{ transform: getSectionContentTransform(section) }"
        >
            <!-- Editable Title -->
            <h1 v-if="isEditing(section.id, 'title')" class="relative inline-block w-full">
                <input
                    v-model="editValue"
                    @blur="saveInlineEdit"
                    @keydown="handleInlineKeydown"
                    class="inline-edit-input font-bold bg-white/20 border border-white/40 rounded px-3 py-1 w-full"
                    :class="textSize.h2"
                    :style="{ color: content.textColor || '#ffffff', ...titleStyle }"
                />
            </h1>
            <h1
                v-else
                @click.stop="startInlineEdit(section.id, 'title', content.title)"
                class="font-bold mb-2 cursor-pointer hover:bg-white/10 rounded px-2 py-1 transition-colors group/title"
                :class="textSize.h2"
                :style="titleStyle"
            >
                {{ content.title || 'Page Title' }}
                <PencilIcon class="w-4 h-4 inline opacity-0 group-hover/title:opacity-70 ml-2" aria-hidden="true" />
            </h1>
            
            <!-- Editable Subtitle -->
            <p
                v-if="content.subtitle"
                class="opacity-90 cursor-pointer hover:bg-white/10 rounded px-2 py-1 transition-colors group/sub"
                :class="textSize.p"
                :style="subtitleStyle"
                @click.stop="startInlineEdit(section.id, 'subtitle', content.subtitle)"
            >
                <template v-if="isEditing(section.id, 'subtitle')">
                    <input
                        v-model="editValue"
                        @blur="saveInlineEdit"
                        @keydown="handleInlineKeydown"
                        class="inline-edit-input bg-white/20 border border-white/40 rounded px-3 py-1 w-full"
                        :class="textSize.p"
                        :style="{ color: content.textColor || '#ffffff', ...subtitleStyle }"
                    />
                </template>
                <template v-else>
                    {{ content.subtitle }}
                    <PencilIcon class="w-3 h-3 inline opacity-0 group-hover/sub:opacity-70 ml-2" aria-hidden="true" />
                </template>
            </p>
        </div>
    </div>
</template>
