<template>
  <div class="min-h-screen bg-gray-50">
    <AdminSidebar />
    
    <div class="lg:pl-64">
      <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Investor Documents</h1>
              <p class="mt-1 text-sm text-gray-500">Manage documents accessible to investors</p>
            </div>
            <Link
              :href="route('admin.investor-documents.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              Upload Document
            </Link>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                <DocumentTextIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Documents</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total_documents }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                <ArrowDownTrayIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Downloads</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total_downloads }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                <FolderIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Categories</p>
                <p class="text-2xl font-bold text-gray-900">{{ Object.keys(documentsGrouped).length }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Documents by Category -->
        <div class="space-y-6">
          <div v-for="(categoryData, category) in documentsGrouped" :key="category" class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
              <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <component :is="getIconComponent(categoryData.category.icon)" class="h-5 w-5 mr-2 text-blue-600" aria-hidden="true" />
                {{ categoryData.category.label }}
                <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                  {{ categoryData.documents.length }}
                </span>
              </h3>
              <p class="text-sm text-gray-600 mt-1">{{ categoryData.category.description }}</p>
            </div>
            
            <div class="divide-y divide-gray-200">
              <div v-for="document in categoryData.documents" :key="document.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                  <div class="flex items-center flex-1">
                    <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                      <DocumentIcon class="h-6 w-6 text-gray-600" aria-hidden="true" />
                    </div>
                    <div class="ml-4 flex-1">
                      <h4 class="text-sm font-medium text-gray-900">{{ document.title }}</h4>
                      <p class="text-sm text-gray-600 mt-1">{{ document.description }}</p>
                      <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                        <span class="flex items-center">
                          <CalendarIcon class="h-3 w-3 mr-1" aria-hidden="true" />
                          {{ formatDate(document.upload_date) }}
                        </span>
                        <span class="flex items-center">
                          <DocumentIcon class="h-3 w-3 mr-1" aria-hidden="true" />
                          {{ document.formatted_file_size }}
                        </span>
                        <span class="flex items-center">
                          <ArrowDownTrayIcon class="h-3 w-3 mr-1" aria-hidden="true" />
                          {{ document.download_count }} downloads
                        </span>
                        <span class="flex items-center">
                          <component :is="document.visible_to_all ? GlobeAltIcon : LockClosedIcon" class="h-3 w-3 mr-1" aria-hidden="true" />
                          {{ document.visible_to_all ? 'All Investors' : 'Specific Round' }}
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="flex items-center gap-2">
                    <button
                      @click="downloadDocument(document.id)"
                      class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                      title="Download"
                    >
                      <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <Link
                      :href="route('admin.investor-documents.edit', document.id)"
                      class="px-3 py-2 text-sm text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors"
                      title="Edit"
                    >
                      <PencilIcon class="h-4 w-4" aria-hidden="true" />
                    </Link>
                    <button
                      @click="archiveDocument(document.id)"
                      class="px-3 py-2 text-sm text-orange-600 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-colors"
                      title="Archive"
                    >
                      <ArchiveBoxIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <button
                      @click="deleteDocument(document.id)"
                      class="px-3 py-2 text-sm text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors"
                      title="Delete"
                    >
                      <TrashIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                  </div>
                </div>
              </div>
              
              <div v-if="categoryData.documents.length === 0" class="px-6 py-12 text-center text-gray-500">
                <DocumentIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
                <p class="text-sm">No documents in this category</p>
                <p class="text-xs mt-1">Upload documents to make them available to investors</p>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="Object.keys(documentsGrouped).length === 0" class="bg-white rounded-lg shadow p-12 text-center">
            <DocumentIcon class="h-16 w-16 mx-auto mb-4 text-gray-400" aria-hidden="true" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No documents uploaded yet</h3>
            <p class="text-gray-600 mb-6">
              Start by uploading your first investor document. Documents will be organized by category and made available to investors.
            </p>
            <Link
              :href="route('admin.investor-documents.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              Upload First Document
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AdminSidebar from '@/components/AdminSidebar.vue';
import { 
  PlusIcon, 
  DocumentTextIcon, 
  ArrowDownTrayIcon, 
  FolderIcon,
  DocumentIcon,
  CalendarIcon,
  GlobeAltIcon,
  LockClosedIcon,
  PencilIcon,
  ArchiveBoxIcon,
  TrashIcon
} from '@heroicons/vue/24/outline';
import {
  DocumentTextIcon as DocumentTextIconSolid,
  ChartBarIcon,
  CalculatorIcon,
  NewspaperIcon,
  ScaleIcon,
  AcademicCapIcon
} from '@heroicons/vue/24/solid';

interface DocumentCategory {
  value: string;
  label: string;
  icon: string;
  description: string;
}

interface Document {
  id: number;
  title: string;
  description: string;
  category: string;
  category_label: string;
  category_icon: string;
  file_name: string;
  formatted_file_size: string;
  upload_date: string;
  download_count: number;
  visible_to_all: boolean;
  status: string;
}

interface CategoryData {
  category: DocumentCategory;
  documents: Document[];
}

interface Stats {
  total_documents: number;
  total_downloads: number;
  most_downloaded: Document[];
}

const props = defineProps<{
  documentsGrouped: Record<string, CategoryData>;
  stats: Stats;
  categories: DocumentCategory[];
}>();

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getIconComponent = (iconName: string) => {
  const iconMap = {
    'document-text': DocumentTextIconSolid,
    'chart-bar': ChartBarIcon,
    'calculator': CalculatorIcon,
    'newspaper': NewspaperIcon,
    'scale': ScaleIcon,
    'academic-cap': AcademicCapIcon,
  };
  return iconMap[iconName] || DocumentTextIconSolid;
};

const downloadDocument = (documentId: number) => {
  window.open(route('admin.investor-documents.download', documentId), '_blank');
};

const archiveDocument = (documentId: number) => {
  if (confirm('Archive this document? It will no longer be visible to investors.')) {
    router.post(route('admin.investor-documents.archive', documentId));
  }
};

const deleteDocument = (documentId: number) => {
  if (confirm('Delete this document permanently? This action cannot be undone.')) {
    router.delete(route('admin.investor-documents.destroy', documentId));
  }
};
</script>