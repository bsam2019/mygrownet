<template>
    <div class="p-4 sm:p-6 space-y-6">
        <!-- Section Type Selection -->
        <div>
            <label class="block text-sm font-medium mb-2" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                What do you want to create?
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <button
                    v-for="section in sectionTypes"
                    :key="section.id"
                    @click="form.sectionType = section.id"
                    class="flex flex-col items-center gap-2 p-3 rounded-xl border transition-all"
                    :class="[
                        form.sectionType === section.id
                            ? 'border-purple-500 bg-purple-500/10'
                            : darkMode 
                                ? 'border-gray-700 hover:border-gray-600' 
                                : 'border-gray-200 hover:border-gray-300'
                    ]"
                >
                    <component 
                        :is="section.icon" 
                        class="w-6 h-6"
                        :class="form.sectionType === section.id 
                            ? 'text-purple-500' 
                            : darkMode ? 'text-gray-400' : 'text-gray-500'"
                        aria-hidden="true"
                    />
                    <span 
                        class="text-xs font-medium"
                        :class="form.sectionType === section.id 
                            ? 'text-purple-500' 
                            : darkMode ? 'text-gray-300' : 'text-gray-700'"
                    >
                        {{ section.label }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Business Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1.5" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                    Business Type
                </label>
                <select
                    v-model="form.businessType"
                    class="w-full px-3 py-2.5 rounded-lg border text-sm transition-colors focus:outline-none focus:ring-2"
                    :class="darkMode 
                        ? 'bg-gray-800 border-gray-700 text-white focus:border-purple-500 focus:ring-purple-500/20' 
                        : 'bg-white border-gray-200 focus:border-purple-500 focus:ring-purple-500/20'"
                >
                    <option v-for="opt in businessTypes" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                    Tone
                </label>
                <select
                    v-model="form.tone"
                    class="w-full px-3 py-2.5 rounded-lg border text-sm transition-colors focus:outline-none focus:ring-2"
                    :class="darkMode 
                        ? 'bg-gray-800 border-gray-700 text-white focus:border-purple-500 focus:ring-purple-500/20' 
                        : 'bg-white border-gray-200 focus:border-purple-500 focus:ring-purple-500/20'"
                >
                    <option v-for="opt in tones" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-medium mb-1.5" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                Tell us about your business
                <span class="font-normal opacity-60">(optional)</span>
            </label>
            <textarea
                v-model="form.description"
                rows="3"
                placeholder="Describe your business, target audience, unique selling points..."
                class="w-full px-3 py-2.5 rounded-lg border text-sm resize-none transition-colors focus:outline-none focus:ring-2"
                :class="darkMode 
                    ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500 focus:border-purple-500 focus:ring-purple-500/20' 
                    : 'bg-white border-gray-200 placeholder-gray-400 focus:border-purple-500 focus:ring-purple-500/20'"
            />
        </div>

        <!-- Generate Button -->
        <button
            @click="handleGenerate"
            :disabled="isLoading"
            class="w-full py-3 rounded-xl font-medium text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 shadow-lg shadow-purple-500/25"
        >
            <ArrowPathIcon v-if="isLoading" class="w-5 h-5 animate-spin" aria-hidden="true" />
            <SparklesIcon v-else class="w-5 h-5" aria-hidden="true" />
            {{ isLoading ? 'Generating...' : 'Generate Content' }}
        </button>

        <!-- Generated Content Preview -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
        >
            <div 
                v-if="generatedContent"
                class="p-4 rounded-xl border"
                :class="darkMode ? 'bg-gray-800/50 border-gray-700' : 'bg-gray-50 border-gray-200'"
            >
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-medium" :class="darkMode ? 'text-white' : 'text-gray-900'">
                        Generated Content
                    </h4>
                    <div class="flex items-center gap-2">
                        <button
                            @click="handleGenerate"
                            class="p-1.5 rounded-lg transition-colors"
                            :class="darkMode ? 'hover:bg-gray-700 text-gray-400' : 'hover:bg-gray-200 text-gray-500'"
                            aria-label="Regenerate"
                        >
                            <ArrowPathIcon class="w-4 h-4" aria-hidden="true" />
                        </button>
                    </div>
                </div>

                <div class="space-y-2 text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                    <div v-if="generatedContent.title">
                        <span class="text-xs font-medium opacity-60">Title:</span>
                        <p class="font-semibold">{{ generatedContent.title }}</p>
                    </div>
                    <div v-if="generatedContent.subtitle">
                        <span class="text-xs font-medium opacity-60">Subtitle:</span>
                        <p>{{ generatedContent.subtitle }}</p>
                    </div>
                    <div v-if="generatedContent.content">
                        <span class="text-xs font-medium opacity-60">Content:</span>
                        <p class="whitespace-pre-wrap">{{ generatedContent.content }}</p>
                    </div>
                    <div v-if="generatedContent.buttonText">
                        <span class="text-xs font-medium opacity-60">Button:</span>
                        <span class="ml-2 px-2 py-1 rounded text-xs bg-purple-500/20 text-purple-400">
                            {{ generatedContent.buttonText }}
                        </span>
                    </div>
                    <div v-if="generatedContent.items?.length">
                        <span class="text-xs font-medium opacity-60">Items:</span>
                        <ul class="mt-1 space-y-1">
                            <li v-for="(item, i) in generatedContent.items" :key="i" class="flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 mt-1.5 flex-shrink-0" />
                                <span>{{ item.title || item }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <button
                    @click="$emit('apply')"
                    class="mt-4 w-full py-2.5 rounded-lg font-medium text-white bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 transition-all flex items-center justify-center gap-2"
                >
                    <CheckIcon class="w-4 h-4" aria-hidden="true" />
                    Apply to Section
                </button>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { 
    SparklesIcon, 
    ArrowPathIcon, 
    CheckIcon,
    RocketLaunchIcon,
    UserGroupIcon,
    Cog6ToothIcon,
    StarIcon,
    MegaphoneIcon,
    PhoneIcon,
    ChatBubbleLeftRightIcon,
    QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';
import { businessTypeOptions, toneOptions } from '../../../composables/useAI';

const props = defineProps<{
    darkMode?: boolean;
    siteName: string;
    businessType?: string;
    isLoading: boolean;
    generatedContent: any;
}>();

const emit = defineEmits<{
    generate: [params: any];
    apply: [];
}>();

const sectionTypes = [
    { id: 'hero', label: 'Hero', icon: RocketLaunchIcon },
    { id: 'about', label: 'About', icon: UserGroupIcon },
    { id: 'services', label: 'Services', icon: Cog6ToothIcon },
    { id: 'features', label: 'Features', icon: StarIcon },
    { id: 'cta', label: 'CTA', icon: MegaphoneIcon },
    { id: 'contact', label: 'Contact', icon: PhoneIcon },
    { id: 'testimonials', label: 'Reviews', icon: ChatBubbleLeftRightIcon },
    { id: 'faq', label: 'FAQ', icon: QuestionMarkCircleIcon },
];

const businessTypes = businessTypeOptions;
const tones = toneOptions;

const form = reactive({
    sectionType: 'hero',
    businessType: props.businessType || 'services',
    tone: 'professional',
    description: '',
});

const handleGenerate = () => {
    emit('generate', {
        sectionType: form.sectionType,
        businessName: props.siteName,
        businessType: form.businessType,
        businessDescription: form.description,
        tone: form.tone,
    });
};
</script>
