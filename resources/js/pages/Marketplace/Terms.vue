<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';

const lastUpdated = 'December 19, 2025';

const sections = [
    {
        title: '1. Acceptance of Terms',
        content: `By accessing or using MyGrowNet Marketplace ("the Platform"), you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use the Platform. We reserve the right to modify these terms at any time, and your continued use of the Platform constitutes acceptance of any changes.`
    },
    {
        title: '2. Account Registration',
        content: `To use certain features of the Platform, you must register for an account. You agree to:
        
• Provide accurate, current, and complete information during registration
• Maintain and promptly update your account information
• Keep your password secure and confidential
• Accept responsibility for all activities under your account
• Notify us immediately of any unauthorized use of your account

We reserve the right to suspend or terminate accounts that violate these terms or engage in fraudulent activity.`
    },
    {
        title: '3. Buyer Terms',
        content: `As a buyer on the Platform, you agree to:

• Pay for items you purchase in full at the time of checkout
• Provide accurate delivery information
• Inspect items upon delivery and confirm receipt within 7 days
• Communicate honestly with sellers regarding any issues
• Not engage in fraudulent chargebacks or false disputes
• Respect the escrow process and confirmation timelines

Your payment is held in escrow until you confirm delivery or the 7-day confirmation period expires.`
    },
    {
        title: '4. Seller Terms',
        content: `As a seller on the Platform, you agree to:

• Complete KYC verification before listing products
• Provide accurate product descriptions and images
• Ship orders within the stated timeframe (typically 2 business days)
• Maintain adequate stock for listed products
• Respond to buyer inquiries within 24 hours
• Accept returns for items that don't match descriptions
• Pay applicable commission fees (5% per sale)
• Comply with all applicable laws and regulations

We reserve the right to remove listings or suspend seller accounts that violate these terms.`
    },
    {
        title: '5. Escrow & Payments',
        content: `The Platform uses an escrow system to protect both buyers and sellers:

• Buyer payments are held securely until delivery is confirmed
• Sellers receive payment after buyer confirmation or after 7 days (whichever comes first)
• Disputes must be raised within the 7-day confirmation window
• Refunds are processed within 3-5 business days when applicable
• We accept Mobile Money (MTN MoMo, Airtel Money) and bank transfers

The Platform is not a bank and does not pay interest on held funds.`
    },
    {
        title: '6. Prohibited Items & Activities',
        content: `The following are prohibited on the Platform:

• Illegal items or substances
• Counterfeit or stolen goods
• Weapons, explosives, or dangerous materials
• Adult content or services
• Items that infringe intellectual property rights
• Fraudulent listings or misleading descriptions
• Price manipulation or fake reviews
• Harassment of other users
• Any activity that violates Zambian law`
    },
    {
        title: '7. Dispute Resolution',
        content: `If a dispute arises between a buyer and seller:

1. Parties should first attempt to resolve the issue directly
2. If unresolved, either party may open a formal dispute
3. Our support team will review evidence from both parties
4. A decision will be made within 5 business days
5. Decisions are final and binding

We reserve the right to hold funds during dispute resolution and release them according to our findings.`
    },
    {
        title: '8. Limitation of Liability',
        content: `To the maximum extent permitted by law:

• The Platform is provided "as is" without warranties
• We are not liable for transactions between buyers and sellers
• We are not responsible for product quality or seller conduct
• Our liability is limited to the fees paid to us
• We are not liable for indirect, incidental, or consequential damages

Users are responsible for their own due diligence when transacting.`
    },
    {
        title: '9. Intellectual Property',
        content: `• The Platform and its content are owned by MyGrowNet
• Users retain ownership of content they upload
• By uploading content, you grant us a license to use it on the Platform
• You may not copy, modify, or distribute Platform content without permission
• Report intellectual property violations to our support team`
    },
    {
        title: '10. Termination',
        content: `We may suspend or terminate your account at any time for:

• Violation of these Terms of Service
• Fraudulent or illegal activity
• Extended inactivity
• At our sole discretion with reasonable notice

Upon termination, pending transactions will be completed or refunded as appropriate.`
    },
    {
        title: '11. Governing Law',
        content: `These Terms of Service are governed by the laws of the Republic of Zambia. Any disputes shall be resolved in the courts of Zambia. By using the Platform, you consent to the jurisdiction of Zambian courts.`
    },
    {
        title: '12. Contact Us',
        content: `For questions about these Terms of Service, contact us at:

Email: legal@mygrownet.com
Address: Lusaka, Zambia

We aim to respond to all inquiries within 2 business days.`
    },
];
</script>

<template>
    <Head title="Terms of Service - Marketplace" />
    
    <MarketplaceLayout>
        <div class="bg-gray-50 min-h-screen">
            <!-- Header -->
            <div class="bg-white border-b">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Terms of Service</h1>
                    <p class="text-gray-500">Last updated: {{ lastUpdated }}</p>
                </div>
            </div>

            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="bg-white rounded-xl shadow-sm p-8">
                    <div class="prose prose-gray max-w-none">
                        <p class="text-gray-600 mb-8">
                            Welcome to MyGrowNet Marketplace. These Terms of Service govern your use of our platform 
                            and the services we provide. Please read them carefully before using the Platform.
                        </p>

                        <div class="space-y-8">
                            <section v-for="section in sections" :key="section.title">
                                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ section.title }}</h2>
                                <div class="text-gray-600 whitespace-pre-line">{{ section.content }}</div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
