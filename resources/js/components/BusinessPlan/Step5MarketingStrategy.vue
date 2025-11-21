<template>
  <div class="space-y-6">
    <StepHeader
      title="Marketing & Sales Strategy"
      description="How will you reach and sell to your customers?"
    />

    <FormField label="Marketing Channels" required hint="How will you promote your business?">
      <div class="space-y-2">
        <label v-for="channel in channels" :key="channel" class="flex items-center">
          <input
            type="checkbox"
            :value="channel"
            v-model="localData.channels"
            class="mr-2 rounded"
          />
          <span class="text-sm">{{ channel }}</span>
        </label>
      </div>
    </FormField>

    <FormField label="Marketing Strategy Details">
      <div class="relative">
        <textarea
          v-model="localData.strategy"
          rows="4"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        />
        <AIButton @click="generateAI('strategy')" :loading="aiLoading" />
      </div>
    </FormField>

    <FormField label="Sales Channels" required>
      <div class="space-y-2">
        <label v-for="channel in salesChannels" :key="channel" class="flex items-center">
          <input
            type="checkbox"
            :value="channel"
            v-model="localData.salesChannels"
            class="mr-2 rounded"
          />
          <span class="text-sm">{{ channel }}</span>
        </label>
      </div>
    </FormField>

    <FormField label="Customer Retention Strategy">
      <textarea
        v-model="localData.retention"
        rows="3"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        placeholder="How will you keep customers coming back?"
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

const localData = ref(props.modelValue || { channels: [], salesChannels: [] });
const aiLoading = ref(false);

const channels = ['Social Media', 'Flyers', 'Radio', 'TV', 'Referrals', 'Events', 'Online Ads'];
const salesChannels = ['Physical Store', 'Online Shop', 'Agents/Distributors', 'Direct Sales', 'Marketplace'];

watch(localData, (newVal) => emit('update:modelValue', newVal), { deep: true });

const generateAI = async (field: string) => {
  aiLoading.value = true;
  setTimeout(() => aiLoading.value = false, 1000);
};
</script>
