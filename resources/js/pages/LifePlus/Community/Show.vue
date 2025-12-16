<script setup lang="ts">
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    MapPinIcon,
    UserCircleIcon,
    ClockIcon,
    CalendarDaysIcon,
    MegaphoneIcon,
    MagnifyingGlassCircleIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Post {
    id: number;
    type: string;
    title: string;
    content: string | null;
    location: string | null;
    event_date: string | null;
    formatted_event_date: string | null;
    image_url: string | null;
    poster: { id: number; name: string } | null;
    posted_at: string;
    is_owner: boolean;
}

const props = defineProps<{
    post: Post;
}>();

const page = usePage();

const typeConfig = computed(() => {
    switch (props.post.type) {
        case 'notice':
            return { icon: MegaphoneIcon, color: 'blue', label: 'Notice' };
        case 'event':
            return { icon: CalendarDaysIcon, color: 'purple', label: 'Event' };
        case 'lost_found':
            return { icon: MagnifyingGlassCircleIcon, color: 'amber', label: 'Lost & Found' };
        default:
            return { icon: MegaphoneIcon, color: 'gray', label: 'Post' };
    }
});

const backRoute = computed(() => {
    switch (props.post.type) {
        case 'notice':
            return route('lifeplus.community.notices');
        case 'event':
            return route('lifeplus.community.events');
        case 'lost_found':
            return route('lifeplus.community.lost-found');
        default:
            return route('lifeplus.community.index');
    }
});

const deletePost = () => {
    if (confirm('Delete this post?')) {
        router.delete(route('lifeplus.community.posts.destroy', props.post.id));
    }
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="backRoute"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Go back"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900 flex-1">{{ typeConfig.label }}</h1>
            <button 
                v-if="post.is_owner"
                @click="deletePost"
                class="p-2 rounded-lg hover:bg-red-50"
                aria-label="Delete post"
            >
                <TrashIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
            </button>
        </div>

        <!-- Image (if available) -->
        <div v-if="post.image_url" class="rounded-2xl overflow-hidden">
            <img :src="post.image_url" :alt="post.title" class="w-full h-48 object-cover" />
        </div>

        <!-- Post Content -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <!-- Type Badge -->
            <div class="flex items-center gap-2 mb-3">
                <span 
                    class="px-3 py-1 rounded-full text-sm font-medium"
                    :class="{
                        'bg-blue-100 text-blue-700': typeConfig.color === 'blue',
                        'bg-purple-100 text-purple-700': typeConfig.color === 'purple',
                        'bg-amber-100 text-amber-700': typeConfig.color === 'amber',
                        'bg-gray-100 text-gray-700': typeConfig.color === 'gray',
                    }"
                >
                    {{ typeConfig.label }}
                </span>
            </div>

            <!-- Title -->
            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ post.title }}</h2>

            <!-- Event Date (for events) -->
            <div v-if="post.type === 'event' && post.formatted_event_date" class="flex items-center gap-2 mb-4 p-3 bg-purple-50 rounded-xl">
                <CalendarDaysIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                <span class="font-medium text-purple-700">{{ post.formatted_event_date }}</span>
            </div>

            <!-- Location -->
            <div v-if="post.location" class="flex items-center gap-2 mb-4 text-gray-600">
                <MapPinIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                <span>{{ post.location }}</span>
            </div>

            <!-- Content -->
            <div v-if="post.content" class="prose prose-gray max-w-none">
                <p class="text-gray-600 whitespace-pre-wrap">{{ post.content }}</p>
            </div>
        </div>

        <!-- Poster Info -->
        <div class="bg-gray-50 rounded-2xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                    <UserCircleIcon class="h-6 w-6 text-gray-500" aria-hidden="true" />
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ post.poster?.name || 'Anonymous' }}</p>
                    <p class="text-sm text-gray-500 flex items-center gap-1">
                        <ClockIcon class="h-3.5 w-3.5" aria-hidden="true" />
                        {{ post.posted_at }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
