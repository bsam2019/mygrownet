<template>
  <AdminLayout :breadcrumbs="[
    { name: 'Dashboard', href: route('admin.dashboard') },
    { name: 'Investment Tiers', href: route('admin.investment-tiers.index') }
  ]">
    <Head title="Investment Tiers Management" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-900">Investment Tiers Management</h2>
              <div class="flex space-x-2">
                <div class="flex items-center">
                  <label class="mr-2 text-sm text-gray-600">Show Archived</label>
                  <button 
                    @click="toggleShowArchived" 
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    :class="showArchived ? 'bg-indigo-600' : 'bg-gray-200'"
                  >
                    <span 
                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                      :class="showArchived ? 'translate-x-5' : 'translate-x-0'"
                    ></span>
                  </button>
                </div>
                <button
                  @click="openAddModal"
                  class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  <PlusIcon class="w-4 h-4 mr-2" />
                  Add New Tier
                </button>
              </div>
            </div>
          </div>

          <!-- Summary Cards -->
          <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
              <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-4 md:p-6">
                  <div class="flex items-center">
                    <div class="p-2 md:p-3 rounded-full bg-indigo-100 text-indigo-600">
                      <BanknotesIcon class="w-5 h-5 md:w-6 md:h-6" />
                    </div>
                    <div class="ml-3 md:ml-4">
                      <p class="text-xs md:text-sm font-medium text-gray-600">Total Tiers</p>
                      <p class="text-base md:text-lg font-semibold text-gray-900">{{ filteredTiers.length }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-4 md:p-6">
                  <div class="flex items-center">
                    <div class="p-2 md:p-3 rounded-full bg-green-100 text-green-600">
                      <BanknotesIcon class="w-5 h-5 md:w-6 md:h-6" />
                    </div>
                    <div class="ml-3 md:ml-4">
                      <p class="text-xs md:text-sm font-medium text-gray-600">Highest Investment</p>
                      <p class="text-base md:text-lg font-semibold text-gray-900">{{ formatKwacha(highestInvestment) }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-4 md:p-6">
                  <div class="flex items-center">
                    <div class="p-2 md:p-3 rounded-full bg-blue-100 text-blue-600">
                      <ChartBarIcon class="w-5 h-5 md:w-6 md:h-6" />
                    </div>
                    <div class="ml-3 md:ml-4">
                      <p class="text-xs md:text-sm font-medium text-gray-600">Highest Profit Rate</p>
                      <p class="text-base md:text-lg font-semibold text-gray-900">{{ formatPercent(highestProfitRate) }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-4 md:p-6">
                  <div class="flex items-center">
                    <div class="p-2 md:p-3 rounded-full bg-purple-100 text-purple-600">
                      <CheckCircleIcon class="w-5 h-5 md:w-6 md:h-6" />
                    </div>
                    <div class="ml-3 md:ml-4">
                      <p class="text-xs md:text-sm font-medium text-gray-600">Active Tiers</p>
                      <p class="text-base md:text-lg font-semibold text-gray-900">{{ activeTiersCount }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Investment</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profit Rate</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referral Rates</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="tier in filteredTiers" :key="tier.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4">
                    <div>
                      <div class="text-sm font-medium text-blue-600">{{ tier.name }}</div>
                      <div class="text-xs text-gray-500">{{ tier.description }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500">{{ formatKwacha(tier.minimum_investment) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">{{ formatPercent(tier.fixed_profit_rate) }}</td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-500">
                      <div>Direct: <span class="font-medium">{{ formatPercent(tier.direct_referral_rate) }}</span></div>
                      <div v-if="tier.level2_referral_rate" class="text-xs">
                        Level 2: {{ formatPercent(tier.level2_referral_rate) }}
                      </div>
                      <div v-if="tier.level3_referral_rate" class="text-xs">
                        Level 3: {{ formatPercent(tier.level3_referral_rate) }}
                      </div>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    <span
                      :class="[
                        tier.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800',
                        'inline-flex rounded-full px-2 text-xs font-semibold leading-5'
                      ]"
                    >
                      {{ tier.is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span
                      v-if="tier.is_archived"
                      class="ml-2 inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800"
                    >
                      Archived
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap space-x-2">
                    <button
                      @click="openEditModal(tier)"
                      class="px-3 py-1 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700"
                      :disabled="tier.is_archived"
                    >
                      Edit
                    </button>
                    <button
                      @click="toggleStatus(tier)"
                      class="px-3 py-1 text-sm text-white rounded"
                      :class="tier.is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                      :disabled="tier.is_archived"
                    >
                      {{ tier.is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button
                      @click="toggleArchive(tier)"
                      class="px-3 py-1 text-sm text-white rounded"
                      :class="tier.is_archived ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-gray-600 hover:bg-gray-700'"
                    >
                      {{ tier.is_archived ? 'Unarchive' : 'Archive' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Add New Tier Modal -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeAddModal"></div>

        <div class="relative bg-white rounded-lg w-full max-w-md transform transition-all my-8">
          <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Add New Investment Tier</h3>
            <button @click="closeAddModal" class="text-gray-500 hover:text-gray-700">
              <span class="text-2xl">&times;</span>
            </button>
          </div>

          <form id="addTierForm" @submit.prevent="submitAdd" class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="form.description"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Investment</label>
              <input
                v-model="form.minimum_investment"
                type="number"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Fixed Profit Rate (%)</label>
              <input
                v-model="form.fixed_profit_rate"
                type="number"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Direct Referral Rate (%)</label>
              <input
                v-model="form.direct_referral_rate"
                type="number"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Level 2 Referral Rate (%)</label>
              <input
                v-model="form.level2_referral_rate"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Level 3 Referral Rate (%)</label>
              <input
                v-model="form.level3_referral_rate"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Order</label>
              <input
                v-model="form.order"
                type="number"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Benefits</label>
              <div class="mb-2">
                <div v-for="(benefit, index) in benefitsArray" :key="index" class="flex items-center mb-2">
                  <input
                    v-model="benefitsArray[index]"
                    type="text"
                    class="flex-grow px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="e.g., Priority Support"
                  />
                  <button 
                    type="button" 
                    @click="removeBenefit(index)" 
                    class="ml-2 text-red-500 hover:text-red-700 focus:outline-none"
                  >
                    <XCircleIcon class="h-5 w-5" />
                  </button>
                </div>
              </div>
              <div class="flex space-x-2">
                <button 
                  type="button" 
                  @click="addBenefit" 
                  class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  <PlusIcon class="h-4 w-4 mr-1" />
                  Add Blank Benefit
                </button>
                <button 
                  type="button" 
                  @click="toggleStandardBenefits" 
                  class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  <span v-if="!standardBenefitsVisible">Show Standard Benefits</span>
                  <span v-else>Hide Standard Benefits</span>
                </button>
              </div>
            </div>
            
            <!-- Standard Benefits Panel -->
            <div v-if="standardBenefitsVisible" class="mt-3 p-3 border rounded-md bg-gray-50 max-h-60 overflow-y-auto">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Standard Benefits</h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div 
                  v-for="benefit in STANDARD_BENEFITS" 
                  :key="benefit"
                  @click="addStandardBenefit(benefit)"
                  class="px-3 py-2 text-sm border rounded-md bg-white hover:bg-indigo-50 cursor-pointer"
                >
                  {{ benefit }}
                </div>
              </div>
            </div>
            <div class="border p-4 rounded-md bg-gray-50">
              <h4 class="text-sm font-medium text-gray-700 mb-3">Settings</h4>
              <div class="space-y-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Early Withdrawal Penalty (%)</label>
                  <input
                    v-model="form.settings.early_withdrawal_penalty"
                    type="number"
                    step="0.01"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Partial Withdrawal Limit (%)</label>
                  <input
                    v-model="form.settings.partial_withdrawal_limit"
                    type="number"
                    step="0.01"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Lock-in Period (days)</label>
                  <input
                    v-model="form.settings.minimum_lock_in_period"
                    type="number"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Performance Bonus Rate (%)</label>
                  <input
                    v-model="form.settings.performance_bonus_rate"
                    type="number"
                    step="0.01"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Optional"
                  />
                </div>
                <div class="flex items-center">
                  <input
                    id="requires_kyc"
                    v-model="form.settings.requires_kyc"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label for="requires_kyc" class="ml-2 block text-sm text-gray-700">Requires KYC</label>
                </div>
                <div class="flex items-center">
                  <input
                    id="requires_approval"
                    v-model="form.settings.requires_approval"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label for="requires_approval" class="ml-2 block text-sm text-gray-700">Requires Approval</label>
                </div>
              </div>
            </div>
          </form>
          
          <div class="flex justify-end space-x-2 p-4 border-t">
            <button
              type="button"
              @click="closeAddModal"
              class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              form="addTierForm"
              class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700"
            >
              Create Tier
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeEditModal"></div>

        <div class="relative bg-white rounded-lg w-full max-w-md transform transition-all my-8">
          <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Edit Investment Tier</h3>
            <button @click="closeEditModal" class="text-gray-500 hover:text-gray-700">
              <span class="text-2xl">&times;</span>
            </button>
          </div>

          <form id="editTierForm" @submit.prevent="submitEdit" class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input
                v-model="editingTier.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="editingTier.description"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Investment</label>
              <input
                v-model="editingTier.minimum_investment"
                type="number"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Fixed Profit Rate (%)</label>
              <input
                v-model="editingTier.fixed_profit_rate"
                type="number"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Direct Referral Rate (%)</label>
              <input
                v-model="editingTier.direct_referral_rate"
                type="number"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Level 2 Referral Rate (%)</label>
              <input
                v-model="editingTier.level2_referral_rate"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Level 3 Referral Rate (%)</label>
              <input
                v-model="editingTier.level3_referral_rate"
                type="number"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Order</label>
              <input
                v-model="editingTier.order"
                type="number"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Benefits</label>
              <div class="mb-2">
                <div v-for="(benefit, index) in editBenefitsArray" :key="index" class="flex items-center mb-2">
                  <input
                    v-model="editBenefitsArray[index]"
                    type="text"
                    class="flex-grow px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="e.g., Priority Support"
                  />
                  <button 
                    type="button" 
                    @click="removeEditBenefit(index)" 
                    class="ml-2 text-red-500 hover:text-red-700 focus:outline-none"
                  >
                    <XCircleIcon class="h-5 w-5" />
                  </button>
                </div>
              </div>
              <button 
                type="button" 
                @click="addEditBenefit" 
                class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <PlusIcon class="h-4 w-4 mr-1" />
                Add Benefit
              </button>
            </div>
            <div class="border p-4 rounded-md bg-gray-50">
              <h4 class="text-sm font-medium text-gray-700 mb-3">Settings</h4>
              <div class="space-y-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Early Withdrawal Penalty (%)</label>
                  <input
                    v-model="editingTier.settings.early_withdrawal_penalty"
                    type="number"
                    step="0.01"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Partial Withdrawal Limit (%)</label>
                  <input
                    v-model="editingTier.settings.partial_withdrawal_limit"
                    type="number"
                    step="0.01"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Lock-in Period (days)</label>
                  <input
                    v-model="editingTier.settings.minimum_lock_in_period"
                    type="number"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Performance Bonus Rate (%)</label>
                  <input
                    v-model="editingTier.settings.performance_bonus_rate"
                    type="number"
                    step="0.01"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Optional"
                  />
                </div>
                <div class="flex items-center">
                  <input
                    id="edit_requires_kyc"
                    v-model="editingTier.settings.requires_kyc"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label for="edit_requires_kyc" class="ml-2 block text-sm text-gray-700">Requires KYC</label>
                </div>
                <div class="flex items-center">
                  <input
                    id="edit_requires_approval"
                    v-model="editingTier.settings.requires_approval"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label for="edit_requires_approval" class="ml-2 block text-sm text-gray-700">Requires Approval</label>
                </div>
              </div>
            </div>
          </form>
          
          <div class="flex justify-end space-x-2 p-4 border-t">
            <button
              type="button"
              @click="closeEditModal"
              class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              form="editTierForm"
              class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700"
            >
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeDeleteModal"></div>

        <div class="relative bg-white rounded-lg w-full max-w-md transform transition-all my-8">
          <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Delete Investment Tier</h3>
            <button @click="closeDeleteModal" class="text-gray-500 hover:text-gray-700">
              <span class="text-2xl">&times;</span>
            </button>
          </div>

          <div class="p-4">
            <p class="text-sm text-gray-600 mb-4">
              Are you sure you want to delete this tier? This action cannot be undone.
            </p>
            <div class="flex justify-end space-x-2">
              <button
                type="button"
                @click="closeDeleteModal"
                class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                type="button"
                @click="deleteTier"
                class="px-4 py-2 text-sm text-white bg-red-600 rounded-md hover:bg-red-700"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm, Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Swal from 'sweetalert2'
import { formatKwacha, formatPercent } from '@/utils/format'
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  CheckCircleIcon,
  XCircleIcon,
  BanknotesIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  tiers: {
    type: Array,
    required: true
  }
})

const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const editingTier = ref(null)
const deletingTier = ref(null)
const showArchived = ref(false)

const filteredTiers = computed(() => {
  return props.tiers.filter(tier => showArchived.value ? tier.is_archived : !tier.is_archived)
})

const highestInvestment = computed(() => {
  return filteredTiers.value.length > 0 ? Math.max(...filteredTiers.value.map(tier => tier.minimum_investment)) : 0
})

const highestProfitRate = computed(() => {
  return filteredTiers.value.length > 0 ? Math.max(...filteredTiers.value.map(tier => tier.fixed_profit_rate)) : 0
})

const activeTiersCount = computed(() => {
  return filteredTiers.value.filter(tier => tier.is_active && !tier.is_archived).length
})

const FORM_STORAGE_KEY = 'investment_tier_form_data'
const STANDARD_BENEFITS = [
  'Priority Customer Support',
  'Exclusive Investment Opportunities',
  'Quarterly Financial Review',
  'Dedicated Account Manager',
  'Invitations to VIP Events',
  'Early Access to New Products',
  'Reduced Transaction Fees',
  'Personalized Investment Strategy',
  'Higher Withdrawal Limits',
  'Special Account Interest Rates',
  'Premium Educational Resources',
  'Complimentary Financial Planning'
]

const standardBenefitsVisible = ref(false)

const form = useForm({
  name: '',
  description: '',
  minimum_investment: 0,
  fixed_profit_rate: 0,
  direct_referral_rate: 0,
  level2_referral_rate: null,
  level3_referral_rate: null,
  order: 0,
  benefits: [],
  is_active: true,
  settings: {
    early_withdrawal_penalty: 0,
    partial_withdrawal_limit: 0,
    minimum_lock_in_period: 0,
    performance_bonus_rate: null,
    requires_kyc: false,
    requires_approval: false
  }
})

const benefitsArray = ref([''])
const editBenefitsArray = ref([''])

const getLastFormData = () => {
  try {
    const storedData = localStorage.getItem(FORM_STORAGE_KEY)
    if (storedData) {
      const parsedData = JSON.parse(storedData)
      return {
        ...parsedData,
        settings: {
          early_withdrawal_penalty: parsedData.settings?.early_withdrawal_penalty || 0,
          partial_withdrawal_limit: parsedData.settings?.partial_withdrawal_limit || 0,
          minimum_lock_in_period: parsedData.settings?.minimum_lock_in_period || 0,
          performance_bonus_rate: parsedData.settings?.performance_bonus_rate || null,
          requires_kyc: parsedData.settings?.requires_kyc || false,
          requires_approval: parsedData.settings?.requires_approval || false
        }
      }
    }
  } catch (error) {
    console.error('Error retrieving form data from localStorage:', error)
  }
  
  return {
    name: '',
    description: '',
    minimum_investment: 0,
    fixed_profit_rate: 0,
    direct_referral_rate: 0,
    level2_referral_rate: null,
    level3_referral_rate: null,
    order: 0,
    benefits: [''],
    is_active: true,
    settings: {
      early_withdrawal_penalty: 0,
      partial_withdrawal_limit: 0,
      minimum_lock_in_period: 0,
      performance_bonus_rate: null,
      requires_kyc: false,
      requires_approval: false
    }
  }
}

const saveFormData = (formData) => {
  try {
    localStorage.setItem(FORM_STORAGE_KEY, JSON.stringify(formData))
  } catch (error) {
    console.error('Error saving form data to localStorage:', error)
  }
}

const toggleStandardBenefits = () => {
  standardBenefitsVisible.value = !standardBenefitsVisible.value
}

const addStandardBenefit = (benefit) => {
  if (!benefitsArray.value.includes(benefit)) {
    benefitsArray.value.push(benefit)
  }
}

const openAddModal = () => {
  const lastData = getLastFormData()
  
  form.name = lastData.name || ''
  form.description = lastData.description || ''
  form.minimum_investment = lastData.minimum_investment || 0
  form.fixed_profit_rate = lastData.fixed_profit_rate || 0
  form.direct_referral_rate = lastData.direct_referral_rate || 0
  form.level2_referral_rate = lastData.level2_referral_rate || null
  form.level3_referral_rate = lastData.level3_referral_rate || null
  form.order = lastData.order || 0
  form.benefits = [...(lastData.benefits || [''])]
  form.settings = {
    early_withdrawal_penalty: lastData.settings.early_withdrawal_penalty || 0,
    partial_withdrawal_limit: lastData.settings.partial_withdrawal_limit || 0,
    minimum_lock_in_period: lastData.settings.minimum_lock_in_period || 0,
    performance_bonus_rate: lastData.settings.performance_bonus_rate || null,
    requires_kyc: lastData.settings.requires_kyc || false,
    requires_approval: lastData.settings.requires_approval || false
  }
  
  benefitsArray.value = lastData.benefits && lastData.benefits.length 
    ? [...lastData.benefits] 
    : ['']
  
  standardBenefitsVisible.value = false
  showAddModal.value = true
}

const closeAddModal = () => {
  const formData = {
    name: form.name,
    description: form.description,
    minimum_investment: form.minimum_investment,
    fixed_profit_rate: form.fixed_profit_rate,
    direct_referral_rate: form.direct_referral_rate,
    level2_referral_rate: form.level2_referral_rate,
    level3_referral_rate: form.level3_referral_rate,
    order: form.order,
    benefits: [...benefitsArray.value],
    settings: {
      early_withdrawal_penalty: form.settings.early_withdrawal_penalty,
      partial_withdrawal_limit: form.settings.partial_withdrawal_limit,
      minimum_lock_in_period: form.settings.minimum_lock_in_period,
      performance_bonus_rate: form.settings.performance_bonus_rate,
      requires_kyc: form.settings.requires_kyc,
      requires_approval: form.settings.requires_approval
    }
  }
  
  saveFormData(formData)
  showAddModal.value = false
}

const submitAdd = () => {
  // Filter out empty benefits
  const filteredBenefits = benefitsArray.value.filter(benefit => benefit.trim() !== '')
  
  // Copy form data to formData object
  form.benefits = filteredBenefits
  
  // Log form data
  console.log('Form data before submission:', {
    formData: form.data(),
    filteredBenefits,
    benefitsArray: benefitsArray.value
  })
  
  // Save current form data to localStorage
  saveFormData({
    ...form.data(),
    benefits: [...benefitsArray.value]
  })

  // Submit the form
  form.post(route('admin.investment-tiers.store'), {
    preserveScroll: true,
    onSuccess: (response) => {
      console.log('Success response:', response)
      showAddModal.value = false
      Swal.fire({
        title: 'Success!',
        text: 'Investment tier created successfully',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      })
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors)
      const errorMessage = Object.values(errors).flat().join('\n')
      Swal.fire({
        title: 'Error!',
        text: errorMessage || 'There was a problem creating the tier. Please check the form and try again.',
        icon: 'error',
      })
    }
  })
}

const openEditModal = (tier) => {
  editingTier.value = { 
    ...tier,
    settings: tier.settings || {
      early_withdrawal_penalty: 0,
      partial_withdrawal_limit: 0,
      minimum_lock_in_period: 0,
      performance_bonus_rate: null,
      requires_kyc: false,
      requires_approval: false
    }
  }
  
  if (tier.benefits && Array.isArray(tier.benefits)) {
    editBenefitsArray.value = [...tier.benefits]
  } else if (tier.benefits && typeof tier.benefits === 'string') {
    try {
      const parsedBenefits = JSON.parse(tier.benefits)
      editBenefitsArray.value = Array.isArray(parsedBenefits) ? parsedBenefits : ['']
    } catch (e) {
      editBenefitsArray.value = [tier.benefits || '']
    }
  } else {
    editBenefitsArray.value = ['']
  }
  
  if (editBenefitsArray.value.length === 0) {
    editBenefitsArray.value = ['']
  }
  
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editingTier.value = null
}

const submitEdit = () => {
  const filteredBenefits = editBenefitsArray.value.filter(benefit => benefit.trim() !== '')
  
  form.name = editingTier.value.name
  form.description = editingTier.value.description
  form.minimum_investment = parseFloat(editingTier.value.minimum_investment)
  form.fixed_profit_rate = parseFloat(editingTier.value.fixed_profit_rate)
  form.direct_referral_rate = parseFloat(editingTier.value.direct_referral_rate)
  form.level2_referral_rate = editingTier.value.level2_referral_rate ? parseFloat(editingTier.value.level2_referral_rate) : null
  form.level3_referral_rate = editingTier.value.level3_referral_rate ? parseFloat(editingTier.value.level3_referral_rate) : null
  form.order = parseInt(editingTier.value.order || 0)
  form.benefits = filteredBenefits
  form.is_active = editingTier.value.is_active
  form.settings = {
    early_withdrawal_penalty: parseFloat(editingTier.value.settings.early_withdrawal_penalty || 0),
    partial_withdrawal_limit: parseFloat(editingTier.value.settings.partial_withdrawal_limit || 0),
    minimum_lock_in_period: parseInt(editingTier.value.settings.minimum_lock_in_period || 0),
    performance_bonus_rate: editingTier.value.settings.performance_bonus_rate ? parseFloat(editingTier.value.settings.performance_bonus_rate) : null,
    requires_kyc: editingTier.value.settings.requires_kyc || false,
    requires_approval: editingTier.value.settings.requires_approval || false
  }

  form.put(route('admin.investment-tiers.update', editingTier.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeEditModal()
      Swal.fire({
        title: 'Success!',
        text: 'Investment tier updated successfully',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      })
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors)
      const errorMessage = Object.values(errors).flat().join('\n')
      Swal.fire({
        title: 'Error!',
        text: errorMessage || 'There was a problem updating the tier. Please check the form and try again.',
        icon: 'error',
      })
    }
  })
}

const confirmDelete = (tier) => {
  deletingTier.value = tier
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  deletingTier.value = null
}

const deleteTier = () => {
  if (deletingTier.value) {
    form.delete(route('admin.investment-tiers.destroy', deletingTier.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        closeDeleteModal()
        Swal.fire({
          title: 'Success!',
          text: 'Investment tier deleted successfully',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        })
      }
    })
  }
}

const toggleStatus = (tier) => {
  Swal.fire({
    title: 'Are you sure?',
    text: `Do you want to ${tier.is_active ? 'deactivate' : 'activate'} this tier?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes'
  }).then((result) => {
    if (result.isConfirmed) {
      form.patch(route('admin.investment-tiers.toggle-status', tier.id), {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire({
            title: 'Success!',
            text: `Tier ${tier.is_active ? 'deactivated' : 'activated'} successfully`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          })
        }
      })
    }
  })
}

const addBenefit = () => {
  benefitsArray.value.push('')
}

const removeBenefit = (index) => {
  if (benefitsArray.value.length > 1) {
    benefitsArray.value.splice(index, 1)
  }
}

const addEditBenefit = () => {
  editBenefitsArray.value.push('')
}

const removeEditBenefit = (index) => {
  if (editBenefitsArray.value.length > 1) {
    editBenefitsArray.value.splice(index, 1)
  }
}

const toggleShowArchived = () => {
  showArchived.value = !showArchived.value
}

const toggleArchive = async (tier) => {
  try {
    const result = await Swal.fire({
      title: tier.is_archived ? 'Unarchive Tier?' : 'Archive Tier?',
      text: tier.is_archived 
        ? 'This will make the tier available again.' 
        : 'This will hide the tier from users but preserve its data.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: tier.is_archived ? 'Yes, unarchive it!' : 'Yes, archive it!'
    });

    if (result.isConfirmed) {
      await form.patch(route('admin.investment-tiers.toggle-archive', tier.id));
      
      Swal.fire(
        'Success!',
        `Tier has been ${tier.is_archived ? 'unarchived' : 'archived'}.`,
        'success'
      );
    }
  } catch (error) {
    console.error('Error toggling archive status:', error);
    Swal.fire(
      'Error!',
      'There was a problem updating the tier status.',
      'error'
    );
  }
};
</script> 