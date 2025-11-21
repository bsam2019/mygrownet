<template>
  <div class="p-8">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Problem & Solution</h2>
      <p class="mt-2 text-gray-600">Define the problem you're solving and your unique solution</p>
    </div>

    <div class="space-y-6">
      <!-- Problem Statement -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Problem Statement <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <textarea
            v-model="localForm.problemStatement"
            rows="4"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="What problem does your business solve? Example: Many Lusaka families struggle to find fresh, affordable organic produce..."
          ></textarea>
          <button
            @click="generateWithAI('problem')"
            type="button"
            class="absolute top-2 right-2 px-3 py-1 text-xs bg-purple-600 text-white rounded-md hover:bg-purple-700 flex items-center space-x-1"
          >
            <SparklesIcon class="h-3 w-3" />
            <span>AI Generate</span>
          </button>
        </div>
        <p class="mt-1 text-xs text-gray-500">Tip: Be specific about the pain point you're addressing</p>
      </div>

      <!-- Solution Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Your Solution <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <textarea
            v-model="localForm.solution"
            rows="4"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="How does your business solve this problem? Example: We deliver farm-fresh organic vegetables directly to homes within 24 hours of harvest..."
          ></textarea>
          <button
            @click="generateWithAI('solution')"
            type="button"
            class="absolute top-2 right-2 px-3 py-1 text-xs bg-purple-600 text-white rounded-md hover:bg-purple-700 flex items-center space-x-1"
          >
            <SparklesIcon class="h-3 w-3" />
            <span>AI Generate</span>
          </button>
        </div>
      </div>

      <!-- Competitive Advantage -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Competitive Advantage <span class="text-red-500">*</span>
        </label>
        <textarea
          v-model="localForm.competitiveAdvantage"
          rows="3"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          placeholder="What makes you better than competitors? Example: Direct farm partnerships, 24-hour freshness guarantee, mobile app ordering..."
        ></textarea>
      </div>

      <!-- Customer Pain Points -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Customer Pain Points (Optional)
        </label>
        <div class="space-y-2">
          <div v-for="(point, index) in localForm.customerPainPoints" :key="index" class="flex items-center space-x-2">
            <input
              v-model="localForm.customerPainPoints[index]"
              type="text"
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
              placeholder="e.g., High prices at supermarkets"
            />
            <button
              @click="removePainPoint(index)"
              type="button"
              class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg"
            >
              Remove
            </button>
          </div>
          <button
            @click="addPainPoint"
            type="button"
            class="px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg border border-blue-200"
          >
            + Add Pain Point
          </button>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <div class="mt-8 flex justify-between pt-6 border-t border-gray-200">
      <button
        @click="$emit('previous')"
        type="button"
        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
      >
        Previous
      </button>
      <div class="flex space-x-3">
        <button
          @click="$emit('save')"
          type="button"
          class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
        >
          Save Draft
        </button>
        <button
          @click="handleNext"
          type="button"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Next Step
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { SparklesIcon } from '@heroicons/vue/24/solid';

interface Props {
  modelValue: any;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:modelValue', 'next', 'previous', 'save']);

const localForm = ref({ ...props.modelValue });

watch(localForm, (newVal) => {
  emit('update:modelValue', newVal);
}, { deep: true });

const generateWithAI = (field: string) => {
  console.log('Generating AI content for:', field);
};

const addPainPoint = () => {
  if (!localForm.value.customerPainPoints) {
    localForm.value.customerPainPoints = [];
  }
  localForm.value.customerPainPoints.push('');
};

const removePainPoint = (index: number) => {
  localForm.value.customerPainPoints.splice(index, 1);
};

const handleNext = () => {
  if (!localForm.value.problemStatement || !localForm.value.solution || !localForm.value.competitiveAdvantage) {
    alert('Please fill in all required fields');
    return;
  }
  emit('next');
};
</script>
