<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InvestorLayout from '@/Layouts/InvestorLayout.vue';
import {
    ChatBubbleLeftRightIcon,
    FolderIcon,
    PlusIcon,
    ChatBubbleOvalLeftIcon,
    EyeIcon,
} from '@heroicons/vue/24/outline';

interface ForumCategory {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    icon: string | null;
    topics_count: number;
}

interface Investor {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    investor: Investor;
    categories: ForumCategory[];
    activePage?: string;
    unreadMessages?: number;
    unreadAnnouncements?: number;
}>();

const getCategoryIcon = (icon: string | null) => {
    // Default to folder icon if no specific icon
    return FolderIcon;
};
</script>

<template>
    <InvestorLayout 
        :investor="investor" 
        page-title="Forum" 
        :active-page="activePage || 'forum'"
        :unread-messages="unreadMessages"
        :unread-announcements="unreadAnnouncements"
    >
        <Head title="Shareholder Forum" />

        <div class="max-w-6xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Shareholder Forum</h1>
                    <p class="text-gray-600 mt-1">
                        Connect and discuss with fellow shareholders
                    </p>
                </div>
            </div>

            <!-- Forum Guidelines -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                <div class="flex gap-3">
                    <ChatBubbleLeftRightIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                    <div>
                        <h3 class="font-medium text-blue-800">Forum Guidelines</h3>
                        <ul class="text-sm text-blue-700 mt-1 list-disc list-inside space-y-1">
                            <li>All posts are moderated before appearing publicly</li>
                            <li>Be respectful and professional in all discussions</li>
                            <li>Do not share confidential company information</li>
                            <li>Focus on constructive dialogue and questions</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="grid gap-4">
                <Link
                    v-for="category in categories"
                    :key="category.id"
                    :href="route('investor.forum.category', category.id)"
                    class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md hover:border-blue-300 transition-all group"
                >
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                            <component :is="getCategoryIcon(category.icon)" class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600">
                                {{ category.name }}
                            </h3>
                            <p v-if="category.description" class="text-gray-600 mt-1">
                                {{ category.description }}
                            </p>
                            <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
                                <span class="flex items-center gap-1">
                                    <ChatBubbleOvalLeftIcon class="h-4 w-4" aria-hidden="true" />
                                    {{ category.topics_count }} topics
                                </span>
                            </div>
                        </div>
                    </div>
                </Link>

                <div v-if="categories.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                    <FolderIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                    <p class="text-gray-500">No forum categories available yet</p>
                    <p class="text-sm text-gray-400 mt-1">Check back soon</p>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>
