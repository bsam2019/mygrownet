<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import PrimeEdgeGuestLayout from '@/layouts/PrimeEdgeGuestLayout.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    company: '',
    service: '',
    message: '',
});

const submit = () => {
    form.post(route('primeedge.public.contact.submit'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <PrimeEdgeGuestLayout>
        <Head title="Contact - PrimeEdge Advisory" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-12">
                <span class="text-emerald-600 font-semibold text-sm uppercase tracking-wider">Get in Touch</span>
                <h1 class="text-4xl font-bold text-gray-900 mt-2 mb-4">Let's Start a Conversation</h1>
                <p class="text-gray-600 max-w-xl mx-auto">Ready to grow your business or take control of your finances? Fill out the form below and we'll get back to you within 24 hours.</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
                        <form @submit.prevent="submit" class="space-y-5">
                            <div>
                                <label for="contact-name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                                <input v-model="form.name" id="contact-name" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="Your name" />
                                <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="contact-email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                    <input v-model="form.email" id="contact-email" type="email" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="you@example.com" />
                                    <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
                                </div>
                                <div>
                                    <label for="contact-phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input v-model="form.phone" id="contact-phone" type="tel" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="+260 XXX XXX XXX" />
                                </div>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="contact-company" class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                    <input v-model="form.company" id="contact-company" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" placeholder="Your company name" />
                                </div>
                                <div>
                                    <label for="contact-service" class="block text-sm font-medium text-gray-700 mb-1">Service Interest</label>
                                    <select v-model="form.service" id="contact-service" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-white">
                                        <option value="">Select a service</option>
                                        <option value="business-advisory">Business Advisory</option>
                                        <option value="accounting-compliance">Accounting & Compliance</option>
                                        <option value="personal-financial">Personal Financial Advisory</option>
                                        <option value="sme-mentorship">SME Mentorship</option>
                                        <option value="virtual-cfo">Virtual CFO Services</option>
                                        <option value="tax-advisory">Tax Advisory</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="contact-message" class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                                <textarea v-model="form.message" id="contact-message" rows="4" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all resize-none" placeholder="Tell us about your needs..."></textarea>
                                <p v-if="form.errors.message" class="text-sm text-red-600 mt-1">{{ form.errors.message }}</p>
                            </div>
                            <button type="submit" :disabled="form.processing" class="w-full px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold rounded-xl hover:from-emerald-700 hover:to-emerald-800 transition-all disabled:opacity-50 shadow-md">
                                {{ form.processing ? 'Sending...' : 'Send Message' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <h3 class="font-semibold text-gray-900 mb-2">Email</h3>
                        <p class="text-sm text-gray-600"><a href="mailto:info@primeedgeadvisory.com" class="text-emerald-700 hover:underline">info@primeedgeadvisory.com</a></p>
                    </div>
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                        <h3 class="font-semibold text-gray-900 mb-2">Location</h3>
                        <p class="text-sm text-gray-600">Lusaka, Zambia</p>
                    </div>
                    <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-xl p-6 text-white">
                        <h3 class="font-semibold mb-1">Prefer a call?</h3>
                        <p class="text-sm text-emerald-100">Schedule a free 15-minute consultation to discuss your needs.</p>
                    </div>
                </div>
            </div>
        </div>
    </PrimeEdgeGuestLayout>
</template>