<template>
  <div class="space-y-6">
    <StepHeader
      title="Implementation Roadmap"
      description="Plan your timeline and milestones"
    />

    <div class="bg-blue-50 p-4 rounded-lg mb-4">
      <button
        @click="addMilestone"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
      >
        + Add Milestone
      </button>
    </div>

    <div v-for="(milestone, index) in localData.milestones" :key="index" class="border border-gray-200 rounded-lg p-4">
      <div class="flex justify-between items-start mb-3">
        <h3 class="font-semibold text-gray-900">Milestone {{ index + 1 }}</h3>
        <button
          @click="removeMilestone(index)"
          class="text-red-600 hover:text-red-800 text-sm"
        >
          Remove
        </button>
      </div>

      <div class="space-y-3">
        <FormField label="Month/Timeline" required>
          <input
            v-model="milestone.timeline"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="E.g., Month 1, Q1 2024"
          />
        </FormField>

        <FormField label="Milestone Description" required>
          <textarea
            v-model="milestone.description"
            rows="2"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="What will be achieved?"
          />
        </FormField>

        <FormField label="Responsibilities">
          <input
            v-model="milestone.responsibilities"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="Who is responsible?"
          />
        </FormField>
      </div>
    </div>

    <div v-if="!localData.milestones?.length" class="text-center py-8 text-gray-500">
      No milestones added yet. Click "Add Milestone" to get started.
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import StepHeader from './StepHeader.vue';
import FormField from './FormField.vue';

const props = defineProps<{ modelValue: any }>();
const emit = defineEmits<{ 'update:modelValue': [value: any] }>();

const localData = ref(props.modelValue || { milestones: [] });

watch(localData, (newVal) => emit('update:modelValue', newVal), { deep: true });

const addMilestone = () => {
  if (!localData.value.milestones) {
    localData.value.milestones = [];
  }
  localData.value.milestones.push({
    timeline: '',
    description: '',
    responsibilities: ''
  });
};

const removeMilestone = (index: number) => {
  localData.value.milestones.splice(index, 1);
};
</script>
