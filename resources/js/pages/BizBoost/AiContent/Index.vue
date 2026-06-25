<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import Card from '@/Components/BizBoost/UI/Card.vue';
import Button from '@/Components/BizBoost/UI/Button.vue';
import FormInput from '@/Components/BizBoost/Form/FormInput.vue';
import FormTextarea from '@/Components/BizBoost/Form/FormTextarea.vue';
import FormSelect from '@/Components/BizBoost/Form/FormSelect.vue';
import FormCheckbox from '@/Components/BizBoost/Form/FormCheckbox.vue';
import {
    SparklesIcon,
    ClipboardDocumentIcon,
    LockClosedIcon,
} from '@heroicons/vue/24/outline';

interface Generation {
    id: number;
    content_type: string;
    prompt: string;
    response: string;
    created_at: string;
}

interface Props {
    hasFeature: boolean;
    canUseAi: { allowed: boolean; remaining: number; reason?: string };
    recentGenerations: Generation[];
    contentTypes: Record<string, string>;
    industries: Record<string, unknown>;
    business: {
        name: string;
        industry: string;
    };
}

const props = defineProps<Props>();

const selectedType = ref('caption');
const context = ref('');
const tone = ref('friendly');
const language = ref('en');
const productName = ref('');
const includeEmoji = ref(true);
const includeHashtags = ref(true);
const includeCta = ref(true);
const generatedContent = ref('');
const isGenerating = ref(false);
const error = ref('');

const tones = [
    { value: 'professional', label: 'Professional' },
    { value: 'casual', label: 'Casual' },
    { value: 'friendly', label: 'Friendly' },
    { value: 'urgent', label: 'Urgent' },
    { value: 'playful', label: 'Playful' },
];

const languages = [
    { value: 'en', label: 'English' },
    { value: 'bem', label: 'Bemba' },
    { value: 'nya', label: 'Nyanja' },
    { value: 'ton', label: 'Tonga' },
    { value: 'loz', label: 'Lozi' },
];

const contentTypeOptions = computed(() =>
    Object.entries(props.contentTypes).map(([value, label]) => ({ value, label }))
);

const toneOptions = tones.map(t => ({ value: t.value, label: t.label }));
const languageOptions = languages.map(l => ({ value: l.value, label: l.label }));

const copyToClipboard = () => {
    navigator.clipboard.writeText(generatedContent.value);
};

const generate = async () => {
    if (!context.value.trim()) {
        error.value = 'Please provide some context for the content.';
        return;
    }
    isGenerating.value = true;
    error.value = '';
    try {
        const response = await fetch(route('bizboost.ai.generate'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                content_type: selectedType.value,
                context: context.value,
                tone: tone.value,
                language: language.value,
                product_name: productName.value,
                include_emoji: includeEmoji.value,
                include_hashtags: includeHashtags.value,
                include_cta: includeCta.value,
            }),
        });
        const data = await response.json();
        if (data.success) {
            generatedContent.value = data.content;
        } else {
            error.value = data.error || 'Failed to generate content.';
        }
    } catch {
        error.value = 'An error occurred. Please try again.';
    } finally {
        isGenerating.value = false;
    }
};
</script>

<template>
    <Head title="AI Content Generator - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <SparklesIcon class="h-7 w-7 text-violet-600" aria-hidden="true" />
                        AI Content Generator
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Generate marketing content powered by AI</p>
                </div>

                <!-- Feature Gate -->
                <Card v-if="!hasFeature" variant="bordered" padding="lg">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-violet-100 dark:bg-violet-900/30 rounded-xl ring-1 ring-violet-200/50 dark:ring-violet-700/30">
                            <LockClosedIcon class="h-8 w-8 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upgrade to Access AI Content</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">AI content generation is available on Professional and Business plans.</p>
                            <Link :href="route('bizboost.subscription.index')" class="mt-2 inline-block text-sm font-medium text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300">
                                Upgrade Now →
                            </Link>
                        </div>
                    </div>
                </Card>

                <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Generator Form -->
                    <Card>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Generate Content</h2>
                        <div class="space-y-4">
                            <FormSelect
                                v-model="selectedType"
                                label="Content Type"
                                :options="contentTypeOptions"
                            />
                            <FormTextarea
                                v-model="context"
                                label="Context / Description"
                                placeholder="Describe what you want to promote or communicate..."
                                :rows="3"
                            />
                            <div class="grid grid-cols-2 gap-4">
                                <FormSelect
                                    v-model="tone"
                                    label="Tone"
                                    :options="toneOptions"
                                />
                                <FormSelect
                                    v-model="language"
                                    label="Language"
                                    :options="languageOptions"
                                />
                            </div>
                            <FormInput
                                v-model="productName"
                                label="Product/Service Name (optional)"
                                placeholder="e.g., Summer Collection"
                            />
                            <div class="flex flex-wrap gap-4">
                                <FormCheckbox
                                    v-model="includeEmoji"
                                    label="Include Emojis"
                                />
                                <FormCheckbox
                                    v-model="includeHashtags"
                                    label="Include Hashtags"
                                />
                                <FormCheckbox
                                    v-model="includeCta"
                                    label="Include CTA"
                                />
                            </div>
                            <p v-if="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
                            <Button
                                type="button"
                                full-width
                                :loading="isGenerating"
                                :disabled="isGenerating || !canUseAi.allowed"
                                @click="generate"
                            >
                                <SparklesIcon class="h-5 w-5" aria-hidden="true" />
                                {{ isGenerating ? 'Generating...' : 'Generate Content' }}
                            </Button>
                            <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                                Credits remaining: {{ canUseAi.remaining ?? 0 }}
                            </p>
                        </div>
                    </Card>

                    <!-- Generated Content -->
                    <div class="space-y-6">
                        <Card>
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Generated Content</h2>
                                <Button
                                    v-if="generatedContent"
                                    variant="ghost"
                                    size="sm"
                                    @click="copyToClipboard"
                                >
                                    <ClipboardDocumentIcon class="h-4 w-4" aria-hidden="true" />
                                    Copy
                                </Button>
                            </div>
                            <div v-if="generatedContent" class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 whitespace-pre-wrap text-gray-800 dark:text-gray-200 text-sm leading-relaxed">
                                {{ generatedContent }}
                            </div>
                            <div v-else class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-8 text-center">
                                <SparklesIcon class="h-12 w-12 mx-auto mb-2 text-gray-300 dark:text-gray-600" aria-hidden="true" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">Your generated content will appear here</p>
                            </div>
                        </Card>

                        <!-- Recent Generations -->
                        <Card v-if="recentGenerations.length > 0">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Recent Generations</h3>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                <div
                                    v-for="gen in recentGenerations"
                                    :key="gen.id"
                                    class="p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors"
                                    @click="generatedContent = gen.response"
                                >
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1 capitalize">{{ gen.content_type }}</div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2">{{ gen.response }}</div>
                                </div>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
