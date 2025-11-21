<template>
  <div class="space-y-6">
    <StepHeader
      title="Products & Services"
      description="Describe what you're offering and how you'll price it"
    />

    <FormField label="Main Product/Service Description" required hint="What are you selling?">
      <div class="relative">
        <textarea
          v-model="localData.description"
          rows="4"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          placeholder="E.g., We offer fresh organic vegetables grown using sustainable farming methods..."
        />
        <AIButton @click="generateAI('description')" :loading="aiLoading" />
      </div>
    </FormField>

    <FormField label="Key Features" hint="What makes your product/service special?">
      <textarea
        v-model="localData.features"
        rows="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        placeholder="• High quality\n• Fast delivery\n• Affordable pricing"
      />
    </FormField>

    <FormField label="Pricing Strategy" required>
      <div class="relative">
        <textarea
          v-model="localData.pricing"
          rows="3"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          placeholder="Describe your pricing approach..."
        />
        <AIButton @click="generateAI('pricing')" :loading="aiLoading" />
      </div>
    </FormField>

    <FormField label="Unique Selling Points" hint="Why should customers choose you?">
      <textarea
        v-model="localData.usp"
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

const props = defineProps<{
  modelValue: any;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: any];
}>();

const localData = ref(props.modelValue || {});
const aiLoading = ref(false);

watch(localData, (newVal) => {
  emit('update:modelValue', newVal);
}, { deep: true });

const generateAI = async (field: string) => {
  aiLoading.value = true;
  // AI generation logic here
  setTimeout(() => aiLoading.value = false, 1000);
};
</script>
