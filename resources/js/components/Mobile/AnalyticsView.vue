<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { 
  TrendingUpIcon, 
  UsersIcon, 
  DollarSignIcon, 
  AwardIcon, 
  XIcon, 
  ArrowRightIcon,
  SparklesIcon,
  ChevronRightIcon
} from 'lucide-vue-next';

interface Props {
  userId: number;
}

const props = defineProps<Props>();

const loading = ref(true);
const performance = ref<any>(null);
const recommendations = ref<any[]>([]);
const growthPotential = ref<any>(null);
const nextMilestone = ref<any>(null);

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-ZM', {
    style: 'currency',
    currency: 'ZMW',
    minimumFractionDigits: 0,
  }).format(amount);
};

const getHealthScoreColor = (score: number) => {
  if (score >= 80) return 'text-green-600';
  if (score >= 60) return 'text-yellow-600';
  return 'text-red-600';
};

const getHealthScoreBg = (score: number) => {
  if (score >= 80) return 'bg-green-50';
  if (score >= 60) return 'bg-yellow-50';
  return 'bg-red-50';
};

const getPriorityColor = (priority: string) => {
  switch (priority) {
    case 'high': return 'border-red-500 bg-red-50';
    case 'medium': return 'border-yellow-500 bg-yellow-50';
    case 'low': return 'border-blue-500 bg-blue-50';
    default: return 'border-gray-500 bg-gray-50';
  }
};

const fetchAnalytics = async () => {
  loading.value = true;
  try {
    // Fetch all analytics data from the performance endpoint (now returns everything)
    const response = await fetch(route('mygrownet.analytics.performance'), {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    
    if (response.ok) {
      const data = await response.json();
      console.log('📊 Analytics data received:', data);
      
      // Set all data
      performance.value = data.performance;
      recommendations.value = Array.isArray(data.recommendations) ? data.recommendations : [];
      growthPotential.value = data.growthPotential || null;
      nextMilestone.value = data.nextMilestone || null;
      
      // Debug logging
      console.log('✅ Performance:', performance.value);
      console.log('💡 Recommendations count:', recommendations.value.length);
      console.log('📈 Growth Potential:', growthPotential.value ? 'Available' : 'Not available');
      console.log('🎯 Next Milestone:', nextMilestone.value ? nextMilestone.value.milestone?.level : 'Not available');
      
      if (!growthPotential.value) {
        console.warn('⚠️ Growth Potential data is missing');
      }
      if (!nextMilestone.value) {
        console.warn('⚠️ Next Milestone data is missing');
      }
      if (recommendations.value.length === 0) {
        console.warn('⚠️ No recommendations available');
      }
    } else {
      console.error('❌ Analytics API error:', response.status, response.statusText);
      const errorText = await response.text();
      console.error('Error details:', errorText);
    }
    
  } catch (error) {
    console.error('❌ Failed to fetch analytics:', error);
  } finally {
    loading.value = false;
  }
};

const dismissRecommendation = async (id: number) => {
  try {
    const response = await fetch(route('mygrownet.analytics.recommendations.dismiss', id), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    });
    
    if (response.ok) {
      recommendations.value = recommendations.value.filter(r => r.id !== id);
    }
  } catch (error) {
    console.error('Failed to dismiss recommendation:', error);
  }
};

onMounted(() => {
  fetchAnalytics();
});
</script>

<template>
  <div class="space-y-4">
    <!-- Loading State -->
    <div v-if="loading" class="space-y-4">
      <div class="animate-pulse bg-gray-200 h-24 rounded-xl"></div>
      <div class="animate-pulse bg-gray-200 h-32 rounded-xl"></div>
      <div class="animate-pulse bg-gray-200 h-40 rounded-xl"></div>
    </div>

    <!-- Analytics Content -->
    <div v-else-if="performance" class="space-y-4">
      
      <!-- Key Metrics Grid -->
      <div class="grid grid-cols-2 gap-3">
        <!-- Total Earnings -->
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg p-4 text-white">
          <div class="flex items-center gap-2 mb-2">
            <DollarSignIcon class="h-5 w-5 opacity-80" />
            <p class="text-xs font-medium opacity-90">Total Earnings</p>
          </div>
          <p class="text-2xl font-bold">{{ formatCurrency(performance.earnings.total) }}</p>
          <p class="text-xs opacity-75 mt-1">All time</p>
        </div>

        <!-- Network Size -->
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg p-4 text-white">
          <div class="flex items-center gap-2 mb-2">
            <UsersIcon class="h-5 w-5 opacity-80" />
            <p class="text-xs font-medium opacity-90">Network</p>
          </div>
          <p class="text-2xl font-bold">{{ performance.network.total_size }}</p>
          <p class="text-xs opacity-75 mt-1">{{ performance.network.active_percentage }}% active</p>
        </div>

        <!-- Health Score -->
        <div class="bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl shadow-lg p-4 text-white">
          <div class="flex items-center gap-2 mb-2">
            <AwardIcon class="h-5 w-5 opacity-80" />
            <p class="text-xs font-medium opacity-90">Health Score</p>
          </div>
          <p class="text-2xl font-bold">{{ performance.health_score }}/100</p>
          <p class="text-xs opacity-75 mt-1">Network health</p>
        </div>

        <!-- Growth Rate -->
        <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg p-4 text-white">
          <div class="flex items-center gap-2 mb-2">
            <TrendingUpIcon class="h-5 w-5 opacity-80" />
            <p class="text-xs font-medium opacity-90">Growth</p>
          </div>
          <p class="text-2xl font-bold">{{ performance.growth.growth_rate }}%</p>
          <p class="text-xs opacity-75 mt-1">Last 30 days</p>
        </div>
      </div>

      <!-- Next Milestone Banner -->
      <div v-if="nextMilestone" class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl shadow-lg p-5 text-white">
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
              <SparklesIcon class="h-6 w-6" />
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-xs font-medium bg-white/20 px-2 py-0.5 rounded-full">Next Milestone</span>
            </div>
            <h3 class="font-bold text-lg">{{ nextMilestone.milestone.level }}</h3>
            <p class="text-sm opacity-90 mt-1">
              {{ nextMilestone.remaining }} more referrals to unlock {{ nextMilestone.milestone.reward }}
            </p>
            <div class="mt-3 bg-white/20 rounded-full h-2.5 overflow-hidden">
              <div 
                class="bg-white h-full transition-all duration-500 rounded-full"
                :style="{ width: nextMilestone.current_progress + '%' }"
              ></div>
            </div>
            <div class="flex items-center justify-between mt-2">
              <p class="text-xs opacity-75">
                {{ nextMilestone.current_progress }}% complete
              </p>
              <p v-if="nextMilestone.estimated_days" class="text-xs opacity-75">
                ~{{ nextMilestone.estimated_days }} days
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recommendations Section -->
      <div v-if="recommendations && recommendations.length > 0" class="space-y-3">
        <div class="flex items-center gap-2 px-1">
          <SparklesIcon class="h-4 w-4 text-yellow-500" />
          <h3 class="text-sm font-bold text-gray-900">Recommendations for You</h3>
        </div>
        <div
          v-for="rec in recommendations"
          :key="rec.id"
          class="bg-white rounded-xl shadow-sm p-4 border-l-4 relative"
          :class="getPriorityColor(rec.priority)"
        >
          <button
            @click="dismissRecommendation(rec.id)"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 active:scale-95 transition-colors"
          >
            <XIcon class="h-4 w-4" />
          </button>
          <div class="pr-8">
            <h4 class="font-semibold text-gray-900 text-sm">{{ rec.title }}</h4>
            <p class="text-xs text-gray-600 mt-1.5 leading-relaxed">{{ rec.description }}</p>
          </div>
          <div class="mt-3 flex items-center justify-between">
            <a
              :href="rec.action_url"
              class="inline-flex items-center text-xs text-blue-600 hover:text-blue-700 font-medium active:scale-95 transition-all"
            >
              {{ rec.action_text }}
              <ChevronRightIcon class="h-3 w-3 ml-0.5" />
            </a>
            <span class="text-xs text-gray-500 font-medium">
              Impact: {{ rec.impact_score }}/100
            </span>
          </div>
        </div>
      </div>

      <!-- Earnings Breakdown -->
      <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-sm font-bold text-gray-900 mb-4">💰 Earnings Breakdown</h3>
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs text-gray-600">Referral Commissions</span>
            <span class="font-semibold text-sm text-gray-900">
              {{ formatCurrency(performance.earnings.by_source.referral_commissions || 0) }}
            </span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-xs text-gray-600">LGR Profit Sharing</span>
            <span class="font-semibold text-sm text-gray-900">
              {{ formatCurrency(performance.earnings.by_source.lgr_profit_sharing || 0) }}
            </span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-xs text-gray-600">Level Bonuses</span>
            <span class="font-semibold text-sm text-gray-900">
              {{ formatCurrency(performance.earnings.by_source.level_bonuses || 0) }}
            </span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-xs text-gray-600">Other</span>
            <span class="font-semibold text-sm text-gray-900">
              {{ formatCurrency(performance.earnings.by_source.other || 0) }}
            </span>
          </div>
          <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
            <span class="text-sm font-bold text-gray-900">Total</span>
            <span class="text-base font-bold text-gray-900">
              {{ formatCurrency(performance.earnings.total) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Growth Potential Section -->
      <div v-if="growthPotential" class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center gap-2 mb-4">
          <TrendingUpIcon class="h-4 w-4 text-green-600" />
          <h3 class="text-sm font-bold text-gray-900">Growth Potential</h3>
        </div>
        <div class="space-y-3">
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-3">
            <div class="flex items-center justify-between">
              <span class="text-xs text-gray-600">Current Monthly</span>
              <span class="font-bold text-sm text-gray-900">
                {{ formatCurrency(growthPotential.current_monthly_potential) }}
              </span>
            </div>
          </div>
          <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-3">
            <div class="flex items-center justify-between">
              <span class="text-xs text-gray-600">Full Activation</span>
              <span class="font-bold text-sm text-green-600">
                {{ formatCurrency(growthPotential.full_activation_potential) }}
              </span>
            </div>
          </div>
          <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-3 border-2 border-orange-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <SparklesIcon class="h-4 w-4 text-orange-600" />
                <span class="text-sm font-bold text-gray-900">Untapped Potential</span>
              </div>
              <span class="text-lg font-bold text-orange-600">
                {{ formatCurrency(growthPotential.untapped_potential) }}
              </span>
            </div>
          </div>
          
          <div v-if="growthPotential.growth_opportunities && growthPotential.growth_opportunities.length > 0" class="pt-3 border-t border-gray-200">
            <p class="text-xs font-bold text-gray-900 mb-2.5">💡 Growth Opportunities</p>
            <div class="space-y-2">
              <div
                v-for="(opp, index) in growthPotential.growth_opportunities"
                :key="index"
                class="flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-3 border border-blue-100"
              >
                <span class="text-xs font-medium text-gray-700">{{ opp.title }}</span>
                <span class="text-xs font-bold text-blue-600 bg-white px-2 py-1 rounded-full">
                  +{{ opp.potential_increase }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Network Overview -->
      <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-sm font-bold text-gray-900 mb-4">👥 Network Overview</h3>
        <div class="grid grid-cols-2 gap-3">
          <div class="text-center p-3 bg-gray-50 rounded-lg">
            <p class="text-xl font-bold text-gray-900">{{ performance.network.total_size }}</p>
            <p class="text-xs text-gray-600 mt-1">Total Network</p>
          </div>
          <div class="text-center p-3 bg-green-50 rounded-lg">
            <p class="text-xl font-bold text-green-600">{{ performance.network.active_count }}</p>
            <p class="text-xs text-gray-600 mt-1">Active Members</p>
          </div>
          <div class="text-center p-3 bg-blue-50 rounded-lg">
            <p class="text-xl font-bold text-blue-600">{{ performance.network.direct_referrals }}</p>
            <p class="text-xs text-gray-600 mt-1">Direct Referrals</p>
          </div>
          <div class="text-center p-3 bg-purple-50 rounded-lg">
            <p class="text-xl font-bold text-purple-600">{{ performance.network.active_percentage }}%</p>
            <p class="text-xs text-gray-600 mt-1">Active Rate</p>
          </div>
        </div>
      </div>

      <!-- Peer Comparison -->
      <div v-if="performance.vs_peers" class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-sm font-bold text-gray-900 mb-2">🏆 Peer Comparison</h3>
        <p class="text-xs text-gray-600 mb-4">
          vs other {{ performance.vs_peers.tier }} members
        </p>
        <div class="grid grid-cols-3 gap-3">
          <div class="text-center p-3 bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg">
            <p class="text-xl font-bold text-gray-900">{{ performance.vs_peers.earnings_percentile }}%</p>
            <p class="text-xs text-gray-600 mt-1">Earnings</p>
          </div>
          <div class="text-center p-3 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg">
            <p class="text-xl font-bold text-gray-900">{{ performance.vs_peers.network_percentile }}%</p>
            <p class="text-xs text-gray-600 mt-1">Network</p>
          </div>
          <div class="text-center p-3 bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg">
            <p class="text-xl font-bold text-gray-900">{{ performance.vs_peers.growth_percentile }}%</p>
            <p class="text-xs text-gray-600 mt-1">Growth</p>
          </div>
        </div>
      </div>

    </div>

    <!-- Error State -->
    <div v-else class="bg-white rounded-xl shadow-sm p-6 text-center">
      <p class="text-gray-600">Unable to load analytics data</p>
      <button
        @click="fetchAnalytics"
        class="mt-4 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 active:scale-95 transition-all"
      >
        Retry
      </button>
    </div>
  </div>
</template>
