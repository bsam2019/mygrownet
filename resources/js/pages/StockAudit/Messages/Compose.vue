<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import {
    PaperAirplaneIcon,
    ArrowLeftIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    users: { id: number; name: string; email: string }[];
}>();

const page = usePage();
const isSubdomain = computed(() => (page.props as any).routeName?.startsWith('stockflow.sub.'));

const baseUrl = computed(() => isSubdomain.value ? '' : '/stock-audit');
const csrfToken = computed(() => (page.props as any).csrf_token ?? '');

const recipientId = ref<number | null>(null);
const subject = ref('');
const body = ref('');
const submitting = ref(false);
const error = ref('');

const sendMessage = async () => {
    error.value = '';
    if (!recipientId.value) { error.value = 'Please select a recipient.'; return; }
    if (!subject.value.trim()) { error.value = 'Please enter a subject.'; return; }
    if (!body.value.trim()) { error.value = 'Please enter a message.'; return; }
    if (submitting.value) return;

    submitting.value = true;
    try {
        const res = await fetch(`${baseUrl.value}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                recipient_id: recipientId.value,
                subject: subject.value.trim(),
                body: body.value.trim(),
            }),
        });
        if (res.ok) {
            router.visit(`${baseUrl.value}/messages`);
        } else {
            const data = await res.json();
            error.value = data.error || 'Failed to send message.';
        }
    } catch {
        error.value = 'An error occurred. Please try again.';
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <StockAuditLayout title="Compose Message">
        <div class="p-4 sm:p-6 lg:p-8 max-w-3xl mx-auto">
            <div class="flex items-center gap-4 mb-6">
                <Link
                    :href="`${baseUrl}/messages`"
                    class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                >
                    <ArrowLeftIcon class="h-5 w-5" />
                </Link>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Compose Message</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Send a message to a team member</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-6 space-y-5">
                    <div v-if="error" class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                        {{ error }}
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Recipient</label>
                        <select
                            v-model="recipientId"
                            class="w-full rounded-lg border-gray-200 text-sm focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option :value="null" disabled>Select a recipient...</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                        <p v-if="users.length === 0" class="text-xs text-gray-400 mt-1">No other users available in your company.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
                        <input
                            v-model="subject"
                            type="text"
                            placeholder="Message subject..."
                            class="w-full rounded-lg border-gray-200 text-sm focus:ring-emerald-500 focus:border-emerald-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
                        <textarea
                            v-model="body"
                            rows="6"
                            placeholder="Write your message..."
                            class="w-full rounded-lg border-gray-200 text-sm focus:ring-emerald-500 focus:border-emerald-500 resize-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button
                            @click="sendMessage"
                            :disabled="submitting"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm"
                        >
                            <PaperAirplaneIcon class="h-4 w-4" />
                            {{ submitting ? 'Sending...' : 'Send Message' }}
                        </button>
                        <Link
                            :href="`${baseUrl}/messages`"
                            class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors"
                        >Cancel</Link>
                    </div>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
