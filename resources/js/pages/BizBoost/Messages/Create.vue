<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    ArrowLeftIcon,
    PaperAirplaneIcon,
    UserIcon,
} from '@heroicons/vue/24/outline';

interface TeamMember {
    id: number;
    name: string;
    email: string;
}

interface Props {
    teamMembers: TeamMember[];
    replyTo?: number;
}

const props = defineProps<Props>();

const form = ref({
    recipient_id: '',
    subject: '',
    body: '',
});
const sending = ref(false);
const errors = ref<Record<string, string>>({});

const sendMessage = async () => {
    if (sending.value) return;
    
    errors.value = {};
    
    if (!form.value.recipient_id) {
        errors.value.recipient_id = 'Please select a recipient';
        return;
    }
    if (!form.value.subject.trim()) {
        errors.value.subject = 'Subject is required';
        return;
    }
    if (!form.value.body.trim()) {
        errors.value.body = 'Message body is required';
        return;
    }
    
    sending.value = true;
    try {
        const response = await fetch('/bizboost/messages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(form.value),
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            router.visit('/bizboost/messages?filter=sent');
        } else {
            errors.value = data.errors || { general: 'Failed to send message' };
        }
    } catch (error) {
        console.error('Failed to send message:', error);
        errors.value = { general: 'Failed to send message. Please try again.' };
    } finally {
        sending.value = false;
    }
};
</script>

<template>
    <Head title="New Message - BizBoost" />

    <BizBoostLayout>
        <div class="max-w-3xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Link
                    href="/bizboost/messages"
                    class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                >
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
                <h1 class="text-xl font-bold text-slate-900">New Message</h1>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <form @submit.prevent="sendMessage" class="p-6 space-y-6">
                    <!-- Error Alert -->
                    <div v-if="errors.general" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errors.general }}</p>
                    </div>

                    <!-- Recipient -->
                    <div>
                        <label for="recipient" class="block text-sm font-medium text-slate-700 mb-2">
                            To
                        </label>
                        <div class="relative">
                            <UserIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" aria-hidden="true" />
                            <select
                                id="recipient"
                                v-model="form.recipient_id"
                                :class="[
                                    'w-full pl-10 pr-4 py-3 border rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 appearance-none',
                                    errors.recipient_id ? 'border-red-300' : 'border-slate-200'
                                ]"
                            >
                                <option value="">Select a team member...</option>
                                <option v-for="member in teamMembers" :key="member.id" :value="member.id">
                                    {{ member.name }} ({{ member.email }})
                                </option>
                            </select>
                        </div>
                        <p v-if="errors.recipient_id" class="mt-1 text-sm text-red-600">{{ errors.recipient_id }}</p>
                        <p v-if="teamMembers.length === 0" class="mt-2 text-sm text-slate-500">
                            No team members found. <Link href="/bizboost/team/invite" class="text-violet-600 hover:underline">Invite team members</Link> first.
                        </p>
                    </div>

                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block text-sm font-medium text-slate-700 mb-2">
                            Subject
                        </label>
                        <input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            placeholder="Enter message subject..."
                            :class="[
                                'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500',
                                errors.subject ? 'border-red-300' : 'border-slate-200'
                            ]"
                        />
                        <p v-if="errors.subject" class="mt-1 text-sm text-red-600">{{ errors.subject }}</p>
                    </div>

                    <!-- Body -->
                    <div>
                        <label for="body" class="block text-sm font-medium text-slate-700 mb-2">
                            Message
                        </label>
                        <textarea
                            id="body"
                            v-model="form.body"
                            rows="8"
                            placeholder="Write your message..."
                            :class="[
                                'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 resize-none',
                                errors.body ? 'border-red-300' : 'border-slate-200'
                            ]"
                        ></textarea>
                        <p v-if="errors.body" class="mt-1 text-sm text-red-600">{{ errors.body }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <Link
                            href="/bizboost/messages"
                            class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="sending"
                            class="inline-flex items-center gap-2 px-6 py-2 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <PaperAirplaneIcon class="h-4 w-4" aria-hidden="true" />
                            {{ sending ? 'Sending...' : 'Send Message' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </BizBoostLayout>
</template>
