<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60" @click="$emit('close')"></div>

            <!-- Panel -->
            <div class="relative z-10 w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-2xl bg-[#16161a] shadow-2xl">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-[#2d2d35] px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">Upload Video</h3>
                    <button @click="$emit('close')" class="rounded-lg p-2 text-zinc-400 hover:bg-[#2d2d35] hover:text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="px-6 py-5 space-y-5">
                    <!-- Dropzone -->
                    <div
                        @click="fileInput?.click()"
                        @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false"
                        @drop.prevent="handleDrop"
                        :class="dragOver ? 'border-emerald-400 bg-emerald-500/5' : 'border-[#2d2d35] hover:border-zinc-500'"
                        class="cursor-pointer rounded-xl border-2 border-dashed p-8 text-center transition-colors"
                    >
                        <input ref="fileInput" type="file" accept="video/*" class="hidden" @change="handleFileSelect" />
                        <template v-if="!form.video">
                            <svg class="mx-auto mb-3 h-10 w-10 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="text-sm font-medium text-white">Click to browse or drag & drop</p>
                            <p class="mt-1 text-xs text-zinc-500">MP4, MOV, MKV, WebM up to 5 GB</p>
                        </template>
                        <template v-else>
                            <div class="flex items-center justify-center gap-3">
                                <svg class="h-8 w-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-left">
                                    <p class="text-sm font-medium text-white">{{ form.video.name }}</p>
                                    <p class="text-xs text-emerald-400">{{ formatSize(form.video.size) }}</p>
                                </div>
                                <button type="button" @click.stop="form.video = null" class="ml-2 rounded-full p-1 text-zinc-500 hover:bg-[#2d2d35] hover:text-white">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Title -->
                    <div>
                        <label for="upload-title" class="mb-1.5 block text-sm font-medium text-zinc-300">Title *</label>
                        <input
                            id="upload-title"
                            v-model="form.title"
                            type="text"
                            required
                            placeholder="Enter video title"
                                                                                    style="color: #f4f4f5 !important; background: #101014 !important; border-color: #2d2d35; -webkit-text-fill-color: #f4f4f5 !important; box-shadow: inset 0 0 0 100px #101014 !important;"
                            class="w-full rounded-xl border px-4 py-3 text-sm placeholder-zinc-500 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-colors"
                        />
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="upload-desc" class="mb-1.5 block text-sm font-medium text-zinc-300">Description *</label>
                        <textarea
                            id="upload-desc"
                            v-model="form.description"
                            rows="3"
                            required
                            placeholder="Brief description of the video"
                                                                                    style="color: #f4f4f5 !important; background: #101014 !important; border-color: #2d2d35; -webkit-text-fill-color: #f4f4f5 !important; box-shadow: inset 0 0 0 100px #101014 !important;"
                            class="w-full rounded-xl border px-4 py-3 text-sm placeholder-zinc-500 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-colors resize-none"
                        ></textarea>
                    </div>

                    <!-- Content Type & Access Level -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-300">Content Type *</label>
                            <select v-model="form.content_type" required                                                         style="color: #f4f4f5 !important; background: #101014 !important; border-color: #2d2d35; -webkit-text-fill-color: #f4f4f5 !important; box-shadow: inset 0 0 0 100px #101014 !important;" class="w-full rounded-xl border px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-colors">
                                <option v-for="(label, value) in props.contentTypes" :key="value" :value="value">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-300">Access *</label>
                            <select v-model="form.access_level" required                                                         style="color: #f4f4f5 !important; background: #101014 !important; border-color: #2d2d35; -webkit-text-fill-color: #f4f4f5 !important; box-shadow: inset 0 0 0 100px #101014 !important;" class="w-full rounded-xl border px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition-colors">
                                <option v-for="(label, value) in props.accessLevels" :key="value" :value="value">{{ label }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div v-if="uploading" class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-300">Uploading…</span>
                            <span class="font-medium text-emerald-400">{{ uploadProgress }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-[#2d2d35]">
                            <div class="h-full rounded-full bg-emerald-500 transition-all duration-300" :style="{ width: `${uploadProgress}%` }"></div>
                        </div>
                    </div>

                    <!-- Error -->
                    <div v-if="errorMessage" class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                        {{ errorMessage }}
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 border-t border-[#2d2d35] px-6 py-4">
                    <button type="button" @click="$emit('close')" :disabled="uploading" class="rounded-xl px-5 py-2.5 text-sm font-medium text-zinc-400 hover:bg-[#2d2d35] hover:text-white disabled:opacity-50 transition-colors">Cancel</button>
                    <button type="button" @click="handleSubmit" :disabled="!form.video || uploading" class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-40 transition-colors">
                        {{ uploading ? `Uploading ${uploadProgress}%…` : 'Upload Video' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import axios from 'axios';

interface Props {
    show: boolean;
    contentTypes?: Record<string, string>;
    accessLevels?: Record<string, string>;
}

const props = withDefaults(defineProps<Props>(), {
    contentTypes: () => ({
        movie: 'Movie', series: 'Series', episode: 'Episode', short: 'Short Video',
        comedy: 'Comedy', skit: 'Skits', soap: 'Soap Opera', drama: 'Drama',
        documentary: 'Documentary', reality: 'Reality & Talk', music: 'Music',
        kids: 'Kids & Family', lifestyle: 'Lifestyle', faith: 'Faith-Based',
    }),
    accessLevels: () => ({ free: 'Free (Everyone)', premium: 'Premium (Subscribers)' }),
});

const emit = defineEmits<{ (e: 'close'): void; (e: 'uploaded'): void }>();

const fileInput = ref<HTMLInputElement>();
const dragOver = ref(false);
const uploading = ref(false);
const uploadProgress = ref(0);
const errorMessage = ref('');

const form = reactive({ video: null as File | null, title: '', description: '', content_type: 'movie', access_level: 'free' });

const formatSize = (bytes: number): string => {
    if (bytes >= 1024 * 1024 * 1024) return (bytes / (1024 * 1024 * 1024)).toFixed(1) + ' GB';
    if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(0) + ' MB';
    return (bytes / 1024).toFixed(0) + ' KB';
};

const handleFileSelect = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    if (file) { form.video = file; errorMessage.value = ''; }
};
const handleDrop = (event: DragEvent) => {
    dragOver.value = false;
    const file = event.dataTransfer?.files?.[0] ?? null;
    if (file) { form.video = file; errorMessage.value = ''; }
};

const handleSubmit = async () => {
    if (!form.video) { errorMessage.value = 'Please select a video file.'; return; }
    if (!form.title.trim()) { errorMessage.value = 'Title is required.'; return; }
    if (!form.description.trim()) { errorMessage.value = 'Description is required.'; return; }

    uploading.value = true;
    errorMessage.value = '';
    uploadProgress.value = 0;

    try {
        const initResp = await axios.post('/admin/videos/tus-init', {
            file_size: form.video.size,
            title: form.title,
            description: form.description,
            content_type: form.content_type,
            access_level: form.access_level,
        });

        const { video_id, upload_url } = initResp.data;
        if (!upload_url) throw new Error('Failed to initialize upload');

        // Upload directly to Cloudflare using TUS PATCH via XMLHttpRequest.
        // XHR does not inject CSRF headers (unlike axios), avoiding the
        // CORS preflight rejection of x-csrf-token by Cloudflare.
        const file = form.video!;
        const total = file.size;

        // Check offset for resume support
        let offset = 0;
        try {
            const headXhr = new XMLHttpRequest();
            headXhr.open('HEAD', upload_url, false);
            headXhr.setRequestHeader('Tus-Resumable', '1.0.0');
            headXhr.send();
            const uo = headXhr.getResponseHeader('Upload-Offset');
            if (uo) offset = parseInt(uo, 10);
        } catch { /* start at 0 */ }

        await new Promise<void>((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('PATCH', upload_url);
            xhr.setRequestHeader('Content-Type', 'application/offset+octet-stream');
            xhr.setRequestHeader('Upload-Offset', String(offset));
            xhr.setRequestHeader('Tus-Resumable', '1.0.0');
            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) uploadProgress.value = Math.round((e.loaded / e.total) * 100);
            };
            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) resolve();
                else reject(new Error('Upload failed (HTTP ' + xhr.status + ')'));
            };
            xhr.onerror = () => reject(new Error('Network error during upload'));
            xhr.send(file.slice(offset));
        });

        uploadProgress.value = 100;

        // Notify server — on failure the file is still on Cloudflare
        // and will be processed. Don't block the user.
        try { await axios.post(`/admin/videos/${video_id}/tus-complete`); } catch { /* non-critical */ }

        emit('uploaded');
        emit('close');
        form.video = null; form.title = ''; form.description = ''; form.content_type = 'movie'; form.access_level = 'free';
    } catch (error: any) {
        const d = error.response?.data;
        errorMessage.value = d?.message
            || (d?.errors ? Object.values(d.errors).flat().join(', ') : null)
            || d?.error
            || `Upload failed (${error.response?.status || 'network error'}). Try again.`;
    } finally {
        uploading.value = false;
    }
};
</script>
