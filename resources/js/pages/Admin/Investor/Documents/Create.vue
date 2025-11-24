<template>
  <div class="min-h-screen bg-gray-50">
    <CustomAdminSidebar />
    
    <div class="lg:pl-64">
      <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center gap-4">
            <Link
              :href="route('admin.investor-documents.index')"
              class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
            </Link>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Upload Document</h1>
              <p class="mt-1 text-sm text-gray-500">Add a new document for investors to access</p>
            </div>
          </div>
        </div>

        <!-- Upload Form -->
        <div class="max-w-2xl">
          <form @submit.prevent="submitForm" class="bg-white rounded-lg shadow p-6 space-y-6">
            <!-- File Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Document File *
              </label>
              <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                <div class="space-y-1 text-center">
                  <DocumentIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                  <div class="flex text-sm text-gray-600">
                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                      <span>Upload a file</span>
                      <input
                        id="file-upload"
                        name="file"
                        type="file"
                        class="sr-only"
                        @change="handleFileChange"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt"
                        required
                      />
                    </label>
                    <p class="pl-1">or drag and drop</p>
                  </div>
                  <p class="text-xs text-gray-500">
                    PDF, Word, Excel, Images, Text files up to 10MB
                  </p>
                </div>
              </div>
              
              <!-- Selected File Display -->
              <div v-if="selectedFile" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <DocumentIcon class="h-5 w-5 text-blue-600 mr-2" aria-hidden="true" />
                    <div>
                      <p class="text-sm font-medium text-blue-900">{{ selectedFile.name }}</p>
                      <p class="text-xs text-blue-600">{{ formatFileSize(selectedFile.size) }}</p>
                    </div>
                  </div>
                  <button
                    type="button"
                    @click="removeFile"
                    class="text-blue-600 hover:text-blue-800"
                  >
                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                  </button>
                </div>
              </div>

              <div v-if="errors.file" class="mt-1 text-sm text-red-600">
                {{ errors.file }}
              </div>
            </div>

            <!-- Document Title -->
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                Document Title *
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Enter document title"
              />
              <div v-if="errors.title" class="mt-1 text-sm text-red-600">
                {{ errors.title }}
              </div>
            </div>

            <!-- Document Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Brief description of the document"
              ></textarea>
              <div v-if="errors.description" class="mt-1 text-sm text-red-600">
                {{ errors.description }}
              </div>
            </div>

            <!-- Category Selection -->
            <div>
              <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                Category *
              </label>
              <select
                id="category"
                v-model="form.category"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select a category</option>
                <option v-for="category in categories" :key="category.value" :value="category.value">
                  {{ category.label }}
                </option>
              </select>
              <div v-if="errors.category" class="mt-1 text-sm text-red-600">
                {{ errors.category }}
              </div>
            </div>

            <!-- Visibility Settings -->
            <div class="space-y-4">
              <h3 class="text-sm font-medium text-gray-700">Visibility Settings</h3>
              
              <div class="space-y-3">
                <label class="flex items-start">
                  <input
                    v-model="form.visible_to_all"
                    type="radio"
                    :value="true"
                    class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                  />
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">All Investors</div>
                    <div class="text-sm text-gray-500">Document will be visible to all current and future investors</div>
                  </div>
                </label>
                
                <label class="flex items-start">
                  <input
                    v-model="form.visible_to_all"
                    type="radio"
                    :value="false"
                    class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                  />
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">Specific Investment Round</div>
                    <div class="text-sm text-gray-500">Document will only be visible to investors from a specific round</div>
                  </div>
                </label>
              </div>

              <!-- Investment Round Selection -->
              <div v-if="!form.visible_to_all" class="ml-7">
                <label for="investment_round_id" class="block text-sm font-medium text-gray-700 mb-2">
                  Investment Round *
                </label>
                <select
                  id="investment_round_id"
                  v-model="form.investment_round_id"
                  :required="!form.visible_to_all"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Select investment round</option>
                  <option v-for="round in investmentRounds" :key="round.id" :value="round.id">
                    {{ round.name }}
                  </option>
                </select>
                <div v-if="errors.investment_round_id" class="mt-1 text-sm text-red-600">
                  {{ errors.investment_round_id }}
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
              <Link
                :href="route('admin.investor-documents.index')"
                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="uploading"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <span v-if="uploading" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Uploading...
                </span>
                <span v-else>Upload Document</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import CustomAdminSidebar from '@/components/CustomAdminSidebar.vue';
import { 
  ArrowLeftIcon,
  DocumentIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline';

interface DocumentCategory {
  value: string;
  label: string;
  icon: string;
  description: string;
}

interface InvestmentRound {
  id: number;
  name: string;
  status: string;
}

const props = defineProps<{
  categories: DocumentCategory[];
  investmentRounds: InvestmentRound[];
  errors?: Record<string, string>;
}>();

const selectedFile = ref<File | null>(null);
const uploading = ref(false);

const form = reactive({
  title: '',
  description: '',
  category: '',
  visible_to_all: true,
  investment_round_id: null as number | null,
});

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0];
    
    // Auto-generate title from filename if empty
    if (!form.title) {
      const filename = target.files[0].name;
      const nameWithoutExtension = filename.substring(0, filename.lastIndexOf('.')) || filename;
      form.title = nameWithoutExtension.replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
  }
};

const removeFile = () => {
  selectedFile.value = null;
  const fileInput = document.getElementById('file-upload') as HTMLInputElement;
  if (fileInput) {
    fileInput.value = '';
  }
};

const formatFileSize = (bytes: number): string => {
  const units = ['B', 'KB', 'MB', 'GB'];
  let size = bytes;
  let unitIndex = 0;
  
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024;
    unitIndex++;
  }
  
  return `${size.toFixed(1)} ${units[unitIndex]}`;
};

const submitForm = () => {
  if (!selectedFile.value) {
    return;
  }

  uploading.value = true;

  const formData = new FormData();
  formData.append('file', selectedFile.value);
  formData.append('title', form.title);
  formData.append('description', form.description);
  formData.append('category', form.category);
  formData.append('visible_to_all', form.visible_to_all ? '1' : '0');
  
  if (!form.visible_to_all && form.investment_round_id) {
    formData.append('investment_round_id', form.investment_round_id.toString());
  }

  router.post(route('admin.investor-documents.store'), formData, {
    onFinish: () => {
      uploading.value = false;
    },
    forceFormData: true,
  });
};
</script>