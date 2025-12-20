<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';

const lastUpdated = 'December 19, 2025';

const sections = [
    {
        title: '1. Information We Collect',
        content: `We collect information to provide and improve our services:

**Personal Information:**
• Name, email address, phone number
• Delivery addresses
• Payment information (processed securely, not stored)
• Government ID for seller verification (KYC)

**Usage Information:**
• Device information and IP address
• Browser type and settings
• Pages visited and features used
• Search queries and interactions

**Transaction Information:**
• Purchase history
• Order details and communications
• Reviews and ratings`
    },
    {
        title: '2. How We Use Your Information',
        content: `We use collected information to:

• Process transactions and deliver orders
• Verify seller identities (KYC)
• Communicate about orders and account updates
• Provide customer support
• Improve our platform and services
• Detect and prevent fraud
• Comply with legal obligations
• Send marketing communications (with your consent)

We do not sell your personal information to third parties.`
    },
    {
        title: '3. Information Sharing',
        content: `We may share your information with:

**Sellers/Buyers:** Name and delivery address to fulfill orders
**Payment Processors:** To process transactions securely
**Service Providers:** Who help us operate the platform
**Legal Authorities:** When required by law or to protect rights

We require all third parties to respect your privacy and handle data securely.`
    },
    {
        title: '4. Data Security',
        content: `We implement security measures to protect your data:

• Encryption of sensitive data in transit and at rest
• Secure payment processing (we don't store card details)
• Regular security audits and updates
• Access controls and authentication
• Employee training on data protection

While we strive to protect your data, no system is 100% secure. Please use strong passwords and protect your account credentials.`
    },
    {
        title: '5. Your Rights',
        content: `You have the right to:

• Access your personal data
• Correct inaccurate information
• Delete your account and data
• Opt out of marketing communications
• Request data portability
• Withdraw consent where applicable

To exercise these rights, contact us at privacy@mygrownet.com.`
    },
    {
        title: '6. Cookies & Tracking',
        content: `We use cookies and similar technologies to:

• Keep you logged in
• Remember your preferences
• Analyze platform usage
• Improve user experience

You can control cookies through your browser settings. Disabling cookies may affect platform functionality.`
    },
    {
        title: '7. Data Retention',
        content: `We retain your data for as long as:

• Your account is active
• Needed to provide services
• Required by law (e.g., financial records)
• Necessary to resolve disputes

After account deletion, we may retain anonymized data for analytics purposes.`
    },
    {
        title: '8. Children\'s Privacy',
        content: `Our platform is not intended for children under 18. We do not knowingly collect information from children. If you believe a child has provided us with personal information, please contact us immediately.`
    },
    {
        title: '9. International Users',
        content: `Our platform is operated from Zambia. If you access the platform from outside Zambia, your information may be transferred to and processed in Zambia. By using the platform, you consent to this transfer.`
    },
    {
        title: '10. Changes to This Policy',
        content: `We may update this Privacy Policy from time to time. We will notify you of significant changes via email or platform notification. Your continued use of the platform after changes constitutes acceptance of the updated policy.`
    },
    {
        title: '11. Contact Us',
        content: `For privacy-related questions or concerns:

Email: privacy@mygrownet.com
Address: Lusaka, Zambia

We aim to respond to all privacy inquiries within 5 business days.`
    },
];
</script>

<template>
    <Head title="Privacy Policy - Marketplace" />
    
    <MarketplaceLayout>
        <div class="bg-gray-50 min-h-screen">
            <!-- Header -->
            <div class="bg-white border-b">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Privacy Policy</h1>
                    <p class="text-gray-500">Last updated: {{ lastUpdated }}</p>
                </div>
            </div>

            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="bg-white rounded-xl shadow-sm p-8">
                    <div class="prose prose-gray max-w-none">
                        <p class="text-gray-600 mb-8">
                            At MyGrowNet Marketplace, we take your privacy seriously. This Privacy Policy explains 
                            how we collect, use, and protect your personal information when you use our platform.
                        </p>

                        <div class="space-y-8">
                            <section v-for="section in sections" :key="section.title">
                                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ section.title }}</h2>
                                <div class="text-gray-600 whitespace-pre-line" v-html="section.content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')"></div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
