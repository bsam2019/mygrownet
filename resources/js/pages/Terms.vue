<template>
  <GuestLayout>
    <div class="min-h-screen bg-gray-50 py-12">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <button
          @click="goBack"
          class="mb-4 flex items-center text-sm font-medium text-gray-600 hover:text-gray-900"
        >
          <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back
        </button>
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
          <h1 class="text-3xl font-bold text-gray-900 mb-4">
            Terms of Service
          </h1>
          <p class="text-gray-600">
            Last Updated: July 30, 2026
          </p>
          <p class="text-gray-600 mt-2">
            Effective Date: July 30, 2026
          </p>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-lg shadow-sm p-8 space-y-8">
          <!-- Introduction -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Agreement to Terms</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              These Terms of Service ("Terms") constitute a legally binding agreement between you and MyGrowNet Technologies Limited, operating as MyGrowNet ("Company," "we," "us," or "our"), concerning your access 
              to and use of the MyGrowNet platform and services.
            </p>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <p class="text-yellow-900 text-sm">
                <strong>Important:</strong> By accessing or using MyGrowNet, you agree to be bound by these Terms. 
                If you do not agree to these Terms, you may not access or use our services.
              </p>
            </div>
          </section>

          <!-- Eligibility -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Eligibility</h2>
            <p class="text-gray-700 mb-4">To use MyGrowNet, you must:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
              <li>Be at least 18 years of age</li>
              <li>Be a resident of Zambia or have legal authorization to conduct business in Zambia</li>
              <li>Have the legal capacity to enter into binding contracts</li>
              <li>Not be prohibited from using our services under Zambian law</li>
              <li>Provide accurate and complete registration information</li>
              <li>Maintain the security of your account credentials</li>
            </ul>
          </section>

          <!-- Account Registration -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Account Registration and Security</h2>
            
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">3.1 Account Creation</h3>
                <p class="text-gray-700 mb-2">When creating an account, you agree to:</p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>Provide accurate, current, and complete information</li>
                  <li>Maintain and update your information to keep it accurate</li>
                  <li>Complete KYC (Know Your Customer) verification</li>
                  <li>Accept responsibility for all activities under your account</li>
                </ul>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">3.2 Account Security</h3>
                <p class="text-gray-700 mb-2">You are responsible for:</p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>Maintaining the confidentiality of your password</li>
                  <li>All activities that occur under your account</li>
                  <li>Notifying us immediately of any unauthorized access</li>
                  <li>Not sharing your account with others</li>
                </ul>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">3.3 Account Termination</h3>
                <p class="text-gray-700">
                  We reserve the right to suspend or terminate your account if you violate these Terms, engage in 
                  fraudulent activity, or for any other reason at our discretion.
                </p>
              </div>
            </div>
          </section>

          <!-- Services Description -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Description of Services</h2>
            <p class="text-gray-700 mb-4">MyGrowNet provides a multi-product digital platform. The specific services available to you depend on your account type, subscriptions, and access level. Our platform includes the following categories:</p>

            <div class="space-y-6">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">4.1 Digital Products</h3>
                <div class="grid md:grid-cols-2 gap-3">
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">GrowStream</h4>
                    <p class="text-gray-700 text-sm">Digital content streaming and media platform</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">GrowLearn</h4>
                    <p class="text-gray-700 text-sm">Online learning and skill development</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">GrowTemplates</h4>
                    <p class="text-gray-700 text-sm">Professional design templates and digital assets</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">GrowBackup</h4>
                    <p class="text-gray-700 text-sm">Cloud backup and data storage services</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">GrowAI</h4>
                    <p class="text-gray-700 text-sm">AI-powered tools and automation services</p>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">4.2 SaaS Business Applications</h3>
                <p class="text-gray-700 text-sm mb-3">Each SaaS application is governed by its own service-specific terms in addition to these Terms:</p>
                <div class="grid md:grid-cols-2 gap-3">
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">BizBoost</h4>
                    <p class="text-gray-700 text-sm">Business directory and lead generation platform</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">GrowMart</h4>
                    <p class="text-gray-700 text-sm">E-commerce and marketplace platform</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">BizDocs</h4>
                    <p class="text-gray-700 text-sm">Document management and invoicing</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">StockFlow</h4>
                    <p class="text-gray-700 text-sm">Inventory and stock audit management</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">GrowBuilder</h4>
                    <p class="text-gray-700 text-sm">Website building and hosting platform</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">QuickInvoice</h4>
                    <p class="text-gray-700 text-sm">Invoice generation and payment tracking</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">ZamStay</h4>
                    <p class="text-gray-700 text-sm">Accommodation and hospitality management</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">PrimeEdge</h4>
                    <p class="text-gray-700 text-sm">Advisory and professional services platform</p>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">4.3 Financial Services</h3>
                <div class="grid md:grid-cols-2 gap-3">
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">MyGrow Wallet</h4>
                    <p class="text-gray-700 text-sm">Digital wallet for platform transactions, withdrawals, and transfers</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Venture Builder</h4>
                    <p class="text-gray-700 text-sm">Co-investment opportunities in vetted business ventures</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Business Growth Fund</h4>
                    <p class="text-gray-700 text-sm">Short-term financing for verified business orders</p>
                  </div>
                  <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">GrowFinance</h4>
                    <p class="text-gray-700 text-sm">Accounting, budgeting, and financial management tools</p>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">4.4 Network and Referral Program</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                  <p class="text-gray-700 text-sm">Eligible members may participate in the MyGrowNet referral and commission program, which rewards network growth and platform activity. Participation is subject to the specific program terms.</p>
                </div>
              </div>

              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-yellow-900 text-sm">
                  <strong>Important:</strong> Each SaaS application and financial product may have additional terms and conditions specific to that service. In the event of any conflict between these general Terms and service-specific terms, the service-specific terms will prevail for that service.
                </p>
              </div>
            </div>
          </section>

          <!-- User Obligations -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. User Obligations and Prohibited Conduct</h2>
            
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">5.1 You Agree To:</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>Use the platform only for lawful purposes</li>
                  <li>Comply with all applicable laws and regulations</li>
                  <li>Provide truthful and accurate information</li>
                  <li>Respect the rights of other users</li>
                  <li>Maintain professional conduct in all interactions</li>
                </ul>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">5.2 You Agree NOT To:</h3>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                  <ul class="list-disc list-inside text-red-900 space-y-1 ml-4 text-sm">
                    <li>Create multiple accounts or fake accounts</li>
                    <li>Engage in fraudulent or deceptive practices</li>
                    <li>Manipulate the referral or commission system</li>
                    <li>Harass, threaten, or abuse other users</li>
                    <li>Attempt to hack, disrupt, or compromise platform security</li>
                    <li>Use automated tools or bots without authorization</li>
                    <li>Misrepresent your identity or affiliation</li>
                    <li>Violate intellectual property rights</li>
                    <li>Spam or send unsolicited communications</li>
                    <li>Engage in money laundering or terrorist financing</li>
                  </ul>
                </div>
              </div>
            </div>
          </section>

          <!-- Financial Terms -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Financial Terms</h2>
            
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">6.1 Subscriptions, Fees, and Payments</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>All fees are stated in Zambian Kwacha (ZMW) unless otherwise indicated</li>
                  <li>Subscription fees are billed in advance on a monthly or annual basis as selected during sign-up</li>
                  <li>Subscriptions automatically renew at the end of each billing period unless cancelled at least 48 hours before the renewal date</li>
                  <li>You may cancel your subscription at any time through your account settings; cancellation takes effect at the end of the current billing period</li>
                  <li>No prorated refunds for partial billing periods unless otherwise stated in service-specific terms</li>
                  <li>One-time membership fees are refundable within 14 days of payment, after which they become non-refundable</li>
                  <li>Payment processing fees may apply and are non-refundable once processed</li>
                  <li>We reserve the right to change fees with 30 days notice via email or platform notification</li>
                </ul>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">6.2 Commissions and Earnings</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>Commissions are earned based on network activity and performance as defined in the program terms</li>
                  <li>Minimum activity points may be required for monthly qualification</li>
                  <li>Earnings are subject to applicable taxes and withholding where required by law</li>
                  <li>We reserve the right to withhold earnings if fraud, abuse, or policy violation is suspected pending investigation</li>
                  <li>Unclaimed earnings may be forfeited after 12 months of account inactivity</li>
                </ul>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">6.3 Withdrawals</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>Minimum and maximum withdrawal limits apply as specified in your account</li>
                  <li>Processing time: 1–5 business days after approval</li>
                  <li>Withdrawal fees may apply and are disclosed at the time of request</li>
                  <li>We may request additional verification for withdrawals exceeding standard thresholds</li>
                  <li>Withdrawals are subject to anti-money laundering checks and may be delayed or refused accordingly</li>
                  <li>Funds from pending or disputed transactions are not available for withdrawal</li>
                </ul>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">6.4 Refund Policy</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>Digital product purchases are non-refundable once delivered or accessed</li>
                  <li>Subscription fees are non-refundable except as required by consumer protection law</li>
                  <li>Investment and BGF contributions are not refundable — see Section 7 for risk disclosures</li>
                  <li>Refund requests must be submitted via support@mygrownet.com</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Investment Risks -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Investment Risks and Disclaimers</h2>
            <div class="bg-red-50 border-2 border-red-300 rounded-lg p-6">
              <h3 class="font-semibold text-red-900 mb-3">IMPORTANT RISK DISCLOSURE</h3>
              <ul class="list-disc list-inside text-red-900 space-y-2 ml-4 text-sm">
                <li><strong>No Guaranteed Returns:</strong> Venture investments and BGF projects carry risk. Returns are not guaranteed.</li>
                <li><strong>Loss of Capital:</strong> You may lose some or all of your investment.</li>
                <li><strong>Illiquidity:</strong> Investments may not be easily converted to cash.</li>
                <li><strong>Market Risk:</strong> Economic conditions may affect performance.</li>
                <li><strong>Business Risk:</strong> Ventures may fail despite due diligence.</li>
                <li><strong>Not Financial Advice:</strong> Information provided is not investment advice. Consult a financial advisor.</li>
              </ul>
            </div>
          </section>

          <!-- Intellectual Property -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Intellectual Property Rights</h2>
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">8.1 Our Content</h3>
                <p class="text-gray-700">
                  All content on MyGrowNet, including text, graphics, logos, images, software, and design, is owned 
                  by or licensed to us and protected by copyright, trademark, and other intellectual property laws.
                </p>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">8.2 Limited License</h3>
                <p class="text-gray-700">
                  We grant you a limited, non-exclusive, non-transferable license to access and use the platform 
                  for personal, non-commercial purposes. You may not copy, modify, distribute, or create derivative 
                  works without our written permission.
                </p>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">8.3 User Content</h3>
                <p class="text-gray-700">
                  By submitting content to MyGrowNet, you grant us a worldwide, royalty-free license to use, 
                  reproduce, and display your content in connection with operating the platform.
                </p>
              </div>
            </div>
          </section>

          <!-- Limitation of Liability -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Limitation of Liability</h2>
            <div class="bg-gray-50 border border-gray-300 rounded-lg p-6">
              <p class="text-gray-700 mb-4">
                TO THE MAXIMUM EXTENT PERMITTED BY LAW:
              </p>
              <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                <li>We provide the platform "AS IS" without warranties of any kind</li>
                <li>We are not liable for indirect, incidental, or consequential damages</li>
                <li>Our total liability shall not exceed the fees you paid in the last 12 months</li>
                <li>We are not responsible for third-party actions or content</li>
                <li>We do not guarantee uninterrupted or error-free service</li>
              </ul>
            </div>
          </section>

          <!-- Indemnification -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Indemnification</h2>
            <p class="text-gray-700">
              You agree to indemnify, defend, and hold harmless MyGrowNet, MyGrowNet Technologies Limited, and our 
              officers, directors, employees, and agents from any claims, damages, losses, liabilities, and expenses 
              (including legal fees) arising from:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4 mt-2">
              <li>Your use of the platform</li>
              <li>Your violation of these Terms</li>
              <li>Your violation of any rights of another party</li>
              <li>Your fraudulent or illegal activities</li>
            </ul>
          </section>

          <!-- Dispute Resolution -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. Dispute Resolution</h2>
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">11.1 Informal Resolution</h3>
                <p class="text-gray-700">
                  Before filing a formal dispute, you agree to contact us at support@mygrownet.com to attempt to 
                  resolve the issue informally.
                </p>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">11.2 Arbitration</h3>
                <p class="text-gray-700">
                  If informal resolution fails, disputes shall be resolved through binding arbitration in Lusaka, 
                  Zambia, in accordance with Zambian arbitration laws.
                </p>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">11.3 Class Action Waiver</h3>
                <p class="text-gray-700">
                  You agree to resolve disputes on an individual basis and waive the right to participate in class 
                  actions or class arbitrations.
                </p>
              </div>
            </div>
          </section>

          <!-- Termination -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">12. Termination</h2>
            <div class="space-y-4">
              <p class="text-gray-700">
                We may suspend or terminate your access to MyGrowNet immediately, without notice, for:
              </p>
              <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                <li>Violation of these Terms</li>
                <li>Fraudulent or illegal activity</li>
                <li>Non-payment of fees</li>
                <li>Inactivity for extended periods</li>
                <li>Any other reason at our discretion</li>
              </ul>
              <p class="text-gray-700 mt-4">
                Upon termination, your right to use the platform ceases immediately. We may retain your information 
                as required by law or for legitimate business purposes, including the preservation of transaction 
                records, audit trails, and compliance with anti-money laundering regulations. Any outstanding 
                obligations (including unpaid fees, loan repayments, or BGF repayments) remain enforceable after 
                termination. Account balances may be forfeited after 12 months of inactivity following termination.
              </p>
            </div>
          </section>

          <!-- Changes to Terms -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">13. Changes to Terms</h2>
            <p class="text-gray-700">
              We reserve the right to modify these Terms at any time. We will notify you of material changes by:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4 mt-2">
              <li>Posting the updated Terms on our platform</li>
              <li>Updating the "Last Updated" date</li>
              <li>Sending email notifications for significant changes</li>
            </ul>
            <p class="text-gray-700 mt-4">
              Your continued use after changes become effective constitutes acceptance of the updated Terms.
            </p>
          </section>

          <!-- Governing Law -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">14. Governing Law and Jurisdiction</h2>
            <p class="text-gray-700">
              These Terms are governed by the laws of the Republic of Zambia. Any legal action or proceeding arising 
              from these Terms shall be brought exclusively in the courts of Zambia.
            </p>
          </section>

          <!-- Miscellaneous -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">15. Miscellaneous</h2>
            <div class="space-y-3">
              <div>
                <h4 class="font-medium text-gray-900">Entire Agreement</h4>
                <p class="text-gray-700 text-sm">These Terms constitute the entire agreement between you and MyGrowNet.</p>
              </div>
              <div>
                <h4 class="font-medium text-gray-900">Severability</h4>
                <p class="text-gray-700 text-sm">If any provision is found invalid, the remaining provisions remain in effect.</p>
              </div>
              <div>
                <h4 class="font-medium text-gray-900">Waiver</h4>
                <p class="text-gray-700 text-sm">Our failure to enforce any right does not constitute a waiver of that right.</p>
              </div>
              <div>
                <h4 class="font-medium text-gray-900">Assignment</h4>
                <p class="text-gray-700 text-sm">You may not assign these Terms without our consent. We may assign them freely.</p>
              </div>
              <div>
                <h4 class="font-medium text-gray-900">Force Majeure</h4>
                <p class="text-gray-700 text-sm">We shall not be liable for any failure or delay in performance caused by events beyond our reasonable control, including but not limited to acts of God, power outages, internet disruptions, government actions, pandemics, civil unrest, or natural disasters.</p>
              </div>
            </div>
          </section>

          <!-- Contact -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">16. Contact Information</h2>
            <div class="bg-gray-50 rounded-lg p-6">
              <p class="text-gray-700 mb-4">
                For questions about these Terms, please contact us:
              </p>
              <div class="space-y-2 text-gray-700">
                <p><strong>Email:</strong> legal@mygrownet.com</p>
                <p><strong>Phone:</strong> 0779872676</p>
                <p><strong>Address:</strong> MyGrowNet Technologies Limited, Lusaka, Zambia</p>
              </div>
            </div>
          </section>

          <!-- Acknowledgment -->
          <section class="border-t pt-8">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
              <h3 class="font-semibold text-blue-900 mb-3">Acknowledgment</h3>
              <p class="text-blue-800 text-sm">
                BY USING MYGROWNET, YOU ACKNOWLEDGE THAT YOU HAVE READ, UNDERSTOOD, AND AGREE TO BE BOUND BY THESE 
                TERMS OF SERVICE. IF YOU DO NOT AGREE, YOU MUST NOT USE OUR SERVICES.
              </p>
            </div>
          </section>
        </div>

        <!-- Back Button -->
        <div class="mt-8 text-center">
          <button @click="$inertia.visit(route('policies'))" class="inline-flex items-center text-gray-600 hover:text-gray-900 font-medium transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to All Policies
          </button>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/layouts/GuestLayout.vue';
import { router } from '@inertiajs/vue3';

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back();
  } else {
    router.visit(route('policies'));
  }
};
</script>
