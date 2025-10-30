<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Category {
    id: number;
    name: string;
    slug: string;
}

defineProps<{
    categories: Category[];
}>();

const form = useForm({
    category_id: '',
    title: '',
    description: '',
    business_model: '',
    funding_target: '',
    minimum_investment: '1000',
    maximum_investment: '',
    funding_start_date: '',
    funding_end_date: '',
    expected_launch_date: '',
    risk_factors: '',
    expected_roi_months: '',
    is_featured: false,
});

const submit = () => {
    form.post(route('admin.ventures.store'));
};
</script>

<template>
    <Head title="Create Venture" />

    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('admin.ventures.index')"
                        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900"
                    >
                        <ArrowLeftIcon class="h-4 w-4" />
                        Back to Ventures
                    </Link>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">Create New Venture</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Add a new business venture for member investment
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 text-lg font-semibold text-gray-900">Basic Information</h2>
                        
                        <div class="space-y-6">
                            <!-- Category -->
                            <div>
                                <label for="category_id" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Category <span class="text-red-600">*</span>
                                </label>
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                    required
                                >
                                    <option value="" class="text-gray-500">Select a category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.category_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.category_id }}
                                </p>
                            </div>

                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Venture Title <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                    placeholder="e.g., Organic Vegetable Farm"
                                    required
                                />
                                <p v-if="form.errors.title" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Description <span class="text-red-600">*</span>
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="5"
                                    class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                    placeholder="Provide a detailed description of the venture, its goals, and expected outcomes..."
                                    required
                                ></textarea>
                                <p class="mt-1 text-xs text-gray-500">Describe what makes this venture unique and attractive to investors</p>
                                <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Business Model -->
                            <div>
                                <label for="business_model" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Business Model
                                </label>
                                <textarea
                                    id="business_model"
                                    v-model="form.business_model"
                                    rows="4"
                                    class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                    placeholder="Explain how the business will generate revenue and achieve profitability..."
                                ></textarea>
                                <p class="mt-1 text-xs text-gray-500">Detail the revenue streams and profit generation strategy</p>
                                <p v-if="form.errors.business_model" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.business_model }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Details -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 text-lg font-semibold text-gray-900">Financial Details</h2>
                        
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Funding Target -->
                            <div>
                                <label for="funding_target" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Funding Target (ZMW) <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">K</span>
                                    <input
                                        id="funding_target"
                                        v-model="form.funding_target"
                                        type="number"
                                        step="0.01"
                                        min="1000"
                                        class="block w-full rounded-lg border-gray-300 bg-white pl-8 pr-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                        placeholder="50000"
                                        required
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Total amount needed to fund this venture</p>
                                <p v-if="form.errors.funding_target" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.funding_target }}
                                </p>
                            </div>

                            <!-- Minimum Investment -->
                            <div>
                                <label for="minimum_investment" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Minimum Investment (ZMW) <span class="text-red-600">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">K</span>
                                    <input
                                        id="minimum_investment"
                                        v-model="form.minimum_investment"
                                        type="number"
                                        step="0.01"
                                        min="100"
                                        class="block w-full rounded-lg border-gray-300 bg-white pl-8 pr-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                        required
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Minimum amount a member can invest</p>
                                <p v-if="form.errors.minimum_investment" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.minimum_investment }}
                                </p>
                            </div>

                            <!-- Maximum Investment -->
                            <div>
                                <label for="maximum_investment" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Maximum Investment (ZMW)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">K</span>
                                    <input
                                        id="maximum_investment"
                                        v-model="form.maximum_investment"
                                        type="number"
                                        step="0.01"
                                        class="block w-full rounded-lg border-gray-300 bg-white pl-8 pr-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                        placeholder="Leave empty for no limit"
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Optional cap on individual investments</p>
                                <p v-if="form.errors.maximum_investment" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.maximum_investment }}
                                </p>
                            </div>

                            <!-- Expected ROI Months -->
                            <div>
                                <label for="expected_roi_months" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Expected ROI Timeline (Months)
                                </label>
                                <input
                                    id="expected_roi_months"
                                    v-model="form.expected_roi_months"
                                    type="number"
                                    min="1"
                                    class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                    placeholder="12"
                                />
                                <p class="mt-1 text-xs text-gray-500">Expected time to return on investment</p>
                                <p v-if="form.errors.expected_roi_months" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.expected_roi_months }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 text-lg font-semibold text-gray-900">Timeline</h2>
                        
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <!-- Funding Start Date -->
                            <div>
                                <label for="funding_start_date" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Funding Start Date
                                </label>
                                <input
                                    id="funding_start_date"
                                    v-model="form.funding_start_date"
                                    type="date"
                                    class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                />
                                <p class="mt-1 text-xs text-gray-500">When funding opens</p>
                                <p v-if="form.errors.funding_start_date" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.funding_start_date }}
                                </p>
                            </div>

                            <!-- Funding End Date -->
                            <div>
                                <label for="funding_end_date" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Funding End Date
                                </label>
                                <input
                                    id="funding_end_date"
                                    v-model="form.funding_end_date"
                                    type="date"
                                    class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                />
                                <p class="mt-1 text-xs text-gray-500">When funding closes</p>
                                <p v-if="form.errors.funding_end_date" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.funding_end_date }}
                                </p>
                            </div>

                            <!-- Expected Launch Date -->
                            <div>
                                <label for="expected_launch_date" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Expected Launch Date
                                </label>
                                <input
                                    id="expected_launch_date"
                                    v-model="form.expected_launch_date"
                                    type="date"
                                    class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                />
                                <p class="mt-1 text-xs text-gray-500">When business starts</p>
                                <p v-if="form.errors.expected_launch_date" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.expected_launch_date }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Risk Factors -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 text-lg font-semibold text-gray-900">Risk Assessment</h2>
                        
                        <div>
                            <label for="risk_factors" class="block text-sm font-semibold text-gray-900 mb-2">
                                Risk Factors
                            </label>
                            <textarea
                                id="risk_factors"
                                v-model="form.risk_factors"
                                rows="5"
                                class="block w-full rounded-lg border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                placeholder="List potential risks and mitigation strategies..."
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">Identify key risks and how they will be managed</p>
                            <p v-if="form.errors.risk_factors" class="mt-2 text-sm text-red-600">
                                {{ form.errors.risk_factors }}
                            </p>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 text-lg font-semibold text-gray-900">Settings</h2>
                        
                        <div class="flex items-start">
                            <div class="flex h-6 items-center">
                                <input
                                    id="is_featured"
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            <div class="ml-3">
                                <label for="is_featured" class="block text-sm font-semibold text-gray-900">
                                    Feature this venture
                                </label>
                                <p class="text-xs text-gray-500">Featured ventures appear prominently on the marketplace</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-6">
                        <Link
                            :href="route('admin.ventures.index')"
                            class="rounded-lg bg-white px-6 py-3 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Venture' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
