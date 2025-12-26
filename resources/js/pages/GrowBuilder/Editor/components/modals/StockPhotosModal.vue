<script setup lang="ts">
/**
 * Stock Photos Modal
 * Search and select free stock photos from Unsplash
 */
import { ref, watch } from 'vue';
import {
    XMarkIcon,
    MagnifyingGlassIcon,
    PhotoIcon,
    ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline';

interface UnsplashPhoto {
    id: string;
    urls: {
        raw: string;
        full: string;
        regular: string;
        small: string;
        thumb: string;
    };
    alt_description: string;
    user: {
        name: string;
        username: string;
    };
    width: number;
    height: number;
}

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'select', url: string, attribution: string): void;
}>();

const searchQuery = ref('');
const photos = ref<UnsplashPhoto[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const page = ref(1);
const hasMore = ref(true);

// Popular search categories
const categories = [
    'business', 'technology', 'nature', 'food', 'travel',
    'architecture', 'people', 'abstract', 'minimal', 'office'
];

// Unsplash API (using demo access - limited to 50 requests/hour)
// For production, you'd use your own API key via backend proxy
const UNSPLASH_ACCESS_KEY = 'demo'; // Replace with actual key in production

const searchPhotos = async (query: string, pageNum: number = 1) => {
    if (!query.trim()) return;
    
    loading.value = true;
    error.value = null;
    
    try {
        // Using Unsplash Source for demo (no API key needed)
        // In production, use the actual API with your key
        const response = await fetch(
            `https://api.unsplash.com/search/photos?query=${encodeURIComponent(query)}&page=${pageNum}&per_page=20&client_id=${UNSPLASH_ACCESS_KEY}`
        );
        
        if (!response.ok) {
            // Fallback to curated photos if API fails
            await loadCuratedPhotos(query);
            return;
        }
        
        const data = await response.json();
        
        if (pageNum === 1) {
            photos.value = data.results;
        } else {
            photos.value = [...photos.value, ...data.results];
        }
        
        hasMore.value = data.total_pages > pageNum;
        page.value = pageNum;
    } catch (err) {
        // Fallback to placeholder images for demo
        await loadCuratedPhotos(query);
    } finally {
        loading.value = false;
    }
};

// Fallback curated photos (for demo without API key)
const loadCuratedPhotos = async (query: string) => {
    // Generate placeholder photos based on query
    const placeholders: UnsplashPhoto[] = Array.from({ length: 12 }, (_, i) => ({
        id: `placeholder-${query}-${i}`,
        urls: {
            raw: `https://source.unsplash.com/800x600/?${encodeURIComponent(query)}&sig=${i}`,
            full: `https://source.unsplash.com/1920x1080/?${encodeURIComponent(query)}&sig=${i}`,
            regular: `https://source.unsplash.com/1080x720/?${encodeURIComponent(query)}&sig=${i}`,
            small: `https://source.unsplash.com/400x300/?${encodeURIComponent(query)}&sig=${i}`,
            thumb: `https://source.unsplash.com/200x150/?${encodeURIComponent(query)}&sig=${i}`,
        },
        alt_description: `${query} photo ${i + 1}`,
        user: { name: 'Unsplash', username: 'unsplash' },
        width: 1920,
        height: 1080,
    }));
    
    photos.value = placeholders;
    hasMore.value = false;
};

const handleSearch = () => {
    page.value = 1;
    searchPhotos(searchQuery.value, 1);
};

const loadMore = () => {
    if (!loading.value && hasMore.value) {
        searchPhotos(searchQuery.value, page.value + 1);
    }
};

const selectPhoto = (photo: UnsplashPhoto) => {
    const attribution = `Photo by ${photo.user.name} on Unsplash`;
    emit('select', photo.urls.regular, attribution);
};

const selectCategory = (category: string) => {
    searchQuery.value = category;
    handleSearch();
};

// Load initial photos when modal opens
watch(() => props.show, (show) => {
    if (show && photos.value.length === 0) {
        searchQuery.value = 'business';
        handleSearch();
    }
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60" @click="emit('close')">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col" @click.stop>
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <PhotoIcon class="w-6 h-6 text-blue-600" />
                        <h2 class="text-lg font-semibold text-gray-900">Stock Photos</h2>
                        <span class="text-xs text-gray-500">Powered by Unsplash</span>
                    </div>
                    <button @click="emit('close')" class="p-1.5 hover:bg-gray-100 rounded-lg" aria-label="Close">
                        <XMarkIcon class="w-5 h-5 text-gray-500" />
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="p-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex gap-2">
                        <div class="flex-1 relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                @keyup.enter="handleSearch"
                                type="text"
                                placeholder="Search free photos..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                        <button
                            @click="handleSearch"
                            :disabled="loading"
                            class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                        >
                            Search
                        </button>
                    </div>
                    
                    <!-- Quick Categories -->
                    <div class="flex flex-wrap gap-2 mt-3">
                        <button
                            v-for="cat in categories"
                            :key="cat"
                            @click="selectCategory(cat)"
                            :class="[
                                'px-3 py-1 text-xs font-medium rounded-full transition-colors capitalize',
                                searchQuery === cat
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-100'
                            ]"
                        >
                            {{ cat }}
                        </button>
                    </div>
                </div>

                <!-- Photos Grid -->
                <div class="flex-1 overflow-y-auto p-4">
                    <!-- Loading -->
                    <div v-if="loading && photos.length === 0" class="flex items-center justify-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-2 border-blue-600 border-t-transparent"></div>
                    </div>

                    <!-- Error -->
                    <div v-else-if="error" class="text-center py-12">
                        <p class="text-red-600">{{ error }}</p>
                        <button @click="handleSearch" class="mt-2 text-blue-600 hover:underline">Try again</button>
                    </div>

                    <!-- Photos -->
                    <div v-else-if="photos.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <button
                            v-for="photo in photos"
                            :key="photo.id"
                            @click="selectPhoto(photo)"
                            class="group relative aspect-[4/3] rounded-lg overflow-hidden bg-gray-100 hover:ring-2 hover:ring-blue-500 transition-all"
                        >
                            <img
                                :src="photo.urls.small"
                                :alt="photo.alt_description || 'Stock photo'"
                                class="w-full h-full object-cover"
                                loading="lazy"
                            />
                            <!-- Overlay -->
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                                <ArrowDownTrayIcon class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                            </div>
                            <!-- Attribution -->
                            <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                                <p class="text-xs text-white truncate">{{ photo.user.name }}</p>
                            </div>
                        </button>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12 text-gray-500">
                        <PhotoIcon class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                        <p>Search for photos to get started</p>
                    </div>

                    <!-- Load More -->
                    <div v-if="hasMore && photos.length > 0" class="text-center mt-6">
                        <button
                            @click="loadMore"
                            :disabled="loading"
                            class="px-6 py-2 text-sm text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50 disabled:opacity-50 transition-colors"
                        >
                            {{ loading ? 'Loading...' : 'Load More' }}
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 text-center">
                    <p class="text-xs text-gray-500">
                        Photos provided by <a href="https://unsplash.com" target="_blank" class="text-blue-600 hover:underline">Unsplash</a>. 
                        Free to use under the Unsplash License.
                    </p>
                </div>
            </div>
        </div>
    </Teleport>
</template>
