<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { ref, computed } from 'vue';
import { ArrowLeftIcon, TrashIcon, EyeIcon } from '@heroicons/vue/24/outline';

interface Post {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    content: string;
    featured_image: string | null;
    status: 'draft' | 'published';
    meta_title: string | null;
    meta_description: string | null;
    views_count: number;
    created_at: string;
}

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
    post: Post;
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const showDeleteModal = ref(false);

const form = useForm({
    title: props.post.title,
    slug: props.post.slug,
    excerpt: props.post.excerpt || '',
    content: props.post.content,
    featured_image: props.post.featured_image || '',
    status: props.post.status,
    meta_title: props.post.meta_title || '',
    meta_description: props.post.meta_description || '',
});

const submit = () => {
    form.put(`/sites/${props.site.subdomain}/dashboard/posts/${props.post.id}`);
};

const publish = () => {
    form.status = 'published';
    submit();
};

const unpublish = () => {
    form.status = 'draft';
    submit();
};

const deletePost = () => {
    router.delete(`/sites/${props.site.subdomain}/dashboard/posts/${props.post.id}`);
};

const canDelete = computed(() => {
    const level = props.user.role?.level || 0;
    return level >= 100 || props.user.permissions.includes('posts.delete');
});
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="Edit Post">
        <Head :title="`Edit: ${post.title} - ${site.name}`" />

        <div class="max-w-4xl mx-auto">
            <Link :href="`/sites/${site.subdomain}/dashboard/posts`" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
                <ArrowLeftIcon class="w-4 h-4" aria-hidden="true" />
                Back to Posts
            </Link>

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Post</h1>
                    <p class="text-sm text-gray-500">{{ post.views_count }} views</p>
                </div>
                <div class="flex items-center gap-2">
                    <a v-if="post.status === 'published'" :href="`/sites/${site.subdomain}/blog/${post.slug}`" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">
                        <EyeIcon class="w-4 h-4" aria-hidden="true" />
                        View
                    </a>
                    <span :class="post.status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'" class="px-2.5 py-1 text-xs font-medium rounded-full capitalize">
                        {{ post.status }}
                    </span>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Main Content -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input v-model="form.title" type="text" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg" />
                        <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">/blog/</span>
                            <input v-model="form.slug" type="text" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                        <textarea v-model="form.excerpt" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                        <textarea v-model="form.content" rows="12" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Featured Image URL</label>
                        <input v-model="form.featured_image" type="url" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
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
                        <input v-model="form.meta_title" type="text" maxlength="60" class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
                        <p class="mt-1 text-xs text-gray-400">{{ form.meta_title.length }}/60</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                        <textarea v-model="form.meta_description" rows="2" maxlength="160" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                        <p class="mt-1 text-xs text-gray-400">{{ form.meta_description.length }}/160</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <button v-if="canDelete" type="button" @click="showDeleteModal = true" class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg">
                        <TrashIcon class="w-4 h-4" aria-hidden="true" />
                        Delete
                    </button>
                    <div v-else></div>
                    <div class="flex items-center gap-3">
                        <button v-if="post.status === 'published'" type="button" @click="unpublish" :disabled="form.processing" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
                            Unpublish
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 disabled:opacity-50">
                            Save Changes
                        </button>
                        <button v-if="post.status === 'draft'" type="button" @click="publish" :disabled="form.processing" class="px-5 py-2.5 text-white font-medium rounded-lg disabled:opacity-50" :style="{ backgroundColor: primaryColor }">
                            Publish
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="showDeleteModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Post</h3>
                    <p class="text-sm text-gray-600 mb-6">Are you sure you want to delete "{{ post.title }}"? This cannot be undone.</p>
                    <div class="flex justify-end gap-3">
                        <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="deletePost" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </SiteMemberLayout>
</template>
