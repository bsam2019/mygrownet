<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <Link :href="route('admin.points.users')" class="text-blue-600 hover:text-blue-800 text-sm mb-2 inline-block">
              ← Back to Users
            </Link>
            <h1 class="text-3xl font-bold text-gray-900">{{ user.name }}'s Points</h1>
            <p class="mt-2 text-gray-600">{{ user.email }}</p>
          </div>
          <div class="text-right">
            <div class="text-sm text-gray-500">Current Level</div>
            <div class="text-2xl font-bold capitalize" :class="getLevelColor(user.current_professional_level)">
              {{ user.current_professional_level }}
            </div>
          </div>
        </div>

        <!-- Points Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 mb-1">Lifetime Points</div>
            <div class="text-3xl font-bold text-blue-600">{{ userPoints?.lifetime_points || 0 }}</div>
            <div class="text-xs text-gray-500 mt-1">Never expires</div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 mb-1">Monthly Points</div>
            <div class="text-3xl font-bold text-green-600">{{ userPoints?.monthly_points || 0 }}</div>
            <div class="text-xs text-gray-500 mt-1">Resets monthly</div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 mb-1">Active Multiplier</div>
            <div class="text-3xl font-bold text-purple-600">{{ userPoints?.active_multiplier || 1.00 }}x</div>
            <div class="text-xs text-gray-500 mt-1">{{ userPoints?.current_streak_months || 0 }} month streak</div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500 mb-1">Qualification Status</div>
            <div class="text-lg font-bold" :class="isQualified ? 'text-green-600' : 'text-red-600'">
              {{ isQualified ? '✓ Qualified' : '✗ Not Qualified' }}
            </div>
            <div class="text-xs text-gray-500 mt-1">This month</div>
          </div>
        </div>

        <!-- Admin Actions -->
        <div class="bg-white rounded-lg shadow mb-8">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Admin Actions</h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <button
                @click="showAwardModal = true"
                class="px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
              >
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Award Points
              </button>

              <button
                @click="showDeductModal = true"
                class="px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
              >
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
                Deduct Points
              </button>

              <button
                @click="showSetModal = true"
                class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
              >
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Set Points
              </button>

              <button
                @click="showAdvanceLevelModal = true"
                class="px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition"
              >
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                Advance Level
              </button>
            </div>
          </div>
        </div>

        <!-- Level Progress -->
        <div class="bg-white rounded-lg shadow mb-8">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Level Progress</h2>
          </div>
          <div class="p-6">
            <div v-if="levelProgress.next_level" class="mb-6">
              <div class="flex justify-between text-sm mb-2">
                <span class="font-medium">Progress to {{ levelProgress.next_level }}</span>
                <span class="text-gray-600">{{ Math.round(levelProgress.progress) }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div
                  class="bg-blue-600 h-3 rounded-full transition-all"
                  :style="{ width: levelProgress.progress + '%' }"
                ></div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div v-for="(requirement, key) in levelProgress.met" :key="key" class="border rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium capitalize">{{ formatRequirementKey(key) }}</span>
                  <span v-if="requirement.met" class="text-green-600">✓</span>
                  <span v-else class="text-red-600">✗</span>
                </div>
                <div class="text-xs text-gray-600">
                  {{ requirement.current }} / {{ requirement.required }}
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                  <div
                    class="h-2 rounded-full transition-all"
                    :class="requirement.met ? 'bg-green-600' : 'bg-yellow-600'"
                    :style="{ width: requirement.progress + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Recent Transactions</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">LP</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">MAP</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Multiplier</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full" :class="getSourceBadgeClass(transaction.source)">
                      {{ formatSource(transaction.source) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-medium" :class="transaction.lp_amount >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ transaction.lp_amount >= 0 ? '+' : '' }}{{ transaction.lp_amount }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-medium" :class="transaction.map_amount >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ transaction.map_amount >= 0 ? '+' : '' }}{{ transaction.map_amount }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    {{ transaction.multiplier_applied }}x
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">{{ transaction.description }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(transaction.created_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Award Points Modal -->
        <Modal :show="showAwardModal" @close="showAwardModal = false">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Award Points to {{ user.name }}</h3>
            <form @submit.prevent="awardPoints">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Lifetime Points (LP)</label>
                <input
                  v-model="awardForm.lp_amount"
                  type="number"
                  min="0"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Points (MAP)</label>
                <input
                  v-model="awardForm.map_amount"
                  type="number"
                  min="0"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                <textarea
                  v-model="awardForm.reason"
                  rows="3"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                ></textarea>
              </div>
              <div class="flex justify-end gap-3">
                <button
                  type="button"
                  @click="showAwardModal = false"
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                >
                  Award Points
                </button>
              </div>
            </form>
          </div>
        </Modal>

        <!-- Deduct Points Modal -->
        <Modal :show="showDeductModal" @close="showDeductModal = false">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Deduct Points from {{ user.name }}</h3>
            <form @submit.prevent="deductPoints">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Lifetime Points (LP)</label>
                <input
                  v-model="deductForm.lp_amount"
                  type="number"
                  min="0"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                  required
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Points (MAP)</label>
                <input
                  v-model="deductForm.map_amount"
                  type="number"
                  min="0"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                  required
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                <textarea
                  v-model="deductForm.reason"
                  rows="3"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                  required
                ></textarea>
              </div>
              <div class="flex justify-end gap-3">
                <button
                  type="button"
                  @click="showDeductModal = false"
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                >
                  Deduct Points
                </button>
              </div>
            </form>
          </div>
        </Modal>

        <!-- Set Points Modal -->
        <Modal :show="showSetModal" @close="showSetModal = false">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Set Points for {{ user.name }}</h3>
            <form @submit.prevent="setPoints">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Lifetime Points (LP)</label>
                <input
                  v-model="setForm.lifetime_points"
                  type="number"
                  min="0"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Points (MAP)</label>
                <input
                  v-model="setForm.monthly_points"
                  type="number"
                  min="0"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                <textarea
                  v-model="setForm.reason"
                  rows="3"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                ></textarea>
              </div>
              <div class="flex justify-end gap-3">
                <button
                  type="button"
                  @click="showSetModal = false"
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                  Set Points
                </button>
              </div>
            </form>
          </div>
        </Modal>

        <!-- Advance Level Modal -->
        <Modal :show="showAdvanceLevelModal" @close="showAdvanceLevelModal = false">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Advance Level for {{ user.name }}</h3>
            <form @submit.prevent="advanceLevel">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">New Level</label>
                <select
                  v-model="advanceLevelForm.new_level"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  required
                >
                  <option value="associate">Associate</option>
                  <option value="professional">Professional</option>
                  <option value="senior">Senior</option>
                  <option value="manager">Manager</option>
                  <option value="director">Director</option>
                  <option value="executive">Executive</option>
                  <option value="ambassador">Ambassador</option>
                </select>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                <textarea
                  v-model="advanceLevelForm.reason"
                  rows="3"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  required
                ></textarea>
              </div>
              <div class="flex justify-end gap-3">
                <button
                  type="button"
                  @click="showAdvanceLevelModal = false"
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700"
                >
                  Advance Level
                </button>
              </div>
            </form>
          </div>
        </Modal>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  user: Object,
  userPoints: Object,
  levelProgress: Object,
  transactions: Object,
  badges: Array,
  monthlyHistory: Array,
});

const showAwardModal = ref(false);
const showDeductModal = ref(false);
const showSetModal = ref(false);
const showAdvanceLevelModal = ref(false);

const awardForm = reactive({
  lp_amount: 0,
  map_amount: 0,
  reason: '',
});

const deductForm = reactive({
  lp_amount: 0,
  map_amount: 0,
  reason: '',
});

const setForm = reactive({
  lifetime_points: props.userPoints?.lifetime_points || 0,
  monthly_points: props.userPoints?.monthly_points || 0,
  reason: '',
});

const advanceLevelForm = reactive({
  new_level: props.user.current_professional_level,
  reason: '',
});

const isQualified = computed(() => {
  const required = getRequiredMAP(props.user.current_professional_level);
  return (props.userPoints?.monthly_points || 0) >= required;
});

const getRequiredMAP = (level) => {
  const requirements = {
    associate: 100,
    professional: 200,
    senior: 300,
    manager: 400,
    director: 500,
    executive: 600,
    ambassador: 800,
  };
  return requirements[level] || 100;
};

const awardPoints = () => {
  router.post(route('admin.points.award', props.user.id), awardForm, {
    onSuccess: () => {
      showAwardModal.value = false;
      awardForm.lp_amount = 0;
      awardForm.map_amount = 0;
      awardForm.reason = '';
    },
  });
};

const deductPoints = () => {
  router.post(route('admin.points.deduct', props.user.id), deductForm, {
    onSuccess: () => {
      showDeductModal.value = false;
      deductForm.lp_amount = 0;
      deductForm.map_amount = 0;
      deductForm.reason = '';
    },
  });
};

const setPoints = () => {
  router.post(route('admin.points.set', props.user.id), setForm, {
    onSuccess: () => {
      showSetModal.value = false;
      setForm.reason = '';
    },
  });
};

const advanceLevel = () => {
  router.post(route('admin.points.advance-level', props.user.id), advanceLevelForm, {
    onSuccess: () => {
      showAdvanceLevelModal.value = false;
      advanceLevelForm.reason = '';
    },
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};

const formatSource = (source) => {
  return source.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatRequirementKey = (key) => {
  return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getLevelColor = (level) => {
  const colors = {
    associate: 'text-gray-600',
    professional: 'text-blue-600',
    senior: 'text-emerald-600',
    manager: 'text-purple-600',
    director: 'text-indigo-600',
    executive: 'text-pink-600',
    ambassador: 'text-amber-600',
  };
  return colors[level] || 'text-gray-600';
};

const getSourceBadgeClass = (source) => {
  const classes = {
    referral: 'bg-blue-100 text-blue-800',
    product_sale: 'bg-green-100 text-green-800',
    course_completion: 'bg-purple-100 text-purple-800',
    daily_login: 'bg-yellow-100 text-yellow-800',
    admin_award: 'bg-red-100 text-red-800',
    admin_deduction: 'bg-red-100 text-red-800',
    admin_adjustment: 'bg-orange-100 text-orange-800',
  };
  return classes[source] || 'bg-gray-100 text-gray-800';
};
</script>
