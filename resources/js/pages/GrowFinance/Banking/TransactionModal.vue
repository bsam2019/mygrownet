<template>
    <div class="fixed inset-0 z-50 flex items-end justify-center">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="$emit('close')" />
        <div class="relative w-full max-w-md bg-white rounded-t-3xl shadow-2xl safe-area-bottom">
            <div class="flex justify-center pt-3 pb-2">
                <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
            </div>
            
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">{{ title }}</h2>
            </div>

            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account</label>
                    <select 
                        v-model="form.account_id"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="">Select account</option>
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                            {{ account.name }} ({{ formatMoney(account.current_balance) }})
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (K)</label>
                    <input 
                        v-model.number="form.amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="0.00"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input 
                        v-model="form.description"
                        type="text"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :placeholder="type === 'deposit' ? 'e.g., Cash deposit' : 'e.g., Cash withdrawal'"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference (Optional)</label>
                    <input 
                        v-model="form.reference"
                        type="text"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., Receipt number"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input 
                        v-model="form.date"
                        type="date"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    />
                </div>

                <div class="flex gap-3 pt-4">
                    <button 
                        type="button"
                        @click="$emit('close')"
                        class="flex-1 py-3 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        :class="[
                            'flex-1 py-3 rounded-xl font-semibold text-white',
                            type === 'deposit' ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-red-500 hover:bg-red-600'
                        ]"
                    >
                        {{ type === 'deposit' ? 'Record Deposit' : 'Record Withdrawal' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue';

interface Account {
    id: number;
    name: string;
    current_balance: number;
}

const props = defineProps<{
    title: string;
    type: 'deposit' | 'withdrawal';
    accounts: Account[];
}>();

const emit = defineEmits<{
    close: [];
    submit: [data: any];
}>();

const form = reactive({
    account_id: '',
    amount: null as number | null,
    description: '',
    reference: '',
    date: new Date().toISOString().split('T')[0],
});

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2 });
};

const handleSubmit = () => {
    const dateField = props.type === 'deposit' ? 'deposit_date' : 'withdrawal_date';
    emit('submit', {
        account_id: form.account_id,
        amount: form.amount,
        description: form.description,
        reference: form.reference || null,
        [dateField]: form.date,
    });
};
</script>

<style scoped>
.safe-area-bottom { padding-bottom: env(safe-area-inset-bottom); }
</style>
