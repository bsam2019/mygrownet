<script setup lang="ts">
/**
 * Team Section Preview Component
 * Supports layouts: grid, social, compact
 */
import { computed } from 'vue';
import { UserGroupIcon } from '@heroicons/vue/24/outline';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
    gridCols4: string;
    getSectionContentTransform: (section: Section) => string;
}>();

const content = computed(() => props.section.content);
const style = computed(() => props.section.style);
const layout = computed(() => content.value.layout || 'grid');

// Text alignment
const textAlignClass = computed(() => {
    const align = content.value?.textPosition || 'center';
    return {
        'text-left': align === 'left',
        'text-center': align === 'center',
        'text-right': align === 'right',
    };
});
</script>

<template>
    <div
        class="h-full flex flex-col justify-center overflow-hidden"
        :class="spacing.section"
        :style="{ color: style?.textColor || '#111827' }"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <!-- Header -->
            <div :class="textAlignClass" class="mb-8">
                <h2 :class="[textSize.h2, 'font-bold']">
                    {{ content.title || 'Our Team' }}
                </h2>
            </div>
            
            <!-- Cards with Social Links Layout -->
            <div v-if="layout === 'social'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="(member, idx) in content.items || []" :key="idx" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
                    <div class="w-24 h-24 mx-auto mb-4 rounded-full overflow-hidden bg-gray-200">
                        <img v-if="member.image" :src="member.image" class="w-full h-full object-cover" :alt="member.name" />
                        <div v-else class="w-full h-full flex items-center justify-center bg-blue-100">
                            <span class="text-blue-600 font-bold text-2xl">{{ member.name?.charAt(0) }}</span>
                        </div>
                    </div>
                    <h3 class="font-semibold text-lg text-gray-900">{{ member.name }}</h3>
                    <p class="text-gray-500 text-sm mb-3">{{ member.role }}</p>
                    <p v-if="member.bio" class="text-gray-600 text-sm mb-4">{{ member.bio }}</p>
                    <div class="flex justify-center gap-3">
                        <a v-if="member.linkedin" :href="member.linkedin" class="text-gray-400 hover:text-blue-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                        <a v-if="member.twitter" :href="member.twitter" class="text-gray-400 hover:text-blue-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        </a>
                        <a v-if="member.email" :href="`mailto:${member.email}`" class="text-gray-400 hover:text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Compact List Layout -->
            <div v-else-if="layout === 'compact'" class="max-w-3xl mx-auto space-y-4">
                <div v-for="(member, idx) in content.items || []" :key="idx" class="flex items-center gap-4 p-4 bg-white rounded-lg border border-gray-100">
                    <div class="w-14 h-14 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                        <img v-if="member.image" :src="member.image" class="w-full h-full object-cover" :alt="member.name" />
                        <div v-else class="w-full h-full flex items-center justify-center bg-blue-100">
                            <span class="text-blue-600 font-bold">{{ member.name?.charAt(0) }}</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ member.name }}</h3>
                        <p class="text-gray-500 text-sm">{{ member.role }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a v-if="member.linkedin" :href="member.linkedin" class="text-gray-400 hover:text-blue-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                        <a v-if="member.email" :href="`mailto:${member.email}`" class="text-gray-400 hover:text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Default Grid Layout -->
            <div v-else class="grid" :class="[gridCols4, spacing.gap]">
                <div
                    v-for="(member, idx) in content.items || []"
                    :key="idx"
                    class="text-center"
                >
                    <div
                        :class="isMobile ? 'w-16 h-16' : 'w-20 h-20'"
                        class="mx-auto mb-3 rounded-full overflow-hidden bg-gray-200"
                    >
                        <img
                            v-if="member.image"
                            :src="member.image"
                            class="w-full h-full object-cover"
                            :alt="member.name"
                        />
                        <UserGroupIcon v-else class="w-8 h-8 m-4 text-gray-400" aria-hidden="true" />
                    </div>
                    <h3 class="font-semibold" :class="textSize.p">{{ member.name }}</h3>
                    <p class="text-gray-500 text-xs">{{ member.role }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
