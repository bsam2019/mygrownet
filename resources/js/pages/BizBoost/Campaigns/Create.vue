<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { RocketLaunchIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Objective {
    id: string;
    name: string;
    description: string;
}

interface Template {
    id: number;
    name: string;
    duration: number;
    posts: number;
}

interface Props {
    objectives: Objective[];
    templates: Template[];
    integrations: { id: number; provider: string; status: string }[];
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    description: '',
    objective: '',
    duration_days: 7,
    start_date: new Date().toISOString().split('T')[0],
    target_platforms: [] as string[],
    template_id: null as number | null,
    auto_generate_content: true,
});

const submit = () => {
    form.post(route('bizboost.campaigns.store'));
};

const togglePlatform = (platform: string) => {
    const index = form.target_platforms.indexOf(platform);
    if (index === -1) {
        form.target_platforms.push(platform);
    } else {
        form.target_platforms.splice(index, 1);
    }
};
</script>

<template>
    <Head title="Create Campaign - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.campaigns.index')" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Campaigns
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <RocketLaunchIcon class="h-7 w-7 text-blue-600" aria-hidden="true" />
                            Create Campaign
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">Set up an automated marketing campaign</p>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Campaign Name</label>
                            <input v-model="form.name" type="text" class="w-full rounded-md border-gray-300" placeholder="e.g., Summer Sale Campaign" required />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                            <textarea v-model="form.description" rows="2" class="w-full rounded-md border-gray-300" placeholder="Brief description of your campaign goals"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Campaign Objective</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label
                                    v-for="obj in objectives"
                                    :key="obj.id"
                                    :class="['p-4 border rounded-lg cursor-pointer transition-colors', form.objective === obj.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300']"
                                >
                                    <input v-model="form.objective" type="radio" :value="obj.id" class="sr-only" />
                                    <div class="font-medium text-gray-900">{{ obj.name }}</div>
                                    <div class="text-sm text-gray-500">{{ obj.description }}</div>
                                </label>
                            </div>
                            <p v-if="form.errors.objective" class="mt-1 text-sm text-red-600">{{ form.errors.objective }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input v-model="form.start_date" type="date" class="w-full rounded-md border-gray-300" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                                <select v-model="form.duration_days" class="w-full rounded-md border-gray-300">
                                    <option :value="3">3 days</option>
                                    <option :value="7">7 days</option>
                                    <option :value="14">14 days</option>
                                    <option :value="21">21 days</option>
                                    <option :value="30">30 days</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Target Platforms</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" :checked="form.target_platforms.includes('facebook')" @change="togglePlatform('facebook')" class="rounded border-gray-300 text-blue-600" />
                                    <span>Facebook</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" :checked="form.target_platforms.includes('instagram')" @change="togglePlatform('instagram')" class="rounded border-gray-300 text-blue-600" />
                                    <span>Instagram</span>
                                </label>
                            </div>
                            <p v-if="form.errors.target_platforms" class="mt-1 text-sm text-red-600">{{ form.errors.target_platforms }}</p>
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input v-model="form.auto_generate_content" type="checkbox" class="rounded border-gray-300 text-blue-600" />
                                <span class="text-sm text-gray-700">Auto-generate content based on objective</span>
                            </label>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link :href="route('bizboost.campaigns.index')" class="px-4 py-2 text-gray-700 hover:text-gray-900">Cancel</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                Create Campaign
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
