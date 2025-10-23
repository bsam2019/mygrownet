<script setup lang="ts">
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: MemberLayout });

const breadcrumbs = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Compensation Plan', href: '/compensation-plan' },
];
</script>

<template>
    <Head title="Compensation Plan" />

    <MemberLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Cover Section -->
            <div class="text-center py-16 bg-gradient-to-br from-blue-50 to-green-50 rounded-lg">
                <h1 class="text-5xl font-bold text-blue-600 mb-4">MyGrowNet</h1>
                <h2 class="text-3xl font-semibold text-green-600 mb-6">Compensation Plan</h2>
                <p class="text-2xl text-gray-700 mb-8">Your Path to Financial Freedom & Personal Growth</p>
                <p class="text-lg text-gray-600">Learn • Earn • Grow</p>
            </div>

            <!-- What is MyGrowNet -->
            <div class="bg-white rounded-lg shadow-sm p-8">
                <h2 class="text-3xl font-bold text-blue-600 border-b-2 border-blue-600 pb-3 mb-6">
                    What is MyGrowNet?
                </h2>
                
                <div class="bg-blue-50 border-l-4 border-blue-600 p-6 mb-6">
                    <p class="text-lg font-semibold mb-4">
                        MyGrowNet is a community empowerment platform that helps you:
                    </p>
                    <ul class="space-y-2 text-base">
                        <li><strong>Learn</strong> - practical skills and business knowledge</li>
                        <li><strong>Earn</strong> - income through multiple streams</li>
                        <li><strong>Grow</strong> - personally and financially</li>
                    </ul>
                </div>

                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Legal Structure</h3>
                        <p><strong>Private Limited Company</strong> - NOT an investment scheme</p>
                        <p><strong>Business Model:</strong> Subscription-based with profit-sharing</p>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Our Mission</h3>
                        <p>
                            To create a community of empowered individuals who grow together through 
                            skills, entrepreneurship, and shared rewards.
                        </p>
                    </div>
                </div>

                <div class="bg-green-50 border-l-4 border-green-600 p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Key Differentiators</h3>
                    <ul class="space-y-2">
                        <li><span class="text-green-600 font-bold">✓</span> Legal & Compliant</li>
                        <li><span class="text-green-600 font-bold">✓</span> Real Products</li>
                        <li><span class="text-green-600 font-bold">✓</span> Multiple Income Streams</li>
                        <li><span class="text-green-600 font-bold">✓</span> Transparent System</li>
                        <li><span class="text-green-600 font-bold">✓</span> Community Support</li>
                    </ul>
                </div>
            </div>

            <!-- 6 Income Streams -->
            <div class="bg-white rounded-lg shadow-sm p-8">
                <h2 class="text-3xl font-bold text-blue-600 border-b-2 border-blue-600 pb-3 mb-6">
                    How You Earn Money
                </h2>
                <p class="text-xl text-green-600 font-semibold mb-6">6 Powerful Income Streams</p>

                <!-- Income Stream 1 -->
                <div class="mb-8">
                    <h3 class="text-2xl font-semibold text-gray-800 mb-3">
                        1. Monthly Bonus Pool (60% of Profits)
                    </h3>
                    <div class="bg-blue-50 border-l-4 border-blue-600 p-6 mb-4">
                        <p class="font-semibold mb-3">
                            60% of all company profits are shared with active members every month!
                        </p>
                        <p class="mb-2"><strong>Formula:</strong></p>
                        <p class="font-mono text-sm">Your Bonus = (Your BP ÷ Total BP) × 60% of Profit</p>
                    </div>
                </div>

                <!-- Income Stream 2 -->
                <div class="mb-8">
                    <h3 class="text-2xl font-semibold text-gray-800 mb-3">
                        2. Direct Referral Bonuses
                    </h3>
                    <p>Earn <strong>K50-K150 per referral</strong> paid instantly</p>
                </div>

                <!-- Income Stream 3 -->
                <div class="mb-8">
                    <h3 class="text-2xl font-semibold text-gray-800 mb-3">
                        3. Network Commissions (7 Levels Deep)
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse bg-white shadow-sm">
                            <thead>
                                <tr class="bg-blue-600 text-white">
                                    <th class="p-3 text-left">Level</th>
                                    <th class="p-3 text-left">Title</th>
                                    <th class="p-3 text-left">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3">1</td>
                                    <td class="p-3">Associate</td>
                                    <td class="p-3 font-bold text-green-600">15%</td>
                                </tr>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3">2</td>
                                    <td class="p-3">Professional</td>
                                    <td class="p-3 font-bold text-green-600">10%</td>
                                </tr>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3">3</td>
                                    <td class="p-3">Senior</td>
                                    <td class="p-3 font-bold text-green-600">8%</td>
                                </tr>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3">4</td>
                                    <td class="p-3">Manager</td>
                                    <td class="p-3 font-bold text-green-600">6%</td>
                                </tr>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3">5</td>
                                    <td class="p-3">Director</td>
                                    <td class="p-3 font-bold text-green-600">4%</td>
                                </tr>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3">6</td>
                                    <td class="p-3">Executive</td>
                                    <td class="p-3 font-bold text-green-600">3%</td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3">7</td>
                                    <td class="p-3">Ambassador</td>
                                    <td class="p-3 font-bold text-green-600">2%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Other Streams -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-2xl font-semibold text-gray-800 mb-3">
                            4. Product Sales Commissions
                        </h3>
                        <p>Earn BP for purchases and sales in MyGrow Shop</p>
                    </div>

                    <div>
                        <h3 class="text-2xl font-semibold text-gray-800 mb-3">
                            5. Quarterly Profit-Sharing
                        </h3>
                        <p>Share in company investment profits every 3 months</p>
                    </div>

                    <div>
                        <h3 class="text-2xl font-semibold text-gray-800 mb-3">
                            6. Milestone Rewards
                        </h3>
                        <p>Cash bonuses and physical rewards as you advance levels</p>
                    </div>
                </div>
            </div>

            <!-- Download Full Plan -->
            <div class="bg-blue-50 border-2 border-blue-600 rounded-lg p-6 text-center">
                <h3 class="text-xl font-semibold text-gray-800 mb-3">
                    Want the Complete Compensation Plan?
                </h3>
                <p class="text-gray-600 mb-4">
                    View the full detailed presentation with all income examples, FAQs, and success stories.
                </p>
                <a 
                    href="/compensation-plan.html" 
                    target="_blank"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    View Full Compensation Plan
                </a>
            </div>

            <!-- Registration Info -->
            <div class="bg-white rounded-lg shadow-sm p-8">
                <h2 class="text-3xl font-bold text-blue-600 border-b-2 border-blue-600 pb-3 mb-6">
                    Ready to Get Started?
                </h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-xl font-semibold mb-3">Investment Required</h3>
                        <ul class="space-y-2">
                            <li>
                                <strong>Registration:</strong> 
                                <span class="text-2xl text-blue-600 font-bold">K500</span> (one-time)
                            </li>
                            <li>
                                <strong>Monthly:</strong> 
                                <span class="text-2xl text-blue-600 font-bold">K150-K1,000</span>
                            </li>
                        </ul>
                    </div>
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="text-xl font-semibold mb-3">Potential Returns</h3>
                        <ul class="space-y-2">
                            <li>
                                <strong>Month 1:</strong> 
                                <span class="text-xl text-green-600 font-bold">K300-K800</span>
                            </li>
                            <li>
                                <strong>Year 1:</strong> 
                                <span class="text-xl text-green-600 font-bold">K2,000-K8,000/month</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
