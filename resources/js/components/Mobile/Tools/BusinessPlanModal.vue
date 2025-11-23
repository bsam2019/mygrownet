<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-white flex flex-col overflow-hidden">
    <!-- Header -->
    <div class="sticky top-0 bg-white border-b border-gray-200 px-4 py-3 z-10 shadow-sm">
      <div class="flex items-center justify-between">
        <button @click="handleBack" class="p-2 text-gray-500 hover:text-gray-700 active:bg-gray-100 rounded-lg">
          <ChevronLeftIcon v-if="currentStep > 1" class="h-6 w-6" />
          <XMarkIcon v-else class="h-6 w-6" />
        </button>
        <div class="flex-1 text-center">
          <h2 class="text-lg font-semibold text-gray-900">
            {{ form.id ? 'Edit Plan' : 'New Plan' }}
          </h2>
          <p v-if="form.business_name" class="text-xs text-gray-500 truncate px-4">{{ form.business_name }}</p>
        </div>
        <button @click="saveDraft" :disabled="saving" class="text-sm text-blue-600 font-medium px-3 py-1.5 rounded-lg active:bg-blue-50">
          {{ saving ? 'Saving...' : 'Save' }}
        </button>
      </div>
      <div class="mt-2 text-center">
        <button @click="viewAllPlans" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
          📋 View All Plans
        </button>
      </div>
    </div>

    <!-- Progress Bar -->
    <div class="bg-white border-b border-gray-200 px-4 py-3">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-medium text-gray-700">Step {{ currentStep }} of {{ totalSteps }}</span>
        <span class="text-xs text-gray-500">{{ Math.round((currentStep / totalSteps) * 100) }}%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-1.5">
        <div 
          class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
          :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
        ></div>
      </div>
      <div class="mt-2 text-center">
        <span class="text-xs font-medium text-blue-600">{{ steps[currentStep - 1]?.full }}</span>
      </div>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto p-4" style="padding-bottom: 140px;">
      <!-- Step 1: Business Information -->
      <div v-if="currentStep === 1" class="space-y-4">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-gray-900">Business Information</h3>
          <p class="text-sm text-gray-600 mt-1">Tell us about your business basics</p>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Business Name <span class="text-red-500">*</span></label>
            <input v-model="form.business_name" type="text" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., MyGrowNet Success Hub" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Industry <span class="text-red-500">*</span></label>
            <select v-model="form.industry" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Select industry...</option>
              <option v-for="ind in industries" :key="ind" :value="ind">{{ ind }}</option>
            </select>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Country <span class="text-red-500">*</span></label>
              <input v-model="form.country" type="text" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Zambia" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
              <input v-model="form.province" type="text" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Lusaka" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
            <input v-model="form.city" type="text" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Lusaka" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Legal Structure <span class="text-red-500">*</span></label>
            <select v-model="form.legal_structure" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Select structure...</option>
              <option value="sole_trader">Sole Trader</option>
              <option value="partnership">Partnership</option>
              <option value="company">Company</option>
              <option value="cooperative">Cooperative</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mission Statement</label>
            <p class="text-xs text-gray-500 mb-1">What is your business purpose?</p>
            <div class="relative">
              <textarea v-model="form.mission_statement" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="To provide..."></textarea>
              <button @click="generateAI('mission_statement')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Vision Statement</label>
            <p class="text-xs text-gray-500 mb-1">Where do you see your business in 5 years?</p>
            <div class="relative">
              <textarea v-model="form.vision_statement" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="To become..."></textarea>
              <button @click="generateAI('vision_statement')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Background/Overview</label>
            <p class="text-xs text-gray-500 mb-1">Brief history or context of your business</p>
            <textarea v-model="form.background" rows="4" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Our business started..."></textarea>
          </div>
        </div>
      </div>

      <!-- Step 2: Problem & Solution -->
      <div v-if="currentStep === 2" class="space-y-4">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-gray-900">Problem & Solution</h3>
          <p class="text-sm text-gray-600 mt-1">Define the problem you're solving and your solution</p>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Problem Statement <span class="text-red-500">*</span></label>
            <p class="text-xs text-gray-500 mb-1">What problem does your business solve?</p>
            <div class="relative">
              <textarea v-model="form.problem_statement" rows="4" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Many people struggle with..."></textarea>
              <button @click="generateAI('problem_statement')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Solution Description <span class="text-red-500">*</span></label>
            <p class="text-xs text-gray-500 mb-1">How does your business solve this problem?</p>
            <div class="relative">
              <textarea v-model="form.solution_description" rows="4" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="We provide..."></textarea>
              <button @click="generateAI('solution_description')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Competitive Advantage <span class="text-red-500">*</span></label>
            <p class="text-xs text-gray-500 mb-1">What makes you different from competitors?</p>
            <div class="relative">
              <textarea v-model="form.competitive_advantage" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Unlike competitors, we..."></textarea>
              <button @click="generateAI('competitive_advantage')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Pain Points</label>
            <p class="text-xs text-gray-500 mb-1">Specific challenges your customers face</p>
            <textarea v-model="form.customer_pain_points" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="• High costs&#10;• Limited access&#10;• Poor quality"></textarea>
          </div>
        </div>
      </div>

      <!-- Step 3: Products/Services -->
      <div v-if="currentStep === 3" class="space-y-4">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-gray-900">Products & Services</h3>
          <p class="text-sm text-gray-600 mt-1">Describe what you're offering</p>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product/Service Description <span class="text-red-500">*</span></label>
            <div class="relative">
              <textarea v-model="form.product_description" rows="4" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="We offer..."></textarea>
              <button @click="generateAI('product_description')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Key Features</label>
            <p class="text-xs text-gray-500 mb-1">What features make your offering valuable?</p>
            <textarea v-model="form.product_features" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="• Feature 1&#10;• Feature 2&#10;• Feature 3"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pricing Strategy <span class="text-red-500">*</span></label>
            <div class="relative">
              <textarea v-model="form.pricing_strategy" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Our pricing is based on..."></textarea>
              <button @click="generateAI('pricing_strategy')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unique Selling Points <span class="text-red-500">*</span></label>
            <textarea v-model="form.unique_selling_points" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="What makes you special?"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Production/Delivery Process</label>
            <textarea v-model="form.production_process" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="How do you create/deliver your product?"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Resource Requirements</label>
            <textarea v-model="form.resource_requirements" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Materials, equipment, skills needed..."></textarea>
          </div>
        </div>
      </div>

      <!-- Step 4: Market Research -->
      <div v-if="currentStep === 4" class="space-y-4">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-gray-900">Market Research</h3>
          <p class="text-sm text-gray-600 mt-1">Understand your market and customers</p>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Target Market <span class="text-red-500">*</span></label>
            <div class="relative">
              <textarea v-model="form.target_market" rows="4" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Our ideal customers are..."></textarea>
              <button @click="generateAI('target_market')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Demographics</label>
            <p class="text-xs text-gray-500 mb-1">Age, gender, income, location, etc.</p>
            <textarea v-model="form.customer_demographics" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Age: 25-45, Income: K5,000+/month..."></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Market Size</label>
            <p class="text-xs text-gray-500 mb-1">How big is your potential market?</p>
            <div class="relative">
              <textarea v-model="form.market_size" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Estimated market size and growth..."></textarea>
              <button @click="generateAI('market_size')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Main Competitors</label>
            <textarea v-model="form.competitors" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="List your main competitors..."></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Competitive Analysis</label>
            <p class="text-xs text-gray-500 mb-1">How do you compare to competitors?</p>
            <div class="relative">
              <textarea v-model="form.competitive_analysis" rows="4" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Strengths, weaknesses, opportunities..."></textarea>
              <button @click="generateAI('competitive_analysis')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 5: Marketing & Sales Strategy -->
      <div v-if="currentStep === 5" class="space-y-4">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-gray-900">Marketing & Sales Strategy</h3>
          <p class="text-sm text-gray-600 mt-1">How will you reach and sell to customers?</p>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Marketing Channels <span class="text-red-500">*</span></label>
            <p class="text-xs text-gray-500 mb-2">Select all that apply</p>
            <div class="grid grid-cols-2 gap-2">
              <label v-for="channel in marketingChannelOptions" :key="channel" class="flex items-center p-2.5 border border-gray-200 rounded-lg hover:bg-gray-50 active:bg-gray-100 cursor-pointer text-sm">
                <input type="checkbox" :value="channel" v-model="form.marketing_channels" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-2" />
                <span class="text-gray-700">{{ channel }}</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Branding Approach</label>
            <p class="text-xs text-gray-500 mb-1">How will you position your brand?</p>
            <div class="relative">
              <textarea v-model="form.branding_approach" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Our brand represents..."></textarea>
              <button @click="generateAI('branding_approach')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sales Channels <span class="text-red-500">*</span></label>
            <p class="text-xs text-gray-500 mb-2">How will customers buy from you?</p>
            <div class="grid grid-cols-2 gap-2">
              <label v-for="channel in salesChannelOptions" :key="channel" class="flex items-center p-2.5 border border-gray-200 rounded-lg hover:bg-gray-50 active:bg-gray-100 cursor-pointer text-sm">
                <input type="checkbox" :value="channel" v-model="form.sales_channels" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-2" />
                <span class="text-gray-700">{{ channel }}</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Retention Strategy</label>
            <p class="text-xs text-gray-500 mb-1">How will you keep customers coming back?</p>
            <div class="relative">
              <textarea v-model="form.customer_retention" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Loyalty programs, follow-ups..."></textarea>
              <button @click="generateAI('customer_retention')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 6: Operations Plan -->
      <div v-if="currentStep === 6" class="space-y-4">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-gray-900">Operations Plan</h3>
          <p class="text-sm text-gray-600 mt-1">How will your business run day-to-day?</p>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Daily Operations <span class="text-red-500">*</span></label>
            <textarea v-model="form.daily_operations" rows="4" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Describe typical daily activities..."></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Staff & Roles</label>
            <p class="text-xs text-gray-500 mb-1">Who do you need and what will they do?</p>
            <textarea v-model="form.staff_roles" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Manager: oversees operations&#10;Sales: customer service..."></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Equipment & Tools</label>
            <p class="text-xs text-gray-500 mb-1">What do you need to operate?</p>
            <textarea v-model="form.equipment_tools" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Computers, machinery, vehicles..."></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Suppliers</label>
            <p class="text-xs text-gray-500 mb-1">Key suppliers and partners</p>
            <textarea v-model="form.supplier_list" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="List main suppliers..."></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Operational Workflow</label>
            <p class="text-xs text-gray-500 mb-1">Step-by-step process</p>
            <textarea v-model="form.operational_workflow" rows="4" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="1. Receive order&#10;2. Process payment&#10;3. Deliver product..."></textarea>
          </div>
        </div>
      </div>

      <!-- Step 7: Financial Plan -->
      <div v-if="currentStep === 7" class="space-y-4">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-gray-900">Financial Plan</h3>
          <p class="text-sm text-gray-600 mt-1">Project your costs, revenue, and profitability</p>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
          <div class="flex items-start">
            <InformationCircleIcon class="h-5 w-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" />
            <div>
              <h4 class="font-semibold text-blue-900 text-sm">Smart Financial Calculator</h4>
              <p class="text-xs text-blue-700 mt-1">Just enter your basic numbers - we'll do the math for you:</p>
              <ul class="text-xs text-blue-700 mt-2 ml-4 list-disc space-y-1">
                <li><strong>Monthly Revenue</strong> = Price Per Unit × Sales Volume (auto-calculated)</li>
                <li><strong>Monthly Profit</strong> = Revenue - Operating Costs</li>
                <li><strong>Break-even Point</strong> = Months to recover startup costs</li>
                <li><strong>Profit Margin</strong> = Profit as % of revenue</li>
                <li><strong>Yearly Projections</strong> = Monthly profit × 12</li>
              </ul>
            </div>
          </div>
        </div>
        
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Startup Costs (K) <span class="text-red-500">*</span></label>
              <input v-model.number="form.startup_costs" type="number" min="0" step="100" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="50000" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Costs (K) <span class="text-red-500">*</span></label>
              <input v-model.number="form.monthly_operating_costs" type="number" min="0" step="100" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="10000" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Price Per Unit (K) <span class="text-red-500">*</span></label>
              <input v-model.number="form.price_per_unit" type="number" min="0" step="10" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Sales/Month <span class="text-red-500">*</span></label>
              <input v-model.number="form.expected_sales_volume" type="number" min="0" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="50" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Expected Monthly Revenue (K)</label>
            <p class="text-xs text-gray-500 mb-1">Auto-calculated from Price × Volume</p>
            <div class="relative">
              <input :value="formatNumber(form.expected_monthly_revenue || 0)" type="text" readonly class="w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed" placeholder="Auto-calculated" />
              <div class="absolute right-3 top-3 text-green-600">
                <SparklesIcon class="w-5 h-5" />
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Staff Salaries (K/month)</label>
              <input v-model.number="form.staff_salaries" type="number" min="0" step="100" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="5000" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Inventory Costs (K/month)</label>
              <input v-model.number="form.inventory_costs" type="number" min="0" step="100" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="3000" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Utilities (K/month)</label>
            <input v-model.number="form.utilities_costs" type="number" min="0" step="50" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="1000" />
          </div>

          <!-- Financial Summary -->
          <div class="mt-6 bg-gradient-to-br from-green-50 to-blue-50 border border-green-200 rounded-lg p-4">
            <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
              <SparklesIcon class="w-4 h-4 mr-2 text-green-600" />
              Financial Projections (Auto-Calculated)
            </h4>
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <p class="text-xs text-gray-600 mb-1">Monthly Profit</p>
                <p class="text-lg font-bold" :class="financialCalculations.monthlyProfit >= 0 ? 'text-green-600' : 'text-red-600'">
                  K{{ formatNumber(financialCalculations.monthlyProfit) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Revenue - Costs</p>
              </div>
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <p class="text-xs text-gray-600 mb-1">Break-Even</p>
                <p class="text-lg font-bold text-blue-600">
                  {{ financialCalculations.breakEvenMonths }} {{ financialCalculations.breakEvenMonths !== '∞' ? 'mo' : '' }}
                </p>
                <p class="text-xs text-gray-500 mt-1">To recover costs</p>
              </div>
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <p class="text-xs text-gray-600 mb-1">Profit Margin</p>
                <p class="text-lg font-bold text-purple-600">
                  {{ financialCalculations.profitMargin }}%
                </p>
                <p class="text-xs text-gray-500 mt-1">Profit % of revenue</p>
              </div>
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <p class="text-xs text-gray-600 mb-1">Yearly Profit</p>
                <p class="text-lg font-bold text-green-600">
                  K{{ formatNumber(financialCalculations.yearlyProfit) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">12-month projection</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 8: Risk Analysis -->
      <div v-if="currentStep === 8" class="space-y-4">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-gray-900">Risk Analysis</h3>
          <p class="text-sm text-gray-600 mt-1">Identify potential risks and how to mitigate them</p>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Key Risks <span class="text-red-500">*</span></label>
            <p class="text-xs text-gray-500 mb-1">What could go wrong?</p>
            <div class="relative">
              <textarea v-model="form.key_risks" rows="5" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="• Financial: Cash flow issues&#10;• Operational: Supply chain disruptions&#10;• Market: New competitors"></textarea>
              <button @click="generateAI('key_risks')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mitigation Strategies <span class="text-red-500">*</span></label>
            <p class="text-xs text-gray-500 mb-1">How will you address these risks?</p>
            <div class="relative">
              <textarea v-model="form.mitigation_strategies" rows="5" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="• Maintain 3-month cash reserve&#10;• Diversify suppliers&#10;• Continuous market monitoring"></textarea>
              <button @click="generateAI('mitigation_strategies')" :disabled="aiLoading" class="absolute bottom-2 right-2 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 active:bg-purple-300 disabled:opacity-50">
                <SparklesIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 9: Implementation Roadmap -->
      <div v-if="currentStep === 9" class="space-y-4">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-gray-900">Implementation Roadmap</h3>
          <p class="text-sm text-gray-600 mt-1">Plan your launch and growth timeline</p>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Timeline</label>
            <p class="text-xs text-gray-500 mb-1">Month-by-month plan</p>
            <textarea v-model="form.timeline" rows="6" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Month 1: Business registration, setup&#10;Month 2: Marketing launch&#10;Month 3: First sales..."></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Key Milestones <span class="text-red-500">*</span></label>
            <textarea v-model="form.milestones" rows="4" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="• Complete registration&#10;• Launch website&#10;• First 10 customers&#10;• Break even"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Responsibilities</label>
            <p class="text-xs text-gray-500 mb-1">Who is responsible for what?</p>
            <textarea v-model="form.responsibilities" rows="4" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Founder: Overall strategy&#10;Manager: Operations&#10;Marketing Lead: Customer acquisition"></textarea>
          </div>
        </div>
      </div>

      <!-- Step 10: Review & Export -->
      <div v-if="currentStep === 10" class="space-y-4">
        <div class="mb-4">
          <h3 class="text-xl font-bold text-gray-900">Review & Export</h3>
          <p class="text-sm text-gray-600 mt-1">Review your business plan and choose export options</p>
        </div>
        
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
          <div class="flex items-start">
            <CheckCircleIcon class="h-6 w-6 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
            <div>
              <h4 class="font-semibold text-green-900">Business Plan Complete!</h4>
              <p class="text-sm text-green-700 mt-1">You've completed all sections. Review your plan below and export in your preferred format.</p>
            </div>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="space-y-3">
          <div class="border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-2">Business Overview</h4>
            <p class="text-sm text-gray-600"><strong>Name:</strong> {{ form.business_name }}</p>
            <p class="text-sm text-gray-600"><strong>Industry:</strong> {{ form.industry }}</p>
            <p class="text-sm text-gray-600"><strong>Location:</strong> {{ form.city }}, {{ form.country }}</p>
          </div>
          
          <div class="border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-2">Financial Snapshot</h4>
            <p class="text-sm text-gray-600"><strong>Startup Costs:</strong> K{{ formatNumber(form.startup_costs || 0) }}</p>
            <p class="text-sm text-gray-600"><strong>Monthly Revenue:</strong> K{{ formatNumber(form.expected_monthly_revenue || 0) }}</p>
            <p class="text-sm text-gray-600"><strong>Monthly Profit:</strong> K{{ formatNumber(financialCalculations?.monthlyProfit || 0) }}</p>
          </div>
        </div>

        <!-- Preview & Download -->
        <div class="mt-6 space-y-3">
          <button @click="showPreview = true" class="w-full flex items-center justify-center px-6 py-3 bg-blue-600 text-white text-base font-semibold rounded-lg hover:bg-blue-700 active:bg-blue-800 shadow-lg">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Preview Business Plan
          </button>

          <button @click="exportPlan('pdf')" class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-base font-semibold rounded-lg hover:from-purple-700 hover:to-indigo-700 active:from-purple-800 active:to-indigo-800 shadow-lg">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download PDF (Premium)
          </button>

          <button @click="exportPlan('word')" class="w-full flex items-center justify-center px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 text-base font-semibold rounded-lg hover:bg-gray-50 active:bg-gray-100">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download Word (Premium)
          </button>
        </div>
      </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="fixed left-0 right-0 bg-white border-t border-gray-200 p-4 flex gap-3 shadow-2xl z-50" style="bottom: 60px;">
      <button v-if="currentStep > 1" @click="previousStep" class="flex-1 px-4 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 active:bg-gray-100">
        ← Previous
      </button>
      <button @click="nextStep" :disabled="!canProceed" class="px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 active:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed" :class="currentStep === 1 ? 'flex-1' : 'flex-1'">
        {{ currentStep === totalSteps ? 'Finish ✓' : 'Next →' }}
      </button>
    </div>

    <!-- Mobile Preview Modal -->
    <div v-if="showPreview" class="fixed inset-0 z-[60] bg-white overflow-y-auto">
      <!-- Preview Header -->
      <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-4 flex items-center justify-between z-10 shadow-lg">
        <h2 class="text-lg font-bold text-white">Business Plan Preview</h2>
        <button @click="showPreview = false" class="text-white p-2">
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <!-- Preview Content -->
      <div class="p-4 space-y-4 pb-32">
        <!-- Executive Summary -->
        <section class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-600">
          <h3 class="text-lg font-bold text-gray-900 mb-3">{{ form.business_name }}</h3>
          <div class="space-y-2 text-sm">
            <p><strong>Industry:</strong> {{ form.industry }}</p>
            <p><strong>Location:</strong> {{ form.city }}, {{ form.country }}</p>
            <p><strong>Legal Structure:</strong> {{ form.legal_structure?.replace('_', ' ') }}</p>
          </div>
          <div v-if="form.mission_statement" class="mt-3 bg-blue-50 p-3 rounded-lg">
            <p class="text-xs font-semibold text-blue-900 mb-1">Mission</p>
            <p class="text-sm text-gray-700">{{ form.mission_statement }}</p>
          </div>
          <div v-if="form.vision_statement" class="mt-2 bg-purple-50 p-3 rounded-lg">
            <p class="text-xs font-semibold text-purple-900 mb-1">Vision</p>
            <p class="text-sm text-gray-700">{{ form.vision_statement }}</p>
          </div>
        </section>

        <!-- Problem & Solution -->
        <section v-if="form.problem_statement" class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
          <h3 class="text-lg font-bold text-gray-900 mb-3">Problem & Solution</h3>
          <div class="space-y-3">
            <div class="bg-red-50 p-3 rounded-lg">
              <p class="text-xs font-semibold text-red-900 mb-1">Problem</p>
              <p class="text-sm text-gray-700">{{ form.problem_statement }}</p>
            </div>
            <div v-if="form.solution_description" class="bg-green-50 p-3 rounded-lg">
              <p class="text-xs font-semibold text-green-900 mb-1">Solution</p>
              <p class="text-sm text-gray-700">{{ form.solution_description }}</p>
            </div>
            <div v-if="form.competitive_advantage" class="bg-yellow-50 p-3 rounded-lg">
              <p class="text-xs font-semibold text-yellow-900 mb-1">Competitive Advantage</p>
              <p class="text-sm text-gray-700">{{ form.competitive_advantage }}</p>
            </div>
          </div>
        </section>

        <!-- Products/Services -->
        <section v-if="form.product_description" class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
          <h3 class="text-lg font-bold text-gray-900 mb-3">Products & Services</h3>
          <p class="text-sm text-gray-700 mb-2">{{ form.product_description }}</p>
          <div v-if="form.pricing_strategy" class="bg-purple-50 p-3 rounded-lg mt-2">
            <p class="text-xs font-semibold text-purple-900 mb-1">Pricing</p>
            <p class="text-sm text-gray-700">{{ form.pricing_strategy }}</p>
          </div>
        </section>

        <!-- Market Research -->
        <section v-if="form.target_market" class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
          <h3 class="text-lg font-bold text-gray-900 mb-3">Market Analysis</h3>
          <div class="bg-green-50 p-3 rounded-lg">
            <p class="text-xs font-semibold text-green-900 mb-1">Target Market</p>
            <p class="text-sm text-gray-700">{{ form.target_market }}</p>
          </div>
          <div v-if="form.market_size" class="bg-blue-50 p-3 rounded-lg mt-2">
            <p class="text-xs font-semibold text-blue-900 mb-1">Market Size</p>
            <p class="text-sm text-gray-700">{{ form.market_size }}</p>
          </div>
        </section>

        <!-- Marketing & Sales -->
        <section v-if="form.marketing_channels?.length" class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-orange-500">
          <h3 class="text-lg font-bold text-gray-900 mb-3">Marketing & Sales</h3>
          <div class="space-y-2">
            <div>
              <p class="text-xs font-semibold text-gray-700 mb-1">Marketing Channels</p>
              <div class="flex flex-wrap gap-1">
                <span v-for="channel in form.marketing_channels" :key="channel" class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded">
                  {{ channel }}
                </span>
              </div>
            </div>
            <div v-if="form.sales_channels?.length">
              <p class="text-xs font-semibold text-gray-700 mb-1 mt-2">Sales Channels</p>
              <div class="flex flex-wrap gap-1">
                <span v-for="channel in form.sales_channels" :key="channel" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                  {{ channel }}
                </span>
              </div>
            </div>
          </div>
        </section>

        <!-- Operations -->
        <section v-if="form.daily_operations" class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-indigo-500">
          <h3 class="text-lg font-bold text-gray-900 mb-3">Operations</h3>
          <p class="text-sm text-gray-700">{{ form.daily_operations }}</p>
          <div v-if="form.staff_roles" class="bg-indigo-50 p-3 rounded-lg mt-2">
            <p class="text-xs font-semibold text-indigo-900 mb-1">Staff & Roles</p>
            <p class="text-sm text-gray-700">{{ form.staff_roles }}</p>
          </div>
        </section>

        <!-- Financial Plan -->
        <section v-if="form.startup_costs" class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-emerald-500">
          <h3 class="text-lg font-bold text-gray-900 mb-3">Financial Plan</h3>
          <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-lg p-4 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-700">Startup Costs</span>
              <span class="font-bold">K{{ formatNumber(form.startup_costs) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-700">Monthly Costs</span>
              <span class="font-bold">K{{ formatNumber(form.monthly_operating_costs) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-700">Monthly Revenue</span>
              <span class="font-bold text-emerald-600">K{{ formatNumber(form.expected_monthly_revenue) }}</span>
            </div>
            <div class="flex justify-between text-sm pt-2 border-t border-emerald-200">
              <span class="text-gray-900 font-semibold">Monthly Profit</span>
              <span class="font-bold text-lg" :class="financialCalculations.monthlyProfit >= 0 ? 'text-green-600' : 'text-red-600'">
                K{{ formatNumber(financialCalculations.monthlyProfit) }}
              </span>
            </div>
            <div class="flex justify-between text-xs text-gray-600">
              <span>Break-Even</span>
              <span>{{ financialCalculations.breakEvenMonths }} months</span>
            </div>
            <div class="flex justify-between text-xs text-gray-600">
              <span>Profit Margin</span>
              <span>{{ financialCalculations.profitMargin }}%</span>
            </div>
          </div>
        </section>

        <!-- Risks -->
        <section v-if="form.key_risks" class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
          <h3 class="text-lg font-bold text-gray-900 mb-3">Risk Analysis</h3>
          <div class="bg-yellow-50 p-3 rounded-lg mb-2">
            <p class="text-xs font-semibold text-yellow-900 mb-1">Key Risks</p>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ form.key_risks }}</p>
          </div>
          <div v-if="form.mitigation_strategies" class="bg-green-50 p-3 rounded-lg">
            <p class="text-xs font-semibold text-green-900 mb-1">Mitigation</p>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ form.mitigation_strategies }}</p>
          </div>
        </section>

        <!-- Implementation -->
        <section v-if="form.milestones" class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-pink-500">
          <h3 class="text-lg font-bold text-gray-900 mb-3">Implementation</h3>
          <div class="bg-pink-50 p-3 rounded-lg">
            <p class="text-xs font-semibold text-pink-900 mb-1">Key Milestones</p>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ form.milestones }}</p>
          </div>
        </section>

        <!-- Footer -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
          <p class="text-xs text-blue-800">
            <strong>Generated by MyGrowNet</strong><br>
            {{ new Date().toLocaleDateString() }}
          </p>
        </div>
      </div>

      <!-- Bottom Actions -->
      <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 space-y-2 shadow-2xl">
        <button @click="exportPlan('pdf')" class="w-full bg-gradient-to-r from-red-600 to-pink-600 text-white font-semibold py-3 rounded-lg flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Download PDF
        </button>
        <button @click="showPreview = false" class="w-full bg-gray-100 text-gray-700 font-medium py-2 rounded-lg">
          Close Preview
        </button>
      </div>
    </div>
  </div>
</template>


<script setup lang="ts">
import { ref, computed, watch, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { XMarkIcon, ChevronLeftIcon, SparklesIcon, InformationCircleIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

interface Props {
  show: boolean;
  existingPlan?: any;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'viewAllPlans']);

const currentStep = ref(1);
const totalSteps = 10;
const saving = ref(false);
const showPreview = ref(false);
const aiLoading = ref(false);

const steps = [
  { short: 'Info', full: 'Business Information' },
  { short: 'Problem', full: 'Problem & Solution' },
  { short: 'Product', full: 'Products/Services' },
  { short: 'Market', full: 'Market Research' },
  { short: 'Marketing', full: 'Marketing & Sales' },
  { short: 'Operations', full: 'Operations Plan' },
  { short: 'Finance', full: 'Financial Plan' },
  { short: 'Risk', full: 'Risk Analysis' },
  { short: 'Roadmap', full: 'Implementation' },
  { short: 'Export', full: 'Review & Export' },
];

const industries = [
  'Agriculture',
  'Retail',
  'Transport',
  'ICT/Technology',
  'Manufacturing',
  'Education',
  'Hospitality',
  'Real Estate',
  'Construction',
  'Freelancing/Services',
  'Healthcare',
  'Food & Beverage',
  'Beauty & Wellness',
  'Entertainment',
  'Other'
];

const marketingChannelOptions = [
  'Facebook',
  'Instagram',
  'WhatsApp',
  'TikTok',
  'LinkedIn',
  'Twitter/X',
  'YouTube',
  'Google Ads',
  'Flyers/Posters',
  'Radio',
  'TV',
  'Referrals',
  'Events',
  'Email Marketing',
  'SMS Marketing',
  'Community Groups'
];

const salesChannelOptions = [
  'Physical Store',
  'Online Store',
  'Mobile App',
  'Social Media',
  'Phone Orders',
  'Agents/Distributors',
  'Wholesale',
  'Direct Sales',
  'Marketplace (e.g., Jumia)',
  'Door-to-Door'
];

const form = ref({
  id: null as number | null,
  business_name: '',
  industry: '',
  country: 'Zambia',
  province: '',
  city: '',
  legal_structure: '',
  mission_statement: '',
  vision_statement: '',
  background: '',
  problem_statement: '',
  solution_description: '',
  competitive_advantage: '',
  customer_pain_points: '',
  product_description: '',
  product_features: '',
  pricing_strategy: '',
  unique_selling_points: '',
  production_process: '',
  resource_requirements: '',
  target_market: '',
  customer_demographics: '',
  market_size: '',
  competitors: '',
  competitive_analysis: '',
  marketing_channels: [] as string[],
  branding_approach: '',
  sales_channels: [] as string[],
  customer_retention: '',
  daily_operations: '',
  staff_roles: '',
  equipment_tools: '',
  supplier_list: '',
  operational_workflow: '',
  startup_costs: null as number | null,
  monthly_operating_costs: null as number | null,
  expected_monthly_revenue: null as number | null,
  price_per_unit: null as number | null,
  expected_sales_volume: null as number | null,
  staff_salaries: null as number | null,
  inventory_costs: null as number | null,
  utilities_costs: null as number | null,
  key_risks: '',
  mitigation_strategies: '',
  timeline: '',
  milestones: '',
  responsibilities: '',
  status: 'draft' as 'draft' | 'completed' | 'archived',
  current_step: 1,
});

// Hide body scrollbar when modal is open
watch(() => props.show, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
}, { immediate: true });

// Load existing plan if available
watch(() => props.existingPlan, (plan) => {
  if (plan && props.show) {
    console.log('Loading existing plan:', plan);
    // Deep copy to avoid reactivity issues
    form.value = {
      ...form.value,
      ...plan,
      // Ensure arrays are properly loaded
      marketing_channels: Array.isArray(plan.marketing_channels) ? [...plan.marketing_channels] : 
                         (typeof plan.marketing_channels === 'string' ? JSON.parse(plan.marketing_channels || '[]') : []),
      sales_channels: Array.isArray(plan.sales_channels) ? [...plan.sales_channels] : 
                     (typeof plan.sales_channels === 'string' ? JSON.parse(plan.sales_channels || '[]') : []),
    };
    currentStep.value = plan.current_step || 1;
    console.log('Loaded existing plan:', plan.business_name, 'Step:', currentStep.value, 'Form ID:', form.value.id);
  } else if (!plan && props.show) {
    // Reset form for new plan
    console.log('Creating new plan - resetting form');
    resetForm();
  }
}, { immediate: true });

// Financial calculations
const financialCalculations = computed(() => {
  const revenue = form.value.expected_monthly_revenue || 0;
  const costs = form.value.monthly_operating_costs || 0;
  const monthlyProfit = revenue - costs;
  const startupCosts = form.value.startup_costs || 0;
  
  let breakEvenMonths = '∞';
  if (monthlyProfit > 0 && startupCosts > 0) {
    breakEvenMonths = Math.ceil(startupCosts / monthlyProfit).toString();
  }
  
  const profitMargin = revenue > 0 ? ((monthlyProfit / revenue) * 100).toFixed(1) : '0.0';
  const yearlyProfit = monthlyProfit * 12;
  
  return {
    monthlyProfit,
    breakEvenMonths,
    profitMargin,
    yearlyProfit,
  };
});

// Auto-calculate monthly revenue
watch([() => form.value.price_per_unit, () => form.value.expected_sales_volume], () => {
  form.value.expected_monthly_revenue = (form.value.price_per_unit || 0) * (form.value.expected_sales_volume || 0);
});

const canProceed = computed(() => {
  switch (currentStep.value) {
    case 1:
      return form.value.business_name && form.value.industry && form.value.country && form.value.legal_structure;
    case 2:
      return form.value.problem_statement && form.value.solution_description && form.value.competitive_advantage;
    case 3:
      return form.value.product_description && form.value.pricing_strategy && form.value.unique_selling_points;
    case 4:
      return form.value.target_market;
    case 5:
      return form.value.marketing_channels.length > 0 && form.value.sales_channels.length > 0;
    case 6:
      return form.value.daily_operations;
    case 7:
      return form.value.startup_costs && form.value.startup_costs > 0 && 
             form.value.monthly_operating_costs && form.value.monthly_operating_costs > 0 && 
             form.value.price_per_unit && form.value.price_per_unit > 0 && 
             form.value.expected_sales_volume && form.value.expected_sales_volume > 0;
    case 8:
      return form.value.key_risks && form.value.mitigation_strategies;
    case 9:
      return form.value.milestones;
    default:
      return true;
  }
});

const handleBack = () => {
  if (currentStep.value > 1) {
    previousStep();
  } else {
    emit('close');
  }
};

const nextStep = async () => {
  if (!canProceed.value) return;
  
  if (currentStep.value < totalSteps) {
    currentStep.value++;
    form.value.current_step = currentStep.value;
    await saveDraft();
    window.scrollTo(0, 0);
  } else {
    // Finish
    form.value.status = 'completed';
    await saveDraft();
  }
};

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
    form.value.current_step = currentStep.value;
    window.scrollTo(0, 0);
  }
};

const saveDraft = () => {
  if (saving.value) return;
  
  saving.value = true;
  form.value.current_step = currentStep.value;
  
  router.post(route('mygrownet.tools.business-plan.save'), {
    ...form.value,
    marketing_channels: JSON.stringify(form.value.marketing_channels),
    sales_channels: JSON.stringify(form.value.sales_channels),
  }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (page: any) => {
      if (page.props.businessPlan) {
        form.value.id = page.props.businessPlan.id;
      }
    },
    onFinish: () => {
      saving.value = false;
    },
  });
};

const generateAI = async (field: string) => {
  if (aiLoading.value) return;
  
  aiLoading.value = true;
  try {
    const response = await axios.post(route('mygrownet.tools.business-plan.generate-ai'), {
      field,
      context: {
        business_name: form.value.business_name,
        industry: form.value.industry,
        country: form.value.country,
      },
    });
    
    if (response.data.content) {
      (form.value as any)[field] = response.data.content;
    }
  } catch (error) {
    console.error('AI generation failed:', error);
  } finally {
    aiLoading.value = false;
  }
};

const exportPlan = () => {
  if (form.value.id) {
    // First save, then export
    saveDraft();
    setTimeout(() => {
      window.location.href = route('mygrownet.tools.business-plan.export', { 
        business_plan_id: form.value.id, 
        export_type: 'pdf' 
      });
    }, 500);
  }
};

const viewAllPlans = () => {
  emit('viewAllPlans');
};

const resetForm = () => {
  form.value = {
    id: null,
    business_name: '',
    industry: '',
    country: 'Zambia',
    province: '',
    city: '',
    legal_structure: '',
    mission_statement: '',
    vision_statement: '',
    background: '',
    problem_statement: '',
    solution_description: '',
    competitive_advantage: '',
    customer_pain_points: '',
    product_description: '',
    product_features: '',
    pricing_strategy: '',
    unique_selling_points: '',
    production_process: '',
    resource_requirements: '',
    target_market: '',
    customer_demographics: '',
    market_size: '',
    competitors: '',
    competitive_analysis: '',
    marketing_channels: [],
    branding_approach: '',
    sales_channels: [],
    customer_retention: '',
    daily_operations: '',
    staff_roles: '',
    equipment_tools: '',
    supplier_list: '',
    operational_workflow: '',
    startup_costs: null,
    monthly_operating_costs: null,
    expected_monthly_revenue: null,
    price_per_unit: null,
    expected_sales_volume: null,
    staff_salaries: null,
    inventory_costs: null,
    utilities_costs: null,
    key_risks: '',
    mitigation_strategies: '',
    timeline: '',
    milestones: '',
    responsibilities: '',
    status: 'draft',
    current_step: 1,
  };
  currentStep.value = 1;
};

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US').format(num);
};

// Cleanup: Restore body overflow when component is destroyed
onUnmounted(() => {
  document.body.style.overflow = '';
});
</script>

<style scoped>
input, select, textarea {
  font-size: 16px; /* Prevents zoom on iOS */
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Active state for touch */
button:active {
  transform: scale(0.98);
}
</style>
