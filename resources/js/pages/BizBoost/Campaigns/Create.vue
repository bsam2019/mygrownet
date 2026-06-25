<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import Card from '@/Components/BizBoost/UI/Card.vue';
import FormInput from '@/Components/BizBoost/Form/FormInput.vue';
import FormTextarea from '@/Components/BizBoost/Form/FormTextarea.vue';
import FormSelect from '@/Components/BizBoost/Form/FormSelect.vue';
import FormCheckbox from '@/Components/BizBoost/Form/FormCheckbox.vue';
import FormSection from '@/Components/BizBoost/Form/FormSection.vue';
import FormActions from '@/Components/BizBoost/Form/FormActions.vue';
import {
    ArrowLeftIcon,
    RocketLaunchIcon,
    CalendarDaysIcon,
    GlobeAltIcon,
} from '@heroicons/vue/24/outline';

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

const togglePlatform = (platform: string) => {
    const index = form.target_platforms.indexOf(platform);
    if (index === -1) {
        form.target_platforms.push(platform);
    } else {
        form.target_platforms.splice(index, 1);
    }
};

const durationOptions = [
    { value: 3, label: '3 days' },
    { value: 7, label: '7 days' },
    { value: 14, label: '14 days' },
    { value: 21, label: '21 days' },
    { value: 30, label: '30 days' },
];

const submit = () => {
    form.post(route('bizboost.campaigns.store'));
};
</script>

<template>
    <Head title="Create Campaign - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <Link
                    :href="route('bizboost.campaigns.index')"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6 transition-colors"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Campaigns
                </Link>

                <Card>
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Campaign Info -->
                        <FormSection title="Campaign Details" description="Name and describe your campaign" :icon="RocketLaunchIcon">
                            <FormInput
                                v-model="form.name"
                                label="Campaign Name"
                                placeholder="e.g., Summer Sale Campaign"
                                :error="form.errors.name"
                                required
                            />
                            <FormTextarea
                                v-model="form.description"
                                label="Description (optional)"
                                placeholder="Brief description of your campaign goals"
                                :rows="2"
                            />
                        </FormSection>

                        <!-- Objective -->
                        <FormSection title="Campaign Objective" description="What do you want to achieve?">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label
                                    v-for="obj in objectives"
                                    :key="obj.id"
                                    :class="[
                                        'p-4 rounded-xl border-2 cursor-pointer transition-all duration-200',
                                        form.objective === obj.id
                                            ? 'border-violet-500 bg-violet-50 dark:bg-violet-900/20 dark:border-violet-400'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-violet-300 dark:hover:border-violet-600 hover:bg-violet-50/50 dark:hover:bg-violet-900/10',
                                    ]"
                                >
                                    <input v-model="form.objective" type="radio" :value="obj.id" class="sr-only" />
                                    <div class="font-medium text-gray-900 dark:text-white">{{ obj.name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ obj.description }}</div>
                                </label>
                            </div>
                            <p v-if="form.errors.objective" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.objective }}</p>
                        </FormSection>

                        <!-- Schedule & Duration -->
                        <FormSection title="Schedule" description="When should the campaign run?" :icon="CalendarDaysIcon">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <FormInput
                                    v-model="form.start_date"
                                    label="Start Date"
                                    type="date"
                                    :error="form.errors.start_date"
                                    required
                                />
                                <FormSelect
                                    v-model="form.duration_days"
                                    label="Duration"
                                    :options="durationOptions"
                                />
                            </div>
                        </FormSection>

                        <!-- Target Platforms -->
                        <FormSection title="Target Platforms" description="Choose where to publish" :icon="GlobeAltIcon">
                            <div class="flex gap-6">
                                <FormCheckbox
                                    :model-value="form.target_platforms.includes('facebook')"
                                    label="Facebook"
                                    @update:model-value="togglePlatform('facebook')"
                                />
                                <FormCheckbox
                                    :model-value="form.target_platforms.includes('instagram')"
                                    label="Instagram"
                                    @update:model-value="togglePlatform('instagram')"
                                />
                            </div>
                            <p v-if="form.errors.target_platforms" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.target_platforms }}</p>
                        </FormSection>

                        <!-- Auto-generate -->
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <FormCheckbox
                                v-model="form.auto_generate_content"
                                label="Auto-generate content with AI"
                                description="Create engaging posts powered by AI based on your campaign objective"
                            />
                        </div>

                        <FormActions
                            submit-label="Create Campaign"
                            cancel-href="/bizboost/campaigns"
                            :processing="form.processing"
                        />
                    </form>
                </Card>
            </div>
        </div>
    </BizBoostLayout>
</template>
