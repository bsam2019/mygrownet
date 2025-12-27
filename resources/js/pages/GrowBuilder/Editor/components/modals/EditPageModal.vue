<script setup lang="ts">
import { ref } from 'vue';
import { SparklesIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import type { Page } from '../../types';

const props = defineProps<{
    show: boolean;
    page: Page | null;
    siteId?: number;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update', page: Page): void;
}>();

const activeTab = ref<'general' | 'seo'>('general');
const aiLoading = ref(false);

const handleUpdate = () => {
    if (props.page) {
        emit('update', props.page);
    }
};

const generateSEO = async () => {
    if (!props.page || !props.siteId) return;
    aiLoading.value = true;
    try {
        const response = await axios.post(`/growbuilder/sites/${props.siteId}/ai/generate-meta`, {
            pageTitle: props.page.title,
            pageContent: props.page.title,
            siteName: props.page.title,
        });
        if (response.data.metaTitle) {
            props.page.metaTitle = response.data.metaTitle;
        }
        if (response.data.metaDescription) {
            props.page.metaDescription = response.data.metaDescription;
        }
    } catch (error) {
        console.error('Failed to generate SEO:', error);
    } finally {
        aiLoading.value = false;
    }
};
</script>

<template>
    <Teleport to="body">
        <div v-if="show && page" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6" @click.stop>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Page</h2>
                
                <!-- Tabs -->
                <div class="flex gap-1 mb-4 p-1 bg-gray-100 rounded-lg">
                    <button
                        type="button"
                        @click="activeTab = 'general'"
                        :class="['flex-1 px-3 py-1.5 text-sm font-medium rounded-md transition', activeTab === 'general' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900']"
                    >General</button>
                    <button
                        type="button"
                        @click="activeTab = 'seo'"
                        :class="['flex-1 px-3 py-1.5 text-sm font-medium rounded-md transition', activeTab === 'seo' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900']"
                    >SEO</button>
                </div>
                
                <form @submit.prevent="handleUpdate" class="space-y-4">
                    <!-- General Tab -->
                    <div v-show="activeTab === 'general'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Page Title</label>
                            <input
                                v-model="page.title"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                            <input
                                v-model="page.slug"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900"
                                :disabled="page.isHomepage"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <input v-model="page.showInNav" type="checkbox" id="editShowInNav" class="rounded border-gray-300" />
                            <label for="editShowInNav" class="text-sm text-gray-700">Show in navigation</label>
                        </div>
                    </div>
                    
                    <!-- SEO Tab -->
                    <div v-show="activeTab === 'seo'" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">Optimize this page for search engines</p>
                            <button
                                type="button"
                                @click="generateSEO"
                                :disabled="aiLoading"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition disabled:opacity-50"
                            >
                                <SparklesIcon class="h-3.5 w-3.5" :class="{ 'animate-spin': aiLoading }" aria-hidden="true" />
                                {{ aiLoading ? 'Generating...' : 'AI Generate' }}
                            </button>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                            <input
                                v-model="page.metaTitle"
                                type="text"
                                maxlength="60"
                                placeholder="Page title for search results"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900"
                            />
                            <p class="mt-1 text-xs text-gray-500">{{ (page.metaTitle || '').length }}/60 characters</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                            <textarea
                                v-model="page.metaDescription"
                                rows="3"
                                maxlength="160"
                                placeholder="Brief description for search results"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">{{ (page.metaDescription || '').length }}/160 characters</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Social Share Image (OG Image)</label>
                            <input
                                v-model="page.ogImage"
                                type="url"
                                placeholder="https://..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900"
                            />
                            <p class="mt-1 text-xs text-gray-500">Recommended: 1200x630 pixels</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="emit('close')" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
