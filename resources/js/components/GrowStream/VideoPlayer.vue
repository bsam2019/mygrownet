<template>
    <div class="relative w-full">
        <!-- Cloudflare Stream Player (iframe) -->
        <div v-if="!error" class="relative aspect-video w-full overflow-hidden rounded-lg bg-black">
            <iframe
                :src="streamPlayerUrl"
                style="border: none; position: absolute; top: 0; left: 0; height: 100%; width: 100%;"
                allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
                allowfullscreen="true"
                @load="onPlayerLoad"
            ></iframe>
            
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

    // Use the account's customer subdomain or default Cloudflare domain
    const accountId = '1d1529172d2e0cd6300114cc1a7ab167'; // From config
    const domain = `customer-${accountId}.cloudflarestream.com`;

    return `https://${domain}/${videoId}/iframe?${params.toString()}`;
});

const onPlayerLoad = () => {
    loading.value = false;
    
    // Listen for messages from the Cloudflare Stream player
    window.addEventListener('message', handlePlayerMessage);
};

const handlePlayerMessage = (event: MessageEvent) => {
    // Cloudflare Stream player sends events via postMessage
    if (event.data && typeof event.data === 'object') {
        const { event: eventType, ...data } = event.data;

        switch (eventType) {
            case 'play':
                // Video started playing
                break;
            case 'pause':
                // Video paused
                break;
            case 'ended':
                emit('ended');
                break;
            case 'timeupdate':
                if (data.currentTime && data.duration) {
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
</script>

            <!-- Throttle indicator -->
            <div v-if="throttled" class="absolute top-3 left-3 z-10 flex items-center gap-2 rounded-full bg-amber-500/90 px-3 py-1 text-xs font-semibold text-white backdrop-blur">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Standard Quality
            </div>

            <!--[REMOVED CUSTOM CONTROLS - Using Cloudflare Stream Player built-in controls]-->
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

    // Use the account's customer subdomain or default Cloudflare domain
    const accountId = '1d1529172d2e0cd6300114cc1a7ab167'; // From config
    const domain = `customer-${accountId}.cloudflarestream.com`;

    return `https://${domain}/${videoId}/iframe?${params.toString()}`;
});

const onPlayerLoad = () => {
    loading.value = false;
    
    // Listen for messages from the Cloudflare Stream player
    window.addEventListener('message', handlePlayerMessage);
};

const handlePlayerMessage = (event: MessageEvent) => {
    // Cloudflare Stream player sends events via postMessage
    if (event.data && typeof event.data === 'object') {
        const { event: eventType, ...data } = event.data;

        switch (eventType) {
            case 'play':
                // Video started playing
                break;
            case 'pause':
                // Video paused
                break;
            case 'ended':
                emit('ended');
                break;
            case 'timeupdate':
                if (data.currentTime && data.duration) {
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
</script>
