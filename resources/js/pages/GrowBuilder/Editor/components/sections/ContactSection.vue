<script setup lang="ts">
/**
 * Contact Section Preview Component
 */
import { computed } from 'vue';
import { getBackgroundStyle } from '../../composables/useBackgroundStyle';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
    getSectionContentTransform: (section: Section) => string;
}>();

const content = computed(() => props.section.content);
const style = computed(() => props.section.style);

const bgStyle = computed(() => getBackgroundStyle(style.value, '#ffffff', '#111827'));
</script>

<template>
    <div
        class="h-full flex flex-col justify-center"
        :class="spacing.section"
        :style="bgStyle"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <h2 :class="[textSize.h2, 'font-bold text-center mb-3']">
                {{ content.title || 'Contact Us' }}
            </h2>
            <p
                class="text-center text-gray-600 mb-6 max-w-xl mx-auto"
                :class="textSize.p"
            >
                {{ content.description || 'Get in touch with our team' }}
            </p>
            <div class="max-w-lg mx-auto w-full">
                <div
                    class="bg-white rounded-xl shadow-lg border border-gray-100"
                    :class="isMobile ? 'p-4' : 'p-6'"
                >
                    <div :class="isMobile ? 'space-y-3' : 'space-y-4'">
                        <div :class="isMobile ? 'space-y-3' : 'grid grid-cols-2 gap-4'">
                            <input
                                type="text"
                                placeholder="First Name"
                                class="w-full border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="isMobile ? 'px-3 py-2 text-sm' : 'px-4 py-3'"
                            />
                            <input
                                type="text"
                                placeholder="Last Name"
                                class="w-full border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="isMobile ? 'px-3 py-2 text-sm' : 'px-4 py-3'"
                            />
                        </div>
                        <input
                            type="email"
                            placeholder="Email Address"
                            class="w-full border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="isMobile ? 'px-3 py-2 text-sm' : 'px-4 py-3'"
                        />
                        <input
                            type="tel"
                            placeholder="Phone Number"
                            class="w-full border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="isMobile ? 'px-3 py-2 text-sm' : 'px-4 py-3'"
                        />
                        <textarea
                            placeholder="How can we help you?"
                            :rows="isMobile ? 3 : 4"
                            class="w-full border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            :class="isMobile ? 'px-3 py-2 text-sm' : 'px-4 py-3'"
                        ></textarea>
                        <button
                            class="w-full bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                            :class="isMobile ? 'py-2 text-sm' : 'py-3'"
                        >
                            Send Message
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
