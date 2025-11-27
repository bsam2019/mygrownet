<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="modelValue" 
        class="fixed inset-0 bg-black"
        style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 999999 !important;"
      >
        <PresentationViewer
          :referral-link="referralLink"
          :referral-code="referralCode"
          :user-name="userName"
          @close="$emit('update:modelValue', false)"
          @share="handleShare"
        />
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import PresentationViewer from './Presentation/PresentationViewer.vue';

defineProps<{
  modelValue: boolean;
  referralLink: string;
  referralCode: string;
  userName?: string;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: boolean];
  share: [];
}>();

const handleShare = () => {
  emit('share');
};
</script>
