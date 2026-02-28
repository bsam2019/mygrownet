<template>
  <AdminLayout title="Starter Kit Content">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">Starter Kit Content Management</h1>
              <p class="mt-1 text-sm text-gray-500">
                Upload and manage e-books, videos, and training materials for starter kits
              </p>
            </div>
            <Link
              :href="route('admin.starter-kit-content.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
            >
              <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              Add Content
            </Link>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                <BookOpenIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">E-Books</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.ebooks }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                <VideoCameraIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Videos</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.videos }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                <AcademicCapIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Training</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.training }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-orange-100 rounded-lg p-3">
                <WrenchScrewdriverIcon class="h-6 w-6 text-orange-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Tools</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.tools }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Content Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Content</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Downloads</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in contentItems" :key="item.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ item.title }}</div>
                  <div class="text-sm text-gray-500">{{ item.category_label }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                    {{ item.category_label }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ item.tier_restriction === 'all' ? 'All Tiers' : 'Premium' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.download_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="item.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ item.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('admin.starter-kit-content.edit', item.id)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</Link>
                  <button @click="confirmDelete(item)" class="text-red-600 hover:text-red-900">Delete</button>
                </td>
              </tr>
              <tr v-if="contentItems.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  <BookOpenIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" aria-hidden="true" />
                  <p class="text-sm">No content items found. Click "Add Content" to upload your first item.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>


<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  PlusIcon,
  BookOpenIcon,
  VideoCameraIcon,
  AcademicCapIcon,
  WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline';
import { useAlert } from '@/composables/useAlert';

interface ContentItem {
  id: number;
  title: string;
  category: string;
  category_label: string;
  tier_restriction: string;
  unlock_day: number;
  download_count: number;
  is_active: boolean;
}

const props = defineProps<{
  contentItems: ContentItem[];
}>();

const { confirm, toast } = useAlert();

const stats = computed(() => ({
  ebooks: props.contentItems.filter(i => i.category === 'ebook').length,
  videos: props.contentItems.filter(i => i.category === 'video').length,
  training: props.contentItems.filter(i => i.category === 'training').length,
  tools: props.contentItems.filter(i => i.category === 'tool').length,
}));

const confirmDelete = async (item: ContentItem) => {
  const confirmed = await confirm({
    title: 'Delete Content',
    message: `Are you sure you want to delete "${item.title}"? This action cannot be undone.`,
    confirmText: 'Yes, delete it',
    cancelText: 'Cancel',
    type: 'danger',
  });

  if (confirmed) {
    router.delete(route('admin.starter-kit-content.destroy', item.id), {
      onSuccess: () => {
        toast('Content deleted successfully', 'success');
      },
      onError: () => {
        toast('Failed to delete content', 'error');
      },
    });
  }
};
</script>
