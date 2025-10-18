<template>
  <div class="referral-tree">
    <div v-if="data && data.length" class="tree-container">
      <div v-for="(referral, index) in data" :key="referral.id" class="referral-node">
        <div class="node-content">
          <div class="node-header">
            <span class="node-name">{{ referral.name }}</span>
            <span class="node-email text-sm text-gray-500">{{ referral.email }}</span>
          </div>
          <div class="node-stats">
            <div class="stat">
              <span class="stat-label">Active Investments:</span>
              <span class="stat-value">{{ referral.activeInvestments }}</span>
            </div>
            <div class="stat">
              <span class="stat-label">Total Invested:</span>
              <span class="stat-value">{{ formatCurrency(referral.totalInvested) }}</span>
            </div>
          </div>
        </div>
        <div v-if="referral.children && referral.children.length" class="children-container">
          <ReferralTree :data="referral.children" />
        </div>
      </div>
    </div>
    <div v-else class="empty-state">
      <p class="text-gray-500">No referrals yet</p>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import { formatCurrency } from '@/utils/formatting'

const props = defineProps({
  data: {
    type: Array,
    required: true
  }
})

const formatNumber = (number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(number)
}
</script>

<style scoped>
.referral-tree {
  @apply w-full;
}

.tree-container {
  @apply flex flex-col space-y-4;
}

.referral-node {
  @apply relative pl-8;
}

.referral-node::before {
  content: '';
  @apply absolute left-0 top-1/2 w-6 h-px bg-gray-300;
}

.referral-node:first-child::before {
  @apply hidden;
}

.node-content {
  @apply bg-white p-4 rounded-lg border border-gray-200 shadow-sm;
}

.node-header {
  @apply flex flex-col;
}

.node-name {
  @apply font-medium text-gray-900;
}

.node-stats {
  @apply mt-2 flex space-x-4;
}

.stat {
  @apply flex items-center space-x-1;
}

.stat-label {
  @apply text-sm text-gray-500;
}

.stat-value {
  @apply text-sm font-medium text-gray-900;
}

.children-container {
  @apply mt-4;
}

.empty-state {
  @apply text-center py-8;
}
</style> 