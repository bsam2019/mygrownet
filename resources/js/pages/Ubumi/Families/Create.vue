<template>
    <UbumiLayout title="Create Family - Ubumi">
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('ubumi.families.index')"
                        class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Families
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-900">Create New Family</h1>
                    <p class="mt-2 text-gray-600">Start building your family tree</p>
                </div>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <form @submit.prevent="submit">
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Family Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.name }"
                                placeholder="e.g., Mwansa Family"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.name }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                This will be the name of your family tree
                            </p>
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <Link
                                :href="route('ubumi.families.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Family</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </UbumiLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import UbumiLayout from '@/layouts/UbumiLayout.vue';

const form = useForm({
    name: '',
});

const submit = () => {
    form.post(route('ubumi.families.store'));
};
</script>
