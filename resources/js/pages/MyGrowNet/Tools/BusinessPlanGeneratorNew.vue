<template>
  <MemberLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Business Plan Generator</h1>
            <p class="mt-2 text-gray-600">Create a professional business plan with AI assistance</p>
          </div>
          <div class="flex space-x-3">
            <button
              v-if="savedPlans.length > 0"
              @click="showSavedPlans = true"
              class="px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              My Plans ({{ savedPlans.length }})
            </button>
            <button
              @click="showTemplates = true"
              class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Browse Templates
            </button>
          </div>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="mb-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Progress</h2>
          <span class="text-sm text-gray-600">Step {{ currentStep + 1 }} of {{ steps.length }}</span>
        </div>
        <div class="relative">
          <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
            <div
              :style="{ width: `${((currentStep + 1) / steps.length) * 100}%` }"
              class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-600 transition-all duration-300"
            ></div>
          </div>
        </div>
        <div class="mt-4 grid grid-cols-5 gap-2">
          <div
            v-for="(step, index) in steps"
            :key="index"
            class="text-center"
          >
            <div
              class="w-10 h-10 mx-auto rounded-full flex items-center justify-center text-sm font-semibold mb-2"
              :class="[
                currentStep >= index
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-200 text-gray-600'
              ]"
            >
              {{ index + 1 }}
            </div>
            <p class="text-xs" :class="currentStep >= index ? 'text-blue-600 font-medium' : 'text-gray-500'">
              {{ step.title }}
            </p>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Step Content -->
        <component
          :is="currentStepComponent"
          v-model="form"
          @next="nextStep"
          @previous="previousStep"
          @save="saveDraft"
        />
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { ref, computed, shallowRef } from 'vue';
import { router } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

// Step Components (to be created)
import Step1BusinessInfo from '@/Components/BusinessPlan/Step1BusinessInfo.vue';
import Step2ProblemSolution from '@/Components/BusinessPlan/Step2ProblemSolution.vue';
import Step3ProductsServices from '@/Components/BusinessPlan/Step3ProductsServices.vue';
import Step4MarketResearch from '@/Components/BusinessPlan/Step4MarketResearch.vue';
import Step5MarketingStrategy from '@/Components/BusinessPlan/Step5MarketingStrategy.vue';
import Step6Operations from '@/Components/BusinessPlan/Step6Operations.vue';
import Step7Financials from '@/Components/BusinessPlan/Step7Financials.vue';
import Step8RiskAnalysis from '@/Components/BusinessPlan/Step8RiskAnalysis.vue';
import Step9Roadmap from '@/Components/BusinessPlan/Step9Roadmap.vue';
import Step10Export from '@/Components/BusinessPlan/Step10Export.vue';

interface Props {
  savedPlans?: any[];
  templates?: any[];
  userSubscription?: any;
}

const props = withDefaults(defineProps<Props>(), {
  savedPlans: () => [],
  templates: () => [],
});

const steps = [
  { title: 'Business Info', component: Step1BusinessInfo },
  { title: 'Problem & Solution', component: Step2ProblemSolution },
  { title: 'Products/Services', component: Step3ProductsServices },
  { title: 'Market Research', component: Step4MarketResearch },
  { title: 'Marketing Strategy', component: Step5MarketingStrategy },
  { title: 'Operations', component: Step6Operations },
  { title: 'Financials', component: Step7Financials },
  { title: 'Risk Analysis', component: Step8RiskAnalysis },
  { title: 'Roadmap', component: Step9Roadmap },
  { title: 'Export', component: Step10Export },
];

const currentStep = ref(0);
const showSavedPlans = ref(false);
const showTemplates = ref(false);

const currentStepComponent = computed(() => steps[currentStep.value].component);

const form = ref({
  // Step 1: Business Information
  businessName: '',
  industry: '',
  location: { country: 'Zambia', province: '', city: '' },
  legalStructure: '',
  mission: '',
  vision: '',
  background: '',
  logo: null,

  // Step 2: Problem & Solution
  problemStatement: '',
  solution: '',
  competitiveAdvantage: '',
  customerPainPoints: [],

  // Step 3: Products/Services
  products: [],
  pricingStrategy: '',
  uniqueSellingPoints: [],
  productionProcess: '',
  resourceRequirements: [],

  // Step 4: Market Research
  targetMarket: '',
  customerDemographics: {},
  marketSize: '',
  competitors: [],
  competitiveAnalysis: '',

  // Step 5: Marketing & Sales
  marketingChannels: [],
  brandingApproach: '',
  salesChannels: [],
  customerRetention: '',

  // Step 6: Operations
  dailyOperations: '',
  staffRoles: [],
  equipment: [],
  suppliers: [],
  operationalWorkflow: '',

  // Step 7: Financials
  startupCosts: {},
  monthlyOperatingCosts: {},
  revenueProjections: {},
  financialCalculations: {},

  // Step 8: Risk Analysis
  risks: [],
  mitigationStrategies: {},

  // Step 9: Implementation Roadmap
  timeline: [],
  milestones: [],
  responsibilities: {},

  // Metadata
  templateId: null,
  status: 'draft',
  lastSaved: null,
});

const nextStep = () => {
  if (currentStep.value < steps.length - 1) {
    currentStep.value++;
    saveDraft();
  }
};

const previousStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--;
  }
};

const saveDraft = () => {
  router.post(route('mygrownet.tools.business-plan.save'), {
    ...form.value,
    currentStep: currentStep.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      form.value.lastSaved = new Date().toISOString();
    },
  });
};
</script>
