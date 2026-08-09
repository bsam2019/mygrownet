import { ref, readonly } from 'vue';
import { router } from '@inertiajs/vue3';
import type { Video } from '@/types/growstream';

const activeVideo = ref<Video | null>(null);
const isMinimized = ref(false);
const isPlaying = ref(true);
const currentTime = ref(0);

export function useMiniPlayer() {
    const minimizeVideo = (video: Video, currentPos: number = 0) => {
        activeVideo.value = video;
        currentTime.value = currentPos;
        isMinimized.value = true;
        isPlaying.value = true;
    };

    const expandVideo = () => {
        if (activeVideo.value) {
            const slug = activeVideo.value.slug;
            isMinimized.value = false;
            router.visit(route('growstream.video.detail', { slug }));
        }
    };

    const closeMiniPlayer = () => {
        activeVideo.value = null;
        isMinimized.value = false;
        isPlaying.value = false;
        currentTime.value = 0;
    };

    const togglePlay = () => {
        isPlaying.value = !isPlaying.value;
    };

    return {
        activeVideo: readonly(activeVideo),
        isMinimized: readonly(isMinimized),
        isPlaying: readonly(isPlaying),
        currentTime: readonly(currentTime),
        minimizeVideo,
        expandVideo,
        closeMiniPlayer,
        togglePlay,
    };
}
