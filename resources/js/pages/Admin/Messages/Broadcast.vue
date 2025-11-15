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
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border border-blue-200 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-blue-600 mb-1">Total Members</div>
                                        <div class="text-3xl font-bold text-blue-700">{{ stats.total_members }}</div>
                                    </div>
                                    <div class="p-3 bg-blue-200 rounded-lg">
                                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl border border-green-200 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-green-600 mb-1">Active Members</div>
                                        <div class="text-3xl font-bold text-green-700">{{ stats.active_subscriptions }}</div>
                                        <div class="text-xs text-green-600 mt-1">With Starter Kit</div>
                                    </div>
                                    <div class="p-3 bg-green-200 rounded-lg">
                                        <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl border border-purple-200 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-purple-600 mb-1">Estimated Reach</div>
                                        <div class="text-3xl font-bold text-purple-700">{{ estimatedRecipients }}</div>
                                        <div class="text-xs text-purple-600 mt-1">Based on filters</div>
                                    </div>
                                    <div class="p-3 bg-purple-200 rounded-lg">
                                        <svg class="w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Broadcast Form -->
                        <form @submit.prevent="sendBroadcast">
                            <!-- Filters -->
                            <div class="mb-8 p-6 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900">Target Audience</h3>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label class="flex items-center p-4 bg-white rounded-lg border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-colors">
                                        <input type="checkbox" v-model="form.filters.has_starter_kit" class="rounded text-blue-600 focus:ring-blue-500 w-5 h-5">
                                        <div class="ml-3">
                                            <span class="font-medium text-gray-900">Starter Kit Members Only</span>
                                            <p class="text-sm text-gray-500">Active members with starter kit</p>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-4 bg-white rounded-lg border-2 border-gray-200 hover:border-green-300 cursor-pointer transition-colors">
                                        <input type="checkbox" v-model="form.filters.active_subscription" class="rounded text-green-600 focus:ring-green-500 w-5 h-5">
                                        <div class="ml-3">
                                            <span class="font-medium text-gray-900">Active Subscriptions</span>
                                            <p class="text-sm text-gray-500">Currently subscribed members</p>
                                        </div>
                                    </label>
                                </div>
                                <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <p class="text-sm text-blue-700">
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                        Leave all unchecked to send to all members
                                    </p>
                                </div>
                            </div>

                            <!-- Subject -->
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                    Subject
                                </label>
                                <input
                                    v-model="form.subject"
                                    type="text"
                                    required
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors"
                                    placeholder="e.g., Important Platform Update"
                                />
                                <div v-if="errors.subject" class="text-red-600 text-sm mt-2 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ errors.subject }}
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="mb-8">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Message Content
                                </label>
                                <textarea
                                    v-model="form.body"
                                    required
                                    rows="12"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors font-mono text-sm"
                                    placeholder="Write your broadcast message here...&#10;&#10;Tips:&#10;- Be clear and concise&#10;- Include a call to action&#10;- Personalize when possible"
                                ></textarea>
                                <div class="flex items-center justify-between mt-2">
                                    <div v-if="errors.body" class="text-red-600 text-sm flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ errors.body }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ form.body.length }} characters
                                    </div>
                                </div>
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
import Swal from 'sweetalert2';

const props = defineProps<{
    stats: {
        total_members: number;
        active_subscriptions: number;
        with_starter_kit: number;
        both_filters: number;
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
    // Both filters checked
    if (form.filters.has_starter_kit && form.filters.active_subscription) {
        return props.stats.both_filters;
    }
    
    // Only starter kit filter
    if (form.filters.has_starter_kit) {
        return props.stats.with_starter_kit;
    }
    
    // Only active subscription filter
    if (form.filters.active_subscription) {
        return props.stats.active_subscriptions;
    }
    
    // No filters - all members
    return props.stats.total_members;
});

const sendBroadcast = async () => {
    const result = await Swal.fire({
        title: 'Confirm Broadcast',
        html: `
            <p class="text-gray-700 mb-2">Are you sure you want to send this message to</p>
            <p class="text-3xl font-bold text-blue-600 mb-2">${estimatedRecipients.value} members?</p>
            <p class="text-sm text-gray-500">This action cannot be undone.</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, send broadcast',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'px-6 py-2 rounded-lg font-medium',
            cancelButton: 'px-6 py-2 rounded-lg font-medium'
        }
    });

    if (result.isConfirmed) {
        form.post(route('admin.messages.broadcast.send'), {
            onSuccess: (page) => {
                // Extract success message from flash data
                const successMessage = page.props.flash?.success || 'Broadcast sent successfully!';
                
                Swal.fire({
                    title: 'Broadcast Sent!',
                    html: `
                        <div class="text-center">
                            <p class="text-gray-700 mb-2">${successMessage}</p>
                            <div class="mt-4 p-4 bg-green-50 rounded-lg">
                                <svg class="w-16 h-16 text-green-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonColor: '#059669',
                    confirmButtonText: 'View Messages',
                    customClass: {
                        popup: 'rounded-xl',
                        confirmButton: 'px-6 py-2 rounded-lg font-medium'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = route('admin.messages.index');
                    }
                });
                
                // Reset form
                form.reset();
            },
            onError: (errors) => {
                const errorMessage = errors.error || 'Failed to send broadcast message. Please try again.';
                
                Swal.fire({
                    title: 'Broadcast Failed',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-xl',
                        confirmButton: 'px-6 py-2 rounded-lg font-medium'
                    }
                });
            }
        });
    }
};
</script>
