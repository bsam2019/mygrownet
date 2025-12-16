<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    MagnifyingGlassIcon,
    BookOpenIcon,
    ArrowDownTrayIcon,
    PlayIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface KnowledgeItem {
    id: number;
    title: string;
    content: string | null;
    excerpt: string | null;
    category: string | null;
    category_icon: string;
    type: string;
    type_icon: string;
    media_url: string | null;
    formatted_duration: string | null;
    is_featured: boolean;
    created_at: string;
}

interface Category {
    id: string;
    name: string;
    icon: string;
}

const props = defineProps<{
    items: KnowledgeItem[];
    categories: Category[];
    filters: Record<string, any>;
}>();

const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');

const search = () => {
    router.get(route('lifeplus.knowledge.index'), {
        search: searchQuery.value || null,
        category: selectedCategory.value || null,
    }, { preserveState: true });
};

const filterByCategory = (category: string) => {
    selectedCategory.value = category;
    search();
};

const downloadItem = (id: number) => {
    router.post(route('lifeplus.knowledge.download', id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Knowledge Center</h1>
            <Link 
                :href="route('lifeplus.knowledge.downloads')"
                class="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors text-sm"
            >
                <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                Downloads
            </Link>
        </div>

        <!-- Search -->
        <div class="relative">
            <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
            <input 
                v-model="searchQuery"
                @keyup.enter="search"
                type="text"
                placeholder="Search articles, tips..."
                class="w-full pl-12 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
            />
        </div>

        <!-- Categories -->
        <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4">
            <button 
                @click="filterByCategory('')"
                :class="[
                    'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                    !selectedCategory 
                        ? 'bg-teal-500 text-white' 
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
            >
                All
            </button>
            <button 
                v-for="cat in categories" 
                :key="cat.id"
                @click="filterByCategory(cat.id)"
                :class="[
                    'flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                    selectedCategory === cat.id 
                        ? 'bg-teal-500 text-white' 
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
            >
                <span>{{ cat.icon }}</span>
                {{ cat.name }}
            </button>
        </div>

        <!-- Featured Section -->
        <div v-if="!selectedCategory && !searchQuery" class="bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl p-5 text-white">
            <h2 class="font-semibold mb-2">💡 Daily Tips</h2>
            <p class="text-teal-100 text-sm">
                Get practical tips on finance, business, health, and more to improve your daily life.
            </p>
        </div>

        <!-- Items List -->
        <div class="space-y-3">
            <div v-if="items.length === 0" class="text-center py-12">
                <BookOpenIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500">No content found</p>
            </div>

            <div 
                v-for="item in items" 
                :key="item.id"
                class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100"
            >
                <div class="flex items-start gap-3">
                    <div 
                        class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                        :class="item.type === 'audio' ? 'bg-purple-100' : 'bg-teal-100'"
                    >
                        <span v-if="item.type === 'audio'" class="text-2xl">🎧</span>
                        <span v-else class="text-2xl">{{ item.category_icon }}</span>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 capitalize">
                                {{ item.category }}
                            </span>
                            <span v-if="item.type === 'audio'" class="text-xs text-gray-400">
                                {{ item.formatted_duration }}
                            </span>
                        </div>
                        
                        <h3 class="font-semibold text-gray-900">{{ item.title }}</h3>
                        <p v-if="item.excerpt" class="text-sm text-gray-600 mt-1 line-clamp-2">
                            {{ item.excerpt }}
                        </p>
                        
                        <div class="flex items-center gap-2 mt-3">
                            <Link 
                                :href="route('lifeplus.knowledge.show', item.id)"
                                class="px-4 py-1.5 bg-teal-500 text-white rounded-lg text-sm font-medium hover:bg-teal-600 transition-colors"
                            >
                                {{ item.type === 'audio' ? 'Listen' : 'Read' }}
                            </Link>
                            <button 
                                @click="downloadItem(item.id)"
                                class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                                aria-label="Download for offline"
                            >
                                <ArrowDownTrayIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
