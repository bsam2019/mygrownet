<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    ArrowLeftIcon,
    SparklesIcon,
    CheckCircleIcon,
    ArrowDownTrayIcon,
    ShoppingBagIcon,
    ScissorsIcon,
    CakeIcon,
    ShoppingCartIcon,
    WrenchIcon,
    CameraIcon,
    DevicePhoneMobileIcon,
    ComputerDesktopIcon,
    BuildingStorefrontIcon,
    LightBulbIcon,
    DocumentDuplicateIcon,
} from '@heroicons/vue/24/outline';
import { ref, computed, type Component } from 'vue';

interface Template {
    id: number;
    name: string;
    description: string;
    preview_path: string | null;
    category: string;
}

interface IndustryKit {
    id: string;
    name: string;
    description: string;
    icon: string;
    color: string;
    features: string[];
    sample_posts?: string[];
}

interface Business {
    id: number;
    industry: string;
}

interface Props {
    kit: IndustryKit;
    templates: Template[];
    business: Business;
}

const props = defineProps<Props>();
const isApplying = ref(false);

// Map icon names to actual components
const iconMap: Record<string, Component> = {
    ShoppingBagIcon,
    SparklesIcon,
    ScissorsIcon,
    CakeIcon,
    ShoppingCartIcon,
    WrenchIcon,
    CameraIcon,
    DevicePhoneMobileIcon,
    ComputerDesktopIcon,
    BuildingStorefrontIcon,
};

// Map color names to Tailwind classes
const colorMap: Record<string, { bg: string; text: string; gradient: string; light: string }> = {
    pink: { bg: 'bg-pink-600', text: 'text-pink-600', gradient: 'from-pink-500 to-rose-600', light: 'bg-pink-100' },
    purple: { bg: 'bg-purple-600', text: 'text-purple-600', gradient: 'from-purple-500 to-violet-600', light: 'bg-purple-100' },
    blue: { bg: 'bg-blue-600', text: 'text-blue-600', gradient: 'from-blue-500 to-indigo-600', light: 'bg-blue-100' },
    orange: { bg: 'bg-orange-600', text: 'text-orange-600', gradient: 'from-orange-500 to-amber-600', light: 'bg-orange-100' },
    green: { bg: 'bg-green-600', text: 'text-green-600', gradient: 'from-green-500 to-emerald-600', light: 'bg-green-100' },
    yellow: { bg: 'bg-amber-600', text: 'text-amber-600', gradient: 'from-amber-500 to-yellow-600', light: 'bg-amber-100' },
    indigo: { bg: 'bg-indigo-600', text: 'text-indigo-600', gradient: 'from-indigo-500 to-purple-600', light: 'bg-indigo-100' },
    emerald: { bg: 'bg-emerald-600', text: 'text-emerald-600', gradient: 'from-emerald-500 to-teal-600', light: 'bg-emerald-100' },
    cyan: { bg: 'bg-cyan-600', text: 'text-cyan-600', gradient: 'from-cyan-500 to-blue-600', light: 'bg-cyan-100' },
    gray: { bg: 'bg-gray-600', text: 'text-gray-600', gradient: 'from-gray-500 to-slate-600', light: 'bg-gray-100' },
};

const getIcon = (iconName: string): Component => {
    return iconMap[iconName] || BuildingStorefrontIcon;
};

const getColorClasses = (color: string) => {
    return colorMap[color] || colorMap.gray;
};

const isCurrentKit = computed(() => props.business.industry === props.kit.id);

const applyKit = () => {
    if (isCurrentKit.value) return;
    
    isApplying.value = true;
    router.post(route('bizboost.industry-kits.apply', props.kit.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Page will reload with updated data
        },
        onError: (errors) => {
            console.error('Failed to apply kit:', errors);
        },
        onFinish: () => {
            isApplying.value = false;
        },
    });
};

const useTemplate = (templateId: number) => {
    router.post(route('bizboost.templates.use', templateId));
};
</script>

<template>
    <Head :title="`${kit.name} - BizBoost`" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Back Link -->
                <div class="mb-6">
                    <Link 
                        :href="route('bizboost.industry-kits.index')" 
                        class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors group"
                    >
                        <ArrowLeftIcon class="h-4 w-4 group-hover:-translate-x-1 transition-transform" aria-hidden="true" />
                        <span>Back to Industry Kits</span>
                    </Link>
                </div>

                <!-- Kit Header Card -->
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden mb-8">
                    <!-- Gradient Header -->
                    <div :class="['h-2 bg-gradient-to-r', getColorClasses(kit.color).gradient]"></div>
                    
                    <div class="p-6 lg:p-8">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                            <div class="flex items-start gap-5">
                                <div 
                                    :class="[
                                        'w-16 h-16 lg:w-20 lg:h-20 rounded-2xl flex items-center justify-center shadow-lg',
                                        getColorClasses(kit.color).light
                                    ]"
                                >
                                    <component 
                                        :is="getIcon(kit.icon)" 
                                        :class="['h-8 w-8 lg:h-10 lg:w-10', getColorClasses(kit.color).text]"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ kit.name }}</h1>
                                        <span 
                                            v-if="isCurrentKit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-medium rounded-full"
                                        >
                                            <CheckCircleIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                            Active
                                        </span>
                                    </div>
                                    <p class="text-gray-600 text-lg">{{ kit.description }}</p>
                                </div>
                            </div>
                            
                            <button
                                @click="applyKit"
                                :disabled="isApplying || isCurrentKit"
                                :class="[
                                    'flex-shrink-0 px-6 py-3 rounded-xl font-medium flex items-center gap-2 transition-all shadow-lg',
                                    isCurrentKit 
                                        ? 'bg-gray-100 text-gray-500 cursor-not-allowed shadow-none' 
                                        : `bg-gradient-to-r ${getColorClasses(kit.color).gradient} text-white hover:shadow-xl hover:-translate-y-0.5`
                                ]"
                            >
                                <ArrowDownTrayIcon v-if="!isApplying" class="h-5 w-5" aria-hidden="true" />
                                <svg v-else class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ isCurrentKit ? 'Currently Active' : isApplying ? 'Applying...' : 'Apply This Kit' }}
                            </button>
                        </div>

                        <!-- Features & Sample Posts Grid -->
                        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Features -->
                            <div class="bg-gray-50 rounded-xl p-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <CheckCircleIcon :class="['h-5 w-5', getColorClasses(kit.color).text]" aria-hidden="true" />
                                    <h3 class="font-semibold text-gray-900">What's Included</h3>
                                </div>
                                <ul class="space-y-3">
                                    <li 
                                        v-for="feature in kit.features" 
                                        :key="feature" 
                                        class="flex items-start gap-3 text-sm text-gray-700"
                                    >
                                        <CheckCircleIcon class="h-4 w-4 text-emerald-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                        <span>{{ feature }}</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Sample Posts -->
                            <div v-if="kit.sample_posts?.length" class="bg-gray-50 rounded-xl p-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <LightBulbIcon :class="['h-5 w-5', getColorClasses(kit.color).text]" aria-hidden="true" />
                                    <h3 class="font-semibold text-gray-900">Sample Post Ideas</h3>
                                </div>
                                <ul class="space-y-3">
                                    <li 
                                        v-for="(post, index) in kit.sample_posts" 
                                        :key="index" 
                                        class="p-3 bg-white rounded-lg border border-gray-200 text-sm text-gray-700 italic"
                                    >
                                        "{{ post }}"
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Templates Section -->
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
                    <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-violet-100 rounded-lg">
                                <DocumentDuplicateIcon class="h-5 w-5 text-violet-600" aria-hidden="true" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Included Templates</h2>
                                <p class="text-sm text-gray-500">{{ templates.length }} templates ready to use</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Templates Grid -->
                    <div v-if="templates.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 p-5">
                        <div
                            v-for="template in templates"
                            :key="template.id"
                            class="group bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-violet-300 transition-all"
                        >
                            <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-50 relative overflow-hidden">
                                <img
                                    v-if="template.preview_path"
                                    :src="`/storage/${template.preview_path}`"
                                    :alt="template.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <SparklesIcon class="h-12 w-12 text-gray-300" aria-hidden="true" />
                                </div>
                            </div>
                            <div class="p-4">
                                <h4 class="font-medium text-gray-900 mb-1">{{ template.name }}</h4>
                                <p class="text-sm text-gray-500 mb-3 capitalize">{{ template.category }}</p>
                                <button
                                    @click="useTemplate(template.id)"
                                    class="w-full px-4 py-2 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700 transition-colors"
                                >
                                    Use Template
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <DocumentDuplicateIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Templates Yet</h3>
                        <p class="text-gray-500 max-w-md mx-auto">
                            Templates for this industry kit are being prepared. Check back soon!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
