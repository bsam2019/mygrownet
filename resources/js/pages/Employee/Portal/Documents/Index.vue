<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ref, watch } from 'vue';
import {
    DocumentTextIcon,
    DocumentArrowDownIcon,
    FolderIcon,
    LockClosedIcon,
} from '@heroicons/vue/24/outline';

interface Document {
    id: number;
    title: string;
    description: string;
    category: string;
    filename: string;
    original_name: string;
    mime_type: string;
    size: number;
    is_confidential: boolean;
    valid_from: string;
    valid_until: string;
    created_at: string;
}

interface Props {
    documents: {
        data: Document[];
        links: any;
    };
    categories: string[];
    filters: {
        category?: string;
    };
}

const props = defineProps<Props>();

const selectedCategory = ref(props.filters.category || '');

const applyFilters = () => {
    router.get(route('employee.portal.documents'), {
        category: selectedCategory.value || undefined,
    }, { preserveState: true });
};

watch(selectedCategory, applyFilters);

const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getFileIcon = (mimeType: string) => {
    if (mimeType.includes('pdf')) return '📄';
    if (mimeType.includes('image')) return '🖼️';
    if (mimeType.includes('word') || mimeType.includes('document')) return '📝';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return '📊';
    return '📎';
};

const getCategoryLabel = (category: string) => {
    const labels: Record<string, string> = {
        'contract': 'Contracts',
        'payslip': 'Payslips',
        'policy': 'Policies',
        'certificate': 'Certificates',
        'personal': 'Personal Documents',
        'other': 'Other',
    };
    return labels[category] || category;
};
</script>

<template>
    <Head title="Documents" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Documents</h1>
                <p class="text-gray-500 mt-1">Access your contracts, payslips, and other documents</p>
            </div>

            <!-- Documents List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Documents</h2>
                    <select v-model="selectedCategory"
                        class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">
                            {{ getCategoryLabel(cat) }}
                        </option>
                    </select>
                </div>

                <div class="divide-y divide-gray-100">
                    <div v-for="doc in documents.data" :key="doc.id"
                        class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-4">
                            <div class="text-3xl">{{ getFileIcon(doc.mime_type) }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-medium text-gray-900">{{ doc.title }}</h3>
                                            <LockClosedIcon v-if="doc.is_confidential" 
                                                class="h-4 w-4 text-amber-500" 
                                                title="Confidential"
                                                aria-hidden="true" />
                                        </div>
                                        <p v-if="doc.description" class="text-sm text-gray-500 mt-1">
                                            {{ doc.description }}
                                        </p>
                                    </div>
                                    <a :href="`/storage/documents/${doc.filename}`" 
                                        target="_blank"
                                        class="flex items-center gap-2 px-3 py-1.5 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                                        <DocumentArrowDownIcon class="h-4 w-4" aria-hidden="true" />
                                        Download
                                    </a>
                                </div>
                                <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                    <span class="px-2 py-0.5 bg-gray-100 rounded-full text-xs">
                                        {{ getCategoryLabel(doc.category) }}
                                    </span>
                                    <span>{{ formatFileSize(doc.size) }}</span>
                                    <span>{{ new Date(doc.created_at).toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="documents.data.length === 0" class="p-12 text-center">
                        <FolderIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                        <h3 class="text-lg font-medium text-gray-900">No documents</h3>
                        <p class="text-gray-500 mt-1">You don't have any documents yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
