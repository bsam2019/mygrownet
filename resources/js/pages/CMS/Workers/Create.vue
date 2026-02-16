<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import FormInput from '@/components/CMS/FormInput.vue'
import FormSelect from '@/components/CMS/FormSelect.vue'
import FormSection from '@/components/CMS/FormSection.vue'

defineOptions({
  layout: CMSLayout
})

interface Props {
  departments: Array<{ id: number; name: string }>
}

const props = defineProps<Props>()

const form = useForm({
  // Basic Info
  first_name: '',
  last_name: '',
  middle_name: '',
  phone: '',
  email: '',
  
  // Personal Details
  date_of_birth: '',
  gender: '',
  nationality: 'Zambian',
  address: '',
  city: '',
  province: '',
  
  // Emergency Contact
  emergency_contact_name: '',
  emergency_contact_phone: '',
  emergency_contact_relationship: '',
  
  // Employment Details
  job_title: '',
  department_id: null,
  hire_date: '',
  employment_type: 'permanent',
  employment_status: 'active',
  contract_start_date: '',
  contract_end_date: '',
  probation_end_date: '',
  
  // Compensation
  worker_type: 'permanent',
  hourly_rate: 0,
  daily_rate: 0,
  commission_rate: 0,
  monthly_salary: 0,
  salary_currency: 'ZMW',
  
  // Tax & Benefits
  tax_number: '',
  napsa_number: '',
  nhima_number: '',
  
  // Payment Method
  payment_method: 'mobile_money',
  mobile_money_number: '',
  bank_name: '',
  bank_account_number: '',
  
  notes: '',
})

const workerTypes = [
  { value: 'casual', label: 'Casual' },
  { value: 'contract', label: 'Contract' },
  { value: 'permanent', label: 'Permanent' },
]

const employmentTypes = [
  { value: 'full_time', label: 'Full Time' },
  { value: 'part_time', label: 'Part Time' },
  { value: 'contract', label: 'Contract' },
  { value: 'temporary', label: 'Temporary' },
  { value: 'intern', label: 'Intern' },
]

const genderOptions = [
  { value: 'male', label: 'Male' },
  { value: 'female', label: 'Female' },
  { value: 'other', label: 'Other' },
]

const paymentMethods = [
  { value: 'cash', label: 'Cash' },
  { value: 'mobile_money', label: 'Mobile Money' },
  { value: 'bank_transfer', label: 'Bank Transfer' },
]

const submit = () => {
  form.post(route('cms.payroll.workers.store'))
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <button
          @click="$inertia.visit(route('cms.payroll.workers.index'))"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Workers
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Add Worker</h1>
        <p class="mt-1 text-sm text-gray-500">Register a new worker or contractor to your team</p>
      </div>

      <form @submit.prevent="submit" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 sm:p-8 space-y-8">
          <!-- Personal Information -->
          <FormSection
            title="Personal Information"
            description="Basic details about the worker"
            :divider="false"
          >
            <FormInput
              v-model="form.first_name"
              label="First Name"
              placeholder="Enter first name"
              required
              :error="form.errors.first_name"
            />

            <FormInput
              v-model="form.last_name"
              label="Last Name"
              placeholder="Enter last name"
              required
              :error="form.errors.last_name"
            />

            <FormInput
              v-model="form.middle_name"
              label="Middle Name"
              placeholder="Enter middle name (optional)"
              :error="form.errors.middle_name"
            />

            <FormInput
              v-model="form.date_of_birth"
              label="Date of Birth"
              type="date"
              :error="form.errors.date_of_birth"
            />

            <FormSelect
              v-model="form.gender"
              label="Gender"
              :options="genderOptions"
              :error="form.errors.gender"
            />

            <FormInput
              v-model="form.nationality"
              label="Nationality"
              placeholder="e.g., Zambian"
              :error="form.errors.nationality"
            />

            <FormInput
              v-model="form.phone"
              label="Phone Number"
              type="tel"
              placeholder="+260 XXX XXX XXX"
              required
              :error="form.errors.phone"
            />

            <FormInput
              v-model="form.email"
              label="Email Address"
              type="email"
              placeholder="worker@example.com"
              :error="form.errors.email"
            />

            <div class="sm:col-span-2">
              <FormInput
                v-model="form.address"
                label="Address"
                placeholder="Street address"
                :error="form.errors.address"
              />
            </div>

            <FormInput
              v-model="form.city"
              label="City"
              placeholder="e.g., Lusaka"
              :error="form.errors.city"
            />

            <FormInput
              v-model="form.province"
              label="Province"
              placeholder="e.g., Lusaka Province"
              :error="form.errors.province"
            />
          </FormSection>

          <!-- Emergency Contact -->
          <FormSection
            title="Emergency Contact"
            description="Person to contact in case of emergency"
          >
            <FormInput
              v-model="form.emergency_contact_name"
              label="Contact Name"
              placeholder="Full name"
              :error="form.errors.emergency_contact_name"
            />

            <FormInput
              v-model="form.emergency_contact_phone"
              label="Contact Phone"
              type="tel"
              placeholder="+260 XXX XXX XXX"
              :error="form.errors.emergency_contact_phone"
            />

            <div class="sm:col-span-2">
              <FormInput
                v-model="form.emergency_contact_relationship"
                label="Relationship"
                placeholder="e.g., Spouse, Parent, Sibling"
                :error="form.errors.emergency_contact_relationship"
              />
            </div>
          </FormSection>

          <!-- Employment Details -->
          <FormSection
            title="Employment Details"
            description="Job position and employment terms"
          >
            <FormInput
              v-model="form.job_title"
              label="Job Title"
              placeholder="e.g., Construction Worker"
              required
              :error="form.errors.job_title"
            />

            <FormSelect
              v-model="form.department_id"
              label="Department"
              :options="departments.map(d => ({ value: d.id, label: d.name }))"
              :error="form.errors.department_id"
            />

            <FormInput
              v-model="form.hire_date"
              label="Hire Date"
              type="date"
              required
              :error="form.errors.hire_date"
            />

            <FormSelect
              v-model="form.employment_type"
              label="Employment Type"
              :options="employmentTypes"
              required
              :error="form.errors.employment_type"
            />

            <FormInput
              v-model="form.contract_start_date"
              label="Contract Start Date"
              type="date"
              :error="form.errors.contract_start_date"
              help-text="For contract workers"
            />

            <FormInput
              v-model="form.contract_end_date"
              label="Contract End Date"
              type="date"
              :error="form.errors.contract_end_date"
              help-text="For contract workers"
            />

            <FormInput
              v-model="form.probation_end_date"
              label="Probation End Date"
              type="date"
              :error="form.errors.probation_end_date"
              help-text="For new permanent employees"
            />

            <FormSelect
              v-model="form.worker_type"
              label="Worker Type (Legacy)"
              :options="workerTypes"
              :error="form.errors.worker_type"
              help-text="For payroll compatibility"
            />
          </FormSection>

          <!-- Compensation -->
          <FormSection
            title="Compensation"
            description="Salary and payment rates"
          >
            <FormInput
              v-model.number="form.monthly_salary"
              label="Monthly Salary (K)"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              :error="form.errors.monthly_salary"
            />

            <FormInput
              v-model="form.salary_currency"
              label="Currency"
              placeholder="ZMW"
              :error="form.errors.salary_currency"
            />

            <FormInput
              v-model.number="form.hourly_rate"
              label="Hourly Rate (K)"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              :error="form.errors.hourly_rate"
              help-text="For hourly workers"
            />

            <FormInput
              v-model.number="form.daily_rate"
              label="Daily Rate (K)"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              :error="form.errors.daily_rate"
              help-text="For daily workers"
            />

            <div class="sm:col-span-2">
              <FormInput
                v-model.number="form.commission_rate"
                label="Commission Rate (%)"
                type="number"
                step="0.01"
                min="0"
                max="100"
                placeholder="0.00"
                :error="form.errors.commission_rate"
                help-text="Percentage for commission-based work"
              />
            </div>
          </FormSection>

          <!-- Tax & Benefits -->
          <FormSection
            title="Tax & Benefits"
            description="Tax identification and social security numbers"
          >
            <FormInput
              v-model="form.tax_number"
              label="TPIN (Tax Number)"
              placeholder="Enter TPIN"
              :error="form.errors.tax_number"
            />

            <FormInput
              v-model="form.napsa_number"
              label="NAPSA Number"
              placeholder="Enter NAPSA number"
              :error="form.errors.napsa_number"
            />

            <div class="sm:col-span-2">
              <FormInput
                v-model="form.nhima_number"
                label="NHIMA Number"
                placeholder="Enter NHIMA number"
                :error="form.errors.nhima_number"
              />
            </div>
          </FormSection>

          <!-- Payment Information -->
          <FormSection
            title="Payment Information"
            description="How the worker will receive payments"
          >
            <div class="sm:col-span-2">
              <FormSelect
                v-model="form.payment_method"
                label="Payment Method"
                :options="paymentMethods"
                required
                :error="form.errors.payment_method"
              />
            </div>

            <div v-if="form.payment_method === 'mobile_money'" class="sm:col-span-2">
              <FormInput
                v-model="form.mobile_money_number"
                label="Mobile Money Number"
                type="tel"
                placeholder="+260 XXX XXX XXX"
                :error="form.errors.mobile_money_number"
              />
            </div>

            <template v-if="form.payment_method === 'bank_transfer'">
              <FormInput
                v-model="form.bank_name"
                label="Bank Name"
                placeholder="e.g., Zanaco, FNB, Stanbic"
                :error="form.errors.bank_name"
              />

              <FormInput
                v-model="form.bank_account_number"
                label="Account Number"
                placeholder="Enter account number"
                :error="form.errors.bank_account_number"
              />
            </template>
          </FormSection>

          <!-- Additional Notes -->
          <FormSection
            title="Additional Notes"
            description="Any other relevant information"
          >
            <div class="sm:col-span-2">
              <FormInput
                v-model="form.notes"
                label="Notes"
                type="textarea"
                :rows="4"
                placeholder="Add any additional notes about this worker..."
                :error="form.errors.notes"
              />
            </div>
          </FormSection>
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-6 sm:px-8 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
          <button
            type="button"
            @click="$inertia.visit(route('cms.payroll.workers.index'))"
            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="form.processing">Creating...</span>
            <span v-else>Create Worker</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
