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
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg shadow-sm p-8 mb-8 border border-purple-200">
          <h1 class="text-3xl font-bold text-gray-900 mb-4">
            MyGrow Wallet Policy
          </h1>
          <p class="text-gray-700 leading-relaxed mb-3">
            <strong>Welcome to MyGrow Wallet!</strong> Your convenient digital account for managing platform funds. 
            This policy explains how the wallet works, your rights, and how we keep your funds safe.
          </p>
          <p class="text-gray-600 text-sm">
            Last Updated: October 31, 2025 | Effective Date: October 31, 2025
          </p>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-lg shadow-sm p-8 space-y-8">
          
          <!-- What It Is -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. What is MyGrow Wallet?</h2>
            <p class="text-gray-700 mb-4">
              MyGrow Wallet is your digital account within the MyGrowNet platform - think of it as a convenient digital purse 
              for managing your earnings and making platform purchases.
            </p>
            
            <div class="grid md:grid-cols-2 gap-4">
              <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h3 class="font-semibold text-green-900 mb-2 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  What It IS
                </h3>
                <ul class="space-y-1 text-green-800 text-sm">
                  <li>✓ Prepaid digital account</li>
                  <li>✓ Platform convenience feature</li>
                  <li>✓ Secure fund storage</li>
                  <li>✓ Quick payment tool</li>
                </ul>
              </div>

              <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h3 class="font-semibold text-red-900 mb-2 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  What It's NOT
                </h3>
                <ul class="space-y-1 text-red-800 text-sm">
                  <li>✗ NOT a bank account</li>
                  <li>✗ NOT an investment</li>
                  <li>✗ NOT for money transfers</li>
                  <li>✗ NOT a lending service</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- How to Use It -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. How to Use Your Wallet</h2>
            
            <div class="space-y-4">
              <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="font-medium text-blue-900 mb-2">💰 Money In (Deposits)</h3>
                <ul class="text-blue-800 text-sm space-y-1 ml-4">
                  <li>• Earnings (commissions, bonuses, profit-sharing)</li>
                  <li>• Mobile money deposits (MTN MoMo, Airtel Money)</li>
                  <li>• Bank transfers</li>
                  <li>• Promotional rewards</li>
                </ul>
              </div>

              <div class="bg-purple-50 rounded-lg p-4">
                <h3 class="font-medium text-purple-900 mb-2">💸 Money Out (Spending & Withdrawals)</h3>
                <ul class="text-purple-800 text-sm space-y-1 ml-4">
                  <li>• Buy products and subscriptions</li>
                  <li>• Pay for training and workshops</li>
                  <li>• Invest in Venture Builder projects</li>
                  <li>• Withdraw to mobile money or bank (min K50)</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Your Funds Are Safe -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Your Funds Are Safe</h2>
            <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-6">
              <div class="grid md:grid-cols-3 gap-4">
                <div>
                  <div class="text-green-600 font-semibold mb-2">🔒 Your Property</div>
                  <p class="text-gray-700 text-sm">Funds remain yours until used for platform transactions</p>
                </div>
                <div>
                  <div class="text-blue-600 font-semibold mb-2">🏦 Segregated Accounts</div>
                  <p class="text-gray-700 text-sm">Kept separate from company operations</p>
                </div>
                <div>
                  <div class="text-purple-600 font-semibold mb-2">🛡️ Bank-Level Security</div>
                  <p class="text-gray-700 text-sm">Encrypted transactions and secure storage</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Rewards & Bonuses -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. Rewards & Bonuses</h2>
            <p class="text-gray-700 mb-4">
              We occasionally reward active members with loyalty points, bonus credits, and promotional vouchers!
            </p>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
              <p class="text-yellow-900 text-sm">
                <strong>Important:</strong> Rewards are promotional incentives (like gift cards), NOT interest or investment returns. 
                They can only be used for platform purchases and cannot be withdrawn as cash.
              </p>
            </div>

            <div class="grid md:grid-cols-3 gap-4 text-sm">
              <div class="bg-white border rounded-lg p-4">
                <div class="font-medium text-gray-900 mb-1">Loyalty Points</div>
                <p class="text-gray-600">Earned through platform activity and engagement</p>
              </div>
              <div class="bg-white border rounded-lg p-4">
                <div class="font-medium text-gray-900 mb-1">Bonus Credits</div>
                <p class="text-gray-600">Achievement rewards and special promotions</p>
              </div>
              <div class="bg-white border rounded-lg p-4">
                <div class="font-medium text-gray-900 mb-1">Vouchers</div>
                <p class="text-gray-600">Limited-time offers and event credits</p>
              </div>
            </div>
          </section>

          <!-- Transaction Limits -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Transaction Limits & Fees</h2>
            
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Limit/Fee</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Minimum Withdrawal</td>
                    <td class="px-6 py-4 text-sm text-gray-700">K50</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Maximum Daily Withdrawal</td>
                    <td class="px-6 py-4 text-sm text-gray-700">K50,000 (with verification)</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Withdrawal Processing</td>
                    <td class="px-6 py-4 text-sm text-gray-700">1-5 business days</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Deposit from Earnings</td>
                    <td class="px-6 py-4 text-sm text-green-600 font-medium">FREE</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">Withdrawal Fee</td>
                    <td class="px-6 py-4 text-sm text-gray-700">Small fee (disclosed before confirming)</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- Your Responsibilities -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Your Responsibilities</h2>
            
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <h3 class="font-medium text-green-900 mb-2">✅ Please DO:</h3>
                <ul class="text-gray-700 text-sm space-y-1 ml-4">
                  <li>• Keep your password secure</li>
                  <li>• Use for platform transactions only</li>
                  <li>• Report unauthorized activity</li>
                  <li>• Keep contact info updated</li>
                  <li>• Review transactions regularly</li>
                </ul>
              </div>

              <div>
                <h3 class="font-medium text-red-900 mb-2">❌ Please DON'T:</h3>
                <ul class="text-gray-700 text-sm space-y-1 ml-4">
                  <li>• Share your account access</li>
                  <li>• Use for non-platform activities</li>
                  <li>• Create multiple accounts</li>
                  <li>• Attempt to manipulate the system</li>
                  <li>• Engage in fraudulent activity</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Security Tips -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Keep Your Wallet Secure</h2>
            <div class="grid md:grid-cols-2 gap-4">
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">🔐 Strong Password</h4>
                <p class="text-blue-800 text-sm">Mix letters, numbers, and symbols. Never share it.</p>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">📱 Enable 2FA</h4>
                <p class="text-blue-800 text-sm">Extra security layer protects your funds.</p>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">👀 Monitor Activity</h4>
                <p class="text-blue-800 text-sm">Check transactions regularly for anything unusual.</p>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">🚪 Log Out</h4>
                <p class="text-blue-800 text-sm">Always log out on shared devices.</p>
              </div>
            </div>
          </section>

          <!-- Legal Compliance -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Legal Compliance</h2>
            <p class="text-gray-700 mb-4">
              MyGrow Wallet operates in full compliance with Zambian regulations:
            </p>
            <div class="space-y-2 text-gray-700 text-sm ml-4">
              <p>✓ <strong>Companies Act:</strong> Registered private limited company</p>
              <p>✓ <strong>Bank of Zambia:</strong> Prepaid system (not deposit-taking)</p>
              <p>✓ <strong>SEC:</strong> Not an investment scheme</p>
              <p>✓ <strong>AML Regulations:</strong> KYC verification and monitoring</p>
            </div>
          </section>

          <!-- Support -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Need Help?</h2>
            <div class="bg-gray-50 rounded-lg p-6">
              <p class="text-gray-700 mb-4">Our support team is here to help!</p>
              <div class="space-y-2 text-gray-700">
                <p><strong>Email:</strong> support@mygrownet.com</p>
                <p><strong>Phone:</strong> +260 977 563 730</p>
                <p><strong>Hours:</strong> Monday - Friday, 8 AM - 5 PM</p>
                <p><strong>Response Time:</strong> 24-48 hours</p>
              </div>
            </div>
          </section>

          <!-- Quick Reference -->
          <section class="border-t pt-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Quick Reference</h2>
            <div class="grid md:grid-cols-3 gap-4 text-sm">
              <div class="bg-purple-50 rounded-lg p-4">
                <div class="font-medium text-purple-900 mb-2">What You CAN Do</div>
                <ul class="text-purple-800 space-y-1">
                  <li>✓ Store earnings</li>
                  <li>✓ Buy products</li>
                  <li>✓ Pay subscriptions</li>
                  <li>✓ Withdraw funds</li>
                </ul>
              </div>
              <div class="bg-red-50 rounded-lg p-4">
                <div class="font-medium text-red-900 mb-2">What You CANNOT Do</div>
                <ul class="text-red-800 space-y-1">
                  <li>✗ Earn interest</li>
                  <li>✗ Transfer to others</li>
                  <li>✗ Use outside platform</li>
                  <li>✗ Treat as investment</li>
                </ul>
              </div>
              <div class="bg-blue-50 rounded-lg p-4">
                <div class="font-medium text-blue-900 mb-2">Key Numbers</div>
                <ul class="text-blue-800 space-y-1">
                  <li>• Min withdrawal: K50</li>
                  <li>• Max daily: K50,000</li>
                  <li>• Processing: 1-5 days</li>
                  <li>• Support: 24-48 hrs</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Acknowledgment -->
          <section class="border-t pt-8">
            <div class="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-6">
              <h3 class="font-semibold text-gray-900 mb-3">By Using MyGrow Wallet</h3>
              <p class="text-gray-700 text-sm mb-3">
                You acknowledge that you have read and understood this policy, and agree to use the wallet 
                responsibly for platform transactions only.
              </p>
              <p class="text-gray-600 text-xs">
                This wallet is a convenience feature, not a bank or investment. Your funds are safe, secure, 
                and always accessible for platform use or withdrawal.
              </p>
            </div>
          </section>
        </div>

        <!-- Back Button -->
        <div class="mt-8 flex items-center justify-center gap-6">
          <button @click="$inertia.visit(route('policies'))" class="inline-flex items-center text-gray-600 hover:text-gray-900 font-medium transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to All Policies
          </button>
          <span class="text-gray-300">|</span>
          <Link :href="route('mygrownet.wallet.index')" class="inline-flex items-center text-purple-600 hover:text-purple-700 font-medium transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            Go to My Wallet
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
    router.visit(route('home'));
  }
};
</script>
