<template>
  <GuestLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
          <h1 class="text-3xl font-bold text-gray-900 mb-4">
            Business Growth Fund (BGF) Policy
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
            <p class="text-gray-700 leading-relaxed mb-4">
              The MyGrowNet Business Growth Fund (BGF) is a business partnership model that provides short-term 
              financing support to active members who have verified business orders or opportunities but lack 
              sufficient working capital.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <p class="text-blue-900 font-medium">
                Important: BGF is NOT a loan facility. It operates as a profit-sharing partnership on specific business transactions.
              </p>
            </div>
          </section>

          <!-- Eligibility -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Eligibility Requirements</h2>
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">2.1 Membership Requirements</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                  <li>Active MyGrowNet membership with paid subscription</li>
                  <li>Minimum 2 months participation OR 300+ Lifetime Points (LP)</li>
                  <li>Completed mandatory training modules</li>
                  <li>Verified identity (KYC) - NRC, TPIN, proof of residence</li>
                  <li>Good standing - no misconduct or unresolved issues</li>
                </ul>
              </div>
              
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">2.2 Business Requirements</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                  <li>Real business transaction or verified order (not personal use)</li>
                  <li>Proof of order: LPO, quotation, invoice, or agreement</li>
                  <li>Simple business plan showing returns within 1-4 months</li>
                  <li>Member contribution of 20-30% of required capital</li>
                  <li>Business bank or mobile money account</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Funding Structure -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Funding Structure</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Support Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount Range</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profit Split</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Timeline</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Short-term Order</td>
                    <td class="px-6 py-4 text-sm text-gray-700">Below K20,000</td>
                    <td class="px-6 py-4 text-sm text-gray-700">70% Member : 30% MyGrowNet</td>
                    <td class="px-6 py-4 text-sm text-gray-700">1-3 months</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Medium Venture</td>
                    <td class="px-6 py-4 text-sm text-gray-700">K20,000 - K50,000</td>
                    <td class="px-6 py-4 text-sm text-gray-700">65% Member : 35% MyGrowNet</td>
                    <td class="px-6 py-4 text-sm text-gray-700">2-4 months</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">High-Risk/New Member</td>
                    <td class="px-6 py-4 text-sm text-gray-700">Any amount</td>
                    <td class="px-6 py-4 text-sm text-gray-700">60% Member : 40% MyGrowNet</td>
                    <td class="px-6 py-4 text-sm text-gray-700">1-3 months</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- Application Process -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Application Process</h2>
            <div class="space-y-3">
              <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-semibold">1</div>
                <div class="ml-4">
                  <h4 class="font-medium text-gray-900">Submit Application</h4>
                  <p class="text-gray-600 text-sm">Complete online form with business details and documents</p>
                </div>
              </div>
              <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-semibold">2</div>
                <div class="ml-4">
                  <h4 class="font-medium text-gray-900">Initial Screening</h4>
                  <p class="text-gray-600 text-sm">Automated eligibility check (24 hours)</p>
                </div>
              </div>
              <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-semibold">3</div>
                <div class="ml-4">
                  <h4 class="font-medium text-gray-900">Risk Assessment</h4>
                  <p class="text-gray-600 text-sm">Officer evaluates and scores application (2-3 days)</p>
                </div>
              </div>
              <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-semibold">4</div>
                <div class="ml-4">
                  <h4 class="font-medium text-gray-900">Committee Review</h4>
                  <p class="text-gray-600 text-sm">Business Review Committee makes decision (7 days total)</p>
                </div>
              </div>
              <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-semibold">5</div>
                <div class="ml-4">
                  <h4 class="font-medium text-gray-900">Contract & Disbursement</h4>
                  <p class="text-gray-600 text-sm">Sign agreement and receive funds in tranches</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Scoring System -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Scoring System</h2>
            <p class="text-gray-700 mb-4">Applications are scored out of 100 points. Minimum score required: 70/100</p>
            <div class="space-y-3">
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                <span class="text-gray-700">Membership Tenure & Activity</span>
                <span class="font-semibold text-gray-900">15 points</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                <span class="text-gray-700">Training Completion</span>
                <span class="font-semibold text-gray-900">10 points</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                <span class="text-gray-700">Business Viability</span>
                <span class="font-semibold text-gray-900">25 points</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                <span class="text-gray-700">Credibility & References</span>
                <span class="font-semibold text-gray-900">15 points</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                <span class="text-gray-700">Member Contribution</span>
                <span class="font-semibold text-gray-900">15 points</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                <span class="text-gray-700">Risk Control Measures</span>
                <span class="font-semibold text-gray-900">10 points</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                <span class="text-gray-700">Previous Project Success</span>
                <span class="font-semibold text-gray-900">10 points</span>
              </div>
            </div>
          </section>

          <!-- Disbursement -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Disbursement Rules</h2>
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">6.1 Tranche-Based Release</h3>
                <p class="text-gray-700 mb-2">Funds released in stages based on milestones:</p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>Stage 1: Raw materials purchase</li>
                  <li>Stage 2: Production/execution</li>
                  <li>Stage 3: Delivery completion</li>
                </ul>
              </div>
              
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">6.2 Direct Vendor Payment</h3>
                <p class="text-gray-700">
                  For large orders, MyGrowNet may pay suppliers directly to reduce risk of fund misuse.
                </p>
              </div>
              
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">6.3 Reporting Requirements</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>Profit reconciliation report within 14 days after completion</li>
                  <li>Proof of delivery and payment received</li>
                  <li>Transparent accounting of all expenses</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Profit Sharing -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Profit Sharing & Returns</h2>
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-4">
              <h3 class="font-semibold text-green-900 mb-3">Example Calculation:</h3>
              <div class="space-y-2 text-green-800">
                <p><strong>Order Value:</strong> K15,000</p>
                <p><strong>BGF Support:</strong> K10,500 (70%)</p>
                <p><strong>Member Contribution:</strong> K4,500 (30%)</p>
                <p><strong>Total Profit:</strong> K5,000</p>
                <p class="pt-2 border-t border-green-300">
                  <strong>Member Share (70%):</strong> K3,500<br>
                  <strong>MyGrowNet Share (30%):</strong> K1,500
                </p>
              </div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <p class="text-yellow-900 text-sm">
                <strong>Note:</strong> All returns made after completion of order/project. Not fixed installments - based on actual profit.
              </p>
            </div>
          </section>

          <!-- Consequences -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Consequences of Misuse</h2>
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
              <h3 class="font-semibold text-red-900 mb-3">If funds are misused or project fails due to negligence:</h3>
              <ul class="list-disc list-inside text-red-800 space-y-2 ml-4">
                <li>Immediate suspension from future BGF support</li>
                <li>Legal action if fraud is detected</li>
                <li>Negative impact on member reputation and points</li>
                <li>Possible termination of MyGrowNet membership</li>
                <li>Recovery proceedings for misappropriated funds</li>
              </ul>
            </div>
          </section>

          <!-- Rewards -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Rewards for Good Performance</h2>
            <div class="grid md:grid-cols-2 gap-4">
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-medium text-blue-900 mb-2">Immediate Rewards</h3>
                <ul class="list-disc list-inside text-blue-800 text-sm space-y-1 ml-4">
                  <li>Bonus Points (BP) or Life Points (LP)</li>
                  <li>"Trusted Entrepreneur" badge</li>
                  <li>First priority in future funding</li>
                </ul>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-medium text-blue-900 mb-2">Long-Term Benefits</h3>
                <ul class="list-disc list-inside text-blue-800 text-sm space-y-1 ml-4">
                  <li>Eligibility to become Venture Partner</li>
                  <li>Opportunity to mentor new applicants</li>
                  <li>Higher funding limits</li>
                  <li>Better profit-sharing terms</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Priority Sectors -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Priority Considerations</h2>
            <p class="text-gray-700 mb-4">In early stages, priority given to:</p>
            <div class="grid md:grid-cols-2 gap-4">
              <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-2">Demographics</h3>
                <ul class="list-disc list-inside text-gray-700 text-sm space-y-1 ml-4">
                  <li>Women-led ventures</li>
                  <li>Youth entrepreneurs</li>
                  <li>Rural/semi-urban members</li>
                </ul>
              </div>
              <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-2">Key Sectors</h3>
                <ul class="list-disc list-inside text-gray-700 text-sm space-y-1 ml-4">
                  <li>Agriculture</li>
                  <li>Manufacturing</li>
                  <li>Trade & Services</li>
                  <li>Education</li>
                  <li>Job creation projects</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Legal -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. Legal & Compliance</h2>
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">11.1 Legal Framework</h3>
                <p class="text-gray-700">
                  BGF operates as a business partnership model to remain compliant with Zambian law:
                </p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4 mt-2">
                  <li>No deposit-taking from members</li>
                  <li>Company-financed fund from MyGrowNet revenue</li>
                  <li>Profit-sharing model, not loan facility</li>
                  <li>Transaction-specific support</li>
                  <li>Clear written contracts for each project</li>
                </ul>
              </div>
              
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">11.2 Governing Law</h3>
                <p class="text-gray-700">
                  This policy is governed by the laws of the Republic of Zambia. Disputes resolved through 
                  arbitration in Lusaka, Zambia.
                </p>
              </div>
            </div>
          </section>

          <!-- Contact -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">12. Contact Information</h2>
            <div class="bg-gray-50 rounded-lg p-6">
              <p class="text-gray-700 mb-2">
                <strong>For BGF inquiries:</strong>
              </p>
              <p class="text-gray-700">Email: bgf@mygrownet.com</p>
              <p class="text-gray-700">Phone: +260 977 563 730</p>
              <p class="text-gray-700 mt-4">
                <strong>MyGrowNet / Rockshield Investments Limited</strong><br>
                Lusaka, Zambia
              </p>
            </div>
          </section>

          <!-- Acknowledgment -->
          <section class="border-t pt-8">
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
              <h3 class="font-semibold text-green-900 mb-3">Application Acknowledgment</h3>
              <p class="text-green-800 text-sm mb-4">
                By applying for BGF support, you acknowledge that you have:
              </p>
              <ul class="list-disc list-inside text-green-800 text-sm space-y-1 ml-4">
                <li>Read and understood this BGF Policy</li>
                <li>Provided accurate and truthful information</li>
                <li>Have a genuine business opportunity with verified order</li>
                <li>Will contribute 20-30% of required capital</li>
                <li>Understand this is profit-sharing, not a loan</li>
                <li>Agree to transparent reporting and accountability</li>
                <li>Accept consequences if funds are misused</li>
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
          <Link :href="route('bgf.about')" class="inline-flex items-center text-green-600 hover:text-green-700 font-medium transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Business Growth Fund
          </Link>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';
</script>
