<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GeopamuLayout from '@/Layouts/GeopamuLayout.vue';
import { ClockIcon, UserIcon, EyeIcon, CalendarIcon } from '@heroicons/vue/24/outline';

defineProps<{
  post: any;
  relatedPosts: any[];
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
  <Head :title="`${post.title} - Geopamu Blog`" />
  
  <GeopamuLayout>
    <article class="py-16 bg-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-gray-600 mb-8">
          <Link href="/geopamu" class="hover:text-blue-600">Home</Link>
          <span>/</span>
          <Link href="/geopamu/blog" class="hover:text-blue-600">Blog</Link>
          <span>/</span>
          <span class="text-gray-900">{{ post.title }}</span>
        </nav>

        <!-- Category Badge -->
        <span class="inline-block px-4 py-1 bg-blue-100 text-blue-600 rounded-full text-sm font-medium capitalize mb-4">
          {{ post.category }}
        </span>

        <!-- Title -->
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
          {{ post.title }}
        </h1>

        <!-- Meta Info -->
        <div class="flex flex-wrap items-center gap-6 text-gray-600 mb-8 pb-8 border-b">
          <span class="flex items-center gap-2">
            <UserIcon class="h-5 w-5" aria-hidden="true" />
            {{ post.author?.name || 'Admin' }}
          </span>
          <span class="flex items-center gap-2">
            <CalendarIcon class="h-5 w-5" aria-hidden="true" />
            {{ formatDate(post.published_at) }}
          </span>
          <span class="flex items-center gap-2">
            <ClockIcon class="h-5 w-5" aria-hidden="true" />
            {{ post.reading_time }} min read
          </span>
          <span class="flex items-center gap-2">
            <EyeIcon class="h-5 w-5" aria-hidden="true" />
            {{ post.views_count }} views
          </span>
        </div>

        <!-- Featured Image -->
        <div v-if="post.featured_image" class="mb-12 rounded-xl overflow-hidden">
          <img
            :src="post.featured_image"
            :alt="post.title"
            class="w-full h-auto"
          />
        </div>

        <!-- Content -->
        <div class="prose prose-lg max-w-none mb-12" v-html="post.content"></div>

        <!-- Tags -->
        <div v-if="post.tags && post.tags.length" class="flex flex-wrap gap-2 mb-12">
          <span
            v-for="tag in post.tags"
            :key="tag"
            class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm"
          >
            #{{ tag }}
          </span>
        </div>
      </div>
    </article>

    <!-- Related Posts -->
    <section v-if="relatedPosts.length" class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Related Articles</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <Link
            v-for="relatedPost in relatedPosts"
            :key="relatedPost.id"
            :href="`/geopamu/blog/${relatedPost.slug}`"
            class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all overflow-hidden"
          >
            <div class="aspect-video bg-gray-200 overflow-hidden">
              <img
                v-if="relatedPost.featured_image"
                :src="relatedPost.featured_image"
                :alt="relatedPost.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
            </div>
            
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2">
                {{ relatedPost.title }}
              </h3>
              <p class="text-sm text-gray-600">
                {{ formatDate(relatedPost.published_at) }}
              </p>
            </div>
          </Link>
        </div>
      </div>
    </section>
  </GeopamuLayout>
</template>

<style scoped>
.prose {
  @apply text-gray-700;
}

.prose :deep(h2) {
  @apply text-2xl font-bold text-gray-900 mt-8 mb-4;
}

.prose :deep(h3) {
  @apply text-xl font-bold text-gray-900 mt-6 mb-3;
}

.prose :deep(p) {
  @apply mb-4 leading-relaxed;
}

.prose :deep(ul), .prose :deep(ol) {
  @apply mb-4 pl-6;
}

.prose :deep(li) {
  @apply mb-2;
}

.prose :deep(a) {
  @apply text-blue-600 hover:text-blue-700 underline;
}

.prose :deep(img) {
  @apply rounded-lg my-8;
}

.prose :deep(blockquote) {
  @apply border-l-4 border-blue-600 pl-4 italic my-6;
}
</style>
