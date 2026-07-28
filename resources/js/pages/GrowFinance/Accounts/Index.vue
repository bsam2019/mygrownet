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

            <!-- Accounts Tree -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <ul v-if="treeAccounts.length > 0" class="divide-y divide-gray-100">
                    <template v-for="account in treeAccounts" :key="account.id">
                        <!-- Parent / Header row -->
                        <li
                            :class="[
                                'transition-colors',
                                account.level <= 1 ? 'bg-gray-50' : '',
                            ]"
                        >
                            <div
                                class="flex items-center justify-between p-4"
                                :style="{ paddingLeft: `${12 + (account.level - 1) * 24}px` }"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <button
                                        v-if="account.children_count > 0"
                                        @click="toggleExpand(account.id)"
                                        class="shrink-0 p-1 text-gray-400 hover:text-gray-600 transition-colors"
                                        :aria-label="expanded[account.id] ? 'Collapse' : 'Expand'"
                                    >
                                        <ChevronDownIcon
                                            v-if="expanded[account.id]"
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                        <ChevronRightIcon
                                            v-else
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                    </button>
                                    <span v-else class="w-6 shrink-0"></span>
                                    <div :class="[
                                        'w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold shrink-0',
                                        getTypeColor(account.type)
                                    ]">
                                        {{ account.code }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium text-gray-900 truncate">{{ account.name }}</p>
                                            <span
                                                v-if="account.is_contra"
                                                class="text-[10px] px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700 font-medium shrink-0"
                                            >Contra</span>
                                            <span
                                                v-if="account.is_system && account.level <= 1"
                                                class="text-[10px] px-1.5 py-0.5 rounded bg-blue-100 text-blue-600 font-medium shrink-0"
                                            >Header</span>
                                        </div>
                                        <p class="text-xs text-gray-500 truncate">{{ account.statement_category || account.type }}</p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-4">
                                    <p v-if="account.level > 1" :class="[
                                        'font-semibold',
                                        account.current_balance >= 0 ? 'text-gray-900' : 'text-red-600'
                                    ]">
                                        {{ formatMoney(account.current_balance) }}
                                    </p>
                                    <div class="flex items-center gap-2 justify-end mt-1">
                                        <button
                                            @click="toggleActive(account)"
                                            :class="[
                                                'text-xs px-2 py-0.5 rounded-full font-medium',
                                                account.is_active
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-gray-100 text-gray-500'
                                            ]"
                                        >
                                            {{ account.is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                        <Link
                                            :href="route('growfinance.accounts.show', account.id)"
                                            class="text-xs text-amber-600 hover:text-amber-700 font-medium"
                                        >
                                            View
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </template>
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
import { Link, router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { PlusIcon, ChevronDownIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

interface Account {
    id: number;
    code: string;
    name: string;
    type: string;
    category: string | null;
    statement_category: string | null;
    normal_balance: string;
    parent_id: number | null;
    level: number;
    path: string | null;
    current_balance: number;
    is_system: boolean;
    is_active: boolean;
    children_count?: number;
    is_contra?: boolean;
}

interface Props {
    accounts: Account[];
    accountTypes: Array<{ value: string; label: string; color: string }>;
}

const props = defineProps<Props>();

const selectedType = ref('asset');
const expanded = ref<Record<number, boolean>>({});

const treeAccounts = computed(() => {
    const filtered = props.accounts.filter(a => a.type === selectedType.value);

    const withFlags = filtered.map(a => ({
        ...a,
        is_contra: (a.normal_balance === 'debit' && !['asset', 'expense'].includes(a.type))
            || (a.normal_balance === 'credit' && ['asset', 'expense'].includes(a.type)),
    }));

    const childrenMap: Record<number, Account[]> = {};
    const roots: Account[] = [];

    withFlags.forEach(a => {
        if (a.parent_id) {
            if (!childrenMap[a.parent_id]) childrenMap[a.parent_id] = [];
            childrenMap[a.parent_id].push(a);
        } else {
            roots.push(a);
        }
    });

    Object.values(childrenMap).forEach(children => {
        children.sort((a, b) => a.code.localeCompare(b.code));
    });

    const withCounts = withFlags.map(a => ({
        ...a,
        children_count: (childrenMap[a.id] || []).length,
    }));

    const result: (Account & { children_count: number; is_contra: boolean })[] = [];

    const flatten = (accounts: Account[], depth: number) => {
        accounts.forEach(a => {
            const full = withCounts.find(w => w.id === a.id)!;
            result.push(full);
            if (expanded.value[full.id] !== false && childrenMap[full.id]) {
                flatten(childrenMap[full.id], depth + 1);
            }
        });
    };

    roots.sort((a, b) => a.code.localeCompare(b.code));
    withCounts.forEach(a => {
        if (!a.parent_id && a.level <= 1) {
            expanded.value[a.id] = expanded.value[a.id] ?? true;
        }
    });
    flatten(roots, 0);

    return result;
});

const toggleExpand = (id: number) => {
    expanded.value[id] = !expanded.value[id];
};

const toggleActive = (account: Account) => {
    router.put(route('growfinance.accounts.update', account.id), {
        is_active: !account.is_active,
    });
};

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
