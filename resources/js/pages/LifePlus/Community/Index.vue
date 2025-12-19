<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import { useLifePlusAccess } from '@/composables/useLifePlusAccess';
import UpgradePrompt from '@/Components/LifePlus/UpgradePrompt.vue';
import {
    PlusIcon,
    MegaphoneIcon,
    CalendarDaysIcon,
    MagnifyingGlassIcon,
    MapPinIcon,
    BriefcaseIcon,
    ClipboardDocumentListIcon,
    LockClosedIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

const { canAccess } = useLifePlusAccess();
const canPost = canAccess('community_post');

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
    <div class="min-h-screen bg-gradient-to-br from-teal-50 via-cyan-50 to-blue-50">
        <!-- Header with Gradient -->
        <div class="bg-gradient-to-r from-teal-500 via-cyan-600 to-blue-600 p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Community</h1>
                    <p class="text-sm text-teal-100 mt-1">Connect with your neighbors 🤝</p>
                </div>
                <button 
                    v-if="canPost"
                    @click="showAddModal = true"
                    class="flex items-center gap-2 px-5 py-2.5 bg-white/20 backdrop-blur-sm text-white rounded-xl font-semibold hover:bg-white/30 transition-all shadow-md"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Post
                </button>
                <div v-else class="flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm text-white/70 rounded-xl text-sm">
                    <LockClosedIcon class="h-4 w-4" aria-hidden="true" />
                    Premium
                </div>
            </div>
        </div>
        
        <div class="p-4 space-y-6">

        <!-- Quick Links -->
        <div class="grid grid-cols-2 gap-3">
            <Link 
                :href="route('lifeplus.gigs.index')"
                class="flex items-center gap-3 p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl hover:from-blue-100 hover:to-blue-200 transition-all shadow-lg hover:shadow-xl hover:scale-105 border border-blue-200"
            >
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md">
                    <BriefcaseIcon class="h-5 w-5 text-white" aria-hidden="true" />
                </div>
                <div>
                    <p class="font-semibold text-blue-900">Gig Finder</p>
                    <p class="text-xs text-blue-600">Find local jobs</p>
                </div>
            </Link>
            
            <Link 
                :href="route('lifeplus.community.events')"
                class="flex items-center gap-3 p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl hover:from-purple-100 hover:to-purple-200 transition-all shadow-lg hover:shadow-xl hover:scale-105 border border-purple-200"
            >
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-md">
                    <CalendarDaysIcon class="h-5 w-5 text-white" aria-hidden="true" />
                </div>
                <div>
                    <p class="font-semibold text-purple-900">Events</p>
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
                    'flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all shadow-md',
                    activeTab === tab.id 
                        ? 'bg-gradient-to-r from-teal-500 to-cyan-600 text-white shadow-lg scale-105' 
                        : 'bg-gradient-to-br from-gray-100 to-gray-200 text-gray-700 hover:from-gray-200 hover:to-gray-300'
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
                class="block bg-gradient-to-br from-white to-gray-50 rounded-2xl p-4 shadow-lg border border-gray-200 hover:shadow-xl hover:scale-[1.02] transition-all"
            >
                <div class="flex items-start gap-3">
                    <span class="text-2xl">{{ post.type_icon }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gradient-to-r from-teal-100 to-cyan-100 text-teal-700 font-medium">
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
    </div>
</template>
