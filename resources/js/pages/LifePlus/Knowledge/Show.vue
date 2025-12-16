<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    ArrowDownTrayIcon,
    PlayIcon,
    PauseIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface KnowledgeItem {
    id: number;
    title: string;
    content: string | null;
    category: string | null;
    category_icon: string;
    type: string;
    media_url: string | null;
    duration_seconds: number | null;
    formatted_duration: string | null;
    is_featured: boolean;
    created_at: string;
}

const props = defineProps<{
    item: KnowledgeItem;
}>();

const isPlaying = ref(false);
const audioRef = ref<HTMLAudioElement | null>(null);
const currentTime = ref(0);
const duration = ref(0);

const togglePlay = () => {
    if (!audioRef.value) return;
    
    if (isPlaying.value) {
        audioRef.value.pause();
    } else {
        audioRef.value.play();
    }
    isPlaying.value = !isPlaying.value;
};

const onTimeUpdate = () => {
    if (audioRef.value) {
        currentTime.value = audioRef.value.currentTime;
    }
};

const onLoadedMetadata = () => {
    if (audioRef.value) {
        duration.value = audioRef.value.duration;
    }
};

const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const downloadItem = () => {
    router.post(route('lifeplus.knowledge.download', props.item.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.knowledge.index')"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Back to knowledge center"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900 flex-1 truncate">{{ item.title }}</h1>
            <button 
                @click="downloadItem"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Download for offline"
            >
                <ArrowDownTrayIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </button>
        </div>

        <!-- Category Badge -->
        <div class="flex items-center gap-2">
            <span class="text-2xl">{{ item.category_icon }}</span>
            <span class="px-3 py-1 bg-teal-100 text-teal-700 rounded-full text-sm font-medium capitalize">
                {{ item.category }}
            </span>
        </div>

        <!-- Audio Player (if audio type) -->
        <div v-if="item.type === 'audio' && item.media_url" class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl p-6 text-white">
            <audio 
                ref="audioRef"
                :src="item.media_url"
                @timeupdate="onTimeUpdate"
                @loadedmetadata="onLoadedMetadata"
                @ended="isPlaying = false"
            ></audio>
            
            <div class="flex items-center gap-4">
                <button 
                    @click="togglePlay"
                    class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors"
                    :aria-label="isPlaying ? 'Pause' : 'Play'"
                >
                    <component 
                        :is="isPlaying ? PauseIcon : PlayIcon" 
                        class="h-8 w-8 text-white"
                        aria-hidden="true"
                    />
                </button>
                
                <div class="flex-1">
                    <div class="h-2 bg-white/20 rounded-full overflow-hidden">
                        <div 
                            class="h-full bg-white rounded-full transition-all"
                            :style="{ width: duration ? `${(currentTime / duration) * 100}%` : '0%' }"
                        ></div>
                    </div>
                    <div class="flex justify-between mt-2 text-sm text-white/80">
                        <span>{{ formatTime(currentTime) }}</span>
                        <span>{{ formatTime(duration) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ item.title }}</h2>
            <div 
                v-if="item.content"
                class="prose prose-gray max-w-none text-gray-600"
                v-html="item.content"
            ></div>
            <p v-else class="text-gray-500 italic">No content available</p>
        </div>

        <!-- Metadata -->
        <div class="text-center text-sm text-gray-400">
            {{ item.created_at }}
        </div>
    </div>
</template>
