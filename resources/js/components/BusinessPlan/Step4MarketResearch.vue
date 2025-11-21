<template>
  <div class="space-y-6">
    <StepHeader
      title="Market Research"
      description="Understand your market, customers, and competition"
    />

    <FormField label="Target Market" required hint="Who are your ideal customers?">
      <div class="relative">
        <textarea
          v-model="localData.targetMarket"
          rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          placeholder="E.g., Urban professionals aged 25-45 who value organic food..."
        />
        <AIButton @click="generateAI('targetMarket')" :loading="aiLoading" />
      </div>
    </FormField>

    <FormField label="Customer Demographics">
      <textarea
        v-model="localData.demographics"
        rows="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        placeholder="Age, income, location, interests..."
      />
    </FormField>

    <FormField label="Market Size Estimate" hint="How big is your potential market?">
      <input
        v-model="localData.marketSize"
        type="text"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        placeholder="E.g., 50,000 potential customers in Lusaka"
      />
    </FormField>

    <FormField label="Main Competitors" hint="Who else is serving this market?">
      <div class="relative">
        <textarea
          v-model="localData.competitors"
          rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        />
        <AIButton @click="generateAI('competitors')" :loading="aiLoading" />
      </div>
    </FormField>

    <FormField label="Competitive Advantage" required>
      <textarea
        v-model="localData.competitiveAdvantage"
        rows="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        placeholder="What makes you better than competitors?"
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
