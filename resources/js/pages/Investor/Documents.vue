<template>
  <InvestorLayout :investor="investor" page-title="Documents" active-page="documents">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Investment Documents</h1>
      <p class="mt-1 text-gray-600">Access your investment documents, reports, and legal files</p>
    </div>

    <!-- Search and Filter -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
      <div class="flex-1 relative">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search documents..."
          class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow shadow-sm"
        />
      </div>
      <select
        v-model="selectedCategory"
        class="px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
      >
        <option value="">All Categories</option>
        <option v-for="category in categories" :key="category.value" :value="category.value">
          {{ category.label }}
        </option>
      </select>
    </div>

    <!-- Document Categories -->
    <div class="space-y-6">
      <div 
        v-for="(categoryData, category) in filteredDocuments" 
        :key="category" 
        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow"
      >
        <!-- Category Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div :class="getCategoryIconBg(category)" class="w-10 h-10 rounded-xl flex items-center justify-center">
                <component :is="getIconComponent(categoryData.category.icon)" class="h-5 w-5 text-white" aria-hidden="true" />
              </div>
              <div>
                <h3 class="font-semibold text-gray-900">{{ categoryData.category.label }}</h3>
                <p class="text-xs text-gray-500">{{ categoryData.category.description }}</p>
              </div>
            </div>
            <span class="px-3 py-1 bg-blue-50 text-blue-700 text-sm font-semibold rounded-full">
              {{ categoryData.documents.length }} {{ categoryData.documents.length === 1 ? 'file' : 'files' }}
            </span>
          </div>
        </div>
        
        <!-- Documents List -->
        <div class="divide-y divide-gray-100">
          <div 
            v-for="document in categoryData.documents" 
            :key="document.id" 
            class="px-6 py-4 hover:bg-gray-50/50 transition-colors group"
          >
            <div class="flex items-center gap-4">
              <!-- File Icon -->
              <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl flex items-center justify-center border border-blue-100 group-hover:scale-105 transition-transform">
                <DocumentIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
              </div>
              
              <!-- Document Info -->
              <div class="flex-1 min-w-0">
                <h4 class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ document.title }}</h4>
                <p class="text-sm text-gray-500 mt-0.5 line-clamp-1">{{ document.description }}</p>
                <div class="flex items-center gap-4 mt-2">
                  <span class="inline-flex items-center text-xs text-gray-400">
                    <CalendarIcon class="h-3.5 w-3.5 mr-1" aria-hidden="true" />
                    {{ formatDate(document.upload_date) }}
                  </span>
                  <span class="inline-flex items-center text-xs text-gray-400">
                    <DocumentIcon class="h-3.5 w-3.5 mr-1" aria-hidden="true" />
                    {{ document.formatted_file_size }}
                  </span>
                  <span class="inline-flex items-center text-xs text-gray-400">
                    <ArrowDownTrayIcon class="h-3.5 w-3.5 mr-1" aria-hidden="true" />
                    {{ document.download_count }} downloads
                  </span>
                </div>
              </div>
              
              <!-- Actions -->
              <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button
                  v-if="document.can_preview"
                  @click="previewDocument(document)"
                  class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                  aria-label="Preview document"
                >
                  <EyeIcon class="h-5 w-5" aria-hidden="true" />
                </button>
                <button
                  @click="downloadDocument(document.id)"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                >
                  <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                  Download
                </button>
              </div>
            </div>
          </div>
          
          <!-- Empty Category State -->
          <div v-if="categoryData.documents.length === 0" class="px-6 py-12 text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
              <DocumentIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
            </div>
            <p class="text-sm text-gray-500">No documents in this category</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="Object.keys(filteredDocuments).length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
      <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <DocumentIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">No documents found</h3>
      <p class="text-gray-500 mb-4">
        {{ searchQuery ? 'Try adjusting your search terms' : 'Documents will appear here when uploaded' }}
      </p>
      <button
        v-if="searchQuery"
        @click="clearSearch"
        class="text-blue-600 hover:text-blue-700 font-medium"
      >
        Clear search
      </button>
    </div>

    <!-- Help Section -->
    <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-6">
      <div class="flex items-start gap-4">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
          <InformationCircleIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
        </div>
        <div>
          <h4 class="font-semibold text-blue-900 mb-1">Need help accessing documents?</h4>
          <p class="text-sm text-blue-700 mb-3">
            If you're having trouble accessing or downloading documents, or need a specific document that's not listed, please contact our investor relations team.
          </p>
          <a
            href="mailto:investors@mygrownet.com"
            class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700"
          >
            Contact Investor Relations
            <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
          </a>
        </div>
      </div>
    </div>
  </InvestorLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import {
  DocumentIcon,
  CalendarIcon,
  ArrowDownTrayIcon,
  EyeIcon,
  InformationCircleIcon,
  ArrowRightIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';
import {
  DocumentTextIcon,
  ChartBarIcon,
  CalculatorIcon,
  NewspaperIcon,
  ScaleIcon,
  AcademicCapIcon
} from '@heroicons/vue/24/solid';

interface Investor {
  name: string;
  email: string;
}

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
  file_name: string;
  formatted_file_size: string;
  upload_date: string;
  download_count: number;
  can_preview: boolean;
}

interface CategoryData {
  category: DocumentCategory;
  documents: Document[];
}

const props = defineProps<{
  investor: Investor;
  groupedDocuments: Record<string, CategoryData>;
  categories: DocumentCategory[];
}>();

const searchQuery = ref('');
const selectedCategory = ref('');

const filteredDocuments = computed(() => {
  let filtered = { ...props.groupedDocuments };

  if (selectedCategory.value) {
    filtered = Object.fromEntries(
      Object.entries(filtered).filter(([category]) => category === selectedCategory.value)
    );
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = Object.fromEntries(
      Object.entries(filtered).map(([category, categoryData]) => [
        category,
        {
          ...categoryData,
          documents: categoryData.documents.filter(doc =>
            doc.title.toLowerCase().includes(query) ||
            doc.description.toLowerCase().includes(query) ||
            doc.file_name.toLowerCase().includes(query)
          )
        }
      ]).filter(([, categoryData]) => categoryData.documents.length > 0)
    );
  }

  return filtered;
});

const downloadDocument = (documentId: number) => {
  window.open(route('investor.documents.download', documentId), '_blank');
};

const previewDocument = (document: Document) => {
  // Implement preview logic
};

const clearSearch = () => {
  searchQuery.value = '';
  selectedCategory.value = '';
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getIconComponent = (iconName: string) => {
  const iconMap: Record<string, any> = {
    'document-text': DocumentTextIcon,
    'chart-bar': ChartBarIcon,
    'calculator': CalculatorIcon,
    'newspaper': NewspaperIcon,
    'scale': ScaleIcon,
    'academic-cap': AcademicCapIcon,
  };
  return iconMap[iconName] || DocumentTextIcon;
};

const getCategoryIconBg = (category: string): string => {
  const colors: Record<string, string> = {
    'legal': 'bg-gradient-to-br from-violet-500 to-purple-600',
    'financial': 'bg-gradient-to-br from-emerald-500 to-teal-600',
    'reports': 'bg-gradient-to-br from-blue-500 to-indigo-600',
    'tax': 'bg-gradient-to-br from-amber-500 to-orange-600',
    'general': 'bg-gradient-to-br from-gray-500 to-slate-600',
  };
  return colors[category] || 'bg-gradient-to-br from-blue-500 to-indigo-600';
};
</script>
