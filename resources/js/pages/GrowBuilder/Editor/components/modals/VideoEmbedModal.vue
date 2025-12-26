<script setup lang="ts">
/**
 * Video Embed Modal
 * Parse and embed YouTube/Vimeo videos for backgrounds
 */
import { ref, computed, watch } from 'vue';
import {
    XMarkIcon,
    PlayIcon,
    LinkIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
} from '@heroicons/vue/24/outline';

interface VideoInfo {
    platform: 'youtube' | 'vimeo' | 'unknown';
    videoId: string;
    embedUrl: string;
    thumbnailUrl: string;
    title?: string;
}

const props = defineProps<{
    show: boolean;
    currentUrl?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'select', videoInfo: VideoInfo): void;
}>();

const videoUrl = ref('');
const videoInfo = ref<VideoInfo | null>(null);
const error = ref<string | null>(null);
const autoplay = ref(true);
const muted = ref(true);
const loop = ref(true);

// Parse video URL
const parseVideoUrl = (url: string): VideoInfo | null => {
    if (!url) return null;
    
    // YouTube patterns
    const youtubePatterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/,
    ];
    
    for (const pattern of youtubePatterns) {
        const match = url.match(pattern);
        if (match) {
            const videoId = match[1];
            return {
                platform: 'youtube',
                videoId,
                embedUrl: `https://www.youtube.com/embed/${videoId}`,
                thumbnailUrl: `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`,
            };
        }
    }
    
    // Vimeo patterns
    const vimeoPatterns = [
        /vimeo\.com\/(\d+)/,
        /player\.vimeo\.com\/video\/(\d+)/,
    ];
    
    for (const pattern of vimeoPatterns) {
        const match = url.match(pattern);
        if (match) {
            const videoId = match[1];
            return {
                platform: 'vimeo',
                videoId,
                embedUrl: `https://player.vimeo.com/video/${videoId}`,
                thumbnailUrl: `https://vumbnail.com/${videoId}.jpg`,
            };
        }
    }
    
    return null;
};

// Build embed URL with options
const finalEmbedUrl = computed(() => {
    if (!videoInfo.value) return '';
    
    const params = new URLSearchParams();
    
    if (videoInfo.value.platform === 'youtube') {
        if (autoplay.value) params.set('autoplay', '1');
        if (muted.value) params.set('mute', '1');
        if (loop.value) params.set('loop', '1');
        params.set('playlist', videoInfo.value.videoId); // Required for loop
        params.set('controls', '0');
        params.set('showinfo', '0');
        params.set('rel', '0');
        params.set('modestbranding', '1');
    } else if (videoInfo.value.platform === 'vimeo') {
        if (autoplay.value) params.set('autoplay', '1');
        if (muted.value) params.set('muted', '1');
        if (loop.value) params.set('loop', '1');
        params.set('background', '1');
        params.set('controls', '0');
    }
    
    return `${videoInfo.value.embedUrl}?${params.toString()}`;
});

// Handle URL input
const handleUrlChange = () => {
    error.value = null;
    const parsed = parseVideoUrl(videoUrl.value);
    
    if (videoUrl.value && !parsed) {
        error.value = 'Could not recognize video URL. Please use a YouTube or Vimeo link.';
        videoInfo.value = null;
    } else {
        videoInfo.value = parsed;
    }
};

// Select video
const selectVideo = () => {
    if (videoInfo.value) {
        emit('select', {
            ...videoInfo.value,
            embedUrl: finalEmbedUrl.value,
        });
    }
};

// Example URLs
const exampleUrls = [
    { label: 'YouTube', url: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' },
    { label: 'Vimeo', url: 'https://vimeo.com/76979871' },
];

// Initialize with current URL
watch(() => props.show, (show) => {
    if (show) {
        videoUrl.value = props.currentUrl || '';
        handleUrlChange();
    }
});

watch(videoUrl, handleUrlChange);
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4" @click="emit('close')">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col" @click.stop>
                <!-- Header -->
                <div class="flex-shrink-0 flex items-center justify-between p-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <PlayIcon class="w-6 h-6 text-blue-600" />
                        <h2 class="text-lg font-semibold text-gray-900">Add Video Background</h2>
                    </div>
                    <button @click="emit('close')" class="p-1.5 hover:bg-gray-100 rounded-lg" aria-label="Close">
                        <XMarkIcon class="w-5 h-5 text-gray-500" />
                    </button>
                </div>

                <!-- Content (scrollable) -->
                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    <!-- URL Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Video URL</label>
                        <div class="relative">
                            <LinkIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" />
                            <input
                                v-model="videoUrl"
                                type="url"
                                placeholder="Paste YouTube or Vimeo URL..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900"
                            />
                        </div>
                        
                        <!-- Error -->
                        <div v-if="error" class="flex items-center gap-2 mt-2 text-red-600">
                            <ExclamationCircleIcon class="w-4 h-4 flex-shrink-0" />
                            <span class="text-sm">{{ error }}</span>
                        </div>
                        
                        <!-- Success -->
                        <div v-else-if="videoInfo" class="flex items-center gap-2 mt-2 text-green-600">
                            <CheckCircleIcon class="w-4 h-4 flex-shrink-0" />
                            <span class="text-sm capitalize">{{ videoInfo.platform }} video detected</span>
                        </div>
                        
                        <!-- Examples -->
                        <p class="text-xs text-gray-500 mt-2">
                            Supported: YouTube, Vimeo
                        </p>
                    </div>

                    <!-- Preview -->
                    <div v-if="videoInfo" class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">Preview</label>
                        <div class="relative aspect-video bg-gray-900 rounded-lg overflow-hidden max-h-48">
                            <img
                                :src="videoInfo.thumbnailUrl"
                                :alt="videoInfo.platform + ' video thumbnail'"
                                class="w-full h-full object-cover"
                                @error="($event.target as HTMLImageElement).src = 'https://via.placeholder.com/640x360?text=Video+Preview'"
                            />
                            <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                                <div class="w-12 h-12 bg-white/90 rounded-full flex items-center justify-center">
                                    <PlayIcon class="w-6 h-6 text-gray-900 ml-0.5" />
                                </div>
                            </div>
                            <!-- Platform Badge -->
                            <div class="absolute top-2 left-2 px-2 py-0.5 bg-black/60 rounded text-white text-xs font-medium capitalize">
                                {{ videoInfo.platform }}
                            </div>
                        </div>
                    </div>

                    <!-- Options -->
                    <div v-if="videoInfo" class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Background Options</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="autoplay" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm text-gray-700">Autoplay</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="muted" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm text-gray-700">Muted</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="loop" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm text-gray-700">Loop</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">
                            For background videos, autoplay + muted + loop is recommended.
                        </p>
                    </div>
                </div>

                <!-- Footer (always visible) -->
                <div class="flex-shrink-0 flex items-center justify-end gap-3 p-4 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                    <button
                        @click="emit('close')"
                        class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="selectVideo"
                        :disabled="!videoInfo"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        Add Video
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
