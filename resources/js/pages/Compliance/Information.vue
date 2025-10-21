<template>
  <div class="min-h-screen bg-gray-50">
    <Navigation />
    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
          MyGrowNet Compliance & Legal Information
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Transparency, compliance, and legal protection are fundamental to our business operations.
          Learn about our commitment to regulatory compliance and member protection.
        </p>
      </div>

      <!-- Business Structure -->
      <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Business Structure & Model</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Our Business Model</h3>
            <div class="space-y-3">
              <div class="flex items-start">
                <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                <div>
                  <p class="font-medium text-gray-900">{{ business_structure.business_model }}</p>
                  <p class="text-gray-600 text-sm">{{ business_structure.focus }}</p>
                </div>
              </div>
            </div>
          </div>

          <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue Sources</h3>
            <div class="space-y-3">
              <div 
                v-for="(description, source) in business_structure.revenue_sources" 
                :key="source"
                class="flex items-start"
              >
                <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                <div>
                  <p class="font-medium text-gray-900 capitalize">{{ source.replace('_', ' ') }}</p>
                  <p class="text-gray-600 text-sm">{{ description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Compliance Standards</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div 
              v-for="(description, standard) in business_structure.compliance_standards" 
              :key="standard"
              class="bg-blue-50 border border-blue-200 rounded-lg p-4"
            >
              <h4 class="font-semibold text-blue-900 capitalize mb-2">{{ standard.replace('_', ' ') }}</h4>
              <p class="text-blue-800 text-sm">{{ description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Legal Disclaimers -->
      <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Legal Disclaimers & Important Information</h2>
        
        <div class="space-y-8">
          <div 
            v-for="(disclaimer, key) in legal_disclaimers" 
            :key="key"
            class="border-l-4 border-blue-500 pl-6"
          >
            <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ disclaimer.title }}</h3>
            <div class="space-y-3">
              <div 
                v-for="(item, index) in disclaimer.content" 
                :key="index"
                class="flex items-start"
              >
                <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                <p class="text-gray-700">{{ item }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Regulatory Compliance -->
      <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Regulatory Compliance</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- MLM Compliance -->
          <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-green-900">MLM Compliance</h3>
            </div>
            <p class="text-green-800 text-sm mb-4">
              Status: <span class="font-semibold">{{ regulatory_compliance.mlm_compliance.status }}</span>
            </p>
            <div class="space-y-2">
              <div 
                v-for="(status, area) in regulatory_compliance.mlm_compliance.areas" 
                :key="area"
                class="flex justify-between items-center"
              >
                <span class="text-sm text-green-700 capitalize">{{ area.replace('_', ' ') }}</span>
                <span 
                  class="px-2 py-1 text-xs font-semibold rounded"
                  :class="status === 'COMPLIANT' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'"
                >
                  {{ status }}
                </span>
              </div>
            </div>
          </div>

          <!-- Consumer Protection -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-blue-900">Consumer Protection</h3>
            </div>
            <div class="space-y-3 text-sm">
              <div>
                <span class="font-medium text-blue-800">Cooling-off Period:</span>
                <span class="text-blue-700 ml-2">{{ regulatory_compliance.consumer_protection.cooling_off_period }}</span>
              </div>
              <div>
                <span class="font-medium text-blue-800">Refund Policy:</span>
                <span class="text-blue-700 ml-2">{{ regulatory_compliance.consumer_protection.refund_policy }}</span>
              </div>
              <div>
                <span class="font-medium text-blue-800">Complaint Resolution:</span>
                <span class="text-blue-700 ml-2">{{ regulatory_compliance.consumer_protection.complaint_resolution }}</span>
              </div>
            </div>
          </div>

          <!-- Financial Regulations -->
          <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zM14 6a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h8zM6 8a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2a2 2 0 012-2h2z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-purple-900">Financial Regulations</h3>
            </div>
            <div class="space-y-3 text-sm">
              <div>
                <span class="font-medium text-purple-800">Status:</span>
                <span class="text-purple-700 ml-2">{{ regulatory_compliance.financial_regulations.status }}</span>
              </div>
              <div>
                <span class="font-medium text-purple-800">Audit Frequency:</span>
                <span class="text-purple-700 ml-2">{{ regulatory_compliance.financial_regulations.audit_frequency }}</span>
              </div>
              <div>
                <span class="font-medium text-purple-800">Financial Reporting:</span>
                <span class="text-purple-700 ml-2">{{ regulatory_compliance.financial_regulations.financial_reporting }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Information -->
      <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Compliance Contact Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">General Compliance Inquiries</h3>
            <div class="space-y-3">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                  <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                </svg>
                <span class="text-gray-700">compliance@mygrownet.com</span>
              </div>
              <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                </svg>
                <div class="flex flex-col">
                  <span class="text-gray-700">+260 977 563 730</span>
                  <span class="text-gray-700">+260 961 144 812</span>
                </div>
              </div>
            </div>
          </div>

          <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Legal Department</h3>
            <div class="space-y-3">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                  <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                </svg>
                <span class="text-gray-700">legal@mygrownet.com</span>
              </div>
              <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                </svg>
                <span class="text-gray-700">Lusaka, Zambia</span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-semibold text-blue-900 mb-2">Important Notice</h4>
            <p class="text-blue-800 text-sm">
              This information is provided for transparency and educational purposes. 
              For specific legal advice or compliance questions, please consult with qualified legal professionals. 
              MyGrowNet is committed to maintaining the highest standards of legal and regulatory compliance.
            </p>
          </div>
        </div>
      </div>
      </div>
    </div>
    <Footer />
  </div>
</template>

<script setup>
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';

// Props
const props = defineProps({
  business_structure: Object,
  legal_disclaimers: Object,
  regulatory_compliance: Object
})
</script>