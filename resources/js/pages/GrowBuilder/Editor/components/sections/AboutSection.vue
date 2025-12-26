<script setup lang="ts">
/**
 * About Section Preview Component
 */
import { computed } from 'vue';
import { PhotoIcon, PencilIcon } from '@heroicons/vue/24/outline';
import { getBackgroundStyle } from '../../composables/useBackgroundStyle';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
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
</script>

<template>
    <div
        class="h-full flex flex-col justify-center overflow-hidden"
        :class="spacing.section"
        :style="bgStyle"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <div
                class="flex items-center max-w-5xl mx-auto"
                :class="[
                    isMobile ? 'flex-col gap-6' : 'flex-col md:flex-row gap-8',
                    { 'md:flex-row-reverse': content.imagePosition === 'left' && !isMobile }
                ]"
            >
                <!-- Text Content -->
                <div :class="content.image && !isMobile ? 'md:w-1/2' : 'w-full'">
                    <h2
                        v-if="isEditing(section.id, 'title')"
                        class="relative inline-block w-full"
                    >
                        <input
                            :value="editingValue"
                            @input="emit('update:editingValue', ($event.target as HTMLInputElement).value)"
                            @blur="saveInlineEdit()"
                            @keydown="handleInlineKeydown($event)"
                            class="inline-edit-input font-bold border border-blue-400 rounded px-3 py-1 text-gray-900 w-full"
                            :class="textSize.h2"
                        />
                    </h2>
                    <h2
                        v-else
                        @click.stop="startInlineEdit( section.id, 'title', content.title)"
                        class="font-bold mb-4 cursor-pointer hover:bg-gray-100 rounded px-2 py-1 transition-colors group/title"
                        :class="textSize.h2"
                    >
                        {{ content.title || 'About Us' }}
                        <PencilIcon class="w-4 h-4 inline opacity-0 group-hover/title:opacity-50 ml-2" aria-hidden="true" />
                    </h2>
                    <p
                        v-if="isEditing(section.id, 'description')"
                        class="relative"
                    >
                        <textarea
                            :value="editingValue"
                            @input="emit('update:editingValue', ($event.target as HTMLTextAreaElement).value)"
                            @blur="saveInlineEdit()"
                            @keydown="handleInlineKeydown($event)"
                            rows="4"
                            class="inline-edit-input w-full border border-blue-400 rounded px-3 py-2 text-gray-900"
                            :class="textSize.p"
                        ></textarea>
                    </p>
                    <p
                        v-else
                        @click.stop="startInlineEdit( section.id, 'description', content.description)"
                        class="text-gray-600 leading-relaxed cursor-pointer hover:bg-gray-100 rounded px-2 py-1 transition-colors group/desc"
                        :class="textSize.p"
                    >
                        {{ content.description || 'Tell your story here...' }}
                        <PencilIcon class="w-3 h-3 inline opacity-0 group-hover/desc:opacity-50 ml-2" aria-hidden="true" />
                    </p>
                </div>

                <!-- Image -->
                <div v-if="content.image" :class="isMobile ? 'w-full' : 'md:w-1/2'">
                    <img
                        :src="content.image"
                        :class="isMobile ? 'w-full h-48' : 'w-full h-64'"
                        class="object-cover rounded-xl shadow-lg"
                    />
                </div>
                <div
                    v-else
                    :class="[
                        isMobile ? 'w-full h-40' : 'md:w-1/2 h-56',
                        'bg-gradient-to-br from-blue-100 to-indigo-100 rounded-xl flex items-center justify-center'
                    ]"
                >
                    <div class="text-center">
                        <PhotoIcon
                            :class="isMobile ? 'w-10 h-10' : 'w-12 h-12'"
                            class="text-blue-300 mx-auto mb-2"
                            aria-hidden="true"
                        />
                        <p class="text-blue-400 text-xs">Add an image</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
