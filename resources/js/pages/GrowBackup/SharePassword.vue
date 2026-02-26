<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { LockClosedIcon, DocumentIcon } from '@heroicons/vue/24/outline';
import ShareFooter from '@/Components/Storage/ShareFooter.vue';
import axios from 'axios';

interface Props {
    token: string;
    filename: string;
}

const props = defineProps<Props>();

const password = ref('');
const error = ref('');
const verifying = ref(false);

const verifyPassword = async () => {
    if (!password.value) {
        error.value = 'Please enter a password';
        return;
    }

    verifying.value = true;
    error.value = '';

    try {
        await axios.post(`/share/${props.token}/verify`, {
            password: password.value,
        });

        // Password correct, reload page to show file
        router.reload();
    } catch (err: any) {
        error.value = err.response?.data?.error || 'Incorrect password';
        password.value = '';
    } finally {
        verifying.value = false;
    }
};

const handleSubmit = (e: Event) => {
    e.preventDefault();
    verifyPassword();
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 mb-4">
                    <span class="text-4xl">☁️</span>
                    <h1 class="text-2xl font-bold text-gray-900">GrowBackup</h1>
                </div>
                <p class="text-gray-600">This file is password protected</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <!-- File Icon -->
                <div class="flex justify-center mb-6">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center">
                        <LockClosedIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
                    </div>
                </div>

                <!-- File Name -->
                <div class="text-center mb-6">
                    <div class="flex items-center justify-center gap-2 text-gray-600 mb-2">
                        <DocumentIcon class="h-5 w-5" aria-hidden="true" />
                        <span class="text-sm font-medium truncate">{{ filename }}</span>
                    </div>
                    <p class="text-sm text-gray-500">
                        Enter the password to access this file
                    </p>
                </div>

                <!-- Password Form -->
                <form @submit="handleSubmit" class="space-y-4">
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input
                            id="password"
                            v-model="password"
                            type="password"
                            placeholder="Enter password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': error }"
                            :disabled="verifying"
                            autofocus
                        />
                        <p v-if="error" class="mt-2 text-sm text-red-600">
                            {{ error }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="verifying || !password"
                        class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ verifying ? 'Verifying...' : 'Access File' }}
                    </button>
                </form>
            </div>

            <!-- Help Text -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-500">
                    Don't have the password? Contact the person who shared this file.
                </p>
            </div>

            <!-- Footer -->
            <ShareFooter />
        </div>
    </div>
</template>
