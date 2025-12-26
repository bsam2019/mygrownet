<script setup lang="ts">
/**
 * Section Inspector Component
 * Settings panel for editing section content, style, and advanced options
 */
import { ref, computed } from 'vue';
import { XMarkIcon, PhotoIcon, TrashIcon, FilmIcon, Squares2X2Icon } from '@heroicons/vue/24/outline';
import RichTextEditor from '../common/RichTextEditor.vue';
import IconPickerModal from '../modals/IconPickerModal.vue';
import VideoEmbedModal from '../modals/VideoEmbedModal.vue';

interface Section {
    id: string;
    type: string;
    content: Record<string, any>;
    style: Record<string, any>;
}

interface SectionType {
    type: string;
    name: string;
    icon: any;
    category: string;
    description: string;
}

const props = defineProps<{
    section: Section;
    sectionType: SectionType | null;
    activeTab: 'content' | 'style' | 'advanced';
    darkMode?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:activeTab', value: 'content' | 'style' | 'advanced'): void;
    (e: 'updateContent', field: string, value: any): void;
    (e: 'updateStyle', field: string, value: any): void;
    (e: 'openMediaLibrary', sectionId: string, field: string): void;
    (e: 'addItem'): void;
    (e: 'removeItem', index: number): void;
    (e: 'addPlan'): void;
    (e: 'removePlan', index: number): void;
    (e: 'addPlanFeature', planIndex: number): void;
    (e: 'addFaqItem'): void;
    (e: 'removeFaqItem', index: number): void;
    (e: 'addTeamMember'): void;
    (e: 'removeTeamMember', index: number): void;
    (e: 'addBlogPost'): void;
    (e: 'removeBlogPost', index: number): void;
    (e: 'addStatItem'): void;
    (e: 'removeStatItem', index: number): void;
    (e: 'removeGalleryImage', index: number): void;
}>();

// Icon Picker State
const showIconPicker = ref(false);
const iconPickerTarget = ref<{ itemIndex: number } | null>(null);

// Video Embed State
const showVideoEmbed = ref(false);

// Theme colors for style tab
const themeColors = [
    { name: 'White', value: '#ffffff' },
    { name: 'Gray 50', value: '#f9fafb' },
    { name: 'Gray 100', value: '#f3f4f6' },
    { name: 'Gray 900', value: '#111827' },
    { name: 'Blue 50', value: '#eff6ff' },
    { name: 'Blue 600', value: '#2563eb' },
    { name: 'Green 50', value: '#f0fdf4' },
    { name: 'Green 600', value: '#16a34a' },
];

// Gradient presets
const gradientPresets = [
    { name: 'Ocean', from: '#667eea', to: '#764ba2', direction: 'to-r' },
    { name: 'Sunset', from: '#f093fb', to: '#f5576c', direction: 'to-r' },
    { name: 'Forest', from: '#11998e', to: '#38ef7d', direction: 'to-r' },
    { name: 'Fire', from: '#f12711', to: '#f5af19', direction: 'to-r' },
    { name: 'Sky', from: '#2193b0', to: '#6dd5ed', direction: 'to-b' },
    { name: 'Purple', from: '#8e2de2', to: '#4a00e0', direction: 'to-br' },
    { name: 'Midnight', from: '#232526', to: '#414345', direction: 'to-b' },
    { name: 'Coral', from: '#ff9966', to: '#ff5e62', direction: 'to-r' },
];

// Gradient directions
const gradientDirections = [
    { label: '→', value: 'to-r' },
    { label: '↓', value: 'to-b' },
    { label: '↘', value: 'to-br' },
    { label: '↗', value: 'to-tr' },
];

// Computed gradient CSS
const computedGradient = computed(() => {
    const from = props.section.style?.gradientFrom || '#3b82f6';
    const to = props.section.style?.gradientTo || '#8b5cf6';
    const dir = props.section.style?.gradientDirection || 'to-r';

    const degMap: Record<string, string> = {
        'to-r': '90deg',
        'to-b': '180deg',
        'to-br': '135deg',
        'to-tr': '45deg',
    };

    return `linear-gradient(${degMap[dir] || '90deg'}, ${from}, ${to})`;
});

const updateContent = (field: string, value: any) => {
    emit('updateContent', field, value);
};

const updateStyle = (field: string, value: any) => {
    emit('updateStyle', field, value);
};

// Switch to gradient mode with defaults
const switchToGradient = () => {
    emit('updateStyle', 'backgroundType', 'gradient');
    emit('updateStyle', 'gradientFrom', props.section.style?.gradientFrom || '#3b82f6');
    emit('updateStyle', 'gradientTo', props.section.style?.gradientTo || '#8b5cf6');
    emit('updateStyle', 'gradientDirection', props.section.style?.gradientDirection || 'to-r');
};

// Apply gradient preset
const applyGradientPreset = (preset: { from: string; to: string; direction: string }) => {
    emit('updateStyle', 'gradientFrom', preset.from);
    emit('updateStyle', 'gradientTo', preset.to);
    emit('updateStyle', 'gradientDirection', preset.direction || 'to-r');
};

// Icon Picker handlers
const openIconPicker = (itemIndex: number) => {
    iconPickerTarget.value = { itemIndex };
    showIconPicker.value = true;
};

const handleIconSelect = (iconName: string, iconStyle: 'outline' | 'solid') => {
    if (iconPickerTarget.value !== null && props.section.content.items) {
        const item = props.section.content.items[iconPickerTarget.value.itemIndex];
        if (item) {
            item.icon = iconName;
            item.iconStyle = iconStyle;
        }
    }
    showIconPicker.value = false;
    iconPickerTarget.value = null;
};

// Video Embed handlers
const handleVideoSelect = (videoInfo: { platform: string; videoId: string; embedUrl: string; thumbnailUrl: string }) => {
    emit('updateContent', 'videoBackground', videoInfo.embedUrl);
    emit('updateContent', 'videoThumbnail', videoInfo.thumbnailUrl);
    emit('updateContent', 'videoPlatform', videoInfo.platform);
    showVideoEmbed.value = false;
};

// Get icon component for display
const getIconComponent = (iconName: string) => {
    // Return a placeholder - actual icon rendering happens in SectionRenderer
    return iconName;
};
</script>

<template>
    <div class="flex flex-col h-full">
        <!-- Inspector Tabs -->
        <div class="flex border-b border-gray-200">
            <button
                @click="emit('update:activeTab', 'content')"
                :class="['flex-1 py-2.5 text-sm font-medium transition-colors border-b-2', activeTab === 'content' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700']"
            >
                Content
            </button>
            <button
                @click="emit('update:activeTab', 'style')"
                :class="['flex-1 py-2.5 text-sm font-medium transition-colors border-b-2', activeTab === 'style' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700']"
            >
                Style
            </button>
            <button
                @click="emit('update:activeTab', 'advanced')"
                :class="['flex-1 py-2.5 text-sm font-medium transition-colors border-b-2', activeTab === 'advanced' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700']"
            >
                Advanced
            </button>
        </div>

        <!-- Inspector Content -->
        <div :class="['flex-1 overflow-y-auto p-4', darkMode ? 'custom-scrollbar-dark' : 'custom-scrollbar']">
            <!-- Content Tab -->
            <div v-if="activeTab === 'content'" class="space-y-4">
                <!-- Hero Section Content -->
                <template v-if="section.type === 'hero'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                        <textarea
                            :value="section.content.subtitle"
                            @input="updateContent('subtitle', ($event.target as HTMLTextAreaElement).value)"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        ></textarea>
                    </div>
                    
                    <!-- Background Type Selection -->
                    <div class="border-t border-gray-200 pt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Background Type</label>
                        <div class="flex gap-2 mb-3">
                            <button
                                @click="updateContent('backgroundType', 'image')"
                                :class="[
                                    'flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-lg border-2 transition-colors text-sm',
                                    (!section.content.backgroundType || section.content.backgroundType === 'image')
                                        ? 'border-blue-500 bg-blue-50 text-blue-700'
                                        : 'border-gray-200 text-gray-600 hover:border-gray-300'
                                ]"
                            >
                                <PhotoIcon class="w-4 h-4" aria-hidden="true" />
                                Image
                            </button>
                            <button
                                @click="updateContent('backgroundType', 'video')"
                                :class="[
                                    'flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-lg border-2 transition-colors text-sm',
                                    section.content.backgroundType === 'video'
                                        ? 'border-blue-500 bg-blue-50 text-blue-700'
                                        : 'border-gray-200 text-gray-600 hover:border-gray-300'
                                ]"
                            >
                                <FilmIcon class="w-4 h-4" aria-hidden="true" />
                                Video
                            </button>
                        </div>
                    </div>
                    
                    <!-- Image Background -->
                    <div v-if="!section.content.backgroundType || section.content.backgroundType === 'image'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Background Image</label>
                        <div v-if="section.content.backgroundImage" class="relative mb-2">
                            <img :src="section.content.backgroundImage" class="w-full h-24 object-cover rounded-lg" />
                            <button @click="updateContent('backgroundImage', '')" class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full hover:bg-red-600">
                                <XMarkIcon class="w-4 h-4" aria-hidden="true" />
                            </button>
                        </div>
                        <button
                            @click="emit('openMediaLibrary', section.id, 'backgroundImage')"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-blue-400 hover:text-blue-600 transition-colors"
                        >
                            <PhotoIcon class="w-5 h-5" aria-hidden="true" />
                            <span class="text-sm">{{ section.content.backgroundImage ? 'Change Background' : 'Add Background' }}</span>
                        </button>
                    </div>
                    
                    <!-- Video Background -->
                    <div v-else-if="section.content.backgroundType === 'video'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Video Background</label>
                        <div v-if="section.content.videoBackground" class="relative mb-2">
                            <img 
                                :src="section.content.videoThumbnail || 'https://via.placeholder.com/640x360?text=Video'" 
                                class="w-full h-24 object-cover rounded-lg" 
                            />
                            <div class="absolute inset-0 flex items-center justify-center bg-black/30 rounded-lg">
                                <div class="w-10 h-10 bg-white/90 rounded-full flex items-center justify-center">
                                    <FilmIcon class="w-5 h-5 text-gray-900" />
                                </div>
                            </div>
                            <button @click="updateContent('videoBackground', ''); updateContent('videoThumbnail', '')" class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full hover:bg-red-600">
                                <XMarkIcon class="w-4 h-4" aria-hidden="true" />
                            </button>
                        </div>
                        <button
                            @click="showVideoEmbed = true"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-blue-400 hover:text-blue-600 transition-colors"
                        >
                            <FilmIcon class="w-5 h-5" aria-hidden="true" />
                            <span class="text-sm">{{ section.content.videoBackground ? 'Change Video' : 'Add Video Background' }}</span>
                        </button>
                        <p class="text-xs text-gray-500 mt-1">YouTube or Vimeo videos work best</p>
                    </div>
                    
                    <!-- Overlay Settings (for image/video backgrounds) -->
                    <div v-if="section.content.backgroundImage || section.content.videoBackground" class="border-t border-gray-200 pt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Overlay</label>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Overlay Color</label>
                                <div class="flex gap-2">
                                    <button
                                        @click="updateContent('overlayColor', 'black')"
                                        :class="[
                                            'flex-1 py-2 rounded-lg border-2 text-sm font-medium transition-colors',
                                            (!section.content.overlayColor || section.content.overlayColor === 'black')
                                                ? 'border-gray-800 bg-gray-800 text-white'
                                                : 'border-gray-300 text-gray-600 hover:border-gray-400'
                                        ]"
                                    >
                                        Dark
                                    </button>
                                    <button
                                        @click="updateContent('overlayColor', 'white')"
                                        :class="[
                                            'flex-1 py-2 rounded-lg border-2 text-sm font-medium transition-colors',
                                            section.content.overlayColor === 'white'
                                                ? 'border-blue-500 bg-white text-gray-900'
                                                : 'border-gray-300 text-gray-600 hover:border-gray-400'
                                        ]"
                                    >
                                        Light
                                    </button>
                                    <button
                                        @click="updateContent('overlayColor', 'gradient')"
                                        :class="[
                                            'flex-1 py-2 rounded-lg border-2 text-sm font-medium transition-colors',
                                            section.content.overlayColor === 'gradient'
                                                ? 'border-blue-500 text-white'
                                                : 'border-gray-300 text-gray-600 hover:border-gray-400'
                                        ]"
                                        :style="section.content.overlayColor === 'gradient' ? { background: `linear-gradient(135deg, ${section.content.overlayGradientFrom || '#2563eb'}, ${section.content.overlayGradientTo || '#7c3aed'})` } : {}"
                                    >
                                        Gradient
                                    </button>
                                </div>
                            </div>
                            <!-- Gradient Color Customization -->
                            <div v-if="section.content.overlayColor === 'gradient'" class="space-y-3 p-3 bg-gray-50 rounded-lg">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">From Color</label>
                                        <div class="flex items-center gap-2">
                                            <div class="relative w-8 h-8 flex-shrink-0">
                                                <input
                                                    type="color"
                                                    :value="section.content.overlayGradientFrom || '#2563eb'"
                                                    @input="updateContent('overlayGradientFrom', ($event.target as HTMLInputElement).value)"
                                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                />
                                                <div
                                                    class="w-full h-full rounded border border-gray-300 shadow-sm"
                                                    :style="{ backgroundColor: section.content.overlayGradientFrom || '#2563eb' }"
                                                ></div>
                                            </div>
                                            <input
                                                :value="section.content.overlayGradientFrom || '#2563eb'"
                                                @input="updateContent('overlayGradientFrom', ($event.target as HTMLInputElement).value)"
                                                class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-xs font-mono text-gray-900 bg-white"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">To Color</label>
                                        <div class="flex items-center gap-2">
                                            <div class="relative w-8 h-8 flex-shrink-0">
                                                <input
                                                    type="color"
                                                    :value="section.content.overlayGradientTo || '#7c3aed'"
                                                    @input="updateContent('overlayGradientTo', ($event.target as HTMLInputElement).value)"
                                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                />
                                                <div
                                                    class="w-full h-full rounded border border-gray-300 shadow-sm"
                                                    :style="{ backgroundColor: section.content.overlayGradientTo || '#7c3aed' }"
                                                ></div>
                                            </div>
                                            <input
                                                :value="section.content.overlayGradientTo || '#7c3aed'"
                                                @input="updateContent('overlayGradientTo', ($event.target as HTMLInputElement).value)"
                                                class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-xs font-mono text-gray-900 bg-white"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <!-- Gradient Presets -->
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Presets</label>
                                    <div class="grid grid-cols-4 gap-1.5">
                                        <button
                                            v-for="preset in [
                                                { from: '#2563eb', to: '#7c3aed', name: 'Blue-Purple' },
                                                { from: '#059669', to: '#0891b2', name: 'Teal' },
                                                { from: '#dc2626', to: '#ea580c', name: 'Fire' },
                                                { from: '#7c3aed', to: '#db2777', name: 'Pink' },
                                                { from: '#1e3a8a', to: '#312e81', name: 'Deep' },
                                                { from: '#065f46', to: '#1e40af', name: 'Ocean' },
                                                { from: '#78350f', to: '#92400e', name: 'Warm' },
                                                { from: '#111827', to: '#374151', name: 'Dark' },
                                            ]"
                                            :key="preset.name"
                                            @click="updateContent('overlayGradientFrom', preset.from); updateContent('overlayGradientTo', preset.to)"
                                            class="h-6 rounded border border-gray-200 hover:scale-105 transition-transform"
                                            :style="{ background: `linear-gradient(135deg, ${preset.from}, ${preset.to})` }"
                                            :title="preset.name"
                                        ></button>
                                    </div>
                                </div>
                                <!-- Gradient Midpoint -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-xs text-gray-500">Color Balance</label>
                                        <span class="text-xs text-gray-500">{{ section.content.overlayGradientMidpoint || 50 }}%</span>
                                    </div>
                                    <input
                                        type="range"
                                        :value="section.content.overlayGradientMidpoint || 50"
                                        @input="updateContent('overlayGradientMidpoint', parseInt(($event.target as HTMLInputElement).value))"
                                        min="10"
                                        max="90"
                                        step="5"
                                        class="w-full h-2 rounded-lg appearance-none cursor-pointer"
                                        :style="{ background: `linear-gradient(90deg, ${section.content.overlayGradientFrom || '#2563eb'}, ${section.content.overlayGradientTo || '#7c3aed'})` }"
                                    />
                                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                                        <span>More {{ section.content.overlayGradientFrom ? 'left' : 'blue' }}</span>
                                        <span>More {{ section.content.overlayGradientTo ? 'right' : 'purple' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="text-xs text-gray-500">Overlay Opacity</label>
                                    <span class="text-xs text-gray-500">{{ section.content.overlayOpacity || 50 }}%</span>
                                </div>
                                <input
                                    type="range"
                                    :value="section.content.overlayOpacity || 50"
                                    @input="updateContent('overlayOpacity', parseInt(($event.target as HTMLInputElement).value))"
                                    min="0"
                                    max="90"
                                    step="5"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                />
                                <div class="flex justify-between text-xs text-gray-400 mt-1">
                                    <span>None</span>
                                    <span>Heavy</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Text Position</label>
                        <select
                            :value="section.content.textPosition || 'center'"
                            @change="updateContent('textPosition', ($event.target as HTMLSelectElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        >
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                        <input
                            :value="section.content.buttonText"
                            @input="updateContent('buttonText', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Button Link</label>
                        <input
                            :value="section.content.buttonLink"
                            @input="updateContent('buttonLink', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                            placeholder="#contact"
                        />
                    </div>
                </template>

                <!-- Page Header Section Content -->
                <template v-else-if="section.type === 'page-header'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                            placeholder="About Us"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle (optional)</label>
                        <textarea
                            :value="section.content.subtitle"
                            @input="updateContent('subtitle', ($event.target as HTMLTextAreaElement).value)"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                            placeholder="A brief description of this page"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Background Image</label>
                        <div v-if="section.content.backgroundImage" class="relative mb-2">
                            <img :src="section.content.backgroundImage" class="w-full h-24 object-cover rounded-lg" />
                            <button @click="updateContent('backgroundImage', '')" class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full hover:bg-red-600">
                                <XMarkIcon class="w-4 h-4" aria-hidden="true" />
                            </button>
                        </div>
                        <button
                            @click="emit('openMediaLibrary', section.id, 'backgroundImage')"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-blue-400 hover:text-blue-600 transition-colors"
                        >
                            <PhotoIcon class="w-5 h-5" aria-hidden="true" />
                            <span class="text-sm">{{ section.content.backgroundImage ? 'Change Background' : 'Add Background' }}</span>
                        </button>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Text Position</label>
                        <select
                            :value="section.content.textPosition || 'center'"
                            @change="updateContent('textPosition', ($event.target as HTMLSelectElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        >
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Colors</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Background Color</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        :value="section.content.backgroundColor || '#1e40af'"
                                        @input="updateContent('backgroundColor', ($event.target as HTMLInputElement).value)"
                                        class="w-10 h-10 rounded cursor-pointer"
                                    />
                                    <input
                                        :value="section.content.backgroundColor || '#1e40af'"
                                        @input="updateContent('backgroundColor', ($event.target as HTMLInputElement).value)"
                                        class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-sm font-mono text-gray-900"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Text Color</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        :value="section.content.textColor || '#ffffff'"
                                        @input="updateContent('textColor', ($event.target as HTMLInputElement).value)"
                                        class="w-10 h-10 rounded cursor-pointer"
                                    />
                                    <input
                                        :value="section.content.textColor || '#ffffff'"
                                        @input="updateContent('textColor', ($event.target as HTMLInputElement).value)"
                                        class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-sm font-mono text-gray-900"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- About Section Content -->
                <template v-else-if="section.type === 'about'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            :value="section.content.description"
                            @input="updateContent('description', ($event.target as HTMLTextAreaElement).value)"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                        <div v-if="section.content.image" class="relative mb-2">
                            <img :src="section.content.image" class="w-full h-32 object-cover rounded-lg" />
                            <button @click="updateContent('image', '')" class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full hover:bg-red-600">
                                <XMarkIcon class="w-4 h-4" aria-hidden="true" />
                            </button>
                        </div>
                        <button
                            @click="emit('openMediaLibrary', section.id, 'image')"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-blue-400 hover:text-blue-600 transition-colors"
                        >
                            <PhotoIcon class="w-5 h-5" aria-hidden="true" />
                            <span class="text-sm">{{ section.content.image ? 'Change Image' : 'Add Image' }}</span>
                        </button>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image Position</label>
                        <select
                            :value="section.content.imagePosition || 'right'"
                            @change="updateContent('imagePosition', ($event.target as HTMLSelectElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        >
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                </template>

                <!-- Services/Features Section Content -->
                <template v-else-if="section.type === 'services' || section.type === 'features'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Items</label>
                            <button @click="emit('addItem')" class="text-xs text-blue-600 hover:text-blue-700">+ Add Item</button>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(item, idx) in section.content.items || []" :key="idx" class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-500">Item {{ idx + 1 }}</span>
                                    <button @click="emit('removeItem', idx)" class="p-1 hover:bg-red-100 rounded">
                                        <TrashIcon class="w-3.5 h-3.5 text-red-500" aria-hidden="true" />
                                    </button>
                                </div>
                                
                                <!-- Icon Picker Button -->
                                <div class="mb-2">
                                    <label class="block text-xs text-gray-500 mb-1">Icon</label>
                                    <button
                                        @click="openIconPicker(idx)"
                                        class="w-full flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors"
                                    >
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <Squares2X2Icon v-if="!item.icon" class="w-4 h-4 text-blue-600" aria-hidden="true" />
                                            <span v-else class="text-xs text-blue-600 font-medium">{{ item.icon?.replace('Icon', '').slice(0, 2) }}</span>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ item.icon ? item.icon.replace('Icon', '') : 'Choose Icon' }}</span>
                                    </button>
                                </div>
                                
                                <input v-model="item.title" placeholder="Title" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-2 text-gray-900" />
                                <textarea v-model="item.description" placeholder="Description" rows="2" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900"></textarea>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Testimonials Section Content -->
                <template v-else-if="section.type === 'testimonials'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Testimonials</label>
                            <button @click="emit('addItem')" class="text-xs text-blue-600 hover:text-blue-700">+ Add</button>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(item, idx) in section.content.items || []" :key="idx" class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-500">Testimonial {{ idx + 1 }}</span>
                                    <button @click="emit('removeItem', idx)" class="p-1 hover:bg-red-100 rounded">
                                        <TrashIcon class="w-3.5 h-3.5 text-red-500" aria-hidden="true" />
                                    </button>
                                </div>
                                <input v-model="item.name" placeholder="Name" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-2 text-gray-900" />
                                <input v-model="item.role" placeholder="Role" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-2 text-gray-900" />
                                <textarea v-model="item.text" placeholder="Testimonial" rows="2" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900"></textarea>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Pricing Section Content -->
                <template v-else-if="section.type === 'pricing'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Plans</label>
                            <button @click="emit('addPlan')" class="text-xs text-blue-600 hover:text-blue-700">+ Add Plan</button>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(plan, idx) in section.content.plans || []" :key="idx" class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-500">Plan {{ idx + 1 }}</span>
                                    <button @click="emit('removePlan', idx)" class="p-1 hover:bg-red-100 rounded">
                                        <TrashIcon class="w-3.5 h-3.5 text-red-500" aria-hidden="true" />
                                    </button>
                                </div>
                                <input v-model="plan.name" placeholder="Plan Name" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-2 text-gray-900" />
                                <input v-model="plan.price" placeholder="Price (e.g., K99)" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-2 text-gray-900" />
                                <div class="mt-2">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs text-gray-500">Features</span>
                                        <button @click="emit('addPlanFeature', idx)" class="text-xs text-blue-600">+ Add</button>
                                    </div>
                                    <div v-for="(feature, fIdx) in plan.features || []" :key="fIdx" class="flex items-center gap-1 mb-1">
                                        <input v-model="plan.features[fIdx]" class="flex-1 px-2 py-1 border border-gray-300 rounded text-xs text-gray-900" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>


                <!-- Gallery Section Content -->
                <template v-else-if="section.type === 'gallery'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Images</label>
                            <button @click="emit('openMediaLibrary', section.id, 'images')" class="text-xs text-blue-600 hover:text-blue-700">+ Add Image</button>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div v-for="(img, idx) in section.content.images || []" :key="img.id || idx" class="relative aspect-square">
                                <img :src="img.url" class="w-full h-full object-cover rounded-lg" />
                                <button @click="emit('removeGalleryImage', idx)" class="absolute top-1 right-1 p-0.5 bg-red-500 text-white rounded-full hover:bg-red-600">
                                    <XMarkIcon class="w-3 h-3" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                        <div v-if="!section.content.images?.length" class="text-center py-6 text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
                            <PhotoIcon class="w-8 h-8 mx-auto mb-2" aria-hidden="true" />
                            <p class="text-xs">No images yet</p>
                        </div>
                    </div>
                </template>

                <!-- Contact Section Content -->
                <template v-else-if="section.type === 'contact'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            :value="section.content.description"
                            @input="updateContent('description', ($event.target as HTMLTextAreaElement).value)"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        ></textarea>
                    </div>
                </template>

                <!-- CTA Section Content -->
                <template v-else-if="section.type === 'cta'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            :value="section.content.description"
                            @input="updateContent('description', ($event.target as HTMLTextAreaElement).value)"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                        <input
                            :value="section.content.buttonText"
                            @input="updateContent('buttonText', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                </template>

                <!-- Member CTA Section Content -->
                <template v-else-if="section.type === 'member-cta'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                            placeholder="Join Our Community"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                        <input
                            :value="section.content.subtitle"
                            @input="updateContent('subtitle', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                            placeholder="Become a member and unlock exclusive benefits"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            :value="section.content.description"
                            @input="updateContent('description', ($event.target as HTMLTextAreaElement).value)"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                            placeholder="Get access to premium content..."
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Benefits (one per line)</label>
                        <textarea
                            :value="(section.content.benefits || []).join('\n')"
                            @input="updateContent('benefits', ($event.target as HTMLTextAreaElement).value.split('\n').filter(b => b.trim()))"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                            placeholder="Access to exclusive content&#10;Member-only discounts&#10;Priority support"
                        ></textarea>
                    </div>
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Buttons</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Register Button Text</label>
                                <input
                                    :value="section.content.registerText"
                                    @input="updateContent('registerText', ($event.target as HTMLInputElement).value)"
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900"
                                    placeholder="Sign Up Now"
                                />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Register Button Style</label>
                                <select
                                    :value="section.content.registerButtonStyle || 'solid'"
                                    @change="updateContent('registerButtonStyle', ($event.target as HTMLSelectElement).value)"
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900"
                                >
                                    <option value="solid">Solid (filled)</option>
                                    <option value="outline">Outline</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    :checked="section.content.showLoginLink !== false"
                                    @change="updateContent('showLoginLink', ($event.target as HTMLInputElement).checked)"
                                    id="showLoginLink"
                                    class="rounded border-gray-300 text-blue-600"
                                />
                                <label for="showLoginLink" class="text-sm text-gray-700">Show login link</label>
                            </div>
                            <div v-if="section.content.showLoginLink !== false">
                                <label class="block text-xs text-gray-500 mb-1">Login Link Text</label>
                                <input
                                    :value="section.content.loginText"
                                    @input="updateContent('loginText', ($event.target as HTMLInputElement).value)"
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900"
                                    placeholder="Already a member? Login"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Colors</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Background Color</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        :value="section.content.backgroundColor || '#1e40af'"
                                        @input="updateContent('backgroundColor', ($event.target as HTMLInputElement).value)"
                                        class="w-10 h-10 rounded cursor-pointer"
                                    />
                                    <input
                                        :value="section.content.backgroundColor || '#1e40af'"
                                        @input="updateContent('backgroundColor', ($event.target as HTMLInputElement).value)"
                                        class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-sm font-mono text-gray-900"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Text Color</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        :value="section.content.textColor || '#ffffff'"
                                        @input="updateContent('textColor', ($event.target as HTMLInputElement).value)"
                                        class="w-10 h-10 rounded cursor-pointer"
                                    />
                                    <input
                                        :value="section.content.textColor || '#ffffff'"
                                        @input="updateContent('textColor', ($event.target as HTMLInputElement).value)"
                                        class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-sm font-mono text-gray-900"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Text Section Content -->
                <template v-else-if="section.type === 'text'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                        <RichTextEditor
                            :modelValue="section.content.content || ''"
                            @update:modelValue="updateContent('content', $event)"
                            placeholder="Enter your text content..."
                            :minHeight="150"
                            :maxHeight="400"
                            :darkMode="darkMode"
                        />
                        <p class="text-xs text-gray-500 mt-1">Use the toolbar for formatting or Ctrl+B/I/U for shortcuts</p>
                    </div>
                </template>

                <!-- FAQ Section Content -->
                <template v-else-if="section.type === 'faq'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Questions</label>
                            <button @click="emit('addFaqItem')" class="text-xs text-blue-600 hover:text-blue-700">+ Add Question</button>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(item, idx) in section.content.items || []" :key="idx" class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-500">Q{{ idx + 1 }}</span>
                                    <button @click="emit('removeFaqItem', idx)" class="p-1 hover:bg-red-100 rounded">
                                        <TrashIcon class="w-3.5 h-3.5 text-red-500" aria-hidden="true" />
                                    </button>
                                </div>
                                <input v-model="item.question" placeholder="Question" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-2 text-gray-900" />
                                <textarea v-model="item.answer" placeholder="Answer" rows="2" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900"></textarea>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Team Section Content -->
                <template v-else-if="section.type === 'team'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Team Members</label>
                            <button @click="emit('addTeamMember')" class="text-xs text-blue-600 hover:text-blue-700">+ Add Member</button>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(member, idx) in section.content.items || []" :key="idx" class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-500">Member {{ idx + 1 }}</span>
                                    <button @click="emit('removeTeamMember', idx)" class="p-1 hover:bg-red-100 rounded">
                                        <TrashIcon class="w-3.5 h-3.5 text-red-500" aria-hidden="true" />
                                    </button>
                                </div>
                                <input v-model="member.name" placeholder="Name" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-2 text-gray-900" />
                                <input v-model="member.role" placeholder="Role/Position" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-2 text-gray-900" />
                                <textarea v-model="member.bio" placeholder="Short bio" rows="2" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900"></textarea>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Blog Section Content -->
                <template v-else-if="section.type === 'blog'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Blog Posts</label>
                            <button @click="emit('addBlogPost')" class="text-xs text-blue-600 hover:text-blue-700">+ Add Post</button>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(post, idx) in section.content.posts || []" :key="idx" class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-500">Post {{ idx + 1 }}</span>
                                    <button @click="emit('removeBlogPost', idx)" class="p-1 hover:bg-red-100 rounded">
                                        <TrashIcon class="w-3.5 h-3.5 text-red-500" aria-hidden="true" />
                                    </button>
                                </div>
                                <input v-model="post.title" placeholder="Post Title" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-2 text-gray-900" />
                                <input v-model="post.date" type="date" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-2 text-gray-900" />
                                <textarea v-model="post.excerpt" placeholder="Excerpt" rows="2" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900"></textarea>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Stats Section Content -->
                <template v-else-if="section.type === 'stats'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section Title (optional)</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Stats</label>
                            <button @click="emit('addStatItem')" class="text-xs text-blue-600 hover:text-blue-700">+ Add Stat</button>
                        </div>
                        <div class="space-y-3">
                            <div v-for="(stat, idx) in section.content.items || []" :key="idx" class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-500">Stat {{ idx + 1 }}</span>
                                    <button @click="emit('removeStatItem', idx)" class="p-1 hover:bg-red-100 rounded">
                                        <TrashIcon class="w-3.5 h-3.5 text-red-500" aria-hidden="true" />
                                    </button>
                                </div>
                                <input v-model="stat.value" placeholder="Value (e.g., 500+)" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm mb-2 text-gray-900" />
                                <input v-model="stat.label" placeholder="Label (e.g., Happy Clients)" class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900" />
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Map Section Content -->
                <template v-else-if="section.type === 'map'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Google Maps Embed URL</label>
                        <input
                            :value="section.content.embedUrl"
                            @input="updateContent('embedUrl', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                            placeholder="https://www.google.com/maps/embed?..."
                        />
                        <p class="text-xs text-gray-500 mt-1">Get embed URL from Google Maps → Share → Embed</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input
                            :value="section.content.address"
                            @input="updateContent('address', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            :checked="section.content.showAddress"
                            @change="updateContent('showAddress', ($event.target as HTMLInputElement).checked)"
                            class="rounded border-gray-300"
                        />
                        <label class="text-sm text-gray-700">Show address below map</label>
                    </div>
                </template>

                <!-- Video Section Content -->
                <template v-else-if="section.type === 'video'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                        <input
                            :value="section.content.title"
                            @input="updateContent('title', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            :value="section.content.description"
                            @input="updateContent('description', ($event.target as HTMLTextAreaElement).value)"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
                        <input
                            :value="section.content.videoUrl"
                            @input="updateContent('videoUrl', ($event.target as HTMLInputElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                            placeholder="YouTube or Vimeo embed URL"
                        />
                        <p class="text-xs text-gray-500 mt-1">Use embed URL (e.g., https://www.youtube.com/embed/VIDEO_ID)</p>
                    </div>
                </template>

                <!-- Divider Section Content -->
                <template v-else-if="section.type === 'divider'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Style</label>
                        <select
                            :value="section.content.style || 'line'"
                            @change="updateContent('style', ($event.target as HTMLSelectElement).value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        >
                            <option value="line">Line</option>
                            <option value="dots">Dots</option>
                            <option value="space">Space Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Height (px)</label>
                        <input
                            type="number"
                            :value="section.content.height || 40"
                            @input="updateContent('height', parseInt(($event.target as HTMLInputElement).value) || 40)"
                            min="10"
                            max="200"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900"
                        />
                    </div>
                    <div v-if="section.content.style !== 'space'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                        <input
                            type="color"
                            :value="section.content.color || '#e5e7eb'"
                            @input="updateContent('color', ($event.target as HTMLInputElement).value)"
                            class="w-full h-10 rounded-lg cursor-pointer"
                        />
                    </div>
                </template>
            </div>


            <!-- Style Tab -->
            <div v-if="activeTab === 'style'" class="space-y-4">
                <!-- Section Height -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Section Height</label>
                    <div class="flex items-center gap-3">
                        <input
                            type="range"
                            :value="section.style?.minHeight || 200"
                            @input="updateStyle('minHeight', parseInt(($event.target as HTMLInputElement).value))"
                            min="100"
                            max="800"
                            step="10"
                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                        />
                        <div class="flex items-center gap-1">
                            <input
                                type="number"
                                :value="section.style?.minHeight || 200"
                                @input="updateStyle('minHeight', parseInt(($event.target as HTMLInputElement).value) || 200)"
                                min="100"
                                max="800"
                                class="w-16 px-2 py-1 border border-gray-300 rounded text-sm text-center text-gray-900"
                            />
                            <span class="text-xs text-gray-500">px</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Drag the bottom edge of a section to resize, or use the slider</p>
                </div>

                <!-- Background Type Toggle -->
                <div class="border-t border-gray-200 pt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Background Type</label>
                    <div class="flex gap-2 mb-3">
                        <button
                            @click="updateStyle('backgroundType', 'solid')"
                            :class="[
                                'flex-1 px-3 py-2 rounded-lg border-2 text-sm font-medium transition-colors',
                                (!section.style?.backgroundType || section.style?.backgroundType === 'solid')
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'
                            ]"
                        >
                            Solid
                        </button>
                        <button
                            @click="switchToGradient"
                            :class="[
                                'flex-1 px-3 py-2 rounded-lg border-2 text-sm font-medium transition-colors',
                                section.style?.backgroundType === 'gradient'
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'
                            ]"
                        >
                            Gradient
                        </button>
                    </div>
                </div>

                <!-- Solid Background -->
                <template v-if="!section.style?.backgroundType || section.style?.backgroundType === 'solid'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Background Color</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="color in themeColors"
                                :key="color.value"
                                @click="updateStyle('backgroundColor', color.value)"
                                :class="['w-full aspect-square rounded-lg border-2 transition-all', section.style?.backgroundColor === color.value ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-300']"
                                :style="{ backgroundColor: color.value }"
                                :title="color.name"
                            ></button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Custom Background</label>
                        <input
                            type="color"
                            :value="section.style?.backgroundColor || '#ffffff'"
                            @input="updateStyle('backgroundColor', ($event.target as HTMLInputElement).value)"
                            class="w-full h-10 rounded-lg cursor-pointer"
                        />
                    </div>
                </template>

                <!-- Gradient Background -->
                <template v-else-if="section.style?.backgroundType === 'gradient'">
                    <!-- Gradient Presets -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gradient Presets</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="(preset, idx) in gradientPresets"
                                :key="idx"
                                @click="applyGradientPreset(preset)"
                                class="w-full aspect-square rounded-lg border-2 border-gray-200 hover:border-blue-400 transition-all"
                                :style="{ background: `linear-gradient(${preset.direction === 'to-b' ? '180deg' : preset.direction === 'to-br' ? '135deg' : '90deg'}, ${preset.from}, ${preset.to})` }"
                                :title="preset.name"
                            ></button>
                        </div>
                    </div>
                    
                    <!-- Custom Gradient Colors -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">From Color</label>
                            <input
                                type="color"
                                :value="section.style?.gradientFrom || '#3b82f6'"
                                @input="updateStyle('gradientFrom', ($event.target as HTMLInputElement).value)"
                                class="w-full h-10 rounded-lg cursor-pointer"
                            />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">To Color</label>
                            <input
                                type="color"
                                :value="section.style?.gradientTo || '#8b5cf6'"
                                @input="updateStyle('gradientTo', ($event.target as HTMLInputElement).value)"
                                class="w-full h-10 rounded-lg cursor-pointer"
                            />
                        </div>
                    </div>
                    
                    <!-- Gradient Direction -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Direction</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="dir in gradientDirections"
                                :key="dir.value"
                                @click="updateStyle('gradientDirection', dir.value)"
                                :class="[
                                    'px-2 py-2 text-xs font-medium rounded-lg border-2 transition-colors',
                                    section.style?.gradientDirection === dir.value || (!section.style?.gradientDirection && dir.value === 'to-r')
                                        ? 'border-blue-500 bg-blue-50 text-blue-700'
                                        : 'border-gray-200 text-gray-600 hover:border-gray-300'
                                ]"
                            >
                                {{ dir.label }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Gradient Preview -->
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Preview</label>
                        <div 
                            class="h-12 rounded-lg border border-gray-200"
                            :style="{ background: computedGradient }"
                        ></div>
                    </div>
                </template>

                <div class="border-t border-gray-200 pt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Text Color</label>
                    <input
                        type="color"
                        :value="section.style?.textColor || '#111827'"
                        @input="updateStyle('textColor', ($event.target as HTMLInputElement).value)"
                        class="w-full h-10 rounded-lg cursor-pointer"
                    />
                </div>
            </div>

            <!-- Advanced Tab -->
            <div v-if="activeTab === 'advanced'" class="space-y-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Section ID: <code class="text-xs bg-gray-200 px-1 rounded">{{ section.id }}</code></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CSS Class</label>
                    <input
                        :value="section.style?.cssClass || ''"
                        @input="updateStyle('cssClass', ($event.target as HTMLInputElement).value)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono text-gray-900"
                        placeholder="custom-class"
                    />
                </div>
            </div>
        </div>
    </div>
    
    <!-- Icon Picker Modal -->
    <IconPickerModal
        :show="showIconPicker"
        :currentIcon="iconPickerTarget !== null && section.content.items?.[iconPickerTarget.itemIndex]?.icon"
        @close="showIconPicker = false; iconPickerTarget = null"
        @select="handleIconSelect"
    />
    
    <!-- Video Embed Modal -->
    <VideoEmbedModal
        :show="showVideoEmbed"
        :currentUrl="section.content.videoBackground"
        @close="showVideoEmbed = false"
        @select="handleVideoSelect"
    />
</template>
