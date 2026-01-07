<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GeopamuLayout from '@/Layouts/GeopamuLayout.vue';
import PageHeader from '@/Components/Geopamu/PageHeader.vue';
import { ClockIcon, UserIcon, EyeIcon } from '@heroicons/vue/24/outline';

defineProps<{
  posts: any;
  categories?: string[];
  selectedCategory?: string;
}>();

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};
</script>

<template>
  <Head title="Blog - Geopamu" />
  
  <GeopamuLayout>
    <PageHeader
      title="Our Blog"
      subtitle="Tips, insights, and news about printing and branding"
    />

    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Categories Filter -->
        <div v-if="categories && categories.length" class="flex flex-wrap gap-3 mb-12">
          <Link
            href="/geopamu/blog"
            :class="[
              'px-6 py-2 rounded-full font-medium transition-all',
              !selectedCategory
                ? 'bg-blue-600 text-white shadow-lg'
                : 'bg-white text-gray-700 hover:bg-gray-100 shadow-sm'
            ]"
          >
            All Posts
          </Link>
          <Link
            v-for="category in categories"
            :key="category"
            :href="`/geopamu/blog/category/${category}`"
            :class="[
              'px-6 py-2 rounded-full font-medium transition-all capitalize',
              selectedCategory === category
                ? 'bg-blue-600 text-white shadow-lg'
                : 'bg-white text-gray-700 hover:bg-gray-100 shadow-sm'
            ]"
          >
            {{ category }}
          </Link>
        </div>

        <!-- Blog Posts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <Link
            v-for="post in posts.data"
            :key="post.id"
            :href="`/geopamu/blog/${post.slug}`"
            class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all overflow-hidden"
          >
            <div class="aspect-video bg-gray-200 overflow-hidden">
              <img
                v-if="post.featured_image"
                :src="post.featured_image"
                :alt="post.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                No Image
              </div>
            </div>
            
            <div class="p-6">
              <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-xs font-medium capitalize">
                  {{ post.category }}
                </span>
                <span class="flex items-center gap-1">
                  <ClockIcon class="h-4 w-4" aria-hidden="true" />
                  {{ formatDate(post.published_at) }}
                </span>
              </div>
              
              <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                {{ post.title }}
              </h3>
              
              <p v-if="post.excerpt" class="text-gray-600 mb-4 line-clamp-2">
                {{ post.excerpt }}
              </p>
              
              <div class="flex items-center justify-between text-sm text-gray-500">
                <span class="flex items-center gap-1">
                  <UserIcon class="h-4 w-4" aria-hidden="true" />
                  {{ post.author?.name || 'Admin' }}
                </span>
                <span class="flex items-center gap-1">
                  <EyeIcon class="h-4 w-4" aria-hidden="true" />
                  {{ post.views_count }} views
                </span>
              </div>
            </div>
          </Link>
        </div>

        <!-- Pagination -->
        <div v-if="posts.links && posts.links.length > 3" class="mt-12 flex justify-center gap-2">
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
    </section>
  </GeopamuLayout>
</template>
