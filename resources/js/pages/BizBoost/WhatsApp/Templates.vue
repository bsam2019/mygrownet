<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ChatBubbleLeftRightIcon, ClipboardDocumentIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Template {
    id: string;
    name: string;
    category: string;
    content: string;
    variables: string[];
}

interface Props {
    templates: Template[];
}

const props = defineProps<Props>();

const selectedTemplate = ref<Template | null>(null);
const copiedId = ref<string | null>(null);

const copyTemplate = (template: Template) => {
    navigator.clipboard.writeText(template.content);
    copiedId.value = template.id;
    setTimeout(() => copiedId.value = null, 2000);
};

const categoryColors: Record<string, string> = {
    general: 'bg-gray-100 text-gray-800',
    promotion: 'bg-green-100 text-green-800',
    follow_up: 'bg-blue-100 text-blue-800',
    special: 'bg-purple-100 text-purple-800',
};
</script>

<template>
    <Head title="WhatsApp Templates - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.whatsapp.broadcasts')" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Broadcasts
                    </Link>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <ChatBubbleLeftRightIcon class="h-7 w-7 text-green-600" aria-hidden="true" />
                            Message Templates
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">Pre-written messages for common scenarios</p>
                    </div>
                </div>

                <div class="grid gap-4">
                    <div
                        v-for="template in templates"
                        :key="template.id"
                        class="bg-white rounded-lg shadow p-6"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ template.name }}</h3>
                                <span :class="['inline-block mt-1 px-2 py-0.5 rounded text-xs font-medium', categoryColors[template.category] || 'bg-gray-100 text-gray-800']">
                                    {{ template.category }}
                                </span>
                            </div>
                            <button
                                @click="copyTemplate(template)"
                                class="flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800"
                            >
                                <ClipboardDocumentIcon class="h-4 w-4" aria-hidden="true" />
                                {{ copiedId === template.id ? 'Copied!' : 'Copy' }}
                            </button>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 whitespace-pre-wrap text-sm text-gray-700">
                            {{ template.content }}
                        </div>
                        <div v-if="template.variables.length > 0" class="mt-3 text-xs text-gray-500">
                            Variables: {{ template.variables.join(', ') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
