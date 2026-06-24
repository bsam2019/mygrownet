<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';

interface Update {
    id: number; title: string; content: string; type: string; visibility: string;
    is_pinned: boolean; published_at: string; author: { name: string };
}

const props = defineProps<{
    venture: { id: number; title: string };
    updates: { data: Update[] };
}>();

const showCreate = ref(false);
const form = useForm({ title: '', content: '', type: 'general', visibility: 'public', send_notification: false, is_pinned: false });

const create = () => {
    form.post(route('admin.ventures.updates.create', props.venture.id), {
        onSuccess: () => { showCreate.value = false; form.reset(); }
    });
};
const formatDate = (date: string) => new Date(date).toLocaleDateString();
</script>

<template>
    <Head :title="`Updates - ${venture.title}`" />
    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">{{ venture.title }} - Updates</h1>
                    <button @click="showCreate = !showCreate" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">{{ showCreate ? 'Cancel' : 'Create Update' }}</button>
                </div>
                <form v-if="showCreate" @submit.prevent="create" class="mb-6 rounded-lg bg-white p-6 shadow space-y-4">
                    <input v-model="form.title" placeholder="Update title" class="w-full rounded-md border-gray-300" required />
                    <textarea v-model="form.content" placeholder="Content" rows="4" class="w-full rounded-md border-gray-300" required></textarea>
                    <div class="grid grid-cols-2 gap-4">
                        <select v-model="form.type" class="rounded-md border-gray-300"><option value="milestone">Milestone</option><option value="financial">Financial</option><option value="operational">Operational</option><option value="announcement">Announcement</option><option value="alert">Alert</option><option value="general">General</option></select>
                        <select v-model="form.visibility" class="rounded-md border-gray-300"><option value="public">Public</option><option value="investors_only">Investors Only</option><option value="shareholders_only">Shareholders Only</option></select>
                    </div>
                    <label class="flex items-center gap-2"><input v-model="form.is_pinned" type="checkbox" class="rounded" /> Pin this update</label>
                    <label class="flex items-center gap-2"><input v-model="form.send_notification" type="checkbox" class="rounded" /> Send notification to investors</label>
                    <button type="submit" :disabled="form.processing" class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white">{{ form.processing ? 'Creating...' : 'Create Update' }}</button>
                </form>
                <div class="space-y-4">
                    <div v-for="update in updates.data" :key="update.id" class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ update.title }}</h3>
                                <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ update.content }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span v-if="update.is_pinned" class="rounded bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800">Pinned</span>
                                <span class="rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800">{{ update.type }}</span>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
                            <span>By: {{ update.author.name }}</span>
                            <span>{{ formatDate(update.published_at) }}</span>
                            <span>Visibility: {{ update.visibility.replace('_', ' ') }}</span>
                        </div>
                    </div>
                    <div v-if="updates.data.length === 0" class="rounded-lg bg-white p-12 text-center text-gray-500">No updates yet.</div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
