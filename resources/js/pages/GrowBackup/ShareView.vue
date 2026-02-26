<script setup lang="ts">
import { ArrowDownTrayIcon, ClockIcon, DocumentIcon, PhotoIcon } from '@heroicons/vue/24/outline';
import ShareFooter from '@/Components/Storage/ShareFooter.vue';

interface Props {
    token: string;
    file: {
        name: string;
        size: string;
        mime_type: string;
        can_preview: boolean;
    };
    share: {
        expires_at: string | null;
        downloads_remaining: number | null;
    };
}

const props = defineProps<Props>();

const isImage = props.file.mime_type.startsWith('image/');
const isPDF = props.file.mime_type === 'application/pdf';
const canPreview = props.file.can_preview && (isImage || isPDF);

const streamUrl = `/share/${props.token}/stream`;
const downloadUrl = `/share/${props.token}/download`;

const formatDate = (dateString: string | null) => {
    if (!dateString) return null;
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="max-w-4xl w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 mb-4">
                    <span class="text-4xl">☁️</span>
                    <h1 class="text-2xl font-bold text-gray-900">GrowBackup</h1>
                </div>
                <p class="text-gray-600">Someone shared a file with you</p>
            </div>

            <!-- Main Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- File Info -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <PhotoIcon v-if="isImage" class="h-12 w-12 text-blue-500" aria-hidden="true" />
                            <DocumentIcon v-else class="h-12 w-12 text-gray-400" aria-hidden="true" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-xl font-semibold text-gray-900 truncate">
                                {{ file.name }}
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">{{ file.size }}</p>
                            
                            <div v-if="share.expires_at || share.downloads_remaining !== null" class="flex flex-wrap gap-4 mt-3 text-sm text-gray-600">
                                <div v-if="share.expires_at" class="flex items-center gap-1">
                                    <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                    <span>Expires {{ formatDate(share.expires_at) }}</span>
                                </div>
                                <div v-if="share.downloads_remaining !== null" class="flex items-center gap-1">
                                    <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                                    <span>{{ share.downloads_remaining }} downloads remaining</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <div v-if="canPreview" class="bg-gray-50 p-6">
                    <div class="bg-white rounded-lg overflow-hidden shadow-sm">
                        <img
                            v-if="isImage"
                            :src="streamUrl"
                            :alt="file.name"
                            class="w-full h-auto max-h-[600px] object-contain"
                        />
                        <iframe
                            v-else-if="isPDF"
                            :src="streamUrl"
                            class="w-full h-[600px]"
                            frameborder="0"
                        ></iframe>
                    </div>
                </div>

                <!-- Download Button -->
                <div class="p-6 bg-gray-50">
                    <a
                        :href="downloadUrl"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                    >
                        <ArrowDownTrayIcon class="h-5 w-5" aria-hidden="true" />
                        <span>Download File</span>
                    </a>
                    
                    <p class="text-center text-xs text-gray-500 mt-4">
                        Shared via GrowBackup • Secure Cloud Storage
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <ShareFooter />
        </div>
    </div>
</template>
