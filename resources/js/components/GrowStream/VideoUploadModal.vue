<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black/70" @click="$emit('close')"></div>

        <div class="relative z-10 flex min-h-screen items-center justify-center px-4 py-8">
            <!-- Modal panel -->
            <div class="relative w-full max-w-3xl overflow-hidden rounded-[var(--gs-radius)] bg-[var(--gs-card)] text-left shadow-xl sm:my-8">
                <!-- Header -->
                <div class="border-b border-[var(--gs-border)] px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-[var(--gs-text)]">Upload Video</h3>
                        <button
                            @click="$emit('close')"
                            class="rounded-lg p-2 text-[var(--gs-muted)] hover:bg-[var(--gs-bg-elevated)] hover:text-[var(--gs-text)]"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <form @submit.prevent="handleSubmit" class="px-6 py-4">
                    <!-- File Upload -->
                    <div class="mb-6">
                        <label class="gs-label">Video File *</label>
                        <div
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop"
                            :class="[
                                dragOver ? 'border-[var(--gs-primary)] bg-[var(--gs-primary-soft)]' : 'border-[var(--gs-border)]',
                                'mt-1 flex justify-center rounded-[var(--gs-radius)] border-2 border-dashed px-6 pt-5 pb-6',
                            ]"
                        >
                            <div class="space-y-1 text-center">
                                <svg
                                    class="mx-auto h-12 w-12 text-[var(--gs-muted)]"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 48 48"
                                >
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <div class="flex text-sm text-[var(--gs-muted)]">
                                    <label
                                        class="relative cursor-pointer rounded-md bg-white font-medium text-[var(--gs-primary)] hover:text-[var(--gs-primary-hover)]"
                                    >
                                        <span>Upload a file</span>
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            accept="video/*"
                                            class="sr-only"
                                            @change="handleFileSelect"
                                        />
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-[var(--gs-muted)]">MP4, MOV, AVI up to 2GB</p>
                                <p v-if="form.video" class="mt-2 text-sm font-medium text-[var(--gs-primary)]">
                                    Selected: {{ form.video.name }}
                                </p>
                            </div>
                        </div>
                        <p v-if="errors.video" class="mt-1 text-sm text-red-600">{{ errors.video }}</p>
                    </div>

                    <!-- Title -->
                    <div class="mb-4">
                        <label for="upload-title" class="gs-label cursor-pointer">Title *</label>
                        <input
                            id="upload-title"
                            v-model="form.title"
                            type="text"
                            required
                            class="gs-input mt-1 block py-3"
                            style="color: #f4f4f5; background: var(--gs-bg-elevated); border-color: #2d2d35;"
                        />
                        <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="upload-description" class="gs-label cursor-pointer">Description *</label>
                        <textarea
                            id="upload-description"
                            v-model="form.description"
                            rows="3"
                            required
                            class="gs-input mt-1 block py-3"
                            style="color: #f4f4f5; background: var(--gs-bg-elevated); border-color: #2d2d35;"
                        ></textarea>
                        <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                    </div>

                    <!-- Content Type & Access Level -->
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="gs-label">Content Type *</label>
                            <select
                                v-model="form.content_type"
                                required
                                class="gs-input mt-1"
                                style="color: #f4f4f5; background: var(--gs-bg-elevated); border-color: #2d2d35;"
                            >
                                <option v-for="(label, value) in props.contentTypes" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="gs-label">Access Level *</label>
                            <select
                                v-model="form.access_level"
                                required
                                class="gs-input mt-1"
                                style="color: #f4f4f5; background: var(--gs-bg-elevated); border-color: #2d2d35;"
                            >
                                <option v-for="(label, value) in props.accessLevels" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div v-if="uploading" class="mb-4">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-[var(--gs-text)]">Uploading...</span>
                            <span class="text-[var(--gs-muted)]">{{ uploadProgress }}%</span>
                        </div>
                        <div class="h-2 gs-progress-track">
                            <div
                                :style="{ width: `${uploadProgress}%` }"
                                class="gs-progress-fill"
                            ></div>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="mb-4 rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-300">
                        {{ errorMessage }}
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end gap-3 border-t border-[var(--gs-border)] pt-4">
                        <button
                            type="button"
                            @click="$emit('close')"
                            :disabled="uploading"
                            class="gs-btn gs-btn-outline"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="!form.video || uploading"
                            class="gs-btn gs-btn-primary"
                        >
                            {{ uploading ? 'Uploading...' : 'Upload Video' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useGrowStreamAdmin } from '@/composables/useGrowStreamAdmin';

interface Props {
    show: boolean;
    contentTypes?: Record<string, string>;
    accessLevels?: Record<string, string>;
}

const props = withDefaults(defineProps<Props>(), {
    contentTypes: () => ({
        movie: 'Movie',
        series: 'Series',
        episode: 'Episode',
        short: 'Short Video',
        comedy: 'Comedy',
        skit: 'Skits',
        soap: 'Soap Opera',
        drama: 'Drama',
        documentary: 'Documentary',
        reality: 'Reality & Talk Shows',
        music: 'Music & Performance',
        kids: 'Kids & Family',
        lifestyle: 'Lifestyle',
        faith: 'Faith-Based',
    }),
    accessLevels: () => ({
        free: 'Free (Everyone)',
        premium: 'Premium (Subscribers)',
    }),
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'uploaded'): void;
}>();

const { uploadVideo } = useGrowStreamAdmin();

const fileInput = ref<HTMLInputElement>();
const dragOver = ref(false);
const uploading = ref(false);
const uploadProgress = ref(0);
const errorMessage = ref('');

const form = reactive({
    video: null as File | null,
    title: '',
    description: '',
    content_type: 'movie',
    access_level: 'free',
});

const errors = reactive({
    video: '',
    title: '',
    description: '',
});

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.video = target.files[0];
        errors.video = '';
    }
};

const handleDrop = (event: DragEvent) => {
    dragOver.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
        form.video = event.dataTransfer.files[0];
        errors.video = '';
    }
};

const handleSubmit = async () => {
    // Validate
    errors.video = form.video ? '' : 'Video file is required';
    errors.title = form.title ? '' : 'Title is required';
    errors.description = form.description ? '' : 'Description is required';

    if (errors.video || errors.title || errors.description) return;

    uploading.value = true;
    errorMessage.value = '';
    uploadProgress.value = 0;

    try {
        const formData = new FormData();
        formData.append('video', form.video!);
        formData.append('title', form.title);
        formData.append('description', form.description);
        formData.append('content_type', form.content_type);
        formData.append('access_level', form.access_level);

        await uploadVideo(formData);

        // Success
        emit('uploaded');
        emit('close');

        // Reset form
        form.video = null;
        form.title = '';
        form.description = '';
        form.content_type = 'movie';
        form.access_level = 'free';
    } catch (error: any) {
        const errData = error.response?.data;
        const msg = errData?.message
            || (errData?.errors ? Object.values(errData.errors).flat().join(', ') : null)
            || errData?.error
            || 'Upload failed. Please try again.';
        errorMessage.value = msg;
    } finally {
        uploading.value = false;
        uploadProgress.value = 0;
    }
};
</script>


