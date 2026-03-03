<template>
  <form @submit.prevent="submit" class="space-y-6">
    <!-- Header with Icon -->
    <div class="flex items-center gap-3 pb-4 border-b border-gray-200">
      <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30">
        <BanknotesIcon class="h-6 w-6 text-white" aria-hidden="true" />
      </div>
      <div>
        <h3 class="text-lg font-semibold text-gray-900">Record Expense</h3>
        <p class="text-sm text-gray-500">Add a new business expense for approval</p>
      </div>
    </div>

    <!-- Category -->
    <div>
      <div class="flex items-center justify-between mb-1">
        <label class="block text-sm font-medium text-gray-700">
          Expense Category
          <span class="text-red-500 ml-1">*</span>
        </label>
        <button
          type="button"
          @click="showCategoryModal = true"
          class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors"
        >
          <PlusCircleIcon class="h-4 w-4" aria-hidden="true" />
          Quick Add
        </button>
      </div>
      
      <FormSelect
        v-model="form.category_id"
        label=""
        :options="categoryOptions"
        placeholder="Select expense category"
        required
        :error="form.errors.category_id"
        help-text="Choose the category that best describes this expense"
      />
    </div>

    <!-- Quick Add Category Modal -->
    <QuickAddCategoryModal
      :open="showCategoryModal"
      @close="showCategoryModal = false"
      @success="handleCategoryAdded"
    />

    <!-- Description -->
    <FormInput
      v-model="form.description"
      label="Description"
      type="textarea"
      :rows="3"
      placeholder="Brief description of what this expense was for..."
      required
      :error="form.errors.description"
      help-text="Provide details about the expense purpose"
    />

    <!-- Amount & Payment Method -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <FormInput
        v-model="form.amount"
        label="Amount"
        type="number"
        step="0.01"
        min="0"
        placeholder="0.00"
        required
        :error="form.errors.amount"
        help-text="Amount in Kwacha (K)"
      />

      <FormSelect
        v-model="form.payment_method"
        label="Payment Method"
        :options="paymentMethods"
        placeholder="Select payment method"
        required
        :error="form.errors.payment_method"
      />
    </div>

    <!-- Expense Date & Receipt Number -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <FormInput
        v-model="form.expense_date"
        label="Expense Date"
        type="date"
        required
        :error="form.errors.expense_date"
      />

      <FormInput
        v-model="form.receipt_number"
        label="Receipt Number"
        placeholder="Optional"
        :error="form.errors.receipt_number"
        help-text="Reference number from receipt"
      />
    </div>

    <!-- Job Link (Optional) -->
    <FormSelect
      v-if="jobs && jobs.length > 0"
      v-model="form.job_id"
      label="Link to Job"
      :options="[
        { value: '', label: 'None' },
        ...jobs.map(j => ({ value: j.id, label: `${j.job_number} - ${j.description}` }))
      ]"
      :error="form.errors.job_id"
      help-text="Associate this expense with a specific job (optional)"
    />

    <!-- Notes -->
    <FormInput
      v-model="form.notes"
      label="Additional Notes"
      type="textarea"
      :rows="2"
      placeholder="Any additional information or context..."
      :error="form.errors.notes"
    />

    <!-- Receipt Upload -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        Receipt Attachment
      </label>
      
      <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors duration-200">
        <div class="space-y-1 text-center">
          <DocumentArrowUpIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
          <div class="flex text-sm text-gray-600">
            <label
              for="receipt-upload"
              class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
            >
              <span>Upload a file</span>
              <input
                id="receipt-upload"
                type="file"
                accept=".jpg,.jpeg,.png,.pdf"
                class="sr-only"
                @change="handleFileUpload"
              />
            </label>
            <p class="pl-1">or drag and drop</p>
          </div>
          <p class="text-xs text-gray-500">JPG, PNG or PDF up to 5MB</p>
          <p v-if="uploadedFileName" class="text-sm font-medium text-green-600 mt-2">
            ✓ {{ uploadedFileName }}
          </p>
        </div>
      </div>
      <p v-if="form.errors.receipt" class="mt-1 text-sm text-red-600">{{ form.errors.receipt }}</p>
    </div>

    <!-- Info Box -->
    <div class="rounded-lg bg-blue-50 border border-blue-200 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <InformationCircleIcon class="h-5 w-5 text-blue-400" aria-hidden="true" />
        </div>
        <div class="ml-3">
          <p class="text-sm text-blue-700">
            This expense will be submitted for approval. Once approved, it will automatically sync to the financial system and appear in reports.
          </p>
        </div>
      </div>
    </div>

    <!-- Form Actions -->
    <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
      <button
        type="button"
        @click="$emit('cancel')"
        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
      >
        <XMarkIcon class="h-4 w-4" aria-hidden="true" />
        Cancel
      </button>
      <button
        type="submit"
        :disabled="form.processing"
        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-blue-500/30 transition-all duration-200"
      >
        <CheckCircleIcon v-if="!form.processing" class="h-5 w-5" aria-hidden="true" />
        <ArrowPathIcon v-else class="h-5 w-5 animate-spin" aria-hidden="true" />
        <span v-if="form.processing">Recording...</span>
        <span v-else>Record Expense</span>
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
  BanknotesIcon, 
  DocumentArrowUpIcon, 
  InformationCircleIcon,
  CheckCircleIcon,
  XMarkIcon,
  ArrowPathIcon,
  PlusCircleIcon
} from '@heroicons/vue/24/outline';
import FormInput from '@/components/CMS/FormInput.vue';
import FormSelect from '@/components/CMS/FormSelect.vue';
import QuickAddCategoryModal from '@/components/CMS/QuickAddCategoryModal.vue';

interface Category {
  id: number;
  name: string;
}

interface Job {
  id: number;
  job_number: string;
  description: string;
}

interface Props {
  categories: Category[];
  jobs?: Job[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'cancel'): void;
  (e: 'success'): void;
}>();

const uploadedFileName = ref<string>('');
const showCategoryModal = ref(false);
const localCategories = ref<Category[]>([...props.categories]);

const categoryOptions = computed(() => 
  localCategories.value.map(c => ({ value: c.id, label: c.name }))
);

const paymentMethods = [
  { value: 'cash', label: '💵 Cash' },
  { value: 'bank_transfer', label: '🏦 Bank Transfer' },
  { value: 'mtn_momo', label: '📱 MTN MoMo' },
  { value: 'airtel_money', label: '📱 Airtel Money' },
  { value: 'company_card', label: '💳 Company Card' },
];

const form = useForm({
  category_id: '',
  description: '',
  amount: '',
  payment_method: '',
  expense_date: new Date().toISOString().split('T')[0],
  receipt_number: '',
  job_id: '',
  notes: '',
  receipt: null as File | null,
});

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.receipt = target.files[0];
    uploadedFileName.value = target.files[0].name;
  }
};

const handleCategoryAdded = (newCategory: Category) => {
  localCategories.value.push(newCategory);
  form.category_id = newCategory.id.toString();
  showCategoryModal.value = false;
};

const submit = () => {
  form.post(route('cms.expenses.store'), {
    preserveScroll: true,
    onSuccess: () => {
      emit('success');
      form.reset();
      uploadedFileName.value = '';
    },
  });
};
</script>
