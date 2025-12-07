<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, ChatBubbleLeftRightIcon, UserGroupIcon } from '@heroicons/vue/24/outline';

interface Customer {
    id: number;
    name: string;
    whatsapp: string;
}

interface Tag {
    id: number;
    name: string;
}

interface Template {
    id: string;
    name: string;
    category: string;
    content: string;
    variables: string[];
}

interface Props {
    customers: Customer[];
    tags: Tag[];
    templates: Template[];
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    message: '',
    recipient_type: 'all' as 'all' | 'selected' | 'tagged',
    customer_ids: [] as number[],
    tag_ids: [] as number[],
});

const selectedTemplate = ref<string | null>(null);

const applyTemplate = () => {
    const template = props.templates.find(t => t.id === selectedTemplate.value);
    if (template) {
        form.message = template.content;
    }
};

const recipientCount = computed(() => {
    if (form.recipient_type === 'all') return props.customers.length;
    if (form.recipient_type === 'selected') return form.customer_ids.length;
    return 0; // Would need to calculate based on tags
});

const submit = () => {
    form.post(route('bizboost.whatsapp.broadcasts.store'));
};
</script>

<template>
    <Head title="Create Broadcast - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.whatsapp.broadcasts')" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Broadcasts
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <ChatBubbleLeftRightIcon class="h-7 w-7 text-green-600" aria-hidden="true" />
                            Create Broadcast
                        </h1>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Broadcast Name</label>
                            <input v-model="form.name" type="text" class="w-full rounded-md border-gray-300" placeholder="e.g., Weekend Sale Announcement" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Use Template (optional)</label>
                            <div class="flex gap-2">
                                <select v-model="selectedTemplate" class="flex-1 rounded-md border-gray-300">
                                    <option :value="null">Select a template...</option>
                                    <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                                </select>
                                <button type="button" @click="applyTemplate" :disabled="!selectedTemplate" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 disabled:opacity-50">
                                    Apply
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea v-model="form.message" rows="6" class="w-full rounded-md border-gray-300" placeholder="Type your message here..." required></textarea>
                            <p class="mt-1 text-sm text-gray-500">{{ form.message.length }}/4096 characters</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                            <div class="space-y-3">
                                <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer" :class="form.recipient_type === 'all' ? 'border-green-500 bg-green-50' : 'border-gray-200'">
                                    <input v-model="form.recipient_type" type="radio" value="all" class="text-green-600" />
                                    <div>
                                        <div class="font-medium">All Customers</div>
                                        <div class="text-sm text-gray-500">{{ customers.length }} customers with WhatsApp</div>
                                    </div>
                                </label>
                                <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer" :class="form.recipient_type === 'selected' ? 'border-green-500 bg-green-50' : 'border-gray-200'">
                                    <input v-model="form.recipient_type" type="radio" value="selected" class="text-green-600" />
                                    <div>
                                        <div class="font-medium">Select Customers</div>
                                        <div class="text-sm text-gray-500">Choose specific customers</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div v-if="form.recipient_type === 'selected'" class="max-h-48 overflow-y-auto border rounded-lg p-3">
                            <label v-for="customer in customers" :key="customer.id" class="flex items-center gap-2 py-1">
                                <input type="checkbox" :value="customer.id" v-model="form.customer_ids" class="rounded border-gray-300 text-green-600" />
                                <span>{{ customer.name }} ({{ customer.whatsapp }})</span>
                            </label>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center gap-2">
                                <UserGroupIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                <span class="text-sm text-gray-600">{{ recipientCount }} recipients selected</span>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link :href="route('bizboost.whatsapp.broadcasts')" class="px-4 py-2 text-gray-700 hover:text-gray-900">Cancel</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
                                Create Broadcast
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
