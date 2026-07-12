<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

defineProps<{
    documents: Array<{
        id: string;
        name: string;
        type: string;
        fileSize: number;
        mimeType: string;
        createdAt: string;
    }>;
}>();
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head title="Documents - PrimeEdge Advisory" />
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Documents</h1>
                <p class="text-gray-600 mt-1">Uploaded documents and files.</p>
            </div>
            <Link :href="route('primeedge.documents.create')" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white text-sm font-medium rounded-lg hover:from-emerald-700 hover:to-emerald-800 transition-all shadow-sm">Upload Document</Link>
        </div>
        <div v-if="documents?.length" class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Name</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Type</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Size</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Uploaded</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="doc in documents" :key="doc.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ doc.name }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ doc.type }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ (doc.fileSize / 1024).toFixed(1) }} KB</td>
                        <td class="px-6 py-4 text-gray-500">{{ doc.createdAt }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="text-center text-gray-500 py-12">No documents uploaded yet. <Link :href="route('primeedge.documents.create')" class="text-emerald-700 font-medium hover:underline">Upload your first document</Link>.</p>
    </PrimeEdgeAppLayout>
</template>
