<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, CheckIcon } from '@heroicons/vue/24/outline';

interface Page {
    id: string;
    name: string;
    category: string | null;
    access_token: string;
}

interface Props {
    pages: Page[];
    provider: string;
}

const props = defineProps<Props>();

const selectedPage = ref<string | null>(null);

const selectPage = () => {
    if (!selectedPage.value) return;
    
    router.post(route('bizboost.integrations.select-page'), {
        provider: props.provider,
        page_id: selectedPage.value,
    });
};
</script>

<template>
    <Head title="Select Page - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.integrations.index')" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Integrations
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h1 class="text-2xl font-bold text-gray-900">Select a Page</h1>
                        <p class="mt-1 text-sm text-gray-600">Choose which {{ provider }} page to connect</p>
                    </div>

                    <div v-if="pages.length === 0" class="p-8 text-center text-gray-500">
                        No pages found. Make sure you have admin access to at least one page.
                    </div>

                    <div v-else class="divide-y">
                        <label
                            v-for="page in pages"
                            :key="page.id"
                            :class="[
                                'flex items-center gap-4 p-4 cursor-pointer hover:bg-gray-50',
                                selectedPage === page.id ? 'bg-blue-50' : ''
                            ]"
                        >
                            <input
                                v-model="selectedPage"
                                type="radio"
                                :value="page.id"
                                class="text-blue-600"
                            />
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ page.name }}</div>
                                <div v-if="page.category" class="text-sm text-gray-500">{{ page.category }}</div>
                            </div>
                            <CheckIcon v-if="selectedPage === page.id" class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </label>
                    </div>

                    <div class="p-4 bg-gray-50 border-t">
                        <button
                            @click="selectPage"
                            :disabled="!selectedPage"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            Connect Selected Page
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
