<template>
  <div class="space-y-6">
    <StepHeader
      title="Operations Plan"
      description="How will your business run day-to-day?"
    />

    <FormField label="Daily Operations" required hint="Describe a typical day">
      <div class="relative">
        <textarea
          v-model="localData.dailyOps"
          rows="4"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        />
        <AIButton @click="generateAI('dailyOps')" :loading="aiLoading" />
      </div>
    </FormField>

    <FormField label="Staff & Roles" hint="Who will you need to hire?">
      <textarea
        v-model="localData.staff"
        rows="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        placeholder="E.g., 1 Manager, 2 Sales Staff, 1 Accountant"
      />
    </FormField>

    <FormField label="Equipment & Tools Needed">
      <textarea
        v-model="localData.equipment"
        rows="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
      />
    </FormField>

    <FormField label="Key Suppliers">
      <textarea
        v-model="localData.suppliers"
        rows="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        placeholder="List your main suppliers and what they provide"
      />
    </FormField>

    <FormField label="Operational Workflow">
      <textarea
        v-model="localData.workflow"
        rows="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        placeholder="Describe your process from order to delivery"
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
