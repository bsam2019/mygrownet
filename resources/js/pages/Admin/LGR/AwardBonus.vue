<template>
  <AdminLayout>
    <div class="py-8">
      <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <Link
          :href="route('admin.lgr.awards.index')"
          class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 mb-6"
        >
          <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Awards
        </Link>

        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-gray-900">Award LGR Bonus</h1>
          <p class="mt-1 text-sm text-gray-600">Award loyalty credits to active premium members</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white shadow rounded-lg">
          <form @submit.prevent="submitAward" class="p-6 space-y-6">
            
            <!-- Member Search -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Select Premium Member <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search by name, email, or phone..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  @focus="showDropdown = true"
                />
                <svg class="absolute right-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>

              <!-- Dropdown Results -->
              <div
                v-if="showDropdown && filteredMembers.length > 0"
                class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-lg border border-gray-200 overflow-auto"
              >
                <button
                  v-for="member in filteredMembers.slice(0, 10)"
                  :key="member.id"
                  type="button"
                  @click="selectMember(member)"
                  class="w-full px-4 py-3 text-left hover:bg-gray-50 border-b border-gray-100 last:border-0"
                >
                  <div class="font-medium text-gray-900">{{ member.name }}</div>
                  <div class="text-sm text-gray-500">{{ member.email }} • {{ member.phone }}</div>
                  <div class="text-xs text-gray-400 mt-1">{{ member.referrals_count }} referrals • K{{ member.loyalty_points }} LGC</div>
                </button>
              </div>

              <!-- Selected Member Display -->
              <div v-if="selectedMember" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-gray-900">{{ selectedMember.name }}</div>
                    <div class="text-sm text-gray-600">{{ selectedMember.email }}</div>
                  </div>
                  <button
                    type="button"
                    @click="clearSelection"
                    class="text-gray-400 hover:text-gray-600"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
              <p v-if="errors.user_id" class="mt-1 text-sm text-red-600">{{ errors.user_id }}</p>
            </div>

            <!-- Amount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Award Amount (K) <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="form.amount"
                type="number"
                min="10"
                max="2100"
                step="10"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="500"
              />
              <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
              <p class="mt-1 text-xs text-gray-500">Min: K10 | Max: K2,100</p>
            </div>

            <!-- Award Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Award Type <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.award_type"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="early_adopter">Early Adopter Incentive</option>
                <option value="performance">Performance Recognition</option>
                <option value="marketing">Marketing Campaign</option>
                <option value="special">Special Recognition</option>
              </select>
              <p v-if="errors.award_type" class="mt-1 text-sm text-red-600">{{ errors.award_type }}</p>
            </div>

            <!-- Reason -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Reason for Award <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="form.reason"
                rows="4"
                required
                minlength="10"
                maxlength="500"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Explain why this member is receiving this award..."
              ></textarea>
              <p v-if="errors.reason" class="mt-1 text-sm text-red-600">{{ errors.reason }}</p>
              <p class="mt-1 text-xs text-gray-500 text-right">{{ form.reason.length }}/500</p>
            </div>

            <!-- Preview -->
            <div v-if="selectedMember && form.amount" class="p-4 bg-green-50 border border-green-200 rounded-lg">
              <h4 class="font-medium text-green-900 mb-2">Award Preview</h4>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-green-700">Member:</span>
                  <span class="ml-2 font-medium text-green-900">{{ selectedMember.name }}</span>
                </div>
                <div>
                  <span class="text-green-700">Amount:</span>
                  <span class="ml-2 font-medium text-green-900">K{{ form.amount }}</span>
                </div>
                <div>
                  <span class="text-green-700">Current LGC:</span>
                  <span class="ml-2 text-green-900">K{{ selectedMember.loyalty_points }}</span>
                </div>
                <div>
                  <span class="text-green-700">New Balance:</span>
                  <span class="ml-2 font-medium text-green-900">K{{ Number(selectedMember.loyalty_points) + Number(form.amount) }}</span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
              <Link
                :href="route('admin.lgr.awards.index')"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="processing || !selectedMember"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ processing ? 'Processing...' : 'Award Bonus' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Member {
  id: number;
  name: string;
  email: string;
  phone: string;
  tier: string;
  referrals_count: number;
  loyalty_points: number;
}

interface Props {
  eligibleMembers: Member[];
}

const props = defineProps<Props>();

const form = useForm({
  user_id: '',
  amount: 500,
  award_type: 'early_adopter',
  reason: '',
});

const processing = ref(false);
const searchQuery = ref('');
const showDropdown = ref(false);
const selectedMember = ref<Member | null>(null);

const errors = computed(() => {
  return (router.page.props.errors as Record<string, string>) || {};
});

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
  form.user_id = member.id.toString();
  searchQuery.value = member.name;
  showDropdown.value = false;
};

const clearSelection = () => {
  selectedMember.value = null;
  form.user_id = '';
  searchQuery.value = '';
};

const submitAward = () => {
  if (!selectedMember.value) {
    alert('Please select a member');
    return;
  }

  if (!confirm(`Award K${form.amount} to ${selectedMember.value.name}?`)) {
    return;
  }

  processing.value = true;
  form.post(route('admin.lgr.awards.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by redirect
    },
    onError: (errors) => {
      console.error('Award errors:', errors);
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
