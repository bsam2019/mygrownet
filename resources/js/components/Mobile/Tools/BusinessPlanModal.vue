<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto bg-white">
    <!-- Header -->
    <div class="sticky top-0 bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
      <h2 class="text-lg font-semibold text-gray-900">Business Plan Generator</h2>
      <button @click="$emit('close')" class="p-2 text-gray-500 hover:text-gray-700">
        <XMarkIcon class="h-6 w-6" />
      </button>
    </div>

    <div class="p-4">
      <!-- Feature Overview -->
      <div class="mb-6 text-center">
        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
          <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Professional Business Plan Generator</h3>
        <p class="text-sm text-gray-600 leading-relaxed">
          Create a comprehensive 10-step business plan with AI assistance, financial calculators, and professional export options.
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
          <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mb-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h4 class="text-sm font-semibold text-gray-900">AI-Powered</h4>
          <p class="text-xs text-gray-600 mt-1">Smart content generation</p>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100">
          <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mb-2">
            <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
          <h4 class="text-sm font-semibold text-gray-900">Financial Tools</h4>
          <p class="text-xs text-gray-600 mt-1">Auto-calculating projections</p>
        </div>
        
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 border border-purple-100">
          <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mb-2">
            <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h4 class="text-sm font-semibold text-gray-900">Export Options</h4>
          <p class="text-xs text-gray-600 mt-1">PDF, Word, Templates</p>
        </div>
        
        <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl p-4 border border-orange-100">
          <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mb-2">
            <svg class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </div>
          <h4 class="text-sm font-semibold text-gray-900">Save & Resume</h4>
          <p class="text-xs text-gray-600 mt-1">Continue anytime</p>
        </div>
      </div>

      <!-- Existing Plan Notice -->
      <div v-if="existingPlan" class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl">
        <div class="flex items-start">
          <InformationCircleIcon class="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" />
          <div class="flex-1">
            <p class="text-sm font-semibold text-blue-900">Continue Your Plan</p>
            <p class="text-xs text-blue-700 mt-1">
              Last updated: {{ formatDate(existingPlan.updated_at) }} • 
              Step {{ existingPlan.current_step || 1 }} of 10
            </p>
            <div class="mt-3 flex gap-2">
              <button 
                @click="continueExistingPlan" 
                class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors"
              >
                Continue Plan
              </button>
              <button 
                @click="startNewPlan" 
                class="px-3 py-1.5 bg-white text-blue-600 text-xs font-medium rounded-lg border border-blue-200 hover:bg-blue-50 transition-colors"
              >
                Start New
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="space-y-3">
        <button
          @click="openFullGenerator"
          class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 active:scale-95 shadow-lg"
        >
          <div class="flex items-center justify-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>{{ existingPlan ? 'Continue Plan' : 'Start New Plan' }}</span>
          </div>
        </button>

        <button
          v-if="existingPlan"
          @click="viewExistingPlan"
          class="w-full bg-white border-2 border-gray-200 hover:border-gray-300 text-gray-700 font-medium py-3 px-6 rounded-xl transition-all duration-200 active:scale-95"
        >
          <div class="flex items-center justify-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span>View Current Plan</span>
          </div>
        </button>
      </div>

      <!-- Quick Info -->
      <div class="mt-6 bg-gray-50 rounded-xl p-4">
        <h4 class="text-sm font-semibold text-gray-900 mb-3">What's Included:</h4>
        <div class="space-y-2">
          <div class="flex items-center gap-3">
            <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-3 h-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span class="text-sm text-gray-700">10-step comprehensive business plan</span>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-3 h-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span class="text-sm text-gray-700">AI-powered content generation</span>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-3 h-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span class="text-sm text-gray-700">Financial projections & calculators</span>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-3 h-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span class="text-sm text-gray-700">Professional export formats</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { XMarkIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

interface Props {
  show: boolean;
  existingPlan?: any;
}

const props = defineProps<Props>();
const emit = defineEmits(['close']);

const openFullGenerator = () => {
  emit('close');
  router.visit(route('mygrownet.tools.business-plan-generator'));
};

const continueExistingPlan = () => {
  emit('close');
  router.visit(route('mygrownet.tools.business-plan-generator'));
};

const startNewPlan = () => {
  emit('close');
  router.visit(route('mygrownet.tools.business-plan-generator'));
};

const viewExistingPlan = () => {
  if (props.existingPlan?.id) {
    emit('close');
    router.visit(route('mygrownet.tools.business-plan.view', { planId: props.existingPlan.id }));
  }
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};
</script>
