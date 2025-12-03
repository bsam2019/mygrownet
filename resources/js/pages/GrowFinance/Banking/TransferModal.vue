<template>
    <div class="fixed inset-0 z-50 flex items-end justify-center">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="$emit('close')" />
        <div class="relative w-full max-w-md bg-white rounded-t-3xl shadow-2xl safe-area-bottom">
            <div class="flex justify-center pt-3 pb-2">
                <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
            </div>
            
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Transfer Funds</h2>
            </div>

            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Account</label>
                    <select 
                        v-model="form.from_account_id"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="">Select source account</option>
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                            {{ account.name }} ({{ formatMoney(account.current_balance) }})
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Account</label>
                    <select 
                        v-model="form.to_account_id"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="">Select destination account</option>
                        <option 
                            v-for="account in accounts" 
                            :key="account.id" 
                            :value="account.id"
                            :disabled="account.id === Number(form.from_account_id)"
                        >
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                    <input 
                        v-model="form.description"
                        type="text"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g., Transfer to bank"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input 
                        v-model="form.transfer_date"
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
                        class="flex-1 py-3 rounded-xl bg-blue-500 hover:bg-blue-600 font-semibold text-white"
                    >
                        Transfer
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

defineProps<{
    accounts: Account[];
}>();

const emit = defineEmits<{
    close: [];
    submit: [data: any];
}>();

const form = reactive({
    from_account_id: '',
    to_account_id: '',
    amount: null as number | null,
    description: '',
    transfer_date: new Date().toISOString().split('T')[0],
});

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2 });
};

const handleSubmit = () => {
    emit('submit', { ...form });
};
</script>

<style scoped>
.safe-area-bottom { padding-bottom: env(safe-area-inset-bottom); }
</style>
