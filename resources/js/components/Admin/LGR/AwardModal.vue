<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
      <!-- Backdrop -->
      <div 
        class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity" 
        @click="$emit('close')"
      ></div>

      <!-- Modal -->
      <div class="relative w-full max-w-md transform rounded-xl bg-white shadow-2xl transition-all">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5 rounded-t-xl">
          <div class="flex items-start justify-between">
            <div>
              <h3 class="text-xl font-bold text-white">Award LGR Bonus</h3>
              <p class="text-sm text-blue-100 mt-1">Award loyalty credits to active premium members</p>
            </div>
            <button
              type="button"
              @click="$emit('close')"
              class="text-white hover:text-gray-200 transition-colors"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitAward" class="p-6 space-y-5">
          <!-- Member Search -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">
              Select Premium Member <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search by name, email, or phone..."
                class="w-full px-4 py-3 pr-10 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                @focus="showDropdown = true"
              />
              <svg class="absolute right-3 top-3.5 h-5 w-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            <!-- Search Results Dropdown -->
            <div
              v-if="showDropdown && filteredMembers.length > 0"
              class="absolute z-10 mt-2 w-full max-w-md bg-white shadow-xl max-h-64 rounded-lg border-2 border-gray-200 overflow-auto"
            >
              <button
                v-for="member in filteredMembers.slice(0, 10)"
                :key="member.id"
                type="button"
                @click="selectMember(member)"
                class="w-full px-4 py-3 text-left hover:bg-blue-50 border-b border-gray-100 last:border-0 transition-colors"
              >
                <div class="font-semibold text-gray-900">{{ member.name }}</div>
                <div class="text-sm text-gray-600 mt-0.5">{{ member.email }}</div>
                <div class="text-xs text-gray-500 mt-1">
                  {{ member.phone }} • {{ member.referrals_count }} referrals • K{{ member.loyalty_points }} LGC
                </div>
              </button>
            </div>

            <!-- No Results -->
            <div v-if="showDropdown && filteredMembers.length === 0 && searchQuery" class="mt-2 text-sm text-gray-500">
              No members found
            </div>

            <!-- No Premium Members -->
            <div v-if="!eligibleMembers || eligibleMembers.length === 0" class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
              <strong>No premium members found.</strong> Only members with premium starter kits are eligible for LGR awards.
            </div>

            <!-- Selected Member Card -->
            <div v-if="selectedMember" class="mt-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="font-semibold text-gray-900">{{ selectedMember.name }}</div>
                  <div class="text-sm text-gray-600 mt-0.5">{{ selectedMember.email }}</div>
                  <div class="text-xs text-gray-500 mt-1">Current LGC: K{{ selectedMember.loyalty_points }}</div>
                </div>
                <button
                  type="button"
                  @click="clearSelection"
                  class="ml-3 p-1.5 text-gray-400 hover:text-gray-600 hover:bg-white rounded-full transition-all"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Amount -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">
              Award Amount (K) <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.amount"
              type="number"
              min="10"
              max="2100"
              step="10"
              required
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
              placeholder="500"
            />
            <p class="mt-1.5 text-xs text-gray-500">Min: K10 | Max: K2,100</p>
          </div>

          <!-- Award Type -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">
              Award Type <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.award_type"
              required
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
            >
              <option value="early_adopter">Early Adopter Incentive</option>
              <option value="performance">Performance Recognition</option>
              <option value="marketing">Marketing Campaign</option>
              <option value="special">Special Recognition</option>
            </select>
          </div>

          <!-- Reason -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">
              Reason for Award <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="form.reason"
              rows="3"
              required
              minlength="10"
              maxlength="500"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"
              placeholder="Explain why this member is receiving this award..."
            ></textarea>
            <p class="mt-1.5 text-xs text-gray-500 text-right">{{ form.reason.length }}/500 characters</p>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t-2 border-gray-100">
            <button
              type="button"
              @click="$emit('close')"
              class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition-all"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="processing || !selectedMember"
              class="px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-blue-500/30"
            >
              {{ processing ? 'Processing...' : 'Award Bonus' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

interface Member {
  id: number;
  name: string;
  email: string;
  phone: string;
  referrals_count: number;
  loyalty_points: number;
}

interface Props {
  eligibleMembers: Member[];
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'success']);

// Debug: Log eligible members
console.log('Eligible Members:', props.eligibleMembers);
console.log('Total Members:', props.eligibleMembers?.length || 0);

const form = useForm({
  user_id: null as number | null,
  amount: 500,
  award_type: 'early_adopter',
  reason: '',
});

const searchQuery = ref('');
const selectedMember = ref<Member | null>(null);
const showDropdown = ref(false);
const processing = ref(false);

// Debounce timer for preventing rapid submissions
let lastSubmitTime = 0;

const filteredMembers = computed(() => {
  if (!searchQuery.value) return props.eligibleMembers;
  
  const query = searchQuery.value.toLowerCase();
  return props.eligibleMembers.filter(member =>
    member.name.toLowerCase().includes(query) ||
    member.email.toLowerCase().includes(query) ||
    member.phone.includes(query)
  );
});

const selectMember = (member: Member) => {
  selectedMember.value = member;
  form.user_id = member.id;
  searchQuery.value = member.name;
  showDropdown.value = false;
  console.log('Member selected:', member.id, member.name);
};

const clearSelection = () => {
  selectedMember.value = null;
  form.user_id = null;
  searchQuery.value = '';
};

const submitAward = async () => {
  if (!selectedMember.value) {
    Swal.fire({
      icon: 'warning',
      title: 'No Member Selected',
      text: 'Please select a member first',
    });
    return;
  }

  // Prevent double submission
  if (processing.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Processing',
      text: 'Award is already being processed. Please wait.',
      timer: 2000,
      showConfirmButton: false
    });
    return;
  }

  // Prevent rapid submissions (minimum 3 seconds between awards)
  const now = Date.now();
  if (now - lastSubmitTime < 3000) {
    Swal.fire({
      icon: 'warning',
      title: 'Please Wait',
      text: 'Please wait a moment before submitting another award.',
      timer: 2000,
      showConfirmButton: false
    });
    return;
  }

  const result = await Swal.fire({
    title: 'Confirm Award',
    html: `Award <strong>K${form.amount}</strong> to <strong>${selectedMember.value.name}</strong>?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, Award Bonus',
    cancelButtonText: 'Cancel'
  });

  if (!result.isConfirmed) return;

  lastSubmitTime = now;

  console.log('Submitting form data:', {
    user_id: form.user_id,
    amount: form.amount,
    award_type: form.award_type,
    reason: form.reason
  });
  
  console.log('Route URL:', route('admin.lgr.awards.store'));

  processing.value = true;
  
  form.post(route('admin.lgr.awards.store'), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      console.log('Award successful!', page);
      
      Swal.fire({
        icon: 'success',
        title: 'Award Granted!',
        text: `Successfully awarded K${form.amount} to ${selectedMember.value.name}`,
        timer: 3000,
        showConfirmButton: false
      });
      
      emit('success');
      emit('close');
    },
    onError: (errors) => {
      console.error('Award errors:', errors);
      console.error('Form data that failed:', {
        user_id: form.user_id,
        amount: form.amount,
        award_type: form.award_type,
        reason: form.reason
      });
      
      // Show specific error messages
      const errorMessages = Object.entries(errors)
        .map(([field, message]) => `<strong>${field}:</strong> ${message}`)
        .join('<br>');
      
      Swal.fire({
        icon: 'error',
        title: 'Award Failed',
        html: errorMessages || 'Unknown error occurred',
      });
    },
    onFinish: () => {
      processing.value = false;
    },
  });
};

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
  const target = e.target as HTMLElement;
  if (!target.closest('.relative')) {
    showDropdown.value = false;
  }
});
</script>
