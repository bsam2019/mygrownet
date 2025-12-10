<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-emerald-50">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <Link :href="route('welcome')" class="flex items-center gap-2">
              <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                <BuildingOffice2Icon class="w-6 h-6 text-white" />
              </div>
              <span class="text-xl font-bold text-slate-800">GrowBiz</span>
            </Link>
          </div>
          <div class="flex items-center gap-4">
            <Link :href="route('login')" class="text-slate-600 hover:text-slate-900 font-medium">
              Sign In
            </Link>
            <Link :href="route('register')" class="bg-emerald-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-emerald-700 transition-colors">
              Get Started
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative py-20 overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <div>
            <div class="inline-flex items-center gap-2 bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full text-sm font-medium mb-6">
              <SparklesIcon class="w-4 h-4" />
              Business Management Made Simple
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-6 leading-tight">
              Run Your Business
              <span class="text-emerald-600">Smarter, Not Harder</span>
            </h1>
            <p class="text-xl text-slate-600 mb-8 leading-relaxed">
              GrowBiz is your all-in-one business management platform. Manage employees, track tasks, 
              handle communications, and grow your business with powerful yet simple tools.
            </p>
            <div class="flex flex-wrap gap-4">
              <Link :href="route('register')" class="bg-emerald-600 text-white px-8 py-4 rounded-xl font-semibold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-200">
                Start Free Trial
              </Link>
              <a href="#features" class="border-2 border-slate-300 text-slate-700 px-8 py-4 rounded-xl font-semibold hover:border-emerald-500 hover:text-emerald-600 transition-all">
                See Features
              </a>
            </div>
          </div>
          <div class="relative">
            <div class="bg-white rounded-2xl shadow-2xl p-6 border border-slate-200">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
              </div>
              <div class="space-y-4">
                <div class="bg-emerald-50 rounded-lg p-4">
                  <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-slate-700">Team Performance</span>
                    <span class="text-emerald-600 font-bold">92%</span>
                  </div>
                  <div class="w-full bg-emerald-200 rounded-full h-2">
                    <div class="bg-emerald-500 h-2 rounded-full" style="width: 92%"></div>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div class="bg-slate-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-slate-800">24</div>
                    <div class="text-sm text-slate-500">Active Tasks</div>
                  </div>
                  <div class="bg-slate-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-emerald-600">8</div>
                    <div class="text-sm text-slate-500">Team Members</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">
            Everything You Need to Manage Your Business
          </h2>
          <p class="text-xl text-slate-600 max-w-3xl mx-auto">
            From employee management to task tracking, GrowBiz has all the tools you need.
          </p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="feature in features" :key="feature.title" class="bg-slate-50 rounded-2xl p-6 hover:shadow-lg transition-shadow">
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-4">
              <component :is="feature.icon" class="w-6 h-6 text-emerald-600" />
            </div>
            <h3 class="text-xl font-semibold text-slate-800 mb-2">{{ feature.title }}</h3>
            <p class="text-slate-600">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-emerald-600 to-teal-600">
      <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
          Ready to Grow Your Business?
        </h2>
        <p class="text-xl text-emerald-100 mb-8">
          Join thousands of businesses already using GrowBiz to streamline their operations.
        </p>
        <Link :href="route('register')" class="inline-block bg-white text-emerald-600 px-8 py-4 rounded-xl font-semibold hover:bg-emerald-50 transition-colors shadow-lg">
          Start Your Free Trial
        </Link>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="flex items-center gap-2 mb-4 md:mb-0">
            <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center">
              <BuildingOffice2Icon class="w-5 h-5 text-white" />
            </div>
            <span class="font-bold">GrowBiz</span>
          </div>
          <p class="text-slate-400 text-sm">
            © {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import {
  BuildingOffice2Icon,
  SparklesIcon,
  UsersIcon,
  ClipboardDocumentListIcon,
  ChatBubbleLeftRightIcon,
  ChartBarIcon,
  CalendarDaysIcon,
  BellAlertIcon,
} from '@heroicons/vue/24/outline';

const features = [
  {
    title: 'Employee Management',
    description: 'Manage your team with ease. Track attendance, performance, and payroll all in one place.',
    icon: UsersIcon,
  },
  {
    title: 'Task Management',
    description: 'Create, assign, and track tasks. Keep your team organized and productive.',
    icon: ClipboardDocumentListIcon,
  },
  {
    title: 'Team Communication',
    description: 'Built-in messaging and notifications to keep everyone on the same page.',
    icon: ChatBubbleLeftRightIcon,
  },
  {
    title: 'Performance Analytics',
    description: 'Track KPIs and generate reports to make data-driven decisions.',
    icon: ChartBarIcon,
  },
  {
    title: 'Scheduling',
    description: 'Manage shifts, appointments, and deadlines with our intuitive calendar.',
    icon: CalendarDaysIcon,
  },
  {
    title: 'Smart Notifications',
    description: 'Stay informed with real-time alerts for important business events.',
    icon: BellAlertIcon,
  },
];
</script>
