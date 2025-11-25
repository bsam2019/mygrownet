<template>
  <div class="bg-white rounded-xl shadow-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Trends</h3>
    
    <div v-if="performanceMetrics && hasData" class="space-y-6">
      <!-- Chart Tabs -->
      <div class="flex space-x-1 bg-gray-100 rounded-lg p-1">
        <button
          v-for="tab in chartTabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          :class="[
            'flex-1 px-3 py-2 text-sm font-medium rounded-md transition-colors',
            activeTab === tab.key
              ? 'bg-white text-blue-600 shadow-sm'
              : 'text-gray-600 hover:text-gray-900'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Chart Container -->
      <div class="h-64">
        <canvas :ref="el => chartRefs[activeTab] = el"></canvas>
      </div>

      <!-- Chart Legend/Info -->
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div v-if="activeTab === 'revenue'">
          <p class="text-gray-600">Latest Revenue</p>
          <p class="font-semibold text-gray-900">
            K{{ formatNumber(getLatestValue(performanceMetrics.revenue_trend.data)) }}
          </p>
        </div>
        <div v-if="activeTab === 'profit'">
          <p class="text-gray-600">Latest Profit</p>
          <p class="font-semibold" :class="getLatestValue(performanceMetrics.profit_trend.data) >= 0 ? 'text-green-600' : 'text-red-600'">
            K{{ formatNumber(getLatestValue(performanceMetrics.profit_trend.data)) }}
          </p>
        </div>
        <div v-if="activeTab === 'members'">
          <p class="text-gray-600">Total Members</p>
          <p class="font-semibold text-gray-900">
            {{ formatNumber(getLatestValue(performanceMetrics.member_growth.data)) }}
          </p>
        </div>
        <div v-if="activeTab === 'health'">
          <p class="text-gray-600">Health Trend</p>
          <p class="font-semibold text-gray-900">
            {{ getLatestValue(performanceMetrics.health_score_trend.data) }}/100
          </p>
        </div>
        
        <div>
          <p class="text-gray-600">Trend</p>
          <p class="font-semibold" :class="getTrendColor()">
            {{ getTrendDirection() }}
          </p>
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="text-center py-8 text-gray-500">
      <ChartBarIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" aria-hidden="true" />
      <p class="text-sm">Performance trends will be available after multiple reporting periods</p>
      <p class="text-xs mt-1">Charts will show historical data once we have 2+ reports</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';
import { ChartBarIcon } from '@heroicons/vue/24/outline';

Chart.register(...registerables);

interface PerformanceMetrics {
  revenue_trend: {
    labels: string[];
    data: number[];
  };
  profit_trend: {
    labels: string[];
    data: number[];
  };
  member_growth: {
    labels: string[];
    data: number[];
  };
  health_score_trend: {
    labels: string[];
    data: number[];
  };
}

const props = defineProps<{
  performanceMetrics: PerformanceMetrics | null;
}>();

const activeTab = ref('revenue');
const chartRefs = ref<Record<string, HTMLCanvasEle