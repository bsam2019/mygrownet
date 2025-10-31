<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileText, Search, CheckCircle, DollarSign, TrendingUp, AlertCircle } from 'lucide-vue-next';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';
</script>

<template>
    <Head title="How BGF Works - MyGrowNet" />

    <div class="min-h-screen bg-gray-50">
        <Navigation />

        <!-- Page Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <Link :href="route('bgf.about')" class="text-blue-100 hover:text-white mb-4 inline-block">
                    ← Back to Overview
                </Link>
                <h1 class="text-4xl font-bold mb-4">How Business Growth Fund Works</h1>
                <p class="text-xl text-blue-100">
                    A step-by-step guide to accessing short-term business financing
                </p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Step 1: Application -->
            <div class="mb-16">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-full text-2xl font-bold">
                        1
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Submit Your Application</h2>
                        <p class="text-gray-600">Provide details about your business opportunity</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8 ml-20">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">What You'll Need:</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3">
                            <CheckCircle class="h-5 w-5 text-green-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="font-medium text-gray-900">Business Information</p>
                                <p class="text-sm text-gray-600">Name, type, description</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <CheckCircle class="h-5 w-5 text-green-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="font-medium text-gray-900">Order Proof</p>
                                <p class="text-sm text-gray-600">LPO, contract, or business plan</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <CheckCircle class="h-5 w-5 text-green-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="font-medium text-gray-900">Financial Details</p>
                                <p class="text-sm text-gray-600">Amount needed, contribution, expected profit</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <CheckCircle class="h-5 w-5 text-green-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="font-medium text-gray-900">Client Information</p>
                                <p class="text-sm text-gray-600">Name, contact (for verification)</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 bg-blue-50 border-l-4 border-blue-600 p-4">
                        <p class="text-sm text-blue-900">
                            <strong>Tip:</strong> Applications with verified orders and detailed business plans have higher approval rates.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Step 2: Evaluation -->
            <div class="mb-16">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-full text-2xl font-bold">
                        2
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Application Evaluation</h2>
                        <p class="text-gray-600">Our team reviews and scores your application</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8 ml-20">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Scoring Criteria (Total: 100 points)</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Membership & Activity</p>
                                <p class="text-sm text-gray-600">Level, LP, platform engagement</p>
                            </div>
                            <span class="text-2xl font-bold text-blue-600">15</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Business Viability</p>
                                <p class="text-sm text-gray-600">Market analysis, profit potential</p>
                            </div>
                            <span class="text-2xl font-bold text-blue-600">25</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Credibility & References</p>
                                <p class="text-sm text-gray-600">Client verification, history</p>
                            </div>
                            <span class="text-2xl font-bold text-blue-600">15</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Member Contribution</p>
                                <p class="text-sm text-gray-600">Percentage and source</p>
                            </div>
                            <span class="text-2xl font-bold text-blue-600">15</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Other Factors</p>
                                <p class="text-sm text-gray-600">Training, risk control, track record</p>
                            </div>
                            <span class="text-2xl font-bold text-blue-600">30</span>
                        </div>
                    </div>

                    <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-600 p-4">
                        <p class="text-sm text-yellow-900">
                            <strong>Minimum Score:</strong> 70/100 required for approval. Timeline: 3-5 business days.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Approval & Contract -->
            <div class="mb-16">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-full text-2xl font-bold">
                        3
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Approval & Contract Signing</h2>
                        <p class="text-gray-600">Review and sign your funding agreement</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8 ml-20">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Your Contract Includes:</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <CheckCircle class="h-5 w-5 text-green-600 flex-shrink-0 mt-1" />
                            <span class="text-gray-700">Approved funding amount</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <CheckCircle class="h-5 w-5 text-green-600 flex-shrink-0 mt-1" />
                            <span class="text-gray-700">Profit sharing percentage (60-70% member, 30-40% MyGrowNet)</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <CheckCircle class="h-5 w-5 text-green-600 flex-shrink-0 mt-1" />
                            <span class="text-gray-700">Project completion timeline</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <CheckCircle class="h-5 w-5 text-green-600 flex-shrink-0 mt-1" />
                            <span class="text-gray-700">Disbursement schedule and milestones</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <CheckCircle class="h-5 w-5 text-green-600 flex-shrink-0 mt-1" />
                            <span class="text-gray-700">Reporting requirements</span>
                        </li>
                    </ul>

                    <div class="mt-6">
                        <p class="text-sm text-gray-600">
                            Contracts are digitally signed through the platform. Both parties receive a copy.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Step 4: Disbursement -->
            <div class="mb-16">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-full text-2xl font-bold">
                        4
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Receive Funding</h2>
                        <p class="text-gray-600">Get funds based on approved milestones</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8 ml-20">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Disbursement Options:</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border-2 border-blue-200 rounded-lg p-6">
                            <h4 class="font-semibold text-gray-900 mb-2">Direct to Member</h4>
                            <p class="text-sm text-gray-600">
                                Funds transferred to your MyGrow Wallet or bank account for general project expenses.
                            </p>
                        </div>
                        <div class="border-2 border-blue-200 rounded-lg p-6">
                            <h4 class="font-semibold text-gray-900 mb-2">Direct Vendor Payment</h4>
                            <p class="text-sm text-gray-600">
                                We pay suppliers directly for materials, reducing risk and ensuring proper use of funds.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 bg-blue-50 border-l-4 border-blue-600 p-4">
                        <p class="text-sm text-blue-900">
                            <strong>Important:</strong> Keep all receipts and documentation. You'll need them for the final profit report.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Step 5: Project Execution -->
            <div class="mb-16">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-full text-2xl font-bold">
                        5
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Execute Your Project</h2>
                        <p class="text-gray-600">Complete your business within the agreed timeline</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8 ml-20">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">During Project Execution:</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <AlertCircle class="h-5 w-5 text-blue-600 flex-shrink-0 mt-1" />
                            <span class="text-gray-700">Keep detailed records of all expenses</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <AlertCircle class="h-5 w-5 text-blue-600 flex-shrink-0 mt-1" />
                            <span class="text-gray-700">Save all receipts and invoices</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <AlertCircle class="h-5 w-5 text-blue-600 flex-shrink-0 mt-1" />
                            <span class="text-gray-700">Update project progress in the platform</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <AlertCircle class="h-5 w-5 text-blue-600 flex-shrink-0 mt-1" />
                            <span class="text-gray-700">Communicate any challenges or delays immediately</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Step 6: Profit Reporting -->
            <div class="mb-16">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-full text-2xl font-bold">
                        6
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Submit Profit Report</h2>
                        <p class="text-gray-600">Report your results within 7 days of completion</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8 ml-20">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Your Report Must Include:</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3">
                            <FileText class="h-5 w-5 text-blue-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="font-medium text-gray-900">Revenue Breakdown</p>
                                <p class="text-sm text-gray-600">All income from the project</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <FileText class="h-5 w-5 text-blue-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="font-medium text-gray-900">Cost Breakdown</p>
                                <p class="text-sm text-gray-600">All verified expenses</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <FileText class="h-5 w-5 text-blue-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="font-medium text-gray-900">Supporting Documents</p>
                                <p class="text-sm text-gray-600">Receipts, invoices, bank statements</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <FileText class="h-5 w-5 text-blue-600 flex-shrink-0 mt-1" />
                            <div>
                                <p class="font-medium text-gray-900">Net Profit Calculation</p>
                                <p class="text-sm text-gray-600">Revenue minus costs</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-sm text-gray-600">
                            MyGrowNet will verify your report. Once approved, profit sharing is calculated and paid.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Step 7: Profit Sharing -->
            <div class="mb-16">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center justify-center w-16 h-16 bg-green-600 text-white rounded-full text-2xl font-bold">
                        7
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Receive Your Profit Share</h2>
                        <p class="text-gray-600">Get paid within 14 days of report approval</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-8 ml-20">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Example Calculation:</h3>
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-700">Total Revenue:</span>
                                <span class="font-semibold">K15,000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-700">Total Costs:</span>
                                <span class="font-semibold">K10,000</span>
                            </div>
                            <div class="border-t border-gray-300 pt-3 flex justify-between">
                                <span class="font-semibold text-gray-900">Net Profit:</span>
                                <span class="font-bold text-green-600">K5,000</span>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-300">
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-700">Your Share (65%):</span>
                                    <span class="font-bold text-blue-600">K3,250</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700">MyGrowNet Share (35%):</span>
                                    <span class="font-semibold">K1,750</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 bg-green-50 border-l-4 border-green-600 p-4">
                        <p class="text-sm text-green-900">
                            <strong>Success!</strong> Your profit share is paid to your MyGrow Wallet. You can withdraw or use for other platform services.
                        </p>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg p-8 text-center">
                <h2 class="text-2xl font-bold mb-4">Ready to Get Started?</h2>
                <p class="text-lg mb-6 text-blue-100">
                    Join MyGrowNet and access business funding today
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        :href="route('mygrownet.bgf.create')"
                        class="inline-flex items-center justify-center px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition"
                    >
                        Apply Now
                    </Link>
                    <Link
                        :href="route('bgf.terms')"
                        class="inline-flex items-center justify-center px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-blue-600 transition"
                    >
                        Read Terms & Conditions
                    </Link>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>
