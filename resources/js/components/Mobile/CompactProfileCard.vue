<template>
  <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
    <div class="flex items-center gap-3 mb-3">
      <!-- Avatar -->
      <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-lg font-bold flex-shrink-0">
        {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
      </div>
      
      <!-- Name & Tier -->
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
          <h3 class="text-sm font-bold text-gray-900 truncate">{{ user?.name || 'User' }}</h3>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 flex-shrink-0">
            {{ currentTier }}
          </span>
          <span v-if="user?.has_starter_kit" class="text-sm flex-shrink-0">⭐</span>
        </div>
        <p class="text-xs text-gray-600 truncate">{{ user?.email || 'No email' }}</p>
      </div>
    </div>

    <!-- Progress Bar -->
    <div class="mb-3">
      <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
        <span class="truncate">Progress to {{ membershipProgress?.next_tier?.name || 'Max Level' }}</span>
        <span class="font-semibold text-blue-600 flex-shrink-0 ml-2">{{ membershipProgress?.progress_percentage || 0 }}%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full transition-all duration-500"
          :style="{ width: `${membershipProgress?.progress_percentage || 0}%` }"
        ></div>
      </div>
    </div>

    <!-- Edit Button -->
    <button
      @click="$emit('edit')"
      aria-label="Edit your profile"
      class="w-full py-2 px-4 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 transition-colors active:scale-95"
    >
      Edit Profile
    </button>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  user: any;
  currentTier: string;
  membershipProgress: any;
}>();

defineEmits<{
  edit: [];
}>();
</script>
