<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
              <BuildingOfficeIcon class="h-6 w-6 text-white" aria-hidden="true" />
            </div>
            <div class="min-w-0">
              <h1 class="text-base sm:text-xl font-bold text-gray-900 truncate">GrowSuite</h1>
              <p class="text-xs text-gray-500 hidden sm:block">Business Management Suite</p>
            </div>
          </div>
          <div class="flex items-center gap-2 sm:gap-3">
            <Link
              :href="route('cms.login')"
              class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:text-gray-900 transition whitespace-nowrap"
            >
              Sign In
            </Link>
            <Link
              :href="route('cms.register')"
              class="px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-xs sm:text-sm font-medium whitespace-nowrap"
            >
              Get Started
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-12 sm:pt-16 pb-12 sm:pb-16 px-4 sm:px-6 lg:px-8 bg-gray-900">
      <!-- Background Image -->
      <div 
        class="absolute inset-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=2000&auto=format&fit=crop');"
      ></div>
      <!-- Overlay for text readability -->
      <div class="absolute inset-0 bg-gradient-to-br from-slate-900/95 via-blue-900/90 to-indigo-900/95"></div>
      
      <div class="max-w-7xl mx-auto relative z-10 w-full">
        <div class="text-center max-w-4xl mx-auto">
          <!-- Badge -->
          <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full mb-5">
            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
            <span class="text-sm text-white/90 font-medium">Trusted by 500+ businesses</span>
          </div>

          <!-- Main Heading -->
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4 leading-tight tracking-tight">
            Manage Your Business
            <br />
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-400 to-purple-400">
              Effortlessly
            </span>
          </h1>

          <!-- Subheading -->
          <p class="text-base sm:text-lg lg:text-xl text-gray-300 mb-6 max-w-3xl mx-auto leading-relaxed">
            Complete business management solution for SMEs. Track jobs, manage customers, handle invoices, and grow your business with powerful analytics.
          </p>

          <!-- CTA Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 justify-center mb-6">
            <Link
              :href="route('cms.register')"
              class="group px-8 py-3.5 bg-white text-gray-900 rounded-xl hover:bg-gray-100 transition-all duration-300 text-base font-semibold shadow-2xl hover:shadow-white/20 hover:scale-105 flex items-center justify-center gap-2"
            >
              Start Free Trial
              <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </Link>
            <a
              href="#features"
              class="px-8 py-3.5 bg-white/5 backdrop-blur-sm text-white rounded-xl hover:bg-white/10 transition-all duration-300 text-base font-semibold border-2 border-white/20 hover:border-white/40"
            >
              Learn More
            </a>
          </div>

          <!-- Trust Line -->
          <p class="text-xs sm:text-sm text-gray-400 flex items-center justify-center gap-4 sm:gap-6 flex-wrap mb-10">
            <span class="flex items-center gap-2">
              <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              No credit card required
            </span>
            <span class="flex items-center gap-2">
              <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              14-day free trial
            </span>
            <span class="flex items-center gap-2">
              <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Cancel anytime
            </span>
          </p>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 max-w-4xl mx-auto">
          <div class="text-center p-4 rounded-2xl bg-white/5 backdrop-blur-sm border border-white/10 hover:bg-white/10 transition-all duration-300">
            <div class="text-3xl sm:text-4xl font-bold text-white mb-1">500+</div>
            <div class="text-sm text-gray-300 font-medium">Active Businesses</div>
          </div>
          <div class="text-center p-4 rounded-2xl bg-white/5 backdrop-blur-sm border border-white/10 hover:bg-white/10 transition-all duration-300">
            <div class="text-3xl sm:text-4xl font-bold text-white mb-1">50K+</div>
            <div class="text-sm text-gray-300 font-medium">Jobs Managed</div>
          </div>
          <div class="text-center p-4 rounded-2xl bg-white/5 backdrop-blur-sm border border-white/10 hover:bg-white/10 transition-all duration-300">
            <div class="text-3xl sm:text-4xl font-bold text-white mb-1">99%</div>
            <div class="text-sm text-gray-300 font-medium">Satisfaction Rate</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h3 class="text-3xl font-bold text-gray-900 mb-4">Everything You Need to Run Your Business</h3>
          <p class="text-lg text-gray-600">Powerful features designed for growing businesses</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="feature in features"
            :key="feature.title"
            class="p-6 rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-lg transition"
          >
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
              <component :is="feature.icon" class="h-6 w-6 text-blue-600" aria-hidden="true" />
            </div>
            <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ feature.title }}</h4>
            <p class="text-gray-600">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h3 class="text-3xl font-bold text-gray-900 mb-4">Simple, Transparent Pricing</h3>
          <p class="text-lg text-gray-600">Choose the plan that fits your business</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
          <div
            v-for="plan in pricingPlans"
            :key="plan.name"
            :class="[
              'p-8 rounded-2xl border-2',
              plan.featured
                ? 'border-blue-600 shadow-xl scale-105'
                : 'border-gray-200 bg-white'
            ]"
          >
            <div v-if="plan.featured" class="text-center mb-4">
              <span class="px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full">
                Most Popular
              </span>
            </div>
            <h4 class="text-2xl font-bold text-gray-900 mb-2">{{ plan.name }}</h4>
            <div class="mb-6">
              <span class="text-4xl font-bold text-gray-900">K{{ plan.price }}</span>
              <span class="text-gray-600">/month</span>
            </div>
            <ul class="space-y-3 mb-8">
              <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                <span class="text-gray-600">{{ feature }}</span>
              </li>
            </ul>
            <Link
              :href="route('cms.register')"
              :class="[
                'block w-full py-3 rounded-lg text-center font-semibold transition',
                plan.featured
                  ? 'bg-blue-600 text-white hover:bg-blue-700'
                  : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
              ]"
            >
              Get Started
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-blue-600 to-indigo-600">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h3 class="text-4xl font-bold text-white mb-6">
          Ready to Transform Your Business?
        </h3>
        <p class="text-xl text-blue-100 mb-8">
          Join hundreds of businesses already using GrowSuite
        </p>
        <Link
          :href="route('cms.register')"
          class="inline-block px-8 py-4 bg-white text-blue-600 rounded-lg hover:bg-gray-100 transition text-lg font-semibold shadow-lg"
        >
          Start Your Free Trial
        </Link>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div>
            <div class="flex items-center gap-2 mb-4">
              <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <BuildingOfficeIcon class="h-5 w-5 text-white" aria-hidden="true" />
              </div>
              <span class="text-white font-semibold">GrowSuite</span>
            </div>
            <p class="text-sm">
              Professional business management for growing companies.
            </p>
          </div>
          <div>
            <h5 class="text-white font-semibold mb-4">Product</h5>
            <ul class="space-y-2 text-sm">
              <li><a href="#features" class="hover:text-white transition">Features</a></li>
              <li><a href="#pricing" class="hover:text-white transition">Pricing</a></li>
              <li><a href="#" class="hover:text-white transition">Documentation</a></li>
            </ul>
          </div>
          <div>
            <h5 class="text-white font-semibold mb-4">Company</h5>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition">About</a></li>
              <li><a href="#" class="hover:text-white transition">Contact</a></li>
              <li><a href="#" class="hover:text-white transition">Support</a></li>
            </ul>
          </div>
          <div>
            <h5 class="text-white font-semibold mb-4">Legal</h5>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition">Privacy</a></li>
              <li><a href="#" class="hover:text-white transition">Terms</a></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm">
          <p>&copy; 2026 GrowSuite by MyGrowNet. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import {
  BuildingOfficeIcon,
  BriefcaseIcon,
  UsersIcon,
  DocumentTextIcon,
  ChartBarIcon,
  CreditCardIcon,
  CubeIcon,
  CheckIcon,
  BanknotesIcon,
  ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline'

const features = [
  {
    icon: BriefcaseIcon,
    title: 'Job Management',
    description: 'Track projects from quote to completion with status updates and deadlines.',
  },
  {
    icon: UsersIcon,
    title: 'Customer Management',
    description: 'Maintain detailed customer records, contacts, and transaction history.',
  },
  {
    icon: DocumentTextIcon,
    title: 'Invoicing',
    description: 'Create professional invoices, track payments, and manage receivables.',
  },
  {
    icon: CreditCardIcon,
    title: 'Payment Tracking',
    description: 'Record payments, manage credits, and generate receipts automatically.',
  },
  {
    icon: ChartBarIcon,
    title: 'Analytics & Reports',
    description: 'Real-time insights into revenue, expenses, and business performance.',
  },
  {
    icon: CubeIcon,
    title: 'Inventory Management',
    description: 'Track stock levels, movements, and get low-stock alerts.',
  },
  {
    icon: BanknotesIcon,
    title: 'Expense Tracking',
    description: 'Monitor business expenses with categories and approval workflows.',
  },
  {
    icon: ClipboardDocumentListIcon,
    title: 'Quotations',
    description: 'Create and send professional quotes that convert to jobs.',
  },
  {
    icon: BuildingOfficeIcon,
    title: 'Multi-User Access',
    description: 'Team collaboration with role-based permissions and access control.',
  },
]

const pricingPlans = [
  {
    name: 'Starter',
    price: 99,
    featured: false,
    features: [
      'Up to 50 customers',
      'Unlimited jobs',
      'Basic invoicing',
      'Payment tracking',
      'Email support',
    ],
  },
  {
    name: 'Professional',
    price: 249,
    featured: true,
    features: [
      'Unlimited customers',
      'Unlimited jobs',
      'Advanced invoicing',
      'Inventory management',
      'Analytics & reports',
      'Priority support',
      'Multi-user access',
    ],
  },
  {
    name: 'Enterprise',
    price: 499,
    featured: false,
    features: [
      'Everything in Professional',
      'Custom workflows',
      'API access',
      'Dedicated support',
      'Custom integrations',
      'Training & onboarding',
    ],
  },
]
</script>

