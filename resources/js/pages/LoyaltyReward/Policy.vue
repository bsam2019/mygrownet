<template>
  <GuestLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <!-- Back Button (Mobile) -->
        <button
          @click="$inertia.visit(route('home'))"
          class="mb-4 flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 md:hidden"
        >
          <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Home
        </button>
        
        <!-- Header -->
        <div class="mb-8 rounded-lg border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-8 shadow-sm">
          <h1 class="mb-4 text-3xl font-bold text-gray-900">
            Loyalty Growth Reward Policy
          </h1>
          <p class="mb-3 leading-relaxed text-gray-700">
            <strong>Welcome to the Loyalty Growth Reward (LGR) Program!</strong> This is our way of appreciating your active participation, 
            learning, and community growth within MyGrowNet. This policy explains how the program works and how you can earn rewards.
          </p>
          <p class="text-sm text-gray-600">
            Last Updated: October 31, 2025 | Effective Date: January 2026
          </p>
        </div>

        <!-- Content -->
        <div class="space-y-8 rounded-lg bg-white p-8 shadow-sm">
          
          <!-- What It Is -->
          <section>
            <h2 class="mb-4 text-2xl font-semibold text-gray-900">1. What is the Loyalty Growth Reward?</h2>
            <p class="mb-4 text-gray-700">
              The LGR is a performance-based loyalty program that rewards you for staying active on the platform. 
              It's designed to encourage learning, networking, and consistent engagement.
            </p>
            
            <div class="grid gap-4 md:grid-cols-2">
              <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                <h3 class="mb-2 flex items-center font-semibold text-green-900">
                  <svg class="mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  What It IS
                </h3>
                <ul class="space-y-1 text-sm text-green-800">
                  <li>✓ Activity-based rewards</li>
                  <li>✓ Performance incentive</li>
                  <li>✓ Loyalty appreciation</li>
                  <li>✓ Platform engagement driver</li>
                </ul>
              </div>

              <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                <h3 class="mb-2 flex items-center font-semibold text-red-900">
                  <svg class="mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  What It's NOT
                </h3>
                <ul class="space-y-1 text-sm text-red-800">
                  <li>✗ NOT an investment</li>
                  <li>✗ NOT guaranteed income</li>
                  <li>✗ NOT passive earnings</li>
                  <li>✗ NOT a fixed obligation</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- How to Qualify -->
          <section>
            <h2 class="mb-4 text-2xl font-semibold text-gray-900">2. How to Qualify</h2>
            <p class="mb-4 text-gray-700">
              To start your 70-day reward cycle, you must complete ALL four requirements:
            </p>
            
            <div class="space-y-3">
              <div class="flex items-start rounded-lg border border-blue-100 bg-blue-50 p-4">
                <div class="mr-3 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-600 text-white font-bold">1</div>
                <div>
                  <h3 class="font-semibold text-blue-900">Purchase K1,000 Starter Package</h3>
                  <p class="text-sm text-blue-800">Access learning materials and platform tools</p>
                </div>
              </div>

              <div class="flex items-start rounded-lg border border-blue-100 bg-blue-50 p-4">
                <div class="mr-3 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-600 text-white font-bold">2</div>
                <div>
                  <h3 class="font-semibold text-blue-900">Complete Business Fundamentals Training</h3>
                  <p class="text-sm text-blue-800">Essential business knowledge course</p>
                </div>
              </div>

              <div class="flex items-start rounded-lg border border-blue-100 bg-blue-50 p-4">
                <div class="mr-3 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-600 text-white font-bold">3</div>
                <div>
                  <h3 class="font-semibold text-blue-900">Build First-Level Team (3 Members)</h3>
                  <p class="text-sm text-blue-800">All 3 must complete registration and activate Starter Package</p>
                </div>
              </div>

              <div class="flex items-start rounded-lg border border-blue-100 bg-blue-50 p-4">
                <div class="mr-3 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-600 text-white font-bold">4</div>
                <div>
                  <h3 class="font-semibold text-blue-900">Complete 2 Platform Activities</h3>
                  <p class="text-sm text-blue-800">Learning modules, marketplace transactions, events, or community engagement</p>
                </div>
              </div>
            </div>
          </section>

          <!-- How It Works -->
          <section>
            <h2 class="mb-4 text-2xl font-semibold text-gray-900">3. How the Reward Works</h2>
            
            <div class="mb-4 rounded-lg border border-indigo-200 bg-indigo-50 p-6">
              <div class="grid gap-4 md:grid-cols-3">
                <div class="text-center">
                  <div class="mb-2 text-3xl font-bold text-indigo-600">70 Days</div>
                  <div class="text-sm text-indigo-800">Cycle Duration</div>
                </div>
                <div class="text-center">
                  <div class="mb-2 text-3xl font-bold text-indigo-600">K30/Day</div>
                  <div class="text-sm text-indigo-800">For Active Days</div>
                </div>
                <div class="text-center">
                  <div class="mb-2 text-3xl font-bold text-indigo-600">K2,100</div>
                  <div class="text-sm text-indigo-800">Maximum Earnings</div>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div class="rounded-lg bg-yellow-50 p-4">
                <h3 class="mb-2 font-medium text-yellow-900">📅 Active Day = Earn K30</h3>
                <p class="text-sm text-yellow-800">
                  A day counts as "active" when you complete at least ONE activity: learning module, 
                  marketplace transaction, event attendance, or community engagement.
                </p>
              </div>

              <div class="rounded-lg bg-gray-50 p-4">
                <h3 class="mb-2 font-medium text-gray-900">❌ No Activity = No Reward</h3>
                <p class="text-sm text-gray-700">
                  If you don't complete any activity on a day, you won't earn for that day. 
                  The cycle continues, but you only get paid for days you're active.
                </p>
              </div>
            </div>

            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-4">
              <h3 class="mb-2 font-semibold text-green-900">Example Calculation:</h3>
              <div class="space-y-2 text-sm text-green-800">
                <p>• Active for 55 of 70 days: <strong>55 × K30 = K1,650</strong></p>
                <p>• Active for 70 of 70 days: <strong>70 × K30 = K2,100 (maximum)</strong></p>
              </div>
            </div>
          </section>

          <!-- Loyalty Credits -->
          <section>
            <h2 class="mb-4 text-2xl font-semibold text-gray-900">4. Loyalty Growth Credits (LGC)</h2>
            <p class="mb-4 text-gray-700">
              Your rewards are paid as Loyalty Growth Credits (LGC) stored in your MyGrowNet Wallet.
            </p>

            <div class="grid gap-4 md:grid-cols-2">
              <div class="rounded-lg border border-purple-200 bg-purple-50 p-4">
                <h3 class="mb-2 font-medium text-purple-900">💎 Use on Platform (100%)</h3>
                <ul class="space-y-1 text-sm text-purple-800">
                  <li>• Buy products and subscriptions</li>
                  <li>• Pay for training and workshops</li>
                  <li>• Invest in Venture Builder projects</li>
                  <li>• Apply for Business Growth Fund</li>
                </ul>
              </div>

              <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                <h3 class="mb-2 font-medium text-blue-900">💵 Convert to Cash (Max 40%)</h3>
                <ul class="space-y-1 text-sm text-blue-800">
                  <li>• Withdraw up to 40% as cash</li>
                  <li>• Minimum withdrawal: K100</li>
                  <li>• Processing: 3-5 business days</li>
                  <li>• To mobile money or bank</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Pool-Based Distribution -->
          <section>
            <h2 class="mb-4 text-2xl font-semibold text-gray-900">5. Pool-Based Distribution</h2>
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-6">
              <p class="mb-3 text-amber-900">
                <strong>Important:</strong> LGR rewards come from a dedicated reward pool funded by platform revenue 
                (registration fees, product sales, marketplace fees, etc.).
              </p>
              <p class="text-sm text-amber-800">
                If demand exceeds available pool funds, rewards are distributed proportionally to all qualifying members. 
                This ensures MyGrowNet never overpays and the system remains sustainable.
              </p>
            </div>
          </section>

          <!-- Your Responsibilities -->
          <section>
            <h2 class="mb-4 text-2xl font-semibold text-gray-900">6. Your Responsibilities</h2>
            <div class="space-y-2">
              <div class="flex items-start">
                <svg class="mr-2 mt-1 h-5 w-5 flex-shrink-0 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-gray-700">Stay active on the platform daily</p>
              </div>
              <div class="flex items-start">
                <svg class="mr-2 mt-1 h-5 w-5 flex-shrink-0 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-gray-700">Complete genuine activities (no gaming the system)</p>
              </div>
              <div class="flex items-start">
                <svg class="mr-2 mt-1 h-5 w-5 flex-shrink-0 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-gray-700">Maintain good standing (no policy violations)</p>
              </div>
              <div class="flex items-start">
                <svg class="mr-2 mt-1 h-5 w-5 flex-shrink-0 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-gray-700">Comply with all MyGrowNet Terms of Service</p>
              </div>
            </div>
          </section>

          <!-- Suspension & Termination -->
          <section>
            <h2 class="mb-4 text-2xl font-semibold text-gray-900">7. Suspension & Termination</h2>
            <div class="rounded-lg border border-red-200 bg-red-50 p-4">
              <p class="mb-2 font-medium text-red-900">Your LGR participation may be suspended or terminated if you:</p>
              <ul class="space-y-1 text-sm text-red-800">
                <li>• Engage in fraudulent activities</li>
                <li>• Create fake activities to game the system</li>
                <li>• Use multiple accounts</li>
                <li>• Violate MyGrowNet Terms of Service</li>
                <li>• Are inactive for more than 15 consecutive days</li>
              </ul>
            </div>
          </section>

          <!-- Contact -->
          <section>
            <h2 class="mb-4 text-2xl font-semibold text-gray-900">8. Questions or Issues?</h2>
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-6">
              <p class="mb-3 text-blue-900">
                If you have questions about the LGR program or need support:
              </p>
              <div class="space-y-2 text-sm text-blue-800">
                <p><strong>Email:</strong> support@mygrownet.com</p>
                <p><strong>Hours:</strong> Monday-Friday, 8:00 AM - 5:00 PM CAT</p>
                <p><strong>Response Time:</strong> 24-48 hours</p>
              </div>
            </div>
          </section>

          <!-- Acceptance -->
          <section class="rounded-lg border-2 border-indigo-300 bg-indigo-50 p-6">
            <h2 class="mb-4 text-xl font-semibold text-indigo-900">By Participating in LGR, You Agree To:</h2>
            <ul class="space-y-2 text-sm text-indigo-800">
              <li>✓ Understand that LGR is activity-based, not guaranteed income</li>
              <li>✓ Accept pool-based distribution model</li>
              <li>✓ Use LGC according to platform rules</li>
              <li>✓ Maintain active and genuine platform engagement</li>
              <li>✓ Comply with all terms and conditions</li>
            </ul>
          </section>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
          <Link
            :href="route('dashboard')"
            class="text-blue-600 hover:text-blue-800 underline"
          >
            ← Back to Dashboard
          </Link>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';
</script>

