<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'
import FormInput from '@/components/CMS/FormInput.vue'
import { 
  EnvelopeIcon, 
  DocumentTextIcon,
  EyeIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline'

defineOptions({
  layout: CMSLayoutNew
})

interface EmailTemplate {
  id: number
  template_type: string
  subject: string
  body_html: string
  body_text: string | null
  variables: string[]
  is_active: boolean
}

interface Props {
  templates: EmailTemplate[]
  defaultTemplates: Record<string, { subject: string; body_html: string; variables: string[] }>
}

const props = defineProps<Props>()

const selectedTemplate = ref<EmailTemplate | null>(props.templates[0] || null)
const showPreview = ref(false)

const form = useForm({
  template_type: selectedTemplate.value?.template_type || '',
  subject: selectedTemplate.value?.subject || '',
  body_html: selectedTemplate.value?.body_html || '',
  is_active: selectedTemplate.value?.is_active ?? true,
})

const selectTemplate = (template: EmailTemplate) => {
  selectedTemplate.value = template
  form.template_type = template.template_type
  form.subject = template.subject
  form.body_html = template.body_html
  form.is_active = template.is_active
}

const resetToDefault = () => {
  if (!selectedTemplate.value) return
  
  const defaultTemplate = props.defaultTemplates[selectedTemplate.value.template_type]
  if (defaultTemplate) {
    form.subject = defaultTemplate.subject
    form.body_html = defaultTemplate.body_html
  }
}

const submit = () => {
  form.put(route('cms.settings.email.templates.update', selectedTemplate.value?.id), {
    preserveScroll: true,
  })
}

const getTemplateLabel = (type: string) => {
  const labels: Record<string, string> = {
    invoice_sent: 'Invoice Sent',
    payment_received: 'Payment Received',
    payment_reminder: 'Payment Reminder',
    overdue_notice: 'Overdue Notice',
    receipt: 'Receipt',
    quotation_sent: 'Quotation Sent',
  }
  return labels[type] || type
}

const getTemplateDescription = (type: string) => {
  const descriptions: Record<string, string> = {
    invoice_sent: 'Sent when an invoice is emailed to a customer',
    payment_received: 'Sent when a payment is recorded',
    payment_reminder: 'Sent as a reminder before payment due date',
    overdue_notice: 'Sent when an invoice becomes overdue',
    receipt: 'Sent with payment receipt',
    quotation_sent: 'Sent when a quotation is emailed',
  }
  return descriptions[type] || ''
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
          <DocumentTextIcon class="h-7 w-7 text-blue-600" aria-hidden="true" />
          Email Templates
        </h1>
        <p class="mt-1 text-sm text-gray-600">
          Customize email templates sent to your customers
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Template List -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-200">
              <h2 class="text-sm font-semibold text-gray-900">Templates</h2>
            </div>
            <div class="divide-y divide-gray-200">
              <button
                v-for="template in templates"
                :key="template.id"
                @click="selectTemplate(template)"
                :class="[
                  'w-full text-left p-4 hover:bg-gray-50 transition',
                  selectedTemplate?.id === template.id ? 'bg-blue-50 border-l-4 border-blue-600' : ''
                ]"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="font-medium text-gray-900">
                      {{ getTemplateLabel(template.template_type) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                      {{ getTemplateDescription(template.template_type) }}
                    </div>
                  </div>
                  <span
                    v-if="!template.is_active"
                    class="ml-2 px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded"
                  >
                    Inactive
                  </span>
                </div>
              </button>
            </div>
          </div>
        </div>

        <!-- Template Editor -->
        <div class="lg:col-span-2">
          <form @submit.prevent="submit" class="space-y-6">
            <div class="bg-white rounded-lg border border-gray-200 p-6">
              <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">
                  {{ selectedTemplate ? getTemplateLabel(selectedTemplate.template_type) : 'Select a template' }}
                </h2>
                <div class="flex gap-2">
                  <button
                    type="button"
                    @click="showPreview = !showPreview"
                    class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                  >
                    <EyeIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                    {{ showPreview ? 'Hide' : 'Show' }} Preview
                  </button>
                  <button
                    type="button"
                    @click="resetToDefault"
                    class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                  >
                    <ArrowPathIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                    Reset to Default
                  </button>
                </div>
              </div>

              <div v-if="selectedTemplate" class="space-y-4">
                <!-- Active Toggle -->
                <div class="flex items-center gap-3">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    id="is_active"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="is_active" class="text-sm font-medium text-gray-700">
                    Template Active
                  </label>
                </div>

                <!-- Subject -->
                <FormInput
                  v-model="form.subject"
                  label="Email Subject"
                  required
                  help-text="Use variables like {{company_name}}, {{customer_name}}, {{invoice_number}}"
                />

                <!-- Body HTML -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email Body (HTML)
                  </label>
                  <textarea
                    v-model="form.body_html"
                    rows="15"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                    required
                  ></textarea>
                  <p class="mt-1 text-xs text-gray-500">
                    HTML email template. Use Blade syntax for variables.
                  </p>
                </div>

                <!-- Available Variables -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <h3 class="text-sm font-semibold text-gray-900 mb-2">Available Variables:</h3>
                  <div class="flex flex-wrap gap-2">
                    <code
                      v-for="variable in selectedTemplate.variables"
                      :key="variable"
                      class="px-2 py-1 text-xs bg-white border border-blue-300 rounded text-blue-700"
                      v-text="`{{ ${variable} }}`"
                    >
                    </code>
                  </div>
                </div>

                <!-- Preview -->
                <div v-if="showPreview" class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                  <h3 class="text-sm font-semibold text-gray-900 mb-2">Preview:</h3>
                  <div class="bg-white border border-gray-200 rounded p-4" v-html="form.body_html"></div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                  >
                    {{ form.processing ? 'Saving...' : 'Save Template' }}
                  </button>
                </div>
              </div>

              <div v-else class="text-center py-12">
                <EnvelopeIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                <p class="mt-2 text-sm text-gray-500">Select a template to edit</p>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
