<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Objective {
    id: string;
    name: string;
    description: string;
}

interface Campaign {
    id: number;
    name: string;
    description: string | null;
    objective: string;
    duration_days: number;
    start_date: string;
    target_platforms: string[];
}

interface Props {
    campaign: Campaign;
    objectives: Objective[];
    templates: { id: number; name: string }[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.campaign.name,
    description: props.campaign.description || '',
    objective: props.campaign.objective,
    duration_days: props.campaign.duration_days,
    start_date: props.campaign.start_date,
    target_platforms: props.campaign.target_platforms,
});

const submit = () => {
    form.put(route('bizboost.campaigns.update', props.campaign.id));
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
    <Head title="Edit Campaign - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.campaigns.show', campaign.id)" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Campaign
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h1 class="text-2xl font-bold text-gray-900">Edit Campaign</h1>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Campaign Name</label>
                            <input v-model="form.name" type="text" class="w-full rounded-md border-gray-300" required />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="form.description" rows="3" class="w-full rounded-md border-gray-300"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Objective</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label
                                    v-for="obj in objectives"
                                    :key="obj.id"
                                    :class="['p-4 border rounded-lg cursor-pointer', form.objective === obj.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300']"
                                >
                                    <input v-model="form.objective" type="radio" :value="obj.id" class="sr-only" />
                                    <div class="font-medium text-gray-900">{{ obj.name }}</div>
                                    <div class="text-sm text-gray-500">{{ obj.description }}</div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input v-model="form.start_date" type="date" class="w-full rounded-md border-gray-300" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Duration (days)</label>
                                <input v-model="form.duration_days" type="number" min="3" max="30" class="w-full rounded-md border-gray-300" required />
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
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link :href="route('bizboost.campaigns.show', campaign.id)" class="px-4 py-2 text-gray-700 hover:text-gray-900">Cancel</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
