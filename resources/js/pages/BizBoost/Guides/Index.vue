<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    BookOpenIcon, RocketLaunchIcon, WalletIcon, CurrencyDollarIcon,
    ChatBubbleBottomCenterTextIcon, LinkIcon, DocumentTextIcon, ChartBarIcon,
} from '@heroicons/vue/24/outline';

interface Guide {
    slug: string;
    title: string;
    description: string;
    icon: string;
    color: string;
}

interface Props {
    guides: Guide[];
}

const props = defineProps<Props>();

const iconMap: Record<string, any> = {
    RocketLaunchIcon, WalletIcon, CurrencyDollarIcon,
    ChatBubbleBottomCenterTextIcon, LinkIcon, DocumentTextIcon, ChartBarIcon, BookOpenIcon,
};

const colorMap: Record<string, string> = {
    blue: 'bg-blue-50 text-blue-700 border-blue-200 ring-blue-600/20',
    green: 'bg-green-50 text-green-700 border-green-200 ring-green-600/20',
    orange: 'bg-orange-50 text-orange-700 border-orange-200 ring-orange-600/20',
    emerald: 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-600/20',
    indigo: 'bg-indigo-50 text-indigo-700 border-indigo-200 ring-indigo-600/20',
    purple: 'bg-purple-50 text-purple-700 border-purple-200 ring-purple-600/20',
    red: 'bg-red-50 text-red-700 border-red-200 ring-red-600/20',
};
</script>

<template>
    <Head title="Guides - BizBoost" />
    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">BizBoost Guides</h1>
                    <p class="mt-2 text-sm text-gray-600">Step-by-step instructions for every BizBoost feature.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="guide in guides"
                        :key="guide.slug"
                        :href="route('bizboost.guides.show', guide.slug)"
                        class="relative flex flex-col rounded-xl border p-6 transition hover:shadow-md"
                        :class="colorMap[guide.color] || 'bg-gray-50 text-gray-700 border-gray-200'"
                    >
                        <div class="flex items-center gap-3 mb-3">
                            <component :is="iconMap[guide.icon] || BookOpenIcon" class="h-6 w-6 shrink-0" />
                            <h3 class="text-base font-semibold">{{ guide.title }}</h3>
                        </div>
                        <p class="text-sm opacity-80">{{ guide.description }}</p>
                        <span class="mt-4 text-xs font-medium opacity-70">Read guide &rarr;</span>
                    </Link>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
