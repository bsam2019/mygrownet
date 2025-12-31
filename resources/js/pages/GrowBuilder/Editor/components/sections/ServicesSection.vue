<script setup lang="ts">
/**
 * Services Section Preview Component
 * Supports layouts: grid, list, cards-images, alternating
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
const layout = computed(() => content.value.layout || 'grid');

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

// Get icon emoji based on icon name
const getIconEmoji = (icon: string) => {
    const iconMap: Record<string, string> = {
        'chart': '📊', 'code': '💻', 'sparkles': '✨', 'briefcase': '💼',
        'globe': '🌍', 'cog': '⚙️', 'users': '👥', 'shield': '🛡️',
    };
    return iconMap[icon] || '📦';
};

// Grid columns based on layout and settings
const gridColsClass = computed(() => {
    const cols = content.value.columns || 3;
    if (props.isMobile) return 'grid-cols-1';
    return `grid-cols-1 sm:grid-cols-2 lg:grid-cols-${cols}`;
});
</script>

<template>
    <div
        class="h-full flex flex-col justify-center overflow-hidden"
        :class="spacing.section"
        :style="bgStyle"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <!-- Header -->
            <div :class="textAlignClass" class="mb-8">
                <h2 :class="[textSize.h2, 'font-bold mb-4']">
                    {{ content.title || 'Our Services' }}
                </h2>
                <p v-if="content.subtitle" class="text-gray-600 max-w-2xl" :class="[textSize.p, { 'mx-auto': content.textPosition !== 'left' && content.textPosition !== 'right' }]">
                    {{ content.subtitle }}
                </p>
            </div>
            
            <!-- List Layout -->
            <div v-if="layout === 'list'" class="space-y-4">
                <div v-for="(item, idx) in content.items || []" :key="idx" class="flex gap-4 items-start p-4 bg-white rounded-xl shadow-sm border border-gray-100">
                    <div v-if="item.icon" class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="text-xl">{{ getIconEmoji(item.icon) }}</span>
                    </div>
                    <div class="flex-1">
                        <h3 :class="[textSize.h3, 'font-semibold mb-2 text-gray-900']">{{ item.title }}</h3>
                        <p :class="[textSize.p, 'text-gray-600']">{{ item.description }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Cards with Images Layout -->
            <div v-else-if="layout === 'cards-images'" class="grid gap-6" :class="gridColsClass">
                <div v-for="(item, idx) in content.items || []" :key="idx" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group">
                    <div v-if="item.image" class="aspect-video bg-gray-100 overflow-hidden">
                        <img :src="item.image" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                    </div>
                    <div v-else class="aspect-video bg-gray-100 flex items-center justify-center">
                        <span class="text-gray-400 text-sm">Add image</span>
                    </div>
                    <div class="p-5">
                        <h3 :class="[textSize.h3, 'font-semibold mb-2 text-gray-900']">{{ item.title }}</h3>
                        <p :class="[textSize.p, 'text-gray-600']">{{ item.description }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Alternating Rows Layout -->
            <div v-else-if="layout === 'alternating'" class="space-y-12">
                <div v-for="(item, idx) in content.items || []" :key="idx" class="flex flex-col lg:flex-row gap-8 items-center" :class="idx % 2 === 1 ? 'lg:flex-row-reverse' : ''">
                    <div class="lg:w-1/2">
                        <div v-if="item.icon" class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <span class="text-2xl">{{ getIconEmoji(item.icon) }}</span>
                        </div>
                        <h3 :class="[textSize.h2, 'font-semibold mb-3 text-gray-900']">{{ item.title }}</h3>
                        <p :class="[textSize.p, 'text-gray-600']">{{ item.description }}</p>
                    </div>
                    <div class="lg:w-1/2">
                        <img v-if="item.image" :src="item.image" :alt="item.title" class="rounded-xl shadow-lg w-full" />
                        <div v-else class="aspect-video bg-gray-100 rounded-xl flex items-center justify-center">
                            <span class="text-gray-400 text-sm">Service image</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Default Grid Layout -->
            <div v-else class="grid gap-6" :class="gridColsClass">
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
