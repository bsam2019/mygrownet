<template>
  <InvestorLayout 
    :investor="investor" 
    page-title="Legal Documents" 
    :active-page="activePage || 'legal-documents'"
    :unread-messages="unreadMessages"
    :unread-announcements="unreadAnnouncements"
  >
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Legal Documents</h1>
          <p class="mt-1 text-sm text-gray-500">
            Access your share certificates and legal documentation
          </p>
        </div>
      </div>

      <!-- Share Certificates -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Share Certificates</h2>
        </div>
        <div class="p-6">
          <div v-if="certificates.length === 0" class="text-center py-12">
            <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No certificates yet</h3>
            <p class="mt-1 text-sm text-gray-500">
              Certificates will be generated after your investment is confirmed
            </p>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="certificate in certificates"
              :key="certificate.id"
              class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors"
            >
              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                  <DocumentTextIcon class="h-10 w-10 text-blue-600" aria-hidden="true" />
                </div>
                <div>
                  <h3 class="text-sm font-medium text-gray-900">
                    Certificate #{{ certificate.certificate_number }}
                  </h3>
                  <p class="text-sm text-gray-500">
                    {{ certificate.shares }} shares • Issued {{ formatDate(certificate.issue_date) }}
                  </p>
                </div>
              </div>

              <div class="flex items-center space-x-2">
                <button
                  v-if="certificate.is_generated"
                  @click="downloadCertificate(certificate.id)"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  aria-label="Download certificate"
                >
                  <ArrowDownTrayIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                  Download
                </button>
                <span
                  v-else
                  class="inline-flex items-center px-3 py-2 text-sm font-medium text-amber-700 bg-amber-50 rounded-md"
                >
                  <ClockIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                  Generating...
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Other Legal Documents -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Other Documents</h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <DocumentCard
              title="Shareholder Agreement"
              description="Terms and conditions of your investment"
              icon="DocumentTextIcon"
              status="available"
            />
            <DocumentCard
              title="Articles of Association"
              description="Company governance documents"
              icon="BuildingOfficeIcon"
              status="available"
            />
            <DocumentCard
              title="Tax Statements"
              description="Annual tax documentation"
              icon="DocumentChartBarIcon"
              status="coming-soon"
            />
            <DocumentCard
              title="Compliance Documents"
              description="Regulatory compliance records"
              icon="ShieldCheckIcon"
              status="coming-soon"
            />
          </div>
        </div>
      </div>
    </div>
  </InvestorLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import {
  DocumentTextIcon,
  ArrowDownTrayIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline'
import InvestorLayout from '@/Layouts/InvestorLayout.vue'
import DocumentCard from '@/components/Investor/DocumentCard.vue'

interface Certificate {
  id: number
  certificate_number: string
  shares: number
  issue_date: string
  is_generated: boolean
  pdf_path: string | null
}

interface Investor {
  id: number
  name: string
  email: string
}

interface Props {
  investor: Investor
  certificates: Certificate[]
  activePage?: string
  unreadMessages?: number
  unreadAnnouncements?: number
}

const props = defineProps<Props>()

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const downloadCertificate = (certificateId: number) => {
  window.location.href = `/investor/legal-documents/certificates/${certificateId}/download`
}
</script>
