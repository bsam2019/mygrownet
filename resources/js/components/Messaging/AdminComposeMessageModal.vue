<template>
    <TransitionRoot :show="show" as="template">
        <Dialog as="div" class="relative z-50" @close="$emit('close')">
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl">
                            <form @submit.prevent="sendMessage">
                                <!-- Header -->
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </div>
                                            <DialogTitle as="h3" class="text-xl font-semibold text-gray-900">
                                                Compose Message
                                            </DialogTitle>
                                        </div>
                                        <button
                                            type="button"
                                            @click="$emit('close')"
                                            class="text-gray-400 hover:text-gray-600 transition-colors"
                                        >
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Body -->
                                <div class="px-8 py-6 space-y-6 max-h-[calc(100vh-300px)] overflow-y-auto">
                                    <!-- Recipient Selection -->
                                    <div class="group">
                                        <label for="recipient" class="block text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Recipient <span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                    </svg>
                                                </div>
                                                <input
                                                    id="recipient"
                                                    v-model="searchQuery"
                                                    type="text"
                                                    @input="searchUsers"
                                                    @focus="showDropdown = true"
                                                    class="w-full pl-11 pr-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200"
                                                    placeholder="Search by name or email..."
                                                    autocomplete="off"
                                                />
                                            </div>

                                            <!-- Selected User -->
                                            <div v-if="selectedUser" class="mt-3 flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl px-4 py-3 shadow-sm">
                                                <div class="flex items-center space-x-3">
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md">
                                                        <span class="text-white font-semibold text-sm">
                                                            {{ selectedUser.name.charAt(0).toUpperCase() }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-900">{{ selectedUser.name }}</p>
                                                        <p class="text-xs text-gray-600">{{ selectedUser.email }}</p>
                                                    </div>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="clearSelection"
                                                    class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-white"
                                                >
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Dropdown -->
                                            <div
                                                v-if="showDropdown && filteredUsers.length > 0 && !selectedUser"
                                                class="absolute z-10 mt-2 w-full bg-white shadow-2xl max-h-60 rounded-xl py-2 ring-1 ring-gray-200 overflow-auto border border-gray-100"
                                            >
                                                <button
                                                    v-for="user in filteredUsers"
                                                    :key="user.id"
                                                    type="button"
                                                    @click="selectUser(user)"
                                                    class="w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors flex items-center space-x-3 group"
                                                >
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center flex-shrink-0 group-hover:from-blue-100 group-hover:to-blue-200 transition-all">
                                                        <span class="text-gray-700 font-semibold text-sm group-hover:text-blue-700">
                                                            {{ user.name.charAt(0).toUpperCase() }}
                                                        </span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-700">{{ user.name }}</p>
                                                        <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                        <p v-if="form.errors.recipient_id" class="mt-2 text-sm text-red-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            {{ form.errors.recipient_id }}
                                        </p>
                                    </div>

                                    <!-- Subject -->
                                    <div>
                                        <label for="subject" class="block text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                            </svg>
                                            Subject <span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <input
                                            id="subject"
                                            v-model="form.subject"
                                            type="text"
                                            class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200"
                                            placeholder="Enter a clear subject..."
                                            required
                                        />
                                    </div>

                                    <!-- Message Body -->
                                    <div>
                                        <label for="body" class="block text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Message <span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <textarea
                                            id="body"
                                            v-model="form.body"
                                            rows="6"
                                            class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 resize-none"
                                            placeholder="Type your message..."
                                            required
                                        ></textarea>
                                        <p class="mt-2 text-sm text-gray-500 text-right">
                                            {{ form.body.length.toLocaleString() }} / 10,000
                                        </p>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="bg-gray-50 px-8 py-4 flex items-center justify-end space-x-3">
                                    <button
                                        type="button"
                                        @click="$emit('close')"
                                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 shadow-lg transition-all"
                                    >
                                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        Send Message
                                    </button>
                                </div>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import Swal from 'sweetalert2';

interface User {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    show: boolean;
    users: User[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const searchQuery = ref('');
const showDropdown = ref(false);
const selectedUser = ref<User | null>(null);

const form = useForm({
    recipient_id: '',
    subject: '',
    body: '',
    parent_id: null,
});

const filteredUsers = computed(() => {
    if (!searchQuery.value) {
        return props.users.slice(0, 10);
    }

    const query = searchQuery.value.toLowerCase();
    return props.users
        .filter(user => {
            const name = user.name?.toLowerCase() || '';
            const email = user.email?.toLowerCase() || '';
            return name.includes(query) || email.includes(query);
        })
        .slice(0, 10);
});

function searchUsers() {
    showDropdown.value = true;
}

function selectUser(user: User) {
    selectedUser.value = user;
    form.recipient_id = user.id.toString();
    searchQuery.value = '';
    showDropdown.value = false;
}

function clearSelection() {
    selectedUser.value = null;
    form.recipient_id = '';
    searchQuery.value = '';
}

function sendMessage() {
    if (!form.recipient_id) {
        form.setError('recipient_id', 'Please select a recipient');
        return;
    }

    form.post(route('admin.messages.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            selectedUser.value = null;
            emit('close');
            
            Swal.fire({
                icon: 'success',
                title: 'Message Sent!',
                text: 'Your message has been sent successfully.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        },
    });
}

function handleClickOutside(event: MouseEvent) {
    const target = event.target as HTMLElement;
    if (!target.closest('#recipient') && !target.closest('.absolute')) {
        showDropdown.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
