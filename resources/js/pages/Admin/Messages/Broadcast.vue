<template>
    <AdminLayout>
        <Head title="Broadcast Message" />

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Broadcast Message</h2>
                            <Link :href="route('admin.messages.index')" class="text-blue-600 hover:text-blue-800">
                                ← Back to Messages
                            </Link>
                        </div>

                        <!-- Statistics -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600">Total Members</div>
                                <div class="text-2xl font-bold text-blue-600">{{ stats.total_members }}</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600">Active Subscriptions</div>
                                <div class="text-2xl font-bold text-green-600">{{ stats.active_subscriptions }}</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600">With Starter Kit</div>
                                <div class="text-2xl font-bold text-purple-600">{{ stats.with_starter_kit }}</div>
                            </div>
                        </div>

                        <!-- Broadcast Form -->
                        <form @submit.prevent="sendBroadcast">
                            <!-- Filters -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                                <h3 class="text-lg font-semibold mb-4">Target Audience (Optional)</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" v-model="form.filters.has_starter_kit" class="rounded">
                                            <span class="ml-2">Only members with Starter Kit</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" v-model="form.filters.active_subscription" class="rounded">
                                            <span class="ml-2">Only active subscriptions</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="mt-2 text-sm text-gray-600">
                                    Leave unchecked to send to all members
                                </div>
                            </div>

                            <!-- Subject -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                                <input
                                    v-model="form.subject"
                                    type="text"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Enter message subject"
                                />
                                <div v-if="errors.subject" class="text-red-600 text-sm mt-1">{{ errors.subject }}</div>
                            </div>

                            <!-- Body -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                                <textarea
                                    v-model="form.body"
                                    required
                                    rows="10"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Enter your message..."
                                ></textarea>
                                <div v-if="errors.body" class="text-red-600 text-sm mt-1">{{ errors.body }}</div>
                            </div>

                            <!-- Warning -->
                            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                                        <p class="text-sm text-yellow-700 mt-1">
                                            This message will be sent to {{ estimatedRecipients }} members. 
                                            This action cannot be undone.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-4">
                                <Link :href="route('admin.messages.index')" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                                >
                                    <span v-if="form.processing">Sending...</span>
                                    <span v-else>Send Broadcast</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    stats: {
        total_members: number;
        active_subscriptions: number;
        with_starter_kit: number;
    };
    errors: Record<string, string>;
}>();

const form = useForm({
    subject: '',
    body: '',
    filters: {
        has_starter_kit: false,
        active_subscription: false,
    }
});

const estimatedRecipients = computed(() => {
    let count = props.stats.total_members;
    
    if (form.filters.has_starter_kit) {
        count = Math.min(count, props.stats.with_starter_kit);
    }
    
    if (form.filters.active_subscription) {
        count = Math.min(count, props.stats.active_subscriptions);
    }
    
    return count;
});

const sendBroadcast = () => {
    if (!confirm(`Are you sure you want to send this message to ${estimatedRecipients.value} members?`)) {
        return;
    }
    
    form.post(route('admin.messages.broadcast.send'));
};
</script>
