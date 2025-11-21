<template>
  <div class="p-8">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Business Information</h2>
      <p class="mt-2 text-gray-600">Let's start with the basics about your business</p>
    </div>

    <div class="space-y-6">
      <!-- Business Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Business Name <span class="text-red-500">*</span>
        </label>
        <input
          v-model="localForm.businessName"
          type="text"
          required
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="e.g., Lusaka Fresh Farms"
        />
      </div>

      <!-- Industry Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Industry <span class="text-red-500">*</span>
        </label>
        <select
          v-model="localForm.industry"
          @change="loadIndustryTemplate"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        >
          <option value="">Select an industry...</option>
          <option v-for="ind in industries" :key="ind.value" :value="ind.value">
            {{ ind.label }}
          </option>
        </select>
        <p v-if="localForm.industry" class="mt-2 text-sm text-blue-600">
          ✓ Industry template loaded with helpful suggestions
        </p>
      </div>

      <!-- Location -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
          <input
            v-model="localForm.location.country"
            type="text"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50"
            readonly
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
          <select
            v-model="localForm.location.province"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg"
          >
            <option value="">Select province...</option>
            <option v-for="prov in zambianProvinces" :key="prov" :value="prov">{{ prov }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">City/Town</label>
          <input
            v-model="localForm.location.city"
            type="text"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg"
            placeholder="e.g., Lusaka"
          />
        </div>
      </div>

      <!-- Legal Structure -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Legal Structure <span class="text-red-500">*</span>
        </label>
        <select
          v-model="localForm.legalStructure"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg"
        >
          <option value="">Select structure...</option>
          <option value="sole_trader">Sole Trader</option>
          <option value="partnership">Partnership</option>
          <option value="limited_company">Limited Company</option>
          <option value="cooperative">Cooperative</option>
        </select>
      </div>

      <!-- Mission Statement -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Mission Statement <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <textarea
            v-model="localForm.mission"
            rows="3"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="What is your business's purpose? Example: To provide fresh, organic produce to Lusaka families while supporting local farmers..."
          ></textarea>
          <button
            @click="generateWithAI('mission')"
            type="button"
            class="absolute top-2 right-2 px-3 py-1 text-xs bg-purple-600 text-white rounded-md hover:bg-purple-700 flex items-center space-x-1"
          >
            <SparklesIcon class="h-3 w-3" />
            <span>Generate with AI</span>
          </button>
        </div>
        <p class="mt-1 text-xs text-gray-500">Tip: Focus on what you do and who you serve</p>
      </div>

      <!-- Vision Statement -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Vision Statement <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <textarea
            v-model="localForm.vision"
            rows="3"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            placeholder="Where do you see your business in 5 years? Example: To become Zambia's leading organic food supplier, serving 10,000+ families..."
          ></textarea>
          <button
            @click="generateWithAI('vision')"
            type="button"
            class="absolute top-2 right-2 px-3 py-1 text-xs bg-purple-600 text-white rounded-md hover:bg-purple-700 flex items-center space-x-1"
          >
            <SparklesIcon class="h-3 w-3" />
            <span>Generate with AI</span>
          </button>
        </div>
        <p class="mt-1 text-xs text-gray-500">Tip: Think big - where will you be in 5-10 years?</p>
      </div>

      <!-- Background/Overview -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Business Background/Overview
        </label>
        <textarea
          v-model="localForm.background"
          rows="4"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          placeholder="Tell your story. How did this business idea come about? What experience do you have?"
        ></textarea>
      </div>

      <!-- Logo Upload -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Business Logo (Optional)
        </label>
        <div class="flex items-center space-x-4">
          <div v-if="logoPreview" class="w-20 h-20 border-2 border-gray-300 rounded-lg overflow-hidden">
            <img :src="logoPreview" alt="Logo preview" class="w-full h-full object-cover" />
          </div>
          <input
            type="file"
            @change="handleLogoUpload"
            accept="image/*"
            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
          />
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <div class="mt-8 flex justify-between pt-6 border-t border-gray-200">
      <button
        @click="$emit('previous')"
        type="button"
        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
        disabled
      >
        Previous
      </button>
      <div class="flex space-x-3">
        <button
          @click="saveDraft"
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

const industries = [
  { value: 'agriculture', label: 'Agriculture & Farming' },
  { value: 'retail', label: 'Retail & E-commerce' },
  { value: 'transport', label: 'Transport & Logistics' },
  { value: 'ict', label: 'ICT & Technology' },
  { value: 'manufacturing', label: 'Manufacturing' },
  { value: 'education', label: 'Education & Training' },
  { value: 'hospitality', label: 'Hospitality & Tourism' },
  { value: 'real_estate', label: 'Real Estate' },
  { value: 'construction', label: 'Construction' },
  { value: 'services', label: 'Freelancing & Services' },
];

const zambianProvinces = [
  'Central', 'Copperbelt', 'Eastern', 'Luapula', 'Lusaka',
  'Muchinga', 'Northern', 'North-Western', 'Southern', 'Western'
];

const logoPreview = ref<string | null>(null);

const loadIndustryTemplate = () => {
  // Load industry-specific suggestions
  console.log('Loading template for:', localForm.value.industry);
};

const generateWithAI = (field: string) => {
  // AI generation logic
  console.log('Generating AI content for:', field);
};

const handleLogoUpload = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      logoPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
    localForm.value.logo = file;
  }
};

const saveDraft = () => {
  emit('save');
};

const handleNext = () => {
  if (!localForm.value.businessName || !localForm.value.industry || 
      !localForm.value.legalStructure || !localForm.value.mission || !localForm.value.vision) {
    alert('Please fill in all required fields');
    return;
  }
  emit('next');
};
</script>
