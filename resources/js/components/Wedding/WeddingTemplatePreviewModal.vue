<script setup lang="ts">
/**
 * Wedding Template Preview Modal
 * Shows a live preview of how the wedding website will look with the selected template
 */
import { computed } from 'vue';
import { XMarkIcon, HeartIcon } from '@heroicons/vue/24/outline';

interface TemplateSettings {
    colors: {
        primary: string;
        secondary: string;
        accent: string;
        background: string;
        text: string;
        textLight: string;
    };
    fonts: {
        heading: string;
        body: string;
    };
    layout: {
        heroStyle: string;
        navigationStyle: string;
        showCountdown: boolean;
        showGallery: boolean;
    };
    decorations: {
        backgroundPattern: string;
        headerImage: string | null;
        borderStyle: string;
    };
}

interface Template {
    id: number;
    name: string;
    slug: string;
    description: string;
    preview_image: string;
    settings: TemplateSettings;
    is_premium: boolean;
}

const props = defineProps<{
    show: boolean;
    template: Template | null;
    groomName?: string;
    brideName?: string;
    weddingDate?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'select'): void;
}>();

const colors = computed(() => props.template?.settings?.colors || {
    primary: '#9333ea',
    secondary: '#ec4899',
    accent: '#f59e0b',
    background: '#ffffff',
    text: '#1f2937',
    textLight: '#6b7280',
});

const fonts = computed(() => props.template?.settings?.fonts || {
    heading: 'Great Vibes',
    body: 'Inter',
});

const decorations = computed(() => props.template?.settings?.decorations || {
    backgroundPattern: 'flora',
    headerImage: '/images/Wedding/flora.jpg',
    borderStyle: 'elegant',
});

const displayGroomName = computed(() => props.groomName || 'John');
const displayBrideName = computed(() => props.brideName || 'Jane');
const displayDate = computed(() => {
    if (props.weddingDate) {
        return new Date(props.weddingDate).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).toUpperCase();
    }
    return 'SATURDAY, DECEMBER 6, 2025';
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show && template" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" @click="emit('close')">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden" @click.stop>
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ template.name }}</h2>
                        <p class="text-sm text-gray-500">{{ template.description }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            @click="emit('select')"
                            class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors"
                        >
                            Use This Template
                        </button>
                        <button @click="emit('close')" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Close">
                            <XMarkIcon class="w-5 h-5 text-gray-500" />
                        </button>
                    </div>
                </div>

                <!-- Preview Content -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Preview Frame -->
                    <div class="p-6 bg-gray-100">
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden max-w-3xl mx-auto">
                            <!-- Mini Website Preview -->
                            <div class="relative" :style="{ backgroundColor: colors.background }">
                                <!-- Header Decoration -->
                                <div v-if="decorations.headerImage" class="h-32 overflow-hidden">
                                    <img 
                                        :src="decorations.headerImage" 
                                        alt="" 
                                        class="w-full h-full object-cover object-top opacity-80"
                                    />
                                </div>
                                <div v-else class="h-32" :style="{ background: `linear-gradient(135deg, ${colors.primary}20, ${colors.secondary}20)` }"></div>

                                <!-- Couple Names -->
                                <div class="text-center py-8 px-4">
                                    <h1 
                                        class="text-4xl md:text-5xl mb-2"
                                        :style="{ 
                                            fontFamily: fonts.heading + ', cursive',
                                            background: `linear-gradient(to right, ${colors.primary}, ${colors.secondary})`,
                                            WebkitBackgroundClip: 'text',
                                            WebkitTextFillColor: 'transparent',
                                            backgroundClip: 'text'
                                        }"
                                    >
                                        {{ displayGroomName }} & {{ displayBrideName }}
                                    </h1>
                                    <p class="text-xs font-semibold tracking-[0.2em] uppercase" :style="{ color: colors.primary }">
                                        {{ displayDate }}
                                    </p>
                                </div>

                                <!-- Navigation Preview -->
                                <nav class="border-y bg-white/80" :style="{ borderColor: colors.primary + '30' }">
                                    <div class="flex justify-center gap-6 py-3 text-xs font-medium tracking-wide">
                                        <span :style="{ color: colors.primary }" class="border-b-2" :class="{ 'border-current': true }">Home</span>
                                        <span :style="{ color: colors.primary + '80' }">Program</span>
                                        <span :style="{ color: colors.primary + '80' }">Q + A</span>
                                        <span :style="{ color: colors.primary + '80' }">Location</span>
                                        <span :style="{ color: colors.primary + '80' }">RSVP</span>
                                    </div>
                                </nav>

                                <!-- Hero Image Placeholder -->
                                <div class="p-6">
                                    <div 
                                        class="aspect-[4/3] max-w-sm mx-auto rounded-lg flex items-center justify-center"
                                        :style="{ background: `linear-gradient(135deg, ${colors.primary}20, ${colors.secondary}20)` }"
                                    >
                                        <HeartIcon class="w-16 h-16" :style="{ color: colors.primary + '40' }" />
                                    </div>
                                </div>

                                <!-- Countdown Preview -->
                                <div class="px-6 pb-6">
                                    <div 
                                        class="max-w-md mx-auto rounded-lg p-4"
                                        :style="{ 
                                            background: `linear-gradient(to bottom right, ${colors.primary}10, ${colors.secondary}10)`,
                                            borderColor: colors.primary + '30',
                                            borderWidth: '1px'
                                        }"
                                    >
                                        <p class="text-xs tracking-[0.2em] uppercase mb-3 text-center font-medium" :style="{ color: colors.primary }">
                                            Counting Down To
                                        </p>
                                        <div class="grid grid-cols-4 gap-2">
                                            <div v-for="unit in ['Days', 'Hours', 'Mins', 'Secs']" :key="unit" class="text-center">
                                                <div 
                                                    class="rounded-lg p-2"
                                                    :style="{ 
                                                        background: `linear-gradient(to bottom right, ${colors.primary}15, ${colors.secondary}15)`,
                                                        borderColor: colors.primary + '20',
                                                        borderWidth: '1px'
                                                    }"
                                                >
                                                    <div class="text-lg font-bold" :style="{ color: colors.primary }">00</div>
                                                    <div class="text-[10px] uppercase" :style="{ color: colors.primary + 'aa' }">{{ unit }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- RSVP Button Preview -->
                                <div class="text-center pb-8">
                                    <button 
                                        class="px-8 py-3 text-white text-sm font-semibold tracking-wider rounded-full shadow-lg"
                                        :style="{ background: `linear-gradient(to right, ${colors.secondary}, ${colors.primary})` }"
                                    >
                                        RSVP NOW
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Color Palette Display -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Color Palette</h3>
                        <div class="flex gap-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full shadow-sm" :style="{ backgroundColor: colors.primary }"></div>
                                <span class="text-xs text-gray-500">Primary</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full shadow-sm" :style="{ backgroundColor: colors.secondary }"></div>
                                <span class="text-xs text-gray-500">Secondary</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full shadow-sm border border-gray-200" :style="{ backgroundColor: colors.background }"></div>
                                <span class="text-xs text-gray-500">Background</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full shadow-sm" :style="{ backgroundColor: colors.text }"></div>
                                <span class="text-xs text-gray-500">Text</span>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-4 text-xs text-gray-500">
                            <span>Heading Font: <strong class="text-gray-700">{{ fonts.heading }}</strong></span>
                            <span>Body Font: <strong class="text-gray-700">{{ fonts.body }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
