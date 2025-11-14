<template>
    <Transition name="slide-up">
        <div v-if="show" class="fixed inset-0 z-[70] bg-white flex flex-col">
            <!-- Elegant Header -->
            <div class="bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 text-white shadow-xl">
                <!-- Top Bar -->
                <div class="px-4 py-4 flex items-center justify-between">
                    <button
                        @click="handleClose"
                        class="p-2 hover:bg-white/10 rounded-full transition-all active:scale-95"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <h1 class="text-lg font-bold">New Message</h1>
                    <div class="w-10"></div> <!-- Spacer for centering -->
                </div>

                <!-- Recipient Selection Tabs -->
                <div class="px-4 pb-4">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-1 grid grid-cols-3 gap-1">
                        <button
                            type="button"
                            @click="recipientType = 'admin'"
                            :class="[
                                'py-2.5 px-2 rounded-xl font-semibold text-xs transition-all duration-200',
                                recipientType === 'admin'
                                    ? 'bg-white text-blue-600 shadow-lg'
                                    : 'text-white/80 hover:text-white'
                            ]"
                        >
                            <div class="flex flex-col items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                <span>Admin</span>
                            </div>
                        </button>
                        <button
                            type="button"
                            @click="recipientType = 'upline'"
                            :class="[
                                'py-2.5 px-2 rounded-xl font-semibold text-xs transition-all duration-200',
                                recipientType === 'upline'
                                    ? 'bg-white text-blue-600 shadow-lg'
                                    : 'text-white/80 hover:text-white'
                            ]"
                        >
                            <div class="flex flex-col items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Upline</span>
                            </div>
                        </button>
                        <button
                            type="button"
                            @click="recipientType = 'downline'"
                            :class="[
                                'py-2.5 px-2 rounded-xl font-semibold text-xs transition-all duration-200',
                                recipientType === 'downline'
                                    ? 'bg-white text-blue-600 shadow-lg'
                                    : 'text-white/80 hover:text-white'
                            ]"
                        >
                            <div class="flex flex-col items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Downline</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <div class="flex-1 overflow-y-auto bg-gray-50">
                <div class="p-3 space-y-3 pb-4">
                    <!-- Downline Selection (only shown when downline is selected) -->
                    <div v-if="recipientType === 'downline'">
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Select Downline Member
                        </label>
                        
                        <!-- Search Input -->
                        <div class="relative mb-2">
                            <input
                                v-model="downlineSearch"
                                type="text"
                                class="w-full px-3 py-2.5 pl-9 bg-white border border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all text-sm text-gray-900 placeholder-gray-400"
                                placeholder="Search by name..."
                            />
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        
                        <!-- Loading State -->
                        <div v-if="loadingDownlines" class="flex items-center justify-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-2 border-blue-600 border-t-transparent"></div>
                        </div>
                        
                        <!-- Downlines List -->
                        <div v-else-if="filteredDownlines.length > 0" class="space-y-2 max-h-64 overflow-y-auto">
                            <button
                                v-for="downline in filteredDownlines"
                                :key="downline.id"
                                type="button"
                                @click="selectDownline(downline)"
                                :class="[
                                    'w-full p-3 rounded-xl text-left transition-all',
                                    form.recipient_id === downline.id
                                        ? 'bg-blue-100 border-2 border-blue-500'
                                        : 'bg-white border border-gray-200 hover:border-blue-300'
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-sm">
                                            {{ downline.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ downline.name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ downline.email }}</p>
                                        <p v-if="downline.phone" class="text-xs text-gray-400">{{ downline.phone }}</p>
                                    </div>
                                    <svg v-if="form.recipient_id === downline.id" class="w-5 h-5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </div>
                        
                        <!-- Empty State -->
                        <div v-else class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-sm text-gray-500">{{ downlineSearch ? 'No downlines found' : 'You have no downlines yet' }}</p>
                        </div>
                        
                        <p v-if="errors.recipient_id" class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ errors.recipient_id }}
                        </p>
                    </div>
                    
                    <!-- Subject Input -->
                    <div>
                        <label for="subject" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Subject
                        </label>
                        <input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all text-sm text-gray-900 placeholder-gray-400"
                            placeholder="What's this about?"
                            required
                        />
                        <p v-if="errors.subject" class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ errors.subject }}
                        </p>
                    </div>

                    <!-- Message Body -->
                    <div>
                        <label for="body" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Message
                        </label>
                        <div class="relative">
                            <textarea
                                id="body"
                                v-model="form.body"
                                rows="8"
                                class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all resize-none text-sm text-gray-900 placeholder-gray-400"
                                placeholder="Type your message here..."
                                required
                            ></textarea>
                            <!-- Character Counter -->
                            <div class="absolute bottom-2 right-2 px-2 py-0.5 bg-gray-100 rounded-lg">
                                <span :class="[
                                    'text-xs font-medium',
                                    form.body.length > 9000 ? 'text-red-600' : 'text-gray-500'
                                ]">
                                    {{ form.body.length }} / 10k
                                </span>
                            </div>
                        </div>
                        <p v-if="errors.body" class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ errors.body }}
                        </p>
                    </div>

                    <!-- Send Button (moved here) -->
                    <div class="pt-2">
                        <button
                            @click="sendMessage"
                            :disabled="sending || !canSend"
                            class="w-full py-3.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white rounded-xl font-bold text-base disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-xl active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2 shadow-lg"
                        >
                            <svg v-if="sending" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                            <span v-if="sending">Sending...</span>
                            <span v-else>Send Message</span>
                        </button>
                    </div>

                    <!-- Tips Card -->
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                        <div class="flex gap-2">
                            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-blue-900 mb-1">Quick Tips</p>
                                <ul class="text-xs text-blue-700 space-y-0.5">
                                    <li>• Be clear and specific</li>
                                    <li>• Admin responds within 24hrs</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

interface Downline {
    id: number;
    name: string;
    email: string;
}

const page = usePage();
const recipientType = ref('admin');
const sending = ref(false);
const loadingDownlines = ref(false);
const downlines = ref<Downline[]>([]);
const downlineSearch = ref('');
const errors = ref<{ subject?: string; body?: string; recipient_id?: string }>({});

const form = ref({
    recipient_id: 1,
    subject: '',
    body: '',
    parent_id: null,
});

const canSend = computed(() => {
    return form.value.subject.trim().length > 0 && 
           form.value.body.trim().length > 0 &&
           form.value.body.length <= 10000 &&
           form.value.recipient_id > 0;
});

const filteredDownlines = computed(() => {
    if (!downlineSearch.value) return downlines.value;
    
    const search = downlineSearch.value.toLowerCase().trim();
    return downlines.value.filter(d => 
        d.name.toLowerCase().includes(search) || 
        d.email.toLowerCase().includes(search) ||
        (d.phone && d.phone.includes(search))
    );
});

// Update recipient based on type selection
watch(recipientType, async (newType) => {
    if (newType === 'admin') {
        form.value.recipient_id = 1; // Admin user ID
    } else if (newType === 'upline') {
        // Get upline from user data
        const user = page.props.auth?.user as any;
        form.value.recipient_id = user?.referrer_id || 1;
    } else if (newType === 'downline') {
        form.value.recipient_id = 0; // Reset until user selects
        await loadDownlines();
    }
});

async function loadDownlines() {
    loadingDownlines.value = true;
    try {
        const response = await axios.get(route('mygrownet.network.downlines'));
        downlines.value = response.data.downlines || [];
    } catch (error) {
        console.error('Failed to load downlines:', error);
        downlines.value = [];
    } finally {
        loadingDownlines.value = false;
    }
}

function selectDownline(downline: Downline) {
    form.value.recipient_id = downline.id;
}

function handleClose() {
    if (form.value.subject || form.value.body) {
        Swal.fire({
            title: 'Discard message?',
            text: 'Your message will not be saved',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Discard',
            cancelButtonText: 'Keep editing',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                resetForm();
                emit('close');
            }
        });
    } else {
        emit('close');
    }
}

function resetForm() {
    form.value = {
        recipient_id: 1,
        subject: '',
        body: '',
        parent_id: null,
    };
    errors.value = {};
}

async function sendMessage() {
    if (!canSend.value || sending.value) return;
    
    errors.value = {};
    sending.value = true;
    
    try {
        await axios.post(route('mygrownet.messages.store'), form.value);
        
        await Swal.fire({
            icon: 'success',
            title: 'Message Sent!',
            text: 'Your message has been sent successfully',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
        });
        
        resetForm();
        emit('close');
        // No page refresh - just close the modal
    } catch (error: any) {
        console.error('Failed to send message:', error);
        
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
        
        Swal.fire({
            icon: 'error',
            title: 'Failed to send',
            text: error.response?.data?.message || 'Please try again',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
        });
    } finally {
        sending.value = false;
    }
}

watch(() => props.show, (newVal) => {
    if (!newVal) {
        resetForm();
    }
});
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(100%);
}

/* Smooth scrolling */
.overflow-y-auto {
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

/* Hide scrollbar but keep functionality */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.3);
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.5);
}
</style>
