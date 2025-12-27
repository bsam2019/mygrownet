<template>
    <div class="p-4 h-full flex flex-col">
        <!-- Tool Tabs - Compact -->
        <div class="flex gap-1 p-1 rounded-lg mb-4 flex-shrink-0" :class="darkMode ? 'bg-gray-800' : 'bg-gray-100'">
            <button
                v-for="tool in tools"
                :key="tool.id"
                @click="activeTool = tool.id"
                class="flex-1 flex items-center justify-center gap-1.5 px-2 py-1.5 text-xs font-medium rounded-md transition-all"
                :class="[
                    activeTool === tool.id
                        ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-sm'
                        : darkMode
                            ? 'text-gray-400 hover:text-gray-300'
                            : 'text-gray-600 hover:text-gray-900'
                ]"
            >
                <component :is="tool.icon" class="w-3.5 h-3.5" aria-hidden="true" />
                {{ tool.label }}
            </button>
        </div>

        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto min-h-0">
            <!-- Improve Text Tool -->
            <div v-if="activeTool === 'improve'" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                        Text to improve
                    </label>
                    <textarea
                        v-model="improveForm.text"
                        rows="3"
                        placeholder="Paste your text here..."
                        class="w-full px-3 py-2 rounded-lg border text-sm resize-none transition-colors focus:outline-none focus:ring-2"
                        :class="darkMode 
                            ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500 focus:border-purple-500 focus:ring-purple-500/20' 
                            : 'bg-white border-gray-200 placeholder-gray-400 focus:border-purple-500 focus:ring-purple-500/20'"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                            Style
                        </label>
                        <select
                            v-model="improveForm.style"
                            class="w-full px-2 py-2 rounded-lg border text-sm"
                            :class="darkMode ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-200'"
                        >
                            <option v-for="opt in styleOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                            Instruction
                        </label>
                        <input
                            v-model="improveForm.instruction"
                            type="text"
                            placeholder="e.g., shorter"
                            class="w-full px-2 py-2 rounded-lg border text-sm"
                            :class="darkMode ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-200'"
                        />
                    </div>
                </div>

                <button
                    @click="handleImprove"
                    :disabled="!improveForm.text.trim() || isLoading"
                    class="w-full py-2 rounded-lg font-medium text-sm text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 disabled:opacity-50 flex items-center justify-center gap-2"
                >
                    <ArrowPathIcon v-if="isLoading" class="w-4 h-4 animate-spin" aria-hidden="true" />
                    <SparklesIcon v-else class="w-4 h-4" aria-hidden="true" />
                    Improve
                </button>

                <ResultBox v-if="improveResult" :dark-mode="darkMode" :text="improveResult" @copy="copyText" />
            </div>

            <!-- Translate Tool -->
            <div v-if="activeTool === 'translate'" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                        Text to translate
                    </label>
                    <textarea
                        v-model="translateForm.text"
                        rows="3"
                        placeholder="Enter text..."
                        class="w-full px-3 py-2 rounded-lg border text-sm resize-none"
                        :class="darkMode ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-200'"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1.5" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                        Target language
                    </label>
                    <div class="grid grid-cols-4 gap-1.5">
                        <button
                            v-for="lang in languageOptions"
                            :key="lang.value"
                            @click="translateForm.language = lang.value"
                            class="px-2 py-1.5 text-xs rounded-lg border transition-colors"
                            :class="[
                                translateForm.language === lang.value
                                    ? 'bg-purple-600 border-purple-600 text-white'
                                    : darkMode
                                        ? 'bg-gray-800 border-gray-700 text-gray-300 hover:border-purple-500'
                                        : 'bg-white border-gray-200 hover:border-purple-500'
                            ]"
                        >
                            {{ lang.label }}
                        </button>
                    </div>
                </div>

                <button
                    @click="handleTranslate"
                    :disabled="!translateForm.text.trim() || isLoading"
                    class="w-full py-2 rounded-lg font-medium text-sm text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 disabled:opacity-50 flex items-center justify-center gap-2"
                >
                    <ArrowPathIcon v-if="isLoading" class="w-4 h-4 animate-spin" aria-hidden="true" />
                    <LanguageIcon v-else class="w-4 h-4" aria-hidden="true" />
                    Translate
                </button>

                <ResultBox v-if="translateResult" :dark-mode="darkMode" :text="translateResult" @copy="copyText" />
            </div>

            <!-- SEO Tool -->
            <div v-if="activeTool === 'seo'" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                        Page title
                    </label>
                    <input
                        v-model="seoForm.title"
                        type="text"
                        placeholder="Enter page title..."
                        class="w-full px-3 py-2 rounded-lg border text-sm"
                        :class="darkMode ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-200'"
                    />
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                        Content summary
                    </label>
                    <textarea
                        v-model="seoForm.content"
                        rows="2"
                        placeholder="Summarize your page..."
                        class="w-full px-3 py-2 rounded-lg border text-sm resize-none"
                        :class="darkMode ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-200'"
                    />
                </div>

                <button
                    @click="handleSEO"
                    :disabled="!seoForm.title.trim() || isLoading"
                    class="w-full py-2 rounded-lg font-medium text-sm text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 disabled:opacity-50 flex items-center justify-center gap-2"
                >
                    <ArrowPathIcon v-if="isLoading" class="w-4 h-4 animate-spin" aria-hidden="true" />
                    <MagnifyingGlassIcon v-else class="w-4 h-4" aria-hidden="true" />
                    Generate SEO
                </button>

                <div v-if="seoResult" class="space-y-2">
                    <div class="p-3 rounded-lg" :class="darkMode ? 'bg-gray-800' : 'bg-gray-50'">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Meta Description</span>
                            <span class="text-[10px]" :class="darkMode ? 'text-gray-500' : 'text-gray-400'">{{ seoResult.metaDescription?.length || 0 }}/155</span>
                        </div>
                        <p class="text-xs" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">{{ seoResult.metaDescription }}</p>
                    </div>
                    <div class="p-3 rounded-lg" :class="darkMode ? 'bg-gray-800' : 'bg-gray-50'">
                        <span class="text-xs font-medium block mb-1.5" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Keywords</span>
                        <div class="flex flex-wrap gap-1">
                            <span v-for="keyword in seoResult.keywords" :key="keyword" class="px-2 py-0.5 text-[10px] rounded-full" :class="darkMode ? 'bg-gray-700 text-gray-300' : 'bg-gray-200 text-gray-700'">{{ keyword }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colors Tool -->
            <div v-if="activeTool === 'colors'" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Business type</label>
                        <select v-model="colorsForm.businessType" class="w-full px-2 py-2 rounded-lg border text-sm" :class="darkMode ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-200'">
                            <option v-for="opt in businessTypeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">Mood</label>
                        <select v-model="colorsForm.mood" class="w-full px-2 py-2 rounded-lg border text-sm" :class="darkMode ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-200'">
                            <option value="professional">Professional</option>
                            <option value="modern">Modern</option>
                            <option value="playful">Playful</option>
                            <option value="elegant">Elegant</option>
                            <option value="bold">Bold</option>
                        </select>
                    </div>
                </div>

                <button
                    @click="handleColors"
                    :disabled="isLoading"
                    class="w-full py-2 rounded-lg font-medium text-sm text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 disabled:opacity-50 flex items-center justify-center gap-2"
                >
                    <ArrowPathIcon v-if="isLoading" class="w-4 h-4 animate-spin" aria-hidden="true" />
                    <SwatchIcon v-else class="w-4 h-4" aria-hidden="true" />
                    Suggest Colors
                </button>

                <div v-if="colorPalette" class="p-3 rounded-lg" :class="darkMode ? 'bg-gray-800' : 'bg-gray-50'">
                    <div class="grid grid-cols-5 gap-2 mb-3">
                        <div v-for="(color, name) in colorPalette" :key="name" class="text-center">
                            <div class="w-full aspect-square rounded-lg mb-1 border shadow-sm" :style="{ backgroundColor: color }" :class="darkMode ? 'border-gray-600' : 'border-gray-200'" />
                            <span class="text-[9px] capitalize block" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">{{ name }}</span>
                        </div>
                    </div>
                    <button @click="$emit('apply-colors', colorPalette)" class="w-full py-1.5 rounded-lg font-medium text-xs text-white bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 flex items-center justify-center gap-1.5">
                        <CheckIcon class="w-3.5 h-3.5" aria-hidden="true" />
                        Apply to Theme
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { SparklesIcon, ArrowPathIcon, CheckIcon, PencilSquareIcon, LanguageIcon, MagnifyingGlassIcon, SwatchIcon } from '@heroicons/vue/24/outline';
import { businessTypeOptions, styleOptions, languageOptions } from '../../../composables/useAI';
import ResultBox from './ResultBox.vue';

const props = defineProps<{
    darkMode?: boolean;
    isLoading: boolean;
    initialText?: string;
}>();

const emit = defineEmits<{
    improve: [params: any];
    translate: [params: any];
    seo: [title: string, content: string];
    colors: [businessType: string, mood: string];
    'apply-colors': [palette: any];
}>();

const tools = [
    { id: 'improve', label: 'Improve', icon: PencilSquareIcon },
    { id: 'translate', label: 'Translate', icon: LanguageIcon },
    { id: 'seo', label: 'SEO', icon: MagnifyingGlassIcon },
    { id: 'colors', label: 'Colors', icon: SwatchIcon },
];

const activeTool = ref('improve');

const improveForm = reactive({ text: '', style: 'professional', instruction: '' });
const translateForm = reactive({ text: '', language: 'bem' });
const seoForm = reactive({ title: '', content: '' });
const colorsForm = reactive({ businessType: 'services', mood: 'professional' });

const improveResult = ref<string | null>(null);
const translateResult = ref<string | null>(null);
const seoResult = ref<{ metaDescription: string; keywords: string[] } | null>(null);
const colorPalette = ref<any>(null);

const handleImprove = async () => {
    const result = await emit('improve', { text: improveForm.text, style: improveForm.style, instruction: improveForm.instruction });
    if (result) improveResult.value = result as unknown as string;
};

const handleTranslate = async () => {
    const result = await emit('translate', { text: translateForm.text, targetLanguage: translateForm.language });
    if (result) translateResult.value = result as unknown as string;
};

const handleSEO = async () => {
    const result = await emit('seo', seoForm.title, seoForm.content);
    if (result) seoResult.value = result as unknown as { metaDescription: string; keywords: string[] };
};

const handleColors = async () => {
    const result = await emit('colors', colorsForm.businessType, colorsForm.mood);
    if (result) colorPalette.value = result;
};

const copyText = (text: string) => {
    navigator.clipboard.writeText(text);
};

onMounted(() => {
    if (props.initialText) {
        improveForm.text = props.initialText;
        translateForm.text = props.initialText;
    }
});
</script>
