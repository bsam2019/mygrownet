<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Venture {
    id: number;
    category_id: number;
    title: string;
    description: string;
    business_model: string | null;
    funding_target: number;
    minimum_investment: number;
    maximum_investment: number | null;
    funding_start_date: string | null;
    funding_end_date: string | null;
    expected_launch_date: string | null;
    risk_factors: string | null;
    expected_roi_months: number | null;
    is_featured: boolean;
    status: string;
    total_raised: number;
    investor_count: number;
}

const props = defineProps<{
    venture: Venture;
    categories: Category[];
}>();

const form = useForm({
    category_id: props.venture.category_id,
    title: props.venture.title,
    description: props.venture.description,
    business_model: props.venture.business_model || '',
    funding_target: props.venture.funding_target,
    minimum_investment: props.venture.minimum_investment,
    maximum_investment: props.venture.maximum_investment || '',
    funding_start_date: props.venture.funding_start_date || '',
    funding_end_date: props.venture.funding_end_date || '',
    expected_launch_date: props.venture.expected_launch_date || '',
    risk_factors: props.venture.risk_factors || '',
    expected_roi_months: props.venture.expected_roi_months || '',
    is_featured: props.venture.is_featured,
});

const submit = () => {
    form.put(route('admin.ventures.update', props.venture.id));
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800',
        review: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        funding: 'bg-green-100 text-green-800',
        funded: 'bg-indigo-100 text-indigo-800',
        active: 'bg-emerald-100 text-emerald-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Head :title="`Edit ${venture.title}`" />

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
                    <div class="mt-2 flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Edit Venture</h1>
                            <p class="mt-1 text-sm text-gray-600">
                                Update venture information
                            </p>
                        </div>
                        <span
                            :class="[
                                'inline-flex rounded-full px-3 py-1 text-sm font-semibold',
                                getStatusColor(venture.status),
                            ]"
                        >
                            {{ venture.status }}
                        </span>
                    </div>
                </div>

                <!-- Stats Bar -->
                <div class="mb-6 grid grid-cols-3 gap-4 rounded-lg bg-white p-4 shadow">
                    <div>
                        <div class="text-sm text-gray-500">Total Raised</div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ formatCurrency(venture.total_raised) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Target</div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ formatCurrency(venture.funding_target) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Investors</div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ venture.investor_count }}
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h2 class="mb-4 text-lg font-medium text-gray-900">Basic Information</h2>
                        
                        <div class="space-y-4">
                            <!-- Category -->
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">
                                    Category <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    <option value="">Select a category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.category_id }}
                                </p>
                            </div>

                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">
                                    Venture Title <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                />
                                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">
                                    Description <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                ></textarea>
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Business Model -->
                            <div>
                                <label for="business_model" class="block text-sm font-medium text-gray-700">
                                    Business Model
                                </label>
                                <textarea
                                    id="business_model"
                                    v-model="form.business_model"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                ></textarea>
                                <p v-if="form.errors.business_model" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.business_model }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Details -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h2 class="mb-4 text-lg font-medium text-gray-900">Financial Details</h2>
                        
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Funding Target -->
                            <div>
                                <label for="funding_target" class="block text-sm font-medium text-gray-700">
                                    Funding Target (ZMW) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="funding_target"
                                    v-model="form.funding_target"
                                    type="number"
                                    step="0.01"
                                    min="1000"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                />
                                <p v-if="form.errors.funding_target" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.funding_target }}
                                </p>
                            </div>

                            <!-- Minimum Investment -->
                            <div>
                                <label for="minimum_investment" class="block text-sm font-medium text-gray-700">
                                    Minimum Investment (ZMW) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="minimum_investment"
                                    v-model="form.minimum_investment"
                                    type="number"
                                    step="0.01"
                                    min="100"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                />
                                <p v-if="form.errors.minimum_investment" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.minimum_investment }}
                                </p>
                            </div>

                            <!-- Maximum Investment -->
                            <div>
                                <label for="maximum_investment" class="block text-sm font-medium text-gray-700">
                                    Maximum Investment (ZMW)
                                </label>
                                <input
                                    id="maximum_investment"
                                    v-model="form.maximum_investment"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <p v-if="form.errors.maximum_investment" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.maximum_investment }}
                                </p>
                            </div>

                            <!-- Expected ROI Months -->
                            <div>
                                <label for="expected_roi_months" class="block text-sm font-medium text-gray-700">
                                    Expected ROI (Months)
                                </label>
                                <input
                                    id="expected_roi_months"
                                    v-model="form.expected_roi_months"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <p v-if="form.errors.expected_roi_months" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.expected_roi_months }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h2 class="mb-4 text-lg font-medium text-gray-900">Timeline</h2>
                        
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <!-- Funding Start Date -->
                            <div>
                                <label for="funding_start_date" class="block text-sm font-medium text-gray-700">
                                    Funding Start Date
                                </label>
                                <input
                                    id="funding_start_date"
                                    v-model="form.funding_start_date"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <p v-if="form.errors.funding_start_date" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.funding_start_date }}
                                </p>
                            </div>

                            <!-- Funding End Date -->
                            <div>
                                <label for="funding_end_date" class="block text-sm font-medium text-gray-700">
                                    Funding End Date
                                </label>
                                <input
                                    id="funding_end_date"
                                    v-model="form.funding_end_date"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <p v-if="form.errors.funding_end_date" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.funding_end_date }}
                                </p>
                            </div>

                            <!-- Expected Launch Date -->
                            <div>
                                <label for="expected_launch_date" class="block text-sm font-medium text-gray-700">
                                    Expected Launch Date
                                </label>
                                <input
                                    id="expected_launch_date"
                                    v-model="form.expected_launch_date"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <p v-if="form.errors.expected_launch_date" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.expected_launch_date }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Risk Factors -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h2 class="mb-4 text-lg font-medium text-gray-900">Risk Assessment</h2>
                        
                        <div>
                            <label for="risk_factors" class="block text-sm font-medium text-gray-700">
                                Risk Factors
                            </label>
                            <textarea
                                id="risk_factors"
                                v-model="form.risk_factors"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            ></textarea>
                            <p v-if="form.errors.risk_factors" class="mt-1 text-sm text-red-600">
                                {{ form.errors.risk_factors }}
                            </p>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h2 class="mb-4 text-lg font-medium text-gray-900">Settings</h2>
                        
                        <div class="flex items-center">
                            <input
                                id="is_featured"
                                v-model="form.is_featured"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="is_featured" class="ml-2 block text-sm text-gray-700">
                                Feature this venture on the marketplace
                            </label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between border-t border-gray-200 pt-6">
                        <Link
                            :href="route('admin.ventures.index')"
                            class="rounded-lg bg-white px-6 py-3 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </Link>
                        <div class="flex gap-3">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Status Management (Admin Only) -->
                <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Venture Status Management</h2>
                    <p class="mb-6 text-sm text-gray-600">
                        Control the venture lifecycle and visibility
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <!-- Approve (from draft) -->
                        <Link
                            v-if="venture.status === 'draft'"
                            :href="route('admin.ventures.approve', venture.id)"
                            method="post"
                            as="button"
                            class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 transition-colors"
                        >
                            ✓ Approve Venture
                        </Link>

                        <!-- Launch Funding (from approved) -->
                        <Link
                            v-if="venture.status === 'approved'"
                            :href="route('admin.ventures.launch-funding', venture.id)"
                            method="post"
                            as="button"
                            class="rounded-lg bg-green-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-green-500 transition-colors"
                        >
                            🚀 Launch Funding
                        </Link>

                        <!-- Close Funding (from funding) -->
                        <Link
                            v-if="venture.status === 'funding'"
                            :href="route('admin.ventures.close-funding', venture.id)"
                            method="post"
                            as="button"
                            class="rounded-lg bg-orange-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 transition-colors"
                        >
                            Close Funding
                        </Link>

                        <!-- Activate (from funded) -->
                        <Link
                            v-if="venture.status === 'funded'"
                            :href="route('admin.ventures.activate', venture.id)"
                            method="post"
                            as="button"
                            class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition-colors"
                        >
                            ✓ Activate Venture
                        </Link>

                        <!-- Status Info -->
                        <div class="ml-auto flex items-center gap-2 rounded-lg bg-gray-50 px-4 py-3">
                            <span class="text-sm text-gray-600">Current Status:</span>
                            <span
                                :class="[
                                    'inline-flex rounded-full px-3 py-1 text-sm font-semibold',
                                    getStatusColor(venture.status),
                                ]"
                            >
                                {{ venture.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Status Flow Guide -->
                    <div class="mt-6 rounded-lg bg-blue-50 p-4">
                        <h3 class="mb-2 text-sm font-semibold text-blue-900">Venture Lifecycle:</h3>
                        <div class="flex items-center gap-2 text-xs text-blue-700">
                            <span class="rounded bg-white px-2 py-1 font-medium">Draft</span>
                            <span>→</span>
                            <span class="rounded bg-white px-2 py-1 font-medium">Approved</span>
                            <span>→</span>
                            <span class="rounded bg-white px-2 py-1 font-medium">Funding</span>
                            <span>→</span>
                            <span class="rounded bg-white px-2 py-1 font-medium">Funded</span>
                            <span>→</span>
                            <span class="rounded bg-white px-2 py-1 font-medium">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
