<script setup lang="ts">
/**
 * Contact Section Preview Component
 * Layouts: side-by-side (default), stacked, with-map
 */
import { computed } from 'vue';
import { EnvelopeIcon, PhoneIcon, MapPinIcon } from '@heroicons/vue/24/outline';
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
const layout = computed(() => content.value?.layout || 'side-by-side');

const bgStyle = computed(() => getBackgroundStyle(style.value, '#f9fafb', '#111827'));

// Text alignment class
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
        class="h-full flex flex-col justify-center"
        :class="spacing.section"
        :style="bgStyle"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <div :class="textAlignClass" class="mb-6">
                <h2 :class="[textSize.h2, 'font-bold mb-3']">
                    {{ content.title || 'Contact Us' }}
                </h2>
                <p class="text-gray-600 max-w-xl" :class="[textSize.p, { 'mx-auto': content.textPosition !== 'left' && content.textPosition !== 'right' }]">
                    {{ content.description || 'Get in touch with our team' }}
                </p>
            </div>

            <!-- Side by Side Layout -->
            <template v-if="layout === 'side-by-side'">
                <div class="max-w-5xl mx-auto">
                    <div class="grid lg:grid-cols-2 gap-8">
                        <!-- Contact Info -->
                        <div class="space-y-6">
                            <div v-if="content.email" class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <EnvelopeIcon class="w-5 h-5 text-blue-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Email</h4>
                                    <p class="text-gray-600" :class="textSize.p">{{ content.email }}</p>
                                </div>
                            </div>
                            <div v-if="content.phone" class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <PhoneIcon class="w-5 h-5 text-blue-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Phone</h4>
                                    <p class="text-gray-600" :class="textSize.p">{{ content.phone }}</p>
                                </div>
                            </div>
                            <div v-if="content.address" class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <MapPinIcon class="w-5 h-5 text-blue-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Address</h4>
                                    <p class="text-gray-600" :class="textSize.p">{{ content.address }}</p>
                                </div>
                            </div>
                        </div>
                        <!-- Form -->
                        <div v-if="content.showForm !== false" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="space-y-4">
                                <input type="text" placeholder="Your Name" class="w-full px-4 py-3 border border-gray-200 rounded-lg" />
                                <input type="email" placeholder="Email Address" class="w-full px-4 py-3 border border-gray-200 rounded-lg" />
                                <textarea placeholder="Your Message" rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-lg resize-none"></textarea>
                                <button class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                    Send Message
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- With Map Layout -->
            <template v-else-if="layout === 'with-map'">
                <div class="max-w-5xl mx-auto">
                    <div class="grid lg:grid-cols-2 gap-8">
                        <!-- Map -->
                        <div class="aspect-video lg:aspect-auto lg:h-full bg-gray-200 rounded-xl overflow-hidden">
                            <iframe 
                                v-if="content.mapEmbedUrl" 
                                :src="content.mapEmbedUrl" 
                                class="w-full h-full min-h-[300px]" 
                                frameborder="0" 
                                allowfullscreen
                            ></iframe>
                            <div v-else class="w-full h-full min-h-[300px] flex items-center justify-center text-gray-400">
                                <MapPinIcon class="w-12 h-12" aria-hidden="true" />
                            </div>
                        </div>
                        <!-- Form -->
                        <div v-if="content.showForm !== false" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="space-y-4">
                                <input type="text" placeholder="Your Name" class="w-full px-4 py-3 border border-gray-200 rounded-lg" />
                                <input type="email" placeholder="Email Address" class="w-full px-4 py-3 border border-gray-200 rounded-lg" />
                                <textarea placeholder="Your Message" rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-lg resize-none"></textarea>
                                <button class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                    Send Message
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Stacked Layout (Default) -->
            <template v-else>
                <div class="max-w-lg mx-auto w-full">
                    <!-- Contact Info -->
                    <div v-if="content.email || content.phone || content.address" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div v-if="content.email" class="text-center p-4 bg-white rounded-lg">
                            <EnvelopeIcon class="w-6 h-6 mx-auto mb-2 text-blue-600" aria-hidden="true" />
                            <p class="text-sm text-gray-600">{{ content.email }}</p>
                        </div>
                        <div v-if="content.phone" class="text-center p-4 bg-white rounded-lg">
                            <PhoneIcon class="w-6 h-6 mx-auto mb-2 text-blue-600" aria-hidden="true" />
                            <p class="text-sm text-gray-600">{{ content.phone }}</p>
                        </div>
                        <div v-if="content.address" class="text-center p-4 bg-white rounded-lg">
                            <MapPinIcon class="w-6 h-6 mx-auto mb-2 text-blue-600" aria-hidden="true" />
                            <p class="text-sm text-gray-600">{{ content.address }}</p>
                        </div>
                    </div>
                    <!-- Form -->
                    <div
                        v-if="content.showForm !== false"
                        class="bg-white rounded-xl shadow-lg border border-gray-100"
                        :class="isMobile ? 'p-4' : 'p-6'"
                    >
                        <div :class="isMobile ? 'space-y-3' : 'space-y-4'">
                            <div :class="isMobile ? 'space-y-3' : 'grid grid-cols-2 gap-4'">
                                <input type="text" placeholder="First Name" class="w-full border border-gray-200 rounded-lg" :class="isMobile ? 'px-3 py-2 text-sm' : 'px-4 py-3'" />
                                <input type="text" placeholder="Last Name" class="w-full border border-gray-200 rounded-lg" :class="isMobile ? 'px-3 py-2 text-sm' : 'px-4 py-3'" />
                            </div>
                            <input type="email" placeholder="Email Address" class="w-full border border-gray-200 rounded-lg" :class="isMobile ? 'px-3 py-2 text-sm' : 'px-4 py-3'" />
                            <input type="tel" placeholder="Phone Number" class="w-full border border-gray-200 rounded-lg" :class="isMobile ? 'px-3 py-2 text-sm' : 'px-4 py-3'" />
                            <textarea placeholder="How can we help you?" :rows="isMobile ? 3 : 4" class="w-full border border-gray-200 rounded-lg resize-none" :class="isMobile ? 'px-3 py-2 text-sm' : 'px-4 py-3'"></textarea>
                            <button class="w-full bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors" :class="isMobile ? 'py-2 text-sm' : 'py-3'">
                                Send Message
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
