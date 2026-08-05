<template>
    <div class="relative w-full">
        <!-- Video Container -->
        <div ref="playerContainer" class="relative aspect-video w-full overflow-hidden rounded-lg bg-black">
            <video
                ref="videoElement"
                class="h-full w-full"
                :poster="video.poster_url || video.thumbnail_url"
                @loadedmetadata="onLoadedMetadata"
                @timeupdate="onTimeUpdate"
                @ended="onEnded"
                @play="onPlay"
                @pause="onPause"
            >
                <source :src="playbackUrl" type="video/mp4" />
                Your browser does not support the video tag.
            </video>

            <!-- Throttle indicator -->
            <div v-if="throttled" class="absolute top-3 left-3 z-10 flex items-center gap-2 rounded-full bg-amber-500/90 px-3 py-1 text-xs font-semibold text-white backdrop-blur">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Standard Quality
            </div>

            <!-- Custom Controls Overlay -->
            <div
                v-show="showControls || !isPlaying"
                class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/40"
                @mousemove="handleMouseMove"
            >
                <!-- Top Bar -->
                <div class="absolute left-0 right-0 top-0 flex items-center justify-between p-4">
                    <h3 class="text-lg font-semibold text-white">{{ video.title }}</h3>
                    <button
                        @click="$emit('close')"
                        class="rounded-full p-2 text-white hover:bg-white/20"
                        aria-label="Close player"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Center Play Button -->
                <div v-if="!isPlaying" class="absolute inset-0 flex items-center justify-center">
                    <button
                        @click="togglePlay"
                        class="rounded-full bg-[var(--gs-primary)]/90 p-6 transition-transform hover:scale-110"
                        aria-label="Play video"
                    >
                        <svg class="h-12 w-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                    </button>
                </div>

                <!-- Bottom Controls -->
                <div class="absolute bottom-0 left-0 right-0 p-4">
                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <input
                            v-model="currentTime"
                            type="range"
                            min="0"
                            :max="duration"
                            step="0.1"
                            class="h-1 w-full cursor-pointer appearance-none rounded-full bg-white/30"
                            aria-label="Seek video"
                            :aria-valuetext="`${formatTime(currentTime)} of ${formatTime(duration)}`"
                            @input="seek"
                        />
                    </div>

                    <!-- Control Buttons -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <!-- Play/Pause -->
                            <button
                                @click="togglePlay"
                                class="text-white hover:text-[var(--gs-accent)]"
                                :aria-label="isPlaying ? 'Pause' : 'Play'"
                            >
                                <svg v-if="isPlaying" class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                                </svg>
                                <svg v-else class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </button>

                            <!-- Volume -->
                            <button
                                @click="toggleMute"
                                class="text-white hover:text-[var(--gs-accent)]"
                                :aria-label="isMuted ? 'Unmute' : 'Mute'"
                            >
                                <svg v-if="isMuted" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"
                                    />
                                </svg>
                                <svg v-else class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"
                                    />
                                </svg>
                            </button>

                            <!-- Time Display -->
                            <span class="text-sm font-medium text-white">
                                {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Settings -->
                            <button class="text-white hover:text-[var(--gs-accent)]" aria-label="Settings">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                            </button>

                            <!-- Fullscreen -->
                            <button
                                @click="toggleFullscreen"
                                class="text-white hover:text-[var(--gs-accent)]"
                                :aria-label="isFullscreen ? 'Exit fullscreen' : 'Enter fullscreen'"
                            >
                                <svg v-if="isFullscreen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                                <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-black/50">
                <div class="h-12 w-12 animate-spin rounded-full border-4 border-white border-t-transparent"></div>
            </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mt-4 rounded-[var(--gs-radius)] border border-red-500/40 bg-red-500/10 p-4">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                </svg>
                <div class="flex-1">
                    <p class="font-medium text-red-300">Error loading video</p>
                    <p class="text-sm">{{ error }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button class="gs-btn gs-btn-primary" :disabled="retrying" @click="retryLoad">
                            {{ retrying ? 'Retrying...' : 'Try Again' }}
                        </button>
                        <button
                            v-if="retryCount > 0"
                            class="gs-btn gs-btn-outline"
                            @click="retryLoad(true)"
                        >
                            Retry at lower quality
                        </button>
                        <button v-if="video.playback_url && !usingLowerQuality" class="gs-btn gs-btn-outline" @click="playFromDirectUrl">
                            Try direct source
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';
import type { Video } from '@/types/growstream';
import { useGrowStream } from '@/composables/useGrowStream';
import { useDataSaver } from '@/composables/useDataSaver';
import { useGrowStreamMetrics } from '@/composables/useGrowStreamMetrics';

interface Props {
    video: Video;
    autoplay?: boolean;
    startPosition?: number;
    throttled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    autoplay: false,
    startPosition: 0,
    throttled: false,
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'progress', position: number, duration: number): void;
    (e: 'ended'): void;
}>();

const { authorizePlayback, updateWatchProgress } = useGrowStream();
const { dataSaver } = useDataSaver();
const metrics = useGrowStreamMetrics();

const videoElement = ref<HTMLVideoElement>();
const playerContainer = ref<HTMLDivElement>();
const playbackUrl = ref('');
const loading = ref(true);
const error = ref<string | null>(null);
const isPlaying = ref(false);
const isMuted = ref(false);
const isFullscreen = ref(false);
const showControls = ref(true);
const currentTime = ref(0);
const duration = ref(0);
const watchStartTime = ref(0);
const retrying = ref(false);
const retryCount = ref(0);
const usingLowerQuality = ref(false);

let controlsTimeout: ReturnType<typeof setTimeout>;
let progressUpdateInterval: ReturnType<typeof setInterval>;

onMounted(async () => {
    if (props.throttled) {
        usingLowerQuality.value = true;
    }
    await loadVideo();
    if (props.startPosition > 0 && videoElement.value) {
        videoElement.value.currentTime = props.startPosition;
    }
});

onUnmounted(() => {
    clearTimeout(controlsTimeout);
    clearInterval(progressUpdateInterval);
    saveProgress();
});

const loadVideo = async () => {
    try {
        loading.value = true;
        error.value = null;
        const response = await authorizePlayback(props.video.id);
        playbackUrl.value = response.data.signed_url;

        if (dataSaver.value && videoElement.value) {
            // Prefer lower resolution in data-saver mode when multiple sources are available
            const sources = videoElement.value.querySelectorAll('source');
            if (sources.length > 1) {
                sources.forEach((source) => source.setAttribute('data-lq', '1'));
            }
        }

        if (props.autoplay && !dataSaver.value && videoElement.value) {
            await videoElement.value.play();
        }
    } catch (err: any) {
        error.value = err.message || 'Failed to load video';
        metrics.trackPlaybackFailure(props.video.id, { message: err.message });
    } finally {
        loading.value = false;
    }
};

const retryLoad = async (forceLowerQuality = false) => {
    retrying.value = true;
    retryCount.value += 1;
    metrics.trackPlaybackRetry(props.video.id);
    if (forceLowerQuality) {
        usingLowerQuality.value = true;
    }
    try {
        const video = videoElement.value;
        if (video) {
            video.removeAttribute('src');
            video.load();
        }
        // Re-auth to get a fresh signed URL, then swap source
        const response = await authorizePlayback(props.video.id);
        playbackUrl.value = response.data.signed_url;
        error.value = null;
        await loadVideo();
    } catch (err: any) {
        error.value = err.message || 'Retry failed';
    } finally {
        retrying.value = false;
    }
};

const playFromDirectUrl = () => {
    usingLowerQuality.value = true;
    error.value = null;
    if (props.video.playback_url) {
        playbackUrl.value = props.video.playback_url;
        if (videoElement.value) {
            videoElement.value.load();
            videoElement.value.play().catch(() => {
                error.value = 'Could not play the direct source.';
            });
        }
    }
};

const togglePlay = async () => {
    if (!videoElement.value) return;

    if (isPlaying.value) {
        videoElement.value.pause();
    } else {
        await videoElement.value.play();
    }
};

const toggleMute = () => {
    if (!videoElement.value) return;
    videoElement.value.muted = !videoElement.value.muted;
    isMuted.value = videoElement.value.muted;
};

const toggleFullscreen = async () => {
    if (!playerContainer.value) return;

    if (!isFullscreen.value) {
        await playerContainer.value.requestFullscreen();
    } else {
        await document.exitFullscreen();
    }
    isFullscreen.value = !isFullscreen.value;
};

const seek = () => {
    if (!videoElement.value) return;
    videoElement.value.currentTime = currentTime.value;
};

const onLoadedMetadata = () => {
    if (!videoElement.value) return;
    duration.value = videoElement.value.duration;
};

const onTimeUpdate = () => {
    if (!videoElement.value) return;
    currentTime.value = videoElement.value.currentTime;
    
    // Emit progress every 10 seconds
    if (Math.floor(currentTime.value) % 10 === 0) {
        emit('progress', currentTime.value, duration.value);
    }
};

const onPlay = () => {
    isPlaying.value = true;
    watchStartTime.value = Date.now();
    metrics.trackWatchStart(props.video.id);

    // Start progress update interval
    progressUpdateInterval = setInterval(() => {
        saveProgress();
    }, 10000); // Save every 10 seconds
};

const onPause = () => {
    isPlaying.value = false;
    saveProgress();
    clearInterval(progressUpdateInterval);
};

const onEnded = () => {
    isPlaying.value = false;
    saveProgress();
    metrics.trackWatchEnded(props.video.id, { watchedSeconds: Math.floor(currentTime.value) });
    emit('ended');
};

const saveProgress = async () => {
    if (!videoElement.value || currentTime.value === 0) return;
    
    const watchDuration = Math.floor((Date.now() - watchStartTime.value) / 1000);
    
    try {
        await updateWatchProgress(
            props.video.id,
            Math.floor(currentTime.value),
            watchDuration
        );
    } catch (err) {
        console.error('Failed to save progress:', err);
    }
};

const handleMouseMove = () => {
    showControls.value = true;
    clearTimeout(controlsTimeout);
    
    if (isPlaying.value) {
        controlsTimeout = setTimeout(() => {
            showControls.value = false;
        }, 3000);
    }
};

const formatTime = (seconds: number): string => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);

    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    return `${minutes}:${secs.toString().padStart(2, '0')}`;
};
</script>

<style scoped>
input[type='range']::-webkit-slider-thumb {
    appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--gs-primary);
    cursor: pointer;
}

input[type='range']::-moz-range-thumb {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--gs-primary);
    cursor: pointer;
    border: none;
}
</style>
