<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { PlayIcon } from '@heroicons/vue/24/solid';

interface Props {
    content: {
        layout?: string;
        videoUrl?: string;
        posterImage?: string;
        autoPlay?: boolean;
        muted?: boolean;
        loop?: boolean;
        title?: string;
        subtitle?: string;
        buttonText?: string;
        buttonLink?: string;
    };
    style?: {
        overlay?: boolean;
        minHeight?: number;
    };
}

const props = withDefaults(defineProps<Props>(), {
    content: () => ({
        layout: 'fullscreen',
        videoUrl: '',
        posterImage: '',
        autoPlay: false,
        muted: true,
        loop: true,
        title: 'Watch Our Story',
        subtitle: 'Discover how we transform businesses',
        buttonText: 'Learn More',
        buttonLink: '#about',
    }),
    style: () => ({
        overlay: true,
        minHeight: 600,
    }),
});

const isPlaying = ref(false);
const videoRef = ref<HTMLVideoElement | null>(null);

const minHeightStyle = computed(() => ({
    minHeight: `${props.style?.minHeight || 600}px`,
}));

const isYouTube = computed(() => {
    return props.content.videoUrl?.includes('youtube.com') || props.content.videoUrl?.includes('youtu.be');
});

const isVimeo = computed(() => {
    return props.content.videoUrl?.includes('vimeo.com');
});

const embedUrl = computed(() => {
    if (!props.content.videoUrl) return '';
    
    if (isYouTube.value) {
        const videoId = extractYouTubeId(props.content.videoUrl);
        return `https://www.youtube.com/embed/${videoId}?autoplay=${props.content.autoPlay ? 1 : 0}&mute=${props.content.muted ? 1 : 0}&loop=${props.content.loop ? 1 : 0}&controls=0&showinfo=0&rel=0`;
    }
    
    if (isVimeo.value) {
        const videoId = extractVimeoId(props.content.videoUrl);
        return `https://player.vimeo.com/video/${videoId}?autoplay=${props.content.autoPlay ? 1 : 0}&muted=${props.content.muted ? 1 : 0}&loop=${props.content.loop ? 1 : 0}&controls=0`;
    }
    
    return props.content.videoUrl;
});

function extractYouTubeId(url: string): string {
    const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
    return match ? match[1] : '';
}

function extractVimeoId(url: string): string {
    const match = url.match(/vimeo\.com\/(\d+)/);
    return match ? match[1] : '';
}

function togglePlay() {
    if (videoRef.value) {
        if (isPlaying.value) {
            videoRef.value.pause();
        } else {
            videoRef.value.play();
        }
        isPlaying.value = !isPlaying.value;
    }
}

onMounted(() => {
    if (props.content.autoPlay && videoRef.value) {
        videoRef.value.play();
        isPlaying.value = true;
    }
});
</script>

<template>
    <section 
        class="relative overflow-hidden"
        :style="minHeightStyle"
        data-aos="fade-in"
    >
        <!-- Video Background -->
        <div class="absolute inset-0">
            <!-- YouTube/Vimeo Embed -->
            <iframe
                v-if="isYouTube || isVimeo"
                :src="embedUrl"
                class="absolute inset-0 w-full h-full object-cover"
                frameborder="0"
                allow="autoplay; fullscreen"
                allowfullscreen
            ></iframe>

            <!-- Direct Video -->
            <video
                v-else-if="content.videoUrl"
                ref="videoRef"
                :poster="content.posterImage"
                :muted="content.muted"
                :loop="content.loop"
                class="absolute inset-0 w-full h-full object-cover"
                playsinline
            >
                <source :src="content.videoUrl" type="video/mp4" />
                Your browser does not support the video tag.
            </video>

            <!-- Poster Image Fallback -->
            <img
                v-else-if="content.posterImage"
                :src="content.posterImage"
                alt="Video poster"
                class="absolute inset-0 w-full h-full object-cover"
            />

            <!-- Placeholder -->
            <div
                v-else
                class="absolute inset-0 bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center"
            >
                <div class="text-center text-white">
                    <PlayIcon class="h-24 w-24 mx-auto mb-4 opacity-50" aria-hidden="true" />
                    <p class="text-lg opacity-75">Add a video URL</p>
                </div>
            </div>

            <!-- Overlay -->
            <div
                v-if="style?.overlay && content.layout !== 'fullscreen'"
                class="absolute inset-0 bg-black/50"
            ></div>
        </div>

        <!-- Content Overlay (for overlay and split layouts) -->
        <div
            v-if="content.layout !== 'fullscreen'"
            class="relative z-10 h-full flex items-center"
        >
            <div class="max-w-7xl mx-auto px-4 w-full">
                <div
                    :class="[
                        content.layout === 'split' 
                            ? 'grid grid-cols-1 md:grid-cols-2 gap-12 items-center'
                            : 'max-w-3xl mx-auto text-center'
                    ]"
                >
                    <div 
                        class="text-white"
                        :class="{ 'md:col-start-2': content.layout === 'split' }"
                        data-aos="fade-up"
                    >
                        <h1 
                            v-if="content.title"
                            class="text-4xl md:text-6xl font-bold mb-6"
                        >
                            {{ content.title }}
                        </h1>
                        <p 
                            v-if="content.subtitle"
                            class="text-xl md:text-2xl mb-8 opacity-90"
                        >
                            {{ content.subtitle }}
                        </p>
                        <div class="flex gap-4" :class="{ 'justify-center': content.layout === 'overlay' }">
                            <a
                                v-if="content.buttonText"
                                :href="content.buttonLink"
                                class="inline-block px-8 py-4 bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg"
                            >
                                {{ content.buttonText }}
                            </a>
                            <button
                                v-if="!isYouTube && !isVimeo && content.videoUrl"
                                @click="togglePlay"
                                class="inline-flex items-center gap-2 px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition-all"
                            >
                                <PlayIcon class="h-5 w-5" aria-hidden="true" />
                                {{ isPlaying ? 'Pause' : 'Play' }} Video
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Play Button (for fullscreen without autoplay) -->
        <button
            v-if="content.layout === 'fullscreen' && !content.autoPlay && !isYouTube && !isVimeo && content.videoUrl"
            @click="togglePlay"
            class="absolute inset-0 z-10 flex items-center justify-center group"
        >
            <div class="w-24 h-24 rounded-full bg-white/90 flex items-center justify-center group-hover:bg-white group-hover:scale-110 transition-all shadow-2xl">
                <PlayIcon class="h-12 w-12 text-gray-900 ml-1" aria-hidden="true" />
            </div>
        </button>
    </section>
</template>
