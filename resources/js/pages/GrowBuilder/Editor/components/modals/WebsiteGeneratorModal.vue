<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { XMarkIcon, SparklesIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const step = ref<'prompt' | 'generating' | 'preview' | 'refining'>('prompt');
const prompt = ref('');
const refinementPrompt = ref('');
const generatedWebsite = ref<any>(null);
const isGenerating = ref(false);
const isRefining = ref(false);
const error = ref('');
const refinementHistory = ref<Array<{prompt: string, response: string}>>([]);

const examplePrompts = [
    "I run a hair salon in Lusaka called Glam Beauty. We offer haircuts, braiding, weaving, and makeup services. We're open Monday to Saturday.",
    "We're a fitness gym in Kitwe with personal training, group classes, and nutrition coaching. Open 6am-9pm daily.",
    "I'm a freelance web developer offering website design, SEO, and digital marketing services in Zambia.",
    "Our restaurant serves traditional Zambian cuisine in Ndola. We specialize in nshima, village chicken, and ifisashi. Open for lunch and dinner.",
    "I'm a tutor offering Math, Science, and English lessons for Grade 7-12 students. ECZ exam preparation available.",
];

const generateWebsite = async () => {
    if (!prompt.value.trim()) {
        error.value = 'Please describe your business';
        return;
    }
    
    isGenerating.value = true;
    error.value = '';
    step.value = 'generating';
    
    try {
        const response = await axios.post('/growbuilder/ai/generate-website', {
            prompt: prompt.value,
        });
        
        if (response.data.success) {
            generatedWebsite.value = response.data;
            refinementHistory.value = [];
            step.value = 'preview';
        } else {
            throw new Error(response.data.error || 'Generation failed');
        }
    } catch (err: any) {
        console.error('Website generation error:', err);
        error.value = err.response?.data?.message || err.message || 'Failed to generate website. Please try again.';
        step.value = 'prompt';
    } finally {
        isGenerating.value = false;
    }
};

const refineWebsite = async () => {
    if (!refinementPrompt.value.trim()) {
        error.value = 'Please describe what you want to change';
        return;
    }
    
    isRefining.value = true;
    error.value = '';
    const userPrompt = refinementPrompt.value;
    
    try {
        const response = await axios.post('/growbuilder/ai/refine-website', {
            current_website: generatedWebsite.value,
            refinement_prompt: userPrompt,
        });
        
        if (response.data.success) {
            // Add to history
            refinementHistory.value.push({
                prompt: userPrompt,
                response: response.data.refinement_applied || 'Website updated',
            });
            
            // Update website
            generatedWebsite.value = response.data;
            
            // Clear input
            refinementPrompt.value = '';
        } else {
            throw new Error(response.data.error || 'Refinement failed');
        }
    } catch (err: any) {
        console.error('Website refinement error:', err);
        error.value = err.response?.data?.message || err.message || 'Failed to refine website. Please try again.';
    } finally {
        isRefining.value = false;
    }
};

const acceptWebsite = async () => {
    isGenerating.value = true;
    error.value = '';
    
    try {
        const response = await axios.post('/growbuilder/ai/publish-generated-website', {
            website: generatedWebsite.value,
        });
        
        if (response.data.success) {
            // Redirect to editor (site is created as draft)
            const editorUrl = response.data.editor_url;
            
            // Redirect to editor where user can preview and publish
            window.location.href = editorUrl + '?ai_generated=true';
        } else {
            throw new Error(response.data.error || 'Site creation failed');
        }
    } catch (err: any) {
        console.error('Website creation error:', err);
        error.value = err.response?.data?.message || err.message || 'Failed to create website. Please try again.';
        isGenerating.value = false;
    }
};

const useExample = (example: string) => {
    prompt.value = example;
};

const totalSections = computed(() => {
    if (!generatedWebsite.value?.pages) return 0;
    return generatedWebsite.value.pages.reduce((total: number, page: any) => {
        return total + (page.sections?.length || 0);
    }, 0);
});
</script>

<template>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-600 rounded-lg">
                        <SparklesIcon class="w-6 h-6 text-white" aria-hidden="true" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            AI Website Generator
                        </h2>
                        <p class="text-sm text-gray-600 mt-0.5">
                            Describe your business and we'll build your website in seconds
                        </p>
                    </div>
                </div>
                <button 
                    @click="emit('close')" 
                    class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-white/50 rounded-lg"
                    aria-label="Close website generator modal"
                >
                    <XMarkIcon class="w-6 h-6" aria-hidden="true" />
                </button>
            </div>
            
            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- Step 1: Prompt -->
                <div v-if="step === 'prompt'" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Describe your business
                        </label>
                        <textarea
                            v-model="prompt"
                            rows="6"
                            placeholder="Tell us about your business: What do you do? Where are you located? What services do you offer? What makes you special?"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-gray-900 placeholder-gray-400"
                        />
                        <p class="mt-2 text-sm text-gray-500">
                            Include: business name, type, location, services/products, and what makes you unique
                        </p>
                    </div>
                    
                    <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm flex items-start gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ error }}</span>
                    </div>
                    
                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-3">
                            Or try an example:
                        </p>
                        <div class="grid gap-2">
                            <button
                                v-for="(example, index) in examplePrompts"
                                :key="index"
                                @click="useExample(example)"
                                class="text-left px-4 py-3 bg-gray-50 hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-lg text-sm text-gray-700 transition-all"
                            >
                                {{ example }}
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Generating -->
                <div v-else-if="step === 'generating'" class="flex flex-col items-center justify-center py-16">
                    <div class="relative">
                        <div class="w-20 h-20 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                        <SparklesIcon class="w-8 h-8 text-blue-600 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2" aria-hidden="true" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2 mt-6">
                        Creating your website...
                    </h3>
                    <p class="text-gray-600 text-center max-w-md">
                        Our AI is analyzing your business, selecting the perfect template, 
                        and generating professional content. This will take about 30 seconds.
                    </p>
                    <div class="mt-6 flex items-center gap-2 text-sm text-gray-500">
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></div>
                        <span>Analyzing business type...</span>
                    </div>
                </div>
                
                <!-- Step 3: Preview -->
                <div v-else-if="step === 'preview' && generatedWebsite" class="space-y-6">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <CheckCircleIcon class="w-6 h-6 text-green-600 flex-shrink-0" aria-hidden="true" />
                            <div>
                                <h4 class="font-semibold text-green-900">Website Generated Successfully!</h4>
                                <p class="text-sm text-green-700 mt-1">
                                    We've created {{ generatedWebsite.pages.length }} pages with {{ totalSections }} sections of professional content.
                                    Review the pages below, then click "Create Website" to open the editor where you can preview and customize before publishing.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Business Analysis -->
                    <div v-if="generatedWebsite.analysis" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Business Analysis</h4>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-blue-700 font-medium">Name:</span>
                                <span class="text-blue-900 ml-2">{{ generatedWebsite.analysis.business_name }}</span>
                            </div>
                            <div>
                                <span class="text-blue-700 font-medium">Type:</span>
                                <span class="text-blue-900 ml-2">{{ generatedWebsite.analysis.business_type }}</span>
                            </div>
                            <div>
                                <span class="text-blue-700 font-medium">Location:</span>
                                <span class="text-blue-900 ml-2">{{ generatedWebsite.analysis.location }}</span>
                            </div>
                            <div v-if="generatedWebsite.analysis.phone">
                                <span class="text-blue-700 font-medium">Phone:</span>
                                <span class="text-blue-900 ml-2">{{ generatedWebsite.analysis.phone }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pages Preview -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900">Generated Pages</h4>
                        <div v-for="page in generatedWebsite.pages" :key="page.slug" class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h5 class="font-semibold text-gray-900">{{ page.name }}</h5>
                                    <p class="text-sm text-gray-600">{{ page.sections.length }} sections</p>
                                </div>
                                <span v-if="page.is_home" class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded">
                                    Home Page
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span 
                                    v-for="(section, index) in page.sections" 
                                    :key="index" 
                                    class="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full"
                                >
                                    {{ section.type }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                        {{ error }}
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between bg-gray-50">
                <button
                    v-if="step === 'prompt'"
                    @click="emit('close')"
                    class="px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition-colors"
                >
                    Cancel
                </button>
                <button
                    v-else-if="step === 'preview'"
                    @click="step = 'prompt'"
                    class="px-4 py-2 text-gray-700 hover:bg-gray-200 rounded-lg transition-colors"
                >
                    ← Back
                </button>
                <div v-else></div>
                
                <button
                    v-if="step === 'prompt'"
                    @click="generateWebsite"
                    :disabled="!prompt.trim() || isGenerating"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium flex items-center gap-2"
                >
                    <SparklesIcon class="w-5 h-5" aria-hidden="true" />
                    Generate Website
                </button>
                <button
                    v-else-if="step === 'preview'"
                    @click="acceptWebsite"
                    :disabled="isGenerating"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium flex items-center gap-2"
                >
                    <template v-if="!isGenerating">
                        <CheckCircleIcon class="w-5 h-5" aria-hidden="true" />
                        Create Website
                    </template>
                    <template v-else>
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creating...
                    </template>
                </button>
            </div>
        </div>
    </div>
</template>
