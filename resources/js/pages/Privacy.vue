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
            Privacy Policy
          </h1>
          <p class="text-gray-600">
            Last Updated: October 31, 2025
          </p>
          <p class="text-gray-600 mt-2">
            Effective Date: October 31, 2025
          </p>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-lg shadow-sm p-8 space-y-8">
          <!-- Introduction -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Introduction</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
              MyGrowNet, operated by Rockshield Investments Limited ("we," "us," or "our"), is committed to 
              protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard 
              your information when you use our platform and services.
            </p>
            <p class="text-gray-700 leading-relaxed">
              By accessing or using MyGrowNet, you agree to this Privacy Policy. If you do not agree with our 
              policies and practices, please do not use our services.
            </p>
          </section>

          <!-- Information We Collect -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Information We Collect</h2>
            
            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">2.1 Personal Information</h3>
                <p class="text-gray-700 mb-2">We collect information that identifies you personally, including:</p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>Full name and date of birth</li>
                  <li>National Registration Card (NRC) number</li>
                  <li>Tax Identification Number (TPIN)</li>
                  <li>Email address and phone number</li>
                  <li>Physical address and proof of residence</li>
                  <li>Bank account or mobile money details</li>
                  <li>Profile photograph</li>
                </ul>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">2.2 Financial Information</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>Payment transaction details</li>
                  <li>Wallet balance and transaction history</li>
                  <li>Investment and venture participation records</li>
                  <li>Commission and earnings data</li>
                  <li>BGF application and funding information</li>
                </ul>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">2.3 Usage Information</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>Login activity and session data</li>
                  <li>Pages visited and features used</li>
                  <li>Device information (IP address, browser type, operating system)</li>
                  <li>Referral and network activity</li>
                  <li>Points and achievement data</li>
                </ul>
              </div>

              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">2.4 Communications</h3>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                  <li>Messages sent through our platform</li>
                  <li>Support tickets and inquiries</li>
                  <li>Feedback and survey responses</li>
                  <li>Email and SMS communications</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- How We Use Your Information -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. How We Use Your Information</h2>
            <p class="text-gray-700 mb-4">We use your information for the following purposes:</p>
            
            <div class="space-y-3">
              <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                  <h4 class="font-medium text-gray-900">Account Management</h4>
                  <p class="text-gray-700 text-sm">Create and maintain your account, verify your identity (KYC), and provide customer support</p>
                </div>
              </div>

              <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                  <h4 class="font-medium text-gray-900">Service Delivery</h4>
                  <p class="text-gray-700 text-sm">Process transactions, manage investments, distribute commissions, and provide platform features</p>
                </div>
              </div>

              <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                  <h4 class="font-medium text-gray-900">Communications</h4>
                  <p class="text-gray-700 text-sm">Send transactional emails, notifications, updates, and marketing communications (with your consent)</p>
                </div>
              </div>

              <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                  <h4 class="font-medium text-gray-900">Legal Compliance</h4>
                  <p class="text-gray-700 text-sm">Comply with legal obligations, prevent fraud, enforce our terms, and protect our rights</p>
                </div>
              </div>

              <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                  <h4 class="font-medium text-gray-900">Platform Improvement</h4>
                  <p class="text-gray-700 text-sm">Analyze usage patterns, improve our services, develop new features, and enhance user experience</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Information Sharing -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Information Sharing and Disclosure</h2>
            <p class="text-gray-700 mb-4">We may share your information in the following circumstances:</p>
            
            <div class="space-y-4">
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">With Your Consent</h4>
                <p class="text-gray-700 text-sm">We share information when you explicitly authorize us to do so</p>
              </div>

              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Service Providers</h4>
                <p class="text-gray-700 text-sm">Payment processors, SMS providers, email services, and hosting providers who help us operate the platform</p>
              </div>

              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Business Partners</h4>
                <p class="text-gray-700 text-sm">Venture co-investors and BGF project partners (limited to necessary information only)</p>
              </div>

              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Legal Requirements</h4>
                <p class="text-gray-700 text-sm">Government authorities, regulators, or law enforcement when required by law or to protect our rights</p>
              </div>

              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Business Transfers</h4>
                <p class="text-gray-700 text-sm">In connection with a merger, acquisition, or sale of assets (with notice to affected users)</p>
              </div>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-4">
              <p class="text-red-900 text-sm">
                <strong>We do NOT sell your personal information to third parties for marketing purposes.</strong>
              </p>
            </div>
          </section>

          <!-- Data Security -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Data Security</h2>
            <p class="text-gray-700 mb-4">We implement appropriate technical and organizational measures to protect your information:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
              <li>Encryption of data in transit (SSL/TLS) and at rest</li>
              <li>Secure authentication and access controls</li>
              <li>Regular security audits and vulnerability assessments</li>
              <li>Employee training on data protection</li>
              <li>Incident response and breach notification procedures</li>
            </ul>
            <p class="text-gray-700 mt-4">
              However, no method of transmission over the internet is 100% secure. While we strive to protect your 
              information, we cannot guarantee absolute security.
            </p>
          </section>

          <!-- Data Retention -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Data Retention</h2>
            <p class="text-gray-700 mb-4">We retain your information for as long as necessary to:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
              <li>Provide our services and maintain your account</li>
              <li>Comply with legal, tax, and accounting obligations (typically 7 years)</li>
              <li>Resolve disputes and enforce our agreements</li>
              <li>Prevent fraud and abuse</li>
            </ul>
            <p class="text-gray-700 mt-4">
              When information is no longer needed, we securely delete or anonymize it.
            </p>
          </section>

          <!-- Your Rights -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Your Privacy Rights</h2>
            <p class="text-gray-700 mb-4">You have the following rights regarding your personal information:</p>
            
            <div class="grid md:grid-cols-2 gap-4">
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">Access</h4>
                <p class="text-blue-800 text-sm">Request a copy of your personal information</p>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">Correction</h4>
                <p class="text-blue-800 text-sm">Update or correct inaccurate information</p>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">Deletion</h4>
                <p class="text-blue-800 text-sm">Request deletion of your information (subject to legal requirements)</p>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">Portability</h4>
                <p class="text-blue-800 text-sm">Receive your data in a structured, machine-readable format</p>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">Objection</h4>
                <p class="text-blue-800 text-sm">Object to processing of your information for certain purposes</p>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">Withdraw Consent</h4>
                <p class="text-blue-800 text-sm">Withdraw consent for marketing communications</p>
              </div>
            </div>

            <p class="text-gray-700 mt-4">
              To exercise these rights, contact us at <a href="mailto:privacy@mygrownet.com" class="text-blue-600 hover:underline">privacy@mygrownet.com</a>
            </p>
          </section>

          <!-- Cookies -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Cookies and Tracking</h2>
            <p class="text-gray-700 mb-4">We use cookies and similar technologies to:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
              <li>Maintain your session and keep you logged in</li>
              <li>Remember your preferences and settings</li>
              <li>Analyze platform usage and performance</li>
              <li>Provide personalized content and recommendations</li>
            </ul>
            <p class="text-gray-700 mt-4">
              You can control cookies through your browser settings. However, disabling cookies may limit your ability 
              to use certain features of our platform.
            </p>
          </section>

          <!-- Third-Party Links -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Third-Party Links</h2>
            <p class="text-gray-700">
              Our platform may contain links to third-party websites or services. We are not responsible for the 
              privacy practices of these third parties. We encourage you to review their privacy policies before 
              providing any information.
            </p>
          </section>

          <!-- Children's Privacy -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Children's Privacy</h2>
            <p class="text-gray-700">
              MyGrowNet is not intended for individuals under 18 years of age. We do not knowingly collect personal 
              information from children. If we become aware that we have collected information from a child, we will 
              take steps to delete it promptly.
            </p>
          </section>

          <!-- International Transfers -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. International Data Transfers</h2>
            <p class="text-gray-700">
              Your information is primarily stored and processed in Zambia. If we transfer data internationally, 
              we ensure appropriate safeguards are in place to protect your information in accordance with this 
              Privacy Policy and applicable laws.
            </p>
          </section>

          <!-- Changes to Policy -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">12. Changes to This Privacy Policy</h2>
            <p class="text-gray-700">
              We may update this Privacy Policy from time to time. We will notify you of material changes by:
            </p>
            <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4 mt-2">
              <li>Posting the updated policy on our platform</li>
              <li>Updating the "Last Updated" date</li>
              <li>Sending email notifications for significant changes</li>
            </ul>
            <p class="text-gray-700 mt-4">
              Your continued use of MyGrowNet after changes become effective constitutes acceptance of the updated policy.
            </p>
          </section>

          <!-- Contact -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">13. Contact Us</h2>
            <div class="bg-gray-50 rounded-lg p-6">
              <p class="text-gray-700 mb-4">
                If you have questions, concerns, or requests regarding this Privacy Policy or our data practices, 
                please contact us:
              </p>
              <div class="space-y-2 text-gray-700">
                <p><strong>Email:</strong> privacy@mygrownet.com</p>
                <p><strong>Phone:</strong> +260 977 563 730</p>
                <p><strong>Address:</strong> Rockshield Investments Limited, Lusaka, Zambia</p>
              </div>
            </div>
          </section>

          <!-- Governing Law -->
          <section class="border-t pt-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">14. Governing Law</h2>
            <p class="text-gray-700">
              This Privacy Policy is governed by the laws of the Republic of Zambia. Any disputes arising from this 
              policy shall be resolved in accordance with Zambian law.
            </p>
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
import { router } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back();
  } else {
    router.visit(route('policies'));
  }
};
</script>
