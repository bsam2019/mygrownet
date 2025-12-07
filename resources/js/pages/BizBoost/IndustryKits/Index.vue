<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    SparklesIcon,
    CheckCircleIcon,
    ArrowRightIcon,
    ShoppingBagIcon,
    ScissorsIcon,
    CakeIcon,
    ShoppingCartIcon,
    WrenchIcon,
    CameraIcon,
    DevicePhoneMobileIcon,
    ComputerDesktopIcon,
    BuildingStorefrontIcon,
    LockClosedIcon,
} from '@heroicons/vue/24/outline';
import { computed, type Component } from 'vue';

interface IndustryKit {
    id: string;
    name: string;
    description: string;
    icon: string;
    color: string;
    template_count: number;
    features: string[];
}

interface Props {
    industryKits: IndustryKit[];
    currentIndustry: string | null;
    hasAccess?: boolean;
}

const props = defineProps<Props>();

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
const colorMap: Record<string, { bg: string; text: string; ring: string }> = {
    pink: { bg: 'bg-pink-100', text: 'text-pink-600', ring: 'ring-pink-500' },
    purple: { bg: 'bg-purple-100', text: 'text-purple-600', ring: 'ring-purple-500' },
    blue: { bg: 'bg-blue-100', text: 'text-blue-600', ring: 'ring-blue-500' },
    orange: { bg: 'bg-orange-100', text: 'text-orange-600', ring: 'ring-orange-500' },
    green: { bg: 'bg-green-100', text: 'text-green-600', ring: 'ring-green-500' },
    yellow: { bg: 'bg-amber-100', text: 'text-amber-600', ring: 'ring-amber-500' },
    indigo: { bg: 'bg-indigo-100', text: 'text-indigo-600', ring: 'ring-indigo-500' },
    emerald: { bg: 'bg-emerald-100', text: 'text-emerald-600', ring: 'ring-emerald-500' },
    cyan: { bg: 'bg-cyan-100', text: 'text-cyan-600', ring: 'ring-cyan-500' },
    gray: { bg: 'bg-gray-100', text: 'text-gray-600', ring: 'ring-gray-500' },
};

const getIcon = (iconName: string): Component => {
    return iconMap[iconName] || BuildingStorefrontIcon;
};

const getColorClasses = (color: string) => {
    return colorMap[color] || colorMap.gray;
};

const selectKit = (kitId: string) => {
    router.get(route('bizboost.industry-kits.show', kitId));
};
</script>

<template>
    <Head title="Industry Kits - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2.5 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl shadow-lg shadow-violet-500/25">
                            <SparklesIcon class="h-6 w-6 text-white" aria-hidden="true" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Industry Kits</h1>
                            <p class="text-sm text-gray-500">Pre-built templates and content for your industry</p>
                        </div>
                    </div>
                </div>

                <!-- Current Industry Banner -->
                <div 
                    v-if="currentIndustry" 
                    class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl p-4 mb-8 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-100 rounded-lg">
                            <CheckCircleIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                        </div>
                        <div>
                            <span class="text-emerald-800 font-medium">Current industry: </span>
                            <span class="text-emerald-900 font-semibold capitalize">{{ currentIndustry.replace('_', ' ') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Access Warning -->
                <div 
                    v-if="hasAccess === false" 
                    class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-4 mb-8 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-lg">
                            <LockClosedIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                        </div>
                        <div>
                            <span class="text-amber-800 font-medium">Upgrade to unlock Industry Kits</span>
                            <p class="text-sm text-amber-600">Get access to specialized templates for your business type</p>
                        </div>
                    </div>
                </div>

                <!-- Industry Kits Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="kit in industryKits"
                        :key="kit.id"
                        :class="[
                            'group bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden cursor-pointer transition-all duration-300 hover:shadow-xl hover:ring-2 hover:-translate-y-1',
                            currentIndustry === kit.id ? `ring-2 ${getColorClasses(kit.color).ring} shadow-lg` : 'hover:ring-violet-300'
                        ]"
                        @click="selectKit(kit.id)"
                    >
                        <!-- Card Header -->
                        <div class="p-6 pb-4">
                            <div class="flex items-start gap-4 mb-4">
                                <div 
                                    :class="[
                                        'w-14 h-14 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110',
                                        getColorClasses(kit.color).bg
                                    ]"
                                >
                                    <component 
                                        :is="getIcon(kit.icon)" 
                                        :class="['h-7 w-7', getColorClasses(kit.color).text]"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 text-lg leading-tight">{{ kit.name }}</h3>
                                    <span class="inline-flex items-center gap-1 mt-1 text-sm text-gray-500">
                                        <span class="font-medium">{{ kit.template_count }}</span> templates
                                    </span>
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-600 leading-relaxed mb-4">{{ kit.description }}</p>
                            
                            <!-- Features List -->
                            <ul class="space-y-2">
                                <li 
                                    v-for="feature in kit.features.slice(0, 3)" 
                                    :key="feature" 
                                    class="flex items-start gap-2 text-sm text-gray-600"
                                >
                                    <CheckCircleIcon class="h-4 w-4 text-emerald-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                    <span>{{ feature }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Card Footer -->
                        <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between">
                            <span 
                                v-if="currentIndustry === kit.id" 
                                :class="['text-sm font-medium', getColorClasses(kit.color).text]"
                            >
                                ✓ Currently Active
                            </span>
                            <span v-else class="text-sm text-gray-500 group-hover:text-violet-600 transition-colors">
                                Click to explore
                            </span>
                            <ArrowRightIcon 
                                class="h-4 w-4 text-gray-400 group-hover:text-violet-600 group-hover:translate-x-1 transition-all" 
                                aria-hidden="true" 
                            />
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div 
                    v-if="industryKits.length === 0" 
                    class="text-center py-16 bg-white rounded-2xl shadow-sm ring-1 ring-gray-200"
                >
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <BuildingStorefrontIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Industry Kits Available</h3>
                    <p class="text-gray-500 max-w-md mx-auto">
                        Industry kits are being prepared. Check back soon for templates tailored to your business.
                    </p>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
