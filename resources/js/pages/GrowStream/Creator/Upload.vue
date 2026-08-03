<template>
    <AppLayout :title="video ? 'Edit Video - GrowStream Creator' : 'Upload Video - GrowStream Creator'">
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ video ? 'Edit Video' : 'Upload Video' }}
                </h1>
                <p class="mt-2 text-gray-600">
                    {{
                        video
                            ? 'Update your video details'
                            : 'Upload a video. You must own the rights to all content you submit.'
                    }}
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-lg bg-white p-8 shadow">
                <div v-if="form.errors.length > 0" class="rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">Please fix the following:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                        <li v-for="error in form.errors" :key="error">{{ error }}</li>
                    </ul>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                    />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="content_type" class="block text-sm font-medium text-gray-700">Content Type *</label>
                        <select
                            id="content_type"
                            v-model="form.content_type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option v-for="(label, value) in contentTypes" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="access_level" class="block text-sm font-medium text-gray-700">Audience *</label>
                        <select
                            id="access_level"
                            v-model="form.access_level"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option v-for="(label, value) in accessLevels" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div v-if="!video">
                    <label class="block text-sm font-medium text-gray-700">Video File</label>
                    <input
                        type="file"
                        accept="video/*"
                        class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
                        @change="(e) => (form.video_file = (e.target as HTMLInputElement).files?.[0] ?? null)"
                    />
                    <p class="mt-2 text-xs text-gray-500">
                        Max file size: {{ formatBytes(maxFileSize) }}. Supported: MP4, MOV, AVI, MKV, WebM.
                    </p>
                </div>

                <div v-if="!video" class="relative">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-white px-3 text-sm text-gray-500">or provide a URL</span>
                    </div>
                </div>

                <div v-if="!video">
                    <label for="video_url" class="block text-sm font-medium text-gray-700">Video URL</label>
                    <input
                        id="video_url"
                        v-model="form.video_url"
                        type="url"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="https://..."
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Categories</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <label
                            v-for="category in categories"
                            :key="category.id"
                            class="inline-flex cursor-pointer items-center rounded-full border px-3 py-1 text-sm"
                            :class="form.categories.includes(category.id) ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-600'"
                        >
                            <input
                                type="checkbox"
                                :value="category.id"
                                v-model="form.categories"
                                class="sr-only"
                            />
                            {{ category.name }}
                        </label>
                    </div>
                </div>

                <div>
                    <label for="tags" class="block text-sm font-medium text-gray-700">Tags</label>
                    <input
                        id="tags"
                        v-model="tagsInput"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="comma, separated, tags"
                    />
                </div>

                <div v-if="!video" class="flex items-start rounded-md bg-amber-50 p-4">
                    <div class="flex h-5 items-center">
                        <input
                            id="rights_declaration"
                            v-model="form.rights_declaration"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            required
                        />
                    </div>
                    <label for="rights_declaration" class="ml-3 text-sm text-amber-800">
                        I declare that I own the rights to this content or have the necessary
                        permissions to publish it. I understand that violating copyright may result
                        in removal and account suspension.
                    </label>
                </div>

                <div class="flex gap-3">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : video ? 'Update Video' : 'Upload Video' }}
                    </button>
                    <Link
                        :href="route('growstream.creator.videos.index')"
                        class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Category {
    id: number;
    name: string;
}

interface Video {
    id: number;
    title: string;
    description: string;
    content_type: string;
    access_level: string;
    categories: { id: number; name: string }[];
    tags: { id: number; name: string }[];
}

interface Props {
    video?: Video | null;
    categories: Category[];
    contentTypes: Record<string, string>;
    accessLevels: Record<string, string>;
    maxFileSize?: number;
}

const props = defineProps<Props>();

const form = useForm({
    title: props.video?.title ?? '',
    description: props.video?.description ?? '',
    content_type: props.video?.content_type ?? Object.keys(props.contentTypes)[0],
    access_level: props.video?.access_level ?? 'free',
    categories: props.video?.categories?.map((c) => c.id) ?? [],
    tags: props.video?.tags?.map((t) => t.name) ?? [],
    video_file: null as File | null,
    video_url: '',
    rights_declaration: false,
});

const tagsInput = ref(form.tags.join(', '));

const submit = () => {
    form.tags = tagsInput.value.split(',').map((t) => t.trim()).filter(Boolean);

    if (props.video) {
        form.put(route('growstream.creator.videos.update', props.video.id));
        return;
    }

    form.post(route('growstream.creator.videos.store'));
};

const formatBytes = (bytes: number): string => {
    if (bytes >= 1024 * 1024 * 1024) return `${(bytes / (1024 * 1024 * 1024)).toFixed(1)} GB`;
    return `${Math.round(bytes / (1024 * 1024))} MB`;
};
</script>
