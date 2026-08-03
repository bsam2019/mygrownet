<template>
    <CreatorStudioLayout :title="video ? 'Edit Video - GrowStream Creator' : 'Upload Video - GrowStream Creator'">
        <div class="mx-auto max-w-3xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-[var(--gs-text)]">
                    {{ video ? 'Edit Video' : 'Upload Video' }}
                </h1>
                <p class="mt-2 text-[var(--gs-muted)]">
                    {{
                        video
                            ? 'Update your video details'
                            : 'Three steps to publish — upload your file, add details, submit for moderation.'
                    }}
                </p>
            </div>

            <!-- Step indicator -->
            <div v-if="!video" class="mb-8">
                <ol class="flex items-center gap-2">
                    <li
                        v-for="(step, index) in steps"
                        :key="step"
                        class="flex items-center gap-2"
                    >
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold"
                            :class="
                                index < currentStep
                                    ? 'bg-[var(--gs-primary)] text-[#0a0a0c]'
                                    : index === currentStep
                                      ? 'bg-[var(--gs-accent)] text-[#1a1608]'
                                      : 'border border-[var(--gs-border)] text-[var(--gs-muted)]'
                            "
                        >
                            {{ index < currentStep ? '✓' : index + 1 }}
                        </span>
                        <span
                            class="text-sm font-medium"
                            :class="index <= currentStep ? 'text-[var(--gs-text)]' : 'text-[var(--gs-muted)]'"
                        >
                            {{ step }}
                        </span>
                        <svg
                            v-if="index < steps.length - 1"
                            class="mx-1 h-4 w-4 text-[var(--gs-border)]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </li>
                </ol>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div v-if="form.errors.length > 0" class="rounded-md bg-red-500/10 p-4">
                    <p class="text-sm font-medium text-red-400">Please fix the following:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-400">
                        <li v-for="error in form.errors" :key="error">{{ error }}</li>
                    </ul>
                </div>

                <!-- Step 1: Upload -->
                <section v-if="video || currentStep === 0" class="gs-card space-y-5 p-6">
                    <h2 class="text-lg font-semibold text-[var(--gs-text)]">1. Video file</h2>

                    <div v-if="!video">
                        <label class="mb-2 block text-sm font-medium text-[var(--gs-muted)]">Video File</label>
                        <div
                            class="flex cursor-pointer flex-col items-center justify-center rounded-[var(--gs-radius)] border-2 border-dashed border-[var(--gs-border)] bg-[var(--gs-bg-elevated)] p-10 text-center transition-colors hover:border-[var(--gs-primary)]"
                            @click="fileInput?.click()"
                            @dragover.prevent
                            @drop.prevent="onDrop"
                        >
                            <svg class="mb-3 h-12 w-12 text-[var(--gs-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0-12l-4 4m4-4l4 4M4 20h16" />
                            </svg>
                            <p class="font-medium text-[var(--gs-text)]">
                                {{ form.video_file ? form.video_file.name : 'Drop your video here or browse' }}
                            </p>
                            <p class="mt-1 text-sm text-[var(--gs-muted)]">
                                Max {{ formatBytes(maxFileSize) }} · MP4, MOV, AVI, MKV, WebM
                            </p>
                        </div>
                        <input
                            ref="fileInput"
                            type="file"
                            accept="video/*"
                            class="hidden"
                            @change="(e) => (form.video_file = (e.target as HTMLInputElement).files?.[0] ?? null)"
                        />
                    </div>

                    <div class="relative" v-if="!video">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-[var(--gs-border)]"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="bg-[var(--gs-card)] px-3 text-sm text-[var(--gs-muted)]">or provide a URL</span>
                        </div>
                    </div>

                    <div v-if="!video">
                        <label for="video_url" class="mb-1 block text-sm font-medium text-[var(--gs-muted)]">Video URL</label>
                        <input
                            id="video_url"
                            v-model="form.video_url"
                            type="url"
                            class="gs-input"
                            placeholder="https://..."
                        />
                    </div>
                </section>

                <!-- Step 2: Metadata -->
                <section v-if="video || currentStep === 1" class="gs-card space-y-5 p-6">
                    <h2 class="text-lg font-semibold text-[var(--gs-text)]">2. Details</h2>

                    <div>
                        <label for="title" class="gs-label">Title *</label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="gs-input"
                            required
                        />
                    </div>

                    <div>
                        <label for="description" class="gs-label">Description</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="gs-input"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="content_type" class="gs-label">Content Type *</label>
                            <select
                                id="content_type"
                                v-model="form.content_type"
                                class="gs-input"
                            >
                                <option v-for="(label, value) in contentTypes" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="access_level" class="gs-label">Audience *</label>
                            <select
                                id="access_level"
                                v-model="form.access_level"
                                class="gs-input"
                            >
                                <option v-for="(label, value) in accessLevels" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="gs-label">Categories</label>
                        <div class="flex flex-wrap gap-2">
                            <label
                                v-for="category in categories"
                                :key="category.id"
                                class="inline-flex cursor-pointer items-center rounded-full border px-3 py-1 text-sm transition-colors"
                                :class="
                                    form.categories.includes(category.id)
                                        ? 'border-[var(--gs-primary)] bg-[var(--gs-primary-soft)] text-[var(--gs-primary)]'
                                        : 'border-[var(--gs-border)] text-[var(--gs-muted)] hover:border-[var(--gs-border)] hover:text-[var(--gs-text)]'
                                "
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
                        <label for="tags" class="gs-label">Tags</label>
                        <input
                            id="tags"
                            v-model="tagsInput"
                            type="text"
                            class="gs-input"
                            placeholder="comma, separated, tags"
                        />
                    </div>
                </section>

                <!-- Step 3: Review & Submit -->
                <section v-if="video || currentStep === 2" class="gs-card space-y-5 p-6">
                    <h2 class="text-lg font-semibold text-[var(--gs-text)]">3. Review & submit</h2>

                    <div v-if="!video" class="flex items-start rounded-[var(--gs-radius)] border border-[var(--gs-accent)]/30 bg-[var(--gs-accent-soft)] p-4">
                        <div class="flex h-5 items-center">
                            <input
                                id="rights_declaration"
                                v-model="form.rights_declaration"
                                type="checkbox"
                                class="h-4 w-4 rounded border-[var(--gs-accent)] text-[var(--gs-accent)] focus:ring-[var(--gs-accent)]"
                                required
                            />
                        </div>
                        <label for="rights_declaration" class="ml-3 text-sm text-[var(--gs-accent)]">
                            I declare that I own the rights to this content or have the necessary
                            permissions to publish it. I understand that violating copyright may result
                            in removal and account suspension.
                        </label>
                    </div>

                    <div class="gs-surface grid grid-cols-2 gap-4 p-4 text-sm">
                        <div>
                            <p class="text-[var(--gs-muted)]">Title</p>
                            <p class="font-medium text-[var(--gs-text)]">{{ form.title || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[var(--gs-muted)]">Content Type</p>
                            <p class="font-medium text-[var(--gs-text)]">{{ contentTypes[form.content_type] ?? form.content_type }}</p>
                        </div>
                        <div>
                            <p class="text-[var(--gs-muted)]">Audience</p>
                            <p class="font-medium text-[var(--gs-text)]">{{ accessLevels[form.access_level] ?? form.access_level }}</p>
                        </div>
                        <div>
                            <p class="text-[var(--gs-muted)]">Categories</p>
                            <p class="font-medium text-[var(--gs-text)]">
                                {{ selectedCategoryNames.length ? selectedCategoryNames.join(', ') : '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="gs-btn gs-btn-primary"
                        >
                            {{ form.processing ? 'Saving...' : video ? 'Update Video' : 'Submit for Moderation' }}
                        </button>
                        <Link
                            :href="route('growstream.creator.videos.index')"
                            class="gs-btn gs-btn-ghost"
                        >
                            Cancel
                        </Link>
                    </div>
                </section>

                <!-- Step navigation -->
                <div v-if="!video" class="flex items-center justify-between">
                    <button
                        v-if="currentStep > 0"
                        type="button"
                        class="gs-btn gs-btn-outline"
                        @click="currentStep--"
                    >
                        Back
                    </button>
                    <span v-else></span>
                    <button
                        v-if="currentStep < steps.length - 1"
                        type="button"
                        class="gs-btn gs-btn-accent"
                        @click="nextStep"
                    >
                        Continue
                    </button>
                </div>
            </form>
        </div>
    </CreatorStudioLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import CreatorStudioLayout from '@/Layouts/CreatorStudioLayout.vue';

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

const steps = ['Upload', 'Details', 'Submit'];
const currentStep = ref(props.video ? 2 : 0);
const fileInput = ref<HTMLInputElement>();

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

const selectedCategoryNames = computed(() => {
    return (props.categories || [])
        .filter((c) => form.categories.includes(c.id))
        .map((c) => c.name);
});

const nextStep = () => {
    if (currentStep.value === 0 && !form.video_file && !form.video_url) {
        return;
    }
    if (currentStep.value < steps.length - 1) {
        currentStep.value++;
    }
};

const onDrop = (event: DragEvent) => {
    const file = event.dataTransfer?.files?.[0];
    if (file) {
        form.video_file = file;
    }
};

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
