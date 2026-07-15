<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';

interface Company {
    id: number;
    name: string;
    subdomain: string | null;
    email: string | null;
    status: string;
    tagline: string | null;
    brand_color: string;
}

defineProps<{
    companies: Company[];
}>();
</script>

<template>
    <StockFlowLayout title="Companies">
        <Head title="Companies — StockFlow Admin" />
        <div class="min-h-screen bg-gray-50">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Companies</h2>
                    <Link :href="route('stockflow.admin.companies.create')" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium">
                        + New Company
                    </Link>
                </div>

                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Subdomain</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="company in companies" :key="company.id" class="border-b last:border-0 hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-bold" :style="{ backgroundColor: company.brand_color || '#059669' }">
                                            {{ company.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ company.name }}</p>
                                            <p v-if="company.tagline" class="text-xs text-gray-500">{{ company.tagline }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="text-sm bg-gray-100 px-2 py-0.5 rounded">{{ company.subdomain }}.mygrownet.com</code>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800': company.status === 'active',
                                            'bg-yellow-100 text-yellow-800': company.status === 'suspended',
                                            'bg-red-100 text-red-800': company.status === 'cancelled',
                                        }">
                                        {{ company.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('stockflow.admin.companies.show', company.id)" class="text-sm text-emerald-600 hover:text-emerald-800 font-medium">
                                        View &rarr;
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="companies.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    No companies yet. <Link :href="route('stockflow.admin.companies.create')" class="text-emerald-600 hover:underline">Create one</Link>.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
