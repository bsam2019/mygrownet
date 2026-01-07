<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
  post: any;
}>();

const categories = ['general', 'tips', 'news', 'tutorials', 'case-studies'];

const form = useForm({
  title: props.post.title,
  excerpt: props.post.excerpt || '',
  content: props.post.content,
  featured_image: props.post.featured_image || '',
  category: props.post.category,
  tags: props.post.tags || [],
  status: props.post.status
});

const tagInput = ref('');

const addTag = () => {
  if (tagInput.value && !form.tags.includes(tagInput.value)) {
    form.tags.push(tagInput.value);
    tagInput.value = '';
  }
};

const removeTag = (tag: string) => {
  form.tags = form.tags.filter(t => t !== tag);
};

const submit = () => {
  form.put(`/geopamu/admin/blog/${props.post.id}`);
};
</script>

<template>
  <Head :title="`Edit: ${post.title} - Geopamu Admin`" />
  
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Blog Post</h1>
            <p class="text-gray-600 mt-1">{{ post.title }}</p>
          </div>
          <Link
            href="/geopamu/admin/blog"
            class="text-gray-600 hover:text-gray-900 font-medium"
          >
            ← Back to Posts
          </Link>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Title -->
        <div class="bg-white p-6 rounded-lg shadow-sm">
          <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
            Title *
          </label>
          <input
            id="title"
            v-model="form.title"
            type="text"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
          />
        </div>

        <!-- Excerpt -->
        <div class="bg-white p-6 rounded-lg shadow-sm">
          <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">
            Excerpt
          </label>
          <textarea
            id="excerpt"
            v-model="form.excerpt"
            rows="3"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
          ></textarea>
        </div>

        <!-- Content -->
        <div class="bg-white p-6 rounded-lg shadow-sm">
          <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
            Content *
          </label>
          <textarea
            id="content"
            v-model="form.content"
            rows="15"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent font-mono text-sm"
          ></textarea>
        </div>

        <!-- Featured Image -->
        <div class="bg-white p-6 rounded-lg shadow-sm">
          <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">
            Featured Image URL
          </label>
          <input
            id="featured_image"
            v-model="form.featured_image"
            type="url"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
          />
        </div>

        <!-- Category & Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white p-6 rounded-lg shadow-sm">
            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
              Category *
            </label>
            <select
              id="category"
              v-model="form.category"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent capitalize"
            >
              <option v-for="cat in categories" :key="cat" :value="cat">
                {{ cat }}
              </option>
            </select>
          </div>

          <div class="bg-white p-6 rounded-lg shadow-sm">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
              Status *
            </label>
            <select
              id="status"
              v-model="form.status"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
            >
              <option value="draft">Draft</option>
              <option value="published">Published</option>
            </select>
          </div>
        </div>

        <!-- Tags -->
        <div class="bg-white p-6 rounded-lg shadow-sm">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Tags
          </label>
          <div class="flex gap-2 mb-3">
            <input
              v-model="tagInput"
              type="text"
              @keyup.enter="addTag"
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
            />
            <button
              type="button"
              @click="addTag"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
            >
              Add
            </button>
          </div>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="tag in form.tags"
              :key="tag"
              class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm"
            >
              {{ tag }}
              <button
                type="button"
                @click="removeTag(tag)"
                class="hover:text-blue-900"
              >
                ×
              </button>
            </span>
          </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-4">
          <Link
            href="/geopamu/admin/blog"
            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
