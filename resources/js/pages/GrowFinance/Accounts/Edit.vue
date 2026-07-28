<script setup lang="ts">
import { computed } from 'vue';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Account {
    id: number;
    code: string;
    name: string;
    type: string;
    normal_balance: string;
    parent_id: number | null;
    level: number;
    path: string | null;
    statement_category: string | null;
    category: string | null;
    description: string | null;
    is_active: boolean;
    is_system: boolean;
}

interface ParentAccount {
    id: number;
    code: string;
    name: string;
    type: string;
    level: number;
}

interface Props {
    account: Account;
    parentAccounts: ParentAccount[];
    statementCategories: string[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.account.name,
    normal_balance: props.account.normal_balance,
    parent_id: props.account.parent_id ?? '',
    statement_category: props.account.statement_category || '',
    category: props.account.category || '',
    description: props.account.description || '',
    is_active: props.account.is_active,
});

const filteredParents = computed(() => {
    return props.parentAccounts.filter(a => a.type === props.account.type);
});

const submit = () => {
    form.put(route('growfinance.accounts.update', props.account.id));
};
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Edit Account" />

        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-4 mb-6">
                <Link
                    :href="route('growfinance.accounts.index')"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Edit Account</h1>
                <span class="text-sm text-gray-500 font-mono">{{ account.code }}</span>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                <div v-if="account.is_system" class="p-3 bg-blue-50 text-blue-700 text-sm rounded-lg">
                    System account — only name and description can be modified.
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Account Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="normal_balance" class="block text-sm font-medium text-gray-700 mb-1">
                            Normal Balance
                        </label>
                        <select
                            id="normal_balance"
                            v-model="form.normal_balance"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                        >
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                        <p v-if="form.errors.normal_balance" class="mt-1 text-sm text-red-600">{{ form.errors.normal_balance }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Parent Account
                        </label>
                        <select
                            id="parent_id"
                            v-model="form.parent_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                        >
                            <option value="">None (top-level)</option>
                            <option v-for="parent in filteredParents" :key="parent.id" :value="parent.id">
                                {{ parent.code }} - {{ parent.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.parent_id" class="mt-1 text-sm text-red-600">{{ form.errors.parent_id }}</p>
                    </div>

                    <div>
                        <label for="statement_category" class="block text-sm font-medium text-gray-700 mb-1">
                            Statement Category
                        </label>
                        <select
                            id="statement_category"
                            v-model="form.statement_category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                        >
                            <option value="">Select category</option>
                            <option v-for="cat in statementCategories" :key="cat" :value="cat">
                                {{ cat.replace(/_/g, ' ') }}
                            </option>
                        </select>
                        <p v-if="form.errors.statement_category" class="mt-1 text-sm text-red-600">{{ form.errors.statement_category }}</p>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                    ></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500"
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
                        :disabled="form.processing || account.is_system"
                        class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </GrowFinanceLayout>
</template>
