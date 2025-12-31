<script setup lang="ts">
/**
 * Pricing Section Preview Component
 * Layouts: cards (default), table, toggle
 */
import { computed, ref } from 'vue';
import { CheckIcon } from '@heroicons/vue/24/outline';
import { getBackgroundStyle } from '../../composables/useBackgroundStyle';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
    gridCols3: string;
    getSectionContentTransform: (section: Section) => string;
}>();

const content = computed(() => props.section.content);
const style = computed(() => props.section.style);
const layout = computed(() => content.value?.layout || 'cards');

// Toggle state for monthly/yearly pricing
const isYearly = ref(false);

const bgStyle = computed(() => getBackgroundStyle(style.value, '#ffffff', '#111827'));

// Text alignment class
const textAlignClass = computed(() => {
    const align = content.value?.textPosition || 'center';
    return {
        'text-left': align === 'left',
        'text-center': align === 'center',
        'text-right': align === 'right',
    };
});

// Parse features from string or array
const parseFeatures = (plan: any) => {
    if (Array.isArray(plan.features)) return plan.features;
    if (typeof plan.features === 'string') {
        return plan.features.split('\n').filter((f: string) => f.trim());
    }
    return [];
};

// Get price based on toggle state
const getPrice = (plan: any) => {
    if (layout.value === 'toggle' && isYearly.value && plan.yearlyPrice) {
        return plan.yearlyPrice;
    }
    return plan.price;
};
</script>

<template>
    <div
        class="h-full flex flex-col justify-center overflow-hidden"
        :class="spacing.section"
        :style="bgStyle"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <div :class="textAlignClass">
                <h2 :class="[textSize.h2, 'font-bold mb-4']">
                    {{ content.title || 'Pricing' }}
                </h2>
                <p v-if="content.subtitle" class="text-gray-600 mb-8 max-w-2xl" :class="{ 'mx-auto': content.textPosition !== 'left' && content.textPosition !== 'right' }">
                    {{ content.subtitle }}
                </p>
            </div>

            <!-- Toggle Layout -->
            <template v-if="layout === 'toggle'">
                <!-- Billing Toggle -->
                <div class="flex items-center justify-center gap-4 mb-8">
                    <span :class="!isYearly ? 'text-gray-900 font-medium' : 'text-gray-500'">Monthly</span>
                    <button
                        @click="isYearly = !isYearly"
                        class="relative w-14 h-7 rounded-full transition-colors"
                        :class="isYearly ? 'bg-blue-600' : 'bg-gray-300'"
                        aria-label="Toggle billing period"
                    >
                        <span
                            class="absolute top-1 w-5 h-5 bg-white rounded-full transition-transform"
                            :class="isYearly ? 'translate-x-8' : 'translate-x-1'"
                        ></span>
                    </button>
                    <span :class="isYearly ? 'text-gray-900 font-medium' : 'text-gray-500'">
                        Yearly
                        <span class="text-green-600 text-xs ml-1">Save 20%</span>
                    </span>
                </div>

                <!-- Plans Grid -->
                <div class="grid" :class="[gridCols3, spacing.gap]">
                    <div
                        v-for="(plan, idx) in content.plans || []"
                        :key="idx"
                        class="p-4 bg-white border rounded-xl text-center transition-all"
                        :class="plan.popular ? 'border-blue-500 ring-2 ring-blue-100 scale-105' : 'border-gray-200'"
                    >
                        <div v-if="plan.popular" class="mb-3">
                            <span class="bg-blue-100 text-blue-600 text-xs font-semibold px-3 py-1 rounded-full">Most Popular</span>
                        </div>
                        <h3 class="font-semibold mb-2" :class="textSize.h3">{{ plan.name }}</h3>
                        <p
                            class="font-bold text-blue-600 mb-4"
                            :class="isMobile ? 'text-2xl' : 'text-3xl'"
                        >
                            {{ getPrice(plan) }}
                        </p>
                        <ul class="space-y-2 mb-4 text-left">
                            <li
                                v-for="(feature, fIdx) in parseFeatures(plan)"
                                :key="fIdx"
                                class="flex items-start gap-2 text-gray-600"
                                :class="textSize.p"
                            >
                                <CheckIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                {{ feature }}
                            </li>
                        </ul>
                        <button
                            class="w-full py-2 rounded-lg transition-colors"
                            :class="[
                                textSize.p,
                                plan.popular ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
                            ]"
                        >
                            {{ plan.buttonText || 'Choose Plan' }}
                        </button>
                    </div>
                </div>
            </template>

            <!-- Table Layout -->
            <template v-else-if="layout === 'table'">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="p-4 text-left font-semibold">Features</th>
                                <th 
                                    v-for="(plan, idx) in content.plans || []" 
                                    :key="idx"
                                    class="p-4 text-center"
                                    :class="plan.popular ? 'bg-blue-50' : ''"
                                >
                                    <div class="font-semibold">{{ plan.name }}</div>
                                    <div class="text-blue-600 font-bold text-xl mt-1">{{ plan.price }}</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(feature, fIdx) in parseFeatures(content.plans?.[0] || {})" 
                                :key="fIdx"
                                class="border-b border-gray-100"
                            >
                                <td class="p-4 text-gray-600">{{ feature }}</td>
                                <td 
                                    v-for="(plan, pIdx) in content.plans || []" 
                                    :key="pIdx"
                                    class="p-4 text-center"
                                    :class="plan.popular ? 'bg-blue-50' : ''"
                                >
                                    <CheckIcon class="w-5 h-5 text-green-500 mx-auto" aria-hidden="true" />
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="p-4"></td>
                                <td 
                                    v-for="(plan, idx) in content.plans || []" 
                                    :key="idx"
                                    class="p-4 text-center"
                                    :class="plan.popular ? 'bg-blue-50' : ''"
                                >
                                    <button
                                        class="px-6 py-2 rounded-lg transition-colors"
                                        :class="plan.popular ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-900 hover:bg-gray-200'"
                                    >
                                        {{ plan.buttonText || 'Choose' }}
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </template>

            <!-- Cards Layout (Default) -->
            <template v-else>
                <div class="grid" :class="[gridCols3, spacing.gap]">
                    <div
                        v-for="(plan, idx) in content.plans || []"
                        :key="idx"
                        class="p-4 bg-white border rounded-xl text-center"
                        :class="plan.popular ? 'border-blue-500 ring-2 ring-blue-100' : 'border-gray-200'"
                    >
                        <div v-if="plan.popular" class="mb-3">
                            <span class="bg-blue-100 text-blue-600 text-xs font-semibold px-3 py-1 rounded-full">Most Popular</span>
                        </div>
                        <h3 class="font-semibold mb-2" :class="textSize.h3">{{ plan.name }}</h3>
                        <p
                            class="font-bold text-blue-600 mb-4"
                            :class="isMobile ? 'text-2xl' : 'text-3xl'"
                        >
                            {{ plan.price }}
                        </p>
                        <ul class="space-y-1 mb-4">
                            <li
                                v-for="(feature, fIdx) in parseFeatures(plan)"
                                :key="fIdx"
                                class="text-gray-600"
                                :class="textSize.p"
                            >
                                {{ feature }}
                            </li>
                        </ul>
                        <button
                            class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                            :class="textSize.p"
                        >
                            {{ plan.buttonText || 'Choose Plan' }}
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
