<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
    <div class="flex items-center justify-center min-h-screen px-2 sm:px-4 py-4 sm:py-8">
      <div class="fixed inset-0 bg-black opacity-50"></div>
      
      <div class="relative bg-white rounded-lg shadow-2xl max-w-5xl w-full max-h-[95vh] sm:max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 sm:px-8 py-4 sm:py-6 flex items-center justify-between">
          <div class="flex-1 min-w-0 pr-4">
            <h2 class="text-xl sm:text-3xl font-bold text-white truncate">{{ plan.business_name }}</h2>
            <p class="text-blue-100 mt-1 text-xs sm:text-sm truncate">{{ plan.industry }} • {{ plan.city }}, {{ plan.country }}</p>
          </div>
          <button @click="$emit('close')" class="text-white hover:text-blue-100 transition-colors flex-shrink-0">
            <XMarkIcon class="w-6 h-6 sm:w-8 sm:h-8" />
          </button>
        </div>

        <!-- Content - Scrollable -->
        <div class="flex-1 overflow-y-auto px-4 sm:px-8 py-4 sm:py-6 bg-gray-50">
          <div class="max-w-4xl mx-auto space-y-4 sm:space-y-8">
          <!-- Executive Summary -->
          <section class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-600">
            <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
              <span class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">1</span>
              Executive Summary
            </h3>
            
            <div class="grid grid-cols-2 gap-6 mb-6">
              <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Industry</p>
                <p class="text-lg font-semibold text-gray-900">{{ plan.industry }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Location</p>
                <p class="text-lg font-semibold text-gray-900">{{ plan.city }}, {{ plan.country }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Legal Structure</p>
                <p class="text-lg font-semibold text-gray-900 capitalize">{{ plan.legal_structure?.replace('_', ' ') }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Province</p>
                <p class="text-lg font-semibold text-gray-900">{{ plan.province || 'N/A' }}</p>
              </div>
            </div>

            <div v-if="plan.mission_statement" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Mission Statement</h4>
              <p class="text-gray-700 leading-relaxed bg-blue-50 p-4 rounded-lg border-l-2 border-blue-400">{{ plan.mission_statement }}</p>
            </div>

            <div v-if="plan.vision_statement" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Vision Statement</h4>
              <p class="text-gray-700 leading-relaxed bg-purple-50 p-4 rounded-lg border-l-2 border-purple-400">{{ plan.vision_statement }}</p>
            </div>

            <div v-if="plan.background">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Background</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.background }}</p>
            </div>
          </section>

          <!-- Problem & Solution -->
          <section v-if="plan.problem_statement || plan.solution_description" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
            <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
              <span class="bg-red-100 text-red-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">2</span>
              Problem & Solution
            </h3>
            
            <div v-if="plan.problem_statement" class="mb-6">
              <h4 class="text-sm font-bold text-red-600 uppercase mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                The Problem
              </h4>
              <p class="text-gray-700 leading-relaxed bg-red-50 p-4 rounded-lg">{{ plan.problem_statement }}</p>
            </div>

            <div v-if="plan.customer_pain_points" class="mb-6">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Customer Pain Points</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.customer_pain_points }}</p>
            </div>

            <div v-if="plan.solution_description" class="mb-4">
              <h4 class="text-sm font-bold text-green-600 uppercase mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Our Solution
              </h4>
              <p class="text-gray-700 leading-relaxed bg-green-50 p-4 rounded-lg">{{ plan.solution_description }}</p>
            </div>

            <div v-if="plan.competitive_advantage">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Competitive Advantage</h4>
              <p class="text-gray-700 leading-relaxed bg-yellow-50 p-4 rounded-lg border-l-2 border-yellow-400">{{ plan.competitive_advantage }}</p>
            </div>
          </section>

          <!-- Products/Services -->
          <section v-if="plan.product_description" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
            <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
              <span class="bg-purple-100 text-purple-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">3</span>
              Products & Services
            </h3>
            
            <div class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Description</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.product_description }}</p>
            </div>

            <div v-if="plan.product_features" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Key Features</h4>
              <p class="text-gray-700 leading-relaxed bg-purple-50 p-4 rounded-lg">{{ plan.product_features }}</p>
            </div>

            <div v-if="plan.unique_selling_points" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Unique Selling Points</h4>
              <p class="text-gray-700 leading-relaxed bg-yellow-50 p-4 rounded-lg">{{ plan.unique_selling_points }}</p>
            </div>

            <div v-if="plan.pricing_strategy" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Pricing Strategy</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.pricing_strategy }}</p>
            </div>

            <div v-if="plan.production_process" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Production Process</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.production_process }}</p>
            </div>

            <div v-if="plan.resource_requirements">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Resource Requirements</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.resource_requirements }}</p>
            </div>
          </section>

          <!-- Market Research -->
          <section v-if="plan.target_market" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
              <span class="bg-green-100 text-green-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">4</span>
              Market Analysis
            </h3>
            
            <div v-if="plan.target_market" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Target Market</h4>
              <p class="text-gray-700 leading-relaxed bg-green-50 p-4 rounded-lg">{{ plan.target_market }}</p>
            </div>

            <div v-if="plan.customer_demographics" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Customer Demographics</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.customer_demographics }}</p>
            </div>

            <div v-if="plan.market_size" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Market Size & Opportunity</h4>
              <p class="text-gray-700 leading-relaxed bg-blue-50 p-4 rounded-lg">{{ plan.market_size }}</p>
            </div>

            <div v-if="plan.competitors" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Key Competitors</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.competitors }}</p>
            </div>

            <div v-if="plan.competitive_analysis">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Competitive Analysis</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.competitive_analysis }}</p>
            </div>
          </section>

          <!-- Marketing & Sales Strategy -->
          <section v-if="plan.marketing_channels" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
            <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
              <span class="bg-orange-100 text-orange-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">5</span>
              Marketing & Sales Strategy
            </h3>
            
            <div v-if="plan.marketing_channels" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Marketing Channels</h4>
              <p class="text-gray-700 leading-relaxed bg-orange-50 p-4 rounded-lg">{{ plan.marketing_channels }}</p>
            </div>

            <div v-if="plan.branding_approach" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Branding Approach</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.branding_approach }}</p>
            </div>

            <div v-if="plan.sales_channels" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Sales Channels</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.sales_channels }}</p>
            </div>

            <div v-if="plan.customer_retention">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Customer Retention Strategy</h4>
              <p class="text-gray-700 leading-relaxed bg-blue-50 p-4 rounded-lg">{{ plan.customer_retention }}</p>
            </div>
          </section>

          <!-- Operations -->
          <section v-if="plan.daily_operations" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
            <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
              <span class="bg-indigo-100 text-indigo-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">6</span>
              Operations Plan
            </h3>
            
            <div v-if="plan.daily_operations" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Daily Operations</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.daily_operations }}</p>
            </div>

            <div v-if="plan.staff_roles" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Staff & Roles</h4>
              <p class="text-gray-700 leading-relaxed bg-indigo-50 p-4 rounded-lg">{{ plan.staff_roles }}</p>
            </div>

            <div v-if="plan.equipment_tools" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Equipment & Tools</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.equipment_tools }}</p>
            </div>

            <div v-if="plan.supplier_list" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Key Suppliers</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.supplier_list }}</p>
            </div>

            <div v-if="plan.operational_workflow">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Operational Workflow</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.operational_workflow }}</p>
            </div>
          </section>

          <!-- Financial Plan -->
          <section v-if="plan.startup_costs || plan.expected_monthly_revenue" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-emerald-500">
            <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
              <span class="bg-emerald-100 text-emerald-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">7</span>
              Financial Plan
            </h3>
            
            <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-lg p-6 mb-4 border border-emerald-200">
              <h4 class="text-sm font-bold text-emerald-700 uppercase mb-4">Financial Summary</h4>
              <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-emerald-200">
                  <span class="text-gray-700 font-medium">Startup Costs</span>
                  <span class="text-xl font-bold text-gray-900">K{{ formatNumber(plan.startup_costs) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-emerald-200">
                  <span class="text-gray-700 font-medium">Monthly Operating Costs</span>
                  <span class="text-xl font-bold text-gray-900">K{{ formatNumber(plan.monthly_operating_costs) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-emerald-200">
                  <span class="text-gray-700 font-medium">Expected Monthly Revenue</span>
                  <span class="text-xl font-bold text-emerald-600">K{{ formatNumber(plan.expected_monthly_revenue) }}</span>
                </div>
                <div class="flex justify-between items-center py-3 bg-white rounded-lg px-4 mt-2">
                  <span class="text-gray-900 font-bold text-lg">Monthly Profit</span>
                  <span class="text-2xl font-bold" :class="monthlyProfit >= 0 ? 'text-green-600' : 'text-red-600'">
                    K{{ formatNumber(monthlyProfit) }}
                  </span>
                </div>
                <div v-if="profitMargin" class="flex justify-between items-center py-2">
                  <span class="text-gray-700 font-medium">Profit Margin</span>
                  <span class="text-lg font-bold text-emerald-600">{{ profitMargin }}%</span>
                </div>
                <div v-if="breakEvenMonths" class="flex justify-between items-center py-2">
                  <span class="text-gray-700 font-medium">Break-Even Point</span>
                  <span class="text-lg font-bold text-blue-600">{{ breakEvenMonths }} months</span>
                </div>
              </div>
            </div>

            <div v-if="plan.price_per_unit || plan.expected_sales_volume" class="grid grid-cols-2 gap-4 mb-4">
              <div v-if="plan.price_per_unit" class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Price Per Unit</p>
                <p class="text-2xl font-bold text-gray-900">K{{ formatNumber(plan.price_per_unit) }}</p>
              </div>
              <div v-if="plan.expected_sales_volume" class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Expected Sales Volume</p>
                <p class="text-2xl font-bold text-gray-900">{{ formatNumber(plan.expected_sales_volume) }}</p>
              </div>
            </div>

            <div v-if="plan.staff_salaries || plan.inventory_costs || plan.utilities_costs" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-3">Cost Breakdown</h4>
              <div class="space-y-2">
                <div v-if="plan.staff_salaries" class="flex justify-between py-2 border-b border-gray-200">
                  <span class="text-gray-600">Staff Salaries</span>
                  <span class="font-semibold">K{{ formatNumber(plan.staff_salaries) }}</span>
                </div>
                <div v-if="plan.inventory_costs" class="flex justify-between py-2 border-b border-gray-200">
                  <span class="text-gray-600">Inventory Costs</span>
                  <span class="font-semibold">K{{ formatNumber(plan.inventory_costs) }}</span>
                </div>
                <div v-if="plan.utilities_costs" class="flex justify-between py-2 border-b border-gray-200">
                  <span class="text-gray-600">Utilities</span>
                  <span class="font-semibold">K{{ formatNumber(plan.utilities_costs) }}</span>
                </div>
              </div>
            </div>
          </section>

          <!-- Risks -->
          <section v-if="plan.key_risks" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
            <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
              <span class="bg-yellow-100 text-yellow-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">8</span>
              Risk Analysis
            </h3>
            
            <div v-if="plan.key_risks" class="mb-4">
              <h4 class="text-sm font-bold text-yellow-700 uppercase mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                Key Risks
              </h4>
              <p class="text-gray-700 leading-relaxed bg-yellow-50 p-4 rounded-lg border border-yellow-200">{{ plan.key_risks }}</p>
            </div>

            <div v-if="plan.mitigation_strategies">
              <h4 class="text-sm font-bold text-green-700 uppercase mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Mitigation Strategies
              </h4>
              <p class="text-gray-700 leading-relaxed bg-green-50 p-4 rounded-lg border border-green-200">{{ plan.mitigation_strategies }}</p>
            </div>
          </section>

          <!-- Implementation -->
          <section v-if="plan.timeline" class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-pink-500">
            <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
              <span class="bg-pink-100 text-pink-600 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">9</span>
              Implementation Roadmap
            </h3>
            
            <div v-if="plan.timeline" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Timeline</h4>
              <p class="text-gray-700 leading-relaxed bg-pink-50 p-4 rounded-lg">{{ plan.timeline }}</p>
            </div>

            <div v-if="plan.milestones" class="mb-4">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Key Milestones</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.milestones }}</p>
            </div>

            <div v-if="plan.responsibilities">
              <h4 class="text-sm font-bold text-gray-700 mb-2">Responsibilities</h4>
              <p class="text-gray-700 leading-relaxed">{{ plan.responsibilities }}</p>
            </div>
          </section>

          <!-- Footer Note -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
            <p class="text-sm text-blue-800">
              <strong>Generated by MyGrowNet Business Plan Generator</strong><br>
              Created on {{ new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}
            </p>
          </div>
        </div>
      </div>

        <!-- Footer with Download Buttons -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-t-2 border-gray-300 px-4 sm:px-8 py-4 sm:py-5">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="hidden sm:block">
              <p class="text-sm font-semibold text-gray-700">Ready to download?</p>
              <p class="text-xs text-gray-500 mt-1">Choose your preferred format below</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
              <button
                @click="$emit('download', 'template')"
                class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow-md flex items-center gap-2 font-semibold"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                </svg>
                HTML
              </button>
              <button
                @click="$emit('download', 'pdf')"
                :disabled="!isPremium"
                class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-md flex items-center gap-2 font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                :title="!isPremium ? 'Premium feature' : ''"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                </svg>
                PDF
                <span v-if="!isPremium" class="text-xs bg-yellow-400 text-yellow-900 px-2 py-0.5 rounded-full">Premium</span>
              </button>
              <button
                @click="$emit('download', 'word')"
                :disabled="!isPremium"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md flex items-center gap-2 font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                :title="!isPremium ? 'Premium feature' : ''"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                </svg>
                Word
                <span v-if="!isPremium" class="text-xs bg-yellow-400 text-yellow-900 px-2 py-0.5 rounded-full">Premium</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
  show: boolean;
  plan: any;
  isPremium: boolean;
}>();

defineEmits<{
  close: [];
  download: [type: string];
}>();

const monthlyProfit = computed(() => {
  return (props.plan.expected_monthly_revenue || 0) - (props.plan.monthly_operating_costs || 0);
});

const profitMargin = computed(() => {
  const revenue = props.plan.expected_monthly_revenue || 0;
  if (revenue === 0) return 0;
  return ((monthlyProfit.value / revenue) * 100).toFixed(1);
});

const breakEvenMonths = computed(() => {
  const profit = monthlyProfit.value;
  const startup = props.plan.startup_costs || 0;
  if (profit <= 0 || startup === 0) return null;
  return Math.ceil(startup / profit);
});

const formatNumber = (value: any) => {
  return value ? Number(value).toLocaleString() : '0';
};
</script>
