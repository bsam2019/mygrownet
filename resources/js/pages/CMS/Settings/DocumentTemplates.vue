<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { toast } from '@/utils/bizboost-toast';
import { CheckCircleIcon, EyeIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

defineOptions({ layout: CMSLayout });

interface Template {
  id: number;
  name: string;
  document_type: string;
  industry_category: string;
  layout_file: string;
  thumbnail_path: string | null;
  is_default: boolean;
}

interface Props {
  templates: Template[];
  documentType: string;
  preferences: Record<string, number>;
  company: { id: number; name: string; has_bizdocs_module: boolean };
}

const props = defineProps<Props>();

const activeDocType = ref<'invoice' | 'quotation' | 'receipt'>(
  (props.documentType as any) || 'invoice'
);
const previewTemplateId = ref<number | null>(null);
const saving = ref<number | null>(null);
const selectedCategory = ref('all');

// Local copy of preferences so we can update reactively after saving
const localPreferences = ref<Record<string, number>>({ ...props.preferences });

const docTypes = [
  { value: 'invoice',   label: 'Invoices' },
  { value: 'quotation', label: 'Quotations' },
  { value: 'receipt',   label: 'Receipts' },
];

const categoryLabels: Record<string, string> = {
  general_business:      'General Business',
  retail:                'Retail',
  education:             'Education',
  healthcare:            'Healthcare',
  construction:          'Construction',
  professional_services: 'Professional Services',
  creative:              'Creative',
};

const categories = computed(() => {
  const cats = new Set(props.templates.map(t => t.industry_category).filter(Boolean));
  return ['all', ...Array.from(cats)];
});

const filteredTemplates = computed(() =>
  selectedCategory.value === 'all'
    ? props.templates
    : props.templates.filter(t => t.industry_category === selectedCategory.value)
);

const currentTemplateId = computed(() => localPreferences.value[activeDocType.value] ?? null);

const isSelected = (templateId: number) => currentTemplateId.value === templateId;

const previewUrls = ref<Record<number, string>>({})
const loadingPreviewIds = ref<Set<number>>(new Set())

const getPreviewUrl = async (templateId: number): Promise<string> => {
  if (previewUrls.value[templateId]) return previewUrls.value[templateId]
  if (loadingPreviewIds.value.has(templateId)) return ''

  loadingPreviewIds.value.add(templateId)
  try {
    const res = await fetch(route('cms.settings.document-templates.preview-url', { id: templateId }), {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    if (res.ok) {
      const data = await res.json()
      previewUrls.value[templateId] = data.url
      return data.url
    }
  } catch (e) {
    console.error('Failed to get preview URL', e)
  } finally {
    loadingPreviewIds.value.delete(templateId)
  }
  return ''
}

// Pre-fetch preview URLs for visible templates
const initPreviewUrls = async () => {
  for (const t of filteredTemplates.value) {
    getPreviewUrl(t.id)
  }
}

// Watch for filter changes to pre-fetch new templates
watch(filteredTemplates, initPreviewUrls, { immediate: true });

const selectTemplate = (template: Template) => {
  saving.value = template.id;
  router.post(route('cms.settings.document-templates.set'), {
    document_type: activeDocType.value,
    template_id: template.id,
  }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      // Update local preferences immediately so the UI reflects the change
      localPreferences.value[activeDocType.value] = template.id;
      toast.success('Template saved', `${template.name} is now your ${activeDocType.value} template`);
      saving.value = null;
    },
    onError: () => {
      toast.error('Failed to save', 'Could not update template preference');
      saving.value = null;
    },
  });
};
</script>

<template>
  <Head title="Document Templates - CMS" />

  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Document Templates</h1>
      <p class="mt-1 text-sm text-gray-600">
        Choose a template for each document type. Your selection applies to all PDF downloads.
      </p>
    </div>

    <!-- Document type tabs -->
    <div class="flex gap-1 p-1 bg-gray-100 rounded-lg w-fit mb-6">
      <button
        v-for="dt in docTypes"
        :key="dt.value"
        @click="activeDocType = dt.value as any; selectedCategory = 'all'"
        :class="[
          'px-4 py-2 text-sm font-medium rounded-md transition',
          activeDocType === dt.value
            ? 'bg-white text-gray-900 shadow-sm'
            : 'text-gray-600 hover:text-gray-900'
        ]"
      >
        {{ dt.label }}
      </button>
    </div>

    <!-- Current selection banner -->
    <div v-if="currentTemplateId" class="mb-5 flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
      <CheckCircleIcon class="h-4 w-4 flex-shrink-0" aria-hidden="true" />
      <span>
        <strong>{{ templates.find(t => t.id === currentTemplateId)?.name ?? 'Custom' }}</strong>
        is selected for <strong>{{ docTypes.find(d => d.value === activeDocType)?.label }}</strong>.
      </span>
    </div>
    <div v-else class="mb-5 flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
      <DocumentTextIcon class="h-4 w-4 flex-shrink-0" aria-hidden="true" />
      No template selected for <strong class="mx-1">{{ docTypes.find(d => d.value === activeDocType)?.label }}</strong> — using the default layout.
    </div>

    <!-- Category filter -->
    <div class="flex flex-wrap gap-2 mb-6">
      <button
        v-for="cat in categories"
        :key="cat"
        @click="selectedCategory = cat"
        :class="[
          'px-3 py-1.5 text-xs font-medium rounded-full border transition',
          selectedCategory === cat
            ? 'bg-blue-600 text-white border-blue-600'
            : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400'
        ]"
      >
        {{ cat === 'all' ? 'All Templates' : (categoryLabels[cat] ?? cat) }}
      </button>
    </div>

    <!-- Template grid -->
    <div v-if="filteredTemplates.length === 0" class="text-center py-16 text-gray-500 text-sm">
      No templates found for this category.
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="template in filteredTemplates"
        :key="template.id"
        :class="[
          'relative bg-white rounded-xl border-2 shadow-sm overflow-hidden transition-all',
          isSelected(template.id)
            ? 'border-blue-500 ring-2 ring-blue-200'
            : 'border-gray-200 hover:border-blue-300'
        ]"
      >
        <!-- Selected badge -->
        <div v-if="isSelected(template.id)" class="absolute top-2 right-2 z-10">
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-600 text-white">
            <CheckCircleIcon class="h-3 w-3" aria-hidden="true" />
            Active
          </span>
        </div>

        <!-- Template preview (iframe) -->
        <div class="relative bg-gray-50 border-b border-gray-100" style="height: 200px; overflow: hidden;">
          <iframe
            :src="previewUrls[template.id] || ''"
            class="absolute top-0 left-0 w-full border-0 pointer-events-none origin-top-left"
            style="width: 794px; height: 1123px; transform: scale(0.252); transform-origin: top left;"
            loading="lazy"
            :title="`Preview of ${template.name}`"
          />
          <!-- Preview overlay button -->
          <button
            @click="previewTemplateId = template.id"
            class="absolute inset-0 flex items-center justify-center bg-black/0 hover:bg-black/20 transition group"
            :aria-label="`Preview ${template.name} template`"
          >
            <span class="opacity-0 group-hover:opacity-100 flex items-center gap-1.5 px-3 py-1.5 bg-white rounded-lg text-xs font-medium text-gray-900 shadow transition">
              <EyeIcon class="h-3.5 w-3.5" aria-hidden="true" />
              Preview
            </span>
          </button>
        </div>

        <!-- Template info + action -->
        <div class="p-4">
          <div class="flex items-start justify-between gap-2 mb-3">
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ template.name }}</p>
              <p class="text-xs text-gray-500 mt-0.5">{{ categoryLabels[template.industry_category] ?? template.industry_category }}</p>
            </div>
          </div>
          <button
            @click="selectTemplate(template)"
            :disabled="isSelected(template.id) || saving === template.id"
            :class="[
              'w-full py-2 text-sm font-medium rounded-lg transition',
              isSelected(template.id)
                ? 'bg-blue-50 text-blue-700 border border-blue-200 cursor-default'
                : 'bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50'
            ]"
          >
            <span v-if="saving === template.id">Saving…</span>
            <span v-else-if="isSelected(template.id)">Currently Selected</span>
            <span v-else>Use This Template</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Full-screen preview modal -->
  <Teleport to="body">
    <div
      v-if="previewTemplateId"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
      @click.self="previewTemplateId = null"
    >
      <div class="bg-white rounded-xl shadow-2xl overflow-hidden flex flex-col" style="width: 860px; max-height: 90vh;">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200">
          <p class="text-sm font-semibold text-gray-900">
            {{ templates.find(t => t.id === previewTemplateId)?.name }}
          </p>
          <div class="flex items-center gap-2">
            <button
              @click="selectTemplate(templates.find(t => t.id === previewTemplateId)!); previewTemplateId = null"
              class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
            >
              Use This Template
            </button>
            <button
              @click="previewTemplateId = null"
              class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition"
              aria-label="Close preview"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        <div class="flex-1 overflow-auto bg-gray-100 p-4">
          <iframe
            :src="previewUrls[previewTemplateId] || ''"
            class="w-full bg-white rounded shadow"
            style="height: 1000px; border: none;"
            :title="`Full preview of template`"
          />
        </div>
      </div>
    </div>
  </Teleport>
</template>
