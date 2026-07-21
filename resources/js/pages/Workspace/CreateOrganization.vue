<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import WorkspaceLayout from '@/Layouts/WorkspaceLayout.vue';
import { ArrowLeftIcon, BuildingOfficeIcon } from '@heroicons/vue/24/solid';

const form = ref({
    name: '',
    type: 'general',
    country: '',
    currency: '',
    timezone: '',
    language: '',
});

const processing = ref(false);

function submit() {
    processing.value = true;
    router.post(route('workspace.organization.store'), form.value, {
        onFinish: () => { processing.value = false; },
    });
}
</script>

<template>
    <WorkspaceLayout>
        <Head title="Create Organization" />

        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Link
                :href="route('workspace')"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-6 transition-colors"
            >
                <ArrowLeftIcon class="w-4 h-4" />
                Back to Workspace
            </Link>

            <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white">
                        <BuildingOfficeIcon class="w-5 h-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Create Organization</h1>
                        <p class="text-sm text-gray-500">Set up your team workspace</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Organization Name *</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                            placeholder="e.g. Acme Corp"
                        />
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select
                            id="type"
                            v-model="form.type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                        >
                            <option value="general">General</option>
                            <option value="business">Business</option>
                            <option value="nonprofit">Non-Profit</option>
                            <option value="education">Education</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                            <input
                                id="country"
                                v-model="form.country"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="e.g. Zambia"
                            />
                        </div>
                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                            <input
                                id="currency"
                                v-model="form.currency"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="e.g. ZMW"
                                maxlength="3"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                            <input
                                id="timezone"
                                v-model="form.timezone"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="e.g. Africa/Lusaka"
                            />
                        </div>
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                            <input
                                id="language"
                                v-model="form.language"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="e.g. en"
                                maxlength="10"
                            />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button
                            type="submit"
                            :disabled="processing || !form.name.trim()"
                            class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ processing ? 'Creating...' : 'Create Organization' }}
                        </button>
                        <Link
                            :href="route('workspace')"
                            class="text-sm text-gray-500 hover:text-gray-700 transition-colors"
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </WorkspaceLayout>
</template>