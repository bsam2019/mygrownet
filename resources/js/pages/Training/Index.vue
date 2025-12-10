<template>
  <GuestLayout title="Training & Learning Hub">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-800 py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <h1 class="text-4xl font-extrabold text-white sm:text-5xl md:text-6xl">
            Develop Your Skills
          </h1>
          <p class="mt-4 text-xl text-blue-100 max-w-3xl mx-auto">
            Access courses, workshops, and business training that help you build real-world capabilities
          </p>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Value Proposition Section -->
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
          Why Training Matters
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
              <AcademicCapIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Practical Skills</h3>
            <p class="text-gray-600">
              Learn business skills you can apply immediately to grow your venture
            </p>
          </div>

          <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
              <ChartBarIcon class="h-8 w-8 text-green-600" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Business Growth</h3>
            <p class="text-gray-600">
              Develop capabilities that directly impact your business success
            </p>
          </div>

          <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
              <TrophyIcon class="h-8 w-8 text-purple-600" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Career Advancement</h3>
            <p class="text-gray-600">
              Build expertise that opens doors to new opportunities
            </p>
          </div>
        </div>
      </section>

      <!-- Available Training Modules Section -->
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
          Available Training Modules
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Financial Literacy -->
          <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
              <div class="flex items-center">
                <CurrencyDollarIcon class="h-8 w-8 text-white mr-3" aria-hidden="true" />
                <h3 class="text-xl font-bold text-white">Financial Literacy</h3>
              </div>
            </div>
            <div class="p-6">
              <p class="text-gray-600 mb-4">
                Master personal and business finance fundamentals
              </p>
              <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Budgeting and cash flow management</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Understanding financial statements</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Basic accounting principles</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Financial planning strategies</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Business Management -->
          <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
              <div class="flex items-center">
                <BriefcaseIcon class="h-8 w-8 text-white mr-3" aria-hidden="true" />
                <h3 class="text-xl font-bold text-white">Business Management</h3>
              </div>
            </div>
            <div class="p-6">
              <p class="text-gray-600 mb-4">
                Learn to run and grow a successful business
              </p>
              <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Business planning and strategy</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Operations and process management</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Team leadership and delegation</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Performance measurement</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Marketing & Sales -->
          <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
              <div class="flex items-center">
                <MegaphoneIcon class="h-8 w-8 text-white mr-3" aria-hidden="true" />
                <h3 class="text-xl font-bold text-white">Marketing & Sales</h3>
              </div>
            </div>
            <div class="p-6">
              <p class="text-gray-600 mb-4">
                Attract customers and grow your revenue
              </p>
              <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Digital marketing fundamentals</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Social media marketing strategies</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Sales techniques and closing</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Customer relationship management</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Personal Development -->
          <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
              <div class="flex items-center">
                <SparklesIcon class="h-8 w-8 text-white mr-3" aria-hidden="true" />
                <h3 class="text-xl font-bold text-white">Personal Development</h3>
              </div>
            </div>
            <div class="p-6">
              <p class="text-gray-600 mb-4">
                Develop the mindset and habits of successful entrepreneurs
              </p>
              <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Goal setting and achievement</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Time management and productivity</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Communication and networking</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Resilience and problem-solving</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- Member-Only Content Section -->
      <section class="mb-16 bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg p-8 border border-amber-200">
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-100 rounded-full mb-4">
            <LockClosedIcon class="h-8 w-8 text-amber-600" aria-hidden="true" />
          </div>
          <h2 class="text-3xl font-bold text-gray-900 mb-4">
            Member-Only Content
          </h2>
          <p class="text-gray-600 max-w-2xl mx-auto">
            Join MyGrowNet to unlock premium training modules, live webinars, and exclusive workshops
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white rounded-lg p-6 text-center">
            <VideoIcon class="h-12 w-12 text-blue-600 mx-auto mb-4" aria-hidden="true" />
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Video Courses</h3>
            <p class="text-gray-600 text-sm">
              Step-by-step video tutorials on key business topics
            </p>
          </div>

          <div class="bg-white rounded-lg p-6 text-center">
            <UsersIcon class="h-12 w-12 text-green-600 mx-auto mb-4" aria-hidden="true" />
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Live Webinars</h3>
            <p class="text-gray-600 text-sm">
              Interactive sessions with industry experts
            </p>
          </div>

          <div class="bg-white rounded-lg p-6 text-center">
            <DocumentTextIcon class="h-12 w-12 text-purple-600 mx-auto mb-4" aria-hidden="true" />
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Resource Library</h3>
            <p class="text-gray-600 text-sm">
              Templates, guides, and tools for your business
            </p>
          </div>
        </div>

        <div class="text-center mt-8">
          <Link
            :href="route('starter-kits')"
            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition"
          >
            Join to Access Premium Content
            <ArrowRightIcon class="ml-2 h-5 w-5" aria-hidden="true" />
          </Link>
        </div>
      </section>

      <!-- Training Progress Tracking -->
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
          Track Your Progress
        </h2>
        
        <div class="bg-white rounded-lg shadow-lg p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-4">
                Earn Certificates
              </h3>
              <p class="text-gray-600 mb-4">
                Complete courses and earn certificates to showcase your skills and knowledge
              </p>
              <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Professional certificates for completed courses</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Track completion percentage and progress</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Share achievements with your network</span>
                </li>
              </ul>
            </div>

            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-4">
                Learning Dashboard
              </h3>
              <p class="text-gray-600 mb-4">
                Monitor your learning journey with detailed analytics and insights
              </p>
              <ul class="space-y-2 text-sm text-gray-700">
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>View completed and in-progress courses</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Get personalized course recommendations</span>
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span>Set learning goals and track achievements</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- Future Training Expansion Section -->
      <section class="mb-16 bg-blue-50 rounded-lg p-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-4 text-center">
          Coming Soon
        </h2>
        <p class="text-center text-gray-600 mb-8 max-w-3xl mx-auto">
          We're continuously expanding our training library with new courses and content
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="bg-white rounded-lg p-6 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-3">
              <CodeBracketIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Tech Skills</h3>
            <p class="text-sm text-gray-600">Digital tools and software training</p>
          </div>

          <div class="bg-white rounded-lg p-6 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-3">
              <BuildingStorefrontIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">E-Commerce</h3>
            <p class="text-sm text-gray-600">Online selling and marketplace strategies</p>
          </div>

          <div class="bg-white rounded-lg p-6 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 rounded-full mb-3">
              <PresentationChartLineIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Advanced Analytics</h3>
            <p class="text-sm text-gray-600">Data-driven decision making</p>
          </div>

          <div class="bg-white rounded-lg p-6 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-full mb-3">
              <GlobeAltIcon class="h-6 w-6 text-indigo-600" aria-hidden="true" />
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">International Trade</h3>
            <p class="text-sm text-gray-600">Export and import fundamentals</p>
          </div>
        </div>
      </section>

      <!-- Final CTA -->
      <section class="bg-gradient-to-r from-blue-600 to-indigo-800 rounded-lg shadow-xl p-12 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">
          Start Learning Today
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
          Join MyGrowNet and gain access to comprehensive business training that helps you succeed
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Link
            :href="route('starter-kits')"
            class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-lg font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 transition"
          >
            View Starter Kits
            <ArrowRightIcon class="ml-2 h-5 w-5" aria-hidden="true" />
          </Link>
          <Link
            :href="route('about')"
            class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-lg font-medium rounded-md text-white hover:bg-white hover:text-blue-700 transition"
          >
            Learn More About Us
          </Link>
        </div>
      </section>
    </div>
  </GuestLayout>
</template>

<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Link } from '@inertiajs/vue3';
import {
  AcademicCapIcon,
  ChartBarIcon,
  TrophyIcon,
  CurrencyDollarIcon,
  BriefcaseIcon,
  MegaphoneIcon,
  SparklesIcon,
  CheckCircleIcon,
  LockClosedIcon,
  PlayCircleIcon,
  UsersIcon,
  DocumentTextIcon,
  ArrowRightIcon,
  CodeBracketIcon,
  BuildingStorefrontIcon,
  PresentationChartLineIcon,
  GlobeAltIcon,
} from '@heroicons/vue/24/outline';
</script>
