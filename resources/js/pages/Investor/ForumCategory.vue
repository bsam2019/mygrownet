<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InvestorLayout from '@/Layouts/InvestorLayout.vue';
import {
    ChatBubbleLeftRightIcon,
    PlusIcon,
    EyeIcon,
    ClockIcon,
    BookmarkIcon,
} from '@heroicons/vue/24/outline';

interface ForumCategory {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    topics_count: number;
}

interface ForumTopic {
    id: number;
    title: string;
    slug: string;
    content: string;
    status: string;
    is_pinned: boolean;
    views_count: number;
    replies_count: number;
    created_at: string;
    last_reply_at: string | null;
    author: {
        user: { name: string };
    };
    category: ForumCategory;
}

interface Investor {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    investor: Investor;
    categories: ForumCategory[];
    topics: { data: ForumTopic[]; links: any; meta: any };
    currentCategoryId: number;
    activePage?: string;
    unreadMessages?: number;
    unreadAnnouncements?: number;
}>();

const showNewTopicModal = ref(false);
const currentCategory = props.categories.find(c => c.id === props.currentCategoryId);

const form = useForm({
    category_id: props.currentCategoryId,
    title: '',
    content: '',
});

const submitTopic = () => {
    form.post(route('investor.forum.topic.create'), {
        onSuccess: () => {
            showNewTopicModal.value = false;
            form.reset('title', 'content');
        },
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <InvestorLayout 
        :investor="investor" 
        :page-title="currentCategory?.name || 'Forum'" 
        :active-page="activePage || 'forum'"
        :unread-messages="unreadMessages"
        :unread-announcements="unreadAnnouncements"
    >
        <Head :title="currentCategory?.name || 'Forum'" />

        <div class="max-w-6xl mx-auto px-4 py-8">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
                <Link :href="route('investor.forum')" class="hover:text-blue-600">Forum</Link>
                <span>/</span>
                <span class="text-gray-900">{{ currentCategory?.name }}</span>
            </nav>

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ currentCategory?.name }}</h1>
                    <p v-if="currentCategory?.description" class="text-gray-600 mt-1">
                        {{ currentCategory.description }}
                    </p>
                </div>
                <button
                    @click="showNewTopicModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    New Topic
                </button>
            </div>

            <!-- Topics List -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div v-if="topics.data.length === 0" class="p-12 text-center">
                    <ChatBubbleLeftRightIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                    <p class="text-gray-500">No topics yet</p>
                    <p class="text-sm text-gray-400 mt-1">Be the first to start a discussion!</p>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <Link
                        v-for="topic in topics.data"
                        :key="topic.id"
                        :href="route('investor.forum.topic', topic.slug)"
                        class="block p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span v-if="topic.is_pinned" class="text-amber-500">
                                        <BookmarkIcon class="h-4 w-4" aria-hidden="true" />
                                    </span>
                                    <h3 class="font-semibold text-gray-900 truncate">{{ topic.title }}</h3>
                                </div>
                                <p class="text-sm text-gray-600 line-clamp-1">{{ topic.content }}</p>
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                    <span>by {{ topic.author?.user?.name || 'Anonymous' }}</span>
                                    <span class="flex items-center gap-1">
                                        <ClockIcon class="h-3 w-3" aria-hidden="true" />
                                        {{ formatDate(topic.created_at) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-6 text-sm text-gray-500">
                                <div class="text-center">
                                    <p class="font-semibold text-gray-900">{{ topic.replies_count }}</p>
                                    <p class="text-xs">replies</p>
                                </div>
                                <div class="text-center">
                                    <p class="font-semibold text-gray-900">{{ topic.views_count }}</p>
                                    <p class="text-xs">views</p>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- New Topic Modal -->
        <Teleport to="body">
            <div v-if="showNewTopicModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50" @click="showNewTopicModal = false"></div>
                    <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                        <h3 class="text-lg font-semibold mb-4">Start New Discussion</h3>
                        
                        <form @submit.prevent="submitTopic" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="w-full border-gray-300 rounded-lg"
                                    placeholder="What would you like to discuss?"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                                <textarea
                                    v-model="form.content"
                                    rows="6"
                                    class="w-full border-gray-300 rounded-lg"
                                    placeholder="Share your thoughts..."
                                    required
                                ></textarea>
                            </div>

                            <p class="text-sm text-gray-500">
                                Your post will be reviewed by moderators before appearing publicly.
                            </p>

                            <div class="flex gap-3 pt-4">
                                <button type="button" @click="showNewTopicModal = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    Submit for Review
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </InvestorLayout>
</template>
