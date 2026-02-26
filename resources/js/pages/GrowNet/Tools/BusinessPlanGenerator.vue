<template>
  <MemberLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Business Plan Generator</h1>
            <p class="mt-2 text-gray-600">Create a professional business plan step by step</p>
          </div>
          <button
            v-if="form.id"
            @click="saveDraft"
            :disabled="saving"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50"
          >
            {{ saving ? 'Saving...' : 'Save Draft' }}
          </button>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="mb-8 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-4">
          <span class="text-sm font-medium text-gray-700">Step {{ currentStep }} of {{ totalSteps }}</span>
          <span class="text-sm text-gray-500">{{ Math.round((currentStep / totalSteps) * 100) }}% Complete</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div 
            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
            :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
          ></div>
        </div>
        
        <!-- Step Indicators -->
        <div class="mt-6 grid grid-cols-5 md:grid-cols-10 gap-2">
          <div 
            v-for="(step, index) in steps" 
            :key="index"
            class="flex flex-col items-center cursor-pointer"
            @click="goToStep(index + 1)"
          >
            <div 
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold transition-colors"
              :class="currentStep > index ? 'bg-green-500 text-white' : currentStep === index + 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'"
            >
              <CheckIcon v-if="currentStep > index" class="w-4 h-4" />
              <span v-else>{{ index + 1 }}</span>
            </div>
            <span class="text-xs mt-1 text-center hidden md:block" :class="currentStep === index + 1 ? 'text-blue-600 font-medium' : 'text-gray-500'">
              {{ step.short }}
            </span>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 md:p-8 min-h-[600px]">
        <!-- Step 1: Business Information -->
        <div v-if="currentStep === 1" class="space-y-6">
          <StepHeader 
            title="Business Information"
            description="Tell us about your business basics"
          />
          
          <FormField label="Business Name" required>
            <input
              v-model="form.business_name"
              type="text"
              class="form-input"
              placeholder="e.g., MyGrowNet Success Hub"
            />
          </FormField>

          <FormField label="Industry" required>
            <select v-model="form.industry" class="form-input">
              <option value="">Select industry...</option>
              <option v-for="ind in industries" :key="ind" :value="ind">{{ ind }}</option>
            </select>
          </FormField>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <FormField label="Country" required>
              <input v-model="form.country" type="text" class="form-input" placeholder="Zambia" />
            </FormField>
            <FormField label="Province">
              <input v-model="form.province" type="text" class="form-input" placeholder="Lusaka" />
            </FormField>
            <FormField label="City">
              <input v-model="form.city" type="text" class="form-input" placeholder="Lusaka" />
            </FormField>
          </div>

          <FormField label="Legal Structure" required>
            <select v-model="form.legal_structure" class="form-input">
              <option value="">Select structure...</option>
              <option value="sole_trader">Sole Trader</option>
              <option value="partnership">Partnership</option>
              <option value="company">Company</option>
              <option value="cooperative">Cooperative</option>
            </select>
          </FormField>

          <FormField label="Mission Statement" hint="What is your business purpose?">
            <div class="relative">
              <textarea
                v-model="form.mission_statement"
                rows="3"
                class="form-input"
                placeholder="To provide..."
              ></textarea>
              <AIButton @click="generateAI('mission_statement')" :loading="aiLoading" />
            </div>
          </FormField>

          <FormField label="Vision Statement" hint="Where do you see your business in 5 years?">
            <div class="relative">
              <textarea
                v-model="form.vision_statement"
                rows="3"
                class="form-input"
                placeholder="To become..."
              ></textarea>
              <AIButton @click="generateAI('vision_statement')" :loading="aiLoading" />
            </div>
          </FormField>

          <FormField label="Background/Overview" hint="Brief history or context of your business">
            <textarea
              v-model="form.background"
              rows="4"
              class="form-input"
              placeholder="Our business started..."
            ></textarea>
          </FormField>
        </div>

        <!-- Step 2: Problem and Solution -->
        <div v-if="currentStep === 2" class="space-y-6">
          <StepHeader 
            title="Problem & Solution"
            description="Define the problem you're solving and your solution"
          />
          
          <FormField label="Problem Statement" required hint="What problem does your business solve?">
            <div class="relative">
              <textarea
                v-model="form.problem_statement"
                rows="4"
                class="form-input"
                placeholder="Many people struggle with..."
              ></textarea>
              <AIButton @click="generateAI('problem_statement')" :loading="aiLoading" />
            </div>
          </FormField>

          <FormField label="Solution Description" required hint="How does your business solve this problem?">
            <div class="relative">
              <textarea
                v-model="form.solution_description"
                rows="4"
                class="form-input"
                placeholder="We provide..."
              ></textarea>
              <AIButton @click="generateAI('solution_description')" :loading="aiLoading" />
            </div>
          </FormField>

          <FormField label="Competitive Advantage" required hint="What makes you different from competitors?">
            <div class="relative">
              <textarea
                v-model="form.competitive_advantage"
                rows="3"
                class="form-input"
                placeholder="Unlike competitors, we..."
              ></textarea>
              <AIButton @click="generateAI('competitive_advantage')" :loading="aiLoading" />
            </div>
          </FormField>

          <FormField label="Customer Pain Points" hint="Specific challenges your customers face">
            <textarea
              v-model="form.customer_pain_points"
              rows="3"
              class="form-input"
              placeholder="• High costs&#10;• Limited access&#10;• Poor quality"
            ></textarea>
          </FormField>
        </div>

        <!-- Step 3: Products/Services -->
        <div v-if="currentStep === 3" class="space-y-6">
          <StepHeader 
            title="Products & Services"
            description="Describe what you're offering"
          />
          
          <FormField label="Product/Service Description" required>
            <div class="relative">
              <textarea
                v-model="form.product_description"
                rows="4"
                class="form-input"
                placeholder="We offer..."
              ></textarea>
              <AIButton @click="generateAI('product_description')" :loading="aiLoading" />
            </div>
          </FormField>

          <FormField label="Key Features" hint="What features make your offering valuable?">
            <textarea
              v-model="form.product_features"
              rows="3"
              class="form-input"
              placeholder="• Feature 1&#10;• Feature 2&#10;• Feature 3"
            ></textarea>
          </FormField>

          <FormField label="Pricing Strategy" required>
            <div class="relative">
              <textarea
                v-model="form.pricing_strategy"
                rows="3"
                class="form-input"
                placeholder="Our pricing is based on..."
              ></textarea>
              <AIButton @click="generateAI('pricing_strategy')" :loading="aiLoading" />
            </div>
          </FormField>

          <FormField label="Unique Selling Points" required>
            <textarea
              v-model="form.unique_selling_points"
              rows="3"
              class="form-input"
              placeholder="What makes you special?"
            ></textarea>
          </FormField>

          <FormField label="Production/Delivery Process">
            <textarea
              v-model="form.production_process"
              rows="3"
              class="form-input"
              placeholder="How do you create/deliver your product?"
            ></textarea>
          </FormField>

          <FormField label="Resource Requirements">
            <textarea
              v-model="form.resource_requirements"
              rows="3"
              class="form-input"
              placeholder="Materials, equipment, skills needed..."
            ></textarea>
          </FormField>
        </div>

        <!-- Step 4: Market Research -->
        <div v-if="currentStep === 4" class="space-y-6">
          <StepHeader 
            title="Market Research"
            description="Understand your market and customers"
          />
          
          <FormField label="Target Market" required>
            <div class="relative">
              <textarea
                v-model="form.target_market"
                rows="4"
                class="form-input"
                placeholder="Our ideal customers are..."
              ></textarea>
              <AIButton @click="generateAI('target_market')" :loading="aiLoading" />
            </div>
          </FormField>

          <FormField label="Customer Demographics" hint="Age, gender, income, location, etc.">
            <textarea
              v-model="form.customer_demographics"
              rows="3"
              class="form-input"
              placeholder="Age: 25-45, Income: K5,000+/month..."
            ></textarea>
          </FormField>

          <FormField label="Market Size" hint="How big is your potential market?">
            <div class="relative">
              <textarea
                v-model="form.market_size"
                rows="3"
                class="form-input"
                placeholder="Estimated market size and growth..."
              ></textarea>
              <AIButton @click="generateAI('market_size')" :loading="aiLoading" />
            </div>
          </FormField>

          <FormField label="Main Competitors">
            <textarea
              v-model="form.competitors"
              rows="3"
              class="form-input"
              placeholder="List your main competitors..."
            ></textarea>
          </FormField>

          <FormField label="Competitive Analysis" hint="How do you compare to competitors?">
            <div class="relative">
              <textarea
                v-model="form.competitive_analysis"
                rows="4"
                class="form-input"
                placeholder="Strengths, weaknesses, opportunities..."
              ></textarea>
              <AIButton @click="generateAI('competitive_analysis')" :loading="aiLoading" />
            </div>
          </FormField>
        </div>

        <!-- Step 5: Marketing & Sales Strategy -->
        <div v-if="currentStep === 5" class="space-y-6">
          <StepHeader 
            title="Marketing & Sales Strategy"
            description="How will you reach and sell to customers?"
          />
          
          <FormField label="Marketing Channels" required hint="Select all that apply">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
              <label v-for="channel in marketingChannelOptions" :key="channel" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input
                  type="checkbox"
                  :value="channel"
                  v-model="form.marketing_channels"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <span class="ml-3 text-sm text-gray-700">{{ channel }}</span>
              </label>
            </div>
          </FormField>

          <FormField label="Branding Approach" hint="How will you position your brand?">
            <div class="relative">
              <textarea
                v-model="form.branding_approach"
                rows="3"
                class="form-input"
                placeholder="Our brand represents..."
              ></textarea>
              <AIButton @click="generateAI('branding_approach')" :loading="aiLoading" />
            </div>
          </FormField>

          <FormField label="Sales Channels" required hint="How will customers buy from you?">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
              <label v-for="channel in salesChannelOptions" :key="channel" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input
                  type="checkbox"
                  :value="channel"
                  v-model="form.sales_channels"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <span class="ml-3 text-sm text-gray-700">{{ channel }}</span>
              </label>
            </div>
          </FormField>

          <FormField label="Customer Retention Strategy" hint="How will you keep customers coming back?">
            <div class="relative">
              <textarea
                v-model="form.customer_retention"
                rows="3"
                class="form-input"
                placeholder="Loyalty programs, follow-ups..."
              ></textarea>
              <AIButton @click="generateAI('customer_retention')" :loading="aiLoading" />
            </div>
          </FormField>
        </div>

        <!-- Step 6: Operations Plan -->
        <div v-if="currentStep === 6" class="space-y-6">
          <StepHeader 
            title="Operations Plan"
            description="How will your business run day-to-day?"
          />
          
          <FormField label="Daily Operations" required>
            <textarea
              v-model="form.daily_operations"
              rows="4"
              class="form-input"
              placeholder="Describe typical daily activities..."
            ></textarea>
          </FormField>

          <FormField label="Staff & Roles" hint="Who do you need and what will they do?">
            <textarea
              v-model="form.staff_roles"
              rows="3"
              class="form-input"
              placeholder="Manager: oversees operations&#10;Sales: customer service..."
            ></textarea>
          </FormField>

          <FormField label="Equipment & Tools" hint="What do you need to operate?">
            <textarea
              v-model="form.equipment_tools"
              rows="3"
              class="form-input"
              placeholder="Computers, machinery, vehicles..."
            ></textarea>
          </FormField>

          <FormField label="Suppliers" hint="Key suppliers and partners">
            <textarea
              v-model="form.supplier_list"
              rows="3"
              class="form-input"
              placeholder="List main suppliers..."
            ></textarea>
          </FormField>

          <FormField label="Operational Workflow" hint="Step-by-step process">
            <textarea
              v-model="form.operational_workflow"
              rows="4"
              class="form-input"
              placeholder="1. Receive order&#10;2. Process payment&#10;3. Deliver product..."
            ></textarea>
          </FormField>
        </div>

        <!-- Step 7: Financial Plan -->
        <div v-if="currentStep === 7" class="space-y-6">
          <StepHeader 
            title="Financial Plan"
            description="Project your costs, revenue, and profitability"
          />
          
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
              <InformationCircleIcon class="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" />
              <div>
                <h4 class="font-semibold text-blue-900">Smart Financial Calculator</h4>
                <p class="text-sm text-blue-700 mt-1">Just enter your basic numbers - we'll do the math for you:</p>
                <ul class="text-sm text-blue-700 mt-2 ml-4 list-disc space-y-1">
                  <li><strong>Monthly Revenue</strong> = Price Per Unit × Sales Volume (auto-calculated)</li>
                  <li><strong>Monthly Profit</strong> = Revenue - Operating Costs</li>
                  <li><strong>Break-even Point</strong> = Months to recover startup costs</li>
                  <li><strong>Profit Margin</strong> = Profit as % of revenue</li>
                  <li><strong>Yearly Projections</strong> = Monthly profit × 12</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormField label="Startup Costs (K)" required>
              <input
                v-model.number="form.startup_costs"
                type="number"
                min="0"
                step="100"
                class="form-input"
                placeholder="50000"
              />
            </FormField>

            <FormField label="Monthly Operating Costs (K)" required>
              <input
                v-model.number="form.monthly_operating_costs"
                type="number"
                min="0"
                step="100"
                class="form-input"
                placeholder="10000"
              />
            </FormField>

            <FormField label="Price Per Unit/Service (K)" required>
              <input
                v-model.number="form.price_per_unit"
                type="number"
                min="0"
                step="10"
                class="form-input"
                placeholder="500"
              />
            </FormField>

            <FormField label="Expected Sales Volume (units/month)" required>
              <input
                v-model.number="form.expected_sales_volume"
                type="number"
                min="0"
                class="form-input"
                placeholder="50"
              />
            </FormField>

            <FormField label="Expected Monthly Revenue (K)" hint="Auto-calculated from Price × Volume">
              <div class="relative">
                <input
                  :value="formatNumber(form.expected_monthly_revenue || 0)"
                  type="text"
                  readonly
                  class="form-input bg-gray-50 cursor-not-allowed"
                  placeholder="Auto-calculated"
                />
                <div class="absolute right-3 top-3 text-green-600">
                  <SparklesIcon class="w-5 h-5" />
                </div>
              </div>
            </FormField>

            <FormField label="Staff Salaries (K/month)">
              <input
                v-model.number="form.staff_salaries"
                type="number"
                min="0"
                step="100"
                class="form-input"
                placeholder="5000"
              />
            </FormField>

            <FormField label="Inventory Costs (K/month)">
              <input
                v-model.number="form.inventory_costs"
                type="number"
                min="0"
                step="100"
                class="form-input"
                placeholder="3000"
              />
            </FormField>

            <FormField label="Utilities (K/month)">
              <input
                v-model.number="form.utilities_costs"
                type="number"
                min="0"
                step="50"
                class="form-input"
                placeholder="1000"
              />
            </FormField>
          </div>

          <!-- Financial Summary -->
          <div class="mt-8 bg-gradient-to-br from-green-50 to-blue-50 border border-green-200 rounded-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
              <SparklesIcon class="w-5 h-5 mr-2 text-green-600" />
              Financial Projections (Auto-Calculated)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-sm text-gray-600 mb-1">Monthly Profit</p>
                <p class="text-2xl font-bold" :class="financialCalculations.monthlyProfit >= 0 ? 'text-green-600' : 'text-red-600'">
                  K{{ formatNumber(financialCalculations.monthlyProfit) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Revenue - Operating Costs</p>
              </div>
              <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-sm text-gray-600 mb-1">Break-Even Point</p>
                <p class="text-2xl font-bold text-blue-600">
                  {{ financialCalculations.breakEvenMonths }} {{ financialCalculations.breakEvenMonths !== '∞' ? 'months' : '' }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Time to recover startup costs</p>
              </div>
              <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-sm text-gray-600 mb-1">Profit Margin</p>
                <p class="text-2xl font-bold text-purple-600">
                  {{ financialCalculations.profitMargin }}%
                </p>
                <p class="text-xs text-gray-500 mt-1">Profit as % of revenue</p>
              </div>
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-sm text-gray-600 mb-1">Yearly Profit Projection</p>
                <p class="text-xl font-bold text-green-600">
                  K{{ formatNumber(financialCalculations.yearlyProfit) }}
                </p>
              </div>
              <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-sm text-gray-600 mb-1">Revenue per Unit</p>
                <p class="text-xl font-bold text-blue-600">
                  K{{ formatNumber(form.price_per_unit || 0) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 8: Risk Analysis -->
        <div v-if="currentStep === 8" class="space-y-6">
          <StepHeader 
            title="Risk Analysis"
            description="Identify potential risks and how to mitigate them"
          />
          
          <FormField label="Key Risks" required hint="What could go wrong?">
            <div class="relative">
              <textarea
                v-model="form.key_risks"
                rows="5"
                class="form-input"
                placeholder="• Financial: Cash flow issues&#10;• Operational: Supply chain disruptions&#10;• Market: New competitors"
              ></textarea>
              <AIButton @click="generateAI('key_risks')" :loading="aiLoading" />
            </div>
          </FormField>

          <FormField label="Mitigation Strategies" required hint="How will you address these risks?">
            <div class="relative">
              <textarea
                v-model="form.mitigation_strategies"
                rows="5"
                class="form-input"
                placeholder="• Maintain 3-month cash reserve&#10;• Diversify suppliers&#10;• Continuous market monitoring"
              ></textarea>
              <AIButton @click="generateAI('mitigation_strategies')" :loading="aiLoading" />
            </div>
          </FormField>
        </div>

        <!-- Step 9: Implementation Roadmap -->
        <div v-if="currentStep === 9" class="space-y-6">
          <StepHeader 
            title="Implementation Roadmap"
            description="Plan your launch and growth timeline"
          />
          
          <FormField label="Timeline" hint="Month-by-month plan">
            <textarea
              v-model="form.timeline"
              rows="6"
              class="form-input"
              placeholder="Month 1: Business registration, setup&#10;Month 2: Marketing launch&#10;Month 3: First sales..."
            ></textarea>
          </FormField>

          <FormField label="Key Milestones" required>
            <textarea
              v-model="form.milestones"
              rows="4"
              class="form-input"
              placeholder="• Complete registration&#10;• Launch website&#10;• First 10 customers&#10;• Break even"
            ></textarea>
          </FormField>

          <FormField label="Responsibilities" hint="Who is responsible for what?">
            <textarea
              v-model="form.responsibilities"
              rows="4"
              class="form-input"
              placeholder="Founder: Overall strategy&#10;Manager: Operations&#10;Marketing Lead: Customer acquisition"
            ></textarea>
          </FormField>
        </div>

        <!-- Step 10: Review & Export -->
        <div v-if="currentStep === 10" class="space-y-6">
          <StepHeader 
            title="Review & Export"
            description="Review your business plan and choose export options"
          />
          
          <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
            <div class="flex items-start">
              <CheckCircleIcon class="h-6 w-6 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
              <div>
                <h4 class="font-semibold text-green-900">Business Plan Complete!</h4>
                <p class="text-sm text-green-700 mt-1">You've completed all sections. Review your plan below and export in your preferred format.</p>
              </div>
            </div>
          </div>

          <!-- Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border border-gray-200 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-2">Business Overview</h4>
              <p class="text-sm text-gray-600"><strong>Name:</strong> {{ form.business_name }}</p>
              <p class="text-sm text-gray-600"><strong>Industry:</strong> {{ form.industry }}</p>
              <p class="text-sm text-gray-600"><strong>Location:</strong> {{ form.city }}, {{ form.country }}</p>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-2">Financial Snapshot</h4>
              <p class="text-sm text-gray-600"><strong>Startup Costs:</strong> K{{ formatNumber(form.startup_costs) }}</p>
              <p class="text-sm text-gray-600"><strong>Monthly Revenue:</strong> K{{ formatNumber(form.expected_monthly_revenue) }}</p>
              <p class="text-sm text-gray-600"><strong>Monthly Profit:</strong> K{{ formatNumber(financialCalculations?.monthlyProfit || 0) }}</p>
            </div>
          </div>

          <!-- Preview & Download -->
          <div class="mt-8 text-center">
            <button
              @click="showPreview = true"
              class="inline-flex items-center px-8 py-4 bg-blue-600 text-white text-lg font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg"
            >
              <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              Preview & Download Business Plan
            </button>
            <p class="mt-3 text-sm text-gray-500">Review your complete business plan before downloading</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex justify-between mt-8">
        <button
          v-if="currentStep > 1"
          @click="previousStep"
          type="button"
          class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center"
        >
          <ChevronLeftIcon class="w-5 h-5 mr-2" />
          Previous
        </button>
        <div v-else></div>
        
        <div class="flex space-x-4">
          <button
            type="button"
            @click="$inertia.visit(route('mygrownet.tools.index'))"
            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
          >
            Exit
          </button>
          <button
            v-if="currentStep < totalSteps"
            @click="nextStep"
            type="button"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center"
          >
            Next Step
            <ChevronRightIcon class="w-5 h-5 ml-2" />
          </button>
          <button
            v-else
            @click="completePlan"
            type="button"
            :disabled="processing"
            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 flex items-center"
          >
            <CheckCircleIcon class="w-5 h-5 mr-2" />
            {{ processing ? 'Saving...' : 'Complete Plan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Preview Modal -->
    <PreviewModal
      :show="showPreview"
      :plan="form"
      :isPremium="userTier === 'premium'"
      @close="showPreview = false"
      @download="exportPlan"
    />
  </MemberLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import AIButton from '@/Components/BusinessPlan/AIButton.vue';
import ExportCard from '@/Components/BusinessPlan/ExportCard.vue';
import PreviewModal from '@/Components/BusinessPlan/PreviewModal.vue';
import StepHeader from '@/Components/BusinessPlan/StepHeader.vue';
import FormField from '@/Components/BusinessPlan/FormField.vue';
import { 
  CheckIcon, 
  CheckCircleIcon, 
  InformationCircleIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  SparklesIcon
} from '@heroicons/vue/24/outline';

interface Props {
  existingPlan?: any;
  userTier: string;
}

const props = defineProps<Props>();

// Access page props for flash data
const page = usePage();

const totalSteps = 10;
const currentStep = ref(1);
const processing = ref(false);
const saving = ref(false);
const aiLoading = ref(false);
const showPreview = ref(false);

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

// Load existing plan if available
if (props.existingPlan) {
  Object.assign(form.value, props.existingPlan);
  
  // Parse JSON fields
  if (props.existingPlan.marketing_channels) {
    form.value.marketing_channels = JSON.parse(props.existingPlan.marketing_channels);
  }
  if (props.existingPlan.sales_channels) {
    form.value.sales_channels = JSON.parse(props.existingPlan.sales_channels);
  }
  
  // Convert numeric fields from strings to numbers
  const numericFields = [
    'startup_costs',
    'monthly_operating_costs',
    'expected_monthly_revenue',
    'price_per_unit',
    'expected_sales_volume',
    'staff_salaries',
    'inventory_costs',
    'utilities_costs'
  ];
  
  numericFields.forEach(field => {
    if (props.existingPlan[field] !== null && props.existingPlan[field] !== undefined) {
      form.value[field] = Number(props.existingPlan[field]);
    }
  });
  
  currentStep.value = props.existingPlan.current_step || 1;
  
  // Recalculate revenue from price and volume if both exist
  // This ensures the calculation is correct even if saved data is outdated
  if (form.value.price_per_unit && form.value.expected_sales_volume) {
    form.value.expected_monthly_revenue = form.value.price_per_unit * form.value.expected_sales_volume;
  }
}

// Auto-calculate revenue from price and volume
watch([() => form.value.price_per_unit, () => form.value.expected_sales_volume], ([price, volume]) => {
  if (price && volume) {
    form.value.expected_monthly_revenue = price * volume;
  }
});

// Recalculate revenue on mount if price and volume exist
onMounted(() => {
  if (form.value.price_per_unit && form.value.expected_sales_volume) {
    form.value.expected_monthly_revenue = form.value.price_per_unit * form.value.expected_sales_volume;
  }
});

// Financial calculations
const financialCalculations = computed(() => {
  const revenue = Number(form.value.expected_monthly_revenue) || 0;
  const costs = Number(form.value.monthly_operating_costs) || 0;
  const startupCosts = Number(form.value.startup_costs) || 0;
  const monthlyProfit = revenue - costs;
  const profitMargin = revenue > 0 ? ((monthlyProfit / revenue) * 100).toFixed(1) : '0.0';
  const breakEvenMonths = monthlyProfit > 0 ? Math.ceil(startupCosts / monthlyProfit) : '∞';
  
  return {
    monthlyProfit,
    profitMargin,
    breakEvenMonths,
    yearlyProfit: monthlyProfit * 12,
  };
});

// Navigation
const nextStep = () => {
  if (validateStep(currentStep.value)) {
    currentStep.value++;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    saveDraft();
  }
};

const previousStep = () => {
  currentStep.value--;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const goToStep = (step: number) => {
  if (step <= currentStep.value || validateStep(currentStep.value)) {
    currentStep.value = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

// Validation
const validateStep = (step: number): boolean => {
  switch (step) {
    case 1:
      if (!form.value.business_name || !form.value.industry || !form.value.legal_structure) {
        alert('Please fill in all required fields');
        return false;
      }
      break;
    case 2:
      if (!form.value.problem_statement || !form.value.solution_description || !form.value.competitive_advantage) {
        alert('Please fill in all required fields');
        return false;
      }
      break;
    case 3:
      if (!form.value.product_description || !form.value.pricing_strategy || !form.value.unique_selling_points) {
        alert('Please fill in all required fields');
        return false;
      }
      break;
    case 4:
      if (!form.value.target_market) {
        alert('Please fill in all required fields');
        return false;
      }
      break;
    case 5:
      if (form.value.marketing_channels.length === 0 || form.value.sales_channels.length === 0) {
        alert('Please select at least one marketing and sales channel');
        return false;
      }
      break;
    case 6:
      if (!form.value.daily_operations) {
        alert('Please describe your daily operations');
        return false;
      }
      break;
    case 7:
      if (!form.value.startup_costs || !form.value.monthly_operating_costs) {
        alert('Please enter your startup costs and monthly operating costs');
        return false;
      }
      if (!form.value.price_per_unit || !form.value.expected_sales_volume) {
        alert('Please enter your price per unit and expected sales volume');
        return false;
      }
      break;
    case 8:
      if (!form.value.key_risks || !form.value.mitigation_strategies) {
        alert('Please identify risks and mitigation strategies');
        return false;
      }
      break;
    case 9:
      if (!form.value.milestones) {
        alert('Please define your key milestones');
        return false;
      }
      break;
  }
  return true;
};

// Save draft
const saveDraft = () => {
  saving.value = true;
  form.value.current_step = currentStep.value;
  
  router.post(route('mygrownet.tools.business-plan.save'), {
    ...form.value,
    marketing_channels: JSON.stringify(form.value.marketing_channels),
    sales_channels: JSON.stringify(form.value.sales_channels),
  }, {
    preserveScroll: true,
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

// Complete plan
const completePlan = () => {
  if (!validateStep(currentStep.value)) return;
  
  processing.value = true;
  form.value.status = 'completed';
  form.value.current_step = totalSteps;
  
  router.post(route('mygrownet.tools.business-plan.complete'), {
    ...form.value,
    marketing_channels: JSON.stringify(form.value.marketing_channels),
    sales_channels: JSON.stringify(form.value.sales_channels),
  }, {
    onFinish: () => {
      processing.value = false;
    },
  });
};

// AI Generation
const generateAI = (field: string) => {
  if (props.userTier === 'basic') {
    alert('AI generation is a premium feature. Upgrade to access it.');
    return;
  }
  
  aiLoading.value = true;
  
  router.post(route('mygrownet.tools.business-plan.generate-ai'), {
    business_plan_id: form.value.id,
    field,
    context: {
      business_name: form.value.business_name,
      industry: form.value.industry,
      ...form.value,
    },
  }, {
    preserveScroll: true,
    onSuccess: (page: any) => {
      if (page.props.generatedContent) {
        (form.value as any)[field] = page.props.generatedContent;
      }
    },
    onFinish: () => {
      aiLoading.value = false;
    },
  });
};

// Export plan - Direct download
const exportPlan = (type: 'template' | 'pdf' | 'word') => {
  // Check if plan is saved
  if (!form.value.id) {
    alert('Please save your business plan first before exporting.');
    saveDraft();
    return;
  }

  // Check premium access
  if ((type === 'pdf' || type === 'word') && props.userTier !== 'premium') {
    alert('PDF and Word exports are premium features. Upgrade to access them.');
    return;
  }
  
  // Direct download - open in new window
  const url = `/mygrownet/tools/business-plan/export?business_plan_id=${form.value.id}&export_type=${type}`;
  window.open(url, '_blank');
};

// Utility
const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num);
};

// Auto-calculate revenue from price and volume
watch([() => form.value.price_per_unit, () => form.value.expected_sales_volume], ([price, volume]) => {
  if (price && volume) {
    form.value.expected_monthly_revenue = price * volume;
  }
});

// Auto-save on step change
watch(currentStep, () => {
  if (form.value.id) {
    saveDraft();
  }
});
</script>


<style scoped>
.form-input {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors;
}
</style>
