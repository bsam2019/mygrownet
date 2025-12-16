<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    MagnifyingGlassCircleIcon,
    MapPinIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Post {
    id: number;
    title: string;
    content: string | null;
    excerpt: string | null;
    location: string | null;
    image_url: string | null;
    poster: { id: number; name: string } | null;
    posted_at: string;
}

const props = defineProps<{
    posts: Post[];
}>();

const showAddModal = ref(false);

const form = useForm({
    type: 'lost_found',
    title: '',
    content: '',
    location: '',
});

const submitPost = () => {
    form.post(route('lifeplus.community.posts.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
            form.type = 'lost_found';
        },
    });
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.community.index')"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Back to community"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900 flex-1">Lost & Found</h1>
            <button 
                @click="showAddModal = true"
                class="flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600 transition-colors"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Post
            </button>
        </div>

        <!-- Info Banner -->
        <div class="bg-amber-50 rounded-2xl p-4 border border-amber-200">
            <p class="text-sm text-amber-800">
                🔍 Help your community! Post about lost items or things you've found.
            </p>
        </div>

        <!-- Posts List -->
        <div class="space-y-3">
            <div v-if="posts.length === 0" class="text-center py-12">
                <MagnifyingGlassCircleIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500">No lost & found posts</p>
                <button 
                    @click="showAddModal = true"
                    class="mt-3 text-amber-600 font-medium"
                >
                    Post something
                </button>
            </div>

            <Link 
                v-for="post in posts" 
                :key="post.id"
                :href="route('lifeplus.community.posts.show', post.id)"
                class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow"
            >
                <div class="flex items-start gap-3">
                    <div 
                        v-if="post.image_url"
                        class="w-16 h-16 rounded-xl bg-gray-100 overflow-hidden flex-shrink-0"
                    >
                        <img :src="post.image_url" :alt="post.title" class="w-full h-full object-cover" />
                    </div>
                    <div 
                        v-else
                        class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0"
                    >
                        <MagnifyingGlassCircleIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900">{{ post.title }}</h3>
                        <p v-if="post.excerpt" class="text-sm text-gray-600 mt-1 line-clamp-2">
                            {{ post.excerpt }}
                        </p>
                        <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                            <span v-if="post.location" class="flex items-center gap-1">
                                <MapPinIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                {{ post.location }}
                            </span>
                            <span>{{ post.posted_at }}</span>
                        </div>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Add Post Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAddModal" class="fixed inset-0 z-50 bg-black/50 flex items-end justify-center">
                    <div 
                        class="bg-white w-full max-w-lg rounded-t-3xl p-6 safe-area-bottom max-h-[90vh] overflow-y-auto"
                        @click.stop
                    >
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900">Lost or Found?</h2>
                            <button 
                                @click="showAddModal = false"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitPost" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input 
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    placeholder="e.g., Lost: Black wallet near market"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea 
                                    v-model="form.content"
                                    rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    placeholder="Describe the item and any identifying features..."
                                ></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <input 
                                    v-model="form.location"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    placeholder="Where was it lost/found?"
                                />
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600 disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Posting...' : 'Post' }}
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
