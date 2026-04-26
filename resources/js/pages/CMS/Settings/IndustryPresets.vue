<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  PrinterIcon,
  WrenchScrewdriverIcon,
  ShoppingBagIcon,
  BriefcaseIcon,
  WrenchIcon,
  BuildingOfficeIcon,
  CheckCircleIcon,
  ScissorsIcon,
} from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface Preset {
  id: number
  code: string
  name: string
  description: string
  icon: string
  roles: any[]
  expense_categories: any[]
  job_types: string[]
  inventory_categories: string[]
  asset_types: string[]
  default_settings: any
}

interface Props {
  presets: Preset[]
  currentIndustry: string | null
}

const props = defineProps<Props>()

const selectedPreset = ref<string | null>(null)
const previewData = ref<any>(null)
const isLoading = ref(false)
const showPreview = ref(false)

const iconMap: Record<string, any> = {
  'printer': PrinterIcon,
  'building': BuildingOfficeIcon,
  'shopping-bag': ShoppingBagIcon,
  'briefcase': BriefcaseIcon,
  'wrench': WrenchIcon,
  'utensils': BriefcaseIcon,
  'building-office': BuildingOfficeIcon,
  'scissors': ScissorsIcon,
}

const getIcon = (iconName: string) => {
  return iconMap[iconName] || BuildingOfficeIcon
}

const isCurrentPreset = (code: string) => {
  return props.currentIndustry === code
}

const viewPreset = async (code: string) => {
  selectedPreset.value = code
  isLoading.value = true
  showPreview.value = true

  try {
    const response = await fetch(route('cms.settings.industry-presets.show', code))
    previewData.value = await response.json()
  } catch (error) {
    console.error('Failed to load preset details:', error)
  } finally {
    isLoading.value = false
  }
}

const applyPreset = async (code: string) => {
  // Build warning message based on existing data
  let warningMessage = 'Are you sure you want to apply this industry preset?\n\n'
  
  if (previewData.value?.existing_data) {
    const existing = previewData.value.existing_data
    
    if (existing.has_industry) {
      warningMessage += `⚠️ Your company already has "${existing.current_industry}" set as the industry.\n\n`
    }
    
    warningMessage += 'What will happen:\n'
    
    if (existing.roles_count > 0) {
      warningMessage += `• Roles: ${existing.roles_count} existing roles will be kept. Only NEW roles from the preset will be added.\n`
    } else {
      warningMessage += `• Roles: ${previewData.value.roles?.length || 0} new roles will be created.\n`
    }
    
    if (existing.expense_categories_count > 0) {
      warningMessage += `• Expense Categories: ${existing.expense_categories_count} existing categories will be kept. Only NEW categories will be added.\n`
    } else {
      warningMessage += `• Expense Categories: ${previewData.value.expense_categories?.length || 0} new categories will be created.\n`
    }
    
    if (existing.pricing_rules_exist) {
      warningMessage += `• Pricing Rules: Your existing pricing rules will NOT be changed.\n`
    } else if (previewData.value.has_fabrication_module) {
      warningMessage += `• Pricing Rules: Default fabrication pricing rules will be created.\n`
    }
    
    if (existing.has_custom_job_types) {
      warningMessage += `• Job Types: Your custom job types will NOT be changed.\n`
    } else if (previewData.value.job_types?.length) {
      warningMessage += `• Job Types: ${previewData.value.job_types.length} preset job types will be added.\n`
    }
    
    if (existing.has_custom_inventory_categories) {
      warningMessage += `• Inventory Categories: Your custom categories will NOT be changed.\n`
    } else if (previewData.value.inventory_categories?.length) {
      warningMessage += `• Inventory Categories: ${previewData.value.inventory_categories.length} preset categories will be added.\n`
    }
    
    warningMessage += '\n✅ Your existing data will NOT be deleted or overwritten.'
  } else {
    warningMessage += 'This will add predefined roles, expense categories, and settings to your company.'
  }

  if (!confirm(warningMessage)) {
    return
  }

  isLoading.value = true

  try {
    const response = await fetch(route('cms.settings.industry-presets.apply'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({ preset_code: code }),
    })

    const data = await response.json()

    if (data.success) {
      alert('Industry preset applied successfully!')
      router.reload()
    } else {
      alert('Failed to apply preset: ' + (data.error || 'Unknown error'))
    }
  } catch (error) {
    console.error('Failed to apply preset:', error)
    alert('Failed to apply preset. Please try again.')
  } finally {
    isLoading.value = false
  }
}

const closePreview = () => {
  showPreview.value = false
  selectedPreset.value = null
  previewData.value = null
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Industry Presets</h1>
        <p class="mt-2 text-sm text-gray-600">
          Choose an industry preset to automatically configure your CMS with relevant roles, expense categories, and settings.
        </p>
      </div>

      <!-- Current Industry -->
      <div v-if="currentIndustry" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center gap-2">
          <CheckCircleIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
          <p class="text-sm text-blue-800">
            <span class="font-medium">Current Industry:</span>
            {{ presets.find(p => p.code === currentIndustry)?.name || currentIndustry }}
          </p>
        </div>
      </div>

      <!-- Presets Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="preset in presets"
          :key="preset.id"
          class="relative bg-white border rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden"
          :class="{
            'border-blue-500 ring-2 ring-blue-500': isCurrentPreset(preset.code),
            'border-gray-200': !isCurrentPreset(preset.code)
          }"
        >
          <!-- Current Badge -->
          <div
            v-if="isCurrentPreset(preset.code)"
            class="absolute top-3 right-3 px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded-full"
          >
            Current
          </div>

          <div class="p-6">
            <!-- Icon & Title -->
            <div class="flex items-start gap-4 mb-4">
              <div class="flex-shrink-0 p-3 bg-blue-50 rounded-lg">
                <component :is="getIcon(preset.icon)" class="h-8 w-8 text-blue-600" aria-hidden="true" />
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="text-lg font-semibold text-gray-900">{{ preset.name }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ preset.description }}</p>
                <!-- Fabrication module badge -->
                <span v-if="preset.default_settings?.fabrication_module" class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-teal-100 text-teal-700">
                  ✂ Includes Fabrication Module
                </span>
              </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-3 mb-4">
              <div class="text-center p-2 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">{{ preset.roles?.length || 0 }}</div>
                <div class="text-xs text-gray-500">Roles</div>
              </div>
              <div class="text-center p-2 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">{{ preset.expense_categories?.length || 0 }}</div>
                <div class="text-xs text-gray-500">Categories</div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
              <button
                @click="viewPreset(preset.code)"
                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              >
                View Details
              </button>
              <button
                v-if="!isCurrentPreset(preset.code)"
                @click="applyPreset(preset.code)"
                :disabled="isLoading"
                class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Apply
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview Modal -->
      <div
        v-if="showPreview"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            @click="closePreview"
          ></div>

          <!-- Modal panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
              <div class="flex items-start justify-between mb-4">
                <div>
                  <h3 class="text-xl font-bold text-gray-900">{{ previewData?.name }}</h3>
                  <p class="mt-1 text-sm text-gray-500">{{ previewData?.description }}</p>
                </div>
                <button
                  @click="closePreview"
                  class="text-gray-400 hover:text-gray-500"
                >
                  <span class="sr-only">Close</span>
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <div v-if="isLoading" class="py-12 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-sm text-gray-500">Loading preset details...</p>
              </div>

              <div v-else-if="previewData" class="space-y-6 max-h-[60vh] overflow-y-auto">

                <!-- Fabrication Module Badge -->
                <div v-if="previewData.has_fabrication_module" class="flex items-center gap-2 p-3 bg-teal-50 border border-teal-200 rounded-lg">
                  <span class="text-teal-700 text-sm font-medium">✂ Includes Aluminium Fabrication Module</span>
                  <span class="text-xs text-teal-600">— Measurements, Pricing Rules, Fabrication Workflow</span>
                </div>

                <!-- What gets created summary -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                  <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-700">{{ previewData.roles?.length || 0 }}</div>
                    <div class="text-xs text-gray-500 mt-1">Roles</div>
                  </div>
                  <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-700">{{ previewData.expense_categories?.length || 0 }}</div>
                    <div class="text-xs text-gray-500 mt-1">Expense Categories</div>
                  </div>
                  <div class="text-center p-3 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-700">{{ previewData.job_types?.length || 0 }}</div>
                    <div class="text-xs text-gray-500 mt-1">Job Types</div>
                  </div>
                  <div class="text-center p-3 bg-amber-50 rounded-lg">
                    <div class="text-2xl font-bold text-amber-700">{{ previewData.inventory_categories?.length || 0 }}</div>
                    <div class="text-xs text-gray-500 mt-1">Inventory Categories</div>
                  </div>
                </div>

                <!-- Roles -->
                <div>
                  <h4 class="text-sm font-semibold text-gray-900 mb-3">Roles ({{ previewData.roles?.length || 0 }})</h4>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div v-for="(role, index) in previewData.roles" :key="index" class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                      <div class="font-medium text-sm text-gray-900">{{ role.name }}</div>
                      <div class="text-xs text-gray-500 mt-1">{{ role.permissions?.length || 0 }} permissions</div>
                    </div>
                  </div>
                </div>

                <!-- Expense Categories -->
                <div>
                  <h4 class="text-sm font-semibold text-gray-900 mb-3">Expense Categories ({{ previewData.expense_categories?.length || 0 }})</h4>
                  <div class="space-y-2">
                    <div v-for="(cat, index) in previewData.expense_categories" :key="index" class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                      <div class="flex items-start justify-between">
                        <div>
                          <div class="font-medium text-sm text-gray-900">{{ cat.name }}</div>
                          <div class="text-xs text-gray-500 mt-0.5">{{ cat.description }}</div>
                        </div>
                        <span v-if="cat.requires_approval" class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-800 rounded flex-shrink-0 ml-2">
                          Approval required
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Job Types -->
                <div v-if="previewData.job_types?.length">
                  <h4 class="text-sm font-semibold text-gray-900 mb-3">Job Types ({{ previewData.job_types.length }})</h4>
                  <div class="flex flex-wrap gap-2">
                    <span v-for="(type, i) in previewData.job_types" :key="i" class="px-3 py-1 text-sm bg-blue-50 text-blue-700 rounded-full">{{ type }}</span>
                  </div>
                </div>

                <!-- Inventory Categories -->
                <div v-if="previewData.inventory_categories?.length">
                  <h4 class="text-sm font-semibold text-gray-900 mb-3">Inventory Categories ({{ previewData.inventory_categories.length }})</h4>
                  <div class="flex flex-wrap gap-2">
                    <span v-for="(cat, i) in previewData.inventory_categories" :key="i" class="px-3 py-1 text-sm bg-green-50 text-green-700 rounded-full">{{ cat }}</span>
                  </div>
                </div>

                <!-- Asset Types -->
                <div v-if="previewData.asset_types?.length">
                  <h4 class="text-sm font-semibold text-gray-900 mb-3">Asset Types ({{ previewData.asset_types.length }})</h4>
                  <div class="flex flex-wrap gap-2">
                    <span v-for="(type, i) in previewData.asset_types" :key="i" class="px-3 py-1 text-sm bg-purple-50 text-purple-700 rounded-full">{{ type }}</span>
                  </div>
                </div>

                <!-- Default Settings summary -->
                <div v-if="previewData.default_settings" class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                  <h4 class="text-sm font-semibold text-gray-900 mb-2">Default Settings Applied</h4>
                  <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                    <div>Currency: <span class="font-medium text-gray-900">{{ previewData.default_settings.currency ?? 'ZMW' }}</span></div>
                    <div>VAT Rate: <span class="font-medium text-gray-900">{{ previewData.default_settings.vat_rate ?? 16 }}%</span></div>
                    <div>Invoice Due: <span class="font-medium text-gray-900">{{ previewData.default_settings.invoice_due_days ?? 30 }} days</span></div>
                    <div>VAT: <span class="font-medium text-gray-900">{{ previewData.default_settings.vat_enabled ? 'Enabled' : 'Disabled' }}</span></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
              <button
                @click="closePreview"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              >
                Close
              </button>
              <button
                v-if="selectedPreset && !isCurrentPreset(selectedPreset)"
                @click="applyPreset(selectedPreset)"
                :disabled="isLoading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Apply This Preset
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
