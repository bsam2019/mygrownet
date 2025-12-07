<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, SparklesIcon, LockClosedIcon } from '@heroicons/vue/24/outline';

interface Template {
    id: number;
    name: string;
    description: string;
    category: string;
    industry: string | null;
    preview_path: string | null;
    is_premium: boolean;
    usage_count: number;
    template_data: Record<string, unknown>;
}

interface Props {
    template: Template;
    canUse: boolean;
}

const props = defineProps<Props>();

const useTemplate = () => {
    if (!props.canUse) {
        router.visit('/bizboost/upgrade');
        return;
    }
    // Navigate directly to create post with template_id
    router.visit(`/bizboost/posts/create?template_id=${props.template.id}`);
};
</script>

<template>
    <Head :title="`${template.name} - BizBoost`" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.templates.index')" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Templates
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="aspect-video bg-gray-100 relative">
                        <img
                            v-if="template.preview_path"
                            :src="`/storage/${template.preview_path}`"
                            :alt="template.name"
                            class="w-full h-full object-contain"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <SparklesIcon class="h-24 w-24 text-gray-300" aria-hidden="true" />
                        </div>
                        <div v-if="template.is_premium" class="absolute top-4 right-4">
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-600 text-white text-sm rounded-full">
                                <LockClosedIcon class="h-4 w-4" aria-hidden="true" />
                                Premium
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <h1 class="text-2xl font-bold text-gray-900">{{ template.name }}</h1>
                        <p class="mt-2 text-gray-600">{{ template.description }}</p>
                        
                        <div class="mt-4 flex items-center gap-4 text-sm text-gray-500">
                            <span class="px-2 py-1 bg-gray-100 rounded">{{ template.category }}</span>
                            <span v-if="template.industry">{{ template.industry }}</span>
                            <span>{{ template.usage_count }} uses</span>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button
                                @click="useTemplate"
                                :class="[
                                    'flex-1 px-4 py-3 rounded-lg font-medium',
                                    canUse
                                        ? 'bg-blue-600 text-white hover:bg-blue-700'
                                        : 'bg-purple-600 text-white hover:bg-purple-700'
                                ]"
                            >
                                {{ canUse ? 'Use This Template' : 'Upgrade to Use' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
