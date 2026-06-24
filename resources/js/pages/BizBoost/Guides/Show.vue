<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    BookOpenIcon, RocketLaunchIcon, WalletIcon, CurrencyDollarIcon,
    ChatBubbleBottomCenterTextIcon, LinkIcon, DocumentTextIcon, ChartBarIcon,
    ArrowLeftIcon, LightBulbIcon, CheckCircleIcon,
} from '@heroicons/vue/24/outline';

interface Section {
    heading: string;
    content?: string;
    steps?: string[];
    bullets?: { label: string; desc: string }[];
    note?: string;
}

interface Guide {
    slug: string;
    title: string;
    icon: string;
    color: string;
    sections: Section[];
}

interface Props {
    guide: Guide;
}

const props = defineProps<Props>();

const iconMap: Record<string, any> = {
    RocketLaunchIcon, WalletIcon, CurrencyDollarIcon,
    ChatBubbleBottomCenterTextIcon, LinkIcon, DocumentTextIcon, ChartBarIcon, BookOpenIcon,
};

const colorMap: Record<string, string> = {
    blue: 'bg-blue-600', green: 'bg-green-600', orange: 'bg-orange-600',
    emerald: 'bg-emerald-600', indigo: 'bg-indigo-600', purple: 'bg-purple-600', red: 'bg-red-600',
};
</script>

<template>
    <Head :title="guide.title + ' - BizBoost Guides'" />
    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <Link :href="route('bizboost.guides.index')" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
                    <ArrowLeftIcon class="h-4 w-4" /> Back to Guides
                </Link>

                <div class="flex items-center gap-3 mb-8">
                    <div class="rounded-lg p-3" :class="colorMap[guide.color] || 'bg-gray-600'">
                        <component :is="iconMap[guide.icon] || BookOpenIcon" class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ guide.title }}</h1>
                        <p class="text-sm text-gray-500">Follow the steps below to use this feature.</p>
                    </div>
                </div>

                <div class="space-y-8">
                    <div v-for="(section, i) in guide.sections" :key="i" class="rounded-xl border border-gray-200 bg-white p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ section.heading }}</h2>

                        <p v-if="section.content" class="text-sm text-gray-700 leading-relaxed mb-4">{{ section.content }}</p>

                        <ol v-if="section.steps" class="space-y-3 mb-4">
                            <li v-for="(step, j) in section.steps" :key="j" class="flex gap-3 text-sm text-gray-700">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-500">{{ j + 1 }}</span>
                                <span class="pt-0.5">{{ step }}</span>
                            </li>
                        </ol>

                        <div v-if="section.bullets" class="space-y-2 mb-4">
                            <div v-for="(item, k) in section.bullets" :key="k" class="flex gap-2 text-sm">
                                <CheckCircleIcon class="h-5 w-5 shrink-0 mt-0.5 text-green-500" />
                                <div>
                                    <span class="font-medium text-gray-900">{{ item.label }}:</span>
                                    <span class="text-gray-600"> {{ item.desc }}</span>
                                </div>
                            </div>
                        </div>

                        <div v-if="section.note" class="mt-4 flex gap-2 rounded-lg bg-yellow-50 border border-yellow-200 p-3 text-sm text-yellow-800">
                            <LightBulbIcon class="h-5 w-5 shrink-0 mt-0.5 text-yellow-600" />
                            <span>{{ section.note }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
