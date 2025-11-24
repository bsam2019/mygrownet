<template>
  <GuestLayout>
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 text-white">
      <div class="absolute inset-0 bg-grid-white/[0.05] bg-[size:20px_20px]"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <h1 class="text-5xl md:text-6xl font-bold mb-6">
            Invest in Community Empowerment
          </h1>
          <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
            Join us in building Zambia's leading community growth platform. 
            Sustainable revenue, proven model, real impact.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button
              v-if="activeRound"
              @click="scrollToOpportunity"
              class="px-8 py-4 bg-white text-blue-900 font-bold rounded-lg hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl"
            >
              View Investment Opportunity
            </button>
            <button
              @click="scrollToContact"
              class="px-8 py-4 border-2 border-white text-white font-bold rounded-lg hover:bg-white/10 transition-all"
            >
              Schedule a Meeting
            </button>
            <Link
              :href="route('investor.login')"
              class="px-8 py-4 border-2 border-white text-white font-bold rounded-lg hover:bg-white/10 transition-all text-center"
            >
              Investor Login
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Key Metrics Section -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Platform Performance</h2>
          <p class="text-lg text-gray-600">Real-time metrics demonstrating sustainable growth</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <MetricCard
            title="Total Members"
            :value="props.metrics.totalMembers"
            trend="+12.5%"
            trendDirection="up"
            icon="users"
          />
          <MetricCard
            title="Monthly Revenue"
            :value="`K${props.metrics.monthlyRevenue.toLocaleString()}`"
            trend="+18.3%"
            trendDirection="up"
            icon="currency"
          />
          <MetricCard
            title="Active Rate"
            :value="`${props.metrics.activeRate}%`"
            trend="+2.1%"
            trendDirection="up"
            icon="activity"
          />
          <MetricCard
            title="Retention"
            :value="`${props.metrics.retention}%`"
            trend="+1.5%"
            trendDirection="up"
            icon="retention"
          />
        </div>
      </div>
    </section>

    <!-- Growth Chart Section -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-8">
          <h3 class="text-2xl font-bold text-gray-900 mb-6">Revenue Growth Trajectory</h3>
          <div class="h-80">
            <canvas ref="revenueChart"></canvas>
          </div>
        </div>
      </div>
    </section>

    <!-- Value Proposition -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Invest in MyGrowNet?</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <ValueCard
            title="Proven Business Model"
            description="Subscription-based revenue with multiple income streams. Not an investment scheme - a legitimate education and empowerment platform."
            icon="chart-bar"
          />
          <ValueCard
            title="Market Opportunity"
            description="Zambia's growing middle class seeking skills development and income opportunities. First-mover advantage in community empowerment space."
            icon="globe"
          />
          <ValueCard
            title="Sustainable Growth"
            description="94% retention rate, positive unit economics, and clear path to profitability. Strong network effects drive organic growth."
            icon="trending-up"
          />
          <ValueCard
            title="Legal Compliance"
            description="Fully registered private limited company. Compliant with Bank of Zambia regulations. Transparent operations and governance."
            icon="shield-check"
          />
          <ValueCard
            title="Social Impact"
            description="Empowering communities through education, skills training, and income opportunities. Measurable positive impact on members' lives."
            icon="heart"
          />
          <ValueCard
            title="Experienced Team"
            description="Leadership with proven track record in technology, education, and community development. Advisory board with industry expertise."
            icon="user-group"
          />
        </div>
      </div>
    </section>

    <!-- Revenue Breakdown -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="bg-white rounded-xl shadow-lg p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Revenue Streams</h3>
            <div class="space-y-4">
              <RevenueStream
                name="Subscription Fees"
                percentage="45"
                amount="K64,350"
              />
              <RevenueStream
                name="Learning Packs"
                percentage="25"
                amount="K35,750"
              />
              <RevenueStream
                name="Workshops & Training"
                percentage="15"
                amount="K21,450"
              />
              <RevenueStream
                name="Venture Builder Fees"
                percentage="10"
                amount="K14,300"
              />
              <RevenueStream
                name="Other Services"
                percentage="5"
                amount="K7,150"
              />
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-lg p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Unit Economics</h3>
            <div class="space-y-6">
              <UnitEconomic
                label="Average Revenue Per User"
                value="K50/month"
                status="positive"
              />
              <UnitEconomic
                label="Customer Acquisition Cost"
                value="K75"
                status="neutral"
              />
              <UnitEconomic
                label="Lifetime Value"
                value="K1,800"
                status="positive"
              />
              <UnitEconomic
                label="LTV:CAC Ratio"
                value="24:1"
                status="positive"
                note="Excellent (target >3:1)"
              />
              <UnitEconomic
                label="Payback Period"
                value="1.5 months"
                status="positive"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Investment Opportunity -->
    <section v-if="activeRound" id="opportunity" class="py-16 bg-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl shadow-xl p-8 md:p-12 border border-blue-100">
          <div class="text-center mb-8">
            <span class="inline-block px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-full mb-4">
              {{ activeRound.name }}
            </span>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Current Investment Opportunity</h2>
            <p class="text-lg text-gray-600">
              {{ activeRound.description }}
            </p>
          </div>

          <div class="mb-8">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
              <span>Progress</span>
              <span class="font-semibold">K{{ activeRound.raisedAmount.toLocaleString() }} raised ({{ activeRound.progressPercentage }}%)</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
              <div class="bg-gradient-to-r from-blue-600 to-indigo-600 h-4 rounded-full transition-all" :style="`width: ${activeRound.progressPercentage}%`"></div>
            </div>
          </div>

          <!-- Investment Structure -->
          <div class="bg-blue-50 rounded-lg p-6 mb-8 border border-blue-100">
            <h4 class="text-lg font-bold text-gray-900 mb-3">Investment Structure</h4>
            <p class="text-gray-700 mb-4">
              Convertible Investment Units (CIUs) - Your investment converts to equity shares when the company reaches key milestones.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
              <div>
                <span class="text-gray-600">Structure:</span>
                <p class="font-semibold text-gray-900">Convertible Units</p>
              </div>
              <div>
                <span class="text-gray-600">Investor Pool:</span>
                <p class="font-semibold text-gray-900">20-30% of shares</p>
              </div>
              <div>
                <span class="text-gray-600">Conversion:</span>
                <p class="font-semibold text-gray-900">At milestone/funding</p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg p-6 shadow-sm">
              <h4 class="text-sm font-semibold text-gray-500 mb-2">Minimum Investment</h4>
              <p class="text-2xl font-bold text-gray-900">K{{ activeRound.minimumInvestment.toLocaleString() }}</p>
              <p class="text-xs text-gray-500 mt-1">Per investor</p>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-sm">
              <h4 class="text-sm font-semibold text-gray-500 mb-2">Target Raise</h4>
              <p class="text-2xl font-bold text-gray-900">K{{ activeRound.goalAmount.toLocaleString() }}</p>
              <p class="text-xs text-gray-500 mt-1">Total funding goal</p>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-sm">
              <h4 class="text-sm font-semibold text-gray-500 mb-2">Valuation Cap</h4>
              <p class="text-2xl font-bold text-gray-900">K{{ (activeRound.valuation / 1000000).toFixed(1) }}M</p>
              <p class="text-xs text-gray-500 mt-1">Maximum conversion valuation</p>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-sm">
              <h4 class="text-sm font-semibold text-gray-500 mb-2">Expected Return</h4>
              <p class="text-2xl font-bold text-gray-900">{{ activeRound.expectedRoi }}</p>
              <p class="text-xs text-gray-500 mt-1">Projected ROI</p>
            </div>
          </div>

          <!-- Pre-Conversion Benefits -->
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-6 mb-8 border border-green-100">
            <h4 class="text-lg font-bold text-gray-900 mb-3">Pre-Conversion Benefits</h4>
            <p class="text-gray-700 mb-4">
              Before your investment converts to shares, you receive:
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-start gap-3">
                <span class="text-green-600 text-xl">✓</span>
                <div>
                  <p class="font-semibold text-gray-900">Profit Sharing</p>
                  <p class="text-sm text-gray-600">Quarterly distribution of net profits</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <span class="text-green-600 text-xl">✓</span>
                <div>
                  <p class="font-semibold text-gray-900">Advisory Rights</p>
                  <p class="text-sm text-gray-600">Vote on strategic decisions</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <span class="text-green-600 text-xl">✓</span>
                <div>
                  <p class="font-semibold text-gray-900">Priority Access</p>
                  <p class="text-sm text-gray-600">Early access to new features</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <span class="text-green-600 text-xl">✓</span>
                <div>
                  <p class="font-semibold text-gray-900">Regular Updates</p>
                  <p class="text-sm text-gray-600">Quarterly performance reports</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg p-6 shadow-sm mb-8">
            <h4 class="text-lg font-bold text-gray-900 mb-4">Use of Funds</h4>
            <div class="space-y-3">
              <UseFund 
                v-for="(fund, index) in activeRound.useOfFunds" 
                :key="index"
                :label="fund.label" 
                :percentage="fund.percentage.toString()" 
                :amount="`K${fund.amount.toLocaleString()}`" 
              />
            </div>
          </div>

          <div class="text-center">
            <button
              @click="scrollToContact"
              class="px-8 py-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-all shadow-lg hover:shadow-xl"
            >
              Express Interest
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-16 bg-gray-50">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-8">
          <h2 class="text-3xl font-bold text-gray-900 mb-4 text-center">Schedule a Meeting</h2>
          <p class="text-gray-600 mb-8 text-center">
            Interested in learning more? Fill out the form below and we'll get back to you within 24 hours.
          </p>

          <form @submit.prevent="submitInquiry" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
              <input
                v-model="form.phone"
                type="tel"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Investment Interest</label>
              <select
                v-model="form.investmentRange"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select range</option>
                <option value="25-50">K25,000 - K50,000</option>
                <option value="50-100">K50,000 - K100,000</option>
                <option value="100-250">K100,000 - K250,000</option>
                <option value="250+">K250,000+</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
              <textarea
                v-model="form.message"
                rows="4"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              ></textarea>
            </div>

            <button
              type="submit"
              :disabled="submitting"
              class="w-full px-8 py-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ submitting ? 'Submitting...' : 'Submit Inquiry' }}
            </button>
          </form>
        </div>
      </div>
    </section>

  </GuestLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Chart, registerables } from 'chart.js'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import MetricCard from '@/components/Investor/MetricCard.vue'
import ValueCard from '@/components/Investor/ValueCard.vue'
import RevenueStream from '@/components/Investor/RevenueStream.vue'
import UnitEconomic from '@/components/Investor/UnitEconomic.vue'
import UseFund from '@/components/Investor/UseFund.vue'

Chart.register(...registerables)

// Props from backend
interface InvestmentRound {
  id: number
  name: string
  description: string
  goalAmount: number
  raisedAmount: number
  progressPercentage: number
  minimumInvestment: number
  valuation: number
  equityPercentage: number
  expectedRoi: string
  useOfFunds: Array<{
    label: string
    percentage: number
    amount: number
  }>
}

interface Props {
  metrics: {
    totalMembers: number
    monthlyRevenue: number
    activeRate: number
    retention: number
    revenueGrowth: {
      labels: string[]
      data: number[]
    }
  }
  investmentRound: InvestmentRound | null
}

const props = defineProps<Props>()

// Only use investment round from database - no mock data
const activeRound = props.investmentRound

const form = ref({
  name: '',
  email: '',
  phone: '',
  investmentRange: '',
  message: ''
})

const submitting = ref(false)
const revenueChart = ref<HTMLCanvasElement | null>(null)

const scrollToOpportunity = () => {
  document.getElementById('opportunity')?.scrollIntoView({ behavior: 'smooth' })
}

const scrollToContact = () => {
  document.getElementById('contact')?.scrollIntoView({ behavior: 'smooth' })
}

const submitInquiry = async () => {
  submitting.value = true
  
  try {
    // TODO: Implement API call to save inquiry
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    alert('Thank you for your interest! We will contact you within 24 hours.')
    
    // Reset form
    form.value = {
      name: '',
      email: '',
      phone: '',
      investmentRange: '',
      message: ''
    }
  } catch (error) {
    alert('An error occurred. Please try again.')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  if (revenueChart.value) {
    new Chart(revenueChart.value, {
      type: 'line',
      data: {
        labels: props.metrics.revenueGrowth.labels,
        datasets: [{
          label: 'Monthly Revenue (K)',
          data: props.metrics.revenueGrowth.data,
          borderColor: '#2563eb',
          backgroundColor: 'rgba(37, 99, 235, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value) => `K${value.toLocaleString()}`
            }
          }
        }
      }
    })
  }
})
</script>
