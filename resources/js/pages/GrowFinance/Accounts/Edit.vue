<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Account {
    id: number;
    code: string;
    name: string;
    type: string;
    subtype: string | null;
    description: string | null;
    is_active: boolean;
    is_system: boolean;
}

const props = defineProps<{
    account: Account;
}>();

const form = useForm({
    name: props.account.name,
    category: props.account.subtype || '',
    description: props.account.description || '',
    is_active: props.account.is_active,
});

const submit = () => {
    form.put(route('growfinance.accounts.update', props.account.id));
};
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Edit Account" />

        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Link
                    :href="route('growfinance.accounts.index')"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Account</h1>
                    <p class="text-sm text-gray-500">{{ account.code }} - {{ account.type }}</p>
                </div>
            </div>

            <!-- System Account Warning -->
            <div v-if="account.is_system" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800">
                    This is a system account. Only the name and description can be modified.
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Account Code
                    </label>
                    <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 font-mono">
                        {{ account.code }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Account codes cannot be changed</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Account Type
                    </label>
                    <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 capitalize">
                        {{ account.type }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Account types cannot be changed</p>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Account Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                        Category
                    </label>
                    <input
                        id="category"
                        v-model="form.category"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., Current Asset, Operating Expense"
                    />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Optional description for this account"
                    ></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        :disabled="account.is_system"
                    />
                    <label for="is_active" class="text-sm text-gray-700">Active account</label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <Link
                        :href="route('growfinance.accounts.index')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </GrowFinanceLayout>
</template>
