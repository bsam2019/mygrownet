<template>
  <AdminLayout title="Content Management">
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <Link
            :href="route('admin.content-management.index')"
            class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 mb-4"
          >
            ← Back to Content List
          </Link>
          <h1 class="text-2xl font-semibold text-gray-900">
            {{ isEditing ? 'Edit Content' : 'Add New Content' }}
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            Upload e-books, videos, or training materials for starter kits
          </p>
          
          <!-- Draft Loaded Notice -->
          <div v-if="draft && !isEditing" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-start gap-2">
            <svg class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
              <p class="text-sm font-medium text-blue-900">Draft restored</p>
              <p class="text-xs text-blue-700 mt-0.5">Your previous work has been automatically restored. Changes are saved as you type.</p>
            </div>
            <button
              type="button"
              @click="clearDraft(); location.reload()"
              class="text-xs text-blue-600 hover:text-blue-800 font-medium"
            >
              Clear
            </button>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Basic Information -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
            
            <div class="space-y-4">
              <!-- Title -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Title <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.title"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="e.g., Financial Planning for Beginners"
                />
                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Brief description of the content..."
                ></textarea>
              </div>

              <!-- Category and Tier -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Category <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.category"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">Select category</option>
                    <option value="ebook">E-Book</option>
                    <option value="video">Video</option>
                    <option value="training">Training</option>
                    <option value="tool">Tool</option>
                    <option value="library">Library</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tier Access <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.tier_restriction"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">Select tier access</option>
                    <option value="all">All Tiers</option>
                    <option v-for="tier in tiers" :key="tier.key" :value="tier.key">
                      {{ tier.name }} (K{{ tier.price }}) and above
                    </option>
                  </select>
                  <p class="mt-1 text-xs text-gray-500">Select which tier members can access this content</p>
                </div>
              </div>

              <!-- Unlock Day and Value -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Unlock Day <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model.number="form.unlock_day"
                    type="number"
                    min="0"
                    max="30"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="0"
                  />
                  <p class="mt-1 text-xs text-gray-500">0 = Immediate access, 1-30 = days after purchase</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Estimated Value (K) <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model.number="form.estimated_value"
                    type="number"
                    min="0"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="0"
                  />
                  <p class="mt-1 text-xs text-gray-500">Perceived value in Kwacha</p>
                </div>
              </div>
            </div>
          </div>

          <!-- File Upload -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">File Upload</h2>
            
            <div class="space-y-4">
              <FileUpload
                v-model="form.file"
                :accept="'.pdf,.doc,.docx,.ppt,.pptx,.mp4,.mp3,.zip,.epub'"
                :max-size="100"
                label="Upload File"
                :preview="false"
                @error="handleUploadError"
              />

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Or External URL
                </label>
                <input
                  v-model="form.file_url"
                  type="url"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="https://example.com/file.pdf"
                />
                <p class="mt-1 text-xs text-gray-500">Use this if the file is hosted externally</p>
              </div>
            </div>
          </div>

          <!-- Settings -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Settings</h2>
            
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <label class="text-sm font-medium text-gray-700">Allow Downloads</label>
                  <p class="text-xs text-gray-500">Members can download this file</p>
                </div>
                <input
                  v-model="form.is_downloadable"
                  type="checkbox"
                  class="h-5 w-5 text-blue-600 rounded focus:ring-blue-500"
                />
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <label class="text-sm font-medium text-gray-700">Active</label>
                  <p class="text-xs text-gray-500">Make this content visible to members</p>
                </div>
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  class="h-5 w-5 text-blue-600 rounded focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3">
            <Link
              :href="route('admin.content-management.index')"
              class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : (isEditing ? 'Update Content' : 'Create Content') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FileUpload from '@/components/CMS/FileUpload.vue';

interface Content {
  id: number;
  title: string;
  description: string | null;
  category: string;
  tier_restriction: string;
  unlock_day: number;
  estimated_value: number;
  is_downloadable: boolean;
  is_active: boolean;
  file_url: string | null;
}

interface Tier {
  key: string;
  name: string;
  price: number;
}

const props = defineProps<{
  content?: Content;
  categories?: string[];
  tiers?: Tier[];
}>();

const uploadError = ref('');
const STORAGE_KEY = 'starter_kit_content_draft';

const isEditing = computed(() => !!props.content);

// Load saved draft from localStorage
const loadDraft = () => {
  if (isEditing.value) return null; // Don't load draft when editing
  
  try {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) {
      return JSON.parse(saved);
    }
  } catch (e) {
    console.error('Failed to load draft:', e);
  }
  return null;
};

const draft = loadDraft();

const form = useForm({
  title: props.content?.title || draft?.title || '',
  description: props.content?.description || draft?.description || '',
  category: props.content?.category || draft?.category || '',
  tier_restriction: props.content?.tier_restriction || draft?.tier_restriction || 'all',
  unlock_day: props.content?.unlock_day || draft?.unlock_day || 0,
  estimated_value: props.content?.estimated_value || draft?.estimated_value || 0,
  is_downloadable: props.content?.is_downloadable ?? draft?.is_downloadable ?? true,
  is_active: props.content?.is_active ?? draft?.is_active ?? true,
  file: null as File | null,
  file_url: props.content?.file_url || draft?.file_url || '',
});

// Auto-save form data to localStorage
watch(
  () => ({
    title: form.title,
    description: form.description,
    category: form.category,
    tier_restriction: form.tier_restriction,
    unlock_day: form.unlock_day,
    estimated_value: form.estimated_value,
    is_downloadable: form.is_downloadable,
    is_active: form.is_active,
    file_url: form.file_url,
  }),
  (formData) => {
    if (!isEditing.value) {
      // Only save draft for new content, not when editing
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(formData));
      } catch (e) {
        console.error('Failed to save draft:', e);
      }
    }
  },
  { deep: true }
);

// Clear draft when component unmounts or form is submitted
const clearDraft = () => {
  try {
    localStorage.removeItem(STORAGE_KEY);
  } catch (e) {
    console.error('Failed to clear draft:', e);
  }
};

const handleUploadError = (message: string) => {
  uploadError.value = message;
};

const submit = () => {
  if (isEditing.value && props.content) {
    form.post(route('admin.starter-kit-content.update', props.content.id), {
      forceFormData: true,
      _method: 'put',
      onSuccess: () => {
        clearDraft();
      },
    });
  } else {
    form.post(route('admin.starter-kit-content.store'), {
      forceFormData: true,
      onSuccess: () => {
        clearDraft();
      },
    });
  }
};

// Show notification if draft was loaded
onMounted(() => {
  if (draft && !isEditing.value) {
    // You could show a toast notification here
    console.log('Draft loaded from previous session');
  }
});
</script>
