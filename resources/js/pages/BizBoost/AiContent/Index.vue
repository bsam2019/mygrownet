<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { SparklesIcon, ClipboardDocumentIcon, ArrowPathIcon, LockClosedIcon } from '@heroicons/vue/24/outline';

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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
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
    } catch (e) {
        error.value = 'An error occurred. Please try again.';
    } finally {
        isGenerating.value = false;
    }
};

const copyToClipboard = () => {
    navigator.clipboard.writeText(generatedContent.value);
};
</script>

<template>
    <Head title="AI Content Generator - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <SparklesIcon class="h-7 w-7 text-purple-600" aria-hidden="true" />
                        AI Content Generator
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Generate marketing content powered by AI</p>
                </div>

                <!-- Feature Gate -->
                <div v-if="!hasFeature" class="bg-purple-50 border border-purple-200 rounded-lg p-6 mb-6">
                    <div class="flex items-center gap-4">
                        <LockClosedIcon class="h-10 w-10 text-purple-600" aria-hidden="true" />
                        <div>
                            <h3 class="text-lg font-semibold text-purple-900">Upgrade to Access AI Content</h3>
                            <p class="text-purple-700">AI content generation is available on Professional and Business plans.</p>
                            <Link :href="route('bizboost.subscription.index')" class="mt-2 inline-block text-purple-600 hover:text-purple-800 font-medium">
                                Upgrade Now →
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Generator Form -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Generate Content</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Content Type</label>
                                <select v-model="selectedType" class="w-full rounded-md border-gray-300">
                                    <option v-for="(label, key) in contentTypes" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Context / Description</label>
                                <textarea
                                    v-model="context"
                                    rows="3"
                                    class="w-full rounded-md border-gray-300"
                                    placeholder="Describe what you want to promote or communicate..."
                                ></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tone</label>
                                    <select v-model="tone" class="w-full rounded-md border-gray-300">
                                        <option v-for="t in tones" :key="t.value" :value="t.value">{{ t.label }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                                    <select v-model="language" class="w-full rounded-md border-gray-300">
                                        <option v-for="l in languages" :key="l.value" :value="l.value">{{ l.label }}</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product/Service Name (optional)</label>
                                <input v-model="productName" type="text" class="w-full rounded-md border-gray-300" placeholder="e.g., Summer Collection" />
                            </div>

                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-2">
                                    <input v-model="includeEmoji" type="checkbox" class="rounded border-gray-300 text-blue-600" />
                                    <span class="text-sm text-gray-700">Include Emojis</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input v-model="includeHashtags" type="checkbox" class="rounded border-gray-300 text-blue-600" />
                                    <span class="text-sm text-gray-700">Include Hashtags</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input v-model="includeCta" type="checkbox" class="rounded border-gray-300 text-blue-600" />
                                    <span class="text-sm text-gray-700">Include CTA</span>
                                </label>
                            </div>

                            <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

                            <button
                                @click="generate"
                                :disabled="isGenerating || !canUseAi.allowed"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50"
                            >
                                <ArrowPathIcon v-if="isGenerating" class="h-5 w-5 animate-spin" aria-hidden="true" />
                                <SparklesIcon v-else class="h-5 w-5" aria-hidden="true" />
                                {{ isGenerating ? 'Generating...' : 'Generate Content' }}
                            </button>

                            <p class="text-sm text-gray-500 text-center">
                                Credits remaining: {{ canUseAi.remaining ?? 0 }}
                            </p>
                        </div>
                    </div>

                    <!-- Generated Content -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Generated Content</h2>
                            <button
                                v-if="generatedContent"
                                @click="copyToClipboard"
                                class="flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800"
                            >
                                <ClipboardDocumentIcon class="h-4 w-4" aria-hidden="true" />
                                Copy
                            </button>
                        </div>

                        <div v-if="generatedContent" class="bg-gray-50 rounded-lg p-4 whitespace-pre-wrap text-gray-800">
                            {{ generatedContent }}
                        </div>
                        <div v-else class="bg-gray-50 rounded-lg p-8 text-center text-gray-500">
                            <SparklesIcon class="h-12 w-12 mx-auto mb-2 text-gray-300" aria-hidden="true" />
                            <p>Your generated content will appear here</p>
                        </div>

                        <!-- Recent Generations -->
                        <div v-if="recentGenerations.length > 0" class="mt-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Recent Generations</h3>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                <div
                                    v-for="gen in recentGenerations"
                                    :key="gen.id"
                                    class="p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100"
                                    @click="generatedContent = gen.response"
                                >
                                    <div class="text-xs text-gray-500 mb-1">{{ gen.content_type }}</div>
                                    <div class="text-sm text-gray-700 line-clamp-2">{{ gen.response }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
