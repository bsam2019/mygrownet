<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, PencilIcon, TrashIcon, EyeIcon } from '@heroicons/vue/24/outline';

defineProps<{
  posts: any;
}>();

const deletePost = (id: number) => {
  if (confirm('Are you sure you want to delete this post?')) {
    router.delete(`/geopamu/admin/blog/${id}`);
  }
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};
</script>

<template>
  <Head title="Blog Management - Geopamu Admin" />
  
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Blog Management</h1>
            <p class="text-gray-600 mt-1">Manage your blog posts and content</p>
          </div>
          <Link
            href="/geopamu/admin/blog/create"
            class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
          >
            <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
            New Post
          </Link>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Total Posts</div>
          <div class="text-3xl font-bold text-gray-900 mt-2">{{ posts.total }}</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Published</div>
          <div class="text-3xl font-bold text-green-600 mt-2">
            {{ posts.data.filter((p: any) => p.status === 'published').length }}
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Drafts</div>
          <div class="text-3xl font-bold text-yellow-600 mt-2">
            {{ posts.data.filter((p: any) => p.status === 'draft').length }}
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
          <div class="text-sm text-gray-600">Total Views</div>
          <div class="text-3xl font-bold text-blue-600 mt-2">
            {{ posts.data.reduce((sum: number, p: any) => sum + p.views_count, 0) }}
          </div>
        </div>
      </div>

      <!-- Posts Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Title
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Category
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Views
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Date
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="post in posts.data" :key="post.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ post.title }}</div>
                <div class="text-sm text-gray-500">{{ post.slug }}</div>
              </td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded capitalize">
                  {{ post.category }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded',
                    post.status === 'published'
                      ? 'bg-green-100 text-green-800'
                      : 'bg-yellow-100 text-yellow-800'
                  ]"
                >
                  {{ post.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ post.views_count }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ formatDate(post.created_at) }}
              </td>
              <td class="px-6 py-4 text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <Link
                    :href="`/geopamu/blog/${post.slug}`"
                    target="_blank"
                    class="text-gray-600 hover:text-gray-900"
                    title="View"
                  >
                    <EyeIcon class="h-5 w-5" aria-hidden="true" />
                  </Link>
                  <Link
                    :href="`/geopamu/admin/blog/${post.id}/edit`"
                    class="text-blue-600 hover:text-blue-900"
                    title="Edit"
                  >
                    <PencilIcon class="h-5 w-5" aria-hidden="true" />
                  </Link>
                  <button
                    @click="deletePost(post.id)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete"
                  >
                    <TrashIcon class="h-5 w-5" aria-hidden="true" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="posts.links && posts.links.length > 3" class="mt-6 flex justify-center gap-2">
        <Link
          v-for="(link, index) in posts.links"
          :key="index"
          :href="link.url"
          v-html="link.label"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            link.active
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-100'
          ]"
        />
      </div>
    </div>
  </div>
</template>
