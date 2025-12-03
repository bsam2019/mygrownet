<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Chart of Accounts</h1>
                    <p class="text-gray-500 text-sm">Your accounting structure</p>
                </div>
                <button 
                    @click="router.visit(route('growfinance.accounts.create'))"
                    class="p-3 bg-amber-500 text-white rounded-xl shadow-lg shadow-amber-500/30 active:scale-95 transition-transform"
                    aria-label="Add account"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <!-- Account Type Tabs -->
            <div class="flex gap-2 overflow-x-auto pb-2 mb-4 -mx-4 px-4 scrollbar-hide">
                <button
                    v-for="type in accountTypes"
                    :key="type.value"
                    @click="selectedType = type.value"
                    :class="[
                        'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                        selectedType === type.value 
                            ? `bg-${type.color}-500 text-white` 
                            : 'bg-white text-gray-600'
                    ]"
                >
                    {{ type.label }}
                </button>
            </div>

            <!-- Accounts List -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <ul v-if="filteredAccounts.length > 0" class="divide-y divide-gray-100">
                    <li v-for="account in filteredAccounts" :key="account.id" class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold',
                                    getTypeColor(account.type)
                                ]">
                                    {{ account.code }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ account.name }}</p>
                                    <p class="text-xs text-gray-500">{{ account.category || account.type }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p :class="[
                                    'font-semibold',
                                    account.current_balance >= 0 ? 'text-gray-900' : 'text-red-600'
                                ]">
                                    {{ formatMoney(account.current_balance) }}
                                </p>
                                <span v-if="account.is_system" class="text-xs text-gray-400">System</span>
                            </div>
                        </div>
                    </li>
                </ul>
                <div v-else class="p-8 text-center">
                    <p class="text-gray-500 text-sm">No accounts in this category</p>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

interface Account {
    id: number;
    code: string;
    name: string;
    type: string;
    category: string | null;
    current_balance: number;
    is_system: boolean;
}

interface Props {
    accounts: Record<string, Account[]>;
    accountTypes: Array<{ value: string; label: string; color: string }>;
}

const props = defineProps<Props>();

const selectedType = ref('asset');

const filteredAccounts = computed(() => {
    return props.accounts[selectedType.value] || [];
});

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        asset: 'bg-blue-100 text-blue-600',
        liability: 'bg-red-100 text-red-600',
        equity: 'bg-purple-100 text-purple-600',
        income: 'bg-emerald-100 text-emerald-600',
        expense: 'bg-amber-100 text-amber-600',
    };
    return colors[type] || 'bg-gray-100 text-gray-600';
};

const formatMoney = (amount: number) => {
    return 'K' + Math.abs(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
