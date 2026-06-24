<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref } from 'vue';

interface Document {
    id: number; title: string; type: string; visibility: string;
    file_name: string; file_size: number; download_count: number;
    created_at: string; uploader: { name: string };
}

const props = defineProps<{
    venture: { id: number; title: string };
    documents: { data: Document[] };
}>();

const showUpload = ref(false);
const form = useForm({ title: '', description: '', type: 'other', visibility: 'investors_only', file: null as File | null, is_confidential: false });

const upload = () => {
    form.post(route('admin.ventures.documents.upload', props.venture.id), {
        onSuccess: () => { showUpload.value = false; form.reset(); }
    });
};
const formatDate = (date: string) => new Date(date).toLocaleDateString();
const formatSize = (bytes: number) => bytes > 1024 * 1024 ? (bytes / (1024 * 1024)).toFixed(1) + ' MB' : (bytes / 1024).toFixed(1) + ' KB';
</script>

<template>
    <Head :title="`Documents - ${venture.title}`" />
    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">{{ venture.title }} - Documents</h1>
                    <button @click="showUpload = !showUpload" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">{{ showUpload ? 'Cancel' : 'Upload Document' }}</button>
                </div>
                <form v-if="showUpload" @submit.prevent="upload" class="mb-6 rounded-lg bg-white p-6 shadow space-y-4">
                    <input v-model="form.title" placeholder="Document title" class="w-full rounded-md border-gray-300" required />
                    <textarea v-model="form.description" placeholder="Description" class="w-full rounded-md border-gray-300"></textarea>
                    <div class="grid grid-cols-2 gap-4">
                        <select v-model="form.type" class="rounded-md border-gray-300"><option value="business_plan">Business Plan</option><option value="financial_report">Financial Report</option><option value="shareholder_agreement">Shareholder Agreement</option><option value="certificate">Certificate</option><option value="compliance_document">Compliance</option><option value="update_report">Update Report</option><option value="other">Other</option></select>
                        <select v-model="form.visibility" class="rounded-md border-gray-300"><option value="public">Public</option><option value="investors_only">Investors Only</option><option value="shareholders_only">Shareholders Only</option><option value="admin_only">Admin Only</option></select>
                    </div>
                    <input type="file" @input="form.file = ($event.target as HTMLInputElement).files![0]" class="w-full" required />
                    <button type="submit" :disabled="form.processing" class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white">{{ form.processing ? 'Uploading...' : 'Upload' }}</button>
                </form>
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Title</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Type</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Visibility</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Size</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Downloads</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th><th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Uploaded By</th></tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="doc in documents.data" :key="doc.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ doc.title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ doc.type.replace('_', ' ') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ doc.visibility.replace('_', ' ') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ formatSize(doc.file_size) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ doc.download_count }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(doc.created_at) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ doc.uploader.name }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="documents.data.length === 0" class="p-12 text-center text-gray-500">No documents uploaded yet.</div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
