<template>
  <div class="min-h-full w-full bg-gradient-to-br from-amber-500 via-orange-500 to-red-600 flex flex-col items-center justify-center p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -mr-36 -mt-36"></div>
    <div class="absolute bottom-0 left-0 w-56 h-56 bg-white/5 rounded-full -ml-28 -mb-28"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-4">
        <span class="text-white text-sm font-semibold">🏆 Performance Bonus</span>
      </div>
      
      <h2 class="text-3xl md:text-4xl font-bold mb-2">
        Earn Monthly Bonuses from Your Network!
      </h2>
      <p class="text-amber-100 mb-6">
        Get paid when your downlines purchase products or pay subscriptions
      </p>
      
      <!-- Income Potential Table -->
      <div class="bg-white/95 backdrop-blur-sm rounded-xl p-4 border border-white/30 mb-4 shadow-lg">
        <h3 class="font-bold mb-3 text-sm text-gray-800">💰 Monthly Income Potential Example</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead>
              <tr class="border-b-2 border-orange-300">
                <th class="text-left py-2 px-2 text-gray-700 font-semibold">Level</th>
                <th class="text-center py-2 px-2 text-gray-700 font-semibold">Members</th>
                <th class="text-center py-2 px-2 text-gray-700 font-semibold">Avg/Member</th>
                <th class="text-center py-2 px-2 text-gray-700 font-semibold">Rate</th>
                <th class="text-right py-2 px-2 text-gray-700 font-semibold">Your Bonus</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="(example, index) in incomeExamples" 
                :key="`income-${index}`"
                class="border-b border-gray-200"
              >
                <td class="py-2 px-2 font-semibold text-gray-800">L{{ example.level }}</td>
                <td class="text-center py-2 px-2 text-gray-700">{{ example.members }}</td>
                <td class="text-center py-2 px-2 text-gray-700">K{{ example.avgPerMember }}</td>
                <td class="text-center py-2 px-2 text-emerald-600 font-semibold">{{ example.rate }}%</td>
                <td class="text-right py-2 px-2 font-bold text-emerald-600">K{{ formatBonus(example.bonus) }}</td>
              </tr>
              <tr class="font-bold bg-emerald-50">
                <td colspan="4" class="text-right py-2 px-2 text-gray-800">Total Monthly:</td>
                <td class="text-right py-2 px-2 text-emerald-600">K{{ formatBonus(totalMonthlyBonus) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="text-xs text-gray-600 mt-2">
          * Example based on average K120 monthly purchase/subscription per member
        </p>
      </div>
      
      <!-- How it works -->
      <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
        <h3 class="font-bold mb-3 text-sm">How Performance Bonuses Work:</h3>
        <div class="grid grid-cols-1 gap-2 text-xs text-left">
          <div class="flex items-start gap-2">
            <span class="text-green-400 mt-0.5">✓</span>
            <span>Earn when downlines purchase products or pay subscriptions</span>
          </div>
          <div class="flex items-start gap-2">
            <span class="text-green-400 mt-0.5">✓</span>
            <span>Get bonuses from workshop participation and learning resources</span>
          </div>
          <div class="flex items-start gap-2">
            <span class="text-green-400 mt-0.5">✓</span>
            <span>Paid monthly based on network activity</span>
          </div>
          <div class="flex items-start gap-2">
            <span class="text-green-400 mt-0.5">✓</span>
            <span>Commission rates: 15%, 10%, 8%, 5%, 3%, 2%, 2% (7 levels)</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface CommissionRate {
  level: number;
  name: string;
  rate: number;
  positions: number;
}

const props = defineProps<{
  commissionRates?: CommissionRate[] | number[];
}>();

// Default commission rates
const defaultRates = [15, 10, 8, 5, 3, 2, 2];

// Extract rate numbers from the commission rates
const rateNumbers = computed(() => {
  if (!props.commissionRates || props.commissionRates.length === 0) {
    return defaultRates;
  }
  
  // Check if it's an array of objects or numbers
  const firstItem = props.commissionRates[0];
  if (typeof firstItem === 'object' && 'rate' in firstItem) {
    // Array of objects with rate property
    return (props.commissionRates as CommissionRate[]).map(r => r.rate);
  } else {
    // Array of numbers
    return props.commissionRates as number[];
  }
});

// Calculate income examples based on 3x3 matrix
// Assuming average K120 monthly purchase/subscription per member
const incomeExamples = computed(() => {
  const avgPerMember = 120; // Average monthly purchase/subscription per member
  const matrixStructure = [3, 9, 27, 81, 243, 729, 2187]; // 3x3 matrix levels
  
  return rateNumbers.value.map((rate, index) => {
    const members = matrixStructure[index] || 0;
    const totalActivity = members * avgPerMember;
    const bonus = (totalActivity * rate) / 100;
    
    return {
      level: index + 1,
      rate,
      members,
      avgPerMember,
      bonus
    };
  });
});

// Calculate total monthly bonus
const totalMonthlyBonus = computed(() => {
  return incomeExamples.value.reduce((sum, example) => sum + example.bonus, 0);
});

// Format bonus amount
const formatBonus = (amount: number): string => {
  if (amount === undefined || amount === null || isNaN(amount)) {
    return '0.00';
  }
  return Number(amount).toFixed(2);
};

// Get color for each level
const getLevelColor = (index: number) => {
  const colors = [
    'from-blue-500 to-blue-700',      // Level 1
    'from-indigo-500 to-indigo-700',  // Level 2
    'from-purple-500 to-purple-700',  // Level 3
    'from-pink-500 to-pink-700',      // Level 4
    'from-rose-500 to-rose-700',      // Level 5
    'from-orange-500 to-orange-700',  // Level 6
    'from-amber-500 to-amber-700',    // Level 7
  ];
  return colors[index] || 'from-gray-400 to-gray-600';
};

// Get description for each level
const getLevelDescription = (index: number) => {
  const descriptions = [
    'Direct referrals',
    'Second level',
    'Third level',
    'Fourth level',
    'Fifth level',
    'Sixth level',
    'Seventh level',
  ];
  return descriptions[index] || `Level ${index + 1}`;
};
</script>
