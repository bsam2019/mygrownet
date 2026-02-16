<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import FormInput from '@/components/CMS/FormInput.vue'
import FormSelect from '@/components/CMS/FormSelect.vue'
import { CheckCircleIcon, XCircleIcon, EnvelopeIcon, ChartBarIcon } from '@heroicons/vue/24/outline'

defineOptions({
  layout: CMSLayout
})

interface Props {
  company: {
    id: number
    name: string
    email: string
    email_provider: 'platform' | 'custom'
    email_from_address: string | null
    email_from_name: string | null
    email_reply_to: string | null
    smtp_host: string | null
    smtp_port: number | null
    smtp_username: string | null
    smtp_encryption: 'tls' | 'ssl' | 'none'
  }
  stats: {
    total: number
    sent: number
    failed: number
    queued: number
    success_rate: number
  }
}

const props = defineProps<Props>()

const form = useForm({
  email_provider: props.company.email_provider || 'platform',
  email_from_address: props.company.email_from_address || '',
  email_from_name: props.company.email_from_name || '',
  email_reply_to: props.company.email_reply_to || '',
  smtp_host: props.company.smtp_host || '',
  smtp_port: props.company.smtp_port || 587,
  smtp_username: props.company.smtp_username || '',
  smtp_password: '',
  smtp_encryption: props.company.smtp_encryption || 'tls',
})

const testing = ref(false)
const testResult = ref<{ success: boolean; message: string } | null>(null)

const testConnection = async () => {
  if (form.email_provider !== 'custom') return

  testing.value = true
  testResult.value = null

  try {
    const response = await fetch(route('cms.settings.email.test-connection'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        smtp_host: form.smtp_host,
        smtp_port: form.smtp_port,
        smtp_username: form.smtp_username,
        smtp_password: form.smtp_password,
        smtp_encryption: form.smtp_encryption,
      }),
    })

    testResult.value = await response.json()
  } catch (error) {
    testResult.value = {
      success: false,
      message: 'Connection test failed: ' + (error as Error).message,
    }
  } finally {
    testing.value = false
  }
}

const submit = () => {
  form.post(route('cms.settings.email.update'), {
    preserveScroll: true,
    onSuccess: () => {
      if (form.email_provider === 'platform') {
        form.smtp_password = ''
      }
    },
  })
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
          <EnvelopeIcon class="h-7 w-7 text-blue-600" aria-hidden="true" />
          Email Configuration
        </h1>
        <p class="mt-1 text-sm text-gray-600">
          Configure how your company sends emails to customers
        </p>
      </div>

      <!-- Email Statistics -->
      <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <ChartBarIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
          Email Statistics
        </h2>
        
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900">{{ stats.total }}</div>
            <div class="text-sm text-gray-600">Total Sent</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ stats.sent }}</div>
            <div class="text-sm text-gray-600">Delivered</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-red-600">{{ stats.failed }}</div>
            <div class="text-sm text-gray-600">Failed</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-amber-600">{{ stats.queued }}</div>
            <div class="text-sm text-gray-600">Queued</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-blue-600">{{ stats.success_rate }}%</div>
            <div class="text-sm text-gray-600">Success Rate</div>
          </div>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Email Provider Selection -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Email Provider</h2>
          
          <div class="space-y-4">
            <label 
              class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition"
              :class="form.email_provider === 'platform' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
            >
              <input 
                type="radio" 
                v-model="form.email_provider" 
                value="platform" 
                class="mt-1 text-blue-600 focus:ring-blue-500" 
              />
              <div class="ml-3 flex-1">
                <div class="font-medium text-gray-900 flex items-center gap-2">
                  Platform Email (Recommended)
                  <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">FREE</span>
                </div>
                <div class="text-sm text-gray-600 mt-1">
                  Use MyGrowNet's email service. No setup required, works immediately.
                  Emails sent from: <span class="font-mono text-xs">noreply@mygrownet.com</span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                  ✓ Zero configuration • ✓ Instant setup • ✓ Reliable delivery
                </div>
              </div>
            </label>

            <label 
              class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition"
              :class="form.email_provider === 'custom' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
            >
              <input 
                type="radio" 
                v-model="form.email_provider" 
                value="custom" 
                class="mt-1 text-blue-600 focus:ring-blue-500" 
              />
              <div class="ml-3 flex-1">
                <div class="font-medium text-gray-900 flex items-center gap-2">
                  Custom SMTP Server (Advanced)
                  <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="text-sm text-gray-600 mt-1">
                  Use your own email server. Requires SMTP credentials.
                  Emails sent from your domain (e.g., <span class="font-mono text-xs">invoices@yourcompany.com</span>)
                </div>
                <div class="mt-2 text-xs text-gray-500">
                  ✓ Professional branding • ✓ Full control • ✓ Your domain
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- Custom SMTP Configuration -->
        <div v-if="form.email_provider === 'custom'" class="bg-white rounded-lg border border-gray-200 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">SMTP Configuration</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormInput
              v-model="form.email_from_address"
              label="From Email Address"
              type="email"
              placeholder="invoices@yourcompany.com"
              required
              help-text="Email address that appears in the 'From' field"
            />

            <FormInput
              v-model="form.email_from_name"
              label="From Name"
              placeholder="Your Company Name"
              required
              help-text="Name that appears in the 'From' field"
            />

            <FormInput
              v-model="form.email_reply_to"
              label="Reply-To Email"
              type="email"
              placeholder="support@yourcompany.com"
              help-text="Where replies will be sent (optional)"
            />

            <FormInput
              v-model="form.smtp_host"
              label="SMTP Host"
              placeholder="smtp.gmail.com"
              required
              help-text="Your email server address"
            />

            <FormInput
              v-model="form.smtp_port"
              label="SMTP Port"
              type="number"
              placeholder="587"
              required
              help-text="Usually 587 for TLS or 465 for SSL"
            />

            <FormSelect
              v-model="form.smtp_encryption"
              label="Encryption"
              :options="[
                { value: 'tls', label: 'TLS (Recommended)' },
                { value: 'ssl', label: 'SSL' },
                { value: 'none', label: 'None (Not Recommended)' }
              ]"
              required
              help-text="Security protocol for connection"
            />

            <FormInput
              v-model="form.smtp_username"
              label="SMTP Username"
              placeholder="your-email@gmail.com"
              required
              help-text="Usually your email address"
            />

            <FormInput
              v-model="form.smtp_password"
              label="SMTP Password"
              type="password"
              placeholder="Enter password"
              help-text="Leave blank to keep existing password"
            />
          </div>

          <!-- Test Connection -->
          <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex items-center gap-3">
              <button
                type="button"
                @click="testConnection"
                :disabled="testing || !form.smtp_host || !form.smtp_username || !form.smtp_password"
                class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 disabled:opacity-50 disabled:cursor-not-allowed transition"
              >
                {{ testing ? 'Testing Connection...' : 'Test Connection' }}
              </button>

              <div v-if="testResult" class="flex items-center gap-2">
                <CheckCircleIcon v-if="testResult.success" class="h-5 w-5 text-green-600" aria-hidden="true" />
                <XCircleIcon v-else class="h-5 w-5 text-red-600" aria-hidden="true" />
                <span :class="testResult.success ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                  {{ testResult.message }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Reply-To for Platform Email -->
        <div v-if="form.email_provider === 'platform'" class="bg-white rounded-lg border border-gray-200 p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Reply Settings</h2>
          
          <FormInput
            v-model="form.email_reply_to"
            label="Reply-To Email (Optional)"
            type="email"
            :placeholder="company.email"
            help-text="Where customer replies will be sent. Defaults to your company email."
          />
        </div>

        <!-- Save Button -->
        <div class="flex justify-end gap-3">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
          >
            {{ form.processing ? 'Saving...' : 'Save Configuration' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
