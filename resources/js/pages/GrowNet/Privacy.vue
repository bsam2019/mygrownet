<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GrowNetGuestLayout from '@/Layouts/GrowNetGuestLayout.vue';
</script>

<template>
    <GrowNetGuestLayout>
        <Head title="Privacy Policy - GrowNet" />
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 text-sm font-medium mb-6">Privacy Policy</div>
            <h1 class="text-4xl font-bold text-slate-900 mb-8">Privacy Policy</h1>
            <div class="prose prose-slate max-w-none">
                <p class="text-slate-600 leading-relaxed">Last updated: January 2026</p>
                <h2 class="text-xl font-semibold text-slate-900 mt-8">1. Information We Collect</h2>
                <p class="text-slate-600 leading-relaxed">We collect information you provide when creating an account, including your name, email address, and phone number. We also collect usage data such as content viewed, time spent, and interactions with the Platform.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">2. How We Use Your Information</h2>
                <p class="text-slate-600 leading-relaxed">We use your information to provide and improve the Platform, personalize your experience, process payments, send service updates, and provide support.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">3. Data Sharing</h2>
                <p class="text-slate-600 leading-relaxed">We do not sell your personal information. We may share data with trusted service providers who help us operate the Platform, subject to confidentiality agreements.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">4. Data Security</h2>
                <p class="text-slate-600 leading-relaxed">We implement industry-standard security measures to protect your data. However, no method of transmission over the Internet is 100% secure.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">5. Your Rights</h2>
                <p class="text-slate-600 leading-relaxed">You have the right to access, update, or delete your personal information. You can manage your data through your account settings or by contacting us.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">6. Cookies</h2>
                <p class="text-slate-600 leading-relaxed">We use cookies to enhance your experience, analyze usage, and support authentication. You can control cookie preferences through your browser settings.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">7. Changes to This Policy</h2>
                <p class="text-slate-600 leading-relaxed">We may update this policy periodically. We will notify you of significant changes via email or through the Platform.</p>

                <h2 class="text-xl font-semibold text-slate-900 mt-8">8. Contact</h2>
                <p class="text-slate-600 leading-relaxed">For privacy-related inquiries, <Link href="/contact" class="text-violet-600 hover:text-violet-700 font-medium">contact us</Link>.</p>
            </div>
        </div>
    </GrowNetGuestLayout>
</template>
