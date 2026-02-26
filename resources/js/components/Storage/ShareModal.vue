<script setup lang="ts">
import { ref, computed } from 'vue';
import { XMarkIcon, LinkIcon, ClockIcon, ArrowDownTrayIcon, LockClosedIcon, CheckIcon, TrashIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

interface Props {
    show: boolean;
    file: {
        id: string;
        original_name: string;
    } | null;
}

interface Share {
    id: string;
    share_url: string;
    has_password: boolean;
    expires_at: string | null;
    max_downloads: number | null;
    download_count: number;
    is_active: boolean;
    can_access: boolean;
    created_at: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    created: [];
}>();

const loading = ref(false);
const creating = ref(false);
const shares = ref<Share[]>([]);
const copiedId = ref<string | null>(null);

// Form state
const password = ref('');
const expiresInDays = ref<number | null>(null);
const maxDownloads = ref<number | null>(null);

const hasShares = computed(() => shares.value.length > 0);

const loadShares = async () => {
    if (!props.file) return;
    
    loading.value = true;
    try {
        const response = await axios.get(`/api/storage/files/${props.file.id}/shares`);
        shares.value = response.data.shares;
    } catch (error) {
        console.error('Failed to load shares:', error);
    } finally {
        loading.value = false;
    }
};

const createShare = async () => {
    if (!props.file) return;
    
    creating.value = true;
    try {
        const response = await axios.post(`/api/storage/files/${props.file.id}/shares`, {
            password: password.value || null,
            expires_in_days: expiresInDays.value,
            max_downloads: maxDownloads.value,
        });
        
        // Reset form
        password.value = '';
        expiresInDays.value = null;
        maxDownloads.value = null;
        
        // Reload shares
        await loadShares();
        emit('created');
    } catch (error: any) {
        console.error('Failed to create share:', error);
        alert(error.response?.data?.error || 'Failed to create share link');
    } finally {
        creating.value = false;
    }
};

const copyLink = async (share: Share) => {
    try {
        await navigator.clipboard.writeText(share.share_url);
        copiedId.value = share.id;
        setTimeout(() => {
            copiedId.value = null;
        }, 2000);
    } catch (error) {
        console.error('Failed to copy:', error);
    }
};

const deleteShare = async (shareId: string) => {
    if (!confirm('Delete this share link? Anyone with the link will no longer be able to access the file.')) {
        return;
    }
    
    try {
        await axios.delete(`/api/storage/shares/${shareId}`);
        await loadShares();
    } catch (error) {
        console.error('Failed to delete share:', error);
        alert('Failed to delete share link');
    }
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Never';
    return new Date(dateString).toLocaleDateString();
};

// Load shares when modal opens
const handleOpen = () => {
    if (props.show && props.file) {
        loadShares();
    }
};

// Watch for modal opening
import { watch } from 'vue';
watch(() => props.show, (newValue) => {
    if (newValue) {
        handleOpen();
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="emit('close')"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
                
                <!-- Modal -->
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                        <!-- Header -->
                        <div class="flex items-center justify-between p-6 border-b border-gray-200">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Share File
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ file?.original_name }}
                                </p>
                            </div>
                            
                            <button
                                @click="emit('close')"
                                class="p-1 text-gray-400 hover:text-gray-600 rounded-lg transition-colors"
                                aria-label="Close"
                            >
                                <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 overflow-y-auto p-6 space-y-6">
                            <!-- Create New Share -->
                            <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                                <h4 class="font-medium text-gray-900">Create New Share Link</h4>
                                
                                <div class="space-y-3">
                                    <!-- Password -->
                                    <div>
                                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-1">
                                            <LockClosedIcon class="h-4 w-4" aria-hidden="true" />
                                            Password (optional)
                                        </label>
                                        <input
                                            v-model="password"
                                            type="text"
                                            placeholder="Leave empty for no password"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        />
                                    </div>
                                    
                                    <!-- Expiry -->
                                    <div>
                                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-1">
                                            <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                            Expires in (days)
                                        </label>
                                        <input
                                            v-model.number="expiresInDays"
                                            type="number"
                                            min="1"
                                            max="365"
                                            placeholder="Never expires"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        />
                                    </div>
                                    
                                    <!-- Max Downloads -->
                                    <div>
                                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-1">
                                            <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                                            Max downloads
                                        </label>
                                        <input
                                            v-model.number="maxDownloads"
                                            type="number"
                                            min="1"
                                            max="1000"
                                            placeholder="Unlimited"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        />
                                    </div>
                                </div>
                                
                                <button
                                    @click="createShare"
                                    :disabled="creating"
                                    class="w-full py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                >
                                    <LinkIcon class="h-5 w-5" aria-hidden="true" />
                                    <span>{{ creating ? 'Creating...' : 'Create Share Link' }}</span>
                                </button>
                            </div>
                            
                            <!-- Existing Shares -->
                            <div v-if="hasShares">
                                <h4 class="font-medium text-gray-900 mb-3">Active Share Links</h4>
                                
                                <div class="space-y-3">
                                    <div
                                        v-for="share in shares"
                                        :key="share.id"
                                        class="border border-gray-200 rounded-lg p-4"
                                        :class="{ 'opacity-50': !share.can_access }"
                                    >
                                        <div class="flex items-start justify-between gap-3 mb-3">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <input
                                                        :value="share.share_url"
                                                        readonly
                                                        class="flex-1 px-3 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded"
                                                    />
                                                    <button
                                                        @click="copyLink(share)"
                                                        class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center gap-1"
                                                    >
                                                        <CheckIcon v-if="copiedId === share.id" class="h-4 w-4" aria-hidden="true" />
                                                        <LinkIcon v-else class="h-4 w-4" aria-hidden="true" />
                                                        <span>{{ copiedId === share.id ? 'Copied!' : 'Copy' }}</span>
                                                    </button>
                                                </div>
                                                
                                                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600">
                                                    <span v-if="share.has_password" class="flex items-center gap-1">
                                                        <LockClosedIcon class="h-3 w-3" aria-hidden="true" />
                                                        Password protected
                                                    </span>
                                                    <span class="flex items-center gap-1">
                                                        <ClockIcon class="h-3 w-3" aria-hidden="true" />
                                                        Expires: {{ formatDate(share.expires_at) }}
                                                    </span>
                                                    <span class="flex items-center gap-1">
                                                        <ArrowDownTrayIcon class="h-3 w-3" aria-hidden="true" />
                                                        {{ share.download_count }}{{ share.max_downloads ? `/${share.max_downloads}` : '' }} downloads
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <button
                                                @click="deleteShare(share.id)"
                                                class="p-1.5 text-red-600 hover:bg-red-50 rounded transition-colors"
                                                aria-label="Delete share"
                                            >
                                                <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                            </button>
                                        </div>
                                        
                                        <div v-if="!share.can_access" class="text-xs text-red-600">
                                            ⚠️ This link is no longer accessible (expired or limit reached)
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else-if="!loading" class="text-center py-8 text-gray-500">
                                <LinkIcon class="h-12 w-12 mx-auto mb-3 text-gray-300" aria-hidden="true" />
                                <p>No share links yet</p>
                                <p class="text-sm">Create one above to share this file</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
