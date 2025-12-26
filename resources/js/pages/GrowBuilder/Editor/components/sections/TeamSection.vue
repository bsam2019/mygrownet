<script setup lang="ts">
/**
 * Team Section Preview Component
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
</script>

<template>
    <div
        class="h-full flex flex-col justify-center overflow-hidden"
        :class="spacing.section"
        :style="{ color: style?.textColor || '#111827' }"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <h2 :class="[textSize.h2, 'font-bold text-center mb-8']">
                {{ content.title || 'Our Team' }}
            </h2>
            <div class="grid" :class="[gridCols4, spacing.gap]">
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
