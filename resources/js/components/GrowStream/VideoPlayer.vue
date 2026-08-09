<template>
    <div class="relative w-full">
        <!-- Cloudflare Stream Player (iframe) -->
        <div v-if="!error" class="relative aspect-video w-full overflow-hidden rounded-lg bg-black">
            <iframe
                ref="iframeRef"
                :src="streamPlayerUrl"
                style="border: none; position: absolute; top: 0; left: 0; height: 100%; width: 100%;"
                allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
                allowfullscreen="true"
                @load="onPlayerLoad"
            ></iframe>
            
            <!-- Throttle indicator -->
            <div v-if="throttled" class="absolute top-3 left-3 z-10 flex items-center gap-2 rounded-full bg-amber-500/90 px-3 py-1 text-xs font-semibold text-white backdrop-blur">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Standard Quality
            </div>
            
            <!-- Loading Spinner -->
            <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-black/50">
                <div class="h-12 w-12 animate-spin rounded-full border-4 border-white border-t-transparent"></div>
            </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="rounded-[var(--gs-radius)] border border-red-500/40 bg-red-500/10 p-4">
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
                    <p class="text-sm text-red-200">{{ error }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import type { Video } from '@/types/growstream';

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

const loading = ref(true);
const error = ref<string | null>(null);

// Build Cloudflare Stream Player URL
const streamPlayerUrl = computed(() => {
    if (!props.video.provider_video_id) {
        error.value = 'Video ID not found';
        return '';
    }

    // Extract the video ID (remove any query parameters if present)
    const videoId = props.video.provider_video_id.split('?')[0];
    
    // Cloudflare Stream iframe player URL
    const params = new URLSearchParams({
        autoplay: props.autoplay ? 'true' : 'false',
        muted: props.autoplay ? 'true' : 'false', // Autoplay requires muted
        preload: 'auto',
        poster: props.video.thumbnail_url || '',
    });

    if (props.startPosition > 0) {
        params.set('startTime', Math.floor(props.startPosition).toString());
    }

    if (props.throttled) {
        params.set('defaultTextTrack', 'off');
    }

    // Use Cloudflare Stream public embed URL
    return `https://watch.cloudflarestream.com/${videoId}?${params.toString()}`;
});

const iframeRef = ref<HTMLIFrameElement | null>(null);
const isPlaying = ref(false);
const isMuted = ref(false);
const currentTime = ref(0);
const duration = ref(0);

const sendPlayerCommand = (method: string, value?: any) => {
    if (iframeRef.value?.contentWindow) {
        iframeRef.value.contentWindow.postMessage(JSON.stringify({
            method,
            value
        }), '*');
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    // Ignore if typing in input fields
    const target = e.target as HTMLElement;
    if (target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable)) {
        return;
    }

    if (e.code === 'Space') {
        e.preventDefault();
        sendPlayerCommand(isPlaying.value ? 'pause' : 'play');
    } else if (e.code === 'ArrowLeft') {
        e.preventDefault();
        sendPlayerCommand('setCurrentTime', Math.max(0, currentTime.value - 10));
    } else if (e.code === 'ArrowRight') {
        e.preventDefault();
        sendPlayerCommand('setCurrentTime', Math.min(duration.value, currentTime.value + 10));
    } else if (e.code === 'KeyM') {
        e.preventDefault();
        isMuted.value = !isMuted.value;
        sendPlayerCommand('setMuted', isMuted.value);
    } else if (e.code === 'KeyF') {
        e.preventDefault();
        if (iframeRef.value) {
            if (document.fullscreenElement) {
                document.exitFullscreen().catch(() => {});
            } else {
                iframeRef.value.requestFullscreen().catch(() => {});
            }
        }
    }
};

const onPlayerLoad = () => {
    loading.value = false;
    window.addEventListener('message', handlePlayerMessage);
    window.addEventListener('keydown', handleKeydown);
};

const handlePlayerMessage = (event: MessageEvent) => {
    if (event.data && typeof event.data === 'object') {
        const { event: eventType, ...data } = event.data;

        switch (eventType) {
            case 'play':
                isPlaying.value = true;
                break;
            case 'pause':
                isPlaying.value = false;
                break;
            case 'ended':
                isPlaying.value = false;
                emit('ended');
                break;
            case 'timeupdate':
                if (data.currentTime !== undefined && data.duration !== undefined) {
                    currentTime.value = data.currentTime;
                    duration.value = data.duration;
                    emit('progress', data.currentTime, data.duration);
                }
                break;
            case 'error':
                error.value = data.message || 'Video player error';
                loading.value = false;
                break;
        }
    }
};

onMounted(() => {
    if (!props.video.provider_video_id) {
        error.value = 'Video ID is missing';
        loading.value = false;
    }
});

onUnmounted(() => {
    window.removeEventListener('message', handlePlayerMessage);
    window.removeEventListener('keydown', handleKeydown);
});
</script>
