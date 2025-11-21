<template>
  <div class="space-y-6">
    <StepHeader
      title="Review & Export"
      description="Review your business plan and export it"
    />

    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
      <div class="flex items-start">
        <CheckCircleIcon class="w-6 h-6 text-green-600 mr-3 flex-shrink-0 mt-1" />
        <div>
          <h3 class="font-semibold text-green-900 mb-2">Business Plan Complete!</h3>
          <p class="text-green-800 text-sm">
            You've completed all sections. Review your plan below and export it in your preferred format.
          </p>
        </div>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="border border-gray-200 rounded-lg p-4">
        <h4 class="font-semibold text-gray-900 mb-2">Business Information</h4>
        <p class="text-sm text-gray-600">{{ planData.step1?.businessName || 'Not provided' }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ planData.step1?.industry || 'Industry not specified' }}</p>
      </div>

      <div class="border border-gray-200 rounded-lg p-4">
        <h4 class="font-semibold text-gray-900 mb-2">Financial Summary</h4>
        <p class="text-sm text-gray-600">
          Startup: K{{ formatNumber(planData.step7?.startupCosts) }}
        </p>
        <p class="text-sm text-gray-600">
          Monthly Revenue: K{{ formatNumber(planData.step7?.monthlyRevenue) }}
        </p>
      </div>
    </div>

    <!-- Export Options -->
    <div class="border-t border-gray-200 pt-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Options</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <ExportCard
          title="PDF Export"
          description="Professional PDF document"
          icon="document"
          badge="Premium"
          :loading="exportLoading.pdf"
          @click="exportPlan('pdf')"
        />

        <ExportCard
          title="Word Document"
          description="Editable .docx file"
          icon="document"
          badge="Premium"
          :loading="exportLoading.word"
          @click="exportPlan('word')"
        />

        <ExportCard
          title="MyGrowNet Template"
          description="Save to your library"
          icon="save"
          :loading="exportLoading.template"
          @click="exportPlan('template')"
        />
      </div>
    </div>

    <!-- Branding Options -->
    <div class="border-t border-gray-200 pt-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Branding Options</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <FormField label="Theme Color">
          <input
            v-model="brandingOptions.color"
            type="color"
            class="w-full h-10 rounded-lg border border-gray-300"
          />
        </FormField>

        <FormField label="Font Family">
          <select
            v-model="brandingOptions.font"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="Arial">Arial</option>
            <option value="Times New Roman">Times New Roman</option>
            <option value="Helvetica">Helvetica</option>
            <option value="Georgia">Georgia</option>
          </select>
        </FormField>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';
import StepHeader from './StepHeader.vue';
import FormField from './FormField.vue';
import ExportCard from './ExportCard.vue';

const props = defineProps<{
  planData: any;
}>();

const emit = defineEmits<{
  export: [format: string, options: any];
}>();

const exportLoading = ref({
  pdf: false,
  word: false,
  template: false
});

const brandingOptions = ref({
  color: '#2563eb',
  font: 'Arial'
});

const formatNumber = (value: any) => {
  return value ? Number(value).toLocaleString() : '0';
};

const exportPlan = async (format: string) => {
  exportLoading.value[format] = true;
  emit('export', format, brandingOptions.value);
  setTimeout(() => {
    exportLoading.value[format] = false;
  }, 2000);
};
</script>
