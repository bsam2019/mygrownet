<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import Card from '@/Components/BizBoost/UI/Card.vue';
import FormInput from '@/Components/BizBoost/Form/FormInput.vue';
import FormTextarea from '@/Components/BizBoost/Form/FormTextarea.vue';
import FormCheckbox from '@/Components/BizBoost/Form/FormCheckbox.vue';
import FormSection from '@/Components/BizBoost/Form/FormSection.vue';
import FormActions from '@/Components/BizBoost/Form/FormActions.vue';
import {
    ArrowLeftIcon,
    SparklesIcon,
    CalendarDaysIcon,
    ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline';

interface Media {
    id: number;
    path: string;
    type: string;
}

interface Post {
    id: number;
    title: string;
    caption: string;
    status: string;
    post_type: string;
    platform_targets: string[];
    scheduled_at: string | null;
    media: Media[];
}

interface Props {
    post: Post;
    canSchedule: boolean;
    canAutoPost: boolean;
    templates: { id: number; name: string }[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.post.title,
    caption: props.post.caption,
    post_type: props.post.post_type,
    platform_targets: props.post.platform_targets || [],
    scheduled_at: props.post.scheduled_at || '',
});

const showAiGenerator = ref(false);
const aiContext = ref('');
const aiGenerating = ref(false);

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
    form.put(route('bizboost.posts.update', props.post.id));
};
</script>

<template>
    <Head title="Edit Post - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <Link
                    :href="route('bizboost.posts.show', post.id)"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6 transition-colors"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Post
                </Link>

                <Card>
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Post Content -->
                        <FormSection title="Post Content" description="Edit your post title and caption" :icon="ChatBubbleLeftRightIcon">
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

                        <!-- Current Media -->
                        <div v-if="post.media.length > 0" class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Current Media</label>
                            <div class="flex gap-3 flex-wrap">
                                <div v-for="media in post.media" :key="media.id" class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-xl overflow-hidden ring-1 ring-gray-200 dark:ring-gray-600">
                                    <img :src="`/storage/${media.path}`" class="w-full h-full object-cover" />
                                </div>
                            </div>
                        </div>

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
                                :error="form.errors.scheduled_at"
                            />
                        </FormSection>

                        <FormActions
                            submit-label="Save Changes"
                            :cancel-href="route('bizboost.posts.show', post.id)"
                            :processing="form.processing"
                        />
                    </form>
                </Card>
            </div>
        </div>
    </BizBoostLayout>
</template>
