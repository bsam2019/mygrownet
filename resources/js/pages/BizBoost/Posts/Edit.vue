<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, PhotoIcon } from '@heroicons/vue/24/outline';

interface Media {
    id: number;
    path: string;
    type: string;
}

interface Post {
    id: number;
    title: string;
    caption: string;
    status: string;
    post_type: string;
    platform_targets: string[];
    scheduled_at: string | null;
    media: Media[];
}

interface Props {
    post: Post;
    canSchedule: boolean;
    canAutoPost: boolean;
    templates: { id: number; name: string }[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.post.title,
    caption: props.post.caption,
    post_type: props.post.post_type,
    platform_targets: props.post.platform_targets || [],
    scheduled_at: props.post.scheduled_at || '',
});

const togglePlatform = (platform: string) => {
    const index = form.platform_targets.indexOf(platform);
    if (index === -1) {
        form.platform_targets.push(platform);
    } else {
        form.platform_targets.splice(index, 1);
    }
};

const submit = () => {
    form.put(route('bizboost.posts.update', props.post.id));
};
</script>

<template>
    <Head title="Edit Post - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.posts.show', post.id)" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Post
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h1 class="text-2xl font-bold text-gray-900">Edit Post</h1>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input v-model="form.title" type="text" class="w-full rounded-md border-gray-300" placeholder="Post title" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                            <textarea v-model="form.caption" rows="4" class="w-full rounded-md border-gray-300" placeholder="Write your caption..."></textarea>
                        </div>

                        <!-- Current Media -->
                        <div v-if="post.media.length > 0">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Media</label>
                            <div class="flex gap-2 flex-wrap">
                                <div v-for="media in post.media" :key="media.id" class="w-24 h-24 bg-gray-100 rounded overflow-hidden">
                                    <img :src="`/storage/${media.path}`" class="w-full h-full object-cover" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Platforms</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" :checked="form.platform_targets.includes('facebook')" @change="togglePlatform('facebook')" class="rounded border-gray-300 text-blue-600" />
                                    <span>Facebook</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" :checked="form.platform_targets.includes('instagram')" @change="togglePlatform('instagram')" class="rounded border-gray-300 text-blue-600" />
                                    <span>Instagram</span>
                                </label>
                            </div>
                        </div>

                        <div v-if="canSchedule">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Schedule (optional)</label>
                            <input v-model="form.scheduled_at" type="datetime-local" class="w-full rounded-md border-gray-300" />
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link :href="route('bizboost.posts.show', post.id)" class="px-4 py-2 text-gray-700 hover:text-gray-900">Cancel</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
