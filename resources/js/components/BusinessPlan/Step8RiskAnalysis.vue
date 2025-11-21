<template>
  <div class="space-y-6">
    <StepHeader
      title="Risk Analysis"
      description="Identify potential risks and how you'll handle them"
    />

    <FormField label="Financial Risks" hint="What could go wrong financially?">
      <div class="relative">
        <textarea
          v-model="localData.financialRisks"
          rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          placeholder="E.g., Cash flow problems, unexpected costs..."
        />
        <AIButton @click="generateAI('financialRisks')" :loading="aiLoading" />
      </div>
    </FormField>

    <FormField label="Financial Risk Mitigation">
      <textarea
        v-model="localData.financialMitigation"
        rows="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        placeholder="How will you prevent or handle these risks?"
      />
    </FormField>

    <FormField label="Operational Risks">
      <div class="relative">
        <textarea
          v-model="localData.operationalRisks"
          rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          placeholder="E.g., Equipment failure, staff turnover..."
        />
        <AIButton @click="generateAI('operationalRisks')" :loading="aiLoading" />
      </div>
    </FormField>

    <FormField label="Operational Risk Mitigation">
      <textarea
        v-model="localData.operationalMitigation"
        rows="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
      />
    </FormField>

    <FormField label="Market Risks">
      <div class="relative">
        <textarea
          v-model="localData.marketRisks"
          rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          placeholder="E.g., Competition, changing customer preferences..."
        />
        <AIButton @click="generateAI('marketRisks')" :loading="aiLoading" />
      </div>
    </FormField>

    <FormField label="Market Risk Mitigation">
      <textarea
        v-model="localData.marketMitigation"
        rows="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
      />
    </FormField>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import StepHeader from './StepHeader.vue';
import FormField from './FormField.vue';
import AIButton from './AIButton.vue';

const props = defineProps<{ modelValue: any }>();
const emit = defineEmits<{ 'update:modelValue': [value: any] }>();

const localData = ref(props.modelValue || {});
const aiLoading = ref(false);

watch(localData, (newVal) => emit('update:modelValue', newVal), { deep: true });

const generateAI = async (field: string) => {
  aiLoading.value = true;
  setTimeout(() => aiLoading.value = false, 1000);
};
</script>
