<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import Card from '@/Components/BizBoost/UI/Card.vue';
import Button from '@/Components/BizBoost/UI/Button.vue';
import FormInput from '@/Components/BizBoost/Form/FormInput.vue';
import FormTextarea from '@/Components/BizBoost/Form/FormTextarea.vue';
import FormCheckbox from '@/Components/BizBoost/Form/FormCheckbox.vue';
import FormImageUpload from '@/Components/BizBoost/Form/FormImageUpload.vue';
import FormSection from '@/Components/BizBoost/Form/FormSection.vue';
import FormActions from '@/Components/BizBoost/Form/FormActions.vue';
import {
    ArrowLeftIcon,
    SparklesIcon,
    RocketLaunchIcon,
    PhotoIcon,
    CalendarDaysIcon,
    ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline';

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

const getInitialCaption = (): string => {
    if (!props.template) return '';
    const data = props.template.template_data;
    if (!data) return '';
    let caption = '';
    if (typeof data.caption === 'string') {
        caption = data.caption;
    } else if (typeof data === 'object' && 'caption' in data) {
        caption = String(data.caption || '');
    }
    if (data.hashtags && Array.isArray(data.hashtags) && data.hashtags.length > 0) {
        const hashtagStr = data.hashtags
            .map((h: string) => (h.startsWith('#') ? h : `#${h}`))
            .join(' ');
        if (hashtagStr) {
            caption += (caption ? '\n\n' : '') + hashtagStr;
        }
    }
    if (data.cta && typeof data.cta === 'string') {
        caption += (caption ? '\n\n' : '') + data.cta;
    }
    return caption;
};

const form = useForm({
    title: '',
    caption: getInitialCaption(),
    post_type: 'standard',
    platform_targets: props.campaign?.target_platforms ?? [],
    scheduled_at: '',
    status: 'draft',
    media: [] as File[],
    campaign_id: props.campaign?.id ?? null,
});

const mediaPreview = ref<string[]>([]);
const showAiGenerator = ref(false);
const aiContext = ref('');
const aiGenerating = ref(false);

const handleMediaUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const files = Array.from(target.files);
        mediaPreview.value.forEach((url) => URL.revokeObjectURL(url));
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

const generateWithAi = async () => {
    if (!aiContext.value.trim()) return;
    aiGenerating.value = true;
    try {
        const response = await fetch(route('bizboost.ai.generate'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                content_type: 'caption',
                context: aiContext.value,
                tone: 'friendly',
                include_emoji: true,
                include_hashtags: true,
                include_cta: true,
            }),
        });
        const data = await response.json();
        if (data.success) {
            form.caption = data.content;
            showAiGenerator.value = false;
            aiContext.value = '';
        }
    } catch {
        // silent fail
    } finally {
        aiGenerating.value = false;
    }
};

const submit = () => {
    form.status = form.scheduled_at ? 'scheduled' : 'draft';
    form.post(route('bizboost.posts.store'), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Post - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <Link
                    :href="campaign ? route('bizboost.campaigns.show', campaign.id) : route('bizboost.posts.index')"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6 transition-colors"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    {{ campaign ? 'Back to Campaign' : 'Back to Posts' }}
                </Link>

                <!-- Campaign indicator -->
                <div
                    v-if="campaign"
                    class="mb-4 p-4 bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-700 rounded-xl flex items-center gap-3"
                >
                    <RocketLaunchIcon class="h-5 w-5 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                    <span class="text-sm text-violet-700 dark:text-violet-300">
                        Creating post for campaign: <strong>{{ campaign.name }}</strong>
                    </span>
                </div>

                <!-- Template indicator -->
                <div
                    v-if="template"
                    class="mb-4 p-4 bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-700 rounded-xl flex items-center gap-3"
                >
                    <SparklesIcon class="h-5 w-5 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                    <span class="text-sm text-violet-700 dark:text-violet-300">
                        Using template: <strong>{{ template.name }}</strong>
                    </span>
                </div>

                <Card>
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Post Content -->
                        <FormSection title="Post Content" description="Write your post title and caption" :icon="ChatBubbleLeftRightIcon">
                            <FormInput
                                v-model="form.title"
                                label="Title"
                                placeholder="Post title (optional)"
                                :error="form.errors.title"
                            />
                            <FormTextarea
                                v-model="form.caption"
                                label="Caption"
                                placeholder="Write your caption..."
                                :error="form.errors.caption"
                                :rows="5"
                                required
                                max-length="2200"
                            />
                            <div class="flex items-center justify-between">
                                <button
                                    type="button"
                                    @click="showAiGenerator = !showAiGenerator"
                                    class="inline-flex items-center gap-1.5 text-sm font-medium text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300 transition-colors"
                                >
                                    <SparklesIcon class="h-4 w-4" aria-hidden="true" />
                                    {{ showAiGenerator ? 'Close AI Generator' : 'Generate with AI' }}
                                </button>
                            </div>
                            <!-- AI Generator inline -->
                            <div v-if="showAiGenerator" class="bg-gradient-to-br from-violet-50 to-fuchsia-50 dark:from-violet-900/20 dark:to-fuchsia-900/10 rounded-xl p-4 space-y-3 border border-violet-200 dark:border-violet-700">
                                <p class="text-sm font-medium text-violet-700 dark:text-violet-300">Describe what you want to promote</p>
                                <textarea
                                    v-model="aiContext"
                                    rows="2"
                                    class="block w-full rounded-lg border border-violet-200 dark:border-violet-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none"
                                    placeholder="e.g., Our new summer collection with 20% discount..."
                                ></textarea>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="generateWithAi"
                                        :disabled="aiGenerating || !aiContext.trim()"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-fuchsia-500 rounded-lg hover:from-violet-700 hover:to-fuchsia-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm"
                                    >
                                        <svg v-if="aiGenerating" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                        </svg>
                                        <SparklesIcon v-else class="h-4 w-4" aria-hidden="true" />
                                        {{ aiGenerating ? 'Generating...' : 'Generate Caption' }}
                                    </button>
                                </div>
                            </div>
                        </FormSection>

                        <!-- Media Upload -->
                        <FormSection title="Media" description="Upload images or videos for your post" :icon="PhotoIcon">
                            <FormImageUpload
                                v-model="form.media"
                                :multiple="true"
                                accept="image/*,video/*"
                                :error="form.errors['media.0']"
                                hint="Max 10 files. Supported: JPEG, PNG, WebP, MP4"
                            />
                        </FormSection>

                        <!-- Platforms -->
                        <FormSection title="Target Platforms" description="Choose where to publish this post">
                            <div class="flex gap-6">
                                <FormCheckbox
                                    :model-value="form.platform_targets.includes('facebook')"
                                    label="Facebook"
                                    @update:model-value="togglePlatform('facebook')"
                                />
                                <FormCheckbox
                                    :model-value="form.platform_targets.includes('instagram')"
                                    label="Instagram"
                                    @update:model-value="togglePlatform('instagram')"
                                />
                            </div>
                        </FormSection>

                        <!-- Schedule -->
                        <FormSection v-if="canSchedule" title="Schedule" description="Set a future date to publish" :icon="CalendarDaysIcon">
                            <FormInput
                                v-model="form.scheduled_at"
                                label="Schedule Date & Time"
                                type="datetime-local"
                                hint="Leave empty to save as draft"
                                :error="form.errors.scheduled_at"
                            />
                        </FormSection>

                        <FormActions
                            :submit-label="form.scheduled_at ? 'Schedule Post' : 'Save as Draft'"
                            :cancel-href="campaign ? route('bizboost.campaigns.show', campaign.id) : route('bizboost.posts.index')"
                            :processing="form.processing"
                        />
                    </form>
                </Card>
            </div>
        </div>
    </BizBoostLayout>
</template>
