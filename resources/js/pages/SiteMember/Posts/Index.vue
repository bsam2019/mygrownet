<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { ref, computed } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon, EyeIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

interface Post {
    id: number;
    title: string;
    slug: string;
    status: 'draft' | 'published';
    views_count: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
    posts: { data: Post[]; links: any; meta: any };
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const deletingPost = ref<Post | null>(null);

const isAdmin = computed(() => props.user.role?.level >= 100);
const canCreate = computed(() => isAdmin.value || props.user.permissions.includes('posts.create'));
const canEdit = computed(() => isAdmin.value || props.user.permissions.includes('posts.edit'));
const canDelete = computed(() => isAdmin.value || props.user.permissions.includes('posts.delete'));

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-ZM', { year: 'numeric', month: 'short', day: 'numeric' });

const getStatusConfig = (status: string) => {
    return status === 'published' 
        ? { bg: 'bg-emerald-100', text: 'text-emerald-700' }
        : { bg: 'bg-gray-100', text: 'text-gray-600' };
};

const deletePost = () => {
    if (!deletingPost.value) return;
    router.delete(`/sites/${props.site.subdomain}/dashboard/posts/${deletingPost.value.id}`, {
        onSuccess: () => { deletingPost.value = null; },
    });
};
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="Posts">
        <Head :title="`Posts - ${site.name}`" />

        <div class="max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Posts</h1>
                    <p class="text-gray-500">Manage your blog posts and content</p>
                </div>
                <Link v-if="canCreate" :href="`/sites/${site.subdomain}/dashboard/posts/create`"
                    class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-medium rounded-lg"
                    :style="{ backgroundColor: primaryColor }">
                    <PlusIcon class="w-5 h-5" aria-hidden="true" />
                    New Post
                </Link>
            </div>

            <!-- Posts Table -->
            <div v-if="posts.data.length > 0" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="post in posts.data" :key="post.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ post.title }}</p>
                                <p class="text-sm text-gray-500">/{{ post.slug }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full capitalize" :class="[getStatusConfig(post.status).bg, getStatusConfig(post.status).text]">
                                    {{ post.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ post.views_count }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(post.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a v-if="post.status === 'published'" :href="`/sites/${site.subdomain}/blog/${post.slug}`" target="_blank"
                                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                        <EyeIcon class="w-4 h-4" aria-hidden="true" />
                                    </a>
                                    <Link v-if="canEdit" :href="`/sites/${site.subdomain}/dashboard/posts/${post.id}/edit`"
                                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                        <PencilIcon class="w-4 h-4" aria-hidden="true" />
                                    </Link>
                                    <button v-if="canDelete" @click="deletingPost = post"
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                        <TrashIcon class="w-4 h-4" aria-hidden="true" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <DocumentTextIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">No posts yet</h3>
                <p class="text-gray-500 mb-4">Create your first blog post to get started.</p>
                <Link v-if="canCreate" :href="`/sites/${site.subdomain}/dashboard/posts/create`"
                    class="inline-flex items-center gap-2 px-4 py-2 text-white font-medium rounded-lg"
                    :style="{ backgroundColor: primaryColor }">
                    <PlusIcon class="w-5 h-5" aria-hidden="true" />
                    Create Post
                </Link>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="deletingPost" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="deletingPost = null"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Post</h3>
                    <p class="text-sm text-gray-600 mb-6">Are you sure you want to delete "{{ deletingPost.title }}"? This cannot be undone.</p>
                    <div class="flex justify-end gap-3">
                        <button @click="deletingPost = null" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="deletePost" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </SiteMemberLayout>
</template>
