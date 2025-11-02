<template>
  <GuestLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button (Mobile) -->
        <button
          @click="goBack"
          class="mb-4 flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 md:hidden"
        >
          <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back
        </button>
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
          <h1 class="text-3xl font-bold text-gray-900 mb-4">
            Venture Builder Investment Policy
          </h1>
          <p class="text-gray-600">
            Last Updated: October 31, 2025
          </p>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-lg shadow-sm p-8 space-y-8">
          <!-- Introduction -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Introduction</h2>
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6 mb-4">
              <p class="text-gray-800 leading-relaxed mb-3">
                <strong>Welcome to Venture Builder!</strong> We're excited to offer you the opportunity to become a 
                real shareholder in carefully vetted Zambian businesses. This is your chance to build wealth through 
                business ownership while supporting local entrepreneurship.
              </p>
              <p class="text-gray-700 leading-relaxed">
                This Investment Policy outlines how Venture Builder works, your rights as an investor, and what you 
                can expect from us. We believe in transparency and want you to make informed, confident investment decisions.
              </p>
            </div>
          </section>

          <!-- Investment Structure -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Investment Structure</h2>
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">2.1 Legal Framework</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                  <li>Each venture forms a separate limited company under Zambian law</li>
                  <li>Investors become legal shareholders with proper documentation</li>
                  <li>MyGrowNet retains minority equity (10-20%) as facilitator</li>
                  <li>All ventures comply with PACRA and regulatory requirements</li>
                </ul>
              </div>
              
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">2.2 Investment Limits</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                  <li>Minimum investment: K500 per venture</li>
                  <li>Maximum investment: 30% of total venture capital</li>
                  <li>Members can invest in multiple ventures</li>
                  <li>Investment amounts determine shareholding percentage</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Eligibility -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Investor Eligibility</h2>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h3 class="font-medium text-blue-900 mb-2">Requirements:</h3>
              <ul class="list-disc list-inside text-blue-800 space-y-1 ml-4">
                <li>Active MyGrowNet membership</li>
                <li>Completed KYC verification</li>
                <li>Minimum 200 Lifetime Points (LP)</li>
                <li>Verified payment method</li>
                <li>Accepted investment risks</li>
              </ul>
            </div>
          </section>

          <!-- Investment Process -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Investment Process</h2>
            <div class="space-y-3">
              <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold">1</div>
                <div class="ml-4">
                  <h4 class="font-medium text-gray-900">Review Venture Details</h4>
                  <p class="text-gray-600 text-sm">Read business plan, financials, and risk assessment</p>
                </div>
              </div>
              <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold">2</div>
                <div class="ml-4">
                  <h4 class="font-medium text-gray-900">Submit Investment</h4>
                  <p class="text-gray-600 text-sm">Choose amount and payment method</p>
                </div>
              </div>
              <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold">3</div>
                <div class="ml-4">
                  <h4 class="font-medium text-gray-900">Payment Verification</h4>
                  <p class="text-gray-600 text-sm">Admin verifies payment within 24 hours</p>
                </div>
              </div>
              <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold">4</div>
                <div class="ml-4">
                  <h4 class="font-medium text-gray-900">Shareholder Documentation</h4>
                  <p class="text-gray-600 text-sm">Receive share certificate and shareholder agreement</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Returns & Dividends -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Returns & Dividends</h2>
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">5.1 Dividend Distribution</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                  <li>Dividends paid quarterly or annually based on venture performance</li>
                  <li>Distribution proportional to shareholding percentage</li>
                  <li>Paid via MyGrow Wallet or bank transfer</li>
                  <li>Subject to applicable taxes</li>
                </ul>
              </div>
              
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">5.2 Expected Returns</h3>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                  <p class="text-yellow-900 text-sm">
                    <strong>Important:</strong> Returns are NOT guaranteed and depend on venture performance. 
                    Past performance does not indicate future results.
                  </p>
                </div>
              </div>
            </div>
          </section>

          <!-- Understanding Investment Dynamics -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Understanding Investment Dynamics</h2>
            <p class="text-gray-700 mb-4">
              Like all business investments, Venture Builder opportunities come with both potential rewards and considerations 
              to keep in mind. We believe in transparency and want you to make informed decisions.
            </p>
            
            <div class="grid md:grid-cols-2 gap-6 mb-6">
              <!-- Opportunities -->
              <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h3 class="font-semibold text-green-900 mb-3 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  Growth Opportunities
                </h3>
                <ul class="space-y-2 text-green-800 text-sm">
                  <li class="flex items-start">
                    <span class="mr-2">✓</span>
                    <span>Become a real shareholder in vetted businesses</span>
                  </li>
                  <li class="flex items-start">
                    <span class="mr-2">✓</span>
                    <span>Potential for quarterly/annual dividend income</span>
                  </li>
                  <li class="flex items-start">
                    <span class="mr-2">✓</span>
                    <span>Portfolio diversification across multiple ventures</span>
                  </li>
                  <li class="flex items-start">
                    <span class="mr-2">✓</span>
                    <span>Participate in Zambia's growing economy</span>
                  </li>
                  <li class="flex items-start">
                    <span class="mr-2">✓</span>
                    <span>Learn from experienced business operators</span>
                  </li>
                </ul>
              </div>

              <!-- Considerations -->
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="font-semibold text-blue-900 mb-3 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>
                  Important Considerations
                </h3>
                <ul class="space-y-2 text-blue-800 text-sm">
                  <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Performance Varies:</strong> Returns depend on business success</span>
                  </li>
                  <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Long-term Focus:</strong> Best suited for patient investors (12+ months)</span>
                  </li>
                  <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Illiquidity Period:</strong> Shares have a 12-month lock-in before transfer</span>
                  </li>
                  <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Business Dynamics:</strong> All businesses face market challenges</span>
                  </li>
                  <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Invest Wisely:</strong> Only invest amounts you can afford to commit long-term</span>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Our Commitment -->
            <div class="bg-gradient-to-r from-blue-50 to-green-50 border border-blue-200 rounded-lg p-6">
              <h3 class="font-semibold text-gray-900 mb-3">Our Commitment to Your Success</h3>
              <div class="grid md:grid-cols-3 gap-4 text-sm">
                <div>
                  <p class="font-medium text-blue-900 mb-1">Rigorous Vetting</p>
                  <p class="text-gray-700">Every venture undergoes thorough due diligence before listing</p>
                </div>
                <div>
                  <p class="font-medium text-blue-900 mb-1">Ongoing Support</p>
                  <p class="text-gray-700">MyGrowNet provides management support to maximize success</p>
                </div>
                <div>
                  <p class="font-medium text-blue-900 mb-1">Transparent Reporting</p>
                  <p class="text-gray-700">Regular updates and financial reports keep you informed</p>
                </div>
              </div>
            </div>

            <p class="text-gray-600 text-sm mt-4 italic">
              Remember: All investments carry some level of uncertainty, but with careful selection, diversification, 
              and patience, you position yourself for potential long-term wealth creation.
            </p>
          </section>

          <!-- Exit Strategy -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Exit Strategy</h2>
            <div class="space-y-4">
              <p class="text-gray-700">Investors may exit through:</p>
              <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                <li><strong>Share Transfer:</strong> Sell shares to other members (subject to approval)</li>
                <li><strong>Buyback:</strong> Company may buy back shares at fair market value</li>
                <li><strong>Venture Exit:</strong> Proceeds distributed if venture is sold or liquidated</li>
              </ul>
              <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mt-4">
                <p class="text-gray-700 text-sm">
                  <strong>Lock-in Period:</strong> Minimum 12-month holding period before shares can be transferred.
                </p>
              </div>
            </div>
          </section>

          <!-- Governance -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Governance & Reporting</h2>
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">8.1 Shareholder Rights</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                  <li>Voting rights proportional to shareholding</li>
                  <li>Access to quarterly financial reports</li>
                  <li>Attend annual general meetings</li>
                  <li>Receive dividend distributions</li>
                </ul>
              </div>
              
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">8.2 Reporting</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                  <li>Monthly updates via platform dashboard</li>
                  <li>Quarterly financial statements</li>
                  <li>Annual audited accounts</li>
                  <li>Material event notifications</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Fees -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Fees & Charges</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Platform Fee</td>
                    <td class="px-6 py-4 text-sm text-gray-700">2% of investment amount (one-time)</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Management Fee</td>
                    <td class="px-6 py-4 text-sm text-gray-700">Included in MyGrowNet equity stake</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Dividend Processing</td>
                    <td class="px-6 py-4 text-sm text-gray-700">Free</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Share Transfer</td>
                    <td class="px-6 py-4 text-sm text-gray-700">K50 per transfer</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- Legal -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Legal & Compliance</h2>
            <div class="space-y-4">
              <p class="text-gray-700">
                <strong>Governing Law:</strong> This policy is governed by the laws of the Republic of Zambia.
              </p>
              <p class="text-gray-700">
                <strong>Dispute Resolution:</strong> Disputes resolved through arbitration in Lusaka, Zambia.
              </p>
              <p class="text-gray-700">
                <strong>Regulatory Compliance:</strong> All ventures comply with PACRA, ZRA, and relevant sector regulations.
              </p>
            </div>
          </section>

          <!-- Contact -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. Contact Information</h2>
            <div class="bg-gray-50 rounded-lg p-6">
              <p class="text-gray-700 mb-2">
                <strong>For investment inquiries:</strong>
              </p>
              <p class="text-gray-700">Email: ventures@mygrownet.com</p>
              <p class="text-gray-700">Phone: +260 977 563 730</p>
              <p class="text-gray-700 mt-4">
                <strong>MyGrowNet / Rockshield Investments Limited</strong><br>
                Lusaka, Zambia
              </p>
            </div>
          </section>

          <!-- Acknowledgment -->
          <section class="border-t pt-8">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
              <h3 class="font-semibold text-blue-900 mb-3">Investment Acknowledgment</h3>
              <p class="text-blue-800 text-sm mb-4">
                By investing through the Venture Builder platform, you acknowledge that you have:
              </p>
              <ul class="list-disc list-inside text-blue-800 text-sm space-y-1 ml-4">
                <li>Read and understood this Investment Policy</li>
                <li>Reviewed the specific venture's business plan and risks</li>
                <li>Understood that investments may result in loss of capital</li>
                <li>Confirmed you can afford to lose your investment</li>
                <li>Agreed to the 12-month lock-in period</li>
              </ul>
            </div>
          </section>
        </div>

        <!-- Back Buttons -->
        <div class="mt-8 flex items-center justify-center gap-6">
          <button @click="$inertia.visit(route('policies'))" class="inline-flex items-center text-gray-600 hover:text-gray-900 font-medium transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to All Policies
          </button>
          <span class="text-gray-300">|</span>
          <Link :href="route('ventures.about')" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Venture Builder
          </Link>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back();
  } else {
    router.visit(route('ventures.index'));
  }
};
</script>
