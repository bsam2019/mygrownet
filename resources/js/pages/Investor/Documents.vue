<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
          <div>
            <Link
              :href="route('investor.dashboard')"
              class="text-sm text-gray-600 hover:text-gray-900 flex items-center mb-2"
            >
              <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
              Back to Dashboard
            </Link>
            <h1 class="text-2xl font-bold text-gray-900">Investment Documents</h1>
            <p class="text-sm text-gray-600">Access your investment documents and reports</p>
          </div>
          <Link
            :href="route('investor.logout')"
            method="post"
            as="button"
            class="text-sm text-gray-600 hover:text-gray-900"
          >
            Logout
          </Link>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Search and Filter -->
      <div class="mb-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search documents..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <select
          v-model="selectedCategory"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="">All Categories</option>
          <option v-for="category in categories" :key="category.value" :value="category.value">
            {{ category.label }}
          </option>
        </select>
      </div>

      <!-- Document Categories -->
      <div class="space-y-6">
        <div v-for="(categoryData, category) in filteredDocuments" :key="category" class="bg-white rounded-xl shadow-lg">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <component :is="getIconComponent(categoryData.category.icon)" class="h-5 w-5 mr-2 text-blue-600" aria-hidden="true" />
              {{ categoryData.category.label }}
              <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                {{ categoryData.documents.length }}
              </span>
            </h3>
          </div>
          
          <div class="divide-y divide-gray-200">
            <div v-for="document in categoryData.documents" :key="document.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
              <div class="flex items-center justify-between">
                <div class="flex items-center flex-1">
                  <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <DocumentIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
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
                    </div>
                  </div>
                </div>
                
                <div class="flex items-center gap-2">
                  <button
                    v-if="document.can_preview"
                    @click="previewDocument(document)"
                    class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                  >
                    <EyeIcon class="h-4 w-4 mr-1 inline" aria-hidden="true" />
                    Preview
                  </button>
                  <button
                    @click="downloadDocument(document.id)"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                  >
                    <ArrowDownTrayIcon class="h-4 w-4 mr-1 inline" aria-hidden="true" />
                    Download
                  </button>
                </div>
              </div>
            </div>
            
            <div v-if="categoryData.documents.length === 0" class="px-6 py-12 text-center text-gray-500">
              <DocumentIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
              <p class="text-sm">No documents available in this category</p>
              <p class="text-xs mt-1">New documents will appear here when uploaded</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="Object.keys(filteredDocuments).length === 0" class="text-center py-12">
        <DocumentIcon class="h-16 w-16 mx-auto mb-4 text-gray-400" aria-hidden="true" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No documents found</h3>
        <p class="text-gray-600 mb-4">
          {{ searchQuery ? 'Try adjusting your search terms' : 'Documents will appear here when uploaded by administrators' }}
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
      <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex">
          <InformationCircleIcon class="h-6 w-6 text-blue-600 mr-3 flex-shrink-0" aria-hidden="true" />
          <div>
            <h4 class="text-sm font-semibold text-blue-900 mb-1">Need help accessing documents?</h4>
            <p class="text-sm text-blue-800 mb-3">
              If you're having trouble accessing or downloading documents, or need a specific document that's not listed, please contact our investor relations team.
            </p>
            <a
              href="mailto:investors@mygrownet.com"
              class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700"
            >
              Contact Investor Relations
              <ArrowRightIcon class="h-4 w-4 ml-1" aria-hidden="true" />
            </a>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
  ArrowLeftIcon,
  DocumentIcon,
  CalendarIcon,
  ArrowDownTrayIcon,
  EyeIcon,
  InformationCircleIcon,
  ArrowRightIcon,
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
  investment_date?: string;
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
  category_label: string;
  category_icon: string;
  file_name: string;
  formatted_file_size: string;
  upload_date: string;
  download_count: number;
  can_preview: boolean;
  is_pdf: boolean;
  is_image: boolean;
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

// Props and reactive data
const searchQuery = ref('');
const selectedCategory = ref('');
const previewDocument = ref(null);

// Computed properties for filtering
const filteredDocuments = computed(() => {
  let filtered = { ...props.groupedDocuments };

  // Filter by category
  if (selectedCategory.value) {
    filtered = Object.fromEntries(
      Object.entries(filtered).filter(([category]) => category === selectedCategory.value)
    );
  }

  // Filter by search query
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

// Methods
const downloadDocument = async (documentId: number) => {
  window.open(route('investor.documents.download', documentId), '_blank');
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
  const iconMap = {
    'document-text': DocumentTextIcon,
    'chart-bar': ChartBarIcon,
    'calculator': CalculatorIcon,
    'newspaper': NewspaperIcon,
    'scale': ScaleIcon,
    'academic-cap': AcademicCapIcon,
  };
  return iconMap[iconName] || DocumentTextIcon;
};
</script>
