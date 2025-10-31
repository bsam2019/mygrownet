<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';
</script>

<template>
    <Head title="BGF Terms & Conditions - MyGrowNet" />

    <div class="min-h-screen bg-gray-50">
        <Navigation />
        
        <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="mb-8">
                    <Link :href="route('bgf.about')" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                        ← Back to BGF Overview
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Business Growth Fund</h1>
                    <h2 class="text-xl text-gray-600">Terms & Conditions</h2>
                    <p class="text-sm text-gray-500 mt-2">Last Updated: October 30, 2025</p>
                </div>

                <div class="prose prose-blue max-w-none">
                    <h3>1. Introduction</h3>
                    <p>
                        The Business Growth Fund ("BGF") is a short-term financing program offered by MyGrowNet to active members
                        for verified business orders and opportunities. By applying for BGF funding, you agree to these terms and conditions.
                    </p>

                    <h3>2. Eligibility</h3>
                    <p>To be eligible for BGF funding, you must:</p>
                    <ul>
                        <li>Be an active MyGrowNet member in good standing</li>
                        <li>Have completed required training modules</li>
                        <li>Provide a verified business opportunity or order</li>
                        <li>Contribute 20-30% of the total project cost</li>
                        <li>Have a project completion timeline of 1-4 months</li>
                        <li>Maintain minimum Lifetime Points (LP) as per membership level</li>
                    </ul>

                    <h3>3. Funding Limits</h3>
                    <ul>
                        <li><strong>Minimum:</strong> K1,000 per project</li>
                        <li><strong>Maximum:</strong> K50,000 per project</li>
                        <li><strong>Member Contribution:</strong> 20-30% of total project cost required</li>
                        <li><strong>Multiple Projects:</strong> Maximum 2 active projects at any time</li>
                    </ul>

                    <h3>4. Application Process</h3>
                    <p>Applications are evaluated based on:</p>
                    <ul>
                        <li><strong>Membership & Activity (15 points):</strong> Membership level, LP, and platform engagement</li>
                        <li><strong>Training Completion (10 points):</strong> Completed business training modules</li>
                        <li><strong>Business Viability (25 points):</strong> Market analysis, profit potential, feasibility</li>
                        <li><strong>Credibility & References (15 points):</strong> Client verification, business history</li>
                        <li><strong>Member Contribution (15 points):</strong> Percentage and source of contribution</li>
                        <li><strong>Risk Control (10 points):</strong> Risk mitigation strategies</li>
                        <li><strong>Track Record (10 points):</strong> Previous BGF projects and business experience</li>
                    </ul>
                    <p><strong>Minimum Score for Approval:</strong> 70/100</p>

                    <h3>5. Profit Sharing</h3>
                    <p>Profits are shared based on your application score:</p>
                    <ul>
                        <li><strong>Score 90-100:</strong> Member 70%, MyGrowNet 30%</li>
                        <li><strong>Score 80-89:</strong> Member 65%, MyGrowNet 35%</li>
                        <li><strong>Score 70-79:</strong> Member 60%, MyGrowNet 40%</li>
                    </ul>
                    <p>
                        <strong>Net Profit Definition:</strong> Total Revenue minus Total Costs (including materials, labor, transport, and other verified expenses).
                    </p>

                    <h3>6. Disbursement</h3>
                    <ul>
                        <li>Funds are disbursed based on approved milestones</li>
                        <li>Direct vendor payments available for materials and supplies</li>
                        <li>Member must provide receipts and documentation for all expenses</li>
                        <li>Disbursements require admin approval</li>
                    </ul>

                    <h3>7. Repayment & Profit Reporting</h3>
                    <ul>
                        <li>Member must submit profit report within 7 days of project completion</li>
                        <li>Report must include: revenue breakdown, cost breakdown, and supporting documents</li>
                        <li>MyGrowNet reserves the right to verify all financial information</li>
                        <li>Profit sharing payment due within 14 days of report approval</li>
                    </ul>

                    <h3>8. Project Completion</h3>
                    <ul>
                        <li>Projects must be completed within the approved timeline (1-4 months)</li>
                        <li>Extensions may be granted with valid reasons (max 30 days)</li>
                        <li>Failure to complete may result in penalties and future application restrictions</li>
                    </ul>

                    <h3>9. Default & Penalties</h3>
                    <p>In case of project failure or default:</p>
                    <ul>
                        <li>Member is responsible for returning the full funded amount</li>
                        <li>Late payment penalty: 5% per month on outstanding balance</li>
                        <li>Future BGF applications may be suspended</li>
                        <li>Membership benefits may be affected</li>
                    </ul>

                    <h3>10. Prohibited Uses</h3>
                    <p>BGF funds may NOT be used for:</p>
                    <ul>
                        <li>Personal expenses or consumption</li>
                        <li>Debt repayment</li>
                        <li>Speculative investments</li>
                        <li>Illegal activities</li>
                        <li>Gambling or lottery</li>
                        <li>Pyramid schemes or MLM recruitment</li>
                    </ul>

                    <h3>11. Documentation Requirements</h3>
                    <p>Members must provide:</p>
                    <ul>
                        <li>Business plan or order proof</li>
                        <li>Client contact information (for order fulfillment)</li>
                        <li>TPIN (if registered business)</li>
                        <li>Bank account details</li>
                        <li>Receipts for all project expenses</li>
                        <li>Revenue documentation (invoices, receipts)</li>
                    </ul>

                    <h3>12. Confidentiality</h3>
                    <p>
                        MyGrowNet will keep all business information confidential. However, we reserve the right to verify
                        information with clients, suppliers, and other parties as necessary.
                    </p>

                    <h3>13. Amendments</h3>
                    <p>
                        MyGrowNet reserves the right to amend these terms at any time. Members will be notified of significant
                        changes. Continued use of BGF after amendments constitutes acceptance.
                    </p>

                    <h3>14. Dispute Resolution</h3>
                    <p>
                        Any disputes arising from BGF funding will be resolved through:
                    </p>
                    <ol>
                        <li>Direct negotiation with MyGrowNet management</li>
                        <li>Mediation by an independent third party</li>
                        <li>Arbitration under Zambian law</li>
                    </ol>

                    <h3>15. Governing Law</h3>
                    <p>
                        These terms are governed by the laws of the Republic of Zambia. All parties submit to the jurisdiction
                        of Zambian courts.
                    </p>

                    <h3>16. Contact Information</h3>
                    <p>
                        For questions about BGF terms and conditions:<br>
                        Email: bgf@mygrownet.com<br>
                        Phone: +260 XXX XXX XXX
                    </p>

                    <div class="bg-blue-50 border-l-4 border-blue-600 p-4 mt-8">
                        <p class="text-sm text-blue-900">
                            <strong>Important:</strong> By submitting a BGF application, you acknowledge that you have read,
                            understood, and agree to these terms and conditions.
                        </p>
                    </div>
                </div>

                <div class="mt-8 flex gap-4">
                    <Link
                        :href="route('bgf.about')"
                        class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition"
                    >
                        Back to Overview
                    </Link>
                    <Link
                        :href="route('mygrownet.bgf.create')"
                        class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
                    >
                        Apply for Funding
                    </Link>
                </div>
            </div>
        </div>
        </div>

        <Footer />
    </div>
</template>

<style scoped>
.prose h3 {
    @apply text-xl font-bold text-gray-900 mt-8 mb-4;
}

.prose p {
    @apply text-gray-700 mb-4;
}

.prose ul, .prose ol {
    @apply ml-6 mb-4 space-y-2;
}

.prose li {
    @apply text-gray-700;
}

.prose strong {
    @apply font-semibold text-gray-900;
}
</style>
