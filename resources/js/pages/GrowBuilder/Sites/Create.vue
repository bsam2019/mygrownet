<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ArrowLeftIcon, CheckIcon } from '@heroicons/vue/24/outline';

interface Template {
    id: number;
    name: string;
    slug: string;
    category: string;
    categoryLabel: string;
    description: string | null;
    thumbnail: string | null;
    previewImage: string | null;
    isPremium: boolean;
    price: number;
}

const props = defineProps<{
    templates: Template[];
}>();

const selectedTemplate = ref<number | null>(null);
const selectedCategory = ref<string>('all');

const categories = computed(() => {
    const cats = new Set(props.templates.map(t => t.category));
    return [
        { value: 'all', label: 'All Templates' },
        ...Array.from(cats).map(c => ({
            value: c,
            label: props.templates.find(t => t.category === c)?.categoryLabel || c,
        })),
    ];
});

const filteredTemplates = computed(() => {
    if (selectedCategory.value === 'all') {
        return props.templates;
    }
    return props.templates.filter(t => t.category === selectedCategory.value);
});

const form = useForm({
    name: '',
    subdomain: '',
    template_id: null as number | null,
    description: '',
});

const generateSubdomain = () => {
    form.subdomain = form.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '')
        .substring(0, 63);
};

const selectTemplate = (id: number | null) => {
    selectedTemplate.value = id;
    form.template_id = id;
};

const submit = () => {
    form.post(route('growbuilder.sites.store'));
};
</script>

<template>
    <AppLayout>
        <Head title="Create Website - GrowBuilder" />

        <div class="py-6">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <Link
                        :href="route('growbuilder.index')"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Dashboard
                    </Link>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Website</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Choose a template and set up your website
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Basic Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Website Details</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Website Name
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="My Business"
                                    @blur="generateSubdomain"
                                    required
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-1">
                                    Subdomain
                                </label>
                                <div class="flex">
                                    <input
                                        id="subdomain"
                                        v-model="form.subdomain"
                                        type="text"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="my-business"
                                        required
                                    />
                                    <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg text-sm text-gray-500">
                                        .mygrownet.com
                                    </span>
                                </div>
                                <p v-if="form.errors.subdomain" class="mt-1 text-sm text-red-600">{{ form.errors.subdomain }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Description (optional)
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Brief description of your website"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Template Selection -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Choose a Template</h2>

                        <!-- Category Filter -->
                        <div class="flex flex-wrap gap-2 mb-6">
                            <button
                                v-for="cat in categories"
                                :key="cat.value"
                                type="button"
                                :class="[
                                    'px-3 py-1.5 text-sm font-medium rounded-full transition',
                                    selectedCategory === cat.value
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                ]"
                                @click="selectedCategory = cat.value"
                            >
                                {{ cat.label }}
                            </button>
                        </div>

                        <!-- Templates Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Blank Template -->
                            <button
                                type="button"
                                :class="[
                                    'relative p-4 border-2 rounded-xl text-left transition',
                                    selectedTemplate === null
                                        ? 'border-blue-500 bg-blue-50'
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                                @click="selectTemplate(null)"
                            >
                                <div class="h-32 bg-gray-100 rounded-lg flex items-center justify-center mb-3">
                                    <span class="text-gray-400 text-sm">Blank</span>
                                </div>
                                <h3 class="font-medium text-gray-900">Start from Scratch</h3>
                                <p class="text-sm text-gray-500">Build your own design</p>
                                <div
                                    v-if="selectedTemplate === null"
                                    class="absolute top-2 right-2 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center"
                                >
                                    <CheckIcon class="h-4 w-4 text-white" aria-hidden="true" />
                                </div>
                            </button>

                            <!-- Template Cards -->
                            <button
                                v-for="template in filteredTemplates"
                                :key="template.id"
                                type="button"
                                :class="[
                                    'relative p-4 border-2 rounded-xl text-left transition',
                                    selectedTemplate === template.id
                                        ? 'border-blue-500 bg-blue-50'
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                                @click="selectTemplate(template.id)"
                            >
                                <div class="h-32 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg mb-3"></div>
                                <h3 class="font-medium text-gray-900">{{ template.name }}</h3>
                                <p class="text-sm text-gray-500">{{ template.categoryLabel }}</p>
                                <div
                                    v-if="selectedTemplate === template.id"
                                    class="absolute top-2 right-2 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center"
                                >
                                    <CheckIcon class="h-4 w-4 text-white" aria-hidden="true" />
                                </div>
                                <span
                                    v-if="template.isPremium"
                                    class="absolute top-2 left-2 px-2 py-0.5 bg-purple-100 text-purple-800 text-xs font-medium rounded-full"
                                >
                                    Premium
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <Link
                            :href="route('growbuilder.index')"
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Website' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
