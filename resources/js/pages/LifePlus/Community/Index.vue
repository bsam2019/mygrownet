<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    PlusIcon,
    MegaphoneIcon,
    CalendarDaysIcon,
    MagnifyingGlassIcon,
    MapPinIcon,
    BriefcaseIcon,
    ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Post {
    id: number;
    type: string;
    type_label: string;
    type_icon: string;
    title: string;
    content: string | null;
    location: string | null;
    event_date: string | null;
    formatted_event_date: string | null;
    author: { id: number; name: string } | null;
    posted_at: string;
}

const props = defineProps<{
    posts: Post[];
    filters: Record<string, any>;
}>();

const activeTab = ref<string>(props.filters.type || 'all');
const showAddModal = ref(false);

const form = useForm({
    type: 'notice',
    title: '',
    content: '',
    location: '',
    event_date: '',
});

const tabs = [
    { id: 'all', label: 'All', component: ClipboardDocumentListIcon },
    { id: 'notice', label: 'Notices', component: MegaphoneIcon },
    { id: 'event', label: 'Events', component: CalendarDaysIcon },
    { id: 'lost_found', label: 'Lost & Found', component: MagnifyingGlassIcon },
];

const filterByType = (type: string) => {
    activeTab.value = type;
    router.get(route('lifeplus.community.index'), { type: type === 'all' ? null : type }, {
        preserveState: true,
    });
};

const submitPost = () => {
    form.post(route('lifeplus.community.posts.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

const filteredPosts = props.posts;
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Community</h1>
            <button 
                @click="showAddModal = true"
                class="flex items-center gap-2 px-4 py-2 bg-teal-500 text-white rounded-xl font-medium hover:bg-teal-600 transition-colors"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Post
            </button>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-2 gap-3">
            <Link 
                :href="route('lifeplus.gigs.index')"
                class="flex items-center gap-3 p-4 bg-blue-50 rounded-2xl hover:bg-blue-100 transition-colors"
            >
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center">
                    <BriefcaseIcon class="h-5 w-5 text-white" aria-hidden="true" />
                </div>
                <div>
                    <p class="font-medium text-blue-900">Gig Finder</p>
                    <p class="text-xs text-blue-600">Find local jobs</p>
                </div>
            </Link>
            
            <Link 
                :href="route('lifeplus.community.events')"
                class="flex items-center gap-3 p-4 bg-purple-50 rounded-2xl hover:bg-purple-100 transition-colors"
            >
                <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center">
                    <CalendarDaysIcon class="h-5 w-5 text-white" aria-hidden="true" />
                </div>
                <div>
                    <p class="font-medium text-purple-900">Events</p>
                    <p class="text-xs text-purple-600">Local happenings</p>
                </div>
            </Link>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4">
            <button 
                v-for="tab in tabs" 
                :key="tab.id"
                @click="filterByType(tab.id)"
                :class="[
                    'flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                    activeTab === tab.id 
                        ? 'bg-teal-500 text-white' 
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
            >
                <component :is="tab.component" class="h-4 w-4" aria-hidden="true" />
                {{ tab.label }}
            </button>
        </div>

        <!-- Posts List -->
        <div class="space-y-3">
            <div v-if="filteredPosts.length === 0" class="text-center py-12">
                <MegaphoneIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500">No posts yet</p>
                <button 
                    @click="showAddModal = true"
                    class="mt-3 text-teal-600 font-medium"
                >
                    Be the first to post
                </button>
            </div>

            <Link 
                v-for="post in filteredPosts" 
                :key="post.id"
                :href="route('lifeplus.community.posts.show', post.id)"
                class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow"
            >
                <div class="flex items-start gap-3">
                    <span class="text-2xl">{{ post.type_icon }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                {{ post.type_label }}
                            </span>
                            <span class="text-xs text-gray-400">{{ post.posted_at }}</span>
                        </div>
                        <h3 class="font-semibold text-gray-900">{{ post.title }}</h3>
                        <p v-if="post.content" class="text-sm text-gray-600 mt-1 line-clamp-2">
                            {{ post.content }}
                        </p>
                        <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                            <span v-if="post.location" class="flex items-center gap-1">
                                <MapPinIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                {{ post.location }}
                            </span>
                            <span v-if="post.formatted_event_date" class="flex items-center gap-1">
                                <CalendarDaysIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                {{ post.formatted_event_date }}
                            </span>
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
                            <h2 class="text-lg font-semibold text-gray-900">Create Post</h2>
                            <button 
                                @click="showAddModal = false"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitPost" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Post Type</label>
                                <select 
                                    v-model="form.type"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                >
                                    <option value="notice">Notice</option>
                                    <option value="event">Event</option>
                                    <option value="lost_found">Lost & Found</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input 
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                    placeholder="What's this about?"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                                <textarea 
                                    v-model="form.content"
                                    rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                    placeholder="Share more details..."
                                ></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location (optional)</label>
                                <input 
                                    v-model="form.location"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                    placeholder="e.g., Chilenje, Lusaka"
                                />
                            </div>
                            
                            <div v-if="form.type === 'event'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Event Date & Time</label>
                                <input 
                                    v-model="form.event_date"
                                    type="datetime-local"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                />
                            </div>
                            
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-teal-500 text-white rounded-xl font-medium hover:bg-teal-600 disabled:opacity-50 transition-colors"
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
