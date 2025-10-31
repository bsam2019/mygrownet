<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { ref } from 'vue';

const props = defineProps<{
    businessTypes: Record<string, string>;
    orderTypes: Record<string, string>;
}>();

const form = useForm({
    business_name: '',
    business_description: '',
    business_type: '',
    order_type: '',
    amount_requested: '',
    member_contribution: '',
    expected_profit: '',
    completion_period_days: 60,
    feasibility_summary: '',
    order_proof: '',
    client_name: '',
    client_contact: '',
    tpin: '',
    business_account: '',
    has_business_experience: false,
    previous_projects: '',
});

const submit = () => {
    form.post(route('mygrownet.bgf.store'));
};
</script>

<template>
    <Head title="Apply for Growth Fund" />

    <AppLayout>
        <div class="py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Apply for Business Growth Fund</h1>
                    <p class="mt-2 text-gray-600">Complete the application form to access short-term business financing</p>
                    <div class="mt-4 bg-blue-50 border-l-4 border-blue-600 p-4">
                        <p class="text-sm text-blue-900">
                            <strong>Note:</strong> All fields marked with * are required. Your application will be reviewed within 3-5 business days.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Business Information -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
                            <h2 class="text-xl font-semibold text-white">1. Business Information</h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Business Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.business_name"
                                    type="text"
                                    required
                                    placeholder="Enter your business name"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors"
                                />
                                <div v-if="form.errors.business_name" class="text-red-600 text-sm mt-2 flex items-center gap-1">
                                    <span>⚠</span> {{ form.errors.business_name }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Business Type <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.business_type"
                                        required
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors"
                                    >
                                        <option value="">-- Select Business Type --</option>
                                        <option v-for="(label, value) in businessTypes" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Order Type <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.order_type"
                                        required
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors"
                                    >
                                        <option value="">-- Select Order Type --</option>
                                        <option v-for="(label, value) in orderTypes" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Business Description <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    v-model="form.business_description"
                                    rows="4"
                                    required
                                    placeholder="Provide a detailed description of your business..."
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors"
                                ></textarea>
                                <p class="text-xs text-gray-500 mt-2">Describe what your business does and the products/services you offer</p>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200">
                        <div class="bg-gradient-to-r from-green-600 to-emerald-700 px-6 py-4 rounded-t-lg">
                            <h2 class="text-xl font-semibold text-white">2. Financial Information</h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Amount Requested (ZMW) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 font-semibold">K</span>
                                        <input
                                            v-model="form.amount_requested"
                                            type="number"
                                            min="1000"
                                            max="50000"
                                            step="100"
                                            required
                                            placeholder="10000"
                                            class="w-full pl-8 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-colors"
                                        />
                                    </div>
                                    <p class="text-xs text-gray-600 mt-2 bg-gray-50 px-3 py-2 rounded">
                                        💡 Min: K1,000 | Max: K50,000
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Your Contribution (ZMW) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 font-semibold">K</span>
                                        <input
                                            v-model="form.member_contribution"
                                            type="number"
                                            min="0"
                                            step="100"
                                            required
                                            placeholder="3000"
                                            class="w-full pl-8 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-colors"
                                        />
                                    </div>
                                    <p class="text-xs text-gray-600 mt-2 bg-gray-50 px-3 py-2 rounded">
                                        💡 Minimum 20-30% of total project cost
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Expected Profit (ZMW) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 font-semibold">K</span>
                                        <input
                                            v-model="form.expected_profit"
                                            type="number"
                                            min="0"
                                            step="100"
                                            required
                                            placeholder="5000"
                                            class="w-full pl-8 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-colors"
                                        />
                                    </div>
                                    <p class="text-xs text-gray-600 mt-2 bg-gray-50 px-3 py-2 rounded">
                                        💡 Estimated net profit after costs
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Completion Period (Days) <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.completion_period_days"
                                        type="number"
                                        min="30"
                                        max="120"
                                        required
                                        placeholder="60"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-colors"
                                    />
                                    <p class="text-xs text-gray-600 mt-2 bg-gray-50 px-3 py-2 rounded">
                                        💡 Project timeline: 30-120 days
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Details -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200">
                        <div class="bg-gradient-to-r from-purple-600 to-indigo-700 px-6 py-4 rounded-t-lg">
                            <h2 class="text-xl font-semibold text-white">3. Project Details</h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Feasibility Summary <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    v-model="form.feasibility_summary"
                                    rows="6"
                                    required
                                    minlength="100"
                                    placeholder="Describe your business plan, market analysis, target customers, competitive advantage, and how you'll achieve profitability..."
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-colors"
                                ></textarea>
                                <p class="text-xs text-gray-600 mt-2 bg-gray-50 px-3 py-2 rounded">
                                    💡 Minimum 100 characters. Be detailed and specific about your business opportunity.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Client Name
                                    </label>
                                    <input
                                        v-model="form.client_name"
                                        type="text"
                                        placeholder="Enter client/customer name"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-colors"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">For order fulfillment projects</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Client Contact
                                    </label>
                                    <input
                                        v-model="form.client_contact"
                                        type="text"
                                        placeholder="Phone or email"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-colors"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">For verification purposes</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        TPIN
                                    </label>
                                    <input
                                        v-model="form.tpin"
                                        type="text"
                                        placeholder="Tax ID number"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-colors"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">If registered business</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Business Account
                                    </label>
                                    <input
                                        v-model="form.business_account"
                                        type="text"
                                        placeholder="Bank account number"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-colors"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">For disbursements</p>
                                </div>
                            </div>

                            <div class="bg-purple-50 border-2 border-purple-200 rounded-lg p-4">
                                <label class="flex items-start cursor-pointer">
                                    <input
                                        v-model="form.has_business_experience"
                                        type="checkbox"
                                        class="mt-1 rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                    />
                                    <span class="ml-3 text-sm font-medium text-gray-900">
                                        I have previous business experience
                                        <span class="block text-xs text-gray-600 font-normal mt-1">Check this if you've run similar projects before</span>
                                    </span>
                                </label>
                            </div>

                            <div v-if="form.has_business_experience" class="animate-fadeIn">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Previous Projects
                                </label>
                                <textarea
                                    v-model="form.previous_projects"
                                    rows="4"
                                    placeholder="Describe your previous business projects, outcomes, and lessons learned..."
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-colors"
                                ></textarea>
                                <p class="text-xs text-gray-600 mt-2 bg-gray-50 px-3 py-2 rounded">
                                    💡 This helps us understand your track record and increases your approval chances.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <a
                                :href="route('mygrownet.bgf.index')"
                                class="text-gray-600 hover:text-gray-900 font-medium transition-colors"
                            >
                                ← Cancel and Go Back
                            </a>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-lg font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transition-all transform hover:scale-105"
                            >
                                <span v-if="form.processing">
                                    <svg class="animate-spin inline-block h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Submitting Application...
                                </span>
                                <span v-else>
                                    Submit Application →
                                </span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 text-center mt-4">
                            By submitting, you agree to the <a href="/bgf/terms" target="_blank" class="text-blue-600 hover:underline">Terms & Conditions</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
