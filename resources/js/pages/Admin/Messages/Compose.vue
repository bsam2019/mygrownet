<template>
    <AdminLayout title="Compose Message">
        <div class="py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header with Back Button -->
                <div class="mb-8">
                    <Link
                        :href="route('admin.messages.index')"
                        class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors mb-4"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Messages
                    </Link>
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Compose Message</h1>
                            <p class="text-sm text-gray-600 mt-1">Send a message to any member</p>
                        </div>
                    </div>
                </div>

                <!-- Compose Form -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <h2 class="text-xl font-semibold text-gray-900">Message Details</h2>
                        </div>
                    </div>

                    <form @submit.prevent="sendMessage" class="p-8 space-y-8">
                        <!-- Recipient Selection with Search -->
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
                                
                                <!-- Selected User Display -->
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

                                <!-- Dropdown Results -->
                                <div
                                    v-if="showDropdown && filteredUsers.length > 0 && !selectedUser"
                                    class="absolute z-10 mt-2 w-full bg-white shadow-2xl max-h-72 rounded-xl py-2 text-base ring-1 ring-gray-200 overflow-auto focus:outline-none sm:text-sm border border-gray-100"
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
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- No Results -->
                                <div
                                    v-if="showDropdown && searchQuery && filteredUsers.length === 0 && !selectedUser"
                                    class="absolute z-10 mt-2 w-full bg-white shadow-xl rounded-xl py-6 px-4 text-center ring-1 ring-gray-200 border border-gray-100"
                                >
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <p class="text-sm font-medium text-gray-900">No users found</p>
                                    <p class="text-xs text-gray-500 mt-1">Try searching with a different name or email</p>
                                </div>
                            </div>
                            <p v-if="form.errors.recipient_id" class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ form.errors.recipient_id }}
                            </p>
                            <p v-if="!selectedUser" class="mt-2 text-sm text-gray-500 flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Start typing to search for a user by name or email
                            </p>
                        </div>

                        <!-- Subject -->
                        <div class="group">
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
                                placeholder="Enter a clear and concise subject..."
                                required
                            />
                            <p v-if="form.errors.subject" class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ form.errors.subject }}
                            </p>
                        </div>

                        <!-- Message Body -->
                        <div class="group">
                            <label for="body" class="block text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Message <span class="text-red-500 ml-1">*</span>
                            </label>
                            <textarea
                                id="body"
                                v-model="form.body"
                                rows="10"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 resize-none"
                                placeholder="Type your message here... Be clear and professional."
                                required
                            ></textarea>
                            <div class="mt-2 flex items-center justify-between">
                                <p v-if="form.errors.body" class="text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ form.errors.body }}
                                </p>
                                <p class="text-sm font-medium ml-auto" :class="form.body.length > 9000 ? 'text-red-600' : 'text-gray-500'">
                                    {{ form.body.length.toLocaleString() }} / 10,000
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t-2 border-gray-100">
                            <Link
                                :href="route('admin.messages.index')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
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
                </div>

                <!-- Quick Tips -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Tips for effective messaging</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Be clear and concise in your subject line</li>
                                    <li>Use professional and respectful language</li>
                                    <li>Include all necessary information in the message body</li>
                                    <li>For urgent matters, consider following up with a phone call</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface User {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    users: User[];
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
        return props.users.slice(0, 10); // Show first 10 users by default
    }

    const query = searchQuery.value.toLowerCase();
    return props.users
        .filter(user => {
            const name = user.name?.toLowerCase() || '';
            const email = user.email?.toLowerCase() || '';
            return name.includes(query) || email.includes(query);
        })
        .slice(0, 10); // Limit to 10 results
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
        onSuccess: () => {
            // Show success toast and redirect
            window.location.href = route('admin.messages.index');
        },
    });
}

// Close dropdown when clicking outside
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
