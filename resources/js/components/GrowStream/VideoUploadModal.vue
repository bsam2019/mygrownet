<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

            <!-- Modal panel -->
            <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl sm:align-middle">
                <!-- Header -->
                <div class="border-b border-gray-200 bg-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Upload Video</h3>
                        <button
                            @click="$emit('close')"
                            class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500"
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
                        <label class="block text-sm font-medium text-gray-700">Video File *</label>
                        <div
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop"
                            :class="[
                                dragOver ? 'border-blue-500 bg-blue-50' : 'border-gray-300',
                                'mt-1 flex justify-center rounded-lg border-2 border-dashed px-6 pt-5 pb-6',
                            ]"
                        >
                            <div class="space-y-1 text-center">
                                <svg
                                    class="mx-auto h-12 w-12 text-gray-400"
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
                                <div class="flex text-sm text-gray-600">
                                    <label
                                        class="relative cursor-pointer rounded-md bg-white font-medium text-blue-600 hover:text-blue-500"
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
                                <p class="text-xs text-gray-500">MP4, MOV, AVI up to 2GB</p>
                                <p v-if="form.video" class="mt-2 text-sm font-medium text-green-600">
                                    Selected: {{ form.video.name }}
                                </p>
                            </div>
                        </div>
                        <p v-if="errors.video" class="mt-1 text-sm text-red-600">{{ errors.video }}</p>
                    </div>

                    <!-- Title -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Title *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                        <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Description *</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
                        <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                    </div>

                    <!-- Content Type & Access Level -->
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Content Type *</label>
                            <select
                                v-model="form.content_type"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="lesson">Lesson</option>
                                <option value="workshop">Workshop</option>
                                <option value="webinar">Webinar</option>
                                <option value="movie">Movie</option>
                                <option value="short">Short</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Access Level *</label>
                            <select
                                v-model="form.access_level"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="free">Free</option>
                                <option value="basic">Basic</option>
                                <option value="premium">Premium</option>
                                <option value="institutional">Institutional</option>
                            </select>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div v-if="uploading" class="mb-4">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-gray-700">Uploading...</span>
                            <span class="text-gray-600">{{ uploadProgress }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-gray-200">
                            <div
                                :style="{ width: `${uploadProgress}%` }"
                                class="h-full bg-blue-600 transition-all duration-300"
                            ></div>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800">
                        {{ errorMessage }}
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-4">
                        <button
                            type="button"
                            @click="$emit('close')"
                            :disabled="uploading"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="!form.video || uploading"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
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
}

defineProps<Props>();

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
    content_type: 'lesson',
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
        form.content_type = 'lesson';
        form.access_level = 'free';
    } catch (error: any) {
        errorMessage.value = error.response?.data?.error || 'Upload failed. Please try again.';
    } finally {
        uploading.value = false;
        uploadProgress.value = 0;
    }
};
</script>
