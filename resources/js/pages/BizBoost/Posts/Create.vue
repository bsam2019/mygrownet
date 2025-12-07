<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, PhotoIcon, CalendarIcon, SparklesIcon, RocketLaunchIcon } from '@heroicons/vue/24/outline';

interface TemplateData {
    caption?: string;
    hashtags?: string[];
    cta?: string;
    [key: string]: unknown;
}

interface Template {
    id: number;
    name: string;
    description?: string;
    template_data: TemplateData | null;
}

interface Product {
    id: number;
    name: string;
    price: number;
}

interface Campaign {
    id: number;
    name: string;
    objective: string;
    target_platforms: string[];
}

interface Props {
    canSchedule: boolean;
    canAutoPost: boolean;
    template?: Template | null;
    integrations: { id: number; provider: string }[];
    products?: Product[];
    campaign?: Campaign | null;
}

const props = withDefaults(defineProps<Props>(), {
    template: null,
    products: () => [],
    campaign: null,
});

// Build initial caption from template if provided
const getInitialCaption = (): string => {
    if (!props.template) return '';
    
    const data = props.template.template_data;
    if (!data) return '';
    
    let caption = '';
    
    // Handle caption - could be string or nested
    if (typeof data.caption === 'string') {
        caption = data.caption;
    } else if (typeof data === 'object' && 'caption' in data) {
        caption = String(data.caption || '');
    }
    
    // Add hashtags if present
    if (data.hashtags && Array.isArray(data.hashtags) && data.hashtags.length > 0) {
        const hashtagStr = data.hashtags
            .map((h: string) => (h.startsWith('#') ? h : `#${h}`))
            .join(' ');
        if (hashtagStr) {
            caption += (caption ? '\n\n' : '') + hashtagStr;
        }
    }
    
    // Add CTA if present
    if (data.cta && typeof data.cta === 'string') {
        caption += (caption ? '\n\n' : '') + data.cta;
    }
    
    return caption;
};

const form = useForm<{
    title: string;
    caption: string;
    post_type: string;
    platform_targets: string[];
    scheduled_at: string;
    status: 'draft' | 'scheduled';
    media: File[];
    campaign_id: number | null;
}>({
    title: '',
    caption: getInitialCaption(),
    post_type: 'standard',
    platform_targets: props.campaign?.target_platforms ?? [],
    scheduled_at: '',
    status: 'draft',
    media: [],
    campaign_id: props.campaign?.id ?? null,
});

const mediaPreview = ref<string[]>([]);

const handleMediaUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const files = Array.from(target.files);
        // Clear previous previews
        mediaPreview.value.forEach((url) => URL.revokeObjectURL(url));
        // Set new files
        form.media = files;
        mediaPreview.value = files.map((file) => URL.createObjectURL(file));
    }
};

const togglePlatform = (platform: string) => {
    const index = form.platform_targets.indexOf(platform);
    if (index === -1) {
        form.platform_targets.push(platform);
    } else {
        form.platform_targets.splice(index, 1);
    }
};

const submit = () => {
    // Set status based on whether scheduled_at is provided
    form.status = form.scheduled_at ? 'scheduled' : 'draft';

    form.post(route('bizboost.posts.store'), {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        },
    });
};
</script>

<template>
    <Head title="Create Post - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link 
                        :href="campaign ? route('bizboost.campaigns.show', campaign.id) : route('bizboost.posts.index')" 
                        class="inline-flex items-center text-gray-600 hover:text-gray-900"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        {{ campaign ? 'Back to Campaign' : 'Back to Posts' }}
                    </Link>
                </div>

                <!-- Campaign indicator -->
                <div v-if="campaign" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-2">
                    <RocketLaunchIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                    <span class="text-sm text-blue-700">Creating post for campaign: <strong>{{ campaign.name }}</strong></span>
                </div>

                <!-- Template indicator -->
                <div v-if="template" class="mb-4 p-3 bg-violet-50 border border-violet-200 rounded-lg flex items-center gap-2">
                    <SparklesIcon class="h-5 w-5 text-violet-600" aria-hidden="true" />
                    <span class="text-sm text-violet-700">Using template: <strong>{{ template.name }}</strong></span>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h1 class="text-2xl font-bold text-gray-900">Create Post</h1>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input v-model="form.title" type="text" class="w-full rounded-md border-gray-300" placeholder="Post title (optional)" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                            <textarea v-model="form.caption" rows="4" class="w-full rounded-md border-gray-300" placeholder="Write your caption..." required></textarea>
                            <p class="mt-1 text-sm text-gray-500">{{ form.caption.length }} characters</p>
                        </div>

                        <!-- Media Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Media</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                <PhotoIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                                <div class="mt-2">
                                    <label class="cursor-pointer text-blue-600 hover:text-blue-800">
                                        <span>Upload images or videos</span>
                                        <input type="file" multiple accept="image/*,video/*" class="sr-only" @change="handleMediaUpload" />
                                    </label>
                                </div>
                            </div>
                            <div v-if="mediaPreview.length > 0" class="mt-4 flex gap-2 flex-wrap">
                                <div v-for="(preview, index) in mediaPreview" :key="index" class="w-24 h-24 bg-gray-100 rounded overflow-hidden">
                                    <img :src="preview" class="w-full h-full object-cover" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Platforms</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" :checked="form.platform_targets.includes('facebook')" @change="togglePlatform('facebook')" class="rounded border-gray-300 text-blue-600" />
                                    <span>Facebook</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" :checked="form.platform_targets.includes('instagram')" @change="togglePlatform('instagram')" class="rounded border-gray-300 text-blue-600" />
                                    <span>Instagram</span>
                                </label>
                            </div>
                        </div>

                        <div v-if="canSchedule">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <CalendarIcon class="inline h-4 w-4 mr-1" aria-hidden="true" />
                                Schedule (optional)
                            </label>
                            <input v-model="form.scheduled_at" type="datetime-local" class="w-full rounded-md border-gray-300" />
                            <p class="mt-1 text-sm text-gray-500">Leave empty to save as draft</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link 
                                :href="campaign ? route('bizboost.campaigns.show', campaign.id) : route('bizboost.posts.index')" 
                                class="px-4 py-2 text-gray-700 hover:text-gray-900"
                            >
                                Cancel
                            </Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                {{ form.scheduled_at ? 'Schedule Post' : 'Save as Draft' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
