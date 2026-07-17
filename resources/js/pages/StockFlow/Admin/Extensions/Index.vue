<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';

interface Extension {
    id: number;
    code: string;
    name: string;
    description: string | null;
    version: string | null;
    is_active: boolean;
    trial_days: number;
    price_monthly_usd: number;
    price_yearly_usd: number;
    price_monthly_zmw: number;
    price_yearly_zmw: number;
    companies_count: number;
}

interface Company {
    id: number;
    name: string;
}

interface Assignments {
    [companyId: string]: {
        extension_id: number;
        extension_code: string;
        status: string;
        trial_ends_at: string | null;
    }[];
}

const props = defineProps<{
    extensions: Extension[];
    companies: Company[];
    assignments: Assignments;
}>();

const { success } = useNotifications();

const selectedCompany = ref<number | null>(null);
const selectedExtension = ref<number | null>(null);

const assignForm = useForm({
    sa_company_id: 0,
    extension_id: 0,
});

const submitAssign = () => {
    if (!selectedCompany.value || !selectedExtension.value) return;
    assignForm.sa_company_id = selectedCompany.value;
    assignForm.extension_id = selectedExtension.value;
    assignForm.post(route('stockflow.admin.extensions.assign'), {
        onSuccess: () => {
            selectedCompany.value = null;
            selectedExtension.value = null;
        },
    });
};

const getCompanyExtensions = (companyId: number) => {
    return props.assignments[companyId] || [];
};

// Pricing
const editingPrice = ref<number | null>(null);
const priceForm = useForm({ trial_days: 0, price_monthly_usd: 0, price_yearly_usd: 0, price_monthly_zmw: 0, price_yearly_zmw: 0 });

const startEditPrice = (ext: Extension) => {
    editingPrice.value = ext.id;
    priceForm.trial_days = ext.trial_days;
    priceForm.price_monthly_usd = ext.price_monthly_usd;
    priceForm.price_yearly_usd = ext.price_yearly_usd;
    priceForm.price_monthly_zmw = ext.price_monthly_zmw;
    priceForm.price_yearly_zmw = ext.price_yearly_zmw;
};

const savePrice = (ext: Extension) => {
    priceForm.put(route('stockflow.admin.extensions.pricing', ext.id), {
        onSuccess: () => { editingPrice.value = null; success('Pricing saved'); },
    });
};

const cancelEditPrice = () => { editingPrice.value = null; };
</script>

<template>
    <StockFlowLayout title="Extensions">
        <Head title="Extensions — StockFlow Admin" />
        <div class="min-h-screen bg-gray-50">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Extensions</h2>
                </div>

                <!-- Extensions List -->
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Available Extensions</h3>
                    </div>
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Extension</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Code</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Version</th>
                                <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase">Trial <span class="text-gray-400">(days)</span></th>
                                <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase">Monthly <span class="text-gray-400">(USD/ZMW)</span></th>
                                <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase">Yearly <span class="text-gray-400">(USD/ZMW)</span></th>
                                <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase">Companies</th>
                                <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ext in extensions" :key="ext.id" class="border-b last:border-0 hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ ext.name }}</p>
                                    <p v-if="ext.description" class="text-sm text-gray-500 mt-0.5">{{ ext.description }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ ext.code }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ ext.version || '-' }}</td>
                                <td class="px-6 py-4 text-center text-sm">
                                    <template v-if="editingPrice === ext.id">
                                        <input v-model="priceForm.trial_days" type="number" min="0" class="w-14 rounded border border-gray-200 px-1 py-1 text-xs text-center" />
                                    </template>
                                    <template v-else>
                                        <span class="text-gray-700 cursor-pointer hover:text-emerald-600" @click="startEditPrice(ext)">
                                            {{ ext.trial_days }}d
                                        </span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-center text-sm">
                                    <template v-if="editingPrice === ext.id">
                                        <div class="flex items-center justify-center gap-1">
                                            <span class="text-xs text-gray-400">$</span>
                                            <input v-model="priceForm.price_monthly_usd" type="number" step="0.01" class="w-16 rounded border border-gray-200 px-1 py-1 text-xs text-center" />
                                            <span class="text-xs text-gray-400">K</span>
                                            <input v-model="priceForm.price_monthly_zmw" type="number" step="0.01" class="w-16 rounded border border-gray-200 px-1 py-1 text-xs text-center" />
                                        </div>
                                    </template>
                                    <template v-else>
                                        <span class="text-gray-700 cursor-pointer hover:text-emerald-600" @click="startEditPrice(ext)">
                                            ${{ Number(ext.price_monthly_usd).toFixed(2) }} /
                                            K{{ Number(ext.price_monthly_zmw).toFixed(2) }}
                                        </span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-center text-sm">
                                    <template v-if="editingPrice === ext.id">
                                        <div class="flex items-center justify-center gap-1">
                                            <span class="text-xs text-gray-400">$</span>
                                            <input v-model="priceForm.price_yearly_usd" type="number" step="0.01" class="w-16 rounded border border-gray-200 px-1 py-1 text-xs text-center" />
                                            <span class="text-xs text-gray-400">K</span>
                                            <input v-model="priceForm.price_yearly_zmw" type="number" step="0.01" class="w-16 rounded border border-gray-200 px-1 py-1 text-xs text-center" />
                                        </div>
                                    </template>
                                    <template v-else>
                                        <span class="text-gray-700 cursor-pointer hover:text-emerald-600" @click="startEditPrice(ext)">
                                            ${{ Number(ext.price_yearly_usd).toFixed(2) }} /
                                            K{{ Number(ext.price_yearly_zmw).toFixed(2) }}
                                        </span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', ext.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500']">
                                        {{ ext.is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-500">{{ ext.companies_count }}</td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <template v-if="editingPrice === ext.id">
                                        <button @click="savePrice(ext)" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">Save</button>
                                        <button @click="cancelEditPrice" class="text-sm text-gray-400 hover:text-gray-600">Cancel</button>
                                    </template>
                                    <template v-else>
                                        <button @click="startEditPrice(ext)" class="text-sm text-gray-500 hover:text-emerald-600 font-medium">Pricing</button>
                                        <Link :href="route('stockflow.admin.extensions.toggle', ext.id)" method="post" as="button" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                                            {{ ext.is_active ? 'Disable' : 'Enable' }}
                                        </Link>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="!extensions.length">
                                <td colspan="9" class="px-6 py-8 text-center text-gray-400">
                                    No extensions registered. Add extension providers to config/stockflow.php
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Assign Extension to Company -->
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Assign Extension to Company</h3>
                    </div>
                    <div class="p-6">
                        <form @submit.prevent="submitAssign" class="flex gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                <select v-model="selectedCompany" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 min-w-[200px]">
                                    <option :value="null" disabled>Select company...</option>
                                    <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Extension</label>
                                <select v-model="selectedExtension" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 min-w-[200px]">
                                    <option :value="null" disabled>Select extension...</option>
                                    <option v-for="ext in extensions.filter(e => e.is_active)" :key="ext.id" :value="ext.id">{{ ext.name }}</option>
                                </select>
                            </div>
                            <button type="submit" :disabled="!selectedCompany || !selectedExtension" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                Assign
                            </button>
                        </form>
                    </div>

                    <!-- Current Assignments -->
                    <div v-if="Object.keys(assignments).length" class="border-t">
                        <div class="px-6 py-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Current Assignments</h4>
                            <div v-for="company in companies" :key="company.id" class="mb-3 last:mb-0">
                                <div v-if="getCompanyExtensions(company.id).length" class="flex items-center gap-3 flex-wrap">
                                    <span class="text-sm font-medium text-gray-700 min-w-[150px]">{{ company.name }}</span>
                                    <span v-for="ce in getCompanyExtensions(company.id)" :key="ce.extension_id" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset"
                                        :class="ce.status === 'trial' ? 'bg-amber-50 text-amber-700 ring-amber-600/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'">
                                        {{ ce.extension_code }}
                                        <span v-if="ce.status === 'trial' && ce.trial_ends_at" class="text-amber-500">(trial until {{ ce.trial_ends_at }})</span>
                                        <Link :href="route('stockflow.admin.extensions.revoke', ce.extension_id)" method="post" as="button" class="text-emerald-400 hover:text-red-500 ml-1">&times;</Link>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
