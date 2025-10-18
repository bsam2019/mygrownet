<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">
          MyGrowNet Compliance Dashboard
        </h1>
        <p class="text-lg text-gray-600">
          Monitor regulatory compliance, sustainability metrics, and legal requirements
        </p>
      </div>

      <!-- Compliance Status Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">MLM Compliance</p>
              <p class="text-2xl font-semibold text-gray-900">
                {{ compliance_report.regulatory_status.mlm_compliance.status }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Commission Cap</p>
              <p class="text-2xl font-semibold" :class="getCommissionCapColor()">
                {{ compliance_report.commission_compliance.current_percentage }}%
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Sustainability Score</p>
              <p class="text-2xl font-semibold text-gray-900">
                {{ compliance_report.sustainability_metrics.financial_health.sustainability_score }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Next Audit</p>
              <p class="text-2xl font-semibold text-gray-900">
                {{ formatDate(compliance_report.regulatory_status.financial_regulations.next_audit) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Commission Cap Compliance -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Commission Cap Compliance</h2>
          
          <div class="mb-4">
            <div class="flex justify-between text-sm font-medium text-gray-700 mb-2">
              <span>Current Commission Percentage</span>
              <span>{{ compliance_report.commission_compliance.current_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
              <div 
                class="h-3 rounded-full transition-all duration-300"
                :class="getCommissionCapBarColor()"
                :style="{ width: getCommissionCapWidth() + '%' }"
              ></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-1">
              <span>0%</span>
              <span class="text-red-600">Cap: {{ compliance_report.commission_compliance.cap_percentage }}%</span>
            </div>
          </div>

          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Compliance Status</span>
              <span 
                class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="compliance_report.commission_compliance.is_compliant ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
              >
                {{ compliance_report.commission_compliance.is_compliant ? 'COMPLIANT' : 'EXCEEDS CAP' }}
              </span>
            </div>
            
            <div v-if="!compliance_report.commission_compliance.is_compliant" class="bg-red-50 border border-red-200 rounded-lg p-4">
              <h4 class="text-sm font-semibold text-red-800 mb-2">Action Required</h4>
              <ul class="text-sm text-red-700 space-y-1">
                <li v-for="recommendation in compliance_report.commission_compliance.recommendations.urgent" :key="recommendation">
                  • {{ recommendation }}
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Revenue Allocation -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Revenue Allocation</h2>
          
          <div class="space-y-4">
            <div 
              v-for="(percentage, category) in compliance_report.sustainability_metrics.revenue_allocation" 
              :key="category"
              class="flex items-center justify-between"
            >
              <span class="text-sm font-medium text-gray-700 capitalize">
                {{ category.replace('_', ' ') }}
              </span>
              <div class="flex items-center space-x-3">
                <div class="w-24 bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full" 
                    :style="{ width: percentage + '%' }"
                  ></div>
                </div>
                <span class="text-sm font-semibold text-gray-900 w-8">{{ percentage }}%</span>
              </div>
            </div>
          </div>

          <div class="mt-6 pt-4 border-t border-gray-200">
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-gray-500">Total Revenue</p>
                <p class="font-semibold text-gray-900">
                  K{{ compliance_report.sustainability_metrics.financial_health.total_revenue.toLocaleString() }}
                </p>
              </div>
              <div>
                <p class="text-gray-500">Total Commissions</p>
                <p class="font-semibold text-gray-900">
                  K{{ compliance_report.sustainability_metrics.financial_health.total_commissions.toLocaleString() }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Regulatory Compliance Status -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Regulatory Compliance</h2>
          
          <div class="space-y-4">
            <div>
              <h3 class="text-sm font-semibold text-gray-700 mb-2">MLM Compliance Areas</h3>
              <div class="grid grid-cols-2 gap-2">
                <div 
                  v-for="(status, area) in compliance_report.regulatory_status.mlm_compliance.areas" 
                  :key="area"
                  class="flex items-center justify-between p-2 bg-gray-50 rounded"
                >
                  <span class="text-xs text-gray-600 capitalize">{{ area.replace('_', ' ') }}</span>
                  <span 
                    class="px-2 py-1 text-xs font-semibold rounded"
                    :class="status === 'COMPLIANT' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  >
                    {{ status }}
                  </span>
                </div>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-gray-700 mb-2">Consumer Protection</h3>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600">Cooling-off Period</span>
                  <span class="font-medium">{{ compliance_report.regulatory_status.consumer_protection.cooling_off_period }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Refund Policy</span>
                  <span class="font-medium">{{ compliance_report.regulatory_status.consumer_protection.refund_policy }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Business Structure -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Business Structure</h2>
          
          <div class="space-y-4">
            <div>
              <h3 class="text-sm font-semibold text-gray-700 mb-2">Business Model</h3>
              <p class="text-sm text-gray-600">{{ compliance_report.business_structure.business_model }}</p>
              <p class="text-sm text-gray-600 mt-1">Focus: {{ compliance_report.business_structure.focus }}</p>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-gray-700 mb-2">Revenue Sources</h3>
              <ul class="space-y-1 text-sm text-gray-600">
                <li v-for="(description, source) in compliance_report.business_structure.revenue_sources" :key="source">
                  <span class="font-medium capitalize">{{ source.replace('_', ' ') }}:</span>
                  {{ description }}
                </li>
              </ul>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-gray-700 mb-2">Compliance Standards</h3>
              <ul class="space-y-1 text-sm text-gray-600">
                <li v-for="(description, standard) in compliance_report.business_structure.compliance_standards" :key="standard">
                  <span class="font-medium capitalize">{{ standard.replace('_', ' ') }}:</span>
                  {{ description }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Legal Disclaimers -->
      <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Legal Disclaimers</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div 
            v-for="(disclaimer, key) in compliance_report.legal_disclaimers" 
            :key="key"
            class="border border-gray-200 rounded-lg p-4"
          >
            <h3 class="text-lg font-semibold text-gray-800 mb-3">{{ disclaimer.title }}</h3>
            <ul class="space-y-2 text-sm text-gray-600">
              <li v-for="(item, index) in disclaimer.content" :key="index" class="flex items-start">
                <span class="text-blue-500 mr-2">•</span>
                <span>{{ item }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-8 flex justify-between items-center">
        <div class="text-sm text-gray-500">
          Report generated: {{ formatDateTime(compliance_report.report_date) }}
        </div>
        
        <div class="space-x-4">
          <button 
            @click="refreshReport"
            :disabled="loading"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="loading">Refreshing...</span>
            <span v-else>Refresh Report</span>
          </button>
          
          <button 
            @click="downloadReport"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Download Report
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

// Props
const props = defineProps({
  compliance_report: Object
})

// Reactive data
const loading = ref(false)

// Methods
const getCommissionCapColor = () => {
  const percentage = props.compliance_report.commission_compliance.current_percentage
  const cap = props.compliance_report.commission_compliance.cap_percentage
  
  if (percentage <= cap * 0.8) return 'text-green-600'
  if (percentage <= cap) return 'text-yellow-600'
  return 'text-red-600'
}

const getCommissionCapBarColor = () => {
  const percentage = props.compliance_report.commission_compliance.current_percentage
  const cap = props.compliance_report.commission_compliance.cap_percentage
  
  if (percentage <= cap * 0.8) return 'bg-green-500'
  if (percentage <= cap) return 'bg-yellow-500'
  return 'bg-red-500'
}

const getCommissionCapWidth = () => {
  const percentage = props.compliance_report.commission_compliance.current_percentage
  const cap = props.compliance_report.commission_compliance.cap_percentage
  
  // Scale to show relative to cap (cap = 100% width)
  return Math.min((percentage / cap) * 100, 100)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const formatDateTime = (dateString) => {
  return new Date(dateString).toLocaleString()
}

const refreshReport = async () => {
  loading.value = true
  
  try {
    const response = await axios.get('/compliance/generate-report')
    
    if (response.data.success) {
      // Reload the page with new data
      window.location.reload()
    } else {
      alert('Error refreshing report: ' + response.data.message)
    }
  } catch (error) {
    console.error('Error refreshing report:', error)
    alert('An error occurred while refreshing the report.')
  } finally {
    loading.value = false
  }
}

const downloadReport = () => {
  // Create downloadable JSON report
  const reportData = JSON.stringify(props.compliance_report, null, 2)
  const blob = new Blob([reportData], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  
  const link = document.createElement('a')
  link.href = url
  link.download = `compliance-report-${new Date().toISOString().split('T')[0]}.json`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  
  URL.revokeObjectURL(url)
}
</script>