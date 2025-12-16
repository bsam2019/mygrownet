<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    ArrowDownTrayIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Download {
    id: number;
    knowledge_item: {
        id: number;
        title: string;
        category: string | null;
        category_icon: string;
        type: string;
        formatted_duration: string | null;
    };
    downloaded_at: string;
}

const props = defineProps<{
    downloads: Download[];
}>();
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
            <h1 class="text-xl font-bold text-gray-900">Downloads</h1>
        </div>

        <!-- Info Banner -->
        <div class="bg-teal-50 rounded-2xl p-4 border border-teal-200">
            <div class="flex items-start gap-3">
                <ArrowDownTrayIcon class="h-5 w-5 text-teal-600 mt-0.5" aria-hidden="true" />
                <div>
                    <h3 class="font-medium text-teal-800">Offline Content</h3>
                    <p class="text-sm text-teal-600 mt-1">
                        Downloaded content is available even without internet connection.
                    </p>
                </div>
            </div>
        </div>

        <!-- Downloads List -->
        <div class="space-y-3">
            <div v-if="downloads.length === 0" class="text-center py-12">
                <ArrowDownTrayIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500">No downloads yet</p>
                <Link 
                    :href="route('lifeplus.knowledge.index')"
                    class="mt-3 inline-block text-teal-600 font-medium"
                >
                    Browse content
                </Link>
            </div>

            <Link 
                v-for="download in downloads" 
                :key="download.id"
                :href="route('lifeplus.knowledge.show', download.knowledge_item.id)"
                class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow"
            >
                <div class="flex items-start gap-3">
                    <div 
                        class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                        :class="download.knowledge_item.type === 'audio' ? 'bg-purple-100' : 'bg-teal-100'"
                    >
                        <span v-if="download.knowledge_item.type === 'audio'" class="text-2xl">🎧</span>
                        <span v-else class="text-2xl">{{ download.knowledge_item.category_icon }}</span>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 capitalize">
                                {{ download.knowledge_item.category }}
                            </span>
                            <span v-if="download.knowledge_item.type === 'audio'" class="text-xs text-gray-400">
                                {{ download.knowledge_item.formatted_duration }}
                            </span>
                        </div>
                        
                        <h3 class="font-semibold text-gray-900">{{ download.knowledge_item.title }}</h3>
                        <p class="text-xs text-gray-400 mt-1">Downloaded {{ download.downloaded_at }}</p>
                    </div>
                </div>
            </Link>
        </div>
    </div>
</template>
