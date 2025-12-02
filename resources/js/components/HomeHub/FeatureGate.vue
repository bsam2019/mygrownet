<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { LockClosedIcon } from '@heroicons/vue/24/outline';

interface UpgradeSuggestion {
  feature: string;
  required_tier: string;
  tier_name: string;
  price: number;
  currency: string;
  billing_cycle: string;
}

interface Props {
  moduleId: string;
  feature: string;
  fallbackMessage?: string;
}

const props = withDefaults(defineProps<Props>(), {
  fallbackMessage: 'This feature requires an upgrade',
});

const emit = defineEmits<{
  (e: 'upgrade-click', suggestion: UpgradeSuggestion): void;
}>();

const hasAccess = ref<boolean | null>(null);
const loading = ref(true);
const upgradeSuggestion = ref<UpgradeSuggestion | null>(null);

const checkAccess = async () => {
  try {
    loading.value = true;
    const response = await fetch(`/api/modules/${props.moduleId}/features/${props.feature}/access`);
    const data = await response.json();
    
    hasAccess.value = data.has_access;
    if (!data.has_access && data.upgrade_suggestion) {
      upgradeSuggestion.value = data.upgrade_suggestion;
    }
  } catch (error) {
    console.error('Failed to check feature access:', error);
    hasAccess.value = false;
  } finally {
    loading.value = false;
  }
};

const handleUpgradeClick = () => {
  if (upgradeSuggestion.value) {
    emit('upgrade-click', upgradeSuggestion.value);
  }
};

onMounted(() => {
  checkAccess();
});
</script>

<template>
  <div>
    <!-- Loading state -->
    <div v-if="loading" class="animate-pulse">
      <slot name="loading">
        <div class="h-8 bg-gray-200 rounded"></div>
      </slot>
    </div>

    <!-- Has access - show content -->
    <div v-else-if="hasAccess">
      <slot />
    </div>

    <!-- No access - show locked state -->
    <div v-else class="relative">
      <!-- Blurred/disabled content -->
      <div class="opacity-50 pointer-events-none blur-sm">
        <slot name="locked-preview">
          <slot />
        </slot>
      </div>

      <!-- Upgrade overlay -->
      <div class="absolute inset-0 flex items-center justify-center bg-white/80 rounded-lg">
        <div class="text-center p-4">
          <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
            <LockClosedIcon class="w-6 h-6 text-gray-500" aria-hidden="true" />
          </div>
          
          <p class="text-gray-600 text-sm mb-3">
            {{ upgradeSuggestion ? `Upgrade to ${upgradeSuggestion.tier_name} to unlock` : fallbackMessage }}
          </p>
          
          <button
            v-if="upgradeSuggestion"
            @click="handleUpgradeClick"
            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
          >
            Upgrade for {{ upgradeSuggestion.currency }} {{ upgradeSuggestion.price }}/{{ upgradeSuggestion.billing_cycle }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
