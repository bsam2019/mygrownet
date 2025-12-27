<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { ref, computed, watch } from 'vue';
import { ArrowLeftIcon, PhotoIcon, EyeIcon } from '@heroicons/vue/24/outline';

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');

const form = useForm({
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    featured_image: '',
    status: 'draft' as 'draft' | 'published',
    meta_title: '',
    meta_description: '',
});

const generateSlug = () => {
    form.slug = form.title
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
};

watch(() => form.title, generateSlug);

const submit = () => {
    form.post(`/sites/${props.site.subdomain}/dashboard/posts`);
};

const submitAndPublish = () => {
    form.status = 'published';
    submit();
};
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="Create Post">
        <Head :title="`Create Post - ${site.name}`" />

        <div class="max-w-4xl mx-auto">
            <Link :href="`/sites/${site.subdomain}/dashboard/posts`" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
                <ArrowLeftIcon class="w-4 h-4" aria-hidden="true" />
                Back to Posts
            </Link>

            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Create New Post</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Main Content -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input v-model="form.title" type="text" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg" placeholder="Enter post title" />
                        <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">/blog/</span>
                            <input v-model="form.slug" type="text" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm" placeholder="post-url-slug" />
                        </div>
                        <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                        <textarea v-model="form.excerpt" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Brief summary of the post..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                        <textarea v-model="form.content" rows="12" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm" placeholder="Write your post content here... (Markdown supported)"></textarea>
                        <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Featured Image URL</label>
                        <input v-model="form.featured_image" type="url" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="https://..." />
                        <div v-if="form.featured_image" class="mt-2">
                            <img :src="form.featured_image" alt="Preview" class="h-32 w-auto rounded-lg object-cover" />
                        </div>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                    <h3 class="font-semibold text-gray-900">SEO Settings</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                        <input v-model="form.meta_title" type="text" maxlength="60" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="SEO title (defaults to post title)" />
                        <p class="mt-1 text-xs text-gray-400">{{ form.meta_title.length }}/60</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                        <textarea v-model="form.meta_description" rows="2" maxlength="160" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="SEO description (defaults to excerpt)"></textarea>
                        <p class="mt-1 text-xs text-gray-400">{{ form.meta_description.length }}/160</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <Link :href="`/sites/${site.subdomain}/dashboard/posts`" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
                        Cancel
                    </Link>
                    <div class="flex items-center gap-3">
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 disabled:opacity-50">
                            Save as Draft
                        </button>
                        <button type="button" @click="submitAndPublish" :disabled="form.processing" class="px-5 py-2.5 text-white font-medium rounded-lg disabled:opacity-50" :style="{ backgroundColor: primaryColor }">
                            {{ form.processing ? 'Saving...' : 'Publish' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </SiteMemberLayout>
</template>
