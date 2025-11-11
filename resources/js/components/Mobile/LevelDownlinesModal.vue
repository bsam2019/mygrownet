<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-[100000] overflow-y-auto"
        @click.self="emit('close')"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
        
        <!-- Modal -->
        <div class="flex min-h-full items-end justify-center p-0 relative z-10">
          <div class="relative w-full bg-white rounded-t-3xl shadow-2xl transform transition-all max-h-[85vh] flex flex-col">
            <!-- Header -->
            <div 
              class="sticky top-0 text-white px-6 py-4 rounded-t-3xl flex-shrink-0"
              :class="getHeaderColorClass(level)"
            >
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-bold">Level {{ level }} Team</h3>
                  <p class="text-sm opacity-90">{{ members.length }} member{{ members.length !== 1 ? 's' : '' }}</p>
                </div>
                <button
                  @click="emit('close')"
                  class="p-2 hover:bg-white/20 rounded-full transition-colors"
                >
                  <XMarkIcon class="h-5 w-5" />
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-3 overflow-y-auto flex-1">
              <!-- Filter Toggle -->
              <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                <span class="text-sm text-gray-700">Show only members without starter kit</span>
                <button
                  @click="showOnlyWithoutKit = !showOnlyWithoutKit"
                  :class="[
                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                    showOnlyWithoutKit ? 'bg-blue-600' : 'bg-gray-300'
                  ]"
                >
                  <span
                    :class="[
                      'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                      showOnlyWithoutKit ? 'translate-x-6' : 'translate-x-1'
                    ]"
                  />
                </button>
              </div>

              <!-- Members List -->
              <div v-if="filteredMembers.length > 0" class="space-y-2">
                <div
                  v-for="member in filteredMembers"
                  :key="member.id"
                  class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition-all"
                >
                  <div class="flex items-center gap-3">
                    <!-- Avatar -->
                    <div 
                      class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0"
                      :class="getAvatarColorClass(level)"
                    >
                      {{ member.name.charAt(0).toUpperCase() }}
                    </div>
                    
                    <!-- Member Info -->
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-gray-900 truncate">
                        {{ member.name }}
                      </h4>
                      <p class="text-xs text-gray-500">
                        {{ member.phone || member.email }}
                      </p>
                      <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                          {{ member.tier || 'Associate' }}
                        </span>
                        <span v-if="member.has_starter_kit" class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                          ✓ Kit
                        </span>
                        <span v-else class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                          No Kit
                        </span>
                      </div>
                    </div>

                    <!-- Stats & Actions -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                      <div class="text-right">
                        <p class="text-xs text-gray-500">Joined</p>
                        <p class="text-xs font-medium text-gray-900">{{ member.joined_date }}</p>
                        <p v-if="member.total_earnings" class="text-xs font-bold text-green-600 mt-1">
                          K{{ formatCurrency(member.total_earnings) }}
                        </p>
                      </div>
                      
                      <!-- Gift Button (Inline) -->
                      <button
                        v-if="member.has_starter_kit === false || member.has_starter_kit === undefined"
                        @click.stop="openGiftModal(member)"
                        type="button"
                        class="p-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all shadow-sm active:scale-95 z-50"
                        title="Gift Starter Kit"
                      >
                        <GiftIcon class="h-5 w-5" />
                      </button>
                    </div>
                  </div>

                  <!-- Additional Info (Collapsible) -->
                  <div v-if="member.direct_referrals > 0" class="mt-3 pt-3 border-t border-gray-100">
                    <div class="flex items-center justify-between text-xs">
                      <span class="text-gray-600">Direct Referrals:</span>
                      <span class="font-medium text-gray-900">{{ member.direct_referrals }}</span>
                    </div>
                    <div v-if="member.team_size > 0" class="flex items-center justify-between text-xs mt-1">
                      <span class="text-gray-600">Total Team:</span>
                      <span class="font-medium text-gray-900">{{ member.team_size }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-else class="text-center py-12">
                <UsersIcon class="h-16 w-16 text-gray-300 mx-auto mb-4" />
                <p class="text-gray-500 font-medium">
                  {{ showOnlyWithoutKit ? 'All members have starter kits!' : 'No members at this level yet' }}
                </p>
                <p class="text-sm text-gray-400 mt-2">
                  {{ showOnlyWithoutKit ? 'Great job helping your team!' : 'Start referring to build your team!' }}
                </p>
              </div>

              <!-- Summary Stats -->
              <div v-if="members.length > 0" class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Level {{ level }} Summary</h4>
                <div class="grid grid-cols-3 gap-3">
                  <div>
                    <p class="text-xs text-gray-600">Total Members</p>
                    <p class="text-lg font-bold text-gray-900">{{ members.length }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600">Without Kit</p>
                    <p class="text-lg font-bold text-amber-600">{{ membersWithoutKit }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600">Total Earnings</p>
                    <p class="text-lg font-bold text-green-600">K{{ formatCurrency(totalEarnings) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Gift Starter Kit Modal -->
    <GiftStarterKitModal
      v-if="selectedRecipient"
      :is-open="showGiftModal && !!selectedRecipient"
      :recipient="selectedRecipient || { id: 0, name: '', phone: '' }"
      :wallet-balance="walletBalance"
      @close="closeGiftModal"
      @success="handleGiftSuccess"
    />
  </Teleport>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { XMarkIcon, UsersIcon, GiftIcon } from '@heroicons/vue/24/outline';
import GiftStarterKitModal from './GiftStarterKitModal.vue';

interface Member {
  id: number;
  name: string;
  email: string;
  phone?: string;
  tier?: string;
  joined_date: string;
  total_earnings?: number;
  direct_referrals?: number;
  team_size?: number;
  has_starter_kit?: boolean;
}

interface Props {
  show: boolean;
  level: number;
  members: Member[];
  walletBalance?: number;
}

const props = withDefaults(defineProps<Props>(), {
  members: () => [],
  walletBalance: 0,
});

const emit = defineEmits(['close']);

// Debug: Log members when modal opens
watch(() => props.show, (isOpen) => {
  if (isOpen && props.members.length > 0) {
    console.log('Level modal opened with members:', props.members);
    console.log('First member has_starter_kit:', props.members[0]?.has_starter_kit);
  }
});

// Gift modal state
const showGiftModal = ref(false);
const selectedRecipient = ref<Member | null>(null);

// Filter state
const showOnlyWithoutKit = ref(false);

// Filtered members based on toggle
const filteredMembers = computed(() => {
  if (!showOnlyWithoutKit.value) {
    return props.members;
  }
  // Filter members without starter kit (undefined means no kit)
  return props.members.filter(member => member.has_starter_kit === false || member.has_starter_kit === undefined);
});

// Calculate total earnings for this level
const totalEarnings = computed(() => {
  return props.members.reduce((sum, member) => sum + (member.total_earnings || 0), 0);
});

// Count members without starter kit (undefined means no kit)
const membersWithoutKit = computed(() => {
  return props.members.filter(member => member.has_starter_kit === false || member.has_starter_kit === undefined).length;
});

// Open gift modal
const openGiftModal = (member: Member) => {
  console.log('Opening gift modal for:', member.name);
  selectedRecipient.value = member;
  showGiftModal.value = true;
  console.log('Gift modal state:', showGiftModal.value, selectedRecipient.value);
};

// Close gift modal
const closeGiftModal = () => {
  showGiftModal.value = false;
  selectedRecipient.value = null;
};

// Handle successful gift
const handleGiftSuccess = () => {
  // Optionally refresh the member list or show a success message
  emit('close');
};

// Get header color based on level
const getHeaderColorClass = (level: number) => {
  const colors = [
    'bg-gradient-to-r from-blue-600 to-blue-700',
    'bg-gradient-to-r from-green-600 to-green-700',
    'bg-gradient-to-r from-yellow-600 to-yellow-700',
    'bg-gradient-to-r from-purple-600 to-purple-700',
    'bg-gradient-to-r from-pink-600 to-pink-700',
    'bg-gradient-to-r from-indigo-600 to-indigo-700',
    'bg-gradient-to-r from-orange-600 to-orange-700',
  ];
  return colors[level - 1] || 'bg-gradient-to-r from-gray-600 to-gray-700';
};

// Get avatar color based on level
const getAvatarColorClass = (level: number) => {
  const colors = [
    'bg-blue-500',
    'bg-green-500',
    'bg-yellow-500',
    'bg-purple-500',
    'bg-pink-500',
    'bg-indigo-500',
    'bg-orange-500',
  ];
  return colors[level - 1] || 'bg-gray-500';
};

const formatCurrency = (value: number | undefined | null) => {
  if (value === undefined || value === null || isNaN(value)) return '0.00';
  return Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: translateY(100%);
}
</style>
