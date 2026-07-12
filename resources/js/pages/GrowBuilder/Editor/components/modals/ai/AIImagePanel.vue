<template>
    <div class="p-4 space-y-6">
        <!-- Tab Switcher -->
        <div class="flex gap-2 border-b border-gray-200 pb-3">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors"
                :class="activeTab === tab.id ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-500' : 'text-gray-500 hover:text-gray-700'"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Image Generation Tab -->
        <div v-if="activeTab === 'image'" class="space-y-4">
            <p class="text-sm text-gray-600">Describe the image you want to generate for your site.</p>
            <textarea
                v-model="imagePrompt"
                placeholder="e.g. A modern restaurant interior with warm lighting, African art on walls..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm min-h-[100px] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Width</label>
                    <select v-model="imageWidth" class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                        <option value="512">512px</option>
                        <option value="768">768px</option>
                        <option value="1024">1024px</option>
                        <option value="1536">1536px</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Height</label>
                    <select v-model="imageHeight" class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                        <option value="512">512px</option>
                        <option value="768">768px</option>
                        <option value="1024">1024px</option>
                        <option value="1536">1536px</option>
                    </select>
                </div>
            </div>

            <button
                @click="doGenerateImage"
                :disabled="!imagePrompt.trim() || generating"
                class="w-full py-2.5 bg-blue-600 text-white font-medium rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                {{ generating ? 'Generating...' : 'Generate Image' }}
            </button>
        </div>

        <!-- Logo Generation Tab -->
        <div v-if="activeTab === 'logo'" class="space-y-4">
            <p class="text-sm text-gray-600">Generate a professional logo for your business.</p>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Business Name</label>
                <input
                    v-model="logoBusinessName"
                    placeholder="e.g. Lusaka Fresh Foods"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Industry</label>
                <select v-model="logoIndustry" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                    <option value="restaurant">Restaurant / Food</option>
                    <option value="retail">Retail / Shop</option>
                    <option value="technology">Technology</option>
                    <option value="healthcare">Healthcare</option>
                    <option value="education">Education</option>
                    <option value="real_estate">Real Estate</option>
                    <option value="creative">Creative / Design</option>
                    <option value="professional_service">Professional Service</option>
                    <option value="fitness">Fitness / Wellness</option>
                    <option value="ngo">NGO / Non-Profit</option>
                    <option value="church">Church / Ministry</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Style</label>
                <select v-model="logoStyle" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                    <option value="minimalist">Minimalist</option>
                    <option value="modern">Modern</option>
                    <option value="classic">Classic</option>
                    <option value="playful">Playful / Colorful</option>
                    <option value="luxury">Luxury / Elegant</option>
                    <option value="handdrawn">Hand-drawn</option>
                </select>
            </div>

            <button
                @click="doGenerateLogo"
                :disabled="!logoBusinessName.trim() || generating"
                class="w-full py-2.5 bg-purple-600 text-white font-medium rounded-lg text-sm hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                {{ generating ? 'Generating...' : 'Generate Logo' }}
            </button>
        </div>

        <!-- Reference Import Tab -->
        <div v-if="activeTab === 'import'" class="space-y-4">
            <p class="text-sm text-gray-600">Import an existing website by URL. We'll analyze it and recreate it in GrowBuilder.</p>

            <div>
                <input
                    v-model="importUrl"
                    placeholder="https://example.com"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>

            <button
                @click="doAnalyzeImport"
                :disabled="!importUrl.trim() || importing"
                class="w-full py-2.5 bg-green-600 text-white font-medium rounded-lg text-sm hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                {{ importing ? 'Analyzing...' : 'Analyze & Import' }}
            </button>
        </div>

        <!-- Error Display -->
        <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-600">{{ error }}</p>
        </div>

        <!-- Results Display -->
        <div v-if="results.length > 0" class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-700">Generated Images</h3>
                <button @click="results = []" class="text-xs text-gray-400 hover:text-gray-600">Clear</button>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div v-for="(img, i) in results" :key="i" class="relative group">
                    <img :src="img.url" :alt="'Generated image ' + (i + 1)" class="w-full h-32 object-cover rounded-lg border border-gray-200" />
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors rounded-lg flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                        <button @click="useImage(img)" class="px-3 py-1 bg-white text-gray-800 text-xs font-medium rounded shadow hover:bg-gray-100">
                            Use
                        </button>
                        <button @click="downloadImage(img.url)" class="px-3 py-1 bg-white text-gray-800 text-xs font-medium rounded shadow hover:bg-gray-100">
                            Download
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import results -->
        <div v-if="importResult" class="p-4 bg-green-50 border border-green-200 rounded-lg space-y-2">
            <h3 class="text-sm font-medium text-green-800">Analysis Complete</h3>
            <p class="text-xs text-green-700">Found {{ importResult.suggested_pages?.length || 0 }} pages, {{ importResult.sections?.length || 0 }} sections</p>
            <div class="text-xs text-green-600 space-y-1">
                <p><strong>Business:</strong> {{ importResult.business_name || importResult.title }}</p>
                <p><strong>Type:</strong> {{ importResult.business_type }}</p>
                <p v-if="importResult.colors?.primary"><strong>Colors:</strong> {{ importResult.colors.primary }}, {{ importResult.colors.secondary }}</p>
            </div>
            <button @click="applyImport" class="mt-2 px-4 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700">
                Apply as New Site
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useImages } from '../../../composables/useImages'

const emit = defineEmits<{
    useImage: [url: string]
    importSite: [structure: any]
}>()

const { generating, error, generateImage, generateLogo, analyzeReferenceSite } = useImages()

const tabs = [
    { id: 'image', label: 'Generate Image' },
    { id: 'logo', label: 'Create Logo' },
    { id: 'import', label: 'Import Site' },
]
const activeTab = ref('image')

// Image generation
const imagePrompt = ref('')
const imageWidth = ref(1024)
const imageHeight = ref(1024)
const results = ref<Array<{ url: string; path: string; filename: string }>>([])

// Logo generation
const logoBusinessName = ref('')
const logoIndustry = ref('restaurant')
const logoStyle = ref('minimalist')

// Reference import
const importUrl = ref('')
const importing = ref(false)
const importResult = ref<any>(null)

async function doGenerateImage() {
    const images = await generateImage(imagePrompt.value, {
        width: imageWidth.value,
        height: imageHeight.value,
    })
    if (images) results.value.push(...images)
}

async function doGenerateLogo() {
    const images = await generateLogo(logoBusinessName.value, logoIndustry.value, logoStyle.value)
    if (images) results.value.push(...images)
}

async function doAnalyzeImport() {
    importing.value = true
    importResult.value = null
    try {
        const analysis = await analyzeReferenceSite(importUrl.value)
        if (analysis) importResult.value = analysis
    } finally {
        importing.value = false
    }
}

function useImage(img: { url: string }) {
    emit('useImage', img.url)
}

function downloadImage(url: string) {
    window.open(url, '_blank')
}

function applyImport() {
    emit('importSite', importResult.value)
}
</script>
